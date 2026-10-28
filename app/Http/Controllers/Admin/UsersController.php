<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BulkTwoStepVerificationImportTemplate;
use App\Exports\UsersExport;
use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Imports\BulkTwoStepVerificationImport;
use App\Models\Company\CompanyModel;
use App\Models\MemberCompanyFollowUp;
use App\Models\MemberLeadFollowUp;
use App\Models\MemberSource;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Models\UserEditLog;
use App\Models\VerificationLog;
use App\Services\Membership\MemberVerificationService;
use App\Support\JobTierClassifier;
use App\Support\JobTierRole;
use App\Support\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }
    public function index(Request $request)
    {
        // The row list itself is no longer fetched here — it was a single ~2.8s query
        // pulling all ~3,000 matching rows just to render an initial page. The table
        // now starts empty and loads its first page (and every page after) via
        // usersData(), so only stat counters are needed up front.

        // Stats — hanya hitungan ringan yang dipakai KPI card & badge tab tabel.
        // Statistik berat (chart, SLA, leads, Job Tier, Members by Source) tidak
        // dihitung di sini: dimuat lewat stats() saat panel "Statistik & Chart" dibuka.
        $statusCounts = $this->memberStatusCounts();
        $countActiveMember  = $statusCounts['active'];
        $countPendingMember = $statusCounts['pending'];
        $countDeclined      = $statusCounts['declined'];
        $countDeactivated   = $statusCounts['deactivated'];

        $registeredAt = $this->registeredAtExpr();

        $countNewThisMonth = User::whereNotNull('isStatus')
            ->whereBetween(DB::raw($registeredAt), [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        $countUnRegistered = MemberModel::where('created_at', '>=', Carbon::now()->startOfYear())
            ->whereNull('register_as')
            ->count();

        $countVerifyEmail = User::whereNotNull('isStatus')
            ->whereNotNull('verify_email')->whereNull('verify_phone')->count();

        $countVerifyPhone = User::whereNotNull('isStatus')
            ->whereNotNull('verify_phone')->whereNull('verify_email')->count();

        $countDoubleVerify = User::whereNotNull('isStatus')
            ->whereNotNull('verify_phone')->whereNotNull('verify_email')->count();

        $countSelfEdited = DB::table('user_edit_logs')
            ->whereNull('admin_id')
            ->distinct('user_id')
            ->count('user_id');

        $countActiveWithoutPassword = User::whereNotNull('isStatus')
            ->where('status_member', 'active')
            ->whereNull('password')
            ->count();

        $countCompaniesVerified = CompanyModel::countVerifiedCompanies();

        $countProspecting = User::whereNotNull('users.isStatus')
            ->join('profiles', 'profiles.users_id', 'users.id')
            ->join('company', 'company.id', 'profiles.company_id')
            ->where('company.explore', 'agree')
            ->count();

        $countTwoStepVerified = User::whereNotNull('isStatus')
            ->where('two_step_verified', true)
            ->count();

        // Tab "Unregistered" pakai partial server-rendered terpisah
        // (_table_unregistered.blade.php, sumber data MemberModel, bukan
        // User) — bukan AJAX DataTables seperti tabel utama, jadi $list-nya
        // memang perlu dihitung di sini, hanya saat tab ini aktif.
        $list = $request->filter === 'unregist' ? $this->buildFilteredMemberList($request) : null;

        return view('admin.users.index', [
            'list'                => $list,
            'sources'            => $this->sourceColorMap(),
            'countActiveMember'  => $countActiveMember,
            'countPendingMember' => $countPendingMember,
            'countDeclined'      => $countDeclined,
            'countDeactivated'   => $countDeactivated,
            'countNewThisMonth'  => $countNewThisMonth,
            'countUnRegistered'  => $countUnRegistered,
            'countVerifyEmail'   => $countVerifyEmail,
            'countVerifyPhone'   => $countVerifyPhone,
            'countDoubleVerify'  => $countDoubleVerify,
            'countTwoStepVerified' => $countTwoStepVerified,
            'countSelfEdited'    => $countSelfEdited,
            'countActiveWithoutPassword' => $countActiveWithoutPassword,
            'countCompaniesVerified'     => $countCompaniesVerified,
            'countProspecting'           => $countProspecting,
            'companyNames'       => CompanyModel::whereNotNull('company_name')
                ->whereRaw("TRIM(company_name) <> ''")
                ->distinct()
                ->orderBy('company_name')
                ->pluck('company_name'),
        ]);
    }

    /**
     * Panel "Statistik & Chart" di halaman Members (AJAX, dipanggil saat panel
     * dibuka pertama kali): HTML panel (_stats_panel) + data chart untuk
     * initMemberStats(). Query string halaman ikut dikirim, jadi Members by
     * Source tetap mengikuti filter tabel yang sedang aktif.
     */
    public function stats(Request $request)
    {
        $statusCounts  = $this->memberStatusCounts();
        $validation    = $this->validation48hStats();
        $composition   = $this->memberCompositionStats();

        $html = view('admin.users.partials._stats_panel', array_merge(
            $this->verificationSlaStats(),
            $this->leadStats(),
            [
                'countActiveMember'       => $statusCounts['active'],
                'countPendingMember'      => $statusCounts['pending'],
                'countDeclined'           => $statusCounts['declined'],
                'countDeactivated'        => $statusCounts['deactivated'],
                'countValidatedWithin48h' => $validation['within48h'],
                'countValidatedAfter48h'  => $validation['after48h'],
                'sourceBreakdown'         => $this->computeSourceBreakdown($this->buildMemberQuery($request)),
                'memberComposition'       => $composition,
            ]
        ))->render();

        return response()->json([
            'html' => $html,
            'data' => [
                'status'            => $statusCounts,
                'validation'        => $validation,
                'registrations'     => $this->registrationTrends(),
                'memberComposition' => $composition,
            ],
        ]);
    }

    /** Jumlah member per status (KPI card, badge tab, chart Verification Status). */
    private function memberStatusCounts(): array
    {
        $base = function () {
            return User::whereNotNull('isStatus');
        };

        return [
            'active'      => $base()->where('status_member', 'active')->count(),
            'pending'     => $base()->where(function ($q) {
                $q->whereNull('status_member')->orWhere('status_member', 'pending');
            })->count(),
            'declined'    => $base()->where('status_member', 'declined')->count(),
            'deactivated' => $base()->where('status_member', 'deactivated')->count(),
        ];
    }

    /**
     * New Member Validation (48h): rolling 30 hari terakhir (created_at) supaya
     * mencerminkan performa terkini, bukan tumpukan histori all-time.
     */
    private function validation48hStats(): array
    {
        $registeredAt = $this->registeredAtExpr();
        $base = function () use ($registeredAt) {
            return User::whereNotNull('verified_at')
                ->where(DB::raw($registeredAt), '>=', Carbon::now()->subDays(30));
        };

        return [
            'within48h' => $base()->whereRaw("TIMESTAMPDIFF(HOUR, {$registeredAt}, verified_at) <= 48")->count(),
            'after48h'  => $base()->whereRaw("TIMESTAMPDIFF(HOUR, {$registeredAt}, verified_at) > 48")->count(),
        ];
    }

    /**
     * Member Registrations trend — weekly (8 minggu), monthly (≥ 6 bulan) &
     * yearly (maks. 8 tahun, Members Relation SOP §6 "Growth Tahunan") — untuk
     * reporting cepat ke management. Zero-filled supaya periode sepi tampil 0,
     * bukan bolong. Tiap periode: { labels, counts, ranges } (ranges dipakai
     * modal breakdown source saat bar diklik).
     */
    private function registrationTrends(): array
    {
        $registeredAt = $this->registeredAtExpr();
        $now = Carbon::now();

        $weeklyRaw = User::whereNotNull('isStatus')
            ->where(DB::raw($registeredAt), '>=', $now->copy()->subWeeks(7)->startOfWeek())
            ->selectRaw("YEARWEEK({$registeredAt}, 3) as period_key, COUNT(*) as total")
            ->groupBy('period_key')
            ->pluck('total', 'period_key');

        $weekly = ['labels' => [], 'counts' => [], 'ranges' => []];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekly['labels'][] = $weekStart->format('d M');
            $weekly['counts'][] = (int) ($weeklyRaw[(int) $weekStart->format('oW')] ?? 0);
            $weekly['ranges'][] = ['from' => $weekStart->toDateTimeString(), 'to' => $weekStart->copy()->endOfWeek()->toDateTimeString()];
        }

        // startOfMonth() SEBELUM subMonths() — kalau sesudahnya bisa jatuh ke
        // tanggal yang tidak ada di bulan tujuan (mis. 29 Jul - 5 bulan = 29 Feb di
        // tahun non-kabisat) dan Carbon overflow ke Maret → bulan dobel.
        $monthsToShow = max(5, $now->month - 1);
        $monthlyRaw = User::whereNotNull('isStatus')
            ->where(DB::raw($registeredAt), '>=', $now->copy()->startOfMonth()->subMonths($monthsToShow))
            ->selectRaw("DATE_FORMAT({$registeredAt}, '%Y-%m') as period_key, COUNT(*) as total")
            ->groupBy('period_key')
            ->pluck('total', 'period_key');

        $monthly = ['labels' => [], 'counts' => [], 'ranges' => []];
        for ($i = $monthsToShow; $i >= 0; $i--) {
            $monthStart = $now->copy()->startOfMonth()->subMonths($i);
            $monthly['labels'][] = $monthStart->format('M Y');
            $monthly['counts'][] = (int) ($monthlyRaw[$monthStart->format('Y-m')] ?? 0);
            $monthly['ranges'][] = ['from' => $monthStart->toDateTimeString(), 'to' => $monthStart->copy()->endOfMonth()->toDateTimeString()];
        }

        $earliestRegistered = User::whereNotNull('isStatus')->min(DB::raw($registeredAt));
        $earliestYear = $earliestRegistered ? Carbon::parse($earliestRegistered)->year : $now->year;
        $earliestYear = max($earliestYear, $now->year - 7);

        $yearlyRaw = User::whereNotNull('isStatus')
            ->where(DB::raw($registeredAt), '>=', Carbon::createFromDate($earliestYear, 1, 1)->startOfDay())
            ->selectRaw("YEAR({$registeredAt}) as period_key, COUNT(*) as total")
            ->groupBy('period_key')
            ->pluck('total', 'period_key');

        $yearly = ['labels' => [], 'counts' => [], 'ranges' => []];
        for ($year = $earliestYear; $year <= $now->year; $year++) {
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $yearly['labels'][] = (string) $year;
            $yearly['counts'][] = (int) ($yearlyRaw[$year] ?? 0);
            $yearly['ranges'][] = ['from' => $yearStart->toDateTimeString(), 'to' => $yearStart->copy()->endOfYear()->toDateTimeString()];
        }

        return ['weekly' => $weekly, 'monthly' => $monthly, 'yearly' => $yearly];
    }

    /**
     * Verification SLA (Members Relation SOP §2-4): item yang sedang di-review per
     * tier umur, rata-rata waktu proses & performa per PIC (30 hari). Di-guard
     * Schema::hasTable supaya panel tetap jalan kalau migration-nya belum ada.
     */
    private function verificationSlaStats(): array
    {
        $stats = [
            'countSlaGreen'        => 0,
            'countSlaYellow'       => 0,
            'countSlaRed'          => 0,
            'avgVerificationHours' => null,
            'picPerformance'       => collect(),
        ];

        if (!Schema::hasTable('verification_logs')) {
            return $stats;
        }

        try {
            $stats['countSlaGreen']  = VerificationLog::open()->whereRaw('TIMESTAMPDIFF(HOUR, started_at, NOW()) < 24')->count();
            $stats['countSlaYellow'] = VerificationLog::open()->whereRaw('TIMESTAMPDIFF(HOUR, started_at, NOW()) BETWEEN 24 AND 48')->count();
            $stats['countSlaRed']    = VerificationLog::open()->whereRaw('TIMESTAMPDIFF(HOUR, started_at, NOW()) > 48')->count();

            $avgMinutes = VerificationLog::whereNotNull('finished_at')
                ->where('started_at', '>=', Carbon::now()->subDays(30))
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, finished_at)) as avg_minutes')
                ->value('avg_minutes');
            $stats['avgVerificationHours'] = $avgMinutes ? round($avgMinutes / 60, 1) : null;

            $stats['picPerformance'] = VerificationLog::whereNotNull('finished_at')
                ->where('started_at', '>=', Carbon::now()->subDays(30))
                ->whereNotNull('finished_by_name')
                ->selectRaw('finished_by_name, COUNT(*) as total, AVG(TIMESTAMPDIFF(MINUTE, started_at, finished_at)) as avg_minutes')
                ->groupBy('finished_by_name')
                ->orderByDesc('total')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('stats: verification SLA stats failed: ' . $e->getMessage());
        }

        return $stats;
    }

    /** Leads (Members Relation SOP §7): total, pending follow-up & lewat SLA. */
    private function leadStats(): array
    {
        $stats = ['countLeads' => 0, 'countLeadsPendingFollowUp' => 0, 'countLeadsOverSla' => 0];

        if (!Schema::hasTable('member_lead_follow_ups')) {
            return $stats;
        }

        try {
            $pending = function () {
                return MemberLeadFollowUp::approvedMember()->where('result', MemberLeadFollowUp::RESULT_PENDING);
            };
            $stats['countLeads'] = MemberLeadFollowUp::approvedMember()->count();
            $stats['countLeadsPendingFollowUp'] = $pending()->count();
            $stats['countLeadsOverSla'] = MemberLeadFollowUp::approvedMember()->overSla()->count();
        } catch (\Throwable $e) {
            Log::warning('stats: lead stats failed: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Data chart "Job Tier" & "Company Category × Job Tier" — komposisi member
     * active (Internal Operating Guideline: position tier dan company category
     * dilaporkan terpisah). Tier pakai aturan yang sama dengan badge tabel
     * (ProfileModel::jobTierFor), jadi angkanya selalu cocok dengan filter Job Tier.
     */
    private function memberCompositionStats(): array
    {
        $tierNos = array_keys(JobTierClassifier::TIERS);
        $hasTierColumn = ProfileModel::hasJobTierColumn();

        // Hanya member Active yang company-nya sudah verified (per nama company,
        // sama seperti Company Database).
        $verifiedNames = CompanyModel::verifiedCompanyNameSet();
        $rows = User::leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus')
            ->where('users.status_member', 'active')
            ->get(array_merge(
                ['users.status_member', 'profiles.job_title', 'company.company_name', 'company.company_category'],
                $hasTierColumn ? ['profiles.job_tier'] : []
            ))
            ->filter(function ($row) use ($verifiedNames) {
                return CompanyModel::isVerifiedName($row->company_name, $verifiedNames);
            });

        $tierCounts = array_fill_keys($tierNos, 0);
        $tierRoles = [];
        foreach ($tierNos as $tierNo) {
            $tierRoles[$tierNo] = JobTierRole::emptyCounts($tierNo);
        }
        $byCategory = [];
        foreach ($rows as $row) {
            $tier = ProfileModel::jobTierFor($row->job_title, $row->status_member, $row->job_tier ?? null);
            $category = CompanyModel::categoryLabel($row->company_category);

            if (!isset($byCategory[$category])) {
                $byCategory[$category] = array_fill_keys($tierNos, 0);
            }
            $byCategory[$category][$tier]++;
            $tierCounts[$tier]++;
            $tierRoles[$tier][JobTierRole::roleFor($tier, $row->job_title)]++;
        }

        // Urut terbanyak dulu; "Others" & "Not set" selalu paling bawah.
        $tail = ['Others', 'Not set'];
        uksort($byCategory, function ($a, $b) use ($byCategory, $tail) {
            $aTail = array_search($a, $tail, true);
            $bTail = array_search($b, $tail, true);
            if ($aTail !== false || $bTail !== false) {
                return ($aTail === false ? -1 : $aTail) <=> ($bTail === false ? -1 : $bTail);
            }
            return array_sum($byCategory[$b]) <=> array_sum($byCategory[$a]);
        });

        $tiers = [];
        foreach (JobTierClassifier::TIERS as $tierNo => $meta) {
            $tiers[] = [
                'tier'      => $tierNo,
                'label'     => 'T' . $tierNo,
                'roles'     => $meta['roles'],
                'action'    => $meta['action'],
                'color'     => $meta['color'],
                'count'     => $tierCounts[$tierNo],
                'byCategory' => array_values(array_column($byCategory, $tierNo)),
                // Daftar detail di card Job Tier: jumlah per role di definisi tier.
                'roleCounts' => $tierRoles[$tierNo],
            ];
        }

        return [
            'total'      => $rows->count(),
            'tiers'      => $tiers,
            'categories' => array_keys($byCategory),
        ];
    }

    /**
     * Query dasar member ter-filter (join + tab/date filters), dipakai bersama oleh
     * buildFilteredMemberList() (export, initial-load-free path) dan usersData()
     * (AJAX pagination) supaya keduanya selalu melihat baris yang sama persis.
     * Tidak menangani filter=unregist — itu sumber datanya beda (MemberModel), lihat
     * buildFilteredMemberList().
     */
    private function buildMemberQuery(Request $request)
    {
        $filter       = $request->filter;
        $dateFrom     = $request->date_from;
        $dateTo       = $request->date_to;
        $month        = $request->month;
        $year         = $request->year;
        $sources      = array_values(array_filter((array) $request->source));
        $statusMember = $request->status_member; // 'active' | 'pending' | ''

        $query = User::leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus');
        if ($filter == 'this_month') {
            $query->whereBetween(DB::raw($this->registeredAtExpr()), [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]);
        }

        if ($filter == 'self_edited') {
            $selfEditedIds = DB::table('user_edit_logs')
                ->whereNull('admin_id')
                ->pluck('user_id')
                ->unique()
                ->values()
                ->all();
            $query->whereIn('users.id', $selfEditedIds);
        }

        if ($filter == 'password_null') {
            $query->whereNull('users.password');
        }

        if ($filter == 'company_verified') {
            $query->where('company.is_verified', true);
        }

        if ($filter == 'two_step_verified') {
            $query->where('users.two_step_verified', true);
        }

        if ($filter == 'prospecting') {
            $query->where('company.explore', 'agree');
        }

        if ($statusMember === 'active') {
            $query->where('users.status_member', 'active');
        } elseif ($statusMember === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('users.status_member')
                    ->orWhere('users.status_member', 'pending');
            });
        } elseif ($statusMember === 'declined') {
            $query->where('users.status_member', 'declined');
        } elseif ($statusMember === 'deactivated') {
            $query->where('users.status_member', 'deactivated');
        } else {
            $query->where(function ($q) {
                $q->whereNull('users.status_member')
                    ->orWhereNotIn('users.status_member', ['declined', 'deactivated']);
            });
        }

        $jobTier = (int) $request->job_tier;
        if (isset(JobTierClassifier::TIERS[$jobTier]) && ProfileModel::hasJobTierColumn()) {
            $query->where('profiles.job_tier', $jobTier);
        }

        $registeredAtCol = DB::raw($this->registeredAtExpr());
        if ($dateFrom) $query->whereDate($registeredAtCol, '>=', $dateFrom);
        if ($dateTo)   $query->whereDate($registeredAtCol, '<=', $dateTo);
        if ($month)    $query->whereMonth($registeredAtCol, $month);
        if ($year)     $query->whereYear($registeredAtCol, $year);

        if (!empty($sources)) {
            // Bisa pilih lebih dari 1 source sekaligus — OR-kan klausa per bucket
            // yang dipilih (tiap bucket sendiri bisa berupa OR pattern-match,
            // mis. dmc_event menggabungkan beberapa variasi source lama).
            $query->where(function ($outer) use ($sources) {
                foreach ($sources as $source) {
                    $outer->orWhere(function ($q) use ($source) {
                        if ($source === 'dmc_event') {
                            // 'DMC Event' bucket = anything event-ish: the unified 'DMC Event'
                            // value itself, legacy 'event*'/'e/*'-prefixed leftovers, and
                            // 'scanner' (check-in registrations) — see normalizeSourceKey().
                            $q->where(DB::raw('LOWER(TRIM(users.source))'), 'dmc event')
                              ->orWhere(DB::raw('LOWER(TRIM(users.source))'), 'scanner')
                              ->orWhere(function ($legacy) {
                                  $legacy->where('users.source', 'like', 'event%')
                                         ->where(DB::raw('LOWER(TRIM(users.source))'), '!=', 'event partnership');
                              })
                              ->orWhere('users.source', 'like', 'e/%');
                        } elseif ($source === 'partner') {
                            $q->where('users.source', 'like', 'ep/%')
                              ->orWhere(DB::raw('LOWER(TRIM(users.source))'), 'event partnership');
                        } else {
                            $q->where(DB::raw('LOWER(TRIM(users.source))'), strtolower($source));
                        }
                    });
                }
            });
        }

        return $query;
    }

    /**
     * Bangun daftar member sesuai filter aktif (dipakai oleh export — perlu semua
     * baris sekaligus, bukan per-halaman). Tampilan tabel di index() memakai
     * usersData() (AJAX, per-halaman) supaya tidak query 3000 baris di setiap load.
     */
    private function buildFilteredMemberList(Request $request)
    {
        $filter   = $request->filter;
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;
        $month    = $request->month;
        $year     = $request->year;

        if ($filter == 'unregist') {
            $query = MemberModel::whereNull('register_as');

            if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);
            if ($month)    $query->whereMonth('created_at', $month);
            if ($year)     $query->whereYear('created_at', $year);

            if (!$dateFrom && !$dateTo && !$month && !$year) {
                $query->where('created_at', '>=', Carbon::now()->startOfYear());
            }

            return $query->orderBy('id', 'desc')->get();
        }

        $list = $this->buildMemberQuery($request)
            ->orderBy('users.id', 'desc')
            ->select(
                'users.*',
                DB::raw($this->registeredAtExpr() . ' as user_created_at'),
                'profiles.*',
                'company.*',
                'users.id as user_id'
            )
            ->get();

        return $this->attachVerifiedCompanyNameFlag($list);
    }

    /**
     * Mapping manual: jika ada company lain dengan nama sama yang sudah verified,
     * tandai row ini agar tombol verify bisa langsung biru.
     */
    private function attachVerifiedCompanyNameFlag($list)
    {
        $verifiedCompanyNameMap = CompanyModel::query()
            ->where('is_verified', true)
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->selectRaw('LOWER(TRIM(company_name)) as normalized_name')
            ->distinct()
            ->pluck('normalized_name')
            ->flip();

        return $list->map(function ($row) use ($verifiedCompanyNameMap) {
            $normalizedName = Str::lower(trim((string) ($row->company_name ?? '')));
            $row->has_verified_company_name = $normalizedName !== '' && $verifiedCompanyNameMap->has($normalizedName);
            return $row;
        });
    }

    /**
     * AJAX endpoint behind clicking a bar on the "Member Registrations" chart —
     * source breakdown (LinkedIn: 2, Website: 5, Event: 2, ...) for just that
     * one period, instead of the all-time totals "Members by Source" already
     * shows below it. Reuses the same source normalization as everywhere else
     * so labels/colors always agree with the rest of the page.
     */
    public function registrationSourceBreakdown(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $baseQuery = User::leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus');
        if ($dateFrom) $baseQuery->where(DB::raw($this->registeredAtExpr()), '>=', $dateFrom);
        if ($dateTo)   $baseQuery->where(DB::raw($this->registeredAtExpr()), '<=', $dateTo);

        $raw = (clone $baseQuery)->selectRaw('users.source as source, COUNT(*) as total')
            ->groupBy('users.source')
            ->pluck('total', 'source');

        $breakdown = [];
        foreach ($this->sourceColorMap() as $key => $meta) {
            $breakdown[$key] = ['label' => $meta['label'], 'color' => $meta['color'], 'icon' => $meta['icon'], 'total' => 0];
        }
        foreach ($raw as $rawSource => $total) {
            $key = $this->normalizeSourceKey($rawSource);
            if (isset($breakdown[$key])) {
                $breakdown[$key]['total'] += (int) $total;
            }
        }

        $result = collect($breakdown)->filter(function ($row) {
            return $row['total'] > 0;
        })->sortByDesc('total')->values();

        // Status breakdown (Verified/Waiting/Declined/Deactivated) + Leads
        // split by whether they're still a live prospect — one pass over every
        // matching row (not just the capped list below) so both stay accurate
        // even for a wide range like a full year. A declined applicant can't
        // realistically be pursued as a sponsorship lead anymore, so "Potential
        // Leads" alone overstates it — split into still-viable vs already-dead.
        $countVerified = $countWaiting = $countDeclinedStatus = $countDeactivatedStatus = 0;
        $leadsViable = $leadsDead = 0;

        (clone $baseQuery)->get(['users.status_member', 'company.explore'])
            ->each(function ($row) use (&$countVerified, &$countWaiting, &$countDeclinedStatus, &$countDeactivatedStatus, &$leadsViable, &$leadsDead) {
                $status = strtolower((string) ($row->status_member ?? ''));
                if ($status === 'active')           $countVerified++;
                elseif ($status === 'declined')     $countDeclinedStatus++;
                elseif ($status === 'deactivated')  $countDeactivatedStatus++;
                else                                 $countWaiting++;

                if ($this->isLeadExplore($row->explore ?? null)) {
                    if ($status === 'declined') $leadsDead++;
                    else                        $leadsViable++;
                }
            });

        $leadsCount = $leadsViable + $leadsDead;

        // Who exactly registered this period — capped so a wide (e.g. yearly)
        // range doesn't return an unusable wall of rows; newest first.
        $memberRowLimit = 300;
        $totalMembers = (clone $baseQuery)->count();
        $members = (clone $baseQuery)
            ->orderByRaw($this->registeredAtExpr() . ' desc')
            ->limit($memberRowLimit)
            ->get([
                'users.name', 'users.email', 'users.source', DB::raw($this->registeredAtExpr() . ' as created_at'),
                'users.status_member', 'company.company_name', 'company.explore',
            ])
            ->map(function ($row) {
                return [
                    'name'          => $row->name,
                    'email'         => $row->email,
                    'company_name'  => $row->company_name,
                    'source'        => $row->source ?: 'Unknown',
                    'status_member' => $row->status_member ?: 'pending',
                    'is_lead'       => $this->isLeadExplore($row->explore ?? null),
                    'created_at'    => optional($row->created_at)->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'success'            => true,
            'total'              => (int) $result->sum('total'),
            'breakdown'          => $result,
            'leads_count'        => $leadsCount,
            'leads_viable'       => $leadsViable,
            'leads_dead'         => $leadsDead,
            'status'             => [
                'verified'    => $countVerified,
                'waiting'     => $countWaiting,
                'declined'    => $countDeclinedStatus,
                'deactivated' => $countDeactivatedStatus,
            ],
            'members'            => $members,
            'members_total'      => $totalMembers,
            'members_truncated'  => $totalMembers > $memberRowLimit,
        ]);
    }

    /**
     * AJAX endpoint (DataTables server-side processing) yang menyuplai tabel Members
     * di admin/users — hanya query & render baris yang benar-benar sedang dilihat
     * (default 25/halaman), bukan seluruh ~3000 baris seperti sebelumnya.
     */
    public function usersData(Request $request)
    {
        $baseQuery = $this->buildMemberQuery($request);

        $recordsTotal = (clone $baseQuery)->count();

        $searchValue = trim((string) $request->input('search.value', ''));
        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('users.name', 'like', "%{$searchValue}%")
                    ->orWhere('users.email', 'like', "%{$searchValue}%")
                    ->orWhere('company.company_name', 'like', "%{$searchValue}%")
                    ->orWhere('profiles.fullphone', 'like', "%{$searchValue}%")
                    ->orWhere('profiles.job_title', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $columnsSortMap = [
            1 => DB::raw($this->registeredAtExpr()),
            2 => 'users.source',
            3 => 'users.name',
            4 => 'users.status_member',
            7 => 'company.company_name',
            8 => 'users.email',
            9 => 'profiles.fullphone',
        ];
        $orderColumnIndex = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columnsSortMap[$orderColumnIndex] ?? 'users.id';

        $start  = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 25);
        $length = $length > 0 ? min($length, 100) : 25;

        $rows = $baseQuery->orderBy($orderColumn, $orderDir)
            ->select(
                'users.*',
                DB::raw($this->registeredAtExpr() . ' as user_created_at'),
                'profiles.*',
                'company.*',
                'users.id as user_id'
            )
            ->skip($start)
            ->take($length)
            ->get();

        $rows = $this->attachVerifiedCompanyNameFlag($rows);

        $userIds = $rows->pluck('user_id')->all();

        $selfEditMap = DB::table('user_edit_logs')
            ->whereNull('admin_id')
            ->whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('MAX(created_at) as last_self_edit'))
            ->groupBy('user_id')
            ->pluck('last_self_edit', 'user_id')
            ->all();

        // Guarded the same way as index() — keeps the table working if this
        // migration hasn't landed yet, rows just show "New" instead of the
        // New/Verification split until it does.
        $openVerificationIds = collect();
        if (Schema::hasTable('verification_logs')) {
            $openVerificationIds = VerificationLog::open()
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->unique()
                ->flip();
        }

        // Open company follow-up flags for the rows on this page (single query).
        $followUpMap = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // Status Mailchimp: satu peta untuk seluruh audience (di-cache), bukan
        // request per baris. null = Mailchimp tidak terjangkau -> kolom tampil "—".
        $mailchimpStatuses = app(MemberVerificationService::class)->mailchimpStatusMap();

        $data = [];
        foreach ($rows as $i => $post) {
            $cells = $this->renderMemberRowCells($post, $selfEditMap, $openVerificationIds, $followUpMap, $mailchimpStatuses);
            $cells[0] = $start + $i + 1;
            $data[] = $cells;
        }

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Render satu baris tabel Members jadi array cell (index 0..16, cocok dengan
     * urutan <th> di _table_members.blade.php) + metadata baris (DT_RowId/DT_RowAttr)
     * untuk response DataTables server-side.
     */
    private function renderMemberRowCells($post, array $selfEditMap, $openVerificationIds = null, $followUpMap = null, ?array $mailchimpStatuses = null): array
    {
        $sourceColorMap = $this->sourceColorMap();

        $memberStatus  = strtolower($post->status_member ?? '');
        $isActive      = $memberStatus === 'active';
        $isDeclined    = $memberStatus === 'declined';
        $isDeactivated = $memberStatus === 'deactivated';
        $rowBg = $isActive ? '' : ($isDeclined ? 'background-color:#fff5f5;' : ($isDeactivated ? 'background-color:#f0f0f0;' : 'background-color:#fffbee;'));

        $sourceRaw = trim((string) ($post->source ?? ''));
        $sourceKey = $this->normalizeSourceKey($sourceRaw);
        $sourceStyle = $sourceColorMap[$sourceKey] ?? ['color' => '#adb5bd', 'icon' => 'fas fa-question'];
        $sourceLabel = $sourceRaw !== '' ? $sourceRaw : 'Unknown';

        // 'DMC Event' merges 'scanner' + legacy 'event*' registrations into one
        // filterable bucket — show the finer detail (which one it actually was)
        // from `hear` instead of the now-generic `source` value.
        if ($sourceKey === 'dmc_event') {
            $hearRaw = trim((string) ($post->hear ?? ''));
            if ($hearRaw !== '') {
                $sourceLabel = $hearRaw;
            }
        }

        $registerRaw = $post->user_created_at ?? $post->created_at;
        $cellRegister = '<span class="text-nowrap">' . e(date('d M Y', strtotime($registerRaw)))
            . '<br><small class="text-muted">' . e(date('H:i', strtotime($registerRaw))) . '</small></span>';

        $cellSource = '<span class="text-nowrap"><span class="badge" style="background-color:'
            . e($sourceStyle['color']) . ';color:#fff;">' . e($sourceLabel) . '</span></span>';

        $cellName = e($post->name);
        if (isset($selfEditMap[$post->user_id])) {
            $cellName .= ' <i class="fas fa-user-edit text-warning ml-1" title="User mengubah data sendiri — '
                . e(\Carbon\Carbon::parse($selfEditMap[$post->user_id])->format('d M Y H:i'))
                . '" data-toggle="tooltip"></i>';
        }

        $isInVerification = $openVerificationIds ? isset($openVerificationIds[$post->user_id]) : false;
        $isLead = $this->isLeadExplore($post->explore ?? null);

        $cellStatus = view('admin.users.partials._row.status_badge', [
            'post' => $post, 'isActive' => $isActive, 'isDeclined' => $isDeclined, 'isDeactivated' => $isDeactivated,
            'isInVerification' => $isInVerification, 'isLead' => $isLead,
        ])->render();

        $cellActions = view('admin.users.partials._row.status_actions', [
            'post' => $post, 'isActive' => $isActive, 'isDeclined' => $isDeclined, 'isDeactivated' => $isDeactivated,
        ])->render();

        $cellJobTitle = '<span class="text-nowrap">' . $this->jobTierBadge($post) . ' '
            . '<span class="cell-truncate" title="' . e($post->job_title) . '">' . e($post->job_title) . '</span></span>';
        $cellCompany  = '<span class="cell-truncate" title="' . e($post->company_name) . '">' . e($post->company_name) . '</span>';
        $cellEmail    = '<a href="mailto:' . e($post->email) . '" class="cell-truncate" title="' . e($post->email) . '">' . e($post->email) . '</a>';
        $cellPhone    = '<span class="text-nowrap">' . e($post->fullphone ?? $post->phone) . '</span>';
        $cellOffice   = '<span class="text-nowrap">' . e($post->full_office_number) . '</span>';
        $cellAddress  = '<span class="cell-truncate" title="' . e($post->address) . '">' . e($post->address) . '</span>';

        $cellWebsite = '';
        if ($post->company_website) {
            $cellWebsite = '<a href="' . e($post->company_website) . '" target="_blank" rel="noopener" class="cell-truncate" title="'
                . e($post->company_website) . '">' . e($post->company_website) . '</a>';
        }

        $categoryLabel = $post->company_category == 'other' ? $post->company_other : $post->company_category;
        $cellCategory = '<span class="cell-truncate" title="' . e($categoryLabel) . '">' . e($categoryLabel) . '</span>';

        $waAgree = strtolower(trim((string) $post->wa_updates)) === 'agree';
        $cellWaSpon = '<div class="btn-icon-group">'
            . '<i class="fab fa-whatsapp ' . ($waAgree ? 'text-success' : 'text-muted') . '" title="WA Updates: ' . ($waAgree ? 'Yes' : 'No') . '" data-toggle="tooltip"></i>'
            . '<i class="fas fa-star ' . ($post->explore ? 'text-warning' : 'text-muted') . '" title="Open to Sponsorship: ' . ($post->explore ? 'Yes' : 'No') . '" data-toggle="tooltip"></i>'
            . '<button type="button" class="btn btn-icon btn-outline-secondary btn-import-mailchimp"'
            . ' data-url="' . e(route('users.import.mailchimp')) . '" data-user-id="' . e($post->user_id) . '" data-email="' . e($post->email) . '"'
            . ' data-tags=\'["Register of Membership ' . e(now()->format('d M Y')) . '"]\''
            . ' title="Re-sync data member ini ke Mailchimp" data-toggle="tooltip"><i class="fas fa-sync-alt"></i></button>'
            . '</div>';

        $cellMailchimp = $this->mailchimpStatusBadge($mailchimpStatuses, $post->email);

        $cellPasswordActions = view('admin.users.partials._row.password_actions', [
            'post' => $post,
            'openFollowUp' => $followUpMap ? ($followUpMap[$post->user_id] ?? null) : null,
        ])->render();

        return [
            1  => $cellRegister,
            2  => $cellSource,
            3  => $cellName,
            4  => $cellStatus,
            5  => $cellActions,
            6  => $cellJobTitle,
            7  => $cellCompany,
            8  => $cellEmail,
            9  => $cellPhone,
            10 => $cellOffice,
            11 => $cellAddress,
            12 => $cellWebsite,
            13 => $cellCategory,
            14 => $cellWaSpon,
            15 => $cellMailchimp,
            16 => $cellPasswordActions,
            'DT_RowId'   => 'row_' . $post->user_id,
            'DT_RowAttr' => ['style' => $rowBg],
        ];
    }

    /**
     * Select kecil "T1".."T4" di depan job title (hanya member active), bisa
     * diubah langsung dari tabel → updateJobTier().
     */
    private function jobTierBadge($post): string
    {
        return view('admin.users.partials._row.job_tier_select', [
            'post'   => $post,
            'tier'   => ProfileModel::jobTierFor($post->job_title, $post->status_member, $post->job_tier ?? null),
            'manual' => !empty($post->job_tier_manual),
        ])->render();
    }

    /**
     * Ubah job tier langsung dari tabel Members: 'auto' = ikut job_title,
     * 1-4 = override manual. Hanya untuk member active.
     */
    public function updateJobTier(Request $request, $id)
    {
        $request->validate(['job_tier' => 'required|in:auto,1,2,3,4']);

        $user = User::findOrFail($id);
        if ($user->status_member !== 'active') {
            return response()->json(['success' => false, 'message' => 'Job tier hanya untuk member Active.'], 422);
        }

        $profile = ProfileModel::hasJobTierColumn() ? ProfileModel::where('users_id', $user->id)->first() : null;
        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'Profile member tidak ditemukan.'], 422);
        }

        $manual = $request->input('job_tier') !== 'auto';
        $tier   = $manual ? (int) $request->input('job_tier') : JobTierClassifier::classify($profile->job_title);

        $old = $this->jobTierLogValue($profile->job_tier, (bool) $profile->job_tier_manual);
        $new = $this->jobTierLogValue($tier, $manual);

        $profile->job_tier_manual = $manual;
        $profile->job_tier        = $tier;
        $profile->save();

        if ($old !== $new) {
            $admin = auth()->user();
            DB::table('user_edit_logs')->insert([
                'user_id'    => $user->id,
                'admin_id'   => auth()->id(),
                'admin_name' => $admin ? $admin->name : null,
                'changes'    => json_encode(['job_tier' => ['old' => $old, 'new' => $new]]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'tier'    => $tier,
            'manual'  => $manual,
            'color'   => JobTierClassifier::color($tier),
            'text'    => JobTierClassifier::textColor($tier),
            'title'   => JobTierClassifier::tooltip($tier, $manual),
        ]);
    }

    /**
     * Ekspresi SQL "tanggal daftar member": member_registered_at (diisi saat
     * orang yang sebelumnya cuma terdaftar di event mendaftar jadi member),
     * fallback ke users.created_at. Fallback penuh ke created_at kalau
     * migration kolomnya belum dijalankan.
     */
    private function registeredAtExpr(): string
    {
        return User::hasMemberRegisteredAtColumn()
            ? 'COALESCE(users.member_registered_at, users.created_at)'
            : 'users.created_at';
    }

    /**
     * Badge status Mailchimp per member. $statuses = peta email => status dari
     * MemberVerificationService::mailchimpStatusMap(), atau null kalau lookup gagal.
     */
    private function mailchimpStatusBadge(?array $statuses, $email): string
    {
        if ($statuses === null) {
            return '<span class="text-muted" title="Status Mailchimp tidak bisa diambil" data-toggle="tooltip">—</span>';
        }

        $status = $statuses[strtolower(trim((string) $email))] ?? null;

        $map = [
            'subscribed'    => ['badge-success', 'Subscribed', 'Terdaftar & aktif menerima email'],
            'unsubscribed'  => ['badge-warning', 'Unsubscribed', 'Pernah berhenti berlangganan (opt-out)'],
            'cleaned'       => ['badge-danger', 'Cleaned', 'Email bounce / tidak valid di Mailchimp'],
            'pending'       => ['badge-info', 'Pending', 'Menunggu konfirmasi double opt-in'],
            'transactional' => ['badge-secondary', 'Transactional', 'Hanya email transaksional'],
        ];

        if ($status === null) {
            return '<span class="badge badge-light border text-muted" title="Belum ada di audience Mailchimp (belum pernah masuk atau sudah di-archive)" data-toggle="tooltip">Not in list</span>';
        }

        $meta = $map[$status] ?? ['badge-secondary', ucfirst($status), $status];

        return '<span class="badge ' . $meta[0] . '" title="' . e($meta[2]) . '" data-toggle="tooltip">' . e($meta[1]) . '</span>';
    }

    /**
     * Export tabel Users ke Excel dengan value asli (bukan HTML tombol seperti
     * export bawaan DataTables). Mengikuti filter yang sedang aktif di halaman.
     */
    public function exportExcel(Request $request)
    {
        $list       = $this->buildFilteredMemberList($request);
        $isUnregist = $request->filter === 'unregist';

        $filename = 'users-' . ($isUnregist ? 'unregistered' : 'members')
            . '-' . date('Ymd-His') . '.xlsx';

        return Excel::download(new UsersExport($list, $isUnregist), $filename);
    }

    public function store(Request $request)
    {
        try {
            $user = User::firstOrNew(['email' => $request->email]);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
            $company->prefix = $request->prefix;
            $company->company_name = $request->company_name;
            $company->company_website = $request->company_website;
            $company->company_category = $request->company_category;
            $company->company_other = $request->company_other;
            $company->address = $request->address;
            $company->city = $request->city;
            $company->portal_code = $request->portal_code;
            $company->office_number = $request->office_number;
            $company->country = $request->country;
            $company->users_id = $user->id;
            $company->save();

            $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
            $profile->phone = $request->phone;
            $profile->job_title = $request->job_title;
            $profile->users_id = $user->id;
            $profile->company_id = $company->id;
            $profile->save();

            return redirect()->route('users')->with('success', 'Successfully added user');
        } catch (\Exception $e) {
            // Handle the exception
            return back()->withErrors('Failed to add user. Error: ' . $e->getMessage());
        }
    }

    // App\Http\Controllers\Admin\UsersController.php (tambahin method ini)
    public function updateTier(Request $request, $id)
    {
        $request->validate([
            'tier' => 'required|in:reguler,black',
        ]);

        $user = User::findOrFail($id);
        $user->tier = $request->tier;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tier updated.',
            'tier'    => $user->tier,
        ]);
    }

    /**
     * Fired the moment an admin opens the verify modal for a member — stamps
     * when review actually started (not just when it finished), so the SLA
     * dashboard can show items currently in progress, not only completed ones.
     * Idempotent: re-opening the modal on the same member doesn't create a
     * second open log row.
     */
    public function startVerification(Request $request, $id)
    {
        // Fire-and-forget from the client — never let this block or fail the
        // actual verify/decline flow, and degrade quietly if the migration
        // hasn't landed on this DB yet (never auto-run against it).
        if (!Schema::hasTable('verification_logs')) {
            return response()->json(['success' => true, 'log_id' => null]);
        }

        try {
            $user = User::findOrFail($id);
            $admin = auth()->user();

            $log = VerificationLog::open()->where('user_id', $user->id)->latest('started_at')->first();

            if (!$log) {
                $log = VerificationLog::create([
                    'user_id'         => $user->id,
                    'started_at'      => now(),
                    'started_by_id'   => auth()->id(),
                    'started_by_name' => $admin ? $admin->name : 'Staff',
                ]);
            }

            return response()->json(['success' => true, 'log_id' => $log->id]);
        } catch (\Throwable $e) {
            Log::warning('startVerification failed for user ' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => true, 'log_id' => null]);
        }
    }

    /**
     * Closes out the open verification_logs row for a member (finishing
     * verifyMember()/declineMember()). Creates one on the fly if none is open
     * (e.g. startVerification() ping never landed) so nothing goes unlogged.
     * Never throws — a logging failure must not block verifyMember()/
     * declineMember() from completing the actual verify/decline.
     */
    private function finishVerificationLog(User $user, string $result): void
    {
        if (!Schema::hasTable('verification_logs')) {
            return;
        }

        try {
            $admin = auth()->user();
            $finishedAt = now();

            $log = VerificationLog::open()->where('user_id', $user->id)->latest('started_at')->first();

            if (!$log) {
                $log = new VerificationLog([
                    'user_id'         => $user->id,
                    'started_at'      => $finishedAt,
                    'started_by_id'   => auth()->id(),
                    'started_by_name' => $admin ? $admin->name : 'Staff',
                ]);
            }

            $log->finished_at      = $finishedAt;
            $log->finished_by_id   = auth()->id();
            $log->finished_by_name = $admin ? $admin->name : 'Staff';
            $log->result           = $result;
            $log->save();
        } catch (\Throwable $e) {
            Log::warning('finishVerificationLog failed for user ' . $user->id . ': ' . $e->getMessage());
        }
    }

    /**
     * "Explore Marketing" on the company record is a messy free-text column
     * in practice (NULL/''/'0'/'agree'/'aggree' [typo]/'true') — treat any of
     * these as truthy rather than the single exact-match the old "Prospecting"
     * filter used, which silently missed the typo variant.
     */
    private function isLeadExplore($value): bool
    {
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['agree', 'aggree', 'true', '1'], true);
    }

    /**
     * "Members by Source" breakdown (Members Relation SOP §8) — takes an
     * already-filtered member query (buildMemberQuery($request), so it
     * respects whatever date/month/year/source/status filter is currently
     * active) and returns members/leads/win/loss/conversion_rate per bucket.
     * Total Members always computable; Total Leads/Win/Loss only once the
     * leads table exists (guarded, same as the rest of that feature).
     */
    private function computeSourceBreakdown($baseQuery)
    {
        // Plain array, not a Collection, while accumulating below —
        // $collection[$key]['field'] += x silently no-ops on a Collection
        // (offsetGet returns the nested array by value, so the increment
        // never writes back); a real array doesn't have that problem.
        $sourceBreakdown = [];
        foreach ($this->sourceColorMap() as $key => $meta) {
            $sourceBreakdown[$key] = ['label' => $meta['label'], 'members' => 0, 'leads' => 0, 'win' => 0, 'loss' => 0, 'children' => []];
        }

        (clone $baseQuery)
            ->selectRaw('users.source as source, COUNT(*) as total')
            ->groupBy('users.source')
            ->pluck('total', 'source')
            ->each(function ($total, $rawSource) use (&$sourceBreakdown) {
                $key = $this->normalizeSourceKey($rawSource);
                if (isset($sourceBreakdown[$key])) {
                    $sourceBreakdown[$key]['members'] += (int) $total;
                    $this->addSourceChild($sourceBreakdown[$key], $key, $rawSource, 'members', (int) $total);
                }
            });

        if (Schema::hasTable('member_lead_follow_ups')) {
            try {
                (clone $baseQuery)
                    ->join('member_lead_follow_ups', 'member_lead_follow_ups.user_id', 'users.id')
                    ->where(function ($q) {
                        // Sama dengan MemberLeadFollowUp::scopeApprovedMember()
                        $q->where('users.status_member', 'active')
                            ->orWhereNotNull('member_lead_follow_ups.sponsorkit_sent_at')
                            ->orWhere('member_lead_follow_ups.result', '!=', 'pending');
                    })
                    ->selectRaw('
                        users.source as source,
                        COUNT(*) as total,
                        SUM(CASE WHEN member_lead_follow_ups.result = "win" THEN 1 ELSE 0 END) as win_count,
                        SUM(CASE WHEN member_lead_follow_ups.result = "loss" THEN 1 ELSE 0 END) as loss_count
                    ')
                    ->groupBy('users.source')
                    ->get()
                    ->each(function ($row) use (&$sourceBreakdown) {
                        $key = $this->normalizeSourceKey($row->source);
                        if (isset($sourceBreakdown[$key])) {
                            $sourceBreakdown[$key]['leads'] += (int) $row->total;
                            $sourceBreakdown[$key]['win']   += (int) $row->win_count;
                            $sourceBreakdown[$key]['loss']  += (int) $row->loss_count;
                            $this->addSourceChild($sourceBreakdown[$key], $key, $row->source, 'leads', (int) $row->total, (int) $row->win_count, (int) $row->loss_count);
                        }
                    });
            } catch (\Throwable $e) {
                Log::warning('computeSourceBreakdown: lead stats failed: ' . $e->getMessage());
            }
        }

        return collect($sourceBreakdown)
            ->map(function ($row) {
                $row['conversion_rate'] = $row['leads'] > 0 ? round($row['win'] / $row['leads'] * 100, 1) : null;
                $row['children'] = collect($row['children'])
                    ->map(function ($child) {
                        $child['conversion_rate'] = $child['leads'] > 0 ? round($child['win'] / $child['leads'] * 100, 1) : null;
                        return $child;
                    })
                    ->sortByDesc('members')
                    ->values()
                    ->all();
                return $row;
            })
            ->filter(function ($row) {
                return $row['members'] > 0;
            })
            ->sortByDesc('members')
            ->values();
    }

    /**
     * Nama tampilan per source dari tabel member_sources, di-key 'category/code'
     * lowercase (mis. 'ep/mi2026' => 'Mining Indonesia 2026'), supaya breakdown
     * menampilkan nama event, bukan kode mentah. Kosong kalau tabel belum ada.
     */
    private function memberSourceNames(): array
    {
        static $names = null;

        if ($names === null) {
            $names = [];
            try {
                if (Schema::hasTable('member_sources')) {
                    foreach (MemberSource::all(['category', 'code', 'name']) as $row) {
                        $names[strtolower($row->category . '/' . $row->code)] = $row->name;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('memberSourceNames failed: ' . $e->getMessage());
            }
        }

        return $names;
    }

    /**
     * Rincian per source mentah (mis. EP/MI2026 vs EP/CTICMMS2026) di bawah
     * bucket 'Partnership Event' — satu bucket menampung banyak event, jadi
     * tim MR perlu lihat tiap event-nya. Bucket lain tidak dirinci. Grouping
     * case-insensitive supaya 'EP/MI2026' dan 'ep/mi2026' tidak terpecah.
     */
    private function addSourceChild(array &$bucket, string $key, $rawSource, string $field, int $total, int $win = 0, int $loss = 0): void
    {
        if ($key !== 'partner') {
            return;
        }

        $code = trim((string) $rawSource);
        $id = strtolower($code);

        if (!isset($bucket['children'][$id])) {
            $bucket['children'][$id] = ['label' => $this->memberSourceNames()[$id] ?? $code, 'code' => $code, 'members' => 0, 'leads' => 0, 'win' => 0, 'loss' => 0];
        }

        $bucket['children'][$id][$field] += $total;
        $bucket['children'][$id]['win']  += $win;
        $bucket['children'][$id]['loss'] += $loss;
    }

    /**
     * Registration channel buckets — shared between the per-row Source badge
     * (renderMemberRowCells) and the "Members by Source" breakdown stat, so
     * a row's badge and the aggregate table it feeds always agree.
     */
    private function sourceColorMap(): array
    {
        return [
            'website'   => ['label' => 'Website', 'color' => '#4e73df', 'icon' => 'fas fa-globe'],
            'apps'      => ['label' => 'Apps', 'color' => '#1cc88a', 'icon' => 'fas fa-mobile-alt'],
            'linkedin'  => ['label' => 'LinkedIn', 'color' => '#0077b5', 'icon' => 'fab fa-linkedin-in'],
            'instagram' => ['label' => 'Instagram', 'color' => '#e1306c', 'icon' => 'fab fa-instagram'],
            'dmc_event' => ['label' => 'DMC Event', 'color' => '#f6a92f', 'icon' => 'fas fa-calendar-alt'],
            'partner'   => ['label' => 'Partnership Event', 'color' => '#36b9cc', 'icon' => 'fas fa-handshake'],
        ];
    }

    /**
     * Raw `source` values are messy free text ('website' vs 'Linkedin' vs
     * legacy 'Event Mining Balikpapan' vs 'scanner', written inconsistently by
     * web/apps/scanner/admin-import entry points) — collapse to one of the
     * fixed buckets above. 'DMC Event', 'scanner', and any legacy
     * "event*"/"e/*"-prefixed value all fold into 'dmc_event' — check-in
     * scanner registrations are a DMC Event mechanism, not a separate channel;
     * the finer detail (which one it actually was) lives in `hear` instead,
     * see renderMemberRowCells(). There's no "Other" bucket — anything
     * unrecognized (including empty) returns a key that isn't in the map
     * above, so callers building a breakdown skip it (isset() guard) instead
     * of showing a catch-all category.
     */
    private function normalizeSourceKey($rawSource): string
    {
        $key = strtolower(trim((string) $rawSource));
        if ($key === '') {
            return 'other';
        }

        // 'Event Partnership' (ditulis PartnershipEventVisitorController::registerAsMember)
        // harus dicek sebelum bucket 'event*' di bawah, kalau tidak ikut kehitung DMC Event.
        if (strpos($key, 'ep/') === 0 || $key === 'event partnership') {
            return 'partner';
        }

        if ($key === 'dmc event' || $key === 'scanner' || strpos($key, 'e/') === 0 || strpos($key, 'event') === 0) {
            return 'dmc_event';
        }

        $map = $this->sourceColorMap();
        return isset($map[$key]) ? $key : 'other';
    }

    public function verifyMember(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isNewApproval = $user->status_member !== 'active';

        // Modal verify juga boleh mengoreksi email — simpan yang lama supaya
        // contact Mailchimp-nya bisa ikut dipindahkan (no-op kalau member ini
        // memang belum pernah masuk audience).
        $previousEmail = strtolower(trim($user->email ?? ''));

        if ($request->filled('name') || $request->filled('email') || $request->filled('alternative_email') || $request->filled('job_title') || $request->filled('phone')) {
            if ($request->filled('name')) {
                $user->name = trim($request->input('name'));
            }
            if ($request->filled('email')) {
                $user->email = trim($request->input('email'));
            }
            if ($request->filled('alternative_email')) {
                $user->alternative_email = trim($request->input('alternative_email'));
            }
            if ($request->filled('job_title') || $request->filled('phone')) {
                $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
                if ($request->filled('job_title')) {
                    $profile->job_title = trim($request->input('job_title'));
                }
                if ($request->filled('phone')) {
                    $profile->phone = trim($request->input('phone'));
                }
                $profile->save();
            }
        }

        $verifiedAt = now();

        $user->status_member = 'active';
        $user->uname = $this->generateVerificationMemberId($user, $verifiedAt);
        $user->verified_at = $verifiedAt;

        try {
            $qrImage = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($user->uname);

            $fileName = 'img-verify-' . $user->id . '-' . $verifiedAt->timestamp . '.png';
            $outputFile = '/public/uploads/qr-code/' . $fileName;
            $dbPath = '/storage/uploads/qr-code/' . $fileName;

            Storage::disk('local')->put($outputFile, $qrImage);
            $user->qrcode = $dbPath;
        } catch (\Throwable $e) {
            Log::warning('verifyMember: QR regeneration failed for user ' . $id . ': ' . $e->getMessage());
        }

        $user->save();

        $this->finishVerificationLog($user, VerificationLog::RESULT_ACTIVE);

        // Kalau emailnya barusan dikoreksi, pindahkan dulu contact lamanya —
        // sebelum import di bawah — supaya merge field hasil import mendarat di
        // contact yang sama, bukan bikin contact kedua dengan alamat baru.
        app(MemberVerificationService::class)->changeMailchimpEmail($user, $previousEmail);

        // Auto-import to Mailchimp after verify
        $company = CompanyModel::where('users_id', $user->id)->first();
        $profile = ProfileModel::where('users_id', $user->id)->first();

        // Only an explicit transition to approved membership can create a lead.
        // Editing data or verifying an already-active member must not reopen a
        // resolved lead or reset the deadline of an existing one. Explore
        // Marketing consent remains required for automatic lead creation.
        if ($isNewApproval && $company && $company->isLeadExplore() && Schema::hasTable('member_lead_follow_ups')) {
            try {
                $leadAdmin = auth()->user();
                $lead = MemberLeadFollowUp::firstOrCreate(
                    ['user_id' => $user->id, 'result' => MemberLeadFollowUp::RESULT_PENDING],
                    [
                        'deadline_at'     => MemberLeadFollowUp::deadlineFor('sponsorkit', $verifiedAt),
                        'created_by_id'   => auth()->id(),
                        'created_by_name' => $leadAdmin ? $leadAdmin->name : 'Staff',
                    ]
                );

                // Lead lama yang dibuat sebelum approve (saat registrasi) dan
                // belum disentuh: SLA 48 jam dihitung dari approve, bukan registrasi.
                if (!$lead->wasRecentlyCreated && !$lead->sponsorkit_sent_at) {
                    $lead->deadline_at = MemberLeadFollowUp::deadlineFor('sponsorkit', $verifiedAt);
                    $lead->save();
                }
            } catch (\Throwable $e) {
                Log::warning('verifyMember: lead auto-create failed for user ' . $id . ': ' . $e->getMessage());
            }
        }

        $email = strtolower(trim($user->email ?? ''));

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
                $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);
                $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');

                if ($apiKey && $server && $listId) {
                    $merge = [];
                    if (!empty($user->name))               $merge['FNAME']    = $user->name;
                    if (!empty($company->company_name))    $merge['MMERGE5']  = $company->company_name;
                    if (!empty($profile->job_title))       $merge['MMERGE7']  = $profile->job_title;
                    if (!empty($company->company_website)) $merge['MMERGE13'] = $company->company_website;
                    $merge['MMERGE11'] = now()->format('m/d/Y');

                    $phone = $profile->fullphone ?? $profile->phone ?? null;
                    if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', trim((string) $phone))) {
                        $merge['MERGE4'] = trim((string) $phone);
                    }

                    $subscriberHash = md5($email);
                    Http::withBasicAuth('anystring', $apiKey)
                        ->timeout(20)
                        ->put("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}", [
                            'email_address' => $email,
                            'status_if_new' => 'subscribed',
                            'status'        => 'subscribed',
                            'merge_fields'  => $merge,
                        ]);
                    MemberVerificationService::forgetMailchimpStatusCache();
                }
            } catch (\Throwable $e) {
                Log::warning('verifyMember: Mailchimp import failed for user ' . $id . ': ' . $e->getMessage());
            }

            if ($request->boolean('send_notification', true)) {
                try {
                    $setPasswordUrl = null;
                    if (empty($user->password)) {
                        $token = Password::broker()->createToken($user);
                        $setPasswordUrl = route('password.reset', [
                            'token' => $token,
                            'email' => $email,
                        ]);
                    }

                    $memberId = $user->uname;
                    $linkExpiryMinutes = (int) config('auth.passwords.users.expire', 60);
                    $linkExpiryHours = (int) max(1, ceil($linkExpiryMinutes / 60));
                    $loginUrl = (string) config('dmc.post_reset_password_redirect_url', 'https://www.djakarta-miningclub.com?modalloginopen=true');

                    $send = new EmailSender();
                    $send->subject = 'Djakarta Mining Club – Membership Approval Confirmation (ID: ' . $memberId . ')';
                    $send->template = 'email.membership-approved';
                    $send->data = [
                        'users_name' => $user->name ?? 'Member',
                        'member_id' => $memberId,
                        'registered_email' => $email,
                        'set_password_url' => $setPasswordUrl,
                        'link_expiry_hours' => $linkExpiryHours,
                        'login_url' => $loginUrl,
                    ];
                    $send->name = $user->name ?? 'Member';
                    $send->from = env('EMAIL_SENDER');
                    $send->name_sender = env('EMAIL_NAME');
                    $send->to = $email;
                    $send->sendEmail();
                } catch (\Throwable $e) {
                    Log::warning('verifyMember: approval email failed for user ' . $id . ': ' . $e->getMessage());
                }
            }
        }

        $message = 'Member verified dan data telah diimport ke Mailchimp.'
            . ($request->boolean('send_notification', true) ? '' : ' Email notifikasi TIDAK dikirim (sesuai pilihan admin).');

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function declineMember(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->status_member = 'declined';
        $user->save();

        $this->finishVerificationLog($user, VerificationLog::RESULT_DECLINED);

        $email = strtolower(trim($user->email ?? ''));
        $sendNotification = $request->boolean('send_notification', true);

        if ($sendNotification && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $send = new EmailSender();
                $send->subject = 'Update on Your Djakarta Mining Club Membership Application';
                $send->template = 'email.membership-declined';
                $send->data = [
                    'users_name' => $user->name ?? 'Applicant',
                ];
                $send->name         = $user->name ?? 'Applicant';
                $send->from         = env('EMAIL_SENDER');
                $send->name_sender  = env('EMAIL_NAME');
                $send->to           = $email;
                $send->sendEmail();
            } catch (\Throwable $e) {
                Log::warning('declineMember: decline email failed for user ' . $id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => $sendNotification
                ? 'Membership application declined dan email notifikasi telah dikirim.'
                : 'Membership application declined. Email notifikasi TIDAK dikirim (sesuai pilihan admin).',
        ]);
    }

    public function deactivateMember(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $user  = User::findOrFail($id);
        $admin = auth()->user();

        $user->status_member       = 'deactivated';
        $user->deactivation_reason = trim($request->input('reason'));
        $user->deactivated_at      = now();
        $user->deactivated_by      = $admin ? $admin->name : 'Staff';
        $user->save();

        // Member non-aktif tidak boleh lagi menerima campaign member — sekalian
        // archive dari audience Mailchimp (bukan unsubscribe: archived tidak
        // dihitung ke billing, unsubscribed tetap terhitung). Gagal/tidak ketemu
        // di Mailchimp tidak boleh membatalkan deactivate-nya, cukup dilaporkan
        // di message.
        $archived = app(MemberVerificationService::class)->archiveFromMailchimp($user);

        return response()->json([
            'success' => true,
            'message' => $user->name . ' berhasil di-deactivate'
                . ($archived ? ' & di-archive dari Mailchimp.' : '. (Mailchimp: tidak ada kontak yang di-archive.)'),
        ]);
    }

    public function reactivateMember(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status_member       = 'active';
        $user->deactivation_reason = null;
        $user->deactivated_at      = null;
        $user->deactivated_by      = null;
        $user->save();

        // Kebalikan dari deactivate: coba subscribe-kan lagi ke Mailchimp.
        // Mailchimp menolak contact yang statusnya unsubscribed (compliance
        // state) — kalau ditolak, opt-in ulang harus dilakukan manual/oleh
        // member sendiri, jadi hasilnya dilaporkan apa adanya ke admin.
        $resubscribed = app(MemberVerificationService::class)->syncToMailchimp($user);

        return response()->json([
            'success' => true,
            'message' => $user->name . ' berhasil di-reactivate'
                . ($resubscribed ? ' & di-subscribe lagi ke Mailchimp.' : '. (Mailchimp: subscribe ulang gagal — perlu opt-in manual.)'),
        ]);
    }

    public function toggleTwoStep(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $admin = auth()->user();

        if ($user->two_step_verified) {
            $user->two_step_verified = false;
            $user->two_step_verified_at = null;
            $user->two_step_verified_by = null;
            $user->save();
            $msg = 'Verifikasi 2-Langkah dibatalkan untuk ' . $user->name . '.';
        } else {
            $user->two_step_verified = true;
            $user->two_step_verified_at = now();
            $user->two_step_verified_by = $admin ? $admin->name : 'Staff';
            $user->save();
            $msg = $user->name . ' berhasil ditandai Verifikasi 2-Langkah (Staff).';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'two_step_verified' => (bool) $user->two_step_verified,
            'two_step_verified_at' => $user->two_step_verified_at ? $user->two_step_verified_at->format('d M Y H:i') : null,
            'two_step_verified_by' => $user->two_step_verified_by,
        ]);
    }

    public function bulkTwoStepImportTemplate()
    {
        return Excel::download(new BulkTwoStepVerificationImportTemplate(), 'template-import-verifikasi-2-langkah.xlsx');
    }

    /**
     * Bulk version of toggleTwoStep() — sama efeknya, cuma dari daftar email
     * di Excel sekaligus (LinkedIn/Telfon verification batch yang dikerjakan
     * di luar sistem, lalu hasilnya diimport balik ke sini).
     */
    public function bulkTwoStepImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $admin = auth()->user();
        $import = new BulkTwoStepVerificationImport($admin ? $admin->name : 'Staff');
        Excel::import($import, $request->file('file'));

        $message = "Import selesai. {$import->getVerified()} member ditandai verified, "
            . "{$import->getUnverified()} dibatalkan verifikasinya, {$import->getUnchanged()} tidak berubah.";
        if ($import->getNotFound() > 0) {
            $message .= " {$import->getNotFound()} email tidak ditemukan.";
        }

        if (!empty($import->getErrors())) {
            return back()->with('success', $message)->with('import_errors', $import->getErrors());
        }

        return back()->with('success', $message);
    }

    private function generateVerificationMemberId(User $user, ?Carbon $verifiedAt = null): string
    {
        $verifiedAt = $verifiedAt ? $verifiedAt->copy() : now();
        $datePart = $verifiedAt->format('Ymd');
        $monthPrefix = $verifiedAt->format('Ym');

        $maxSequence = 0;

        User::where('uname', 'like', $monthPrefix . '%')
            ->pluck('uname')
            ->each(function ($uname) use ($monthPrefix, &$maxSequence) {
                if (!is_string($uname)) {
                    return;
                }

                if (preg_match('/^' . preg_quote($monthPrefix, '/') . '\d{2}(\d{4})[A-Z0-9]*$/', $uname, $matches)) {
                    $sequence = (int) $matches[1];
                    if ($sequence > $maxSequence) {
                        $maxSequence = $sequence;
                    }
                }
            });

        $nextSequence = max(1, $maxSequence + 1);

        for ($sequence = $nextSequence; $sequence <= 9999; $sequence++) {
            $memberId = $datePart . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            if (!$this->memberIdExistsForOtherUser($memberId, $user)) {
                return $memberId;
            }
        }

        $sequence = max(10000, $nextSequence);
        while (true) {
            $memberId = $datePart . (string) $sequence;
            if (!$this->memberIdExistsForOtherUser($memberId, $user)) {
                return $memberId;
            }
            $sequence++;
        }
    }

    private function memberIdExistsForOtherUser(string $memberId, User $user): bool
    {
        return User::where('uname', $memberId)
            ->where('id', '!=', $user->id)
            ->exists();
    }

    private function generateMemberIdFromUser(User $user): string
    {
        $datePart = Carbon::parse($user->created_at ?? now())->format('Ymd');
        $idPart = str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
        $memberId = $datePart . $idPart;

        $isTakenByOtherUser = User::where('uname', $memberId)
            ->where('id', '!=', $user->id)
            ->exists();

        if (!$isTakenByOtherUser) {
            return $memberId;
        }

        return $memberId . strtoupper(Str::random(2));
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'alternative_email' => 'nullable|email|max:255',
            'job_title'     => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:50',
            'prefix'        => 'nullable|string|max:255',
            'company_name'  => 'nullable|string|max:255',
            'company_website' => 'nullable|string|max:255',
            'company_category' => 'nullable|string|max:255',
            'company_other' => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'portal_code'   => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:255',
            'prefix_office_number' => 'nullable|string|max:255',
            'office_number' => 'nullable|string|max:255',
            'full_office_number' => 'nullable|string|max:255',
            'status_member' => 'nullable|in:active,pending',
            'tier'          => 'nullable|in:reguler,black',
            'job_tier'      => 'nullable|in:auto,1,2,3,4',
        ]);

        $user    = User::findOrFail($id);
        $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
        $company = null;

        if (!empty($profile->company_id)) {
            $company = CompanyModel::find($profile->company_id);
        }

        if (!$company) {
            $company = CompanyModel::where('users_id', $user->id)->first();
        }

        if (!$company) {
            $company = new CompanyModel();
            $company->users_id = $user->id;
        }

        $normalizeForCompare = static function ($value): string {
            if (is_null($value)) {
                return '';
            }

            return is_string($value) ? trim($value) : (string) $value;
        };

        $nullableString = static function ($value): ?string {
            if (is_null($value)) {
                return null;
            }

            $value = is_string($value) ? trim($value) : (string) $value;
            return $value === '' ? null : $value;
        };

        $nextStatusMember = $request->filled('status_member')
            ? (string) $request->input('status_member')
            : (string) ($user->status_member ?? '');
        $nextTier = $request->filled('tier')
            ? (string) $request->input('tier')
            : (string) ($user->tier ?? '');

        $watchUser    = ['name', 'email', 'alternative_email', 'status_member', 'tier'];
        $watchProfile = ['job_title', 'phone'];
        $watchCompany = [
            'prefix',
            'company_name',
            'company_website',
            'company_category',
            'company_other',
            'address',
            'city',
            'portal_code',
            'country',
            'prefix_office_number',
            'office_number',
            'full_office_number',
        ];

        $changes = [];
        $hasCompanyChanges = false;
        $shouldAutoVerifyCompany = false;
        $isFilled = static function ($value): bool {
            return !is_null($value) && (!is_string($value) || trim($value) !== '');
        };

        foreach ($watchUser as $field) {
            $old = $normalizeForCompare($user->$field ?? '');
            if ($field === 'status_member') {
                $new = $normalizeForCompare($nextStatusMember);
            } elseif ($field === 'tier') {
                $new = $normalizeForCompare($nextTier);
            } else {
                $new = $normalizeForCompare($request->input($field, ''));
            }
            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        foreach ($watchProfile as $field) {
            $old = $normalizeForCompare($profile->$field ?? '');
            $new = $normalizeForCompare($request->input($field, ''));
            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        foreach ($watchCompany as $field) {
            if (!$request->has($field)) {
                continue;
            }
            $old = $normalizeForCompare($company->$field ?? '');
            $new = $normalizeForCompare($request->input($field, ''));
            if ($old !== $new) {
                $hasCompanyChanges = true;
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        $candidateCompanyData = [];
        foreach ($watchCompany as $field) {
            if ($request->has($field)) {
                $candidateCompanyData[$field] = $nullableString($request->input($field));
            } else {
                $candidateCompanyData[$field] = $nullableString($company->{$field} ?? null);
            }
        }

        $requiredCompanyFields = [
            'prefix',
            'company_name',
            'company_website',
            'company_category',
            'address',
            'city',
            'portal_code',
            'prefix_office_number',
            'office_number',
            'full_office_number',
            'country',
        ];

        $isCompanyComplete = true;
        foreach ($requiredCompanyFields as $field) {
            if (!$isFilled($candidateCompanyData[$field] ?? null)) {
                $isCompanyComplete = false;
                break;
            }
        }

        if (($candidateCompanyData['company_category'] ?? null) === 'other' && !$isFilled($candidateCompanyData['company_other'] ?? null)) {
            $isCompanyComplete = false;
        }

        if (!(bool) ($company->is_verified ?? false) && $isCompanyComplete) {
            // Auto-verify hanya jika field company lengkap.
            $shouldAutoVerifyCompany = true;
        }

        if ($shouldAutoVerifyCompany) {
            $changes['is_verified'] = ['old' => (bool) ($company->is_verified ?? false) ? '1' : '0', 'new' => '1'];
        }

        // Job tier: 'auto' = ikut job_title (JobTierClassifier), 1-4 = override manual.
        // Hanya untuk member active — status lain selalu dikosongkan.
        $nextJobTier = null;
        $nextJobTierManual = null;
        if ($request->filled('job_tier') && ProfileModel::hasJobTierColumn()) {
            $isActiveNext = $nextStatusMember === 'active';
            $nextJobTierManual = $isActiveNext && $request->input('job_tier') !== 'auto';
            if ($isActiveNext) {
                $nextJobTier = $nextJobTierManual
                    ? (int) $request->input('job_tier')
                    : JobTierClassifier::classify($nullableString($request->job_title));
            }

            $oldJobTier = $this->jobTierLogValue($profile->job_tier, (bool) $profile->job_tier_manual);
            $newJobTier = $this->jobTierLogValue($nextJobTier, $nextJobTierManual);
            if ($oldJobTier !== $newJobTier) {
                $changes['job_tier'] = ['old' => $oldJobTier, 'new' => $newJobTier];
            }
        }

        if (empty($changes)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
        }

        $admin = auth()->user();

        // Kalau member ini lagi ditandai "needs follow up" (Member Follow-Up
        // menu), edit di sini SUDAH sama artinya dengan menyelesaikan follow-up
        // itu — admin gak wajib buka menu Follow-Up terpisah cuma buat
        // menutupnya. Sama persis perilakunya dengan
        // MemberCompanyFollowUpController::update() (menandai 2-Step Verified +
        // menutup follow-up), tapi TANPA auto-approve status_member — modal
        // Edit User ini sudah punya kontrol status_member sendiri, jadi gak
        // perlu ditimpa otomatis.
        $openFollowUp = MemberCompanyFollowUp::where('user_id', $user->id)
            ->where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP)
            ->first();

        $user->name              = trim((string) $request->name);
        $user->email             = trim((string) $request->email);
        $user->alternative_email = $nullableString($request->alternative_email);
        $user->status_member     = $nextStatusMember;
        $user->tier              = $nextTier;
        if ($openFollowUp) {
            $user->two_step_verified    = true;
            $user->two_step_verified_at = now();
            $user->two_step_verified_by = $admin ? $admin->name : 'Staff';
        }
        $user->save();

        if ($openFollowUp) {
            $openFollowUp->status           = MemberCompanyFollowUp::STATUS_VERIFIED;
            $openFollowUp->verified_by_id   = auth()->id();
            $openFollowUp->verified_by_name = $admin ? $admin->name : null;
            $openFollowUp->verified_at      = now();
            $openFollowUp->save();
        }

        $shouldSaveCompany = $hasCompanyChanges || $shouldAutoVerifyCompany;
        if ($shouldSaveCompany && $company->exists) {
            $belongsToOtherUser = !empty($company->users_id) && (int) $company->users_id !== (int) $user->id;
            $sharedWithOtherUsers = ProfileModel::where('company_id', $company->id)
                ->where('users_id', '!=', $user->id)
                ->exists();

            if ($belongsToOtherUser || $sharedWithOtherUsers) {
                $company = $company->replicate();
                $company->users_id = $user->id;
            }
        }

        if ($shouldSaveCompany) {
            foreach ($watchCompany as $field) {
                if (!$request->has($field)) {
                    continue;
                }
                $company->{$field} = $nullableString($request->input($field));
            }

            if ($shouldAutoVerifyCompany) {
                $company->is_verified = true;
                $company->verified_at = now();
            }

            $company->users_id = $user->id;
            $company->save();
        }

        $phone = $nullableString($request->phone);
        $profile->job_title = $nullableString($request->job_title);
        $profile->phone     = $phone;
        $profile->fullphone = $phone;
        if ($nextJobTierManual !== null) {
            $profile->job_tier_manual = $nextJobTierManual;
            $profile->job_tier        = $nextJobTier;
        }
        if (($hasCompanyChanges || $company->exists) && (int) ($company->id ?? 0) > 0) {
            $profile->company_id = $company->id;
        }
        $profile->users_id  = $user->id;
        $profile->save();

        DB::table('user_edit_logs')->insert([
            'user_id'    => $user->id,
            'admin_id'   => auth()->id(),
            'admin_name' => $admin ? $admin->name : null,
            'changes'    => json_encode($changes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Admin edited user', [
            'admin'   => $admin ? $admin->name : null,
            'user_id' => $user->id,
            'email'   => $user->email,
            'changes' => $changes,
        ]);

        // Email diganti dari CMS = contact-nya di Mailchimp ikut dipindahkan,
        // supaya alamat lama berhenti menerima campaign dan alamat baru yang
        // menerimanya. Dijalankan paling akhir: semua penulisan DB sudah
        // selesai, jadi Mailchimp lambat/error tidak menggagalkan edit-nya.
        $message = 'Data user berhasil diperbarui.'
            . ($openFollowUp ? ' Follow-up perusahaan otomatis ditutup & ditandai 2-Step Verified.' : '');
        if (isset($changes['email'])) {
            $mailchimpResult = app(MemberVerificationService::class)
                ->changeMailchimpEmail($user, (string) $changes['email']['old']);

            if ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_SWAPPED) {
                $message .= ' Mailchimp: email lama di-archive, email baru di-subscribe.';
            } elseif ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_FAILED) {
                $message .= ' (Mailchimp: gagal memindahkan email — perlu dicek manual.)';
            }
        }

        return response()->json(['success' => true, 'message' => $message, 'changes' => $changes]);
    }

    /** Nilai job tier untuk user_edit_logs, mis. "Tier 2 (manual)" / "Tier 3 (auto)". */
    private function jobTierLogValue($tier, bool $manual): string
    {
        if ($tier === null || $tier === '') {
            return '';
        }

        return JobTierClassifier::label((int) $tier) . ($manual ? ' (manual)' : ' (auto)');
    }

    public function editLogs(Request $request)
    {
        $criticalFields = ['company_name', 'company_category', 'company_other', 'prefix'];

        $query = UserEditLog::with('user')->orderByDesc('created_at');

        if ($request->source === 'self') {
            $query->whereNull('admin_id');
        } elseif ($request->source === 'admin') {
            $query->whereNotNull('admin_id');
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->critical === '1') {
            $query->where(function ($q) use ($criticalFields) {
                foreach ($criticalFields as $field) {
                    $q->orWhere('changes', 'like', '%"' . $field . '"%');
                }
            });
        }

        $logs = $query->paginate(50);

        $countSelfEdit    = UserEditLog::whereNull('admin_id')->count();
        $countAdminEdit   = UserEditLog::whereNotNull('admin_id')->count();
        $countUniqueUsers = UserEditLog::distinct('user_id')->count('user_id');
        $countCritical    = UserEditLog::whereNull('admin_id')
            ->where(function ($q) use ($criticalFields) {
                foreach ($criticalFields as $field) {
                    $q->orWhere('changes', 'like', '%"' . $field . '"%');
                }
            })->count();

        return view('admin.users.edit_logs', compact(
            'logs',
            'countSelfEdit',
            'countAdminEdit',
            'countUniqueUsers',
            'countCritical'
        ));
    }

    public function userLogs(Request $request, $id)
    {
        $logs = DB::table('user_edit_logs')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                $log->changes = json_decode($log->changes, true);
                return $log;
            });

        return response()->json($logs);
    }

    public function import(Request $request)
    {
        $request->validate([
            'uploaded_file' => 'required|file|mimes:xls,xlsx|max:20480', // 20MB
        ]);

        $file = $request->file('uploaded_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();

            $lastRow    = (int) $sheet->getHighestDataRow();
            $startRow   = 2; // header di row 1
            $success    = 0;
            $skipped    = 0;
            $errors     = 0;
            $errorRows  = [];

            // Helper kecil
            $clean = fn($v) => is_string($v) ? trim($v) : (is_null($v) ? null : $v);
            $validEmail = fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL);
            $normalizeHeader = function ($header) {
                $header = strtolower(trim((string) $header));
                if ($header === '') {
                    return '';
                }
                // Samakan format header: "Email Address" -> "email address"
                $header = preg_replace('/[^a-z0-9]+/i', ' ', $header);
                return trim(preg_replace('/\s+/', ' ', $header));
            };
            $nullIfPlaceholder = function ($value) use ($clean) {
                $value = $clean($value);
                if (!is_string($value)) {
                    return $value;
                }

                $normalized = strtolower(trim($value));
                if (in_array($normalized, ['-', '--', 'n/a', 'na', 'null', 'none'], true)) {
                    return null;
                }

                return $value;
            };
            $normPhone = function ($p) {
                if (!$p) return null;
                $p = preg_replace('/[^0-9+]/', '', (string)$p);
                // contoh normalisasi sederhana: leading 0 -> +62
                if (Str::startsWith($p, '0')) $p = '+62' . ltrim($p, '0');
                return $p;
            };
            $normUrl = function ($u) use ($clean) {
                $u = $clean($u);
                if (!$u) return null;
                // tambah https kalau user isi tanpa schema
                if (!Str::startsWith($u, ['http://', 'https://'])) {
                    $u = 'https://' . $u;
                }
                return $u;
            };

            // Build mapping header -> column letter (row 1)
            $highestColumn = $sheet->getHighestDataColumn();
            $headerRow     = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1] ?? [];
            $headerToCol   = [];
            foreach ($headerRow as $columnLetter => $headerValue) {
                $normalizedHeader = $normalizeHeader($headerValue);
                if ($normalizedHeader !== '') {
                    $headerToCol[$normalizedHeader] = $columnLetter;
                }
            }

            $getCellValue = function ($columnLetter, $row) use ($sheet, $clean) {
                $cell = $sheet->getCell($columnLetter . $row);
                $rawValue = $cell->getValue();

                // Untuk numeric (terutama nomor telepon), gunakan formatted value agar tidak scientific notation
                if (is_numeric($rawValue)) {
                    return $clean($cell->getFormattedValue());
                }

                return $clean($rawValue);
            };

            $valueFromAliases = function (int $row, array $aliases, ?string $fallbackColumn = null) use ($headerToCol, $normalizeHeader, $getCellValue) {
                foreach ($aliases as $alias) {
                    $normalizedAlias = $normalizeHeader($alias);
                    if (isset($headerToCol[$normalizedAlias])) {
                        return $getCellValue($headerToCol[$normalizedAlias], $row);
                    }
                }

                return $fallbackColumn ? $getCellValue($fallbackColumn, $row) : null;
            };

            DB::beginTransaction();

            for ($row = $startRow; $row <= $lastRow; $row++) {
                try {
                    // Mapping berdasarkan header template, fallback ke posisi lama agar tetap backward-compatible
                    $name          = $valueFromAliases($row, ['Name'], 'B');
                    $jobTitle      = $valueFromAliases($row, ['Job title', 'Job Title'], 'C');
                    $companyName   = $valueFromAliases($row, ['Company name', 'Company Name'], 'A');
                    $email         = strtolower((string) $valueFromAliases($row, ['Email Address', 'Email'], 'E'));
                    $phoneRaw      = $valueFromAliases($row, ['Mobile Phone', 'Phone', 'Phone Number'], 'D');
                    $officeNumber  = $valueFromAliases($row, ['Office Number', 'Full Office Number'], 'L');
                    $address       = $valueFromAliases($row, ['Office Address', 'Address'], 'I');
                    $companyWeb    = $normUrl($valueFromAliases($row, ['Website', 'Company Website'], 'F'));
                    $companyCat    = $valueFromAliases($row, ['Category', 'Company Category'], 'G');
                    $companyOther  = $valueFromAliases($row, ['Company Other', 'Other Category'], 'H');
                    $city          = $valueFromAliases($row, ['City'], 'J');
                    $portalCode    = $valueFromAliases($row, ['Portal Code', 'Postal Code', 'Zip Code'], 'K');
                    $registerAs    = $nullIfPlaceholder($valueFromAliases($row, ['Register As', 'Source'], 'M')); // optional

                    // skip baris kosong total
                    if (!$email && !$name && !$companyName) {
                        $skipped++;
                        continue;
                    }

                    // wajib email valid
                    if (!$email || !$validEmail($email)) {
                        $errors++;
                        $errorRows[] = "Row {$row}: email invalid/empty ({$email})";
                        continue;
                    }

                    // upsert User
                    /** @var \App\Models\User $user */
                    $user = \App\Models\User::firstOrNew(['email' => $email]);
                    $user->name     = $name ?: $user->name ?: '(no name)';
                    $user->isStatus = 'Active';
                    if ($registerAs) {
                        $user->source = $registerAs;
                    }
                    $user->save();

                    // upsert Company (by users_id)
                    /** @var \App\Models\CompanyModel $company */
                    $company = CompanyModel::firstOrNew(['users_id' => $user->id]);
                    if ($companyName)  $company->company_name = $companyName;
                    if ($companyWeb)   $company->company_website = $companyWeb;
                    if ($companyCat)   $company->company_category = $companyCat;
                    if ($companyOther) $company->company_other = $companyOther;
                    if ($address)      $company->address = $address;
                    if ($city)         $company->city = $city;
                    if ($portalCode)   $company->portal_code = $portalCode;
                    if ($officeNumber) {
                        $company->office_number = $officeNumber;
                        $company->full_office_number = $officeNumber;
                    }
                    $company->save();

                    // upsert Profile (by users_id)
                    /** @var \App\Models\ProfileModel $profile */
                    $profile = ProfileModel::firstOrNew(['users_id' => $user->id]);
                    $phoneNormalized = $normPhone($phoneRaw);
                    if ($phoneNormalized) {
                        $profile->fullphone  = $phoneNormalized;
                        $profile->phone      = $phoneNormalized;
                    }
                    if ($jobTitle) {
                        $profile->job_title  = $jobTitle;
                    }
                    $profile->users_id   = $user->id;
                    $profile->company_id = $company->id;
                    $profile->save();

                    $success++;
                } catch (\Throwable $rowEx) {
                    $errors++;
                    $errorRows[] = "Row {$row}: " . $rowEx->getMessage();
                    // lanjut baris berikutnya
                }
            }

            DB::commit();

            // log detail error ke laravel.log biar ga numpuk di flash message
            if (!empty($errorRows)) {
                Log::warning('Import XLS - partial errors', ['errors' => $errorRows]);
            }

            return back()->with('success', "Import selesai: {$success} berhasil, {$skipped} dilewati (kosong), {$errors} error. Cek log untuk detail error.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import XLS gagal total', ['exception' => $e]);
            return back()->withErrors('Gagal mengimpor data. Pesan: ' . $e->getMessage());
        }
    }



    public function mailchimpContactCount()
    {
        $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
        $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');
        $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);

        if (!$apiKey || !$listId || !$server) {
            return response()->json(['success' => false, 'count' => null, 'message' => 'Mailchimp belum dikonfigurasi.']);
        }

        try {
            $resp = Http::withBasicAuth('anystring', $apiKey)
                ->timeout(10)
                ->get("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}", [
                    'fields' => 'stats.member_count,stats.unsubscribe_count,stats.cleaned_count',
                ]);

            if (!$resp->successful()) {
                return response()->json(['success' => false, 'count' => null, 'message' => 'Gagal mengambil data Mailchimp.']);
            }

            $stats = $resp->json('stats');
            return response()->json([
                'success'      => true,
                'count'        => $stats['member_count'] ?? 0,
                'unsubscribed' => $stats['unsubscribe_count'] ?? 0,
                'cleaned'      => $stats['cleaned_count'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning('mailchimpContactCount failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'count' => null, 'message' => $e->getMessage()]);
        }
    }

    public function member()
    {

        $list = MemberModel::orderBy('created_at', 'desc')->get();
        $data = [
            'list' => $list
        ];
        return view('admin.member.index', $data);
    }

    public function editUserEvent($id)
    {
        $data = User::join('payment', 'payment.member_id', 'users.id')
            ->leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->leftjoin('company', 'company.id', 'profiles.company_id')
            ->where('payment.id', $id)
            ->first();
        if (!empty($data)) {

            return response()->json([
                'status' => 1,
                'payload' => $data
            ]);
        } else {
            $data = User::join('profiles', 'profiles.users_id', 'users.id')
                ->join('company', 'company.id', 'profiles.company_id')
                ->where('payment.id', $id)
                ->first();
            return response()->json([
                'status' => 1,
                'payload' => $data
            ]);
        }
    }

    public function checkMember($email)
    {
        $check = MemberModel::where('email', $email)->first();
        if (!empty($check)) {
            return response()->json([
                'status' => 1,
                'message' => 'Members'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Non-Members'
            ]);
        }
    }

    public function importToMailchimp(Request $request)
    {
        $email  = strtolower(trim($request->input('email', '')));
        $userId = $request->input('user_id');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Email tidak valid.'], 422);
        }

        $user    = $userId ? User::find($userId) : User::where('email', $email)->first();
        $company = $user ? CompanyModel::where('users_id', $user->id)->first()
            : CompanyModel::where('company_email', $email)->first();
        $profile = $user ? ProfileModel::where('users_id', $user->id)->first()
            : ProfileModel::where('email', $email)->first();

        $merge = [];
        if (!empty($user->name))               $merge['FNAME']    = $user->name;
        if (!empty($company->company_name))    $merge['MMERGE5']  = $company->company_name;
        if (!empty($profile->job_title))       $merge['MMERGE7']  = $profile->job_title;
        if (!empty($company->explore ?? $company->cci)) $merge['MMERGE12'] = $company->explore ?? $company->cci;
        if (!empty($company->company_website)) $merge['MMERGE13'] = $company->company_website;
        $merge['MMERGE11'] = now()->format('m/d/Y');

        $phone = is_string($profile->fullphone ?? $profile->phone ?? null) ? trim($profile->fullphone ?? $profile->phone) : null;
        if ($phone && preg_match('/^\+\d[\d\s\-\(\)]{5,}$/', $phone)) {
            $merge['MERGE4'] = $phone;
        }

        $apiKey = config('newsletter.apiKey');
        $listId = config('newsletter.lists.subscribers.id');
        $server = explode('-', $apiKey)[1] ?? null;

        if (!$apiKey || !$server || !$listId) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi Mailchimp belum lengkap.'], 500);
        }

        try {
            $resp = \Illuminate\Support\Facades\Http::withBasicAuth('anystring', $apiKey)
                ->timeout(20)
                ->put("https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/" . md5($email), [
                    'email_address' => $email,
                    'status_if_new' => 'subscribed',
                    'status'        => 'subscribed',
                    'merge_fields'  => $merge,
                ]);

            if (!$resp->successful()) {
                $json   = $resp->json();
                $detail = $json['detail'] ?? 'Gagal impor.';
                if (!empty($json['errors'])) {
                    $detail .= ' — ' . collect($json['errors'])->map(fn($e) => ($e['field'] ?? '') . ': ' . ($e['message'] ?? ''))->implode(' | ');
                }
                return response()->json(['success' => false, 'message' => $resp->status() . ': ' . $detail], 400);
            }

            MemberVerificationService::forgetMailchimpStatusCache();

            return response()->json(['success' => true, 'message' => 'Berhasil diimpor ke Mailchimp.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    public function export(Request $request, $id)
    {
        $m = MemberModel::findOrFail($id); // <- hanya baca dari MemberModel

        // validasi minimal yang memang ada di MemberModel
        $email       = strtolower(trim((string) $m->email));
        $name        = trim((string)($m->name ?? ''));
        $companyName = trim((string)($m->company_name ?? ''));
        if ($email === '')   return back()->with('error', 'Export gagal: email kosong.');
        if ($companyName === '') return back()->with('error', 'Export gagal: company_name kosong.');

        DB::beginTransaction();
        try {
            // ===== 1) USERS (key: email) =====
            $existingUser = User::where('email', $email)->first();
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'         => $name ?: $email,
                    'password'     => $existingUser ? $existingUser->password : null,
                    'verify_email' => $existingUser ? ($existingUser->verify_email ?? 'verified') : 'verified',
                    'verify_phone' => $existingUser ? ($existingUser->verify_phone ?? null) : null,
                    'otp'          => null,
                    'isStatus'     => $existingUser ? ($existingUser->isStatus ?? 'Active') : 'Active',
                    'qrcode'       => $existingUser ? $existingUser->qrcode : null,
                ]
            );

            if (empty($user->uname)) {
                $user->uname = $this->generateMemberIdFromUser($user);
                $user->save();
            }

            // ===== 2) COMPANY (key: company_name + optional company_website) =====
            $companyWebsite = trim((string)($m->company_website ?? ''));
            $companyQuery = CompanyModel::where('company_name', $companyName);
            if ($companyWebsite !== '') $companyQuery->where('company_website', $companyWebsite);
            $existingCompany = $companyQuery->first();

            $companyPayload = [
                'prefix'               => $m->prefix ?: null,
                'company_name'         => $companyName,
                'company_website'      => $companyWebsite ?: null,
                'company_category'     => $m->company_category ?: null,
                'company_other'        => $m->company_other ?: null,
                'address'              => $m->address ?: null,
                'city'                 => $m->city ?: null,
                'portal_code'          => $m->portal_code ?: null,
                'prefix_office_number' => $m->prefix_office_number ?: null,
                'office_number'        => $m->office_number ?: null,
                'full_office_number'   => $m->full_office_number ?: null,
                'country'              => $m->country ?: null,
                'cci'                  => (int)($m->cci ?? 0),
                'explore'              => (int)($m->explore ?? 0),
                'users_id'             => $user->id, // owner
            ];

            if ($existingCompany) {
                $existingCompany->update($companyPayload);
                $company = $existingCompany;
            } else {
                $company = CompanyModel::create($companyPayload);
            }

            // ===== 3) PROFILE (key: users_id) =====
            $existingProfile = ProfileModel::where('users_id', $user->id)->first();
            $profilePayload = [
                'prefix_phone' => $m->prefix_phone ?: ($existingProfile ? $existingProfile->prefix_phone : null),
                'phone'        => $m->phone ?: ($existingProfile ? $existingProfile->phone : null),
                'fullphone'    => $m->fullphone ?: ($existingProfile ? $existingProfile->fullphone : null),
                'image'        => $existingProfile ? $existingProfile->image : null,
                'job_title'    => $m->job_title ?: ($existingProfile ? $existingProfile->job_title : null),
                'company_id'   => $company->id,
                'users_id'     => $user->id,
            ];

            if ($existingProfile) {
                $existingProfile->update($profilePayload);
                $profile = $existingProfile;
            } else {
                $profile = ProfileModel::create($profilePayload);
            }

            // // ===== 4) MAILCHIMP INTEGRATION =====
            // NewsletterFacade::subscribeOrUpdate($email, [
            //     'FNAME'    => $user->name,
            //     'MERGE3'   => $company->address,
            //     'PHONE'    => $profile->phone,
            //     'MMERGE5'  => $company->company_name,
            //     'MMERGE6'  => $company->company_category,
            //     'MMERGE8'  => $profile->job_title,
            //     'MMERGE10' => now(),
            //     'MMERGE11' => $company->office_number,
            // ]);

            // // Tambah tag penanda sumber registrasi
            // $this->mcAddTags($user->email, [
            //     'Backend Membership'
            // ]);
            $m->update(['exported_at' => now()]);

            DB::commit();
            return back()->with('success', "Export OK → user:{$user->id}, company:{$company->id}, profile:{$profile->id}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Export gagal: ' . $e->getMessage());
        }
    }
    protected function mcAddTags(string $email, array $tags): void
    {
        try {
            $apiKey = config('newsletter.apiKey') ?: env('MAILCHIMP_APIKEY');
            $listId = config('newsletter.lists.subscribers.id') ?: env('MAILCHIMP_LIST_ID');
            if (!$apiKey || !$listId) return;

            $server = config('newsletter.server') ?: (explode('-', $apiKey)[1] ?? null);
            if (!$server) return;

            $subscriberHash = md5(strtolower($email));
            Http::withBasicAuth('anystring', $apiKey)->post(
                "https://{$server}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}/tags",
                ['tags' => collect($tags)->filter()->values()->map(fn($t) => ['name' => $t, 'status' => 'active'])->all()]
            );
        } catch (\Throwable $e) {
            Log::error('Mailchimp tagging failed: ' . $e->getMessage());
        }
    }
}
