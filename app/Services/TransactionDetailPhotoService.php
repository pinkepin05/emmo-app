<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransactionDetailPhotoService
{
    public const DISK = 'transaction_detail_photos';
    private const PENDING = 'transaction_detail_pending_photos';

    public static function rules(string $prefix): array
    {
        return [
            $prefix.'.*.photo' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|extensions:jpg,jpeg,png,webp|max:2048',
            $prefix.'.*.remove_photo' => 'sometimes|boolean',
        ];
    }

    public function assertBelongsTo(Request $request, $header): void
    {
        $ids = array_unique(array_map('strval', array_merge(array_keys((array) $request->input('details', [])), (array) $request->input('delete_details', []))));
        $owned = $header->details()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (string) $id)->all();
        if (array_diff($ids, $owned)) {
            throw ValidationException::withMessages(['details' => 'Rincian tidak ditemukan pada transaksi ini. Muat ulang halaman.']);
        }
    }

    // Call inside the transaction protected by AtomicBsRequest. Never accept paths from input.
    public function attributes(Request $request, string $key): array
    {
        $file = $request->file($key.'.photo');
        if (!$file) {
            return $request->boolean($key.'.remove_photo') ? ['photo_path' => null, 'photo_name' => null] : [];
        }
        try {
            $path = $file->store('receipts', self::DISK);
        } catch (\League\Flysystem\FilesystemException $exception) {
            report($exception);
            throw ValidationException::withMessages([$key.'.photo' => 'Foto gagal disimpan. Silakan pilih ulang dan coba kembali.']);
        }
        if (!$path) {
            throw ValidationException::withMessages([$key.'.photo' => 'Foto gagal disimpan. Silakan pilih ulang dan coba kembali.']);
        }
        $pending = $request->attributes->get(self::PENDING, []);
        $pending[] = $path;
        $request->attributes->set(self::PENDING, $pending);
        DB::afterCommit(function () use ($request, $path) {
            $request->attributes->set(self::PENDING, array_diff($request->attributes->get(self::PENDING, []), [$path]));
        });

        return ['photo_path' => $path, 'photo_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255)];
    }

    public function discardPending(Request $request): void
    {
        foreach ($request->attributes->get(self::PENDING, []) as $path) {
            $this->deleteFile($path);
        }
        $request->attributes->remove(self::PENDING);
    }

    public function releaseAfterCommit(?string $path): void
    {
        if (!$path) return;
        DB::afterCommit(function () use ($path) {
            // Finance history keeps the exact evidence reviewed, even after a correction.
            if (DB::table('bs_usage_check_details')->where('source_photo_path', $path)->exists()) return;
            foreach (['transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_detail'] as $table) {
                if (DB::table($table)->where('photo_path', $path)->whereNull('deleted_at')->exists()) return;
            }
            $this->deleteFile($path);
        });
    }

    private function deleteFile(string $path): void
    {
        try {
            Storage::disk(self::DISK)->delete($path);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
