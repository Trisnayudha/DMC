<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function index()
    {
        return view('FormScholarship.index');
    }

    public function store(Request $request)
    {
        //
    }
}
