@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Event Detail Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Event Detail Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Event Detail </h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Event Detail Management</h4>
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

                                {{-- <div class="float-right">
                                    <a href="javascript:;"
                                        class="btn btn-block btn-icon icon-left btn-success btn-filter mb-3" id="modal-2">
                                        <i class="fas fa-plus-circle"></i>
                                        Import Data</a>
                                </div> --}}
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
                                                <th>Status Approval</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d,F H:i', strtotime($post->created_at)) }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-pill {{ $post->status == 'Approve' ? 'badge-primary' : 'badge-warning' }}">
                                                            {{ $post->status }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="#" data-toggle="dropdown"
                                                            class="btn btn-info dropdown-toggle">Action</a>
                                                        <ul class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                                            <form action="{{ url('/admin/special-event') }}"
                                                                method="post">
                                                                <li>
                                                                    @csrf
                                                                    <input type="hidden" name="id" id="id"
                                                                        value="{{ $post->id }}">
                                                                    <input type="hidden" name="val" value="approve">
                                                                    <button type="submit"
                                                                        class="dropdown-item">Approve</button>
                                                                </li>
                                                            </form>
                                                            <form action="{{ url('/admin/special-event') }}"
                                                                method="post">
                                                                <li>
                                                                    @csrf
                                                                    <input type="hidden" name="id" id="id"
                                                                        value="{{ $post->id }}">
                                                                    <input type="hidden" name="val" value="reject">
                                                                    <button type="submit"
                                                                        class="dropdown-item">Reject</button>
                                                                </li>
                                                            </form>
                                                        </ul>
                                                        <a href="#" data-id="{{ $post->id }}"
                                                            class="btn btn-success"><span class=" fa fa-eye"></a>
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
                    <form action="{{ Route('events.import') }}" method="post" enctype="multipart/form-data">
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
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
    </script>
@endpush
