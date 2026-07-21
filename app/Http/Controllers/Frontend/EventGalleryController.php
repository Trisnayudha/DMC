<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsHighlight;

class EventGalleryController extends Controller
{
    public function show($slug)
    {
        $event = Events::where('slug', $slug)->firstOrFail();

        $photos = EventsHighlight::where('events_id', $event->id)
            ->orderByRaw('CASE WHEN sort IS NULL OR sort = 0 THEN 1 ELSE 0 END')
            ->orderBy('sort')
            ->orderByDesc('id')
            ->get(['id', 'image']);

        $photos->each(function ($photo) {
            [$photo->width, $photo->height] = $this->imageDimensions($photo->image);
            $photo->url = $this->imageUrl($photo->image);
        });

        $bannerPath = $event->image_banner ?: $event->image;
        $bannerUrl = $bannerPath ? $this->imageUrl($bannerPath) : null;

        return view('event_gallery.show', [
            'event'     => $event,
            'photos'    => $photos,
            'bannerUrl' => $bannerUrl,
        ]);
    }

    private function imageDimensions($image)
    {
        $relative = ltrim(str_replace('/storage/', '', $image), '/');
        $absolute = storage_path('app/public/' . $relative);

        $size = file_exists($absolute) ? @getimagesize($absolute) : false;

        return $size ? [$size[0], $size[1]] : [3, 2];
    }

    private function imageUrl($path)
    {
        return 'https://membership.djakarta-miningclub.com' . str_replace(' ', '%20', $path);
    }
}
