<?php

namespace App\Http\Controllers;

use App\Models\Events\Events;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\EventSponsors;
use App\Models\User;
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

    public function sponsor($slug, $type)
    {
        $findEvent = Events::where('slug', $slug)->first();
        $findEvent['typeSponsor'] = $type;
        $type = EventSponsors::where('code_access', $type)->first();
        if (!empty($type)) {
            $findEvent->typeSponsor = $type->code_access;
            $type->count += 1;
            $type->save();
        }
        // Pass data to the view using an associative array
        return view('register_event.single-sponsor', $findEvent);
        // Return a "Not Found" error response
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

    public function checkEmail($email)
    {
        $check = User::join('profiles', 'profiles.users_id', 'users.id')->join('company', 'company.users_id', 'users.id')->first();
    }
}
