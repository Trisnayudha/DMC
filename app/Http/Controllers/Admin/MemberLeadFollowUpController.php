<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsUser;
use App\Models\MemberLeadFollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $search = trim((string) $request->get('search'));

        $query = MemberLeadFollowUp::with(['user.profile', 'user.company'])->orderBy('created_at', 'desc');

        if (in_array($result, [MemberLeadFollowUp::RESULT_PENDING, MemberLeadFollowUp::RESULT_WIN, MemberLeadFollowUp::RESULT_LOSS], true)) {
            $query->where('result', $result);
        } else {
            $result = 'all';
        }

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q2) use ($search) {
                        $q2->where('company_name', 'like', "%{$search}%");
                    });
            });
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
            'search',
            'countPending',
            'countWin',
            'countLoss',
            'countOverSla',
            'countTotalLeads',
            'conversionRate',
            'pics'
        ));
    }

    /**
     * Logs whichever step is next for this lead (Kirim Sponsorkit → Follow Up 1
     * → Follow Up 2) — one shared endpoint, same as before, just now aware of
     * 3 steps instead of 2 and taking a manually-editable date.
     */
    public function logFollowUp(Request $request, $id)
    {
        $request->validate([
            'date'    => 'nullable|date',
            'channel' => 'nullable|string|max:50',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $lead = MemberLeadFollowUp::findOrFail($id);

        $stepKey = $lead->nextStepKey();
        if (!$stepKey) {
            return response()->json([
                'success' => false,
                'message' => 'Semua tahap follow up untuk lead ini sudah tercatat.',
            ], 422);
        }

        $date = $request->filled('date') ? Carbon::parse($request->date) : now();

        if ($request->filled('channel')) {
            $lead->channel = $request->channel;
        }
        if ($request->filled('notes')) {
            $lead->notes = $request->notes;
        }

        if ($stepKey === 'sponsorkit') {
            $lead->sponsorkit_sent_at = $date;
        } elseif ($stepKey === 'follow_up_1') {
            $lead->first_follow_up_at = $date;
        } else {
            $lead->second_follow_up_at = $date;
        }

        // Auto-assign PIC to whoever performs the FIRST action on this lead —
        // stays locked to that person for accountability. Manual re-assign
        // UI was removed; later actions by other admins don't reassign it.
        if (!$lead->pic_id) {
            $admin = auth()->user();
            if ($admin) {
                $lead->pic_id   = $admin->id;
                $lead->pic_name = $admin->name;
            }
        }

        // Rolling SLA: the deadline for the NEXT step is 48h after whichever
        // step was just completed — not from lead creation anymore. Once
        // Follow Up 2 (the last defined step) is done there's nothing left
        // to be late for, so the deadline is cleared and the row stops
        // turning red.
        $lead->deadline_at = $lead->nextStepKey() ? $date->copy()->addHours(48) : null;

        $lead->save();

        return response()->json([
            'success' => true,
            'message' => MemberLeadFollowUp::stepLabel($stepKey) . ' berhasil dicatat.',
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
