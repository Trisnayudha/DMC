<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Support\XenditFee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class FinancialReportController extends Controller
{
    public function __construct()
    {
        // auth handled by cms_auth route middleware
    }

    /**
     * Base query: PAID OFF + has payment_method + exclude free (price > 0)
     * Includes joins for tickets/users/profiles/company.
     */
    private function baseQuery($eventId, Request $request)
    {
        $q = DB::table('payment as p')
            ->leftJoin('events_tickets as et', 'et.id', '=', 'p.tickets_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.member_id')
            ->leftJoin('profiles as pr', 'pr.users_id', '=', 'u.id')
            ->leftJoin('company as c', 'c.id', '=', 'pr.company_id')
            ->where('p.events_id', $eventId)
            ->where('p.status_registration', 'Paid Off')
            ->whereNotNull('p.payment_method')
            ->where(function ($w) {
                $w->where('et.price_rupiah', '>', 0)
                    ->orWhere('et.price_dollar', '>', 0);
            });

        // Filters
        if ($request->filled('start_date')) {
            $q->whereDate('p.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $q->whereDate('p.created_at', '<=', $request->end_date);
        }

        // payment_method: treat "All" as empty (defensive)
        if ($request->filled('payment_method') && strtolower($request->payment_method) !== 'all') {
            $q->where('p.payment_method', $request->payment_method);
        }

        if ($request->filled('keyword')) {
            $kw = '%' . $request->keyword . '%';
            $q->where(function ($w) use ($kw) {
                $w->where('u.name', 'like', $kw)
                    ->orWhere('u.email', 'like', $kw)
                    ->orWhere('c.company_name', 'like', $kw)
                    ->orWhere('p.code_payment', 'like', $kw)
                    ->orWhere('et.title', 'like', $kw);
            });
        }

        return $q;
    }

    /**
     * Row columns + computed amounts.
     */
    private function selectColumns($q)
    {
        return $q->select([
            'p.id as payment_id',
            'p.created_at as paid_at',
            'p.code_payment',
            'p.payment_method',
            'p.discount',
            'p.package',
            'p.member_id',
            'p.groupby_users_id',
            'p.booking_contact_id',

            'et.title as ticket_title',
            'et.type as ticket_type',
            'et.price_rupiah',
            'et.price_dollar',

            'u.name',
            'u.email',

            'pr.job_title',
            'pr.phone',

            'c.company_name',
            'c.company_category',
        ])
            ->selectRaw("
                CASE
                    WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah
                    ELSE IFNULL(et.price_dollar,0)
                END as gross_amount
            ")
            ->selectRaw("
                GREATEST(
                    (CASE
                        WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah
                        ELSE IFNULL(et.price_dollar,0)
                    END) - IFNULL(p.discount,0),
                    0
                ) as net_amount
            ");
    }

    /**
     * Collapse group-payment rows (same code_payment + groupby_users_id set) into one summary row.
     * Individual payments (groupby_users_id IS NULL) are kept as-is.
     */
    private function groupRows($rows)
    {
        $singles = [];
        $groups  = [];

        foreach ($rows as $row) {
            if (!empty($row->groupby_users_id)) {
                // Group by owner (groupby_users_id): satu transaksi Xendit = satu grup,
                // walaupun tiap peserta punya code_payment sendiri. Kalau di-key per
                // code_payment, fee tetap (mis. Rp 2.000 kartu) kepotong berkali-kali.
                $key = $row->groupby_users_id;
                if (!isset($groups[$key])) {
                    $g = clone $row;
                    $g->gross_amount     = 0;
                    $g->discount         = 0;
                    $g->net_amount       = 0;
                    $g->participant_count = 0;
                    $g->members          = [];
                    $g->ticket_titles    = [];
                    $g->is_group         = true;
                    $groups[$key]        = $g;
                }
                $groups[$key]->gross_amount      += (float) ($row->gross_amount ?? 0);
                $groups[$key]->discount          += (float) ($row->discount ?? 0);
                $groups[$key]->net_amount        += (float) ($row->net_amount ?? 0);
                $groups[$key]->participant_count++;
                $groups[$key]->members[]          = $row->name . ' (' . $row->email . ')';
                if ($row->ticket_title && !in_array($row->ticket_title, $groups[$key]->ticket_titles)) {
                    $groups[$key]->ticket_titles[] = $row->ticket_title;
                }
                // Jadikan owner (groupby_users_id) sebagai kontak utama grup:
                // code_payment-nya = reference transaksi Xendit, plus identitasnya.
                if ((string) $row->member_id === (string) $row->groupby_users_id) {
                    $groups[$key]->code_payment = $row->code_payment;
                    $groups[$key]->name         = $row->name;
                    $groups[$key]->email        = $row->email;
                    $groups[$key]->job_title    = $row->job_title;
                    $groups[$key]->phone        = $row->phone;
                    $groups[$key]->company_name = $row->company_name;
                }
            } else {
                $row->is_group = false;
                $singles[]     = $row;
            }
        }

        // Finalize ticket_title for group rows
        foreach ($groups as $g) {
            $g->ticket_title = implode(', ', $g->ticket_titles);
        }

        $all = array_merge($singles, array_values($groups));
        usort($all, fn ($a, $b) => strcmp($b->paid_at, $a->paid_at));

        return collect($all);
    }

    /**
     * Apply fee estimation to rows and attach KPI fields.
     */
    private function applyFeeEstimation($rows, $kpi)
    {
        $feeTotal = 0.0;
        $pph23Total = 0.0;

        foreach ($rows as $r) {
            $calc = XenditFee::estimate($r->payment_method, $r->net_amount ?? 0);

            $r->x_fee = $calc['fee'];
            $r->x_vat = $calc['vat'];
            $r->x_total_fee = $calc['total_fee'];
            $r->x_pph23 = $calc['pph23'];
            $r->net_after_fee = $calc['net'];

            $feeTotal += $r->x_total_fee;
            $pph23Total += $r->x_pph23;
        }

        // attach extra KPI
        if ($kpi) {
            $kpi->fee_total_est = $feeTotal;
            $kpi->pph23_total_est = $pph23Total;
            $kpi->net_settlement_est = max(($kpi->net_total ?? 0) - $feeTotal - $pph23Total, 0);
        }

        return [$rows, $kpi];
    }

    /**
     * Bangun baris laporan lengkap (grouped + estimasi fee) beserta KPI.
     * Dipakai bersama oleh index, exportPdf, dan exportExcel supaya angka konsisten.
     */
    private function buildReportRows($eventId, Request $request)
    {
        $rows = $this->selectColumns($this->baseQuery($eventId, $request))
            ->orderBy('p.created_at', 'desc')
            ->get();

        $kpi = $this->baseQuery($eventId, $request)
            ->selectRaw("COUNT(*) as paid_trx")
            ->selectRaw("SUM(CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE IFNULL(et.price_dollar,0) END) as gross_total")
            ->selectRaw("SUM(IFNULL(p.discount,0)) as discount_total")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE IFNULL(et.price_dollar,0) END) - IFNULL(p.discount,0),0)) as net_total")
            ->first();

        // Collapse group payments jadi satu baris, lalu tempel estimasi fee.
        $rows = $this->groupRows($rows);
        return $this->applyFeeEstimation($rows, $kpi);
    }

    public function index($slug, Request $request)
    {
        $event = Events::where('slug', $slug)->firstOrFail();

        // Rows + KPI (grouped + fee estimation)
        [$rows, $kpi] = $this->buildReportRows($event->id, $request);

        // Chart: net by day
        $chartDaily = $this->baseQuery($event->id, $request)
            ->selectRaw("DATE(p.created_at) as trx_date")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE IFNULL(et.price_dollar,0) END) - IFNULL(p.discount,0),0)) as net_total")
            ->groupBy(DB::raw("DATE(p.created_at)"))
            ->orderBy('trx_date', 'asc')
            ->get();

        // Chart: net by payment method
        $chartMethod = $this->baseQuery($event->id, $request)
            ->selectRaw("p.payment_method as label")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE IFNULL(et.price_dollar,0) END) - IFNULL(p.discount,0),0)) as value")
            ->groupBy('p.payment_method')
            ->orderBy('value', 'desc')
            ->get();

        // Chart: net by ticket
        $chartTicket = $this->baseQuery($event->id, $request)
            ->selectRaw("et.title as label")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE IFNULL(et.price_dollar,0) END) - IFNULL(p.discount,0),0)) as value")
            ->groupBy('et.title')
            ->orderBy('value', 'desc')
            ->limit(12)
            ->get();

        // Payment method list for dropdown (clean: Paid Off + paid tickets only)
        $methods = $this->baseQuery($event->id, new Request()) // base without request filters
            ->distinct()
            ->orderBy('p.payment_method', 'asc')
            ->pluck('p.payment_method');

        return view('admin.events.financial-report', [
            'slug' => $slug,
            'event' => $event,
            'rows' => $rows,
            'kpi' => $kpi,
            'methods' => $methods,
            'chartDaily' => $chartDaily,
            'chartMethod' => $chartMethod,
            'chartTicket' => $chartTicket,
            'filters' => $request->only(['start_date', 'end_date', 'payment_method', 'keyword']),
        ]);
    }

    public function exportExcel($slug, Request $request)
    {
        $event = Events::where('slug', $slug)->firstOrFail();
        [$rows] = $this->buildReportRows($event->id, $request);
        return Excel::download(new FinancialReportExport($rows), 'financial-report-' . $slug . '.xlsx');
    }

    public function exportPdf($slug, Request $request)
    {
        $event = Events::where('slug', $slug)->firstOrFail();

        // Rows + KPI (grouped + fee estimation)
        [$rows, $kpi] = $this->buildReportRows($event->id, $request);

        $pdf = Pdf::setOptions(['isRemoteEnabled' => true])
            ->loadView('admin.events.financial-report-pdf', [
                'event' => $event,
                'rows' => $rows,
                'kpi' => $kpi,
                'filters' => $request->only(['start_date', 'end_date', 'payment_method', 'keyword']),
            ]);

        return $pdf->download('financial-report-' . $slug . '.pdf');
    }
}
