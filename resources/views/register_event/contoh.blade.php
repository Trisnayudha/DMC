<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        Cutting Edge Series 7 March 2023 Melbourne Mining
    </title>
    <meta name="rbsiud" content="aa680918c762c4eaca87c127dd78ea6e725e29f0">

    <link rel="icon" href="http://events.melbourneminingclub.com/img/fav_icon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="http://events.melbourneminingclub.com/img/fav_icon.png" type="image/x-icon">

    <script src="http://events.melbourneminingclub.com/js/prefixfree.min.js"></script>
    <script type="text/javascript" src="http://fast.fonts.net/jsapi/84945a85-bd68-4f6f-b457-0e4367e99c02.js"></script>

    <link rel="stylesheet" href="http://events.melbourneminingclub.com/css/chosen2.css" />
    <link rel="stylesheet" href="http://events.melbourneminingclub.com/css/datepicker.css" />
    <link rel="stylesheet" href="http://events.melbourneminingclub.com/css/jquery.ui.min.css" />
    <link rel="stylesheet" href="http://events.melbourneminingclub.com/style.css" />

    <link rel="stylesheet" href="http://events.melbourneminingclub.com/css/booking_page_style.css" />
    <!--[if lte IE 7]>
    <link rel="stylesheet" type="text/css" href="http://events.melbourneminingclub.com/ie7-and-down.css" />
    <![endif]-->

    <script src="http://events.melbourneminingclub.com/js/jquery-1.10.2.min.js"></script>
    <script src="http://events.melbourneminingclub.com/js/jquery.chosen.min.js"></script>
    <script src="http://events.melbourneminingclub.com/js/jquery.ui.min.js"></script>
    <script src="http://events.melbourneminingclub.com/js/jquery.ui.timepicker.js"></script>
    <script src="http://events.melbourneminingclub.com/js/jquery.placeholder.js"></script>
    <script src="http://events.melbourneminingclub.com/js/pusher.min.js"></script>
    <script src="http://events.melbourneminingclub.com/js/moment.min.js"></script>
    <script src="http://events.melbourneminingclub.com/js/jquery.screwdefaultbuttonsV2.min.js"></script>
    <script src="http://events.melbourneminingclub.com/js/accounting.js"></script>
    <script src="http://events.melbourneminingclub.com/js/app.js"></script>

    <!-- Google Analytics-->
    <script type="text/javascript" src="http://events.melbourneminingclub.com/js/galinks.js"></script>

    <!--[if lt IE 9]>
        <script>
            location.href = "/ie"
        </script>
    <![endif]-->
</head>

