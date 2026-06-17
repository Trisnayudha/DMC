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
                                <small class="text-muted">KMK Pajak — auto-fetched</small>
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
                                <label>Amount IDR <small class="text-muted">(auto dari USD × KMK, bisa diubah)</small></label>
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
                                    <small class="text-muted font-weight-normal">(auto-generated, bisa diubah)</small>
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

<!-- Modal: Renewal Follow-up (riwayat + form bukti wajib) -->
<div class="modal fade" id="followupModal" tabindex="-1" role="dialog"
    aria-labelledby="followupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#fffbf0;border-bottom:2px solid #f39c12;">
                <h5 class="modal-title" id="followupModalLabel">
                    <i class="fas fa-phone-volume mr-1" style="color:#f39c12;"></i>
                    Renewal Follow-up — <span id="followupSponsorName"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Riwayat follow-up --}}
                <div class="mb-3">
                    <div class="font-weight-600 text-uppercase text-muted mb-2" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-history mr-1"></i> Follow-up History
                    </div>
                    <div id="followupTimeline" class="border rounded p-2" style="max-height:220px;overflow-y:auto;background:#fafbfc;"></div>
                </div>

                {{-- Form follow-up baru --}}
                <form id="followupForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="followupSponsorId" value="">
                    <div class="font-weight-600 text-uppercase text-muted mb-2" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-plus-circle mr-1"></i> Record New Follow-up
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Renewal Year <span class="text-danger">*</span></label>
                                <input type="number" name="renewal_year" id="followupYear" class="form-control"
                                    min="2020" max="2100" value="{{ now()->year }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Follow-up Date <span class="text-danger">*</span></label>
                                <input type="date" name="followed_up_at" id="followupDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                            placeholder="e.g. Sudah dihubungi via WA, menunggu konfirmasi internal mereka..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Proof / Bukti Follow-up <span class="text-danger">*</span></label>
                        <input type="file" name="proof" id="followupProof" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf" required>
                        <small class="text-muted">Wajib — screenshot chat/email atau dokumen (JPG/PNG/PDF, max 5 MB)</small>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn" style="background:#f39c12;color:#fff;" id="followupSubmitBtn">
                            <i class="fas fa-save mr-1"></i> Save Follow-up
                        </button>
                    </div>
                </form>
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
