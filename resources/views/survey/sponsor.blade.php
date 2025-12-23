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
                    <strong>Thank you for your time and insights!</strong>
                    Your feedback helps us enhance our programs and ensure a more valuable partnership in the future.
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
                <h5 class="section-title">DMC Sponsorship Survey – Quick Feedback</h5>

                <p>
                    Dear Sponsor,<br>
                    Thank you for partnering with Djakarta Mining Club. Your feedback helps us improve our programs,
                    sponsorship benefits, and overall experience. Please take a few minutes to complete this quick
                    survey.
                </p>

                <div class="form-group mt-4">
                    <label class="form-title">
                        Email <span class="required">*</span>
                    </label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-title">
                        Name <span class="required">*</span>
                    </label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-title">
                        Company <span class="required">*</span>
                    </label>
                    <input type="text" name="company" class="form-control" value="{{ old('company') }}" required>
                </div>

                {{-- Q1 --}}
                <label class="form-title mt-4">
                    1. How familiar are you with DMC’s programs, including events, event partnerships,
                    Commodity Maps, Scholarship, Outstanding Contribution Awards, etc? <span class="required">*</span>
                </label>
                @foreach (['Very Familiar', 'Somewhat Familiar', 'Not Very Familiar', 'Not Familiar'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="familiar{{ $loop->index }}"
                            name="program_familiarity" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="familiar{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q2 --}}
                <label class="form-title mt-4">
                    2. How would you rate the value and branding opportunities provided through these programs?
                    <span class="required">*</span>
                </label>
                @foreach (['Excellent', 'Good', 'Average', 'Poor'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="value{{ $loop->index }}"
                            name="branding_value" value="{{ $v }}" required>
                        <label class="custom-control-label" for="value{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q3 --}}
                <label class="form-title mt-4">
                    3. How well was your brand visibility maximized through your sponsorship?
                    <span class="required">*</span>
                </label>
                @foreach (['Extremely Well', 'Very Well', 'Not Very Well', 'Not at All'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="visibility{{ $loop->index }}"
                            name="brand_visibility" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="visibility{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q4 --}}
                <label class="form-title mt-4">
                    4. How satisfied are you with communication and support from the DMC team?
                    <span class="required">*</span>
                </label>
                @foreach (['Very Satisfied', 'Satisfied', 'Dissatisfied', 'Very Dissatisfied'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="support{{ $loop->index }}"
                            name="team_support" value="{{ $v }}" required>
                        <label class="custom-control-label"
                            for="support{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q5 --}}
                <label class="form-title mt-4">
                    5. Are you interested in renewing your sponsorship next year?
                    <span class="required">*</span>
                </label>
                @foreach (['Yes, definitely', 'Likely', 'Unsure', 'Unlikely', 'No'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="renew{{ $loop->index }}"
                            name="renewal_interest" value="{{ $v }}" required>
                        <label class="custom-control-label" for="renew{{ $loop->index }}">{{ $v }}</label>
                    </div>
                @endforeach

                {{-- Q6 --}}
                <div class="form-group mt-4">
                    <label class="form-title">
                        6. Please share any suggestions to improve sponsorship benefits or the overall experience.
                        <span class="required">*</span>
                    </label>
                    <textarea name="improvement_suggestion" rows="4" class="form-control" required></textarea>
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
