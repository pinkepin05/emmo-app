<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsRejected extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs, public bool $bySystem = false) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '❌ Pengajuan BS Ditolak',
            'message' => $this->bySystem
                ? 'Pengajuan BS ' . $this->bs->document_no . ' Anda ditolak oleh sistem. ' . $this->bs->rejection_reason
                : 'Pengajuan BS ' . $this->bs->document_no . ' Anda ditolak oleh atasan.',
            'url'     => route('pengajuan-bs.index'),
        ];
    }
}
