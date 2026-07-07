<!-- Modal: Update Contract / Renewal -->
<div class="modal fade" id="updateContractModal" tabindex="-1" role="dialog"
    aria-labelledby="updateContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="updateContractForm">
                @csrf
                @method('POST')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateContractModalLabel">Update Contract / Renewal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalSponsorId" name="sponsor_id" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contract Start <span class="text-danger">*</span></label>
                                <input type="month" name="contract_start" id="modalContractStart" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contract End <span class="text-danger">*</span></label>
                                <input type="month" name="contract_end" id="modalContractEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Renewal Type <span class="text-danger">*</span></label>
                                <select name="renewal_type" id="modalRenewalType" class="form-control" required>
                                    <option value="renewal">Renewal</option>
                                    <option value="upgrade">Renewal - Upgrade</option>
                                    <option value="new">New Sponsor</option>
                                    <option value="new_member">New Member</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Package <span class="text-danger">*</span></label>
                                <select name="package" id="modalPackage" class="form-control" required>
                                    <option value="platinum">Platinum / Major</option>
                                    <option value="gold">Gold</option>
                                    <option value="silver">Silver</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>KMK Rate (USD/IDR)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                    <input type="number" id="modalKmkRate" class="form-control" readonly placeholder="Loading...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="btnRefreshKmkRate" title="Refresh rate">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">KMK Tax Rate — auto-fetched</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount USD</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">USD</span></div>
                                    <input type="number" name="amount_usd" id="modalAmountUsd" class="form-control" step="0.01" min="0" placeholder="e.g. 2500">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount IDR <small class="text-muted">(auto-calculated from USD × KMK, editable)</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                    <input type="text" id="modalAmountIdrDisplay" class="form-control" placeholder="e.g. 39.000.000">
                                    <input type="hidden" name="amount_idr" id="modalAmountIdr">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quotation Number
                                    <small class="text-muted font-weight-normal">(auto-generated, editable)</small>
                                </label>
                                <input type="text" name="quotation_number" id="modalQuotationNumber"
                                    class="form-control" placeholder="e.g. 2026DMC14"
                                    style="font-family:monospace;">
                                <small class="text-muted" id="quotationNumberHint"></small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes / Final Confirmation</label>
                        <textarea name="notes" id="modalNotes" class="form-control" rows="2" placeholder="e.g. Confirmed - Gold Sponsorship USD 3.500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Renewal Process (Step 1 Generate Renewal Form → Step 2 Follow-up) -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog"
    aria-labelledby="followupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#fffbf0;border-bottom:2px solid #f39c12;">
                <h5 class="modal-title" id="followupModalLabel">
                    <i class="fas fa-redo-alt mr-1" style="color:#f39c12;"></i>
                    Renewal Process — <span id="followupSponsorName"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="followupSponsorId" value="">

                {{-- Sponsor picker: default ke sponsor baris yang diklik, bisa diganti --}}
                <div class="form-group row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label font-weight-600 mb-0">Sponsor <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select id="rfSponsorSelect" class="form-control">
                            <option value="">Loading sponsors...</option>
                        </select>
                    </div>
                </div>

                {{-- Shared renewal year: drives both the renewal form & follow-up cycle --}}
                <div class="form-group row align-items-center mb-3">
                    <label class="col-sm-4 col-form-label font-weight-600 mb-0">Renewal Year <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <input type="number" id="renewalYear" class="form-control" min="2020" max="2100" value="{{ now()->year }}">
                    </div>
                </div>

                {{-- ═══ STEP 1 — Renewal Form ═══ --}}
                <div class="mb-4">
                    <div class="font-weight-600 text-uppercase text-muted mb-2" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-file-signature mr-1"></i> Step 1 — Generate Renewal Form
                    </div>

                    {{-- Already generated → summary + preview --}}
                    <div id="renewalFormGenerated" class="border rounded p-3" style="display:none;background:#eafaf0;border-color:#b7e4c7!important;">
                        <div class="d-flex justify-content-between align-items-start" style="gap:10px;">
                            <div style="min-width:0;">
                                <div class="font-weight-700" style="color:#1e7e45;">
                                    <i class="fas fa-check-circle mr-1"></i> Renewal Form <span id="rfGenNumber"></span>
                                </div>
                                <div class="text-muted" style="font-size:12.5px;line-height:1.7;margin-top:3px;">
                                    Generated: <span id="rfGenDate">—</span><span id="rfGenBy"></span><br>
                                    KMK Rate: <span id="rfGenKmk">—</span><br>
                                    KMK Nomor: <span id="rfGenKmkNumber">—</span><br>
                                    <span id="rfGenAmount"></span>
                                </div>
                            </div>
                            <a href="#" target="_blank" id="rfPreviewBtn" class="btn btn-sm btn-light border flex-shrink-0">
                                <i class="fas fa-file-pdf mr-1"></i> Preview Form
                            </a>
                        </div>
                    </div>

                    {{-- Not generated yet → the generate form --}}
                    <form id="renewalFormForm" style="display:none;">
                        @csrf
                        {{-- Notice: ingatkan cek kurs & nomor KMK terbaru sebelum generate --}}
                        <div class="alert alert-warning py-2 mb-3" style="font-size:12.5px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Cek dulu kurs &amp; Nomor KMK terbaru</strong> di
                            <a href="https://fiskal.kemenkeu.go.id/informasi-publik/kurs-pajak" target="_blank" rel="noopener">
                                fiskal.kemenkeu.go.id/informasi-publik/kurs-pajak
                            </a>.
                            Isi <strong>KMK Rate</strong> dan <strong>KMK Nomor</strong> sesuai yang berlaku saat ini.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Renewal Form Number
                                        <small class="text-muted font-weight-normal">(auto-generated, editable)</small>
                                    </label>
                                    <input type="text" id="rfFormNumber" class="form-control"
                                        placeholder="Loading..." style="font-family:monospace;">
                                    <small class="text-muted" id="rfFormNumberHint"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>KMK Rate (USD/IDR) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                        <input type="number" id="rfKmkRate" class="form-control" min="1" placeholder="Loading...">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnRefreshRfKmk" title="Refresh rate">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">KMK Tax Rate — auto-fetched, editable.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>KMK Nomor <span class="text-danger">*</span></label>
                                    <input type="text" id="rfKmkNumber" class="form-control"
                                        placeholder="mis. 30/MK/EF.2/2026">
                                    <small class="text-muted">Nomor KMK dari fiskal.kemenkeu.go.id — input manual.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount USD</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">USD</span></div>
                                        <input type="number" id="rfAmountUsd" class="form-control" step="0.01" min="0" placeholder="e.g. 3500">
                                    </div>
                                    <small class="text-muted" id="rfUsdHint"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount IDR <small class="text-muted">(auto from USD × KMK, editable)</small></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                        <input type="text" id="rfAmountIdrDisplay" class="form-control" placeholder="e.g. 54.000.000">
                                        <input type="hidden" id="rfAmountIdr">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea id="rfNotes" class="form-control" rows="2"
                                placeholder="e.g. Proposal Gold Sponsorship 2026, dikirim via email ke PIC..."></textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn" style="background:#47c363;color:#fff;" id="rfSubmitBtn">
                                <i class="fas fa-file-signature mr-1"></i> Generate Renewal Form
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ═══ STEP 2 — Follow-up ═══ --}}
                <div>
                    <div class="font-weight-600 text-uppercase text-muted mb-2" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-phone-volume mr-1"></i> Step 2 — Follow-up
                    </div>

                    {{-- Locked until the renewal form exists for the selected year --}}
                    <div id="followupLocked" class="border rounded p-3 text-center text-muted" style="display:none;background:#fafbfc;">
                        <i class="fas fa-lock mb-1 d-block" style="opacity:.4;"></i>
                        <span style="font-size:12.5px;">Generate the renewal form first before recording follow-ups.</span>
                    </div>

                    <div id="followupContent" style="display:none;">
                        {{-- Follow-up history --}}
                        <div class="mb-3">
                            <div id="followupTimeline" class="border rounded p-2" style="max-height:200px;overflow-y:auto;background:#fafbfc;"></div>
                        </div>

                        {{-- New follow-up form --}}
                        <form id="followupForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Follow-up Date <span class="text-danger">*</span></label>
                                        <input type="date" name="followed_up_at" id="followupDate" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Channel</label>
                                        <select name="channel" id="followupChannel" class="form-control">
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="email">Email</option>
                                            <option value="call">Phone Call</option>
                                            <option value="meeting">Meeting</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" id="followupNotes" class="form-control" rows="2"
                                    placeholder="e.g. Contacted via WhatsApp, awaiting their internal confirmation..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Proof / Evidence of Follow-up <span class="text-danger">*</span></label>
                                <input type="file" name="proof" id="followupProof" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted">Required — chat/email screenshot or document (JPG/PNG/PDF, max 5 MB)</small>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn" style="background:#f39c12;color:#fff;" id="followupSubmitBtn">
                                    <i class="fas fa-save mr-1"></i> Save Follow-up
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Not Renewed -->
<div class="modal fade" id="notRenewedModal" tabindex="-1" role="dialog"
    aria-labelledby="notRenewedModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="notRenewedForm">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="notRenewedModalLabel">
                        <i class="fas fa-times-circle"></i> Mark as Not Renewed
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Record <strong id="notRenewedSponsorName"></strong> as not renewed.</p>
                    <input type="hidden" id="notRenewedSponsorId" value="">
                    <div class="form-group">
                        <label>Year of Non-Renewal <span class="text-danger">*</span></label>
                        <input type="number" name="renewal_year" id="notRenewedYear" class="form-control"
                            min="2020" max="2100" value="{{ now()->year }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Contract Period (Start)</label>
                                <input type="month" name="contract_start" id="notRenewedContractStart" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Contract Period (End)</label>
                                <input type="month" name="contract_end" id="notRenewedContractEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Reason for Not Renewing</label>
                        <textarea name="notes" id="notRenewedNotes" class="form-control" rows="3"
                            placeholder="e.g. Budget is limited, they will focus on other priorities..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-times-circle"></i> Confirm Not Renewed</button>
                </div>
            </form>
        </div>
    </div>
</div>
