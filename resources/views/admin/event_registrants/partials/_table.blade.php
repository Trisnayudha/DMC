{{-- Main table card: filter + table --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-user-check mr-1"></i>Registrants of All Events</h4>
    </div>

    <div class="card-body">

        <form action="{{ url('admin/event-registrants') }}" method="GET"
            class="d-flex flex-wrap align-items-end mb-3" style="gap:10px;">
            <div class="form-group mb-0" style="min-width:240px; flex:1 1 240px;">
                <label class="mb-1 small text-muted">Event</label>
                <select name="event_id" class="form-control form-control-sm">
                    <option value="">All Events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" {{ (string) $filters['event_id'] === (string) $event->id ? 'selected' : '' }}>
                            {{ $event->name }}{{ $event->start_date ? ' (' . \Carbon\Carbon::parse($event->start_date)->format('d M Y') . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Package</label>
                <select name="package" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package }}" {{ $filters['package'] === $package ? 'selected' : '' }}>{{ $package }}</option>
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
                <label class="mb-1 small text-muted">Registered From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Registered To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ url('admin/event-registrants') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-striped" id="event-registrants-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Registered</th>
                        <th>Event</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Member</th>
                        <th>Check-in</th>
                    </tr>
                </thead>
                {{-- Baris diisi DataTables dari JSON di _scripts --}}
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>
