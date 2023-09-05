<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\News\News;
use App\Services\Sponsors\SponsorService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $event = Events::select('id', 'name as title', 'description as desc', 'slug', 'image')->orderBy('id', 'desc')->limit(3)->get();
        foreach ($event as $key) {
            $key->type = 'events';
            $key->desc = (strlen($key->desc) > 50 ? substr($key->desc, 0,  50) . '...' : $key->desc);
        }
        $news = News::select('id', 'title', 'desc', 'slug', 'image')->where('highlight', '=', 'Yes')->orderBy('id', 'desc')->limit(3)->get();
        foreach ($news as $key) {
            $key->type = 'news';
            $key->desc = (strlen($key->desc) > 50 ? substr($key->desc, 0,  50) . '...' : $key->desc);
        }
        $collection = collect($event);
        $merged     = $collection->merge($news);
        $sorted = $merged->sort();
        $result   = $sorted->values()->all();
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $result;
        return response()->json($response);
    }
}
