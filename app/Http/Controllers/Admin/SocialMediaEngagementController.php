<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SocialMediaEngagement;
use App\Models\Sponsors\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SocialMediaEngagementController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter platform dan tahun dari request, dengan default untuk tahun sekarang
        $platform = $request->get('platform', ''); // Jika kosong, ambil semua
        $year = $request->get('year', Carbon::now()->format('Y'));

        // Query untuk mengambil data engagement dengan relasi sponsor
        $query = SocialMediaEngagement::with('sponsor');
        if ($platform) {
            $query->where('platform', $platform);
        }
        $query->whereYear('activity_date', $year);
        $engagements = $query->orderBy('activity_date', 'desc')->get();

        // Statistik engagement per jenis aktivitas
        $stats = [
            'like'    => $engagements->where('activity_type', 'like')->count(),
            'comment' => $engagements->where('activity_type', 'comment')->count(),
            'share'   => $engagements->where('activity_type', 'share')->count(),
        ];

        // Hitung total engagement
        $totalEngagement = $stats['like'] + $stats['comment'] + $stats['share'];

        // Kelompokkan engagement berdasarkan sponsor_id untuk menghitung jumlah engagement per sponsor
        $engagementCount = $engagements->groupBy(function ($engagement) {
            return $engagement->sponsor->id;
        })->map(function ($group) {
            return $group->count();
        });

        // Ambil semua sponsor aktif agar sponsor yang tidak memiliki engagement tetap tampil dengan count 0
        $allSponsors = Sponsor::where('status', 'publish')->get();

        return view('admin.sponsor-engagement.index', compact(
            'engagements',
            'stats',
            'totalEngagement',
            'year',
            'platform',
            'engagementCount',
            'allSponsors'
        ));
    }


    public function create()
    {
        // Ambil daftar sponsor untuk dipilih
        $sponsors = Sponsor::where('status', 'publish')->get();
        return view('admin.sponsor-engagement.create', compact('sponsors'));
    }

    public function store(Request $request)
    {
        // Validasi input data
        $validated = $request->validate([
            'sponsor_id'    => 'required|exists:sponsors,id',
            'activity_type' => 'required|in:like,comment,share',
            'platform'      => 'required|string',
            'activity_date' => 'required|date',
            'screenshot'    => 'nullable|image|max:2048', // maks. 2MB
        ]);

        // Proses upload screenshot jika ada
        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('public/engagement_screenshots');
            // Simpan hanya nama file atau URL publik, sesuai kebutuhan
            $validated['screenshot'] = Storage::url($path);
        }

        // Simpan data ke database
        SocialMediaEngagement::create($validated);

        return redirect()->route('sponsor-engagement.index')->with('success', 'Engagement data successfully added.');
    }
}
