<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $kpi = $this->kpiData();
        $membership = $this->membershipSectionData();
        return view('admin.index', array_merge($kpi, $membership));
    }

    /**
     * SECTION KPI
     */
    private function kpiData(): array
    {
        // ===== 1) New Members (this month) + Growth =====
        $newMembersThisMonth = DB::table('users')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $newMembersLastMonth = DB::table('users')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        $growthPercent = 0;
        if ($newMembersLastMonth > 0) {
            $growthPercent = (($newMembersThisMonth - $newMembersLastMonth) / $newMembersLastMonth) * 100;
        } else {
            $growthPercent = $newMembersThisMonth > 0 ? 100 : 0;
        }
        $growthPercent = round($growthPercent);

        // ===== 2) Active Members =====
        $oneYearAgo = now()->subYear();

        $activeMembers = DB::table('users')
            ->where(function ($q) use ($oneYearAgo) {
                $q->where('updated_at', '>=', $oneYearAgo)
                    ->orWhere('verify_email', 'verified')
                    ->orWhere('verify_phone', 'verified')
                    ->orWhereNotNull('verify_email')
                    ->orWhereNotNull('verify_phone');
            })
            ->count();

        // ===== 3) Expiring (30 Days) =====
        // yang akan melewati batas "inactive 1 tahun" dalam 30 hari ke depan
        $expiring30Days = DB::table('users')
            ->whereBetween('updated_at', [now()->subYear(), now()->subYear()->addDays(30)])
            ->count();

        $inactiveOver1Year = DB::table('users')
            ->where('updated_at', '<', $oneYearAgo)
            ->count();

        // ===== 4) Total Events =====
        $totalEvents = DB::table('events')->count();

        // ===== 5) Upcoming Events =====
        // events.status = publish, start_date >= today
        $upcomingEvents = DB::table('events')
            ->where('status', 'publish')
            ->whereDate('start_date', '>=', now()->toDateString())
            ->count();

        // ===== 6) Event Registrations =====
        // source: users_event + payment.status_registration
        // status list: Paid Off, Waiting, Expired, Reject, Cancel, NULL
        // Default aku hitung yang "valid registration" = Paid Off + Waiting
        // (kalau kamu mau Paid Off saja, tinggal hapus 'Waiting')
        $eventRegistrations = DB::table('users_event as ue')
            ->leftJoin('payment as p', 'p.id', '=', 'ue.payment_id')
            ->whereIn('p.status_registration', ['Paid Off', 'Waiting'])
            ->count();

        // ===== 7) Published News =====
        $publishedNews = DB::table('news')
            ->where('status', 'publish')
            ->count();

        // ===== 8) News Views (This month) =====
        // views ada di table news (field: views)
        // filter bulan ini pakai date_news (fallback ke created_at kalau date_news null)
        $newsViewsThisMonth = DB::table('news')
            ->where('status', 'publish')
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->whereNotNull('date_news')
                        ->whereYear('date_news', now()->year)
                        ->whereMonth('date_news', now()->month);
                })
                    ->orWhere(function ($qq) {
                        $qq->whereNull('date_news')
                            ->whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month);
                    });
            })
            ->sum(DB::raw('COALESCE(views,0)'));

        return compact(
            'newMembersThisMonth',
            'growthPercent',
            'activeMembers',
            'expiring30Days',
            'inactiveOver1Year',
            'totalEvents',
            'upcomingEvents',
            'eventRegistrations',
            'publishedNews',
            'newsViewsThisMonth'
        );
    }

    private function membershipSectionData(): array
    {
        $totalUsers = DB::table('users')->count();

        // ===== Active / Inactive (strict verified OR updated within 1 year) =====
        $oneYearAgo = now()->subYear();

        $activeMembers = DB::table('users')
            ->where(function ($q) use ($oneYearAgo) {
                $q->where('updated_at', '>=', $oneYearAgo)
                    ->orWhere('verify_email', 'verified')
                    ->orWhere('verify_phone', 'verified');
            })
            ->count();

        $inactiveMembers = max(0, $totalUsers - $activeMembers);

        $activePercent = $totalUsers > 0 ? round(($activeMembers / $totalUsers) * 100) : 0;

        // ===== Joined Event % (distinct users yang pernah ada di users_event) =====
        $joinedEventUsers = DB::table('users_event')->distinct('users_id')->count('users_id');
        $joinedEventPercent = $totalUsers > 0 ? round(($joinedEventUsers / $totalUsers) * 100) : 0;

        // ===== Membership Growth (Last 6 Months incl this month) =====
        $start = now()->copy()->startOfMonth()->subMonths(5);
        $end   = now()->copy()->endOfMonth();

        $raw = DB::table('users')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym'); // ['2026-01' => 12, ...]

        $membershipGrowthLabels = [];
        $membershipGrowthData   = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $ym = $cursor->format('Y-m');
            $membershipGrowthLabels[] = $cursor->format('M'); // Aug, Sep, ...
            $membershipGrowthData[]   = (int) ($raw[$ym] ?? 0);
            $cursor->addMonth();
        }

        // ===== Quick Insights (best/lowest/avg from last 6 months data) =====
        $bestMonthIndex = null;
        $lowestMonthIndex = null;

        if (count($membershipGrowthData) > 0) {
            $maxVal = max($membershipGrowthData);
            $minVal = min($membershipGrowthData);
            $bestMonthIndex = array_search($maxVal, $membershipGrowthData, true);
            $lowestMonthIndex = array_search($minVal, $membershipGrowthData, true);
        }

        $bestMonthLabel   = $bestMonthIndex !== null ? $membershipGrowthLabels[$bestMonthIndex] : '-';
        $bestMonthValue   = $bestMonthIndex !== null ? $membershipGrowthData[$bestMonthIndex] : 0;

        $lowestMonthLabel = $lowestMonthIndex !== null ? $membershipGrowthLabels[$lowestMonthIndex] : '-';
        $lowestMonthValue = $lowestMonthIndex !== null ? $membershipGrowthData[$lowestMonthIndex] : 0;

        $avgPerMonth = count($membershipGrowthData) > 0
            ? (int) round(array_sum($membershipGrowthData) / count($membershipGrowthData))
            : 0;

        // ===== Avg News / Member (biar angka realistis, pakai avg views/month per member) =====
        // - ambil total views news publish bulan ini, lalu / totalUsers
        $newsViewsThisMonth = DB::table('news')
            ->where('status', 'publish')
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->whereNotNull('date_news')
                        ->whereYear('date_news', now()->year)
                        ->whereMonth('date_news', now()->month);
                })->orWhere(function ($qq) {
                    $qq->whereNull('date_news')
                        ->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', now()->month);
                });
            })
            ->sum(DB::raw('COALESCE(views,0)'));

        $avgNewsPerMember = $totalUsers > 0 ? round($newsViewsThisMonth / $totalUsers, 1) : 0;

        // ===== Inactive Members Table (30+ days by updated_at) =====
        $inactiveRows = DB::table('users as u')
            ->leftJoin('profiles as p', 'p.users_id', '=', 'u.id')
            ->leftJoin('company as c', 'c.id', '=', 'p.company_id')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.updated_at',
                'u.created_at',
                'c.company_name',
            ])
            ->where('u.updated_at', '<=', now()->subDays(30))
            ->orderBy('u.updated_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $days = Carbon::parse($row->updated_at)->diffInDays(now());
                $row->days_inactive = $days;

                if ($days >= 60) {
                    $row->badge_class = 'badge-danger';
                    $row->badge_text  = 'Dormant';
                } else {
                    $row->badge_class = 'badge-warning';
                    $row->badge_text  = 'Inactive';
                }

                return $row;
            });

        return compact(
            'membershipGrowthLabels',
            'membershipGrowthData',
            'bestMonthLabel',
            'bestMonthValue',
            'lowestMonthLabel',
            'lowestMonthValue',
            'avgPerMonth',
            'activeMembers',
            'inactiveMembers',
            'activePercent',
            'joinedEventPercent',
            'avgNewsPerMember',
            'inactiveRows'
        );
    }
}
