<?php

namespace App\Console\Commands;

use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewalForm;
use Illuminate\Console\Command;

/**
 * Sebelum flow dipisah, "renewal form" ditandai oleh follow-up pertama yang berisi
 * KMK rate. Command ini membuat record sponsor_renewal_forms dari data lama tersebut
 * supaya sponsor yang siklus follow-up-nya sedang berjalan tidak ikut ter-gate.
 */
class BackfillSponsorRenewalForms extends Command
{
    protected $signature = 'sponsor:backfill-renewal-forms';
    protected $description = 'Backfill sponsor_renewal_forms from existing follow-ups that carry a KMK rate';

    public function handle()
    {
        // Follow-up pertama (ada KMK) per sponsor+tahun = penanda form lama.
        $seeds = SponsorFollowup::whereNotNull('kmk_rate')
            ->orderBy('followed_up_at')
            ->orderBy('id')
            ->get()
            ->groupBy(function ($f) {
                return $f->sponsor_id . '-' . $f->renewal_year;
            });

        $created = 0;

        foreach ($seeds as $group) {
            $seed = $group->first();

            $exists = SponsorRenewalForm::where('sponsor_id', $seed->sponsor_id)
                ->where('renewal_year', $seed->renewal_year)
                ->exists();

            if ($exists) {
                continue;
            }

            $year   = (int) $seed->renewal_year;
            $number = SponsorRenewalForm::generateFormNumber($year);

            // Ambil nilai proposal dari kontrak berjalan sponsor, jika ada.
            $sponsor = $seed->sponsor;
            $current = $sponsor ? $sponsor->currentRenewal : null;

            SponsorRenewalForm::create([
                'sponsor_id'   => $seed->sponsor_id,
                'renewal_year' => $year,
                'form_number'  => $number,
                'kmk_rate'     => $seed->kmk_rate,
                'amount_usd'   => $current ? $current->amount_usd : null,
                'amount_idr'   => $current ? $current->amount_idr : null,
                'notes'        => 'Backfilled from follow-up history.',
                'generated_at' => $seed->followed_up_at,
                'created_by'   => $seed->created_by,
            ]);

            $created++;
        }

        $this->info("Backfill sponsor renewal forms completed. Created: {$created}.");
    }
}
