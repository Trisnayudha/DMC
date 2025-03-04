<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBenefitUsage;
use Illuminate\Http\Request;

use Carbon\Carbon;

class SponsorBenefitController extends Controller
{

    public function index(Request $request)
    {
        // Ambil tahun dari request, default ke tahun sekarang
        $year = $request->get('year', now()->format('Y'));

        // Ambil semua record penggunaan benefit untuk tahun tersebut
        // Karena field 'period' disimpan dengan format "YYYY-MM", kita gunakan LIKE untuk filter tahun
        $usageRecords = SponsorBenefitUsage::with('benefit', 'sponsor')
            ->where('period', 'LIKE', $year . '-%')
            ->get();

        // Summary global
        $totalBenefits = $usageRecords->count();
        $usedBenefits = $usageRecords->where('status', 'used')->count();
        $unusedBenefits = $totalBenefits - $usedBenefits;
        $benefitUsageRate = $totalBenefits > 0 ? round(($usedBenefits / $totalBenefits) * 100) : 0;

        // Statistik per kategori benefit
        $categories = $usageRecords->groupBy(function ($item) {
            return $item->benefit->category;
        });
        $categoryStats = [];
        foreach ($categories as $category => $records) {
            $totalCat = $records->count();
            $usedCat = $records->where('status', 'used')->count();
            $percentUsed = $totalCat > 0 ? round(($usedCat / $totalCat) * 100) : 0;
            $categoryStats[] = [
                'category'     => $category,
                'total'        => $totalCat,
                'used'         => $usedCat,
                'percent_used' => $percentUsed,
            ];
        }

        // Daftar sponsor dengan perhitungan persentase penggunaan benefit per kategori
        $sponsorGroups = $usageRecords->groupBy('sponsor_id');
        $sponsorStats = [];
        foreach ($sponsorGroups as $sponsorId => $records) {
            $sponsorName = $records->first()->sponsor->name;
            // Grouping per kategori
            $catGroup = $records->groupBy(function ($item) {
                return $item->benefit->category;
            });
            $catPercentages = [];
            foreach ($catGroup as $category => $catRecords) {
                $total = $catRecords->count();
                $used = $catRecords->where('status', 'used')->count();
                $percent = $total > 0 ? round(($used / $total) * 100) : 0;
                $catPercentages[$category] = $percent;
            }
            $sponsorStats[] = [
                'sponsor_id'           => $sponsorId,
                'sponsor_name'         => $sponsorName,
                'category_percentages' => $catPercentages,
            ];
        }

        return view('admin.sponsor-benefit.index', compact(
            'year',
            'totalBenefits',
            'usedBenefits',
            'unusedBenefits',
            'benefitUsageRate',
            'categoryStats',
            'sponsorStats'
        ));
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
