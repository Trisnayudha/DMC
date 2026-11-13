<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\Sponsors\SponsorRenewalForm;
use App\Services\Sponsors\RenewalConfirmedFinanceNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Pencatatan follow-up sponsor per tahun renewal. Tiap follow-up wajib menyertakan bukti.
 *
 * Dua jenis (kolom type):
 * - renewal : tim Secretariat, sampai sponsor konfirmasi renew. Prasyarat: renewal
 *             form tahun bersangkutan sudah di-generate (SponsorRenewalFormController).
 * - invoice : tim Finance, setelah invoice terbit (kontrak baru tercatat + invoice
 *             number), sampai sponsor bayar. Confirmed = paid → paid_date kontrak terisi.
 *
 * Kedua tim bisa melihat riwayat keduanya. Keputusan kontrak tetap lewat
 * updateContract / markNotRenewed.
 */
class SponsorFollowupController extends Controller
{
    /**
     * Riwayat follow-up + renewal form + kontrak renewed per tahun (JSON, untuk modal).
     */
    public function index($sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        return response()->json([
            'success'      => true,
            'followups'    => $this->followupRows($sponsor),
            'renewalForms' => $this->renewalFormRows($sponsor),
            'invoices'     => $this->invoiceRows($sponsor),
            'billing'      => $this->billingRow($sponsor),
        ]);
    }

    public function store(Request $request, $sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        $validated = $request->validate([
            'type'           => 'nullable|in:' . SponsorFollowup::TYPE_RENEWAL . ',' . SponsorFollowup::TYPE_INVOICE,
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

        $type = $validated['type'] ?? SponsorFollowup::TYPE_RENEWAL;

        if (!SponsorFollowup::userCanRecord($type, auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => $type === SponsorFollowup::TYPE_INVOICE
                    ? 'Only the Finance team can record invoice follow-ups.'
                    : 'Only the Secretariat team can record renewal follow-ups.',
            ], 403);
        }

        return $type === SponsorFollowup::TYPE_INVOICE
            ? $this->storeInvoiceFollowup($request, $sponsor, $validated)
            : $this->storeRenewalFollowup($request, $sponsor, $validated);
    }

    private function storeRenewalFollowup(Request $request, Sponsor $sponsor, array $validated)
    {
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

        $confirmed = !empty($validated['confirmed']);
        $sequence  = $this->nextSequence($sponsor, $validated['renewal_year'], SponsorFollowup::TYPE_RENEWAL);

        $followup = SponsorFollowup::create($this->baseAttributes($request, $sponsor, $validated, SponsorFollowup::TYPE_RENEWAL) + [
            'confirmed_renewal' => $confirmed,
        ]);

        // Sponsor sudah konfirmasi renew → kabari finance untuk issue invoice (manual di Accurate)
        if ($confirmed) {
            RenewalConfirmedFinanceNotifier::send($sponsor, $followup);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Renewal follow-up #' . $sequence . ' for ' . $sponsor->name . ' recorded.'
                . ($confirmed ? ' Finance has been notified to issue the invoice.' : ''),
            'sequence' => $sequence,
        ]);
    }

