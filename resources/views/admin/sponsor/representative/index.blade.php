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
                                        {{ $sponsor->name }}
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($representatives as $index => $rep)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $rep->representative_name }}</td>
                                                    <td>{{ $rep->company }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($rep->attend_time)->format('d M Y H:i') }}
                                                    </td>
                                                    <td>{{ $rep->event_name }}</td>
                                                    <td>
                                                        @if ($rep->present)
                                                            <span class="badge badge-success">Present</span>
                                                            <small
                                                                class="text-muted ml-1">{{ \Carbon\Carbon::parse($rep->present)->format('d M Y H:i') }}</small>
                                                        @else
                                                            <span class="badge badge-secondary">Not Present</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">No attendance
                                                        records found.</td>
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
                                                            <div class="font-weight-bold">{{ $sponsor->name }}</div>
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

<!-- Modal: Add Sponsor Member to Event -->
<div class="modal fade" id="addToEventModal" tabindex="-1" role="dialog" aria-labelledby="addToEventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addToEventModalLabel">
                    <i class="fas fa-calendar-plus"></i> Register Sponsor Member to Event
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
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Member <span class="text-danger">*</span></label>
                    <select id="ateMemberSelect" class="form-control" required disabled>
                        <option value="">— Select Sponsor first —</option>
                    </select>
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
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="ateSendEmail" checked>
                        <label class="custom-control-label" for="ateSendEmail">
                            Send email confirmation + ticket PDF to member
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

@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });

        // Sponsor members map (preloaded from PHP)
        var sponsorMembersMap = @json($sponsorMembersMap);

        // Reset modal when it opens
        $('#addToEventModal').on('show.bs.modal', function() {
            $('#ateSponsorSelect').val('');
            $('#ateMemberSelect').html('<option value="">— Select Sponsor first —</option>').prop('disabled', true);
            $('#ateEventId').val('');
            $('#ateSendEmail').prop('checked', true);
        });

        // Populate members when sponsor changes
        $('#ateSponsorSelect').on('change', function() {
            var sponsorId = $(this).val();
            var $memberSelect = $('#ateMemberSelect');
            $memberSelect.html('<option value="">— Select Member —</option>');

            if (sponsorId && sponsorMembersMap[sponsorId] && sponsorMembersMap[sponsorId].length > 0) {
                $.each(sponsorMembersMap[sponsorId], function(i, member) {
                    $memberSelect.append('<option value="' + member.id + '">' + member.name + '</option>');
                });
                $memberSelect.prop('disabled', false);
            } else if (sponsorId) {
                $memberSelect.html('<option value="">— No members found —</option>').prop('disabled', true);
            } else {
                $memberSelect.html('<option value="">— Select Sponsor first —</option>').prop('disabled', true);
            }
        });

        // Submit Add to Event
        $('#ateSubmitBtn').on('click', function() {
            var sponsorId = $('#ateSponsorSelect').val();
            var userId    = $('#ateMemberSelect').val();
            var eventId   = $('#ateEventId').val();

            if (!sponsorId) {
                toastr.warning('Please select a sponsor.', '', { positionClass: 'toast-top-right' });
                return;
            }
            if (!userId) {
                toastr.warning('Please select a member.', '', { positionClass: 'toast-top-right' });
                return;
            }
            if (!eventId) {
                toastr.warning('Please select an event.', '', { positionClass: 'toast-top-right' });
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registering...');

            $.ajax({
                url: '{{ route("sponsors.representative.add_to_event") }}',
                method: 'POST',
                data: {
                    _token:     '{{ csrf_token() }}',
                    user_id:    userId,
                    event_id:   eventId,
                    sponsor_id: sponsorId,
                    send_email: $('#ateSendEmail').is(':checked') ? 1 : '',
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success', { positionClass: 'toast-top-right' });
                        $('#addToEventModal').modal('hide');
                    } else {
                        toastr.error(response.message, 'Error', { positionClass: 'toast-top-right' });
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'An error occurred.';
                    toastr.error(msg, 'Error', { positionClass: 'toast-top-right' });
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-calendar-check"></i> Register to Event');
                }
            });
        });
    </script>
@endpush
