<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorRenewal;
use Carbon\Carbon;

class BackfillSponsorRenewals extends Command
{
    protected $signature = 'sponsor:backfill-renewals';
    protected $description = 'Backfill existing sponsors into sponsor_renewals table';

    public function handle()
    {
        $sponsors = Sponsor::whereNotNull('contract_start')
            ->whereNotNull('contract_end')
            ->get();

        foreach ($sponsors as $sponsor) {
            $exists = SponsorRenewal::where('sponsor_id', $sponsor->id)
                ->where('contract_start', $sponsor->contract_start)
                ->where('contract_end', $sponsor->contract_end)
                ->exists();

            if (!$exists) {
                SponsorRenewal::create([
                    'sponsor_id'      => $sponsor->id,
                    'renewal_year'    => Carbon::createFromFormat('Y-m', $sponsor->contract_start)->year,
                    'contract_start'  => $sponsor->contract_start,
                    'contract_end'    => $sponsor->contract_end,
                    'package'         => $sponsor->package,
                    'renewal_status'  => 'renewed',
                    'is_current'      => 1,
                ]);
            }
        }

        $this->info('Backfill sponsor renewals completed.');
    }
}
