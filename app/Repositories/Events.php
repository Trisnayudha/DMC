<?php

namespace App\Repositories;

use App\Models\Events\Events as EventsEvents;
use Illuminate\Support\Facades\DB;

class Events extends EventsEvents
{
    public static function listAllEventsOnlySearch($search, $limit)
    {
        $column_filter = "events.start_date";
        $type_filter = "desc";

        return EventsEvents::select(
            'events.id',
            'events.name as title',
            'events.start_date',
            'events.end_date',
            'events.start_time',
            'events.image',
        )
            ->where(function ($q) use ($search) {
                if (!empty($search)) {
                    $q->where('events.name', 'LIKE', '%' . $search . '%');
                }
            })
            ->orderby($column_filter, $type_filter)
            ->paginate($limit);
    }
}
