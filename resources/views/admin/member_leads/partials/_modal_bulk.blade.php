{{-- Modal: log sponsor kit / follow-up for all ticked leads at once --}}
<div class="modal fade" id="bulkFollowUpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-comment-dots mr-1"></i>Bulk Follow-up / Send Kit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong class="bulk-count-text">0</strong> lead(s) selected.</p>
                <div class="form-group">
                    <label>Step</label>
                    <select id="bf-step" class="form-control">
                        <option value="">Next step of each lead</option>
                        <option value="sponsorkit">Send Sponsor Kit only</option>
                        <option value="follow_up_1">Follow-up 1 only</option>
                        <option value="follow_up_2">Follow-up 2 only</option>
                    </select>
                    <small class="text-muted">With a specific step, leads whose next step is different are skipped. Leads that aren't pending, are flagged Do Not Send, or are already done are always skipped.</small>
                </div>
                <div class="form-group">
                    <label>Date &amp; Time</label>
                    <input type="datetime-local" id="bf-date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Channel</label>
                    <select id="bf-channel" class="form-control">
                        <option value="">— Keep current —</option>
                        <option value="call">Call</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Notes</label>
                    <textarea id="bf-notes" rows="3" class="form-control" placeholder="Optional — added to every selected lead's notes history"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bf-btn-submit">
                    <i class="fas fa-check mr-1"></i> Log
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: set temperature categories for all ticked leads at once --}}
<div class="modal fade" id="bulkTemperatureModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-thermometer-half mr-1"></i>Bulk Temperature</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong class="bulk-count-text">0</strong> lead(s) selected. Only categories you change are applied; the rest stay as they are on each lead.</p>
                @foreach (\App\Services\LeadTemperatureService::POINTS as $field => $points)
                    <div class="form-group d-flex align-items-center justify-content-between mb-2">
                        <label class="mb-0">
                            <strong>{{ $points }}</strong> {{ \App\Services\LeadTemperatureService::LABELS[$field] }}
                            @if (in_array($field, \App\Services\LeadTemperatureService::AUTO_FIELDS, true))
                                <span class="badge badge-light border ml-1">auto</span>
                            @endif
                        </label>
                        <select class="form-control form-control-sm bt-change" data-field="{{ $field }}" style="width:130px;">
                            <option value="">No change</option>
                            <option value="1">Check</option>
                            <option value="0">Uncheck</option>
                        </select>
                    </div>
                @endforeach
                <small class="text-muted">Auto categories already proven by data (event attendance, email opens/clicks) stay checked even if set to Uncheck.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bt-btn-submit">
                    <i class="fas fa-save mr-1"></i> Apply
                </button>
            </div>
        </div>
    </div>
</div>
