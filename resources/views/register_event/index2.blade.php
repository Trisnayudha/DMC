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



</head>

<body>
    <div class="container">
        <div class="py-2 text-center">
            <img style="border-radius: 15px; margin-bottom: 19px; height: 120px; " src="{{ asset('image/dmc.png') }}"
                class="img-fluid" alt="">
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
                <p>: {{ date('h.i a', strtotime($start_time)) . ' - ' . date('h.i a', strtotime($end_time)) }}
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
        {{-- <form action="{{ url('/payment-personal') }}" method="POST" class="needs-validation" novalidate> --}}

        {{-- @csrf --}}
        <input type="hidden" data-action-type="submit" data-event-type="theatre" id="formType">
        <div class="row g-5">
            <div class="col-md-8 order-md-1">
                <h4 class="mb-3">* ATTENDEES</h4>
                <div class="alert alert-info" role="alert">
                    Enter guest details here. Any special requirements can be noted in the additional
                    information section below.
                </div>
                <div class="form-check non-member">
                    <input id="debit" name="paymentMethod" type="radio" class="form-check-input" checked required
                        value="nonmember">
                    <label class="form-check-label" for="debit">Non Member (Rp. 1.000.000)</label>
                </div>
                <div class="form-check member">
                    <input id="credit" name="paymentMethod" type="radio" class="form-check-input" required
                        value="member">
                    <label class="form-check-label" for="credit">Member (Rp. 900.000)</label>
                </div>

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
                                        <input type="tel" class="phone form-control" name="phone"
                                            placeholder="" value="{{ old('phone') ? old('phone') : '+62' }}" required>
                                        <div class="invalid-feedback">
                                            Please provide a Mobile Number
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="job_title" class="form-label">Job Title *</label>
                                        <input type="text" class="form-control" name="job_title" placeholder=""
                                            required value="">
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
                <h4 class="mb-3">* Booking Contact</h4>
                <div class="alert alert-warning" role="alert">
                    Enter the best person we can contact for this booking in the event of unplanned changes.
                </div>
                <div class="row g-3">
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
                        <input type="text" class="form-control" name="company_name"
                            placeholder="Your company name" value="{{ old('company_name') }}" required>
                        <div class="invalid-feedback">
                            Valid company name is required.
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <label for="name" class="form-label">Full name *</label>
                        <input type="text" class="form-control" name="name_contact" placeholder=""
                            value="{{ old('name') }}" required>
                        <div class="invalid-feedback">
                            Valid name is required.
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="phone" class="form-label">Mobile number *</label>
                        <input type="tel" class="form-control" name="phone_contact"id="phone"
                            placeholder="" value="{{ old('phone') ? old('phone') : '+62' }}" required>
                        <div class="invalid-feedback">
                            Please provide a Mobile Number
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="job_title" class="form-label">Job Title *</label>
                        <input type="text" class="form-control" name="job_title_contact" placeholder="" required
                            value="{{ old('job_title') }}">
                        <div class="invalid-feedback">
                            Please enter your Job Title.
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="email" class="form-label">Email Address * <span
                                class="text-muted"></span></label>
                        <input type="email" class="form-control" name="email_contact" id="email"
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
                    <input type="tel" class="phone form-control" name="phone[]" placeholder="" value=""
                        required>
                    <div class="invalid-feedback">
                        Please provide a Mobile Number
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="job_title" class="form-label">Job Title *</label>
                    <input type="text" class="form-control" name="job_title" placeholder="" required
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
        var capacity = 76;
        var availableSeats = 224;
        var tableSeatLimit = 10;

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


            // var table_limit = $('[data-table-limit]').data('table-limit') - 1;

            // var table_count = 1;
            // var first_attendee = true;

            // If updating hide remove and add buttons etc
            // if (form_type.data('action-type') === 'update') {
            //     $('.add_group_members').addClass('hide');
            //     $('.remove_row').addClass('hide');
            //     $('.organiser_attending_container').addClass('hide');
            //     $('.whole_table_container').addClass('hide');
            //     $('.payment_method').addClass('hide');
            //     $('.totals > dl').addClass('hide');
            //     $('.seated_sep').addClass('hide');
            //     $('#login_form').addClass('hide');
            //     $('.group_rows > .group_row > .form_item').css({ width: '25%' });

            //     // Disable your detail inputs
            //     your_details.find('input, #contact_person').each(function (i, input) {
            //         $(input).attr('disabled', '')
            //         $(input).css({
            //             border: 0
            //         })
            //     });

            //     $('#save').val('Save Changes')
            // }


            // Fix seat limit
            // Tables start with one member added by default,
            // so take 1 from the max.
            var initSeatLimit = function(seat_limit) {
                var original_seat_limit = seat_limit.data('seat-limit'),
                    rows = seat_limit.closest('.group_table').find('.group_row').length,
                    new_seat_limit = original_seat_limit - rows;

                seat_limit.data('seat-limit', new_seat_limit);

                if (new_seat_limit === 0) {
                    seat_limit.addClass('disabled');
                }
            };

            // Initialise Seat Limits
            // $('[data-seat-limit]').each(function (i, seat_limit) {
            //     initSeatLimit($(seat_limit));
            // });

            var generateInputLabels = function() {
                // var inputs = $('input[type="checkbox"]');

                // inputs.each(function (i, input) {
                //     var fieldname = $(input).attr('name'),
                //         input_id = fieldname + '_' + i;

                //     $(input).attr('id', input_id);

                //     if ($(input).parent().siblings('label').length > 0) {
                //         $(input).parent().siblings('label').attr('for', input_id);
                //     }
                // });
            };

            generateInputLabels();

            // Calculates & refreshes price and No. of attendees.
            var calculatePrice = function() {
                // var el = $('#total_price'),
                //     price_per_individual = el.data('individual-price'),
                //     price_per_table = el.data('table-price'),
                //     currency_symbol = el.data('currency-symbol'),
                //     currency_code = el.data('currency-code'),
                //     total_price = 5000,
                //     total_attendees = 0;
                // $('.group_table', group_details).each(function (i, table) {
                //     var whole_table = $('input.whole_table', this),
                //         table_id = i;

                //     if (whole_table.is(':checked')) {
                //         total_price = total_price + price_per_table;
                //     }

                //     $('.group_rows > .group_row', table).each(function (i, row) {
                //         if (!whole_table.is(':checked')) {
                //             total_price = total_price + price_per_individual;
                //         }
                //     });

                // });

                // el.data('total_price', total_price);

                // total_price = accounting.formatMoney(total_price, {
                //     symbol: currency_code + ' ' + currency_symbol,
                // });

                // el.html(total_price);
                $('[data-total-attendees]').html($('.group_rows').children().length);
            };

            // Calculate starting price
            calculatePrice();

            // var initCheckbox = function () {
            //     $('input:checkbox').each(function (i, checkbox) {
            //         if (!$(checkbox).parent().hasClass('styledCheckbox')) {
            //             $(checkbox).screwDefaultButtons({
            //                 image: "url(/img/checkbox.png)",
            //                 width: 22,
            //                 height: 18
            //             });
            //         }
            //     });
            // };

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
                                'price': $('input[name="paymentMethod"]:checked')
                                    .val()

                            }

                            if ($(this).hasClass('organiser')) {
                                member.organiser = true;
                            }
                            members.push(member);
                        }
                        //CHECK PENTING
                        // console.log(members)

                    });
                    //PROSES Array
                    // var table = {

                    //     members
                    // };
                    // tables.push(table);
                    // console.log(tables)
                });

                return members;


                // console.log(tables)
            };


            // --------------------INI PENTING BROW-----------------------
            var appendToContactPersonSelect = function() {
                var count = 0;

                // var select_options = '<option value="">Select a Contact Person</option><option value="other">Other (Please fill out details below)</option>';
                var attendees = '';

                $('#your_group > .group_table').each(function() {
                    $('.group_row', this).each(function() {
                        count++;

                        if ($('input[name="email"]', this).val() != '') {
                            var member = {
                                // 'role': $(this).find('input[name="group_role"]').val(),
                                'email': $(this).find('input[name="email"]').val(),
                                'name': $(this).find('input[name="name"]').val(),
                                // 'special_reqs': $(this).find('input[name="special_reqs"]').val(),
                                // 'seated_sep': $(this).find('input[name="separately"]').is(':checked')
                            }

                            // if ($(this).hasClass('organiser')) {
                            //     member.organiser = true;
                            // }
                            var package = $('input[name="paymentMethod"]:checked').val();
                            var number = 0;
                            if (package == 'nonmember') {

                                number = 1000000;
                            } else {
                                number = 900000
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

                            // select_options += '<option value="' + count + '">' + name_string + '</option>';
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

            // var onContactPersonChange = function (value) {
            //     var contactPerson = $('#your_group > .group_table').find('[data-uid="' + value + '"]'),
            //         $contactPerson = $(contactPerson);

            //     //set details
            //     your_details.find('input[name="organiser_firstname"]').val(contactPerson.find('input[name="email"]').val());
            //     your_details.find('input[name="organiser_lastname"]').val(contactPerson.find('input[name="name"]').val());
            // };

            // Add row
            var addRow = function(this_table, before_or_after, beforeAdd) {
                var new_group_row = $(group_row),
                    group_rows = $('.group_rows', this_table),
                    button = $('.add_group_members', this_table),
                    number_left = button.data('seat-limit'),
                    rows_left = $('.group_row', this_table).length;
                last_row = lastRow(this_table);

                // function executed before adding, to allow for
                // manipulation of variables or the element prior
                // to adding.
                // if (typeof (beforeAdd) === 'function') {
                //     beforeAdd(new_group_row, button_row, button, number_left, rows_left);
                // } else {
                //     $('.seated_sep > input', new_group_row).attr('type', 'checkbox');
                // }

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

                    // Reinitialise checkbox
                    // initCheckbox();

                    // Increment Price
                    calculatePrice();

                    // Refresh input labels
                    // generateInputLabels();
                }

                if (number_left === 0) {
                    button.addClass('disabled');
                };

                if ($('.whole_table', this_table).is(':checked')) {
                    //$('.seated_sep', this_table).addClass('hide');
                    // $('.group_role', this_table).addClass('extended_width');
                }

                // $("#your_group .group_row").find(".seated_sep").removeClass("hide");
                // $("#your_group .group_row").find(".remove_row").removeClass("hide");
                // // $("#your_group .group_row:first").find(".seated_sep").addClass("hide");
                // $("#your_group .group_row:first").find(".remove_row").addClass("hide");
            };

            // var checkFieldsEmpty = function () {
            //     var flag = true;
            //     $("#your_group .group_row:first input[type='text']").each(function () {
            //         if ($(this).val() != "") {
            //             flag = false;
            //         }
            //     });
            //     return flag;
            // }

            // var addRowAndPopulate = function (this_table, insert) {
            //     if ($('.group_row', this_table).length === 1 && checkFieldsEmpty()) {
            //         removeRow($('.group_row', this_table), this_table);
            //     }

            //     if (insert != false) {
            //         addRow(this_table, 'before');
            //     }

            //     var added_row = $('.group_row:first', this_table);

            //     added_row.addClass('organiser');

            //     var first_name = $('input[name="organiser_firstname"]', your_details).val(),
            //         last_name = $('input[name="organiser_lastname"]', your_details).val(),
            //         special_reqs = $('input[name="special_reqs"]', your_details).val(),
            //         company = $('input[name="organiser_role"]', your_details).val();

            //     $('input[name="email"]', added_row).val(first_name);
            //     $('input[name="name"]', added_row).val(last_name);
            //     $('input[name="special_reqs"]', added_row).val(special_reqs);
            //     $('input[name="group_role"]', added_row).val(company);
            // }

            // var addTable = function (callback) {
            //     if (table_limit === 0) {
            //         if (typeof (callback) === 'function') {
            //             return callback();
            //         }
            //     } else {
            //         if (table_count < 2) {
            //             $('#your_group .add_another_table').addClass('hide');

            //             // Insert Table
            //             $(group_table).insertAfter(last_table());

            //             // Add First Row
            //             addRow(last_table());

            //             // Reinit Checkboxes
            //             initCheckbox();

            //             // Decrement Table Limit
            //             table_limit = table_limit - 1;

            //             // If table limit maxed out disable add button
            //             if (table_limit === 0) {
            //                 $('.add_another_table').addClass('disabled').click(function () {
            //                     return false;
            //                 });
            //             }

            //             // Scroll to table
            //             $('body, html').animate({
            //                 scrollTop: $(last_table()).offset().top
            //             }, 500);
            //         }
            //     }
            // }

            var removeRow = function(row_to_remove, this_table) {
                var add_button = $('.add_group_members', this_table),
                    number_left = parseInt(add_button.data('seat-limit'));

                row_to_remove.remove();

                calculatePrice();

                appendToContactPersonSelect();

                add_button.removeClass('disabled').data('seat-limit', number_left + 1);
            }

            // Will display the add table button if any of the whole table buttons are checked,
            // if none are checked, the button will be hidden.
            // var isChecked = function () {
            //     var is_checked = false;

            //     $('.whole_table').each(function (i, checkbox) {
            //         if ($(checkbox).is(':checked')) {
            //             is_checked = true;
            //         }
            //     });

            //     if (is_checked && ($("#your_details").data("num_free_tables") >= 2)) {
            //         $('.group_table:last-child .add_another_table', group_details).removeClass('hide');
            //     } else {
            //         $('.group_table:last-child .add_another_table', group_details).addClass('hide');
            //     }
            // };


            // is also attending
            // $(document).on('click', '.organiser_attending', function () {
            //     if ($('input', this).is(':checked')) {
            //         var free_slot_found = false;

            //         // Add to first table with free slot
            //         $('#your_group .group_table').each(function (i, this_table) {
            //             if ($('[data-seat-limit]', this_table).data('seat-limit') != 0) {
            //                 addRowAndPopulate(this_table);
            //                 free_slot_found = true;
            //                 return false;
            //             }
            //             else {
            //                 free_slot_found = false;
            //             }
            //         });

            //         // If no table has a free slot add a new table,
            //         // then add a new row to that table.
            //         if (!free_slot_found) {
            //             var table_added = addTable(function () {
            //                 $('.errors').html('You have reached the limit of tables & members that can be added.');
            //                 $('html, body').animate({
            //                     scrollTop: $('.errors').offset().top
            //                 }, 2000);

            //                 return false;
            //             });

            //             if (table_added != false) {
            //                 addRowAndPopulate(group_details.find('.group_table:last'), false);
            //             }
            //         }

            //         $("#your_group .group_row").find(".seated_sep").removeClass("hide");
            //         $("#your_group .group_row").find(".remove_row").removeClass("hide");
            //         $("#your_group .group_row:first").find(".seated_sep").addClass("hide");
            //         $("#your_group .group_row:first").find(".remove_row").addClass("hide");
            //     } else {
            //         var row_to_remove = $('.group_row.organiser');
            //         var table_with_row = row_to_remove.closest('.group_table');

            //         $("#your_group .group_row:first input[type='text']").val("");
            //         if ($("#your_group .group_row").length > 1) {
            //             removeRow(row_to_remove, table_with_row);
            //         }

            //         if ($("#your_group .group_row").length == 1) {
            //             first_attendee = true;
            //         }

            //         $("#your_group .group_row").find(".seated_sep").removeClass("hide");
            //         $("#your_group .group_row").find(".remove_row").removeClass("hide");
            //         // $("#your_group .group_row:first").find(".seated_sep").addClass("hide");
            //         $("#your_group .group_row:first").find(".remove_row").addClass("hide");
            //     }
            //     ;
            // });

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

            // Add Table
            // $(document).on('click', '.whole_table', function () {
            //     var this_table = $(this).closest('.group_table');
            //     // Count the number of rows already set
            //     var add_button = $('.add_group_members', this_table),
            //         number_left = parseInt(add_button.data('seat-limit')),
            //         this_table = $(this).closest('.group_table');
            //     var currentSeats = $('.group_rows .group_row').length;


            //     if ($('input', this).is(':checked') == true && (availableSeats < number_left)) {
            //         $(this).trigger('click');
            //         alert('Capacity limit reached. Unable to book whole table.');
            //     } else {
            //         if ($('input', this).is(':checked')) {
            //             for (i = 0; i < number_left; i++) {
            //                 addRow(this_table);
            //             }
            //             $('.remove_row').hide();
            //         }


            //         // TODO remove empty fields
            //         //removeRow(row_to_remove, this_table);
            //         if (!$('input', this).is(':checked')) {
            //             $('.group_row').each(function () {
            //                 if ($('div input[name="email"]', this).val() == '') {
            //                     var this_table = $(this).closest('.group_table');
            //                     removeRow(this, this_table);
            //                 }
            //             });
            //             if ($('.group_row').length == 0) {
            //                 addRow(this_table);
            //             }
            //             $('.remove_row').show();
            //         }


            //         if ($('input', this).is(':checked') && table_limit != 0) {
            //             if (this_table[0] === $(this_table).parent().children().last()[0] && table_count < 2)
            //                 $('.add_another_table', this_table).removeClass('hide');

            //             //$('.seated_sep', this_table).addClass('hide');
            //             //$('.group_role', this_table).addClass('extended_width');
            //         } else {
            //             $('.add_another_table', this_table).addClass('hide');
            //             $('.seated_sep', this_table).removeClass('hide');
            //             $('.group_role', this_table).removeClass('extended_width');
            //         }

            //         calculatePrice();
            //     }
            // });

            // $(document).on('click', '.add_another_table', function () {
            //     addTable();
            //     table_count++;
            // });

            // $(document).on('change', 'select#contact_person', function () {
            //     onContactPersonChange($(this).val());
            // })

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
                // var currentSeats = $('.group_rows .group_row').length;

                // if (currentSeats > availableSeats) {
                //     alert('Capacity limit reached. There is only ' + availableSeats + ' seat/s available. You currently have ' + currentSeats + '. ');
                //     return false;
                // }
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
                    'company_category': $('input[name="company_name"]').val(),
                    'company_other': $('input[name="company_other"]').val(),
                }
                var booking_obj = {
                    // 'name': your_details.find('input[name="name"]').val(),
                    'booking_contact': booking_contact

                };


                //Nyambung ke class atas
                booking_obj.tables = collectTableDatas();

                var json_submit = booking_obj;
                console.log(json_submit)
                // $('.json_input').val(json_submit);

                // var all_good = '1';
                // var scope = $(this).parents('form');

                // Text inputs
                // $(scope).find('input[type=text].required, input[type=password].required').each(function () {
                //     if ($(this).hasClass('dependent')) {
                //         var dependent = $('input[name="' + $(this).data('dependent') + '"]', $(this).closest('.group_row'));

                //         if (dependent.val() != '' && $(this).val() == '') {
                //             all_good = '0';
                //             var prev_placeholder = $(this).attr('placeholder');
                //             $(this).addClass('error').attr('placeholder', 'This field is required');
                //             $(this).on('focus', function () {
                //                 $(this).attr('placeholder', prev_placeholder);
                //             });
                //         } else {
                //             $(this).removeClass('error').attr('placeholder', $(this).data('original-placeholder'));
                //         }
                //     } else if ($(this).val() == '') {
                //         all_good = '0';
                //         var prev_placeholder = $(this).attr('placeholder');
                //         $(this).addClass('error').attr('placeholder', 'This field is required');
                //         $(this).on('focus', function () {
                //             $(this).attr('placeholder', prev_placeholder);
                //         });
                //     } else {
                //         $(this).removeClass('error').attr('placeholder', '');

                //         if ($(this).hasClass('email')) {
                //             // var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
                //             var filter = /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/;
                //             if (!filter.test($(this).val())) {
                //                 all_good = '0';
                //                 var prev_input = $(this).val();
                //                 $(this).val('').addClass('error').attr('placeholder', 'Not a valid email address');
                //                 $(this).on('focus', function () {
                //                     $(this).val(prev_input);
                //                 });
                //             }
                //         }
                //         if ($(this).hasClass('phone')) {
                //             if ($.trim($(this).val()).length < 8) {
                //                 all_good = '0';
                //                 var prev_input = $(this).val();
                //                 $(this).val('').addClass('error').attr('placeholder', 'Not a valid phone number');
                //                 $(this).on('focus', function () {
                //                     $(this).val(prev_input);
                //                 });
                //             }
                //         }

                //     }

                // });

                // $(scope).find('input[type="checkbox"].required').each(function (i, checkbox) {
                //     var id = $(checkbox).attr('id');

                //     if ($(checkbox).is(':checked') === false) {
                //         all_good = '0';
                //         $('label[for="' + id + '"]').addClass('error');
                //     } else {
                //         $('label[for="' + id + '"]').removeClass('error');
                //     }
                // });

                // // Selects
                // $(scope).find('select.required').each(function () {
                //     if (!$(this).val() || $(this).val().trim() == '') {
                //         all_good = '0';
                //         $(this).next('.chosen-container').addClass('error');
                //     } else {
                //         $(this).next('.chosen-container').removeClass('error');
                //     }
                // });

                // // Config Event
                // var url = form_type.data('event-url'),
                //     event_category = form_type.data('event-type'),
                //     event_name = form_type.data('action-type')
                //         + (function () {
                //             if ($('input[name="payment_type"]:checked').val() == 1) {
                //                 return ' paypal'
                //             } else {
                //                 return ' cheque'
                //             }
                //         })();

                // Get redirect url prefix from payment type
                // var redirect = (function () {
                //     var payment_type = $('input[name="payment_type"]:checked').val();

                //     if (payment_type == 1) {
                //         return '/bookings/booking-success/';
                //     } else if (payment_type == 2) {
                //         return '/bookings/booking-success-cheque/';
                //     } else {
                //         return '/bookings/booking-success-default/'
                //     }
                // })();

                // Ajax config
                $.ajax({
                    type: 'POST',
                    url: '{{ url('test') }}',
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}',
                    },
                    data: json_submit,
                    success: function(msg) {
                        console.log(msg)

                    }
                });
                // var ajax_conf = {
                //     type: 'POST',
                //     url: '{{ url('/test') }}',
                //     headers: {
                //         'X-CSRF-Token': '{{ csrf_token() }}',
                //     },
                //     dataType: 'json',
                //     data: {
                //         myData: json_submit
                //     },
                //     // beforeSend: function() {
                //     //     var ajax_loader_modal = $('#ajax_loader_modal');

                //     //     if (form_type.data('action-type') === 'update') {
                //     //         ajax_loader_modal.removeClass('hide');
                //     //         $('.ajax_loading', ajax_loader_modal).html(
                //     //             'Saving your changes, please wait...');
                //     //     } else if ($('input[name="payment_type"]:checked').val() == 1) {
                //     //         ajax_loader_modal.removeClass('hide');
                //     //     } else {
                //     //         $('.ajax_loading', ajax_loader_modal).html(
                //     //             'Saving your booking, please wait...');
                //     //         ajax_loader_modal.removeClass('hide');
                //     //     }
                //     // },
                //     success: function(data) {
                //         console.log(data)
                //         // if (data) {
                //         //     gaLinks.linkHandler($('#save'), event_category, event_name, function() {
                //         //         // if(form_type.data('action-type') === 'submit') {
                //         //         if (data.type == 'paypal') {
                //         //             setTimeout(function() {
                //         //                 location.href = data.url;
                //         //             }, 700);
                //         //         } else {
                //         //             var redirect_to = redirect + data.booking_id + '/' +
                //         //                 data.hash;

                //         //             if (data.price) {
                //         //                 redirect_to = redirect_to + '/' + data.price
                //         //             }
                //         //             setTimeout(function() {
                //         //                 location.href = redirect_to;
                //         //             }, 700);
                //         //         }
                //         //         // } else {
                //         //         //     console.log(data);
                //         //         //     location.reload();
                //         //         // }

                //         //     });
                //         // }
                //     },
                //     error: function(jqXHR, textStatus, error) {
                //         console.log(error)
                //         $('#ajax_loader_modal').addClass('hide');
                //     }
                // }

                // Scroll to first error
                // if ($('.error')[0]) {
                //     $('html, body').animate({
                //         scrollTop: $('.error').first().offset().top - 40
                //     }, 500);
                // } else if (all_good === '1') {
                //     if ($('input[name="payment_type"]:checked').val() == 1) {
                //         // $.ajax(ajax_conf);
                //     } else {
                //         // $.ajax(ajax_conf);
                //     }
                // }
            });

            // $(document).on("focus", ".error", function (e) {
            //     $(this).removeClass('error');
            // });

            // Add previously submitted info to the form
            // var submitted_info_input = $('#submitted_info');
            // if (submitted_info_input.length > 0) {

            // var submitted_info = $.parseJSON(submitted_info_input.val());
            // $('.calendar_controls').find('select').addClass('chosen-select').val(submitted_info.room_id);

            // // Your details section
            // your_details.find('input[name="organiser_id"]').val(submitted_info.organiser.id);
            // your_details.find('input[name="organiser_role"]').val(submitted_info.organiser.role);
            // your_details.find('input[name="organiser_firstname"]').val(submitted_info.organiser.first_name);
            // your_details.find('input[name="organiser_lastname"]').val(submitted_info.organiser.last_name);
            // your_details.find('input[name="organiser_mobile"]').val(submitted_info.organiser.mobile);
            // your_details.find('input[name="organiser_email"]').val(submitted_info.organiser.email);

            // Your group section
            // for (var g = 0; g < submitted_info.group.length; g++) {

            //     var last_group = $('#your_group > .group_row:last');

            //     last_group.find('input[name="group_id"]').val(submitted_info.group[g].id);
            //     last_group.find('input[name="group_role"]').val(submitted_info.group[g].role);
            //     last_group.find('input[name="email"]').val(submitted_info.group[g].first_name);
            //     last_group.find('input[name="name"]').val(submitted_info.group[g].last_name);
            //     last_group.find('input[name="group_mobile"]').val(submitted_info.group[g].mobile);
            //     last_group.find('input[name="group_email"]').val(submitted_info.group[g].email);

            //     if (g != submitted_info.group.length - 1) {
            //         $(group_row).insertAfter(last_group);
            //         group_details.find('select').addClass('chosen-select');
            //         group_details.find('.chosen-select').chosen({ disable_search_threshold: 50 });
            //     }

            // }

            // var add_member_btn = $('.add_group_members');

            // var text_array = add_member_btn.text().split('(');
            // var text_before = text_array[0];
            // var text_after_array = text_array[1].split(' ');
            // var text_after = text_after_array[1];

            // var number_left = parseInt(text_after_array[0]);
            // number_left = number_left - (submitted_info.group.length - 1);

            // add_member_btn.text(text_before + '(' + number_left + ' ' + text_after);
            // if (number_left == 0) add_member_btn.addClass('disabled');

            // $('#selected_room_id').val(submitted_info.room_id);

            // // Calendar
            // var appointment_start = moment(submitted_info.appointment.start, 'YYYY-MM-DD HH:mm:ss');
            // var appointment_end = moment(submitted_info.appointment.end, 'YYYY-MM-DD HH:mm:ss');
            // var weeks_from_now = appointment_start.isoWeek() - moment().isoWeek();

            // $('.calendar_datepicker').val(appointment_start.format('DD/MM/YY'));

            // $('#selected_session_start').val(submitted_info.appointment.start);
            // $('#selected_session_end').val(submitted_info.appointment.end);

            // $('.select_store').addClass('hide');
            // $('.calendar_wrap').removeClass('hide');

            // Calendar('/bookings/calendar-data/' + submitted_info.room_id, true, false, false, weeks_from_now, false, true, false, true);

            // $(document).on("CalendarComplete", function () {

            //     if ($('.error').data('error', 'clashing_booking')[0]) {
            //         $('.day[data-day=' + appointment_start.format('YYMMDD') + ']').find('.booking_block[data-start=' + appointment_start.format('HHmm') + ']').addClass('booking_error');
            //     }

            // });

            // Other details
            // if (submitted_info.other_details.wedding_date != '0000-00-00') {
            //     other_details.find('input[name="wedding_date"]').val(moment(submitted_info.other_details.wedding_date, 'YYYY-MM-DD').format('DD/MM/YY'));
            // }
            // other_details.find('textarea[name="notes"]').val(submitted_info.other_details.notes);

            // }

            // $('.chosen-select').chosen({ disable_search_threshold: 50 });

            // $('.datepicker_input').datepicker({
            //     format: 'dd/mm/yy',
            //     weekStart: 1,
            //     todayHighlight: true,
            //     autoclose: true
            // });

        });

        // function changePaymentDesc(classId) {
        //     $('.payment_method i.payment_method_desc').hide();
        //     $('.payment_method i.payment_method_desc.' + classId).show();
        // }
    </script>
</body>

</html>
