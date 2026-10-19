<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Support\JobTierClassifier;
use App\Support\JobTierRole;
use Illuminate\Http\Request;

/**
 * Modal detail chart "Job Tier" di halaman admin Users — daftar member Active
 * dalam satu tier, dipecah per job title & company category. Tier dihitung
 * dengan aturan yang sama dengan chart (ProfileModel::jobTierFor).
 */
class JobTierDetailController extends Controller
{
    public function show(Request $request)
    {
        $tier = (int) $request->query('tier');
        if (!isset(JobTierClassifier::TIERS[$tier])) {
            abort(404);
        }

        $members = $this->membersInTier($tier);

        return response()->json([
            'tier'        => $tier,
            'label'       => JobTierClassifier::label($tier),
            'roles'       => JobTierClassifier::TIERS[$tier]['roles'],
            'action'      => JobTierClassifier::TIERS[$tier]['action'],
            'total'       => count($members),
            'manual'      => count(array_filter(array_column($members, 'manual'))),
            'roles_breakdown' => $this->roleBreakdown($tier, $members),
            'categories'  => $this->categoryBreakdown($members),
            'members'     => $members,
            'members_url' => route('users', ['status_member' => 'active', 'job_tier' => $tier]),
        ]);
    }

    private function membersInTier(int $tier): array
    {
        $hasTierColumn = ProfileModel::hasJobTierColumn();

        $rows = User::leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus')
            ->where('users.status_member', 'active')
            ->orderBy('users.name')
            ->get(array_merge(
                ['users.id', 'users.name', 'users.email', 'users.status_member',
                 'profiles.job_title', 'company.company_name', 'company.company_category'],
                $hasTierColumn ? ['profiles.job_tier', 'profiles.job_tier_manual'] : []
            ));

        // Sama dengan chart Job Tier: hanya company yang sudah verified.
        $verifiedNames = CompanyModel::verifiedCompanyNameSet();

        $members = [];
        foreach ($rows as $row) {
            if (!CompanyModel::isVerifiedName($row->company_name, $verifiedNames)) {
                continue;
            }
            if (ProfileModel::jobTierFor($row->job_title, $row->status_member, $row->job_tier ?? null) !== $tier) {
                continue;
            }

            $members[] = [
                'id'        => $row->id,
                'name'      => $row->name,
                'email'     => $row->email,
                'job_title' => $row->job_title,
                'role'      => JobTierRole::roleFor($tier, $row->job_title),
                'company'   => $row->company_name,
                'category'  => CompanyModel::categoryLabel($row->company_category),
                'manual'    => !empty($row->job_tier_manual),
            ];
        }

        return $members;
    }

    /** Semua role di definisi tier (termasuk yang 0), urut sesuai definisi. */
    private function roleBreakdown(int $tier, array $members): array
    {
        $counts = JobTierRole::emptyCounts($tier);
        foreach ($members as $member) {
            $counts[$member['role']]++;
        }

        $result = [];
        foreach ($counts as $name => $count) {
            $result[] = ['name' => $name, 'count' => $count];
        }

        return $result;
    }

    private function categoryBreakdown(array $members): array
    {
        $counts = array_count_values(array_column($members, 'category'));
        arsort($counts);

        $result = [];
        foreach ($counts as $name => $count) {
            $result[] = ['name' => (string) $name, 'count' => $count];
        }

        return $result;
    }
}