    private function storeInvoiceFollowup(Request $request, Sponsor $sponsor, array $validated)
    {
        if (!SponsorFollowup::hasTypeColumn()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice follow-up is not available yet. Please contact the administrator.',
            ], 422);
        }

        // Gate: kontrak baru tahun tsb sudah tercatat dengan invoice number.
        $renewal = $this->invoicedRenewal($sponsor, $validated['renewal_year']);
        if (!$renewal) {
            return response()->json([
                'success' => false,
                'message' => 'No invoice recorded for ' . $validated['renewal_year'] . ' yet. Confirm the renewal and fill in the invoice number first.',
            ], 422);
        }
        if ($renewal->paid_date) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice ' . $renewal->invoice_number . ' is already paid on ' . $renewal->paid_date->format('d M Y') . '.',
            ], 422);
        }

        $paid       = !empty($validated['confirmed']);
        $followedAt = Carbon::parse($validated['followed_up_at'])->startOfDay();
        if ($paid && $renewal->invoice_date && $followedAt->lt($renewal->invoice_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Paid date cannot be earlier than the invoice date (' . $renewal->invoice_date->format('d M Y') . ').',
            ], 422);
        }

        $sequence = $this->nextSequence($sponsor, $validated['renewal_year'], SponsorFollowup::TYPE_INVOICE);

        SponsorFollowup::create($this->baseAttributes($request, $sponsor, $validated, SponsorFollowup::TYPE_INVOICE) + [
            'confirmed_payment' => $paid,
        ]);

        // Paid → isi paid_date kontrak. SponsorRenewal sendiri yang mengirim email
        // "payment received" ke secretariat saat paid_date pertama kali terisi.
        if ($paid) {
            $renewal->update(['paid_date' => $followedAt->toDateString()]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Invoice follow-up #' . $sequence . ' for ' . $sponsor->name . ' recorded.'
                . ($paid ? ' Invoice marked as paid and the Secretariat has been notified.' : ''),
            'sequence' => $sequence,
        ]);
    }

    private function baseAttributes(Request $request, Sponsor $sponsor, array $validated, string $type): array
    {
        $attributes = [
            'sponsor_id'     => $sponsor->id,
            'renewal_year'   => $validated['renewal_year'],
            'followed_up_at' => $validated['followed_up_at'],
            'channel'        => $validated['channel'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'proof_path'     => $request->file('proof')->store('sponsor-followups', 'public'),
            'created_by'     => auth()->id(),
        ];
        if (SponsorFollowup::hasTypeColumn()) {
            $attributes['type'] = $type;
        }
        return $attributes;
    }

    private function nextSequence(Sponsor $sponsor, int $year, string $type): int
    {
        return SponsorFollowup::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $year)
            ->ofType($type)
            ->count() + 1;
    }

    /** Kontrak renewed tahun tsb yang sudah punya invoice number (terbaru kalau ada >1). */
    private function invoicedRenewal(Sponsor $sponsor, int $year): ?SponsorRenewal
    {
        return SponsorRenewal::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $year)
            ->where('renewal_status', 'renewed')
            ->whereNotNull('invoice_number')
            ->orderByDesc('contract_start')
            ->orderByDesc('id')
            ->first();
    }

    private function followupRows(Sponsor $sponsor)
    {
        return $sponsor->followups()
            ->with('creator:id,name')
            ->orderBy('id')
            ->get()
            ->map(fn ($f) => [
                'type'           => $f->type ?: SponsorFollowup::TYPE_RENEWAL,
                'renewal_year'   => (int) $f->renewal_year,
                'followed_up_at' => $f->followed_up_at->format('d M Y'),
                'channel'        => $f->channel,
                'notes'          => $f->notes,
                'confirmed'      => $f->isInvoice() ? (bool) $f->confirmed_payment : (bool) $f->confirmed_renewal,
                'proof_url'      => asset('storage/' . $f->proof_path),
                'created_by'     => $f->creator ? $f->creator->name : null,
            ])
            ->values();
    }

    private function renewalFormRows(Sponsor $sponsor)
    {
        return $sponsor->renewalForms()
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
                // Jejak email ke secretariat (null sebelum migration emailed_* / belum pernah terkirim).
                'emailed_ago'      => $rf->emailed_at ? $rf->emailed_at->copy()->locale('en')->diffForHumans() : null,
                'emailed_at'       => $rf->emailed_at ? $rf->emailed_at->format('d M Y, H:i') : null,
                'emailed_to'       => $rf->emailed_to,
                'email_count'      => (int) $rf->email_count,
            ]);
    }

    /** Invoice per tahun renewal, untuk Step 3 (Invoice Follow-up). */
    private function invoiceRows(Sponsor $sponsor)
    {
        return SponsorRenewal::where('sponsor_id', $sponsor->id)
            ->where('renewal_status', 'renewed')
            ->whereNotNull('invoice_number')
            ->orderByDesc('contract_start')
            ->orderByDesc('id')
            ->get()
            ->unique('renewal_year')
            ->map(fn ($r) => [
                'renewal_year'      => (int) $r->renewal_year,
                'invoice_number'    => $r->invoice_number,
                'invoice_date'      => $r->invoice_date ? $r->invoice_date->format('d M Y') : null,
                'invoice_sent_date' => $r->invoice_sent_date ? $r->invoice_sent_date->format('d M Y') : null,
                'paid_date'         => $r->paid_date ? $r->paid_date->format('d M Y') : null,
            ])
            ->values();
    }

    private function billingRow(Sponsor $sponsor): ?array
    {
        $contact = $sponsor->billingContact();
        if (!$contact) {
            return null;
        }

        return [
            'name'       => $contact->name,
            'salutation' => $contact->salutation,
            'title'      => $contact->title,
            'email'      => $contact->email,
            'phone'      => $contact->phone,
            // true = sponsor belum punya billing contact, field diisi dari PIC
            'from_pic'   => !$sponsor->firstBilling,
        ];
    }
}
