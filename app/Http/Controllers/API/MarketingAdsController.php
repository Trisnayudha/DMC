<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Advertisement\AdvertisementModel;
use App\Models\Marketing\MarketingAds;
use Illuminate\Http\Request;

class MarketingAdsController extends Controller
{
    public function index()
    {
        $result = MarketingAds::inRandomOrder()->limit(1)->get();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $result;
        return response()->json($response);
    }

    public function advertisementSide()
    {
        $result = AdvertisementModel::inRandomOrder()->limit(1)->get();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $result;
        return response()->json($response);
    }
}
