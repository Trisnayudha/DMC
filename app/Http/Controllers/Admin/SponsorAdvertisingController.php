<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorAdvertising;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:20480', // Maksimal 20MB
        ]);

        $advertising = new SponsorAdvertising();
        $advertising->name = $request->name; // Ini akan menjadi 'title'
        $advertising->sponsor_id = $request->sponsor_id;
        $advertising->date = now();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $timestamp = now()->timestamp;
            $fileName = $timestamp . '.' . $file->getClientOriginalExtension();

            // Simpan file PDF
            $filePath = $file->storeAs('public/sponsor/advertising', $fileName);
            $fileUrl = asset('storage/sponsor/advertising/' . $fileName);

            // Dapatkan ukuran file
            $advertising->file_size = $file->getSize();

            // Path lengkap file PDF
            $pdfPath = storage_path('app/public/sponsor/advertising/' . $fileName);

            // Menghasilkan gambar dari halaman pertama PDF menggunakan Imagick
            try {
                $imageName = $timestamp . '.jpg';
                $imagePath = storage_path('app/public/sponsor/advertising/thumbnails/' . $imageName);

                // Pastikan direktori thumbnails ada
                if (!file_exists(dirname($imagePath))) {
                    mkdir(dirname($imagePath), 0755, true);
                }

                $imagick = new \Imagick();
                $imagick->setResolution(150, 150); // Resolusi dapat disesuaikan
                $imagick->readImage($pdfPath . '[0]'); // Membaca halaman pertama
                $imagick->setImageFormat('jpeg');

                // Opsional: Atur kualitas gambar (0-100)
                $imagick->setImageCompressionQuality(90);

                $imagick->writeImage($imagePath);
                $imagick->clear();
                $imagick->destroy();

                // Simpan URL gambar
                $advertising->image = asset('storage/sponsor/advertising/thumbnails/' . $imageName);
            } catch (\Exception $e) {
                // Penanganan jika terjadi error
                Log::error('Error generating image from PDF: ' . $e->getMessage());
                $advertising->image = null;
            }
        } else {
            return redirect()->back()->with('error', 'File is required');
        }

        // Simpan link untuk download
        $advertising->link = $fileUrl;
        $advertising->save();

        return redirect()->back()->with('success', 'Success add Advertising');
    }

    public function destroy($id)
    {
        try {
            $advertising = SponsorAdvertising::findOrFail($id);

            // Hapus file PDF jika ada
            if ($advertising->link) {
                $pdfPath = str_replace(asset('storage'), 'public', $advertising->link);
                Storage::delete($pdfPath);
            }

            // Hapus gambar thumbnail jika ada
            if ($advertising->image) {
                $imagePath = str_replace(asset('storage'), 'public', $advertising->image);
                Storage::delete($imagePath);
            }

            // Hapus data dari database
            $advertising->delete();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error deleting data: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data.'], 500);
        }
    }
}
