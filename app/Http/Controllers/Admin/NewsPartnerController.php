<?php
// app/Http/Controllers/Admin/NewsPartnerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News\NewsPartner;
use Illuminate\Http\Request;

class NewsPartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // (opsional) halaman create partner terpisah
    public function create()
    {
        return view('admin.news_partners.create');
    }

    // (opsional) save via halaman create partner
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'company'  => 'nullable|string|max:255',
            'website'  => 'nullable|string|max:255',
            'image'    => 'nullable|string|max:255', // URL hasil upload ajax
            'quote'    => 'nullable|string|max:255',
        ]);

        NewsPartner::create($request->only(['name', 'position', 'company', 'website', 'image', 'quote']));

        return redirect()->route('news.create')->with('success', 'Partner created.');
    }

    // dipakai oleh modal create partner di form news
    public function ajaxStore(Request $request)
    {
        $request->validate([
            'partner_name'     => 'required|string|max:255',
            'partner_position' => 'nullable|string|max:255',
            'partner_company'  => 'nullable|string|max:255',
            'partner_website'  => 'nullable|string|max:255',
            'partner_image'    => 'nullable|string|max:255',
            'partner_quote'    => 'nullable|string|max:255',
        ]);

        $p = NewsPartner::create($request->only(['partner_name', 'partner_position', 'partner_company', 'partner_website', 'partner_image', 'partner_quote']));

        return response()->json([
            'success' => true,
            'partner' => [
                'id'   => $p->id,
                'text' => $p->partner_name . ($p->partner_company ? ' - ' . $p->partner_company : ''),
            ],
        ]);
    }
}
