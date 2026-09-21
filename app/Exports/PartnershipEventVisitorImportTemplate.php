<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartnershipEventVisitorImportTemplate implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'No',
            'Company Name',
            'Name',
            'Job Title',
            'Business Email',
            'Mobile Number',
            'Office Number',
            'Website',
            'Address',
            'Remarks',
            'Merchandise',
        ];
    }

    public function array(): array
    {
        return [
            [1, 'Gessner', 'Michael O\'Connor', 'Director', 'MOC@Gessner.com.au', '61419302930', '', '', '', 'Mi26 - day1 - visitor', ''],
            [2, 'Neotek Inovasi Global', 'Izdhihar Lubis', 'Operations & Sales Coordinator', 'ops.sales5@neotekinovasi.com', '6282227668931', '', '', '', 'Mi26 - day1 - visitor', ''],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CC0000']],
        ]);

        $sheet->getComment('A1')->getText()->createTextRun('Opsional. Nomor urut, tidak disimpan ke database.');
        $sheet->getComment('B1')->getText()->createTextRun('Wajib diisi salah satu antara Company Name atau Name.');
        $sheet->getComment('C1')->getText()->createTextRun('Wajib diisi salah satu antara Company Name atau Name.');

        return [];
    }
}
