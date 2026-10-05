<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use Illuminate\Http\Request;

class LaporanBsController extends Controller
{
    private function query(Request $request)
    {
        abort_unless(auth()->user()->canSeeAllTransactions(), 403);
        $request->validate(['periode' => 'nullable|string', 'status' => 'nullable|string']);
        return PengajuanBs::with('user')
            ->when($request->filled('periode'), fn ($q) => $q->where('periode', app(\App\Services\PeriodService::class)->normalize($request->periode)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('periode')->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $data = $this->query($request)->get();
        return view('laporan-bs.index', compact('data'));
    }

    public function settle(PengajuanBs $bs)
    {
        abort(403, 'Settlement per pengajuan tidak berlaku pada saldo BS gabungan.');
    }

    public function export(Request $request)
    {
        $headings = ['Doc No', 'User', 'Periode', 'Jenis Pengajuan', 'Nominal Pengajuan', 'Nominal Cair', 'Pengembalian Historis', 'Reimburse Historis', 'Status'];
        $rows = $this->query($request)->get()->map(fn ($bs) => [
            $bs->document_no, $bs->user?->nama ?? '', $bs->periode, $bs->tipe_label,
            (float) $bs->nominal_pengajuan, (float) $bs->nominal_approved,
            (float) $bs->nominal_pengembalian, (float) $bs->nominal_reimburse, $bs->status,
        ])->all();
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['E', 'F', 'G', 'H']),
            'laporan-pengajuan-bs-'.($request->periode ?: 'semua-periode').'.xlsx'
        );
    }
}
