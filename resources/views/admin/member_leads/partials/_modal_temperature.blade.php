{{-- Modal: SOP lead scoring checklist → HOT / WARM / COLD --}}
<div class="modal fade" id="leadTemperatureModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-thermometer-half mr-1"></i>Lead Temperature</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Lead: <strong id="lt-member-name">-</strong></p>
                <p class="mb-3">
                    <span id="lt-badge" class="badge temp-badge temp-cold" style="font-size:12px;">COLD</span>
                    <span class="ml-1">Score: <strong id="lt-score">0</strong> / 100</span>
                </p>

                <div id="lt-items"></div>

                <small class="text-muted d-block mt-2">
                    HOT 70-100 · WARM 40-69 · COLD 0-39. <span class="badge badge-light border">auto</span>
                    categories are checked automatically from event attendance and email opens/clicks.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="lt-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
