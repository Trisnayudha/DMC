<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\SponsorRenewal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Riwayat contract (sponsor_renewals) lintas sponsor, dengan kemampuan edit
 * untuk membetulkan data setelah renew/decline tercatat — mis. mengisi Paid
 * Date belakangan setelah sponsor bayar invoice, atau membetulkan input yang
 * salah saat renew/decline.
 */
class SponsorContractHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $year    = $request->query('year');
        $status  = $request->query('status');
        $package = $request->query('package');

        $renewals = SponsorRenewal::with('sponsor:id,name,branding_name')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('sponsor', function ($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%');
                });
            })
            ->when($year, fn ($q) => $q->where('renewal_year', $year))
            ->when($status, fn ($q) => $q->where('renewal_status', $status))
            ->when($package, fn ($q) => $q->where('package', $package))
            ->orderByDesc('renewal_year')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $availableYears = SponsorRenewal::select('renewal_year')
            ->distinct()
            ->orderByDesc('renewal_year')
            ->pluck('renewal_year');

        return view('admin.sponsor.contract-history', [
            'renewals'       => $renewals,
            'search'         => $search,
            'year'           => $year,
            'status'         => $status,
            'package'        => $package,
            'availableYears' => $availableYears,
        ]);
    }

    public function update(Request $request, SponsorRenewal $renewal)
    {
        $validated = $request->validate([
            'contract_start'   => 'required|date_format:Y-m',
            'contract_end'     => 'required|date_format:Y-m',
            'package'          => 'nullable|in:platinum,gold,silver',
            'renewal_type'     => 'nullable|in:renewal,upgrade,new,new_member',
            'amount_usd'       => 'nullable|numeric|min:0',
            'amount_idr'       => 'nullable|numeric|min:0',
            'quotation_number' => 'nullable|string|max:30|unique:sponsor_renewals,quotation_number,' . $renewal->id,
            'quotation_date'   => 'nullable|date',
            'invoice_date'     => 'nullable|date',
            'invoice_number'   => 'nullable|string|max:30|unique:sponsor_renewals,invoice_number,' . $renewal->id,
            'paid_date'        => 'nullable|date|after_or_equal:invoice_date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $start = Carbon::createFromFormat('Y-m', $validated['contract_start']);
        $end   = Carbon::createFromFormat('Y-m', $validated['contract_end']);

        if ($end->lt($start)) {
            return response()->json([
                'success' => false,
                'message' => 'Contract end must be after contract start.',
            ], 422);
        }

        DB::transaction(function () use ($renewal, $validated) {
            $renewal->update($validated);

            if ($renewal->is_current && $renewal->sponsor) {
                $renewal->sponsor->update([
                    'contract_start' => $validated['contract_start'],
                    'contract_end'   => $validated['contract_end'],
                    'package'        => $validated['package'] ?? $renewal->sponsor->package,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Contract record updated successfully.',
        ]);
    }
}
