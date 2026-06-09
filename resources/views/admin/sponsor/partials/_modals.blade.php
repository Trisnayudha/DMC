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
                                <label>Amount USD</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">USD</span></div>
                                    <input type="number" name="amount_usd" id="modalAmountUsd" class="form-control" step="0.01" min="0" placeholder="e.g. 2500">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount IDR</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">IDR</span></div>
                                    <input type="number" name="amount_idr" id="modalAmountIdr" class="form-control" step="0.01" min="0" placeholder="e.g. 39000000">
                                </div>
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
