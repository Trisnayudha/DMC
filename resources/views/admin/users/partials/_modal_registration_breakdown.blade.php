{{-- Modal: source breakdown for one bar of the Member Registrations chart --}}
<div class="modal fade" id="registrationBreakdownModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content reg-breakdown-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie mr-1"></i>Source Breakdown — <span id="reg-breakdown-period">-</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reg-breakdown-loading" class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm mr-2"></span>Memuat...
                </div>
                <div id="reg-breakdown-empty" class="text-center text-muted py-5" style="display:none;">
                    Tidak ada pendaftar di periode ini.
                </div>
                <div id="reg-breakdown-content" style="display:none;">

                    {{-- Stat cards --}}
                    <div class="reg-stat-row mb-3">
                        <div class="reg-stat-card reg-stat-total">
                            <div class="reg-stat-value" id="reg-breakdown-total">0</div>
                            <div class="reg-stat-label">Total Pendaftar</div>
                        </div>
                        <div class="reg-stat-card reg-stat-leads">
                            <div class="reg-stat-value" id="reg-breakdown-leads">0</div>
                            <div class="reg-stat-label">
                                Potential Leads <span id="reg-breakdown-leads-pct" class="text-muted font-weight-normal"></span>
                                <i class="fas fa-info-circle ml-1" data-toggle="tooltip"
                                    title="Members who indicated Open to Sponsorship (Explore Marketing) — same definition as Lead in the Lead Follow-Up menu."></i>
                            </div>
                            <div class="reg-stat-sub" id="reg-breakdown-leads-split"></div>
                        </div>
                    </div>

                    {{-- Source + Status chips, side by side --}}
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="reg-section-label">Source</div>
                            <div class="reg-chip-row" id="reg-breakdown-chips"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="reg-section-label">
                                Status Verifikasi
                                <i class="fas fa-info-circle ml-1" data-toggle="tooltip"
                                    title="Verified = approved by admin (Active). Waiting = pending review. Declined = rejected — no longer a viable sponsorship prospect."></i>
                            </div>
                            <div class="reg-chip-row" id="reg-breakdown-status-chips"></div>
                        </div>
                    </div>

                    {{-- Table toolbar --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px;">
                        <h6 class="mb-0">Daftar Member <small class="text-muted font-weight-normal" id="reg-breakdown-members-note"></small></h6>
                        <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                            <select id="reg-breakdown-status-filter" class="form-control form-control-sm" style="width:auto;">
                                <option value="">Semua status</option>
                                <option value="active">Verified</option>
                                <option value="waiting">Waiting</option>
                                <option value="declined">Declined</option>
                                <option value="deactivated">Deactivated</option>
                            </select>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="reg-breakdown-leads-only">
                                <label class="custom-control-label small" for="reg-breakdown-leads-only">Leads saja</label>
                            </div>
                            <div class="reg-search-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" id="reg-breakdown-search" class="form-control form-control-sm"
                                    placeholder="Cari nama, email, company, source...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive reg-breakdown-table-wrap">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Source</th>
                                    <th width="90px">Status</th>
                                    <th class="text-center" width="60px">Lead</th>
                                    <th class="text-nowrap">Registered</th>
                                </tr>
                            </thead>
                            <tbody id="reg-breakdown-members-tbody"></tbody>
                        </table>
                        <div id="reg-breakdown-no-match" class="text-center text-muted py-4" style="display:none;">
                            Tidak ada member yang cocok dengan filter/pencarian ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
