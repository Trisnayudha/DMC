<?php

namespace App\Http\Controllers;

use App\Models\Events\Events;
use App\Models\Profiles\ProfileModel;
use Illuminate\Http\Request;

class EventsRegisterController extends Controller
{
    public function single($slug)
    {
        $findEvent = Events::where('slug', $slug)->first();
        return view('register_event.single', $findEvent);
    }

    public function multiple($slug)
    {
        $findEvent = Events::where('slug', $slug)->first();
        return view('register_event.multiple', $findEvent);
    }

    public function checkPhone($phone)
    {
        $check = ProfileModel::where('phone', $phone)->first();

        $status = $check != null ? 1 : 0; // Menggunakan operator ternary untuk mengatur status

        return response()->json([
            'status' => $status,
            'message' => $status ? 'Nomor telepon sudah digunakan' : 'Nomor telepon tersedia',
        ]);
    }
}
