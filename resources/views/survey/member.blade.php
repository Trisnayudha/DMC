@php($title = '2026 Djakarta Mining Club Member Survey')
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

            {{-- INTRO --}}
            <h3 class="font-weight-bold mb-3">
                Welcome to the 2026 Djakarta Mining Club Member Survey!
            </h3>

            <p>Dear Members,</p>

            <p>
                We value your input and would like to hear your thoughts to help us improve our programs and services in
                2026.
                This survey will take approximately <strong>5–10 minutes</strong> to complete.
            </p>

            <ul>
                <li>Plan events and programs that match your interests and expectations</li>
                <li>Identify preferred speakers for 2026 programs</li>
                <li>Understand your awareness and usage of our communication channels</li>
                <li>Recognize outstanding contributions to the Indonesian mining industry</li>
            </ul>

            <p class="mb-4">
                All responses will remain confidential and used solely to enhance the value of your membership
                experience.
                Thank you for taking the time to share your feedback.
            </p>

            <form method="POST" action="{{ route('survey.dmc.store') }}">
                @csrf

                {{-- 1. MEMBER INFORMATION --}}
                <h5 class="section-title">1. Member Information Update</h5>

                <div class="form-group">
                    <label class="form-title">Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>

                </div>

                <div class="form-group">
                    <label class="form-title">Company / Organization <span class="required">*</span></label>
                    <input type="text" name="company" value="{{ old('company') }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-title">Position / Title <span class="required">*</span></label>
                    <input type="text" name="position" value="{{ old('position') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-title">Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-title">Mobile Number <span class="required">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-title">LinkedIn / Professional Profile</label>
                    <input type="text" name="linkedin" value="{{ old('linkedin') }}" class="form-control">
                </div>

                {{-- 2. PROGRAM EXPECTATIONS --}}
                <h5 class="section-title">2. Program Expectations & Input</h5>

                <label class="form-title">Which types of events would you like us to organize in 2026?</label>
                @foreach (['Workshops', 'Seminars', 'Networking Events', 'Webinars', 'Other'] as $v)
                    <label class="checkcard d-block">
                        <input type="checkbox" name="event_types[]" value="{{ $v }}"
                            {{ in_array($v, old('event_types', [])) ? 'checked' : '' }}>
                        {{ $v }}
                    </label>
                @endforeach

                <div class="form-group mt-3">
                    <label class="form-title">What topics are you most interested in 2026 programs?</label>
                    <textarea name="topics_interest" rows="3" class="form-control">{{ old('topics_interest') }}</textarea>

                </div>

                <div class="form-group">
                    <label class="form-title">Do you have any wishlist for speakers for 2026 programs?</label>
                    <textarea name="speaker_wishlist" rows="3" class="form-control">{{ old('speaker_wishlist') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-title">Nomination for Outstanding Contribution to the Indonesia Mining Industry
                        Award 2026
                    </label>
                    <input type="text" name="nominee_name" value="{{ old('nominee_name') }}"
                        class="form-control mb-2" placeholder="Name of nominee">
                    <input type="text" name="nominee_company" value="{{ old('nominee_company') }}"
                        class="form-control" placeholder="Company / Institution">
                </div>

                <div class="form-group">
                    <label class="form-title">Suggestions to improve past events</label>
                    <textarea name="event_improvement" rows="3" class="form-control">{{ old('event_improvement') }}</textarea>
                </div>

                {{-- 3. MARKETING & COMMUNICATION --}}
                <h5 class="section-title">3. Marketing & Communication Awareness</h5>

                <label class="form-title">
                    How familiar are you with our social media channels?
                </label>

                @foreach (['Very familiar', 'Somewhat familiar', 'Not familiar'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="sf{{ $loop->index }}"
                            name="social_familiarity[]" value="{{ $v }}"
                            {{ in_array($v, old('social_familiarity', [])) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="sf{{ $loop->index }}">
                            {{ $v }}
                        </label>
                    </div>
                @endforeach




                <label class="form-title mt-3">Which social media platforms do you follow us on? <small><i>(Select all
                            that apply)
                        </i></small></label>
                @foreach (['LinkedIn', 'Instagram', 'Facebook', 'Twitter', 'YouTube', 'WhatsApp', 'Other'] as $v)
                    <label class="checkcard d-block">
                        <input type="checkbox" name="platforms[]" value="{{ $v }}">
                        {{ $v }}
                    </label>
                @endforeach

                <label class="form-title mt-3">
                    Are you aware of the Djakarta Mining Club mobile app and website?
                </label>

                @foreach (['Yes, both', 'Yes, mobile app only', 'Yes, website only', 'No'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="app{{ $loop->index }}"
                            name="app_awareness[]" value="{{ $v }}"
                            {{ in_array($v, old('app_awareness', [])) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="app{{ $loop->index }}">
                            {{ $v }}
                        </label>
                    </div>
                @endforeach




                <label class="form-title mt-3">
                    If yes, how often do you use the mobile app or visit the website?
                </label>

                @foreach (['Frequently', 'Occasionally', 'Rarely'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="use{{ $loop->index }}"
                            name="usage_frequency[]" value="{{ $v }}"
                            {{ in_array($v, old('usage_frequency', [])) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="use{{ $loop->index }}">
                            {{ $v }}
                        </label>
                    </div>
                @endforeach

                <label class="form-title mt-3">
                    What is your primary goal when opening emails from the Djakarta Mining Club?
                </label>

                @foreach (['Seeking information on upcoming events (Registration / Agenda)', 'Obtaining industry news and updates', 'Looking for networking opportunities', 'Other'] as $v)
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="goal{{ $loop->index }}"
                            name="email_primary_goal[]" value="{{ $v }}"
                            {{ in_array($v, old('email_primary_goal', [])) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="goal{{ $loop->index }}">
                            {{ $v }}
                        </label>
                    </div>
                @endforeach


                {{-- Other (specify) --}}
                <div class="form-group mt-2">
                    <input type="text" name="email_primary_goal_other" class="form-control"
                        placeholder="If other, please specify" value="{{ old('email_primary_goal_other') }}">
                    <label class="form-title mt-4">
                        What day is the best for you to receive industry-related emails?
                    </label>

                    @foreach (['Monday–Wednesday', 'Thursday–Friday', 'Saturday–Sunday', 'Time is not an issue'] as $v)
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="day{{ $loop->index }}"
                                name="email_best_day[]" value="{{ $v }}"
                                {{ in_array($v, old('email_best_day', [])) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="day{{ $loop->index }}">
                                {{ $v }}
                            </label>
                        </div>
                    @endforeach


                    <div class="form-group mt-3">
                        <label class="form-title">
                            Suggestions to improve communication and engagement
                        </label>
                        <textarea name="communication_feedback" rows="3" class="form-control">{{ old('communication_feedback') }}</textarea>
                    </div>

                    {{-- 4. ADDITIONAL --}}
                    <h5 class="section-title">4. Additional Feedback</h5>
                    <textarea name="additional_feedback" rows="3" class="form-control">{{ old('additional_feedback') }}</textarea>

                    <button type="submit" class="btn btn-primary btn-lg mt-4">
                        Submit Survey
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
