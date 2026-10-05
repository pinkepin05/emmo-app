<?php

require __DIR__.'/bs-verification-bootstrap.php';

use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;

$number = DB::transaction(function () {
    // Establish a repeatable-read snapshot before competing for the sequence.
    DB::table('pengajuan_bs')->count();
    $number = app(DocumentNumberService::class)->next('BS', '2099-12-31');
    usleep(200000);

    return $number;
}, 3);
echo $number.PHP_EOL;
