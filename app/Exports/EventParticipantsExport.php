<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export peserta event (custom, bukan export bawaan DataTables).
 *
 * Ambil value asli tiap peserta + Subcategory perusahaan (kalau ada).
 * Kolom tombol (Aksi / assign Sponsor / checkbox Mining) tidak diikutkan;
 * Sponsor & Mining diekspor sebagai value (nama sponsor / Yes-No).
 */
class EventParticipantsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var Collection */
    private $rows;

    /** @var Collection company_id => "Sub A, Sub B" */
    private $subcategoryMap;

    /** @var int */
    private $no = 0;

    public function __construct($rows)
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
        $this->subcategoryMap = $this->loadSubcategories($this->rows);
    }

    /**
     * Ambil semua subcategory sekaligus (hindari N+1), grouped per company_id.
     */
    private function loadSubcategories($rows)
    {
        $companyIds = $rows->pluck('company_id_ref')->filter()->unique()->values();
        if ($companyIds->isEmpty()) {
            return collect();
        }

        return DB::table('company_subcategory_company as pivot')
            ->join('company_subcategories as sc', 'sc.id', '=', 'pivot.company_subcategory_id')
            ->whereIn('pivot.company_id', $companyIds->all())
            ->orderBy('sc.sort_order')
            ->select('pivot.company_id', 'sc.name')
            ->get()
            ->groupBy('company_id')
            ->map(function ($group) {
                return $group->pluck('name')->implode(', ');
            });
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Date Reg',
            'Code',
            'Package',
            'Name',
            'Job Title',
            'Company',
            'Category',
            'Subcategory',
            'Email',
            'Phone',
            'Office',
            'Address',
            'Status',
            'PIC',
            'Sponsor',
            'Mining',
            'Referral',
        ];
    }

    public function map($row): array
    {
        $no = ++$this->no;

        $package = $row->package;
        if (!empty($row->mobile)) {
            $package .= ' (Mobile)';
        }

        $category = $row->company_category === 'other'
            ? $row->company_other
            : $row->company_category;

        return [
            $no,
            $row->register ? date('d M Y H:i', strtotime($row->register)) : '',
            $row->code_payment,
            $package,
            $row->name,
            $row->job_title,
            $row->company_name,
            $category,
            $this->subcategoryMap->get($row->company_id_ref, ''),
            $row->email,
            $row->fullphone ?? $row->phone,
            $row->full_office_number,
            $row->address,
            $row->status_registration,
            $row->pic_name ?: 'System',
            $row->sponsor_name ?: '',
            !empty($row->is_mining) ? 'Yes' : 'No',
            $row->referral ?: '',
        ];
    }
}
