<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sponsors\SponsorPhotoVideo;
use Illuminate\Support\Facades\Storage;

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
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $item = SponsorPhotoVideo::findOrFail($id);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480',
        ]);

        $item = SponsorPhotoVideo::findOrFail($id);

        if ($request->hasFile('file')) {
            // delete old file if stored (convert URL to storage path)
            if ($item->path) {
                $oldRelative = str_replace(asset('storage') . '/', '', $item->path);
                Storage::disk('public')->delete($oldRelative);
            }
            $file = $request->file('file');
            $timestamp = now()->timestamp;
            $extension = $file->getClientOriginalExtension();
            $fileName = $timestamp . '.' . $extension;
            // store in sponsor/advertising
            $file->storeAs('public/sponsor/advertising', $fileName);
            // build full URL
            $fileUrl = asset('storage/sponsor/advertising/' . $fileName);
            $item->path = $fileUrl;
            // set type
            $item->type = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']) ? 'photo' : 'video';
        }

        $item->save();

        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $item = SponsorPhotoVideo::findOrFail($id);
        // delete stored file
        if ($item->path) {
            Storage::disk('public')->delete($item->path);
        }
        $item->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
