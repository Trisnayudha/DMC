{{-- Modal: Add/Edit Visitor (shared, action & values filled via JS) --}}
<div class="modal fade" id="visitorFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="visitorForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="visitorFormModalTitle">Tambah Visitor</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Company Name</label>
                            <input type="text" name="company_name" id="vf_company_name" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" id="vf_name" class="form-control">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-3" style="margin-top:-10px;">Isi minimal salah satu: Company Name, Name, Business Email, atau Mobile Number (di bawah).</small>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Job Title</label>
                            <input type="text" name="job_title" id="vf_job_title" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Business Email</label>
                            <input type="email" name="business_email" id="vf_business_email" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile_number" id="vf_mobile_number" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Office Number</label>
                            <input type="text" name="office_number" id="vf_office_number" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Website</label>
                        <input type="text" name="website" id="vf_website" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" id="vf_address" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Remarks</label>
                            <input type="text" name="remarks" id="vf_remarks" class="form-control"
                                placeholder="contoh: Mi26 - day1 - visitor">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Merchandise</label>
                            <input type="text" name="merchandise" id="vf_merchandise" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
