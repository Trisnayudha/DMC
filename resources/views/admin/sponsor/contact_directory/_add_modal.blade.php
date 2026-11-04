<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" method="POST" action="{{ route('sponsors.contact-directory.store') }}">
            @csrf
            <input type="hidden" name="contact_action" value="add">
            <input type="hidden" name="sponsor_id" id="ac-sponsor-id" value="{{ old('sponsor_id') }}">
            <div class="modal-header">
                <h5 class="modal-title">Add Contact — <span id="ac-sponsor-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group"><label for="ac-role">Contact type</label>
                    <select class="form-control" id="ac-role" name="role" required><option value="pic" {{ old('role') === 'pic' ? 'selected' : '' }}>Primary Contact (PIC)</option><option value="billing" {{ old('role') === 'billing' ? 'selected' : '' }}>Billing</option></select>
                </div>
                <div class="form-group"><label for="ac-name">Name <span class="text-danger">*</span></label><input class="form-control" id="ac-name" name="name" value="{{ old('name') }}" required maxlength="255"></div>
                <div class="form-group"><label for="ac-title">Title / Position</label><input class="form-control" id="ac-title" name="title" value="{{ old('title') }}" maxlength="255"></div>
                <div class="form-group"><label for="ac-email">Email</label><input type="email" class="form-control" id="ac-email" name="email" value="{{ old('email') }}" maxlength="255"></div>
                <div class="form-group mb-0"><label for="ac-phone">Phone</label><input type="text" class="form-control" id="ac-phone" name="phone" value="{{ old('phone') }}" maxlength="50"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Contact</button>
            </div>
        </form>
    </div>
</div>
