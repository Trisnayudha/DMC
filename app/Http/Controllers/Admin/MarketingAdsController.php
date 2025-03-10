<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Marketing\MarketingAds;
use App\Models\News\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketingAdsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $list = MarketingAds::orderBy('id', 'desc')->get();
        return view('admin.marketing-ads.index', ['list' => $list]);
    }

    /**
     * Simpan data (create atau update) menggunakan base64 untuk image.
     */
    public function store(Request $request)
    {
        $data = MarketingAds::updateOrCreate(
            ['id' => $request->id],
            [
                'type'      => $request->type,
                'location'  => $request->location,
                'target_id' => $request->target_id
            ]
        );

        // Jika ada base64_image, decode dan simpan file-nya
        if ($request->has('base64_image') && !empty($request->base64_image)) {
            $base64Str = $request->base64_image;
            // Hilangkan prefix data URL jika ada (contoh: "data:image/png;base64,")
            if (strpos($base64Str, 'base64,') !== false) {
                $base64Str = substr($base64Str, strpos($base64Str, 'base64,') + 7);
            }
            $imageName = time() . '.png'; // atau sesuaikan ekstensi berdasarkan kebutuhan
            $decodedImage = base64_decode($base64Str);
            // Simpan file ke folder "ads" pada disk "public"
            Storage::disk('public')->put('ads/' . $imageName, $decodedImage);
            $data->image = '/storage/ads/' . $imageName;
            $data->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Ambil data untuk form edit.
     */
    public function edit(Request $request)
    {
        $data = MarketingAds::where('id', $request->id)->first();
        return response()->json($data);
    }

    /**
     * Hapus data.
     */
    public function destroy(Request $request)
    {
        MarketingAds::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function event()
    {
        $data = Events::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'payload' => $data
        ]);
    }

    public function news()
    {
        $data = News::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'payload' => $data
        ]);
    }
}
