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
        $data = [
            'name' => $request->name,
            'company' => $request->company,
            'job_title' => $request->job_title,
        ];

        // Cek apakah file gambar diupload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();
            $dbPath = '/storage/events-speakers/' . $imageName;
            $image->storeAs('public/events-speakers', $imageName);
            $data['image'] = $dbPath;
        }

        try {
            if ($request->id != null) {
                // Ini adalah permintaan pembaruan
                $model = EventsSpeakers::find($request->id);

                if (!$model) {
                    return response()->json(['error' => 'Model not found.'], 404);
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
        $data = EventsSpeakers::find($id)->delete();
        return response()->json($data);
    }
}
