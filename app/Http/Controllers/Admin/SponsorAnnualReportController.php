<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SponsorAnnualReportExport;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\Sponsors\SponsorRenewalForm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Halaman Sponsors Annual Report: summary cards, statistik bulanan,
 * contract expiry forecast (dengan follow-up status), dan tab daftar sponsor.
 */
class SponsorAnnualReportController extends Controller
{
    public function index(Request $request)
    {
        $year        = (int) $request->get('year', now()->year);
        $package     = $request->get('package');
        $search      = $request->get('search');
        $renewalType = $request->get('renewal_type');

        $renewedSponsors = $this->renewedSponsors($year, $package, $renewalType, $search);
        if ($renewalType === 'not_renewed') {
            $renewedSponsors = collect();
        }

        $expiryForecast = $this->expiryForecast($year);
        $this->applyFollowUpStatus($expiryForecast);
        $this->attachFollowupLog($expiryForecast, $year);
        $this->attachRenewalStep($expiryForecast);

        [$monthlyStats, $monthlyDetails] = $this->monthlyActivity($year);

        $pendingRenewals = $this->pendingRenewals($expiryForecast, $package, $search, $year);

        return view('admin.sponsor.annual-report', [
            'year'               => $year,
            'package'            => $package,
            'search'             => $search,
            'renewalType'        => $renewalType,
            'availableYears'     => $this->availableYears(),
            'renewedSponsors'    => $renewedSponsors,
            'notRenewedSponsors' => $this->notRenewedSponsors($year, $package, $search),
            'monthlyStats'       => $monthlyStats,
            'monthlyDetails'     => $monthlyDetails,
            'packageBreakdown'   => $this->packageBreakdown($year),
            'expiryForecast'     => $expiryForecast,
            'peakExpiryMonth'    => $this->peakExpiryMonth($expiryForecast),
            'pendingRenewals'    => $pendingRenewals,
            'headcount'          => $this->sponsorHeadcount($year),
            'dashboardCards'     => $this->dashboardCards($year, $pendingRenewals),
            'chartData'          => $this->chartData($year),
        ] + $this->summaryCounts($year));
    }

    public function download(Request $request)
    {
        $year     = (int) $request->get('year', now()->year);
        $filename = "DMC-Sponsors-Report-{$year}.xlsx";

        return Excel::download(new SponsorAnnualReportExport($year), $filename);
    }

    /**
     * Headcount sponsor per PERUSAHAAN (bukan per kontrak/transaksi) — menjawab
     * "tahun ini jumlah sponsor kita berapa, achieved atau tidak vs tahun lalu".
     *
     * Hanya dihitung untuk tahun berjalan karena memakai snapshot status publish
     * hari ini; untuk tahun lain section-nya disembunyikan (return null).
     *
     * "Tahun lalu" = sponsor yang punya kontrak aktif selama tahun sebelumnya DAN
     * masih publish sekarang atau resmi dicatat not_renewed — sponsor lama yang
     * nonaktif diam-diam tanpa catatan tidak ikut dihitung, supaya angkanya cocok
     * dengan hitungan manajemen.
     */
    private function sponsorHeadcount(int $year): ?array
    {
        if ($year !== (int) now()->year) {
            return null;
        }

        $prevYear = $year - 1;

        $activeLastYear = SponsorRenewal::where('renewal_status', 'renewed')
            ->whereNotNull('contract_start')
            ->whereNotNull('contract_end')
            ->where('contract_start', '<=', $prevYear . '-12')
            ->where('contract_end', '>=', $prevYear . '-01')
            ->pluck('sponsor_id')
            ->unique();

        $currentIds = Sponsor::where('status', 'publish')->pluck('id');
        $lostIds    = SponsorRenewal::where('renewal_year', $year)
            ->where('renewal_status', 'not_renewed')
            ->pluck('sponsor_id')
            ->unique();

        // Sponsor lama bisa lanjut lewat BARIS sponsor baru (contoh: Berlian Cranserco
        // Silver ID 27 upgrade ke Gold sebagai ID 80, baris lama ditinggal draft).
        // Penentu new vs lanjutan adalah renewal_type-nya, bukan identitas baris:
        // baris baru ber-record renewal/upgrade = kelanjutan sponsor tahun lalu.
        $renewingIds = SponsorRenewal::where('renewal_year', $year)
            ->where('renewal_status', 'renewed')
            ->whereIn('renewal_type', ['renewal', 'upgrade'])
            ->pluck('sponsor_id')
            ->unique();

        $newRows        = $currentIds->diff($activeLastYear);
        $continuedCount = $newRows->intersect($renewingIds)->count();

        $lastYearCount = $activeLastYear->intersect($currentIds->merge($lostIds))->count() + $continuedCount;
        $lostCount     = $activeLastYear->intersect($lostIds)->count();
        $newCount      = $newRows->count() - $continuedCount;
        $currentCount  = $currentIds->count();

        return [
            'prevYear'      => $prevYear,
            'lastYearCount' => $lastYearCount,
            'lostCount'     => $lostCount,
            'newCount'      => $newCount,
            'currentCount'  => $currentCount,
            'netChange'     => $currentCount - $lastYearCount,
        ];
    }

