<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsHighlight;
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
}
