{{-- Modal: notes history per lead + add a note (e.g. prospect's reply) --}}
<div class="modal fade" id="leadNotesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history mr-1"></i>Notes History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Lead: <strong id="ln-member-name">-</strong></p>

                <div id="ln-list" class="mb-3" style="max-height:300px; overflow-y:auto;"></div>

                <div class="border-top pt-3">
                    <div class="form-group">
                        <label>Step</label>
                        <select id="ln-step" class="form-control">
                            <option value="">General</option>
                            <option value="sponsorkit">Send Sponsor Kit</option>
                            <option value="follow_up_1">Follow-up 1</option>
                            <option value="follow_up_2">Follow-up 2</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>New Note</label>
                        <textarea id="ln-note" rows="3" class="form-control" placeholder="e.g. prospect's reply..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="ln-btn-submit">
                    <i class="fas fa-plus mr-1"></i> Add Note
                </button>
            </div>
        </div>
    </div>
</div>
