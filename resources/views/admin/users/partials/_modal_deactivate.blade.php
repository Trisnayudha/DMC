{{-- Modal: Deactivate Member (asks for a reason before deactivating) --}}
<div class="modal fade" id="deactivateMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-slash mr-1"></i>Deactivate Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Deactivate <strong id="dm-member-name">-</strong>? Member tidak bisa login dan mendapat harga
                    nonmember.
                </p>
                <input type="hidden" id="dm-url">
                <div class="form-group mb-0">
                    <label>Reason <span class="text-danger">*</span></label>
                    <textarea id="dm-reason" rows="3" class="form-control"
                        placeholder="Kenapa member ini di-deactivate? (mis. akun testing, member meninggal, akun duplikat...)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-outline-danger" id="dm-btn-submit">
                    <i class="fas fa-user-slash mr-1"></i> Deactivate
                </button>
            </div>
        </div>
    </div>
</div>
