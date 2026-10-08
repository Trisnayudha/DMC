<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;
    protected $table = 'events';

    /**
     * Jumlah hari event (start_date..end_date inklusif), minimal 1. Dipakai
     * untuk membentuk pilihan Day 1..N di form visitor booth — event 1 hari
     * berarti tidak ada pilihan day.
     */
    public function dayCount(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 1;
        }

        $start = \Illuminate\Support\Carbon::parse($this->start_date)->startOfDay();
        $end   = \Illuminate\Support\Carbon::parse($this->end_date)->startOfDay();

        return max(1, min($start->diffInDays($end) + 1, 31));
    }

    /**
     * Day ke berapa hari ini (1..N) kalau hari ini jatuh di dalam rentang
     * event, selain itu null.
     */
    public function currentDay(): ?int
    {
        if (!$this->start_date) {
            return null;
        }

        $start = \Illuminate\Support\Carbon::parse($this->start_date)->startOfDay();
        $today = \Illuminate\Support\Carbon::today();
        $day   = $start->diffInDays($today, false) + 1;

        return ($day >= 1 && $day <= $this->dayCount()) ? $day : null;
    }
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'image',
        'location',
        'event_category_id',
        'slug',
        'topic',
        'status',
        'type',
        'maps',
        'image_banner',
        'has_giveaway',
    ];

    protected $casts = [
        'has_giveaway' => 'boolean',
    ];

    public function getSubjectNameAttribute()
    {
        return preg_replace('/^The\s+/i', '', $this->name);
    }

    public function partnershipEventVisitors()
    {
        return $this->hasMany(\App\Models\PartnershipEvent\PartnershipEventVisitor::class, 'events_id');
    }
}
