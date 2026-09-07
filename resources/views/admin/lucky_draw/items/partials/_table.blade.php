{{-- Main table card: chance budget summary + add button + prize list --}}

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <h4 class="mb-0">
            <i class="fas fa-gift mr-1"></i>Prize List
            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                title="Chance % is set manually per prize. A 0% prize never gets drawn. If the active prizes' total chance is under 100%, the remainder is the chance a visitor gets no prize at all."
                data-toggle="tooltip"></i>
        </h4>
        <div class="d-flex align-items-center" style="gap:8px;">
            <span class="badge {{ $usedChance >= 100 ? 'badge-success' : 'badge-warning' }}" style="font-size:12px;">
                Active chance total: {{ rtrim(rtrim(number_format($usedChance, 2, '.', ''), '0'), '.') }}%
                (remaining {{ rtrim(rtrim(number_format(max(0, round(100 - $usedChance, 2)), 2, '.', ''), '0'), '.') }}%)
            </span>
            <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">
                <i class="fas fa-plus mr-1"></i> Add Prize
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive lucky-draw-table-wrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="10px">No</th>
                        <th width="70px">Photo</th>
                        <th>Prize Name</th>
                        <th class="text-nowrap" width="110px">Chance %</th>
                        <th width="80px">Order</th>
                        <th width="90px">Status</th>
                        <th width="110px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>
                                @if ($item->image)
                                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" width="44" height="44"
                                        style="object-fit:cover;border-radius:6px;">
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>{{ $item->name }}</td>
                            <td class="text-nowrap">
                                <strong>{{ rtrim(rtrim(number_format($item->chance_percent, 2, '.', ''), '0'), '.') }}%</strong>
                            </td>
                            <td>{{ $item->sort_order }}</td>
                            <td>
                                @if ($item->is_active)
                                    <span class="badge badge-success mini-badge">Active</span>
                                @else
                                    <span class="badge badge-secondary mini-badge">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-icon-group">
                                    <button type="button" class="btn btn-icon btn-outline-secondary btn-edit-item"
                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                        data-chance="{{ $item->chance_percent }}" data-sort="{{ $item->sort_order }}"
                                        data-active="{{ $item->is_active ? 1 : 0 }}"
                                        data-image="{{ $item->image ? asset($item->image) : '' }}"
                                        data-update-url="{{ route('admin.lucky_draw.items.update', $item->id) }}"
                                        title="Edit" data-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.lucky_draw.items.destroy', $item->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete prize &quot;{{ $item->name }}&quot;?');">
                                        @csrf
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="Delete"
                                            data-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No prizes yet. Click "Add Prize"
                                to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>{{-- /card --}}

<style>
    /* Compact, modern look — same treatment as the Lead Follow-Up table
       (resources/views/admin/member_leads/partials/_table.blade.php). */
    .lucky-draw-table-wrap table {
        border-collapse: separate !important;
        border: none;
    }
    .lucky-draw-table-wrap table thead th {
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
    .lucky-draw-table-wrap table tbody td {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        font-size: 11.5px;
        padding: .4rem .5rem;
    }
    .lucky-draw-table-wrap table tbody tr:hover {
        background-color: #f7f9fc;
    }
    .lucky-draw-table-wrap .btn-icon {
        width: 22px;
        height: 22px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        line-height: 1;
        border-radius: 5px;
    }
    .lucky-draw-table-wrap .btn-icon-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 3px;
    }
    .lucky-draw-table-wrap .mini-badge {
        font-size: 9.5px;
        padding: .2em .45em;
        font-weight: 600;
    }
</style>
