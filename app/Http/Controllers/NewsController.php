<?php

namespace App\Http\Controllers;

use App\Models\News\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = News::orderBy('id', 'desc')->get();
        $data = [
            'list' => $list
        ];
        return view('admin.news.index', $data);
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(Request $request)
    {
        $title = $request->title;
        $desc = $request->description;
        $reference_link = $request->reference_link;
        $image = $request->image;
        $slug = Str::slug($request->title);
        $date_news = $request->date_news;
        $file = $request->image;

        $save = new News();
        $save->title = $title;
        $save->desc = $desc;
        $save->reference_link = $reference_link;
        $save->slug = $slug;
        $save->date_news = $date_news;
        if (!empty($file)) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/news/' . $imageName;
            $save_folder = $request->image->storeAs('public/news', $imageName);
            $save->image = $db;
        }
        $save->save();
        return redirect()->route('news')->with('success', 'Successfully create news');
    }
}
