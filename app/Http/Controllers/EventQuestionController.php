<?php

namespace App\Http\Controllers;

use App\Models\EventQuestion;
use Illuminate\Http\Request;

class EventQuestionController extends Controller
{
    public const EVENT_SLUG = 'indonesian-energy-market-briefing-2026';

    public function create()
    {
        return view('event_questions.create');
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
