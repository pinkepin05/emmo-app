<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\BsCashFlowSnapshot;
use App\Models\CashFlowSnapshot;
use App\Models\StockCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\PeriodClosingLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsPeriodClosingService
{
    public function __construct(private PeriodService $periods, private BsCashFlowService $cash) {}

    public function close(int $id, bool $excludePending = false, ?string $reason = null, ?string $reviewToken = null): void
    {
        if ($excludePending) {
            abort_unless(auth()->user()?->can('akses.master-data'), 403);
            $reason = trim($reason ?? '');
            validator(['reason' => $reason, 'review_token' => $reviewToken], [
                'reason' => 'required|string|min:5|max:1000',
                'review_token' => 'required|string|size:64',
            ])->validate();
        }
        DB::transaction(function () use ($id, $excludePending, $reason, $reviewToken) {
            $records = Periode::orderBy('periode')->lockForUpdate()->get();
            $period = $records->firstWhere('id', $id);
            abort_unless($period, 404);
            $this->require($period->status === 'OPEN', 'Periode sudah ditutup sebelumnya.');
            $this->require($period->periode < $this->periods->current(), 'Periode baru dapat ditutup setelah bulannya berakhir.');
            $prior = $records->where('periode', '<', $period->periode);
            $this->require(!$prior->contains('status', 'OPEN'), 'Tutup periode sebelumnya terlebih dahulu.');
            $this->require($prior->isEmpty() || $prior->last()->periode === $this->periods->previous($period->periode), 'Ada bulan yang belum tercatat di antara periode. Lengkapi urutan periode terlebih dahulu.');
            $this->require(!$records->where('periode', '>', $period->periode)->contains('status', 'CLOSED'), 'Periode berikutnya sudah ditutup; lakukan rekonsiliasi urutan tutup buku.');
            $review = app(PeriodClosingReviewService::class);
            $blockers = $review->blockers($period->periode);
            if ($excludePending) {
                $this->require(hash_equals($review->token($period->periode, $blockers), $reviewToken),
                    'Daftar transaksi tertunda berubah. Muat ulang halaman periode dan periksa kembali sebelum menutup buku.');
            } else {
                $this->require(!$blockers->whereIn('stage', ['DRAFT', 'PENDING_OWNER'])->count(),
                    'Masih ada transaksi draft atau menunggu persetujuan pemohon pada periode ini. Selesaikan transaksi atau gunakan Tutup dengan Pengecualian pada halaman periode.');
                $this->require($blockers->isEmpty(),
                    'Masih ada transaksi atau event yang belum selesai dikonfirmasi Finance. Selesaikan pengecekan Finance atau gunakan Tutup dengan Pengecualian pada halaman periode.');
            }

            $this->cash->ensureSnapshotForPeriod($period->periode);
            $this->require(!BsCashFlowSnapshot::where('periode', $period->periode)->where('opening_final', false)->exists(), 'Saldo awal belum final; periksa penutupan periode sebelumnya.');
            $stock = app(StockCashFlowService::class);
            $stock->ensureSnapshotForPeriod($period->periode);
            $this->require(!StockCashFlowSnapshot::where('periode', $period->periode)->where('opening_final', false)->exists(), 'Saldo awal Stok Kas belum final; periksa penutupan periode sebelumnya.');
            $period->update(['status' => 'CLOSED', 'closed_at' => now(), 'closed_by' => auth()->id()]);
            PeriodClosingLog::create([
                'periode' => $period->periode,
                'mode' => $excludePending ? 'EXCLUDE_PENDING' : 'STANDARD',
                'actor_id' => auth()->id(), 'actor_name' => auth()->user()?->nama,
                'reason' => $excludePending ? $reason : null,
                'excluded_transactions' => $blockers->values()->all(), 'created_at' => now(),
            ]);
            app(BsApprovalPeriodService::class)->rejectClosedPeriods(periode: $period->periode);
            $this->cash->ensureSnapshotForPeriod($period->periode);
            $stock->ensureSnapshotForPeriod($period->periode);
            // The legacy summary also respects the explicitly excluded transactions.
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
