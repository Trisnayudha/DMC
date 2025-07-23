<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Pastikan ini ada
use Illuminate\Support\Facades\Log; // Untuk logging error

class PublicController extends Controller
{
    public function summaryAttandance(Request $request)
    {
        // --- BLOK VALIDASI API KEY DIHAPUS DI SINI ---
        // Ini berarti endpoint ini sekarang dapat diakses oleh siapa saja
        // Pastikan Anda memahami risiko keamanannya.
        // --- AKHIR BLOK VALIDASI API KEY ---

        // Ambil event_id dari query parameter (misal: /api/summary-attendance?event_id=123)
        $eventId = $request->input('event_id');

        // Validasi event_id
        if (empty($eventId)) {
            return response()->json(['message' => 'Parameter event_id is required'], 400);
        }

        try {
            $results = DB::table('users_event as ue')
                ->join('payment as p', 'ue.payment_id', '=', 'p.id')
                ->select(
                    DB::raw("
                        CASE
                            WHEN p.package = 'non member' OR p.package = 'member' THEN 'Paid Delegate'
                            WHEN p.package = 'free' THEN 'Invitation Exclusive'
                            WHEN p.package = 'sponsor' THEN 'Sponsor'
                            WHEN p.package = 'speaker' THEN 'Speaker'
                            ELSE 'Lain-lain'
                        END AS package_category
                    "),
                    DB::raw('COUNT(ue.id) AS count')
                )
                ->where('ue.events_id', $eventId)
                ->whereNotNull('ue.present') // Hanya yang sudah check-in
                ->groupBy('package_category')
                ->orderByRaw("FIELD(package_category, 'Paid Delegate', 'Invitation Exclusive', 'Sponsor', 'Speaker', 'Lain-lain')")
                ->get();

            // Mengembalikan hasil dalam format JSON
            return response()->json($results);
        } catch (\Exception $e) {
            // Log error untuk debugging di server
            Log::error('Error fetching DMC check-in summary from API: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mengembalikan respons error JSON
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}
