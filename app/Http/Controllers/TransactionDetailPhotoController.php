<?php

namespace App\Http\Controllers;

use App\Models\BsUsageCheckDetail;
use App\Models\TransaksiOperasionalDetail;
use App\Models\TransaksiPerjalananDinasDetail;
use App\Services\TransactionDetailPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionDetailPhotoController extends Controller
{
    public function show(Request $request, string $type, int $detail)
    {
        $model = match ($type) {
            'OPR' => TransaksiOperasionalDetail::class,
            'PD' => TransaksiPerjalananDinasDetail::class,
            default => abort(404),
        };
        $row = $model::with('header')->findOrFail($detail);
        abort_unless($row->header, 404);
        abort_unless($request->user()->canViewBsTransaction($row->header)
            || $request->user()->can('akses.approval-finance'), 403);

        return $this->respond($request, $row->photo_path, $row->photo_name);
    }

    public function history(Request $request, BsUsageCheckDetail $detail)
    {
        // Uses the same Finance permission as the immutable check detail page.
        abort_unless($request->user()->can('akses.approval-finance'), 403);

        return $this->respond($request, $detail->source_photo_path, $detail->source_photo_name);
    }

    private function respond(Request $request, ?string $path, ?string $name)
    {
        abort_unless($path && preg_match('~\Areceipts/[a-zA-Z0-9_-]+\.(jpe?g|png|webp)\z~', $path), 404);
        $disk = Storage::disk(TransactionDetailPhotoService::DISK);
        abort_unless($disk->exists($path), 404, 'Foto tidak ditemukan.');
        $mime = match (pathinfo($path, PATHINFO_EXTENSION)) {
            'png' => 'image/png', 'webp' => 'image/webp', default => 'image/jpeg',
        };

        return $disk->response($path, $name ?: basename($path), [
            'Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ], $request->boolean('download') ? 'attachment' : 'inline');
    }
}
