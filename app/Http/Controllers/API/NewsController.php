<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News\News;
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
        $data = [
            'carosel' => $banner
        ];
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function ListAll(Request $request)
    {
        $limit = $request->limit;
        $search = $request->search;
        $bannersPluck = RepositoriesNews::listAllToArray();
        $news = RepositoriesNews::listAllNewsOnlySearch($search, $bannersPluck, $limit);
        foreach ($news as $value) {
            $value->date_news = date('d, M Y H:i', strtotime($value->date_news));
        }
        $data = [
            'list_news' => $news,
        ];
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detail($slug)
    {
        $detail = News::where('slug', '=', $slug)->first();
        // dd($detail);
        $detail->date_news = date('d, M Y H:i', strtotime($detail->date_news));

        $data = [
            'detail' => $detail
        ];

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
