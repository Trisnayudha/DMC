@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>News Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">News Management</a>
                    </div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">News </h2>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="far fa-newspaper"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>View News this month</h4>
                                </div>
                                <div class="card-body">
                                    {{ $countView }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="far fa-newspaper"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>All News this month</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalView }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>News Management</h4>
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
                                    <a href="{{ Route('news.create') }}"
                                        class="btn btn-block btn-icon icon-left btn-success btn-filter mb-3"
                                        id="addNewCategory">
                                        <i class="fas fa-plus-circle"></i>
                                        Add News</a>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Date News</th>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Views</th>
                                                <th>Share</th>
                                                <th>Status</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d, F Y', strtotime($post->date_news)) }}</td>
                                                    <td>
                                                        <img alt="image" src="{{ asset($post->image) }}"
                                                            class="rounded-circle" width="35" data-toggle="tooltip">
                                                    </td>
                                                    <td>{{ $post->title }}</td>
                                                    <td>{{ $post->views != null ? $post->views : '0' }}</td>
                                                    <td>{{ $post->share != null ? $post->share : '0' }}</td>
                                                    <td>
                                                        <span
                                                            class="{{ $post->status == 'publish' ? 'badge badge-primary' : 'badge badge-warning' }}">
                                                            {{ $post->status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="https://djakarta-miningclub.com/news/{{ $post->slug }}"
                                                            class="btn btn-primary m-1" target="_blank">
                                                            <span class="fa fa-eye"></span>
                                                        </a>
                                                        <a href="{{ route('news.edit', ['id' => $post->id]) }}"
                                                            class="btn btn-success m-1" title="Edit Data">
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <form method="POST"
                                                            action="{{ route('news.destroy', $post->id) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger m-1" value="`+ row.id +`"
                                                                id="deleteProgram" type="submit" title="Hapus Data">
                                                                <span class="fa fa-trash"></span></button>
                                                        </form>
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
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ]
            });
        });
    </script>
@endpush
