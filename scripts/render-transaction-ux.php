<?php

// Render actual pages for local visual inspection; roll back automatic dashboard snapshot writes.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\TransaksiOperasionalHeader;
use App\Models\PerongkosanEventDalamKota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

config(['session.driver' => 'array', 'cache.default' => 'array']);
$app['env'] = 'testing';
auth()->setUser(User::permission('akses.approval-finance')->firstOrFail());
$directory = storage_path('app/private/transaction-ux-preview');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$pages = [
    'dashboard' => route('dashboard.transaksi', ['pemakaian_periode' => '']),
    'pengajuan' => route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_periode' => '']),
    'finance' => route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_periode' => '']),
    'empty' => route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_periode' => '2099-01']),
    'lookup' => route('bs-usage-checks.index'),
];
$source = TransaksiOperasionalHeader::has('details')->firstOrFail();
$pages['check-form'] = route('bs-usage-checks.create', ['OPR', $source->id]);
$pages['detail'] = route('transaction-recap.show', ['jenis' => 'OPR', 'source' => $source->id,
    'dari' => 'laporan', 'pemakaian_periode' => '', 'pemakaian_jenis' => 'OPR', 'tampilan' => 'finance', 'pemakaian_page' => 2]);
$event = PerongkosanEventDalamKota::first();
if ($event) $pages['event'] = route('transaction-recap.show', ['EDK', $event->id]);
DB::beginTransaction();
try {
    foreach ($pages as $name => $url) {
        if ($name === 'event') auth()->setUser($event->user);
        $response = $kernel->handle(Request::create($url, 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']));
        if ($response->getStatusCode() !== 200) throw new RuntimeException('Cannot render '.$name.': HTTP '.$response->getStatusCode());
        file_put_contents($directory.'/'.$name.'.html', $response->getContent());
        echo 'Rendered '.$name.PHP_EOL;
    }
} finally {
    DB::rollBack();
}
file_put_contents($directory.'/pages.json', json_encode(array_keys($pages), JSON_THROW_ON_ERROR));
echo "Local previews ready; automatic database writes rolled back.\n";
