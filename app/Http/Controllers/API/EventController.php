<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $data = Events::select('id', 'start_date', 'end_date', 'start_time', 'end_time')
            ->take(5)->orderBy('start_date', 'DESC')->orderBy('start_time', 'DESC')->get();
        $fake = Events::select('id', 'name as title', 'start_date', 'start_time',  'image')
            ->take(5)->orderBy('start_date', 'DESC')->orderBy('start_time', 'DESC')->get();
        foreach ($data as $value) {
            foreach ($fake as $val) {
                $val->start_date = date('d', strtotime($value->start_date)) . ' - ' . date('d F Y', strtotime($value->end_time));
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $fake;
        return response()->json($response);
    }
}
