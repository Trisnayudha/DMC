<?php

namespace App\Exports;

use App\Services\Sponsors\SponsorContactRowBuilder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SponsorContactDirectoryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    const ROLE_LABELS = [
        'pic'            => 'Primary Contact (PIC)',
        'cc'             => 'CC',
        'billing'        => 'Billing',
        'representative' => 'Representative Profile',
    ];

    protected $sponsors;
    protected $roles;

    /**
     * @param \Illuminate\Support\Collection $sponsors sponsor dengan contactRows yang sudah ter-dedup
     * @param array $roles subset of pic|cc|billing|representative; empty = all contacts
     */
    public function __construct($sponsors, array $roles = [])
    {
        $this->sponsors = $sponsors;
        $this->roles = $roles;
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
                $contactRoles = self::contactRoles($contact);

                // Satu kontak masuk sekali walau cocok dengan beberapa tipe terpilih.
                $shownRoles = $this->roles ? array_values(array_intersect($contactRoles, $this->roles)) : $contactRoles;
                if ($this->roles && !$shownRoles) {
                    continue;
                }

                $rows->push([
                    'Sponsor'          => $sponsor->branding_name ?: $sponsor->name,
                    'Package'          => sponsor_package_label($sponsor->package),
                    'Name'             => $contact['name'] ?? '-',
                    'Role'             => implode(', ', array_map(function ($type) { return self::ROLE_LABELS[$type]; }, $shownRoles)),
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

    /** Semua tipe yang dimiliki satu baris kontak (bisa lebih dari satu). */
    public static function contactRoles(array $contact): array
    {
        $roles = [];
        foreach (array_keys(self::ROLE_LABELS) as $type) {
            if ($contact['role'] === $type || SponsorContactRowBuilder::sourceIds($contact, $type)) {
                $roles[] = $type;
            }
        }
        return $roles;
    }
}
