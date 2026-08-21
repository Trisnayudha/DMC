{{-- Modal: Edit member's data in place (same field set as Members DMC's Edit User
     modal) — completing this updates the existing account directly (no duplicate
     account), auto-triggers 2-Step Verified, and closes out the follow-up. --}}
<div class="modal fade" id="editFollowUpMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-check mr-1"></i>Edit & Verify — <span id="efm-member-name">-</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border py-2 mb-3 small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Menyimpan perubahan di sini akan meng-update data member ini langsung (bukan bikin akun baru),
                    otomatis menandai <strong>Verifikasi 2-Langkah</strong>, dan menutup follow-up ini.
                    Flagged: company <strong id="efm-flagged-company">-</strong>
                    <span id="efm-flagged-job-title-wrap">/ job title <strong id="efm-flagged-job-title">-</strong></span>
                </div>

                <input type="hidden" id="efm-follow-up-id">

                <div class="form-group">
                    <label class="small font-weight-bold">Nama</label>
                    <input type="text" id="efm-name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Email</label>
                    <input type="email" id="efm-email" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Job Title</label>
                    <input type="text" id="efm-job-title" class="form-control">
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Phone</label>
                    <input type="text" id="efm-phone" class="form-control">
                </div>

                <div class="border rounded p-3 mb-3">
                    <div class="small font-weight-bold text-primary mb-3">Data Company</div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Prefix</label>
                            <select id="efm-prefix" class="form-control">
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
                            <input type="text" id="efm-company-name" class="form-control" list="efm-company-datalist"
                                placeholder="Ketik nama company...">
                            <datalist id="efm-company-datalist">
                                @foreach ($companyNames ?? [] as $name)
                                    <option value="{{ $name }}"></option>
                                @endforeach
                            </datalist>
                            <small class="text-success d-none" id="efm-company-autofilled">
                                <i class="fas fa-check-circle mr-1"></i>Data company diisi otomatis dari company yang sudah terverifikasi.
                            </small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label class="small font-weight-bold">Website</label>
                            <input type="text" id="efm-company-website" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Company Category</label>
                            <select id="efm-company-category" class="form-control">
                                @include('partials._company_category_options')
                            </select>
                        </div>
                        <div class="form-group col-md-6 efm-company-other-wrap" style="display:none;">
                            <label class="small font-weight-bold">Company Other</label>
                            <input type="text" id="efm-company-other" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Address</label>
                        <textarea id="efm-address" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">City</label>
                            <input type="text" id="efm-city" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Postal Code</label>
                            <input type="text" id="efm-portal-code" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Country</label>
                            <input type="text" id="efm-country" class="form-control">
                        </div>
                    </div>

                    <div class="form-row mb-0">
                        <div class="form-group col-md-12">
                            <label class="small font-weight-bold">Full Office Number</label>
                            <input type="text" id="efm-full-office-number" class="form-control" placeholder="e.g. +62 21 12345678">
                            <small class="text-muted">Awali dengan kode negara (mis. <code>+62</code>) agar prefix terdeteksi otomatis.</small>
                        </div>
                        <input type="hidden" id="efm-prefix-office-number">
                        <input type="hidden" id="efm-office-number">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="efm-btn-submit">
                    <i class="fas fa-user-check mr-1"></i> Simpan & Verify
                </button>
            </div>
        </div>
    </div>
</div>
