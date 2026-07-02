<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewalForm;
use Illuminate\Http\Request;

/**
 * Pencatatan follow-up renewal sponsor. Tiap follow-up wajib menyertakan bukti.
 *
 * Prasyarat: renewal form tahun bersangkutan HARUS sudah di-generate lebih dulu
 * (lihat SponsorRenewalFormController). Keputusan finalnya tetap lewat
 * updateContract / markNotRenewed.
 */
class SponsorFollowupController extends Controller
{
    /**
     * Riwayat follow-up + status renewal form sebuah sponsor (JSON, untuk modal).
     */
    public function index($sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        $followups = $sponsor->followups()
            ->with('creator:id,name')
            ->orderBy('followed_up_at')
            ->orderBy('id')
            ->get()
            ->map(fn ($f, $i) => [
                'sequence'       => $i + 1,
                'renewal_year'   => (int) $f->renewal_year,
                'followed_up_at' => $f->followed_up_at->format('d M Y'),
                'channel'        => $f->channel,
                'notes'          => $f->notes,
                'proof_url'      => asset('storage/' . $f->proof_path),
                'created_by'     => $f->creator ? $f->creator->name : null,
            ]);

        $renewalForms = $sponsor->renewalForms()
            ->with('creator:id,name')
            ->orderBy('renewal_year')
            ->get()
            ->map(fn ($rf) => [
                'renewal_year' => (int) $rf->renewal_year,
                'form_number'  => $rf->form_number,
                'kmk_rate'     => $rf->kmk_rate,
                'amount_usd'   => $rf->amount_usd,
                'amount_idr'   => $rf->amount_idr,
                'notes'        => $rf->notes,
                'generated_at' => $rf->generated_at ? $rf->generated_at->format('d M Y') : null,
                'created_by'   => $rf->creator ? $rf->creator->name : null,
            ]);

        return response()->json([
            'success'      => true,
            'followups'    => $followups,
            'renewalForms' => $renewalForms,
        ]);
    }

    public function store(Request $request, $sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        $validated = $request->validate([
            'renewal_year'   => 'required|integer|min:2020|max:2100',
            'followed_up_at' => 'required|date',
            'channel'        => 'nullable|in:whatsapp,email,call,meeting,other',
            'notes'          => 'nullable|string|max:1000',
            'proof'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'proof.required' => 'Bukti follow-up wajib diupload.',
            'proof.mimes'    => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'proof.max'      => 'Ukuran bukti maksimal 5 MB.',
        ]);

        // Gate: renewal form tahun tsb harus sudah di-generate lebih dulu.
        $formExists = SponsorRenewalForm::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->exists();

        if (!$formExists) {
            return response()->json([
                'success' => false,
                'message' => 'Generate renewal form dulu untuk tahun ' . $validated['renewal_year'] . ' sebelum mencatat follow-up.',
            ], 422);
        }

        $sequence = SponsorFollowup::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->count() + 1;

        $proofPath = $request->file('proof')->store('sponsor-followups', 'public');

        SponsorFollowup::create([
            'sponsor_id'     => $sponsor->id,
            'renewal_year'   => $validated['renewal_year'],
            'followed_up_at' => $validated['followed_up_at'],
            'channel'        => $validated['channel'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'proof_path'     => $proofPath,
            'created_by'     => auth()->id(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Follow-up #' . $sequence . ' untuk ' . $sponsor->name . ' tercatat.',
            'sequence' => $sequence,
        ]);
    }
}
