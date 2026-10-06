<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MailchimpUnsubscribeExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MailchimpUnsubscribeController extends Controller
{
    const CACHE_TTL_MINUTES   = 5;
    const CACHE_KEY_MEMBERS    = 'mailchimp_unsubscribed_members_data';
    const CACHE_KEY_STATS      = 'mailchimp_audience_stats_data';
    const CACHE_KEY_ACTIVITIES = 'mailchimp_unsubscribed_activities_data';

    public function __construct()
    {
        // Auth handled by cms_auth middleware
    }

    /**
     * Tampilan daftar kontak yang unsubscribed dari Mailchimp
     * dengan pencocokan status keanggotaan DMC.
     */
    public function index(Request $request)
    {
        $config = $this->mailchimpConfig();
        if (!$config) {
            return view('admin.mailchimp.unsubscribes', [
                'configured'         => false,
                'errorMessage'       => 'Konfigurasi Mailchimp belum lengkap. Pastikan MAILCHIMP_APIKEY dan MAILCHIMP_LIST_ID terisi di .env.',
                'contacts'           => new LengthAwarePaginator([], 0, 25),
                'stats'              => [],
                'filter'             => 'all',
                'search'             => '',
                'campaign'           => '',
                'perPage'            => 25,
                'dateFrom'           => '',
                'dateTo'             => '',
                'month'              => null,
                'year'               => null,
                'preset'             => '',
                'availableYears'     => collect([now()->year]),
                'availableCampaigns' => collect(),
                'hasDateFilter'      => false,
            ]);
        }

        // Force refresh cache jika ada parameter ?refresh=1
        if ($request->boolean('refresh')) {
            Cache::forget(self::CACHE_KEY_MEMBERS);
            Cache::forget(self::CACHE_KEY_STATS);
            Cache::forget(self::CACHE_KEY_ACTIVITIES);
        }

        // Ambil data dari Mailchimp (dengan cache)
        try {
            $rawMembers = Cache::remember(self::CACHE_KEY_MEMBERS, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($config) {
                return $this->fetchUnsubscribedMembersFromMailchimp($config);
            });

            $listStats = Cache::remember(self::CACHE_KEY_STATS, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($config) {
                return $this->fetchListStatsFromMailchimp($config);
            });

            $rawActivities = Cache::remember(self::CACHE_KEY_ACTIVITIES, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($config, $rawMembers) {
                return $this->fetchActivitiesForMembersFromMailchimp($config, $rawMembers);
            });
        } catch (\Throwable $e) {
            Log::warning('MailchimpUnsubscribeController: failed to fetch: ' . $e->getMessage());
            return view('admin.mailchimp.unsubscribes', [
                'configured'         => true,
                'errorMessage'       => 'Gagal menghubungi server Mailchimp: ' . $e->getMessage(),
                'contacts'           => new LengthAwarePaginator([], 0, 25),
                'stats'              => [],
                'filter'             => 'all',
                'search'             => '',
                'campaign'           => '',
                'perPage'            => 25,
                'dateFrom'           => '',
                'dateTo'             => '',
                'month'              => null,
                'year'               => null,
                'preset'             => '',
                'availableYears'     => collect([now()->year]),
                'availableCampaigns' => collect(),
                'hasDateFilter'      => false,
            ]);
        }

        // Cross-reference dengan tabel users di database DMC dan sertakan info aktivitas unsubscribe
        $allEnrichedContacts = $this->enrichContactsWithDmcData($rawMembers, $rawActivities);

        // Parameters Filter & Search
        $search   = trim((string) $request->query('search', ''));
        $filter   = (string) $request->query('filter', 'all'); // 'all', 'active_member', 'other_dmc', 'non_member'
        $campaign = trim((string) $request->query('campaign', ''));
        $perPage  = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo   = trim((string) $request->query('date_to', ''));
        $month    = $request->query('month');
        $year     = $request->query('year');
        $preset   = trim((string) $request->query('preset', ''));

        // Handle preset shortcut jika date_from dan date_to belum diisi
        if ($preset !== '' && empty($dateFrom) && empty($dateTo) && empty($month) && empty($year)) {
            switch ($preset) {
                case 'today':
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo   = now()->format('Y-m-d');
                    break;
                case 'yesterday':
                    $dateFrom = now()->subDay()->format('Y-m-d');
                    $dateTo   = now()->subDay()->format('Y-m-d');
                    break;
                case 'last_7_days':
                    $dateFrom = now()->subDays(6)->format('Y-m-d');
                    $dateTo   = now()->format('Y-m-d');
                    break;
                case 'last_30_days':
                    $dateFrom = now()->subDays(29)->format('Y-m-d');
                    $dateTo   = now()->format('Y-m-d');
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo   = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'last_month':
                    $dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d');
                    $dateTo   = now()->subMonth()->endOfMonth()->format('Y-m-d');
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->format('Y-m-d');
                    $dateTo   = now()->endOfYear()->format('Y-m-d');
                    break;
            }
        }

        $hasDateFilter = (!empty($dateFrom) || !empty($dateTo) || !empty($month) || !empty($year) || !empty($preset));

        // Available years & campaigns for dropdown
        $availableYears = $allEnrichedContacts->pluck('unsubscribed_year')->filter()->unique()->sortDesc()->values();
        if ($availableYears->isEmpty()) {
            $availableYears = collect([now()->year, now()->year - 1]);
        }

        $availableCampaigns = $allEnrichedContacts->pluck('unsub_campaign_title')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Terapkan filter tanggal terlebih dahulu
        $dateFilteredContacts = $this->applyDateFilter($allEnrichedContacts, $dateFrom, $dateTo, $month, $year);

        // Filter campaign jika dipilih
        if ($campaign !== '') {
            $dateFilteredContacts = $dateFilteredContacts->filter(function ($c) use ($campaign) {
                if ($campaign === '__admin__') {
                    return $c->is_admin_unsub;
                }
                return $c->unsub_campaign_title === $campaign;
            });
        }

        // Hitung total stat untuk periode yang dipilih
        $totalUnsubscribed  = $dateFilteredContacts->count();
        $activeMembersCount = $dateFilteredContacts->where('dmc_status', 'active')->count();
        $otherDmcCount      = $dateFilteredContacts->filter(fn ($c) => $c->is_dmc_user && $c->dmc_status !== 'active')->count();
        $nonMemberCount     = $dateFilteredContacts->where('is_dmc_user', false)->count();

        // Terapkan filter tab
        $filtered = $dateFilteredContacts;

        if ($filter === 'active_member') {
            $filtered = $filtered->where('dmc_status', 'active');
        } elseif ($filter === 'other_dmc') {
            $filtered = $filtered->filter(fn ($c) => $c->is_dmc_user && $c->dmc_status !== 'active');
        } elseif ($filter === 'non_member') {
            $filtered = $filtered->where('is_dmc_user', false);
        }

        // Terapkan search (mencakup email, nama, PT, alasan, dan nama email/campaign)
        if ($search !== '') {
            $needle = strtolower($search);
            $filtered = $filtered->filter(function ($c) use ($needle) {
                return str_contains(strtolower($c->email), $needle)
                    || str_contains(strtolower($c->full_name), $needle)
                    || str_contains(strtolower($c->dmc_name ?? ''), $needle)
                    || str_contains(strtolower($c->company ?? ''), $needle)
                    || str_contains(strtolower($c->reason ?? ''), $needle)
                    || str_contains(strtolower($c->unsub_campaign_title ?? ''), $needle)
                    || str_contains(strtolower($c->unsub_campaign_id ?? ''), $needle);
            });
        }

        // Paginasi manual untuk Collection
        $page = (int) $request->query('page', 1);
        $total = $filtered->count();
        $items = $filtered->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        $stats = [
            'total_unsubscribed'    => $totalUnsubscribed,
            'all_time_unsubscribed' => $allEnrichedContacts->count(),
            'active_members_count'  => $activeMembersCount,
            'other_dmc_count'       => $otherDmcCount,
            'non_member_count'      => $nonMemberCount,
            'member_count'          => $listStats['member_count'] ?? 0,
            'cleaned_count'         => $listStats['cleaned_count'] ?? 0,
        ];

        return view('admin.mailchimp.unsubscribes', [
            'configured'         => true,
            'errorMessage'       => null,
            'contacts'           => $paginator,
            'stats'              => $stats,
            'filter'             => $filter,
            'search'             => $search,
            'campaign'           => $campaign,
            'perPage'            => $perPage,
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'month'              => $month,
            'year'               => $year,
            'preset'             => $preset,
            'availableYears'     => $availableYears,
            'availableCampaigns' => $availableCampaigns,
            'hasDateFilter'      => $hasDateFilter,
            'server'             => $config['server'],
            'listId'             => $config['listId'],
        ]);
    }

    /**
     * AJAX endpoint: Riwayat lengkap aktivitas email untuk 1 subscriber.
     */
    public function memberActivity(Request $request, string $hash)
    {
        $config = $this->mailchimpConfig();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi Mailchimp belum lengkap.'], 400);
        }

        try {
            $response = Http::withBasicAuth('anystring', $config['apiKey'])
                ->timeout(12)
                ->get("https://{$config['server']}.api.mailchimp.com/3.0/lists/{$config['listId']}/members/{$hash}/activity");

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil riwayat dari Mailchimp: ' . ($response->json('detail') ?: 'HTTP Error'),
                ], 400);
            }

            $raw = $response->json('activity') ?? [];
            $timezone = config('app.timezone', 'Asia/Jakarta');

            $activities = collect($raw)->map(function ($act) use ($timezone) {
                $time = !empty($act['timestamp']) ? Carbon::parse($act['timestamp'])->setTimezone($timezone) : null;
                return [
                    'action'      => $act['action'] ?? 'unknown', // unsub, open, click, sent, bounce
                    'title'       => $act['title'] ?? ($act['type'] === 'A' ? 'Unsubscribed by Admin' : '-'),
                    'campaign_id' => $act['campaign_id'] ?? null,
                    'type'        => $act['type'] ?? null,
                    'url'         => $act['url'] ?? null,
                    'ip'          => $act['ip'] ?? null,
                    'time'        => $time ? $time->format('d M Y, H:i') : '-',
                    'time_human'  => $time ? $time->diffForHumans() : '-',
                ];
            });

            return response()->json([
                'success'    => true,
                'email'      => $request->query('email', ''),
                'hash'       => $hash,
                'activities' => $activities,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Resubscribe kembali email ke Mailchimp (misal jika member meminta di-subscribe kembali).
     */
    public function resubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim((string) $request->email));
        $config = $this->mailchimpConfig();
        if (!$config) {
            return back()->with('error', 'Konfigurasi Mailchimp belum lengkap.');
        }

        try {
            $hash = md5($email);
            $response = Http::withBasicAuth('anystring', $config['apiKey'])
                ->timeout(15)
                ->put("https://{$config['server']}.api.mailchimp.com/3.0/lists/{$config['listId']}/members/{$hash}", [
                    'status' => 'subscribed',
                ]);

            if (!$response->successful()) {
                $detail = $response->json('detail') ?: 'Respon gagal dari Mailchimp API';
                return back()->with('error', "Gagal re-subscribe {$email}: " . $detail);
            }

            // Hapus cache agar data langsung diperbarui
            Cache::forget(self::CACHE_KEY_MEMBERS);
            Cache::forget(self::CACHE_KEY_STATS);
            Cache::forget(self::CACHE_KEY_ACTIVITIES);

            return back()->with('success', "Kontak {$email} berhasil di-subscribe ulang ke Mailchimp.");
        } catch (\Throwable $e) {
            Log::warning('Mailchimp resubscribe failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal re-subscribe: ' . $e->getMessage());
        }
    }

    /**
     * Export daftar unsubscribed ke Excel.
     */
    public function export(Request $request)
    {
        $config = $this->mailchimpConfig();
        if (!$config) {
            return back()->with('error', 'Konfigurasi Mailchimp belum lengkap.');
        }

        try {
            $rawMembers = Cache::remember(self::CACHE_KEY_MEMBERS, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($config) {
                return $this->fetchUnsubscribedMembersFromMailchimp($config);
            });

            $rawActivities = Cache::remember(self::CACHE_KEY_ACTIVITIES, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($config, $rawMembers) {
                return $this->fetchActivitiesForMembersFromMailchimp($config, $rawMembers);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengambil data dari Mailchimp: ' . $e->getMessage());
        }

        $allContacts = $this->enrichContactsWithDmcData($rawMembers, $rawActivities);

        $filter   = (string) $request->query('filter', 'all');
        $search   = trim((string) $request->query('search', ''));
        $campaign = trim((string) $request->query('campaign', ''));

        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo   = trim((string) $request->query('date_to', ''));
        $month    = $request->query('month');
        $year     = $request->query('year');
        $preset   = trim((string) $request->query('preset', ''));

        // Handle preset shortcut jika date_from dan date_to belum diisi
        if ($preset !== '' && empty($dateFrom) && empty($dateTo) && empty($month) && empty($year)) {
            switch ($preset) {
                case 'today':
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo   = now()->format('Y-m-d');
                    break;
                case 'yesterday':
                    $dateFrom = now()->subDay()->format('Y-m-d');
                    $dateTo   = now()->subDay()->format('Y-m-d');
                    break;
                case 'last_7_days':
                    $dateFrom = now()->subDays(6)->format('Y-m-d');
                    $dateTo   = now()->format('Y-m-d');
                    break;
                case 'last_30_days':
                    $dateFrom = now()->subDays(29)->format('Y-m-d');
                    $dateTo   = now()->format('Y-m-d');
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo   = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'last_month':
                    $dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d');
                    $dateTo   = now()->subMonth()->endOfMonth()->format('Y-m-d');
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->format('Y-m-d');
                    $dateTo   = now()->endOfYear()->format('Y-m-d');
                    break;
            }
        }

        // Terapkan filter tanggal
        $allContacts = $this->applyDateFilter($allContacts, $dateFrom, $dateTo, $month, $year);

        // Filter campaign jika dipilih
        if ($campaign !== '') {
            $allContacts = $allContacts->filter(function ($c) use ($campaign) {
                if ($campaign === '__admin__') {
                    return $c->is_admin_unsub;
                }
                return $c->unsub_campaign_title === $campaign;
            });
        }

        // Terapkan filter status
        if ($filter === 'active_member') {
            $allContacts = $allContacts->where('dmc_status', 'active');
        } elseif ($filter === 'other_dmc') {
            $allContacts = $allContacts->filter(fn ($c) => $c->is_dmc_user && $c->dmc_status !== 'active');
        } elseif ($filter === 'non_member') {
            $allContacts = $allContacts->where('is_dmc_user', false);
        }

        // Terapkan search
        if ($search !== '') {
            $needle = strtolower($search);
            $allContacts = $allContacts->filter(function ($c) use ($needle) {
                return str_contains(strtolower($c->email), $needle)
                    || str_contains(strtolower($c->full_name), $needle)
                    || str_contains(strtolower($c->dmc_name ?? ''), $needle)
                    || str_contains(strtolower($c->company ?? ''), $needle)
                    || str_contains(strtolower($c->reason ?? ''), $needle)
                    || str_contains(strtolower($c->unsub_campaign_title ?? ''), $needle)
                    || str_contains(strtolower($c->unsub_campaign_id ?? ''), $needle);
            });
        }

        $filename = 'mailchimp-unsubscribes-' . date('Ymd-His') . '.xlsx';
        return Excel::download(new MailchimpUnsubscribeExport($allContacts), $filename);
    }

    /**
     * Filter koleksi kontak berdasarkan range tanggal, bulan, atau tahun.
     */
    private function applyDateFilter(Collection $contacts, ?string $dateFrom, ?string $dateTo, $month = null, $year = null): Collection
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');

        if (!empty($dateFrom)) {
            try {
                $fromTimestamp = Carbon::parse($dateFrom, $timezone)->startOfDay()->timestamp;
                $contacts = $contacts->filter(function ($c) use ($fromTimestamp) {
                    return $c->unsubscribed_timestamp >= $fromTimestamp;
                });
            } catch (\Throwable $e) {
                Log::debug('Invalid date_from: ' . $dateFrom);
            }
        }

        if (!empty($dateTo)) {
            try {
                $toTimestamp = Carbon::parse($dateTo, $timezone)->endOfDay()->timestamp;
                $contacts = $contacts->filter(function ($c) use ($toTimestamp) {
                    return $c->unsubscribed_timestamp > 0 && $c->unsubscribed_timestamp <= $toTimestamp;
                });
            } catch (\Throwable $e) {
                Log::debug('Invalid date_to: ' . $dateTo);
            }
        }

        if (!empty($month)) {
            $monthInt = (int) $month;
            $contacts = $contacts->filter(function ($c) use ($monthInt) {
                return $c->unsubscribed_month === $monthInt;
            });
        }

        if (!empty($year)) {
            $yearInt = (int) $year;
            $contacts = $contacts->filter(function ($c) use ($yearInt) {
                return $c->unsubscribed_year === $yearInt;
            });
        }

        return $contacts;
    }

    /**
     * Fetch seluruh kontak unsubscribed dari Mailchimp API v3.
     */
    private function fetchUnsubscribedMembersFromMailchimp(array $config): array
    {
        $allMembers = [];
        $offset = 0;
        $count = 100; // Mailchimp count per batch

        do {
            $response = Http::withBasicAuth('anystring', $config['apiKey'])
                ->timeout(20)
                ->get("https://{$config['server']}.api.mailchimp.com/3.0/lists/{$config['listId']}/members", [
                    'status'     => 'unsubscribed',
                    'count'      => $count,
                    'offset'     => $offset,
                    'sort_field' => 'last_changed',
                    'sort_dir'   => 'DESC',
                ]);

            if (!$response->successful()) {
                throw new \Exception($response->json('detail') ?: 'HTTP Error ' . $response->status());
            }

            $data = $response->json();
            $members = $data['members'] ?? [];
            $totalItems = (int) ($data['total_items'] ?? 0);

            $allMembers = array_merge($allMembers, $members);
            $offset += count($members);

            // Jika sudah mencapai total atau batch kosong, hentikan
            if (empty($members) || $offset >= $totalItems) {
                break;
            }
        } while (true);

        return $allMembers;
    }

    /**
     * Fetch statistik list audience Mailchimp.
     */
    private function fetchListStatsFromMailchimp(array $config): array
    {
        $response = Http::withBasicAuth('anystring', $config['apiKey'])
            ->timeout(10)
            ->get("https://{$config['server']}.api.mailchimp.com/3.0/lists/{$config['listId']}", [
                'fields' => 'stats.member_count,stats.unsubscribe_count,stats.cleaned_count',
            ]);

        if ($response->successful()) {
            return $response->json('stats') ?: [];
        }

        return [];
    }

    /**
     * Cocokkan data kontak Mailchimp dengan data di tabel users & profiles DMC
     * dan sematkan data aktivitas unsubscribe (email/campaign asal).
     */
    private function enrichContactsWithDmcData(array $rawMembers, array $rawActivities = []): Collection
    {
        $emails = collect($rawMembers)
            ->pluck('email_address')
            ->filter()
            ->map(fn ($e) => strtolower(trim($e)))
            ->unique()
            ->values();

        $dmcUsers = collect();
        if ($emails->isNotEmpty()) {
            $dmcUsers = User::whereIn('email', $emails)
                ->with(['profile', 'profile.company'])
                ->get()
                ->keyBy(fn ($u) => strtolower(trim($u->email)));
        }

        $timezone = config('app.timezone', 'Asia/Jakarta');

        return collect($rawMembers)->map(function ($m) use ($dmcUsers, $rawActivities, $timezone) {
            $email = strtolower(trim((string) ($m['email_address'] ?? '')));
            $dmcUser = $dmcUsers->get($email);

            $lastChanged = !empty($m['last_changed'])
                ? Carbon::parse($m['last_changed'])->setTimezone($timezone)
                : null;
            $reason = trim((string) ($m['unsubscribe_reason'] ?? ''));
            if ($reason === '' || strtolower($reason) === 'none given') {
                $reason = 'None given';
            }

            $fname = trim((string) ($m['merge_fields']['FNAME'] ?? ''));
            $lname = trim((string) ($m['merge_fields']['LNAME'] ?? ''));
            $fullName = trim($fname . ' ' . $lname) ?: ($m['full_name'] ?? '-');

            $companyName = optional(optional($dmcUser)->profile)->company
                ? $dmcUser->profile->company->company_name
                : ($m['merge_fields']['COMPANY'] ?? null);

            $jobTitle = optional(optional($dmcUser)->profile)->job_title
                ?: ($m['merge_fields']['JOBTITLE'] ?? null);

            $phone = optional(optional($dmcUser)->profile)->phone
                ?: ($m['merge_fields']['PHONE'] ?? null);

            // Cari aktivitas unsubscribe dari list aktivitas
            $activities = $rawActivities[$email] ?? [];
            $unsubAct = null;
            foreach ($activities as $act) {
                if (($act['action'] ?? '') === 'unsub') {
                    $unsubAct = $act;
                    break;
                }
            }

            $unsubCampaignTitle = !empty($unsubAct['title']) ? trim($unsubAct['title']) : null;
            $unsubCampaignId    = !empty($unsubAct['campaign_id']) ? trim($unsubAct['campaign_id']) : null;
            $unsubType          = $unsubAct['type'] ?? null;
            $isAdminUnsub       = ($unsubType === 'A' || stripos($reason, 'admin') !== false);

            return (object) [
                'id'                     => $m['id'] ?? md5($email),
                'hash'                   => md5($email),
                'web_id'                 => $m['web_id'] ?? null,
                'email'                  => $m['email_address'] ?? '-',
                'full_name'              => $fullName,
                'rating'                 => (int) ($m['member_rating'] ?? 0),
                'reason'                 => $reason,
                'unsubscribed_at'        => $lastChanged ? $lastChanged->format('d M Y, H:i') : '-',
                'unsubscribed_human'     => $lastChanged ? $lastChanged->diffForHumans() : '-',
                'unsubscribed_timestamp' => $lastChanged ? $lastChanged->timestamp : 0,
                'unsubscribed_date'      => $lastChanged ? $lastChanged->format('Y-m-d') : null,
                'unsubscribed_month'     => $lastChanged ? (int) $lastChanged->format('n') : null,
                'unsubscribed_year'      => $lastChanged ? (int) $lastChanged->format('Y') : null,
                'unsub_campaign_title'   => $unsubCampaignTitle,
                'unsub_campaign_id'      => $unsubCampaignId,
                'unsub_type'             => $unsubType,
                'is_admin_unsub'         => $isAdminUnsub,
                'activity_count'         => count($activities),
                'is_dmc_user'            => (bool) $dmcUser,
                'dmc_user_id'            => optional($dmcUser)->id,
                'dmc_name'               => optional($dmcUser)->name,
                'dmc_uname'              => optional($dmcUser)->uname,
                'dmc_status'             => optional($dmcUser)->status_member, // 'active', 'pending', etc.
                'company'                => $companyName ?: '-',
                'job_title'              => $jobTitle ?: '-',
                'phone'                  => $phone ?: '-',
            ];
        });
    }

    /**
     * Fetch aktivitas anggota unsubscribed dari Mailchimp secara paralel via Http::pool.
     */
    private function fetchActivitiesForMembersFromMailchimp(array $config, array $rawMembers): array
    {
        $activitiesByEmail = [];
        $chunks = array_chunk($rawMembers, 25);

        foreach ($chunks as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk, $config) {
                foreach ($chunk as $m) {
                    $email = strtolower(trim((string) ($m['email_address'] ?? '')));
                    if ($email !== '') {
                        $hash = md5($email);
                        $pool->as($email)->timeout(12)->withBasicAuth('anystring', $config['apiKey'])
                            ->get("https://{$config['server']}.api.mailchimp.com/3.0/lists/{$config['listId']}/members/{$hash}/activity");
                    }
                }
            });

            foreach ($chunk as $m) {
                $email = strtolower(trim((string) ($m['email_address'] ?? '')));
                if ($email === '') continue;

                $r = $responses[$email] ?? null;
                if ($r && $r->successful()) {
                    $activitiesByEmail[$email] = $r->json('activity') ?? [];
                } else {
                    $activitiesByEmail[$email] = [];
                }
            }
        }

        return $activitiesByEmail;
    }

    /**
     * Konfigurasi kredensial Mailchimp dari .env / config.
     */
    private function mailchimpConfig(): ?array
    {
        $apiKey = trim((string) (config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY')));
        $listId = trim((string) (config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID')));
        $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);

        if (!$apiKey || !$listId || !$server) {
            return null;
        }

        return [
            'apiKey' => $apiKey,
            'server' => $server,
            'listId' => $listId,
        ];
    }
}