<body data-base_url="http://events.melbourneminingclub.com" class="
            form theatre newformstyle
    ">

    <div class="super">
        <div class="super_wrap">
            <div class="fixed_width">
                <div class="logo">
                    <a class="replace" href="http://events.melbourneminingclub.com">Melbourne Mining</a>
                </div>

            </div>
        </div>
    </div>

    <div class="container clearfix">

        <!-- <h1>Cutting Edge Series 7 March 2023 - Tuesday 7 March 2023</h1> -->

        <form method="POST" action="http://events.melbourneminingclub.com/bookings/create-booking"
            accept-charset="UTF-8" class="clearfix"><input name="_token" type="hidden"
                value="OpTRm65FH7Jl5zETx7yRAjUd36YOBg5pKEToXlTl">


            <input type="hidden" data-action-type="submit" data-event-type="theatre" data-event-url="url tai"
                id="formType">

            <input type="hidden" id="selected_room_id" value="122" />

            <div class="mid-block clearfix">
                <div class="left-column">
                    <div class="border-wrap">
                        <div class="title orange">
                            <h2>Attendees</h2>
                        </div>

                        <!--dynamic table limit will be needed here for foundation members-->
                        <div class="section" id="your_group" data-table-limit="1000">
                            <div class="ibox orange clearfix">
                                <div class="left"><strong>Guest Details</strong></div>
                                <div class="right">Enter guest details here. Any special requirements can be
                                    noted in the additional information section below.
                                </div>
                            </div>
                            <div class="group_table">
                                <div class="form_row grid items5 vert-align">
                                    <div class="form_item firstname">
                                        First Name
                                    </div>
                                    <div class="form_item lastname">
                                        Last Name
                                    </div>
                                    <div class="form_item group_role">
                                        Company
                                    </div>
                                </div>

                                <a name="guest_details"></a>
                                <div class="group_rows">
                                    <div class="group_row form_row grid items5 vert-align">
                                        <div class="form_item firstname">
                                            <label for="email">
                                                <input type="text" name="email" id="email" class="required"
                                                    data-dependent="name" data-original-placeholder="First name" />
                                            </label>
                                        </div>
                                        <div class="form_item lastname">
                                            <label for="name">
                                                <input type="text" name="name" id="group_last_name"
                                                    class="required" data-dependent="email"
                                                    data-original-placeholder="Last name" />
                                            </label>
                                        </div>
                                        <div class="form_item group_role">
                                            <label for="company">
                                                <input type="text" name="group_role" id="company" />
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="form_row grid vert-align items2 add_buttons">
                                    <div class="form_item">
                                        <a href="javascript:void(0)" class="btn add_group_members" data-seat-limit="10"
                                            alt="Add Guest">Add
                                            Guest</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="right-column">
                    <div class="border-wrap">
                        <div class="booking-summary">
                            <div class="title orange">
                                <h2>Booking Summary</h2>
                            </div>

                            <div class="section">
                                <div class="subtitle">
                                    <h3>No of Attendees <span data-total-attendees="1">1</span></h3>
                                </div>

                                <div id="attendees-list">

                                </div>

                                <div class="subtitle">
                                    <h3>Grand Total <span>Free</span></h3>
                                </div>


                                <div class="clearfix">
                                    <input type="submit" value="Make Booking" class="btn save" id="save" />
                                </div>

                                <br />

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
        <!--html partials-->

        <!-- Blank Group Row -->
        <div class="hide" id="blank_row">
            <div class="group_row form_row grid items5 vert-align">
                <div class="form_item firstname">
                    <input type="text" name="email" placeholder="First name" class="required"
                        data-dependent="name" data-original-placeholder="First name" />
                </div>
                <div class="form_item lastname">
                    <input type="text" name="name" placeholder="Last name" class="required"
                        data-dependent="email" data-original-placeholder="Last name" />
                </div>
                <div class="form_item group_role">
                    <input type="text" name="group_role" placeholder="Company" />
                </div>
                <div class="form_item remove_row"></div>
            </div>
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

                    $('#your_group > .group_table').each(function() {
                        var members = [];

                        $('.group_row', this).each(function() {
                            if ($('input[name="email"]', this).val() != '') {
                                var member = {
                                    'id': $(this).find('input[name="group_id"]').val(),
                                    'role': $(this).find('input[name="group_role"]').val(),
                                    'email': $(this).find('input[name="email"]').val(),
                                    'name': $(this).find('input[name="name"]').val(),
                                    'special_reqs': $(this).find('input[name="special_reqs"]')
                                        .val(),
                                    'seated_sep': $(this).find('input[name="separately"]').is(
                                        ':checked')
                                }

                                if ($(this).hasClass('organiser')) {
                                    member.organiser = true;
                                }
                                members.push(member);
                            }
                            //CHECK PENTING
                            console.log(members)

                        });
                        //PROSES Array
                        var table = {
                            'whole_table': $(this).find('input[name="whole_table"]').is(':checked'),
                            'members': members
                        };
                        tables.push(table);
                        // console.log(tables)
                    });

                    return tables;


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

                                // $(this).attr('data-uid', count);

                                var name_string = member.email + ' - ' + member.name;

                                // select_options += '<option value="' + count + '">' + name_string + '</option>';
                                attendees += '<p><i>' + name_string + '</i><p>'
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
                        // calculatePrice();

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

                    // var currentSeats = $('.group_rows .group_row').length;

                    // if (currentSeats > availableSeats) {
                    //     alert('Capacity limit reached. There is only ' + availableSeats + ' seat/s available. You currently have ' + currentSeats + '. ');
                    //     return false;
                    // }

                    var booking_obj = {
                        'organiser': {
                            'id': your_details.find('input[name="organiser_id"]').val(),
                            'role': your_details.find('input[name="organiser_role"]').val(),
                            'first_name': your_details.find('input[name="organiser_firstname"]').val(),
                            'last_name': your_details.find('input[name="organiser_lastname"]').val(),
                            'mobile': your_details.find('input[name="organiser_mobile"]').val(),
                            'email': your_details.find('input[name="organiser_email"]').val()
                        },
                        'tables': [],
                        'room_id': $('#selected_room_id').val(),
                        'notes': other_details.find('textarea[name="notes"]').val(),
                        'payment_method': $('input[name=payment_type]:checked').val(),
                        'total_price': $('#total_price').data('total_price')
                    };


                    //Nyambung ke class atas
                    booking_obj.tables = collectTableDatas();

                    // var json_submit = JSON.stringify(booking_obj);

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
                    // var ajax_conf = {
                    //     type: 'POST',
                    //     url: 'awe',
                    //     dataType: 'json',
                    //     data: { myData: json_submit },
                    //     beforeSend: function () {
                    //         var ajax_loader_modal = $('#ajax_loader_modal');

                    //         if (form_type.data('action-type') === 'update') {
                    //             ajax_loader_modal.removeClass('hide');
                    //             $('.ajax_loading', ajax_loader_modal).html('Saving your changes, please wait...');
                    //         } else if ($('input[name="payment_type"]:checked').val() == 1) {
                    //             ajax_loader_modal.removeClass('hide');
                    //         } else {
                    //             $('.ajax_loading', ajax_loader_modal).html('Saving your booking, please wait...');
                    //             ajax_loader_modal.removeClass('hide');
                    //         }
                    //     },
                    //     success: function (data) {
                    //         if (data) {
                    //             gaLinks.linkHandler($('#save'), event_category, event_name, function () {
                    //                 // if(form_type.data('action-type') === 'submit') {
                    //                 if (data.type == 'paypal') {
                    //                     setTimeout(function () {
                    //                         location.href = data.url;
                    //                     }, 700);
                    //                 } else {
                    //                     var redirect_to = redirect + data.booking_id + '/' + data.hash;

                    //                     if (data.price) {
                    //                         redirect_to = redirect_to + '/' + data.price
                    //                     }
                    //                     setTimeout(function () {
                    //                         location.href = redirect_to;
                    //                     }, 700);
                    //                 }
                    //                 // } else {
                    //                 //     console.log(data);
                    //                 //     location.reload();
                    //                 // }

                    //             });
                    //         }
                    //     },
                    //     error: function (jqXHR, textStatus, error) {
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

        <div class="clearfix" id="footer">
            <a href="/terms" class="tandc" target="_blank">Terms & Conditions</a>
        </div>
    </div>



    <script>
        /*(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
             (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
             m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
             })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

             ga('create', 'UA-11203218-4', 'metalicus.com');
             ga('send', 'pageview');*/
    </script>
</body>

</html>
