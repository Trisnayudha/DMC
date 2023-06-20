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
                                        Import Data</a>
                                    <a href="javascript:;" class="btn btn-block btn-info btn-filter mb-3" id="tambah">
                                        <i class="fas fa-users"></i>
                                        Tambah Data</a>
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
                                                    <td>{{ $post->office_number }}</td>
                                                    <td>
                                                        {{ $post->company_category }}
                                                    </td>
                                                    <td>
                                                        {{ $post->id }}
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
    <div class="modal fade" tabindex="-1" role="dialog" id="addUser">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit peserta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ Route('users.store') }}" method="post">
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

                            </div>
                            <div class="col-12">
                                <div class="myDiv">
                                    <label for="company_other" class="form-label">Company Other *</label>
                                    <input type="text" class="form-control" name="company_other" placeholder="">
                                    <div class="invalid-feedback">
                                        Please enter your Company Other
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="edit">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="prefix">PT</label>
                            <select class="form-control" id="prefix_edit" name="prefix" required>
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
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" id="name_edit">
                        </div>
                        <div class="form-group">
                            <label for="company_website">Company Website</label>
                            <input type="text" class="form-control" name="company_website" id="company_website_edit">
                        </div>
                        <div class="form-group">
                            <label for="job_title">Job Title</label>
                            <input type="text" class="form-control" name="job_title" id="job_title_edit">
                        </div>
                        <div class="form-group">
                            <label for="country" class="form-label">Country *</label>
                            <select class="form-control js-example-basic-single" name="country" id="country_edit"
                                required>
                                <option value="Indonesia" selected>Indonesia</option>
                            </select>
                            <div class="invalid-feedback">
                                Please provide a valid Country
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" class="form-control" name="company_name" id="company_name_edit">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" class="form-control" name="email" id="email_edit">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" class="form-control" name="phone" id="phone_edit">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" name="address" id="address_edit">
                        </div>
                        <div class="form-group">
                            <label for="office_number">Office Number</label>
                            <input type="text" class="form-control" name="office_number" id="office_number_edit">
                        </div>
                        <div class="form-group">
                            <label for="company_category" class="form-label">Company Category *</label>
                            <select class="form-control js-example-basic-single d-block w-100" name="company_category"
                                id="company_category_edit" required>
                                <option value="">--Select--</option>
                                <option value="Coal Mining">Coal Mining</option>
                                <option value="Minerals Producer">Minerals Producer</option>
                                <option value="Supplier/Distributor/Manufacturer">Supplier/Distributor/Manufacturer
                                </option>
                                <option value="Contractor">Contractor</option>
                                <option value="Association / Organization / Government">Association / Organization /
                                    Government</option>
                                <option value="Financial Services">Financial Services</option>
                                <option value="Technology">Technology</option>
                                <option value="Investors">Investors</option>
                                <option value="Logistics and Shipping">Logistics and Shipping</option>
                                <option value="Media">Media</option>
                                <option value="Consultant">Consultant</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="company_other" class="form-label">Company Other *</label>
                            <input type="text" class="form-control" name="company_other" id="company_other_edit">
                            <div class="invalid-feedback">
                                Please enter your Company Other
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{-- <button class="btn btn-primary">Save Changes</button> --}}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            $('#company_category').on('change', function() {
                var demovalue = $(this).val();
                if (demovalue == 'other') {
                    $('.myDiv').css('display', 'grid');
                } else {
                    $('.myDiv').css('display', 'none');
                }
            });
        });
        $(document).ready(function() {
            // Event handler ketika tombol edit di klik
            $(document).on('click', '.edit-button', function() {
                var postId = $(this).data('id'); // Mendapatkan ID pos

                // Mengirim permintaan AJAX ke endpoint yang memuat data pos
                $.ajax({
                    url: '/edit/user/' + postId, // Replace with the appropriate URL endpoint
                    type: 'GET',
                    success: function(response) {
                        console.log(response.payload);

                        // Populate the form fields with the response payload
                        $('#prefix_edit').val(response.payload.package || '');
                        $('#name_edit').val(response.payload.name || '');
                        $('#job_title_edit').val(response.payload.job_title || '');
                        $('#company_name_edit').val(response.payload.company_name || '');
                        $('#email_edit').val(response.payload.email || '');
                        $('#phone_edit').val(response.payload.phone || '');
                        $('#company_website_edit').val(response.payload.company_website || '');
                        $('#address_edit').val(response.payload.address || '');
                        $('#office_number_edit').val(response.payload.office_number || '');
                        $('#company_category_edit').val(response.payload.company_category ||
                            '');
                        $('#company_other_edit').val(response.payload.company_other || '');

                        // Show the edit modal
                        $('#edit').modal('show');
                    },


                    error: function(xhr) {
                        // Menangani kesalahan jika permintaan gagal
                        console.log(xhr.responseText);
                    }
                });
            });
        });
        $('#modal-2').click(function() {
            $('#example').modal('show');
        });
        $('#tambah').click(function() {
            $('#addUser').modal('show');
        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
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
                option.value = country.cioc;
                option.textContent = country.name.common;
                select.appendChild(option);
            });
        }

        function handleCountryChange() {
            const countryData = country.find(
                country => select.value === country.alpha2Code
            );
            // flag.style.backgroundImage = `url(${countryData.flag})`;
        }

        select.addEventListener("change", handleCountryChange.bind(this));
    </script>
@endpush
