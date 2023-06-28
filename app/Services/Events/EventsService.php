<?php

namespace App\Services\Events;

use App\Models\Events\Events;

class EventsService extends Events
{

    public static function showDetail($params)
    {
        if (is_numeric($params)) {
            return Events::where('id', $params)->first();
        } else {
            return Events::where('slug', $params)->first();
        }
    }

    public static function showAll()
    {
        return Events::orderby('id', 'desc')->get();
    }
}
