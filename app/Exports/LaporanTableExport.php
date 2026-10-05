<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanTableExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        private array $headings,
        private array $rows,
        private array $numberColumns = [] // contoh: ['H'] = kolom Nominal
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    /** Header tebal berwarna gelap + format angka ribuan */
    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A'],
                ],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];

        foreach ($this->numberColumns as $col) {
            $styles[$col] = ['numberFormat' => ['formatCode' => '#,##0']];
        }

        return $styles;
    }

    /** AutoFilter (panah filter) + Freeze header */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings));
                $lastRow = count($this->rows) + 1;

                if ($lastRow > 1) {
                    $event->sheet->getDelegate()->setAutoFilter("A1:{$lastCol}{$lastRow}");
                }
                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }
}