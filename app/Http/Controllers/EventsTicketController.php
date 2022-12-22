<?php

namespace App\Http\Controllers;

use App\Models\Events\Events;
use App\Models\Events\EventsTicket;
use Illuminate\Http\Request;

class EventsTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = EventsTicket::orderBy('id', 'desc')->get();
        $events = Events::orderBy('id', 'desc')->get();
        // dd($list);
        $data = [
            'list' => $list,
            'events' => $events,
        ];
        return view('admin.events-ticket.index', $data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data   =   EventsTicket::updateOrCreate(
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
        $data  = EventsTicket::where($where)->first();
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
        $data = EventsTicket::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }
}
