{{-- Modal: Import Excel --}}
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.partnership_events.import', $event->slug) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-upload mr-2"></i>Import Visitor</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light" style="font-size:12px;">
                        <strong>Format Excel:</strong> kolom
                        <code>Company Name, Name, Job Title, Business Email, Mobile Number, Office Number, Website, Address, Remarks, Merchandise</code>.
                        <div class="mt-1 text-muted">
                            Kolom <code>No</code> boleh ada, akan diabaikan. Row yang Company Name, Name, Business Email, DAN Mobile Number-nya kosong semua akan dilewati.
                        </div>
                        <div class="mt-1 text-muted">
                            Row dengan <strong>Business Email</strong> (atau Mobile Number kalau email kosong) yang sudah ada di event
                            <strong>{{ $event->name }}</strong> akan meng-update data visitor tersebut (bukan dobel). Khusus
                            <strong>Remarks</strong> &amp; <strong>Merchandise</strong> digabung (mis. visitor hadir day1 &amp; day3),
                            tidak ditimpa — cocok untuk tracking kehadiran per hari.
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control-file" accept=".xlsx,.xls,.csv" required>
                        <a href="{{ route('admin.partnership_events.import_template') }}" class="small mt-1 d-inline-block">
                            <i class="fas fa-download"></i> Download template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-upload mr-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
