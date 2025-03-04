<?php

namespace Database\Seeders;

use App\Models\Sponsors\Benefit;
use App\Models\Sponsors\PackageBenefit;
use Illuminate\Database\Seeder;


class PackageBenefitSeeder extends Seeder
{
    public function run()
    {
        /**
         * Mapping paket sponsor ke daftar benefit.
         * Pastikan 'benefit' di sini sesuai dengan nilai kolom 'name' di tabel benefits.
         */
        $packageBenefits = [
            'platinum' => [
                // LOGO PLACEMENT
                [
                    'benefit'         => 'Logo Placement on DMC Weekly Newsletter',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on EDM & Social Media Content',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on Onsite Event',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on DMC Website',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement in Program Book / Brochure Material',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // PROFILE PAGE
                [
                    'benefit'         => 'Company Profile Page (Logo, Website, Profile)',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Digital Brochure / Product Catalogue',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Company Representative Profile',
                    'quantity'        => 3,
                    'additional_info' => 'Max 3 profiles & links to contact info',
                ],
                [
                    'benefit'         => 'Company Video & Activities',
                    'quantity'        => 1,
                    'additional_info' => '1 video upload allowed',
                ],
                [
                    'benefit'         => 'Company Social Media Information',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // EXPOSURE ON DMC NEWSLETTER
                [
                    'benefit'         => 'Article / Press Release Announcement Inclusion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Industry Event Participation Promotion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Job Vacancies Promotion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // EVENT ATTENDANCE
                [
                    'benefit'         => 'Complimentary Tickets - DMC Main Event',
                    'quantity'        => 3,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Complimentary Workshop Pass',
                    'quantity'        => 2,
                    'additional_info' => null,
                ],
                // ADDITIONAL VALUE IN LIVE EVENT
                [
                    'benefit'         => 'Acknowledgement by MC / Moderator',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Goodie Bag Promotional Inclusion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Brand/Logo Projection (DMC Event Partnership)',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Video Participation (DMC Main Event)',
                    'quantity'        => 1,
                    'additional_info' => 'Slot video max 2 minutes',
                ],
                [
                    'benefit'         => 'Company Video on Stage',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // ADVERTISEMENTS & OTHERS
                [
                    'benefit'         => 'EDM & Social Media Content: Sponsor Announcement',
                    'quantity'        => 1,
                    'additional_info' => 'Full Profile Inclusion',
                ],
                [
                    'benefit'         => 'DMC Annual Book Inclusion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Company Banner',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Additional Promotional Items',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
            ],

            'gold' => [
                // LOGO PLACEMENT
                [
                    'benefit'         => 'Logo Placement on DMC Weekly Newsletter',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on EDM & Social Media Content',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on Onsite Event',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on DMC Website',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement in Program Book / Brochure Material',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // PROFILE PAGE
                [
                    'benefit'         => 'Company Profile Page (Logo, Website, Profile)',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Digital Brochure / Product Catalogue',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Company Representative Profile',
                    'quantity'        => 2,
                    'additional_info' => 'Max 2 profiles & links to contact info',
                ],
                [
                    'benefit'         => 'Company Video & Activities',
                    'quantity'        => 1,
                    'additional_info' => '1 video upload allowed',
                ],
                [
                    'benefit'         => 'Company Social Media Information',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // EXPOSURE ON DMC NEWSLETTER
                [
                    'benefit'         => 'Article / Press Release Announcement Inclusion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Industry Event Participation Promotion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Job Vacancies Promotion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // EVENT ATTENDANCE
                [
                    'benefit'         => 'Complimentary Tickets - DMC Main Event',
                    'quantity'        => 2,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Complimentary Workshop Pass',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // ADDITIONAL VALUE IN LIVE EVENT
                [
                    'benefit'         => 'Acknowledgement by MC / Moderator',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Goodie Bag Promotional Inclusion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Brand/Logo Projection (DMC Event Partnership)',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Video Participation (DMC Main Event)',
                    'quantity'        => 1,
                    'additional_info' => 'Slot video max 1 minute',
                ],
                // (Untuk Gold, kita asumsikan Company Video on Stage tidak disediakan)
                // ADVERTISEMENTS & OTHERS
                [
                    'benefit'         => 'EDM & Social Media Content: Sponsor Announcement',
                    'quantity'        => 1,
                    'additional_info' => 'Medium Profile Inclusion',
                ],
                [
                    'benefit'         => 'DMC Annual Book Inclusion',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Company Banner',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Additional Promotional Items',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
            ],

            'silver' => [
                // LOGO PLACEMENT
                [
                    'benefit'         => 'Logo Placement on DMC Weekly Newsletter',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on EDM & Social Media Content',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on Onsite Event',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement on DMC Website',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Logo Placement in Program Book / Brochure Material',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // PROFILE PAGE (Silver mendapatkan tampilan dasar)
                [
                    'benefit'         => 'Company Profile Page (Logo, Website, Profile)',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                [
                    'benefit'         => 'Company Representative Profile',
                    'quantity'        => 1,
                    'additional_info' => 'Max 1 profile & link to contact info',
                ],
                // ADVERTISEMENTS (Silver mendapatkan tampilan logo saja)
                [
                    'benefit'         => 'EDM & Social Media Content: Sponsor Announcement',
                    'quantity'        => 1,
                    'additional_info' => 'Logo only inclusion',
                ],
                [
                    'benefit'         => 'DMC Annual Book Inclusion',
                    'quantity'        => 1,
                    'additional_info' => 'Logo inclusion only',
                ],
                [
                    'benefit'         => 'Company Banner',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
                // EVENT ATTENDANCE
                [
                    'benefit'         => 'Complimentary Tickets - DMC Main Event',
                    'quantity'        => 1,
                    'additional_info' => null,
                ],
            ],
        ];

        // Loop setiap paket dan benefit, kemudian masukkan ke tabel package_benefit
        foreach ($packageBenefits as $packageName => $benefits) {
            foreach ($benefits as $b) {
                // Cari data Benefit berdasarkan nama
                $benefitModel = Benefit::where('name', $b['benefit'])->first();

                if ($benefitModel) {
                    PackageBenefit::create([
                        'package_name'    => $packageName,
                        'benefit_id'      => $benefitModel->id,
                        'quantity'        => $b['quantity'] ?? 1,
                        'additional_info' => $b['additional_info'] ?? null,
                    ]);
                }
            }
        }
    }
}
