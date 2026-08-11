<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\Company\CompanyModel;
use App\Models\MemberCompanyFollowUp;
use App\Models\MemberLeadFollowUp;
use App\Models\MemberModel;
use App\Models\Profiles\ProfileModel;
use App\Models\User;
use App\Models\UserEditLog;
use App\Models\VerificationLog;
use App\Services\Membership\MemberVerificationService;
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

        // Stats
        $countActiveMember = User::whereNotNull('isStatus')
            ->where('status_member', 'active')
            ->count();

        $countPendingMember = User::whereNotNull('isStatus')
            ->where(function ($q) {
                $q->whereNull('status_member')
                    ->orWhere('status_member', 'pending');
            })
            ->count();

        $countDeclined = User::whereNotNull('isStatus')
            ->where('status_member', 'declined')
            ->count();

        $countDeactivated = User::whereNotNull('isStatus')
            ->where('status_member', 'deactivated')
            ->count();

        $countNewThisMonth = User::whereNotNull('isStatus')
            ->whereBetween('created_at', [
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

        // New Member Validation (48h): rolling 30 hari terakhir (created_at) supaya
        // mencerminkan performa terkini, bukan tumpukan histori all-time.
        $validationWindowStart = Carbon::now()->subDays(30);

        $countValidatedWithin48h = User::whereNotNull('verified_at')
            ->where('created_at', '>=', $validationWindowStart)
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, verified_at) <= 48')
            ->count();

        $countValidatedAfter48h = User::whereNotNull('verified_at')
            ->where('created_at', '>=', $validationWindowStart)
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, verified_at) > 48')
            ->count();

        $countTwoStepVerified = User::whereNotNull('isStatus')
            ->where('two_step_verified', true)
            ->count();

        // Member Registrations trend — weekly (last 8 weeks) & monthly (last 6
        // months) — for quick management reporting ("berapa yang daftar minggu/
        // bulan ini"). Zero-filled so a quiet week/month shows as 0, not a gap.
        $weeklyRaw = User::whereNotNull('isStatus')
            ->where('created_at', '>=', Carbon::now()->subWeeks(7)->startOfWeek())
            ->selectRaw('YEARWEEK(created_at, 3) as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->pluck('total', 'period_key');

        $registrationsWeeklyLabels = [];
        $registrationsWeeklyCounts = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $key = (int) $weekStart->format('oW');
            $registrationsWeeklyLabels[] = $weekStart->format('d M');
            $registrationsWeeklyCounts[] = (int) ($weeklyRaw[$key] ?? 0);
        }

        // startOfMonth() BEFORE subMonths() — doing it after can land on a day
        // that doesn't exist in the target month (e.g. Jul 29 - 5 months = Feb 29,
        // invalid in a non-leap year) and Carbon silently overflows into March,
        // producing a duplicate month instead of February.
        $monthlyRaw = User::whereNotNull('isStatus')
            ->where('created_at', '>=', Carbon::now()->startOfMonth()->subMonths(5))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period_key, COUNT(*) as total")
            ->groupBy('period_key')
            ->pluck('total', 'period_key');

        $registrationsMonthlyLabels = [];
        $registrationsMonthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->startOfMonth()->subMonths($i);
            $key = $monthStart->format('Y-m');
            $registrationsMonthlyLabels[] = $monthStart->format('M Y');
            $registrationsMonthlyCounts[] = (int) ($monthlyRaw[$key] ?? 0);
        }

        // Yearly registration trend (Members Relation SOP §6 "Growth Tahunan")
        // — one bar per calendar year, from the earliest registration on record
        // (capped at the last 8 years so a very old dataset doesn't produce an
        // unreadable chart) through the current year.
        $earliestYear = (int) (User::whereNotNull('isStatus')->min('created_at')
            ? Carbon::parse(User::whereNotNull('isStatus')->min('created_at'))->year
            : Carbon::now()->year);
        $earliestYear = max($earliestYear, Carbon::now()->year - 7);

        $yearlyRaw = User::whereNotNull('isStatus')
            ->where('created_at', '>=', Carbon::createFromDate($earliestYear, 1, 1)->startOfDay())
            ->selectRaw('YEAR(created_at) as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->pluck('total', 'period_key');

        $registrationsYearlyLabels = [];
        $registrationsYearlyCounts = [];
        for ($year = $earliestYear; $year <= Carbon::now()->year; $year++) {
            $registrationsYearlyLabels[] = (string) $year;
            $registrationsYearlyCounts[] = (int) ($yearlyRaw[$year] ?? 0);
        }

        // Members by Source (Members Relation SOP §8) — Total Members always
        // computable; Total Leads/Win/Loss/Conversion Rate only once the leads
        // table exists (guarded below alongside the rest of that feature).
        $sourceBreakdown = collect($this->sourceColorMap())
            ->map(function ($meta, $key) {
                return ['label' => $meta['label'], 'members' => 0, 'leads' => 0, 'win' => 0, 'loss' => 0];
            });

        User::whereNotNull('isStatus')
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->each(function ($total, $rawSource) use (&$sourceBreakdown) {
                $key = $this->normalizeSourceKey($rawSource);
                $sourceBreakdown[$key]['members'] += (int) $total;
            });

        // Verification SLA + Leads (Members Relation SOP §2-4): both new tables
        // ship in this same change but their migrations haven't necessarily run
        // yet (never auto-run against this DB — see project convention). Guarded
        // so the Members page keeps working in that gap instead of 500ing, and
        // starts reporting real numbers the moment the migrations land, with no
        // further code change needed.
        $countNewMember = $countPendingMember;
        $countInVerification = 0;
        $countSlaGreen = $countSlaYellow = $countSlaRed = 0;
        $avgVerificationHours = null;
        $picPerformance = collect();
        $countLeads = $countLeadsPendingFollowUp = $countLeadsOverSla = 0;
        $countLeadsWin = $countLeadsLoss = 0;
        $leadConversionRate = null;

        if (Schema::hasTable('verification_logs')) {
            try {
                // New vs In Verification are computed display labels —
                // status_member itself stays untouched (pricing logic elsewhere
                // keys off it) — split by whether an open verification_logs row
                // exists for that pending user.
                $openVerificationUserIds = VerificationLog::open()->pluck('user_id')->unique();

                $countNewMember = User::whereNotNull('isStatus')
                    ->where(function ($q) {
                        $q->whereNull('status_member')->orWhere('status_member', 'pending');
                    })
                    ->whereNotIn('id', $openVerificationUserIds)
                    ->count();

                $countInVerification = User::whereNotNull('isStatus')
                    ->where(function ($q) {
                        $q->whereNull('status_member')->orWhere('status_member', 'pending');
                    })
                    ->whereIn('id', $openVerificationUserIds)
                    ->count();

                // SLA tiers for items currently open (started, not finished yet)
                $countSlaGreen = VerificationLog::open()->whereRaw('TIMESTAMPDIFF(HOUR, started_at, NOW()) < 24')->count();
                $countSlaYellow = VerificationLog::open()->whereRaw('TIMESTAMPDIFF(HOUR, started_at, NOW()) BETWEEN 24 AND 48')->count();
                $countSlaRed = VerificationLog::open()->whereRaw('TIMESTAMPDIFF(HOUR, started_at, NOW()) > 48')->count();

                // Average processing time + per-PIC breakdown, last 30 days (only
                // ~5 PICs today so a small table is more useful here than a chart)
                $avgVerificationMinutes = VerificationLog::whereNotNull('finished_at')
                    ->where('started_at', '>=', Carbon::now()->subDays(30))
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, finished_at)) as avg_minutes')
                    ->value('avg_minutes');
                $avgVerificationHours = $avgVerificationMinutes ? round($avgVerificationMinutes / 60, 1) : null;

                $picPerformance = VerificationLog::whereNotNull('finished_at')
                    ->where('started_at', '>=', Carbon::now()->subDays(30))
                    ->whereNotNull('finished_by_name')
                    ->selectRaw('finished_by_name, COUNT(*) as total, AVG(TIMESTAMPDIFF(MINUTE, started_at, finished_at)) as avg_minutes')
                    ->groupBy('finished_by_name')
                    ->orderByDesc('total')
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('index: verification SLA stats failed: ' . $e->getMessage());
            }
        }

        if (Schema::hasTable('member_lead_follow_ups')) {
            try {
                $countLeads = MemberLeadFollowUp::count();
                $countLeadsPendingFollowUp = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_PENDING)->count();
                $countLeadsOverSla = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_PENDING)
                    ->whereNotNull('deadline_at')
                    ->where('deadline_at', '<', now())
                    ->count();

                // Lead Performance (SOP §7): Conversion Rate = Win ÷ Total Lead,
                // per the SOP's own definition (denominator includes pending, not
                // just resolved leads).
                $countLeadsWin = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_WIN)->count();
                $countLeadsLoss = MemberLeadFollowUp::where('result', MemberLeadFollowUp::RESULT_LOSS)->count();
                $leadConversionRate = $countLeads > 0 ? round($countLeadsWin / $countLeads * 100, 1) : null;

                // Members by Source (SOP §8) — fold the lead/win/loss counts
                // into the member-count breakdown built above.
                MemberLeadFollowUp::join('users', 'users.id', 'member_lead_follow_ups.user_id')
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
                        $sourceBreakdown[$key]['leads'] += (int) $row->total;
                        $sourceBreakdown[$key]['win']   += (int) $row->win_count;
                        $sourceBreakdown[$key]['loss']  += (int) $row->loss_count;
                    });
            } catch (\Throwable $e) {
                Log::warning('index: lead stats failed: ' . $e->getMessage());
            }
        }

        // Finalize the source breakdown: compute conversion rate per bucket,
        // drop buckets with no members at all, sort by member count desc.
        $sourceBreakdown = $sourceBreakdown
            ->map(function ($row) {
                $row['conversion_rate'] = $row['leads'] > 0 ? round($row['win'] / $row['leads'] * 100, 1) : null;
                return $row;
            })
            ->filter(function ($row) {
                return $row['members'] > 0;
            })
            ->sortByDesc('members');

        return view('admin.users.index', [
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
            'countValidatedWithin48h'    => $countValidatedWithin48h,
            'countValidatedAfter48h'     => $countValidatedAfter48h,
            'registrationsWeeklyLabels'  => $registrationsWeeklyLabels,
            'registrationsWeeklyCounts'  => $registrationsWeeklyCounts,
            'registrationsMonthlyLabels' => $registrationsMonthlyLabels,
            'registrationsMonthlyCounts' => $registrationsMonthlyCounts,
            'registrationsYearlyLabels'  => $registrationsYearlyLabels,
            'registrationsYearlyCounts'  => $registrationsYearlyCounts,
            'sourceBreakdown'     => $sourceBreakdown,
            'countNewMember'      => $countNewMember,
            'countInVerification' => $countInVerification,
            'countSlaGreen'       => $countSlaGreen,
            'countSlaYellow'      => $countSlaYellow,
            'countSlaRed'         => $countSlaRed,
            'avgVerificationHours' => $avgVerificationHours,
            'picPerformance'      => $picPerformance,
            'countLeads'                => $countLeads,
            'countLeadsPendingFollowUp' => $countLeadsPendingFollowUp,
            'countLeadsOverSla'         => $countLeadsOverSla,
            'countLeadsWin'             => $countLeadsWin,
            'countLeadsLoss'            => $countLeadsLoss,
            'leadConversionRate'        => $leadConversionRate,
            'companyNames'       => CompanyModel::whereNotNull('company_name')
                ->whereRaw("TRIM(company_name) <> ''")
                ->distinct()
                ->orderBy('company_name')
                ->pluck('company_name'),
        ]);
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
        $statusMember = $request->status_member; // 'active' | 'pending' | ''

        $query = User::leftJoin('profiles', 'profiles.users_id', 'users.id')
            ->leftJoin('company', 'company.id', 'profiles.company_id')
            ->whereNotNull('users.isStatus');
        if ($filter == 'this_month') {
            $query->whereBetween('users.created_at', [
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

        if ($dateFrom) $query->whereDate('users.created_at', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('users.created_at', '<=', $dateTo);
        if ($month)    $query->whereMonth('users.created_at', $month);
        if ($year)     $query->whereYear('users.created_at', $year);

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
                'users.created_at as user_created_at',
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
            1 => 'users.created_at',
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
                'users.created_at as user_created_at',
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

        $followUpMap = MemberCompanyFollowUp::where('status', MemberCompanyFollowUp::STATUS_NEEDS_FOLLOW_UP)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

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

        $data = [];
        foreach ($rows as $i => $post) {
            $cells = $this->renderMemberRowCells($post, $selfEditMap, $followUpMap, $openVerificationIds);
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
     * Render satu baris tabel Members jadi array cell (index 0..15, cocok dengan
     * urutan <th> di _table_members.blade.php) + metadata baris (DT_RowId/DT_RowAttr)
     * untuk response DataTables server-side.
     */
    private function renderMemberRowCells($post, array $selfEditMap, $followUpMap, $openVerificationIds = null): array
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

        $registerRaw = $post->user_created_at ?? $post->created_at;
        $cellRegister = '<span class="text-nowrap">' . e(date('d M Y', strtotime($registerRaw)))
            . '<br><small class="text-muted">' . e(date('H:i', strtotime($registerRaw))) . '</small></span>';

        $cellSource = '<span class="text-nowrap"><span class="badge mini-badge" style="background-color:'
            . e($sourceStyle['color']) . ';color:#fff;" title="' . e($sourceLabel) . '" data-toggle="tooltip">'
            . '<i class="' . e($sourceStyle['icon']) . '"></i></span></span>';

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

        $cellJobTitle = '<span class="cell-truncate" title="' . e($post->job_title) . '">' . e($post->job_title) . '</span>';
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

        $cellPasswordActions = view('admin.users.partials._row.password_actions', [
            'post' => $post, 'followUpMap' => $followUpMap,
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
            15 => $cellPasswordActions,
            'DT_RowId'   => 'row_' . $post->user_id,
            'DT_RowAttr' => ['style' => $rowBg],
        ];
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
     * Registration channel buckets — shared between the per-row Source badge
     * (renderMemberRowCells) and the "Members by Source" breakdown stat, so
     * a row's badge and the aggregate table it feeds always agree.
     */
    private function sourceColorMap(): array
    {
        return [
            'website'   => ['label' => 'Website', 'color' => '#4e73df', 'icon' => 'fas fa-globe'],
            'apps'      => ['label' => 'Apps', 'color' => '#1cc88a', 'icon' => 'fas fa-mobile-alt'],
            'scanner'   => ['label' => 'Scanner', 'color' => '#858796', 'icon' => 'fas fa-qrcode'],
            'linkedin'  => ['label' => 'LinkedIn', 'color' => '#0077b5', 'icon' => 'fab fa-linkedin-in'],
            'instagram' => ['label' => 'Instagram', 'color' => '#e1306c', 'icon' => 'fab fa-instagram'],
            'event'     => ['label' => 'Event', 'color' => '#f6a92f', 'icon' => 'fas fa-calendar-alt'],
            'other'     => ['label' => 'Other', 'color' => '#6f42c1', 'icon' => 'fas fa-ellipsis-h'],
        ];
    }

    /**
     * Raw `source` values are messy free text ('website' vs 'Linkedin' vs
     * 'Event Mining Balikpapan' vs 'Check-in Scanner', written inconsistently
     * by web/apps/scanner/admin-import entry points) — collapse to one of the
     * fixed buckets above. Any "event*"-prefixed value folds into 'event';
     * anything else unrecognized (including empty) folds into 'other'.
     */
    private function normalizeSourceKey($rawSource): string
    {
        $key = strtolower(trim((string) $rawSource));
        if ($key === '') {
            return 'other';
        }

        $map = $this->sourceColorMap();
        if (!isset($map[$key]) && strpos($key, 'event') === 0) {
            $key = 'event';
        }

        return isset($map[$key]) ? $key : 'other';
    }

    public function verifyMember(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Modal verify juga boleh mengoreksi email — simpan yang lama supaya
        // contact Mailchimp-nya bisa ikut dipindahkan (no-op kalau member ini
        // memang belum pernah masuk audience).
        $previousEmail = strtolower(trim($user->email ?? ''));

        if ($request->filled('name') || $request->filled('email') || $request->filled('job_title') || $request->filled('phone')) {
            if ($request->filled('name')) {
                $user->name = trim($request->input('name'));
            }
            if ($request->filled('email')) {
                $user->email = trim($request->input('email'));
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

        // Explore Marketing → auto-create a lead follow-up task, SLA 2x24h from
        // now. firstOrCreate on ['user_id','result'=>pending] won't duplicate an
        // already-open lead, but starts a fresh one if a prior lead for this
        // member was already resolved (win/loss). Never let this block the
        // actual verify — degrades quietly if the migration hasn't landed yet.
        if ($company && $this->isLeadExplore($company->explore ?? null) && Schema::hasTable('member_lead_follow_ups')) {
            try {
                $leadAdmin = auth()->user();
                MemberLeadFollowUp::firstOrCreate(
                    ['user_id' => $user->id, 'result' => MemberLeadFollowUp::RESULT_PENDING],
                    [
                        'deadline_at'     => $verifiedAt->copy()->addHours(48),
                        'created_by_id'   => auth()->id(),
                        'created_by_name' => $leadAdmin ? $leadAdmin->name : 'Staff',
                    ]
                );
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
                }
            } catch (\Throwable $e) {
                Log::warning('verifyMember: Mailchimp import failed for user ' . $id . ': ' . $e->getMessage());
            }

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

        return response()->json([
            'success' => true,
            'message' => 'Member verified dan data telah diimport ke Mailchimp.',
        ]);
    }

    public function declineMember(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->status_member = 'declined';
        $user->save();

        $this->finishVerificationLog($user, VerificationLog::RESULT_DECLINED);

        $email = strtolower(trim($user->email ?? ''));

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            'message' => 'Membership application declined dan email notifikasi telah dikirim.',
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
        // unsubscribe dari audience Mailchimp. Gagal/tidak ketemu di Mailchimp
        // tidak boleh membatalkan deactivate-nya, cukup dilaporkan di message.
        $unsubscribed = app(MemberVerificationService::class)->unsubscribeFromMailchimp($user);

        return response()->json([
            'success' => true,
            'message' => $user->name . ' berhasil di-deactivate'
                . ($unsubscribed ? ' & di-unsubscribe dari Mailchimp.' : '. (Mailchimp: tidak ada kontak yang di-unsubscribe.)'),
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

        $watchUser    = ['name', 'email', 'status_member', 'tier'];
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

        if (empty($changes)) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan.']);
        }

        $user->name          = trim((string) $request->name);
        $user->email         = trim((string) $request->email);
        $user->status_member = $nextStatusMember;
        $user->tier          = $nextTier;
        $user->save();

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
        if (($hasCompanyChanges || $company->exists) && (int) ($company->id ?? 0) > 0) {
            $profile->company_id = $company->id;
        }
        $profile->users_id  = $user->id;
        $profile->save();

        $adminUser = auth()->user();
        DB::table('user_edit_logs')->insert([
            'user_id'    => $user->id,
            'admin_id'   => auth()->id(),
            'admin_name' => $adminUser ? $adminUser->name : null,
            'changes'    => json_encode($changes),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Admin edited user', [
            'admin'   => $adminUser ? $adminUser->name : null,
            'user_id' => $user->id,
            'email'   => $user->email,
            'changes' => $changes,
        ]);

        // Email diganti dari CMS = contact-nya di Mailchimp ikut dipindahkan,
        // supaya alamat lama berhenti menerima campaign dan alamat baru yang
        // menerimanya. Dijalankan paling akhir: semua penulisan DB sudah
        // selesai, jadi Mailchimp lambat/error tidak menggagalkan edit-nya.
        $message = 'Data user berhasil diperbarui.';
        if (isset($changes['email'])) {
            $mailchimpResult = app(MemberVerificationService::class)
                ->changeMailchimpEmail($user, (string) $changes['email']['old']);

            if ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_RENAMED) {
                $message .= ' Email di Mailchimp ikut dipindahkan.';
            } elseif ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_SWAPPED) {
                $message .= ' Mailchimp: email lama di-unsubscribe, email baru di-subscribe.';
            } elseif ($mailchimpResult === MemberVerificationService::MAILCHIMP_EMAIL_FAILED) {
                $message .= ' (Mailchimp: gagal memindahkan email — perlu dicek manual.)';
            }
        }

        return response()->json(['success' => true, 'message' => $message, 'changes' => $changes]);
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
