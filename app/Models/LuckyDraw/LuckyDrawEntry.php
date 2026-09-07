<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyDrawEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'lucky_draw_item_id',
        'name',
        'company_name',
        'job_title',
        'phone',
        'email',
        'business_card_path',
        'drawn_at',
    ];

    protected $casts = [
        'drawn_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(LuckyDrawItem::class, 'lucky_draw_item_id');
    }

    /**
     * Sudah diundi & dapat hadiah.
     */
    public function scopeWon($query)
    {
        return $query->whereNotNull('lucky_draw_item_id');
    }

    /**
     * Sudah diundi tapi zonk — beda dengan "belum sempat diundi" (lihat
     * scopePending): drawn_at wajib terisi supaya baris yang cuma auto-upload
     * kartu nama tanpa pernah klik undi tidak ikut kehitung zonk.
     */
    public function scopeLost($query)
    {
        return $query->whereNull('lucky_draw_item_id')->whereNotNull('drawn_at');
    }

    /**
     * Kartu nama sudah keupload duluan (auto-upload dari kamera) tapi
     * pengunjung belum/batal klik undi.
     */
    public function scopePending($query)
    {
        return $query->whereNull('drawn_at');
    }
}
