<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DmcMemberSurvey;
use Illuminate\Http\Request;

class MemberSurveyResponseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:2000|max:2100',
            'status' => 'nullable|in:' . DmcMemberSurvey::STATUS_COMPLETED . ',' . DmcMemberSurvey::STATUS_IN_PROGRESS,
        ]);
        $status = $filters['status'] ?? null;
        $search = trim($filters['search'] ?? '');
        $year = $filters['year'] ?? null;
        $responses = DmcMemberSurvey::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($year, function ($query) use ($year) { $query->whereYear('created_at', $year); })
            ->when($status, function ($query) use ($status) { $query->where('status', $status); })
            ->orderByDesc('updated_at')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        $statusCounts = app(\App\Services\MemberSurveyDraftService::class)->enabled()
            ? DmcMemberSurvey::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')
            : collect();

        return view('admin.member_survey.index', compact('responses', 'search', 'year', 'status', 'statusCounts'));
    }

    public function show(DmcMemberSurvey $memberSurvey)
    {
        return view('admin.member_survey.show', ['response' => $memberSurvey]);
    }
}
