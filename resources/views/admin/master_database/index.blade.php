@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Master Database</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Master Database</a></div>
                </div>
            </div>

            <div class="section-body">
                <style>
                    .contact-total {
                        width: 100%;
                        text-align: left;
                        background: #fff;
                        border: 2px solid #e4e7ef;
                        border-radius: 12px;
                        padding: 24px;
                        color: #34395e;
                        margin-bottom: 24px;
                    }

                    .contact-total strong {
                        display: block;
                        font-size: 36px;
                        line-height: 1.3;
                        margin: 6px 0;
                    }

                    .contact-grid {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 20px;
                        margin-bottom: 24px;
                    }

                    .contact-card {
                        background: #fff;
                        border: 2px solid #e4e7ef;
                        border-radius: 12px;
                        padding: 20px;
                        display: flex;
                        flex-direction: column;
                        min-width: 0;
                    }

                    .contact-card-action {
                        background: none;
                        border: 0;
                        padding: 0;
                        width: 100%;
                        text-align: left;
                        color: #34395e;
                        cursor: pointer;
                    }

                    .contact-label {
                        font-size: 15px;
                        font-weight: 700;
                        display: block;
                    }

                    .contact-number {
                        display: block;
                        font-size: 32px;
                        font-weight: 700;
                        line-height: 1.3;
                        margin: 12px 0 2px;
                    }

                    .contact-caption {
                        color: #606b78;
                        display: block;
                        font-size: 13px;
                    }

                    .contact-description {
                        font-size: 13px;
                        line-height: 1.6;
                        color: #606b78;
                        margin: 12px 0 16px;
                    }

                    .contact-details {
                        border-top: 1px solid #e9ebf1;
                        padding-top: 12px;
                        margin-top: auto;
                    }

                    .contact-detail {
                        display: flex;
                        justify-content: space-between;
                        gap: 12px;
                        font-size: 13px;
                        margin: 6px 0;
                        color: #606b78;
                    }

                    button.contact-detail {
                        width: 100%;
                        background: none;
                        border: 0;
                        padding: 4px 0;
                        text-align: left;
                        cursor: pointer;
                    }

                    .contact-detail strong {
                        color: #34395e;
                        font-variant-numeric: tabular-nums;
                    }

                    .contact-active,
                    .contact-active strong {
                        color: #187343;
                        font-weight: 700;
                    }

                    .contact-total[aria-pressed="true"],
                    .contact-card.is-selected {
                        border-color: #6777ef;
                        background: #fafaff;
                    }

                    .contact-card-action:focus-visible,
                    .contact-total:focus-visible,
                    .contact-detail:focus-visible {
                        outline: 3px solid #6777ef;
                        outline-offset: 4px;
                    }

                    .contact-detail[aria-pressed="true"] {
                        background: #eef1ff;
                        border-radius: 4px;
                    }

                    .src-tags {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        gap: 4px;
                        min-width: 120px;
                    }

                    .src-tag {
                        --tag: #606b78;
                        --tag-bg: #f1f2f6;
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        padding: 1px 8px;
                        border: 1px solid transparent;
                        border-radius: 999px;
                        background: var(--tag-bg);
                        color: var(--tag);
                        font-size: 11px;
                        font-weight: 600;
                        line-height: 1.7;
                        white-space: nowrap;
                    }

                    .src-tag::before {
                        content: '';
                        width: 6px;
                        height: 6px;
                        border-radius: 50%;
                        background: var(--tag);
                    }

                    .src-members {
                        --tag: #187343;
                        --tag-bg: #e6f5ec;
                    }

                    .src-events {
                        --tag: #3d4fc4;
                        --tag-bg: #eceefe;
                    }

                    .src-partnership {
                        --tag: #8a5a00;
                        --tag-bg: #fdf1d8;
                    }

                    .src-intake {
                        --tag: #0b6a85;
                        --tag-bg: #e0f3f8;
                    }

                    .src-tag.is-also {
                        background: #fff;
                        border-color: #e4e7ef;
                        color: #7a8290;
                        font-weight: 500;
                    }

                    .src-count {
                        padding: 1px 6px;
                        border-radius: 4px;
                        background: #f1f2f6;
                        color: #606b78;
                        font-size: 11px;
                        font-weight: 600;
                        line-height: 1.7;
                        font-variant-numeric: tabular-nums;
                        cursor: help;
                    }

                    .contact-filter-bar {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 12px;
                        margin-bottom: 16px;
                    }

                    @media(max-width:1199px) {
                        .contact-grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    }

                    @media(max-width:575px) {
                        .contact-grid {
                            grid-template-columns: 1fr;
                        }
                    }
                </style>
                @php
                    $sourceCards = [
                        'members' => ['Members', 'Active members', 'success'],
                        'events' => ['Event Registrant', 'Event participants who never registered as members', 'primary'],
                        'partnership' => ['Event Partnership', 'Contacts found only in event partnership data', 'warning'],
                        'intake' => ['Data Tampung', 'Contacts found only in Data Tampung', 'info'],
                    ];
                    $cards = [
                        'active' => ['Active Member', '', 'success'],
                        'pending' => ['Pending Member', '', 'warning'],
                        'declined' => ['Declined', '', 'danger'],
                        'deactivated' => ['Deactivated', '', 'secondary'],
                        'event' => ['Event Only', '', 'primary'],
                        'intake' => ['Data Tampung', '', 'info'],
                    ];
                @endphp
                <button type="button" class="contact-total master-category-card" aria-pressed="true"
                    data-category-all="true" data-filter-label="All contacts">
                    <span class="contact-label">TOTAL UNIQUE CONTACTS</span>
                    <strong>{{ number_format($totalDeduped) }}</strong>
                    <span class="contact-caption">All sources combined, each person counted once.</span>
                </button>
                <h2 class="section-title mt-0">Contacts by Group</h2>
                <p class="text-muted">Each person appears in one card only. Click a card to see its list.</p>
                <div class="contact-grid">
                    @foreach ($sourceCards as $key => [$label, $caption, $color])
                        <div class="contact-card">
                            <button type="button" class="contact-card-action master-category-card" aria-pressed="false"
                                data-source-filter="{{ $key }}"
                                @if ($key === 'members') data-status="active" @endif
                                data-filter-label="{{ $key === 'members' ? 'Members — Active' : $label }}">
                                <span class="contact-label">{{ $label }}</span>
                                <span
                                    class="contact-number {{ $key === 'members' ? 'contact-active' : '' }}">{{ number_format($key === 'members' ? $breakdown['active'] : $groupCounts[$key]) }}</span>
                                <span class="contact-caption">{{ $caption }}</span>
                            </button>
                            <div class="contact-details">
                                @if ($key === 'members')
                                    @foreach (['active' => 'Active', 'pending' => 'Pending', 'declined' => 'Declined', 'deactivated' => 'Deactivated'] as $status => $statusLabel)
                                        <button type="button"
                                            class="contact-detail master-status-filter {{ $status === 'active' ? 'contact-active' : '' }}"
                                            data-status="{{ $status }}"
                                            data-filter-label="Members — {{ $statusLabel }}"
                                            aria-pressed="false"><span>{{ $statusLabel }}</span><strong>{{ number_format($breakdown[$status]) }}</strong></button>
                                    @endforeach
                                    <div class="contact-detail"><span>All statuses</span><strong>{{ number_format($groupCounts['members']) }}</strong></div>
                                @else
                                    <div class="contact-detail"><span>Full list</span><strong>{{ number_format($sourceCounts[$key]) }}</strong></div>
                                    @foreach ($sourceCards as $group => [$groupLabel])
                                        @if (!empty($sourceOverlap[$key][$group]))
                                            <div class="contact-detail"><span>Already in
                                                    {{ $groupLabel }}</span><strong>−{{ number_format($sourceOverlap[$key][$group]) }}</strong>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($groupCounts['other'])
                    <p class="alert alert-warning">{{ number_format($groupCounts['other']) }} contacts have no identified
                        source. They are included in the total but not in any card above.</p>
                @else
                    <p class="text-muted small">Members (all statuses) {{ number_format($groupCounts['members']) }} + Event
                        Registrant {{ number_format($groupCounts['events']) }} + Event Partnership
                        {{ number_format($groupCounts['partnership']) }} + Data Tampung
                        {{ number_format($groupCounts['intake']) }} = {{ number_format($totalDeduped) }} unique contacts.</p>
                @endif
                <p class="text-muted small mb-4">Contacts are matched by email: the same email in several lists counts
                    as one person. Records without an email are counted one by one.</p>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between"
                                style="width:100%; gap:12px;">
                                <h4 style="margin:0;">Master Database</h4>

                                {{-- Filter Mode + Summary --}}
                                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <form method="GET" action="{{ route('admin.master_database.index') }}"
                                        class="form-inline" style="margin:0;">
                                        <div class="form-group" style="margin:0;">
                                            <select name="mode" class="form-control form-control-sm"
                                                onchange="this.form.submit()">
                                                <option value="deduped"
                                                    {{ ($mode ?? 'deduped') == 'deduped' ? 'selected' : '' }}>
                                                    No Duplicate (Deduped)
                                                </option>
                                                <option value="duplicates"
                                                    {{ ($mode ?? '') == 'duplicates' ? 'selected' : '' }}>
                                                    Duplicates Only (Validation)
                                                </option>
                                                <option value="raw" {{ ($mode ?? '') == 'raw' ? 'selected' : '' }}>
                                                    All Raw (No Dedupe)
                                                </option>
                                            </select>
                                        </div>
                                    </form>

                                    <span class="badge badge-light">Raw: {{ $totalRaw ?? '-' }}</span>
                                    <span class="badge badge-success">Deduped: {{ $totalDeduped ?? '-' }}</span>
                                    <span class="badge badge-warning">Duplicate Rows:
                                        {{ $totalDuplicatesRows ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-warning">
                                        <div class="alert-title">Whoops!</div>
                                        @lang('general.validation_error_message')
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <div class="contact-filter-bar">
                                    <span id="contact-filter-label" role="status" aria-live="polite">Filter: All
                                        contacts</span>
                                    <button type="button" id="contact-reset" class="btn btn-outline-primary btn-sm">Reset
                                        filter</button>
                                </div>
                                @if ($mode !== 'deduped')
                                    <p class="text-muted small">Card menunjukkan kontak unik seluruh database. Tabel
                                        menampilkan
                                        {{ $mode === 'raw' ? 'record mentah' : 'record untuk validasi duplikat' }},
                                        sehingga jumlah baris bisa berbeda.</p>
                                @endif
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Source</th>
                                                <th>Category</th>

                                                {{-- only show when mode=duplicates --}}
                                                @if (($mode ?? 'deduped') == 'duplicates')
                                                    <th>Dup Count</th>
                                                    <th>Picked?</th>
                                                    <th>Unique Key</th>
                                                @endif

                                                <th>Date Register</th>
                                                <th>Nama</th>
                                                <th>Job Title</th>
                                                <th>Company</th>
                                                <th>Email</th>
                                                <th>Phone Number</th>
                                                <th>Office Number</th>
                                                <th>Address</th>
                                                <th>Website</th>
                                                <th>Category Company</th>
                                                <th width="15%">Explore Marketing</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->source }}_{{ $post->id }}"
                                                    data-contact-group="{{ $post->contact_group }}"
                                                    data-member-status="{{ $post->identity_member_status }}"
                                                    data-sources="{{ collect($sourceCards)->keys()->filter(fn($key) => $post->{'in_' . $key})->implode(' ') }}"
                                                    data-duplicate-count="{{ $post->dup_count }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>
                                                        <div class="src-tags">
                                                            @if ($post->contact_group === 'other')
                                                                <span class="src-tag src-other">Other / Unknown</span>
                                                            @else
                                                                <span class="src-tag src-{{ $post->contact_group }}"
                                                                    title="Counted under {{ $sourceCards[$post->contact_group][0] }}">{{ $sourceCards[$post->contact_group][0] }}</span>
                                                            @endif
                                                            @foreach ($sourceCards as $key => [$label])
                                                                @if ($key !== $post->contact_group && $post->{'in_' . $key})
                                                                    <span class="src-tag src-{{ $key }} is-also"
                                                                        title="Also found in {{ $label }}">{{ $label }}</span>
                                                                @endif
                                                            @endforeach
                                                            @if ($post->dup_count > 1)
                                                                <span class="src-count"
                                                                    title="{{ $post->dup_count }} records share this email and are shown as one contact. See Duplicates Only for all of them.">×{{ $post->dup_count }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $cards[$post->category][0] ?? 'Other / Unknown' }}</td>

                                                    @if (($mode ?? 'deduped') == 'duplicates')
                                                        <td>{{ $post->dup_count ?? 0 }}</td>
                                                        <td>
                                                            @if (($post->rn ?? 0) == 1)
                                                                <span class="badge badge-success">KEEP</span>
                                                            @else
                                                                <span class="badge badge-danger">DROP</span>
                                                            @endif
                                                        </td>
                                                        <td
                                                            style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                            {{ $post->unique_key ?? '-' }}
                                                        </td>
                                                    @endif

                                                    <td
                                                        data-order="{{ $post->created_at ? date('Y-m-d H:i:s', strtotime($post->created_at)) : '' }}">
                                                        {{ $post->created_at ? date('d M Y H:i', strtotime($post->created_at)) : '-' }}
                                                    </td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->office_number }}</td>
                                                    <td style="min-width:180px; max-width:240px;">
                                                        <div class="master-address-text"
                                                            style="overflow-wrap:anywhere; @if (\Illuminate\Support\Str::length($post->address ?? '') > 70) display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; @endif">
                                                            {{ $post->address ?: '-' }}</div>
                                                        @if (\Illuminate\Support\Str::length($post->address ?? '') > 70)
                                                            <button type="button"
                                                                class="btn btn-link btn-sm p-0 master-address-toggle"
                                                                aria-expanded="false">Show more</button>
                                                        @endif
                                                    </td>
                                                    <td>{{ $post->company_website }}</td>
                                                    <td>{{ $post->company_category }}</td>
                                                    <td>{{ !empty($post->cci) ? 'cci' : '' }} -
                                                        {{ !empty($post->explore) ? 'explore' : '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div> {{-- card-body --}}
                        </div> {{-- card --}}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {

            // destroy kalau sudah ada (biar aman kalau reload / tab)
            if ($.fn.DataTable.isDataTable('#laravel_crud')) {
                $('#laravel_crud').DataTable().destroy();
            }

            var exportOptions = {
                format: {
                    body: function(data, row, column, node) {
                        var address = $(node).find('.master-address-text');
                        return address.length ? address.text().trim() : $('<div>').html(data).text().trim();
                    }
                }
            };
            var table = $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copyHtml5',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'csvHtml5',
                        exportOptions: exportOptions
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: exportOptions
                    }
                ],
                pageLength: 25,
                order: [
                    // default sort by Date Register desc (kolom index berubah kalau mode=duplicates)
                    [{{ ($mode ?? 'deduped') == 'duplicates' ? 6 : 3 }}, 'desc']
                ]
            });
            var selectedSource = '';
            var selectedStatus = '';
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'laravel_crud') return true;
                var row = $(table.row(dataIndex).node());
                return (!selectedSource || row.attr('data-contact-group') === selectedSource) &&
                    (!selectedStatus || row.attr('data-member-status') === selectedStatus);
            });
            $('#laravel_crud').on('click', '.master-address-toggle', function() {
                var button = $(this);
                var expanded = button.attr('aria-expanded') === 'true';
                button.siblings('.master-address-text').css('-webkit-line-clamp', expanded ? '2' : 'unset');
                button.attr('aria-expanded', expanded ? 'false' : 'true').text(expanded ? 'Show more' :
                    'Show less');
            });

            function applyContactFilter(source, status, label, control) {
                selectedSource = source;
                selectedStatus = status;
                $('.master-category-card, .master-status-filter').attr('aria-pressed', 'false');
                $('.contact-card').removeClass('is-selected');
                control.attr('aria-pressed', 'true').closest('.contact-card').addClass('is-selected');
                $('#contact-filter-label').text('Filter: ' + label);
                table.draw();
            }

            function resetContactFilter() {
                table.search('').columns().search('');
                applyContactFilter('', '', 'All contacts', $('.master-category-card[data-category-all]'));
            }
            $('.master-category-card').on('click', function() {
                var card = $(this);
                if (card.data('category-all') || card.attr('aria-pressed') === 'true')
                return resetContactFilter();
                applyContactFilter(card.attr('data-source-filter'), card.attr('data-status') || '', card
                    .attr('data-filter-label'), card);
            });
            $('.master-status-filter').on('click', function() {
                var button = $(this);
                applyContactFilter('members', button.attr('data-status'), button.attr('data-filter-label'),
                    button);
            });
            $('#contact-reset').on('click', resetContactFilter);
        });
    </script>
@endpush
