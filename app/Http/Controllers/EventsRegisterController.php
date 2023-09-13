<?php

namespace App\Http\Controllers;

use App\Models\Events\Events;
use Illuminate\Http\Request;

class EventsRegisterController extends Controller
{
    public function single($slug)
    {
        $findEvent = Events::where('slug', $slug)->first();
        return view('register_event.single-indo', $findEvent);
    }

    public function multiple($slug)
    {
        $findEvent = Events::where('slug', $slug)->first();
        return view('register_event.multiple', $findEvent);
    }
}
