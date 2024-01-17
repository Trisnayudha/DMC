<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsRundown;
use App\Models\Events\EventsSpeakersRundown;
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

    public function rundown($slug)
    {
        $findEvent = RepositoriesEvents::findEvent($slug);
        $dataRundown = EventsRundown::where('events_id', $findEvent->id)->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = [];

        foreach ($dataRundown as $rundown) {
            $time = Carbon::parse($rundown->date)->format('H:i') . ' WIB';

            $item['id'] = $rundown->id;
            $item['name'] = $rundown->name;
            $item['time'] = $time;
            $item['speakers'] = [];

            $dataSpeakers = EventsSpeakersRundown::leftJoin('events_rundown', 'events_rundown.id', 'events_speakers_rundown.events_rundown_id')
                ->leftJoin('events_speakers', 'events_speakers.id', 'events_speakers_rundown.events_speakers_id')
                ->where('events_rundown.id', $rundown->id)
                ->get();

            foreach ($dataSpeakers as $speaker) {
                $speakerItem['name'] = $speaker->name;
                $speakerItem['job_title'] = $speaker->job_title;
                $speakerItem['image'] = $speaker->image;
                $item['speakers'][] = $speakerItem;
            }

            $response['payload'][] = $item;
        }

        // Sort the payload based on the time/date
        usort($response['payload'], function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        return response()->json($response);
    }

    public function myEvent(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $filter = $request->filter;
        $limit = $request->limit;
        $date = date('Y-m-d');
        $findMyEvent = UserRegister::where('users_id', $id)->first();
        if (!empty($findMyEvent)) {
            $findDetail = UserRegister::join('events', 'events.id', 'users_event.events_id')
                ->join('payment', 'payment.id', 'users_event.payment_id')
                ->where(function ($q) use ($filter, $date) {
                    if ($filter == 'active' || $filter == 'upcoming') {
                        $q->where('events.end_date', '>=', $date);
                    } elseif ($filter == 'history' || $filter == 'finish') {
                        $q->where('events.end_date', '<', $date);
                    }
                })
                ->where('users_event.users_id', '=', $id)
                ->select(
                    'events.id',
                    'events.name as event_name',
                    'events.start_date',
                    'events.location',
                    'events.start_time',
                    'events.image',
                    'payment.code_payment',
                    'payment.qr_code',
                    'payment.status_registration',
                    'payment.package as present',
                    'users_event.present',
                    'events.slug'
                )
                ->orderBy('payment.id', 'desc')
                ->paginate($limit);
            foreach ($findDetail as $val => $key) {
                $key->present = $key->present != null ? true : false;
                $key->status = $key->end_date >= $date ? 'Upcoming Event' : 'Completed Event';
            }
            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['payload'] = $findDetail;
        } else {
            $response['status'] = 200;
            $response['message'] = 'Event Not Found';
            $response['payload'] = [];
        }
        return response()->json($response);
    }
    public function myEvent(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $filter = $request->filter;
        $limit = $request->limit;
        $date = date('Y-m-d');
        $findMyEvent = UserRegister::where('users_id', $id)->first();
        if (!empty($findMyEvent)) {
            $findDetail = UserRegister::join('events', 'events.id', 'users_event.events_id')
                ->join('payment', 'payment.id', 'users_event.payment_id')
                ->where(function ($q) use ($filter, $date) {
                    if ($filter == 'active' || $filter == 'upcoming') {
                        $q->where('events.end_date', '>=', $date);
                    } elseif ($filter == 'history' || $filter == 'finish') {
                        $q->where('events.end_date', '<', $date);
                    }
                })
                ->where('users_event.users_id', '=', $id)
                ->select(
                    'events.id',
                    'events.name as event_name',
                    'events.start_date',
                    'events.location',
                    'events.start_time',
                    'events.image',
                    'payment.code_payment',
                    'payment.qr_code',
                    'payment.status_registration',
                    'payment.package as present',
                    'users_event.present',
                    'events.slug'
                )
                ->orderBy('payment.id', 'desc')
                ->paginate($limit);
            foreach ($findDetail as $val => $key) {
                $key->present = $key->present != null ? true : false;
                $key->status = isset($key->end_date) && $key->end_date >= $date ? 'Upcoming Event' : 'Completed Event';
            }

            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['payload'] = $findDetail;
        } else {
            $response['status'] = 200;
            $response['message'] = 'Event Not Found';
            $response['payload'] = [];
        }
        return response()->json($response);
    }
}
