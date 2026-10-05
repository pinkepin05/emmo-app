<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\ApprovalBsFinance;
use App\Models\BsActivityLog;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalFinanceController extends Controller
{
    public function index(Request $request)
    {
        // 🛡️ Validasi divisi Finance secara DINAMIS (tidak hardcode code role)
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

    public function disburse(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        if ($bs->status !== 'APPROVED_ATASAN') {
            return back()->with('error', 'Pengajuan ini tidak bisa dicairkan (status bukan APPROVED_ATASAN).');
        }

        $request->validate([
            'nominal_approved' => 'required|numeric|decimal:0,2|min:1|max:' . $bs->nominal_pengajuan
        ]);

        DB::transaction(function () use ($request, $bs) {
            $tanggal = \App\Support\AppTime::now()->toDateString();
            $postingPeriod = app(\App\Services\PeriodService::class)->current();
            app(\App\Services\PeriodService::class)->lockOpen($postingPeriod);
            if (ApprovalBsFinance::where('pengajuan_bs_id', $bs->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['error' => 'BS ini sudah pernah dicairkan.']);
            }

            $docNo = app(DocumentNumberService::class)->next('CAIR', $tanggal);

            ApprovalBsFinance::create([
                'document_no'          => $docNo,
                'pengajuan_bs_id'      => $bs->id,
                'tanggal_cair'         => $tanggal,
                'finance_user_id'      => auth()->id(),
                'nominal_pengajuan_bs' => $bs->nominal_pengajuan,
                'nominal_sisa_bs'      => $bs->nominal_sisa_bs_snapshot,
                'nominal_approved'     => $request->nominal_approved,
                'periode'              => $postingPeriod,
            ]);

            $bs->update([
                'nominal_approved' => $request->nominal_approved,
                'status'           => 'DISBURSED',
            ]);

            BsActivityLog::record(
                $bs->id,
                'DISBURSED',
                'Dicairkan oleh Finance',
                'Pencairan uang telah dikonfirmasi oleh Finance.',
                $request->nominal_approved
            );

            \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsDisbursed($bs, $request->nominal_approved)));

            app(\App\Services\BsCashFlowService::class)->recalculateSnapshot((int) $bs->user_id, $postingPeriod);
            app(\App\Services\StockCashFlowService::class)->recalculateSnapshot((int) $bs->user_id, $postingPeriod);
        });

        return back()->with('success', 'BS berhasil dicairkan.');
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
}
