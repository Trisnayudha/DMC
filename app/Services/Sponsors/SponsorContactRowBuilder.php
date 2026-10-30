<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\Sponsor;
use Illuminate\Support\Collection;

/**
 * Satu baris per orang: PIC, billing, representative, dan member digabung.
 * Identitas baris ditentukan email (lowercase+trim); tanpa email tidak
 * pernah di-dedup. Member dimasukkan lebih dulu agar datanya menang,
 * field kosong diisi dari sumber lain, dan role berprioritas tertinggi
 * (pic > billing > representative) yang menempel di baris gabungan.
 * 'sources' mencatat record asal tiap baris (dipakai edit di Contact Directory).
 */
class SponsorContactRowBuilder
{
    const ROLE_PRIORITY = [
        'pic'            => 1,
        'billing'        => 2,
        'representative' => 3,
    ];

    public function build(Sponsor $sponsor): Collection
    {
        $rows = collect();

        foreach ($sponsor->members as $member) {
            $this->addContact($rows, [
                'name'      => $member->name,
                'title'     => $member->job_title,
                'email'     => $member->email,
                'phone'     => $member->fullphone,
                'instagram' => null,
                'linkedin'  => null,
                'role'      => 'representative',
                'user_id'   => $member->id,
                'sources'   => [['type' => 'member', 'id' => $member->id]],
            ]);
        }

        foreach ($sponsor->pics as $pic) {
            $this->addContact($rows, [
                'name'      => $pic->name,
                'title'     => $pic->title,
                'email'     => $pic->email,
                'phone'     => $pic->phone,
                'instagram' => null,
                'linkedin'  => null,
                'role'      => 'pic',
                'user_id'   => null,
                'sources'   => [['type' => 'pic', 'id' => $pic->id]],
            ]);
        }

        foreach ($sponsor->billings as $billing) {
            $this->addContact($rows, [
                'name'      => $billing->name,
                'title'     => $billing->title,
                'email'     => $billing->email,
                'phone'     => $billing->phone,
                'instagram' => null,
                'linkedin'  => null,
                'role'      => 'billing',
                'user_id'   => null,
                'sources'   => [['type' => 'billing', 'id' => $billing->id]],
            ]);
        }

        foreach ($sponsor->representatives as $rep) {
            $this->addContact($rows, [
                'name'      => $rep->name,
                'title'     => $rep->job_title,
                'email'     => $rep->email,
                'phone'     => null,
                'instagram' => $rep->instagram,
                'linkedin'  => $rep->linkedin,
                'role'      => 'representative',
                'user_id'   => null,
                'sources'   => [['type' => 'representative', 'id' => $rep->id]],
            ]);
        }

        return $rows
            ->sortBy(function ($row) {
                return [self::ROLE_PRIORITY[$row['role']], strtolower($row['name'] ?? '')];
            })
            ->values();
    }

    private function addContact(Collection $rows, array $contact): void
    {
        $emailKey = strtolower(trim($contact['email'] ?? ''));

        if ($emailKey !== '') {
            $existingIndex = $rows->search(function ($row) use ($emailKey) {
                return $row['email_key'] === $emailKey;
            });

            if ($existingIndex !== false) {
                $existing = $rows[$existingIndex];
                foreach (['name', 'title', 'email', 'phone', 'instagram', 'linkedin', 'user_id'] as $field) {
                    if (empty($existing[$field]) && !empty($contact[$field])) {
                        $existing[$field] = $contact[$field];
                    }
                }
                $existing['sources'] = array_merge($existing['sources'], $contact['sources']);
                if (self::ROLE_PRIORITY[$contact['role']] < self::ROLE_PRIORITY[$existing['role']]) {
                    $existing['role'] = $contact['role'];
                }
                $rows[$existingIndex] = $existing;
                return;
            }
        }

        $contact['email_key'] = $emailKey;
        $rows->push($contact);
    }

    /** ID record sumber per tipe dari satu baris, mis. sourceIds($row, 'pic'). */
    public static function sourceIds(array $row, string $type): array
    {
        $ids = [];
        foreach ($row['sources'] ?? [] as $source) {
            if ($source['type'] === $type) {
                $ids[] = $source['id'];
            }
        }
        return $ids;
    }
}
