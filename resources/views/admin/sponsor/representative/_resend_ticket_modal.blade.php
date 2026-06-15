<div class="modal fade" id="resendTicketModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane"></i> Resend E-Ticket
                    <small class="font-weight-normal ml-2" id="resendRecipientLabel"></small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">

                {{-- Channel --}}
                <div class="form-group">
                    <label class="font-weight-bold">Send via</label>
                    <div class="d-flex" style="gap:16px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="resendViaEmail" checked>
                            <label class="custom-control-label" for="resendViaEmail">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="resendViaWa">
                            <label class="custom-control-label" for="resendViaWa">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Email section --}}
                <div id="resendEmailSection">
                    <hr class="mt-1 mb-3">
                    <div class="font-weight-bold text-muted mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px">
                        <i class="fas fa-envelope mr-1"></i> Email
                    </div>
                    <div class="form-group">
                        <label>To</label>
                        <input type="text" id="resendEmailTo" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="form-group">
                        <label>Subject <span class="text-muted font-weight-normal">(editable)</span></label>
                        <input type="text" id="resendEmailSubject" class="form-control form-control-sm">
                    </div>
                    <div class="form-group">
                        <label>Custom Note <span class="text-muted font-weight-normal">(optional — ditampilkan di atas body email template)</span></label>
                        <textarea id="resendEmailBody" class="form-control form-control-sm" rows="3"
                            placeholder="Kosongkan untuk menggunakan body email default..."></textarea>
                    </div>
                    <small class="text-muted"><i class="fas fa-info-circle"></i> PDF ticket tetap dilampirkan secara otomatis.</small>
                </div>

                {{-- WA section --}}
                <div id="resendWaSection" style="display:none">
                    <hr class="mt-3 mb-3">
                    <div class="font-weight-bold text-muted mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="resendWaPhone" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="form-group">
                        <label>Message <span class="text-muted font-weight-normal">(editable)</span></label>
                        <textarea id="resendWaMessage" class="form-control form-control-sm" rows="6"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="resendSubmitBtn" class="btn btn-info">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>
