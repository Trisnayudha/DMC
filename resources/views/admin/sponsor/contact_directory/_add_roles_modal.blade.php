<div class="modal fade" id="addRolesModal" tabindex="-1" role="dialog" aria-labelledby="addRolesTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" method="POST" action="{{ route('sponsors.contact-directory.roles') }}">
            @csrf
            <input type="hidden" name="contact_action" value="add_roles">
            <input type="hidden" name="sponsor_id" id="ar-sponsor-id">
            <input type="hidden" name="email" id="ar-email">
            <div class="modal-header">
                <h5 class="modal-title" id="addRolesTitle">Add Roles — <span id="ar-sponsor-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                @if ($errors->any() && old('contact_action') === 'add_roles')
                    <div class="alert alert-warning small">{{ $errors->first() }}</div>
                @endif
                <div class="border rounded p-3 mb-3"><strong id="ar-contact-name"></strong><div class="small text-muted" id="ar-contact-email"></div></div>
                <p class="small text-muted">Select one or more roles. The contact's existing details will be copied automatically.</p>
                @foreach (['pic' => 'Primary Contact (PIC)', 'cc' => 'CC', 'billing' => 'Billing'] as $value => $label)
                    <label class="d-block border rounded p-2 mb-2"><input type="checkbox" name="roles[]" value="{{ $value }}" class="mr-2">{{ $label }} <small class="text-muted ar-existing" style="display:none">Already added</small></label>
                @endforeach
                <div class="small text-muted">Contacts are matched by email within this sponsor.</div>
                <div class="text-danger small mt-2" id="ar-validation" role="alert" style="display:none">Select at least one role.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Roles</button>
            </div>
        </form>
    </div>
</div>
