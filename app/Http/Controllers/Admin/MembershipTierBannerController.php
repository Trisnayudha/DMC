<?php

// app/Http/Controllers/Admin/MembershipTierBannerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipTierBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MembershipTierBannerController extends Controller
{
    public function index()
    {
        $data = MembershipTierBanner::orderBy('id', 'desc')->get();
        return view('admin.membership-tier-banners.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tier'        => 'required|in:reguler,black',
            'section_key' => 'required|in:dashboard_left,dashboard_right',
            'title'       => 'nullable|string|max:255',
            'link_url'    => 'nullable',
            'open_new_tab' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:1',
            'is_active'   => 'nullable|boolean',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);


        $banner = new MembershipTierBanner();
        $banner->tier = $request->tier;
        $banner->section_key = $request->section_key;
        $banner->title = $request->title;
        $banner->link_url = $request->link_url;
        $banner->open_new_tab = $request->boolean('open_new_tab');
        $banner->sort_order = $request->sort_order ?? 1;
        $banner->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('public/membership-tier-banners', $imageName);

            // simpan seperti format kamu: /storage/...
            $banner->image = '/storage/membership-tier-banners/' . $imageName;
        }

        $banner->save();

        return redirect()->route('membership-tier-banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $banner = MembershipTierBanner::find($id);
        if (!$banner) {
            return redirect()->route('membership-tier-banners.index')->with('error', 'Data tidak ditemukan.');
        }

        $request->validate([
            'tier'        => 'required|in:reguler,black',
            'section_key' => 'required|in:dashboard_left,dashboard_right',
            'title'       => 'nullable|string|max:255',
            'link_url'    => 'nullable',
            'open_new_tab' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:1',
            'is_active'   => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $banner->tier = $request->tier;
        $banner->section_key = $request->section_key;
        $banner->title = $request->title;
        $banner->link_url = $request->link_url;
        $banner->open_new_tab = $request->boolean('open_new_tab');
        $banner->sort_order = $request->sort_order ?? 1;
        $banner->is_active = $request->boolean('is_active');

        // kalau upload image baru: hapus lama, simpan baru
        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::delete('public/membership-tier-banners/' . basename($banner->image));
            }

            $timestamp = now()->timestamp;
            $imageName = $timestamp . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('public/membership-tier-banners', $imageName);
            $banner->image = '/storage/membership-tier-banners/' . $imageName;
        }

        $banner->save();

        return redirect()->route('membership-tier-banners.index')->with('success', 'Banner berhasil diupdate.');
    }

    public function destroy($id)
    {
        $banner = MembershipTierBanner::find($id);

        if (!$banner) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        if ($banner->image) {
            Storage::delete('public/membership-tier-banners/' . basename($banner->image));
        }

        $banner->delete();

        return response()->json(['message' => 'Banner berhasil dihapus.']);
    }
}
