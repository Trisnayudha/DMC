<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\Events;
use App\Models\Events\EventsTicket;
use App\Models\Payments\FinanceInvoice;
use App\Models\Sponsors\Sponsor;
use App\Services\FinanceInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Invoice Finance dengan pembayaran VA Mandiri (Xendit). Tipe: sponsor renewal,
 * tiket event, atau tagihan lainnya. Status lunas di-update otomatis oleh webhook
 * Xendit (FinanceInvoiceService::handleCallback).
 */
class FinanceInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $type   = (string) $request->query('type', '');

        $invoices = FinanceInvoice::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('invoice_number', 'like', '%' . $search . '%')
                        ->orWhere('bill_to_name', 'like', '%' . $search . '%')
                        ->orWhere('bill_to_company', 'like', '%' . $search . '%')
                        ->orWhere('bill_to_email', 'like', '%' . $search . '%')
                        ->orWhere('va_number', 'like', '%' . $search . '%');
                });
            })
            ->when(isset(FinanceInvoice::STATUSES[$status]), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when(isset(FinanceInvoice::TYPES[$type]), function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.finance_invoices.index', [
            'invoices' => $invoices,
            'summary'  => $this->summary(),
            'filters'  => compact('search', 'status', 'type'),
        ]);
    }

    public function create(Request $request)
    {
        $type = isset(FinanceInvoice::TYPES[$request->query('type')]) ? $request->query('type') : 'sponsor_renewal';

        return view('admin.finance_invoices.create', [
            'type'          => $type,
            'sponsors'      => Sponsor::orderBy('name')->get(['id', 'name']),
            'events'        => $this->eventOptions(),
            'nextNumber'    => FinanceInvoiceService::nextInvoiceNumber((int) date('Y')),
            'defaultDue'    => now()->addDays(7)->toDateString(),
        ]);
    }

    /** Prefill form tipe sponsor renewal: kontak billing + daftar kontrak sponsor. */
    public function sponsorData($id)
    {
        $sponsor = Sponsor::with(['billings', 'firstPic'])->findOrFail($id);
        $contact = $sponsor->billings->first() ?: $sponsor->firstPic;

        $renewals = $sponsor->renewals()
            ->orderByDesc('is_current')->orderByDesc('contract_start')
            ->limit(10)
            ->get(['id', 'renewal_year', 'contract_start', 'contract_end', 'package', 'amount_idr', 'amount_usd', 'invoice_number', 'paid_date', 'is_current']);

        return response()->json([
            'company'  => $sponsor->name,
            'address'  => $sponsor->address,
            'name'     => $contact ? $contact->name : '',
            'email'    => $contact && $contact->email ? $contact->email : $sponsor->email,
            'phone'    => $contact ? $contact->phone : $sponsor->office_number,
            'renewals' => $renewals->map(function ($r) {
                return [
                    'id'             => $r->id,
                    'label'          => trim($r->renewal_year . ' · ' . ucfirst((string) $r->package) . ' · ' . $r->contract_start . ' s/d ' . $r->contract_end
                        . ($r->is_current ? ' (current)' : '') . ($r->paid_date ? ' · paid' : '')),
                    'package'        => ucfirst((string) $r->package),
                    'contract_start' => $r->contract_start,
                    'contract_end'   => $r->contract_end,
                    'amount_idr'     => $r->amount_idr ? (int) round((float) $r->amount_idr) : null,
                    'invoice_number' => $r->invoice_number,
                ];
            }),
        ]);
    }

    /** Tiket berbayar (IDR) suatu event untuk prefill item. */
    public function eventTickets($id)
    {
        $event = Events::findOrFail($id);
        $tickets = EventsTicket::where('events_id', $event->id)
            ->where('price_rupiah', '>', 0)
            ->orderBy('price_rupiah')
            ->get(['id', 'title', 'price_rupiah']);

        return response()->json([
            'event'   => $event->name,
            'tickets' => $tickets->map(function ($t) {
                return ['id' => $t->id, 'title' => $t->title, 'price' => (int) round((float) $t->price_rupiah)];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $invoice = FinanceInvoice::create(array_merge($data, [
            'status'     => 'pending',
            'va_bank'    => 'MANDIRI',
            'created_by' => auth()->id(),
        ]));

        try {
            FinanceInvoiceService::createVirtualAccount($invoice);
        } catch (\Throwable $e) {
            Log::error('Finance invoice VA creation failed', ['invoice' => $invoice->invoice_number, 'error' => $e->getMessage()]);
            return redirect()->route('admin.finance_invoices.show', $invoice->id)->with(
                'error',
                'Invoice tersimpan, tapi VA Mandiri gagal dibuat: ' . $e->getMessage() . ' — coba "Buat Ulang VA".'
            );
        }

        $message = 'Invoice ' . $invoice->invoice_number . ' dibuat. VA Mandiri: ' . $invoice->va_number . '.';
        if ($request->boolean('send_email')) {
            $message .= $this->trySendEmail($invoice);
        }

        return redirect()->route('admin.finance_invoices.show', $invoice->id)->with('success', $message);
    }

    public function show($id)
    {
        $invoice = FinanceInvoice::with(['sponsor', 'sponsorRenewal', 'event', 'creator'])->findOrFail($id);

        return view('admin.finance_invoices.show', compact('invoice'));
    }

    public function pdf($id)
    {
        $invoice = FinanceInvoice::findOrFail($id);

        return FinanceInvoiceService::pdf($invoice)->stream(FinanceInvoiceService::pdfFilename($invoice));
    }

    public function sendEmail($id)
    {
        $invoice = FinanceInvoice::findOrFail($id);
        if (!$invoice->isPayable()) {
            return back()->with('error', 'Invoice ini tidak bisa dikirim: VA belum aktif, sudah expired, lunas, atau dibatalkan.');
        }

        $result = $this->trySendEmail($invoice);

        return back()->with($invoice->fresh()->email_sent_at ? 'success' : 'error', ltrim($result));
    }

    /** Buat VA baru (mis. VA lama expired) dengan jatuh tempo baru. */
    public function regenerateVa(Request $request, $id)
    {
        $invoice = FinanceInvoice::findOrFail($id);
        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            return back()->with('error', 'Invoice yang sudah lunas atau dibatalkan tidak bisa dibuatkan VA baru.');
        }

        $request->validate(['due_date' => 'required|date|after_or_equal:today']);
        $invoice->due_date = $request->due_date;
        $invoice->save();

        try {
            FinanceInvoiceService::createVirtualAccount($invoice);
        } catch (\Throwable $e) {
            Log::error('Finance invoice VA regeneration failed', ['invoice' => $invoice->invoice_number, 'error' => $e->getMessage()]);
            return back()->with('error', 'VA Mandiri gagal dibuat: ' . $e->getMessage());
        }

        $message = 'VA Mandiri baru: ' . $invoice->va_number . ', berlaku sampai ' . $invoice->due_date->format('d M Y') . '.';
        if ($request->boolean('send_email')) {
            $message .= $this->trySendEmail($invoice);
        }

        return back()->with('success', $message);
    }

    public function cancel($id)
    {
        $invoice = FinanceInvoice::findOrFail($id);
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice yang sudah lunas tidak bisa dibatalkan.');
        }

        $invoice->forceFill(['status' => 'cancelled'])->save();

        return back()->with('success', 'Invoice ' . $invoice->invoice_number . ' dibatalkan. Jangan bagikan lagi nomor VA-nya.');
    }

    private function trySendEmail(FinanceInvoice $invoice): string
    {
        try {
            FinanceInvoiceService::sendInvoiceEmail($invoice);
            return ' Email invoice terkirim ke ' . $invoice->bill_to_email . '.';
        } catch (\Throwable $e) {
            Log::error('Finance invoice email failed', ['invoice' => $invoice->invoice_number, 'error' => $e->getMessage()]);
            return ' Email gagal dikirim: ' . $e->getMessage();
        }
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'type'                => ['required', Rule::in(array_keys(FinanceInvoice::TYPES))],
            'invoice_number'      => 'nullable|string|max:30|unique:finance_invoices,invoice_number',
            'bill_to_name'        => 'required|string|max:255',
            'bill_to_company'     => 'nullable|string|max:255',
            'bill_to_email'       => 'required|email|max:255',
            'bill_to_cc'          => 'nullable|string|max:255',
            'bill_to_phone'       => 'nullable|string|max:30',
            'bill_to_address'     => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.qty'         => 'required|integer|min:1|max:10000',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'vat_percent'         => 'nullable|numeric|min:0|max:100',
            'issued_date'         => 'required|date',
            'due_date'            => 'required|date|after_or_equal:today',
            'notes'               => 'nullable|string|max:2000',
            'sponsor_id'          => 'required_if:type,sponsor_renewal|nullable|exists:sponsors,id',
            'sponsor_renewal_id'  => 'nullable|exists:sponsor_renewals,id',
            'events_id'           => 'required_if:type,event_ticket|nullable|exists:events,id',
        ], [
            'sponsor_id.required_if' => 'Pilih sponsor untuk invoice sponsor renewal.',
            'events_id.required_if'  => 'Pilih event untuk invoice tiket event.',
        ]);

        foreach ($this->ccEmails($request->bill_to_cc) as $cc) {
            if (!filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['bill_to_cc' => 'Email CC tidak valid: ' . $cc]);
            }
        }

        $vatPercent = (float) ($request->vat_percent ?? 0);
        [$items, $subtotal, $vatAmount, $total] = FinanceInvoiceService::calculate($request->items, $vatPercent);
        if ($total < FinanceInvoiceService::MIN_AMOUNT) {
            throw ValidationException::withMessages([
                'items' => 'Total invoice minimal Rp ' . number_format(FinanceInvoiceService::MIN_AMOUNT, 0, ',', '.') . ' (batas VA Mandiri).',
            ]);
        }

        $type = $request->type;

        return [
            'type'               => $type,
            'invoice_number'     => $request->invoice_number ?: FinanceInvoiceService::nextInvoiceNumber((int) date('Y', strtotime($request->issued_date))),
            'bill_to_name'       => $request->bill_to_name,
            'bill_to_company'    => $request->bill_to_company,
            'bill_to_email'      => $request->bill_to_email,
            'bill_to_cc'         => implode(', ', $this->ccEmails($request->bill_to_cc)) ?: null,
            'bill_to_phone'      => $request->bill_to_phone,
            'bill_to_address'    => $request->bill_to_address,
            'items'              => $items,
            'subtotal'           => $subtotal,
            'vat_percent'        => $vatPercent,
            'vat_amount'         => $vatAmount,
            'total_amount'       => $total,
            'notes'              => $request->notes,
            'sponsor_id'         => $type === 'sponsor_renewal' ? $request->sponsor_id : null,
            'sponsor_renewal_id' => $type === 'sponsor_renewal' ? $request->sponsor_renewal_id : null,
            'events_id'          => $type === 'event_ticket' ? $request->events_id : null,
            'issued_date'        => $request->issued_date,
            'due_date'           => $request->due_date,
        ];
    }

    private function ccEmails($value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    /** Event yang punya tiket rupiah berbayar, terbaru dulu. */
    private function eventOptions()
    {
        return Events::whereExists(function ($q) {
            $q->selectRaw('1')->from('events_tickets as t')
                ->whereColumn('t.events_id', 'events.id')
                ->where('t.price_rupiah', '>', 0);
        })
            ->orderByDesc('start_date')
            ->limit(60)
            ->get(['id', 'name', 'start_date']);
    }

    private function summary(): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'pending_count'     => FinanceInvoice::where('status', 'pending')->count(),
            'pending_amount'    => (int) FinanceInvoice::where('status', 'pending')->sum('total_amount'),
            'paid_month'        => FinanceInvoice::where('status', 'paid')->where('paid_at', '>=', $monthStart)->count(),
            'paid_month_amount' => (int) FinanceInvoice::where('status', 'paid')->where('paid_at', '>=', $monthStart)->sum('paid_amount'),
        ];
    }
}
