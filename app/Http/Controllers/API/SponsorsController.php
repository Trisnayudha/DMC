<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorAddress;
use App\Models\Sponsors\SponsorAdvertising;
use App\Models\Sponsors\SponsorPhotoVideo;
use App\Models\Sponsors\SponsorRepresentative;
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
        // Cek apakah founded bukan null sebelum melakukan konversi
        if ($sponsor->founded !== null) {
            // Konversi founded ke timestamp Unix jika diperlukan
            if (!is_numeric($sponsor->founded)) {
                $sponsor->founded = strtotime($sponsor->founded);
            }
            // Format founded menjadi tahun saja
            $sponsor->founded = date('Y', $sponsor->founded);
        }
        $location = SponsorAddress::where('sponsor_id', $sponsor->id)->get();
        $representative = SponsorRepresentative::where('sponsor_id', $sponsor->id)->get();
        $advertising = SponsorAdvertising::where('sponsor_id', $sponsor->id)->get();
        // Menggunakan transform untuk memodifikasi data
        $advertising->transform(function ($item) {
            // Menggunakan accessor untuk memformat ukuran file dan tanggal
            $item->fileSize = $item->formatted_file_size;
            // $item->date = $item->formatted_date; // Jika Anda memiliki accessor untuk tanggal
            return $item;
        });
        $photosvideos = SponsorPhotoVideo::where('sponsor_id', $sponsor->id)->paginate(10);

        $data = [
            'detail' => $sponsor,
            'location' => $location,
            'representative' => $representative,
            'advertising' => $advertising,
            'photosvideos' => $photosvideos
        ];
        $response['status'] = 200;
        $response['message'] = 'Successfully show sponsor detail';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
