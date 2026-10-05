<?php

// Local verification only. Existing business data is hashed, never exported as database rows.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\BsUsageCheck;
use App\Services\BsUsageCheckService;
use App\Services\BsUsageDashboardService;
use App\Support\AppTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$directory = storage_path('app/private/transaction-recap-verification');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$tables = ['user', 'cost_category', 'periode', 'pengajuan_bs', 'approval_bs_finance',
    'transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail',
    'transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail',
    'perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota',
    'bs_usage_logs', 'bs_usage_approval_logs', 'bs_cash_flow_snapshots', 'stock_cash_flow_snapshots',
    'reimbursement_claims', 'bs_usage_checks', 'bs_usage_check_details', 'document_number_sequences'];
$fingerprint = function (array $columns): array {
    $result = [];
    foreach ($columns as $table => $fields) {
        $hash = hash_init('sha256');
        $query = DB::table($table)->select($fields);
        if ($table === 'document_number_sequences') $query->orderBy('type')->orderBy('date');
        else $query->orderBy('id');
        $count = 0;
        foreach ($query->cursor() as $row) { hash_update($hash, json_encode($row, JSON_THROW_ON_ERROR)); $count++; }
        $result[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }
    return $result;
};
if (($argv[1] ?? '') === 'before') {
    $columns = [];
    foreach ($tables as $table) $columns[$table] = Schema::getColumnListing($table);
    file_put_contents($directory.'/before.json', json_encode(['columns' => $columns, 'hashes' => $fingerprint($columns)], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    echo "Stored hashes of 19 tables before migration.\n";
    exit;
}
if (($argv[1] ?? '') !== 'after') throw new RuntimeException('Use before or after.');
$before = json_decode(file_get_contents($directory.'/before.json'), true, flags: JSON_THROW_ON_ERROR);
if ($before['hashes'] !== $fingerprint($before['columns'])) throw new RuntimeException('Existing column values changed during migration.');
$columns = [];
foreach ($tables as $table) $columns[$table] = Schema::getColumnListing($table);
$fullBefore = $fingerprint($columns);
config(['session.driver' => 'array', 'cache.default' => 'array']);
$app['env'] = 'testing';
$finance = User::permission('akses.approval-finance')->firstOrFail();
auth()->setUser($finance);
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$service = app(BsUsageCheckService::class);
$results = ['requests' => 0, 'source_types' => [], 'event_confirmations' => []];
$visit = function (string $url, string $method = 'GET', array $data = []) use ($kernel, &$results) {
    $response = $kernel->handle(Request::create($url, $method, $data, [], [], ['HTTP_ACCEPT' => 'text/html']));
    if (($method === 'GET' && $response->getStatusCode() !== 200) || $response->getStatusCode() >= 400) {
        throw new RuntimeException('HTTP '.$response->getStatusCode().' at '.parse_url($url, PHP_URL_PATH));
    }
    $results['requests']++;
    return $response;
};
DB::beginTransaction();
try {
    $visit(route('dashboard.transaksi'));
    foreach (BsUsageCheckService::TYPES as $type => $model) {
        $event = in_array($type, ['EDK', 'ELK']);
        if ($event) {
            $permission = $type === 'EDK' ? 'akses.event-dalam-kota' : 'akses.event-luar-kota';
            $source = $model::whereIn('user_id', User::permission($permission)->select('id'))->first();
        } else {
            $source = $model::where('status', 'VALIDATED')->has('details')->first();
        }
        if (!$source) continue;
        if ($event) {
            auth()->setUser($source->user);
            $visit(route('transaction-recap.show', [$type, $source->id]));
            $visit(route('transaction-recap.confirm-event', [$type, $source->id]), 'POST', [
                'source_token' => $service->token($type, $source->id, $service->snapshot($source)['fingerprint']),
            ]);
            if ($source->fresh()->status !== 'VALIDATED') throw new RuntimeException('Event confirmation failed.');
            $results['event_confirmations'][] = $type;
            auth()->setUser($finance);
        }
        $source->refresh();
        $visit(route('bs-usage-checks.create', [$type, $source->id]));
        $original = $source->getAttributes();
        foreach (['REVISION', 'REJECTED', 'APPROVED'] as $decision) {
            $snapshot = $service->snapshot($source);
            $start = BsUsageCheck::count();
            $visit(route('bs-usage-checks.store', [$type, $source->id]), 'POST', [
                'tanggal_pengecekan' => AppTime::now()->toDateString(), 'keputusan' => $decision,
                'catatan_keputusan' => 'Verifikasi lokal - dibatalkan setelah pengujian',
                'source_token' => $service->token($type, $source->id, $snapshot['fingerprint']),
                'details' => $snapshot['items']->map(fn ($item) => ['cost_category_id' => (int) $item['cost_category_id'],
                    'nominal_disetujui' => '0', 'catatan' => 'Local rollback smoke'])->all(),
            ]);
            if (BsUsageCheck::count() !== $start + 1) throw new RuntimeException('Decision was not saved.');
            $last = BsUsageCheck::latest('id')->firstOrFail();
            if ($last->keputusan !== $decision) throw new RuntimeException('Incorrect decision.');
            $visit(route('bs-usage-checks.show', $last));
        }
        if ($original !== $source->fresh()->getAttributes()) throw new RuntimeException('Finance altered source data.');
        $request = Request::create('/laporan-transaksi', 'GET', ['pemakaian_periode' => '', 'pemakaian_jenis' => $type, 'pemakaian_referensi' => $source->transaksi_id]);
        $request->setUserResolver(fn () => $finance);
        $app->instance('request', $request);
        $recap = app(BsUsageDashboardService::class)->data($request);
        $row = $recap['rows']->firstWhere('source_id', $source->id);
        if (!$row || (int) $row->check_id !== $last->id || $row->finance_status !== 'APPROVED' || $row->selisih === null) {
            throw new RuntimeException('Latest check incorrectly reflected in recap.');
        }
        $results['source_types'][] = $type;
        $visit(route('transaction-recap.show', [$type, $source->id]));
    }
    foreach (['OPR', 'PD', 'EDK', 'ELK'] as $type) {
        $response = $visit(route('transaction-recap.module', ['jenis' => $type, 'pemakaian_periode' => '']));
        // Private, local render for browser inspection; it contains temporary smoke decisions.
        file_put_contents($directory.'/module-'.$type.'.html', $response->getContent());
    }
    $visit(route('bs-usage-checks.index'));
    $visit(route('bs-usage-checks.report'));
} finally {
    DB::rollBack();
}
if ($fullBefore !== $fingerprint($columns)) throw new RuntimeException('Smoke writes did not fully roll back.');
$results['existing_business_values_preserved'] = true;
$results['all_19_tables_unchanged_after_smoke'] = true;
file_put_contents($directory.'/result.json', json_encode($results, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($results, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
