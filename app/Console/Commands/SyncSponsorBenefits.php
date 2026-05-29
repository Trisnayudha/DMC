<?php

namespace App\Console\Commands;

use App\Models\Sponsors\Sponsor;
use App\Services\Sponsors\SponsorBenefitService;
use Illuminate\Console\Command;

class SyncSponsorBenefits extends Command
{
    protected $signature   = 'sponsors:sync-benefits {--id= : Sync hanya satu sponsor by ID}';
    protected $description = 'Generate / backfill benefit usage records untuk sponsor aktif berdasarkan periode kontrak';

    public function handle(): int
    {
        $sponsorId = $this->option('id');

        if ($sponsorId) {
            $sponsor = Sponsor::find($sponsorId);
            if (!$sponsor) {
                $this->error("Sponsor ID {$sponsorId} tidak ditemukan.");
                return 1;
            }
            $count = SponsorBenefitService::generateForSponsor($sponsor);
            $this->info("Sponsor [{$sponsor->name}]: {$count} record benefit dibuat.");
            return 0;
        }

        $this->info('Memulai sync benefit untuk semua sponsor aktif...');
        $result = SponsorBenefitService::generateForAllActive();
        $this->info("Selesai. {$result['sponsors']} sponsor diproses, {$result['records']} record benefit baru dibuat.");

        return 0;
    }
}
