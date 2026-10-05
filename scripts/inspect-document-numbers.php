<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'pengajuan_bs' => ['document_no', 'tanggal'],
    'approval_bs_finance' => ['document_no', 'tanggal_cair'],
    'transaksi_operasional_area_sales_header' => ['transaksi_id', 'tanggal'],
    'transaksi_perjalanan_dinas_header' => ['transaksi_id', 'tanggal'],
];
$result = ['database' => DB::connection()->getDatabaseName(), 'tables' => []];
foreach ($tables as $table => [$column, $date]) {
    $result['tables'][$table] = [
        'numbers' => DB::table($table)->orderBy('id')->get(['id', $column, $date]),
        'number_column' => array_values(array_filter(Schema::getColumns($table), fn ($c) => $c['name'] === $column)),
        'foreign_keys' => Schema::getForeignKeys($table),
    ];
}
foreach (['transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_detail'] as $table) {
    $result['tables'][$table] = ['foreign_keys' => Schema::getForeignKeys($table),
        'number_column' => array_values(array_filter(Schema::getColumns($table), fn ($c) => $c['name'] === 'transaksi_id'))];
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
