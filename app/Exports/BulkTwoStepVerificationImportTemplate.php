<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BulkTwoStepVerificationImportTemplate implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return ['Email', 'Status'];
    }

    public function array(): array
    {
        return [
            ['john@example.com', 'Verified'],
            ['jane@example.com', 'Not Verified'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CC0000']],
        ]);

        $sheet->getComment('A1')->getText()->createTextRun('Wajib diisi. Harus sama persis dengan email member di sistem.');
        $sheet->getComment('B1')->getText()->createTextRun('Terima: Verified/Yes/Ya/True/1 = tercentang, selain itu (termasuk kosong) = tidak tercentang.');

        return [];
    }
}
