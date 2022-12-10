<?php

namespace App\Http\Controllers;

use App\Models\Videos\Videos;
use Illuminate\Http\Request;

class VideosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = Videos::orderBy('id', 'desc')->get();
        // dd($list);
        $data = [
            'list' => $list
        ];
        return view('admin.videos.index', $data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data   =   Videos::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'link' => $request->link,
            ]
        );
        // activity()->log('Menambahkan Data Kategori');
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
        $data  = Videos::where($where)->first();
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
        $data = Videos::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }
}
