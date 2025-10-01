<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class EditorUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // max 5MB
        ]);

        $file = $request->file('file');

        // Resize maksimal lebar 1600px, jaga rasio, auto orientasi
        $img = Image::make($file)
            ->orientate()
            ->resize(1600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80); // simpan ke webp dengan kualitas 80

        $path = 'editor/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($path, (string) $img);

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }

    // Opsional: hapus file kalau user delete gambar dari Summernote
    public function delete(Request $request)
    {
        $src = $request->input('src');
        if (!$src) {
            return response()->json(['message' => 'No src provided'], 400);
        }

        // Hilangkan "/storage/" dari path URL
        $path = str_replace('/storage/', '', parse_url($src, PHP_URL_PATH));
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['ok' => true]);
    }
}
