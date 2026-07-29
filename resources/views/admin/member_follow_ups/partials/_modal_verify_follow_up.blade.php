{{-- Modal: Verify Company Follow-Up → Deactivate old account, create new verified account --}}
<div class="modal fade" id="verifyFollowUpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-check mr-1"></i>Verify Company Change
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Info Box --}}
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Perhatian:</strong> Akun lama akan <strong>dinonaktifkan</strong> dan akun baru
                    dengan company terbaru akan <strong>dibuat & langsung diverifikasi</strong>.
                    Phone number akan dipindahkan ke akun baru.
                </div>

                <p class="mb-3">
                    Member: <strong id="vfu-member-name">-</strong>
                </p>
                <input type="hidden" id="vfu-follow-up-id">

                <div class="form-group">
                    <label>Company Terbaru <span class="text-danger">*</span></label>
                    <input type="text" id="vfu-new-company" class="form-control"
                        list="vfu-company-datalist"
                        placeholder="Ketik nama company terbaru...">
                    <datalist id="vfu-company-datalist">
                        @foreach ($companyNames ?? [] as $name)
                            <option value="{{ $name }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <div class="form-group">
                    <label>Job Title Baru <span class="text-muted small">(opsional — kosongkan jika sama)</span></label>
                    <input type="text" id="vfu-new-job-title" class="form-control"
                        placeholder="Job title di company baru...">
                </div>

                <div class="form-group mb-0">
                    <label>Email Baru <span class="text-muted small">(opsional — kosongkan jika sama)</span></label>
                    <input type="email" id="vfu-new-email" class="form-control"
                        placeholder="Email baru (jika berubah)...">
                    <small class="text-muted">Jika diisi, akun baru akan menggunakan email ini untuk login & approval email.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="vfu-btn-submit">
                    <i class="fas fa-user-check mr-1"></i> Verify & Buat Akun Baru
                </button>
            </div>
        </div>
    </div>
</div>
