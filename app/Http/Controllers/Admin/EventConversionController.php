<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EventConversionExport;
use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\PartnershipEvent\PartnershipEventVisitor;
use App\Models\Payments\Payment;
use App\Models\User;
use App\Services\Events\EventConversionAttribution;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EventConversionController extends Controller
{
    /** @var EventConversionAttribution */
    private $attribution;

    public function __construct(EventConversionAttribution $attribution)
    {
        // Auth handled by cms_auth middleware
        $this->attribution = $attribution;
    }

    /**
     * Tampilan utama Event Database and Member Conversion.
     * Mengadopsi diagram operasional DMC:
     * - DMC Owned Events vs Supporting Events
     * - Event Participation Funnel (Registration -> Confirmation -> Check-in -> Follow-up Interest)
     * - Annual Attendance (Entries, Unique people, Verified members)
     * - Member Conversion (Consent/interest -> Pending Verification -> Active Member)
     */
    public function index(Request $request)
    {
        return view('admin.event_conversions.index', $this->conversionData($request));
    }

    /**
     * Diagram saja (HTML partial), dimuat lewat AJAX di halaman DMC Main Event
     * supaya list event tidak ikut menunggu hitungan metrik.
     */
    public function diagram(Request $request)
    {
        return view('admin.event_conversions.partials._diagram_embed', $this->conversionData($request));
    }

    private function conversionData(Request $request): array
    {
        $selectedYear = $request->query('year');
        $selectedType = $request->query('type', 'all'); // 'all', 'owned', 'supporting'
        $search = trim((string) $request->query('search', ''));

        // Ambil daftar tahun unik dari events
        $years = Events::whereNotNull('start_date')
            ->selectRaw('DISTINCT YEAR(start_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->values()
            ->all();

        if (empty($years)) {
            $years = [(int) date('Y')];
        }

        // Default: Tahun sekarang jika ada, atau tahun terbaru yang ada datanya
        if (empty($selectedYear)) {
            $currentYear = (int) date('Y');
            $selectedYear = in_array($currentYear, $years, true) ? (string) $currentYear : (string) ($years[0] ?? 'all');
        }

        $events = $this->filteredEvents((string) $selectedYear, (string) $selectedType, $search);

        $cacheKey = 'dmc_event_conv_metrics_v7_' . md5($selectedYear . '_' . $selectedType . '_' . $search);
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
            foreach ($selectedYear === 'all' ? $years : [(int) $selectedYear] as $y) {
                $this->attribution->forget((int) $y);
            }
        }

        $metrics = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($events) {
            $processedEvents = [];
            $ownedCount = 0;
            $supportingCount = 0;

            $funnelOwned = [
                'events'        => 0,
                'all_registrations' => 0, // every status
                'registrations' => 0,     // Paid Off only
                'confirmed'     => 0,
                'attendance'    => 0,
                'interest'      => 0,
                'pending'       => 0,
                'active'        => 0,
                'converted'     => 0,
                'rate'          => 0,
            ];

            $funnelSupporting = [
                'events'        => 0,
                'events_with_data' => 0, // partnership events that have booth-visitor data

                'registrations' => 0,
                'confirmed'     => 0,
                'attendance'    => 0,
                'interest'      => 0,
                'pending'       => 0,
                'active'        => 0,
                'converted'     => 0,
                'rate'          => 0,
            ];

            $allUniqueAttendeeEmails = collect();
            $allVerifiedMemberEmails = collect();
            $totalEntries = 0;
            // Same sets split by event type, for the ⓘ breakdown on Annual attendance.
            $uniqueByType = ['owned' => collect(), 'partner' => collect()];
            $verifiedByType = ['owned' => collect(), 'partner' => collect()];

            foreach ($events as $event) {
                $isSupporting = ($event->event_type === 'Partnership Event');

                if ($isSupporting) {
                    $supportingCount++;
                    $eventData = $this->calculateSupportingEventMetrics($event);
                    $funnelSupporting['events']++;
                    if ($eventData->registration_count > 0) {
                        $funnelSupporting['events_with_data']++;
                    }
                    $funnelSupporting['registrations'] += $eventData->registration_count;
                    $funnelSupporting['confirmed']     += $eventData->confirmed_count;
                    $funnelSupporting['attendance']    += $eventData->attendance_count;
                    $funnelSupporting['interest']      += $eventData->interest_count;
                    $funnelSupporting['pending']       += $eventData->pending_converted;
                    $funnelSupporting['active']        += $eventData->active_converted;
                    $funnelSupporting['converted']     += $eventData->total_converted;
                } else {
                    $ownedCount++;
                    $eventData = $this->calculateOwnedEventMetrics($event);
                    $funnelOwned['events']++;
                    $funnelOwned['registrations'] += $eventData->registration_count;
                    $funnelOwned['all_registrations'] += $eventData->all_registration_count;
                    $funnelOwned['confirmed']     += $eventData->confirmed_count;
                    $funnelOwned['attendance']    += $eventData->attendance_count;
                    $funnelOwned['interest']      += $eventData->interest_count;
                    $funnelOwned['pending']       += $eventData->pending_converted;
                    $funnelOwned['active']        += $eventData->active_converted;
                    $funnelOwned['converted']     += $eventData->total_converted;
                }

                $totalEntries += $eventData->attendance_count;
                $typeKey = $isSupporting ? 'partner' : 'owned';

                if (!empty($eventData->attendee_emails)) {
                    $allUniqueAttendeeEmails = $allUniqueAttendeeEmails->merge($eventData->attendee_emails);
                    $uniqueByType[$typeKey] = $uniqueByType[$typeKey]->merge($eventData->attendee_emails);
                }
                if (!empty($eventData->verified_member_emails)) {
                    $allVerifiedMemberEmails = $allVerifiedMemberEmails->merge($eventData->verified_member_emails);
                    $verifiedByType[$typeKey] = $verifiedByType[$typeKey]->merge($eventData->verified_member_emails);
                }

                $processedEvents[] = $eventData;
            }

            // Conversion rate = new Active members ÷ unique people who actually attended.
            $ownedBase = max(1, $uniqueByType['owned']->unique()->count());
            $funnelOwned['rate'] = round(($funnelOwned['converted'] / $ownedBase) * 100, 1);

            $supportingBase = max(1, $uniqueByType['partner']->unique()->count());
            $funnelSupporting['rate'] = round(($funnelSupporting['converted'] / $supportingBase) * 100, 1);

            // Overall Annual Attendance
            $annualAttendance = [
                'entries'          => $totalEntries,
                'unique_people'    => $allUniqueAttendeeEmails->unique()->count(),
                'verified_members' => $allVerifiedMemberEmails->unique()->count(),
                'unique_owned'     => $uniqueByType['owned']->unique()->count(),
                'unique_partner'   => $uniqueByType['partner']->unique()->count(),
                'verified_owned'   => $verifiedByType['owned']->unique()->count(),
                'verified_partner' => $verifiedByType['partner']->unique()->count(),
            ];

            // Overall Member Conversion
            $totalInterest = $funnelOwned['interest'] + $funnelSupporting['interest'];
            $totalPending = $funnelOwned['pending'] + $funnelSupporting['pending'];
            $totalActive = $funnelOwned['active'] + $funnelSupporting['active'];
            $totalConverted = $totalActive; // Pending Review is pipeline, not a conversion
            $totalParticipants = max(1, $annualAttendance['unique_people']);
            $overallRate = round(($totalConverted / $totalParticipants) * 100, 1);

            $annualConversion = [
                'interest'        => $totalInterest,
                'pending'         => $totalPending,
                'active'          => $totalActive,
                'total_converted' => $totalConverted,
                'rate'            => $overallRate,
            ];

            // Combined Event Participation
            $totalRegistrations = $funnelOwned['registrations'] + $funnelSupporting['registrations'];
            $totalConfirmed = $funnelOwned['confirmed'] + $funnelSupporting['confirmed'];
            $totalAttendance = $funnelOwned['attendance'] + $funnelSupporting['attendance'];

            $eventParticipation = [
                'registration' => $totalRegistrations,
                'confirmation' => $totalConfirmed,
                'attendance'   => $totalAttendance,
                'interest'     => $totalInterest,
            ];

            return [
                'processedEvents'    => $processedEvents,
                'ownedCount'         => $ownedCount,
                'supportingCount'    => $supportingCount,
                'funnelOwned'        => $funnelOwned,
                'funnelSupporting'   => $funnelSupporting,
                'annualAttendance'   => $annualAttendance,
                'annualConversion'   => $annualConversion,
                'eventParticipation' => $eventParticipation,
            ];
        });

        return [
            'events'              => $metrics['processedEvents'],
            'years'               => $years,
            'selectedYear'        => $selectedYear,
            'selectedType'        => $selectedType,
            'search'              => $search,
            'ownedCount'          => $metrics['ownedCount'],
            'supportingCount'     => $metrics['supportingCount'],
            'funnelOwned'         => $metrics['funnelOwned'],
            'funnelSupporting'    => $metrics['funnelSupporting'],
            'annualAttendance'    => $metrics['annualAttendance'],
            'annualConversion'    => $metrics['annualConversion'],
            'eventParticipation'  => $metrics['eventParticipation'],
        ];
    }

    /**
     * Hitung metrik konversi untuk Supporting Event (Partnership Event).
     * Booth visitors -> consent / opt-in -> pending member -> verified member.
     */
    private function calculateSupportingEventMetrics(Events $event): object
    {
        $visitors = PartnershipEventVisitor::where('events_id', $event->id)->get();
        $regCount = $visitors->count();

        // Valid contact info (email or mobile)
        $confirmedCount = $visitors->filter(function ($v) {
            return !empty($v->business_email) || !empty($v->mobile_number);
        })->count();

        // Di booth partnership, setiap baris visitor adalah check-in/entry fisik
        $attendanceCount = $regCount;

        $emails = $visitors->pluck('business_email')
            ->filter()
            ->map(fn ($e) => strtolower(trim($e)))
            ->unique()
            ->values();

        $uniqueCount = $emails->count();

        // Verified active members who visited the booth
        $verifiedMemberEmails = collect();
        if ($emails->isNotEmpty()) {
            $verifiedMemberEmails = User::whereIn('email', $emails)
                ->where('status_member', 'active')
                ->where('source', '!=', 'Event Partnership')
                ->pluck('email')
                ->map(fn ($e) => strtolower(trim($e)));
        }
        $verifiedMembersCount = $verifiedMemberEmails->count();

        // Conversions: see EventConversionAttribution (attended, joined on/after the event,
        // once per person — credited to the last event before joining).
        $credited = $this->attribution->forEvent($event);
        $pendingConverted = count($credited['pending']);
        $activeConverted = count($credited['active']);

        // Sponsorship interest = booth visitors who are Active members and showed
        // sponsorship interest (see sponsorInterestRows). Not "Merchandise or Remarks
        // filled": Remarks is mostly a day/visitor-type note, which counted almost everyone.
        $interestCount = count($this->sponsorInterestRows($event, $visitors));

        $totalConverted = $activeConverted;
        $convRate = $uniqueCount > 0 ? round(($totalConverted / $uniqueCount) * 100, 1) : 0;

        return (object) [
            'id'                     => $event->id,
            'name'                   => $event->name,
            'slug'                   => $event->slug,
            'event_type'             => $event->event_type ?: 'Partnership Event',
            'is_supporting'          => true,
            'has_checkin_data'       => true, // every booth visitor row is a physical visit
            'category_label'         => 'Supporting Event',
            'start_date'             => $event->start_date,
            'end_date'               => $event->end_date,
            'location'               => $event->location ?: '-',
            'registration_count'     => $regCount,
            'confirmed_count'        => $confirmedCount,
            'attendance_count'       => $attendanceCount,
            'unique_count'           => $uniqueCount,
            'verified_members_count' => $verifiedMembersCount,
            'interest_count'         => $interestCount,
            'pending_converted'      => $pendingConverted,
            'active_converted'       => $activeConverted,
            'total_converted'        => $totalConverted,
            'conversion_rate'        => $convRate,
            'detail_url'             => route('admin.partnership_events.show', $event->slug),
            'attendee_emails'        => $emails,
            'verified_member_emails' => $verifiedMemberEmails,
        ];
    }

    /**
     * Hitung metrik konversi untuk DMC Owned Event (DMC Event / DMC Partnership Event).
     * Pendaftaran tiket -> konfirmasi bayar -> hadir (present) -> prospek -> convert member.
     */
    private function calculateOwnedEventMetrics(Events $event): object
    {
        $payments = Payment::where('events_id', $event->id)->get();

        // Registration & Confirmation = approved registrations only (Paid Off). Waiting,
        // Reject, Cancel and Expired are left out; a free ticket counts once approved.
        $regCount = $payments->where('status_registration', 'Paid Off')->count();
        $confirmedCount = $regCount;
        $allRegCount = $payments->count(); // every status — "Registrants" on the DMC owned events card

        // Check-in and attendance from users_event (present is not null)
        $attendeeRegisters = UserRegister::where('events_id', $event->id)
            ->whereNotNull('present')
            ->get(['users_id']);

        $attendeeUserIds = $attendeeRegisters
            ->pluck('users_id')
            ->filter()
            ->unique()
            ->values();

        // Only scanned check-ins count as attendance — no fallback to registrations, so an
        // event without scans shows 0 attendance / 0 conversions and is flagged instead.
        $attendanceCount = $attendeeRegisters->count();
        $hasCheckInData = $attendanceCount > 0;
        $uniqueCount = $attendeeUserIds->count();

        $attendeeEmails = collect();
        $verifiedMemberEmails = collect();
        if ($attendeeUserIds->isNotEmpty()) {
            $attendeeUsers = User::whereIn('id', $attendeeUserIds)->get(['id', 'email', 'status_member']);
            $attendeeEmails = $attendeeUsers->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)))->unique();
            $verifiedMemberEmails = $attendeeUsers->where('status_member', 'active')->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)));
        }
        $verifiedMembersCount = $verifiedMemberEmails->count();

        // Sponsorship interest = registrants who are Active members with the sponsorship tick
        // (see ownedSponsorInterestRows) — same rule as partnership events.
        $interestCount = count($this->ownedSponsorInterestRows($event, $payments));

        // Conversions: see EventConversionAttribution (checked in, joined on/after the event,
        // once per person — credited to the last event before joining).
        $credited = $this->attribution->forEvent($event);
        $pendingConverted = count($credited['pending']);
        $activeConverted = count($credited['active']);

        $totalConverted = $activeConverted;
        $convRate = $uniqueCount > 0 ? round(($totalConverted / $uniqueCount) * 100, 1) : 0;

        return (object) [
            'id'                     => $event->id,
            'name'                   => $event->name,
            'slug'                   => $event->slug,
            'event_type'             => $event->event_type ?: 'DMC Event',
            'is_supporting'          => false,
            'has_checkin_data'       => $hasCheckInData,
            'category_label'         => 'DMC Owned Event',
            'start_date'             => $event->start_date,
            'end_date'               => $event->end_date,
            'location'               => $event->location ?: '-',
            'registration_count'     => $regCount,
            'all_registration_count' => $allRegCount,
            'confirmed_count'        => $confirmedCount,
            'attendance_count'       => $attendanceCount,
            'unique_count'           => $uniqueCount,
            'verified_members_count' => $verifiedMembersCount,
            'interest_count'         => $interestCount,
            'pending_converted'      => $pendingConverted,
            'active_converted'       => $activeConverted,
            'total_converted'        => $totalConverted,
            'conversion_rate'        => $convRate,
            'detail_url'             => route('events-details', $event->slug),
            'attendee_emails'        => $attendeeEmails,
            'verified_member_emails' => $verifiedMemberEmails,
        ];
    }

    /**
     * Kapan user ini mendaftar jadi member: member_registered_at (diisi saat
     * akun event-only mendaftar member), fallback users.created_at untuk data
     * lama / yang daftar member langsung.
     */
    private function memberRegisteredAt($user): ?Carbon
    {
        $value = $user->member_registered_at ?? $user->created_at ?? null;

        return $value ? Carbon::parse($value) : null;
    }

    /**
     * AJAX endpoint untuk modal detail peserta/visitor yang terkonversi
     * untuk event tertentu.
     */
    public function convertedMembers(Request $request, $id)
    {
        $event = Events::findOrFail($id);
        $isSupporting = ($event->event_type === 'Partnership Event');
        $members = $this->convertedMembersFor($event);

        return response()->json([
            'status'        => true,
            'event_name'    => $event->name,
            'event_type'    => $event->event_type ?: ($isSupporting ? 'Partnership Event' : 'DMC Event'),
            'is_supporting' => $isSupporting,
            'count'         => count($members),
            'members'       => $members,
        ]);
    }

    /**
     * AJAX for the clickable "Converted Members" on the diagram cards: everyone
     * counted as converted across all events of one type in the selected period
     * (same events and rules as the card's number).
     */
    public function convertedMembersSummary(Request $request)
    {
        $type = in_array($request->query('type'), ['owned', 'supporting', 'all'], true) ? $request->query('type') : 'supporting';
        // "Converted Members" = Active only (same as the card number); Pending Review asks for status=pending.
        $status = $request->query('status') === 'pending' ? 'pending' : 'active';
        $year = (string) $request->query('year', 'all');

        $members = [];
        foreach ($this->filteredEvents($year, $type, trim((string) $request->query('search', ''))) as $event) {
            foreach ($this->convertedMembersFor($event) as $member) {
                if ($member['status_member'] !== $status) {
                    continue;
                }
                $member['event'] = $event->name;
                $member['event_type'] = $event->event_type === 'Partnership Event' ? 'Partnership' : 'DMC Owned';
                $members[] = $member;
            }
        }

        return response()->json([
            'status'  => true,
            'type'    => $type,
            'period'  => $this->periodLabel($year),
            'count'   => count($members),
            'columns' => [
                ['event_type', 'Type'], ['event', 'Event'], ['name', 'Name'], ['email', 'Email'], ['company', 'Company'],
                ['job_title', 'Job Title'], ['status_member', 'Status'], ['source', 'Source'], ['converted_at', 'Registered'],
            ],
            'members' => $members,
        ]);
    }

    /**
     * AJAX for the clickable "Sponsorship Interest": Active members who registered for
     * a DMC owned event or visited a partnership booth and showed sponsorship interest.
     */
    public function interestSummary(Request $request)
    {
        $year = (string) $request->query('year', 'all');

        $rows = [];
        foreach ($this->filteredEvents($year, 'all', trim((string) $request->query('search', ''))) as $event) {
            $rows = array_merge($rows, $event->event_type === 'Partnership Event'
                ? $this->sponsorInterestRows($event, PartnershipEventVisitor::where('events_id', $event->id)->get())
                : $this->ownedSponsorInterestRows($event, Payment::where('events_id', $event->id)->get(['member_id'])));
        }

        return response()->json([
            'status'  => true,
            'period'  => $this->periodLabel($year),
            'count'   => count($rows),
            'columns' => [
                ['event_type', 'Type'], ['event', 'Event'], ['name', 'Name'], ['email', 'Email'], ['company', 'Company'],
                ['job_title', 'Job Title'], ['reason', 'Counted because'],
            ],
            'members' => $rows,
        ]);
    }

    /**
     * AJAX for the clickable "Entries (Check-in)" on the DMC owned events card —
     * one row per check-in, same rule as attendance_count in calculateOwnedEventMetrics.
     */
    public function checkInSummary(Request $request)
    {
        $year = (string) $request->query('year', 'all');

        $rows = [];
        foreach ($this->filteredEvents($year, 'owned', trim((string) $request->query('search', ''))) as $event) {
            $rows = array_merge($rows, $this->checkInRows($event));
        }

        return response()->json([
            'status'  => true,
            'period'  => $this->periodLabel($year),
            'count'   => count($rows),
            'columns' => [
                ['event', 'Event'], ['name', 'Name'], ['email', 'Email'], ['company', 'Company'], ['job_title', 'Job Title'],
                ['membership', 'Membership'], ['package', 'Package'], ['checked_in', 'Checked in'],
            ],
            'members' => $rows,
        ]);
    }

    /**
     * AJAX for "Registrants" (every status) and "Approved registrants" (?approved=1,
     * Paid Off only) on the DMC owned events card.
     */
    public function registrantSummary(Request $request)
    {
        $year = (string) $request->query('year', 'all');
        $approvedOnly = $request->boolean('approved');

        $rows = [];
        foreach ($this->filteredEvents($year, 'owned', trim((string) $request->query('search', ''))) as $event) {
            $rows = array_merge($rows, $this->registrantRows($event, $approvedOnly));
        }

        return response()->json([
            'status'  => true,
            'period'  => $this->periodLabel($year),
            'count'   => count($rows),
            'columns' => [
                ['event', 'Event'], ['name', 'Name'], ['email', 'Email'], ['company', 'Company'], ['job_title', 'Job Title'],
                ['package', 'Package'], ['registration_status', 'Status'], ['registered', 'Registered'],
            ],
            'members' => $rows,
        ]);
    }

    private function registrantRows(Events $event, bool $approvedOnly): array
    {
        return $this->attendeeQuery()
            ->join('payment', 'payment.member_id', 'users.id')
            ->where('payment.events_id', $event->id)
            ->when($approvedOnly, function ($q) {
                $q->where('payment.status_registration', 'Paid Off');
            })
            ->orderBy('payment.created_at')
            ->get(['users.name', 'users.email', 'company.company_name', 'profiles.job_title', 'payment.package', 'payment.status_registration', 'payment.created_at'])
            ->map(function ($r) use ($event) {
                return [
                    'event'               => $event->name,
                    'name'                => $r->name,
                    'email'               => $r->email,
                    'company'             => $r->company_name,
                    'job_title'           => $r->job_title,
                    'package'             => $r->package,
                    'registration_status' => $r->status_registration ?: '-',
                    'registered'          => $r->created_at ? substr($r->created_at, 0, 10) : '-',
                ];
            })
            ->all();
    }

    private function checkInRows(Events $event): array
    {
        $rows = $this->attendeeQuery()
            ->join('users_event', 'users_event.users_id', 'users.id')
            ->where('users_event.events_id', $event->id)
            ->whereNotNull('users_event.present')
            ->leftJoin('payment', 'payment.id', 'users_event.payment_id')
            ->orderBy('users_event.present')
            ->get(['users.name', 'users.email', 'users.status_member', 'company.company_name', 'profiles.job_title', 'payment.package', 'users_event.present as checked_in']);

        return $rows->map(function ($r) use ($event) {
            return [
                'event'      => $event->name,
                'name'       => $r->name,
                'email'      => $r->email,
                'company'    => $r->company_name,
                'job_title'  => $r->job_title,
                'membership' => $r->status_member === 'active' ? 'Member' : 'Non-member',
                'package'    => $r->package,
                'checked_in' => $r->checked_in,
            ];
        })->all();
    }

    /**
     * AJAX for the clickable "Booth Visitors" on the Partnership events card —
     * every visitor row, same as attendance_count in calculateSupportingEventMetrics.
     */
    public function boothVisitorSummary(Request $request)
    {
        $year = (string) $request->query('year', 'all');
        $events = $this->filteredEvents($year, 'supporting', trim((string) $request->query('search', '')));

        $visitors = PartnershipEventVisitor::whereIn('events_id', $events->pluck('id'))->orderBy('created_at')->get();
        $eventNames = $events->pluck('name', 'id');
        $memberStatus = User::whereIn('email', $visitors->pluck('business_email')->filter()->unique())
            ->pluck('status_member', 'email')
            ->mapWithKeys(function ($status, $email) {
                return [strtolower(trim($email)) => $status];
            });

        $rows = $visitors->map(function ($v) use ($eventNames, $memberStatus) {
            $status = $memberStatus->get(strtolower(trim((string) $v->business_email)));

            return [
                'event'      => $eventNames->get($v->events_id),
                'name'       => $v->name,
                'email'      => $v->business_email,
                'mobile'     => $v->mobile_number,
                'company'    => $v->company_name,
                'job_title'  => $v->job_title,
                'membership' => $status === 'active' ? 'Member' : ($status ? ucfirst($status) : 'Not a member'),
                'remarks'    => $v->remarks,
                'visited'    => $v->created_at ? $v->created_at->format('d M Y') : '-',
            ];
        })->values()->all();

        return response()->json([
            'status'  => true,
            'period'  => $this->periodLabel($year),
            'count'   => count($rows),
            'columns' => [
                ['event', 'Event'], ['name', 'Name'], ['email', 'Email'], ['mobile', 'Mobile'], ['company', 'Company'],
                ['job_title', 'Job Title'], ['membership', 'Membership (now)'], ['remarks', 'Remarks'], ['visited', 'Visited'],
            ],
            'members' => $rows,
        ]);
    }

    /** users + latest profile + company, as the base for attendee lists. */
    private function attendeeQuery()
    {
        return DB::table('users')
            ->leftJoin('profiles', 'profiles.id', DB::raw('(SELECT MAX(p2.id) FROM profiles p2 WHERE p2.users_id = users.id)'))
            ->leftJoin('company', 'company.id', 'profiles.company_id');
    }

    /** company.explore — "receive information about corporate sponsorship opportunities". */
    private function hasSponsorshipTick($explore): bool
    {
        return in_array(strtolower(trim((string) $explore)), ['agree', 'aggree', 'true', '1'], true);
    }

    /**
     * Booth visitors of a partnership event who are Active members and showed
     * sponsorship interest: the sponsorship tick on their member profile
     * or a booth Remark mentioning sponsorship. One row per person.
     */
    private function sponsorInterestRows(Events $event, $visitors): array
    {
        $emails = $visitors->pluck('business_email')->filter()->map(fn ($e) => strtolower(trim($e)))->unique()->values();
        if ($emails->isEmpty()) {
            return [];
        }

        $sponsorRemark = $visitors
            ->filter(fn ($v) => !empty($v->business_email) && stripos((string) $v->remarks, 'sponsor') !== false)
            ->map(fn ($v) => strtolower(trim($v->business_email)))
            ->flip();

        return $this->attendeeQuery()
            ->whereIn('users.email', $emails->all())
            ->where('users.status_member', 'active')
            ->get(['users.name', 'users.email', 'company.company_name', 'company.explore', 'profiles.job_title'])
            ->unique(fn ($u) => strtolower(trim($u->email)))
            ->map(function ($u) use ($event, $sponsorRemark) {
                $tick = $this->hasSponsorshipTick($u->explore);
                $remark = $sponsorRemark->has(strtolower(trim($u->email)));
                if (!$tick && !$remark) {
                    return null;
                }

                return [
                    'event_type' => 'Partnership',
                    'event'      => $event->name,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'company'    => $u->company_name,
                    'job_title'  => $u->job_title,
                    'reason'     => $tick ? 'Sponsorship interest' : 'Booth remark: sponsorship',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Registrants of a DMC owned event (any registration status) who are Active members
     * with the sponsorship tick on their member profile. One row per person.
     */
    private function ownedSponsorInterestRows(Events $event, $payments): array
    {
        $userIds = $payments->pluck('member_id')->filter()->unique()->values();
        if ($userIds->isEmpty()) {
            return [];
        }

        return $this->attendeeQuery()
            ->whereIn('users.id', $userIds->all())
            ->where('users.status_member', 'active')
            ->orderBy('users.name')
            ->get(['users.name', 'users.email', 'company.company_name', 'company.explore', 'profiles.job_title'])
            ->filter(fn ($u) => $this->hasSponsorshipTick($u->explore))
            ->map(function ($u) use ($event) {
                return [
                    'event_type' => 'DMC Owned',
                    'event'      => $event->name,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'company'    => $u->company_name,
                    'job_title'  => $u->job_title,
                    'reason'     => 'Sponsorship interest',
                ];
            })
            ->values()
            ->all();
    }

    private function periodLabel(string $year): string
    {
        return $year === 'all' ? 'All Years' : 'Year ' . $year;
    }

    /** Events in the dashboard period, filtered by type ('all' | 'owned' | 'supporting') and search. */
    private function filteredEvents(string $year, string $type, string $search)
    {
        $query = Events::query();

        if ($year !== 'all') {
            $query->where(function ($q) use ($year) {
                $q->whereYear('start_date', $year)
                    ->orWhereYear('end_date', $year);
            });
        }

        if ($type === 'owned') {
            $query->where('event_type', '!=', 'Partnership Event');
        } elseif ($type === 'supporting') {
            $query->where('event_type', 'Partnership Event');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    /**
     * People credited to one event by EventConversionAttribution (Active + Pending Review) —
     * the list behind its "Converted Member" number.
     */
    private function convertedMembersFor(Events $event): array
    {
        $isSupporting = ($event->event_type === 'Partnership Event');
        $credited = $this->attribution->forEvent($event);
        $userIds = array_merge($credited['active'], $credited['pending']);
        if (!$userIds) {
            return [];
        }

        $visitorsByEmail = collect();
        $prospectIds = collect();
        if ($isSupporting) {
            $visitorsByEmail = PartnershipEventVisitor::where('events_id', $event->id)->get()
                ->filter(fn ($v) => !empty($v->business_email))
                ->keyBy(fn ($v) => strtolower(trim($v->business_email)));
        } else {
            $prospectIds = Payment::where('events_id', $event->id)->where('is_membership_prospect', 1)
                ->pluck('member_id')->flip();
        }

        return User::whereIn('id', $userIds)
            ->with(['profile', 'profile.company'])
            ->get()
            ->map(function ($user) use ($isSupporting, $visitorsByEmail, $prospectIds) {
                $visitor = $visitorsByEmail->get(strtolower(trim((string) $user->email)));
                $profile = $user->profile;
                $registeredAt = $this->memberRegisteredAt($user);

                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'phone'         => optional($profile)->phone ?: ($visitor->mobile_number ?? '-'),
                    'company'       => optional($profile)->company ? $profile->company->company_name : ($visitor->company_name ?? '-'),
                    'job_title'     => optional($profile)->job_title ?: ($visitor->job_title ?? '-'),
                    'status_member' => $user->status_member,
                    'source'        => $user->source ?: ($isSupporting ? 'Event Partnership' : 'DMC Event'),
                    'converted_at'  => $registeredAt ? $registeredAt->format('d M Y') : '-',
                    'is_prospect'   => $isSupporting
                        ? (!empty($visitor->remarks) || !empty($visitor->merchandise))
                        : $prospectIds->has($user->id),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Export data konversi event ke Excel.
     */
    public function export(Request $request)
    {
        $selectedYear = $request->query('year', 'all');
        $selectedType = $request->query('type', 'all');
        $search = trim((string) $request->query('search', ''));

        $eventsQuery = Events::query();

        if ($selectedYear !== 'all') {
            $eventsQuery->where(function ($q) use ($selectedYear) {
                $q->whereYear('start_date', $selectedYear)
                    ->orWhereYear('end_date', $selectedYear);
            });
        }

        if ($selectedType === 'owned') {
            $eventsQuery->where('event_type', '!=', 'Partnership Event');
        } elseif ($selectedType === 'supporting') {
            $eventsQuery->where('event_type', 'Partnership Event');
        }

        if ($search !== '') {
            $eventsQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%');
            });
        }

        $events = $eventsQuery->orderBy('start_date', 'desc')->get();
        $processed = [];

        foreach ($events as $event) {
            if ($event->event_type === 'Partnership Event') {
                $processed[] = $this->calculateSupportingEventMetrics($event);
            } else {
                $processed[] = $this->calculateOwnedEventMetrics($event);
            }
        }

        $filename = 'event-member-conversions-' . ($selectedYear !== 'all' ? $selectedYear : 'all') . '-' . date('Ymd-His') . '.xlsx';
        return Excel::download(new EventConversionExport($processed), $filename);
    }
}
