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
    // Fungsi untuk memeriksa apakah semua input yang diperlukan telah diisi
    function updateNextButton() {
        const nextButtons = $('.nextButton');
        const currentStepFieldset = $(`fieldset:nth-child(${currentStep})`);

        // Periksa apakah pengguna berada di dalam fieldset Step 2
        if (currentStepFieldset.attr('id') === 'step2') {
            console.log('step2 ini');

            // Buat fungsi pembaruan tombol "Next"
            function checkInputs() {
                const nameInput = $('input[name="name"]').val().trim();
                const emailInput = $('input[name="email"]').val().trim();
                const phoneInput = $('input[name="phone"]').val().trim();
                const jobTitleInput = $('input[name="job_title"]').val().trim();
                const companyInput = $('input[name="company"]').val().trim();

                if (nameInput !== '' && emailInput !== '' && phoneInput !== '' && jobTitleInput !== '' &&
                    companyInput !==
                    '') {
                    nextButtons.prop('disabled', false); // Aktifkan tombol "Next" jika semua input telah diisi
                    console.log('false');
                } else {
                    nextButtons.prop('disabled', true); // Nonaktifkan tombol "Next" jika ada yang belum terisi
                    console.log('true');
                }
            }

            // Tambahkan event listener untuk input
            $('input[name="name"], input[name="email"], input[name="phone"], input[name="job_title"], input[name="company"]')
                .on('input', checkInputs);

            // Panggil checkInputs() saat halaman dimuat untuk memeriksa input awal
            checkInputs();
        } else {
            // Jika pengguna tidak berada di dalam fieldset Step 2, nonaktifkan validasi
            nextButtons.prop('disabled', false);
        }
    }



    // Tambahkan event listener ke setiap input yang relevan
    const nextButtons = $('.nextButton');
    const agreeCheckbox = $('#agree_terms');

    // Tambahkan event listener ke checkbox "agree_terms"
    agreeCheckbox.on('change', function() {
        const isChecked = agreeCheckbox.is(':checked');
        nextButtons.prop('disabled', !isChecked); // Disable the button if checkbox is not checked
    });


    let currentStep = 1;

    function nextStep(step) {
        // Hide the current step
        $(`fieldset:nth-child(${step})`).css('display', 'none');

        // Show the next step
        step++;
        $(`fieldset:nth-child(${step})`).css('display', 'block');
        currentStep = step;

        // Panggil fungsi validasi setiap kali pindah ke step baru
        updateNextButton();
    }

    function prevStep(step) {
        // Hide the current step
        $(`fieldset:nth-child(${step})`).css('display', 'none');

        // Show the previous step
        step--;
        $(`fieldset:nth-child(${step})`).css('display', 'block');
        currentStep = step;

        // Panggil fungsi validasi setiap kali pindah ke step baru
        updateNextButton();
    }
</script>

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
    // const xhttp = new XMLHttpRequest();
    // const select = document.getElementById("country");
    // const flag = document.getElementById("flag");

    // let country;

    // xhttp.onreadystatechange = function() {
    //     if (this.readyState == 4 && this.status == 200) {
    //         country = JSON.parse(xhttp.responseText);
    //         assignValues();
    //         handleCountryChange();
    //     }
    // };
    // xhttp.open("GET", "https://restcountries.com/v3.1/all", true);
    // xhttp.send();

    // function assignValues() {
    //     country.forEach(country => {
    //         const option = document.createElement("option");
    //         option.value = country.name.common; // Menggunakan nama negara sebagai nilai opsi
    //         option.textContent = country.name.common; // Menggunakan nama negara sebagai teks opsi
    //         select.appendChild(option);
    //     });
    // }

    // function handleCountryChange() {
    //     const countryData = country.find(
    //         country => select.value === country.name.common // Membandingkan dengan nama negara
    //     );
    //     flag.style.backgroundImage = `url(${countryData.flags.svg})`; // Menggunakan URL bendera negara
    // }

    // select.addEventListener("change", handleCountryChange.bind(this));
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
                $('#prefix').val($('#prefix').val());
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
        var list_summary = $('#list_summary'); //PENTING
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
                            'prefix': $('#prefix').val(),
                            'events_id': ''
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
            var listSummarys = '';
            $('#your_group > .group_table').each(function() {
                $('.group_row', this).each(function() {
                    count++;

                    if ($('input[name="email"]', this).val() != '') {
                        var member = {
                            'email': $(this).find('input[name="email"]').val(),
                            'name': $(this).find('input[name="name"]').val(),
                        }

                        var package = $('input[name="paymentMethod"]:checked').val();

                        $.ajax({
                            type: 'POST',
                            url: '{{ url('checkMember') }}/' + member.email,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(response) {
                                console.log(response);

                                var number = 0;

                                if (package == 'onsite') {
                                    number = 1250000;
                                } else if (package == 'table') {
                                    number = 4000000;
                                } else if (response.status == 1) {
                                    number = 900000;
                                } else if (response.status == 0) {
                                    number = 1000000;
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
                                attendees += name_string;

                                var listSummary = `
                                <tr>
                            <td style="text-align: left;">

                                <i> <small>${member.email}</small></i>

                                <h6>${member.name}</h6>
                            </td>
                            <td style="text-align: right;">
                                ${fix}
                            </td>
                            </tr>
                        `
                                var list_string = listSummary;
                                listSummarys += list_string;
                                const total_price = number * count;
                                const formattedRp = total_price.toLocaleString(
                                    'id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    });

                                $('.total_price').html(formattedRp);
                                $('.sub_total').html(formattedRp);
                                list_summary.html(listSummarys);
                                attendees_list.html(attendees);
                            },
                            error: function(error) {
                                console.error('Error checking member status:',
                                    error);
                            }
                        });
                    }
                });
            });

        };


        // Add row
        var addRow = function(this_table, before_or_after, beforeAdd) {
            var new_group_row = $(group_row),
                group_rows = $('.group_rows', this_table),
                button = $('.add_group_members', this_table),
                number_left = button.data('seat-limit'),
                rows_left = $('.group_row', this_table).length;
            last_row = lastRow(this_table);
            // var nextButtons = $('.nextButton');


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
                // nextButton.disabled = true; // Nonaktifkan tombol "Next" jika ada yang belum terpenuhi
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
                'prefix_contact': $('select[name="prefix_contact"]').val(),
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
            console.log(json_submit);
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
