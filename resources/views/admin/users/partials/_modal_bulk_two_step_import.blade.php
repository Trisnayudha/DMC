{{-- Modal: Bulk Import Verifikasi 2-Langkah --}}
<div class="modal fade" tabindex="-1" role="dialog" id="bulkTwoStepImportModal">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-check mr-2"></i>Bulk Import Verifikasi 2-Langkah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">
                    Upload file berisi kolom <strong>Email</strong> dan <strong>Status</strong>
                    (Verified / Not Verified). Email dicocokkan persis ke email member yang sudah
                    terdaftar — email yang tidak ditemukan akan dilaporkan, tidak dibuat member baru.
                </p>
                <form action="{{ route('users.bulk_two_step_import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="small text-muted">Pilih file .xlsx / .xls / .csv</label>
                        <input type="file" name="file" id="bulk_two_step_file" class="form-control-file" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-upload mr-1"></i> Upload &amp; Import
                    </button>
                </form>
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="{{ route('users.bulk_two_step_import.template') }}" class="btn btn-outline-primary" download>
                    <i class="fas fa-download mr-1"></i> Download Template
                </a>
            </div>
        </div>
    </div>
</div>
