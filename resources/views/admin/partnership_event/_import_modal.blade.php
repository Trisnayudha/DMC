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
                        <strong>Excel format:</strong> columns
                        <code>Company Name, Name, Job Title, Business Email, Mobile Number, Office Number, Website, Address, Remarks, Merchandise</code>.
                        <div class="mt-1 text-muted">
                            The optional <code>No</code> column is ignored. Rows with no company name, name, business email, or mobile number will be skipped.
                        </div>
                        <div class="mt-1 text-muted">
                            Rows matching a visitor already in <strong>{{ $event->name }}</strong> by <strong>Business Email</strong> (or Mobile Number if email is blank) update that visitor instead of creating a duplicate. <strong>Remarks</strong> and <strong>Merchandise</strong> are combined rather than overwritten, so attendance across days can be tracked.
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Excel File (.xlsx, .xls, .csv)</label>
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
