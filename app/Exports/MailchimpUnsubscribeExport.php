<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MailchimpUnsubscribeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /** @var Collection */
    private $contacts;
    private $no = 0;

    public function __construct($contacts)
    {
        $this->contacts = $contacts instanceof Collection ? $contacts : collect($contacts);
    }

    public function collection()
    {
        return $this->contacts;
    }

    public function headings(): array
    {
        return [
            'No',
            'Email Address',
            'Full Name',
            'Unsubscribed At',
            'Unsubscribe Campaign / Email',
            'Campaign ID',
            'Unsubscribe Reason',
            'Mailchimp Rating',
            'In DMC Database',
            'DMC Member ID',
            'DMC Name',
            'DMC Member Status',
            'Company Name',
            'Job Title',
            'Phone Number',
        ];
    }

    /**
     * @param object|array $contact
     */
    public function map($contact): array
    {
        $this->no++;
        $c = (object) $contact;

        return [
            $this->no,
            $c->email ?? '-',
            $c->full_name ?: ($c->dmc_name ?? '-'),
            $c->unsubscribed_at ?? '-',
            $c->unsub_campaign_title ?? ($c->is_admin_unsub ? 'Unsubscribed by Admin' : '-'),
            $c->unsub_campaign_id ?? '-',
            $c->reason ?? '-',
            $c->rating ?? '-',
            $c->is_dmc_user ? 'YES' : 'NO',
            $c->dmc_uname ?? '-',
            $c->dmc_name ?? '-',
            $c->dmc_status ?? '-',
            $c->company ?? '-',
            $c->job_title ?? '-',
            $c->phone ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
