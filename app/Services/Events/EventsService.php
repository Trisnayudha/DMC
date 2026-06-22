<?php

namespace App\Services\Events;

use App\Models\Events\Events;

class EventsService extends Events
{

    public static function showDetail($params)
    {
        if (is_numeric($params)) {
            return Events::where('id', $params)->first();
        } else {
            return Events::where('slug', $params)->first();
        }
    }

    public static function showAll()
    {
        return Events::orderby('id', 'desc')->get();
    }

    /**
     * event_type yang ikut penomoran share link tahunan.
     */
    const SHARE_NUMBERED_TYPES = ['DMC Event', 'DMC Partnership Event'];

    /**
     * Bangun URL share SEO-friendly: /events-{tahun}-{nomor}-{slug_topic}.
     * Nomor = urutan event pada tahun yang sama (berdasarkan start_date)
     * di antara event ber-type DMC Event / DMC Partnership Event.
     * Bagian topik diambil dari slug_topic (hasil slugify field topic).
     * Untuk type lain / topic belum diisi / data tidak lengkap,
     * fallback ke link share lama berbasis slug.
     */
    public static function buildShareUrl($event)
    {
        if (empty($event->slug_topic) || empty($event->start_date) || !in_array($event->event_type, self::SHARE_NUMBERED_TYPES)) {
            return url('share/events/' . $event->slug);
        }

        $year = date('Y', strtotime($event->start_date));
        $number = Events::whereIn('event_type', self::SHARE_NUMBERED_TYPES)
            ->whereYear('start_date', $year)
            ->where(function ($q) use ($event) {
                $q->where('start_date', '<', $event->start_date)
                    ->orWhere(function ($q2) use ($event) {
                        $q2->where('start_date', '=', $event->start_date)
                            ->where('id', '<=', $event->id);
                    });
            })
            ->count();

        return url('events-' . $year . '-' . $number . '-' . $event->slug_topic);
    }
}
