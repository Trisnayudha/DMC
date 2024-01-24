<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorAdvertising;
use Illuminate\Http\Request;

class SponsorAdvertisingController extends Controller
{
    public function show($id)
    {
        $data['sponsor_id'] = $id;
        $data['data'] = SponsorAdvertising::where('sponsor_id', $id)->orderBy('id', 'desc')->get();
        return view('admin.sponsor-advertising.sponsor', $data);
    }

    public function store(Request $request)
    {

        $save = new SponsorAdvertising();
        $save->name = $request->name;
        $save->sponsor_id = $request->sponsor_id;
        if ($request->hasFile('file')) {
            $timestamp = now()->timestamp; // Mengambil timestamp saat ini
            $fileName = $timestamp . '.' . $request->file('file')->getClientOriginalExtension(); // Nama gambar menjadi timestamp.extensi
            $filePath = $request->file('file')->storeAs('public/sponsor/advertising', $fileName); // Simpan gambar ke dalam direktori penyimpanan sponsor dengan nama timestamp
            $fileUrl = asset('storage/sponsor/advertising/' . $fileName); // Buat URL penyimpanan gambar
        } else {
            $fileName = null; // Atur menjadi null jika tidak ada gambar yang diunggah
            $fileUrl = null; // Atur menjadi null jika tidak ada gambar yang diunggah
        }
        $save->link = $fileUrl;
        $save->save();

        return redirect()->back()->with('success', 'Success add Advertising');
    }
}
