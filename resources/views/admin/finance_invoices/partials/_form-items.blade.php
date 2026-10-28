{{-- Item tagihan (baris dinamis, lihat _form-scripts) --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Item Tagihan</h4>
        <button type="button" class="btn btn-sm btn-outline-primary" id="fi-add-item"><i class="fas fa-plus mr-1"></i>Tambah Item</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0" id="fi-items-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th width="90">Qty</th>
                        <th width="170">Harga Satuan (Rp)</th>
                        <th width="150" class="text-right">Jumlah</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody id="fi-items"></tbody>
            </table>
        </div>
    </div>
    <div class="card-body pt-2">
        <textarea name="notes" class="form-control" rows="2" maxlength="2000"
            placeholder="Catatan di invoice (opsional), mis. periode kontrak atau nomor PO">{{ old('notes') }}</textarea>
    </div>
</div>
