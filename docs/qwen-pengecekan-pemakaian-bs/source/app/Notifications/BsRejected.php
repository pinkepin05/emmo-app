<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsRejected extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '❌ Pengajuan BS Ditolak',
            'message' => 'Pengajuan BS ' . $this->bs->document_no . ' Anda ditolak oleh atasan.',
            'url'     => route('pengajuan-bs.index'),
        ];
    }
}