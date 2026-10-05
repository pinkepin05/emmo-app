<?php

// Local verification: metadata/data hashes only. Smoke writes always roll back.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BsUsageCheck;
use App\Models\User;
use App\Services\BsUsageCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$mode = $argv[1] ?? 'before';
$directory = storage_path('app/private/bs-finance-check-verification');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$tables = ['user', 'cost_category', 'periode', 'pengajuan_bs', 'approval_bs_finance',
    'transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail',
    'transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail',
    'bs_usage_logs', 'bs_usage_approval_logs', 'bs_cash_flow_snapshots', 'stock_cash_flow_snapshots', 'reimbursement_claims'];
$fingerprint = function () use ($tables) {
    $result = [];
    foreach ($tables as $table) {
        $hash = hash_init('sha256');
        $count = 0;
        foreach (DB::table($table)->orderBy('id')->cursor() as $row) {
            hash_update($hash, json_encode($row, JSON_THROW_ON_ERROR));
            $count++;
        }
        $result[$table] = ['count' => $count, 'sha256' => hash_final($hash)];
    }

    return $result;
};

if ($mode === 'before') {
    file_put_contents($directory.'/before.json', json_encode($fingerprint(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    echo "Stored hashes of 14 existing tables; no transaction data exported.\n";
    exit;
}
if ($mode !== 'after' || !is_file($directory.'/before.json')) throw new RuntimeException('Run before verification first.');
$before = json_decode(file_get_contents($directory.'/before.json'), true, flags: JSON_THROW_ON_ERROR);
if ($before !== $fingerprint()) throw new RuntimeException('Existing table content changed since before verification.');
foreach (['bs_usage_checks', 'bs_usage_check_details'] as $table) {
    if (!Schema::hasTable($table)) throw new RuntimeException('Missing table: '.$table);
}

// No persistent session writes and no external notifications are needed for these routes.
config(['session.driver' => 'array', 'cache.default' => 'array']);
$app['env'] = 'testing';
$finance = User::permission('akses.approval-finance')->firstOrFail();
auth()->setUser($finance);
$service = app(BsUsageCheckService::class);
$routes = [];
$reviewed = [];
$countBefore = BsUsageCheck::count();
$detailCountBefore = DB::table('bs_usage_check_details')->count();
$sequenceBefore = DB::table('document_number_sequences')->orderBy('type')->orderBy('date')->get()->toJson();
DB::beginTransaction();
try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $visit = function (string $url, string $method = 'GET', array $data = []) use ($kernel, &$routes) {
        $request = \Illuminate\Http\Request::create($url, $method, $data, [], [], ['HTTP_ACCEPT' => 'text/html']);
        $response = $kernel->handle($request);
        if ($response->getStatusCode() >= 400) throw new RuntimeException('Smoke route failed: '.$response->getStatusCode().' '.$url);
        if ($method === 'POST' && str_contains((string) $response->headers->get('Location'), '/login')) throw new RuntimeException('Unexpected login redirect.');
        if ($method === 'GET' && $response->getStatusCode() !== 200) throw new RuntimeException('Unexpected GET redirect: '.$url);
        $routes[] = ['route' => parse_url($url, PHP_URL_PATH), 'method' => $method, 'status' => $response->getStatusCode()];

        return $response;
    };
    $visit(route('bs-usage-checks.index'));
    $visit(route('bs-usage-checks.report'));
    foreach (array_intersect_key(BsUsageCheckService::TYPES, array_flip(['OPR', 'PD'])) as $type => $model) {
        $source = $model::has('details')->first();
        if (!$source) continue;
        $visit(route('bs-usage-checks.create', [$type, $source->id]));
        $snapshot = $service->snapshot($source);
        $data = [
            'tanggal_pengecekan' => \App\Support\AppTime::now()->toDateString(),
            'source_token' => $service->token($type, $source->id, $snapshot['fingerprint']),
            'details' => $snapshot['items']->map(fn ($item) => [
                'cost_category_id' => $item['cost_category_id'], 'nominal_disetujui' => '0', 'catatan' => 'Verification rolled back',
            ])->all(),
        ];
        $startCount = BsUsageCheck::count();
        for ($i = 0; $i < 2; $i++) {
            $visit(route('bs-usage-checks.store', [$type, $source->id]), 'POST', $data);
        }
        if (BsUsageCheck::count() !== $startCount + 2) throw new RuntimeException('Rechecks did not create two documents.');
        $check = BsUsageCheck::latest('id')->firstOrFail();
        $visit(route('bs-usage-checks.show', $check));
        $visit(route('bs-usage-checks.report', ['mode' => 'semua', 'source_id' => $source->id, 'jenis_source' => $type]));
        $reviewed[] = $type;
    }
    if ($before !== $fingerprint()) throw new RuntimeException('Smoke check changed existing financial tables.');
} finally {
    DB::rollBack();
}
if (BsUsageCheck::count() !== $countBefore || DB::table('bs_usage_check_details')->count() !== $detailCountBefore) throw new RuntimeException('Smoke rows were not rolled back.');
if ($sequenceBefore !== DB::table('document_number_sequences')->orderBy('type')->orderBy('date')->get()->toJson()) throw new RuntimeException('Number reservation was not rolled back.');
$result = ['existing_tables_unchanged' => true, 'reviewed_source_types' => $reviewed, 'routes' => $routes,
    'smoke_rows_rolled_back' => true, 'sequence_unchanged' => true, 'simultaneous_concurrency_tested' => false];
file_put_contents($directory.'/after.json', json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
