<?php

namespace App\Http\Controllers;

use App\Models\News\NewsCategory;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = NewsCategory::orderBy('id', 'desc')->paginate(10);
        return view(
            'admin.news-category.index',
            ['category' => $data]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data   =   NewsCategory::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'name_category' => $request->category_name,
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
        $data  = NewsCategory::where($where)->first();
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
        $data = NewsCategory::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }
}
