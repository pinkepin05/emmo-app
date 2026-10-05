<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransaksiOverPlafon extends Notification
{
    use Queueable;

    public function __construct(
        public string $judulTransaksi,
        public float $nominal,
        public float $plafon,
        public float $selisih,
        public string $url
    ) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => '⚠️ Transaksi Melebihi Plafon',
            'message' => $this->judulTransaksi . ' sebesar Rp ' . number_format($this->nominal, 0, ',', '.')
                . ' melebihi plafon Rp ' . number_format($this->plafon, 0, ',', '.')
                . ' (selisih Rp ' . number_format($this->selisih, 0, ',', '.') . '). Mohon ditinjau.',
            'url'     => $this->url,
        ];
    }
}