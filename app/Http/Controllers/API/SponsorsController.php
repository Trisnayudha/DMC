<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Services\Sponsors\SponsorService;

class SponsorsController extends Controller
{

    public function sponsor()
    {

        $platinum = SponsorService::getSponsorType('platinum');
        $gold = SponsorService::getSponsorType('gold');
        $silver = SponsorService::getSponsorType('silver');

        $data = [
            [
                'name' => 'PLATINUM SPONSORS',
                'type' => 'platinum',
                'data' => $platinum,
            ],
            [
                'name' => 'GOLD SPONSORS',
                'type' => 'gold',
                'data' => $gold,
            ],
            [
                'name' => 'SILVER SPONSORS',
                'type' => 'silver',
                'data' => $silver,
            ],
        ];
        $response['status'] = 200;
        $response['message'] = 'Successfully show list sponsors';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detail($slug)
    {
        $sponsor = Sponsor::where('slug', $slug)->first();

        if (!$sponsor) {
            $response['status'] = 404; // Atur status 404 Not Found
            $response['message'] = 'Sponsor not found';
            $response['payload'] = null;
            return response()->json($response);
        }

        $response['status'] = 200;
        $response['message'] = 'Successfully show sponsor detail';
        $response['payload'] = $sponsor;
        return response()->json($response);
    }
}
