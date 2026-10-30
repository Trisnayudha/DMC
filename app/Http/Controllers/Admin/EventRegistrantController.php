<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Event Registrant: semua pendaftar dari semua event DMC dalam satu tabel
 * (sumber: payment, satu baris per registrasi). Nama event menautkan ke
 * halaman detail event masing-masing.
 */
class EventRegistrantController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    public function index(Request $request)
    {
        $filters = [
            'event_id'   => $request->get('event_id'),
            'status'     => $request->get('status'),
            'package'    => $request->get('package'),
            'membership' => $request->get('membership'),
            'date_from'  => $request->get('date_from'),
            'date_to'    => $request->get('date_to'),
        ];

        // unique: user dengan lebih dari satu profil membuat payment-nya muncul dobel.
        $list = $this->registrantQuery($filters)->get()->unique('id')->values();

        return view('admin.event_registrants.index', [
            'rows'     => $this->tableRows($list),
            'filters'  => $filters,
            'stats'    => $this->stats($list),
            'events'   => $this->eventOptions(),
            'statuses' => $this->distinctPaymentValues('status_registration'),
            'packages' => $this->distinctPaymentValues('package'),
        ]);
    }

    private function registrantQuery(array $filters)
    {
        $query = DB::table('payment')
            ->join('events', 'events.id', 'payment.events_id')
            ->join('users', 'users.id', 'payment.member_id')
            ->leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            // Subquery, bukan join: satu payment bisa punya beberapa baris users_event.
            ->selectSub(
                DB::table('users_event')->selectRaw('MAX(present)')->whereColumn('users_event.payment_id', 'payment.id'),
                'present'
            )
            ->addSelect(
                'payment.id',
                'payment.created_at',
                'payment.code_payment',
                'payment.package',
                'payment.status_registration',
                'events.name as event_name',
                'events.slug as event_slug',
                'events.start_date',
                'users.id as users_id',
                'users.name',
                'users.email',
                'users.status_member',
                'profiles.fullphone',
                'profiles.phone',
                'profiles.job_title',
                'company.company_name'
            )
            ->orderBy('payment.id', 'desc');

        if ($filters['event_id']) {
            $query->where('payment.events_id', $filters['event_id']);
        }
        if ($filters['status']) {
            $query->where('payment.status_registration', $filters['status']);
        }
        if ($filters['package']) {
            $query->where('payment.package', $filters['package']);
        }
        if ($filters['membership'] === 'member') {
            $query->where('users.status_member', 'active');
        } elseif ($filters['membership'] === 'non_member') {
            $query->where(function ($q) {
                $q->whereNull('users.status_member')->orWhere('users.status_member', '<>', 'active');
            });
        }
        if ($filters['date_from']) {
            $query->whereDate('payment.created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('payment.created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function stats($list): array
    {
        return [
            'total'     => $list->count(),
            'paid_off'  => $list->where('status_registration', 'Paid Off')->count(),
            'unique'    => $list->pluck('users_id')->unique()->count(),
            'checked_in' => $list->whereNotNull('present')->count(),
        ];
    }

    /** Baris tabel sebagai JSON (dirender DataTables), jauh lebih ringan dari ~3.400 <tr>. */
    private function tableRows($list): array
    {
        return $list->map(function ($row) {
            return [
                'registered'   => $row->created_at,
                'event'        => $row->event_name,
                'event_url'    => route('events-details', $row->event_slug),
                'name'         => $row->name,
                'email'        => $row->email,
                'phone'        => $this->displayPhone($row->fullphone ?: $row->phone),
                'company'      => $row->company_name,
                'job_title'    => $row->job_title,
                'package'      => $row->package,
                'status'       => $row->status_registration,
                'is_member'    => $row->status_member === 'active',
                'checked_in'   => (bool) $row->present,
            ];
        })->all();
    }

    /**
     * fullphone belum terisi untuk semua profil (backfill), jadi fallback ke profiles.phone.
     * Isian sampah seperti "+62", "11" atau "-" (kurang dari 7 digit) dianggap kosong.
     */
    private function displayPhone($phone): ?string
    {
        $phone = trim((string) $phone);
        return strlen(preg_replace('/\D/', '', $phone)) >= 7 ? $phone : null;
    }

    private function eventOptions()
    {
        return DB::table('events')
            ->whereIn('id', DB::table('payment')->whereNotNull('events_id')->select('events_id'))
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'start_date']);
    }

    private function distinctPaymentValues(string $column)
    {
        return DB::table('payment')
            ->whereNotNull('events_id')
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }
}
