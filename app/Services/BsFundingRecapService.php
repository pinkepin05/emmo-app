<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\PengajuanBs;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BsFundingRecapService
{
    public function summary(User $viewer, array $filters): array
{
    $result = [];

    foreach (['diajukan', 'dicairkan', 'pending', 'ditolak'] as $stage) {
        $totals = DB::query()
            ->fromSub(
                $this->stageQuery($viewer, $filters, $stage)->toBase(),
                'rekap'
            )
            ->selectRaw(
                'COALESCE(SUM(nominal_rekap), 0) AS amount,
                 COUNT(*) AS total'
            )
            ->first();

        $result[$stage] = [
            'amount' => Money::decimal(Money::minor($totals->amount)),
            'count' => (int) $totals->total,
        ];
    }

    // Selisih pencairan sebagian merupakan bagian dari Total Ditolak.
    $difference = DB::query()
        ->fromSub(
            $this->stageQuery($viewer, $filters, 'dicairkan')->toBase(),
            'rekap'
        )
        ->whereColumn('nominal_pengajuan', '>', 'nominal_rekap')
        ->selectRaw(
            'COALESCE(SUM(nominal_pengajuan - nominal_rekap), 0) AS amount'
        )
        ->value('amount');

    $result['ditolak']['selisih_finance'] =
        Money::decimal(Money::minor($difference));

    return $result;
}

public function stageQuery(
    User $viewer,
    array $filters,
    string $stage
): Builder {
    // Jumlahkan dokumen pencairan untuk setiap pengajuan.
    $source = $this->requests($viewer, $filters)
        ->withSum('financeApprovals as total_dicairkan', 'nominal_approved');

    $query = PengajuanBs::query()
        ->fromSub($source->toBase(), 'pengajuan_bs');

    $paidStatuses = ['DISBURSED', 'SETTLED'];
    $rejectedStatuses = ['REJECTED_ATASAN', 'REJECTED_FINANCE'];

    $amount = match ($stage) {
        'diajukan', 'pending' => 'nominal_pengajuan',
        'dicairkan' => 'COALESCE(total_dicairkan, 0)',
        'ditolak' => "
            CASE
                WHEN status IN ('REJECTED_ATASAN', 'REJECTED_FINANCE')
                    THEN nominal_pengajuan
                ELSE nominal_pengajuan - COALESCE(total_dicairkan, 0)
            END
        ",
        default => throw new \InvalidArgumentException('Tahap tidak valid.'),
    };

    if ($stage === 'pending') {
        $query->whereIn('status', [
            'PENDING_ATASAN',
            'APPROVED_ATASAN',
        ]);
    }

    if ($stage === 'dicairkan') {
        $query->whereIn('status', $paidStatuses);
    }

    if ($stage === 'ditolak') {
        $query->where(function (Builder $group) use (
            $paidStatuses,
            $rejectedStatuses
        ) {
            $group->whereIn('status', $rejectedStatuses)
                ->orWhere(function (Builder $partial) use ($paidStatuses) {
                    $partial->whereIn('status', $paidStatuses)
                        ->whereRaw(
                            'nominal_pengajuan > COALESCE(total_dicairkan, 0)'
                        );
                });
        });
    }

    return $query->select('pengajuan_bs.*')
        ->selectRaw("$amount AS nominal_rekap");
}

    public function requests(User $viewer, array $filters): Builder
    {
        $query = PengajuanBs::query();
        if (!$this->seesAll($viewer)) $query->where('user_id', $viewer->id);
        if ($period = $filters['pemakaian_periode'] ?? '') {
            $query->whereIn('periode', [$period, app(PeriodService::class)->key($period)]);
        }
        if ($reference = $filters['pemakaian_referensi'] ?? '') {
            $query->where('document_no', 'like', '%'.$reference.'%');
        }

        return $query;
    }



    public function payments(User $viewer, array $filters): Builder
    {
        $query = ApprovalBsFinance::whereHas('pengajuanBs', function (Builder $bs) use ($viewer) {
            if (!$this->seesAll($viewer)) $bs->where('user_id', $viewer->id);
        });
        if ($period = $filters['pemakaian_periode'] ?? '') {
            $query->where('tanggal_cair', '>=', $period.'-01')
                ->where('tanggal_cair', '<', app(PeriodService::class)->next($period).'-01');
        }
        if ($reference = $filters['pemakaian_referensi'] ?? '') {
            $query->where(fn (Builder $payment) => $payment->where('document_no', 'like', '%'.$reference.'%')
                ->orWhereHas('pengajuanBs', fn (Builder $bs) => $bs->where('document_no', 'like', '%'.$reference.'%')));
        }

        return $query;
    }

    private function seesAll(User $viewer): bool
    {
        return $viewer->can('akses.approval-finance') || $viewer->canSeeAllTransactions();
    }
}
