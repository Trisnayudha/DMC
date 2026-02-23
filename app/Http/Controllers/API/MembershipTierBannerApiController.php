<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipTierBanner;
use Illuminate\Http\Request;

class MembershipTierBannerApiController extends Controller
{
    /**
     * GET /api/membership/tier-banners
     * Optional query:
     * - section_key=dashboard_left (filter 1 section)
     * - include_inactive=1 (admin/debug)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // fallback aman kalau field tier null
        $tier = $user->tier ?? 'reguler';

        $sectionKey = $request->query('section_key'); // optional filter
        $includeInactive = $request->boolean('include_inactive', false);

        $q = MembershipTierBanner::query()
            ->where('tier', $tier);

        if (!$includeInactive) {
            $q->where('is_active', true);
        }

        if (!empty($sectionKey)) {
            $q->where('section_key', $sectionKey);
        }

        $items = $q->orderBy('section_key')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($row) {
                return [
                    'id'           => $row->id,
                    'tier'         => $row->tier,
                    'section_key'  => $row->section_key,
                    'title'        => $row->title,
                    'image'        => $row->image, // sudah /storage/...
                    'link_url'     => $row->link_url,
                    'open_new_tab' => (bool) $row->open_new_tab,
                    'sort_order'   => (int) $row->sort_order,
                ];
            })
            ->groupBy('section_key')
            ->values(); // optional, kalau mau array numerik

        // Kalau kamu prefer object by key (lebih enak buat binding), pakai ini:
        $grouped = $q->orderBy('section_key')->orderBy('sort_order')->get()
            ->map(function ($row) {
                return [
                    'id'           => $row->id,
                    'title'        => $row->title,
                    'image'        => $row->image,
                    'link_url'     => $row->link_url,
                    'open_new_tab' => (bool) $row->open_new_tab,
                    'sort_order'   => (int) $row->sort_order,
                ];
            })
            ->groupBy('section_key');

        return response()->json([
            'status' => true,
            'tier'   => $tier,
            'data'   => [
                // default keys biar frontend aman kalau kosong
                'dashboard_left'  => $grouped->get('dashboard_left', collect())->values(),
                'dashboard_right' => $grouped->get('dashboard_right', collect())->values(),
            ],
        ]);
    }
}
