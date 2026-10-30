<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Event Partnership (General Database): semua booth visitor dari semua
 * Partnership Event dalam satu tabel. Kelola visitor per event (tambah, import,
 * register as member) tetap di halaman Partnership Event masing-masing.
 */
class PartnershipVisitorDatabaseController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $filters = [
            'event_id'   => $request->get('event_id'),
            'membership' => $request->get('membership'),
        ];

        $list = $this->visitorQuery($filters['event_id'])->get();
        $statuses = $this->memberStatusByEmail($list);
        foreach ($list as $visitor) {
            $visitor->member_status = $statuses[strtolower(trim((string) $visitor->business_email))] ?? null;
        }
        if ($filters['membership'] === 'member') {
            $list = $list->where('member_status', 'active')->values();
        } elseif ($filters['membership'] === 'non_member') {
            $list = $list->where('member_status', '!=', 'active')->values();
        }

        return view('admin.partnership_visitors.index', [
            'list'    => $list,
            'filters' => $filters,
            'events'  => $this->eventOptions(),
            'stats'   => [
                'total'     => $list->count(),
                'events'    => $list->pluck('events_id')->unique()->count(),
                'companies' => $list->pluck('company_name')->filter()->map(function ($c) {
                    return strtolower(trim($c));
                })->unique()->count(),
                'members'   => $list->where('member_status', 'active')->count(),
            ],
        ]);
    }

    private function visitorQuery($eventId)
    {
        return DB::table('partnership_event_visitors as v')
            ->join('events', 'events.id', 'v.events_id')
            ->when($eventId, function ($q) use ($eventId) {
                $q->where('v.events_id', $eventId);
            })
            ->select(
                'v.id', 'v.events_id', 'v.name', 'v.company_name', 'v.job_title', 'v.business_email',
                'v.mobile_number', 'v.remarks', 'v.created_at',
                'events.name as event_name', 'events.slug as event_slug', 'events.start_date'
            )
            ->orderBy('events.start_date', 'desc')
            ->orderBy('v.id', 'desc');
    }

    /** status_member per email (huruf kecil), dicocokkan ke users.email. */
    private function memberStatusByEmail($list): array
    {
        $emails = $list->pluck('business_email')->filter()->map(function ($e) {
            return strtolower(trim($e));
        })->unique()->values();
        if ($emails->isEmpty()) {
            return [];
        }
        return DB::table('users')
            ->whereIn(DB::raw('LOWER(email)'), $emails->all())
            ->get(['email', 'status_member'])
            ->mapWithKeys(function ($u) {
                return [strtolower(trim($u->email)) => $u->status_member];
            })->all();
    }

    private function eventOptions()
    {
        return DB::table('events')
            ->whereIn('id', DB::table('partnership_event_visitors')->select('events_id'))
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'start_date']);
    }
}
