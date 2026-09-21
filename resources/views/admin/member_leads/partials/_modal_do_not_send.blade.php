{{-- Modal: Flag a lead as ineligible for the sponsor kit --}}
<div class="modal fade" id="doNotSendModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban mr-1 text-danger"></i>Flag as Do Not Send</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Lead: <strong id="dns-member-name">-</strong></p>
                <div class="alert alert-warning py-2 small">
                    The sponsor kit will not be sent to this lead until this flag is removed.
                </div>
                <div class="form-group">
                    <label>Reason</label>
                    <select id="dns-reason-category" class="form-control">
                        <option value="Competitor">Competitor</option>
                        <option value="Unqualified Lead">Unqualified Lead</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group mb-0" id="dns-reason-other-wrap" style="display:none;">
                    <label>Reason Details</label>
                    <input type="text" id="dns-reason-other" class="form-control" placeholder="Provide more detail...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="dns-btn-submit">
                    <i class="fas fa-ban mr-1"></i> Flag
                </button>
            </div>
        </div>
    </div>
</div>
