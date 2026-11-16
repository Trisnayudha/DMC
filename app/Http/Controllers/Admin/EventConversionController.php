<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EventConversionExport;
use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\UserRegister;
use App\Models\PartnershipEvent\PartnershipEventVisitor;
use App\Models\Payments\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EventConversionController extends Controller
{
    public function __construct()
    {
        // Auth handled by cms_auth middleware
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

        $cacheKey = 'dmc_event_conv_metrics_v5_' . md5($selectedYear . '_' . $selectedType . '_' . $search);
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
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

            // Hitung conversion rate aggregat funnel
            $ownedBase = max(1, $funnelOwned['attendance'] > 0 ? $funnelOwned['attendance'] : $funnelOwned['registrations']);
            $funnelOwned['rate'] = round(($funnelOwned['converted'] / $ownedBase) * 100, 1);

            $supportingBase = max(1, $funnelSupporting['attendance'] > 0 ? $funnelSupporting['attendance'] : $funnelSupporting['registrations']);
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
            $totalConverted = $totalPending + $totalActive;
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
        $pendingConverted = 0;
        $activeConverted = 0;

        if ($emails->isNotEmpty()) {
            $matchedUsers = User::whereIn('email', $emails)->get($this->conversionUserColumns(['id', 'email', 'status_member', 'source']));

            $verifiedMemberEmails = $matchedUsers->where('status_member', 'active')
                ->where('source', '!=', 'Event Partnership')
                ->pluck('email')
                ->map(fn ($e) => strtolower(trim($e)));

            $enteredByEmail = $this->boothEnteredAt($visitors, $event);

            // Hanya yang mendaftar jadi member di tahun event ini DAN bukan
            // sebelum masuk ke event ini yang dihitung konversi.
            $pendingConverted = $matchedUsers
                ->where('status_member', 'pending')
                ->filter(fn ($u) => $this->convertedByEvent($u, $event, $enteredByEmail->get(strtolower(trim($u->email)))))
                ->count();

            $activeConverted = $matchedUsers->filter(function ($u) use ($event, $enteredByEmail) {
                return $u->status_member === 'active'
                    && $this->convertedByEvent($u, $event, $enteredByEmail->get(strtolower(trim($u->email))))
                    && $this->isPartnershipSource($u->source);
            })->count();
        }
        $verifiedMembersCount = $verifiedMemberEmails->count();

        // Follow up interest = booth visitors already in the users table who showed
        // sponsorship interest (see sponsorInterestRows). Not "Merchandise or Remarks
        // filled": Remarks is mostly a day/visitor-type note, which counted almost everyone.
        $interestCount = count($this->sponsorInterestRows($event, $visitors));

        $totalConverted = $pendingConverted + $activeConverted;
        $nonMemberPool = max(0, $uniqueCount - $verifiedMembersCount);
        $convRate = $nonMemberPool > 0
            ? round(($totalConverted / $nonMemberPool) * 100, 1)
            : ($uniqueCount > 0 ? round(($totalConverted / $uniqueCount) * 100, 1) : 0);

        return (object) [
            'id'                     => $event->id,
            'name'                   => $event->name,
            'slug'                   => $event->slug,
            'event_type'             => $event->event_type ?: 'Partnership Event',
            'is_supporting'          => true,
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

        $attendanceCount = $attendeeRegisters->count();

        // Fallback jika belum check-in scanner tetapi registrasi confirmed ada
        if ($attendanceCount === 0 && $confirmedCount > 0) {
            $attendeeUserIds = $payments->pluck('member_id')->filter()->unique()->values();
            $attendanceCount = $confirmedCount;
        }

        $uniqueCount = $attendeeUserIds->count();

        // Member conversion:
        // Peserta non-member atau prospek yang menjadi pending/active member
        $nonMemberUserIds = $payments->whereIn('package', ['nonmember', 'onsite', 'free', 'table'])
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $prospectUserIds = $payments->where('is_membership_prospect', 1)
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $conversionCandidateIds = $nonMemberUserIds->merge($prospectUserIds)->unique()->values();

        // Batch query all users
        $allUserIds = $attendeeUserIds->merge($conversionCandidateIds)->unique()->values();
        $usersMap = collect();
        if ($allUserIds->isNotEmpty()) {
            $usersMap = User::whereIn('id', $allUserIds)->get($this->conversionUserColumns(['id', 'email', 'status_member', 'source']))->keyBy('id');
        }

        $attendeeEmails = collect();
        $verifiedMemberEmails = collect();
        if ($attendeeUserIds->isNotEmpty()) {
            $attendeeUsers = $attendeeUserIds->map(fn ($id) => $usersMap->get($id))->filter();
            $attendeeEmails = $attendeeUsers->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)))->unique();
            $verifiedMemberEmails = $attendeeUsers->where('status_member', 'active')->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)));
        }
        $verifiedMembersCount = $verifiedMemberEmails->count();

        // Follow up interest (marked as membership prospect)
        $interestCount = $payments->where('is_membership_prospect', 1)->count();

        $pendingConverted = 0;
        $activeConverted = 0;
        if ($conversionCandidateIds->isNotEmpty()) {
            // Konversi = mendaftar jadi member di tahun event dan bukan sebelum
            // mendaftar tiket event ini (lihat convertedByEvent).
            $enteredByMember = $payments->filter(fn ($p) => $p->member_id)
                ->groupBy('member_id')
                ->map(fn ($group) => $group->min('created_at'));

            $candidateUsers = $conversionCandidateIds
                ->map(fn ($id) => $usersMap->get($id))
                ->filter()
                ->filter(fn ($u) => $this->convertedByEvent($u, $event, $enteredByMember->get($u->id)))
                // Registered through a partnership booth link → counted under Partnership, not here.
                ->reject(fn ($u) => $this->isPartnershipSource($u->source));
            $pendingConverted = $candidateUsers->where('status_member', 'pending')->count();
            $activeConverted = $candidateUsers->where('status_member', 'active')->count();
        }

        $totalConverted = $pendingConverted + $activeConverted;
        $nonMemberPool = max(0, $uniqueCount - $verifiedMembersCount);
        $convRate = $nonMemberPool > 0
            ? round(($totalConverted / $nonMemberPool) * 100, 1)
            : ($uniqueCount > 0 ? round(($totalConverted / $uniqueCount) * 100, 1) : 0);

        return (object) [
            'id'                     => $event->id,
            'name'                   => $event->name,
            'slug'                   => $event->slug,
            'event_type'             => $event->event_type ?: 'DMC Event',
            'is_supporting'          => false,
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
     * When each booth visitor (by email) "entered" the event, for the conversion cutoff.
     * Visitor rows have no reliable visit date: created_at is when the list was typed in
     * or imported, often days or months after the event (Mining Balikpapan: June event,
     * list imported in October). Using it excluded everyone who registered at the booth.
     * So the event start date is used; created_at only when the event has no date.
     */
    private function boothEnteredAt($visitors, Events $event)
    {
        return $visitors->filter(fn ($v) => !empty($v->business_email))
            ->groupBy(fn ($v) => strtolower(trim($v->business_email)))
            ->map(fn ($group) => $event->start_date ?: $group->min('created_at'));
    }

    /**
     * Kolom user yang dibutuhkan untuk menentukan tanggal daftar member.
     * member_registered_at hanya ikut di-select kalau migration-nya sudah jalan.
     */
    private function conversionUserColumns(array $base): array
    {
        $columns = array_merge($base, ['created_at']);
        if (User::hasMemberRegisteredAtColumn()) {
            $columns[] = 'member_registered_at';
        }

        return $columns;
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
     * Source member yang berasal dari Partnership Event: 'Event Partnership'
     * atau kode event 'EP/...' (huruf besar/kecil tidak dibedakan).
     */
    private function isPartnershipSource($source): bool
    {
        $source = strtolower(trim((string) $source));

        return $source === 'event partnership' || strpos($source, 'ep/') === 0;
    }

    /**
     * Syarat konversi event: orangnya mendaftar jadi member
     *  (1) di TAHUN YANG SAMA dengan event, dan
     *  (2) bukan sebelum dia masuk ke event itu ($enteredAt = pendaftaran
     *      tiket / kunjungan booth), dengan toleransi 1 hari karena akun
     *      dan pendaftaran event dibuat berurutan dalam satu alur.
     * Member yang sudah daftar sebelumnya (tahun lalu, atau lebih awal di
     * tahun yang sama) lalu ikut event hanyalah peserta member, bukan
     * konversi dari event ini. Event tanpa tanggal tidak bisa dicocokkan
     * tahunnya, jadi syarat (1) dilewati.
     */
    private function convertedByEvent($user, Events $event, $enteredAt = null): bool
    {
        $registeredAt = $this->memberRegisteredAt($user);
        if ($registeredAt === null) {
            return false;
        }

        $eventDate = $event->start_date ?: $event->end_date;
        if ($eventDate && $registeredAt->year !== Carbon::parse($eventDate)->year) {
            return false;
        }

        if ($enteredAt) {
            return $registeredAt->greaterThanOrEqualTo(Carbon::parse($enteredAt)->subDay());
        }

        return true;
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
        $status = in_array($request->query('status'), ['pending', 'active'], true) ? $request->query('status') : null;
        $year = (string) $request->query('year', 'all');

        $members = [];
        foreach ($this->filteredEvents($year, $type, trim((string) $request->query('search', ''))) as $event) {
            foreach ($this->convertedMembersFor($event) as $member) {
                if ($status !== null && $member['status_member'] !== $status) {
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
     * AJAX for the clickable "Stated Interest" / "Follow up interest": DMC owned
     * registrations flagged "Potential Member", plus partnership booth visitors in
     * the users table with sponsorship interest.
     */
    public function interestSummary(Request $request)
    {
        $year = (string) $request->query('year', 'all');

        $rows = [];
        foreach ($this->filteredEvents($year, 'all', trim((string) $request->query('search', ''))) as $event) {
            $rows = array_merge($rows, $event->event_type === 'Partnership Event'
                ? $this->sponsorInterestRows($event, PartnershipEventVisitor::where('events_id', $event->id)->get())
                : $this->prospectInterestRows($event));
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

        // No scanner check-ins yet → the count falls back to confirmed registrations.
        if ($rows->isEmpty()) {
            $rows = $this->attendeeQuery()
                ->join('payment', 'payment.member_id', 'users.id')
                ->where('payment.events_id', $event->id)
                ->where('payment.status_registration', 'Paid Off')
                ->orderBy('payment.created_at')
                ->get(['users.name', 'users.email', 'users.status_member', 'company.company_name', 'profiles.job_title', 'payment.package', DB::raw("'Not scanned (confirmed registration)' as checked_in")]);
        }

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

    /**
     * Booth visitors of a partnership event who are already in the users table and showed
     * sponsorship interest: the sponsorship tick on their member profile
     * (company.explore — "receive information about corporate sponsorship opportunities")
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
            ->get(['users.name', 'users.email', 'company.company_name', 'company.explore', 'profiles.job_title'])
            ->unique(fn ($u) => strtolower(trim($u->email)))
            ->map(function ($u) use ($event, $sponsorRemark) {
                $tick = in_array(strtolower(trim((string) $u->explore)), ['agree', 'aggree', 'true', '1'], true);
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
                    'reason'     => $tick && $remark ? 'Sponsorship tick + booth remark'
                        : ($tick ? 'Sponsorship tick on member profile' : 'Booth remark: sponsorship'),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /** DMC owned registrations flagged "Potential Member" (same rule as calculateOwnedEventMetrics). */
    private function prospectInterestRows(Events $event): array
    {
        return Payment::where('payment.events_id', $event->id)
            ->where('payment.is_membership_prospect', 1)
            ->leftJoin('users', 'users.id', 'payment.member_id')
            ->leftJoin('profiles', 'profiles.id', DB::raw('(SELECT MAX(p2.id) FROM profiles p2 WHERE p2.users_id = users.id)'))
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->orderBy('payment.created_at')
            ->get(['users.name', 'users.email', 'company.company_name', 'profiles.job_title'])
            ->map(function ($p) use ($event) {
                return [
                    'event_type'  => 'DMC Owned',
                    'event'       => $event->name,
                    'name'        => $p->name,
                    'email'       => $p->email,
                    'company'     => $p->company_name,
                    'job_title'   => $p->job_title,
                    'reason'      => 'Potential Member flag',
                    'remarks'     => '',
                    'merchandise' => '',
                ];
            })
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

    /** People counted as converted by one event (the list behind its "Converted Member" number). */
    private function convertedMembersFor(Events $event): array
    {
        $isSupporting = ($event->event_type === 'Partnership Event');

        $members = [];

        if ($isSupporting) {
            $visitors = PartnershipEventVisitor::where('events_id', $event->id)->get();
            $emails = $visitors->pluck('business_email')
                ->filter()
                ->map(fn ($e) => strtolower(trim($e)))
                ->unique()
                ->values();

            $enteredByEmail = $this->boothEnteredAt($visitors, $event);

            if ($emails->isNotEmpty()) {
                $users = User::whereIn('email', $emails)
                    ->whereIn('status_member', ['pending', 'active'])
                    ->with(['profile', 'profile.company'])
                    ->get()
                    ->filter(fn ($u) => $this->convertedByEvent($u, $event, $enteredByEmail->get(strtolower(trim($u->email)))))
                    // Sama dengan kartu: yang sudah active dihitung konversi hanya
                    // kalau memang daftar lewat Partnership Event (source EP/...).
                    ->filter(fn ($u) => $u->status_member === 'pending' || $this->isPartnershipSource($u->source));

                foreach ($users as $user) {
                    $userEmail = strtolower(trim((string) $user->email));
                    $visitor = $visitors->first(function ($v) use ($userEmail) {
                        return strtolower(trim((string) $v->business_email)) === $userEmail;
                    });

                    $companyName = optional($user->profile)->company ? $user->profile->company->company_name : ($visitor->company_name ?? '-');
                    $jobTitle = optional($user->profile)->job_title ?: ($visitor->job_title ?? '-');
                    $phone = optional($user->profile)->phone ?: ($visitor->mobile_number ?? '-');

                    $members[] = [
                        'id'            => $user->id,
                        'name'          => $user->name,
                        'email'         => $user->email,
                        'phone'         => $phone,
                        'company'       => $companyName,
                        'job_title'     => $jobTitle,
                        'status_member' => $user->status_member,
                        'source'        => $user->source ?: 'Event Partnership',
                        'converted_at'  => $this->memberRegisteredAt($user) ? $this->memberRegisteredAt($user)->format('d M Y') : '-',
                        'is_prospect'   => !empty($visitor->remarks) || !empty($visitor->merchandise),
                    ];
                }
            }
        } else {
            $payments = Payment::where('events_id', $event->id)
                ->where(function ($q) {
                    $q->whereIn('package', ['nonmember', 'onsite', 'free', 'table'])
                        ->orWhere('is_membership_prospect', 1);
                })
                ->get();

            $memberIds = $payments->pluck('member_id')->filter()->unique()->values();
            $enteredByMember = $payments->filter(fn ($p) => $p->member_id)
                ->groupBy('member_id')
                ->map(fn ($group) => $group->min('created_at'));

            if ($memberIds->isNotEmpty()) {
                $users = User::whereIn('id', $memberIds)
                    ->whereIn('status_member', ['pending', 'active'])
                    ->with(['profile', 'profile.company'])
                    ->get()
                    ->filter(fn ($u) => $this->convertedByEvent($u, $event, $enteredByMember->get($u->id)))
                    // Same as the card: partnership-link members are counted under Partnership.
                    ->reject(fn ($u) => $this->isPartnershipSource($u->source));

                foreach ($users as $user) {
                    $payment = $payments->firstWhere('member_id', $user->id);
                    $companyName = optional($user->profile)->company ? $user->profile->company->company_name : '-';
                    $jobTitle = optional($user->profile)->job_title ?: '-';
                    $phone = optional($user->profile)->phone ?: '-';

                    $members[] = [
                        'id'            => $user->id,
                        'name'          => $user->name,
                        'email'         => $user->email,
                        'phone'         => $phone,
                        'company'       => $companyName,
                        'job_title'     => $jobTitle,
                        'status_member' => $user->status_member,
                        'source'        => $user->source ?: 'DMC Event',
                        'converted_at'  => $this->memberRegisteredAt($user) ? $this->memberRegisteredAt($user)->format('d M Y') : '-',
                        'is_prospect'   => (bool) ($payment->is_membership_prospect ?? false),
                    ];
                }
            }
        }

        return $members;
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
