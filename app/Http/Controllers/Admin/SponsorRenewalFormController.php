<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ScrapeHelper;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\PackageBenefit;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\Sponsors\SponsorRenewalForm;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Renewal form / proposal sponsor.
 *
 * Alur: renewal form WAJIB di-generate lebih dulu (store) — mencatat nomor form,
 * KMK rate, dan nilai proposal (USD/IDR). Baru setelah itu siklus follow-up boleh
 * berjalan (SponsorFollowupController memvalidasi keberadaan form ini).
 */
class SponsorRenewalFormController extends Controller
{
    private const WA_GROUP = '120363429723388586@g.us';

    private function buildData(int $sponsorId): array
    {
        $sponsor = Sponsor::with(['firstPic', 'currentRenewal'])->findOrFail($sponsorId);

        $packageBenefits = PackageBenefit::with('benefit')
            ->where('package_name', $sponsor->package)
            ->get()
            ->groupBy(function ($pb) {
                return $pb->benefit->category ?? 'Other';
            });

        // Sumber nilai proposal: renewal form terakhir yang di-generate untuk sponsor ini.
        $renewalForm = SponsorRenewalForm::where('sponsor_id', $sponsor->id)
            ->orderByDesc('renewal_year')
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        // KMK rate: dari renewal form; fallback ke follow-up lama (data pra-migrasi),
        // terakhir baru ke kurs live.
        $kursRate = $renewalForm && $renewalForm->kmk_rate ? $renewalForm->kmk_rate : null;

        if (!$kursRate) {
            $kursRate = SponsorFollowup::where('sponsor_id', $sponsor->id)
                ->whereNotNull('kmk_rate')
                ->orderBy('followed_up_at')
                ->orderBy('id')
                ->value('kmk_rate');
        }

        if (!$kursRate) {
            try {
                $kursRate = ScrapeHelper::scrapeExchangeRate();
            } catch (\Exception $e) {
                // silently fallback to null
            }
        }

        return [
            'sponsor'         => $sponsor,
            'renewal'         => $sponsor->currentRenewal,
            'renewalForm'     => $renewalForm,
            'packageBenefits' => $packageBenefits,
            'kursRate'        => $kursRate,
            'logoPath'        => public_path('image/logo-dmc-cci3.png'),
        ];
    }

    public function preview(int $sponsorId)
    {
        return view('admin.sponsor.renewal-form', $this->buildData($sponsorId));
    }

