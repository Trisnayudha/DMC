<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsCategoryList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('share');
    }
    public function index()
    {
        $list = News::orderBy('id', 'desc')->get();
        $currentMonth = Carbon::now()->month;
        $countView = News::whereMonth('created_at', $currentMonth)->sum('views');
        // Menghitung jumlah total view dari semua berita
        $totalView = News::whereMonth('created_at', $currentMonth)->count('id');

        $data = [
            'totalView' => $totalView,
            'countView' => $countView,
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
        // Ambil input dari form
        $title = $request->title;
        $desc = $request->description;
        $desc2 = $request->description2; // Deskripsi 2
        $reference_link = $request->reference_link;
        $reference_image = $request->reference_image;
        $file = $request->image;
        $slug = Str::slug($request->title);
        $date_news = $request->date_news;
        $status = $request->status;

        // Simpan data ke model News
        $save = new News();
        $save->title = $title;
        $save->desc = $desc;
        $save->desc2 = $desc2; // Simpan Deskripsi 2
        $save->reference_link = $reference_link;
        $save->reference_image = $reference_image;
        $save->slug = $slug;
        $save->status = $status;
        $save->date_news = $date_news;

        // Cek apakah ada file gambar yang diupload
        if (!empty($file)) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/news/' . $imageName;

            // Simpan file ke storage
            $request->image->storeAs('public/news', $imageName);

            // Simpan path ke database
            $save->image = $db;
        }

        // Simpan record berita
        $save->save();

        // Simpan kategori (jika multiple categories)
        if (!empty($request->category_id)) {
            foreach ($request->category_id as $catId) {
                NewsCategoryList::create([
                    'news_id' => $save->id,
                    'news_category_id' => $catId
                ]);
            }
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
        $news->desc2 = $request->desc2; // Deskripsi 2
        $news->reference_link = $request->reference_link;
        $news->reference_image = $request->reference_image;
        $news->date_news = $request->date_news;
        $news->status = $request->status;
        $news->slug = Str::slug($request->title);
        // Cek file baru
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/news/' . $imageName;

            $request->image->storeAs('public/news', $imageName);
            $news->image = $db;
        }

        $news->save();
        // Update categories
        if (!empty($request->category_id)) {
            // Hapus data kategori lama
            NewsCategoryList::where('news_id', $news->id)->delete();

            // Simpan data kategori baru
            foreach ($request->category_id as $catId) {
                NewsCategoryList::create([
                    'news_id' => $news->id,
                    'news_category_id' => $catId
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

    public function share($slug)
    {
        $news = News::where('slug', $slug)->first();
        $data = [
            'news' => $news
        ];
        return view('admin.news.news-share', $data);
    }
}
