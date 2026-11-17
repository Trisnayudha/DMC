<?php

namespace App\Services\Finance;

use App\Models\Sponsors\SponsorRenewal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Data menu Financial Reports (Finance): invoice sponsor (kontrak sponsor_renewals yang
 * sudah punya invoice number) per periode, dipisah Unpaid / Paid.
 *
 * Tiap baris (array):
 *   invoice_no, invoice_date (Carbon — dasar filter periode)
 *   sponsor, description (paket + periode kontrak)
 *   amount        nilai kontrak IDR sebelum PPN
 *   vat           amount × vat_percent
 *   net           amount + vat
 *   pph23         pph23_amount yang diinput Finance
 *   actual        amount - pph23
 *   is_paid, paid_date (Carbon|null), sent_date (Carbon|null)
 */
class FinanceReportService
{
    /**
     * Laporan satu tahun (berdasarkan invoice_date), opsional satu bulan.
     * months = ringkasan 12 bulan tahun itu (untuk strip bulan), tidak terpengaruh filter bulan.
     *
     * @return array{unpaid: Collection, paid: Collection, totals: array, unpaid_totals: array, paid_totals: array, months: array}
     */
    public function build(int $year, ?int $month = null, string $keyword = ''): array
    {
        $rows   = $this->yearRows($year, $keyword);
        $months = $this->monthSummary($rows);

        if ($month) {
            $rows = $rows->filter(function ($row) use ($month) {
                return (int) $row['renewal_month']->month === $month;
            });
        }

        // Unpaid: invoice tertua dulu (paling lama menunggu); Paid: terbaru dulu.
        $unpaid = $rows->where('is_paid', false)->sortBy(function ($row) { return $row['invoice_date']->timestamp; })->values();
        $paid   = $rows->where('is_paid', true)->sortByDesc(function ($row) { return $row['invoice_date']->timestamp; })->values();

        return [
            'unpaid'        => $unpaid,
            'paid'          => $paid,
            'totals'        => $this->totals($rows),
            'unpaid_totals' => $this->totals($unpaid),
            'paid_totals'   => $this->totals($paid),
            'months'        => $months,
        ];
    }

    /** Memo semua invoice sponsor dalam satu request (dipakai build + monthlySummary + availableYears). */
    private $allRows;

