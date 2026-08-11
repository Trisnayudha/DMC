<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsUser;
use App\Models\MemberLeadFollowUp;
use Illuminate\Http\Request;

class MemberLeadFollowUpController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $result = $request->get('result', MemberLeadFollowUp::RESULT_PENDING);
        $picId  = $request->get('pic_id');

        $query = MemberLeadFollowUp::with(['user.profile', 'user.company'])->orderBy('created_at', 'desc');

        if (in_array($result, [MemberLeadFollowUp::RESULT_PENDING, MemberLeadFollowUp::RESULT_WIN, MemberLeadFollowUp::RESULT_LOSS], true)) {
            $query->where('result', $result);
        } else {
            $result = 'all';
        }

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        $list = $query->get();

        $countPending = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_PENDING)->count();
        $countWin     = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_WIN)->count();
        $countLoss    = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_LOSS)->count();
        $countOverSla = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_PENDING)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->count();

        // Lead Performance (SOP §7): Conversion Rate = Win ÷ Total Lead.
        $countTotalLeads = $countPending + $countWin + $countLoss;
        $conversionRate  = $countTotalLeads > 0 ? round($countWin / $countTotalLeads * 100, 1) : null;

        $pics = CmsUser::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.member_leads.index', compact(
            'list',
            'result',
            'picId',
            'countPending',
            'countWin',
            'countLoss',
            'countOverSla',
            'countTotalLeads',
            'conversionRate',
            'pics'
        ));
    }

    public function assignPic(Request $request, $id)
    {
        $request->validate([
            'pic_id' => 'required|exists:cms_users,id',
        ]);

        $lead = MemberLeadFollowUp::findOrFail($id);
        $pic  = CmsUser::findOrFail($request->pic_id);

        $lead->pic_id   = $pic->id;
        $lead->pic_name = $pic->name;
        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'PIC berhasil di-assign ke ' . $pic->name . '.',
        ]);
    }

    public function logFollowUp(Request $request, $id)
    {
        $request->validate([
            'channel' => 'nullable|string|max:50',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $lead = MemberLeadFollowUp::findOrFail($id);

        if ($request->filled('channel')) {
            $lead->channel = $request->channel;
        }
        if ($request->filled('notes')) {
            $lead->notes = $request->notes;
        }

        if (!$lead->first_follow_up_at) {
            $lead->first_follow_up_at = now();
        } elseif (!$lead->second_follow_up_at) {
            $lead->second_follow_up_at = now();
        }

        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'Follow up berhasil dicatat.',
        ]);
    }

    public function markResult(Request $request, $id)
    {
        $request->validate([
            'result' => 'required|in:win,loss',
        ]);

        $lead = MemberLeadFollowUp::findOrFail($id);
        $lead->result = $request->result;
        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'Hasil lead berhasil ditandai ' . ucfirst($request->result) . '.',
        ]);
    }
}
