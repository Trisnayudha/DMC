{{-- Nomor, tanggal, PPN, total, kirim --}}
<div class="card fi-summary-card">
    <div class="card-header"><h4>Invoice</h4></div>
    <div class="card-body">
        <div class="form-group">
            <label>No. Invoice</label>
            <input type="text" name="invoice_number" class="form-control" maxlength="30" value="{{ old('invoice_number') }}"
                placeholder="{{ $nextNumber }}">
            <small class="text-muted">Kosongkan untuk nomor otomatis ({{ $nextNumber }}).</small>
        </div>
        <div class="form-row">
            <div class="form-group col-6">
                <label>Tanggal</label>
                <input type="date" name="issued_date" class="form-control" required value="{{ old('issued_date', date('Y-m-d')) }}">
            </div>
            <div class="form-group col-6">
                <label>Jatuh Tempo</label>
                <input type="date" name="due_date" class="form-control" required min="{{ date('Y-m-d') }}" value="{{ old('due_date', $defaultDue) }}">
            </div>
        </div>
        <small class="text-muted d-block mb-3" style="margin-top:-8px;">VA Mandiri berlaku sampai akhir hari jatuh tempo (WIB).</small>
        <div class="form-group">
            <label>PPN (%)</label>
            <input type="number" name="vat_percent" id="fi-vat" class="form-control" min="0" max="100" step="0.01" value="{{ old('vat_percent', 0) }}">
        </div>

        <table class="table table-sm mb-3 fi-totals">
            <tr><td>Subtotal</td><td class="text-right" id="fi-subtotal">Rp 0</td></tr>
            <tr><td>PPN</td><td class="text-right" id="fi-vat-amount">Rp 0</td></tr>
            <tr class="font-weight-bold"><td>Total</td><td class="text-right" id="fi-total">Rp 0</td></tr>
        </table>
        <div class="text-danger small mb-2" id="fi-min-warning" style="display:none;">
            Total minimal Rp {{ number_format(\App\Services\FinanceInvoiceService::MIN_AMOUNT, 0, ',', '.') }} (batas VA Mandiri).
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" name="send_email" id="fi-send-email" value="1" {{ old('send_email', '1') ? 'checked' : '' }}>
            <label class="form-check-label" for="fi-send-email">Langsung kirim email invoice (PDF + nomor VA)</label>
        </div>
        <button type="submit" class="btn btn-primary btn-block" id="fi-submit">
            <i class="fas fa-file-invoice-dollar mr-1"></i>Buat Invoice & VA Mandiri
        </button>
    </div>
</div>

<style>
    .fi-summary-card { position: sticky; top: 90px; }
    .fi-totals td { border-top: 1px solid #eef0f4; padding: .4rem .25rem; }
    #fi-items-table th { background: #f8f9fc; font-size: 11px; color: #67728a; }
    #fi-items-table td { vertical-align: middle; }
</style>
