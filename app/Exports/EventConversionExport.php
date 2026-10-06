<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventConversionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /** @var Collection */
    private $events;
    private $no = 0;

    public function __construct($events)
    {
        $this->events = $events instanceof Collection ? $events : collect($events);
    }

    public function collection()
    {
        return $this->events;
    }

    public function headings(): array
    {
        return [
            'No',
            'Event Name',
            'Event Category',
            'Event Type',
            'Start Date',
            'End Date',
            'Location',
            'Registration / Visitors',
            'Confirmed Contacts',
            'Attendance (Entries)',
            'Unique People',
            'Verified Members Attended',
            'Follow-up Interest (Prospects)',
            'Pending Converted Members',
            'Active Converted Members',
            'Total Converted Members',
            'Conversion Rate (%)',
        ];
    }

    /**
     * @param object|array $event
     */
    public function map($event): array
    {
        $this->no++;
        $event = (object) $event;

        return [
            $this->no,
            $event->name ?? '-',
            $event->is_supporting ? 'Supporting Event (Partnership)' : 'DMC Owned Event',
            $event->event_type ?? '-',
            $event->start_date ?? '-',
            $event->end_date ?? '-',
            $event->location ?? '-',
            $event->registration_count ?? 0,
            $event->confirmed_count ?? 0,
            $event->attendance_count ?? 0,
            $event->unique_count ?? 0,
            $event->verified_members_count ?? 0,
            $event->interest_count ?? 0,
            $event->pending_converted ?? 0,
            $event->active_converted ?? 0,
            $event->total_converted ?? 0,
            ($event->conversion_rate ?? 0) . '%',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
