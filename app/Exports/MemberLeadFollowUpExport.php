<?php

namespace App\Exports;

use App\Models\MemberLeadFollowUp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Lead Follow-Up export — one row per lead, with the whole notes history
 * (oldest first) in the last column.
 */
class MemberLeadFollowUpExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $leads;

    public function __construct(Collection $leads)
    {
        $this->leads = $leads;
    }

    public function headings(): array
    {
        return [
            'No',
            'Created',
            'Name',
            'Job Title',
            'Email',
            'Phone',
            'Company',
            'PIC',
            'Channel',
            'Result',
            'Temperature',
            'Score',
            'Next Step',
            'Deadline',
            'Over SLA',
            'Sponsor Kit Sent',
            'Follow-up 1',
            'Follow-up 2',
            'Do Not Send',
            'Latest Note',
            'Notes History',
        ];
    }

    public function collection()
    {
        $no = 1;

        return $this->leads->map(function (MemberLeadFollowUp $lead) use (&$no) {
            $user    = $lead->user;
            $profile = optional($user)->profile;
            $next    = $lead->nextStepKey();
            $notes   = $lead->notesHistory;

            return [
                $no++,
                $this->date($lead->created_at),
                optional($user)->name ?? '(deleted user)',
                optional($profile)->job_title,
                optional($user)->email,
                optional($profile)->fullphone ?: optional($profile)->phone,
                optional(optional($user)->company)->company_name,
                $lead->pic_name,
                $lead->channel ? ucfirst($lead->channel) : null,
                ucfirst($lead->result),
                strtoupper($lead->temperature ?: 'cold'),
                (int) $lead->temperature_score,
                $next ? MemberLeadFollowUp::stepLabel($next) : 'Done',
                $this->date($lead->deadline_at),
                $lead->isOverSla() ? 'Yes' : 'No',
                $this->date($lead->sponsorkit_sent_at),
                $this->date($lead->first_follow_up_at),
                $this->date($lead->second_follow_up_at),
                $lead->do_not_send ? 'Yes' . ($lead->do_not_send_reason ? ' — ' . $lead->do_not_send_reason : '') : 'No',
                optional($notes->first())->note,
                $notes->reverse()->map(function ($note) {
                    return '[' . $this->date($note->created_at) . ' · ' . $note->stepLabel() . ' · ' . ($note->created_by_name ?: '-') . '] ' . $note->note;
                })->implode("\n"),
            ];
        });
    }

    private function date($value): ?string
    {
        return $value ? $value->format('d M Y H:i') : null;
    }
}
