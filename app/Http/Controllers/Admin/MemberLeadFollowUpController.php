<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsUser;
use App\Models\MemberLeadFollowUp;
use App\Models\User;
use App\Services\LeadTemperatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MemberLeadFollowUpController extends Controller
{
    /** Columns the date range filter can apply to. */
    const DATE_FIELDS = [
        'created_at'          => 'Created',
        'deadline_at'         => 'Deadline',
        'sponsorkit_sent_at'  => 'Sponsor Kit',
        'first_follow_up_at'  => 'Follow-up 1',
        'second_follow_up_at' => 'Follow-up 2',
    ];

    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $result = $request->get('result', MemberLeadFollowUp::RESULT_PENDING);
        $picId  = $request->get('pic_id');
        $temperature = $request->get('temperature');
        $followUp  = $request->get('follow_up');
        $dateField = array_key_exists($request->get('date_field'), self::DATE_FIELDS) ? $request->get('date_field') : 'created_at';
        $dateFrom  = $request->get('date_from');
        $dateTo    = $request->get('date_to');
        $search = trim((string) $request->get('search'));

        $query = MemberLeadFollowUp::approvedMember()->with(['user.profile', 'user.company', 'notesHistory'])->orderBy('created_at', 'desc');

        // 'do_not_send' is its own tab, independent of win/pending/loss — a
        // flagged lead can be in any result state, so it filters on the flag
        // instead of the result column.
        if ($result === 'do_not_send') {
            $query->where('do_not_send', true);
        } elseif (in_array($result, [MemberLeadFollowUp::RESULT_PENDING, MemberLeadFollowUp::RESULT_WIN, MemberLeadFollowUp::RESULT_LOSS], true)) {
            $query->where('result', $result);
        } else {
            $result = 'all';
        }

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        if (in_array($temperature, ['hot', 'warm', 'cold'], true)) {
            $query->where('temperature', $temperature);
        }

        $this->applyFollowUpFilter($query, $followUp);
        $this->applyDateFilter($query, $dateField, $dateFrom, $dateTo);

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

        $countPending   = MemberLeadFollowUp::approvedMember()->where('result', MemberLeadFollowUp::RESULT_PENDING)->count();
        $countWin       = MemberLeadFollowUp::approvedMember()->where('result', MemberLeadFollowUp::RESULT_WIN)->count();
        $countLoss      = MemberLeadFollowUp::approvedMember()->where('result', MemberLeadFollowUp::RESULT_LOSS)->count();
        $countDoNotSend = MemberLeadFollowUp::approvedMember()->where('do_not_send', true)->count();
        $countOverSla   = MemberLeadFollowUp::approvedMember()->overSla()->count();

        // Counts shown on the quick "Due today" / "Need follow-up" filter pills.
        $countDueToday = MemberLeadFollowUp::approvedMember();
        $this->applyFollowUpFilter($countDueToday, 'today');
        $countDueToday = $countDueToday->count();
        $countNeedFollowUp = MemberLeadFollowUp::approvedMember();
        $this->applyFollowUpFilter($countNeedFollowUp, 'need');
        $countNeedFollowUp = $countNeedFollowUp->count();

        // Lead Performance (SOP §7): Conversion Rate = Win ÷ Total Lead.
        $countTotalLeads = $countPending + $countWin + $countLoss;
        $conversionRate  = $countTotalLeads > 0 ? round($countWin / $countTotalLeads * 100, 1) : null;

        $pics = CmsUser::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.member_leads.index', compact(
            'list',
            'result',
            'picId',
            'temperature',
            'followUp',
            'dateField',
            'dateFrom',
            'dateTo',
            'search',
            'countPending',
            'countWin',
            'countLoss',
            'countDoNotSend',
            'countOverSla',
            'countDueToday',
            'countNeedFollowUp',
            'countTotalLeads',
            'conversionRate',
            'pics'
        ));
    }

    /**
     * "Due today" = next step's deadline falls today; "Need follow-up" = due
     * today or already overdue. Only pending, not-flagged leads that still
     * have a step left (deadline_at is cleared once Follow-up 2 is done).
     */
    private function applyFollowUpFilter($query, ?string $followUp): void
    {
        if (!in_array($followUp, ['today', 'need'], true)) {
            return;
        }

        $query->where('result', MemberLeadFollowUp::RESULT_PENDING)
            ->where('do_not_send', false)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<=', now()->endOfDay());

        if ($followUp === 'today') {
            $query->where('deadline_at', '>=', now()->startOfDay());
        }
    }

    private function applyDateFilter($query, string $dateField, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom) {
            $query->where($dateField, '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where($dateField, '<=', Carbon::parse($dateTo)->endOfDay());
        }
    }

    /**
     * Autocomplete untuk modal "Add Member to Lead": hanya member active yang
     * belum punya lead pending (kasus member lama yang tidak otomatis masuk
     * karena sudah approved sebelum fitur ini ada).
     */
    public function searchMembers(Request $request)
    {
        $term = trim((string) $request->get('q'));
        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $users = User::with('company')
            ->where('status_member', 'active')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('company', function ($c) use ($term) {
                        $c->where('company_name', 'like', "%{$term}%");
                    });
            })
            ->whereNotIn('id', MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_PENDING)->pluck('user_id'))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $users->map(function ($u) {
                $company = optional($u->company)->company_name;
                return [
                    'id'   => $u->id,
                    'text' => $u->name . ' — ' . $u->email . ($company ? ' (' . $company . ')' : ''),
                ];
            })->values(),
        ]);
    }

    /**
     * Tambah member (yang sudah approved) ke Lead Follow-Up secara manual.
     * Aturan SLA awal sama seperti lead otomatis: 48 jam untuk kirim sponsor kit.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'notes'   => 'nullable|string|max:2000',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->status_member !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya member yang sudah approved (active) yang bisa dimasukkan ke Lead Follow-Up.',
            ], 422);
        }

        if (MemberLeadFollowUp::where('user_id', $user->id)->where('result', MemberLeadFollowUp::RESULT_PENDING)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Member ini sudah ada di Lead Follow-Up (status pending).',
            ], 422);
        }

        $admin = auth()->user();

        $lead = MemberLeadFollowUp::create([
            'user_id'         => $user->id,
            'deadline_at'     => MemberLeadFollowUp::deadlineFor('sponsorkit', now()),
            'result'          => MemberLeadFollowUp::RESULT_PENDING,
            'created_by_id'   => auth()->id(),
            'created_by_name' => $admin ? $admin->name : 'Staff',
        ]);

        $lead->addNote(null, $request->notes, $admin);

        return response()->json([
            'success' => true,
            'message' => $user->name . ' ditambahkan ke Lead Follow-Up.',
        ]);
    }

    /**
     * Logs whichever step is next for this lead (Send Sponsor Kit → Follow-up 1
     * → Follow-up 2) — one shared endpoint, same as before, just now aware of
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
                'message' => $lead->do_not_send
                    ? 'This lead is flagged "Do Not Send" — the sponsor kit cannot be sent.'
                    : 'All follow-up steps for this lead have already been logged.',
            ], 422);
        }

        $date = $request->filled('date') ? Carbon::parse($request->date) : now();
        $lead->logNextStep($date, $request->channel, $request->notes, auth()->user());

        return response()->json([
            'success' => true,
            'message' => MemberLeadFollowUp::stepLabel($stepKey) . ' logged successfully.',
        ]);
    }

    /**
     * Notes history modal: list every note for this lead (newest first).
     */
    public function notes($id)
    {
        $lead = MemberLeadFollowUp::with('notesHistory')->findOrFail($id);

        return response()->json([
            'notes' => $lead->notesHistory->map(function ($note) {
                return [
                    'step_label' => $note->stepLabel(),
                    'note'       => $note->note,
                    'by'         => $note->created_by_name ?: '-',
                    'at'         => optional($note->created_at)->format('d M Y H:i'),
                ];
            })->values(),
        ]);
    }

    /**
     * Add a note at any time — e.g. the prospect replied after the sponsor
     * kit / a follow-up — tagged with the step it belongs to.
     */
    public function storeNote(Request $request, $id)
    {
        $request->validate([
            'step' => 'nullable|in:' . implode(',', array_keys(MemberLeadFollowUp::SLA_HOURS)),
            'note' => 'required|string|max:2000',
        ]);

        $lead = MemberLeadFollowUp::findOrFail($id);
        $lead->addNote($request->step, $request->note, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Note added.',
        ]);
    }

    /**
     * Fix the channel after the fact (e.g. forgot to pick it when logging).
     */
    public function updateChannel(Request $request, $id)
    {
        $request->validate([
            'channel' => 'nullable|in:call,whatsapp,email,other',
        ]);

        $lead = MemberLeadFollowUp::findOrFail($id);
        $lead->channel = $request->filled('channel') ? $request->channel : null;
        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'Channel updated.',
        ]);
    }

    /**
     * Temperature checklist modal: current boxes + which auto boxes the
     * data already proves (those are shown locked). Recomputes first so the
     * auto categories are fresh.
     */
    public function temperature($id)
    {
        $lead = MemberLeadFollowUp::with('user')->findOrFail($id);
        LeadTemperatureService::recompute($lead);

        return response()->json($this->temperaturePayload($lead));
    }

    public function updateTemperature(Request $request, $id)
    {
        $request->validate([
            'checked'   => 'nullable|array',
            'checked.*' => 'in:' . implode(',', array_keys(LeadTemperatureService::POINTS)),
        ]);

        $lead = MemberLeadFollowUp::with('user')->findOrFail($id);
        LeadTemperatureService::setFlags($lead, $request->input('checked', []));

        return response()->json(array_merge($this->temperaturePayload($lead), [
            'success' => true,
            'message' => 'Temperature updated: ' . strtoupper($lead->temperature) . ' (' . $lead->temperature_score . ').',
        ]));
    }

    private function temperaturePayload(MemberLeadFollowUp $lead): array
    {
        $proven = LeadTemperatureService::provenFields($lead);

        $items = [];
        foreach (LeadTemperatureService::POINTS as $field => $points) {
            $items[] = [
                'field'   => $field,
                'label'   => LeadTemperatureService::LABELS[$field],
                'points'  => $points,
                'checked' => (bool) $lead->{$field},
                'auto'    => in_array($field, LeadTemperatureService::AUTO_FIELDS, true),
                'locked'  => in_array($field, $proven, true),
            ];
        }

        return [
            'temperature' => $lead->temperature,
            'score'       => $lead->temperature_score,
            'items'       => $items,
        ];
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
            'message' => 'Lead marked as ' . ucfirst($request->result) . '.',
        ]);
    }

    /**
     * Flag/unflag a lead as ineligible for the sponsor kit (competitor,
     * unqualified, etc.) — separate from win/loss, since this is a
     * disqualification decided before any real pursuit happens. While
     * flagged, MemberLeadFollowUp::nextStepKey() never returns 'sponsorkit',
     * so the "Send Sponsor Kit" action stays hidden/blocked for this lead.
     */
    public function markDoNotSend(Request $request, $id)
    {
        $request->validate([
            'do_not_send' => 'required|boolean',
            'reason'      => 'nullable|string|max:255',
        ]);

        $lead  = MemberLeadFollowUp::findOrFail($id);
        $admin = auth()->user();

        $lead->do_not_send = $request->boolean('do_not_send');

        if ($lead->do_not_send) {
            $lead->do_not_send_reason  = $request->input('reason');
            $lead->do_not_send_by_id   = auth()->id();
            $lead->do_not_send_by_name = $admin ? $admin->name : 'Staff';
            $lead->do_not_send_at      = now();
        } else {
            $lead->do_not_send_reason  = null;
            $lead->do_not_send_by_id   = null;
            $lead->do_not_send_by_name = null;
            $lead->do_not_send_at      = null;
        }

        $lead->save();

        return response()->json([
            'success' => true,
            'message' => $lead->do_not_send
                ? 'Lead flagged — the sponsor kit will not be sent to this lead.'
                : '"Do Not Send" flag removed.',
        ]);
    }
}
