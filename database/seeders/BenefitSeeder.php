<?php

namespace Database\Seeders;

use App\Models\Sponsors\Benefit;
use Illuminate\Database\Seeder;


class BenefitSeeder extends Seeder
{
    public function run()
    {
        $benefits = [
            // ================== LOGO PLACEMENT ==================
            [
                'name'        => 'Logo Placement on DMC Weekly Newsletter',
                'description' => 'Penempatan logo di newsletter mingguan DMC.',
                'category'    => 'LOGO_PLACEMENT',
            ],
            [
                'name'        => 'Logo Placement on EDM & Social Media Content',
                'description' => 'Penempatan logo pada EDM dan konten media sosial DMC (promosi, greetings).',
                'category'    => 'LOGO_PLACEMENT',
            ],
            [
                'name'        => 'Logo Placement on Onsite Event',
                'description' => 'Penempatan logo di lokasi acara (spanduk, backdrop, dsb).',
                'category'    => 'LOGO_PLACEMENT',
            ],
            [
                'name'        => 'Logo Placement on DMC Website',
                'description' => 'Penempatan logo di website resmi DMC.',
                'category'    => 'LOGO_PLACEMENT',
            ],
            [
                'name'        => 'Logo Placement in Program Book / Brochure Material',
                'description' => 'Penempatan logo di buku program atau brosur resmi DMC.',
                'category'    => 'LOGO_PLACEMENT',
            ],

            // ========== PROFILE PAGE ON DMC WEBSITE & MOBILE APP ==========
            [
                'name'        => 'Company Profile Page (Logo, Website, Profile)',
                'description' => 'Profil perusahaan di website & app DMC, termasuk logo, tagline, brand color.',
                'category'    => 'PROFILE_PAGE',
            ],
            [
                'name'        => 'Digital Brochure / Product Catalogue',
                'description' => 'Menampilkan brosur digital atau katalog produk di halaman sponsor.',
                'category'    => 'PROFILE_PAGE',
            ],
            [
                'name'        => 'Company Representative Profile',
                'description' => 'Menambahkan data perwakilan perusahaan di halaman sponsor.',
                'category'    => 'PROFILE_PAGE',
            ],
            [
                'name'        => 'Company Video & Activities',
                'description' => 'Memasang video atau dokumentasi aktivitas perusahaan pada profil sponsor.',
                'category'    => 'PROFILE_PAGE',
            ],
            [
                'name'        => 'Company Social Media Information',
                'description' => 'Pencantuman tautan media sosial perusahaan (Facebook, Instagram, LinkedIn, dsb).',
                'category'    => 'PROFILE_PAGE',
            ],

            // ================= EXPOSURE ON DMC NEWSLETTER =================
            [
                'name'        => 'Article / Press Release Announcement Inclusion',
                'description' => 'Pencantuman sponsor dalam artikel/press release DMC Newsletter.',
                'category'    => 'EXPOSURE_NEWSLETTER',
            ],
            [
                'name'        => 'Industry Event Participation Promotion',
                'description' => 'Promosi partisipasi event industri (banner disediakan sponsor).',
                'category'    => 'EXPOSURE_NEWSLETTER',
            ],
            [
                'name'        => 'Job Vacancies Promotion',
                'description' => 'Promosi lowongan kerja sponsor melalui newsletter/kanal DMC.',
                'category'    => 'EXPOSURE_NEWSLETTER',
            ],

            // ====================== EVENT ATTENDANCES ======================
            [
                'name'        => 'Complimentary Tickets - DMC Main Event',
                'description' => 'Tiket gratis untuk menghadiri main event DMC (jumlah tergantung paket).',
                'category'    => 'EVENT_ATTENDANCE',
            ],
            [
                'name'        => 'Complimentary Workshop Pass',
                'description' => 'Pass gratis untuk mengikuti workshop (jumlah tergantung paket).',
                'category'    => 'EVENT_ATTENDANCE',
            ],

            // =========== ADDITIONAL VALUE IN LIVE EVENT ===========
            [
                'name'        => 'Acknowledgement by MC / Moderator',
                'description' => 'Sponsor disebutkan secara khusus oleh MC/Moderator di panggung utama.',
                'category'    => 'ADDITIONAL_VALUE',
            ],
            [
                'name'        => 'Goodie Bag Promotional Inclusion',
                'description' => 'Sponsor dapat memasukkan merchandise/leaflet ke dalam goodie bag acara.',
                'category'    => 'ADDITIONAL_VALUE',
            ],
            [
                'name'        => 'Brand/Logo Projection (DMC Event Partnership)',
                'description' => 'Penayangan brand/logo sponsor di layar utama atau panggung acara.',
                'category'    => 'ADDITIONAL_VALUE',
            ],
            [
                'name'        => 'Video Participation (DMC Main Event)',
                'description' => 'Sponsor dapat memutar video singkat dalam rangkaian acara utama.',
                'category'    => 'ADDITIONAL_VALUE',
            ],
            [
                'name'        => 'Company Video on Stage',
                'description' => 'Penayangan video perusahaan di panggung utama (bisa berdurasi tertentu).',
                'category'    => 'ADDITIONAL_VALUE',
            ],

            // =============== ADVERTISEMENTS AND OTHERS ===============
            [
                'name'        => 'EDM & Social Media Content: Sponsor Announcement',
                'description' => 'Pengumuman sponsor di EDM & media sosial (full/medium/logo-only).',
                'category'    => 'ADVERTISEMENTS',
            ],
            [
                'name'        => 'DMC Annual Book Inclusion',
                'description' => 'Pencantuman profil/logo sponsor di buku tahunan DMC.',
                'category'    => 'ADVERTISEMENTS',
            ],
            [
                'name'        => 'Company Banner',
                'description' => 'Penempatan banner sponsor di area event atau platform DMC.',
                'category'    => 'ADVERTISEMENTS',
            ],
            [
                'name'        => 'Additional Promotional Items',
                'description' => 'Sponsor dapat menambahkan item promosi lain sesuai kesepakatan.',
                'category'    => 'ADVERTISEMENTS',
            ],
        ];

        foreach ($benefits as $benefit) {
            Benefit::create([
                'name'        => $benefit['name'],
                'description' => $benefit['description'],
                'category'    => $benefit['category'],
            ]);
        }
    }
}
