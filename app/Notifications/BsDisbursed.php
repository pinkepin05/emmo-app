<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsDisbursed extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs, public $nominal) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '💰 BS Telah Dicairkan',
            'message' => 'BS ' . $this->bs->document_no . ' cair sebesar Rp ' .
                         number_format($this->nominal, 0, ',', '.') .
                         '. Saldo BS Anda kini aktif untuk realisasi.',
            'url'     => route('pengajuan-bs.index'),
        ];
    }
}