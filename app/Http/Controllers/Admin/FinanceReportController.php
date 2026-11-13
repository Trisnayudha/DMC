<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceReportService;
use Illuminate\Http\Request;

/**
 * Menu Financial Reports (Finance): invoice sponsor per tahun / bulan (invoice_date),
 * ringkasan Total Amount / Net / Actual / PPh 23, Unpaid di atas, Paid di bawah.
 * Data disusun oleh FinanceReportService.
 */
class FinanceReportController extends Controller
{
    public function index(Request $request, FinanceReportService $service)
    {
        $years = $service->availableYears();

        $year = (int) $request->get('year', now()->year);
        if (!in_array($year, $years, true)) {
            $year = (int) now()->year;
        }

        $month = (int) $request->get('month', 0);
        $month = ($month >= 1 && $month <= 12) ? $month : null;

        $keyword = trim((string) $request->get('keyword', ''));

        // Tahun pembanding grafik (vs): default tahun sebelumnya.
        $compareYears = $this->compareYears($years, $year);
        $compare = (int) $request->get('compare', $year - 1);
        if (!in_array($compare, $compareYears, true)) {
            $compare = $year - 1;
        }

        return view('admin.finance_reports.index', $service->build($year, $month, $keyword) + [
            'years'         => $years,
            'compareYears'  => $compareYears,
            'compareMonths' => $service->monthlySummary($compare, $keyword),
            'filters'       => [
                'year'    => $year,
                'month'   => $month,
                'keyword' => $keyword,
                'compare' => $compare,
            ],
        ]);
    }

    /**
     * Pilihan tahun pembanding: tahun yang punya invoice + 3 tahun sebelum tahun terpilih
     * (supaya tetap bisa dibandingkan walau datanya kosong), tanpa tahun terpilih sendiri.
     */
    private function compareYears(array $years, int $year): array
    {
        $options = array_merge($years, [$year - 1, $year - 2, $year - 3]);
        $options = array_values(array_unique(array_diff($options, [$year])));
        rsort($options);
        return $options;
    }
}
