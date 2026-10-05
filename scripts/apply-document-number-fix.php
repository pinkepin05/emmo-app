<?php

// Local activation: full backup first, then migration, repair, and reconciliation.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

if (($argv[1] ?? '') !== '--apply') {
    throw new RuntimeException('Use --apply after the isolated document-number tests pass.');
}
$connection = DB::connection();
if ($connection->getDriverName() !== 'mysql') throw new RuntimeException('This activation script requires MySQL.');
$config = $connection->getConfig();
$directory = storage_path('app/private/document-number-backups/activation-'.gmdate('Ymd_His'));
if (!mkdir($directory, 0700, true)) throw new RuntimeException('Could not create backup directory.');
$dump = $directory.'/before.sql';
$options = $directory.'/connection.cnf';
$escape = fn ($value) => '"'.str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], (string) $value).'"';
file_put_contents($options, "[client]\n".'host='.$escape($config['host'])."\n".'port='.(int) $config['port']."\n".'user='.$escape($config['username'])."\n".'password='.$escape($config['password'])."\n");
try {
    $process = new Process(['C:/xampp/mysql/bin/mysqldump.exe', '--defaults-extra-file='.$options, '--single-transaction', '--quick', '--routines', '--triggers', '--events', '--hex-blob', '--result-file='.$dump, $connection->getDatabaseName()]);
    $process->setTimeout(60)->mustRun();
} finally {
    if (is_file($options)) unlink($options);
}
if (!is_file($dump) || filesize($dump) < 100) throw new RuntimeException('Missing or empty database backup.');

$numberColumns = [
    'pengajuan_bs' => 'document_no',
    'approval_bs_finance' => 'document_no',
    'transaksi_operasional_area_sales_header' => 'transaksi_id',
    'transaksi_operasional_area_sales_detail' => 'transaksi_id',
    'transaksi_perjalanan_dinas_header' => 'transaksi_id',
    'transaksi_perjalanan_dinas_detail' => 'transaksi_id',
    'cash_flow_snapshots' => null,
    'bs_cash_flow_snapshots' => null,
    'pengajuan_bs_members' => null,
];
$before = [];
foreach ($numberColumns as $table => $column) {
    $before[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
}
$logsBefore = DB::table('bs_activity_logs')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
file_put_contents($directory.'/before.json', json_encode(['database' => $connection->getDatabaseName(), 'dump_sha256' => hash_file('sha256', $dump), 'tables' => $before, 'logs' => $logsBefore], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

$exit = Artisan::call('migrate', ['--path' => 'database/migrations/2026_09_17_100000_restore_readable_document_numbers.php', '--force' => true]);
echo Artisan::output();
if ($exit) throw new RuntimeException('Migration failed; backup: '.$dump);
$exit = Artisan::call('bs:repair-document-numbers', ['--apply' => true]);
echo Artisan::output();
if ($exit) throw new RuntimeException('Repair failed; backup: '.$dump);

$changes = [];
foreach ($numberColumns as $table => $column) {
    $after = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
    if (count($after) !== count($before[$table])) throw new RuntimeException('Row count changed: '.$table);
    $key = fn ($row) => isset($row['id']) ? (string) $row['id'] : json_encode($row);
    $afterByKey = [];
    foreach ($after as $row) $afterByKey[$key($row)] = $row;
    foreach ($before[$table] as $row) {
        $updated = $afterByKey[$key($row)] ?? throw new RuntimeException('Missing row: '.$table);
        if ($column) {
            if ($row[$column] !== $updated[$column]) {
                $changes[] = ['table' => $table, 'id' => $row['id'], 'old' => $row[$column], 'new' => $updated[$column]];
            }
            unset($row[$column], $updated[$column]);
        }
        if ($row !== $updated) throw new RuntimeException('A non-number field changed: '.$table);
    }
}
foreach ($logsBefore as $row) {
    if ((array) DB::table('bs_activity_logs')->where('id', $row['id'])->first() !== $row) throw new RuntimeException('Historical audit log changed.');
}
foreach (['transaksi_operasional_area_sales', 'transaksi_perjalanan_dinas'] as $base) {
    $orphans = DB::table($base.'_detail as d')->leftJoin($base.'_header as h', 'h.transaksi_id', '=', 'd.transaksi_id')->whereNull('h.id')->count();
    if ($orphans) throw new RuntimeException('Orphaned details found: '.$base);
}
file_put_contents($directory.'/result.json', json_encode(['changes' => $changes, 'verified' => true, 'completed_at' => gmdate(DATE_ATOM)], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo 'Verified: only document numbers changed; amounts, statuses, dates, BS links, snapshots, and historical logs preserved.'.PHP_EOL;
echo 'Database backup and reconciliation: '.$directory.PHP_EOL;
