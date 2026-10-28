{{-- Tipe invoice + sumber data (sponsor / event) untuk prefill --}}
@php $currentType = old('type', $type); @endphp
<div class="card">
    <div class="card-header"><h4>Jenis Tagihan</h4></div>
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
                        <option value="">— Pilih sponsor —</option>
                        @foreach ($sponsors as $sponsor)
                            <option value="{{ $sponsor->id }}" {{ (string) old('sponsor_id') === (string) $sponsor->id ? 'selected' : '' }}>
                                {{ $sponsor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Kontrak <span class="text-muted font-weight-normal">(opsional)</span></label>
                    <select name="sponsor_renewal_id" id="fi-renewal" class="form-control" data-old="{{ old('sponsor_renewal_id') }}">
                        <option value="">— Tidak dikaitkan —</option>
                    </select>
                    <small class="text-muted">Saat lunas, Paid Date kontrak ini terisi otomatis (Invoice No./Date juga kalau masih kosong).</small>
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
                        <option value="">— Pilih event —</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" {{ (string) old('events_id') === (string) $event->id ? 'selected' : '' }}>
                                {{ $event->name }}{{ $event->start_date ? ' (' . \Illuminate\Support\Carbon::parse($event->start_date)->format('d M Y') . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Tambah tiket ke item</label>
                    <div class="input-group">
                        <select id="fi-ticket" class="form-control"><option value="">— Pilih event dulu —</option></select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="fi-add-ticket"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <small class="text-muted d-block">Invoice ini mencatat pembayaran tiket saja — pendaftaran peserta tetap lewat alur registrasi event.</small>
        </div>

        <div class="js-type-section" data-type="custom">
            <p class="text-muted mb-0">Isi penerima dan item tagihan secara bebas (mis. iklan, booth, membership korporat).</p>
        </div>
    </div>
</div>
