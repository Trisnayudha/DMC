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
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Temukan data SponsorRepresentative berdasarkan ID
        $rep = SponsorRepresentative::findOrFail($id);
        // Jika ingin menampilkan form edit di halaman tersendiri:
        // return view('admin.sponsor-representative.edit', compact('rep'));

        // Jika ingin menampilkan data json (untuk diambil via AJAX misalnya):
        return response()->json($rep);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rep = SponsorRepresentative::findOrFail($id);

        $rep->name       = $request->name;
        $rep->job_title  = $request->job_title;
        $rep->instagram  = $request->instagram;
        $rep->linkedin   = $request->linkedin;

        // Jika ada file gambar yang diupload
        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            // Simpan gambar ke folder sponsor/representative
            $request->file('image')->storeAs('public/sponsor/representative', $imageName);
            // Buat URL untuk di database
            $imageUrl = asset('storage/sponsor/representative/' . $imageName);

            // Simpan ke kolom image
            $rep->image = $imageUrl;
        }

        $rep->save();

        return redirect()->back()->with('success', 'Berhasil mengupdate data sponsor representative.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rep = SponsorRepresentative::findOrFail($id);
        $rep->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus data sponsor representative.'
        ]);
    }
}
