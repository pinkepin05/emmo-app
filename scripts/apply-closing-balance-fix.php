<?php

// Apply only the closing-balance migration to the local application, with a backup.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$connection = DB::connection();
if (!app()->environment('local') || $connection->getDriverName() !== 'mysql'
    || !in_array($connection->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('This script requires the local application and a local MySQL/MariaDB database.');
}

$migration = '2026_09_18_100000_defer_closing_balances_until_period_close';
$tables = ['bs_cash_flow_snapshots', 'cash_flow_snapshots'];
$periods = DB::table('periode')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
$closed = array_column(array_filter($periods, fn ($row) => $row['status'] === 'CLOSED'), 'periode');
$before = ['database' => $connection->getDatabaseName(), 'periods' => $periods, 'snapshots' => [], 'columns' => []];
foreach ($tables as $table) {
    $before['snapshots'][$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    $before['columns'][$table] = Schema::getColumns($table);
}

$applied = DB::table('migrations')->where('migration', $migration)->exists();
if (!in_array('--apply', $argv, true)) {
    echo json_encode(['migration_applied' => $applied, 'snapshot_counts' => array_map('count', $before['snapshots'])], JSON_PRETTY_PRINT).PHP_EOL;
    exit;
}

$backup = null;
if (!$applied) {
    $directory = storage_path('app/private/closing-balance-backups');
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new RuntimeException('Cannot create backup directory.');
    $backup = $directory.'/before-'.gmdate('Ymd_His').'-'.bin2hex(random_bytes(3)).'.json';
    $json = json_encode($before, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    if (file_put_contents($backup, $json, LOCK_EX) !== strlen($json)) throw new RuntimeException('Backup could not be saved.');

    $code = Artisan::call('migrate', ['--path' => 'database/migrations/'.$migration.'.php', '--no-interaction' => true]);
    echo Artisan::output();
    if ($code !== 0) throw new RuntimeException('Migration failed; backup: '.$backup);
}

foreach ($tables as $table) {
    $expected = array_map(function ($row) use ($closed) {
        if (!in_array($row['periode'], $closed, true)) $row['saldo_akhir'] = null;
        return $row;
    }, $before['snapshots'][$table]);
    $actual = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    if ($actual !== $expected) throw new RuntimeException('Unexpected snapshot change in '.$table.'; review backup: '.$backup);
    $column = collect(Schema::getColumns($table))->firstWhere('name', 'saldo_akhir');
    if (!$column['nullable']) throw new RuntimeException('Closing balance is not nullable in '.$table);
}
if (DB::table('periode')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all() !== $periods) {
    throw new RuntimeException('Period records changed; review backup: '.$backup);
}
Artisan::call('view:clear');
echo json_encode(['verified' => true, 'backup' => $backup, 'closed_balances_preserved' => true, 'open_balances_null' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
