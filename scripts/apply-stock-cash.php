<?php

// Build the combined stock-cash summaries without rewriting source transactions.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BsCashFlowSnapshot;
use App\Models\StockCashFlowSnapshot;
use App\Services\PeriodService;
use App\Services\StockCashFlowService;
use App\Support\Money;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $connection = DB::connection();
    if (!app()->environment('local') || $connection->getDriverName() !== 'mysql'
        || !in_array($connection->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)) {
        throw new RuntimeException('This script requires the local application and a local MySQL/MariaDB database.');
    }

    if (!in_array('--apply', $argv, true)) {
        echo json_encode([
            'combined_table_exists' => Schema::hasTable('stock_cash_flow_snapshots'),
            'event_dalam_rows' => DB::table('perongkosan_event_dalam_kota')->count(),
            'event_luar_rows' => DB::table('perongkosan_event_luar_kota')->count(),
        ], JSON_PRETTY_PRINT).PHP_EOL;
        exit;
    }

    $sources = ['periode', 'cash_flow_snapshots', 'pengajuan_bs', 'approval_bs_finance',
        'transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail',
        'transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail',
        'perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'];
    $rows = fn ($table) => DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    $before = [];
    foreach ($sources as $table) $before[$table] = $rows($table);
    $before['bs_cash_flow_snapshots'] = $rows('bs_cash_flow_snapshots');
    if (Schema::hasTable('stock_cash_flow_snapshots')) $before['stock_cash_flow_snapshots'] = $rows('stock_cash_flow_snapshots');
    $directory = storage_path('app/private/stock-cash-backups');
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('Cannot create backup directory.');
    $backup = $directory.'/before-'.gmdate('Ymd_His').'-'.bin2hex(random_bytes(3)).'.json';
    $json = json_encode($before, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    if (file_put_contents($backup, $json, LOCK_EX) !== strlen($json)) throw new RuntimeException('Cannot save backup.');

    $code = Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php',
        '--no-interaction' => true,
    ]);
    echo Artisan::output();
    if ($code !== 0) throw new RuntimeException('Migration failed; backup: '.$backup);

    DB::transaction(function () use ($sources, $before, $rows) {
        $periods = DB::table('periode')->orderBy('periode')->lockForUpdate()->get();
        $cash = app(StockCashFlowService::class);
        foreach ($periods as $period) $cash->ensureSnapshotForPeriod($period->periode);

        foreach ($sources as $table) {
            if ($rows($table) !== $before[$table]) throw new RuntimeException('Source data unexpectedly changed: '.$table);
        }
        $closed = $periods->where('status', 'CLOSED')->pluck('periode')->all();
        foreach ($before['bs_cash_flow_snapshots'] as $snapshot) {
            if (in_array($snapshot['periode'], $closed, true) && $snapshot['saldo_akhir'] !== null
                && (array) DB::table('bs_cash_flow_snapshots')->where('id', $snapshot['id'])->first() !== $snapshot) {
                throw new RuntimeException('A frozen BS summary changed.');
            }
        }

        foreach (StockCashFlowSnapshot::all() as $snapshot) {
            $bs = BsCashFlowSnapshot::where('user_id', $snapshot->user_id)->where('periode', $snapshot->periode)->firstOrFail();
            $eventTotal = 0;
            $start = $snapshot->periode.'-01';
            $end = app(PeriodService::class)->next($snapshot->periode).'-01';
            foreach (['perongkosan_event_dalam_kota' => 'total_event_dalam', 'perongkosan_event_luar_kota' => 'total_event_luar'] as $table => $column) {
                $amount = Money::minor(DB::table($table)->where('user_id', $snapshot->user_id)->where('tanggal', '>=', $start)->where('tanggal', '<', $end)->sum('nominal'));
                if (Money::minor($snapshot->$column) !== $amount) throw new RuntimeException('Event total does not match source.');
                $eventTotal += $amount;
            }
            if ($snapshot->total_masuk !== $bs->total_masuk || $snapshot->total_keluar_bs !== $bs->total_keluar
                || Money::minor($snapshot->total_keluar) !== Money::minor($bs->total_keluar) + $eventTotal) {
                throw new RuntimeException('Combined movements do not match source.');
            }
            if (in_array($snapshot->periode, $closed, true)) {
                $expected = Money::minor($snapshot->saldo_awal) + Money::minor($snapshot->total_masuk)
                    - Money::minor($snapshot->total_keluar) + Money::minor($snapshot->penyesuaian);
                if ($snapshot->saldo_akhir === null || Money::minor($snapshot->saldo_akhir) !== $expected) {
                    throw new RuntimeException('Combined closing balance does not match its movements; reconcile the historical BS summary.');
                }
            } elseif ($snapshot->saldo_akhir !== null) {
                throw new RuntimeException('An open period has a closing balance.');
            }
        }
    }, 3);

    Artisan::call('view:clear');
    echo json_encode(['verified' => true, 'backup' => $backup, 'combined_snapshots' => StockCashFlowSnapshot::count(),
        'source_transactions_unchanged' => true, 'closed_bs_balances_preserved' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit(1);
}
