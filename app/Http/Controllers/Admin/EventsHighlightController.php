<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsHighlight;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventsHighlightController extends Controller
{
    public function index()
    {
        $list = EventsHighlight::join('events', 'events.id', 'events_highlight.events_id')->orderBy('events_highlight.id', 'desc')
            ->select('events_highlight.id as id', 'events_highlight.image', 'events.name')->get();
        $events = Events::orderBy('id', 'desc')->get();
        $data = [
            'list' => $list,
            'events' => $events,
        ];
        return view('admin.events-highlight.index', $data);
    }

    public function store(Request $request)
    {
        // Proses upload gambar dan kompresi
        if ($request->hasFile('image')) {
            $images = $request->file('image');
            $event_id = $request->events_id;
            foreach ($images as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/events-highlight', $imageName);
                // Kompresi gambar dengan library Intervention
                $compressedImage = Image::make($image);
                $compressedImage->resize(1400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $compressedImage->save(storage_path('app/public/events-highlight/' . $imageName));

                // Simpan data ke database
                $data = EventsHighlight::create(
                    [
                        'events_id' => $event_id,
                        'image' => '/storage/events-highlight/' . $imageName,
                    ]
                );
            }
            return redirect()->back();
        }
    }

    public function destroy(Request $request)
    {
        $data = EventsHighlight::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json([
            'success' => true,
            'id' => $request->id
        ]);
    }
}
