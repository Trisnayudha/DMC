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
        $participant = null;
        $code = $request->query('code_payment');
        if (is_string($code) && $code !== '' && strlen($code) <= 255) {
            $participant = DB::table('payment')
                ->join('events', 'events.id', '=', 'payment.events_id')
                ->join('users', 'users.id', '=', 'payment.member_id')
                ->leftJoin('company', 'company.users_id', '=', 'users.id')
                ->where('events.slug', self::EVENT_SLUG)
                ->where('payment.code_payment', $code)
                ->select('users.name', 'users.email', 'company.company_name')
                ->first();
        }

        return response()->view('event_questions.create', compact('participant'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'question' => 'required|string|max:3000',
        ]);
        $data['event_slug'] = self::EVENT_SLUG;
        EventQuestion::create($data);

        return redirect()->route('event-questions.create')->with('success', true);
    }
}
