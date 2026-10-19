<?php

namespace App\Services;

use App\Models\BsActivityLog;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Notifications\BsRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsApprovalPeriodService
{
    public function __construct(private PeriodService $periods) {}

    public function rejectIfClosed(PengajuanBs $bs): bool
    {
        $rejected = DB::transaction(function () use ($bs) {
            $key = $this->periods->normalize($bs->periode);
            // Use the same lock order as approval and period closing: period, then BS.
            $period = Periode::where('periode', $key)->lockForUpdate()->first();
            $locked = PengajuanBs::whereKey($bs->id)->lockForUpdate()->firstOrFail();
            if ($this->periods->normalize($locked->periode) !== $key) {
                throw ValidationException::withMessages(['error' => 'Periode pengajuan berubah. Muat ulang halaman.']);
            }
            $status = $period?->status ?? ($key < $this->periods->current() ? 'CLOSED' : 'OPEN');
            if ($locked->status !== 'PENDING_ATASAN' || $status === 'OPEN') {
                return false;
            }

            $reason = "Ditolak otomatis oleh sistem karena periode pengajuan {$key} sudah ditutup.";
            $locked->update([
                'status' => 'REJECTED_ATASAN',
                'rejection_reason' => $reason,
                'approval_message' => null,
            ]);
            BsActivityLog::create([
                'pengajuan_bs_id' => $locked->id,
                'activity_code' => 'REJECTED_SYSTEM',
                'activity_name' => 'Ditolak Sistem',
                'actor_user_id' => null,
                'description' => $reason,
            ]);
            BsNotifications::send($locked->user, new BsRejected($locked, bySystem: true));

            return true;
        }, 3);

        if ($rejected) $bs->refresh();

        return $rejected;
    }

    public function rejectClosedPeriods(?int $atasanId = null, ?string $periode = null): int
    {
        $count = 0;
        $query = PengajuanBs::where('status', 'PENDING_ATASAN')
            ->when($atasanId !== null, fn ($q) => $q->where('atasan_user_id', $atasanId));
        if ($periode !== null) {
            $query->whereIn('periode', [$this->periods->normalize($periode), $this->periods->key($periode)]);
        }
        $query->chunkById(100, function ($requests) use (&$count) {
            foreach ($requests as $bs) {
                if ($this->rejectIfClosed($bs)) $count++;
            }
        });

        return $count;
    }
}
