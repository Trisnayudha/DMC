<?php

namespace App\Http\Controllers;

use App\Services\MemberSurveyDraftService;
use Illuminate\Http\Request;

/**
 * Autosave endpoint for the member survey: backs up an unfinished survey
 * to the database while the respondent is still filling it.
 */
class MemberSurveyDraftController extends Controller
{
    public function store(Request $request, MemberSurveyDraftService $drafts)
    {
        $token = $drafts->tokenFrom($request);
        if (!$token) {
            return response()->noContent();
        }

        $draft = $drafts->save($token, $request->validate($drafts->rules()), $request);

        return response()->json([
            'saved' => (bool) $draft,
            'last_step' => $draft ? $draft->last_step : null,
        ]);
    }
}
