<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;


class SurveyController extends Controller
{
    // Tampilkan form (pakai resources/views/index.blade.php)
    public function index()
    {
        $presentations = [
            'Spotlight on US Tariffs: Impact on Global Coal Supply and Demand',
            'Choosing the Right Coal Index and Managing Risk',
            'Chinese Coal Policy: Impact on Supply and Demand',
            'Met Coal: Challenges and Opportunities for Indonesia',
        ];

        return view('survey.index', compact('presentations'));
    }

    // Terima submit form dan simpan ke DB
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'informative_score' => 'required|integer|min:1|max:5',
            'most_relevant_presentation' => 'required|string',
            'is_member' => 'nullable|in:0,1',
            'wants_more_info' => 'nullable|in:0,1',
            'feedback' => 'nullable|string',
            'topics_2026' => 'nullable|string',
            'consent' => 'accepted',
        ]);

        $data['ip'] = $request->ip();
        $data['ua'] = substr($request->userAgent() ?? '', 0, 255);
        $data['consent'] = 1;

        SurveyResponse::create($data);

        // kembali ke halaman yang sama dengan alert sukses
        return back()->with('ok', true)->withInput([]);
    }
}
