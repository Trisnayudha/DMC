<?php

namespace App\Services;

use App\Models\LuckyDraw\LuckyDrawItem;

class LuckyDrawService
{
    /**
     * Undi 1 item berdasarkan chance_percent masing-masing (nilai literal
     * yang diatur admin, tidak dinormalisasi ulang). Total chance_percent
     * semua item aktif boleh kurang dari 100% — sisanya adalah peluang
     * "zonk" (return null), sesuai keputusan produk: item dengan chance 0%
     * tidak pernah keluar, dan tidak semua orang wajib menang.
     */
    public static function draw(): ?LuckyDrawItem
    {
        $items = LuckyDrawItem::active()
            ->where('chance_percent', '>', 0)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        // Angka acak 0.0000 - 99.9999 (presisi 4 desimal) supaya adil untuk
        // chance_percent yang punya pecahan (mis. 12.5%).
        $roll = mt_rand(0, 999999) / 10000;

        $cursor = 0.0;

        foreach ($items as $item) {
            $cursor += (float) $item->chance_percent;

            if ($roll < $cursor) {
                return $item;
            }
        }

        // $roll jatuh di luar total chance yang dialokasikan → zonk.
        return null;
    }
}
