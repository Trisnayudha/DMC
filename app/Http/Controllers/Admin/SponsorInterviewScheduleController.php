<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorInterviewSchedule;
use Illuminate\Http\Request;

class SponsorInterviewScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = SponsorInterviewSchedule::query();

        if ($request->filled('package')) {
            $query->where('sponsor_package', strtolower((string) $request->package));
        }

        if ($request->filled('company')) {
            $query->where('sponsor_id', (int) $request->company);
        }

        if ($request->filled('slot')) {
            $query->where('preferred_time_slot', (string) $request->slot);
        }

        $list = $query->orderByDesc('id')->get();

        $sponsors = Sponsor::query()
            ->where('status', 'publish')
            ->orderBy('name')
            ->get(['id', 'name']);

        $slots = SponsorInterviewSchedule::query()
            ->select('preferred_time_slot')
            ->distinct()
            ->orderBy('preferred_time_slot')
            ->pluck('preferred_time_slot');

        return view('admin.interview_schedule.sponsor', [
            'list' => $list,
            'sponsors' => $sponsors,
            'slots' => $slots,
            'questionMap' => $this->questionMap(),
            'filterPackage' => (string) $request->query('package', ''),
            'filterCompany' => (string) $request->query('company', ''),
            'filterSlot' => (string) $request->query('slot', ''),
        ]);
    }

    private function questionMap(): array
    {
        return [
            1 => 'Could you briefly introduce your company, including your core business and expertise?',
            2 => 'What key products or solutions are you showcasing at this event?',
            3 => 'What\'s something new about your product or solution?',
            4 => 'Why is this solution relevant for the industry today?',
            5 => 'What trends or challenges are you currently seeing in the mining industry?',
            6 => 'How is your company adapting to new developments and demands in the market?',
            7 => 'What makes your products or services unique in the mining sector?',
            8 => 'Why did you decide to participate in Indonesia Miner 2026?',
            9 => 'What made this event relevant for your company?',
            10 => 'How do you see partnerships and collaborations shaping the future of the mining industry?',
            11 => 'Where can visitors find your booth, and what can they expect to see, discuss, or experience when they visit?',
        ];
    }
}
