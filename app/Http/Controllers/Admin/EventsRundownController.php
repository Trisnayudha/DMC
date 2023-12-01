<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsRundown;
use App\Models\Events\EventsSpeakers;
use App\Models\Events\EventsSpeakersRundown;
use Illuminate\Http\Request;

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

        // Use updateOrCreate to insert a new record or update an existing one for EventsRundown
        $eventsRundown = EventsRundown::updateOrCreate(
            [
                'id' => $eventsRundownId,
                // Add other unique keys as needed
            ],
            [
                'name' => $request->input('name'),
                'date' => $request->input('date'),
                'events_id' => $request->input('events_id')
                // Add other fields as needed
            ]
        );

        // Update or insert the associated speakers
        $eventsRundown->speakers()->sync($speakerIds);
        // Assuming you want to return the newly created or updated record as a JSON response
        return response()->json($eventsRundown);
    }

    public function edit($id)
    {
        $data = EventsRundown::with('speakers')
            ->join('events_speakers_rundown as esr', 'events_rundown.id', '=', 'esr.events_rundown_id')
            ->find($id);


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
