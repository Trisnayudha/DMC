@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Event Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Event Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Event </h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Event Management</h4>
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

                                <div class="float-right">
                                    <a href="{{ Route('events.create') }}"
                                        class="btn btn-block btn-icon icon-left btn-success btn-filter mb-3"
                                        id="addNewCategory">
                                        <i class="fas fa-plus-circle"></i>
                                        Add Event</a>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Date Event</th>
                                                <th>Image</th>
                                                <th>Nama</th>
                                                <th>Status</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d, F', strtotime($post->start_date)) }}</td>
                                                    <td>
                                                        <img alt="image" src="{{ asset($post->image) }}"
                                                            class="rounded-circle" width="35" data-toggle="tooltip">
                                                    </td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>
                                                        <div class="badge {{ $post->status == 'publish' ? 'badge-success' : 'badge-warning' }}"
                                                            style="text-transform: uppercase">
                                                            {{ $post->status }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="#" class="btn btn-primary" title="View Tickets">
                                                            <span>Ticket</span></a>
                                                        <a href="{{ url('share/events/' . $post->slug) }}"
                                                            class="btn btn-success" title="View Tickets">
                                                            <span>Share</span></a>
                                                        <a href="{{ Route('events-details', $post->slug) }}"
                                                            class="btn btn-primary" title="Lihat Peserta">
                                                            <span class="fa fa-user"></span></a>
                                                        <a href="{{ Route('events.edit', $post->id) }}"
                                                            class="btn btn-success" title="Edit Data">
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-danger delete"><span class=" fa fa-trash"></a>
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
@endsection
@push('bottom')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
        $(document).on('click', '.delete', function() {

            var id = $(this).data('id');
            Swal.fire({
                title: "Anda Yakin ?",
                text: "Ingin Menghapus Data ini.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('admin/events/delete') }}",
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(res) {
                            Swal.fire({
                                    title: "Success",
                                    icon: "success",
                                    showConfirmButton: false,
                                    position: 'center',
                                    timer: 1500
                                }),
                                window.location.reload();
                        }
                    });
                }
            });


        });
    </script>
@endpush
