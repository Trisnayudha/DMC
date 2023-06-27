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
                                <h4>Detail Registration</h4>
                                <div class="card-header-action">
                                    <a data-collapse="#detail-registration" class="btn btn-icon btn-info" href="#"><i
                                            class="fas fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="collapse" id="detail-registration">
                                <div class="card-body" style="background: #f8f9fa">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                            <div class="card card-statistic-1">
                                                <div class="card-icon bg-primary">
                                                    <i class="far fa-user"></i>
                                                </div>
                                                <div class="card-wrap">
                                                    <div class="card-header">
                                                        <h4>Total All Pendaftar</h4>
                                                    </div>
                                                    <div class="card-body">
                                                        10
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                            <div class="card card-statistic-1">
                                                <div class="card-icon bg-info">
                                                    <i class="far fa-user"></i>
                                                </div>
                                                <div class="card-wrap">
                                                    <div class="card-header">
                                                        <h4>Total Pendaftar <b> Sponsor</b></h4>
                                                    </div>
                                                    <div class="card-body">
                                                        10
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
                                                        <h4>Total Pendaftar <b> Berbayar</b></h4>
                                                    </div>
                                                    <div class="card-body">
                                                        10
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                            <div class="card card-statistic-1">
                                                <div class="card-icon bg-warning">
                                                    <i class="far fa-user"></i>
                                                </div>
                                                <div class="card-wrap">
                                                    <div class="card-header">
                                                        <h4>Total Pendaftar <b> Gratis</b></h4>
                                                    </div>
                                                    <div class="card-body">
                                                        10
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4>Pie Chart</h4>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="myChart4"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Tambah Peserta</h4>
                                <div class="card-header-action">
                                    <a data-collapse="#tambah-peserta" class="btn btn-icon btn-info" href="#"><i
                                            class="fas fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="collapse" id="tambah-peserta">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <form action="{{ route('events.add.check') }}" method="post">
                                                @csrf
                                                <input type="hidden" name="event" value="{{ $slug }}">
                                                <div class="form-group">
                                                    <label for="nama">Nama</label>
                                                    <select name="nama" id="nama" class="form-control select2"
                                                        required>
                                                        <option value="">Default</option>
                                                        @foreach ($users as $value)
                                                            <option value="{{ $value->id }}">{{ $value->name }} -
                                                                {{ $value->email }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name">Ticket</label>
                                                    <select name="ticket" id="" class="form-control select2">
                                                        <option value="free">Invitation ( Free No Cost Non Sponsor )
                                                        </option>
                                                        <option value="sponsor">Invitation ( Free No Cost Sponsor)</option>
                                                        <option value="member">Membership ( Rp. 900.000 )</option>
                                                        <option value="nonmember">Non Member ( Rp. 1.000.000 )</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" name="pilihan" class="custom-switch-input"
                                                        checked>
                                                    <span class="custom-switch-indicator"></span>
                                                    <span class="custom-switch-description">Send Notification</span>
                                                </div>
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary add-user">Add
                                                    peserta</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Print Badges ( Without Record Present )</h4>
                                <div class="card-header-action">
                                    <a data-collapse="#print-badges" class="btn btn-icon btn-info" href="#"><i
                                            class="fas fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="collapse" id="print-badges">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="text-input"> Code Access</label>
                                                <input type="text" class="form-control" name="text-input"
                                                    id="text-input">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Table of Registration</h4>
                                <div class="card-header-action">
                                    <div class="dropdown d-inline mr-2">
                                        <button class="btn btn-primary dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            Filter Table
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#">Paid</a>
                                            <a class="dropdown-item" href="#">Sponsor</a>
                                            <a class="dropdown-item" href="#">Free</a>
                                        </div>
                                    </div>
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

                                <div class="float-right ml-3">
                                    <a href="javascript:;"
                                        class="btn btn-block btn-icon icon-left btn-success btn-filter mb-3"
                                        id="modal-2">
                                        <i class="fas fa-plus-circle"></i>
                                        Tambah Peserta
                                    </a>
                                </div>
                                <div class="float-right ml-3">
                                    <a href="{{ Route('events-details-participant', $slug) }}"
                                        class="btn btn-block btn-icon icon-left btn-info btn-filter mb-3">
                                        <i class="fa fa-users"></i>
                                        View Participant Approve
                                    </a>
                                </div>
                                <div class="float-right ml-3">
                                    <a href="{{ url('scan') }}"
                                        class="btn btn-block btn-icon icon-left btn-light btn-filter mb-3">
                                        Scan Present Page
                                    </a>
                                </div>

                                <div class="float-right ml-3 dropdown">
                                    <a href="#" class="dropdown-toggle btn btn-primary" data-toggle="dropdown">Link
                                        Pendaftaran</a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ url($slug . '/register-event') }}" class="dropdown-item">Berbayar</a>
                                        <a href="{{ url($slug . '/register-event/exclusive-invitation') }}"
                                            class="dropdown-item">Exclusive Gratis</a>
                                        <a href="{{ url($slug . '/register-event/sponsor') }}"
                                            class="dropdown-item">Sponsors</a>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Date Register</th>
                                                <th>Code Access</th>
                                                <th>Package</th>
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
                                                    <td>{{ date('d,F H:i', strtotime($post->register)) }}</td>
                                                    <td>{{ $post->code_payment }}</td>
                                                    <td>{{ $post->package }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->email }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->office_number }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-pill {{ $post->status_registration == 'Paid Off' ? 'badge-primary' : 'badge-warning' }}">
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
                                                            @if ($post->status_registration == 'Expired')
                                                                <form action="{{ url('renewal-payment') }}"
                                                                    method="post">
                                                                    <li>
                                                                        @csrf
                                                                        <input type="hidden" name="id"
                                                                            id="id"
                                                                            value="{{ $post->payment_id }}">
                                                                        <button type="submit"
                                                                            class="dropdown-item">Renewal</button>
                                                                    </li>
                                                                </form>
                                                            @endif
                                                            <form action="{{ url('remove-participant') }}"
                                                                method="post">
                                                                <li>
                                                                    @csrf
                                                                    <input type="hidden" name="id" id="id"
                                                                        value="{{ $post->payment_id }}">
                                                                    <button type="submit" class="dropdown-item ">Remove
                                                                        Delegate</button>
                                                                </li>
                                                            </form>
                                                        </ul>
                                                        <a href="#" data-id="{{ $post->id }}"
                                                            class="btn btn-success"><span class=" fa fa-eye"></a>
                                                        <a href="#" data-id="{{ $post->id }}"
                                                            class="btn btn-warning edit-button"> <span
                                                                class="fa fa-edit "></span></a>
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
                                    <label for="country" class="form-label">Country * </label>
                                    <select class="form-control js-example-basic-single" name="country" id="country"
                                        placeholder="" required>
                                        <option value="Indonesia" selected>Indonesia</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please provide a valid Country
                                    </div>
                                </div>

                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="company_name"> Company Name</label>
                                    <input type="text" class="form-control" name="company_name" id="company_name">
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
                                    <input type="text" class="form-control" name="office_number" id="office_number">
                                </div>

                            </div>
                            <div class="col-12">
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
                                <div class="form-group myDiv">
                                    <label for="company_other" class="form-label">Company Other *</label>
                                    <input type="text" class="form-control" name="company_other" placeholder="">
                                    <div class="invalid-feedback">
                                        Please enter your Company Other
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="name">Ticket</label>
                                    <select name="ticket" id="" class="form-control">
                                        <option value="free">Invitation ( Free No Cost Non Sponsor )</option>
                                        <option value="sponsor">Invitation ( Free No Cost Sponsor)</option>
                                        <option value="member">Membership ( Rp. 900.000 )</option>
                                        <option value="nonmember">Non Member ( Rp. 1.000.000 )</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Add peserta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="edit">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit peserta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="package">Package:</label>
                            <select class="form-control select2" id="package_edit">
                                <option value="free">Free</option>
                                <option value="member">Member</option>
                                <option value="sponsor">Sponsor</option>
                                <option value="nonmember">Non Member</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name_edit">
                        </div>
                        <div class="form-group">
                            <label for="job_title">Job Title:</label>
                            <input type="text" class="form-control" id="job_title_edit">
                        </div>
                        <div class="form-group">
                            <label for="company_name">Company:</label>
                            <input type="text" class="form-control" id="company_name_edit">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email_edit">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" class="form-control" id="phone_edit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="loader" style="display:none">
        <div class="loader"></div>
    </div>
