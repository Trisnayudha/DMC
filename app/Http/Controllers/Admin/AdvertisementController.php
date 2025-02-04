<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement\AdvertisementModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function index()
    {
        $data = AdvertisementModel::orderBy('id', 'desc')->get();
        return view('admin.advertisement.index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        // Validasi data yang diterima dari request
        $request->validate([
            'link' => 'required|url',
            'type' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Ubah jenis file dan ukuran sesuai kebutuhan Anda
        ]);

        // Simpan data iklan ke dalam database
        $advertisement = new AdvertisementModel();
        $advertisement->link = $request->link;
        $advertisement->type = $request->type;

        // Unggah dan simpan gambar iklan
        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp; // Mengambil timestamp saat ini
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension(); // Nama gambar menjadi timestamp.extensi
            $imagePath = $request->file('image')->storeAs('public/advertisement', $imageName); // Simpan gambar ke dalam direktori penyimpanan advertisement dengan nama timestamp
            $imageUrl = 'storage/advertisement/' . $imageName; // Buat URL penyimpanan gambar
            $advertisement->image = $imageUrl; // Simpan URL gambar dalam atribut 'image'
        }

        $advertisement->save();

        // Redirect atau berikan respons sesuai kebutuhan Anda
        return redirect()->route('advertisement.index');
    }


    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        $advertisement = AdvertisementModel::find($id);

        if (!$advertisement) {
            return response()->json(['message' => 'Iklan tidak ditemukan.'], 404);
        }

        // Hapus gambar terkait dari penyimpanan jika ada
        if ($advertisement->image) {
            Storage::delete('public/advertisement/' . basename($advertisement->image));
        }

        $advertisement->delete();

        return response()->json(['message' => 'Iklan berhasil dihapus.']);
    }
}
