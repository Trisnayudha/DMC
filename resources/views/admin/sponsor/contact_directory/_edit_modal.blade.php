{{-- Modal edit kontak (PIC / representative). Diisi dari data-* tombol .btn-edit-contact. --}}
<div class="modal fade" id="editContactModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" method="POST" action="{{ route('sponsors.contact-directory.update') }}">
            @csrf
            <input type="hidden" name="sponsor_id" id="ec-sponsor-id">
            <div id="ec-source-ids"></div>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit mr-1"></i>Edit Contact — <span id="ec-sponsor-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3" id="ec-scope"></p>
                <div class="form-group">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="ec-name" class="form-control" required maxlength="255">
                </div>
                <div class="form-group">
                    <label>Title / Position</label>
                    <input type="text" name="title" id="ec-title" class="form-control" maxlength="255">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="ec-email" class="form-control" maxlength="255">
                </div>
                <div class="form-group ec-pic-only">
                    <label>Phone</label>
                    <input type="text" name="phone" id="ec-phone" class="form-control" maxlength="50">
                </div>
                <div class="form-row ec-rep-only">
                    <div class="form-group col-md-6">
                        <label>Instagram</label>
                        <input type="text" name="instagram" id="ec-instagram" class="form-control" maxlength="255" placeholder="@username">
                    </div>
                    <div class="form-group col-md-6">
                        <label>LinkedIn</label>
                        <input type="text" name="linkedin" id="ec-linkedin" class="form-control" maxlength="255" placeholder="https://linkedin.com/in/...">
                    </div>
                </div>
                <div class="alert alert-light border small mb-0" id="ec-note" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save</button>
            </div>
        </form>
    </div>
</div>
