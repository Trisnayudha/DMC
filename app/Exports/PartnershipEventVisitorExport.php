<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartnershipEventVisitorExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private Collection $visitors;
    private Collection $memberEmails;

    public function __construct(Collection $visitors, Collection $memberEmails)
    {
        $this->visitors = $visitors;
        $this->memberEmails = $memberEmails;
    }

    public function collection()
    {
        return $this->visitors->values()->map(function ($visitor, $i) {
            $isMember = $visitor->business_email
                && $this->memberEmails->contains(strtolower(trim($visitor->business_email)));

            return [
                $i + 1,
                $visitor->company_name,
                $visitor->name,
                $visitor->job_title,
                $visitor->business_email,
                $visitor->mobile_number,
                $visitor->office_number,
                $visitor->website,
                $visitor->address,
                $visitor->remarks,
                $visitor->merchandise,
                $isMember ? 'Member' : 'Visitor',
            ];
        });
    }

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
            'Membership',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CC0000']],
        ]);

        return [];
    }
}
