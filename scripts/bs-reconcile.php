<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\BsCashFlowService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply-snapshots', $argv, true);
$directory = storage_path('app/private/bs-readiness');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$cash = app(BsCashFlowService::class);
$periods = DB::table('periode')->orderBy('periode')->get();
$owners = DB::table('pengajuan_bs')->distinct()->orderBy('user_id')->pluck('user_id');
$rows = []; $issues = []; $mismatches = 0;
foreach ($owners as $owner) {
    $prior = null;
    foreach ($periods as $period) {
        $start = $period->periode.'-01';
        $end = app(\App\Services\PeriodService::class)->next($period->periode).'-01';
        $incoming = Money::minor(DB::table('approval_bs_finance as f')->join('pengajuan_bs as b', 'b.id', '=', 'f.pengajuan_bs_id')->where('b.user_id', $owner)->where('f.tanggal_cair', '>=', $start)->where('f.tanggal_cair', '<', $end)->sum('f.nominal_approved'));
        $outgoing = 0;
        foreach (['transaksi_operasional_area_sales', 'transaksi_perjalanan_dinas'] as $type) {
            $q = DB::table($type.'_header as h')->join($type.'_detail as d', 'd.transaksi_id', '=', 'h.transaksi_id')->join('pengajuan_bs as b', 'b.id', '=', 'h.bs_id')
                ->where('b.user_id', $owner)->where('h.periode', $period->periode)->where('h.status', 'VALIDATED');
            if ($type === 'transaksi_perjalanan_dinas') $q->whereNull('d.deleted_at');
            $outgoing += Money::minor($q->sum('d.nominal'));
        }
        $settled = DB::table('pengajuan_bs')->where('user_id', $owner)->where('status', 'SETTLED')->where('settled_periode', $period->periode);
        $adjustment = Money::minor((clone $settled)->sum('nominal_reimburse')) - Money::minor($settled->sum('nominal_pengembalian'));
        $opening = $prior && $prior['status'] === 'CLOSED' && $prior['next'] === $period->periode ? $prior['ending'] : 0;
        $available = $opening + $incoming - $outgoing + $adjustment;
        $ending = $period->status === 'CLOSED' ? $available : null;
        $calculated = $cash->calculate((int) $owner, $period->periode);
        $expected = ['saldo_awal' => Money::decimal($opening), 'total_masuk' => Money::decimal($incoming), 'total_keluar' => Money::decimal($outgoing), 'penyesuaian' => Money::decimal($adjustment), 'saldo_akhir' => $ending !== null ? Money::decimal($ending) : null];
        $match = !array_diff_assoc($expected, $calculated);
        if (!$match) $mismatches++;
        $legacy = DB::table('cash_flow_snapshots')->where('user_id', $owner)->where('periode', $period->periode)->first();
        $rows[] = ['user_id' => $owner, 'periode' => str_replace('-', '', $period->periode), 'status' => $period->status] + $expected + ['matches_source' => $match, 'legacy_ending' => $legacy?->saldo_akhir, 'legacy_difference' => $ending !== null && $legacy?->saldo_akhir !== null ? Money::decimal($ending - Money::minor($legacy->saldo_akhir)) : null];
        if ($available < 0) $issues[] = ['type' => 'negative_period_balance', 'user_id' => $owner, 'periode' => $period->periode, 'amount' => Money::decimal($available)];
        if ($apply && $match) {
            $snapshot = $cash->recalculateSnapshot((int) $owner, $period->periode);
            foreach ($expected as $key => $value) if ($snapshot->$key !== $value) throw new RuntimeException('Stored snapshot differs from source; manual reconciliation required.');
        }
        $prior = ['status' => $period->status, 'next' => substr($end, 0, 7), 'ending' => $ending];
    }
}
foreach (DB::table('approval_bs_finance')->whereRaw("periode <> DATE_FORMAT(tanggal_cair, '%Y-%m')")->get(['id', 'pengajuan_bs_id', 'periode', 'tanggal_cair']) as $row) $issues[] = ['type' => 'legacy_receipt_period_mismatch', 'record' => $row];
foreach (DB::table('approval_bs_finance')->selectRaw('pengajuan_bs_id, COUNT(*) as total')->groupBy('pengajuan_bs_id')->havingRaw('COUNT(*) > 1')->get() as $row) $issues[] = ['type' => 'duplicate_receipt', 'record' => $row];
foreach (DB::table('pengajuan_bs')->get(['id', 'user_id', 'nominal_approved', 'status']) as $bs) {
    $spent = $cash->spentOnBs($bs->id);
    if ($spent > Money::minor($bs->nominal_approved)) $issues[] = ['type' => 'legacy_bs_overspent', 'bs_id' => $bs->id, 'user_id' => $bs->user_id, 'excess' => Money::decimal($spent - Money::minor($bs->nominal_approved))];
}
foreach (['transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header'] as $table) {
    foreach (DB::table($table.' as h')->join('periode as p', 'p.periode', '=', 'h.periode')->where('p.status', 'CLOSED')->where('h.status', 'DRAFT')->get(['h.id', 'h.periode']) as $row) $issues[] = ['type' => 'legacy_draft_in_closed_period', 'table' => $table, 'record' => $row];
    foreach (DB::table($table)->whereRaw("periode IS NULL OR periode <> DATE_FORMAT(tanggal, '%Y-%m')")->get(['id', 'periode', 'tanggal']) as $row) $issues[] = ['type' => 'legacy_expense_period_mismatch', 'table' => $table, 'record' => $row];
}
$report = ['database' => DB::connection()->getDatabaseName(), 'generated_at' => gmdate(DATE_ATOM), 'snapshots_applied' => $apply, 'source_mismatches' => $mismatches, 'rows' => $rows, 'issues' => $issues];
file_put_contents($directory.'/reconciliation.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$csv = fopen($directory.'/reconciliation.csv', 'w');
if ($rows) { fputcsv($csv, array_keys($rows[0])); foreach ($rows as $row) fputcsv($csv, $row); }
fclose($csv);
$counts = array_count_values(array_column($issues, 'type'));
echo json_encode(['owners' => $owners->count(), 'periods' => $periods->count(), 'rows' => count($rows), 'source_mismatches' => $mismatches, 'snapshots_applied' => $apply, 'issues' => $counts, 'report' => $directory.'/reconciliation.csv'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($mismatches ? 1 : 0);
