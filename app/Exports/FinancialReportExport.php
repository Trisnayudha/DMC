<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export Financial Report ke Excel.
 *
 * Menerima baris yang SUDAH diproses controller (group payment digabung +
 * estimasi fee Xendit tertempel), supaya angka di Excel identik dengan tabel
 * di layar & PDF — termasuk perlakuan 1 transaksi grup = 1 baris.
 */
class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var Collection */
    private $rows;

    public function __construct($rows)
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Paid At',
            'Code',
            'Payment Method',
            'Participants',
            'Ticket',
            'Buyer Name',
            'Email',
            'Job Title',
            'Phone',
            'Company',
            'Gross',
            'Discount',
            'Amount',
            'Fee',
            'VAT',
            'PPh 23',
            'Net Settlement',
        ];
    }

    public function map($row): array
    {
        $name = $row->name;
        if (!empty($row->is_group) && (int) ($row->participant_count ?? 1) > 1) {
            $name .= ' (+' . ((int) $row->participant_count - 1) . ' more)';
        }

        return [
            $row->paid_at ? date('Y-m-d H:i', strtotime($row->paid_at)) : '',
            $row->code_payment,
            $row->payment_method,
            !empty($row->is_group) ? (int) $row->participant_count : 1,
            $row->ticket_title,
            $name,
            $row->email,
            $row->job_title,
            $row->phone,
            $row->company_name,
            (float) ($row->gross_amount ?? 0),
            (float) ($row->discount ?? 0),
            (float) ($row->net_amount ?? 0),
            (float) ($row->x_fee ?? 0),
            (float) ($row->x_vat ?? 0),
            (float) ($row->x_pph23 ?? 0),
            (float) ($row->net_after_fee ?? 0),
        ];
    }
}
