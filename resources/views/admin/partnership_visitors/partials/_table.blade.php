{{-- Main table card: filter + table --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-handshake mr-1"></i>Visitors of All Partnership Events</h4>
    </div>

    <div class="card-body">

        <form action="{{ url('admin/partnership-visitors') }}" method="GET"
            class="d-flex flex-wrap align-items-end mb-3" style="gap:10px;">
            <div class="form-group mb-0" style="min-width:240px; flex:1 1 240px;">
                <label class="mb-1 small text-muted">Event</label>
                <select name="event_id" class="form-control form-control-sm">
                    <option value="">All Partnership Events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" {{ (string) $filters['event_id'] === (string) $event->id ? 'selected' : '' }}>
                            {{ $event->name }}{{ $event->start_date ? ' (' . \Carbon\Carbon::parse($event->start_date)->format('d M Y') . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Membership</label>
                <select name="membership" class="form-control form-control-sm">
                    <option value="">All</option>
                    <option value="member" {{ $filters['membership'] === 'member' ? 'selected' : '' }}>Member (Active)</option>
                    <option value="non_member" {{ $filters['membership'] === 'non_member' ? 'selected' : '' }}>Non Member</option>
                </select>
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ url('admin/partnership-visitors') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-striped" id="partnership-visitors-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Event Date</th>
                        <th>Event</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Remarks</th>
                        <th>Member</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list as $visitor)
                        <tr>
                            <td data-order="{{ $visitor->start_date }}" class="text-nowrap">{{ $visitor->start_date ? \Carbon\Carbon::parse($visitor->start_date)->format('d M Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.partnership_events.show', $visitor->event_slug) }}">{{ $visitor->event_name }}</a>
                            </td>
                            <td>{{ $visitor->name ?: '-' }}</td>
                            <td>{{ $visitor->company_name ?: '-' }}</td>
                            <td>{{ $visitor->job_title ?: '-' }}</td>
                            <td>{{ $visitor->business_email ?: '-' }}</td>
                            <td class="text-nowrap">{{ $visitor->mobile_number ?: '-' }}</td>
                            <td>{{ $visitor->remarks ?: '-' }}</td>
                            <td class="text-nowrap">
                                @if ($visitor->member_status === 'active')
                                    <span class="badge badge-primary">Member</span>
                                @elseif ($visitor->member_status)
                                    <span class="badge badge-light">{{ ucfirst($visitor->member_status) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
