<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CompanyDatabaseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private $groups;
    private $subcategoryNames; // id => name

    public function __construct($groups)
    {
        $this->groups = $groups;

        // Resolve semua id subcategory jadi nama sekali saja (hindari N+1 saat mapping).
        $ids = collect($groups)
            ->flatMap(fn($g) => $g->best_values['subcategory_ids'] ?? [])
            ->unique()
            ->values();

        $this->subcategoryNames = $ids->isEmpty()
            ? collect()
            : DB::table('company_subcategories')->whereIn('id', $ids->all())->pluck('name', 'id');
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
            'Subcategories',
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
        $subcategories = collect($row->best_values['subcategory_ids'] ?? [])
            ->map(fn($id) => $this->subcategoryNames->get($id))
            ->filter()
            ->implode(', ');

        return [
            $row->best_values['company_name'] ?? $row->company_name,
            $row->best_values['prefix'] ?? '',
            $row->best_values['company_website'] ?? '',
            $row->best_values['company_category'] ?? '',
            $subcategories,
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
