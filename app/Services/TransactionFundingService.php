<?php

namespace App\Services;

use App\Models\PengajuanBs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionFundingService
{
    public static function rules(): array
    {
        return [
            'funding_source' => 'sometimes|in:BS',
            'bs_id' => 'prohibited',
            'bs_owner_id' => 'nullable|integer|exists:user,id',
        ];
    }

    public function owners(int $inputterId, bool $travel)
    {
        $ids = collect([$inputterId]);
        if ($travel) {
            $ids = $ids->merge($this->sharedBs($inputterId)->pluck('user_id'));
        }
        return User::whereIn('id', $ids->unique())->orderBy('nama')->get();
    }

    public function resolve(Request $request, bool $travel, ?Model $document = null): int
    {
        $inputterId = (int) ($document?->user_id ?? $request->user()->id);
        $ownerId = (int) ($request->input('bs_owner_id') ?: ($document?->bsCashOwnerId() ?? $inputterId));
        $this->assertAccess($inputterId, $ownerId, $travel);
        return $ownerId;
    }

    public function assertAccess(int $inputterId, int $ownerId, bool $travel): void
    {
        if ($ownerId === $inputterId) return;
        if (!$travel || !$this->sharedBs($inputterId)->where('user_id', $ownerId)->exists()) {
            throw ValidationException::withMessages(['bs_owner_id' => 'Anda tidak berhak menggunakan saldo BS pemilik ini. Saldo grup hanya dapat dipakai anggota untuk Perjalanan Dinas.']);
        }
    }

    private function sharedBs(int $inputterId)
    {
        return PengajuanBs::where('tipe_pengajuan', 'perjalanan_dinas')->where('status', 'DISBURSED')
            ->whereHas('financeApprovals')
            ->whereHas('members', fn ($q) => $q->where('user_id', $inputterId));
    }
}
