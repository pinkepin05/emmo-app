<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CashFlowService;
use App\Models\User;

class RecalculateCashFlow extends Command
{
    protected $signature = 'cashflow:recalculate {--user= : Specific user ID}';
    protected $description = 'Recalculate all cash flow snapshots';

    public function handle(CashFlowService $service)
    {
        if ($userId = $this->option('user')) {
            $service->recalculateAllPeriods($userId);
            $this->info("✅ Recalculated for user ID: {$userId}");
        } else {
            $users = User::where('is_active', 1)->get();
            $this->info("🔄 Recalculating for {$users->count()} active users...");
            
            foreach ($users as $user) {
                $service->recalculateAllPeriods($user->id);
                $this->info("  ✓ {$user->nama} (ID: {$user->id})");
            }
        }
        
        $this->info('🎉 Selesai!');
    }
}