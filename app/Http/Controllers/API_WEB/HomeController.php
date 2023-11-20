<?php

namespace App\Http\Controllers\API_WEB;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
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
        $events = Events::select('id', 'name', 'description', 'slug', 'start_date')->orderBy('id', 'desc')->limit(6)->get();

        $result = [];

        foreach ($events as $event) {
            $eventHighlight = EventsHighlight::where('events_id', $event->id)->limit(5)->get();

            $nameWords = explode(' ', $event->name);
            $heading1 = implode(' ', array_slice($nameWords, 0, 4));
            $heading2 = count($nameWords) > 4 ? implode(' ', array_slice($nameWords, 4)) : '';
            $listImage = $eventHighlight->pluck('image')->map(function ($image) {
                return Storage::url($image); // Assuming 'image' is the column name storing file paths
            })->toArray();
            $result[] = [
                'heading1' => $heading1,
                'heading2' => $heading2,
                'date' => Carbon::parse($event->start_date)->format('d F Y'),
                'listImage' => $listImage
            ];
        }

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $result;

        return response()->json($response);
    }

    public function getComingSoon()
    {
        $event = Events::where('start_date', '>=', Carbon::now())->orderby('id', 'desc')->first();

        if ($event) {
            $result = [
                'date' => Carbon::parse($event->start_date)->format('d F Y'),
            ];
        } else {
            $result = [
                'date' => null, // Atau nilai default lainnya jika tidak ada event yang akan datang
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
        $send->message = $request->email;
        $send->phone = '083829314436';
        $send->WhatsappMessage();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = null;
        return response()->json($response);
    }

    public function getScheduleEvent()
    {

        $data = EventsSchedule::where('date', '>=', Carbon::now())->orderby('sort', 'asc')->get();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
