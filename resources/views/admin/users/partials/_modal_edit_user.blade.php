{{-- Modal: Edit User --}}
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Data User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="eu-user-id">
                <input type="hidden" id="eu-update-url">

                <div class="form-group">
                    <label class="small font-weight-bold">Nama</label>
                    <input type="text" id="eu-name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Email</label>
                    <input type="email" id="eu-email" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Job Title</label>
                    <input type="text" id="eu-job-title" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Phone</label>
                    <input type="text" id="eu-phone" class="form-control">
                </div>

                <div class="border rounded p-3 mb-3">
                    <div class="small font-weight-bold text-primary mb-3">Data Company</div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Prefix</label>
                            <select id="eu-prefix" class="form-control eu-prefix-select2">
                                <option value="">Other</option>
                                <option value="PT">PT</option>
                                <option value="CV">CV</option>
                                <option value="Ltd">Ltd</option>
                                <option value="GmbH">GmbH</option>
                                <option value="Limited">Limited</option>
                                <option value="Llc">Llc</option>
                                <option value="Corp">Corp</option>
                                <option value="Pte Ltd">Pte Ltd</option>
                                <option value="Assosiation">Assosiation</option>
                                <option value="Government">Government</option>
                                <option value="Pty Ltd">Pty Ltd</option>
                            </select>
                        </div>
                        <div class="form-group col-md-8">
                            <label class="small font-weight-bold">Company Name</label>
                            <div class="position-relative">
                                <input type="text" id="eu-company-name" class="form-control" autocomplete="off"
                                    placeholder="Ketik nama company atau pilih dari verified...">
                                <div id="eu-company-suggestions" class="list-group position-absolute w-100"
                                    style="z-index:9999; display:none; max-height:180px; overflow-y:auto; top:100%; left:0; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                                </div>
                            </div>
                            <small class="text-muted">Suggestion hanya dari company yang sudah verified.</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label class="small font-weight-bold">Website</label>
                            <input type="text" id="eu-company-website" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Company Category</label>
                            <select id="eu-company-category" class="form-control">
                                <option value="">--Select--</option>
                                <option value="Coal Mining">Coal Mining</option>
                                <option value="Minerals Producer">Minerals Producer</option>
                                <option value="Supplier/Distributor/Manufacturer">Supplier/Distributor/Manufacturer</option>
                                <option value="Contrator">Contrator</option>
                                <option value="Association / Organization / Government">Association / Organization / Government</option>
                                <option value="Financial Services">Financial Services</option>
                                <option value="Technology">Technology</option>
                                <option value="Investors">Investors</option>
                                <option value="Logistics and Shipping">Logistics and Shipping</option>
                                <option value="Media">Media</option>
                                <option value="Consultant">Consultant</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 eu-company-other-wrap" style="display:none;">
                            <label class="small font-weight-bold">Company Other</label>
                            <input type="text" id="eu-company-other" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Address</label>
                        <textarea id="eu-address" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">City</label>
                            <input type="text" id="eu-city" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Postal Code</label>
                            <input type="text" id="eu-portal-code" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Country</label>
                            <input type="text" id="eu-country" class="form-control">
                        </div>
                    </div>

                    <div class="form-row mb-0">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Prefix Office Number</label>
                            <input type="text" id="eu-prefix-office-number" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Office Number</label>
                            <input type="text" id="eu-office-number" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Full Office Number</label>
                            <input type="text" id="eu-full-office-number" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Status Member</label>
                        <select id="eu-status-member" class="form-control">
                            <option value="">-- tidak diubah --</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Tier</label>
                        <select id="eu-tier" class="form-control">
                            <option value="reguler">Reguler</option>
                            <option value="black">Black</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="eu-btn-save">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
