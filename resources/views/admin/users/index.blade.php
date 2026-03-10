{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Users Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Users Management</a></div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Users</h2>

                {{-- Alerts --}}
                <div id="alert-area"></div>
                @if (request('filter') == 'this_month')
                    <div class="alert alert-info alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            Showing new members registered in {{ now()->format('F Y') }}.
                        </div>
                    </div>
                @endif
                @if (request('date_from') || request('date_to') || request('month') || request('year'))
                    <div class="alert alert-info alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            Filter applied:
                            @if (request('date_from'))
                                From <strong>{{ request('date_from') }}</strong>
                            @endif

                            @if (request('date_to'))
                                to <strong>{{ request('date_to') }}</strong>
                            @endif

                            @if (request('month'))
                                | Month:
                                <strong>{{ \Carbon\Carbon::create()->month((int) request('month'))->format('F') }}</strong>
                            @endif

                            @if (request('year'))
                                | Year: <strong>{{ request('year') }}</strong>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="row">
                    {{-- Stats --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="far fa-user"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Registered Member</h4>
                                </div>
                                <div class="card-body">{{ $countMember }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="far fa-user"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Verify</h4>
                                </div>
                                <div class="card-body" style="font-size: 12px">
                                    <span class="badge badge-primary">Email: {{ $countVerifyEmail }}</span>
                                    <span class="badge badge-info">Phone: {{ $countVerifyPhone }}</span>
                                    <span class="badge badge-success">Email & Phone : {{ $countDoubleVerify }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger"><i class="far fa-user"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Unregistration Member</h4>
                                </div>
                                <div class="card-body">
                                    {{ $countUnRegistered }}
                                    <span>
                                        <a href="{{ url('admin/users?filter=unregist') }}" class="badge badge-info">Show
                                            Data</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Users Management</h4>
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

                                <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
                                    <form action="{{ url('admin/users') }}" method="GET"
                                        class="d-flex flex-wrap align-items-end">
                                        <div class="form-group mr-2 mb-2">
                                            <label class="mb-1">From Date</label>
                                            <input type="date" name="date_from" class="form-control"
                                                value="{{ request('date_from') }}">
                                        </div>

                                        <div class="form-group mr-2 mb-2">
                                            <label class="mb-1">To Date</label>
                                            <input type="date" name="date_to" class="form-control"
                                                value="{{ request('date_to') }}">
                                        </div>

                                        <div class="form-group mr-2 mb-2">
                                            <label class="mb-1">Month</label>
                                            <select name="month" class="form-control">
                                                <option value="">All</option>
                                                @for ($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}"
                                                        {{ request('month') == $m ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div class="form-group mr-2 mb-2">
                                            <label class="mb-1">Year</label>
                                            <select name="year" class="form-control">
                                                <option value="">All</option>
                                                @for ($y = now()->year; $y >= 2025; $y--)
                                                    <option value="{{ $y }}"
                                                        {{ request('year') == $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>

                                        {{-- keep filter lama kalau ada --}}
                                        @if (request('filter'))
                                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                                        @endif

                                        <div class="form-group mr-2 mb-2">
                                            <button type="submit" class="btn btn-primary">
                                                Filter
                                            </button>
                                        </div>

                                        <div class="form-group mr-2 mb-2">
                                            <a href="{{ url('admin/users') }}" class="btn btn-warning">
                                                Clear Filter
                                            </a>
                                        </div>
                                    </form>

                                    <div class="mb-2">
                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                            data-target="#example">
                                            Import Excel
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Date Register</th>
                                                <th>Nama</th>
                                                <th>Tier</th> {{-- NEW --}}
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
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>

                                                    {{-- IMPORTANT:
                                                        kalau di controller kamu udah pakai alias user_created_at → pakai itu.
                                                        kalau belum, tetap pakai created_at (tapi rawan ketimpa join).
                                                    --}}
                                                    <td>{{ date('d,F Y H:i', strtotime($post->user_created_at ?? $post->created_at)) }}
                                                    </td>

                                                    <td>{{ $post->name }}</td>

                                                    {{-- TIER --}}
                                                    <td style="min-width: 170px;">
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-control form-control-sm user-tier-select"
                                                                data-url="{{ route('users.update.tier', $post->id) }}"
                                                                style="max-width: 130px;">
                                                                @php
                                                                    $tier = strtolower(
                                                                        (string) ($post->tier ?? 'reguler'),
                                                                    );
                                                                    if (!in_array($tier, ['reguler', 'black'])) {
                                                                        $tier = 'reguler';
                                                                    }
                                                                @endphp
                                                                <option value="reguler"
                                                                    {{ $tier === 'reguler' ? 'selected' : '' }}>Reguler
                                                                </option>
                                                                <option value="black"
                                                                    {{ $tier === 'black' ? 'selected' : '' }}>Black
                                                                </option>
                                                            </select>
                                                            <span class="ml-2 badge badge-light tier-status">Saved</span>
                                                        </div>
                                                    </td>

                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>

                                                    {{-- phone: fallback ke fullphone kalau phone kosong --}}
                                                    <td>{{ $post->fullphone ?? $post->phone }}</td>

                                                    {{-- office: fallback ke full_office_number kalau office_number kosong --}}
                                                    <td>{{ $post->office_number ?? $post->full_office_number }}</td>

                                                    {{-- address --}}
                                                    <td>{{ $post->address }}</td>

                                                    <td>{{ $post->company_website }}</td>

                                                    <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}
                                                    </td>

                                                    <td>
                                                        {{ $post->cci ? 'cci' : '' }} -
                                                        {{ $post->explore ? 'explore' : '' }}

                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary ml-2 btn-import-mailchimp"
                                                            data-url="{{ route('users.import.mailchimp', $post->id) }}"
                                                            data-email="{{ $post->email }}"
                                                            data-tags='["Register of Membership {{ now()->format('d M Y') }}"]'
                                                            {{ $post->explore || $post->cci ? '' : 'disabled' }}>
                                                            <i class="fas fa-paper-plane"></i> Import
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>{{-- row --}}
            </div>{{-- section-body --}}
        </section>
    </div>

    {{-- Modal Import --}}
    <div class="modal fade" tabindex="-1" role="dialog" id="example">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ Route('users.import') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="file" name="uploaded_file" id="uploaded_file">
                            <button type="submit" class="btn btn-success">Upload</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="{{ url('sample/sample.xlsx') }}" class="btn btn-primary" download>Download example xlsx</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('bottom')
    <script>
        $('#modal-2').click(function() {
            $('#example').modal('show');
        });

        // CSRF untuk semua request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        // Helper alert (Bootstrap sederhana)
        function showAlert(type, message) {
            if ($('.section-body .alert-area').length === 0) {
                $('.section-body').prepend('<div class="alert-area mb-3"></div>');
            }
            $('.section-body .alert-area').html(
                `<div class="alert alert-${type} alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>×</span></button>
                        ${message}
                    </div>
                </div>`
            );
        }

        // Parse data-tags
        function parseTags(raw) {
            if (!raw) return [];
            if (Array.isArray(raw)) return raw;
            try {
                const j = JSON.parse(raw);
                return Array.isArray(j) ? j : [];
            } catch (e) {
                return String(raw).split(',').map(s => s.trim()).filter(Boolean);
            }
        }

        // Klik import mailchimp
        $(document).on('click', '.btn-import-mailchimp', function() {
            const $btn = $(this);
            const url = $btn.data('url');
            const userId = $btn.data('user-id');
            const email = $btn.data('email');
            const tags = parseTags($btn.attr('data-tags'));

            const original = $btn.html();
            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Importing...'
            );

            $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: userId,
                        email: email,
                        tags: tags
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        $('.section-body').prepend(
                            `<div class="alert alert-success alert-dismissible show fade"><div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>×</span></button>${res.message}
                            </div></div>`
                        );
                        $btn.removeClass('btn-outline-primary').addClass('btn-success')
                            .html('<i class="fas fa-check"></i> Imported');
                    } else {
                        $('.section-body').prepend(
                            `<div class="alert alert-warning alert-dismissible show fade"><div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>×</span></button>${(res && res.message) || 'Import gagal'}
                            </div></div>`
                        );
                        $btn.prop('disabled', false).html(original);
                    }
                })
                .fail(function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Gagal menghubungi server.';
                    $('.section-body').prepend(
                        `<div class="alert alert-danger alert-dismissible show fade"><div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>${msg}
                        </div></div>`
                    );
                    $btn.prop('disabled', false).html(original);
                });
        });

        // ===== NEW: Update Tier (Reguler/Black) via AJAX =====
        $(document).on('change', '.user-tier-select', function() {
            const $select = $(this);
            const url = $select.data('url');
            const tier = $select.val();
            const $badge = $select.closest('td').find('.tier-status');

            $badge.removeClass('badge-light badge-success badge-danger').addClass('badge-warning').text(
                'Saving...');

            $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        tier: tier
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        $badge.removeClass('badge-warning').addClass('badge-success').text('Saved');
                    } else {
                        $badge.removeClass('badge-warning').addClass('badge-danger').text('Failed');
                        showAlert('warning', (res && res.message) ? res.message : 'Gagal update tier.');
                    }
                })
                .fail(function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message :
                        'Gagal menghubungi server.';
                    $badge.removeClass('badge-warning').addClass('badge-danger').text('Failed');
                    showAlert('danger', msg);
                });
        });

        // DataTable
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                pageLength: 25,
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
            });
        });
    </script>
@endpush
