<?php

// Local MySQL verification. Every smoke write, including dashboard snapshots, rolls back.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\BsUsageCheckService;
use App\Services\BsUsageDashboardService;
use App\Support\AppTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (DB::connection()->getDriverName() !== 'mysql') {
    throw new RuntimeException('This verification is intended for the local MySQL database.');
}
config(['session.driver' => 'array', 'cache.default' => 'array']);
$app['env'] = 'testing';
$finance = User::permission('akses.approval-finance')->firstOrFail();
auth()->setUser($finance);
$tables = ['user', 'cost_category', 'periode', 'pengajuan_bs', 'approval_bs_finance',
    'transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail',
    'transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail',
    'bs_usage_logs', 'bs_usage_approval_logs', 'bs_cash_flow_snapshots', 'stock_cash_flow_snapshots',
    'reimbursement_claims', 'bs_usage_checks', 'bs_usage_check_details', 'document_number_sequences'];
$fingerprint = function () use ($tables): array {
    $result = [];
    foreach ($tables as $table) {
        $hash = hash_init('sha256');
        $query = DB::table($table);
        if ($table === 'document_number_sequences') {
            $query->orderBy('type')->orderBy('date');
        } else {
            $query->orderBy('id');
        }
        foreach ($query->cursor() as $row) {
            hash_update($hash, json_encode($row, JSON_THROW_ON_ERROR));
        }
        $result[$table] = hash_final($hash);
    }

    return $result;
};
$before = $fingerprint();
$results = ['driver' => 'mysql', 'rendered_requests' => 0, 'latest_checks_verified' => []];
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$visit = function (array $filters = []) use ($kernel, &$results): void {
    $response = $kernel->handle(Request::create(route('dashboard.transaksi', $filters), 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']));
    if ($response->getStatusCode() !== 200 || !str_contains($response->getContent(), 'Rekap Pengajuan dan Hasil Finance')) {
        throw new RuntimeException('Dashboard render failed, HTTP '.$response->getStatusCode());
    }
    $results['rendered_requests']++;
};
DB::beginTransaction();
try {
    $visit();
    $visit(['pemakaian_periode' => '']);
    $visit(['pemakaian_periode' => '', 'pemakaian_jenis' => 'OPR']);
    $visit(['pemakaian_periode' => '', 'pemakaian_jenis' => 'PD']);
    $visit(['pemakaian_periode' => '2099-01']);

    $checks = app(BsUsageCheckService::class);
    foreach (array_intersect_key(BsUsageCheckService::TYPES, array_flip(['OPR', 'PD'])) as $type => $model) {
        $source = $model::where('status', 'VALIDATED')->has('details')->first();
        if (!$source) {
            continue;
        }
        $snapshot = $checks->snapshot($source);
        for ($i = 0; $i < 2; $i++) {
            $check = $checks->store($finance, $type, $source->id, [
                'tanggal_pengecekan' => AppTime::now()->toDateString(),
                'source_token' => $checks->token($type, $source->id, $snapshot['fingerprint']),
                'details' => $snapshot['items']->map(fn ($item) => [
                    'cost_category_id' => $item['cost_category_id'],
                    'nominal_disetujui' => $i === 0 ? $item['nominal_asal'] : '0',
                    'catatan' => 'Dashboard verification rolled back',
                ])->all(),
            ]);
        }
        $found = false;
        $page = 1;
        do {
            $request = Request::create('/dashboard/transaksi', 'GET', [
                'pemakaian_periode' => $source->periode, 'pemakaian_jenis' => $type, 'pemakaian_page' => $page,
            ]);
            $request->setUserResolver(fn () => $finance);
            $app->instance('request', $request);
            $data = app(BsUsageDashboardService::class)->data($request);
            foreach ($data['rows'] as $row) {
                if ((int) $row->source_id !== (int) $source->id) continue;
                if ((int) $row->check_id !== $check->id || (int) $row->needs_recheck !== 0
                    || (float) $row->nominal_finance !== 0.0 || $row->selisih === null) {
                    throw new RuntimeException('Latest Finance check or zero-approval difference is incorrect.');
                }
                $found = true;
            }
            $page++;
        } while (!$found && $data['rows']->hasMorePages());
        if (!$found) throw new RuntimeException('Confirmed source missing from dashboard.');
        $results['latest_checks_verified'][] = $type;
        $visit(['pemakaian_periode' => $source->periode, 'pemakaian_jenis' => $type]);

        auth()->setUser($source->user);
        $visit(['pemakaian_periode' => '']);
        auth()->setUser($finance);
    }
} finally {
    DB::rollBack();
}
if ($before !== $fingerprint()) throw new RuntimeException('Database content changed after rollback.');
$results['all_17_tables_unchanged'] = true;
$directory = storage_path('app/private/bs-dashboard-verification');
if (!is_dir($directory)) mkdir($directory, 0700, true);
file_put_contents($directory.'/result.json', json_encode($results, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($results, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
