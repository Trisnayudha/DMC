<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsConference;
use App\Models\Events\EventsConferenceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventsConferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = EventsConference::join('events', 'events.id', 'events_conferen.events_id')->select('events.*', 'events.name as event_name', 'events_conferen.*', 'events_conferen.name as events_conference_name')->orderBy('events_conferen.id', 'desc')->get();
        // dd($list);

        $data = [
            'list' => $list,

        ];
        return view('admin.events-conference.index', $data);
    }


    public function create()
    {
        $events = Events::orderBy('id', 'desc')->get();
        $data = [
            'events' => $events,
        ];
        return view('admin.events-conference.create', $data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Simpan data utama dalam model EventsConference
        $data = EventsConference::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'events_id' => $request->events_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'date' => $request->start_date,
                'time_start' => $request->start_time,
                'time_end' => $request->end_time,
                'youtube_link' => $request->link,
                'status' => $request->status,
            ]
        );

        // Cek apakah ada file gambar yang diunggah
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Tentukan direktori penyimpanan gambar
            $imagePath = 'public/conference_images';

            // Simpan gambar ke direktori penyimpanan dengan nama yang unik
            $imagePath = $image->store($imagePath);

            // Update kolom 'image' pada model 'EventsConference'
            $data->update(['image' => $imagePath]);
        }

        // Simpan data file konferensi dalam model EventsConferenceFile
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Tentukan direktori penyimpanan file konferensi
            $storagePath = 'public/conference_files';

            // Simpan file ke direktori penyimpanan dengan nama yang unik
            $filePath = $file->store($storagePath);

            // Simpan data file konferensi dalam tabel EventsConferenceFile
            $saveConferenceFile = EventsConferenceFile::updateOrCreate(
                ['id' => $request->id],
                [
                    'events_id' => $request->events_id,
                    'events_conference_id' => $data->id,
                    'file' => $filePath, // Simpan path file yang unik
                    'name_file' => $file->getClientOriginalName(), // Simpan nama file asli
                ]
            );
        }
        // activity()->log('Menambahkan Data Kategori');
        return redirect()->route('events.conference')->with('success', 'Successfully create new event conference');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $data  = EventsConference::where($where)->first();
        // activity()->log('Edit Data Kategori');
        return response()->json($data);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = EventsConference::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }
}
