{{-- Modal: Log a follow-up (1st or 2nd, whichever slot is open) --}}
<div class="modal fade" id="followUpLogModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-comment-dots mr-1"></i>Log <span id="fl-step-label">Follow-up</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Lead: <strong id="fl-member-name">-</strong></p>
                <div class="form-group">
                    <label>Date &amp; Time</label>
                    <input type="datetime-local" id="fl-date" class="form-control">
                    <small class="text-muted">Defaults to now — change it if this action was already done earlier (the time is also used to calculate the next 48-hour SLA).</small>
                </div>
                <div class="form-group">
                    <label>Channel</label>
                    <select id="fl-channel" class="form-control">
                        <option value="">— Select channel —</option>
                        <option value="call">Call</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Notes</label>
                    <textarea id="fl-notes" rows="3" class="form-control" placeholder="Follow-up outcome..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="fl-btn-submit">
                    <i class="fas fa-comment-dots mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
