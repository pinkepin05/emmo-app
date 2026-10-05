<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsApproved extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '💼 BS Menunggu Pencairan',
            'message' => 'BS ' . $this->bs->document_no . ' milik ' . $this->bs->user->nama .
                         ' telah disetujui atasan. Segera proses pencairan.',
            'url'     => route('approval-finance.index'),
        ];
    }
}