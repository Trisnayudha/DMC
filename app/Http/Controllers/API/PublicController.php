<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Pastikan ini ada
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Support\Facades\Storage;

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
                            WHEN p.package = 'Free' OR p.package = 'nonmember' THEN 'Delegates'
                            WHEN p.package = 'sponsor' THEN 'Sponsors'
                            WHEN p.package = 'speaker' THEN 'Speakers'
                            ELSE 'Delegates'
                        END AS package_category
                    "),
                    DB::raw('COUNT(ue.id) AS count')
                )
                ->where('ue.events_id', $eventId)
                ->whereNotNull('ue.present') // Hanya yang sudah check-in
                ->groupBy('package_category')
                ->orderByRaw("FIELD(package_category, 'Delegates', 'Sponsors', 'Speakers')")
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
    // Endpoint 1: Attendance per package (checked-in only)
    public function attendanceByPackage(Request $request)
    {
        $eventId = $request->input('event_id');
        if (empty($eventId)) {
            return response()->json(['message' => 'Parameter event_id is required'], 400);
        }

        try {
            $rows = DB::table('users_event as ue')
                ->join('payment as p', 'ue.payment_id', '=', 'p.id')
                ->join('users as u', 'u.id', '=', 'ue.users_id')
                ->join('company as c', 'u.id', 'c.users_id')
                ->where('ue.events_id', $eventId)
                ->whereNotNull('ue.present') // ✅ hanya yang sudah check-in (present datetime)
                ->select([
                    // ✅ Kategorisasi package
                    DB::raw("
                    CASE
                        WHEN p.package IN ('Free','nonmember') THEN 'Delegates'
                        WHEN p.package = 'sponsor' THEN 'Sponsors'
                        WHEN p.package = 'speaker' THEN 'Speakers'
                        ELSE 'Delegates'
                    END AS package_category
                "),
                    'u.name as user_name',
                    DB::raw('p.code_payment as codepayment'),
                    'c.company_name as company'
                ])
                ->orderBy('package_category')
                ->orderBy('user_name')
                ->get();

            // ✅ GROUP BY kategori, bukan package asli
            $grouped = $rows->groupBy('package_category')->map(function ($items, $category) {
                return [
                    'category' => $category,
                    'attendees' => $items->map(fn($r) => [
                        'name'        => $r->user_name,
                        'codepayment' => $r->codepayment,
                        'company' => $r->company
                    ])->values(),
                ];
            })->values();

            return response()->json([
                'event_id'        => (int) $eventId,
                'categories'      => $grouped,
                'total_attendees' => $rows->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('attendanceByPackage error: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);

            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }


    // GET /api/user-detail-by-codepayment/{codepayment}?event_id=55
    public function userDetailByCodepayment(Request $request, string $codepayment)
    {
        $eventId = $request->input('event_id');
        if (empty($eventId)) {
            return response()->json(['message' => 'Parameter event_id is required'], 400);
        }

        try {
            $rows = DB::table('users_event as ue')
                ->join('payment as p', 'ue.payment_id', '=', 'p.id')
                ->leftJoin('users as u', 'u.id', '=', 'ue.users_id')
                ->join('company as c', 'u.id', 'c.users_id')
                ->join('profiles', 'profiles.users_id', 'u.id')
                ->where('ue.events_id', $eventId)
                // ⚠️ Sesuaikan dengan kolom terbaru: code_payment
                ->where('p.code_payment', $codepayment)
                ->select([
                    // normalisasi output → codepayment
                    DB::raw('p.code_payment as codepayment'),
                    'p.package',
                    // ambil nama dari users, fallback ke users_event
                    'u.name as user_name',
                    'ue.photo',
                    'ue.present',
                    'ue.id as users_event_id',
                    'c.company_nam as company',
                    'p.job_title as job'
                ])
                ->orderBy('user_name')
                ->get();

            // Jika ingin hanya yang sudah check-in (present datetime), aktifkan ini:
            // $rows = $rows->filter(fn($r) => !is_null($r->present))->values();

            $users = $rows->map(function ($r) {
                $photo = $r->photo;

                // Jika path storage lokal, buat URL publik
                if ($photo && !preg_match('~^https?://~', $photo)) {
                    $photo = Storage::url($photo); // sesuaikan disk jika perlu
                }

                return [
                    'users_event_id' => (int) $r->users_event_id,
                    'name'           => $r->user_name,
                    'photo'          => $photo,     // dari users_event.photo
                    'present'        => $r->present, // datetime/null
                    'company'       => $r->company
                ];
            });

            $meta = $rows->first();

            return response()->json([
                'event_id'    => (int) $eventId,
                'codepayment' => $codepayment,
                'package'     => $meta->package ?? null,
                'users'       => $users,
                'total'       => $users->count(),
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('userDetailByCodepayment error: ' . $e->getMessage(), [
                'event_id'    => $eventId,
                'codepayment' => $codepayment,
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
