<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CashFlowChanged
{
    use Dispatchable, SerializesModels;

    public int $userId;
    public string $periode;

    public function __construct(int $userId, string $periode)
    {
        $this->userId = $userId;
        $this->periode = $periode;
    }
}