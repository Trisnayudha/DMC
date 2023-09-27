<?php

namespace App\Http\Controllers;

use App\Models\Events\Events;
use App\Models\Sponsors\EventSponsors;
use App\Models\Sponsors\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventsRegisterSponsorController extends Controller
{
    public function index()
    {
        //
        $sponsors = Sponsor::where('status', '=', 'publish')->orderby('id', 'desc')->get();
        $events = Events::orderby('id', 'desc')->get();
        $list = EventSponsors::leftjoin('sponsors', 'sponsors.id', 'event_sponsors.sponsors_id')
            ->leftjoin('events', 'events.id', 'event_sponsors.events_id')->orderby('event_sponsors.id', 'desc')
            ->select(
                'event_sponsors.id',
                'events.slug',
                'sponsors.name as sponsor_name',
                'events.name as events_name',
                'event_sponsors.code_access',
                'event_sponsors.count'
            )->get();
        $data = [
            'sponsor' => $sponsors,
            'events' => $events,
            'list' => $list
        ];

        return view('admin.events-sponsors.index', $data);
    }
    public function store(Request $request)
    {
        $save =  new EventSponsors();
        $codePayment = strtoupper(Str::random(25));
        $save->sponsors_id = $request->sponsor_id;
        $save->events_id = $request->events_id;
        $save->code_access = $codePayment;
        $save->save();
        return response()->json(['success' => true]);
    }

    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $data  = EventSponsors::where($where)->first();
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
        $data = EventSponsors::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }
}
