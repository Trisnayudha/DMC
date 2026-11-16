<?php

namespace App\Exports;

use App\Models\SurveyResponse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventSurveyResponseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /** @var Collection */
    private $rows;
    private $no = 0;

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
            'No', 'Submitted At', 'Event', 'Event Code', 'Email',
            'Overall Event Rating', 'Content Rating', 'Networking Rating',
            'Liked Most', 'Improvement Feedback', 'Topic Recommendation',
            'Wants McCloskey Expert Contact', 'App Activated',
            // First survey (no event code)
            'Informative Score', 'Most Relevant Presentations', 'Is Member', 'Wants More Info',
            'Feedback', 'Topics 2026',
        ];
    }

    /**
     * @param SurveyResponse $r
     */
    public function map($r): array
    {
        $this->no++;
        $presentations = is_array($r->most_relevant_presentations) ? implode('; ', $r->most_relevant_presentations) : '';

        return [
            $this->no,
            $r->created_at ? $r->created_at->format('Y-m-d H:i') : '',
            SurveyResponse::eventLabel($r->event_code),
            $r->event_code ?: '',
            $r->email,
            $r->event_rating,
            $r->rating_content,
            $r->rating_networking,
            $r->liked_most,
            $r->improvement_feedback,
            $r->topic_recommendation,
            $this->yesNo($r->wants_expert_contact),
            $r->app_activated,
            $r->informative_score,
            $presentations,
            $this->yesNo($r->is_member),
            $this->yesNo($r->wants_more_info),
            $r->feedback,
            $r->topics_2026,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    private function yesNo($value)
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'Yes' : 'No';
    }
}
