<?php

namespace App\Http\Controllers;

use App\Models\Events\Events;
use App\Models\Marketing\MarketingAds;
use App\Models\News\News;
use Illuminate\Http\Request;

class MarketingAdsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = MarketingAds::orderBy('id', 'desc')->get();
        // dd($list);
        $data = [
            'list' => $list
        ];
        return view('admin.marketing-ads.index', $data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = MarketingAds::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'type' => $request->type,
                'location' => $request->location,
                'target_id' => $request->target_id
            ]
        );

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();
            $db = '/storage/ads/' . $imageName;
            $image->storeAs('public/ads', $imageName);
            $data->image = $db;
            $data->save();
        }

        return response()->json(['success' => true]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $data  = MarketingAds::where($where)->first();
        // activity()->log('Edit Data Kategori');
        return response()->json($data);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = MarketingAds::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }

    public function event()
    {
        $data = Events::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'payload' => $data
        ]);
    }

    public function news()
    {
        $data = News::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'payload' => $data
        ]);
    }
}
