<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Pencatatan follow-up renewal sponsor: tiap follow-up wajib menyertakan bukti,
 * dan follow-up PERTAMA dalam satu siklus (sponsor+tahun) mengirim notifikasi
 * ke grup WhatsApp. Keputusan finalnya tetap lewat updateContract / markNotRenewed.
 */
class SponsorFollowupController extends Controller
{
    private const WA_GROUP = '120363429723388586@g.us';

    /**
     * Riwayat follow-up sebuah sponsor (JSON, untuk timeline di modal).
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
                'renewal_year'   => $f->renewal_year,
                'followed_up_at' => $f->followed_up_at->format('d M Y'),
                'channel'        => $f->channel,
                'notes'          => $f->notes,
                'kmk_rate'       => $f->kmk_rate,
                'proof_url'      => asset('storage/' . $f->proof_path),
                'created_by'     => $f->creator ? $f->creator->name : null,
            ]);

        return response()->json(['success' => true, 'followups' => $followups]);
    }

    public function store(Request $request, $sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        // Tentukan urutan follow-up dulu: KMK rate hanya wajib di follow-up PERTAMA
        // (saat renewal form di-generate). Follow-up berikutnya tidak perlu input KMK.
        $renewalYear = (int) $request->input('renewal_year');
        $isFirst = SponsorFollowup::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $renewalYear)
            ->count() === 0;

        $validated = $request->validate([
            'renewal_year'   => 'required|integer|min:2020|max:2100',
            'followed_up_at' => 'required|date',
            'channel'        => 'nullable|in:whatsapp,email,call,meeting,other',
            'notes'          => 'nullable|string|max:1000',
            'kmk_rate'       => ($isFirst ? 'required' : 'nullable') . '|integer|min:1',
            'proof'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'kmk_rate.required' => 'KMK rate wajib diisi pada follow-up pertama (saat generate renewal form).',
            'proof.required'    => 'Bukti follow-up wajib diupload.',
            'proof.mimes'       => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'proof.max'         => 'Ukuran bukti maksimal 5 MB.',
        ]);

        $sequence = SponsorFollowup::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->count() + 1;

        $proofPath = $request->file('proof')->store('sponsor-followups', 'public');

        $followup = SponsorFollowup::create([
            'sponsor_id'     => $sponsor->id,
            'renewal_year'   => $validated['renewal_year'],
            'followed_up_at' => $validated['followed_up_at'],
            'channel'        => $validated['channel'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'kmk_rate'       => $validated['kmk_rate'] ?? null,
            'proof_path'     => $proofPath,
            'created_by'     => auth()->id(),
        ]);

        // Follow-up pertama dalam siklus ini → kabari grup bahwa pengejaran dimulai
        if ($sequence === 1) {
            $this->notifyFirstFollowup($sponsor, $followup);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Follow-up #' . $sequence . ' untuk ' . $sponsor->name . ' tercatat.',
            'sequence' => $sequence,
        ]);
    }

    private function notifyFirstFollowup(Sponsor $sponsor, SponsorFollowup $followup): void
    {
        try {
            $contractEnd = $sponsor->contract_end
                ? Carbon::createFromFormat('Y-m', $sponsor->contract_end)->format('M Y')
                : '-';

            $lines = [
                '📞 *RENEWAL FOLLOW-UP DIMULAI*',
                '',
                'Sponsor: *' . $sponsor->name . '* (' . ucfirst($sponsor->package ?? '-') . ')',
                'Kontrak berakhir: ' . $contractEnd,
                'Follow-up #1: ' . $followup->followed_up_at->format('d M Y')
                    . ($followup->channel ? ' via ' . ucfirst($followup->channel) : ''),
                'Oleh: ' . (auth()->user()->name ?? '-'),
            ];
            if ($followup->kmk_rate) {
                $lines[] = 'KMK Rate: IDR ' . number_format($followup->kmk_rate, 0, '.', '.') . '/USD';
            }
            if ($followup->notes) {
                $lines[] = 'Notes: ' . $followup->notes;
            }
            $lines[] = '';
            $lines[] = '📄 *Renewal Form (proposal):*';
            $lines[] = config('app.url') . '/admin/sponsors/' . $sponsor->id . '/renewal-form/preview';
            $lines[] = '';
            $lines[] = '_Status: menunggu keputusan sponsor (renew / not renew)._';

            $wa = new WhatsappApi();
            $wa->phone   = self::WA_GROUP;
            $wa->message = implode("\n", $lines);
            $wa->WhatsappMessageGroup();
        } catch (\Exception $e) {
            Log::warning('WA follow-up notification failed: ' . $e->getMessage());
        }
    }
}
