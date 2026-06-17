<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CompanyDatabaseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private $groups;

    public function __construct($groups)
    {
        $this->groups = $groups;
    }

    public function collection()
    {
        return $this->groups;
    }

    public function headings(): array
    {
        return [
            'Company Name',
            'Prefix',
            'Website',
            'Category',
            'Address',
            'City',
            'Postal Code',
            'Office Number',
            'Country',
            'Verified',
            'Total Records',
            'Completeness',
        ];
    }

    public function map($row): array
    {
        return [
            $row->best_values['company_name'] ?? $row->company_name,
            $row->best_values['prefix'] ?? '',
            $row->best_values['company_website'] ?? '',
            $row->best_values['company_category'] ?? '',
            $row->best_values['address'] ?? '',
            $row->best_values['city'] ?? '',
            $row->best_values['portal_code'] ?? '',
            $row->best_values['full_office_number'] ?? '',
            $row->best_values['country'] ?? '',
            $row->is_verified ? 'Yes' : 'No',
            $row->total_records,
            $row->best_score . '/' . $row->max_score,
        ];
    }
}
