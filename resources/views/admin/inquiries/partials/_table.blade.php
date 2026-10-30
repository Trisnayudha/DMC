{{-- Main table card: filter + table --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-question-circle mr-1"></i>Contact Us Inquiries</h4>
    </div>

    <div class="card-body">

        <form action="{{ url('admin/inquiry-database') }}" method="GET"
            class="d-flex flex-wrap align-items-end mb-3" style="gap:10px;">
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">Source</label>
                <select name="source" class="form-control form-control-sm">
                    <option value="">All</option>
                    <option value="web" {{ $filters['source'] === 'web' ? 'selected' : '' }}>Website</option>
                    <option value="apps" {{ $filters['source'] === 'apps' ? 'selected' : '' }}>Apps</option>
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">From Date</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <label class="mb-1 small text-muted">To Date</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ url('admin/inquiry-database') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover table-striped" id="inquiries-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Category</th>
                        <th>Message</th>
                        <th>Member</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list as $inquiry)
                        @php $profile = optional($inquiry->user)->profile; @endphp
                        <tr>
                            <td data-order="{{ $inquiry->created_at }}" class="text-nowrap">{{ optional($inquiry->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $inquiry->source === 'apps' ? 'badge-warning' : 'badge-info' }}">{{ $inquiry->source === 'apps' ? 'Apps' : 'Website' }}</span>
                            </td>
                            <td>{{ $inquiry->full_name ?: '-' }}</td>
                            <td>{{ optional(optional($profile)->company)->company_name ?: '-' }}</td>
                            <td>{{ optional($profile)->job_title ?: '-' }}</td>
                            <td>{{ $inquiry->email ?: '-' }}</td>
                            <td class="text-nowrap">{{ $inquiry->phone ?: '-' }}</td>
                            <td>{{ $inquiry->category ?: '-' }}</td>
                            <td style="min-width:320px; white-space:pre-line;">{{ $inquiry->message ?: '-' }}</td>
                            <td class="text-nowrap">
                                @if (optional($inquiry->user)->status_member === 'active')
                                    <span class="badge badge-primary">Member</span>
                                @elseif ($inquiry->user)
                                    <span class="badge badge-light">Registered</span>
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
