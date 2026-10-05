<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class BsNotifications
{
    public static function send(?\App\Models\User $recipient, \Illuminate\Notifications\Notification $notification): void
    {
        self::afterCommit(fn () => $recipient?->notify($notification));
    }

    public static function afterCommit(callable $send): void
    {
        DB::afterCommit(function () use ($send) {
            try { $send(); } catch (Throwable $e) { report($e); }
        });
    }
}
