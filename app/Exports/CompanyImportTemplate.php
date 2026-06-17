<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyImportTemplate implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'old_company_name',
            'prefix',
            'company_name',
            'company_website',
            'company_category',
            'address',
            'city',
            'portal_code',
            'full_office_number',
            'country',
        ];
    }

    public function array(): array
    {
        return [
            ['PT. INTER DELTA PERSADA', 'PT', 'Inter Delta PERSADA', 'www.interdelta.com', 'Coal Mining', 'Jl. Sudirman No.1', 'Jakarta', '12190', '+62 21 1234567', 'Indonesia'],
            ['MMI', 'PT', 'Media Mitrakarya Indonesia', '', 'Media', '', '', '', '', ''],
            ['PT Media Mitrakarya', '', 'Media Mitrakarya Indonesia', '', '', '', '', '', '', ''],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CC0000']],
        ]);

        $sheet->getComment('A1')->getText()->createTextRun("WAJIB. Nama company yang saat ini ada di database.\nDigunakan untuk matching record.");
        $sheet->getComment('B1')->getText()->createTextRun('Prefix baru (PT, CV, Ltd, dll). Kosongkan jika tidak diubah.');
        $sheet->getComment('C1')->getText()->createTextRun('Nama company yang benar. Kosongkan = pakai target dari row di atas (merge).');

        return [];
    }
}
