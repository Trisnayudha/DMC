<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsCategoryList;
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
        $categories = NewsCategory::orderBy('id', 'desc')->get();

        $data = [
            'categories' => $categories
        ];
        return view('admin.news.create', $data);
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

        foreach ($request->category_id as $key => $value) {
            $category = NewsCategoryList::create([
                'news_id' => $save->id,
                'news_category_id' => $request->category_id[$key]
            ]);
        }
        return redirect()->route('news')->with('success', 'Successfully create news');
    }
}
