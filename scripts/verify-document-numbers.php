<?php

require __DIR__.'/bs-verification-bootstrap.php';

use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

$migration = 'database/migrations/2026_09_17_100000_restore_readable_document_numbers.php';
if (Artisan::call('migrate', ['--path' => $migration, '--force' => true])) {
    throw new RuntimeException(Artisan::output());
}
echo Artisan::output();
foreach (['transaksi_operasional_area_sales', 'transaksi_perjalanan_dinas'] as $base) {
    $keys = Schema::getForeignKeys($base.'_detail');
    foreach ($keys as $key) {
        if (in_array('transaksi_id', $key['columns'], true) && $key['on_update'] !== 'cascade') {
            throw new RuntimeException('Expected original cascading foreign key.');
        }
    }
}

DB::beginTransaction();
try {
    $header = DB::table('transaksi_operasional_area_sales_header')->first();
    $detail = DB::table('transaksi_operasional_area_sales_detail')->where('transaksi_id', $header->transaksi_id)->first();
    DB::table('document_number_sequences')->insert(['type' => 'OAS', 'date' => '2099-12-30', 'last_number' => 99]);
    $number = app(DocumentNumberService::class)->next('OAS', '2099-12-30');
    if ($number !== 'OAS991230100') throw new RuntimeException('Sequence overflow failed.');
    DB::table('transaksi_operasional_area_sales_header')->where('id', $header->id)->update(['transaksi_id' => $number]);
    if (DB::table('transaksi_operasional_area_sales_detail')->where('id', $detail->id)->value('transaksi_id') !== $number) {
        throw new RuntimeException('Foreign-key cascade failed.');
    }
    echo "MySQL: suffix 100 and detail cascade passed.\n";

    // Exercise preview and correction against real MySQL foreign keys.
    $bs = DB::table('pengajuan_bs')->first();
    DB::table('pengajuan_bs')->where('id', $bs->id)->update(['document_no' => 'BS-01M2PG9NE6ZWTJYZTSW18HN5NW']);
    DB::table('transaksi_operasional_area_sales_header')->where('id', $header->id)->update(['transaksi_id' => 'OASXXT5VHYO']);
    Artisan::call('bs:repair-document-numbers');
    if (DB::table('pengajuan_bs')->where('id', $bs->id)->value('document_no') !== 'BS-01M2PG9NE6ZWTJYZTSW18HN5NW') {
        throw new RuntimeException('Preview changed the database.');
    }
    config(['filesystems.disks.local.root' => storage_path('app/private/document-number-verification')]);
    Artisan::call('bs:repair-document-numbers', ['--apply' => true]);
    $new = DB::table('transaksi_operasional_area_sales_header')->where('id', $header->id)->value('transaksi_id');
    if ($new === 'OASXXT5VHYO' || DB::table('transaksi_operasional_area_sales_detail')->where('id', $detail->id)->value('transaksi_id') !== $new) {
        throw new RuntimeException('Repair lost a detail reference.');
    }
    echo "MySQL: repair preview, apply, and detail references passed.\n";
} finally {
    DB::rollBack();
}

DB::table('document_number_sequences')->where(['type' => 'BS', 'date' => '2099-12-31'])->delete();
$processes = [];
for ($i = 0; $i < 6; $i++) {
    $process = new Process([PHP_BINARY, __DIR__.'/document-number-worker.php'], base_path());
    $process->setTimeout(30)->start();
    $processes[] = $process;
}
$numbers = [];
foreach ($processes as $process) {
    $process->wait();
    if (!$process->isSuccessful()) throw new RuntimeException($process->getErrorOutput().$process->getOutput());
    $numbers[] = trim($process->getOutput());
}
foreach ($numbers as $number) {
    if (!preg_match('/^BS-20991231-[0-9]{3,}$/D', $number)) throw new RuntimeException('Invalid document number: '.$number);
}
if (count(array_unique($numbers)) !== 6) throw new RuntimeException('Concurrent allocation produced duplicate numbers: '.json_encode($numbers));
sort($numbers);
echo 'MySQL: six concurrent allocations passed: '.implode(', ', $numbers).PHP_EOL;
