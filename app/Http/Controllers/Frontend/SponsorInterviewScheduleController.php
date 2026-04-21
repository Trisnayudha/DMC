<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorInterviewSchedule;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SponsorInterviewScheduleController extends Controller
{
    public function create()
    {
        $sponsors = Sponsor::query()
            ->where('status', 'publish')
            ->whereIn('package', ['silver', 'gold'])
            ->orderBy('name')
            ->get(['id', 'name', 'package']);

        $bookedSlots = SponsorInterviewSchedule::query()
            ->pluck('preferred_time_slot')
            ->all();

        return view('interview_schedule.sponsor', [
            'sponsors' => $sponsors,
            'timeSlots' => $this->timeSlots(),
            'questions' => $this->questions(),
            'bookedSlots' => $bookedSlots,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('sponsors', 'id')],
            'number_of_interviewees' => 'required|integer|min:1|max:5',
            'interviewees' => 'required|array|min:1|max:5',
            'interviewees.*.name' => 'required|string|max:255',
            'interviewees.*.job_title' => 'required|string|max:255',
            'preferred_time_slot' => ['required', 'string', Rule::in($this->timeSlots())],
            'selected_questions' => 'required|array',
            'selected_questions.*' => ['integer', Rule::in(array_keys($this->questions()))],
        ]);

        $sponsor = Sponsor::query()
            ->where('id', $validated['company_id'])
            ->where('status', 'publish')
            ->first();

        if (!$sponsor) {
            return back()->withErrors(['company_id' => 'Company sponsor tidak ditemukan.'])->withInput();
        }

        $package = strtolower((string) $sponsor->package);
        if (!in_array($package, ['silver', 'gold'], true)) {
            return back()->withErrors(['company_id' => 'Hanya sponsor Silver / Gold yang bisa mengisi form ini.'])->withInput();
        }

        $selectedQuestions = collect($validated['selected_questions'])
            ->map(fn($q) => (int) $q)
            ->unique()
            ->sort()
            ->values();

        if (!$selectedQuestions->contains(1) || !$selectedQuestions->contains(11)) {
            return back()->withErrors([
                'selected_questions' => 'Question nomor 1 dan 11 wajib dipilih.',
            ])->withInput();
        }

        $additionalCount = $selectedQuestions
            ->reject(fn($q) => in_array($q, [1, 11], true))
            ->count();

        $maxAdditional = $package === 'silver' ? 3 : 5;
        if ($additionalCount > $maxAdditional) {
            return back()->withErrors([
                'selected_questions' => "Sponsor {$package} maksimal memilih {$maxAdditional} pertanyaan tambahan.",
            ])->withInput();
        }

        if ((int) $validated['number_of_interviewees'] !== count($validated['interviewees'])) {
            return back()->withErrors([
                'interviewees' => 'Jumlah interviewee tidak sesuai dengan number of interviewees.',
            ])->withInput();
        }

        $slotAlreadyTaken = SponsorInterviewSchedule::query()
            ->where('preferred_time_slot', $validated['preferred_time_slot'])
            ->exists();

        if ($slotAlreadyTaken) {
            return back()->withErrors([
                'preferred_time_slot' => 'Time slot ini sudah dipilih sponsor lain. Silakan pilih slot lain.',
            ])->withInput();
        }

        try {
            $saved = SponsorInterviewSchedule::create([
                'sponsor_id' => $sponsor->id,
                'company_name' => $sponsor->name,
                'sponsor_package' => $package,
                'number_of_interviewees' => (int) $validated['number_of_interviewees'],
                'interviewees' => array_values($validated['interviewees']),
                'preferred_time_slot' => $validated['preferred_time_slot'],
                'selected_questions' => $selectedQuestions->all(),
            ]);
        } catch (QueryException $e) {
            // Handle concurrent submit that hits unique(preferred_time_slot)
            if ((string) $e->getCode() === '23000') {
                return back()->withErrors([
                    'preferred_time_slot' => 'Time slot ini baru saja dibooking sponsor lain. Silakan pilih slot lain.',
                ])->withInput();
            }

            throw $e;
        }

        $this->sendInterviewScheduleNotification($saved, $selectedQuestions->all());

        return redirect()
            ->route('sponsor.interview-schedule.create')
            ->with('success', 'Interview schedule berhasil dikirim.');
    }

    public function bookedSlots()
    {
        $slots = SponsorInterviewSchedule::query()
            ->pluck('preferred_time_slot')
            ->values();

        return response()->json([
            'booked_slots' => $slots,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function timeSlots(): array
    {
        return [
            '10:00 - 10:15',
            '10:15 - 10:30',
            '10:30 - 10:45',
            '10:45 - 11:00',
            '11:00 - 11:15',
            '11:15 - 11:30',
            '11:30 - 11:45',
            '11:45 - 12:00',
            '13:00 - 13:15',
            '13:15 - 13:30',
            '13:30 - 13:45',
            '13:45 - 14:00',
            '14:00 - 14:15',
            '14:15 - 14:30',
        ];
    }

    private function questions(): array
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

    private function sendInterviewScheduleNotification(SponsorInterviewSchedule $schedule, array $selectedQuestions): void
    {
        try {
            $groupId = '120363422942310672@g.us';
            $interviewees = collect($schedule->interviewees ?? [])
                ->map(function ($item, $idx) {
                    $name = trim((string) ($item['name'] ?? ''));
                    $job = trim((string) ($item['job_title'] ?? ''));
                    return ($idx + 1) . '. ' . ($name !== '' ? $name : '-') . ' - ' . ($job !== '' ? $job : '-');
                })
                ->implode("\n");

            $message = "New Sponsor Interview Schedule Submitted\n\n"
                . "Company: {$schedule->company_name}\n"
                . "Package: " . strtoupper((string) $schedule->sponsor_package) . "\n"
                . "Time Slot: {$schedule->preferred_time_slot}\n"
                . "Number of Interviewees: {$schedule->number_of_interviewees}\n"
                . "Selected Questions: " . implode(', ', $selectedQuestions) . "\n"
                . "Submitted At: " . now()->format('Y-m-d H:i:s') . "\n\n"
                . "Interviewees:\n" . ($interviewees !== '' ? $interviewees : '-');

            $wa = new WhatsappApi();
            $wa->phone = $groupId;
            $wa->message = $message;
            $wa->WhatsappMessageGroup();
        } catch (\Throwable $e) {
            Log::warning('Failed sending sponsor interview schedule WA notification: ' . $e->getMessage());
        }
    }
}
