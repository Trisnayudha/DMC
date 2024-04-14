<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventsScheduleController extends Controller
{
    public function index()
    {
        $list = EventsSchedule::leftjoin('events', 'events.id', 'events_schedule.events_id')->orderby('events_schedule.sort', 'desc')->select('events_schedule.*', 'events.slug')->get();
        $events = Events::orderBy('id', 'desc')->get();
        $data = [
            'list' => $list,
            'events' => $events,
        ];
        return view('admin.events-schedule.index', $data);
    }


    public function store(Request $request)
    {
        // Validasi form jika diperlukan
        $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'date' => 'required|date',
            'type' => 'required|string',
            // Tambahkan validasi lainnya sesuai kebutuhan
        ]);

        // Jika ini adalah pembaruan, hitung ulang sort
        if ($request->has('id')) {
            // Ambil nilai sort terbesar dari tabel EventsSchedule
            $lastSort = EventsSchedule::max('sort');
        } else {
            // Ini adalah penyimpanan baru, atur sort menjadi nilai terbesar + 1
            $lastSort = EventsSchedule::max('sort') + 1;
        }

        $eventsSchedule = EventsSchedule::updateOrCreate(
            [
                'id' => $request->input('id'),
                // Sesuaikan dengan kunci unik lainnya yang diperlukan untuk updateOrCreate
            ],
            [
                'events_id' => $request->input('events_id'),
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'date' => $request->input('date'),
                'type' => $request->input('type'),
                'sort' => $lastSort ?? 1, // Atur sort menjadi nilai terbesar + 1
                'events_id' => $request->input('events_id')
                // Tambahkan field lainnya sesuai kebutuhan
            ]
        );

        // Redirect atau berikan respon sesuai kebutuhan
        return redirect()->route('events.schedule')->with('success', 'Event schedule has been saved successfully.');
    }


    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $data  = EventsSchedule::where($where)->first();
        // activity()->log('Edit Data Kategori');
        $data->date = Carbon::parse($data->date)->format('Y-m-d');
        return response()->json($data);
    }


    public function destroy(Request $request)
    {
        $data = EventsSchedule::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json([
            'success' => true,
            'id' => $request->id
        ]);
    }

    public function moveSort(Request $request)
    {
        $eventsSchedule = EventsSchedule::findOrFail($request->input('id'));
        $newSort = $request->input('new_sort');

        // Pastikan nomor urut baru valid
        if ($newSort > 0 && $newSort <= EventsSchedule::count()) {
            $eventsSchedule->update(['sort' => $newSort]);

            // Update nomor urut untuk entitas lain jika diperlukan
            EventsSchedule::where('id', '!=', $eventsSchedule->id)->where('sort', '>=', $newSort)->increment('sort');

            return redirect()->route('events.schedule')->with('success', 'Sort order updated successfully.');
        }

        return redirect()->route('events.schedule')->with('error', 'Invalid sort order.');
    }
}
