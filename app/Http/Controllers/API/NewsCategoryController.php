<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News\NewsCategory;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
    public function index()
    {
        $list = NewsCategory::orderBy('id', 'desc')->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $list;
        return response()->json($response);
    }
}
