<?php

require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$directory = storage_path('app/private/bs-readiness');
$state = json_decode(file_get_contents($directory.'/state.json'), true, flags: JSON_THROW_ON_ERROR);
$testDatabase = $state['test_database'] ?? '';
if (!preg_match('/^emmo_bs_verify_\d{8}_\d{6}$/D', $testDatabase)) throw new RuntimeException('Verified isolated database required.');
$original = \Illuminate\Support\Facades\DB::connection();
if ($original->getDatabaseName() === $testDatabase) throw new RuntimeException('Test database must differ from configured application database.');
$config = $original->getConfig();
unset($config['name']);
$config['database'] = $testDatabase;
$config['url'] = null;
config(['database.connections.bs_verify' => $config, 'database.default' => 'bs_verify', 'cache.default' => 'array', 'session.driver' => 'file',
    'session.files' => $directory.'/sessions', 'session.cookie' => 'emmo_bs_verify_session', 'session.domain' => null,
    'app.url' => 'http://127.0.0.1:8099', 'mail.default' => 'array', 'queue.default' => 'sync']);
if (!is_dir($directory.'/sessions')) mkdir($directory.'/sessions', 0700, true);
if (\Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== $testDatabase) throw new RuntimeException('Isolation failed.');
\Illuminate\Support\Facades\Schema::clearResolvedInstance('db.schema');
if (\Illuminate\Support\Facades\DB::connection()->getName() !== 'bs_verify') throw new RuntimeException('Connection name isolation failed.');
return [$app, $directory, $state];
