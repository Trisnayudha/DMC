<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorAdvertising;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SponsorAdvertisementApiController extends Controller
{
    // Metode untuk mendapatkan daftar iklan sponsor
    public function index(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;
            $search = $request->input('search');
            $order = $request->input('order'); // Default order is 'newest'

            // Menentukan urutan penyortiran berdasarkan parameter 'order'
            $sortOrder = ($order === 'oldest') ? 'asc' : 'desc';

            // Mengambil data dengan join ke tabel sponsors dan kondisi pencarian
            $advertisings = SponsorAdvertising::join('sponsors', 'sponsors.id', '=', 'sponsors_advertising.sponsor_id')
                ->select(
                    'sponsors_advertising.id',
                    'sponsors_advertising.name as title',
                    'sponsors_advertising.image',
                    'sponsors.name as company',
                    'sponsors_advertising.date',
                    'sponsors_advertising.file_size',
                    'sponsors_advertising.link as download'
                )
                ->when($search, function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->where('sponsors_advertising.name', 'like', '%' . $search . '%')
                            ->orWhere('sponsors.name', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('sponsors_advertising.date', $sortOrder)
                ->paginate($limit);

            // Menggunakan transform untuk memodifikasi data
            $advertisings->getCollection()->transform(function ($item) {
                // Menggunakan accessor untuk memformat ukuran file dan tanggal
                $item->fileSize = $item->formatted_file_size;
                // $item->date = $item->formatted_date; // Jika Anda memiliki accessor untuk tanggal
                return $item;
            });

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
            $response['payload'] = [];
        }

        return response()->json($response, $response['status']);
    }


    // Metode untuk mendownload file iklan sponsor
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
