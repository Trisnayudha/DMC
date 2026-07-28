<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberCompanyFollowUp;
use App\Models\User;
use Illuminate\Http\Request;

class MemberCompanyFollowUpController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $status = $request->get('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP);

        $query = MemberCompanyFollowUp::with(['user.profile'])->orderBy('created_at', 'desc');

        if ($status === MemberCompanyFollowUp::STATUS_VERIFIED) {
            $query->where('status', MemberCompanyFollowUp::STATUS_VERIFIED);
        } elseif ($status !== 'all') {
            $status = MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP;
            $query->where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP);
        }

        $list = $query->get();

        $countNeedsFollowUp = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP)->count();
        $countVerified      = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_VERIFIED)->count();

        $companyNames = CompanyModel::whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name');

        return view('admin.member_follow_ups.index', compact(
            'list',
            'status',
            'countNeedsFollowUp',
            'countVerified',
            'companyNames'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'new_company_name' => 'required|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        $user = User::with('profile.company')->findOrFail($request->user_id);
        $previousCompanyName = optional(optional($user->profile)->company)->company_name;

        $followUp = MemberCompanyFollowUp::firstOrNew([
            'user_id' => $user->id,
            'status'  => MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP,
        ]);

        $admin = auth()->user();
        $followUp->previous_company_name = $previousCompanyName;
        $followUp->new_company_name      = $request->new_company_name;
        $followUp->notes                 = $request->notes;
        $followUp->status                = MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP;
        $followUp->flagged_by_id         = auth()->id();
        $followUp->flagged_by_name       = $admin ? $admin->name : null;
        $followUp->save();

        return response()->json([
            'success' => true,
            'message' => $user->name . ' berhasil ditandai perlu follow up.',
        ]);
    }

    public function markVerified(Request $request, $id)
    {
        $followUp = MemberCompanyFollowUp::with('user')->findOrFail($id);
        $admin    = auth()->user();

        $followUp->status          = MemberCompanyFollowUp::STATUS_VERIFIED;
        $followUp->verified_by_id  = auth()->id();
        $followUp->verified_by_name = $admin ? $admin->name : null;
        $followUp->verified_at     = now();
        $followUp->save();

        return response()->json([
            'success' => true,
            'message' => optional($followUp->user)->name . ' ditandai Update Verified.',
        ]);
    }
}
