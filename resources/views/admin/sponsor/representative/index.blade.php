@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsor Representative Attendance</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors Management</a></div>
                    <div class="breadcrumb-item active">Representative Attendance</div>
                </div>
                <div class="section-header-action">
                    <a href="{{ route('sponsors.contact-directory') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-address-book"></i> Contact Directory
                    </a>
                </div>
            </div>

            <div class="section-body">

                <!-- Filter -->
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="{{ route('sponsors.representative.index') }}"
                            class="form-inline flex-wrap" style="gap:8px">
                            <label class="mb-0 mr-1">Year:</label>
                            <input type="number" name="year" class="form-control form-control-sm"
                                value="{{ $year }}" min="2000" max="{{ now()->year }}" style="width:90px">

                            <label class="mb-0 mr-1 ml-2">Sponsor:</label>
                            <select name="company" class="form-control form-control-sm" style="min-width:200px">
                                <option value="">— All Sponsors —</option>
                                @foreach ($sponsorList as $sponsor)
                                    <option value="{{ $sponsor->name }}"
                                        {{ $filterSponsor == $sponsor->name ? 'selected' : '' }}>
                                        {{ $sponsor->branding_name ?: $sponsor->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i>
                                Filter</button>
                            @if ($filterSponsor || $year != now()->year)
                                <a href="{{ route('sponsors.representative.index') }}"
                                    class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="mb-0">Representative Event Attendance — {{ $year }}</h4>
                                    <small class="text-muted">Active sponsors only</small>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addToEventModal">
                                    <i class="fas fa-calendar-plus"></i> Add Member to Event
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Name</th>
                                                <th>Sponsor</th>
                                                <th>Attend Time</th>
                                                <th>Event</th>
                                                <th>Check-in</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($representatives as $index => $rep)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $rep->representative_name }}</td>
                                                    <td>{{ $rep->company }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($rep->attend_time)->format('d M Y H:i') }}</td>
                                                    <td>{{ $rep->event_name }}</td>
                                                    <td>
                                                        @if ($rep->present)
                                                            <span class="badge badge-success">Present</span>
                                                            <small class="text-muted ml-1">{{ \Carbon\Carbon::parse($rep->present)->format('d M Y H:i') }}</small>
                                                        @else
                                                            <span class="badge badge-secondary">Not Present</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-info btn-resend"
                                                            data-payment-id="{{ $rep->payment_id }}"
                                                            data-name="{{ $rep->representative_name }}"
                                                            data-email="{{ $rep->representative_email }}"
                                                            data-phone="{{ $rep->representative_phone }}"
                                                            data-code="{{ $rep->code_payment }}"
                                                            data-event="{{ $rep->event_name }}"
                                                            data-date="{{ $rep->start_date ? \Carbon\Carbon::parse($rep->start_date)->format('d M Y') : '' }}"
                                                            data-toggle="modal"
                                                            data-target="#resendTicketModal">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-3">No attendance records found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Non-Attend Sponsors Table -->
                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    Sponsors with No Representative Attendance — {{ $year }}
                                    <span class="badge badge-warning ml-1">{{ $nonAttendSponsors->count() }}</span>
                                </h4>
                                <small class="text-muted">Active sponsors with no event attendance this year — contact info
                                    shown for follow-up</small>
                            </div>
                            <div class="card-body p-0">
                                @if ($nonAttendSponsors->isEmpty())
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                        All active sponsors have attendance records this year.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table id="laravel_crud_non_attend" class="table table-bordered table-hover mb-0"
                                            style="font-size:13px">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width:40px">No</th>
                                                    <th style="width:160px">Sponsor</th>
                                                    <th>PIC <small class="text-muted font-weight-normal">(Primary
                                                            contact)</small></th>
                                                    <th>Representatives</th>
                                                    <th>Members</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($nonAttendSponsors as $index => $sponsor)
                                                    <tr style="vertical-align:top">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $sponsor->branding_name ?: $sponsor->name }}</div>
                                                            <span
                                                                class="badge badge-{{ $sponsor->package === 'platinum' ? 'primary' : ($sponsor->package === 'gold' ? 'warning' : 'secondary') }} mt-1">
                                                                {{ ucfirst($sponsor->package) }}
                                                            </span>
                                                        </td>

                                                        {{-- PICs (sponsors_pic) --}}
                                                        <td>
                                                            @if ($sponsor->pics->isEmpty())
                                                                <span class="text-muted" style="font-size:12px"><i
                                                                        class="fas fa-user-slash"></i> No PIC</span>
                                                            @else
                                                                @foreach ($sponsor->pics as $pic)
                                                                    <div class="d-flex align-items-start mb-2"
                                                                        style="gap:8px">
                                                                        <div
                                                                            style="width:30px;height:30px;border-radius:50%;background:#6c757d;color:#fff;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                                                        </div>
                                                                        <div style="line-height:1.4">
                                                                            <div class="font-weight-bold">
                                                                                {{ $pic->name }}</div>
                                                                            @if ($pic->title)
                                                                                <div class="text-muted"
                                                                                    style="font-size:11px">
                                                                                    {{ $pic->title }}</div>
                                                                            @endif
                                                                            <div class="d-flex flex-wrap mt-1"
                                                                                style="gap:8px">
                                                                                @if ($pic->email)
                                                                                    <a href="mailto:{{ $pic->email }}"
                                                                                        style="font-size:11px"
                                                                                        class="text-primary">
                                                                                        <i class="fas fa-envelope"></i>
                                                                                        {{ $pic->email }}
                                                                                    </a>
                                                                                @endif
                                                                                @if ($pic->phone)
                                                                                    <a href="tel:{{ $pic->phone }}"
                                                                                        style="font-size:11px"
                                                                                        class="text-success">
                                                                                        <i class="fas fa-phone"></i>
                                                                                        {{ $pic->phone }}
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </td>

                                                        {{-- Representatives (sponsors_representative) --}}
                                                        <td>
                                                            @if ($sponsor->representatives->isEmpty())
                                                                <span class="text-muted" style="font-size:12px"><i
                                                                        class="fas fa-user-slash"></i> No
                                                                    representatives</span>
                                                            @else
                                                                @foreach ($sponsor->representatives as $rep)
                                                                    <div class="d-flex align-items-start mb-2"
                                                                        style="gap:8px">
                                                                        <div
                                                                            style="width:30px;height:30px;border-radius:50%;background:#007bff;color:#fff;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                                                            {{ strtoupper(substr($rep->name, 0, 1)) }}
                                                                        </div>
                                                                        <div style="line-height:1.4">
                                                                            <div class="font-weight-bold">
                                                                                {{ $rep->name }}</div>
                                                                            @if ($rep->job_title)
                                                                                <div class="text-muted"
                                                                                    style="font-size:11px">
                                                                                    {{ $rep->job_title }}</div>
                                                                            @endif
                                                                            <div class="d-flex flex-wrap mt-1"
                                                                                style="gap:8px">
                                                                                @if ($rep->email)
                                                                                    <a href="mailto:{{ $rep->email }}"
                                                                                        style="font-size:11px"
                                                                                        class="text-primary">
                                                                                        <i class="fas fa-envelope"></i>
                                                                                        {{ $rep->email }}
                                                                                    </a>
                                                                                @endif
                                                                                @if ($rep->instagram)
                                                                                    <a href="https://instagram.com/{{ ltrim($rep->instagram, '@') }}"
                                                                                        target="_blank"
                                                                                        style="font-size:11px"
                                                                                        class="text-danger">
                                                                                        <i class="fab fa-instagram"></i>
                                                                                        {{ $rep->instagram }}
                                                                                    </a>
                                                                                @endif
                                                                                @if ($rep->linkedin)
                                                                                    <a href="{{ $rep->linkedin }}"
                                                                                        target="_blank"
                                                                                        style="font-size:11px"
                                                                                        class="text-info">
                                                                                        <i class="fab fa-linkedin"></i>
                                                                                        LinkedIn
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </td>

                                                        {{-- Members (users via payment.sponsor_id) --}}
                                                        <td>
                                                            @if ($sponsor->members->isEmpty())
                                                                <span class="text-muted" style="font-size:12px"><i
                                                                        class="fas fa-user-slash"></i> No members</span>
                                                            @else
                                                                @foreach ($sponsor->members as $member)
                                                                    <div class="d-flex align-items-start mb-2"
                                                                        style="gap:8px">
                                                                        <div
                                                                            style="width:30px;height:30px;border-radius:50%;background:#28a745;color:#fff;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                                                        </div>
                                                                        <div style="line-height:1.4">
                                                                            <div class="font-weight-bold">
                                                                                {{ $member->name }}</div>
                                                                            @if ($member->status_member)
                                                                                <div class="text-muted"
                                                                                    style="font-size:11px">
                                                                                    {{ $member->status_member }}</div>
                                                                            @endif
                                                                            <div class="d-flex flex-wrap mt-1"
                                                                                style="gap:8px">
                                                                                @if ($member->email)
                                                                                    <a href="mailto:{{ $member->email }}"
                                                                                        style="font-size:11px"
                                                                                        class="text-primary">
                                                                                        <i class="fas fa-envelope"></i>
                                                                                        {{ $member->email }}
                                                                                    </a>
                                                                                @endif
                                                                                @if ($member->fullphone)
                                                                                    <a href="tel:{{ $member->fullphone }}"
                                                                                        style="font-size:11px"
                                                                                        class="text-success">
                                                                                        <i class="fas fa-phone"></i>
                                                                                        {{ $member->fullphone }}
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@include('admin.sponsor.representative._add_to_event_modal')
@include('admin.sponsor.representative._resend_ticket_modal')

