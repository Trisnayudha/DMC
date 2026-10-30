<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ScrapeHelper;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Sponsors\PackageBenefit;
use App\Models\Sponsors\Sponsor;
use App\Models\Sponsors\SponsorFollowup;
use App\Models\Sponsors\SponsorBilling;
use App\Models\Sponsors\SponsorRenewal;
use App\Models\Sponsors\SponsorRenewalForm;
use App\Services\Sponsors\RenewalFormSecretariatNotifier;
use App\Services\Sponsors\RenewalPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

    /** @param SponsorRenewalForm|null $draftForm form belum disimpan (Preview sebelum Generate) */
    private function buildData(int $sponsorId, ?SponsorRenewalForm $draftForm = null): array
    {
        $sponsor = Sponsor::with(['firstPic', 'firstBilling', 'currentRenewal'])->findOrFail($sponsorId);

        $packageBenefits = PackageBenefit::with('benefit')
            ->where('package_name', $sponsor->package)
            ->get()
            ->groupBy(function ($pb) {
                return $pb->benefit->category ?? 'Other';
            });

        // Sumber nilai proposal: draft yang sedang diisi, atau renewal form terakhir
        // yang di-generate untuk sponsor ini.
        $renewalForm = $draftForm ?: SponsorRenewalForm::where('sponsor_id', $sponsor->id)
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
            // Periode yang ditawarkan = periode renewal tahun form, bukan kontrak berjalan
            // (sebelum confirm renew, currentRenewal masih kontrak lama).
            'renewalPeriod'   => $renewalForm ? RenewalPeriod::forForm($sponsor, $renewalForm)['renewal'] : null,
            'packageBenefits' => $packageBenefits,
            'kursRate'        => $kursRate,
            'logoPath'        => public_path('image/logo-dmc-cci3.png'),
        ];
    }

    public function preview(int $sponsorId)
    {
        // isPreview = true → tampilkan tombol Download PDF (disembunyikan saat generate PDF)
        return view('admin.sponsor.renewal-form', $this->buildData($sponsorId) + ['isPreview' => true]);
    }

    /**
     * Preview PDF dari isian form yang belum disimpan (tombol Preview di samping Generate).
     * Tidak menyimpan apa pun & tidak mengirim email.
     */
    public function draftPreview(Request $request, int $sponsorId)
    {
        $input = $request->validate([
            'renewal_year'  => 'required|integer|min:2020|max:2100',
            'form_number'   => 'nullable|string|max:30',
            'kmk_rate'      => 'nullable|integer|min:1',
            'kmk_number'    => 'nullable|string|max:50',
            'amount_usd'    => 'nullable|numeric|min:0',
            'amount_idr'    => 'nullable|numeric|min:0',
            'vat_percent'   => 'nullable|numeric|min:0|max:100',
            'notes'         => 'nullable|string|max:1000',
            'generated_at'  => 'nullable|date',
            'billing_name'  => 'nullable|string|max:255',
            'billing_title' => 'nullable|string|max:255',
            'billing_email' => 'nullable|string|max:255',
            'billing_phone' => 'nullable|string|max:50',
        ]);

        $draft = new SponsorRenewalForm([
            'renewal_year' => $input['renewal_year'],
            'form_number'  => ($input['form_number'] ?? '') ?: SponsorRenewalForm::generateFormNumber((int) $input['renewal_year']),
            'kmk_rate'     => $input['kmk_rate'] ?? null,
            'kmk_number'   => $input['kmk_number'] ?? null,
            'amount_usd'   => $input['amount_usd'] ?? null,
            'amount_idr'   => $input['amount_idr'] ?? null,
            'vat_percent'  => $input['vat_percent'] ?? 0,
            'notes'        => $input['notes'] ?? null,
            'generated_at' => $input['generated_at'] ?? now()->toDateString(),
        ]);
        $draft->sponsor_id = $sponsorId;

        $data = $this->buildData($sponsorId, $draft);

        // Billing contact yang sedang diketik ikut ke baris Attention (kosong = kontak tersimpan).
        $typed = array_filter([
            'name'  => $input['billing_name'] ?? null,
            'title' => $input['billing_title'] ?? null,
            'email' => $input['billing_email'] ?? null,
            'phone' => $input['billing_phone'] ?? null,
        ]);
        $contactOverride = $typed ? (object) array_merge(['name' => null, 'title' => null, 'email' => null, 'phone' => null], $typed) : null;

        return view('admin.sponsor.renewal-form', $data + [
            'isPreview'       => true,
            'isDraft'         => true,
            'contactOverride' => $contactOverride,
        ]);
    }

    public function generate(int $sponsorId)
    {
        $data = $this->buildData($sponsorId);

        return $this->buildPdf($data)->download($this->pdfFilename($data['sponsor']));
    }

    private function buildPdf(array $data)
    {
        return Pdf::setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'sans-serif',
            ])
            ->loadView('admin.sponsor.renewal-form', $data)
            ->setPaper('a4', 'portrait');
    }

    private function pdfFilename(Sponsor $sponsor): string
    {
        return 'renewal-form-' . str_replace(' ', '-', strtolower($sponsor->name)) . '.pdf';
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
     * Renewal form yang sudah di-generate untuk sponsor ini — dipakai untuk
     * auto-prefill modal Update Contract (KMK, amount, quotation) supaya admin
     * tidak perlu input ulang, dan juga ditampilkan sebagai referensi read-only
     * di modal Edit Contract Record (Contract History / Annual Report).
     *
     * Kalau `year` dikirim (dipakai modal Update Contract, selalu tahun berjalan),
     * dibatasi ke renewal_year itu. Kalau tidak dikirim, ambil form paling baru
     * apa pun tahunnya — supaya form yang baru di-generate tetap kelihatan
     * meski belum ada kontrak renewed untuk cycle tahun itu.
     */
    public function latest(Request $request, int $sponsorId)
    {
        $year = $request->filled('year') ? (int) $request->get('year') : null;

        $form = SponsorRenewalForm::where('sponsor_id', $sponsorId)
            ->when($year, fn ($q) => $q->where('renewal_year', $year))
            ->orderByDesc('renewal_year')
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        if (!$form) {
            return response()->json(['success' => true, 'form' => null]);
        }

        return response()->json([
            'success' => true,
            'form'    => [
                'renewal_year' => $form->renewal_year,
                'form_number'  => $form->form_number,
                'kmk_rate'     => $form->kmk_rate,
                'kmk_number'   => $form->kmk_number,
                'amount_usd'   => $form->amount_usd,
                'amount_idr'   => $form->amount_idr,
                'vat_percent'  => $form->vat_percent,
                'notes'        => $form->notes,
                'generated_at' => $form->generated_at ? $form->generated_at->format('d M Y') : null,
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
     * Catat renewal form (langkah pertama sebelum follow-up). Kalau form untuk
     * sponsor+tahun ini sudah pernah dibuat, submit ulang akan meng-update
     * record yang sama (regenerate) alih-alih ditolak — dipakai saat admin
     * sadar ada input yang kurang/salah dan perlu dibetulkan.
     */
    public function store(Request $request, int $sponsorId)
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        $existing = SponsorRenewalForm::where('sponsor_id', $sponsor->id)
            ->where('renewal_year', $request->input('renewal_year'))
            ->first();

        $validated = $request->validate([
            'renewal_year' => 'required|integer|min:2020|max:2100',
            'form_number'  => 'nullable|string|max:30',
            'kmk_rate'     => 'required|integer|min:1',
            'kmk_number'   => 'required|string|max:50',
            'amount_usd'   => 'nullable|numeric|min:0',
            'amount_idr'   => 'nullable|numeric|min:0',
            // Optional — tim finance yang nentuin company mana yang kena VAT. Dropdown
            // di UI cuma nawarin nilai yang valid (nggak ada input bebas/typo), validasi
            // range di sini tetap dijaga longgar (0-100) biar nggak perlu redeploy kalau
            // suatu saat ada tarif baru yang perlu ditambahkan ke dropdown.
            'vat_percent'  => 'nullable|numeric|min:0|max:100',
            'notes'        => 'nullable|string|max:1000',
            'generated_at' => 'nullable|date',
            'billing_name'  => 'nullable|string|max:255',
            'billing_title' => 'nullable|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'billing_phone' => 'nullable|string|max:50',
        ], [
            'kmk_rate.required'   => 'KMK rate is required when generating the renewal form.',
            'kmk_number.required' => 'KMK Number is required (check at fiskal.kemenkeu.go.id).',
        ]);

        $formNumber = !empty($validated['form_number'])
            ? $validated['form_number']
            : SponsorRenewalForm::generateFormNumber((int) $validated['renewal_year']);

        $numberTaken = SponsorRenewalForm::where('form_number', $formNumber)
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
            ->exists();

        if ($numberTaken) {
            return response()->json([
                'success' => false,
                'message' => 'Renewal form number "' . $formNumber . '" is already in use.',
            ], 422);
        }

        // Snapshot sebelum disimpan: dipakai untuk daftar "Changes in this update" di email.
        $billingBefore = $this->billingSnapshot($sponsor);

        $form = DB::transaction(function () use ($sponsor, $validated, $existing, $formNumber) {
            $form = SponsorRenewalForm::updateOrCreate(
                ['sponsor_id' => $sponsor->id, 'renewal_year' => $validated['renewal_year']],
                [
                    'form_number'  => $formNumber,
                    'kmk_rate'     => $validated['kmk_rate'],
                    'kmk_number'   => $validated['kmk_number'],
                    'amount_usd'   => $validated['amount_usd'] ?? null,
                    'amount_idr'   => $validated['amount_idr'] ?? null,
                    'vat_percent'  => $validated['vat_percent'] ?? 0,
                    'notes'        => $validated['notes'] ?? null,
                    'generated_at' => $validated['generated_at'] ?? ($existing ? $existing->generated_at : now()->toDateString()),
                    'created_by'   => $existing ? $existing->created_by : auth()->id(),
                ]
            );

            // Billing contact hanya bisa diubah pemegang izin Sponsors; Finance read-only.
            $hasBillingInput = !empty($validated['billing_name']) || !empty($validated['billing_title'])
                || !empty($validated['billing_email']) || !empty($validated['billing_phone']);
            if ($hasBillingInput && auth()->user()->canAccessModule('sponsors')) {
                $billing = $sponsor->firstBilling ?? new SponsorBilling(['sponsor_id' => $sponsor->id]);
                $billing->fill([
                    'name'  => $validated['billing_name'] ?? $billing->name,
                    'title' => $validated['billing_title'] ?? $billing->title,
                    'email' => $validated['billing_email'] ?? $billing->email,
                    'phone' => $validated['billing_phone'] ?? $billing->phone,
                ]);
                $billing->sponsor_id = $sponsor->id;
                $billing->save();
            }

            return $form;
        });

        if (!$existing) {
            $this->notifyRenewalFormGenerated($sponsor, $form);
        }

        $changes = $existing
            ? array_merge($this->formChanges($existing, $form), $this->billingChanges($billingBefore, $this->billingSnapshot($sponsor->fresh())))
            : [];
        $this->emailRenewalFormToSecretariat($sponsor, $form, (bool) $existing, $changes);

        return response()->json([
            'success'     => true,
            'message'     => $existing
                ? 'Renewal form ' . $formNumber . ' for ' . $sponsor->name . ' updated successfully.'
                : 'Renewal form ' . $formNumber . ' for ' . $sponsor->name . ' generated successfully.',
            'form_number' => $formNumber,
        ]);
    }

    /** Field renewal form yang ditampilkan di daftar perubahan email, dengan label-nya. */
    private const CHANGE_LABELS = [
        'form_number'  => 'Form Number',
        'generated_at' => 'Quotation Date',
        'kmk_rate'     => 'KMK Rate (IDR/USD)',
        'kmk_number'   => 'KMK Number',
        'amount_usd'   => 'Amount (USD)',
        'amount_idr'   => 'Amount (IDR)',
        'vat_percent'  => 'VAT (%)',
        'notes'        => 'Notes',
    ];

    /** Perubahan field renewal form: [label => ['old' => ..., 'new' => ...]]. */
    private function formChanges(SponsorRenewalForm $before, SponsorRenewalForm $after): array
    {
        $changes = [];
        foreach (self::CHANGE_LABELS as $field => $label) {
            $old = $this->comparable($before->{$field});
            $new = $this->comparable($after->{$field});
            if ($old !== $new) {
                $changes[$label] = ['old' => $old, 'new' => $new];
            }
        }
        return $changes;
    }

    private function billingSnapshot(Sponsor $sponsor): array
    {
        $contact = $sponsor->billingContact();
        return [
            'Billing Name'  => $contact ? (string) $contact->name : '',
            'Billing Title' => $contact ? (string) $contact->title : '',
            'Billing Email' => $contact ? (string) $contact->email : '',
            'Billing Phone' => $contact ? (string) $contact->phone : '',
        ];
    }

    private function billingChanges(array $before, array $after): array
    {
        $changes = [];
        foreach ($after as $label => $new) {
            if (($before[$label] ?? '') !== $new) {
                $changes[$label] = ['old' => $before[$label] ?? '', 'new' => $new];
            }
        }
        return $changes;
    }

    /** Nilai pembanding & tampilan: tanggal "d F Y", angka format Indonesia (4.000 / 4.000,50). */
    private function comparable($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d F Y');
        }
        if (is_numeric($value)) {
            $number = (float) $value;
            return number_format($number, floor($number) == $number ? 0 : 2, ',', '.');
        }
        return trim((string) $value);
    }

    /**
     * Kirim PDF renewal form ke tim secretariat. Dikirim juga saat regenerate supaya
     * secretariat selalu pegang versi PDF terbaru.
     */
    private function emailRenewalFormToSecretariat(Sponsor $sponsor, SponsorRenewalForm $form, bool $isUpdate, array $changes = []): void
    {
        try {
            $data = $this->buildData($sponsor->id);
            $pdf  = $this->buildPdf($data)->output();

            $sentTo = RenewalFormSecretariatNotifier::send($data['sponsor'], $form, $pdf, $this->pdfFilename($sponsor), $isUpdate, $changes);
            if ($sentTo) {
                $this->markEmailed($form, $sentTo);
            }
        } catch (\Throwable $e) {
            Log::warning('Secretariat renewal-form email failed: ' . $e->getMessage());
        }
    }

    /**
     * Catat pengiriman email (ditampilkan di Step 1). saveQuietly: bukan perubahan
     * isi form, jadi nggak perlu masuk Sponsor Log. Di-skip kalau migration kolom
     * emailed_* belum dijalankan.
     */
    private function markEmailed(SponsorRenewalForm $form, array $recipients): void
    {
        if (!Schema::hasColumn('sponsor_renewal_forms', 'emailed_at')) {
            return;
        }

        $form->forceFill([
            'emailed_at'  => now(),
            'emailed_to'  => implode(', ', $recipients),
            'email_count' => (int) $form->email_count + 1,
        ])->saveQuietly();
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
                'Sponsor: *' . $sponsor->name . '* (' . sponsor_package_label($sponsor->package ?? '-') . ')',
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
