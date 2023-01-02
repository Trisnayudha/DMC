<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Faq\FaqModel;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $all = FaqModel::get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $all;
        return response()->json($response);
    }
}
