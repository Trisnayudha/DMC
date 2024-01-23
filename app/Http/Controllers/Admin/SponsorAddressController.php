<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorAddressController extends Controller
{

    public function show($id)
    {
        $jsonResponse = $this->getCountry();
        $countryData = json_decode($jsonResponse->getContent(), true);


        $data['country'] = $countryData;
        $data['data'] = SponsorAddress::where('sponsor_id', $id)->orderBy('id', 'desc')->get();
        return view('admin.sponsor-address.sponsor', $data);
    }

    private function getCountry()
    {
        $jsonFileUrl = public_path('country-flag.json');

        if (file_exists($jsonFileUrl)) {
            $jsonContents = file_get_contents($jsonFileUrl);
            $jsonData = json_decode($jsonContents);

            // Sekarang Anda memiliki data JSON dalam bentuk array atau objek yang bisa digunakan sesuai kebutuhan.

            // Contoh: Mengembalikan data JSON sebagai respons
            return response()->json($jsonData);
        } else {
            // Handle jika file JSON tidak ditemukan
            return response()->json(['error' => 'File JSON tidak ditemukan'], 404);
        }
    }
}
