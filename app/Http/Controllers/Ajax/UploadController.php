<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function image(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:3072'], // 3MB
        ]);
        $url = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($image->isValid()) {
                // pastikan folder & symlink sudah benar: php artisan storage:link
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/news', $imageName);  // -> storage/app/public/news/{file}
                $url = url('/storage/news/' . $imageName); // URL publik
            } else {
                return back()
                    ->withErrors(['image' => 'File upload tidak valid. Coba pilih file lain.'])
                    ->withInput();
            }
        }

        return response()->json(['url' => $url]);
    }
}
