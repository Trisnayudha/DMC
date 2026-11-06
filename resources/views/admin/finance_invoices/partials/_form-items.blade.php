{{-- Item tagihan (baris dinamis, lihat _form-scripts) --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Invoice Items</h4>
        <button type="button" class="btn btn-sm btn-outline-primary" id="fi-add-item"><i class="fas fa-plus mr-1"></i>Add Item</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0" id="fi-items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th width="90">Qty</th>
                        <th width="170">Unit Price (Rp)</th>
                        <th width="150" class="text-right">Amount</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody id="fi-items"></tbody>
            </table>
        </div>
    </div>
    <div class="card-body pt-2">
        <textarea name="notes" class="form-control" rows="2" maxlength="2000"
            placeholder="Invoice notes (optional), e.g. contract period or PO number">{{ old('notes') }}</textarea>
    </div>
</div>
