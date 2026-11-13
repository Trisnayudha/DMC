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
                return (int) $row['invoice_date']->month === $month;
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

    /** Tahun yang punya invoice sponsor (terbaru dulu), selalu termasuk tahun berjalan. */
    public function availableYears(): array
    {
        $years = SponsorRenewal::whereNotNull('invoice_number')
            ->whereNotNull('invoice_date')
            ->selectRaw('DISTINCT YEAR(invoice_date) AS y')
            ->pluck('y')
            ->map(function ($y) { return (int) $y; })
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $years;
    }

    /** Ringkasan 12 bulan satu tahun — dipakai grafik untuk tahun pembanding (vs). */
    public function monthlySummary(int $year, string $keyword = ''): array
    {
        return $this->monthSummary($this->yearRows($year, $keyword));
    }

    /** Invoice satu tahun (invoice_date), opsional disaring keyword nomor invoice / sponsor. */
    private function yearRows(int $year, string $keyword): Collection
    {
        $rows = $this->sponsorRows(Carbon::create($year, 1, 1), Carbon::create($year, 12, 31));

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
                return (int) $row['invoice_date']->month === $m;
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

    /** Kontrak sponsor yang sudah punya invoice number, invoice_date di dalam periode. */
    private function sponsorRows(Carbon $from, Carbon $to): Collection
    {
        return SponsorRenewal::with('sponsor:id,name,branding_name')
            ->whereNotNull('invoice_number')
            ->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (SponsorRenewal $r) {
                $amount = (float) ($r->amount_idr ?? 0);
                $vat    = floor($amount * (float) ($r->vat_percent ?? 0) / 100);
                $pph23  = (float) ($r->pph23_amount ?? 0);

                return [
                    'invoice_no'   => $r->invoice_number,
                    'invoice_date' => $r->invoice_date,
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

    private function period(?string $start, ?string $end): string
    {
        if (!$start || !$end) {
            return '';
        }
        return Carbon::createFromFormat('Y-m', $start)->format('M Y') . ' – ' . Carbon::createFromFormat('Y-m', $end)->format('M Y');
    }
}
