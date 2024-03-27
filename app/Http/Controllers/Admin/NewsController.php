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
        $reference_image = $request->reference_image;
        $image = $request->image;
        $slug = Str::slug($request->title);
        $date_news = $request->date_news;
        $file = $request->image;
        $status = $request->status;

        $save = new News();
        $save->title = $title;
        $save->desc = $desc;
        $save->reference_link = $reference_link;
        $save->reference_image = $reference_image;
        $save->slug = $slug;
        $save->status = $status;
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

    public function edit($id)
    {
        $news = News::findOrFail($id);
        // $categories = NewsCategory::orderBy('id', 'desc')->get();
        // $selectedCategories = $news->categories->pluck('id')->toArray(); // Assuming a Many-to-Many relationship

        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $news->title = $request->title;
        $news->desc = $request->desc;
        $news->reference_link = $request->reference_link;
        $news->reference_image = $request->reference_image;
        $news->date_news = $request->date_news;
        $news->status = $request->status;
        $news->slug = Str::slug($request->title);

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/news/' . $imageName;
            $save_folder = $request->image->storeAs('public/news', $imageName);
            $news->image = $db;
        }

        $news->save();

        // Update categories
        if (!empty($request->category_id)) {
            NewsCategoryList::where('news_id', $news->id)->delete();
            foreach ($request->category_id as $key => $value) {
                NewsCategoryList::create([
                    'news_id' => $news->id,
                    'news_category_id' => $value
                ]);
            }
        }

        return redirect()->route('news')->with('success', 'News updated successfully');
    }


    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return redirect()->route('news')->with('success', 'News deleted successfully');
    }
}
