{{-- Tipe invoice + sumber data (sponsor / event) untuk prefill --}}
@php $currentType = old('type', $type); @endphp
<div class="card">
    <div class="card-header"><h4>Invoice Type</h4></div>
    <div class="card-body">
        <div class="btn-group btn-group-toggle d-flex flex-wrap mb-3" data-toggle="buttons">
            @foreach (\App\Models\Payments\FinanceInvoice::TYPES as $key => $label)
                <label class="btn btn-outline-primary flex-fill {{ $currentType === $key ? 'active' : '' }}">
                    <input type="radio" name="type" value="{{ $key }}" class="js-invoice-type" autocomplete="off"
                        {{ $currentType === $key ? 'checked' : '' }}> {{ $label }}
                </label>
            @endforeach
        </div>

        {{-- Sponsor renewal --}}
        <div class="js-type-section" data-type="sponsor_renewal">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Sponsor <span class="text-danger">*</span></label>
                    <select name="sponsor_id" id="fi-sponsor" class="form-control"
                        data-url="{{ url('admin/finance-invoices/sponsors') }}">
                        <option value="">— Select a sponsor —</option>
                        @foreach ($sponsors as $sponsor)
                            <option value="{{ $sponsor->id }}" {{ (string) old('sponsor_id') === (string) $sponsor->id ? 'selected' : '' }}>
                                {{ $sponsor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Contract <span class="text-muted font-weight-normal">(optional)</span></label>
                    <select name="sponsor_renewal_id" id="fi-renewal" class="form-control" data-old="{{ old('sponsor_renewal_id') }}">
                        <option value="">— Not linked —</option>
                    </select>
                    <small class="text-muted">When paid, the contract’s Paid Date is filled automatically; the Invoice No. and Date are also filled if blank.</small>
                </div>
            </div>
        </div>

        {{-- Tiket event --}}
        <div class="js-type-section" data-type="event_ticket">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Event <span class="text-danger">*</span></label>
                    <select name="events_id" id="fi-event" class="form-control"
                        data-url="{{ url('admin/finance-invoices/events') }}">
                        <option value="">— Select an event —</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" {{ (string) old('events_id') === (string) $event->id ? 'selected' : '' }}>
                                {{ $event->name }}{{ $event->start_date ? ' (' . \Illuminate\Support\Carbon::parse($event->start_date)->format('d M Y') . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Add ticket to items</label>
                    <div class="input-group">
                        <select id="fi-ticket" class="form-control"><option value="">— Select an event first —</option></select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="fi-add-ticket"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <small class="text-muted d-block">This invoice records ticket payment only. Participants must still register through the event registration process.</small>
        </div>

        <div class="js-type-section" data-type="custom">
            <p class="text-muted mb-0">Enter the recipient and invoice items (e.g. advertising, booth, or corporate membership).</p>
        </div>
    </div>
</div>
