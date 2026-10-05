<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\BsActivityLog;
use Illuminate\Http\Request;

class ApprovalAtasanController extends Controller
{
    public function index(Request $request)
    {
        // 🛡️ Proteksi lapis 1: permission fitur (lapis 2 sudah di middleware route)
        abort_unless(auth()->user()->can('approval-atasan'), 403);

        $statuses = [
            'PENDING_ATASAN', 'APPROVED_ATASAN', 'REJECTED_ATASAN',
            'DISBURSED', 'REJECTED_FINANCE', 'SETTLED',
        ];

        $scope = fn($q) => $q->where('atasan_user_id', auth()->id())->whereIn('status', $statuses);

        $bulanExpr = "DATE_FORMAT(tanggal, '%Y-%m')";

        $availablePeriods = $scope(PengajuanBs::query())
            ->selectRaw("$bulanExpr as bulan")
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan')->values();

        $currentPeriod = \App\Support\AppTime::now()->format('Y-m');
        $defaultPeriod = $availablePeriods->contains($currentPeriod)
            ? $currentPeriod
            : ($availablePeriods->first() ?? $currentPeriod);

        $selectedPeriod = $request->query('periode', $defaultPeriod);
        if (!$availablePeriods->contains($selectedPeriod)) {
            $selectedPeriod = $defaultPeriod;
        }

        $data = $scope(PengajuanBs::query())
            ->whereRaw("$bulanExpr = ?", [$selectedPeriod])
            ->with([
                'user', 'atasan',
                'logs' => fn ($q) => $q->whereIn('activity_code', ['APPROVED_ATASAN', 'REJECTED_ATASAN'])->latest(),
            ])
            ->orderByRaw("CASE status
                            WHEN 'PENDING_ATASAN'   THEN 1
                            WHEN 'APPROVED_ATASAN'  THEN 2
                            WHEN 'DISBURSED'        THEN 3
                            WHEN 'SETTLED'          THEN 4
                            WHEN 'REJECTED_ATASAN'  THEN 5
                            WHEN 'REJECTED_FINANCE' THEN 6
                          END")
            ->latest()
            ->get();

        return view('approval-atasan.index', compact('data', 'availablePeriods', 'selectedPeriod'));
    }

    public function detail(PengajuanBs $bs)
    {
       abort_unless(auth()->user()->can('approval-atasan'), 403);

        // 🛡️ Proteksi lapis 2: KONTEKSTUAL — hanya atasan langsung (sesuai note 4-5 dokumen)
        if ($bs->atasan_user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $bs->load(['user', 'atasan', 'members']);

        $logs = BsActivityLog::where('pengajuan_bs_id', $bs->id)
                             ->with('actor')
                             ->orderBy('created_at', 'asc')
                             ->get();

        return view('approval-atasan.detail', compact('bs', 'logs'));
    }

    public function approve(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('approval-atasan'), 403);

        if ($bs->atasan_user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak menyetujui pengajuan ini.');
        }

        if ($bs->status !== 'PENDING_ATASAN') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate(['approval_message' => 'nullable|string|max:1000']);
        $message = $request->approval_message ?: null;

        $bs->update([
            'status'           => 'APPROVED_ATASAN',
            'approval_message' => $message,
        ]);

        $logDesc = 'Pengajuan disetujui oleh ' . auth()->user()->nama . ' dan diteruskan ke Finance.';
        if ($message) $logDesc .= ' Pesan: ' . $message;

        BsActivityLog::record($bs->id, 'APPROVED_ATASAN', 'Disetujui Atasan', $logDesc);

        \App\Services\BsNotifications::afterCommit(fn () => \App\Models\User::permission('akses.approval-finance')
            ->get()
            ->each(fn($u) => $u->notify(new \App\Notifications\BsApproved($bs))));

        return back()->with('success', 'BS berhasil disetujui.' . ($message ? ' Pesan Anda akan diteruskan ke pemohon.' : ''));
    }

    public function reject(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('approval-atasan'), 403);

        if ($bs->atasan_user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak menolak pengajuan ini.');
        }

        if ($bs->status !== 'PENDING_ATASAN') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $bs->update([
            'status'           => 'REJECTED_ATASAN',
            'rejection_reason' => $request->rejection_reason,
        ]);

        BsActivityLog::record(
            $bs->id,
            'REJECTED_ATASAN',
            'Ditolak Atasan',
            'Pengajuan ditolak oleh ' . auth()->user()->nama . ' dengan alasan: ' . $request->rejection_reason
        );

        \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsRejected($bs)));

        return back()->with('success', 'BS berhasil ditolak.');
    }
}
