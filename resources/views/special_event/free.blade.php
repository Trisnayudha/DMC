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
                    <p>: The Future Of Sustainable Mining In Indonesia</p>
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
                </div>

                <div class="col-9">
                    <p>
                        : Thursday - 9 March 2023
                    </p>
                    <p>: 10.00 am - 12.00 am (Jakarta Time) </p>
                    <p>: Live - Networking Dinner</p>
                    <p>: Nusantara Ballroom, The Dharmawangsa Hotel Jakarta</p>

                </div>
                <br>

            </div>
            <hr class="my-1">

            <div class="row g-5">

                <div class="col-md-12 col-lg-12">
                    <h4 class="mb-3">* Required information</h4>
                    <form action="{{ url('/regis-special-event') }}" method="POST" class="needs-validation" novalidate>
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
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full name *</label>
                                    <input type="text" class="form-control name" name="name" id="name"
                                        placeholder="" value="" required>
                                    <div class="invalid-feedback">
                                        Valid name is required.
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="email" class="form-label">Email Address * <span
                                        class="text-muted"></span></label>
                                <input type="email" class="form-control email" name="email" id="email"
                                    placeholder="Your work email" required value="">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="phone" class="form-label">Mobile number *</label>
                                <input type="tel" class="phone form-control" name="phone" id="phone"
                                    placeholder="" value="{{ old('phone') ? old('phone') : '+62' }}" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="job_title" class="form-label">Job Title *</label>
                                <input type="text" class="form-control" name="job_title" id="job_title"
                                    placeholder="" required value="">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="company" class="form-label">Company *</label>
                                <input type="text" class="form-control" name="company" id="company"
                                    placeholder="" required value="">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <input type="hidden" name="paymentMethod" value="free">
                        <button class="w-80 btn btn-primary btn-lg" type="submit">Register Event</button>
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
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
        integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.js"
        integrity="sha512-nO7wgHUoWPYGCNriyGzcFwPSF+bPDOR+NvtOYy2wMcWkrnCNPKBcFEkU80XIN14UVja0Gdnff9EmydyLlOL7mQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.slim.js"
        integrity="sha512-M3zrhxXOYQaeBJYLBv7DsKg2BWwSubf6htVyjSkjc9kPqx7Se98+q1oYyBJn2JZXzMaZvUkB8QzKAmeVfzj9ug=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.slim.min.js"
        integrity="sha512-jxwTCbLJmXPnV277CvAjAcWAjURzpephk0f0nO2lwsvcoDMqBdy1rh1jEwWWTabX1+Grdmj9GFAgtN22zrV0KQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
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
        @if (session('success'))
            swal({
                text: "{{ session('success') }}",
                icon: "success",
            });
        @elseif (session('error'))
            swal({
                text: "{{ session('error') }}",
                icon: "error",
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
