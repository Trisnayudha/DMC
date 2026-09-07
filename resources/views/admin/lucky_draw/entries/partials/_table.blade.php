{{-- Main table card: result tabs + search + entries list --}}

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-list mr-1"></i>Draw History
            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                title="Recipient details (name/company/contact/business card photo) are filled in by the visitor themselves, optionally, through the public /lucky-draw page."
                data-toggle="tooltip"></i>
        </h4>
    </div>

    <div class="card-body">

        {{-- Result tabs --}}
        <div class="mb-3">
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/lucky-draw/entries?' . http_build_query(array_merge(request()->except('result'), ['result' => 'all']))) }}"
                    class="btn btn-sm {{ $result === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                    <i class="fas fa-list mr-1"></i> All
                    <span class="badge badge-light ml-1">{{ $countTotal }}</span>
                </a>
                <a href="{{ url('admin/lucky-draw/entries?' . http_build_query(array_merge(request()->except('result'), ['result' => 'won']))) }}"
                    class="btn btn-sm {{ $result === 'won' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle mr-1"></i> Won
                    <span class="badge badge-light ml-1">{{ $countWon }}</span>
                </a>
                <a href="{{ url('admin/lucky-draw/entries?' . http_build_query(array_merge(request()->except('result'), ['result' => 'lost']))) }}"
                    class="btn btn-sm {{ $result === 'lost' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-times-circle mr-1"></i> No Prize
                    <span class="badge badge-light ml-1">{{ $countLost }}</span>
                </a>
                <a href="{{ url('admin/lucky-draw/entries?' . http_build_query(array_merge(request()->except('result'), ['result' => 'pending']))) }}"
                    class="btn btn-sm {{ $result === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-clock mr-1"></i> Not Drawn Yet
                    <span class="badge badge-light ml-1">{{ $countPending }}</span>
                </a>
            </div>
        </div>

        {{-- Search --}}
        <form action="{{ url('admin/lucky-draw/entries') }}" method="GET"
            class="d-flex flex-wrap align-items-end mb-3 border-top pt-3" style="gap:10px;">
            <input type="hidden" name="result" value="{{ $result }}">
            <div class="form-group mb-0" style="min-width:260px; flex:1 1 260px;">
                <label class="mb-1 small text-muted">Search</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm"
                    placeholder="Name, company, email, or phone...">
            </div>
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ url('admin/lucky-draw/entries?result=' . $result) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive lucky-draw-entries-table-wrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="10px">No</th>
                        <th class="text-nowrap">Date</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th class="text-nowrap">Phone</th>
                        <th>Email</th>
                        <th width="80px">Business Card</th>
                        <th width="130px">Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = ($list->currentPage() - 1) * $list->perPage() + 1; ?>
                    @forelse ($list as $entry)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td class="text-nowrap"><small>{{ $entry->created_at->format('d M Y H:i') }}</small></td>
                            <td><span class="cell-truncate" title="{{ $entry->name }}">{{ $entry->name ?: '—' }}</span></td>
                            <td><span class="cell-truncate" title="{{ $entry->company_name }}">{{ $entry->company_name ?: '—' }}</span>
                            </td>
                            <td><span class="cell-truncate" title="{{ $entry->job_title }}">{{ $entry->job_title ?: '—' }}</span>
                            </td>
                            <td class="text-nowrap">{{ $entry->phone ?: '—' }}</td>
                            <td><span class="cell-truncate" title="{{ $entry->email }}">{{ $entry->email ?: '—' }}</span></td>
                            <td>
                                @if ($entry->business_card_path)
                                    <a href="{{ asset($entry->business_card_path) }}" target="_blank" rel="noopener">
                                        <img src="{{ asset($entry->business_card_path) }}" alt="Business card" width="44"
                                            height="44" style="object-fit:cover;border-radius:6px;">
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($entry->item)
                                    <span class="badge badge-success mini-badge">{{ $entry->item->name }}</span>
                                @elseif ($entry->drawn_at)
                                    <span class="badge badge-secondary mini-badge">No Prize</span>
                                @else
                                    <span class="badge badge-warning mini-badge">Not Drawn Yet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $list->links() }}

    </div>
</div>{{-- /card --}}

<style>
    .lucky-draw-entries-table-wrap table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fc;
        border-top: none !important;
        border-bottom: 2px solid #e3e6f0 !important;
        border-left: none !important;
        border-right: none !important;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        white-space: nowrap;
        vertical-align: middle;
        padding: .4rem .5rem;
    }
    .lucky-draw-entries-table-wrap table tbody td {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        font-size: 11.5px;
        padding: .4rem .5rem;
    }
    .lucky-draw-entries-table-wrap table tbody tr:hover {
        background-color: #f7f9fc;
    }
    .lucky-draw-entries-table-wrap .mini-badge {
        font-size: 9.5px;
        padding: .2em .45em;
        font-weight: 600;
    }
    .lucky-draw-entries-table-wrap .cell-truncate {
        display: inline-block;
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
</style>
