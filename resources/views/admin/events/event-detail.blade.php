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

                                <div class="float-right">
                                    <a href="javascript:;"
                                        class="btn btn-block btn-icon icon-left btn-success btn-filter mb-3" id="modal-2">
                                        <i class="fas fa-plus-circle"></i>
                                        Tambah Peserta
                                    </a>
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
                                                <th>Status Approval</th>
                                                <th>Status Payment</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($payment as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ date('d,F H:i', strtotime($post->created_at)) }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->office_number }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-pill {{ $post->status_registration == 'Approve' ? 'badge-primary' : 'badge-warning' }}">
                                                            {{ $post->status_registration }}</span>
                                                    </td>
                                                    <td>{{ $post->groupby_users_id ? 'Multiple Payment' : 'Single Payment' }}
                                                    </td>
                                                    <td>
                                                        <a href="#" data-toggle="dropdown"
                                                            class="btn btn-info dropdown-toggle">Action</a>
                                                        <ul class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                                            <form action="{{ url('request-event') }}" method="post">
                                                                <li>
                                                                    @csrf
                                                                    <input type="hidden" name="id" id="id"
                                                                        value="{{ $post->payment_id }}">
                                                                    <input type="hidden" name="val" value="approve">
                                                                    <button type="submit"
                                                                        class="dropdown-item">Approve</button>
                                                                </li>
                                                            </form>
                                                            <form action="{{ url('request-event') }}" method="post">
                                                                <li>
                                                                    @csrf
                                                                    <input type="hidden" name="id" id="id"
                                                                        value="{{ $post->payment_id }}">
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
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah peserta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-tab="mygroup-tab" href="#tab-home">Check Database</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-tab="mygroup-tab" href="#tab-profile">Tambah data</a>
                        </li>
                    </ul>
                    <div id="tab-home" class="active" data-tab-group="mygroup-tab">
                        <form action="{{ route('events.add.check') }}" method="post">
                            @csrf
                            <input type="hidden" name="event" value="{{ $slug }}">
                            <div class="form-group">

                                <label for="name">Nama</label>
                                <select name="nama" id="" class="form-control select2">
                                    <option value="">Default</option>
                                    @foreach ($users as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }} - {{ $value->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name">Ticket</label>
                                <select name="ticket" id="" class="form-control">
                                    <option value="free">Invitation ( Free No Cost )</option>
                                    <option value="member">Membership ( Rp. 900.000 )</option>
                                    <option value="nonmember">Non Member ( Rp. 1.000.000 )</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="pilihan" class="custom-switch-input" checked>
                                <span class="custom-switch-indicator"></span>
                                <span class="custom-switch-description">Send Notification</span>
                            </div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button class="btn btn-primary">Add peserta</button>
                        </form>
                    </div>
                    <div id="tab-profile" data-tab-group="mygroup-tab">
                        <form action="{{ Route('events.add.invitation') }}" method="post">
                            @csrf
                            <div class="row">

                                <div class="col-6">
                                    <div class="form-group">

                                        <label for="company_name" class="form-label">PT</label>
                                        <select class="form-control" id="prefix" name="prefix" required>
                                            <option value="PT">PT</option>
                                            <option value="CV">CV</option>
                                            <option value="Ltd">Ltd</option>
                                            <option value="GmbH">GmbH</option>
                                            <option value="Limited">Limited</option>
                                            <option value="Llc">Llc</option>
                                            <option value="Corp">Corp</option>
                                            <option value="Pte Ltd">Pte Ltd</option>
                                            <option value="Assosiation">Assosiation</option>
                                            <option value="Government">Government</option>
                                            <option value="Pty Ltd">Pty Ltd</option>
                                            <option value="">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="name"> Name</label>
                                        <input type="text" class="form-control" name="name" id="name">
                                    </div>
                                    <div class="form-group">
                                        <label for="company_website"> Company Website</label>
                                        <input type="text" class="form-control" name="company_website"
                                            id="company_website">
                                    </div>
                                    <div class="form-group">
                                        <label for="job_title"> Job Title</label>
                                        <input type="text" class="form-control" name="job_title" id="job_title">
                                    </div>
                                    <div class="form-group">
                                        <label for="company_category" class="form-label">Company Category *</label>
                                        <select class="form-control js-example-basic-single d-block w-100"
                                            name="company_category" id="company_category" required>
                                            <option value="">--Select--</option>
                                            <option value="Coal Mining">Coal Mining</option>
                                            <option value="Minerals Producer">Minerals Producer</option>
                                            <option value="Supplier/Distributor/Manufacturer">
                                                Supplier/Distributor/Manufacturer
                                            </option>
                                            <option value="Contrator">Contrator</option>
                                            <option value="Association / Organization / Government">
                                                Association / Organization / Government</option>
                                            <option value="Financial Services">Financial Services</option>
                                            <option value="Technology">Technology</option>
                                            <option value="Investors">Investors</option>
                                            <option value="Logistics and Shipping">Logistics and Shipping</option>
                                            <option value="Media">Media</option>
                                            <option value="Consultant">Consultant</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="company_name"> Company Name</label>
                                        <input type="text" class="form-control" name="company_name"
                                            id="company_name">
                                    </div>

                                    <div class="form-group">
                                        <label for="email"> Email</label>
                                        <input type="text" class="form-control" name="email" id="email">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone"> Phone number</label>
                                        <input type="text" class="form-control" name="phone" id="phone">
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" class="form-control" name="address" id="address">
                                    </div>
                                    <div class="form-group">
                                        <label for="office_number">Office Number</label>
                                        <input type="text" class="form-control" name="office_number"
                                            id="office_number">
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="name">Ticket</label>
                                    <select name="ticket" id="" class="form-control">
                                        <option value="free">Invitation ( Free No Cost )</option>
                                        <option value="member">Membership ( Rp. 900.000 )</option>
                                        <option value="nonmember">Non Member ( Rp. 1.000.000 )</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button class="btn btn-primary">Add peserta</button>
                        </form>
                    </div>
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
