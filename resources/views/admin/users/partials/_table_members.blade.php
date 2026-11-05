{{-- Table: registered members (User model) --}}
<table id="laravel_crud" class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th width="10px">No</th>
            <th>Register</th>
            <th width="90px">
                Source
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Member registration channel (website, app, event, etc.)."
                    data-toggle="tooltip"></i>
            </th>
            <th>Name</th>
            <th width="80px">
                Status
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Active = verified by an admin. Pending = not yet verified."
                    data-toggle="tooltip"></i>
            </th>
            <th width="70px">Actions</th>
            <th>Job Title</th>
            <th>Company</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Office</th>
            <th>Address</th>
            <th>Website</th>
            <th>Category</th>
            <th width="60px">
                WA/Spon.
                <i class="fas fa-info-circle text-muted ml-1"
                    title="WA Updates: member agrees to receive updates via WhatsApp. Open to Sponsorship: member is open to receiving sponsorship offers."
                    data-toggle="tooltip"></i>
            </th>
            <th width="80px">
                Mailchimp
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Member email status in the Mailchimp audience (cached for about 10 minutes; updates after sync or archive from the CMS)."
                    data-toggle="tooltip"></i>
            </th>
            <th width="70px">Password</th>
        </tr>
    </thead>
    {{-- <tbody> intentionally empty — DataTables server-side (see _scripts.blade.php)
         fetches and renders rows from usersData(), 25 at a time, instead of this
         page rendering all ~3,000 matching rows up front. Row markup for reference
         lives in partials/_row/*.blade.php + UsersController::renderMemberRowCells(). --}}
    <tbody></tbody>
</table>
