<?php

namespace App\Exports;

use App\Models\Sponsors\Sponsor;
use App\Services\Sponsors\SponsorAddressParser;
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
            ->with(['pics', 'ccContacts', 'billings'])
            ->get();

        $this->rows = collect();
        $addressParser = new SponsorAddressParser();

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

            // Tabel sponsors tidak punya kolom city/country/postal_code, jadi diambil dari address
            $location = $addressParser->parse($sponsor->address);

            // One row per contact position, keeping all contacts when role counts differ.
            $pics = $sponsor->pics->values();
            $ccs = $sponsor->ccContacts->values();
            $billings = $sponsor->billings->values();
            $rowCount = max(1, $pics->count(), $ccs->count(), $billings->count());
            for ($index = 0; $index < $rowCount; $index++) {
                $pic = $pics->get($index);
                $cc = $ccs->get($index);
                $billing = $billings->get($index);
                $this->rows->push([
                    'type_of_sponsor' => $sponsor->package,
                    'sponsor_period' => $period,
                    'company' => $sponsor->name,
                    'name_pic' => $pic->name ?? '',
                    'position_pic' => $pic->title ?? '',
                    'email_pic' => $pic->email ?? '',
                    'mobile_phone' => $pic->phone ?? '',
                    'address' => $sponsor->address,
                    'city' => $location['city'],
                    'country' => $location['country'],
                    'postal_code' => $location['postal_code'],
                    'office_number' => $sponsor->office_number ?? '',
                    'website' => $sponsor->company_website,
                    'name_cc' => $cc ? trim(($cc->salutation ?? '') . ' ' . $cc->name) : '',
                    'position_cc' => $cc->title ?? '',
                    'email_cc' => $cc->email ?? '',
                    'phone_cc' => $cc->phone ?? '',
                    'name_billing' => $billing ? trim(($billing->salutation ?? '') . ' ' . $billing->name) : '',
                    'position_billing' => $billing->title ?? '',
                    'email_billing' => $billing->email ?? '',
                    'phone_billing' => $billing->phone ?? '',
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
            'Name CC',
            'Position CC',
            'Email CC',
            'Phone CC',
            'Name Billing',
            'Position Billing',
            'Email Billing',
            'Phone Billing',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Bold header dan center alignment
                $sheet->getStyle('A1:U1')->getFont()->setBold(true);
                $sheet->getStyle('A1:U1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Atur border untuk seluruh sel
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:U{$highestRow}")->applyFromArray([
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
