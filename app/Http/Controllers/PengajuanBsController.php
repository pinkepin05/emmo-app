<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\User;
use App\Models\BsActivityLog;
use App\Services\BonSementaraService;
use App\Services\DocumentNumberService;
use App\Services\BsAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanBsController extends Controller
{
    public function index()
    {
        $data = PengajuanBs::where('user_id', auth()->id())
            ->with(['logs' => fn ($q) => $q->where('activity_code', 'REJECTED_SYSTEM')])->latest()->get();
        return view('pengajuan-bs.index', compact('data'));
    }

    public function create(BonSementaraService $bsService)
    {
        $periode   = app(\App\Services\PeriodService::class)->current();
        $sisaSaldo = $bsService->getRealtimeSisaSaldo(auth()->id(), $periode);
        $members   = User::where('id', '!=', auth()->id())->where('is_active', 1)->get();
        return view('pengajuan-bs.create', compact('sisaSaldo', 'members', 'periode'));
    }

    public function store(Request $request, BonSementaraService $bsService)
    {
        $request->validate([
            'tipe_pengajuan'    => 'required|in:operasional_sales,perjalanan_dinas',
            'nominal_pengajuan' => 'required|numeric|decimal:0,2|min:1|max:9999999999999.99',
            'description'       => 'nullable|string|max:1000',
            'member_ids'        => 'nullable|array',
            'member_ids.*'      => 'distinct|exists:user,id',
            // 🎯 Attachment: WAJIB jika perjalanan_dinas, format JPG/PDF, max 2MB
            'attachment'        => 'exclude_unless:tipe_pengajuan,perjalanan_dinas|required|file|mimes:pdf,jpg,jpeg|extensions:pdf,jpg,jpeg|max:2048',
        ], [
            'attachment.required'   => 'Lampiran (Laporan HRIS / Izin Perjalanan Dinas) wajib diunggah untuk tipe Perjalanan Dinas.',
            'attachment.file'       => 'Lampiran harus berupa file PDF atau JPG.',
            'attachment.mimes'       => 'Format file harus PDF atau JPG.',
            'attachment.extensions' => 'Format file harus PDF atau JPG.',
            'attachment.max'         => 'Ukuran file maksimal 2MB.',
        ]);

        $periode  = app(\App\Services\PeriodService::class)->current();
        $snapshot = $bsService->getRealtimeSisaSaldo(auth()->id(), $periode);

        DB::transaction(function () use ($request, $periode, $snapshot) {
            $tanggal = \App\Support\AppTime::now()->toDateString();
            $docNo = app(DocumentNumberService::class)->next('BS', $tanggal);

            $attachmentPath = null;
            $attachmentName = null;
            if ($request->tipe_pengajuan === 'perjalanan_dinas' && $request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = app(BsAttachmentService::class)->store($request, $file);
                $attachmentName = $file->getClientOriginalName();
            }

            $bs = PengajuanBs::create([
                'document_no'              => $docNo,
                'tanggal'                  => $tanggal,
                'user_id'                  => auth()->id(),
                'atasan_user_id'           => auth()->user()->atasan_user_id,
                'periode'                  => $periode,
                'tipe_pengajuan'           => $request->tipe_pengajuan,
                'nominal_sisa_bs_snapshot' => $snapshot,
                'nominal_pengajuan'        => $request->nominal_pengajuan,
                'description'              => $request->description,
                'attachment_path'          => $attachmentPath,
                'attachment_name'          => $attachmentName,
                'status'                   => 'PENDING_ATASAN',
            ]);

            // 🎯 AUDIT TRAIL
            $tipeLabel = $bs->tipe_pengajuan === 'perjalanan_dinas' ? 'Perjalanan Dinas' : 'Operasional Sales';
            $logDesc = "Pengajuan BS ({$tipeLabel}) dibuat untuk keperluan: " . ($bs->description ?? '-');
            if ($attachmentName) {
                $logDesc .= " | Lampiran: {$attachmentName}";
            }
            BsActivityLog::record(
                $bs->id,
                'CREATED',
                'Pengajuan Dibuat',
                $logDesc,
                $bs->nominal_pengajuan
            );

            // 🎯 Members HANYA untuk Perjalanan Dinas
            if ($request->tipe_pengajuan === 'perjalanan_dinas' && $request->filled('member_ids')) {
                $bs->members()->attach($request->member_ids);
            }

            // 🎯 Notifikasi ke Atasan
            if ($bs->atasan_user_id) {
                \App\Services\BsNotifications::afterCommit(fn () => $bs->atasan?->notify(new \App\Notifications\BsCreated($bs)));
            }
        });

        return redirect()->route('pengajuan-bs.index')->with('success', 'Pengajuan BS berhasil dibuat.');
    }

    public function show(PengajuanBs $bs)
    {
        // 🎯 PRIVACY GUARD
        $isAuthorized = auth()->user()->canSeeAllTransactions() ||
            $bs->user_id === auth()->id() ||
            $bs->members->contains(auth()->id());

        if (!$isAuthorized) {
            abort(403, 'Anda tidak memiliki akses ke riwayat BS ini.');
        }

        $logs = BsActivityLog::where('pengajuan_bs_id', $bs->id)
            ->with('actor')
            ->orderBy('created_at', 'asc')
            ->get();
        $bs->setRelation('logs', $logs);

        return view('pengajuan-bs.show', compact('bs', 'logs'));
    }
}
