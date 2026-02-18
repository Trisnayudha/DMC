<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class FinancialReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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
            // exclude free by price (IMPORTANT)
            ->where(function ($w) {
                $w->where('et.price_rupiah', '>', 0)
                    ->orWhere('et.price_dollar', '>', 0);
            });

        // filters
        if ($request->filled('start_date')) $q->whereDate('p.created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $q->whereDate('p.created_at', '<=', $request->end_date);

        if ($request->filled('payment_method')) {
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

    private function selectColumns($q)
    {
        return $q->select([
            'p.id as payment_id',
            'p.created_at as paid_at',
            'p.code_payment',
            'p.payment_method',
            'p.discount',
            'p.package',

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
              ELSE et.price_dollar
            END as gross_amount
        ")
            ->selectRaw("
            GREATEST(
              (CASE
                WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah
                ELSE et.price_dollar
              END) - IFNULL(p.discount,0),
              0
            ) as net_amount
        ");
    }

    public function index($slug, Request $request)
    {
        $event = Events::where('slug', $slug)->firstOrFail();

        // for datatable rows
        $rowsQuery = $this->selectColumns($this->baseQuery($event->id, $request))
            ->orderBy('p.created_at', 'desc');

        $rows = $rowsQuery->get();

        // KPI
        $kpi = $this->baseQuery($event->id, $request)
            ->selectRaw("COUNT(*) as paid_trx")
            ->selectRaw("SUM(CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) as gross_total")
            ->selectRaw("SUM(IFNULL(p.discount,0)) as discount_total")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) - IFNULL(p.discount,0),0)) as net_total")
            ->first();


        // chart: by day
        $chartDaily = $this->baseQuery($event->id, $request)
            ->selectRaw("DATE(p.created_at) as trx_date")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) - IFNULL(p.discount,0),0)) as net_total")
            ->groupBy(DB::raw("DATE(p.created_at)"))
            ->orderBy('trx_date', 'asc')
            ->get();


        // chart: by payment method
        $chartMethod = $this->baseQuery($event->id, $request)
            ->selectRaw("p.payment_method as label")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) - IFNULL(p.discount,0),0)) as value")
            ->groupBy('p.payment_method')
            ->orderBy('value', 'desc')
            ->get();


        // chart: by ticket
        $chartTicket = $this->baseQuery($event->id, $request)
            ->selectRaw("et.title as label")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) - IFNULL(p.discount,0),0)) as value")
            ->groupBy('et.title')
            ->orderBy('value', 'desc')
            ->limit(12)
            ->get();


        // payment_method list (for filter dropdown)
        $methods = DB::table('payment')
            ->where('events_id', $event->id)
            ->whereNotNull('payment_method')
            ->distinct()
            ->orderBy('payment_method', 'asc')
            ->pluck('payment_method');

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
        return Excel::download(new FinancialReportExport($event->id, $request), 'financial-report-' . $slug . '.xlsx');
    }

    public function exportPdf($slug, Request $request)
    {
        $event = Events::where('slug', $slug)->firstOrFail();

        $rows = $this->selectColumns($this->baseQuery($event->id, $request))
            ->orderBy('p.created_at', 'desc')
            ->get();

        $kpi = $this->baseQuery($event->id, $request)
            ->selectRaw("COUNT(*) as paid_trx")
            ->selectRaw("SUM(CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) as gross_total")
            ->selectRaw("SUM(IFNULL(p.discount,0)) as discount_total")
            ->selectRaw("SUM(GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) - IFNULL(p.discount,0),0)) as net_total")
            ->first();

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
