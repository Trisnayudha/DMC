<?php
// app/Http/Controllers/Admin/NewsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsCategoryList;
use App\Models\News\NewsPartner;
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
        $totalView = News::whereMonth('created_at', $currentMonth)->count('id');

        return view('admin.news.index', [
            'totalView' => $totalView,
            'countView' => $countView,
            'list' => $list
        ]);
    }

    public function create()
    {
        $categories = NewsCategory::orderBy('id', 'desc')->get();
        $partners = NewsPartner::orderBy('partner_name', 'asc')->get(); // dropdown partner

        return view('admin.news.create', [
            'categories' => $categories,
            'partners'   => $partners,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'            => 'required|in:default,partnership,sponsor',
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'description2'    => 'nullable|string',
            'reference_link'  => 'nullable|string',
            'reference_image' => 'nullable|string',
            'date_news'       => 'required|date',
            'status'          => 'required|in:draft,publish',
            'image'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',

            // partner hanya id (dipakai kalau type=partnership)
            'news_partners_id' => 'nullable|integer|exists:news_partners,id',

            // categories (optional)
            'category_id'      => 'nullable|array',
        ]);

        // partnership: wajib pilih partner
        if ($request->type === 'partnership' && !$request->filled('news_partners_id')) {
            return back()
                ->withErrors(['news_partners_id' => 'Partner wajib dipilih untuk tipe Partnership.'])
                ->withInput();
        }

        $save = new News();
        $save->type            = $request->type;
        $save->title           = $request->title;
        $save->desc            = $request->description;
        $save->desc2           = $request->description2;
        $save->reference_link  = $request->reference_link;
        $save->reference_image = $request->reference_image;
        $save->slug            = Str::slug($request->title);
        $save->status          = $request->status;
        $save->date_news       = $request->date_news;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            if (!$image->isValid()) {
                return back()->withErrors(['image' => 'File upload tidak valid.'])->withInput();
            }
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/news', $imageName);
            $save->image = '/storage/news/' . $imageName;
        }

        // set partner FK only if partnership
        $save->news_partners_id = ($request->type === 'partnership') ? $request->news_partners_id : null;

        $save->save();

        // categories pivot (optional)
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
        $news = News::with('partner')->findOrFail($id);
        $categories = NewsCategory::orderBy('id', 'desc')->get();
        $partners = NewsPartner::orderBy('name', 'asc')->get();

        return view('admin.news.edit', compact('news', 'categories', 'partners'));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'type'            => 'required|in:default,partnership,sponsor',
            'title'           => 'required|string|max:255',
            'desc'            => 'required|string',
            'desc2'           => 'nullable|string',
            'reference_link'  => 'nullable|string',
            'reference_image' => 'nullable|string',
            'date_news'       => 'required|date',
            'status'          => 'required|in:draft,publish',
            'image'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',

            'news_partners_id' => 'nullable|integer|exists:news_partners,id',

            'category_id'      => 'nullable|array',
            'category_id.*'    => 'exists:news_categories,id',
        ]);

        if ($request->type === 'partnership' && !$request->filled('news_partners_id')) {
            return back()
                ->withErrors(['news_partners_id' => 'Partner wajib dipilih untuk tipe Partnership.'])
                ->withInput();
        }

        $news->type            = $request->type;
        $news->title           = $request->title;
        $news->desc            = $request->desc;
        $news->desc2           = $request->desc2;
        $news->reference_link  = $request->reference_link;
        $news->reference_image = $request->reference_image;
        $news->date_news       = $request->date_news;
        $news->status          = $request->status;
        $news->slug            = Str::slug($request->title);

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/news', $imageName);
            $news->image = '/storage/news/' . $imageName;
        }

        $news->news_partners_id = ($request->type === 'partnership') ? $request->news_partners_id : null;

        $news->save();

        // categories pivot (optional)
        if (!empty($request->category_id)) {
            NewsCategoryList::where('news_id', $news->id)->delete();

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

        return view('admin.news.news-share', [
            'news' => $news
        ]);
    }
}