@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });

        // ── Resend ticket ──────────────────────────────────────────────────
        var resendPaymentId = null;

        // Populate modal saat tombol resend diklik
        $(document).on('click', '.btn-resend', function() {
            var btn   = $(this);
            resendPaymentId = btn.data('payment-id');
            var name  = btn.data('name');
            var email = btn.data('email') || '';
            var phone = btn.data('phone') || '';
            var code  = btn.data('code')  || '';
            var event = btn.data('event') || '';
            var date  = btn.data('date')  || '';

            $('#resendRecipientLabel').text('— ' + name);
            $('#resendEmailTo').val(email);
            $('#resendEmailSubject').val(code + ' - Your registration is approved for ' + event);
            $('#resendEmailBody').val('');
            $('#resendWaPhone').val(phone);
            $('#resendWaMessage').val(
                'Hi ' + name + ',\n\n' +
                'Your e-ticket for *' + event + '* (' + date + ') is ready.\n\n' +
                'Ticket Code: *' + code + '*\n\n' +
                'Please show this code at the registration desk.\n\n' +
                'Thank you,\nDMC Team'
            );
        });

        // Show/hide email & WA sections
        $('#resendViaEmail').on('change', function() {
            $('#resendEmailSection').toggle(this.checked);
        });
        $('#resendViaWa').on('change', function() {
            $('#resendWaSection').toggle(this.checked);
        });

        // Reset modal saat ditutup
        $('#resendTicketModal').on('hidden.bs.modal', function() {
            resendPaymentId = null;
            $('#resendViaEmail').prop('checked', true);
            $('#resendViaWa').prop('checked', false);
            $('#resendEmailSection').show();
            $('#resendWaSection').hide();
        });

        // Submit resend
        $('#resendSubmitBtn').on('click', function() {
            if (!resendPaymentId) return;

            var sendEmail = $('#resendViaEmail').is(':checked');
            var sendWa    = $('#resendViaWa').is(':checked');

            if (!sendEmail && !sendWa) {
                toastr.warning('Please select at least one channel.', '', {positionClass:'toast-top-right'});
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

            $.ajax({
                url:    '/admin/sponsors-representative-count/' + resendPaymentId + '/resend-ticket',
                method: 'POST',
                data: {
                    _token:        '{{ csrf_token() }}',
                    send_email:    sendEmail ? 1 : '',
                    send_wa:       sendWa    ? 1 : '',
                    email_subject: $('#resendEmailSubject').val(),
                    email_body:    $('#resendEmailBody').val(),
                    wa_message:    $('#resendWaMessage').val(),
                },
                success:  function(r) {
                    if (r.success) {
                        toastr.success(r.message, 'Sent', {positionClass:'toast-top-right'});
                        $('#resendTicketModal').modal('hide');
                    } else {
                        toastr.error(r.message, 'Error', {positionClass:'toast-top-right'});
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                    toastr.error(msg, 'Error', {positionClass:'toast-top-right'});
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');
                }
            });
        });

        var sponsorContactsMap = @json($sponsorContactsMap);
        var selectedContact    = null; // currently selected contact object

        // ── Helpers ──────────────────────────────────────────────────────────
        function roleLabel(role) {
            if (role === 'pic')     return '<span class="badge badge-primary ml-1">PIC</span>';
            if (role === 'billing') return '<span class="badge badge-warning ml-1">Billing</span>';
            return '<span class="badge badge-secondary ml-1">Rep</span>';
        }

        function resetModal() {
            $('#ateSponsorSelect').val('');
            $('#ateContactSelect').html('<option value="">— Select Sponsor first —</option>').prop('disabled', true);
            $('#ateEventId').val('');
            $('#ateSendEmail').prop('checked', true);
            selectedContact = null;
            setMode('existing');
            clearNewPersonForm();
        }

        function setMode(mode) {
            if (mode === 'existing') {
                $('#ateModeExistingBtn').addClass('active');
                $('#ateModeNewBtn').removeClass('active');
                $('input[name="ateMode"][value="existing"]').prop('checked', true);
                $('#ateContactGroup').show();
                $('#ateNewPersonForm').hide();
                $('#ateRegisterMemberGroup').hide();
            } else {
                $('#ateModeNewBtn').addClass('active');
                $('#ateModeExistingBtn').removeClass('active');
                $('input[name="ateMode"][value="new"]').prop('checked', true);
                $('#ateContactGroup').hide();
                $('#ateNewPersonForm').show();
                $('#ateRegisterMemberGroup').show();
            }
        }

        function clearNewPersonForm() {
            $('#atePrefix').val('PT');
            $('#ateName,#ateCompanyWebsite,#ateJobTitle,#ateCompanyName,#ateEmail,#atePhone,#ateAddress,#ateOfficeNumber,#ateCompanyOther').val('');
            $('#ateCountry').val('Indonesia');
            $('#ateCompanyCategory').val('');
            $('#ateCompanyOtherGroup').hide();
            $('#ateTicket').val('sponsor');
            $('#ateRegisterAsMember').prop('checked', false);
        }

        function prefillCompanyFromSponsor(sponsorId) {
            if (!sponsorId || !sponsorContactsMap[sponsorId]) return;
            var s = sponsorContactsMap[sponsorId];
            if (s.company_name)     $('#ateCompanyName').val(s.company_name);
            if (s.company_website)  $('#ateCompanyWebsite').val(s.company_website);
            if (s.prefix)           $('#atePrefix').val(s.prefix);
            if (s.country)          $('#ateCountry').val(s.country);
            if (s.address)          $('#ateAddress').val(s.address);
            if (s.office_number)    $('#ateOfficeNumber').val(s.office_number);
            if (s.company_category) {
                $('#ateCompanyCategory').val(s.company_category).trigger('change');
            }
        }

        function prefillNewPersonForm(contact) {
            $('#ateName').val(contact.name || '');
            $('#ateEmail').val(contact.email || '');
            $('#atePhone').val(contact.phone || '');
            $('#ateJobTitle').val(contact.title || '');
        }

        // ── Reset on open ────────────────────────────────────────────────────
        $('#addToEventModal').on('show.bs.modal', function() { resetModal(); });

        // ── Mode toggle ──────────────────────────────────────────────────────
        $('#ateModeExistingBtn').on('click', function() { setMode('existing'); });
        $('#ateModeNewBtn').on('click', function() {
            setMode('new');
            clearNewPersonForm();
            // Auto-fill company fields from currently selected sponsor
            var sponsorId = $('#ateSponsorSelect').val();
            if (sponsorId) prefillCompanyFromSponsor(sponsorId);
        });

        // ── Sponsor change → populate contacts ───────────────────────────────
        $('#ateSponsorSelect').on('change', function() {
            var sponsorId = $(this).val();
            var $sel      = $('#ateContactSelect');
            var $newBtn   = $('#ateModeNewBtn');
            selectedContact = null;
            $sel.html('<option value="">— Select Contact —</option>');

            // Enable / disable tombol "New Contact"
            if (sponsorId) {
                $newBtn.removeClass('disabled').css({opacity: '', 'pointer-events': ''}).attr('title', '');
            } else {
                $newBtn.addClass('disabled').css({opacity: '.45', 'pointer-events': 'none'}).attr('title', 'Select a sponsor first');
                // If sponsor is reset, revert to existing mode
                if ($('input[name="ateMode"]:checked').val() === 'new') {
                    setMode('existing');
                    clearNewPersonForm();
                }
            }

            var contacts = (sponsorId && sponsorContactsMap[sponsorId]) ? sponsorContactsMap[sponsorId].contacts : [];

            if (sponsorId && contacts && contacts.length > 0) {
                $.each(contacts, function(i, c) {
                    var label = c.name + (c.email ? ' (' + c.email + ')' : '') + (c.user_id ? ' ✓' : ' [no account]');
                    $sel.append('<option value="' + i + '">' + label + '</option>');
                });
                $sel.prop('disabled', false);
            } else if (sponsorId) {
                $sel.html('<option value="">— No contacts found —</option>').prop('disabled', true);
            } else {
                $sel.html('<option value="">— Select Sponsor first —</option>').prop('disabled', true);
            }

            // If in New Contact mode, prefill company from sponsor
            var mode = $('input[name="ateMode"]:checked').val();
            if (mode === 'new' && sponsorId) {
                prefillCompanyFromSponsor(sponsorId);
            }
        });

        // ── Contact change → existing user atau prefill new contact ───────────
        $('#ateContactSelect').on('change', function() {
            var sponsorId = $('#ateSponsorSelect').val();
            var idx       = $(this).val();
            if (!sponsorId || idx === '') { selectedContact = null; return; }

            var contacts = sponsorContactsMap[sponsorId] ? sponsorContactsMap[sponsorId].contacts : [];
            selectedContact = contacts[idx];

            if (!selectedContact.user_id) {
                // No account yet → switch to New Contact mode, prefill from contact data
                setMode('new');
                prefillCompanyFromSponsor(sponsorId);  // Fill company from sponsor first
                prefillNewPersonForm(selectedContact);  // Override name/email/phone/title
            } else {
                setMode('existing');
            }
        });

        // ── Company Other toggle ─────────────────────────────────────────────
        $('#ateCompanyCategory').on('change', function() {
            if ($(this).val() === 'other') {
                $('#ateCompanyOtherGroup').show();
            } else {
                $('#ateCompanyOtherGroup').hide();
                $('#ateCompanyOther').val('');
            }
        });

        // ── Submit ───────────────────────────────────────────────────────────
        $('#ateSubmitBtn').on('click', function() {
            var sponsorId = $('#ateSponsorSelect').val();
            var eventId   = $('#ateEventId').val();
            var mode      = $('input[name="ateMode"]:checked').val();
            var btn       = $(this);

            if (!sponsorId) { toastr.warning('Please select a sponsor.', '', {positionClass:'toast-top-right'}); return; }
            if (!eventId)   { toastr.warning('Please select an event.', '',   {positionClass:'toast-top-right'}); return; }

            if (mode === 'existing') {
                if (!selectedContact || !selectedContact.user_id) {
                    toastr.warning('Please select a contact with a user account, or switch to New Contact.', '', {positionClass:'toast-top-right'});
                    return;
                }
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registering...');
                $.ajax({
                    url:    '{{ route("sponsors.representative.add_to_event") }}',
                    method: 'POST',
                    data: {
                        _token:     '{{ csrf_token() }}',
                        user_id:    selectedContact.user_id,
                        event_id:   eventId,
                        sponsor_id: sponsorId,
                        send_email: $('#ateSendEmail').is(':checked') ? 1 : '',
                    },
                    success:  function(r) {
                        if (r.success) { toastr.success(r.message, 'Success', {positionClass:'toast-top-right'}); $('#addToEventModal').modal('hide'); }
                        else           { toastr.error(r.message, 'Error',   {positionClass:'toast-top-right'}); }
                    },
                    error:    function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
                        toastr.error(msg, 'Error', {positionClass:'toast-top-right'});
                    },
                    complete: function() { btn.prop('disabled', false).html('<i class="fas fa-calendar-check"></i> Register to Event'); }
                });

            } else {
                // New person
                var name  = $.trim($('#ateName').val());
                var email = $.trim($('#ateEmail').val());
                if (!name)  { toastr.warning('Name is required.', '', {positionClass:'toast-top-right'}); return; }
                if (!email) { toastr.warning('Email is required.', '', {positionClass:'toast-top-right'}); return; }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registering...');
                $.ajax({
                    url:    '{{ route("sponsors.representative.add_new_person") }}',
                    method: 'POST',
                    data: {
                        _token:             '{{ csrf_token() }}',
                        sponsor_id:         sponsorId,
                        event_id:           eventId,
                        send_email:         $('#ateSendEmail').is(':checked') ? 1 : '',
                        register_as_member: $('#ateRegisterAsMember').is(':checked') ? 1 : '',
                        prefix:             $('#atePrefix').val(),
                        name:               name,
                        email:              email,
                        phone:              $('#atePhone').val(),
                        job_title:          $('#ateJobTitle').val(),
                        company_name:       $('#ateCompanyName').val(),
                        company_website:    $('#ateCompanyWebsite').val(),
                        company_category:   $('#ateCompanyCategory').val(),
                        company_other:      $('#ateCompanyOther').val(),
                        address:            $('#ateAddress').val(),
                        office_number:      $('#ateOfficeNumber').val(),
                        country:            $('#ateCountry').val(),
                        ticket:             $('#ateTicket').val(),
                    },
                    success:  function(r) {
                        if (r.success) { toastr.success(r.message, 'Success', {positionClass:'toast-top-right'}); $('#addToEventModal').modal('hide'); }
                        else           { toastr.error(r.message, 'Error',   {positionClass:'toast-top-right'}); }
                    },
                    error:    function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
                        toastr.error(msg, 'Error', {positionClass:'toast-top-right'});
                    },
                    complete: function() { btn.prop('disabled', false).html('<i class="fas fa-calendar-check"></i> Register to Event'); }
                });
            }
        });
    </script>
@endpush
