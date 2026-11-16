<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EventSurveyResponseExport;
use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Admin view of the public event feedback surveys (survey_responses table).
 * Rows from the first survey (Nov–Dec 2025) have no event_code and use the
 * legacy columns, so they are filtered as "legacy" and shown with their own columns.
 */
class EventSurveyResponseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $events = $this->eventOptions();

        $query = $this->filteredQuery($filters);
        $summary = $this->summary(clone $query, $filters['event'] === SurveyResponse::LEGACY_FILTER);
        $responses = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.event_survey.index', [
            'responses' => $responses,
            'events' => $events,
            'event' => $filters['event'],
            'search' => $filters['search'],
            'legacy' => $filters['event'] === SurveyResponse::LEGACY_FILTER,
            'summary' => $summary,
        ]);
    }

    public function show(SurveyResponse $eventSurvey)
    {
        return view('admin.event_survey.show', ['response' => $eventSurvey]);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->filteredQuery($filters)->orderByDesc('created_at')->orderByDesc('id')->get();
        $suffix = $filters['event'] ?: 'all-events';

        return Excel::download(new EventSurveyResponseExport($rows), 'event-survey-' . $suffix . '-' . now()->format('Ymd-His') . '.xlsx');
    }

    private function filters(Request $request)
    {
        $validated = $request->validate([
            'event' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
        ]);

        // Default to the most recent event so the page opens on the current survey.
        $event = $request->has('event') ? ($validated['event'] ?? '') : $this->latestEventCode();

        return [
            'event' => (string) $event,
            'search' => trim($validated['search'] ?? ''),
        ];
    }

    private function filteredQuery(array $filters)
    {
        $event = $filters['event'];
        $search = $filters['search'];

        return SurveyResponse::query()
            ->when($event === SurveyResponse::LEGACY_FILTER, function ($query) {
                $query->whereNull('event_code');
            })
            ->when($event !== '' && $event !== SurveyResponse::LEGACY_FILTER, function ($query) use ($event) {
                $query->where('event_code', $event);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where('email', 'like', '%' . $search . '%');
            });
    }

    private function summary($query, $legacy)
    {
        if ($legacy) {
            return $query->selectRaw('COUNT(*) as total, AVG(informative_score) as avg_informative,
                SUM(CASE WHEN wants_more_info = 1 THEN 1 ELSE 0 END) as more_info_yes,
                SUM(CASE WHEN is_member = 1 THEN 1 ELSE 0 END) as members')->first();
        }

        return $query->selectRaw('COUNT(*) as total, AVG(event_rating) as avg_event, AVG(rating_content) as avg_content,
            AVG(rating_networking) as avg_networking,
            SUM(CASE WHEN wants_expert_contact = 1 THEN 1 ELSE 0 END) as expert_yes')->first();
    }

    /**
     * Event filter options: [code => label (count)], newest survey first.
     */
    private function eventOptions()
    {
        return SurveyResponse::query()
            ->selectRaw('event_code, COUNT(*) as total, MAX(created_at) as last_at')
            ->groupBy('event_code')
            ->orderByDesc('last_at')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = $row->event_code ?: SurveyResponse::LEGACY_FILTER;

                $code = $row->event_code ?: 'No event code';

                return [$key => $code . ' — ' . SurveyResponse::eventLabel($row->event_code) . ' (' . number_format($row->total) . ')'];
            });
    }

    private function latestEventCode()
    {
        $latest = SurveyResponse::query()->orderByDesc('created_at')->orderByDesc('id')->first();

        if (!$latest) {
            return '';
        }

        return $latest->event_code ?: SurveyResponse::LEGACY_FILTER;
    }
}
