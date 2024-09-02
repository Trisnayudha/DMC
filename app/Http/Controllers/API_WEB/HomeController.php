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
        $signature = 'image/jexpo.png';
        $events = Events::select('id', 'name', 'description', 'slug', 'start_date')
            ->orderBy('id', 'desc')
            ->get();

        $result = [];

        // Check if signature is not null before adding to result
        if ($signature !== null) {
            $result[] = [
                'heading1' => null,
                'heading2' => null,
                'date' => null,
                'listImage' => [$signature], // Include signature as listImage
                'slug' => null
            ];
        }

        foreach ($events as $event) {
            // Cek apakah ada event highlight untuk event saat ini
            $eventHighlight = EventsHighlight::where('events_id', $event->id)->orderby('id', 'desc')->limit(5)->get();

            // Jika tidak ada event highlight, skip event ini
            if ($eventHighlight->isEmpty()) {
                continue;
            }

            $nameWords = explode(' ', $event->name);
            $heading1 = implode(' ', array_slice($nameWords, 0, 4));
            $heading2 = count($nameWords) > 4 ? implode(' ', array_slice($nameWords, 4)) : '';
            $listImage = $eventHighlight->pluck('image')->map(function ($image) {
                return $image; // Assuming 'image' is the column name storing file paths
            })->toArray();
            $result[] = [
                'heading1' => $heading1,
                'heading2' => $heading2,
                'date' => Carbon::parse($event->start_date)->format('d F Y'),
                'listImage' => $listImage,
                'slug' => $event->slug
            ];
        }

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $result;

        return response()->json($response);
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
                'slug' => $event->slug
            ];
        } else {
            $result = [
                'date' => null, // Atau nilai default lainnya jika tidak ada event yang akan datang
                'name' => null,
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
        $data = RepositoriesEvents::listAllEventsOnlySearch($search, $limit, $type, $category);

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
        $send->phone = '081332178421';
        $send->WhatsappMessage();
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
}
