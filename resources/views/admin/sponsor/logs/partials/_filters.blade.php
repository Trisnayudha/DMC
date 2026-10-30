{{-- Filter Sponsor Log --}}

<div class="card mb-3">
    <div class="card-body py-3">
        <form action="{{ route('sponsors.logs') }}" method="GET" class="d-flex flex-wrap align-items-end" style="gap:10px;">
            <div class="form-group mb-0" style="min-width:220px; flex:1 1 220px;">
                <label class="mb-1 small text-muted">Sponsor</label>
                <select name="sponsor_id" class="form-control form-control-sm">
                    <option value="">All Sponsors</option>
                    @foreach ($sponsors as $sponsor)
                        <option value="{{ $sponsor->id }}" {{ (string) $filters['sponsor_id'] === (string) $sponsor->id ? 'selected' : '' }}>
                            {{ $sponsor->branding_name ?: $sponsor->name }} (#{{ $sponsor->id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Section</label>
                <select name="subject" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject }}" {{ $filters['subject'] === $subject ? 'selected' : '' }}>{{ $subject }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Action</label>
                <select name="action" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach (['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted', 'registered' => 'Registered to event', 'ticket_resent' => 'Ticket resent'] as $value => $label)
                        <option value="{{ $value }}" {{ $filters['action'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">By</label>
                <select name="user" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($users as $user)
                        <option value="{{ $user }}" {{ $filters['user'] === $user ? 'selected' : '' }}>{{ $user }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ route('sponsors.logs') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i> Clear</a>
            </div>
        </form>
    </div>
</div>
