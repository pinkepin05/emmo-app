<?php
[$app, $directory, $state] = require __DIR__.'/bs-verification-bootstrap.php';
use Illuminate\Support\Facades\DB;

$input = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);
\Carbon\Carbon::setTestNow($input['now'] ?? '2026-09-16 10:00:00');
\Carbon\CarbonImmutable::setTestNow($input['now'] ?? '2026-09-16 10:00:00');
config(['session.driver' => 'array']);
// Exercise the real HTTP kernel, auth, permissions, bindings and atomic middleware.
// The in-process test request has no browser CSRF token; CSRF is covered by browser UAT.
$app['env'] = 'testing';
auth()->loginUsingId($input['user_id']);
DB::listen(function ($query) use ($input) {
    if (str_contains(strtolower($query->sql), 'for update') && !is_file($input['locked'])) {
        file_put_contents($input['locked'], 'locked');
        usleep(($input['hold_ms'] ?? 700) * 1000);
    }
});
file_put_contents($input['ready'], 'ready');
$deadline = microtime(true) + 20;
while (!is_file($input['go'])) { if (microtime(true) > $deadline) throw new RuntimeException('Barrier timeout'); usleep(10000); }
$request = \Illuminate\Http\Request::create($input['url'], $input['method'] ?? 'POST', $input['data'] ?? [], [], [], ['HTTP_ACCEPT' => 'application/json']);
$start = microtime(true);
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
$kernel->terminate($request, $response);
echo json_encode(['status' => $response->getStatusCode(), 'seconds' => round(microtime(true) - $start, 3), 'transaction_level' => DB::transactionLevel(),
    'body' => $response->getStatusCode() >= 400 ? substr($response->getContent(), 0, 1200) : null]).PHP_EOL;
