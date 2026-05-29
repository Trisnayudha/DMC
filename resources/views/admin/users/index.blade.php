@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Users Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Users Management</div>
                </div>
            </div>

            <div class="section-body">

                {{-- Flash alerts --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-warning alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            <strong>Whoops!</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div id="alert-area" class="mb-2"></div>

                @include('admin.users.partials._stats')

                @include('admin.users.partials._table')

            </div>{{-- /section-body --}}
        </section>
    </div>

    @include('admin.users.partials._modal_verify')
    @include('admin.users.partials._modal_edit_user')
    @include('admin.users.partials._modal_logs')
    @include('admin.users.partials._modal_import')
@endsection

@include('admin.users.partials._scripts')
