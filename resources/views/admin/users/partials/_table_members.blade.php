{{-- Table: registered members (User model) --}}
<table id="laravel_crud" class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th width="10px">No</th>
            <th>Register</th>
            <th width="90px">
                Source
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Channel pendaftaran member (website, apps, event, dll)."
                    data-toggle="tooltip"></i>
            </th>
            <th>Name</th>
            <th width="80px">
                Status
                <i class="fas fa-info-circle text-muted ml-1"
                    title="Active = sudah diverifikasi admin. Pending = belum diverifikasi."
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
                    title="WA Updates: member setuju menerima update via WhatsApp. Open to Sponsorship: member bersedia menerima penawaran paket sponsorship."
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
