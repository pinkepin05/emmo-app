<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsCreated extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '🆕 Pengajuan BS Baru',
            'message' => $this->bs->user->nama . ' mengajukan BS ' . $this->bs->document_no .
                         ' sebesar Rp ' . number_format($this->bs->nominal_pengajuan, 0, ',', '.') .
                         '. Menunggu approval Anda.',
            'url'     => route('approval-atasan.index'),
        ];
    }
}