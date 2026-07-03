<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsRundown;
use App\Models\Events\EventsSpeakers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EventsRundownController extends Controller
{
    public function index()
    {
        // Eager-load speakers & event untuk tabel
        $list = EventsRundown::with(['event:id,name', 'speakers:id,name,job_title'])
            ->leftJoin('events', 'events.id', '=', 'events_rundown.events_id')
            ->select('events_rundown.*', 'events.name as event_name')
            ->orderBy('events_rundown.id', 'desc')
            ->get();

        $event = Events::orderBy('id', 'desc')->get(['id', 'name']);
        $speakers = EventsSpeakers::orderBy('id', 'desc')->get(['id', 'name', 'job_title', 'image']);

        return view('admin.events-rundown.index', [
            'list'     => $list,
            'event'    => $event,
            'speakers' => $speakers,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi basic
        $validated = $request->validate([
            'id'        => ['nullable', 'integer', 'exists:events_rundown,id'],
            'name'      => ['required', 'string', 'max:255'],
            // datetime-local => 'Y-m-d\TH:i' -> kita terima string apa adanya, nanti simpan ke datetime
            'date'      => ['required', 'date'],
            'events_id' => ['required', 'integer', Rule::exists('events', 'id')],
            'speakers'  => ['array'],
            'speakers.*' => ['integer', Rule::exists('events_speakers', 'id')],
            'moderators'  => ['array'],
            'moderators.*' => ['integer', Rule::exists('events_speakers', 'id')],
        ]);

        // Upsert rundown
        $eventsRundown = EventsRundown::find($validated['id'] ?? null) ?? new EventsRundown();
        $eventsRundown->name      = $validated['name'];
        $eventsRundown->date      = $validated['date'];      // pastikan kolom di DB tipe datetime
        $eventsRundown->events_id = $validated['events_id'];
        $eventsRundown->save();

        // Sync speakers (boleh kosong) beserta flag moderator per speaker
        $speakerIds   = $validated['speakers'] ?? [];
        $moderatorIds = $validated['moderators'] ?? [];

        $syncData = [];
        foreach ($speakerIds as $speakerId) {
            $syncData[$speakerId] = [
                'is_moderator' => in_array($speakerId, $moderatorIds) ? 1 : 0,
            ];
        }
        $eventsRundown->speakers()->sync($syncData);

        return response()->json([
            'ok' => true,
            'id' => $eventsRundown->id,
        ]);
    }

    public function edit($id)
    {
        $rundown = EventsRundown::with(['speakers:id,name,job_title,image', 'event:id,name'])
            ->find($id);

        if (!$rundown) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Format agar cocok dengan input datetime-local
        $dateForInput = '';
        if (!empty($rundown->date)) {
            try {
                $dateForInput = \Carbon\Carbon::parse($rundown->date)->format('Y-m-d\TH:i');
            } catch (\Exception $e) {
                $dateForInput = '';
            }
        }

        $moderatorIds = $rundown->speakers->filter(function ($speaker) {
            return (int) ($speaker->pivot->is_moderator ?? 0) === 1;
        })->pluck('id')->values();

        return response()->json([
            'id'            => $rundown->id,
            'name'          => $rundown->name,
            'date'          => $dateForInput, // <-- penting
            'events_id'     => $rundown->events_id,
            'speakers_ids'  => $rundown->speakers->pluck('id'),
            'moderator_ids' => $moderatorIds,
            'speakers'      => $rundown->speakers,
        ]);
    }



    public function destroy($id)
    {
        $rundown = EventsRundown::find($id);
        if (!$rundown) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Lepas pivot supaya rapi
        $rundown->speakers()->detach();
        $rundown->delete();

        return response()->json(['ok' => true]);
    }
}
