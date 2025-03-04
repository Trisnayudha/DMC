<?php

namespace Database\Seeders;

use App\Models\Sponsors\PackageBenefit;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBenefitUsage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;


class SponsorBenefitUsageSeeder extends Seeder
{
    public function run()
    {
        // Ambil sponsor aktif (publish) saja
        $sponsors = Sponsor::where('status', 'publish')->get();

        foreach ($sponsors as $sponsor) {
            // Menggunakan format "Y-m" untuk membuat instance Carbon
            $contractStart = $sponsor->contract_start
                ? Carbon::createFromFormat('Y-m', $sponsor->contract_start)
                : Carbon::now();
            $contractEnd = $sponsor->contract_end
                ? Carbon::createFromFormat('Y-m', $sponsor->contract_end)
                : Carbon::now();

            // Pastikan contract_end tidak kurang dari contract_start
            if ($contractEnd->lt($contractStart)) {
                continue;
            }

            // Loop setiap bulan dari contract_start hingga contract_end
            $period = $contractStart->copy();
            while ($period->lte($contractEnd)) {
                $periodStr = $period->format('Y-m');

                // Ambil benefit yang sesuai untuk paket sponsor tersebut
                $packageBenefits = PackageBenefit::where('package_name', $sponsor->package)->get();

                foreach ($packageBenefits as $packageBenefit) {
                    SponsorBenefitUsage::create([
                        'sponsor_id' => $sponsor->id,
                        'benefit_id' => $packageBenefit->benefit_id,
                        'status'     => 'unused',
                        'used_at'    => null,
                        'period'     => $periodStr,
                    ]);
                }

                // Pindah ke bulan berikutnya
                $period->addMonth();
            }
        }
    }
}
