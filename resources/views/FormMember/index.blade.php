<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="djakarta-miningclub.com">
    <meta name="generator" content="Hugo 0.98.0">
    <title>DMC Register Membership</title>
    <meta name="description" content="Register Membership " />
    <meta property="og:title" content="Register Membership " />
    <meta property="og:url" content="" />
    <meta property="og:description" content="Register Membership" />
    <meta property="og:image" content="" />
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
    </style>
    <!-- Custom styles for this template -->
    <link href="{{ asset('new-zoom/form-validation.css') }}" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container">
        <main>
            <div class="py-2 text-center">
                <img style="border: none; width:400px" src="{{ asset('image/dmc.png') }}" alt="">
                <h2 style="text-transform: uppercase">Membership</h2>
                <p class="lead"> Fill in the form below to join our FREE membership, As a Member of Djakarta Mining
                    Club (DMC) you will be updated with the latest information of DMC in person events & Virtual, also
                    updates news from the industry. Djakarta Mining Club committed to provide networking opportunities
                    to the Indonesian mining industry.</p>
            </div>
            <hr class="my-1">

            <div class="row g-5">

                <div class="col-md-12 col-lg-12">
                    <h4 class="mb-3">* Required information</h4>
                    <form action="{{ url('/membership') }}" method="POST" class="needs-validation" novalidate>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @csrf
                        <!-- {{ csrf_field() }} -->
                        <div class="row g-3">
                            <div class="col-md-2 mb-1">
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
                            <div class="col-sm-4">
                                <label for="company_name" class="form-label" style="color: white">. </label>
                                <input type="text" class="form-control" name="company_name"
                                    placeholder="Your company name" value="{{ old('company_name') }}" required>
                                <div class="invalid-feedback">
                                    Valid company name is required.
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label for="name" class="form-label">Full name *</label>
                                <input type="text" class="form-control" name="name" placeholder=""
                                    value="{{ old('name') }}" required>
                                <div class="invalid-feedback">
                                    Valid name is required.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="phone" class="form-label">Mobile number *</label>
                                <input type="tel" class="form-control" name="phone"id="phone" placeholder=""
                                    value="+62" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
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
                                <input type="email" class="form-control" name="email"
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
                                <input type="text" class="form-control" name="address" placeholder="" required>
                                <div class="invalid-feedback" {{ old('address') }}>
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="office_number" class="form-label">Office Number</label>
                                <input type="tel" class="form-control" name="office_number"id="office_number"
                                    placeholder="" value="+62" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="portal_code" class="form-label">Postal Code</label>
                                <input type="number" class="form-control" name="portal_code" placeholder=""
                                    required>
                                <div class="invalid-feedback" {{ old('portal_code') }}>
                                    Please provide a Postal Code
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" name="city" placeholder="" required>
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
                        <hr class="my-4">

                        <div class="my-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cci" name="cci"
                                    value="true">
                                <label class="form-check-label" for="cci">I am also willing to be
                                    registered as a member of Coal Club Indonesia</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="explore" name="explore"
                                    value="true">
                                <label class="form-check-label" for="explore">I am also willing explore
                                    marketing opportunities for my company through this mining club</label>
                            </div>
                            <div class="invalid-feedback">
                                Field is Required
                            </div>
                        </div>

                        <hr class="my-4">

                        <p>By completing this registration form, your account on Djakarta Mining Club Platform will be
                            automatically created.</p>
                        <button class="w-80 btn btn-primary btn-lg" type="submit">Register</button>
                    </form>
                </div>
            </div>
        </main>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; Djakarta Mining Club</p>
            <ul class="list-inline">
                <li class="list-inline-item"><a href="{{ url('/privacy') }}">Privacy</a></li>
                <li class="list-inline-item"><a href="#">Terms</a></li>
            </ul>
        </footer>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
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
        @endif

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
        xhttp.open("GET", "https://restcountries.com/v3.1/all?fields=name", true);
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
            flag.style.backgroundImage = `url(${countryData.flag})`;
        }

        select.addEventListener("change", handleCountryChange.bind(this));
    </script>


    <script>
        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
            // separateDialCode: true,
            initialCountry: "id",

        });
        var input2 = document.querySelector("#office_number");
        window.intlTelInput(input2, {
            // separateDialCode: true,
            initialCountry: "id",

        });
    </script>
</body>

</html>
