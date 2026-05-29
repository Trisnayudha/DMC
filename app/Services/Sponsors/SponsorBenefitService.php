<?php

namespace App\Services\Sponsors;

use App\Models\Sponsors\PackageBenefit;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBenefitUsage;
use Carbon\Carbon;

class SponsorBenefitService
{
    /**
     * Generate (atau backfill) benefit usage records untuk satu sponsor
     * berdasarkan contract_start dan contract_end mereka.
     *
     * Idempotent — aman dijalankan berulang kali, tidak akan duplikat.
     */
    public static function generateForSponsor(Sponsor $sponsor): int
    {
        if (!$sponsor->contract_start || !$sponsor->contract_end) {
            return 0;
        }

        $start = Carbon::createFromFormat('Y-m', $sponsor->contract_start)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $sponsor->contract_end)->startOfMonth();

        if ($end->lt($start)) {
            return 0;
        }

        $packageBenefits = PackageBenefit::where('package_name', $sponsor->package)->get();
        if ($packageBenefits->isEmpty()) {
            return 0;
        }

        $created = 0;
        $period  = $start->copy();

        while ($period->lte($end)) {
            $periodStr = $period->format('Y-m');

            foreach ($packageBenefits as $pb) {
                $new = SponsorBenefitUsage::firstOrCreate(
                    [
                        'sponsor_id' => $sponsor->id,
                        'benefit_id' => $pb->benefit_id,
                        'period'     => $periodStr,
                    ],
                    [
                        'status'  => 'unused',
                        'used_at' => null,
                    ]
                );

                if ($new->wasRecentlyCreated) {
                    $created++;
                }
            }

            $period->addMonth();
        }

        return $created;
    }

    /**
     * Generate benefit untuk semua sponsor aktif yang belum lengkap benefit-nya.
     * Dipakai oleh artisan command backfill.
     */
    public static function generateForAllActive(): array
    {
        $sponsors = Sponsor::where('status', 'publish')->get();
        $result   = ['sponsors' => 0, 'records' => 0];

        foreach ($sponsors as $sponsor) {
            $count = self::generateForSponsor($sponsor);
            if ($count > 0) {
                $result['sponsors']++;
                $result['records'] += $count;
            }
        }

        return $result;
    }
}
