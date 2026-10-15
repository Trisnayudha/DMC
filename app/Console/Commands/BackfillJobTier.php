<?php

namespace App\Console\Commands;

use App\Models\Profiles\ProfileModel;
use App\Support\JobTierClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Hitung ulang profiles.job_tier dari job_title untuk member active yang
 * BELUM di-override manual oleh admin; profile non-active dikosongkan.
 * Aman dijalankan berulang — mis. setelah rule di JobTierClassifier diubah.
 */
class BackfillJobTier extends Command
{
    protected $signature = 'profiles:backfill-job-tier {--dry-run : Tampilkan distribusi tier tanpa menyimpan}';
    protected $description = 'Isi / hitung ulang profiles.job_tier dari job_title untuk member active (kecuali override manual)';

    public function handle()
    {
        if (!ProfileModel::hasJobTierColumn()) {
            $this->error('Kolom profiles.job_tier belum ada — jalankan migration dulu.');
            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $distribution = array_fill_keys(array_keys(JobTierClassifier::TIERS), 0);
        $changed = 0;

        $activeUserIds = DB::table('users')->where('status_member', 'active')->select('id');

        // Job tier hanya untuk member active — kosongkan sisanya.
        $nonActive = DB::table('profiles')
            ->whereNotIn('users_id', $activeUserIds)
            ->where(function ($q) {
                $q->whereNotNull('job_tier')->orWhere('job_tier_manual', true);
            });
        $cleared = (clone $nonActive)->count();
        if (!$dryRun && $cleared > 0) {
            $nonActive->update(['job_tier' => null, 'job_tier_manual' => false]);
        }

        DB::table('profiles')
            ->whereIn('users_id', $activeUserIds)
            ->where('job_tier_manual', false)
            ->select('id', 'job_title', 'job_tier')
            ->chunkById(500, function ($rows) use ($dryRun, &$distribution, &$changed) {
                $idsByTier = [];
                foreach ($rows as $row) {
                    $tier = JobTierClassifier::classify($row->job_title);
                    $distribution[$tier]++;
                    if ((int) $row->job_tier !== $tier) {
                        $idsByTier[$tier][] = $row->id;
                        $changed++;
                    }
                }

                if ($dryRun) {
                    return;
                }

                // Satu UPDATE per tier per chunk, bukan per baris; sengaja tidak
                // menyentuh updated_at karena ini bukan edit data oleh user.
                foreach ($idsByTier as $tier => $ids) {
                    DB::table('profiles')->whereIn('id', $ids)->update(['job_tier' => $tier]);
                }
            });

        $this->table(['Tier', 'Profiles'], collect($distribution)->map(function ($count, $tier) {
            return [JobTierClassifier::description($tier), $count];
        })->values()->all());

        $this->info(($dryRun ? '[dry-run] Akan diubah: ' : 'Diubah: ') . $changed . ' profile member active.');
        $this->info(($dryRun ? '[dry-run] Akan dikosongkan: ' : 'Dikosongkan: ') . $cleared . ' profile non-active.');

        return 0;
    }
}
