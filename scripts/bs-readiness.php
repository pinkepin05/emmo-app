<?php

// Local deployment verification; never prints connection credentials.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

$mode = $argv[1] ?? 'inspect';
$connection = DB::connection();
if ($connection->getDriverName() !== 'mysql') throw new RuntimeException('A MySQL-compatible connection is required.');
$config = $connection->getConfig();
$directory = storage_path('app/private/bs-readiness');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$stateFile = $directory.'/state.json';
$state = is_file($stateFile) ? json_decode(file_get_contents($stateFile), true, flags: JSON_THROW_ON_ERROR) : [];
$tables = ['user', 'pengajuan_bs', 'approval_bs_finance', 'periode', 'transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail', 'cash_flow_snapshots'];

if ($mode === 'inspect') {
    $data = ['driver' => $connection->getDriverName(), 'database' => $connection->getDatabaseName(), 'server' => DB::selectOne('select version() as version')->version,
        'tables' => [], 'periods' => DB::table('periode')->orderBy('periode')->get(['periode', 'status']),
        'migration_applied' => Schema::hasTable('bs_cash_flow_snapshots')];
    foreach ($tables as $table) $data['tables'][$table] = DB::table($table)->count();
    $data['engines'] = DB::select('select TABLE_NAME as name, ENGINE as engine from information_schema.TABLES where TABLE_SCHEMA = ? and TABLE_TYPE = ?', [$connection->getDatabaseName(), 'BASE TABLE']);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit;
}

if ($mode === 'backup') {
    $stamp = gmdate('Ymd_His');
    $dump = $directory.'/before-bs-'.$stamp.'.sql';
    $optionsFile = $directory.'/connection-'.bin2hex(random_bytes(8)).'.cnf';
    $escape = fn ($v) => '"'.str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], (string) $v).'"';
    file_put_contents($optionsFile, "[client]\n".'host='.$escape($config['host'])."\n".'port='.(int) $config['port']."\n".'user='.$escape($config['username'])."\n".'password='.$escape($config['password'])."\n");
    try {
        $process = new Process(['C:/xampp/mysql/bin/mysqldump.exe', '--defaults-extra-file='.$optionsFile, '--single-transaction', '--quick', '--routines', '--triggers', '--events', '--hex-blob', '--result-file='.$dump, $connection->getDatabaseName()]);
        $process->setTimeout(180);
        $process->mustRun();
    } finally { if (is_file($optionsFile)) unlink($optionsFile); }
    if (!is_file($dump) || filesize($dump) < 100) throw new RuntimeException('Backup is missing or empty.');
    $state = ['source_database' => $connection->getDatabaseName(), 'backup' => $dump, 'backup_sha256' => hash_file('sha256', $dump), 'created_at' => gmdate(DATE_ATOM), 'source_counts' => []];
    foreach ($tables as $table) $state['source_counts'][$table] = DB::table($table)->count();
    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo json_encode(['backup' => $dump, 'bytes' => filesize($dump), 'sha256' => $state['backup_sha256']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit;
}

if ($mode === 'clone') {
    if (($state['source_database'] ?? null) !== $connection->getDatabaseName() || !is_file($state['backup'] ?? '') || hash_file('sha256', $state['backup']) !== $state['backup_sha256']) throw new RuntimeException('Verified backup for this database is required.');
    $testDatabase = 'emmo_bs_verify_'.gmdate('Ymd_His');
    if (!preg_match('/^emmo_bs_verify_\d{8}_\d{6}$/D', $testDatabase) || $testDatabase === $connection->getDatabaseName()) throw new RuntimeException('Unsafe test database name.');
    DB::statement('CREATE DATABASE `'.$testDatabase.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $cloneConfig = $config;
    unset($cloneConfig['name']);
    $cloneConfig['database'] = $testDatabase;
    $cloneConfig['url'] = null;
    config(['database.connections.bs_verify' => $cloneConfig]);
    $pdo = DB::connection('bs_verify')->getPdo();
    // mysql client handles stored-program DELIMITER syntax in the dump.
    $optionsFile = $directory.'/connection-'.bin2hex(random_bytes(8)).'.cnf';
    $escape = fn ($v) => '"'.str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], (string) $v).'"';
    file_put_contents($optionsFile, "[client]\n".'host='.$escape($config['host'])."\n".'port='.(int) $config['port']."\n".'user='.$escape($config['username'])."\n".'password='.$escape($config['password'])."\n");
    $input = fopen($state['backup'], 'rb');
    try {
        $process = new Process(['C:/xampp/mysql/bin/mysql.exe', '--defaults-extra-file='.$optionsFile, '--binary-mode', $testDatabase]);
        $process->setInput($input)->setTimeout(180)->mustRun();
    } finally { fclose($input); if (is_file($optionsFile)) unlink($optionsFile); }
    foreach ($state['source_counts'] as $table => $count) {
        if (DB::connection('bs_verify')->table($table)->count() !== $count) throw new RuntimeException('Restore row-count mismatch: '.$table);
    }
    $state['test_database'] = $testDatabase;
    $state['restore_verified'] = true;
    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    config(['database.default' => 'bs_verify']);
    Schema::clearResolvedInstance('db.schema');
    if (Schema::getConnection()->getName() !== 'bs_verify') throw new RuntimeException('Schema connection isolation failed.');
    $exit = \Illuminate\Support\Facades\Artisan::call('migrate', ['--database' => 'bs_verify', '--path' => 'database/migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php', '--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
    if ($exit) exit($exit);
    echo 'Restored and migrated isolated test database: '.$testDatabase.PHP_EOL;
    exit;
}

throw new RuntimeException('Unknown mode. Use inspect, backup, or clone.');
