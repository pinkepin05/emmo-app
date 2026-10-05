<?php

namespace App\Notifications;

use App\Models\PengajuanBs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BsSettled extends Notification
{
    use Queueable;

    public function __construct(public PengajuanBs $bs) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        $ket = $this->bs->nominal_pengembalian > 0
            ? 'Pengembalian Rp ' . number_format($this->bs->nominal_pengembalian, 0, ',', '.') . ' telah dicatat.'
            : ($this->bs->nominal_reimburse > 0
                ? 'Reimburse Rp ' . number_format($this->bs->nominal_reimburse, 0, ',', '.') . ' telah dicatat.'
                : 'Lunas tanpa selisih.');

        return [
            'title'   => '📁 BS Ditutup (Settled)',
            'message' => 'BS ' . $this->bs->document_no . ' telah ditutup oleh Finance. ' . $ket,
            'url'     => route('pengajuan-bs.show', $this->bs->id),
        ];
    }
}