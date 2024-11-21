<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sponsors\SponsorPhotoVideo;

class SponsorPhotosVideosActivityController extends Controller
{
    public function show($id)
    {
        $data['sponsor_id'] = $id;
        $data['data'] = SponsorPhotoVideo::where('sponsor_id', $id)->orderBy('id', 'desc')->get();
        return view('admin.sponsor-photos-videos-activity.index', $data);
    }

    public function create(Request $request)
    {
        $sponsor_id = $request->sponsor_id;
        return view('admin.sponsor-photos-videos-activity.create', compact('sponsor_id'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'sponsor_id' => 'required|exists:sponsors,id',
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,avi,mkv|max:20480', // Validasi format file
            'description' => 'nullable|string',
        ]);

        // Inisialisasi variabel untuk file
        $fileName = null;
        $fileUrl = null;
        $type = null;

        // Cek apakah ada file yang diunggah
        if ($request->hasFile('file')) {
            $timestamp = now()->timestamp; // Timestamp untuk penamaan unik
            $extension = $request->file('file')->getClientOriginalExtension(); // Dapatkan ekstensi file
            $fileName = $timestamp . '.' . $extension; // Gabungkan timestamp dan ekstensi untuk nama file
            $filePath = $request->file('file')->storeAs('public/sponsor/advertising', $fileName); // Simpan file

            // Dapatkan URL file untuk disimpan di database
            $fileUrl = asset('storage/sponsor/advertising/' . $fileName);

            // Tentukan tipe file (photo/video) berdasarkan ekstensi
            $type = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'photo' : 'video';
        }
        // Simpan data ke database
        SponsorPhotoVideo::create([
            'sponsor_id' => $request->sponsor_id,
            'type' => $type,
            'path' => $fileUrl, // URL file untuk akses publik
            'description' => $request->description,
        ]);

        // Redirect ke halaman dengan pesan sukses
        return redirect()
            ->route('photos-videos-activity.show', $request->sponsor_id)
            ->with('success', 'Media uploaded successfully.');
    }
}
