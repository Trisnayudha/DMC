<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Options for the multi-select "Company" filter on the admin Members page (select2 AJAX).
 * Companies are grouped by normalized name because each member has their own company row.
 */
class MemberCompanyFilterController extends Controller
{
    const LIMIT = 30;

    public function options(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $rows = $this->companyQuery((string) $request->query('status_member', ''))
            ->when($term !== '', function ($query) use ($term) {
                $query->where('company.company_name', 'like', '%' . $term . '%');
            })
            ->groupBy(DB::raw('LOWER(TRIM(company.company_name))'))
            ->orderByRaw('COUNT(DISTINCT users.id) DESC')
            ->limit(self::LIMIT)
            ->get([
                DB::raw('MIN(TRIM(company.company_name)) as name'),
                DB::raw('COUNT(DISTINCT users.id) as members'),
            ]);

        return response()->json([
            'results' => $rows->map(function ($row) {
                return ['id' => $row->name, 'text' => $row->name . ' (' . $row->members . ')'];
            })->values(),
        ]);
    }

    /** Companies of members in the same status bucket as the Members tab being viewed. */
    private function companyQuery(string $statusMember)
    {
        $query = User::join('profiles', 'profiles.users_id', 'users.id')
            ->join('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus')
            ->whereNotNull('company.company_name')
            ->whereRaw("TRIM(company.company_name) <> ''")
            ->toBase();

        if (in_array($statusMember, ['active', 'declined', 'deactivated'], true)) {
            $query->where('users.status_member', $statusMember);
        } elseif ($statusMember === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('users.status_member')->orWhere('users.status_member', 'pending');
            });
        }

        return $query;
    }
}