    public function generate(int $sponsorId)
    {
        $data = $this->buildData($sponsorId);

        $pdf = Pdf::setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'sans-serif',
            ])
            ->loadView('admin.sponsor.renewal-form', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'renewal-form-' . str_replace(' ', '-', strtolower($data['sponsor']->name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Nomor renewal form berikutnya (auto-suggest untuk modal Generate).
     */
    public function getNextFormNumber(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        return response()->json(['next' => SponsorRenewalForm::generateFormNumber($year)]);
    }

    /**
     * Renewal form yang sudah di-generate untuk sponsor + tahun tertentu — dipakai
     * untuk auto-prefill modal Update Contract (KMK, amount, quotation) supaya admin
     * tidak perlu input ulang. Nilai tetap bisa diedit (mis. bila ada penggantian).
     */
    public function latest(Request $request, int $sponsorId)
    {
        $year = (int) $request->get('year', now()->year);

        $form = SponsorRenewalForm::where('sponsor_id', $sponsorId)
            ->where('renewal_year', $year)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        if (!$form) {
            return response()->json(['success' => true, 'form' => null]);
        }

        return response()->json([
            'success' => true,
            'form'    => [
                'form_number' => $form->form_number,
                'kmk_rate'    => $form->kmk_rate,
                'kmk_number'  => $form->kmk_number,
                'amount_usd'  => $form->amount_usd,
                'amount_idr'  => $form->amount_idr,
                'notes'       => $form->notes,
            ],
        ]);
    }

    /**
     * Daftar sponsor untuk dropdown di modal Generate Renewal Form, lengkap dengan
     * nilai terakhir yang dibayar (amount_usd/idr dari kontrak renewed paling akhir)
     * supaya USD bisa auto-terisi saat sponsor dipilih.
     */
    public function sponsorOptions()
    {
        // Nominal terakhir dibayar per sponsor = kontrak renewed paling akhir yang punya nilai.
        $lastAmounts = SponsorRenewal::where('renewal_status', 'renewed')
            ->where(function ($q) {
                $q->whereNotNull('amount_usd')->orWhereNotNull('amount_idr');
            })
            ->orderBy('contract_start')
            ->orderBy('id')
            ->get(['sponsor_id', 'amount_usd', 'amount_idr'])
            ->groupBy('sponsor_id')
            ->map(function ($rows) {
                return $rows->last();
            });

        $sponsors = Sponsor::where('status', 'publish')
            ->orderBy('name')
            ->get(['id', 'name', 'package'])
            ->map(function ($s) use ($lastAmounts) {
                $last = $lastAmounts->get($s->id);

                return [
                    'id'              => $s->id,
                    'name'            => $s->name,
                    'package'         => $s->package,
                    'last_amount_usd' => $last ? $last->amount_usd : null,
                    'last_amount_idr' => $last ? $last->amount_idr : null,
                ];
            });

        return response()->json(['success' => true, 'sponsors' => $sponsors]);
    }

    /**
     * Catat renewal form (langkah pertama sebelum follow-up).
     */
    public function store(Request $request, int $sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        $validated = $request->validate([
            'renewal_year' => 'required|integer|min:2020|max:2100',
            'form_number'  => 'nullable|string|max:30',
            'kmk_rate'     => 'required|integer|min:1',
            'kmk_number'   => 'required|string|max:50',
            'amount_usd'   => 'nullable|numeric|min:0',
            'amount_idr'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:1000',
        ], [
            'kmk_rate.required'   => 'KMK rate wajib diisi saat generate renewal form.',
            'kmk_number.required' => 'KMK Nomor wajib diisi (cek di fiskal.kemenkeu.go.id).',
        ]);

        // Satu form per sponsor per tahun.
        $existing = SponsorRenewalForm::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $validated['renewal_year'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Renewal form untuk tahun ' . $validated['renewal_year'] . ' sudah pernah di-generate.',
            ], 422);
        }

        $formNumber = !empty($validated['form_number'])
            ? $validated['form_number']
            : SponsorRenewalForm::generateFormNumber((int) $validated['renewal_year']);

        if (SponsorRenewalForm::where('form_number', $formNumber)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor renewal form "' . $formNumber . '" sudah dipakai.',
            ], 422);
        }

        $form = SponsorRenewalForm::create([
            'sponsor_id'   => $sponsor->id,
            'renewal_year' => $validated['renewal_year'],
            'form_number'  => $formNumber,
            'kmk_rate'     => $validated['kmk_rate'],
            'kmk_number'   => $validated['kmk_number'],
            'amount_usd'   => $validated['amount_usd'] ?? null,
            'amount_idr'   => $validated['amount_idr'] ?? null,
            'notes'        => $validated['notes'] ?? null,
            'generated_at' => now()->toDateString(),
            'created_by'   => auth()->id(),
        ]);

        $this->notifyRenewalFormGenerated($sponsor, $form);

        return response()->json([
            'success'     => true,
            'message'     => 'Renewal form ' . $formNumber . ' untuk ' . $sponsor->name . ' berhasil di-generate.',
            'form_number' => $formNumber,
        ]);
    }

    /**
     * Kabari grup WhatsApp bahwa renewal form / proposal sudah dibuat, lengkap dengan
     * link preview. Menggantikan notifikasi yang dulu ada di follow-up pertama.
     */
    private function notifyRenewalFormGenerated(Sponsor $sponsor, SponsorRenewalForm $form): void
    {
        try {
            $contractEnd = $sponsor->contract_end
                ? Carbon::createFromFormat('Y-m', $sponsor->contract_end)->format('M Y')
                : '-';

            $lines = [
                '📄 *RENEWAL FORM DIBUAT*',
                '',
                'Sponsor: *' . $sponsor->name . '* (' . ucfirst($sponsor->package ?? '-') . ')',
                'Kontrak berakhir: ' . $contractEnd,
                'No. Form: *' . $form->form_number . '*',
                'Tahun renewal: ' . $form->renewal_year,
            ];
            if ($form->kmk_rate) {
                $lines[] = 'KMK Rate: IDR ' . number_format($form->kmk_rate, 0, '.', '.') . '/USD';
            }
            if ($form->kmk_number) {
                $lines[] = 'KMK Nomor: ' . $form->kmk_number;
            }
            if ($form->amount_usd) {
                $lines[] = 'Nilai: USD ' . number_format($form->amount_usd, 0, '.', '.');
            }
            if ($form->amount_idr) {
                $lines[] = 'Nilai: IDR ' . number_format($form->amount_idr, 0, '.', '.');
            }
            if ($form->notes) {
                $lines[] = 'Notes: ' . $form->notes;
            }
            $lines[] = 'Oleh: ' . (auth()->user()->name ?? '-');
            $lines[] = '';
            $lines[] = '📎 *Renewal Form (proposal):*';
            $lines[] = config('app.url') . '/admin/sponsors/' . $sponsor->id . '/renewal-form/preview';
            $lines[] = '';
            $lines[] = '_Langkah berikutnya: follow-up ke sponsor sampai ada keputusan (renew / not renew)._';

            $wa = new WhatsappApi();
            $wa->phone   = self::WA_GROUP;
            $wa->message = implode("\n", $lines);
            $wa->WhatsappMessageGroup();
        } catch (\Exception $e) {
            Log::warning('WA renewal-form notification failed: ' . $e->getMessage());
        }
    }
}
