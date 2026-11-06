<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventQuestion;
use Illuminate\Http\Request;

class EventQuestionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->validate(['search' => 'nullable|string|max:255'])['search'] ?? '');
        $questions = EventQuestion::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('company', 'like', '%'.$search.'%')
                        ->orWhere('question', 'like', '%'.$search.'%');
                });
            })->latest()->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.event_questions.index', compact('questions', 'search'));
    }
}
