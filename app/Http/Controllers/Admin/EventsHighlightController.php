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
            ->select('events_highlight.id as id', 'events_highlight.image', 'events_highlight.sort', 'events.name')->get();
        $events = Events::orderBy('id', 'desc')->get();
        $data = [
            'list' => $list,
            'events' => $events,
        ];
        return view('admin.events-highlight.index', $data);
    }

    public function store(Request $request)
    {
        if ($request->hasFile('image')) {

            $images = $request->file('image');
            $event_id = $request->events_id;

            // Ambil sort terakhir berdasarkan event
            $lastSort = EventsHighlight::where('events_id', $event_id)->max('sort');
            $nextSort = $lastSort ? $lastSort + 1 : 1;

            foreach ($images as $image) {

                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/events-highlight', $imageName);

                $compressedImage = Image::make($image);
                $compressedImage->resize(1400, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $compressedImage->save(storage_path('app/public/events-highlight/' . $imageName));

                EventsHighlight::create([
                    'events_id' => $event_id,
                    'image' => '/storage/events-highlight/' . $imageName,
                    'sort' => $nextSort++
                ]);
            }

            return redirect()->back();
        }
    }

    public function updateSort(Request $request)
    {
        EventsHighlight::where('id', $request->id)
            ->update(['sort' => $request->sort]);

        return response()->json(['success' => true]);
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
