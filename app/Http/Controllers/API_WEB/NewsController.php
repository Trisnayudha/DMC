<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsBookmark;
use App\Models\News\NewsComment;
use App\Models\News\NewsLike;
use App\Models\News\NewsViews;
use Illuminate\Http\Request;
use App\Repositories\News as RepositoriesNews;

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
            $value->description = strip_tags($value->desc);
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
        $category = $request->category_id;

        $bannersPluck = RepositoriesNews::listAllToArray();
        $news = RepositoriesNews::listAllNewsOnlySearch($search, $bannersPluck, $limit, $category);
        foreach ($news as $value) {
            $value->description = strip_tags($value->desc);
            $value->date_news = date('d, M Y', strtotime($value->date_news));
        }
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $news;
        return response()->json($response);
    }

    public function detail($slug, $limit = 10)
    {
        $id =  auth('sanctum')->user()->id ?? 0;
        $detail = News::where('slug', '=', $slug)->first();
        $detail->views = $detail->views + 1;
        $detail->save();
        $findLike = NewsLike::where('users_id', '=', $id)->where('news_id', '=', $detail->id)->first();
        $findBookmark = NewsBookmark::where('users_id', '=', $id)->where('news_id', '=', $detail->id)->first();
        $findComment = NewsComment::where('news_id', '=', $detail->id)
            ->join('users', 'news_comment.users_id', 'users.id')
            ->leftjoin('profiles', 'profiles.users_id', 'users.id')
            ->select('users.id', 'users.name', 'news_comment.comment', 'news_comment.created_at', 'profiles.image')
            ->orderBy('news_comment.id', 'desc')
            ->paginate($limit);
        $detail->date_news = date('d, M Y H:i', strtotime($detail->date_news));
        $detail->like = $findLike ? true : false;
        $detail->bookmark = $findBookmark ? true : false;
        $insert = NewsViews::create([
            'users_id' => $id,
            'news_id' => $detail->id
        ]);
        $data = [
            'detail' => $detail,
            'comment' => $findComment
        ];
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
