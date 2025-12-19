@php($title = '2026 Djakarta Mining Club Sponsor Survey')
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        /* =====================================================
           DMC THEME TOKENS
        ===================================================== */
        :root {
            --color-primary-default: 241 1 16;
            /* rgb(241,1,16) */
            --color-primary-600: 197 50 39;
            /* #c53227 */
            --color-primary-500: 227 77 50;
            /* #e34d32 */
            --color-primary-400: 240 110 87;
            /* #f06e57 */
        }

        body {
            background: #f5f7fb;
        }

        /* =====================================================
           HEADER (DMC RADIAL GRADIENT)
        ===================================================== */
        .header-ribbon {
            padding: 1rem 1.5rem;
            border-radius: 20px;
            background: radial-gradient(120% 140% at 0% 0%,
                    rgb(var(--color-primary-600)) 0%,
                    rgb(var(--color-primary-500)) 45%,
                    rgb(var(--color-primary-400)) 100%);
            box-shadow: 0 12px 30px rgba(241, 1, 16, .35);
        }

        .header-ribbon img {
            height: 42px;
        }

        /* =====================================================
           CARD
        ===================================================== */
        .card-survey {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(22, 31, 56, .08);
            padding: 2rem;
        }

        /* =====================================================
           SURVEY BANNER
        ===================================================== */
        .survey-banner {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .survey-banner img {
            width: 100%;
            height: auto;
            display: block;
        }

        .survey-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            /* background: linear-gradient(to bottom,
                    rgba(0, 0, 0, .15),
                    rgba(0, 0, 0, .35)); */
        }

        /* =====================================================
           TYPO & FORM
        ===================================================== */
        .section-title {
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
        }

        .form-title {
            font-weight: 600;
        }

        .required {
            color: rgb(var(--color-primary-default));
        }

        .form-control:focus {
            border-color: rgb(var(--color-primary-default));
            box-shadow: 0 0 0 .2rem rgba(241, 1, 16, .25);
        }

        .alert-success-dmc {
            background: linear-gradient(135deg,
                    rgba(40, 167, 69, .08),
                    rgba(40, 167, 69, .02));
            border: 1px solid rgba(40, 167, 69, .35);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            color: #155724;
        }

        .alert-success-dmc strong {
            font-weight: 700;
        }


        .alert-error-dmc {
            background: #fff5f5;
            border: 1px solid #f1b0b7;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* =====================================================
           CHECKCARD
        ===================================================== */
        .checkcard {
            border: 1px solid #e6e9f0;
            border-radius: 12px;
            padding: .75rem 1rem;
            margin-bottom: .5rem;
            cursor: pointer;
        }

        .checkcard input {
            display: none;
        }

        .checkcard.checked {
            border-color: rgb(var(--color-primary-default));
            background: rgba(241, 1, 16, .05);
        }

        /* =====================================================
           BUTTON
        ===================================================== */
        .btn-primary {
            background: rgb(var(--color-primary-default));
            border-color: rgb(var(--color-primary-default));
            border-radius: 12px;
            font-weight: 700;
            padding: .8rem 1.4rem;
        }
    </style>
</head>

<body>
    <div class="container py-4">

        {{-- HEADER --}}
        <div class="header-ribbon mb-4 d-flex align-items-center">
            <img src="https://www.djakarta-miningclub.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2FLogo-DMC.8bf844a3.png&w=640&q=75"
                alt="Djakarta Mining Club">
        </div>

        <div class="card-survey">

            {{-- BANNER --}}
            <div class="survey-banner">
                <img src="{{ asset('image/survey/banner-dmc-2026.png') }}" alt="DMC Survey Banner 2026">
            </div>
            {{-- =======================
             === ADD: SUCCESS ===
        ======================== --}}
            @if (session('ok'))
                <div class="alert-success-dmc">
                    <strong>Thank you!</strong>
                    Your survey has been successfully submitted.
                </div>
            @endif

            {{-- =======================
             === ADD: ERROR ===
        ======================== --}}
            @if ($errors->any())
                <div class="alert-error-dmc">
                    <strong>Please check the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('survey.dmc.store') }}">
                @csrf

                <h5 class="section-title">DMC SPONSOR SURVEY</h5>

                <p>
                    Thank you for your support as our sponsor! To help us support you better in the future,
                    we invite you to provide feedback through this short survey. Your answers are valuable in
                    enhancing the sponsorship experience and maximizing your brand visibility.
                </p>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-title">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                </div>

                {{-- Name --}}
                <div class="form-group">
                    <label class="form-title">Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                {{-- Company --}}
                <div class="form-group">
                    <label class="form-title">Company <span class="required">*</span></label>
                    <input type="text" name="company" class="form-control" required value="{{ old('company') }}">
                </div>

                {{-- Type of Sponsor --}}
                <div class="form-group">
                    <label class="form-title">Type of Sponsor <span class="required">*</span></label>
                    @foreach (['Major Sponsor', 'Gold Sponsor', 'Silver Sponsor'] as $v)
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="type{{ $loop->index }}"
                                name="type_of_sponsor" value="{{ $v }}"
                                {{ old('type_of_sponsor') == $v ? 'checked' : '' }} required>
                            <label class="custom-control-label"
                                for="type{{ $loop->index }}">{{ $v }}</label>
                        </div>
                    @endforeach
                </div>

                {{-- Q1 --}}
                <label class="form-title">
                    How satisfied are you with the promotional benefits received? <span class="required">*</span>
                </label>
                <small class="d-block mb-2">
                    (e.g., Company Logo on Website, Event Promotional Materials, Onsite Branding,
                    Company Profile on Website, New Sponsor Announcements on Social Media and Electronic Direct Mail
                    (EDM))
                </small>
                @foreach (['Very Satisfied', 'Satisfied', 'Dissatisfied', 'Very Dissatisfied'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="promo{{ $loop->index }}"
                            name="promo_benefit_satisfaction" value="{{ $v }}" required>
                        <label class="custom-control-label" for="promo{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="promo_benefit_other" placeholder="Yang lain:">

                {{-- Q2 --}}
                <label class="form-title mt-4">
                    How satisfied are you with the event attendance benefits? <span class="required">*</span>
                </label>
                <small class="d-block mb-2">
                    (e.g., complimentary tickets, registration priorities for virtual events,
                    access to attendee lists from main events (Major sponsor))
                </small>
                @foreach (['Very Satisfied', 'Satisfied', 'Dissatisfied', 'Very Dissatisfied'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="event{{ $loop->index }}"
                            name="event_attendance_satisfaction" value="{{ $v }}" required>
                        <label class="custom-control-label" for="event{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="event_attendance_other" placeholder="Yang lain:">

                {{-- Q3 --}}
                <label class="form-title mt-4">
                    How beneficial was the additional branding provided during live events? <span
                        class="required">*</span>
                </label>
                <small class="d-block mb-2">
                    (e.g., Company Standing Banner (Major sponsor), announcement by MC/Moderator,
                    Goodie Bag Promotional Insertion)
                </small>
                @foreach (['Very Beneficial', 'Beneficial', 'Less Beneficial', 'Not Beneficial'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="live{{ $loop->index }}"
                            name="live_event_branding_benefit" value="{{ $v }}" required>
                        <label class="custom-control-label" for="live{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="live_event_branding_other"
                    placeholder="Yang lain:">

                {{-- Q4 --}}
                <label class="form-title mt-4">
                    How satisfied are you with the additional value and branding provided? <span
                        class="required">*</span>
                </label>
                <small class="d-block mb-2">
                    (e.g., Linkable Logo and Article/Interview Contribution on the Mining Club Website,
                    Inclusion in the Weekly Newsletter, Posting of Job Vacancies in the Mining Club's Weekly Newsletter)
                </small>
                @foreach (['Very Satisfied', 'Satisfied', 'Dissatisfied', 'Very Dissatisfied'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="add{{ $loop->index }}"
                            name="additional_value_satisfaction" value="{{ $v }}" required>
                        <label class="custom-control-label" for="add{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="additional_value_other"
                    placeholder="Yang lain:">

                {{-- Q5 --}}
                <label class="form-title mt-4">
                    Do you feel that the price of your sponsorship level aligns with the benefits received? <span
                        class="required">*</span>
                </label>
                @foreach (['Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="price{{ $loop->index }}"
                            name="price_alignment" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="price{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="price_alignment_other"
                    placeholder="Yang lain:">

                {{-- Q6 --}}
                <label class="form-title mt-4">
                    How well do you feel your brand visibility was maximized through this sponsorship? <span
                        class="required">*</span>
                </label>
                @foreach (['Extremely Well', 'Very Well', 'Not Very Well', 'Not at All'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="vis{{ $loop->index }}"
                            name="brand_visibility" value="{{ $v }}" required>
                        <label class="custom-control-label" for="vis{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="brand_visibility_other"
                    placeholder="Yang lain:">

                {{-- Q7 --}}
                <label class="form-title mt-4">
                    How responsive and helpful was our team in fulfilling your sponsorship needs? <span
                        class="required">*</span>
                </label>
                @foreach (['Extremely Helpful', 'Very Helpful', 'Not Very Helpful', 'Not Helpful at All'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="team{{ $loop->index }}"
                            name="team_responsiveness" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="team{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="team_responsiveness_other"
                    placeholder="Yang lain:">

                {{-- Q8 --}}
                <label class="form-title mt-4">
                    What is your preferred method of communication for sponsorship updates and support? <span
                        class="required">*</span>
                </label>
                @foreach (['Electronic Direct Mail (EDM)', 'Personal Email', 'Phone Call', 'WhatsApp Message'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="comm{{ $loop->index }}"
                            name="preferred_communication" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="comm{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="preferred_communication_other"
                    placeholder="Yang lain:">

                {{-- Q9 --}}
                <label class="form-title mt-4">
                    Are you aware that we have a mobile app to enhance your sponsorship experience? <span
                        class="required">*</span>
                </label>
                @foreach (['Yes, I’m aware and I’ve used it.', 'Yes, I’m aware but I haven’t used it.', 'No, I’m not aware of it.'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="app{{ $loop->index }}"
                            name="mobile_app_awareness" value="{{ $v }}" required>
                        <label class="custom-control-label" for="app{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q10 --}}
                <label class="form-title mt-4">
                    Are you aware of the Commodity Map (Coal Map of Indonesia 2024 Edition and Mineral Map of Indonesia
                    2025 Edition) that we released? <span class="required">*</span>
                </label>
                @foreach (['Yes, I’m aware and have seen it.', 'Yes, I’m aware but haven’t seen it yet.', 'No, I’m not aware of it.'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="map{{ $loop->index }}"
                            name="commodity_map_awareness" value="{{ $v }}" required>
                        <label class="custom-control-label" for="map{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q11 --}}
                <label class="form-title mt-4">
                    Are you aware of any new programs or initiatives we introduced this year that were not included in
                    the sponsorship kit? <span class="required">*</span>
                </label>
                @foreach (['Yes, I’m aware of them.', 'No, I’m not aware of them.'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="prog{{ $loop->index }}"
                            name="new_program_awareness" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="prog{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q12 --}}
                <label class="form-title mt-4">
                    How would you rate your overall experience as a sponsor with us? <span class="required">*</span>
                </label>
                @foreach (['Excellent', 'Good', 'Average', 'Poor', 'Very Poor'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="overall{{ $loop->index }}"
                            name="overall_experience" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="overall{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="overall_experience_other"
                    placeholder="Yang lain:">

                {{-- Q13 --}}
                <label class="form-title mt-4">
                    Are you interested in renewing your sponsorship for the upcoming year? <span
                        class="required">*</span>
                </label>
                @foreach (['Yes, definitely', 'Likely', 'Unsure', 'Unlikely', 'No'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="renew{{ $loop->index }}"
                            name="renewal_interest" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="renew{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach
                <input type="text" class="form-control mt-2" name="renewal_interest_other"
                    placeholder="Yang lain:">

                {{-- Q14 --}}
                <div class="form-group mt-4">
                    <label class="form-title">
                        If unsure or unlikely, could you please share any reasons or improvements that might encourage
                        renewal?
                    </label>
                    <textarea name="renewal_reason" rows="3" class="form-control"></textarea>
                </div>

                {{-- Q15 --}}
                <div class="form-group">
                    <label class="form-title">
                        Do you have any suggestions or feedback to enhance our sponsorship benefits in the future?
                        <span class="required">*</span>
                    </label>
                    <textarea name="future_benefit_suggestion" rows="3" class="form-control" required></textarea>
                </div>

                {{-- Q16 --}}
                <div class="form-group">
                    <label class="form-title">
                        Do you have any suggestions on how we can improve the overall sponsorship experience?
                        <span class="required">*</span>
                    </label>
                    <textarea name="overall_experience_suggestion" rows="3" class="form-control" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg mt-4">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('change', function() {
            document.querySelectorAll('.checkcard').forEach(card => {
                const input = card.querySelector('input');
                card.classList.toggle('checked', input.checked);
            });
        });

        /* === ADD: AUTO SCROLL === */
        @if (session('ok') || $errors->any())
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        @endif
    </script>
</body>

</html>
