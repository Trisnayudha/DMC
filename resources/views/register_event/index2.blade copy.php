<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="djakarta-miningclub.com">
    <meta name="generator" content="Hugo 0.98.0">
    <title>DMC Register Event</title>
    <meta name="description" content="Register Event " />
    <meta property="og:title" content="Register Event " />
    <meta property="og:url" content="https://djakarta-miningclub.com" />
    <meta property="og:description" content="Register Event" />
    <meta property="og:image" content="{{ asset('image/meta.jpeg') }}" />
    <meta property="og:type" content="register" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:locale:alternate" content="fr_FR" />
    <meta property="og:locale:alternate" content="es_ES" />
    <link rel="canonical" href="https://getbootstrap.com/docs/5.2/examples/checkout/">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
        integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        .myDiv {
            display: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .iti {
            display: grid;
        }

        .swal-modal .swal-text {
            text-align: center;
        }

        .swal-footer {
            text-align: center;
        }
    </style>
    <!-- Custom styles for this template -->
    <link href="{{ asset('new-zoom/form-validation.css') }}" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container">
        <main>
            <div class="py-2 text-center">
                <img style="border-radius: 15px; margin-bottom: 19px; height: 120px; "
                    src="{{ asset('image/dmc.png') }}" class="img-fluid" alt="">
                <h2 style="text-transform: uppercase">REGISTER EVENT
                </h2>
                {{-- <p class="lead"> The 53rd Networking Event - Djakarta Mining Club and Coal Club Indonesia x McCloskey
                    by OPIS </p> --}}
            </div>
            <h6>Detail event</h6>
            <div class="row g-5">
                <div class="col-3">
                    <p>Title</p>
                </div>
                <div class="col-9">
                    <p>: {{ $name }}</p>
                </div>
                <div class="col-3">
                    <p>
                        Date
                    </p>
                    <p>
                        Time
                    </p>
                    <p>Event Type</p>
                    <p>
                        Location
                    </p>
                    <p>
                        Ticket Price
                    </p>
                </div>

                <div class="col-9">
                    <p>
                        : {{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }}
                    </p>
                    <p>:{{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}
                        (Jakarta Time) </p>
                    <p>: Live - Networking Dinner</p>
                    <p>: {{ $location }}</p>
                    <p>:
                        Non Member Rp.1.000.000
                    <p> Member Rp.900.000</p>

                    </p>
                </div>
                <br>

            </div>
            <br>
            <hr class="my-1">
            <form action="{{ url('/payment-personal') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="row g-5">

                    <div class="col-md-8 order-md-1">
                        <h4 class="mb-3">* ATTENDEES</h4>
                        <div class="alert alert-info" role="alert">
                            Enter guest details here. Any special requirements can be noted in the additional
                            information section below.
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full name *</label>
                                    <input type="text" class="form-control" name="name" placeholder=""
                                        value="{{ old('name') }}" required>
                                    <div class="invalid-feedback">
                                        Valid name is required.
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="job_title" class="form-label">Job Title *</label>
                                <input type="text" class="form-control" name="job_title" placeholder="" required
                                    value="{{ old('job_title') }}">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="email" class="form-label">Email Address * <span
                                        class="text-muted"></span></label>
                                <input type="email" class="form-control" name="email[]" id="email"
                                    placeholder="Your work email" required value="{{ old('email') }}">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="phone" class="form-label">Mobile number *</label>
                                <input type="tel" class="phone form-control" name="phone[]" placeholder=""
                                    value="{{ old('phone') ? old('phone') : '+62' }}" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-12 mt-2">

                                <a href="javascript:void(0)" class="addData btn btn-primary float-right"> Add
                                    Guest</a>
                            </div>
                        </div>
                        <div class="customer"></div>
                        {{-- <hr class="my-4">
                        <h4 class="mb-3">* BOOKING CONTACT</h4>
                        <div class="alert alert-warning" role="alert">
                            Enter the best person we can contact for this booking in the event of unplanned changes.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                            <label class="form-check-label" for="flexCheckDefault">
                                The same as the attendees
                            </label>
                        </div>
                        <hr class="my-1"> --}}
                        <!-- {{ csrf_field() }} -->
                        {{-- <div class="row g-3">
                            <div class="col-md-3">
                                <label for="company_name" class="form-label">Company name *</label>
                                <select class="custom-select d-block w-100" id="prefix" name="prefix" required>
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
                                <div class="invalid-feedback">
                                    Please select a valid prefix company name.
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="company_name" class="form-label" style="color: white">. </label>
                                <input type="text" class="form-control" name="company_name"
                                    placeholder="Your company name" value="{{ old('company_name') }}" required>
                                <div class="invalid-feedback">
                                    Valid company name is required.
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="name" class="form-label">Full name *</label>
                                <input type="text" class="form-control" name="name_book" placeholder=""
                                    value="{{ old('name') }}" required>
                                <div class="invalid-feedback">
                                    Valid name is required.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="phone" class="form-label">Mobile number *</label>
                                <input type="tel" class="form-control" name="phone_book"id="phone"
                                    placeholder="" value="{{ old('phone') ? old('phone') : '+62' }}" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="job_title" class="form-label">Job Title *</label>
                                <input type="text" class="form-control" name="job_title_book" placeholder=""
                                    required value="{{ old('job_title') }}">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="email" class="form-label">Email Address * <span
                                        class="text-muted"></span></label>
                                <input type="email" class="form-control" name="email_book" id="email"
                                    placeholder="Your work email" required value="{{ old('email') }}">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="company_website" class="form-label">Company Webstie *<span
                                        class="text-muted"></span></label>
                                <input type="text" class="form-control" name="company_website"
                                    value="{{ old('company_website') }}" placeholder="www.yourcompany.com" required>
                                <div class="invalid-feedback">
                                    Please enter a valid company website .
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <label for="address" class="form-label">Address *</label>
                                <input type="text" class="form-control" name="address" placeholder="" required
                                    value="{{ old('address') }}">
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="office_number" class="form-label">Office Number</label>
                                <input type="tel" class="form-control" name="office_number"id="office_number"
                                    placeholder="" value="{{ old('office_number') ? old('office_number') : '+62' }}"
                                    required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="portal_code" class="form-label">Postal Code</label>
                                <input type="number" class="form-control" name="portal_code" placeholder=""
                                    required value="{{ old('portal_code') }}">
                                <div class="invalid-feedback" {{ old('portal_code') }}>
                                    Please provide a Postal Code
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" name="city" placeholder="" required
                                    value="{{ old('city') }}">
                                <div class="invalid-feedback" {{ old('city') }}>
                                    Please provide a City
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="country" class="form-label">Country * </label>
                                <select class="form-control js-example-basic-single" name="country" id="country"
                                    placeholder="" required>
                                    <option value="Indonesia" selected>Indonesia</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please provide a valid Country
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
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
                                <div class="invalid-feedback">
                                    Please enter your Company Other
                                </div>
                            </div>

                            <div class="col-md-12 mb-6">
                                <div class="myDiv">
                                    <label for="company_other" class="form-label">Company Other *</label>
                                    <input type="text" class="form-control" name="company_other" placeholder="">
                                    <div class="invalid-feedback">
                                        Please enter your Company Other
                                    </div>
                                </div>
                            </div>

                        </div>
                        <hr class="my-4"> --}}
                        {{-- <div class="customer"></div> --}}
                        {{-- <div class="form-check non-member">
                            <input id="debit" name="paymentMethod" type="radio" class="form-check-input"
                                checked required value="nonmember">
                            <label class="form-check-label" for="debit">Non Member (Rp. 1.000.000)</label>
                        </div>
                        <div class="form-check member">
                            <input id="credit" name="paymentMethod" type="radio" class="form-check-input"
                                required value="member">
                            <label class="form-check-label" for="credit">Member (Rp. 900.000)</label>
                        </div> --}}
                    </div>
                    <div class="col-md-4 order-md-2 mb-4">
                        <h4 class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">No of Attendees</span>
                            <span class="badge badge-secondary badge-pill attend">1</span>
                        </h4>
                        <ul class="list-group mb-3 test">



                        </ul>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total (Rp)</span>
                            <strong class="total_price">Rp. 1.000.000</strong>
                        </li>
                        <div class="card p-2">
                            <button class="w-80 btn btn-primary btn-lg" type="submit">Register Event</button>
                        </div>
                    </div>
            </form>
        </main>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; Djakarta Mining Club</p>
            <ul class="list-inline">
                <li class="list-inline-item"><a href="{{ url('/privacy') }}">Privacy</a></li>
                <li class="list-inline-item"><a href="#">Terms</a></li>
            </ul>
        </footer>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        var i = 1;
        $('#email').change(function() {
            // $('input[name^="email"]').last().on('blur', function() {
            //     var isAllFilled = true;
            //     console.log('diluar : ' + isAllFilled)
            //     $('input[name^="email"]').each(function() {
            //         if ($(this).val() == '') {
            //             isAllFilled = false;
            //             console.log('didalem' + isAllFilled);
            //         }
            //     });
            //     if (isAllFilled) {
            var email = $(this).val();
            $('.test').append(
                `<li class="list-group-item d-flex justify-content-between lh-condensed list-test">
                        <div>
                        <h6 class="my-0 name_list">lorem</h6>
                        <small class="text-muted email_list">${email}</small>
                        </div>
                        <span class="text-muted">Rp.1.000.000</span>
                    </li>`
            );
            //     } else {
            //         $('.test .list-test:last-of-type').remove();
            //     }
            // });
        });


        $(".addData").click(function() {
            ++i;
            $(".customer").append(
                `  <div class="row g-3 tambah">
                            <div class="col-sm-6">
                                <label for="name" class="form-label">Full name *</label>
                                <input type="text" class="form-control" name="name[]" placeholder=""
                                    value="{{ old('name') }}" required>
                                <div class="invalid-feedback">
                                    Valid name is required.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="job_title" class="form-label">Job Title *</label>
                                <input type="text" class="form-control" name="job_title[]" placeholder="" required
                                    value="{{ old('job_title') }}">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="email" class="form-label">Email Address * <span
                                        class="text-muted"></span></label>
                                <input type="email" class="form-control" name="email[]"
                                    placeholder="Your work email" required value="{{ old('email') }}">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="phone" class="form-label">Mobile number *</label>
                                <input type="tel" class="phone form-control" name="phone[]" id="phone" placeholder=""
                                    value="+62" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-12 mt-2">
                                <a href="javascript:void(0)" class="remove btn btn-danger float-right">Delete
                                    delegate</a>
                            </div>
                        </div>
                        `
            );

            var input = document.querySelector(".phone");
            window.intlTelInput(input, {
                // separateDialCode: true,
                initialCountry: "id",

            });
            var count = $('.tambah').length + 1;
            console.log(count);
            $('.attend').html(count);
        });
        $(document).on('click', '.remove', function() {
            $(this).parents('.tambah').remove();
            $('.list-test').remove();
            var count = $('.tambah').length + 1;
            console.log(count);
            $('.attend').html(count);
        });
    </script>



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
    </script>
    <script src="{{ asset('new-zoom/form-validation.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        @if (session('alert'))
            swal({
                text: "{{ session('alert') }}",
                icon: "success",
                buttons: false,
                timer: 15000,
            }).then(function() {
                window.location = "https://djakarta-miningclub.com/";
            });
        @elseif (session('error'))
            swal({
                text: "{{ session('error') }}",
                icon: "error",
            });
        @endif
    </script>
    <script>
        var input = document.querySelector(".phone");
        window.intlTelInput(input, {
            // separateDialCode: true,
            initialCountry: "id",

        });
        var input2 = document.querySelector("#office_number");
        window.intlTelInput(input2, {
            // separateDialCode: true,
            initialCountry: "id",

        });

        var input3 = document.querySelector(".phone");
        window.intlTelInput(input3, {
            // separateDialCode: true,
            initialCountry: "id",

        });
    </script>
</body>

</html>
