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
                                    <input type="text" class="form-control name" name="name[]" placeholder=""
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
                                <input type="email" class="form-control email" name="email[]" id="email"
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
        $('.email').change(function() {
            var email = $(this).val();
            var name = $('.name').val();
            $('.test').append(
                `<li class="list-group-item d-flex justify-content-between lh-condensed list-test">
            <div>
            <h6 class="my-0 name_list">${name}</h6>
            <small class="text-muted email_list">${email}</small>
            </div>
            <span class="text-muted">Rp.1.000.000</span>
            </li>`
            );

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
                                <input type="email" class="form-control email" name="email[]"
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
            $('.email').change(function() {
                var email = $(this).parent().find('input[name="email[]"]').val();
                var name = $('.name').val();
                $('.test').append(
                    `<li class="list-group-item d-flex justify-content-between lh-condensed list-test">
            <div>
            <h6 class="my-0 name_list">${name}</h6>
            <small class="text-muted email_list">${email}</small>
            </div>
            <span class="text-muted">Rp.1.000.000</span>
            </li>`
                );
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
        var input = document.querySelector(".phone");
        window.intlTelInput(input, {
            // separateDialCode: true,
            initialCountry: "id",

        });
    </script>


</body>

</html>
