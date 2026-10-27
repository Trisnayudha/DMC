<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorAddress;
use App\Services\Sponsors\SponsorAddressGeocoder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SponsorAddressController extends Controller
{
    private $geocoder;

    public function __construct(SponsorAddressGeocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    public function show($id)
    {
        $data['country'] = $this->geocoder->countries();
        $data['sponsor_id'] = $id;
        $data['data'] = SponsorAddress::where('sponsor_id', $id)->orderBy('id', 'desc')->get();
        return view('admin.sponsor-address.sponsor', $data);
    }

    // Dipanggil dari modal: address → lat, lng, country, link gmaps
    public function geocode(Request $request)
    {
        $request->validate(['address' => 'required|string|max:1000']);

        return response()->json($this->geocoder->geocode($request->address));
    }

    public function store(Request $request)
    {
        $this->validateAddress($request);

        $address = new SponsorAddress();
        $address->sponsor_id = $request->sponsor_id;
        $this->fillAddress($address, $request);
        $address->save();

        return redirect()->back()->with('success', 'Successfully added address.');
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
        $address = SponsorAddress::find($id);
        if (!$address) {
            return redirect()->back()->with('error', 'Address not found.');
        }

        $this->validateAddress($request);
        $this->fillAddress($address, $request);
        $address->save();

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

    private function validateAddress(Request $request)
    {
        $request->validate([
            'address'     => 'required|string|max:1000',
            'coordinates' => ['nullable', 'regex:/^\s*-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?\s*$/'],
            'country'     => 'nullable|string',
        ], [
            'coordinates.regex' => 'Coordinates must be "lat, lng", e.g. -6.2281, 106.8071',
        ]);
    }

    /**
     * Field yang kosong di form diisi otomatis dari address, supaya tetap
     * lengkap walaupun admin langsung klik Save tanpa "Find Location".
     */
    private function fillAddress(SponsorAddress $address, Request $request)
    {
        $addressChanged = $address->address !== $request->address;
        $address->address = $request->address;

        $lookup = null;
        if (!$request->filled('coordinates') || !$request->filled('country')) {
            $lookup = $this->geocoder->geocode($request->address);
        }

        if ($request->filled('coordinates')) {
            [$lat, $lng] = array_map('trim', explode(',', $request->coordinates));
        } else {
            $lat = $lookup['lat'] ?? 0;
            $lng = $lookup['lng'] ?? 0;
        }
        $address->lat = $lat;
        $address->lang = $lng;

        $address->country = $request->filled('country') ? $request->country : ($lookup['country'] ?? '');
        $address->image_country = $this->geocoder->flagFor($address->country);

        // Website menampilkan bendera, jadi country wajib ketemu
        if (!$address->image_country) {
            throw ValidationException::withMessages([
                'country' => 'Country could not be detected from the address, please select it manually.',
            ]);
        }

        // Link embed lama (hasil copy manual dari Google Maps) dipertahankan selama address tidak berubah
        if ($addressChanged || !$address->link_gmaps) {
            $address->link_gmaps = $this->geocoder->embedLink($request->address);
        }
    }
}
