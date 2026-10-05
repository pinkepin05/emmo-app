<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\BsCashFlowSnapshot;
use App\Models\CashFlowSnapshot;
use App\Models\StockCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsPeriodClosingService
{
    public function __construct(private PeriodService $periods, private BsCashFlowService $cash) {}

    public function close(int $id): void
    {
        DB::transaction(function () use ($id) {
            $records = Periode::orderBy('periode')->lockForUpdate()->get();
            $period = $records->firstWhere('id', $id);
            abort_unless($period, 404);
            $this->require($period->status === 'OPEN', 'Periode sudah ditutup sebelumnya.');
            $this->require($period->periode < $this->periods->current(), 'Periode baru dapat ditutup setelah bulannya berakhir.');
            $prior = $records->where('periode', '<', $period->periode);
            $this->require(!$prior->contains('status', 'OPEN'), 'Tutup periode sebelumnya terlebih dahulu.');
            $this->require($prior->isEmpty() || $prior->last()->periode === $this->periods->previous($period->periode), 'Ada bulan yang belum tercatat di antara periode. Lengkapi urutan periode terlebih dahulu.');
            $this->require(!$records->where('periode', '>', $period->periode)->contains('status', 'CLOSED'), 'Periode berikutnya sudah ditutup; lakukan rekonsiliasi urutan tutup buku.');
            foreach ([TransaksiOperasionalHeader::class, TransaksiPerjalananDinasHeader::class] as $model) {
                $this->require(!$model::where('periode', $period->periode)->whereIn('status', ['DRAFT', 'PENDING_OWNER'])->exists(), 'Masih ada transaksi draft atau menunggu persetujuan pemohon pada periode ini. Selesaikan transaksi sebelum tutup buku.');
            }

            $this->cash->ensureSnapshotForPeriod($period->periode);
            $this->require(!BsCashFlowSnapshot::where('periode', $period->periode)->where('opening_final', false)->exists(), 'Saldo awal belum final; periksa penutupan periode sebelumnya.');
            $stock = app(StockCashFlowService::class);
            $stock->ensureSnapshotForPeriod($period->periode);
            $this->require(!StockCashFlowSnapshot::where('periode', $period->periode)->where('opening_final', false)->exists(), 'Saldo awal Stok Kas belum final; periksa penutupan periode sebelumnya.');
            $period->update(['status' => 'CLOSED', 'closed_at' => now(), 'closed_by' => auth()->id()]);
            $this->cash->ensureSnapshotForPeriod($period->periode);
            $stock->ensureSnapshotForPeriod($period->periode);
            // Maintain the existing non-BS summary without changing its source rules.
            app(CashFlowService::class)->ensureSnapshotForPeriod($period->periode);

            $next = $this->periods->next($period->periode);
            Periode::firstOrCreate(['periode' => $next], ['status' => 'OPEN']);
            $this->cash->ensureSnapshotForPeriod($next);
            $stock->ensureSnapshotForPeriod($next);
            foreach (CashFlowSnapshot::where('periode', $period->periode)->pluck('user_id') as $owner) {
                app(CashFlowService::class)->recalculateSnapshot((int) $owner, $next);
            }
        }, 3);
    }

    public function reopen(int $id, string $reason): void
    {
        DB::transaction(function () use ($id, $reason) {
            $records = Periode::orderBy('periode')->lockForUpdate()->get();
            $period = $records->firstWhere('id', $id);
            abort_unless($period, 404);
            $this->require($period->status === 'CLOSED', 'Hanya periode CLOSED yang dapat dibuka kembali.');
            $next = $this->periods->next($period->periode);
            $this->require(!$records->where('periode', '>', $period->periode)->contains('status', 'CLOSED'), 'Periode berikutnya sudah ditutup.');
            $this->require(!ApprovalBsFinance::where('tanggal_cair', '>=', $next.'-01')->exists(), 'Periode berikutnya sudah memiliki pencairan.');
            $this->require(!PengajuanBs::where('settled_periode', '>=', $next)->exists(), 'Periode berikutnya sudah memiliki penyelesaian BS.');
            // Preserve checks on shared-period consumers outside the BS workstream.
            foreach (['pengajuan_bs', 'transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header', 'perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'] as $table) {
                $this->require(!DB::table($table)->where('tanggal', '>=', $next.'-01')->exists(), 'Periode berikutnya sudah memiliki transaksi.');
            }
            $period->update(['status' => 'OPEN', 'reopened_at' => now(), 'reopen_reason' => $reason]);
            $this->cash->ensureSnapshotForPeriod($period->periode);
            $stock = app(StockCashFlowService::class);
            $stock->ensureSnapshotForPeriod($period->periode);
            $stock->ensureSnapshotForPeriod($next);
            foreach (CashFlowSnapshot::where('periode', $period->periode)->pluck('user_id') as $owner) {
                app(CashFlowService::class)->recalculateSnapshot((int) $owner, $period->periode);
            }
            $this->cash->ensureSnapshotForPeriod($next);
            foreach (CashFlowSnapshot::where('periode', $next)->pluck('user_id') as $owner) {
                app(CashFlowService::class)->recalculateSnapshot((int) $owner, $next);
            }
        }, 3);
    }

    private function require(bool $condition, string $message): void
    {
        if (!$condition) throw ValidationException::withMessages(['error' => $message]);
    }
}
