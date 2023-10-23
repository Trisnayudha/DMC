<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsConference;
use Illuminate\Http\Request;

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
        $data   =   EventsConference::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'events_id' => $request->events_id,
                'title' => $request->title,
                'price_rupiah' => $request->price_rupiah,
                'price_dollar' => $request->price_dollar,
                'status_ticket' => $request->status_ticket,
                'description' => $request->description,
                'status_sold' => $request->status_sold,
                'type' => $request->type,
            ]
        );
        // activity()->log('Menambahkan Data Kategori');
        return response()->json(['success' => true]);
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
