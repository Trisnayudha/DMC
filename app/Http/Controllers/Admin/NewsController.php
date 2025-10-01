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
        // 1) Validasi awal
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'description2'   => 'nullable|string',
            'reference_link' => 'nullable|string',
            'reference_image' => 'nullable|string',
            'date_news'      => 'required|date',
            'status'         => 'required|in:draft,publish',
            'category_id'    => 'required|array',
            'category_id.*'  => 'exists:news_categories,id',
            'image'          => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120', // 5MB
        ]);

        // 2) Siapkan model
        $save = new News();
        $save->title           = $request->input('title');
        $save->desc            = $request->input('description');
        $save->desc2           = $request->input('description2');
        $save->reference_link  = $request->input('reference_link');
        $save->reference_image = $request->input('reference_image');
        $save->slug            = \Str::slug($request->input('title'));
        $save->status          = $request->input('status');
        $save->date_news       = $request->input('date_news');

        // 3) Upload gambar (aman)
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($image->isValid()) {
                // pastikan folder & symlink sudah benar: php artisan storage:link
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/news', $imageName);  // -> storage/app/public/news/{file}
                $save->image = '/storage/news/' . $imageName; // URL publik
            } else {
                return back()
                    ->withErrors(['image' => 'File upload tidak valid. Coba pilih file lain.'])
                    ->withInput();
            }
        }

        // 4) Simpan record utama
        $save->save();

        // 5) Simpan kategori (pivot)
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
