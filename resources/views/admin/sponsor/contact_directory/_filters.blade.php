@php
    // Filter tipe kontak multi-pilih (mis. PIC + CC). Kosong = semua kontak.
    // Export Excel mengikuti filter yang sedang aktif.
    $contactTypes = [
        'pic'            => ['PIC', 'user-tie'],
        'cc'             => ['CC', 'envelope'],
        'billing'        => ['Billing', 'file-invoice-dollar'],
        'representative' => ['Representatives', 'user-friends'],
    ];
    $roles = $roles ?? [];
    $typeLabel = $roles
        ? implode(' + ', array_map(function ($r) use ($contactTypes) { return $contactTypes[$r][0]; }, $roles))
        : 'All Contact Types';
    $exportParams = array_filter(['search' => $search, 'package' => $package, 'roles' => $roles]);
@endphp
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
                <option value="platinum" {{ $package === 'platinum' ? 'selected' : '' }}>Major</option>
                <option value="gold"     {{ $package === 'gold'     ? 'selected' : '' }}>Gold</option>
                <option value="silver"   {{ $package === 'silver'   ? 'selected' : '' }}>Silver</option>
            </select>

            <div class="dropdown">
                <button type="button" class="btn btn-sm dropdown-toggle {{ $roles ? 'btn-outline-primary' : 'btn-light border' }}"
                    data-toggle="dropdown" style="min-width:170px;text-align:left">
                    <i class="fas fa-users mr-1"></i> <span id="contactTypeLabel">{{ $typeLabel }}</span>
                </button>
                <div class="dropdown-menu p-2 contact-type-menu" style="min-width:200px">
                    <div class="px-1 pb-1 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px">Contact type (select one or more)</div>
                    @foreach ($contactTypes as $type => $meta)
                        <div class="custom-control custom-checkbox mx-1 my-1">
                            <input type="checkbox" name="roles[]" class="custom-control-input contact-type-check"
                                id="contact-type-{{ $type }}" value="{{ $type }}" data-label="{{ $meta[0] }}"
                                {{ in_array($type, $roles, true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="contact-type-{{ $type }}" style="font-size:13px">
                                <i class="fas fa-{{ $meta[1] }} mr-1 text-muted"></i> {{ $meta[0] }}
                            </label>
                        </div>
                    @endforeach
                    <small class="d-block text-muted px-1 mt-1">Leave all unchecked to show every contact.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            @if($search || $package || $roles)
                <a href="{{ route('sponsors.contact-directory') }}" class="btn btn-light btn-sm">Reset</a>
            @endif
            <span class="text-muted ml-2" style="font-size:13px">{{ $sponsors->count() }} sponsor{{ $sponsors->count() === 1 ? '' : 's' }} found</span>

            <a href="{{ route('sponsors.contact-directory.export', $exportParams) }}" class="btn btn-success btn-sm ml-auto"
                title="Exports the contacts shown with the current filters">
                <i class="fas fa-file-excel"></i> Export Excel{{ $roles ? ' (' . $typeLabel . ')' : '' }}
            </a>
        </form>
    </div>
</div>

@push('bottom')
<script>
    // Keep the contact type menu open while ticking several types, and update the button label.
    $(document).on('click', '.contact-type-menu', function(e) { e.stopPropagation(); });
    $(document).on('change', '.contact-type-check', function() {
        var labels = $('.contact-type-check:checked').map(function() { return $(this).data('label'); }).get();
        $('#contactTypeLabel').text(labels.length ? labels.join(' + ') : 'All Contact Types');
    });
</script>
@endpush
