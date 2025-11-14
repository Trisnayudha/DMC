<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $allowedPresentations = [
            'Spotlight on US Tariffs: Impact on Global Coal Supply and Demand',
            'Choosing the Right Coal Index and Managing Risk',
            'Chinese Coal Policy: Impact on Supply and Demand',
            'Met Coal Challenges and Opportunities for Indonesia',
            'An Introduction to Minespans',
        ];
        $data = $request->validate(
            [
                'email'                         => 'required|email',
                'informative_score'             => 'required|integer|min:1|max:5',
                'most_relevant_presentations'   => 'required|array|min:1',
                'most_relevant_presentations.*' => ['string', Rule::in($allowedPresentations)],
                'is_member'                     => 'required|in:0,1',
                'wants_more_info'               => 'required|in:0,1',
                'feedback'                      => 'required|string',
                'topics_2026'                   => 'required|string',
            ],
            [
                'most_relevant_presentations.required' => 'Please select at least one option.',
                'most_relevant_presentations.*.in'     => 'Invalid selection.',
            ]
        );

        // normalisasi tipe untuk boolean
        $data['is_member'] = (int) $data['is_member'];
        $data['wants_more_info'] = (int) $data['wants_more_info'];

        // metadata
        $data['ip'] = $request->ip();
        $data['ua'] = substr($request->userAgent() ?? '', 0, 255);

        SurveyResponse::create($data);

        return back()->with('ok', true)->withInput([]);
    }
}
