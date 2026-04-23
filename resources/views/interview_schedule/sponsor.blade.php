<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Indonesia Miner 2026 - Interview Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --dmc-red: #c8102e;
            --dmc-red-dark: #93081f;
            --dmc-border: #e5e7eb;
            --dmc-bg: #f4f5f7;
            --dmc-text: #111827;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dmc-bg);
            color: var(--dmc-text);
        }

        .page-wrapper {
            padding: 26px 0 40px;
        }

        .page-inner {
            max-width: 980px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .page-header img {
            max-width: 200px;
        }

        .page-title {
            font-size: 1.45rem;
            font-weight: 700;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .page-title span {
            color: var(--dmc-red-dark);
        }

        .card-shell {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        }

        .form-card {
            padding: 20px 24px 26px;
            border-top: 3px solid var(--dmc-red);
            border-radius: 18px;
        }

        .form-section {
            border: 1px solid var(--dmc-border);
            border-radius: 12px;
            padding: 14px 16px 10px;
            margin-bottom: 14px;
        }

        .form-section-title {
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        label {
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        label small {
            color: #ef4444;
        }

        .form-control {
            font-size: .88rem;
            border-radius: 8px;
            border-color: var(--dmc-border);
        }

        .form-control:focus {
            border-color: var(--dmc-red);
            box-shadow: 0 0 0 .15rem rgba(200, 16, 46, .16);
        }

        .btn-apply {
            border-radius: 999px;
            padding: .55rem 1.6rem;
            font-weight: 600;
            background: var(--dmc-red);
            border-color: var(--dmc-red);
        }

        .btn-apply:hover {
            background: var(--dmc-red-dark);
        }

        .question-item {
            border: 1px solid #edf0f3;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 8px;
        }

        .question-item.required {
            border-color: #f7c7cf;
            background: #fff4f6;
        }

        .question-no {
            font-size: .78rem;
            color: #6b7280;
            margin-right: 6px;
        }

        .section-subtitle {
            font-size: .8rem;
            color: #6b7280;
            margin-top: -6px;
            margin-bottom: 10px;
        }

        .interviewee-item {
            border: 1px dashed #d7dbe0;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .guideline-list,
        .script-text {
            font-size: .86rem;
            line-height: 1.55;
            color: #374151;
            padding-left: 18px;
            margin-bottom: 0;
        }

        .script-text {
            padding-left: 0;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="page-inner">
            <div class="page-header">
                <img src="{{ asset('image/dmc.png') }}">
                <h1 class="page-title">Interview <span>Schedule</span></h1>
                <div class="text-muted">Indonesia Miner 2026</div>
            </div>

            <div class="card-shell">
                <div class="form-card">
                    <div class="mb-3">
                        <h5 class="mb-1">Please fill in the details below to schedule your interview session.</h5>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('sponsor.interview-schedule.store') }}" method="POST" id="interview-form">
                        @csrf
                        <div class="form-section">
                            <div class="form-section-title">Interview Guidelines & Terms of Participation</div>
                            <ol class="guideline-list">
                                <li>The interview will be conducted on 5 May 2026 at each respective sponsor's booth,
                                    and the Djakarta Mining Club team and videographer will visit based on the selected
                                    time slot.</li>
                                <li>Each interview session will have a duration of 10-15 minutes for video recording.
                                </li>
                                <li>Each sponsor may assign 1-5 participants per session; interviews can be conducted
                                    with one or multiple participants, who will take turns answering the selected
                                    questions.</li>
                                <li>The final interview video will be approximately 1 minute long; therefore,
                                    participants are requested to select a suitable number of questions to be answered.
                                </li>
                            </ol>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">Company Name</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name <small>*</small></label>
                                        <select name="company_id" id="company_id" class="form-control js-select2" required>
                                            <option value="">-- Select Sponsor --</option>
                                            @foreach ($sponsors as $sponsor)
                                                <option value="{{ $sponsor->id }}"
                                                    data-max-optional="{{ (int) ($maxAdditionalByName[$sponsor->name] ?? 0) }}"
                                                    {{ (string) old('company_id') === (string) $sponsor->id ? 'selected' : '' }}>
                                                    {{ $sponsor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Preferred Interview Time Slot <small>*</small></label>
                                        <select name="preferred_time_slot" id="preferred_time_slot" class="form-control" required>
                                            <option value="">-- Select Time Slot --</option>
                                            @foreach ($timeSlots as $slot)
                                                <option value="{{ $slot }}"
                                                    {{ old('preferred_time_slot') === $slot ? 'selected' : '' }}
                                                    {{ in_array($slot, $bookedSlots, true) ? 'disabled' : '' }}>
                                                    {{ $slot }}{{ in_array($slot, $bookedSlots, true) ? ' (Booked)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Once selected by another sponsor, a slot is no longer
                                            available.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">PIC Name, Address, Message</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>PIC Name <small>*</small></label>
                                        <input type="text" name="pic_name" class="form-control"
                                            value="{{ old('pic_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>PIC Email Address <small>*</small></label>
                                        <input type="email" name="pic_email" class="form-control"
                                            value="{{ old('pic_email') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <small class="text-muted">A copy of this form response will be sent to the email address provided above</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">Number of Interviewees</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label>Number of Interviewees <small>*</small></label>
                                        <select name="number_of_interviewees" id="number_of_interviewees"
                                            class="form-control" required>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}"
                                                    {{ (int) old('number_of_interviewees', 1) === $i ? 'selected' : '' }}>
                                                    {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">Interviewee Details (Name & Job Title)</div>
                            <div class="section-subtitle">Fields below will follow your selected number of interviewees.
                            </div>
                            <div id="interviewees-container"></div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">List of Questions</div>

                            <div class="question-item required">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input required-question"
                                        id="question_1" name="selected_questions[]" value="1"
                                        {{ in_array(1, old('selected_questions', [1, 11])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="question_1">
                                        <span class="question-no">#1</span>
                                        Could you briefly introduce your company, including your core business and
                                        expertise?
                                    </label>
                                </div>
                            </div>

                            @foreach ($questions as $no => $question)
                                @if (!in_array($no, [1, 11], true))
                                    <div class="question-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input optional-question"
                                                id="question_{{ $no }}" name="selected_questions[]"
                                                value="{{ $no }}"
                                                {{ in_array($no, old('selected_questions', [])) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="question_{{ $no }}">
                                                <span class="question-no">#{{ $no }}</span>{{ $question }}
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <div class="question-item required mb-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input required-question"
                                        id="question_11" name="selected_questions[]" value="11"
                                        {{ in_array(11, old('selected_questions', [1, 11])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="question_11">
                                        <span class="question-no">#11</span>
                                        {{ $questions[11] }}
                                    </label>
                                </div>
                            </div>

                            <div class="mt-2">
                                <small class="text-muted" id="question-counter"></small>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">Sample Interview Script</div>
                            <div class="script-text">
                                Hello Djakarta Mining Club Members,<br><br>
                                We are from [Company Name], a trusted provider of integrated mining solutions,
                                specializing in innovative technologies that drive operational excellence, safety, and
                                productivity across mining operations. At Indonesia Miner 2026, we are proud to showcase
                                our latest solutions, including advanced digital and operational technologies designed
                                to help mining companies enhance site performance, optimize workflows, and achieve
                                greater efficiency in an increasingly competitive industry landscape.<br><br>
                                Today's mining sector is rapidly evolving toward smarter, more data-driven, and
                                sustainable operations, while also facing key challenges such as cost efficiency,
                                productivity pressures, and operational complexity. In response, we continuously
                                innovate and tailor our solutions to meet the changing needs of the industry.<br><br>
                                We strongly believe that collaboration and strategic partnerships are essential in
                                shaping the future of mining, enabling innovation and long-term value creation across
                                the ecosystem. You can find us at Booth A12, where we warmly invite you to visit,
                                explore our solutions, and connect with our team to discuss how we can support your
                                mining operations and future growth.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary btn-apply" type="submit">Submit Interview Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {
            var oldInterviewees = @json(old('interviewees', []));
            var maxOptional = 0;
            var bookedSlotsUrl = @json(route('sponsor.interview-schedule.booked-slots'));
            var refreshTimer = null;

            function toInt(value, fallback) {
                var parsed = parseInt(value, 10);
                return isNaN(parsed) ? fallback : parsed;
            }

            function companyMaxAdditional() {
                var option = $('#company_id option:selected');
                return toInt(option.data('max-optional'), 0);
            }

            function renderInterviewees() {
                var count = toInt($('#number_of_interviewees').val(), 1);
                var html = '';

                for (var i = 0; i < count; i++) {
                    var oldName = oldInterviewees[i] && oldInterviewees[i].name ? oldInterviewees[i].name : '';
                    var oldJob = oldInterviewees[i] && oldInterviewees[i].job_title ? oldInterviewees[i].job_title : '';

                    html += '<div class="interviewee-item">';
                    html += '<div class="font-weight-bold mb-2">Interviewee ' + (i + 1) + '</div>';
                    html += '<div class="row">';
                    html +=
                        '<div class="col-md-6"><div class="form-group mb-2"><label>Name <small>*</small></label><input type="text" name="interviewees[' +
                        i + '][name]" class="form-control" value="' + oldName.replace(/"/g, '&quot;') +
                        '" required></div></div>';
                    html +=
                        '<div class="col-md-6"><div class="form-group mb-2"><label>Job Title <small>*</small></label><input type="text" name="interviewees[' +
                        i + '][job_title]" class="form-control" value="' + oldJob.replace(/"/g, '&quot;') +
                        '" required></div></div>';
                    html += '</div>';
                    html += '</div>';
                }

                $('#interviewees-container').html(html);
            }

            function enforceQuestionRules() {
                maxOptional = companyMaxAdditional();

                $('#question_1').prop('checked', true);
                $('#question_11').prop('checked', true);

                var selectedOptional = $('.optional-question:checked').length;

                if (maxOptional <= 0) {
                    $('.optional-question').prop('checked', false).prop('disabled', true);
                    selectedOptional = 0;
                } else {
                    $('.optional-question').prop('disabled', false);

                    if (selectedOptional >= maxOptional) {
                        $('.optional-question:not(:checked)').prop('disabled', true);
                    }
                }

                $('#question-counter').text('Selected additional questions: ' + selectedOptional + ' / ' + maxOptional +
                    ' max');
            }

            function applyBookedSlots(booked, showWarningWhenSelectedTaken) {
                var currentSelected = $('#preferred_time_slot').val();

                $('#preferred_time_slot option').each(function() {
                    var val = $(this).val();
                    if (!val) return;

                    var isBooked = booked.indexOf(val) !== -1;
                    var textBase = val;

                    if (isBooked) {
                        $(this).prop('disabled', true).text(textBase + ' (Booked)');
                    } else {
                        $(this).prop('disabled', false).text(textBase);
                    }
                });

                if (currentSelected && booked.indexOf(currentSelected) !== -1) {
                    $('#preferred_time_slot').val('');
                    if (showWarningWhenSelectedTaken) {
                        alert('Time slot yang kamu pilih baru saja dibooking sponsor lain. Silakan pilih slot lain.');
                    }
                    return false;
                }

                return true;
            }

            function refreshBookedSlots(showWarningWhenSelectedTaken) {
                return $.getJSON(bookedSlotsUrl, function(resp) {
                    var booked = (resp && resp.booked_slots) ? resp.booked_slots : [];
                    applyBookedSlots(booked, showWarningWhenSelectedTaken);
                });
            }

            $(document).ready(function() {
                $('.js-select2').select2({
                    width: '100%'
                });

                renderInterviewees();
                enforceQuestionRules();
                refreshBookedSlots(false);

                $('#number_of_interviewees').on('change', function() {
                    renderInterviewees();
                });

                $('#company_id').on('change', function() {
                    $('.optional-question').prop('checked', false).prop('disabled', false);
                    enforceQuestionRules();
                });

                $('.required-question').on('change', function(e) {
                    $(e.target).prop('checked', true);
                });

                $('.optional-question').on('change', function() {
                    var selectedOptional = $('.optional-question:checked').length;
                    if (selectedOptional > maxOptional) {
                        $(this).prop('checked', false);
                        return;
                    }
                    enforceQuestionRules();
                });

                $('#interview-form').on('submit', function(e) {
                    e.preventDefault();
                    var form = this;

                    $.getJSON(bookedSlotsUrl, function(resp) {
                        var booked = (resp && resp.booked_slots) ? resp.booked_slots : [];
                        var okToSubmit = applyBookedSlots(booked, true);
                        if (okToSubmit) {
                            form.submit();
                        }
                    }).fail(function() {
                        form.submit();
                    });
                });

                refreshTimer = setInterval(function() {
                    refreshBookedSlots(true);
                }, 10000);
            });
        })();
    </script>
</body>

</html>
