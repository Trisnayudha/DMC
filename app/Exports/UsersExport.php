<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export daftar user di halaman Users Management.
 *
 * Sengaja TIDAK memakai export bawaan DataTables, karena export DataTables
 * mengambil isi HTML tiap cell — termasuk tombol (Verify, Deactivate, dll) —
 * sehingga kolom Status Member jadi "Active Verified Deactivate".
 * Di sini kita ambil value asli dari tiap row, sama persis dengan yang dibaca blade.
 */
class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection */
    private $rows;

    /** @var bool */
    private $isUnregist;

    /** @var int running row number */
    private $no = 0;

    public function __construct($rows, bool $isUnregist = false)
    {
        $this->rows       = $rows;
        $this->isUnregist = $isUnregist;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        if ($this->isUnregist) {
            return [
                'No', 'Date', 'Name', 'Company', 'Job Title', 'Email',
                'Phone', 'Address', 'Category', 'Exported',
            ];
        }

        return [
            'No', 'Date Register', 'Name', 'Tier', 'Status Member', 'Job Title',
            'Company', 'Email', 'Phone', 'Office', 'Address', 'Website',
            'Category', 'WA Updates', 'Open to Sponsorship', 'Password',
        ];
    }

    public function map($row): array
    {
        $no = ++$this->no;

        if ($this->isUnregist) {
            return [
                $no,
                $this->formatDate($row->created_at),
                $row->name,
                $row->company_name,
                $row->job_title,
                $row->email,
                $row->fullphone ?? $row->phone,
                $row->address,
                $this->category($row),
                $row->exported_at ? $this->formatDate($row->exported_at) : 'No',
            ];
        }

        return [
            $no,
            $this->formatDate($row->user_created_at ?? $row->created_at),
            $row->name,
            $this->tierLabel($row->tier),
            $this->statusLabel($row->status_member),
            $row->job_title,
            $row->company_name,
            $row->email,
            $row->fullphone ?? $row->phone,
            $row->full_office_number,
            $row->address,
            $row->company_website,
            $this->category($row),
            strtolower(trim((string) $row->wa_updates)) === 'agree' ? 'Yes' : 'No',
            $row->explore ? 'Yes' : 'No',
            $row->password ? 'Set' : 'Not Set',
        ];
    }

    private function formatDate($value): string
    {
        return $value ? date('d M Y H:i', strtotime($value)) : '';
    }

    private function category($row): string
    {
        return (string) ($row->company_category === 'other'
            ? $row->company_other
            : $row->company_category);
    }

    private function tierLabel($tier): string
    {
        $tier = strtolower((string) ($tier ?? 'reguler'));
        if (!in_array($tier, ['reguler', 'black'])) {
            $tier = 'reguler';
        }
        return ucfirst($tier);
    }

    private function statusLabel($status): string
    {
        $status = strtolower((string) ($status ?? ''));
        if ($status === 'active')      return 'Active';
        if ($status === 'declined')    return 'Declined';
        if ($status === 'deactivated') return 'Deactivated';
        return 'Pending';
    }
}
