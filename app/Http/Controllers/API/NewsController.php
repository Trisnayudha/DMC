<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsBookmark;
use App\Models\News\NewsComment;
use App\Models\News\NewsLike;
use App\Models\News\NewsViews;
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

    public function detail($slug, $limit = 10)
    {
        $id =  auth('sanctum')->user()->id;
        $detail = News::where('slug', '=', $slug)->first();
        $findLike = NewsLike::where('users_id', '=', $id)->where('news_id', '=', $detail->id)->first();
        $findComment = NewsComment::where('news_id', '=', $detail->id)
            ->join('users', 'news_comment.users_id', 'users.id')
            ->select('users.id', 'users.name', 'news_comment.comment', 'news_comment.created_at')
            ->paginate($limit);
        // dd($detail);
        $detail->date_news = date('d, M Y H:i', strtotime($detail->date_news));
        $detail->like = $findLike ? true : false;
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

    public function bookmark(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $news_id = $request->news_id;

        $post = NewsBookmark::create([
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

    public function like(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $news_id = $request->news_id;
        $findlike = NewsLike::where('users_id', '=', $id)->where('news_id', '=', $news_id)->first();
        if ($findlike) {
            NewsLike::where('users_id', '=', $id)->where('news_id', '=', $news_id)->delete();
            $response['status'] = 200;
            $response['message'] = 'Unlike News';
            $response['payload'] = null;
        } else {
            $post = NewsLike::create([
                'users_id' => $id,
                'news_id' => $news_id
            ]);
            $response['status'] = 200;
            $response['message'] = 'Like News';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function comment(Request $request)
    {
        $id =  auth('sanctum')->user()->id;
        $news_id = $request->news_id;
        $comment = $request->comment;
        $post = NewsComment::create([
            'users_id' => $id,
            'news_id' => $news_id,
            'comment' => $comment
        ]);
        if ($post) {
            $response['status'] = 200;
            $response['message'] = 'Success Comment News';
            $response['payload'] = null;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Failed Comment News';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
