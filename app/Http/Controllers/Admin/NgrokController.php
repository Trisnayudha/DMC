<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ngrok\NgrokModel;
use Illuminate\Http\Request;

class NgrokController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $list = NgrokModel::orderBy('id', 'desc')->get();
        return view('admin.ngrok.index', compact('list'));
    }

    public function store(Request $request)
    {
        $request->validate(['link' => 'required|url']);

        NgrokModel::create(['link' => $request->link]);

        return response()->json(['status' => 200, 'message' => 'Ngrok link berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['link' => 'required|url']);

        $ngrok = NgrokModel::findOrFail($id);
        $ngrok->link = $request->link;
        $ngrok->save();

        return response()->json(['status' => 200, 'message' => 'Ngrok link berhasil diupdate']);
    }

    public function destroy($id)
    {
        NgrokModel::findOrFail($id)->delete();
        return response()->json(['status' => 200, 'message' => 'Ngrok link berhasil dihapus']);
    }
}
