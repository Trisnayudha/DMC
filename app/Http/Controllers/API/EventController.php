<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Repositories\Events as RepositoriesEvents;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->limit;
        $search = $request->search;

        $type = $request->type;
        //Upcoming, Past Event, All
        $category = $request->category;
        $data = RepositoriesEvents::listAllEventsOnlySearch($search, $limit, $type, $category);

        foreach ($data as $val => $key) {
            $date_end = date('Y-m-d', strtotime($key->end_date));
            $key->isUpcoming = (new \DateTime($date_end) >= new \DateTime(date('Y-m-d')) ? true : false);
            $key->title = (strlen($key->title) > 100 ? substr($key->title, 0,  100) . '...' : $key->title);
            $key->image = (!empty($key->image) ? asset($key->image) : '');
            $key->date_start_events = (!empty($key->start_date) ? date('d ', strtotime($key->start_date)) . ' - ' . date('d M Y', strtotime($key->end_date)) : '');
            $key->start_date = (!empty($key->start_date) ? date('d M Y', strtotime($key->start_date)) : '');
            $key->end_date = (!empty($key->end_date) ? date('d M Y', strtotime($key->end_date)) : '');
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }
    public function detail($slug)
    {
        $id =  auth('sanctum')->user()->id;
        $findEvent = RepositoriesEvents::findEvent($slug);
        $findEvent->image = (!empty($findEvent->image) ? asset($findEvent->image) : '');
        $findTicket = EventsTicket::where('events_id', $findEvent->id)->where('status_ticket', '=', 'on')->get();
        $findUser = UserRegister::where('users_id', '=', $id)->where('events_id', '=', $findEvent->id)->first();
        $findPayment = Payment::where('member_id', '=', $id)->where('events_id', '=', $findEvent->id)->first();
        $listUser = [
            'already_register' => $findUser ? true : false,
            'waiting_payment' => $findPayment ? ($findPayment->status_registration == 'Waiting' ? true : false) : false
        ];
        foreach ($findTicket as $val => $key) {
            $key->price_rupiah = $key->type == 'free' ? 0 : $key->price_rupiah;
            $key->price_dollar = $key->type == 'free' ? 0 : $key->price_dollar;
        }
        if (!empty($findEvent)) {
            $data = [
                'detail' => $findEvent,
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
                    if ($filter == 'active') {
                        $q->where('events.end_date', '>=', $date);
                    } else {
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
                    'payment.status_registration'
                )
                ->orderBy('payment.id', 'desc')
                ->paginate($limit);


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

    public function waitingPay()
    {
        $id =  auth('sanctum')->user()->id;

        $findWaiting = Payment::where('member_id', $id)->where('status_registration', '=', 'Waiting')->first();

        if (!empty($findWaiting)) {
            $findDetail = Payment::where('member_id', $id)->where('status_registration', '=', 'Waiting')
                ->join('payment_users_va', 'payment_users_va.payment_id', 'payment.id')
                ->join('events', 'events.id', 'payment.events_id')
                ->orderBy('payment.id', 'desc')
                ->get();

            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['payload'] = $findDetail;
        } else {
            $response['status'] = 200;
            $response['message'] = 'Payment Not Found';
            $response['payload'] = [];
        }
        return response()->json($response);
    }

    public function detailPayment(Request $request)
    {
        $payment_id = $request->payment_id;

        $findPayment = Payment::where('payment.id', $payment_id)->first();

        if (!empty($findPayment)) {
            $findDetail = Payment::where('payment.id', $payment_id)
                ->join('payment_users_va', 'payment_users_va.payment_id', 'payment.id')
                ->first();

            $response['status'] = 200;
            $response['message'] = 'Success';
            $response['payload'] = $findDetail;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Payment Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
