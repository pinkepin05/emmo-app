<?php

namespace App\Notifications;

use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Notifications\Notification;

class BsUsageApprovalUpdated extends Notification
{
    public function __construct(public TransaksiPerjalananDinasHeader $document, public string $action) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        $ownerMessage = in_array($this->action, ['SUBMITTED', 'WITHDRAWN'], true);
        $title = match ($this->action) {
            'SUBMITTED' => 'Permintaan Pemakaian BS',
            'APPROVED' => 'Pemakaian BS Disetujui',
            'REJECTED' => 'Pemakaian BS Ditolak',
            'WITHDRAWN' => 'Permintaan Pemakaian BS Ditarik',
        };
        return [
            'title' => $title,
            'message' => e($this->document->transaksi_id).' - '.e($this->document->user?->nama ?? '-').'. '.$title.'.',
            'url' => $ownerMessage ? route('bs-usage-approvals.show', $this->document->id)
                : route('transaksi_perjalanan_dinas.show', $this->document->transaksi_id),
        ];
    }
}