    /** Tahun yang punya invoice sponsor (berdasarkan bulan renewal, terbaru dulu), selalu termasuk tahun berjalan. */
    public function availableYears(): array
    {
        return $this->allRows()
            ->map(function ($row) { return (int) $row['renewal_month']->year; })
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /** Ringkasan 12 bulan satu tahun — dipakai grafik untuk tahun pembanding (vs). */
    public function monthlySummary(int $year, string $keyword = ''): array
    {
        return $this->monthSummary($this->yearRows($year, $keyword));
    }

    /** Invoice satu tahun (bulan renewal), opsional disaring keyword nomor invoice / sponsor. */
    private function yearRows(int $year, string $keyword): Collection
    {
        $rows = $this->allRows()->filter(function ($row) use ($year) {
            return (int) $row['renewal_month']->year === $year;
        });

        if ($keyword !== '') {
            $needle = mb_strtolower($keyword);
            $rows = $rows->filter(function ($row) use ($needle) {
                return mb_strpos(mb_strtolower($row['invoice_no'] . ' ' . $row['sponsor']), $needle) !== false;
            });
        }

        return $rows;
    }

    /** [1..12 => ['count', 'unpaid', 'paid', 'amount', 'net', 'actual']] */
    private function monthSummary(Collection $rows): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $inMonth = $rows->filter(function ($row) use ($m) {
                return (int) $row['renewal_month']->month === $m;
            });
            $months[$m] = [
                'count'  => $inMonth->count(),
                'unpaid' => $inMonth->where('is_paid', false)->count(),
                'paid'   => $inMonth->where('is_paid', true)->count(),
                'amount' => $inMonth->sum('amount'),
                'net'    => $inMonth->sum('net'),
                'actual' => $inMonth->sum('actual'),
            ];
        }
        return $months;
    }

    public function totals(Collection $rows): array
    {
        return [
            'count'  => $rows->count(),
            'amount' => $rows->sum('amount'),
            'vat'    => $rows->sum('vat'),
            'net'    => $rows->sum('net'),
            'pph23'  => $rows->sum('pph23'),
            'actual' => $rows->sum('actual'),
        ];
    }

    /** Semua kontrak sponsor yang sudah punya invoice number (+ invoice_date), di-memo per request. */
    private function allRows(): Collection
    {
        if ($this->allRows === null) {
            $this->allRows = $this->sponsorRows();
        }
        return $this->allRows;
    }

    private function sponsorRows(): Collection
    {
        $renewals = SponsorRenewal::with('sponsor:id,name,branding_name')
            ->whereNotNull('invoice_number')
            ->whereNotNull('invoice_date')
            ->get();

        // Kontrak lama (renewed) per sponsor — untuk mencari bulan habis kontrak sebelumnya.
        $history = SponsorRenewal::whereIn('sponsor_id', $renewals->pluck('sponsor_id')->unique())
            ->where('renewal_status', 'renewed')
            ->whereNotNull('contract_end')
            ->get(['id', 'sponsor_id', 'contract_end'])
            ->groupBy('sponsor_id');

        return $renewals->map(function (SponsorRenewal $r) use ($history) {
                $renewalMonth = $this->renewalMonth($r, $history->get($r->sponsor_id, collect()));
                $amount = (float) ($r->amount_idr ?? 0);
                $vat    = floor($amount * (float) ($r->vat_percent ?? 0) / 100);
                $pph23  = (float) ($r->pph23_amount ?? 0);

                return [
                    'invoice_no'   => $r->invoice_number,
                    'invoice_date' => $r->invoice_date,
                    'renewal_month' => $renewalMonth,
                    'sponsor'      => $r->sponsor ? ($r->sponsor->branding_name ?: $r->sponsor->name) : '—',
                    'description'  => trim(sponsor_package_label($r->package) . ' Sponsorship · ' . $this->period($r->contract_start, $r->contract_end)),
                    'amount'       => $amount,
                    'vat'          => $vat,
                    'net'          => $amount + $vat,
                    'pph23'        => $pph23,
                    'actual'       => $amount - $pph23,
                    'is_paid'      => $r->paid_date !== null,
                    'paid_date'    => $r->paid_date,
                    'sent_date'    => $r->invoice_sent_date,
                ];
            });
    }

    /**
     * Bulan renewal = bulan habis kontrak sebelumnya (sama dengan daftar expiry di
     * Sponsorship Renewals): contract_end terbaru milik sponsor yang < contract_start kontrak ini.
     * Sponsor baru (tanpa kontrak sebelumnya) → bulan contract_start; tanpa contract_start → invoice_date.
     */
    private function renewalMonth(SponsorRenewal $r, Collection $history): Carbon
    {
        if ($r->contract_start) {
            // Kontrak lama yang habisnya > 12 bulan sebelum kontrak ini mulai = sponsor kembali, bukan renewal.
            $earliestEnd = Carbon::createFromFormat('Y-m-d', $r->contract_start . '-01')->subMonths(12)->format('Y-m');
            $previousEnd = $history
                ->filter(function ($h) use ($r, $earliestEnd) {
                    return $h->id !== $r->id && $h->contract_end < $r->contract_start && $h->contract_end >= $earliestEnd;
                })
                ->max('contract_end');

            return Carbon::createFromFormat('Y-m-d', ($previousEnd ?: $r->contract_start) . '-01')->startOfDay();
        }

        return $r->invoice_date->copy()->startOfMonth();
    }

    private function period(?string $start, ?string $end): string
    {
        if (!$start || !$end) {
            return '';
        }
        return Carbon::createFromFormat('Y-m', $start)->format('M Y') . ' – ' . Carbon::createFromFormat('Y-m', $end)->format('M Y');
    }
}