@endsection
@push('top')
    <style>
        #loader {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
@push('bottom')
    <script>
        //Untuk Scan
        $("#text-input").on("input", function() {
            if ($(this).val().length >= 7) {
                Swal.fire({
                    title: "Loading",
                    text: "Please wait...",
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(function() {
                    $.ajax({
                        url: "{{ url('scan/request') }}",
                        type: "POST",
                        data: {
                            input_text: $("#text-input").val()
                        },
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            console.log(response)
                            Swal.close();
                            if (response.status == 1) {

                                Swal.fire({
                                    title: "Success Scan",
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                window.open("{{ url('scan/print?name=') }}" + response
                                    .data.name + "&company=" + response.data.company_name +
                                    "&package=" + response.data.package,
                                    "_blank");

                            } else {
                                Swal.fire({
                                    title: "Error Scan",
                                    text: response.message,
                                    type: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                            $("#text-input").val("");
                            // window.location.href = "http://127.0.0.1:8000/scan/success";
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            Swal.fire({
                                title: "Error Scan",
                                text: "Error scanning input text!",
                                type: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    });

                }, 1000);
            }
        });


        $('#modal-2').click(function() {
            $('#example').modal('show');
        });
        $('#edit-button').click(function() {
            $('#edit').modal('show');
        });
        //show data edit
        $(document).ready(function() {
            // Event handler ketika tombol edit di klik
            $(document).on('click', '.edit-button', function() {
                var postId = $(this).data('id'); // Mendapatkan ID pos

                // Mengirim permintaan AJAX ke endpoint yang memuat data pos
                $.ajax({
                    url: '/edit/user/' + postId, // Ganti dengan URL endpoint yang sesuai
                    type: 'GET',
                    success: function(response) {
                        console.log(response.payload.name);
                        // Mengisi nilai form dengan data yang diterima dari server
                        $('#package_edit').val(response.payload.package);
                        $('#name_edit').val(response.payload.name);
                        $('#job_title_edit').val(response.payload.job_title);
                        $('#company_name_edit').val(response.payload.company_name);
                        $('#email_edit').val(response.payload.email);
                        $('#phone_edit').val(response.payload.phone);

                        // Menampilkan modal
                        $('#edit').modal('show');
                    },
                    error: function(xhr) {
                        // Menangani kesalahan jika permintaan gagal
                        console.log(xhr.responseText);
                    }
                });
            });
        });

        $(document).ready(function() {
            //table
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ]
            });

            //validasi
            $(".add-user").click(function(e) {
                // Validasi formulir
                var isValid = true;
                $("form").find(":required").each(function() {
                    if ($(this).is("select")) { // Periksa apakah elemen adalah <select>
                        if ($(this).val() === '' || $(this).val() === null) {
                            isValid = false;
                            return false; // Menghentikan iterasi jika ada input yang kosong
                        }
                    } else {
                        if ($(this).val().trim() === '') {
                            isValid = false;
                            return false; // Menghentikan iterasi jika ada input yang kosong
                        }
                    }
                });
            });
        });
        //Category Other Show
        $(document).ready(function() {
            $('#company_category').on('change', function() {
                var demovalue = $(this).val();
                if (demovalue == 'other') {
                    $('.myDiv').css('display', 'grid');
                } else {
                    $('.myDiv').css('display', 'none');
                }
            });
            $('.js-example-basic-single').select2();
        });


        // Menggunakan jQuery
        const xhttp = new XMLHttpRequest();
        const select = document.getElementById("country");
        const flag = document.getElementById("flag");
        let country;
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                country = JSON.parse(xhttp.responseText);
                assignValues();
                handleCountryChange();
            }
        };
        xhttp.open("GET", "https://restcountries.com/v3.1/all", true);
        xhttp.send();

        function assignValues() {
            country.forEach(country => {
                const option = document.createElement("option");
                option.value = country.name.common; // Menggunakan nama negara sebagai nilai opsi
                option.textContent = country.name.common; // Menggunakan nama negara sebagai teks opsi
                select.appendChild(option);
            });
        }

        function handleCountryChange() {
            const countryData = country.find(
                country => select.value === country.name.common // Membandingkan dengan nama negara
            );
            // flag.style.backgroundImage = `url(${countryData.flags.svg})`; // Menggunakan URL bendera negara
        }
        select.addEventListener("change", handleCountryChange.bind(this));
    </script>
@endpush
