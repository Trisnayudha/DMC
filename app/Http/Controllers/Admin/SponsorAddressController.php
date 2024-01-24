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
        $data['sponsor_id'] = $id;
        $data['data'] = SponsorAddress::where('sponsor_id', $id)->orderBy('id', 'desc')->get();
        return view('admin.sponsor-address.sponsor', $data);
    }

    public function store(Request $request)
    {

        $save = new SponsorAddress();
        $save->link_gmaps = $request->link_gmaps;
        $save->address = $request->address;
        $save->country = $request->country;
        $save->sponsor_id = $request->sponsor_id;
        $searchFlagWithCountry = $this->getCountry();
        $countryData = json_decode($searchFlagWithCountry->getContent(), true);

        // Cari nilai 'flag' berdasarkan 'country'
        $flag = null;
        foreach ($countryData as $item) {
            if ($item['country'] === $request->country) {
                $flag = $item['flag'];
                break; // Keluar dari loop setelah menemukan yang cocok
            }
        }

        if ($flag) {
            // Lakukan sesuatu dengan nilai 'flag', misalnya, simpan ke database
            $save->image_country = $flag;
            $save->save();
            return redirect()->back()->with('success', 'success add address');
        } else {
            // Handle jika 'country' tidak ditemukan dalam data JSON
            return response()->json(['error' => 'Country tidak ditemukan'], 404);
        }
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
