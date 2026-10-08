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

        // Query events
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

        $cacheKey = 'dmc_event_conv_metrics_' . md5($selectedYear . '_' . $selectedType . '_' . $search);
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $metrics = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($events) {
            $processedEvents = [];
            $ownedCount = 0;
            $supportingCount = 0;

            $funnelOwned = [
                'events'        => 0,
                'registrations' => 0,
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

            foreach ($events as $event) {
                $isSupporting = ($event->event_type === 'Partnership Event');

                if ($isSupporting) {
                    $supportingCount++;
                    $eventData = $this->calculateSupportingEventMetrics($event);
                    $funnelSupporting['events']++;
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
                    $funnelOwned['confirmed']     += $eventData->confirmed_count;
                    $funnelOwned['attendance']    += $eventData->attendance_count;
                    $funnelOwned['interest']      += $eventData->interest_count;
                    $funnelOwned['pending']       += $eventData->pending_converted;
                    $funnelOwned['active']        += $eventData->active_converted;
                    $funnelOwned['converted']     += $eventData->total_converted;
                }

                $totalEntries += $eventData->attendance_count;

                if (!empty($eventData->attendee_emails)) {
                    $allUniqueAttendeeEmails = $allUniqueAttendeeEmails->merge($eventData->attendee_emails);
                }
                if (!empty($eventData->verified_member_emails)) {
                    $allVerifiedMemberEmails = $allVerifiedMemberEmails->merge($eventData->verified_member_emails);
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

        return view('admin.event_conversions.index', [
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
        ]);
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

            // Waktu pertama tiap email masuk ke booth event ini.
            $enteredByEmail = $visitors->filter(fn ($v) => !empty($v->business_email))
                ->groupBy(fn ($v) => strtolower(trim($v->business_email)))
                ->map(fn ($group) => $group->min('created_at'));

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

        // Follow up interest (giveaway, merchandise, remarks)
        $interestCount = $visitors->filter(function ($v) {
            return !empty($v->merchandise) || !empty($v->remarks);
        })->count();

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
        $regCount = $payments->count();

        // Confirmed / Paid Off registrations
        $confirmedCount = $payments->filter(function ($p) {
            return in_array($p->status_registration, ['Paid Off', 'Confirmed', 'paid', 'approved'], true)
                || $p->package === 'free';
        })->count();

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
            $usersMap = User::whereIn('id', $allUserIds)->get($this->conversionUserColumns(['id', 'email', 'status_member']))->keyBy('id');
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
                ->filter(fn ($u) => $this->convertedByEvent($u, $event, $enteredByMember->get($u->id)));
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

        $members = [];

        if ($isSupporting) {
            $visitors = PartnershipEventVisitor::where('events_id', $event->id)->get();
            $emails = $visitors->pluck('business_email')
                ->filter()
                ->map(fn ($e) => strtolower(trim($e)))
                ->unique()
                ->values();

            $enteredByEmail = $visitors->filter(fn ($v) => !empty($v->business_email))
                ->groupBy(fn ($v) => strtolower(trim($v->business_email)))
                ->map(fn ($group) => $group->min('created_at'));

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
                    ->filter(fn ($u) => $this->convertedByEvent($u, $event, $enteredByMember->get($u->id)));

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
