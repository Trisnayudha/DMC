<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewalForm;
use App\Services\Sponsors\RenewalConfirmedFinanceNotifier;
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
                'confirmed'      => (bool) $f->confirmed_renewal,
                'proof_url'      => asset('storage/' . $f->proof_path),
                'created_by'     => $f->creator ? $f->creator->name : null,
            ]);

        $renewalForms = $sponsor->renewalForms()
            ->with('creator:id,name')
            ->orderBy('renewal_year')
            ->get()
            ->map(fn ($rf) => [
                'renewal_year'     => (int) $rf->renewal_year,
                'form_number'      => $rf->form_number,
                'kmk_rate'         => $rf->kmk_rate,
                'kmk_number'       => $rf->kmk_number,
                'amount_usd'       => $rf->amount_usd,
                'amount_idr'       => $rf->amount_idr,
                'vat_percent'      => $rf->vat_percent,
                'notes'            => $rf->notes,
                'generated_at'     => $rf->generated_at ? $rf->generated_at->format('d M Y') : null,
                'generated_at_iso' => $rf->generated_at ? $rf->generated_at->format('Y-m-d') : null,
                'created_by'       => $rf->creator ? $rf->creator->name : null,
            ]);

        $contact = $sponsor->billingContact();

        return response()->json([
            'success'      => true,
            'followups'    => $followups,
            'renewalForms' => $renewalForms,
            'billing'      => $contact ? [
                'name'     => $contact->name,
                'title'    => $contact->title,
                'email'    => $contact->email,
                'phone'    => $contact->phone,
                // true = sponsor belum punya billing contact, field diisi dari PIC
                'from_pic' => !$sponsor->firstBilling,
            ] : null,
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
            'confirmed'      => 'nullable|boolean',
            'proof'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'proof.required' => 'Follow-up proof is required.',
            'proof.mimes'    => 'Proof must be a JPG, PNG, or PDF.',
            'proof.max'      => 'Proof file size must not exceed 5 MB.',
        ]);

        // Gate: renewal form tahun tsb harus sudah di-generate lebih dulu.
        $formExists = SponsorRenewalForm::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->exists();

        if (!$formExists) {
            return response()->json([
                'success' => false,
                'message' => 'Generate the renewal form for year ' . $validated['renewal_year'] . ' first before recording a follow-up.',
            ], 422);
        }

        $sequence = SponsorFollowup::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->count() + 1;

        $proofPath = $request->file('proof')->store('sponsor-followups', 'public');

        $confirmed = !empty($validated['confirmed']);

        $followup = SponsorFollowup::create([
            'sponsor_id'        => $sponsor->id,
            'renewal_year'      => $validated['renewal_year'],
            'followed_up_at'    => $validated['followed_up_at'],
            'channel'           => $validated['channel'] ?? null,
            'notes'             => $validated['notes'] ?? null,
            'confirmed_renewal' => $confirmed,
            'proof_path'        => $proofPath,
            'created_by'        => auth()->id(),
        ]);

        // Sponsor sudah konfirmasi renew → kabari finance untuk issue invoice (manual di Accurate)
        if ($confirmed) {
            RenewalConfirmedFinanceNotifier::send($sponsor, $followup);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Follow-up #' . $sequence . ' for ' . $sponsor->name . ' recorded.'
                . ($confirmed ? ' Finance has been notified to issue the invoice.' : ''),
            'sequence' => $sequence,
        ]);
    }
}
