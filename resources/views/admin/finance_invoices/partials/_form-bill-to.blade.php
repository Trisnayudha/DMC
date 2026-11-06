{{-- Penerima tagihan --}}
<div class="card">
    <div class="card-header"><h4>Bill To</h4></div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Contact Name <span class="text-danger">*</span></label>
                <input type="text" name="bill_to_name" id="fi-name" class="form-control" required maxlength="255" value="{{ old('bill_to_name') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Company</label>
                <input type="text" name="bill_to_company" id="fi-company" class="form-control" maxlength="255" value="{{ old('bill_to_company') }}">
                <small class="text-muted">Displayed as the Mandiri virtual account name.</small>
            </div>
            <div class="form-group col-md-6">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="bill_to_email" id="fi-email" class="form-control" required maxlength="255" value="{{ old('bill_to_email') }}">
            </div>
            <div class="form-group col-md-6">
                <label>CC <span class="text-muted font-weight-normal">(separate with commas)</span></label>
                <input type="text" name="bill_to_cc" class="form-control" maxlength="255" value="{{ old('bill_to_cc') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Phone</label>
                <input type="text" name="bill_to_phone" id="fi-phone" class="form-control" maxlength="30" value="{{ old('bill_to_phone') }}">
            </div>
            <div class="form-group col-md-6">
                <label>Address</label>
                <textarea name="bill_to_address" id="fi-address" class="form-control" rows="2" maxlength="1000">{{ old('bill_to_address') }}</textarea>
            </div>
        </div>
    </div>
</div>
