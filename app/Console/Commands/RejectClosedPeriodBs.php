<?php

namespace App\Console\Commands;

use App\Services\BsApprovalPeriodService;
use Illuminate\Console\Command;

class RejectClosedPeriodBs extends Command
{
    protected $signature = 'bs:reject-closed-periods';

    protected $description = 'Tolak otomatis BS yang masih menunggu atasan pada periode tertutup';

    public function handle(BsApprovalPeriodService $approvalPeriods): int
    {
        $count = $approvalPeriods->rejectClosedPeriods();
        $this->info("{$count} pengajuan BS ditolak otomatis oleh sistem.");

        return self::SUCCESS;
    }
}
