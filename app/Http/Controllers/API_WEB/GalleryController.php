<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsHighlight;
use App\Models\Videos\Videos;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function home()
    {
        // Fetch data using Eloquent ORM
        $data = EventsHighlight::join('events', 'events.id', 'events_highlight.events_id')
            ->orderBy('events_highlight.id', 'desc')
            ->select('events_highlight.id as id', 'events_highlight.image', 'events.name')
            ->get();

        // Initialize an associative array to store image URLs
        $imageData = [
            'image1' => [],
            'image2' => [],
            'image3' => [],
            'image4' => [],
        ];

        // Process data and populate $imageData
        $maxImagesPerList = 9;

        foreach ($data as $index => $item) {
            $listIndex = 'image' . (($index % $maxImagesPerList) + 1);

            // Check if the listIndex exists in $imageData
            if (!array_key_exists($listIndex, $imageData)) {
                $listIndex = 'image' . rand(1, count($imageData)); // Assign to a random index if not found
            }

            // Check if the count of items for the index is less than 9
            if (count($imageData[$listIndex]) < $maxImagesPerList) {
                // Add the image URL to the corresponding index
                $imageData[$listIndex][] = $item->image;
            }
        }

        // Create the desired JSON response
        $response = [
            'status'  => 200,
            'message' => 'Success',
            'payload' => $imageData,
        ];

        return response()->json($response);
    }

    public function feature()
    {
        $list = Videos::orderBy('id', 'desc')->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $list;
        return response()->json($response);
    }

    public function eventList()
    {
        $filteredEventList = Events::join('events_highlight', 'events.id', '=', 'events_highlight.events_id')
            ->orderBy('events.id', 'desc')
            ->select('events.id', 'events.name', 'events.slug')
            ->distinct()
            ->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $filteredEventList;

        return response()->json($response);
    }

    public function navigate(Request $request)
    {
        $slug = $request->slug;

        $query = EventsHighlight::query();

        // Check if $slug is provided
        if ($slug) {
            $query->whereHas('event', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        $data = $query->orderby('id', 'desc')->get();

        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;

        return response()->json($response);
    }
}
