<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowSheetExport extends LaporanTableExport implements WithCustomValueBinder, WithStrictNullComparison, WithTitle
{
    public function __construct(
        private string $sheetTitle,
        array $headings,
        array $rows,
        private array $moneyColumns = []
    ) {
        parent::__construct($headings, $rows, $moneyColumns);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = parent::styles($sheet);
        foreach ($this->moneyColumns as $column) {
            $styles[$column]['numberFormat']['formatCode'] = '#,##0.00;[Red]-#,##0.00';
        }

        return $styles;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        // Document numbers and descriptions must remain literal text in Excel.
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }
}
