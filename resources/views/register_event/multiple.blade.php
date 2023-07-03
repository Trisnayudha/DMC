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
    <meta property="og:image" content="{{ asset($image) }}" />
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
    <style>
        .hide {
            display: none;
        }

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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="py-2 text-center">
            <img style="border-radius: 15px; margin-bottom: 19px; height: 120px; "
                src="{{ asset('image/logo-dmc-cci3.png') }}" class="img-fluid" alt="">
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
                <p>Dress Code</p>
            </div>

            <div class="col-9">
                <p>
                    : {{ date('l', strtotime($start_date)) . ' - ' . date(' j F Y', strtotime($end_date)) }}
                </p>
                <p>: {{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}
                    (Jakarta Time) </p>
                <p>: Live - Networking Dinner</p>
                <p>: {{ $location }}</p>
                <p>: Black Tie or Batik</p>

            </div>
            <br>

        </div>
        <br>
        <hr class="my-1">
        {{-- <form action="{{ url('/payment-personal') }}" method="POST" class="needs-validation" novalidate> --}}

        {{-- @csrf --}}
        <input type="hidden" data-action-type="submit" data-event-type="theatre" id="formType">
        <div class="row g-5">
            <div class="col-md-8 order-md-1">
                <h4 class="mb-3">* ATTENDEES</h4>
                <div class="alert alert-info" role="alert">
                    Enter Attendees Details Here
                </div>
                <div class="form-check member">
                    <input id="credit" name="paymentMethod" type="radio" class="form-check-input" required
                        value="member">
                    <label class="form-check-label" for="credit">Member (Rp. 900.000)</label>
                </div>
                <div class="form-check non-member">
                    <input id="debit" name="paymentMethod" type="radio" class="form-check-input" checked required
                        value="nonmember">
                    <label class="form-check-label" for="debit">Non Member (Rp. 1.000.000)</label>
                </div>
                <div class="form-check member">
                    <input id="onsite" name="paymentMethod" type="radio" class="form-check-input" required
                        value="onsite">
                    <label class="form-check-label" for="onsite">On Site (Rp. 1.250.000)</label>
                </div>
                {{-- <div class="form-check member">
                    <input id="table" name="paymentMethod" type="radio" class="form-check-input" required
                        value="table">
                    <label class="form-check-label" for="table">Table of 5 People (Rp. 4.000.000)</label>
                </div> --}}

                <hr class="my-4">
                <!--dynamic table limit will be needed here for foundation members-->
                <div id="your_group">
                    <div class="group_table">
                        <div class="group_rows">
                            <div class="group_row form_row ">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Full name *</label>
                                            <input type="text" class="form-control name" name="name"
                                                id="name" placeholder="" value="" required>
                                            <div class="invalid-feedback">
                                                Valid name is required.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="email" class="form-label">Email Address * <span
                                                class="text-muted"></span></label>
                                        <input type="email" class="form-control email" name="email"
                                            id="email" placeholder="Your work email" required value="">
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="phone" class="form-label">Mobile number *</label>
                                        <input type="tel" class="phone form-control" name="phone"
                                            id="phone" placeholder=""
                                            value="{{ old('phone') ? old('phone') : '+62' }}" required>
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

                                    <div class="col-sm-12 mt-2">
                                        <div class="form_row items2 add_buttons">
                                            <div class="form_item">
                                                <a href="javascript:void(0)"
                                                    class="btn btn-primary float-right add_group_members"
                                                    data-seat-limit="10" alt="Add Guest">Add
                                                    Guest</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="slug" id="slug" value="{{ $slug }}">
                <h4 class="mb-3">* Booking Contact</h4>
                <div class="alert alert-warning" role="alert">
                    Enter the best person we can contact for this booking in the event of unplanned changes.
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkbox_data">
                            <label class="form-check-label" for="checkbox_data">
                                Same as contact person
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="company_name" class="form-label" style="color:white">.</label>
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
                        <input type="text" class="form-control" name="company_name" id="company_name"
                            placeholder="Your company name" value="{{ old('company_name') }}" required>
                        <div class="invalid-feedback">
                            Valid company name is required.
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <label for="name" class="form-label">Full name *</label>
                        <input type="text" class="form-control" name="name_contact" id="name_contact"
                            placeholder="" value="{{ old('name') }}" required>
                        <div class="invalid-feedback">
                            Valid name is required.
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="phone" class="form-label">Mobile number *</label>
                        <input type="tel" class="form-control" name="phone_contact" id="phone_contact"
                            placeholder="" value="{{ old('phone') ? old('phone') : '+62' }}" required>
                        <div class="invalid-feedback">
                            Please provide a Mobile Number
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="job_title" class="form-label">Job Title *</label>
                        <input type="text" class="form-control" name="job_title_contact" id="job_title_contact"
                            placeholder="" required value="{{ old('job_title') }}">
                        <div class="invalid-feedback">
                            Please enter your Job Title.
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="email" class="form-label">Email Address * <span
                                class="text-muted"></span></label>
                        <input type="email" class="form-control" name="email_contact" id="email_contact"
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
                        <input type="number" class="form-control" name="portal_code" placeholder="" required
                            value="{{ old('portal_code') }}">
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
                        <select class="form-control js-example-basic-single d-block w-100" name="company_category"
                            id="company_category" required>
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
            </div>
            <div class="col-md-4 order-md-2 mb-4">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">No of Attendees</span>
                    <span class="badge badge-secondary badge-pill attend" data-total-attendees="1">1</span>
                </h4>
                <ul class="list-group mb-3" id="attendees-list">

                </ul>

                <li class="list-group-item d-flex justify-content-between">
                    <span>Total (Rp)</span>
                    <strong class="total_price">Rp. 0</strong>
                </li>
                <div class="card p-2">
                    <button class="w-80 btn btn-primary btn-lg" id="save" type="submit">Register
                        Event</button>
                </div>
            </div>

        </div>

        {{-- </form> --}}
    </div>

    <!-- Blank Group Row -->
    <div class="hide" id="blank_row">
        <div class="group_row form_row">
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Full name *</label>
                        <input type="text" class="form-control name" name="name" placeholder=""
                            value="" required>
                        <div class="invalid-feedback">
                            Valid name is required.
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="email" class="form-label">Email Address * <span class="text-muted"></span></label>
                    <input type="email" class="form-control email" name="email" id="email"
                        placeholder="Your work email" required value="">
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="phone" class="form-label">Mobile number *</label>
                    <input type="tel" class="phone form-control" name="phone" placeholder="" value=""
                        required>
                    <div class="invalid-feedback">
                        Please provide a Mobile Number
                    </div>
                </div>
                <div class="col-sm-3">
                    <label for="job_title" class="form-label">Job Title *</label>
                    <input type="text" class="form-control" name="job_title" placeholder="" required
                        value="">
                    <div class="invalid-feedback">
                        Please enter your Job Title.
                    </div>
                </div>
                <div class="col-sm-3">
                    <label for="company" class="form-label">Company *</label>
                    <input type="text" class="form-control" name="company" placeholder="" required
                        value="">
                    <div class="invalid-feedback">
                        Please enter your Job Title.
                    </div>
                </div>
                <div class="col-sm-12 mt-2">
                    <div class="btn btn-danger float-right form_item remove_row"> Remove</div>
                </div>
            </div>
        </div>
    </div>



    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">&copy; Djakarta Mining Club</p>
        <ul class="list-inline">
            <li class="list-inline-item"><a href="{{ url('/privacy') }}">Privacy</a></li>
            <li class="list-inline-item"><a href="#">Terms</a></li>
        </ul>
    </footer>
    <div id="loader" style="display:none">
        <div class="loader"></div>
    </div>
    <script type="text/javascript">
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
            flag.style.backgroundImage = `url(${countryData.flags.svg})`; // Menggunakan URL bendera negara
        }

        select.addEventListener("change", handleCountryChange.bind(this));
        var availableSeats = 224;
        $(document).ready(function() {
            // atur event listener untuk checkbox
            $('#checkbox_data').change(function() {
                if ($(this).is(':checked')) {
                    console.log('check')
                    // isi data ke dalam elemen lain
                    $('#name_contact').val($('#name').val());
                    $('#email_contact').val($('#email').val());
                    $('#phone_contact').val($('#phone').val());
                    $('#job_title_contact').val($('#job_title').val());
                    $('#company_name').val($('#company').val());
                } else {
                    // kosongkan elemen lain jika checkbox tidak di ceklis
                    $('#name_contact').val('');
                    $('#email_contact').val('');
                    $('#phone_contact').val('');
                    $('#job_title_contact').val('');
                    $('#company_name').val('');
                }
            });
        });
        $(function() {
            /*----FORM-----*/

            // Form meta details
            var form_type = $('#formType'); //PENTING

            // Sections
            var your_details = $('#your_details');
            var group_details = $('#your_group'); // PENTING
            var other_details = $('#other_details');
            // var price_per = $('[data-total-price]').data('total-price');
            var contact_person = $('#contact_person');
            var attendees_list = $('#attendees-list'); //PENTING

            var group_row = $('#blank_row').html(); //PENTING
            // var group_table = $('#blank_table').html();
            // var last_table = (function () {
            //     return group_details.find('> .group_table:last')
            // });

            //PENTING
            var lastRow = (function(table) {
                return $('.group_row', table).last()
            })

            var initSeatLimit = function(seat_limit) {
                var original_seat_limit = seat_limit.data('seat-limit'),
                    rows = seat_limit.closest('.group_table').find('.group_row').length,
                    new_seat_limit = original_seat_limit - rows;

                seat_limit.data('seat-limit', new_seat_limit);

                if (new_seat_limit === 0) {
                    seat_limit.addClass('disabled');
                }
            };

            var generateInputLabels = function() {

            };

            generateInputLabels();

            // Calculates & refreshes price and No. of attendees.
            var calculatePrice = function() {

                $('[data-total-attendees]').html($('.group_rows').children().length);
            };

            // Calculate starting price
            calculatePrice();

            var collectTableDatas = function() {
                var tables = [];

                var members = [];
                $('#your_group > .group_table').each(function() {
                    $('.group_row', this).each(function() {
                        if ($('input[name="email"]', this).val() != '') {
                            var member = {
                                'email': $(this).find('input[name="email"]').val(),
                                'phone': $(this).find('input[name="phone"]').val(),
                                'job_title': $(this).find('input[name="job_title"]').val(),
                                'name': $(this).find('input[name="name"]').val(),
                                'price': $('input[name="paymentMethod"]:checked').val(),
                                'company': $(this).find('input[name="company"]').val(),
                                'events_id': '{{ $id }}'
                            }
                            if ($(this).hasClass('organiser')) {
                                member.organiser = true;
                            }
                            members.push(member);
                        }
                    });
                });

                var objectArray = [];

                members.forEach(function(member) {
                    objectArray.push(member);
                });

                return objectArray;
            };

            // --------------------INI PENTING BROW-----------------------
            var appendToContactPersonSelect = function() {
                var count = 0;

                var attendees = '';

                $('#your_group > .group_table').each(function() {
                    $('.group_row', this).each(function() {
                        count++;

                        if ($('input[name="email"]', this).val() != '') {
                            var member = {
                                'email': $(this).find('input[name="email"]').val(),
                                'name': $(this).find('input[name="name"]').val(),

                            }
                            var package = $('input[name="paymentMethod"]:checked').val();
                            console.log(package);
                            var number = 0;
                            if (package == 'nonmember') {

                                number = 1000000;
                            } else if (package == 'onsite') {
                                number = 1250000;
                            } else if (package == 'table') {
                                number = 4000000;
                            } else {
                                number = 900000;
                            }
                            const fix = number.toLocaleString('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            });
                            $(this).attr('data-uid', count);
                            var html = `<li class="list-group-item d-flex justify-content-between lh-condensed list-test">
                                <div>
                                <h6 class="my-0 name_list">${member.name}</h6>
                                <small class="text-muted email_list">${member.email}</small>
                                </div>
                                <span class="text-muted">${fix}</span>
                                </li>`
                            var name_string = html;
                            attendees += name_string
                            const total_price = number * count;
                            const formattedRp = total_price.toLocaleString('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            });
                            $('.total_price').html(formattedRp)
                        }
                    });
                });

                attendees_list.html(attendees);
                // contact_person.html(select_options);
            };

            // Add row
            var addRow = function(this_table, before_or_after, beforeAdd) {
                var new_group_row = $(group_row),
                    group_rows = $('.group_rows', this_table),
                    button = $('.add_group_members', this_table),
                    number_left = button.data('seat-limit'),
                    rows_left = $('.group_row', this_table).length;
                last_row = lastRow(this_table);


                if (number_left > 0) {

                    // No limit for theatre bookings
                    if (form_type.data('event-type') === 'luncheon') {
                        number_left = number_left - 1;
                        button.data('seat-limit', number_left);
                    }

                    if (rows_left > 0) {
                        if (before_or_after === 'before') {
                            $(group_rows).prepend(new_group_row);
                        } else {
                            $(group_rows).append(new_group_row);
                        }
                    } else {
                        $(group_rows).append(new_group_row);
                    }
                    calculatePrice();
                }

                if (number_left === 0) {
                    button.addClass('disabled');
                };

                if ($('.whole_table', this_table).is(':checked')) {

                }
            };

            var removeRow = function(row_to_remove, this_table) {
                var add_button = $('.add_group_members', this_table),
                    number_left = parseInt(add_button.data('seat-limit'));

                row_to_remove.remove();

                calculatePrice();

                appendToContactPersonSelect();

                add_button.removeClass('disabled').data('seat-limit', number_left + 1);
            }

            // Add group members
            $(document).on('click', '.add_group_members', function() {
                var currentSeats = $('.group_rows .group_row').length;

                if (currentSeats >= availableSeats) {
                    alert('Capacity limit reached. No more seats available.');
                } else {
                    first_attendee = false;
                    var this_table = $(this).closest('.group_table');
                    // Type checkbox is initially disabled, to prevent copying of an already
                    // initialised checkbox within the row, we enable it now to initialise it.
                    if (form_type.data('action-type') === 'submit')
                        addRow(this_table);
                }
            });

            // Remove group members
            $(document).on("click", ".remove_row", function(e) {
                var this_table = $(this).closest('.group_table'),
                    row_to_remove = $(this).parents('.group_row');

                if (form_type.data('action-type') === 'submit')
                    removeRow(row_to_remove, this_table);
            });

            $(document).on('blur', 'input[name="email"]', function() {
                appendToContactPersonSelect();
            });

            $(document).on('blur', 'input[name="name"]', function() {
                appendToContactPersonSelect();
            });

            // ------------------ Form Submission ----------------- //

            $('#save').click(function(e) {
                e.preventDefault();
                $("#loader").show();

                var booking_contact = [];
                booking_contact = {
                    'prefix': $('select[name="prefix"]').val(),
                    'company_name': $('input[name="company_name"]').val(),
                    'name_contact': $('input[name="name_contact"]').val(),
                    'phone_contact': $('input[name="phone_contact"]').val(),
                    'job_title_contact': $('input[name="job_title_contact"]').val(),
                    'email_contact': $('input[name="email_contact"]').val(),
                    'company_website': $('input[name="company_website"]').val(),
                    'address': $('input[name="address"]').val(),
                    'office_number': $('input[name="office_number"]').val(),
                    'portal_code': $('input[name="portal_code"]').val(),
                    'city': $('input[name="city"]').val(),
                    'country': $('select[name="country"]').val(),
                    'company_category': $('select[name="company_category"]').val(),
                    'company_other': $('input[name="company_other"]').val(),
                    'slug': $('input[name="slug"]').val()
                }
                var booking_obj = {
                    // 'name': your_details.find('input[name="name"]').val(),
                    'booking_contact': booking_contact,
                    'tables': collectTableDatas()

                };
                //Nyambung ke class atas
                // booking_obj.tables = collectTableDatas();
                var json_submit = booking_obj;

                // Ajax config
                $.ajax({
                    type: 'Post',
                    url: '{{ url('payment-multiple') }}',
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}',
                    },
                    data: json_submit,
                    success: function(msg) {
                        $("#loader").hide();
                        if (msg.status === 1) {
                            swal({
                                text: msg.message,
                                icon: "success",
                                buttons: false,
                                timer: 250000,
                            }).then(function() {
                                window.location =
                                    "https://djakarta-miningclub.com/";
                            });
                        } else {
                            swal({
                                text: msg.message,
                                icon: "error",
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
