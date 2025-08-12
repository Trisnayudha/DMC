@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Users Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Users Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Users </h2>
                <div class="alert alert-warning alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>×</span>
                        </button>
                        Data displayed from January 2025 to the current date.
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="far fa-user"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Registered Member</h4>
                                </div>
                                <div class="card-body">
                                    {{ $countMember }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="far fa-user"></i>
                            </div>
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
                            <div class="card-icon bg-danger">
                                <i class="far fa-user"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Unregistration Member</h4>
                                </div>
                                <div class="card-body">
                                    {{ $countUnRegistered }} <span><a href="{{ url('admin/users?filter=unregist') }}"
                                            class="badge badge-info">Show
                                            Data</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
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

                                <div class="float-right d-flex">
                                    <a href="{{ url('admin/users') }}"
                                        class="btn btn-icon icon-left btn-warning btn-filter mb-3 mr-2">
                                        Clear Filter</a>
                                    <button type="button" class="btn btn-primary mb-3" data-toggle="modal"
                                        data-target="#example">
                                        Import Excel
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
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
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d,F Y H:i', strtotime($post->created_at)) }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->office_number }}</td>
                                                    <td>
                                                        {{ $post->company_category }}
                                                    </td>
                                                    <td>
                                                        {{ $post->company_website }}
                                                    </td>
                                                    <td>
                                                        {{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}
                                                    </td>
                                                    <td>
                                                        {{-- Label status --}}
                                                        {{ $post->cci ? 'cci' : '' }} -
                                                        {{ $post->explore ? 'explore' : '' }}

                                                        {{-- Tombol Import via AJAX --}}
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary ml-2 btn-import-mailchimp"
                                                            data-url="{{ route('users.import.mailchimp', $post->id) }}"
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
                </div>
            </div>
        </section>
    </div>
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

        // Klik import langsung eksekusi
        $(document).on('click', '.btn-import-mailchimp', function() {
            const $btn = $(this);
            const url = $btn.data('url');
            if (!url || $btn.prop('disabled')) return;

            const tags = parseTags($btn.attr('data-tags'));
            const originalHtml = $btn.html();

            // Loading state
            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Importing...'
            );

            $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: tags.length ? {
                        tags: tags
                    } : {}
                })
                .done(function(res) {
                    if (res && res.success) {
                        showAlert('success', res.message || 'Berhasil diimport.');
                        $btn.removeClass('btn-outline-primary').addClass('btn-success')
                            .html('<i class="fas fa-check"></i> Imported');
                    } else {
                        showAlert('warning', (res && res.message) ? res.message :
                            'Terjadi masalah saat import.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    let msg = 'Gagal menghubungi server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showAlert('danger', msg);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });

        // DataTable tetap seperti semula
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
            });
        });
    </script>
@endpush