    private function availableYears(): Collection
    {
        $years = SponsorRenewal::select('renewal_year')
            ->distinct()
            ->orderByDesc('renewal_year')
            ->pluck('renewal_year');

        return $years->isEmpty() ? collect([now()->year]) : $years;
    }

    /**
     * Angka untuk summary cards: renewal / upgrade / new / not renewed.
     */
    private function summaryCounts(int $year): array
    {
        $base = fn(int $y) => SponsorRenewal::where('renewal_year', $y);

        return [
            'renewedCount'    => $base($year)->where('renewal_status', 'renewed')->where('renewal_type', 'renewal')->count(),
            'upgradeCount'    => $base($year)->where('renewal_status', 'renewed')->where('renewal_type', 'upgrade')->count(),
            'newCount'        => $base($year)->where('renewal_status', 'renewed')->whereIn('renewal_type', ['new', 'new_member'])->count(),
            'notRenewedCount' => $base($year)->where('renewal_status', 'not_renewed')->count(),
            // Perbandingan year-over-year (final tahun lalu) untuk kartu New Sponsor & Lost.
            'lastYearNewCount'        => $base($year - 1)->where('renewal_status', 'renewed')->whereIn('renewal_type', ['new', 'new_member'])->count(),
            'lastYearNotRenewedCount' => $base($year - 1)->where('renewal_status', 'not_renewed')->count(),
        ];
    }

