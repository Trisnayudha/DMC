<?php

namespace App\Console\Commands;

use App\Models\MemberLeadFollowUp;
use App\Services\LeadTemperatureService;
use Illuminate\Console\Command;

/**
 * Recompute every lead's temperature so the auto categories (attendance,
 * email engagement) catch up with data that arrived outside the Lead
 * Follow-Up page. Run once after the migration, then daily via the scheduler.
 * Safe to re-run: auto boxes only ever go false → true, manual boxes are
 * left as the PIC set them.
 */
class RescoreLeadTemperature extends Command
{
    protected $signature = 'leads:rescore-temperature';
    protected $description = 'Hitung ulang temperature (HOT/WARM/COLD) semua Lead Follow-Up';

    public function handle()
    {
        $counts = ['hot' => 0, 'warm' => 0, 'cold' => 0];

        MemberLeadFollowUp::with('user')->chunkById(200, function ($leads) use (&$counts) {
            foreach ($leads as $lead) {
                LeadTemperatureService::recompute($lead);
                $counts[$lead->temperature]++;
            }
        });

        $this->info("HOT {$counts['hot']} · WARM {$counts['warm']} · COLD {$counts['cold']}");

        return 0;
    }
}
