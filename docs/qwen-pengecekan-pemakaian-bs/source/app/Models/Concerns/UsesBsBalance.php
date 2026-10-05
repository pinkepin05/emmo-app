<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait UsesBsBalance
{
    public function bsOwner()
    {
        return $this->belongsTo(User::class, 'bs_owner_id');
    }

    public function bsCashOwnerId(): ?int
    {
        $owner = $this->bs_owner_id ?? $this->bonSementara?->user_id;
        return $owner === null ? null : (int) $owner;
    }

    public function scopeForBsOwner(Builder $query, int $ownerId): Builder
    {
        return $query->where(fn ($q) => $q->where('bs_owner_id', $ownerId)
            ->orWhere(fn ($legacy) => $legacy->whereNull('bs_owner_id')
                ->whereHas('bonSementara', fn ($bs) => $bs->where('user_id', $ownerId))));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when(!$user->canSeeAllTransactions(), fn ($q) => $q
            ->where(fn ($visible) => $visible->where('user_id', $user->id)
                ->orWhere(fn ($owned) => $owned->forBsOwner((int) $user->id))));
    }
}
