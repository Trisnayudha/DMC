<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsCategory;
use Illuminate\Http\Request;

class EventsCategoryController extends Controller
{
    public function index()
    {
        $list = EventsCategory::orderBy('id', 'desc')->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $list;
        return response()->json($response);
    }
}
