<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function sectionImage()
    {
        $data = [
            asset('image/1.jpeg'),
            asset('image/2.jpg'),
            asset('image/3.jpg'),
            asset('image/4.jpg'),
            asset('image/5.jpg'),
            asset('image/6.jpg')
        ];
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;

        return response()->json($response);
    }
}
