<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use Illuminate\Http\Request;
use App\Repositories\Events as RepositoriesEvents;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{

    public function detail($slug)
    {
        $findEvent = RepositoriesEvents::findEvent($slug);
        // Check if the user is authenticated
        $id = null;
        if (auth('sanctum')->check()) {
            // User is authenticated, get the user ID
            $id = auth('sanctum')->user()->id;
        }
        if (!empty($findEvent)) {
            $findEvent->image = (!empty($findEvent->image) ? asset($findEvent->image) : '');
            $eventStatus = (strtotime($findEvent->start_date) > strtotime('now')) ? 'Upcoming Event' : 'Finished Event';
            $startDateFormat = Carbon::parse($findEvent->start_date)->isoFormat('D MMMM YYYY');
            $endDateFormat = Carbon::parse($findEvent->end_date)->isoFormat('D MMMM YYYY');

            $mappedEvent = [
                'id' => $findEvent->id,
                'name' => $findEvent->name,
                'details' => $findEvent->description,
                'event_type' => $findEvent->event_type,
                'event_status' => $eventStatus,
                'start_date' => $startDateFormat,
                'end_date' => $endDateFormat,
                'start_time' => $findEvent->start_time,
                'end_time' => $findEvent->end_time,
                'location' => $findEvent->location,
                'image' => $findEvent->image_banner,
            ];
            $findTicket = EventsTicket::where('events_id', $findEvent->id)->where('status_ticket', '=', 'on')->orderby('price_rupiah', 'asc')->get();
            $findUser = UserRegister::where('users_id', '=', $id)->where('events_id', '=', $findEvent->id)->first();
            $findPayment = Payment::where('member_id', '=', $id)->where('events_id', '=', $findEvent->id)->first();
            $listUser = [
                'already_register' => $findUser ? true : false,
                'waiting_payment' => $findPayment ? ($findPayment->status_registration == 'Waiting' ? true : false) : false,

            ];
            foreach ($findTicket as $val => $key) {
                $key->price_rupiah = $key->type == 'free' ? 0 : $key->price_rupiah;
                $key->price_dollar = $key->type == 'free' ? 0 : $key->price_dollar;
                $key->description = $key->description == null ? '' : $key->description;
            }
            $data = [
                'detail' => $mappedEvent,
                'ticket' => $findTicket,
                'users' => $listUser,

            ];

            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['payload'] = $data;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Event Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
