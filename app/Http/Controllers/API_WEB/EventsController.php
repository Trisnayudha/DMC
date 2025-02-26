<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\BookingContact\BookingContact;
use App\Models\Events\Events;
use App\Models\Events\EventsRundown;
use App\Models\Events\EventsSpeakersRundown;
use App\Models\Events\EventsTicket;
use App\Models\Events\UserRegister;
use App\Models\Payments\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Events as RepositoriesEvents;
use App\Services\Events\EventsService;
use App\Services\Payment\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->limit;
        $search = $request->search;

        $type = $request->type;
        //Upcoming, Past Event, All
        $category = $request->category;
        $event = $request->event;
        //Partnership Event,DMC Event, DMC Partnership Event
        $data = RepositoriesEvents::listAllEventsOnlySearch($search, $limit, $type, $category, $event);
        foreach ($data as $val => $key) {
            $date_end = date('Y-m-d', strtotime($key->end_date));
            $key->isUpcoming = (new \DateTime($date_end) >= new \DateTime(date('Y-m-d')) ? true : false);
            $key->title = (strlen($key->title) > 100 ? substr($key->title, 0,  100) . '...' : $key->title);
            $key->image = (!empty($key->image) ? asset($key->image) : '');
            $key->date_start_events = (!empty($key->start_date) ? date('d M Y', strtotime($key->start_date))  : '');
            $key->start_date = (!empty($key->start_date) ? date('d M Y', strtotime($key->start_date)) : '');
            $key->end_date = (!empty($key->end_date) ? date('d M Y', strtotime($key->end_date)) : '');
            // Ambil jam dan menit dari start_time dan end_time
            $key->start_time = (!empty($key->start_time) ? date('h:i', strtotime($key->start_time)) : '');
            $key->end_time = (!empty($key->end_time) ? date('h:i A', strtotime($key->end_time)) : '');
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }

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
            $eventStatus =  (new \DateTime($findEvent->end_date) > new \DateTime(date('Y-m-d')) ?  'Upcoming Event' : 'Event Ended');
            $startDateFormat = (!empty($findEvent->start_date) ? date('d M Y', strtotime($findEvent->start_date)) : '');
            $endDateFormat = (!empty($findEvent->end_date) ? date('d M Y', strtotime($findEvent->end_date)) : '');

            $mappedEvent = [
                'id' => $findEvent->id,
                'name' => $findEvent->name,
                'details' => $findEvent->description,
                'event_type' => $findEvent->event_type,
                'event_status' => $eventStatus,
                'start_date' => $startDateFormat,
                'end_date' => $endDateFormat,
                'start_time' => date('h:i', strtotime($findEvent->start_time)),
                'end_time' => date('h:i A', strtotime($findEvent->end_time)),
                'location' => $findEvent->location,
                'image' => $findEvent->image_banner,
                'link' => $findEvent->link,
                'status_event' => $findEvent->status_event,
                'status_member' => $id != null ? 'member' : 'nonmember',
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
            $time = Carbon::parse($rundown->date)->format('h:i A');

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
                $speakerItem['company'] = $speaker->company;
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
                    'events.slug',
                    'events.end_date' // Tambahkan kolom 'end_date' ke dalam hasil kueri
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

    public function downloadTicket(Request $request)
    {
        $code_payment = $request->code_payment;
        $payment_id = Payment::where('code_payment', $code_payment)->first();
        $findUsers = PaymentService::findPaymmentUser($payment_id->id);
        $findEvent = EventsService::showDetail($findUsers->events_id);

        $data = [
            'code_payment' => $findUsers->code_payment,
            'create_date' => date('d, M Y H:i'),
            'users_name' => $findUsers->name,
            'users_email' => $findUsers->email,
            'phone' => $findUsers->phone,
            'job_title' => $findUsers->job_title,
            'company_name' => $findUsers->company_name,
            'company_address' => $findUsers->address,
            'events_name' => $findEvent->name,
            'start_date' => $findEvent->start_date,
            'end_date' => $findEvent->end_date,
            'start_time' => $findEvent->start_time,
            'end_time' => $findEvent->end_time,
            'image' => $findUsers->qr_code
        ];
        $email = $findUsers->email;

        ini_set('max_execution_time', 300);
        $pdf = Pdf::loadView('email.ticket', $data);
        $filename = 'e_ticket-' . $findUsers->code_payment . '.pdf';
        // Download the PDF with the specified filename
        return $pdf->download($filename);
    }

    public function downloadInvoice(Request $request)
    {
        $code_payment = $request->code_payment;
        $payment_id = Payment::where('code_payment', $code_payment)->first();
        $findPayment = PaymentService::findPaymmentUser($payment_id->id);
        //check booking_contact_id
        $findEvent = EventsService::showDetail($findPayment->events_id);

        if ($findPayment->booking_contact_id != null) {
            $findPayments = PaymentService::findPaymmentUsers($findPayment->booking_contact_id);
            $countPrice = null;
            foreach ($findPayments as $table) {
                $item_details[] = [
                    'name' => $table['name'],
                    'job_title' => $table['email'],
                    'price' => number_format($table['price_rupiah'], 0, ',', '.'),
                    'paidoff' => false
                ];
                $countPrice += $table['price_rupiah'];
            }
            $findBooking = BookingContact::where('id', $table['booking_contact_id'])->first();
            $payload = [
                'code_payment' => $findPayment->code_payment,
                'create_date' => date('d, M Y H:i'),
                'users_name' => $findBooking->name_contact,
                'users_email' => $findBooking->email_contact,
                'phone' => $findBooking->phone_contact,
                'company_name' => $findBooking->company_name,
                'company_address' => $findBooking->address,
                'status' => 'Paid Off',
                'voucher_price' => 0,
                'item' => $item_details,
                'price' => number_format($table['price_rupiah'], 0, ',', '.'),
                'total_price' => number_format($countPrice, 0, ',', '.'),
                'events_name' => $findEvent->name,
                'link' => null
            ];
            ini_set('max_execution_time', 120);
            $pdf = Pdf::loadView('email.invoice-new-multiple', $payload);
            $filename = 'invoice_' . $findPayment->code_payment . '.pdf';
            // Download the PDF with the specified filename
            return $pdf->download($filename);
        }
        $payload = [
            'code_payment' => $findPayment->code_payment,
            'create_date' => date('d, M Y H:i'),
            'users_name' => $findPayment->name,
            'users_email' => $findPayment->email,
            'phone' => $findPayment->phone,
            'company_name' => $findPayment->company_name,
            'company_address' => $findPayment->address,
            'status' => 'Paid Off',
            'voucher_price' => 0,
            'price' => number_format($findPayment->price_rupiah, 0, ',', '.'),
            'total_price' => number_format($findPayment->price_rupiah, 0, ',', '.'),
            'events_name' => $findEvent->name,
        ];
        ini_set('max_execution_time', 120); // Set the maximum execution time to 120 seconds
        $pdf = PDF::loadView('email.invoice-new', $payload);
        // Set the desired filename for the downloaded PDF
        $filename = 'invoice_' . $findPayment->code_payment . '.pdf';
        // Download the PDF with the specified filename
        return $pdf->download($filename);
    }

    public function checkUserRegister(Request $request)
    {
        $email = $request->email;
        $events_id = $request->events_id;

        // Early return if event does not exist
        $event = Events::find($events_id);
        if (!$event) {
            return response()->json(['status' => 404, 'message' => 'Event not found']);
        }
        // Define the default response
        $response = [
            'status' => 200,
            'message' => '',
            'payload' => [
                'email' => $email,
                'price' => $event->status_event === 'Free' ? 0 : 1000000,
                'price_dollar' => $event->status_event === 'Free' ? 0 : 62,
            ]
        ];

        // Fetch user and their payment status for the event
        $user = User::where('email', $email)->first();
        $payment = $user ? Payment::where('member_id', $user->id)
            ->where('events_id', $events_id)
            ->whereIn('status_registration', ['Waiting', 'Paid Off'])
            ->first() : null;
        $ticket = EventsTicket::where('events_id', $events_id);

        if ($event->status_event !== 'Free') {
            if (!$user) {
                $ticket->where('title', '=', 'Non Member')->first();
                $response['message'] = 'Email not registered as a member';
                $response['payload']['price'] = $ticket->price_rupiah;
                $response['payload']['price_dollar'] = $ticket->price_dollar;
                $response['payload']['ticket_id'] = $ticket->id;
            } elseif ($payment) {
                $response['message'] = 'Email already registered in event!';
                $response['status'] = 409; // Conflict
            } else {
                $ticket->where('title', '=', 'Member')->first();
                $response['message'] = 'Email is available and can be used for registration';
                $response['payload']['price'] = $ticket->price_rupiah;
                $response['payload']['price_dollar'] = $ticket->price_dollar;
                $response['payload']['ticket_id'] = $ticket->id;
            }
        } else {
            if ($payment) {
                $response['message'] = 'Email already registered in event!';
                $response['status'] = 409; // Conflict
            } else {
                $ticket->where('title', '=', 'free')->first();
                $response['message'] = 'Free event registration is available';
                $response['payload'] = $ticket;
            }
        }

        return response()->json($response);
    }
}
