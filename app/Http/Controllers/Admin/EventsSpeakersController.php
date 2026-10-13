<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsSpeakers;
use Illuminate\Http\Request;

class EventsSpeakersController extends Controller
{
    public function index()
    {
        $list = EventsSpeakers::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list
        ];
        return view('admin.events-speakers.index', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'company'   => 'nullable|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $data = [
            'name'      => $request->name,
            'company'   => $request->company,
            'job_title' => $request->job_title,
        ];

        // Cek apakah file gambar diupload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->extension();
            $dbPath = '/storage/events-speakers/' . $imageName;
            $image->storeAs('public/events-speakers', $imageName);
            $data['image'] = $dbPath;
        }

        try {
            if ($request->filled('id')) {
                // Ini adalah permintaan pembaruan
                $model = EventsSpeakers::find($request->id);

                if (!$model) {
                    return response()->json(['error' => 'Data speaker tidak ditemukan.'], 404);
                }

                $model->update($data);
            } else {
                // Ini adalah permintaan pembuatan baru
                EventsSpeakers::create($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $data = EventsSpeakers::find($id);

        if (!$data) {
            return response()->json(['error' => 'Data speaker tidak ditemukan.'], 404);
        }

        return response()->json($data, 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    public function destroy($id)
    {
        $data = EventsSpeakers::find($id);
        if (!$data) {
            return response()->json(['error' => 'Data speaker tidak ditemukan.'], 404);
        }

        $data->delete();
        return response()->json(['success' => true]);
    }
}
