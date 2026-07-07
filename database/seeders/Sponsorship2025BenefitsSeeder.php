<?php

namespace Database\Seeders;

use App\Models\Sponsors\Benefit;
use App\Models\Sponsors\PackageBenefit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menyelaraskan benefit + mapping paket dengan "DMC 2025 Sponsorship Packages"
 * (Major/Platinum, Gold, Silver). Idempotent: benefit di-upsert by (name, category),
 * lalu package_benefit dibangun ulang. Tidak menghapus baru benefit lama (agar
 * referensi sponsor_benefit_usage tetap aman) — hanya me-reset mapping paket.
 *
 * Format tiap item: [name, desc, packages].
 *   packages: ['platinum'=>[qty, info], 'gold'=>[...], 'silver'=>[...]]
 *   Paket yang tidak disebut = tidak termasuk (tampil "-" di tabel paket).
 */
class Sponsorship2025BenefitsSeeder extends Seeder
{
    public function run()
    {
        $catalog = [
            'Logo Placement' => [
                ['DMC Weekly Newsletter', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['EDM & Social Media Content', 'Program Promotion, Holiday Greetings', ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Onsite Event', 'Promotional Materials', ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
            ],
            'Profile Page on DMC Website and Mobile App' => [
                ['Company Logo', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Company Website', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Company Profile', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Company Social Media Information', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Digital Brochure or Product Catalogue', null, ['platinum' => [1, 'Max. 10'], 'gold' => [1, 'Max. 3'], 'silver' => [1, 'Max. 1']]],
                ['Company Representative Profile', null, ['platinum' => [1, 'Max. 8 Profiles & Linkable to Email Address for Meeting Arrangement or Inquiry'], 'gold' => [1, 'Max. 5 Profiles & Linkable to Email Address for Meeting Arrangement or Inquiry'], 'silver' => [1, 'Max. 3 Profiles & Linkable to Email Address for Meeting Arrangement or Inquiry']]],
                ['Office Location Information', null, ['platinum' => [5, null], 'gold' => [3, null], 'silver' => [1, null]]],
                ['Company Video & Activities', null, ['platinum' => [1, null], 'gold' => [1, null]]],
            ],
            'Exposure on DMC Newsletter' => [
                ['Article, Press Release or Announcement Inclusion', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Industry Event Participation Promotion', 'Banner Provided by Sponsor', ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Job Vacancies', null, ['platinum' => [3, null], 'gold' => [2, null], 'silver' => [1, null]]],
            ],
            'Event Attendance' => [
                ['Complimentary Tickets - DMC Main Event', null, ['platinum' => [5, null], 'gold' => [2, null], 'silver' => [1, null]]],
                ['Access to Event Report', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['List of Attendees - DMC Main Event', null, ['platinum' => [1, null]]],
                ['Registration Priorities - DMC Event Partnership', null, ['platinum' => [1, null]]],
                ['Complimentary Ticket - Industrial Event Partnership', null, ['platinum' => [1, null]]],
            ],
            'Additional Value in Live Event' => [
                ['Announcement by MC / Moderator', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Goodie Bag Promotional Insertion', 'Per Event', ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Digital Brochure or Product Catalogue Display', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Gift/ Souvenir Distribution', 'DMC Event Partnership', ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Video Participation and Interview Sessions - Industrial Event Partnership', null, ['platinum' => [1, null], 'gold' => [1, null]]],
                ['Company Standing Banner - DMC Main Event', 'Provided by The Sponsor', ['platinum' => [1, null]]],
            ],
            'Advertisements and Others' => [
                ['EDM & Social Media Content - New Sponsor Announcement', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Profile Inclusion on DMC Annual Book', null, ['platinum' => [1, 'Full Profile Inclusion'], 'gold' => [1, 'Limited Profile Inclusion'], 'silver' => [1, 'Company Logo Insertion']]],
                ['Social Media Engagement / Interaction', null, ['platinum' => [1, null], 'gold' => [1, null], 'silver' => [1, null]]],
                ['Advertisement on DMC Commodity Map', null, ['platinum' => [1, 'All DMC Commodity Map'], 'gold' => [1, '1x']]],
                ['1 Month Website Advertisement', null, ['platinum' => [1, null]]],
            ],
        ];

        DB::transaction(function () use ($catalog) {
            $benefitIds = [];

            // 1) Upsert benefit rows (jaga referensi lama: match by name+category).
            foreach ($catalog as $category => $items) {
                foreach ($items as $item) {
                    [$name, $desc, $packages] = $item;

                    $benefit = Benefit::firstOrNew(['name' => $name, 'category' => $category]);
                    $benefit->description = $desc;
                    $benefit->save();

                    $benefitIds[] = $benefit->id;

                    // 2) Reset mapping paket untuk benefit ini, lalu bangun ulang.
                    PackageBenefit::where('benefit_id', $benefit->id)->delete();
                    foreach ($packages as $pkg => [$qty, $info]) {
                        PackageBenefit::create([
                            'package_name'    => $pkg,
                            'benefit_id'      => $benefit->id,
                            'quantity'        => $qty,
                            'additional_info' => $info,
                        ]);
                    }
                }
            }

            // 3) Benefit lama yang TIDAK ada di katalog 2025: lepas dari paket manapun
            //    (jangan dihapus benefit-nya agar sponsor_benefit_usage tetap valid).
            PackageBenefit::whereNotIn('benefit_id', $benefitIds)->delete();
        });
    }
}
