{{-- Modal: Verify Member (2-step: company check → member confirm) --}}
<div class="modal fade" id="verifyMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            {{-- Step indicator --}}
            <div class="modal-header pb-2" style="border-bottom:none;">
                <div class="w-100">
                    <div class="d-flex align-items-center mb-2" style="gap:8px;">
                        <span id="vm-step-indicator-1" class="badge badge-primary" style="font-size:12px;">Step 1</span>
                        <span style="font-size:11px; color:#adb5bd;">Verifikasi Company</span>
                        <span style="color:#dee2e6; font-size:14px;">›</span>
                        <span id="vm-step-indicator-2" class="badge badge-light" style="font-size:12px;">Step 2</span>
                        <span style="font-size:11px; color:#adb5bd;">Verifikasi Member</span>
                    </div>
                    <h5 class="modal-title mb-0" id="vm-modal-title">Verifikasi Member</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top:-28px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- ===== STEP 1: User Info + (optional) Company Verification ===== --}}
            <div id="vm-step-1">
                <div class="modal-body pt-2">

                    {{-- Alert shown when company NOT verified --}}
                    <div id="vm-alert-company-not-verified" class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Company belum terverifikasi.</strong><br>
                        <small class="ml-4">Pastikan data company sudah benar sebelum memverifikasi member ini.
                            Perubahan akan diterapkan ke semua user dengan company yang sama.</small>
                    </div>
                    {{-- Alert shown when company IS already verified --}}
                    <div id="vm-alert-company-verified" class="alert alert-success mb-3" style="display:none;">
                        <i class="fas fa-check-circle mr-2"></i>
                        Company <strong id="vm-company-verified-label">-</strong> sudah terverifikasi.
                    </div>

                    {{-- User info (editable) --}}
                    <div class="border rounded p-3 mb-3">
                        <div class="small font-weight-bold text-primary mb-2">
                            <i class="fas fa-user mr-1"></i>Informasi User — <span id="vm-member-name">-</span>
                        </div>
                        <div class="form-row mb-0">
                            <div class="form-group col-md-6 mb-2">
                                <label class="small mb-1">Nama</label>
                                <input type="text" id="vm-user-name" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label class="small mb-1">Email</label>
                                <input type="email" id="vm-user-email" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <label class="small mb-1">Job Title</label>
                                <input type="text" id="vm-user-job-title" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <label class="small mb-1">Phone</label>
                                <input type="text" id="vm-user-phone" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Company form section (hidden when company already verified) --}}
                    <div id="vm-company-form-section">
                        <div class="alert alert-light mb-3 py-2">
                            Company saat ini: <strong id="vm-company-label">-</strong>
                        </div>

                        <input type="hidden" id="vm-normalized-name">

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix</label>
                                <select id="vm-prefix" class="form-control vm-prefix-select2">
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
                                <label>Company Name</label>
                                <div class="position-relative">
                                    <input type="text" id="vm-company-name" class="form-control" autocomplete="off"
                                        placeholder="Ketik nama company atau pilih dari verified...">
                                    <div id="vm-company-suggestions" class="list-group position-absolute w-100"
                                        style="z-index:9999; display:none; max-height:180px; overflow-y:auto; top:100%; left:0; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                                    </div>
                                </div>
                                <small class="text-muted">Ketik untuk saran dari company yang sudah verified.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Website</label>
                                <input type="text" id="vm-company-website" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Company Category</label>
                                <select id="vm-company-category" class="form-control">
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
                            <div class="form-group col-md-6 vm-company-other-wrap" style="display:none;">
                                <label>Company Other</label>
                                <input type="text" id="vm-company-other" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="vm-address" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" id="vm-city" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Postal Code</label>
                                <input type="text" id="vm-portal-code" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Country</label>
                                <input type="text" id="vm-country" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix Office Number</label>
                                <input type="text" id="vm-prefix-office-number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Office Number</label>
                                <input type="text" id="vm-office-number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Full Office Number</label>
                                <input type="text" id="vm-full-office-number" class="form-control">
                            </div>
                        </div>
                    </div>{{-- /vm-company-form-section --}}

                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Status member akan berubah jadi
                        <span class="badge badge-success">Active</span> dan data dikirim ke Mailchimp.
                    </small>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="vm-btn-open-decline">
                        <i class="fas fa-times-circle mr-1"></i> Decline
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary mr-1" data-dismiss="modal">Batal</button>
                        {{-- Shown when company NOT verified --}}
                        <button type="button" class="btn btn-success" id="vm-btn-verify-company">
                            <i class="fas fa-check-circle mr-1"></i> Verifikasi Company & Lanjut
                        </button>
                        {{-- Shown when company already verified --}}
                        <button type="button" class="btn btn-warning px-4" id="vm-btn-verify-member-direct" style="display:none;">
                            <i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== STEP 2: Confirm after company verification ===== --}}
            <div id="vm-step-2" style="display:none;">
                <div class="modal-body py-4 text-center">
                    <div class="mb-3">
                        <span style="font-size:48px; color:#28a745;"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <h5 class="mb-1">Company berhasil diverifikasi!</h5>
                    <p class="text-muted mb-3" id="vm-step2-company-label">-</p>
                    <div class="alert alert-light d-inline-block px-4 text-left">
                        Lanjut verifikasi member <strong id="vm-step2-member-name">-</strong>?<br>
                        <small class="text-muted">Status member akan berubah jadi
                            <span class="badge badge-success">Active</span> dan data dikirim ke Mailchimp.</small>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="vm-btn-open-decline-step2">
                        <i class="fas fa-times-circle mr-1"></i> Decline
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary mr-1" data-dismiss="modal">Nanti dulu</button>
                        <button type="button" class="btn btn-warning px-4" id="vm-btn-verify-member">
                            <i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== DECLINE CONFIRMATION ===== --}}
            <div id="vm-step-decline" style="display:none;">
                <div class="modal-body py-4">
                    <div class="text-center mb-3">
                        <span style="font-size:48px; color:#dc3545;"><i class="fas fa-times-circle"></i></span>
                    </div>
                    <h5 class="text-center mb-3">Decline Membership Application</h5>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Kamu akan <strong>mendecline</strong> aplikasi membership dari
                        <strong id="vm-decline-member-name">-</strong>.<br>
                        <small>Email notifikasi decline akan otomatis dikirim ke mereka.</small>
                    </div>
                    <div class="alert alert-light border py-2 small">
                        <strong>Subject:</strong> Update on Your Djakarta Mining Club Membership Application<br>
                        <span class="text-muted">Email akan dikirim ke: <strong id="vm-decline-member-email">-</strong></span>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="vm-btn-decline-cancel">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="vm-btn-decline-confirm">
                        <i class="fas fa-times-circle mr-1"></i> Ya, Decline & Kirim Email
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
