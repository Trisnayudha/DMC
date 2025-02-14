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
        $save->lat = $request->lat;
        $save->lang = $request->lang;

        // Cari flag berdasarkan country
        $countryData = json_decode($this->getCountry()->getContent(), true);
        $flag = null;
        foreach ($countryData as $item) {
            if ($item['country'] === $request->country) {
                $flag = $item['flag'];
                break;
            }
        }

        if ($flag) {
            $save->image_country = $flag;
            $save->save();
            return redirect()->back()->with('success', 'Successfully added address.');
        } else {
            return response()->json(['error' => 'Country not found'], 404);
        }
    }

    public function edit($id)
    {
        $address = SponsorAddress::find($id);
        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }
        return response()->json($address);
    }

    public function update(Request $request, $id)
    {
        $update = SponsorAddress::find($id);
        if (!$update) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        $update->link_gmaps = $request->link_gmaps;
        $update->address = $request->address;
        $update->country = $request->country;
        $update->lat = $request->lat;
        $update->lang = $request->lang;

        // Update flag berdasarkan country
        $countryData = json_decode($this->getCountry()->getContent(), true);
        foreach ($countryData as $item) {
            if ($item['country'] === $request->country) {
                $update->image_country = $item['flag'];
                break;
            }
        }

        $update->save();
        return redirect()->back()->with('success', 'Successfully updated address.');
    }

    public function destroy($id)
    {
        $delete = SponsorAddress::find($id);
        if (!$delete) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        $delete->delete();
        return response()->json(['success' => 'Successfully deleted address']);
    }

    private function getCountry()
    {
        $jsonFileUrl = public_path('country-flag-updated.json');

        if (file_exists($jsonFileUrl)) {
            $jsonContents = file_get_contents($jsonFileUrl);
            $jsonData = json_decode($jsonContents);
            return response()->json($jsonData);
        } else {
            return response()->json(['error' => 'File JSON not found'], 404);
        }
    }
}
