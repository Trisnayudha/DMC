<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberSource extends Model
{
    use HasFactory;

    protected $table = 'member_sources';

    protected $fillable = [
        'category',
        'code',
        'name',
        'form_type',
        'events_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Event this registration link belongs to — its members count as that event's conversions. */
    public function event()
    {
        return $this->belongsTo(\App\Models\Events\Events::class, 'events_id');
    }

    /** events_id exists only after the 2026_11_18 migration has run. */
    public static function hasEventColumn(): bool
    {
        static $has = null;
        if ($has === null) {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('member_sources', 'events_id');
        }

        return $has;
    }
}
