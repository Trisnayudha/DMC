<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SponsorContactDirectoryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    const ROLE_LABELS = [
        'pic'            => 'Primary Contact (PIC)',
        'billing'        => 'Billing',
        'representative' => 'Representative Profile',
    ];

    protected $sponsors;
    protected $role;

    /**
     * @param \Illuminate\Support\Collection $sponsors sponsor dengan contactRows yang sudah ter-dedup
     * @param string $role pic|billing|representative|all
     */
    public function __construct($sponsors, $role = 'all')
    {
        $this->sponsors = $sponsors;
        $this->role = $role;
    }

    public function headings(): array
    {
        return [
            'Sponsor', 'Package', 'Name', 'Role',
            'Title / Position', 'Email', 'Phone', 'Instagram', 'LinkedIn',
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->sponsors as $sponsor) {
            foreach ($sponsor->contactRows as $contact) {
                if ($this->role !== 'all' && $contact['role'] !== $this->role) {
                    continue;
                }

                $rows->push([
                    'Sponsor'          => $sponsor->name,
                    'Package'          => ucfirst($sponsor->package),
                    'Name'             => $contact['name'] ?? '-',
                    'Role'             => self::ROLE_LABELS[$contact['role']] ?? $contact['role'],
                    'Title / Position' => $contact['title'] ?: '-',
                    'Email'            => $contact['email'] ?: '-',
                    'Phone'            => $contact['phone'] ?: '-',
                    'Instagram'        => $contact['instagram'] ?: '-',
                    'LinkedIn'         => $contact['linkedin'] ?: '-',
                ]);
            }
        }

        return $rows;
    }
}
