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
                <h2 class="section-title">Data Breakdown</h2>
                <p class="text-muted">Counts follow the selected view below. Each displayed row belongs to one category; historical event-only data is identified from available status, registration and source information.</p>
                @php
                    $cards = [
                        'intake' => ['Data Tampung', 'XTWP records', 'info'],
                        'active' => ['Active Member', 'Approved members', 'success'],
                        'pending' => ['Pending Member', 'Awaiting review', 'warning'],
                        'declined' => ['Declined', 'Declined applications', 'danger'],
                        'deactivated' => ['Deactivated', 'Deactivated members', 'secondary'],
                        'event' => ['Event Only', 'Event registrants who have not registered as members', 'primary'],
                    ];
                @endphp
                <div class="row">
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="card card-statistic-1 master-category-card" role="button" tabindex="0" aria-pressed="true" data-category-all="true" style="cursor:pointer; outline:2px solid #6777ef;">
                            <div class="card-icon bg-primary"><i class="fas fa-database"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>All Data</h4></div>
                                <div class="card-body">{{ number_format(array_sum($breakdown)) }}</div>
                            </div>
                            <div class="px-3 pb-3 text-muted small">All rows in the selected view</div>
                        </div>
                    </div>
                    @foreach ($cards as $key => [$label, $description, $color])
                        <div class="col-6 col-md-4 {{ $key === 'event' ? 'col-xl-6' : 'col-xl-3' }}">
                            <div class="card card-statistic-1 master-category-card" role="button" tabindex="0" aria-pressed="false" data-category-label="{{ $label }}" style="cursor:pointer;">
                                <div class="card-icon bg-{{ $color }}"><i class="fas fa-users"></i></div>
                                <div class="card-wrap">
                                    <div class="card-header"><h4>{{ $label }}</h4></div>
                                    <div class="card-body">{{ number_format($breakdown[$key]) }}</div>
                                </div>
                                <div class="px-3 pb-3 text-muted small">{{ $description }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

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
                                                <tr id="row_{{ $post->source }}_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $post->source == 'users' ? 'primary' : 'info' }}">
                                                            {{ strtoupper($post->source) }}
                                                        </span>
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

                                                    <td data-order="{{ $post->created_at ? date('Y-m-d H:i:s', strtotime($post->created_at)) : '' }}">{{ $post->created_at ? date('d M Y H:i', strtotime($post->created_at)) : '-' }}
                                                    </td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->office_number }}</td>
                                                    <td style="min-width:180px; max-width:240px;">
                                                        <div class="master-address-text" style="overflow-wrap:anywhere; @if (\Illuminate\Support\Str::length($post->address ?? '') > 70) display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; @endif">{{ $post->address ?: '-' }}</div>
                                                        @if (\Illuminate\Support\Str::length($post->address ?? '') > 70)
                                                            <button type="button" class="btn btn-link btn-sm p-0 master-address-toggle" aria-expanded="false">Show more</button>
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
                buttons: [
                    { extend: 'copyHtml5', exportOptions: exportOptions },
                    { extend: 'excelHtml5', exportOptions: exportOptions },
                    { extend: 'csvHtml5', exportOptions: exportOptions },
                    { extend: 'pdfHtml5', exportOptions: exportOptions }
                ],
                pageLength: 25,
                order: [
                    // default sort by Date Register desc (kolom index berubah kalau mode=duplicates)
                    [{{ ($mode ?? 'deduped') == 'duplicates' ? 6 : 3 }}, 'desc']
                ]
            });
            $('#laravel_crud').on('click', '.master-address-toggle', function() {
                var button = $(this);
                var expanded = button.attr('aria-expanded') === 'true';
                button.siblings('.master-address-text').css('-webkit-line-clamp', expanded ? '2' : 'unset');
                button.attr('aria-expanded', expanded ? 'false' : 'true').text(expanded ? 'Show more' : 'Show less');
            });
            function filterCategory(card) {
                var wasSelected = card.attr('aria-pressed') === 'true';
                $('.master-category-card').attr('aria-pressed', 'false').css('outline', '');
                if (wasSelected || card.data('category-all')) {
                    table.column(2).search('').draw();
                    $('.master-category-card[data-category-all]').attr('aria-pressed', 'true').css('outline', '2px solid #6777ef');
                } else {
                    card.attr('aria-pressed', 'true').css('outline', '2px solid #6777ef');
                    table.column(2).search('^' + $.fn.dataTable.util.escapeRegex(card.data('category-label')) + '$', true, false).draw();
                }
            }
            $('.master-category-card').on('click', function() { filterCategory($(this)); }).on('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    filterCategory($(this));
                }
            });
        });
    </script>
@endpush
