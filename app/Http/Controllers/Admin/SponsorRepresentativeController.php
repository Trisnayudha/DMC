<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorRepresentative;
use Illuminate\Http\Request;

class SponsorRepresentativeController extends Controller
{
    public function show($id)
    {
        $data['sponsor_id'] = $id;
        $data['data'] = SponsorRepresentative::where('sponsor_id', $id)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.sponsor-representative.sponsor', $data);
    }

    public function store(Request $request)
    {
        // Validasi tambahan bisa ditambahkan jika diperlukan
        $save = new SponsorRepresentative();
        $save->name = $request->name;
        $save->job_title = $request->job_title;
        $save->email = $request->email; // Field email ditambahkan
        $save->instagram = $request->instagram;
        $save->linkedin = $request->linkedin;
        $save->sponsor_id = $request->sponsor_id;

        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('public/sponsor/representative', $imageName);
            $imageUrl = asset('storage/sponsor/representative/' . $imageName);
        } else {
            $imageUrl = null;
        }
        $save->image = $imageUrl;
        $save->save();

        return response()->json([
            'success' => true,
            'message' => 'Success Add Representative',
            'data'    => $save
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rep = SponsorRepresentative::findOrFail($id);
        // Kembalikan data termasuk email agar bisa ditampilkan di modal
        return response()->json($rep);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rep = SponsorRepresentative::findOrFail($id);

        $rep->name       = $request->name;
        $rep->job_title  = $request->job_title;
        $rep->email      = $request->email; // Field email diupdate
        $rep->instagram  = $request->instagram;
        $rep->linkedin   = $request->linkedin;

        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('public/sponsor/representative', $imageName);
            $imageUrl = asset('storage/sponsor/representative/' . $imageName);
            $rep->image = $imageUrl;
        }

        $rep->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data sponsor representative.',
            'data'    => $rep
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rep = SponsorRepresentative::findOrFail($id);
        $rep->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus data sponsor representative.'
        ]);
    }
}
