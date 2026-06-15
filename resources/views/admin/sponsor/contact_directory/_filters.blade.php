<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('sponsors.contact-directory') }}" class="form-inline flex-wrap" style="gap:8px">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search sponsor name..." value="{{ $search }}" style="min-width:220px">
            </div>
            <select name="package" class="form-control form-control-sm" style="min-width:150px">
                <option value="">All Packages</option>
                <option value="platinum" {{ $package === 'platinum' ? 'selected' : '' }}>Platinum</option>
                <option value="gold"     {{ $package === 'gold'     ? 'selected' : '' }}>Gold</option>
                <option value="silver"   {{ $package === 'silver'   ? 'selected' : '' }}>Silver</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            @if($search || $package)
                <a href="{{ route('sponsors.contact-directory') }}" class="btn btn-light btn-sm">Reset</a>
            @endif
            <span class="text-muted ml-2" style="font-size:13px">{{ $sponsors->count() }} sponsor{{ $sponsors->count() === 1 ? '' : 's' }} found</span>

            <div class="dropdown ml-auto">
                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    @php $exportParams = array_filter(['search' => $search, 'package' => $package]); @endphp
                    <a class="dropdown-item" href="{{ route('sponsors.contact-directory.export', $exportParams + ['role' => 'all']) }}">
                        <i class="fas fa-address-book mr-1 text-muted"></i> All Contacts
                    </a>
                    <a class="dropdown-item" href="{{ route('sponsors.contact-directory.export', $exportParams + ['role' => 'pic']) }}">
                        <i class="fas fa-user-tie mr-1 text-muted"></i> PICs Only
                    </a>
                    <a class="dropdown-item" href="{{ route('sponsors.contact-directory.export', $exportParams + ['role' => 'billing']) }}">
                        <i class="fas fa-file-invoice-dollar mr-1 text-muted"></i> Billing Only
                    </a>
                    <a class="dropdown-item" href="{{ route('sponsors.contact-directory.export', $exportParams + ['role' => 'representative']) }}">
                        <i class="fas fa-user-friends mr-1 text-muted"></i> Representatives Only
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
