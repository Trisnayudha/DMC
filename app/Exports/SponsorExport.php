<?php

namespace App\Exports;

use App\Models\Sponsors\Sponsor;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SponsorExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    private $rows;

    public function collection()
    {
        // Ambil sponsor aktif dengan relasi pics, diurutkan berdasarkan package dan sort
        $sponsors = Sponsor::where('status', 'publish')
            ->orderByRaw("FIELD(package, 'platinum','gold','silver')")
            ->orderBy('sort')
            ->with('pics')
            ->get();

        $this->rows = collect();

        foreach ($sponsors as $sponsor) {
            // Konversi contract_start & contract_end ke format "May 2024 - June 2025"
            $period = '';
            if ($sponsor->contract_start && $sponsor->contract_end) {
                try {
                    $startObj = Carbon::createFromFormat('Y-m', $sponsor->contract_start);
                    $endObj   = Carbon::createFromFormat('Y-m', $sponsor->contract_end);
                    $period = $startObj->format('F Y') . ' - ' . $endObj->format('F Y');
                } catch (\Exception $e) {
                    $period = $sponsor->contract_start . ' - ' . $sponsor->contract_end;
                }
            }

            if ($sponsor->pics->count() > 0) {
                foreach ($sponsor->pics as $pic) {
                    $this->rows->push([
                        'type_of_sponsor' => $sponsor->package,
                        'sponsor_period'  => $period,
                        'company'         => $sponsor->name,
                        'name_pic'        => $pic->name,
                        'position_pic'    => $pic->job_title,
                        'email_pic'       => $pic->email,
                        'mobile_phone'    => $pic->phone,
                        'address'         => $sponsor->address,
                        'city'            => $sponsor->city ?? '',
                        'country'         => $sponsor->country ?? '',
                        'postal_code'     => $sponsor->postal_code ?? '',
                        'office_number'   => $sponsor->office_number ?? '',
                        'website'         => $sponsor->company_website,
                    ]);
                }
            } else {
                $this->rows->push([
                    'type_of_sponsor' => $sponsor->package,
                    'sponsor_period'  => $period,
                    'company'         => $sponsor->name,
                    'name_pic'        => '',
                    'position_pic'    => '',
                    'email_pic'       => '',
                    'mobile_phone'    => '',
                    'address'         => $sponsor->address,
                    'city'            => $sponsor->city ?? '',
                    'country'         => $sponsor->country ?? '',
                    'postal_code'     => $sponsor->postal_code ?? '',
                    'office_number'   => $sponsor->office_number ?? '',
                    'website'         => $sponsor->company_website,
                ]);
            }
        }

        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Type of Sponsor',
            'Sponsor Period',
            'Company',
            'Name PIC',
            'Position PIC',
            'Email PIC',
            'Mobile Phone',
            'Address',
            'City',
            'Country',
            'Postal Code',
            'Office Number',
            'Website',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Bold header dan center alignment
                $sheet->getStyle('A1:M1')->getFont()->setBold(true);
                $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Atur border untuk seluruh sel
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:M{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Merge cell sponsor:
                $rowIndex = 2;
                $startRow = 2;
                $previousSponsor = null;

                foreach ($this->rows as $index => $dataRow) {
                    $currentSponsor = $dataRow['company'];
                    if ($previousSponsor !== null && $previousSponsor !== $currentSponsor) {
                        if ($startRow < $rowIndex - 1) {
                            $sheet->mergeCells("A{$startRow}:A" . ($rowIndex - 1));
                            $sheet->mergeCells("B{$startRow}:B" . ($rowIndex - 1));
                            $sheet->mergeCells("C{$startRow}:C" . ($rowIndex - 1));
                            $sheet->mergeCells("H{$startRow}:H" . ($rowIndex - 1));
                            $sheet->mergeCells("I{$startRow}:I" . ($rowIndex - 1));
                            $sheet->mergeCells("J{$startRow}:J" . ($rowIndex - 1));
                            $sheet->mergeCells("K{$startRow}:K" . ($rowIndex - 1));
                            $sheet->mergeCells("L{$startRow}:L" . ($rowIndex - 1));
                            $sheet->mergeCells("M{$startRow}:M" . ($rowIndex - 1));
                        }
                        $startRow = $rowIndex;
                    }
                    $previousSponsor = $currentSponsor;
                    $rowIndex++;
                }
                if ($startRow < $rowIndex - 1) {
                    $sheet->mergeCells("A{$startRow}:A" . ($rowIndex - 1));
                    $sheet->mergeCells("B{$startRow}:B" . ($rowIndex - 1));
                    $sheet->mergeCells("C{$startRow}:C" . ($rowIndex - 1));
                    $sheet->mergeCells("H{$startRow}:H" . ($rowIndex - 1));
                    $sheet->mergeCells("I{$startRow}:I" . ($rowIndex - 1));
                    $sheet->mergeCells("J{$startRow}:J" . ($rowIndex - 1));
                    $sheet->mergeCells("K{$startRow}:K" . ($rowIndex - 1));
                    $sheet->mergeCells("L{$startRow}:L" . ($rowIndex - 1));
                    $sheet->mergeCells("M{$startRow}:M" . ($rowIndex - 1));
                }
            },
        ];
    }
}
