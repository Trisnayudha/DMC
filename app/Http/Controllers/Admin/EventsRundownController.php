<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsRundown;
use App\Models\Events\EventsSpeakers;
use App\Models\Events\EventsSpeakersRundown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventsRundownController extends Controller
{
    public function index()
    {
        $list = EventsRundown::leftjoin('events', 'events.id', 'events_rundown.events_id')
            ->select('events_rundown.*', 'events.name as event_name')->orderby('id', 'desc')->get();
        $event = Events::orderby('id', 'desc')->get();
        $speakers = EventsSpeakers::orderby('id', 'desc')->get();
        $data  = [
            'list' => $list,
            'event' => $event,
            'speakers' => $speakers
        ];
        return view('admin.events-rundown.index', $data);
    }

    public function store(Request $request)
    {
        $eventsRundownId = $request->input('id');
        $speakerIds = $request->input('speakers', []); // Default to an empty array if 'speakers' is not provided

        // Cari record berdasarkan ID
        $eventsRundown = EventsRundown::find($eventsRundownId);

        if ($eventsRundown) {
            // Jika ditemukan, update record
            $eventsRundown->name = $request->input('name');
            $eventsRundown->date = $request->input('date');
            $eventsRundown->events_id = $request->input('events_id');
        } else {
            // Jika tidak ditemukan, buat record baru
            $eventsRundown = new EventsRundown();
            $eventsRundown->name = $request->input('name');
            $eventsRundown->date = $request->input('date');
            $eventsRundown->events_id = $request->input('events_id');
        }

        // Simpan perubahan
        $eventsRundown->save();

        // Update atau insert the associated speakers
        $eventsRundown->speakers()->sync($speakerIds);

        // Mengembalikan response JSON
        return response()->json($eventsRundown);
    }



    public function edit($id)
    {
        // Menggunakan alias yang unik untuk tabel join
        $data = EventsRundown::join('events_speakers_rundown as esr', 'events_rundown.id', '=', 'esr.events_rundown_id')
            ->where('events_rundown.id', $id) // Menambahkan kondisi untuk memastikan ID yang benar
            ->select('events_rundown.*', 'events_rundown.id as id_rundown', 'esr.*') // Menyertakan kolom yang diinginkan dari kedua tabel
            ->first(); // Menggunakan first() karena find() tidak bekerja dengan join

        if (!$data) {
            // Handle the case where the record with the given id is not found
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Assuming you have a resource or transformer to format the data
        // If not, you can return the $data directly

        // For example, if you have a WhatsappSenderResource:
        // return new WhatsappSenderResource($data);

        // Or just return the data directly
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = EventsRundown::find($id)->delete();
        return response()->json($data);
    }
}
