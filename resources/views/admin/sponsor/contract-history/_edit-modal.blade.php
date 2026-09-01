<!-- Modal: Edit Contract Record -->
<div class="modal fade" id="editRenewalModal" tabindex="-1" role="dialog"
    aria-labelledby="editRenewalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editRenewalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editRenewalModalLabel">
                        Edit Contract Record — <span id="editRenewalSponsorName"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editRenewalId" value="">

                    {{-- Referensi read-only: renewal form (proposal) terakhir yang pernah
                         di-generate untuk sponsor ini. Nilainya TIDAK otomatis mengisi field
                         di bawah — cuma buat cross-check, karena form bisa saja dibuat untuk
                         cycle tahun berikutnya, bukan kontrak yang lagi diedit ini. --}}
                    <div id="editRenewalFormRef" class="alert alert-light border" style="display:none;font-size:12.5px;">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <strong><i class="fas fa-file-alt mr-1 text-primary"></i>Renewal form on file:</strong>
                                <span id="editRenewalFormRefNumber" class="font-weight-700"></span>
                                <span class="text-muted">· generated <span id="editRenewalFormRefDate"></span></span>
                                <div class="text-muted mt-1">
                                    KMK: <span id="editRenewalFormRefKmk"></span>
                                    &nbsp;·&nbsp; Value: <span id="editRenewalFormRefAmount"></span>
                                </div>
                                <div id="editRenewalFormRefNotesWrap" class="text-muted mt-1" style="display:none;">
                                    Notes: <span id="editRenewalFormRefNotes"></span>
                                </div>
                            </div>
                            <div class="text-right" style="white-space:nowrap;">
                                <a href="#" id="editRenewalFormRefLink" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button type="button" id="editRenewalFormRefUseBtn" class="btn btn-sm btn-outline-primary mb-1">
                                    <i class="fas fa-arrow-down"></i> Use as Quotation No.
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contract Start <span class="text-danger">*</span></label>
                                <input type="month" name="contract_start" id="editContractStart" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contract End <span class="text-danger">*</span></label>
                                <input type="month" name="contract_end" id="editContractEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Package</label>
                        <select name="package" id="editPackage" class="form-control">
                            <option value="">—</option>
                            <option value="platinum">Platinum / Major</option>
                            <option value="gold">Gold</option>
                            <option value="silver">Silver</option>
                        </select>
                    </div>

                    {{-- Renewal-only fields — only relevant when this record's status is "renewed" --}}
                    <div id="editRenewedFields">
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Renewal Type</label>
                                    <select name="renewal_type" id="editRenewalType" class="form-control">
                                        <option value="">—</option>
                                        <option value="renewal">Renewal</option>
                                        <option value="upgrade">Renewal - Upgrade</option>
                                        <option value="new">New Sponsor</option>
                                        <option value="new_member">New Member</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount USD</label>
                                    <input type="number" name="amount_usd" id="editAmountUsd" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount IDR</label>
                                    <input type="number" name="amount_idr" id="editAmountIdr" class="form-control" step="1" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quotation Number</label>
                                    <input type="text" name="quotation_number" id="editQuotationNumber" class="form-control" style="font-family:monospace;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quotation Date</label>
                                    <input type="date" name="quotation_date" id="editQuotationDate" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Invoice Number</label>
                                    <input type="text" name="invoice_number" id="editInvoiceNumber" class="form-control" style="font-family:monospace;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Invoice Date</label>
                                    <input type="date" name="invoice_date" id="editInvoiceDate" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paid Date
                                        <small class="text-muted font-weight-normal">(fill in once the sponsor has paid)</small>
                                    </label>
                                    <input type="date" name="paid_date" id="editPaidDate" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
