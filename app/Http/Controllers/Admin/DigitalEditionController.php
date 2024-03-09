<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalEdition\DigitalModel;
use Illuminate\Http\Request;

class DigitalEditionController extends Controller
{
    public function index()
    {
        $list = DigitalModel::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list
        ];
        return view('admin.digital-edition.index', $data);
    }

    public function store(Request $request)
    {
        $data = [
            'link' => $request->input('link'),
        ];

        // Cek apakah file gambar diupload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();
            $dbPath = '/storage/digital-edition/' . $imageName;
            $image->storeAs('public/digital-edition', $imageName);
            $data['image'] = $dbPath;
        }

        try {
            if ($request->id != null) {
                // Ini adalah permintaan pembaruan
                $model = DigitalModel::find($request->id);

                if (!$model) {
                    return response()->json(['error' => 'Model not found.'], 404);
                }

                $model->update($data);
            } else {
                // Ini adalah permintaan pembuatan baru
                // Mendapatkan nilai sort tertinggi
                $highestSort = DigitalModel::max('sort');
                // Menambahkan 1 ke nilai sort tertinggi untuk mendapatkan nilai sort yang baru
                $data['sort'] = $highestSort + 1;
                DigitalModel::create($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function edit($id)
    {
        $data = DigitalModel::find($id);

        if (!$data) {
            // Handle the case where the record with the given id is not found
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Assuming you have a resource or transformer to format the data
        // If not, you can return the $data directly

        // For example, if you have a EventsSpeakersResource:
        // return new EventsSpeakersResource($data);

        // Or just return the data directly
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = DigitalModel::find($id)->delete();
        return response()->json($data);
    }
}
