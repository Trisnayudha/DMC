<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorRepresentative;
use Illuminate\Http\Request;

class SponsorRepresentativeController extends Controller
{
    public function show($id)
    {
        $data['sponsor_id'] = $id;
        $data['data'] = SponsorRepresentative::where('sponsor_id', $id)->orderBy('id', 'desc')->get();
        return view('admin.sponsor-representative.sponsor', $data);
    }

    public function store(Request $request)
    {

        //
        $save = new SponsorRepresentative();
        $save->name = $request->name;
        $save->job_title = $request->job_title;
        $save->instagram = $request->instagram;
        $save->linkedin = $request->linkedin;
        $save->sponsor_id = $request->sponsor_id;
        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp; // Mengambil timestamp saat ini
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension(); // Nama gambar menjadi timestamp.extensi
            $imagePath = $request->file('image')->storeAs('public/sponsor/representative', $imageName); // Simpan gambar ke dalam direktori penyimpanan sponsor dengan nama timestamp
            $imageUrl = asset('storage/sponsor/representative/' . $imageName); // Buat URL penyimpanan gambar
        } else {
            $imageName = null; // Atur menjadi null jika tidak ada gambar yang diunggah
            $imageUrl = null; // Atur menjadi null jika tidak ada gambar yang diunggah
        }
        $save->image = $imageUrl;
        $save->save();

        return redirect()->back()->with('success', 'Success Add Representative');
    }
}
