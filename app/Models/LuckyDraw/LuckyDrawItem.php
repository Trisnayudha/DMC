<?php

namespace App\Models\LuckyDraw;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyDrawItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'chance_percent',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'chance_percent' => 'float',
        'is_active'      => 'boolean',
    ];

    public function entries()
    {
        return $this->hasMany(LuckyDrawEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
