<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Services\BsAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajuanBsAttachmentController extends Controller
{
    public function __invoke(Request $request, PengajuanBs $bs)
    {
        $user = $request->user();
        $canViewSubmission = $user->can('akses.pengajuan-bs') && (
            $user->canSeeAllTransactions()
            || (int) $bs->user_id === (int) $user->id
            || $bs->members()->whereKey($user->id)->exists()
        );
        $isApprover = (int) $bs->atasan_user_id === (int) $user->id && $user->can('approval-atasan');
        abort_unless($canViewSubmission || $isApprover || $user->can('akses.approval-finance'), 403);

        $path = $bs->attachment_path;
        abort_unless($bs->isPerjalananDinas() && is_string($path)
            && preg_match('~\Apengajuan_bs_attachments/[a-zA-Z0-9_-]+\.(pdf|jpe?g)\z~', $path), 404);
        $disk = Storage::disk(BsAttachmentService::DISK);
        abort_unless($disk->exists($path), 404, 'Lampiran tidak ditemukan.');

        return $disk->response($path, $bs->attachment_name ?: basename($path), [
            'Content-Type' => str_ends_with($path, '.pdf') ? 'application/pdf' : 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], $request->boolean('download') ? 'attachment' : 'inline');
    }
}
