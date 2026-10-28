<?php

namespace App\Console\Commands;

use App\Models\CmsRole;
use App\Support\CmsAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyCmsTeamDefaults extends Command
{
    protected $signature = 'cms:apply-team-defaults {--force : Replace the configured permissions for the specified teams}';
    protected $description = 'Apply the requested initial menu access for Member, Marketing, Secretariat and Financial teams';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $this->error('This replaces existing team permissions. Pass --force only when this reset is intended.');
            return 1;
        }
        $defaults = config('cms_access.team_defaults', []);
        $teams = ['member_team', 'marketing_team', 'secretariat_team', 'finance_team'];
        foreach ($teams as $team) {
            if (!isset($defaults[$team]) || array_diff($defaults[$team], array_keys(CmsAccess::configurableModules()))) {
                $this->error('Invalid default permissions for ' . $team . '.');
                return 1;
            }
        }
        DB::transaction(function () use ($teams, $defaults) {
            $roles = CmsRole::whereIn('key', $teams)->orderBy('key')->lockForUpdate()->get()->keyBy('key');
            if ($roles->count() !== count($teams)) {
                throw new \RuntimeException('CMS team roles are missing; run the CMS role migrations first.');
            }
            foreach ($teams as $team) {
                $roles[$team]->permissions = $defaults[$team];
                $roles[$team]->save();
            }
        });
        $this->info('Menu access updated for Member, Marketing, Secretariat and Financial teams. Event Team was preserved.');
        return 0;
    }
}