    /**
     * Breakdown platinum/gold/silver per kategori summary.
     * Kategorinya harus sama persis dengan definisi di summaryCounts().
     */
    private function packageBreakdown(int $year): array
    {
        $breakdown = [
            'renewal'     => ['platinum' => 0, 'gold' => 0, 'silver' => 0],
            'upgrade'     => ['platinum' => 0, 'gold' => 0, 'silver' => 0],
            'new'         => ['platinum' => 0, 'gold' => 0, 'silver' => 0],
            'not_renewed' => ['platinum' => 0, 'gold' => 0, 'silver' => 0],
        ];

        SponsorRenewal::where('renewal_year', $year)
            ->selectRaw("CASE
                    WHEN renewal_status = 'not_renewed' THEN 'not_renewed'
                    WHEN renewal_status = 'renewed' AND renewal_type = 'upgrade' THEN 'upgrade'
                    WHEN renewal_status = 'renewed' AND renewal_type IN ('new', 'new_member') THEN 'new'
                    WHEN renewal_status = 'renewed' AND renewal_type = 'renewal' THEN 'renewal'
                END as category, package, COUNT(*) as total")
            ->groupBy('category', 'package')
            ->get()
            ->each(function ($row) use (&$breakdown) {
                if (isset($breakdown[$row->category][$row->package])) {
                    $breakdown[$row->category][$row->package] += (int) $row->total;
                }
            });

        return $breakdown;
    }

    /**
     * Distribusi aktivitas per bulan (1-12, Januari-Desember) berdasarkan contract_start.
     * Mengembalikan [counts, details]: counts untuk bar chart, details berisi record
     * per bulan+kategori untuk daftar sponsor yang muncul saat bar-nya diklik.
     */
    private function monthlyActivity(int $year): array
    {
        $stats   = array_fill(1, 12, ['renewal' => 0, 'upgrade' => 0, 'new' => 0, 'not_renewed' => 0]);
        $details = array_fill(1, 12, ['renewal' => [], 'upgrade' => [], 'new' => [], 'not_renewed' => []]);

        SponsorRenewal::with(['sponsor', 'sponsor.firstPic'])
            ->where('renewal_year', $year)
            ->get()
            ->each(function ($r) use (&$stats, &$details) {
                $date = $r->contract_start ?? ($r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d') : null);
                if (!$date) {
                    return;
                }
                $month = (int) Carbon::parse($date)->format('n');
                if ($r->renewal_status === 'renewed') {
                    if ($r->renewal_type === 'upgrade') {
                        $category = 'upgrade';
                    } elseif (in_array($r->renewal_type, ['new', 'new_member'])) {
                        $category = 'new';
                    } else {
                        $category = 'renewal';
                    }
                } else {
                    $category = 'not_renewed';
                }
                $stats[$month][$category]++;
                $details[$month][$category][] = $r;
            });

        return [$stats, $details];
    }

    private function renewedSponsors(int $year, ?string $package, ?string $renewalType, ?string $search): Collection
    {
        return SponsorRenewal::with(['sponsor', 'sponsor.firstPic'])
            ->where('renewal_year', $year)
            ->where('renewal_status', 'renewed')
            ->when($package, fn($q) => $q->where('package', $package))
            ->when($renewalType && $renewalType !== 'not_renewed', fn($q) => $q->where('renewal_type', $renewalType))
            ->when($search, fn($q) => $q->whereHas('sponsor', fn($sq) => $sq->where('name', 'like', "%{$search}%")))
            ->orderBy('contract_start')
            ->get();
    }

    private function notRenewedSponsors(int $year, ?string $package, ?string $search): Collection
    {
        return SponsorRenewal::with(['sponsor', 'sponsor.firstPic'])
            ->where('renewal_year', $year)
            ->where('renewal_status', 'not_renewed')
            ->when($package, fn($q) => $q->where('package', $package))
            ->when($search, fn($q) => $q->whereHas('sponsor', fn($sq) => $sq->where('name', 'like', "%{$search}%")))
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Kontrak yang habis di tahun terpilih, dikelompokkan per bulan (1-12).
     */
    private function expiryForecast(int $year): Collection
    {
        return SponsorRenewal::with(['sponsor', 'sponsor.firstPic'])
            ->where('renewal_status', 'renewed')
            ->where('contract_end', 'like', $year . '-%')
            ->whereNotNull('contract_end')
            ->orderBy('contract_end')
            ->get()
            ->groupBy(fn($r) => (int) substr($r->contract_end, 5, 2));
    }

    /**
     * Tandai tiap kontrak expiring dengan followup_status + followup_record:
     * - renewed/upgraded : ada record 'renewed' baru yang mulai setelah kontrak ini habis
     * - stopped          : ada record 'not_renewed' untuk tahun tsb / setelahnya
     * - pending          : belum ada kabar
     *
     * Untuk yang masih pending, pending_stage memberi penamaan urgensi
     * (istilah dari manajemen):
     * - pending  : kontrak sudah kelewatan (bulan habisnya sudah lewat)
     * - awaiting : habis di bulan berjalan
     * - upcoming : habis di bulan-bulan berikutnya
     */
    private function applyFollowUpStatus(Collection $expiryForecast): void
    {
        $sponsorIds = $expiryForecast->flatten()->pluck('sponsor_id')->unique();
        $records    = SponsorRenewal::whereIn('sponsor_id', $sponsorIds)
            ->get()
            ->groupBy('sponsor_id');
        $nowYm = now()->format('Y-m');

        $expiryForecast->each(function ($group) use ($records, $nowYm) {
            $group->each(function ($er) use ($records, $nowYm) {
                $sponsorRecords = $records->get($er->sponsor_id, collect());

                $next = $sponsorRecords
                    ->filter(fn($r) => $r->id !== $er->id
                        && $r->renewal_status === 'renewed'
                        && $r->contract_start
                        && $r->contract_start > $er->contract_end)
                    ->sortBy('contract_start')
                    ->first();

                $stopped = $sponsorRecords->first(fn($r) => $r->renewal_status === 'not_renewed'
                    && $r->renewal_year >= (int) substr($er->contract_end, 0, 4));

                if ($next) {
                    $er->followup_status = $next->renewal_type === 'upgrade' ? 'upgraded' : 'renewed';
                    $er->followup_record = $next;
                } elseif ($stopped) {
                    $er->followup_status = 'stopped';
                    $er->followup_record = $stopped;
                } else {
                    $er->followup_status = 'pending';
                    $er->followup_record = null;
                    $er->pending_stage   = $er->contract_end < $nowYm
                        ? 'pending'
                        : ($er->contract_end === $nowYm ? 'awaiting' : 'upcoming');
                }
            });
        });
    }

    /**
     * Tempelkan jejak follow-up log (SponsorFollowup) ke tiap kontrak expiring untuk
     * siklus renewal tahun ini, supaya kolom Follow-up Status bisa menampilkan alur:
     * Renewal Form Submitted → Follow Up 1/2/3 → (tanggal) | (nama PIC).
     */
    private function attachFollowupLog(Collection $expiryForecast, int $year): void
    {
        $sponsorIds = $expiryForecast->flatten()->pluck('sponsor_id')->unique();
        if ($sponsorIds->isEmpty()) {
            return;
        }

        $logs = SponsorFollowup::whereIn('sponsor_id', $sponsorIds)
            ->where('renewal_year', $year)
            ->ofType(SponsorFollowup::TYPE_RENEWAL)
            ->with('creator:id,name')
            ->orderBy('followed_up_at')
            ->orderBy('id')
            ->get()
            ->groupBy('sponsor_id');

        $expiryForecast->each(function ($group) use ($logs) {
            $group->each(function ($er) use ($logs) {
                $f = $logs->get($er->sponsor_id, collect());
                $er->followup_count  = $f->count();
                $er->first_followup  = $f->first();
                $er->last_followup   = $f->last();
                // Follow-up yang ditandai "confirmed renewal" oleh tim secretariat (kalau ada).
                $er->confirmed_followup = $f->where('confirmed_renewal', true)->last();
            });
        });
    }

    /**
     * Tahap proses renewal per kontrak expiring (renewal_step), dipakai kolom Status:
     * - Request Form  : belum ada renewal form untuk siklus berikutnya
     * - Finalize Form : renewal form sudah di-generate (atau kontrak sudah renewed,
     *                   invoice belum dikirim)
     * - Invoice Sent  : kontrak baru punya invoice_sent_date
     * - Paid          : kontrak baru punya paid_date
     * Kontrak yang stopped (Not Renew) tidak diberi tahap.
     */
    private function attachRenewalStep(Collection $expiryForecast): void
    {
        $sponsorIds = $expiryForecast->flatten()->pluck('sponsor_id')->unique();
        if ($sponsorIds->isEmpty()) {
            return;
        }

        $forms = SponsorRenewalForm::whereIn('sponsor_id', $sponsorIds)
            ->get(['id', 'sponsor_id', 'renewal_year', 'form_number'])
            ->keyBy(fn($f) => $f->sponsor_id . '-' . $f->renewal_year);

        // Jumlah follow-up invoice (Finance) per sponsor-tahun, untuk detail tahap Invoice Sent.
        $invoiceFollowups = SponsorFollowup::whereIn('sponsor_id', $sponsorIds)
            ->ofType(SponsorFollowup::TYPE_INVOICE)
            ->selectRaw('sponsor_id, renewal_year, COUNT(*) AS total')
            ->groupBy('sponsor_id', 'renewal_year')
            ->get()
            ->keyBy(fn($f) => $f->sponsor_id . '-' . $f->renewal_year);

        $expiryForecast->each(function ($group) use ($forms, $invoiceFollowups) {
            $group->each(function ($er) use ($forms, $invoiceFollowups) {
                $er->renewal_step = null;
                if (($er->followup_status ?? 'pending') === 'stopped' || !$er->contract_end) {
                    return;
                }

                $rec = $er->followup_record;
                if ($rec && $rec->paid_date) {
                    $er->renewal_step = ['label' => 'Paid', 'color' => '#47c363', 'icon' => 'fa-check-circle'];
                    return;
                }
                if ($rec && $rec->invoice_sent_date) {
                    $fu    = $invoiceFollowups->get($rec->sponsor_id . '-' . $rec->renewal_year);
                    $total = $fu ? (int) $fu->total : 0;
                    $er->renewal_step = [
                        'label'  => 'Invoice Sent',
                        'color'  => '#6777ef',
                        'icon'   => 'fa-paper-plane',
                        'detail' => $total > 0 ? $total . ' payment follow-up' . ($total > 1 ? 's' : '') : null,
                    ];
                    return;
                }

                // Renewal form siklus berikutnya: renewal_year = tahun kontrak baru mulai;
                // fallback ke tahun kontrak lama habis (form dibuat dengan tahun berjalan).
                $endYear   = (int) substr($er->contract_end, 0, 4);
                $nextYear  = (int) Carbon::createFromFormat('Y-m', $er->contract_end)->addMonth()->format('Y');
                $form      = $forms->get($er->sponsor_id . '-' . $nextYear) ?? $forms->get($er->sponsor_id . '-' . $endYear);

                $er->renewal_step = ($form || $rec)
                    ? ['label' => 'Finalize Form', 'color' => '#fd7e14', 'icon' => 'fa-file-signature', 'detail' => $form ? $form->form_number : null]
                    : ['label' => 'Request Form', 'color' => '#3abaf4', 'icon' => 'fa-file-alt'];
            });
        });
    }

    private function peakExpiryMonth(Collection $expiryForecast): ?int
    {
        return $expiryForecast->isNotEmpty()
            ? $expiryForecast->sortByDesc(fn($g) => $g->count())->keys()->first()
            : null;
    }

    /**
     * Pipeline follow-up: kontrak expiring yang masih berstatus pending,
     * dilengkapi jejak follow-up (Not Contacted = belum pernah dihubungi,
     * Prosit = sudah ada follow-up tercatat).
     */
    private function pendingRenewals(Collection $expiryForecast, ?string $package, ?string $search, int $year): Collection
    {
        $pending = $expiryForecast->flatten()
            // Sponsor yang kontraknya sudah dibilling untuk tahun berjalan (renewal_year
            // sama dengan tahun ini, mis. kontrak Jan–Des 2026 yang sudah confirm di
            // Januari) tidak dihitung pending lagi — 1 sponsor = 1 penagihan per tahun.
            // Kontraknya memang berakhir tahun ini, tapi renewal-nya untuk tahun depan,
            // jadi cukup tampil di 30-Day Priority Contracts, bukan di tab Pending.
            ->filter(fn($er) => $er->followup_status === 'pending' && (int) $er->renewal_year < $year)
            ->when($package, fn($c) => $c->where('package', $package))
            ->when($search, fn($c) => $c->filter(fn($er) => $er->sponsor && stripos($er->sponsor->name, $search) !== false))
            ->sortBy('contract_end')
            ->values();

        $followups = SponsorFollowup::whereIn('sponsor_id', $pending->pluck('sponsor_id')->unique())
            ->where('renewal_year', $year)
            ->ofType(SponsorFollowup::TYPE_RENEWAL)
            ->with('creator:id,name')
            ->orderBy('followed_up_at')
            ->orderBy('id')
            ->get()
            ->groupBy('sponsor_id');

        $pending->each(function ($p) use ($followups) {
            $f = $followups->get($p->sponsor_id, collect());
            $p->followup_count = $f->count();
            $p->last_followup  = $f->last();
            $p->confirmed_followup = $f->where('confirmed_renewal', true)->last();
        });

        return $pending;
    }

    private function chartData(int $year): array
    {
        $confirmed = SponsorRenewal::where('renewal_status', 'renewed')
            ->whereNotNull('contract_start')
            ->get();

        $buildMonthly = function ($rows) {
            $data = array_fill(1, 12, 0);
            foreach ($rows as $r) {
                $m = (int) substr($r->contract_start, 5, 2);
                $data[$m]++;
            }
            return array_values($data);
        };

        $buildMonthlyIdr = function ($rows) {
            $data = array_fill(1, 12, 0);
            foreach ($rows as $r) {
                $m = (int) substr($r->contract_start, 5, 2);
                $data[$m] += (int) $r->amount_idr;
            }
            return array_values($data);
        };

        $buildPackageMonthly = function ($rows) {
            $pkgs = ['platinum' => array_fill(1, 12, 0), 'gold' => array_fill(1, 12, 0), 'silver' => array_fill(1, 12, 0)];
            foreach ($rows as $r) {
                $m = (int) substr($r->contract_start, 5, 2);
                $pkg = strtolower($r->package);
                if (isset($pkgs[$pkg])) {
                    $pkgs[$pkg][$m]++;
                }
            }
            return array_map('array_values', $pkgs);
        };

        $years = $confirmed->pluck('renewal_year')->unique()->sort()->values()->toArray();

        $sponsorYoy = [];
        $packageYoy = [];
        $priceYoy = [];
        foreach ($years as $y) {
            $yRows = $confirmed->where('renewal_year', $y);
            $sponsorYoy[$y] = $buildMonthly($yRows);
            $packageYoy[$y] = $buildPackageMonthly($yRows);
            $priceYoy[$y] = $buildMonthlyIdr($yRows);
        }

        return [
            'years'      => $years,
            'sponsorYoy' => $sponsorYoy,
            'packageYoy' => $packageYoy,
            'priceYoy'   => $priceYoy,
            'target'     => 168000000,
        ];
    }

    private function dashboardCards(int $year, Collection $pendingRenewals): array
    {
        $totalSponsor = Sponsor::where('status', 'publish')->count();

        $thisYearConfirmed = SponsorRenewal::where('renewal_year', $year)
            ->where('renewal_status', 'renewed')
            ->count();

        // Final tahun lalu, buat perbandingan year-over-year di kartu This Year Sponsor.
        $lastYearConfirmed = SponsorRenewal::where('renewal_year', $year - 1)
            ->where('renewal_status', 'renewed')
            ->count();

        $pendingRenewalCount = $pendingRenewals->count();

        // Expired = the part of Pending Renewal whose contract already ended (this month
        // or earlier) with no confirm/not-renew decision yet. Counted from the pending
        // list itself so it is always a subset of the Pending Renewal card.
        $nowYm = now()->format('Y-m');
        $expiredCount = $pendingRenewals
            ->filter(function ($er) use ($nowYm) {
                return $er->contract_end <= $nowYm;
            })
            ->count();

        $notRenewCount = SponsorRenewal::where('renewal_year', $year)
            ->where('renewal_status', 'not_renewed')
            ->count();

        return [
            'totalSponsor'        => $totalSponsor,
            'thisYearConfirmed'   => $thisYearConfirmed,
            'lastYearConfirmed'   => $lastYearConfirmed,
            'pendingRenewalCount' => $pendingRenewalCount,
            'expiredCount'        => $expiredCount,
            'notRenewCount'       => $notRenewCount,
        ];
    }
}
