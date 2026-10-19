<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class BsAttachmentService
{
    public const DISK = 'bs_attachments';

    private const PENDING = 'bs_pending_attachments';

    public function store(Request $request, UploadedFile $file): string
    {
        try {
            $path = $file->store('pengajuan_bs_attachments', self::DISK);
        } catch (\League\Flysystem\FilesystemException $exception) {
            report($exception);
            throw ValidationException::withMessages(['attachment' => 'Lampiran gagal disimpan. Silakan coba kembali.']);
        }
        if (!$path) {
            throw ValidationException::withMessages(['attachment' => 'Lampiran gagal disimpan. Silakan coba kembali.']);
        }

        $pending = $request->attributes->get(self::PENDING, []);
        $pending[] = $path;
        $request->attributes->set(self::PENDING, $pending);

        // Only keep uploads after the outer AtomicBsRequest transaction commits.
        // Callbacks from rolled-back attempts are discarded by Laravel.
        DB::afterCommit(function () use ($request, $path) {
            $request->attributes->set(self::PENDING, array_diff(
                $request->attributes->get(self::PENDING, []), [$path]
            ));
        });

        return $path;
    }

    public function discardPending(Request $request): void
    {
        foreach ($request->attributes->get(self::PENDING, []) as $path) {
            try {
                Storage::disk(self::DISK)->delete($path);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
        $request->attributes->remove(self::PENDING);
    }
}
