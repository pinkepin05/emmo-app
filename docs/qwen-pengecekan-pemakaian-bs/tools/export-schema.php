<?php

// Read-only schema metadata. No transaction rows or connection credentials.
$root = $argv[1] ?? null;
if (!$root || !is_file($root.'/vendor/autoload.php')) {
    fwrite(STDERR, "Usage: php export-schema.php <application-root>\n");
    exit(1);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'user', 'roles', 'company', 'level', 'position', 'wilayah', 'cost_category', 'periode',
    'transaksi_operasional_area_sales_header', 'transaksi_operasional_area_sales_detail',
    'transaksi_perjalanan_dinas_header', 'transaksi_perjalanan_dinas_detail',
    'pengajuan_bs', 'pengajuan_bs_members', 'approval_bs_finance',
    'document_number_sequences', 'bs_usage_logs', 'bs_usage_approval_logs',
    'bs_activity_logs', 'bs_cash_flow_snapshots', 'stock_cash_flow_snapshots',
    'cash_flow_snapshots', 'reimbursement_claims',
    'spatie_permissions', 'spatie_roles', 'spatie_role_has_permissions',
    'spatie_model_has_permissions', 'spatie_model_has_roles',
];

try {
    $result = [
        'generated_at_utc' => gmdate('c'),
        'driver' => \Illuminate\Support\Facades\DB::connection()->getDriverName(),
        'scope' => 'Schema metadata only; no business rows, connection settings, or credentials.',
        'tables' => [],
    ];
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        $foreignKeys = $exists ? \Illuminate\Support\Facades\Schema::getForeignKeys($table) : [];
        foreach ($foreignKeys as &$foreignKey) {
            unset($foreignKey['foreign_schema']);
        }
        unset($foreignKey);
        $result['tables'][$table] = $exists ? [
            'exists' => true,
            'columns' => \Illuminate\Support\Facades\Schema::getColumns($table),
            'indexes' => \Illuminate\Support\Facades\Schema::getIndexes($table),
            'foreign_keys' => $foreignKeys,
        ] : ['exists' => false];
    }
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    // Avoid echoing connection details embedded in database exception messages.
    fwrite(STDERR, 'Schema export failed ('.get_class($exception)."). No data exported.\n");
    exit(1);
}
