<?php

namespace App\Services;

use App\Models\Giveaway\GiveawayLog;
use App\Models\Giveaway\GiveawayItem;

use Illuminate\Support\Facades\DB;

class GiveawayService
{
    public static function draw($visitId)
    {
        return DB::transaction(function () use ($visitId) {

            $items = GiveawayItem::where('remaining_qty', '>', 0)->get();

            $pool = [];

            foreach ($items as $item) {

                $stockRatio = $item->remaining_qty / $item->total_qty;

                if ($item->is_rare) {
                    // rare naik kalau non-rare mulai habis
                    $weight = $item->base_weight + ((1 - $stockRatio) * 30);
                } else {
                    // non-rare turun perlahan
                    $weight = $item->base_weight * $stockRatio;
                }

                $weight = max(1, (int) round($weight));

                for ($i = 0; $i < $weight; $i++) {
                    $pool[] = $item;
                }
            }

            if (empty($pool)) {
                return null;
            }

            $selected = $pool[array_rand($pool)];

            $selected->decrement('remaining_qty');

            GiveawayLog::create([
                'visit_id' => $visitId,
                'giveaway_item_id' => $selected->id,
            ]);

            return $selected;
        });
    }
}
