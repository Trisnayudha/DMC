{{-- Modal: manually add an already-approved member to Lead Follow-Up --}}
<div class="modal fade" id="addLeadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus mr-1"></i>Add Member to Lead</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    Hanya member yang sudah approved. Untuk member lama yang tidak otomatis masuk ke Lead Follow-Up.
                    SLA kirim sponsor kit: 48 jam dari sekarang.
                </div>
                <div class="form-group">
                    <label>Member <span class="text-danger">*</span></label>
                    <select id="al-user" class="form-control" style="width:100%;"></select>
                    <small class="text-muted">Ketik minimal 2 huruf: nama, email, atau perusahaan.</small>
                </div>
                <div class="form-group mb-0">
                    <label>Notes</label>
                    <textarea id="al-notes" rows="2" class="form-control" maxlength="2000" placeholder="Opsional"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="al-btn-submit">
                    <i class="fas fa-plus mr-1"></i> Add to Lead
                </button>
            </div>
        </div>
    </div>
</div>
