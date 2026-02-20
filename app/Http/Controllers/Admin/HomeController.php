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
        $event      = $this->eventSectionData();
        $news       = $this->newsSectionData();
        return view('admin.index', array_merge($kpi, $membership, $event, $news));
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



    private function eventSectionData(): array
    {
        // ===== Registration Trend (Last 6 months, include this month) =====
        $start = now()->copy()->startOfMonth()->subMonths(5);
        $end   = now()->copy()->endOfMonth();

        $raw = DB::table('users_event')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $eventRegLabels = [];
        $eventRegData   = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $ym = $cursor->format('Y-m');
            $eventRegLabels[] = $cursor->format('M');
            $eventRegData[]   = (int) ($raw[$ym] ?? 0);
            $cursor->addMonth();
        }

        // ===== Event Status Donut (based on date, only publish) =====
        $today = now()->toDateString();

        $eventUpcoming = DB::table('events')
            ->where('status', 'publish')
            ->whereDate('start_date', '>', $today)
            ->count();

        $eventOngoing = DB::table('events')
            ->where('status', 'publish')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $eventCompleted = DB::table('events')
            ->where('status', 'publish')
            ->whereDate('end_date', '<', $today)
            ->count();

        // ===== Top Events by Attendance (attendance = count users_event per event) =====
        $topEvents = DB::table('users_event as ue')
            ->join('events as e', 'e.id', '=', 'ue.events_id')
            ->selectRaw('e.id, e.name, e.start_date, e.end_date, e.status, COUNT(*) as attendees')
            ->where('e.status', 'publish')
            ->groupBy('e.id', 'e.name', 'e.start_date', 'e.end_date', 'e.status')
            ->orderByDesc('attendees')
            ->limit(5)
            ->get()
            ->map(function ($row) use ($today) {
                // status badge berdasarkan tanggal
                $start = $row->start_date;
                $end   = $row->end_date;

                if ($start > $today) {
                    $row->status_label = 'Upcoming';
                    $row->badge_class  = 'badge-warning';
                } elseif ($start <= $today && $end >= $today) {
                    $row->status_label = 'Ongoing';
                    $row->badge_class  = 'badge-primary';
                } else {
                    $row->status_label = 'Completed';
                    $row->badge_class  = 'badge-success';
                }

                return $row;
            });

        return compact(
            'eventRegLabels',
            'eventRegData',
            'eventUpcoming',
            'eventOngoing',
            'eventCompleted',
            'topEvents'
        );
    }

    private function newsSectionData(): array
    {
        // ===== News Status donut =====
        $newsPublished = DB::table('news')->where('status', 'publish')->count();
        $newsDraft     = DB::table('news')->where('status', 'draft')->count();

        // kalau kamu ga punya archived, set 0 saja (atau tambahkan status lain)
        $newsArchived  = DB::table('news')->where('status', 'archived')->count();

        // ===== News Views Trend (Last 7 Days) =====
        // Asumsi: views = akumulatif per news, jadi kita sum views artikel yang publish di tanggal tsb.
        // Pakai date_news kalau ada, fallback created_at.
        $start = now()->copy()->startOfDay()->subDays(6);
        $end   = now()->copy()->endOfDay();

        $raw = DB::table('news')
            ->selectRaw("
            DATE(COALESCE(date_news, created_at)) as d,
            SUM(COALESCE(views,0)) as total_views
        ")
            ->where('status', 'publish')
            ->whereBetween(DB::raw("DATE(COALESCE(date_news, created_at))"), [
                $start->toDateString(),
                $end->toDateString()
            ])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total_views', 'd'); // ['2026-02-14' => 1200, ...]

        $newsTrendLabels = [];
        $newsTrendData   = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $d = $cursor->toDateString();
            $newsTrendLabels[] = $cursor->format('D'); // Mon Tue Wed ...
            $newsTrendData[]   = (int) ($raw[$d] ?? 0);
            $cursor->addDay();
        }

        // ===== Top Viewed News =====
        // Join kategori jika ada table news_category (umum). Kalau tidak ada, category tampil '-'.
        // Dari screenshot ada news_category_id, jadi aku join ke news_category.
        $topNews = DB::table('news as n')
            ->leftJoin('news_category as c', 'c.id', '=', 'n.news_category_id')
            ->select([
                'n.id',
                'n.title',
                'n.status',
                'n.views',
                'n.date_news',
                'n.created_at',
                // DB::raw('c.name as category_name'),
            ])
            ->orderByDesc(DB::raw('COALESCE(n.views,0)'))
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $row->views = (int) ($row->views ?? 0);

                // published time (pakai date_news kalau ada)
                $publishedAt = $row->date_news ?? $row->created_at;

                $row->published_human = $publishedAt
                    ? Carbon::parse($publishedAt)->diffForHumans()
                    : '-';

                if ($row->status === 'publish') {
                    $row->badge_class = 'badge-success';
                    $row->status_label = 'Published';
                } elseif ($row->status === 'draft') {
                    $row->badge_class = 'badge-warning';
                    $row->status_label = 'Draft';
                } else {
                    $row->badge_class = 'badge-secondary';
                    $row->status_label = ucfirst($row->status ?? 'Unknown');
                }

                return $row;
            });

        return compact(
            'newsPublished',
            'newsDraft',
            'newsArchived',
            'newsTrendLabels',
            'newsTrendData',
            'topNews'
        );
    }
}
