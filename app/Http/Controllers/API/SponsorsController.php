<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorAddress;
use App\Models\Sponsors\SponsorAdvertising;
use App\Models\Sponsors\SponsorPhotoVideo;
use App\Models\Sponsors\SponsorRepresentative;
use App\Models\Sponsors\SponsorsInquiry;
use App\Services\Sponsors\SponsorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $advertising = SponsorAdvertising::where('sponsor_id', $sponsor->id)->orderby('date', 'desc')->get();
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

    public function sentInquiry(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'representative_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            $response['status'] = 422;
            $response['message'] = 'Validation Error';
            $response['payload'] = $validator->errors();

            return response()->json($response, 422);
        }

        // Mendapatkan user_id dari user yang sedang login
        $users_id = auth('sanctum')->user()->id;
        $representative_id = $request->representative_id;
        $message = $request->message;

        // Simpan data ke database
        $inquiry = new SponsorsInquiry();
        $inquiry->sponsors_representative_id = $representative_id;
        $inquiry->users_id = $users_id;
        $inquiry->message = $message;
        $inquiry->save();

        // Siapkan response dengan pesan marketing yang ringkas
        $response['status'] = 200;
        $response['message'] = "Your inquiry has been successfully received! We'll be in touch soon with offers tailored just for you.";
        $response['payload'] = $inquiry;

        return response()->json($response, 200);
    }
}
