<!-- Modal: Add Sponsor Contact to Event -->
<div class="modal fade" id="addToEventModal" tabindex="-1" role="dialog" aria-labelledby="addToEventModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addToEventModalLabel">
                    <i class="fas fa-calendar-plus"></i> Register Sponsor Contact to Event
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label class="font-weight-bold">Sponsor <span class="text-danger">*</span></label>
                    <select id="ateSponsorSelect" class="form-control" required>
                        <option value="">— Select Sponsor —</option>
                        @foreach ($allSponsorsWithMembers as $s)
                            <option value="{{ $s->id }}" data-name="{{ $s->branding_name ?: $s->name }}">
                                {{ $s->branding_name ?: $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Mode: existing contact / new person --}}
                <div class="form-group">
                    <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                        <label class="btn btn-outline-primary active flex-fill" id="ateModeExistingBtn">
                            <input type="radio" name="ateMode" value="existing" checked> Existing Contact
                        </label>
                        <label class="btn btn-outline-primary flex-fill disabled" id="ateModeNewBtn"
                            style="opacity:.45;pointer-events:none" title="Select a sponsor first">
                            <input type="radio" name="ateMode" value="new"> New Contact
                        </label>
                    </div>
                </div>

                {{-- Existing contact picker --}}
                <div class="form-group" id="ateContactGroup">
                    <label class="font-weight-bold">Contact <span class="text-danger">*</span></label>
                    <select id="ateContactSelect" class="form-control" required disabled>
                        <option value="">— Select Sponsor first —</option>
                    </select>
                    <small class="text-muted">
                        Contacts with a user account are listed first. Selecting a PIC/Representative without an
                        account opens the form below, pre-filled — the account is created automatically.
                    </small>
                </div>

                {{-- New Contact form — focused on 4 main fields --}}
                <div id="ateNewPersonForm" style="display:none">
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ateName">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Email address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="ateEmail">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Mobile number</label>
                                <input type="text" class="form-control" id="atePhone">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Job Title</label>
                                <input type="text" class="form-control" id="ateJobTitle">
                            </div>
                        </div>
                    </div>

                    {{-- Hidden fields — auto-filled from sponsor data --}}
                    <div style="display:none">
                        <select id="atePrefix">
                            <option value="PT">PT</option>
                            <option value="CV">CV</option>
                            <option value="Ltd">Ltd</option>
                            <option value="GmbH">GmbH</option>
                            <option value="Limited">Limited</option>
                            <option value="Llc">Llc</option>
                            <option value="Corp">Corp</option>
                            <option value="Pte Ltd">Pte Ltd</option>
                            <option value="Assosiation">Assosiation</option>
                            <option value="Government">Government</option>
                            <option value="Pty Ltd">Pty Ltd</option>
                            <option value="">Other</option>
                        </select>
                        <input type="text" id="ateCompanyName">
                        <input type="text" id="ateCompanyWebsite">
                        <input type="text" id="ateAddress">
                        <input type="text" id="ateOfficeNumber">
                        <input type="text" id="ateCompanyOther">
                        <select id="ateCountry">
                            <option value="Indonesia" selected>Indonesia</option>
                        </select>
                        <select id="ateCompanyCategory">
                            @include('partials._company_category_options')
                        </select>
                        <div id="ateCompanyOtherGroup"></div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Ticket</label>
                                <select class="form-control" id="ateTicket">
                                    <option value="sponsor">Invitation ( Free No Cost Sponsor)</option>
                                    <option value="free">Invitation ( Free No Cost Non Sponsor )</option>
                                    <option value="member">Membership ( Rp. 900.000 )</option>
                                    <option value="nonmember">Non Member ( Rp. 1.000.000 )</option>
                                    <option value="onsite">On Site ( Rp. 1.250.000 )</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Event <span class="text-danger">*</span></label>
                    <select id="ateEventId" class="form-control" required>
                        <option value="">— Select Event —</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}">
                                {{ $event->name }} ({{ \Carbon\Carbon::parse($event->start_date)->format('M Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="ateRegisterMemberGroup" style="display:none">
                    <div class="alert alert-warning py-2 mb-2" style="font-size:13px">
                        <i class="fas fa-info-circle"></i>
                        <strong>Notice:</strong> This contact is not yet registered as a member in the system.
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="ateRegisterAsMember">
                        <label class="custom-control-label" for="ateRegisterAsMember">
                            <strong>Register as member (status: <span
                                    class="badge badge-warning">Pending</span>)</strong>
                            <div class="text-muted" style="font-size:12px;font-weight:normal">
                                A WhatsApp notification will be sent to the membership team for verification.
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="ateSendEmail" checked>
                        <label class="custom-control-label" for="ateSendEmail">
                            Send email confirmation + ticket/invoice to this person
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="ateSubmitBtn" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i> Register to Event
                </button>
            </div>
        </div>
    </div>
</div>
