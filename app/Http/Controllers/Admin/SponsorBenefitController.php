<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBenefitUsage;
use Illuminate\Http\Request;

use Carbon\Carbon;

class SponsorBenefitController extends Controller
{

    public function index()
    {
        return 'awe awe';
    }
    /**
     * Menampilkan daftar penggunaan benefit untuk sponsor tertentu.
     *
     * @param  int  $sponsorId
     * @return \Illuminate\Http\Response
     */
    public function detail($sponsorId)
    {
        // Ambil sponsor berdasarkan ID (hanya sponsor aktif)
        $sponsor = Sponsor::where('id', $sponsorId)
            ->where('status', 'publish')
            ->firstOrFail();

        // Ambil detail penggunaan benefit untuk sponsor ini, diurutkan berdasarkan periode
        $benefitDetails = SponsorBenefitUsage::with('benefit')
            ->where('sponsor_id', $sponsor->id)
            ->orderBy('period')
            ->get();

        // Hitung total dan used benefit untuk sponsor ini
        $totalCount = $benefitDetails->count();
        $usedCount = $benefitDetails->where('status', 'used')->count();

        // Hitung usage rate sebagai persentase
        $benefitUsageRate = $totalCount > 0 ? round(($usedCount / $totalCount) * 100) : 0;

        return view('admin.sponsor-benefit.detail', compact('sponsor', 'benefitDetails', 'usedCount', 'totalCount', 'benefitUsageRate'));
    }

    /**
     * Menandai sebuah benefit sebagai sudah digunakan.
     *
     * @param  int  $id  ID dari record sponsor benefit usage
     * @return \Illuminate\Http\Response
     */
    public function markUsed($id)
    {
        $benefitUsage = SponsorBenefitUsage::findOrFail($id);

        // Pastikan hanya mengubah jika status masih "unused"
        if ($benefitUsage->status !== 'used') {
            $benefitUsage->status = 'used';
            $benefitUsage->used_at = Carbon::now();
            $benefitUsage->save();
        }

        return redirect()->back()->with('success', 'Benefit telah ditandai sebagai digunakan.');
    }
}
