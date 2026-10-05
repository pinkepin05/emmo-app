<?php

namespace App\Exports;

use App\Models\StockCashFlowSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CashFlowExport implements WithMultipleSheets
{
    public function __construct(
        private Collection $snapshots,
        private array $statuses,
        private ?array $transactions = null
    ) {}

    public function sheets(): array
    {
        $rows = $this->snapshots->map(function (StockCashFlowSnapshot $snapshot) {
            $status = $this->statuses[$snapshot->periode] ?? 'OPEN';

            return [
                str_replace('-', '', $snapshot->periode),
                $status,
                (float) $snapshot->saldo_awal,
                (float) $snapshot->total_masuk,
                (float) $snapshot->total_keluar_bs,
                (float) $snapshot->total_event_dalam,
                (float) $snapshot->total_event_luar,
                (float) $snapshot->total_keluar,
                (float) $snapshot->penyesuaian,
                $status === 'CLOSED' && $snapshot->saldo_akhir !== null ? (float) $snapshot->saldo_akhir : null,
                $snapshot->opening_final ? 'Final' : 'Menunggu penutupan periode sebelumnya',
            ];
        })->all();

        $sheets = [new CashFlowSheetExport('Ringkasan Stok Kas', [
            'Periode', 'Status Periode', 'Saldo Awal (Rp)', 'Total Masuk (Rp)',
            'Pengeluaran BS (Rp)', 'Event Dalam Kota (Rp)', 'Event Luar Kota (Rp)',
            'Total Keluar (Rp)', 'Penyesuaian BS (Rp)', 'Saldo Akhir (Rp)', 'Status Saldo Awal',
        ], $rows, ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'])];

        if ($this->transactions !== null) {
            foreach (['masuk' => 'Uang Masuk', 'keluar' => 'Uang Keluar'] as $key => $title) {
                $rows = array_map(fn (array $transaction) => [
                    Carbon::parse($transaction['date'])->format('Y-m-d'),
                    $transaction['type'],
                    $transaction['description'],
                    (float) $transaction['nominal'],
                ], $this->transactions[$key]);

                $sheets[] = new CashFlowSheetExport($title, [
                    'Tanggal', 'Jenis', 'Keterangan', 'Nominal (Rp)',
                ], $rows, ['D']);
            }

            $rows = array_map(fn (array $transaction) => [
                $transaction['description'], (float) $transaction['nominal'],
            ], $this->transactions['penyesuaian']);
            $sheets[] = new CashFlowSheetExport('Penyesuaian BS', [
                'Dokumen BS', 'Penyesuaian (Rp)',
            ], $rows, ['B']);
        }

        return $sheets;
    }
}
