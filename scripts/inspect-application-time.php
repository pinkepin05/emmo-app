<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$result = [
    'storage_timezone' => config('app.timezone'),
    'display_timezone' => config('app.display_timezone', 'Asia/Jakarta'),
    'server_utc' => now('UTC')->toIso8601String(),
    'server_local' => now(config('app.display_timezone', 'Asia/Jakarta'))->toIso8601String(),
    'database_clock' => DB::selectOne('SELECT NOW() AS server_now, UTC_TIMESTAMP() AS utc_now, @@session.time_zone AS session_timezone'),
];
foreach (['pengajuan_bs', 'approval_bs_finance', 'bs_activity_logs', 'transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header'] as $table) {
    $columns = array_values(array_filter(Schema::getColumns($table), fn ($column) => in_array($column['name'], ['tanggal', 'tanggal_cair', 'created_at', 'updated_at', 'validated_at'])));
    $result['tables'][$table] = [
        'columns' => array_map(fn ($column) => ['name' => $column['name'], 'type' => $column['type']], $columns),
        'recent_times' => DB::table($table)->orderByDesc('id')->limit(3)->get(array_merge(['id'], array_column($columns, 'name'))),
    ];
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
