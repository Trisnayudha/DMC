<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsBookmark;
use App\Repositories\News as RepositoriesNews;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(RepositoriesNews $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $banner = RepositoriesNews::listAll();
        foreach ($banner as $value) {
            $value['date_news'] = date('d, M Y H:i', strtotime($value->date_news));
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $banner;
        return response()->json($response);
    }

    public function ListAll(Request $request)
    {
        $limit = $request->limit;
        $search = $request->search;
        $category = $request->category;
        $bannersPluck = RepositoriesNews::listAllToArray();
        $news = RepositoriesNews::listAllNewsOnlySearch($search, $bannersPluck, $limit, $category);
        foreach ($news as $value) {
            $value->date_news = date('d, M Y H:i', strtotime($value->date_news));
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $news;
        return response()->json($response);
    }

    public function detail($slug)
    {
        $detail = News::where('slug', '=', $slug)->first();
        // dd($detail);
        $detail->date_news = date('d, M Y H:i', strtotime($detail->date_news));

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $detail;
        return response()->json($response);
    }

    public function bookmark(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $news_id = $request->news_id;

        $post = NewsBookmark::insert([
            'users_id' => $id,
            'news_id' => $news_id
        ]);
        if ($post) {

            $response['status'] = 200;
            $response['message'] = 'Success Bookmark News';
            $response['payload'] = null;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Failed Bookmark News';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
