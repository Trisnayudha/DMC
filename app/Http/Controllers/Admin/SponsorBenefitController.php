<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\PackageBenefit;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorBenefitUsage;
use App\Models\Sponsors\SponsorBenefitUsageMark;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SponsorBenefitController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->format('Y'));

        $usageRecords = SponsorBenefitUsage::with('benefit', 'sponsor')
            ->where(function ($q) use ($year) {
                $q->where('period', $year)
                  ->orWhere('period', 'LIKE', $year . '-%')
                  ->orWhere('period', 'LIKE', '%-' . $year);
            })
            ->get();

        $totalBenefits    = $usageRecords->count();
        $usedBenefits     = $usageRecords->where('status', 'used')->count();
        $unusedBenefits   = $totalBenefits - $usedBenefits;
        $benefitUsageRate = $totalBenefits > 0 ? round(($usedBenefits / $totalBenefits) * 100) : 0;

        $categories = $usageRecords->groupBy(function ($item) {
            return $item->benefit->category;
        });
        $categoryStats = [];
        foreach ($categories as $category => $records) {
            $totalCat    = $records->count();
            $usedCat     = $records->where('status', 'used')->count();
            $percentUsed = $totalCat > 0 ? round(($usedCat / $totalCat) * 100) : 0;
            $categoryStats[] = [
                'category'     => $category,
                'total'        => $totalCat,
                'used'         => $usedCat,
                'percent_used' => $percentUsed,
            ];
        }

        $sponsorGroups = $usageRecords->groupBy('sponsor_id');
        $sponsorStats  = [];
        foreach ($sponsorGroups as $sponsorId => $records) {
            $sponsorName    = $records->first()->sponsor->name;
            $catGroup       = $records->groupBy(function ($item) { return $item->benefit->category; });
            $catPercentages = [];
            foreach ($catGroup as $category => $catRecords) {
                $total   = $catRecords->count();
                $used    = $catRecords->where('status', 'used')->count();
                $catPercentages[$category] = $total > 0 ? round(($used / $total) * 100) : 0;
            }
            $sponsorStats[] = [
                'sponsor_id'           => $sponsorId,
                'sponsor_name'         => $sponsorName,
                'category_percentages' => $catPercentages,
            ];
        }

        return view('admin.sponsor-benefit.index', compact(
            'year', 'totalBenefits', 'usedBenefits', 'unusedBenefits',
            'benefitUsageRate', 'categoryStats', 'sponsorStats'
        ));
    }

    public function detail(int $sponsorId)
    {
        $sponsor = Sponsor::where('id', $sponsorId)
            ->where('status', 'publish')
            ->firstOrFail();

        $startYear  = Carbon::createFromFormat('Y-m', $sponsor->contract_start)->year;
        $endYear    = Carbon::createFromFormat('Y-m', $sponsor->contract_end)->year;
        $activePeriod = $startYear === $endYear ? (string) $startYear : "{$startYear}-{$endYear}";

        $benefitDetails = SponsorBenefitUsage::with(['benefit', 'marks.createdBy'])
            ->where('sponsor_id', $sponsor->id)
            ->where('period', $activePeriod)
            ->orderBy('benefit_id')
            ->get();

        // Quantity per benefit dari package_benefit (kuota berdasarkan package sponsor)
        $quantityMap = PackageBenefit::where('package_name', $sponsor->package)
            ->pluck('quantity', 'benefit_id');

        $totalCount       = $benefitDetails->count();
        $usedCount        = $benefitDetails->where('status', 'used')->count();
        $benefitUsageRate = $totalCount > 0 ? round(($usedCount / $totalCount) * 100) : 0;

        return view('admin.sponsor-benefit.detail', compact(
            'sponsor', 'benefitDetails', 'quantityMap',
            'usedCount', 'totalCount', 'benefitUsageRate', 'activePeriod'
        ));
    }

    public function addMark(Request $request, int $usageId)
    {
        $request->validate([
            'marked_at'   => 'required|date',
            'note'        => 'nullable|string|max:255',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $usage = SponsorBenefitUsage::findOrFail($usageId);

        $quantity   = PackageBenefit::where('package_name', $usage->sponsor->package)
            ->where('benefit_id', $usage->benefit_id)
            ->value('quantity') ?? 1;

        if ($usage->marks()->count() >= $quantity) {
            return back()->with('error', 'Kuota benefit ini sudah penuh (' . $quantity . '/' . $quantity . ').');
        }

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $file      = $request->file('proof_image');
            $filename  = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/sponsor-benefit', $filename);
            $proofPath = '/storage/sponsor-benefit/' . $filename;
        }

        SponsorBenefitUsageMark::create([
            'sponsor_benefit_usage_id' => $usage->id,
            'marked_at'                => $request->marked_at,
            'note'                     => $request->note,
            'proof_image'              => $proofPath,
            'created_by'               => Auth::id(),
        ]);

        // Sync status lama supaya index page tetap akurat
        $marksCount = $usage->marks()->count();
        $usage->status  = $marksCount >= $quantity ? 'used' : 'unused';
        $usage->used_at = $marksCount >= $quantity ? now() : null;
        $usage->save();

        return back()->with('success', 'Mark berhasil ditambahkan.');
    }

    public function removeMark(int $markId)
    {
        $mark  = SponsorBenefitUsageMark::findOrFail($markId);
        $usage = $mark->usage;

        if ($mark->proof_image) {
            $localPath = str_replace('/storage/', 'public/', $mark->proof_image);
            Storage::delete($localPath);
        }

        $mark->delete();

        // Sync status lama
        $quantity   = PackageBenefit::where('package_name', $usage->sponsor->package)
            ->where('benefit_id', $usage->benefit_id)
            ->value('quantity') ?? 1;
        $marksCount = $usage->marks()->count();
        $usage->status  = $marksCount >= $quantity ? 'used' : 'unused';
        $usage->used_at = $marksCount >= $quantity ? now() : null;
        $usage->save();

        return back()->with('success', 'Mark berhasil dihapus.');
    }

    // Kept for backward compatibility — halaman lama yang masih pakai route ini
    public function markUsed(int $id)
    {
        $benefitUsage = SponsorBenefitUsage::findOrFail($id);
        if ($benefitUsage->status !== 'used') {
            $benefitUsage->status  = 'used';
            $benefitUsage->used_at = Carbon::now();
            $benefitUsage->save();
        }
        return redirect()->back()->with('success', 'Benefit telah ditandai sebagai digunakan.');
    }
}
