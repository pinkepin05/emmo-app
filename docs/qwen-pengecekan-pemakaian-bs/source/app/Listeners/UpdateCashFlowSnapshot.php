<?php

namespace App\Listeners;

use App\Events\CashFlowChanged;
use App\Services\CashFlowService;

class UpdateCashFlowSnapshot
{
    protected CashFlowService $cashFlowService;

    public function __construct(CashFlowService $cashFlowService)
    {
        $this->cashFlowService = $cashFlowService;
    }

    public function handle(CashFlowChanged $event): void
    {
        $this->cashFlowService->recalculateSnapshot(
            $event->userId,
            $event->periode
        );
    }
}