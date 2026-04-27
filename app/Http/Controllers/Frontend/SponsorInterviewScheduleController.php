<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\EmailSender;
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
        $sponsorIdLimits = $this->sponsorIdLimits();
        $allowedSponsorIds = array_keys($sponsorIdLimits);
        $sponsorDisplayNames = $this->sponsorDisplayNames();

        $sponsors = Sponsor::query()
            ->where('status', 'publish')
            ->whereIn('id', $allowedSponsorIds)
            ->get(['id', 'name', 'package'])
            ->sortBy(function ($sponsor) use ($allowedSponsorIds) {
                $idx = array_search((int) $sponsor->id, $allowedSponsorIds, true);
                return $idx === false ? 9999 : $idx;
            })
            ->map(function ($sponsor) use ($sponsorDisplayNames) {
                $sponsor->setAttribute('display_name', $sponsorDisplayNames[(int) $sponsor->id] ?? $sponsor->name);
                return $sponsor;
            })
            ->values();

        $maxAdditionalById = $sponsors
            ->mapWithKeys(function ($sponsor) {
                return [$sponsor->id => $this->maxAdditionalFromSponsorId((int) $sponsor->id) ?? 0];
            })
            ->all();

        $bookedSlots = SponsorInterviewSchedule::query()
            ->pluck('preferred_time_slot')
            ->all();

        return view('interview_schedule.sponsor', [
            'sponsors' => $sponsors,
            'timeSlots' => $this->timeSlots(),
            'questions' => $this->questions(),
            'bookedSlots' => $bookedSlots,
            'maxAdditionalById' => $maxAdditionalById,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('sponsors', 'id')],
            'pic_name' => 'required|string|max:255',
            'pic_email' => 'required|email|max:255',
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

        $maxAdditional = $this->maxAdditionalFromSponsorId((int) $sponsor->id);
        if ($maxAdditional === null) {
            return back()->withErrors(['company_id' => 'Company sponsor tidak termasuk daftar interview IM 2026.'])->withInput();
        }

        $selectedQuestions = collect($validated['selected_questions'])
            ->map(fn($q) => (int) $q)
            ->unique()
            ->sort()
            ->values();

        if (!$selectedQuestions->contains(1) || !$selectedQuestions->contains(2)) {
            return back()->withErrors([
                'selected_questions' => 'Question nomor 1 dan 2 wajib dipilih.',
            ])->withInput();
        }

        $additionalCount = $selectedQuestions
            ->reject(fn($q) => in_array($q, [1, 2], true))
            ->count();

        if ($additionalCount > $maxAdditional) {
            return back()->withErrors([
                'selected_questions' => "Sponsor ini maksimal memilih {$maxAdditional} pertanyaan tambahan.",
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

        $package = strtolower((string) $sponsor->package);

        try {
            $saved = SponsorInterviewSchedule::create([
                'sponsor_id' => $sponsor->id,
                'company_name' => $sponsor->name,
                'sponsor_package' => $package,
                'pic_name' => $validated['pic_name'],
                'pic_email' => $validated['pic_email'],
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
        $this->sendInterviewScheduleConfirmationEmail($saved, $selectedQuestions->all());

        return redirect()
            ->route('sponsor.interview-schedule.create')
            ->with('success', 'Thank you. We have successfully received your submission. A summary of your responses has been sent to your email.');
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
            2 => 'Where can visitors find your booth, and what can they expect to see, discuss, or experience when they visit?',
            3 => 'What key products or solutions are you showcasing at this event?',
            4 => 'What\'s something new about your product or solution?',
            5 => 'Why is this solution relevant for the industry today?',
            6 => 'What trends or challenges are you currently seeing in the mining industry?',
            7 => 'How is your company adapting to new developments and demands in the market?',
            8 => 'What makes your products or services unique in the mining sector?',
            9 => 'Why did you decide to participate in Indonesia Miner 2026?',
            10 => 'What made this event relevant for your company?',
            11 => 'How do you see partnerships and collaborations shaping the future of the mining industry?',
        ];
    }

    private function sponsorIdLimits(): array
    {
        return [
            // Gold (max additional questions: 5)
            4 => 3,   // MMD Mining Machinery Indonesia, PT
            8 => 3,   // Weir Minerals Indonesia, PT
            11 => 3,  // Suprabakti Mandiri, PT
            10 => 3,  // McLanahan Corporation Pty Ltd
            43 => 3,  // PT Teknokraftindo Asia
            73 => 3,  // Puncakbaru Jayatama, PT

            // Silver (max additional questions: 3)
            22 => 1,  // Diamond Hire Group
            26 => 1,  // Hexindo Adiperkasa Tbk, PT
            65 => 1,  // Herrenknecht Tunnelling Systems Indonesia, PT
            39 => 1,  // Valenza Engineering Asia
            47 => 1,  // Deswik Mining Consultant (Australia) Pty Ltd
            76 => 1,  // Johnson Screens
        ];
    }

    private function sponsorDisplayNames(): array
    {
        return [
            4 => 'MMD Mining Machinery Indonesia',
            8 => 'Weir Minerals',
            11 => 'PT Suprabakti Mandiri',
            10 => 'Mclanahan',
            43 => 'PT Teknokraftindo Asia',
            73 => 'PT Puncakbaru Jayatama',
            22 => 'Diamond Hire Group',
            26 => 'PT Hexindo Adiperkasa Tbk',
            65 => 'PT Herrenknecht Tunnelling Systems Indonesia',
            39 => 'PT Valenza Engineering Asia',
            47 => 'Deswik Mining Consultant (Australia) Pty Ltd',
            76 => 'Johnson Screens',
        ];
    }

    private function maxAdditionalFromSponsorId(int $sponsorId): ?int
    {
        $limits = $this->sponsorIdLimits();
        return $limits[$sponsorId] ?? null;
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
                . "PIC Name: {$schedule->pic_name}\n"
                . "PIC Email: {$schedule->pic_email}\n"
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

    private function sendInterviewScheduleConfirmationEmail(SponsorInterviewSchedule $schedule, array $selectedQuestions): void
    {
        try {
            $questionMap = $this->questions();

            $questionDetails = collect($selectedQuestions)
                ->map(function ($no) use ($questionMap) {
                    return [
                        'no' => (int) $no,
                        'text' => $questionMap[(int) $no] ?? '',
                    ];
                })
                ->values()
                ->all();

            $send = new EmailSender();
            $send->subject = 'Djakarta Mining Club - Indonesia Miner 2026 Interview Confirmation';
            $send->template = 'email.interview-schedule-confirmation';
            $send->data = [
                'schedule' => $schedule,
                'questionDetails' => $questionDetails,
            ];
            $send->name = (string) $schedule->pic_name;
            $send->from = env('EMAIL_SENDER');
            $send->name_sender = env('EMAIL_NAME');
            $send->to = (string) $schedule->pic_email;
            $send->cc = 'secretariat@djakarta-miningclub.com';
            $send->sendEmail();
        } catch (\Throwable $e) {
            Log::warning('Failed sending sponsor interview schedule confirmation email: ' . $e->getMessage());
        }
    }
}
