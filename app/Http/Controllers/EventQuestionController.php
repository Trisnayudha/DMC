<?php

namespace App\Http\Controllers;

use App\Models\EventQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventQuestionController extends Controller
{
    public const EVENT_SLUG = 'indonesian-energy-market-briefing-2026';

    public function create(Request $request)
    {
        $code = $request->query('code_payment');
        $validCode = is_string($code) && $code !== '' && strlen($code) <= 255 && $this->findParticipant($code) !== null;

        return response()->view('event_questions.create', compact('code', 'validCode'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code_payment' => 'required|string|max:255',
            'question' => 'required|string|max:3000',
        ]);
        $participant = $this->findParticipant($data['code_payment']);
        if (!$participant) {
            return back()->withErrors(['code_payment' => 'Your registration link is invalid. Please use the link in your reminder email.'])->withInput();
        }
        EventQuestion::create([
            'event_slug' => self::EVENT_SLUG,
            'code_payment' => $data['code_payment'],
            'name' => $participant->name,
            'email' => $participant->email,
            'company' => $participant->company_name,
            'question' => $data['question'],
        ]);

        return redirect()->route('event-questions.create')->with('success', true);
    }

    private function findParticipant(string $code)
    {
        return DB::table('payment')
            ->join('events', 'events.id', '=', 'payment.events_id')
            ->join('users', 'users.id', '=', 'payment.member_id')
            ->leftJoin('company', 'company.users_id', '=', 'users.id')
            ->where('events.slug', self::EVENT_SLUG)
            ->where('payment.code_payment', $code)
            ->select('users.name', 'users.email', 'company.company_name')
            ->first();
    }
}
