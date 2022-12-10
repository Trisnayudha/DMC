<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Videos\Videos;
use Illuminate\Http\Request;

class VideosContorller extends Controller
{
    public function index()
    {
        $list = Videos::orderBy('id', 'desc')->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $list;
        return response()->json($response);
    }
}
