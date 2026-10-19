<?php

namespace App\Models\Concerns;

use App\Models\TransaksiPerjalananDinasDetail;
use App\Services\TransactionDetailPhotoService;

trait HasDetailPhoto
{
    public static function bootHasDetailPhoto(): void
    {
        static::updated(function ($detail) {
            if ($detail->wasChanged('photo_path')) {
                app(TransactionDetailPhotoService::class)->releaseAfterCommit($detail->getRawOriginal('photo_path'));
            }
        });
        static::deleted(fn ($detail) => app(TransactionDetailPhotoService::class)->releaseAfterCommit($detail->photo_path));
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? route('transaction-detail-photo.show', [
            'type' => $this instanceof TransaksiPerjalananDinasDetail ? 'PD' : 'OPR', 'detail' => $this->id,
        ]) : null;
    }
}
