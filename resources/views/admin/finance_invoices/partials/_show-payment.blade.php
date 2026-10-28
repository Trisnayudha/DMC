{{-- Panel pembayaran VA Mandiri + aksi --}}
<div class="card">
    <div class="card-header"><h4>Pembayaran VA Mandiri</h4></div>
    <div class="card-body">
        @if ($invoice->status === 'paid')
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle mr-1"></i>Lunas {{ $invoice->paid_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                <div class="small">Diterima: Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
            </div>
        @endif

        <div class="text-muted small text-uppercase">Nomor VA Mandiri</div>
        <div class="d-flex align-items-center mb-2" style="gap:6px;">
            <code style="font-size:1.25rem;" id="va-number">{{ $invoice->va_number ?: '—' }}</code>
            @if ($invoice->va_number)
                <button type="button" class="btn btn-sm btn-light border" title="Copy"
                    onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $invoice->va_number }}'); this.innerHTML='<i class=&quot;fas fa-check&quot;></i>';">
                    <i class="fas fa-copy"></i>
                </button>
            @endif
        </div>
        <div class="small mb-1">Total: <strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong></div>
        <div class="small mb-1">Berlaku sampai:
            {{ $invoice->va_expires_at ? $invoice->va_expires_at->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB' : '—' }}</div>
        <div class="small mb-1">Email terkirim: {{ $invoice->email_sent_at ? $invoice->email_sent_at->timezone('Asia/Jakarta')->format('d M Y H:i') : 'belum' }}</div>
        <div class="small text-muted mb-3">Dibuat oleh {{ $invoice->creator ? $invoice->creator->name : '—' }} · ref {{ $invoice->reference_id ?: '—' }}</div>

        @if ($invoice->isPayable())
            <form method="POST" action="{{ route('admin.finance_invoices.send_email', $invoice->id) }}" class="mb-2">
                @csrf
                <button class="btn btn-primary btn-block"><i class="fas fa-paper-plane mr-1"></i>{{ $invoice->email_sent_at ? 'Kirim Ulang Email' : 'Kirim Email Invoice' }}</button>
            </form>
        @endif

        @if (!in_array($invoice->status, ['paid', 'cancelled'], true))
            <form method="POST" action="{{ route('admin.finance_invoices.regenerate_va', $invoice->id) }}" class="border rounded p-2 mb-2">
                @csrf
                <div class="small font-weight-bold mb-1">Buat Ulang VA</div>
                <div class="small text-muted mb-2">Untuk VA yang expired atau gagal dibuat. Nomor VA lama tidak dipakai lagi.</div>
                <input type="date" name="due_date" class="form-control form-control-sm mb-2" required min="{{ date('Y-m-d') }}"
                    value="{{ $invoice->due_date->isPast() ? now()->addDays(7)->toDateString() : $invoice->due_date->toDateString() }}">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="send_email" id="regen-send" value="1" checked>
                    <label class="form-check-label small" for="regen-send">Kirim email dengan VA baru</label>
                </div>
                <button class="btn btn-sm btn-outline-primary btn-block"
                    onclick="return confirm('Buat VA Mandiri baru untuk invoice ini?');">Buat Ulang VA</button>
            </form>

            <form method="POST" action="{{ route('admin.finance_invoices.cancel', $invoice->id) }}">
                @csrf
                <button class="btn btn-sm btn-outline-danger btn-block"
                    onclick="return confirm('Batalkan invoice {{ $invoice->invoice_number }}?');">Batalkan Invoice</button>
            </form>
        @endif
    </div>
</div>
