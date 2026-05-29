{{-- Modal: Import Excel --}}
<div class="modal fade" tabindex="-1" role="dialog" id="example">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('users.import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="small text-muted">Pilih file .xlsx / .xls</label>
                        <input type="file" name="uploaded_file" id="uploaded_file" class="form-control-file">
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-upload mr-1"></i> Upload &amp; Import
                    </button>
                </form>
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="{{ url('sample/sample.xlsx') }}" class="btn btn-outline-primary" download>
                    <i class="fas fa-download mr-1"></i> Download Template
                </a>
            </div>
        </div>
    </div>
</div>
