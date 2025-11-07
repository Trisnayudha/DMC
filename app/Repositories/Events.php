<?php

namespace App\Repositories;

use App\Models\Events\Events as EventsEvents;
use Illuminate\Support\Facades\DB;

class Events extends EventsEvents
{
    public static function listAllEventsOnlySearch($search, $limit, $type, $category, $event)
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
            'events.slug',
            'events.event_type',
            'events.location',
            'events.end_time',
            'events.status_event',
            'events.quota'
        )
            ->leftJoin('event_category_list', function ($join) {
                $join->on('events.id', '=', 'event_category_list.events_id');
            })
            ->leftJoin('events_category', function ($join) {
                $join->on('events_category.id', '=', 'event_category_list.events_category_id');
            })
            ->where(function ($q) use ($search, $type, $category, $event) {
                if (!empty($search)) {
                    $q->where('events.name', 'LIKE', '%' . $search . '%');
                }
                if ($type == 'past') {
                    $q->whereDate('events.end_date', '<=', date('Y-m-d'));
                } elseif ($type == 'upcoming') {
                    $q->whereDate('events.end_date', '>=', date('Y-m-d'));
                }

                if (!empty($category)) {
                    $q->where('event_category_list.events_category_id', '=', $category);
                }
                if (!empty($event)) {
                    $q->where('events.event_type', '=', $event);
                }
                $q->where('status', '=', 'publish');
                $q->where('events.event_type', '!=', 'Partnership Event');
            })
            ->orderby($column_filter, $type_filter)
            ->paginate($limit);
    }
    public static function listAllEventsOnlySearchPartnership($search, $limit, $type, $category)
    {
        $column_filter = "events.start_date";
        $type_filter = "asc";

        return EventsEvents::select(
            'events.id',
            'events.name as title',
            'events.start_date',
            'events.end_date',
            'events.start_time',
            'events.end_time',
            'events.image_banner',
            'events.slug',
            'events.event_type',
            'events.location'
        )
            ->leftJoin('event_category_list', function ($join) {
                $join->on('events.id', '=', 'event_category_list.events_id');
            })
            ->leftJoin('events_category', function ($join) {
                $join->on('events_category.id', '=', 'event_category_list.events_category_id');
            })
            ->where(function ($q) use ($search, $type, $category) {
                if (!empty($search)) {
                    $q->where('events.name', 'LIKE', '%' . $search . '%');
                }
                // if ($type == 'past') {
                // $q->whereDate('events.end_date', '<=', date('Y-m-d'));
                // } elseif ($type == 'upcoming') {
                $q->whereDate('events.end_date', '>=', date('Y-m-d'));
                // }

                if (!empty($category)) {
                    $q->where('event_category_list.events_category_id', '=', $category);
                }
                $q->where('status', '=', 'publish');
                $q->where('event_type', '=', 'Partnership Event');
            })
            ->orderby($column_filter, $type_filter)
            ->paginate($limit);
    }

    public static function findEvent($slug)
    {
        return EventsEvents::where('slug', $slug)->first();
    }
}
