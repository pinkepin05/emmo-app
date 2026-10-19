<?php

namespace App\Services;

use App\Models\BsUsageApprovalLog;
use App\Models\BsUsageCheck;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\User;
use App\Notifications\BsUsageApprovalUpdated;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BsValidationService
{
    public function finalize(Model $document): string
    {
        return $this->withLockedDocument($document, function ($locked, $owner) {
            abort_unless(auth()->user()->canAccessTransaction($locked), 403);
            $this->requireStatus($locked, 'DRAFT');
            $amount = $this->amount($locked);
            $travel = $locked instanceof TransaksiPerjalananDinasHeader;
            app(TransactionFundingService::class)->assertAccess((int) $locked->user_id, $owner, $travel);
            if ($travel && $owner !== (int) $locked->user_id) {
                $locked->update([
                    'bs_owner_id' => $owner, 'status' => 'PENDING_OWNER',
                    'approval_version' => (int) $locked->approval_version + 1,
                    'submitted_at' => now(), 'submitted_by' => auth()->id(),
                    'owner_reviewed_at' => null, 'owner_reviewed_by' => null, 'owner_rejection_reason' => null,
                ]);
                $this->reviewLog($locked, 'SUBMITTED', $amount);
                $this->notify($locked, 'SUBMITTED', $owner);
                return 'PENDING_OWNER';
            }
            $this->post($locked, $owner, $amount);
            return 'VALIDATED';
        });
    }

    public function approve(TransaksiPerjalananDinasHeader $document, int $version): void
    {
        $this->withLockedDocument($document, function ($locked, $owner) use ($version) {
            $this->requireOwner($locked, $owner);
            $this->requireStatus($locked, 'PENDING_OWNER', $version);
            app(TransactionFundingService::class)->assertAccess((int) $locked->user_id, $owner, true);
            $amount = $this->amount($locked);
            $this->post($locked, $owner, $amount);
            $locked->update(['owner_reviewed_at' => now(), 'owner_reviewed_by' => auth()->id(), 'owner_rejection_reason' => null]);
            $this->reviewLog($locked, 'APPROVED', $amount);
            $this->notify($locked, 'APPROVED', (int) $locked->user_id);
        });
    }

    public function reject(TransaksiPerjalananDinasHeader $document, int $version, string $reason): void
    {
        if (trim($reason) === '' || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['reason' => 'Alasan penolakan wajib diisi, maksimal 1000 karakter.']);
        }
        $this->withLockedDocument($document, function ($locked, $owner) use ($version, $reason) {
            $this->requireOwner($locked, $owner);
            $this->requireStatus($locked, 'PENDING_OWNER', $version);
            $locked->update(['status' => 'REJECTED_OWNER', 'owner_reviewed_at' => now(),
                'owner_reviewed_by' => auth()->id(), 'owner_rejection_reason' => trim($reason)]);
            $this->reviewLog($locked, 'REJECTED', $this->amount($locked), trim($reason));
            $this->notify($locked, 'REJECTED', (int) $locked->user_id);
        });
    }

    public function returnToDraft(TransaksiPerjalananDinasHeader $document, int $version, bool $rejected = false): void
    {
        $this->withLockedDocument($document, function ($locked, $owner) use ($version, $rejected) {
            abort_unless((int) auth()->id() === (int) $locked->user_id, 403);
            $this->requireStatus($locked, $rejected ? 'REJECTED_OWNER' : 'PENDING_OWNER', $version);
            $action = $rejected ? 'REVISED' : 'WITHDRAWN';
            $this->reviewLog($locked, $action, $this->amount($locked));
            $locked->update(['status' => 'DRAFT', 'submitted_at' => null, 'submitted_by' => null,
                'owner_reviewed_at' => null, 'owner_reviewed_by' => null, 'owner_rejection_reason' => null]);
            if (!$rejected) $this->notify($locked, 'WITHDRAWN', $owner);
        });
    }

    public function reopenAfterFinanceRejection(Model $document, int $checkId): void
    {
        $this->withLockedDocument($document, function ($locked, $owner) use ($checkId) {
            abort_unless((int) auth()->id() === (int) $locked->user_id, 403);
            $this->requireStatus($locked, 'VALIDATED');
            $type = $locked instanceof TransaksiPerjalananDinasHeader ? 'PD' : 'OPR';
            // Finance writes also lock the source first. A stale rejection cannot reopen a newer decision.
            $latest = BsUsageCheck::where('jenis_transaksi', $type)
                ->where('transaksi_pemakaian_id', $locked->id)->latest('id')->first();
            if (!$latest || $latest->id !== $checkId || !in_array($latest->keputusan, ['REJECTED', 'REVISION'], true)
                || (int) $latest->source_revision !== (int) $locked->submission_version) {
                throw ValidationException::withMessages(['check_id' => 'Hasil Finance atau versi pengajuan berubah. Buka kembali rincian pengajuan.']);
            }
            $attributes = ['status' => 'DRAFT', 'validated_at' => null, 'validated_by' => null,
                'finance_correction_check_id' => $latest->id];
            if ($locked instanceof TransaksiPerjalananDinasHeader) {
                $attributes += ['submitted_at' => null, 'submitted_by' => null,
                    'owner_reviewed_at' => null, 'owner_reviewed_by' => null, 'owner_rejection_reason' => null];
            }
            $locked->forceFill($attributes)->save();
            // Rejected and resubmitted usage waits for a fresh Finance confirmation.
            app(BsCashFlowService::class)->recalculateSnapshot($owner, $locked->periode);
            app(StockCashFlowService::class)->recalculateSnapshot($owner, $locked->periode);
        });
    }

    private function withLockedDocument(Model $document, callable $action): mixed
    {
        abort_unless($document instanceof TransaksiOperasionalHeader || $document instanceof TransaksiPerjalananDinasHeader, 400);
        $owner = $document->bsCashOwnerId() ?? (int) $document->user_id;
        return DB::transaction(function () use ($document, $owner, $action) {
            app(PeriodService::class)->lockOpen($document->periode);
            foreach (collect([$owner, (int) $document->user_id])->unique()->sort() as $userId) {
                DB::table('user')->where('id', $userId)->lockForUpdate()->first();
            }
            $locked = $document->newQuery()->whereKey($document->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->periode !== $document->periode || ($locked->bsCashOwnerId() ?? (int) $locked->user_id) !== $owner
                || (int) $locked->user_id !== (int) $document->user_id) {
                throw ValidationException::withMessages(['error' => 'Transaksi berubah. Muat ulang halaman.']);
            }
            return $action($locked, $owner);
        }, 3);
    }

    private function requireStatus(Model $document, string $status, ?int $version = null): void
    {
        if ($document->status !== $status || ($version !== null && (int) $document->approval_version !== $version)) {
            throw ValidationException::withMessages(['error' => 'Status atau versi permintaan berubah. Muat ulang dan periksa rincian terbaru sebelum melanjutkan.']);
        }
    }

    private function requireOwner(TransaksiPerjalananDinasHeader $document, int $owner): void
    {
        abort_unless((int) auth()->id() === $owner && $owner !== (int) $document->user_id, 403,
            'Hanya pemilik saldo yang dapat menyetujui atau menolak permintaan anggota.');
    }

    private function amount(Model $document): int
    {
        $amount = Money::minor($document->details()->sum('nominal'));
        if ($amount <= 0) throw ValidationException::withMessages(['details' => 'Tambahkan rincian biaya dengan total lebih dari nol.']);
        return $amount;
    }

    private function post(Model $document, int $owner, int $amount): void
    {
        $cash = app(BsCashFlowService::class);
        $available = Money::minor($cash->availableBalance($cash->calculate($owner, $document->periode)));
        if ($amount > $available) {
            throw ValidationException::withMessages(['bs_balance' => 'Total biaya melebihi saldo BS gabungan yang tersedia pada periode ini. Sisa: Rp '.number_format($available / 100, 2, ',', '.').'. Saldo bulan sebelumnya tersedia setelah tutup buku.']);
        }
        $document->forceFill(['bs_owner_id' => $owner, 'status' => 'VALIDATED', 'validated_at' => now(),
            'validated_by' => auth()->id(), 'submission_version' => (int) $document->submission_version + 1])->save();
        DB::table('bs_usage_logs')->insert([
            'transaction_type' => $document instanceof TransaksiPerjalananDinasHeader ? 'perjalanan_dinas' : 'operasional_sales',
            'transaction_id' => $document->id, 'document_no' => $document->transaksi_id,
            'bs_owner_id' => $owner, 'inputter_id' => $document->user_id, 'actor_id' => auth()->id(),
            'periode' => $document->periode, 'nominal' => Money::decimal($amount), 'created_at' => now(),
            'submission_version' => $document->submission_version,
        ]);
        $cash->recalculateSnapshot($owner, $document->periode);
        app(StockCashFlowService::class)->recalculateSnapshot($owner, $document->periode);
    }

    private function reviewLog(TransaksiPerjalananDinasHeader $document, string $action, int $amount, ?string $reason = null): void
    {
        BsUsageApprovalLog::create([
            'transaction_id' => $document->id, 'document_no' => $document->transaksi_id,
            'approval_version' => $document->approval_version, 'bs_owner_id' => $document->bsCashOwnerId(),
            'inputter_id' => $document->user_id, 'actor_id' => auth()->id(), 'action' => $action,
            'nominal' => Money::decimal($amount), 'reason' => $reason, 'created_at' => now(),
        ]);
    }

    private function notify(TransaksiPerjalananDinasHeader $document, string $action, int $recipient): void
    {
        BsNotifications::send(User::find($recipient), new BsUsageApprovalUpdated($document, $action));
    }
}
