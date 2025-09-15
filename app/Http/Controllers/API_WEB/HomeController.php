<?php

namespace App\Http\Controllers\API_WEB;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\DigitalEdition\DigitalModel;
use App\Models\Events\Events;
use App\Models\Events\EventsHighlight;
use App\Models\Events\EventsSchedule;
use App\Models\Sponsors\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Repositories\Events as RepositoriesEvents;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

    public function getCarousel()
    {
        // 2 signature terpisah (urutannya sesuai array)
        $signatures = [
            '/image/libur6.png',
            '/image/libur5.png',
        ];

        $events = Events::select('id', 'name', 'description', 'slug', 'start_date')
            ->where('status', 'publish')
            ->orderBy('id', 'desc')
            ->get();

        $result = [];

        // Tambahkan setiap signature sebagai slide sendiri
        foreach ($signatures as $sig) {
            if (!empty($sig)) {
                $result[] = [
                    'heading1'   => null,
                    'heading2'   => null,
                    'date'       => null,
                    'listImage'  => [$sig], // tetap array karena front-end expect array
                    'slug'       => null,
                    'is_signature' => true, // optional flag kalau mau beda styling di FE
                ];
            }
        }

        // Lanjutkan dengan event highlight
        foreach ($events as $event) {
            $eventHighlight = EventsHighlight::where('events_id', $event->id)
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();

            if ($eventHighlight->isEmpty()) {
                continue;
            }

            $nameWords = explode(' ', $event->name);
            $heading1 = implode(' ', array_slice($nameWords, 0, 4));
            $heading2 = count($nameWords) > 4 ? implode(' ', array_slice($nameWords, 4)) : '';

            $listImage = $eventHighlight->pluck('image')->map(function ($image) {
                return $image;
            })->toArray();

            $result[] = [
                'heading1'  => $heading1,
                'heading2'  => $heading2,
                'date'      => \Carbon\Carbon::parse($event->start_date)->format('d F Y'),
                'listImage' => $listImage,
                'slug'      => $event->slug
            ];
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Success',
            'payload' => $result,
        ]);
    }




    public function getComingSoon()
    {
        $event = Events::where('event_type', '!=', 'Partnership Event')
            ->where('status', 'publish')
            ->where('start_date', '>=', Carbon::now())
            ->orderBy('id', 'desc')
            ->first();

        if ($event) {
            $result = [
                'date' => Carbon::parse($event->start_date)->format('d F Y'),
                'name' => $event->name,
                'slug' => $event->slug,
                'image' => $event->image,
                'type' => $event->event_type,
                'start_date' => $event->start_date,
                'location' => $event->location,
                'time' => $event->start_time
            ];
        } else {
            $result = [
                'date' => null, // Atau nilai default lainnya jika tidak ada event yang akan datang
                'name' => null,
                'slug' => null,
                'image' => null,
                'type' => null,
                'start_date' => null,
                'location' => null,
                'time' => null
            ];
        }

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $result;

        return response()->json($response);
    }

    public function getPastGalleryEvent(Request $request)
    {

        $limit = $request->limit;
        $search = $request->search;

        $type = $request->type;
        //Upcoming, Past Event, All
        $category = $request->category;
        $event = $request->event;
        $data = RepositoriesEvents::listAllEventsOnlySearch($search, $limit, $type, $category, $event);

        foreach ($data as $val => $key) {
            $date_end = date('Y-m-d', strtotime($key->end_date));
            $key->isUpcoming = (new \DateTime($date_end) >= new \DateTime(date('Y-m-d')) ? true : false);
            $key->title = (strlen($key->title) > 100 ? substr($key->title, 0,  100) . '...' : $key->title);
            $key->image = (!empty($key->image) ? asset($key->image) : '');
            $key->date = (!empty($key->start_date) ? date('d ', strtotime($key->start_date)) . ' - ' . date('d M Y', strtotime($key->end_date)) : '');
            $key->eventType = ($key->event_type ? $key->event_type : '');
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }


    public function postUpComing(Request $request)
    {
        $send = new WhatsappApi();
        $send->message = 'Trigger UpComing Event
        Email: ' . $request->email;
        $send->phone = '120363422942310672';
        $send->WhatsappMessageGroup();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = null;
        return response()->json($response);
    }

    public function getScheduleEvent()
    {

        $data = EventsSchedule::leftjoin('events', 'events.id', 'events_schedule.events_id')->select('events_schedule.*', 'events.slug')->where('events_schedule.date', '>=', Carbon::now())->orderby('events_schedule.sort', 'asc')->get();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function getPartnership(Request $request)
    {
        $limit = $request->limit;
        $search = $request->search;

        $type = $request->type;
        //Upcoming, Past Event, All
        $category = $request->category;
        $data = RepositoriesEvents::listAllEventsOnlySearchPartnership($search, $limit, $type, $category);

        foreach ($data as $val => $key) {
            $date_end = date('Y-m-d', strtotime($key->end_date));
            $key->isUpcoming = (new \DateTime($date_end) >= new \DateTime(date('Y-m-d')) ? true : false);
            $key->title = (strlen($key->title) > 100 ? substr($key->title, 0,  100) . '...' : $key->title);
            $key->image = (!empty($key->image) ? asset($key->image) : '');
            $key->date = (!empty($key->start_date) ? date('d ', strtotime($key->start_date)) . ' - ' . date('d M Y', strtotime($key->end_date)) : '');
            $key->eventType = ($key->event_type ? $key->event_type : '');
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function getDigitalEdition()
    {
        $data = DigitalModel::orderby('sort', 'asc')->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function statistic()
    {
        $data = [
            'annual' => '1.500',
            'events' => '6',
            'members' => '5.000',
            'corporate' => '45',
        ];
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
