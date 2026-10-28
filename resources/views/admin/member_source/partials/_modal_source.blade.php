{{-- Modal: Tambah / Edit member source --}}
<div class="modal fade" id="memberSourceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="member-source-form" action="{{ route('admin.member_sources.store') }}" method="POST">
            @csrf
            {{-- Dipakai untuk membuka ulang modal edit yang benar kalau validasi gagal --}}
            <input type="hidden" name="_update_url" id="ms-update-url" value="{{ old('_update_url') }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="member-source-modal-title">
                        <i class="fas fa-link mr-1"></i>Tambah Source
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-5">
                            <label>Category</label>
                            <input type="text" name="category" id="ms-category" class="form-control text-uppercase"
                                list="ms-category-options" required maxlength="20" pattern="[A-Za-z]+"
                                value="{{ old('category') }}">
                            <datalist id="ms-category-options">
                                @foreach ($knownCategories as $categoryCode => $categoryLabel)
                                    <option value="{{ $categoryCode }}">{{ $categoryLabel }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group col-7">
                            <label>Code</label>
                            <input type="text" name="code" id="ms-code" class="form-control text-uppercase" required
                                maxlength="50" pattern="[A-Za-z0-9]+" value="{{ old('code') }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-3" id="ms-key-hint">
                        Huruf/angka saja, tanpa spasi. <strong>EP</strong> = Partnership Event,
                        <strong>E</strong> = DMC Event (keduanya masuk chart "Members by Source"),
                        <strong>REF</strong> = Referral.
                    </small>
                    <small class="text-warning d-none mb-3" id="ms-key-locked">
                        <i class="fas fa-lock mr-1"></i>Category/Code dikunci karena sudah ada member yang daftar lewat link ini.
                    </small>
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" id="ms-name" class="form-control" required maxlength="255"
                            placeholder="mis. Mining Indonesia 2026" value="{{ old('name') }}">
                        <small class="text-muted">Tampil di halaman pendaftaran dan di breakdown source admin/users.</small>
                    </div>
                    <div class="form-group">
                        <label>Tipe Form</label>
                        <select name="form_type" id="ms-form-type" class="form-control" required>
                            @foreach ($formTypes as $formValue => $formLabel)
                                <option value="{{ $formValue }}" {{ old('form_type') === $formValue ? 'selected' : '' }}>
                                    {{ $formLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="is_active" id="ms-active" value="1" checked>
                        <label class="form-check-label" for="ms-active">Aktif (link bisa dipakai untuk daftar)</label>
                    </div>
                    <div class="small text-muted">
                        Link: <code id="ms-link-preview">{{ $frontendUrl }}/member/…</code>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
