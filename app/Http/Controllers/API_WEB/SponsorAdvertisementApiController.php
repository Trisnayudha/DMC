<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorAdvertising;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SponsorAdvertisementApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;

            $advertisings = SponsorAdvertising::join('sponsors', 'sponsors.id', '=', 'sponsors_advertising.sponsor_id')
                ->select(
                    'sponsors_advertising.id',
                    'sponsors_advertising.name as title',
                    'sponsors_advertising.image',
                    'sponsors.name as company',
                    'sponsors_advertising.date',
                    'sponsors_advertising.file_size as fileSize',
                    'sponsors_advertising.link as download'
                )
                ->paginate($limit);

            if ($advertisings->count() > 0) {
                $response['status'] = 200;
                $response['message'] = 'Success';
                $response['payload'] = $advertisings;
            } else {
                $response['status'] = 404;
                $response['message'] = 'No data found';
                $response['payload'] = [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching data: ' . $e->getMessage());
            $response['status'] = 500;
            $response['message'] = 'Internal Server Error';
            $response['payload'] = $e->getMessage();
        }

        return response()->json($response, $response['status']);
    }

    public function download($id)
    {
        try {
            $advertising = SponsorAdvertising::findOrFail($id);

            if (!$advertising->link) {
                $response['status'] = 404;
                $response['message'] = 'File not found';
                $response['payload'] = [];
                return response()->json($response, $response['status']);
            }

            $filePath = storage_path('app/public/sponsor/advertising/' . basename($advertising->link));

            if (!file_exists($filePath)) {
                $response['status'] = 404;
                $response['message'] = 'File not found on server';
                $response['payload'] = [];
                return response()->json($response, $response['status']);
            }

            // Mengembalikan file sebagai respons download
            return response()->download($filePath);
        } catch (\Exception $e) {
            Log::error('Error downloading file: ' . $e->getMessage());
            $response['status'] = 500;
            $response['message'] = 'Internal Server Error';
            $response['payload'] = [];
            return response()->json($response, $response['status']);
        }
    }
}
