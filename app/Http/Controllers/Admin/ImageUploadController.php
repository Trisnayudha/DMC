<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120'
        ]);

        try {

            $file = $request->file('image');

            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload tidak valid.'
                ], 422);
            }

            // Folder penyimpanan
            $folder = 'public/uploads/news';

            // Nama file unik
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

            // Simpan ke storage/app/public/uploads/news
            $file->storeAs($folder, $filename);

            // URL publik (akses via storage link)
            $url = asset('storage/uploads/news/' . $filename);

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
