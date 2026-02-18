<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinancialReportExport implements FromCollection, WithHeadings
{
    private int $eventId;
    private array $filters;

    public function __construct(int $eventId, Request $request)
    {
        $this->eventId = $eventId;
        $this->filters = $request->only(['start_date', 'end_date', 'payment_method', 'keyword']);
    }

    public function headings(): array
    {
        return [
            'Paid At',
            'Code',
            'Payment Method',
            'Ticket',
            'Buyer Name',
            'Email',
            'Job Title',
            'Phone',
            'Company',
            'Gross',
            'Discount',
            'Net'
        ];
    }

    public function collection()
    {
        $q = DB::table('payment as p')
            ->leftJoin('events_tickets as et', 'et.id', '=', 'p.tickets_id')
            ->leftJoin('users as u', 'u.id', '=', 'p.member_id')
            ->leftJoin('profiles as pr', 'pr.users_id', '=', 'u.id')
            ->leftJoin('company as c', 'c.id', '=', 'pr.company_id')
            ->where('p.events_id', $this->eventId)
            ->where('p.status_registration', 'Paid Off')
            ->whereNotNull('p.payment_method')
            ->where(function ($w) {
                $w->where('et.price_rupiah', '>', 0)
                    ->orWhere('et.price_dollar', '>', 0);
            });

        if (!empty($this->filters['start_date'])) $q->whereDate('p.created_at', '>=', $this->filters['start_date']);
        if (!empty($this->filters['end_date'])) $q->whereDate('p.created_at', '<=', $this->filters['end_date']);
        if (!empty($this->filters['payment_method'])) $q->where('p.payment_method', $this->filters['payment_method']);

        if (!empty($this->filters['keyword'])) {
            $kw = '%' . $this->filters['keyword'] . '%';
            $q->where(function ($w) use ($kw) {
                $w->where('u.name', 'like', $kw)
                    ->orWhere('u.email', 'like', $kw)
                    ->orWhere('c.company_name', 'like', $kw)
                    ->orWhere('p.code_payment', 'like', $kw)
                    ->orWhere('et.title', 'like', $kw);
            });
        }

        $rows = $q->select([
            DB::raw("DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i') as paid_at"),
            'p.code_payment',
            'p.payment_method',
            'et.title as ticket_title',
            'u.name',
            'u.email',
            'pr.job_title',
            'pr.phone',
            'c.company_name',
            DB::raw("CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END as gross"),
            DB::raw("IFNULL(p.discount,0) as discount"),
            DB::raw("GREATEST((CASE WHEN IFNULL(et.price_rupiah,0) > 0 THEN et.price_rupiah ELSE et.price_dollar END) - IFNULL(p.discount,0),0) as net"),
        ])
            ->orderBy('p.created_at', 'desc')
            ->get();

        return new Collection($rows);
    }
}
