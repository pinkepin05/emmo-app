<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\ApprovalBsFinance;
use App\Models\BsActivityLog;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalFinanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        $financeStatuses = ['APPROVED_ATASAN', 'DISBURSED', 'REJECTED_FINANCE'];
        $bulanExpr = "DATE_FORMAT(tanggal, '%Y-%m')";

        $availablePeriods = PengajuanBs::whereIn('status', $financeStatuses)
            ->selectRaw("$bulanExpr as bulan")
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan')->values();

        $currentPeriod = app(\App\Services\PeriodService::class)->current();
        $defaultPeriod = $availablePeriods->contains($currentPeriod)
            ? $currentPeriod
            : ($availablePeriods->first() ?? $currentPeriod);

        $selectedPeriod = $request->query('periode', $defaultPeriod);
        if (!$availablePeriods->contains($selectedPeriod)) {
            $selectedPeriod = $defaultPeriod;
        }

        $data = PengajuanBs::whereIn('status', $financeStatuses)
            ->whereRaw("$bulanExpr = ?", [$selectedPeriod])
            ->with([
                'user',
                'atasan',
                'logs' => fn ($q) => $q->where('activity_code', 'REJECTED_FINANCE')->latest(),
                'financeApprovals' => fn($q) => $q->with('financeUser')->latest(),
            ])
            ->orderByRaw("CASE status
                            WHEN 'APPROVED_ATASAN'  THEN 1
                            WHEN 'DISBURSED'        THEN 2
                            WHEN 'REJECTED_FINANCE' THEN 3
                          END")
            ->latest()
            ->get();

        return view('approval-finance.index', compact('data', 'availablePeriods', 'selectedPeriod'));
    }

    /**
     * Pencairan tunggal (dari form manual per card).
     */
    public function disburse(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        if ($bs->status !== 'APPROVED_ATASAN') {
            return back()->with('error', 'Pengajuan ini tidak bisa dicairkan (status bukan APPROVED_ATASAN).');
        }

        $request->validate([
            'nominal_approved' => 'required|numeric|decimal:0,2|min:1|max:' . $bs->nominal_pengajuan
        ]);

        try {
            DB::transaction(function () use ($request, $bs) {
                $this->processDisburse($bs, (int) $request->nominal_approved);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mencairkan BS: ' . $e->getMessage());
        }

        return back()->with('success', 'BS berhasil dicairkan.');
    }

    /**
     * 🎯 Pencairan BATCH (beberapa pengajuan sekaligus, nominal penuh).
     */
    public function batchDisburse(Request $request)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        $validated = $request->validate([
            'ids'   => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:pengajuan_bs,id',
        ]);

        $ids = $validated['ids'];

        $pengajuans = PengajuanBs::whereIn('id', $ids)
            ->where('status', 'APPROVED_ATASAN')
            ->get()
            ->keyBy('id');

        if ($pengajuans->isEmpty()) {
            return redirect()->route('approval-finance.index')
                ->with('error', 'Tidak ada pengajuan valid yang dapat dicairkan. Muat ulang halaman.');
        }

        $successCount = 0;
        $successDocs  = [];
        $failedDocs   = [];

        // Proses granular: setiap item dalam transaksi sendiri.
        // Yang berhasil tetap tersimpan, yang gagal di-skip dengan alasan.
        foreach ($ids as $id) {
            $bs = $pengajuans->get($id);
            if (!$bs) {
                $failedDocs[] = "ID#{$id} (sudah diproses atau tidak valid)";
                continue;
            }

            try {
                DB::transaction(function () use ($bs) {
                    $this->processDisburse($bs, (int) $bs->nominal_pengajuan);
                });
                $successCount++;
                $successDocs[] = $bs->document_no;
            } catch (\Throwable $e) {
                $failedDocs[] = $bs->document_no . ': ' . $e->getMessage();
            }
        }

        // Flash message berdasarkan hasil
        if ($successCount === 0) {
            return redirect()->route('approval-finance.index')
                ->with('error', '❌ Semua pengajuan gagal dicairkan. ' . implode(' | ', $failedDocs));
        }

        if (!empty($failedDocs)) {
            return redirect()->route('approval-finance.index')
                ->with('warning', "⚠️ Berhasil: {$successCount}. Gagal: " . implode(' | ', $failedDocs));
        }

        return redirect()->route('approval-finance.index')
            ->with('success', "✅ {$successCount} pengajuan berhasil dicairkan dengan nominal penuh.");
    }

    public function reject(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        if ($bs->status !== 'APPROVED_ATASAN') {
            return back()->with('error', 'Pengajuan ini tidak bisa ditolak (sudah diproses sebelumnya).');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $bs) {
            $bs->update([
                'status'           => 'REJECTED_FINANCE',
                'rejection_reason' => $request->rejection_reason,
            ]);

            BsActivityLog::record(
                $bs->id,
                'REJECTED_FINANCE',
                'Ditolak Finance',
                'Pencairan ditolak oleh ' . auth()->user()->nama . ' dengan alasan: ' . $request->rejection_reason
            );

            \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsRejected($bs)));
            \App\Services\BsNotifications::afterCommit(fn () => $bs->atasan?->notify(new \App\Notifications\BsRejected($bs)));
        });

        return back()->with('success', 'Pencairan BS berhasil ditolak.');
    }

    public function detail(PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        $bs->load([
            'user',
            'atasan',
            'members',
            'financeApprovals' => fn ($q) => $q->with('financeUser')->latest(),
        ]);

        $logs = BsActivityLog::where('pengajuan_bs_id', $bs->id)
            ->with('actor')
            ->latest()
            ->get();

        return view('approval-finance.detail', compact('bs', 'logs'));
    }

    /**
     * 🎯 LOGIKA INTI PENCAIRAN BS.
     * Dipanggil oleh disburse() tunggal maupun batchDisburse().
     * HARUS dijalankan di dalam DB::transaction.
     *
     * @param PengajuanBs $bs       Pengajuan yang akan dicairkan
     * @param int         $nominal  Nominal yang disetujui Finance
     */
    private function processDisburse(PengajuanBs $bs, int $nominal): void
    {
        if ($bs->status !== 'APPROVED_ATASAN') {
            throw new \RuntimeException('Pengajuan tidak bisa dicairkan (status bukan APPROVED_ATASAN).');
        }

        if ($nominal < 1 || $nominal > (int) $bs->nominal_pengajuan) {
            throw new \RuntimeException('Nominal tidak valid.');
        }

        $tanggal = \App\Support\AppTime::now()->toDateString();
        $postingPeriod = app(\App\Services\PeriodService::class)->current();
        app(\App\Services\PeriodService::class)->lockOpen($postingPeriod);

        if (ApprovalBsFinance::where('pengajuan_bs_id', $bs->id)->exists()) {
            throw new \RuntimeException('BS ini sudah pernah dicairkan.');
        }

        $docNo = app(DocumentNumberService::class)->next('CAIR', $tanggal);

        ApprovalBsFinance::create([
            'document_no'          => $docNo,
            'pengajuan_bs_id'      => $bs->id,
            'tanggal_cair'         => $tanggal,
            'finance_user_id'      => auth()->id(),
            'nominal_pengajuan_bs' => $bs->nominal_pengajuan,
            'nominal_sisa_bs'      => $bs->nominal_sisa_bs_snapshot,
            'nominal_approved'     => $nominal,
            'periode'              => $postingPeriod,
        ]);

        $bs->update([
            'nominal_approved' => $nominal,
            'status'           => 'DISBURSED',
        ]);

        BsActivityLog::record(
            $bs->id,
            'DISBURSED',
            'Dicairkan oleh Finance',
            'Pencairan uang telah dikonfirmasi oleh Finance.',
            $nominal
        );

        \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsDisbursed($bs, $nominal)));

        app(\App\Services\BsCashFlowService::class)->recalculateSnapshot((int) $bs->user_id, $postingPeriod);
        app(\App\Services\StockCashFlowService::class)->recalculateSnapshot((int) $bs->user_id, $postingPeriod);
    }
}