<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScholarshipController extends Controller
{
    public function index()
    {
        $data = [
            'list' => DB::table('scholarship_form')->orderby('id', 'desc')->get()
        ];
        // dd($data);
        return view('admin.scholarship.index', $data);
    }
}
