<?php
// One-time reconciliation for the explicitly confirmed trial dataset.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\BsCashFlowSnapshot;

$directory = storage_path('app/private/bs-readiness');
$state = json_decode(file_get_contents($directory.'/state.json'), true, flags: JSON_THROW_ON_ERROR);
if (DB::connection()->getDatabaseName() !== $state['source_database'] || !($state['restore_verified'] ?? false) || !is_file($state['backup'])) throw new RuntimeException('Verified source backup required.');
$changes = [];
foreach (['approval_bs_finance' => 'tanggal_cair', 'transaksi_operasional_area_sales_header' => 'tanggal', 'transaksi_perjalanan_dinas_header' => 'tanggal'] as $table => $date) {
    foreach (DB::table($table)->whereRaw("periode IS NULL OR periode <> DATE_FORMAT({$date}, '%Y-%m')")->get(['id', 'periode', $date]) as $row) {
        $target = substr($row->$date, 0, 7);
        if (!DB::table('periode')->where('periode', $target)->exists()) throw new RuntimeException('Missing target period: '.$target);
        $changes[] = ['table' => $table, 'id' => $row->id, 'from' => $row->periode, 'to' => $target];
    }
}
if (!in_array('--apply', $argv, true)) { echo json_encode($changes, JSON_PRETTY_PRINT).PHP_EOL; exit; }
$auditFile = $directory.'/period-alignment-'.gmdate('Ymd_His').'.json';
$audit = ['database' => $state['source_database'], 'backup' => $state['backup'], 'changes' => $changes, 'snapshots_before' => BsCashFlowSnapshot::orderBy('user_id')->orderBy('periode')->get()->toArray()];
file_put_contents($auditFile, json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
DB::transaction(function () use ($changes) {
    $periods = \App\Models\Periode::orderBy('periode')->lockForUpdate()->get();
    $cash = app(\App\Services\BsCashFlowService::class);
    foreach ($cash->owners() as $owner) DB::table('user')->where('id', $owner)->lockForUpdate()->first();
    foreach ($changes as $change) {
        $affected = DB::table($change['table'])->where('id', $change['id'])->where('periode', $change['from'])->update(['periode' => $change['to']]);
        if ($affected !== 1) throw new RuntimeException('Data changed during reconciliation.');
    }
    // Explicit maintenance rebuild of the new BS-only summaries, with before-image.
    // Source dates, amounts, statuses and legacy mixed snapshots remain unchanged.
    foreach ($periods as $period) foreach ($cash->owners() as $owner) {
        BsCashFlowSnapshot::updateOrCreate(['user_id' => $owner, 'periode' => $period->periode], $cash->calculate((int) $owner, $period->periode) + ['last_updated' => now()]);
    }
});
$audit['completed_at'] = gmdate(DATE_ATOM);
file_put_contents($auditFile, json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo 'Aligned '.count($changes).' period fields with their original dates; audit: '.$auditFile.PHP_EOL;
