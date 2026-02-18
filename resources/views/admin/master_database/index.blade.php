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
                <h2 class="section-title">All Members</h2>

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

                                                    <td>{{ $post->created_at ? date('d,F H:i', strtotime($post->created_at)) : '-' }}
                                                    </td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->office_number }}</td>
                                                    <td>{{ $post->address }}</td>
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

            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ],
                pageLength: 25,
                order: [
                    // default sort by Date Register desc (kolom index berubah kalau mode=duplicates)
                    [{{ ($mode ?? 'deduped') == 'duplicates' ? 5 : 3 }}, 'desc']
                ]
            });
        });
    </script>
@endpush
