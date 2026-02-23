<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipTierBanner;
use Illuminate\Http\Request;

class MembershipTierBannerApiController extends Controller
{
    public function index(Request $request)
    {
        // kalau endpoint ini tanpa auth, ganti jadi tier dari request (tapi lebih aman pakai auth)
        $user = $request->user();

        // Ambil tier user, normalize
        $rawTier = strtolower($user->tier ?? 'reguler');

        // mapping biar konsisten dengan DB
        $tierMap = [
            'regular' => 'reguler',
            'reguler' => 'reguler',
            'black'   => 'black',
        ];

        $tier = $tierMap[$rawTier] ?? 'reguler';

        $sectionKey = $request->query('section_key'); // optional

        $baseQuery = \App\Models\Membership\MembershipTierBanner::query()
            ->where('is_active', true);
        // 1) coba ambil sesuai tier user
        $q = (clone $baseQuery)->where('tier', $tier);

        if ($sectionKey) {
            $q->where('section_key', $sectionKey);
        }

        $rows = MembershipTierBanner::query()
            ->where('tier', $tier)
            ->where('is_active', true)
            ->orderBy('section_key')
            ->orderBy('sort_order')
            ->get();

        $grouped = $rows->groupBy('section_key')->map(function ($items) {
            return $items->map(function ($row) {
                return [
                    'id'           => $row->id,
                    'title'        => $row->title,
                    'image'        => $row->image,
                    'link_url'     => $row->link_url,
                    'open_new_tab' => (bool) $row->open_new_tab,
                    'sort_order'   => (int) $row->sort_order,
                ];
            })->values();
        });

        return response()->json([
            'status' => true,
            'tier'   => $tier,
            'data'   => [
                'dashboard_left'  => $grouped->get('dashboard_left', collect())->values(),
                'dashboard_right' => $grouped->get('dashboard_right', collect())->values(),
            ],
        ]);
    }
}
