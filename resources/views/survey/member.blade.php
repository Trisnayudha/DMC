@php($title = 'Annual Member Feedback Survey')
@php($steps = ['Member Information Update', 'Program Expectations & Feedback', 'Marketing & Communications Awareness', 'Additional Feedback'])
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3a0a14">
    <title>{{ $title }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @include('survey.partials.member._styles')
</head>

<body>

    <div class="survey-shell {{ session('ok') ? 'is-done' : '' }}">

        {{-- SIDEBAR --}}
        <aside class="survey-side">
            {{-- Wave artwork matching the banner --}}
            <svg class="side-art" viewBox="0 0 420 1000" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <defs>
                    <linearGradient id="sideArtBg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#7d0b13" />
                        <stop offset=".5" stop-color="#5f0710" />
                        <stop offset="1" stop-color="#2b060b" />
                    </linearGradient>
                    <linearGradient id="sideArtBand" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#981820" />
                        <stop offset="1" stop-color="#7a0e16" />
                    </linearGradient>
                </defs>
                <rect width="420" height="1000" fill="url(#sideArtBg)" />
                <path d="M-20 -20 C60 110 220 190 440 170 L440 -20 Z" fill="#860f18" opacity=".55" />
                <path d="M440 300 C300 330 200 300 -20 220 L-20 170 C200 250 300 270 440 240 Z" fill="#6b0811" opacity=".6" />
                <path d="M-20 560 C130 570 270 650 440 860 L440 990 C270 760 130 690 -20 680 Z" fill="url(#sideArtBand)" />
                <path d="M-20 700 C150 715 290 820 440 1020 L-20 1020 Z" fill="#6e0a12" opacity=".85" />
                <path d="M440 880 C360 920 300 970 270 1020 L440 1020 Z" fill="#4a070d" opacity=".9" />
            </svg>
            <div class="side-inner">
                <div class="side-brand">
                    <img src="https://www.djakarta-miningclub.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2FLogo-DMC.8bf844a3.png&w=640&q=75"
                        alt="Djakarta Mining Club">
                </div>

                @unless (session('ok'))
                    {{-- INTRO --}}
                    <div class="survey-intro">
                        <p class="intro-lead">We value your input and would love to hear your feedback to help us improve
                            our programs and services in 2027. This survey will take approximately 5-10 minutes to
                            complete.</p>
                        <p class="intro-list-title">Your feedback will help us:</p>
                        <ul>
                            <li>Plan programs that align with your interests and expectations.</li>
                            <li>Better understand your awareness and use of our communication channels.</li>
                            <li>Recognize outstanding contributions to the Indonesian mining industry.</li>
                        </ul>
                        <p class="intro-note">All responses will be kept confidential and used solely to enhance the value
                            of your membership experience.</p>
                        <p class="intro-thanks">Thank you for taking the time to share your feedback. We truly appreciate
                            your participation!</p>
                    </div>
                @endunless
            </div>
        </aside>

        {{-- MAIN --}}
        <main class="survey-main">
            <div class="main-inner">

                <div class="survey-banner">
                    <img src="{{ asset('image/survey/banner-dmc-2027-v2.png') }}" alt="DMC Survey Banner 2027">
                </div>

                @if (session('ok'))
                    <div class="success-panel">
                        <div class="success-icon"><i class="fas fa-check"></i></div>
                        <h3 class="font-weight-bold mb-2">Thank you!</h3>
                        <p class="mb-0">Your survey has been successfully submitted.</p>
                    </div>
                @else
                    <nav id="survey-steps" class="stepper">
                        @foreach ($steps as $step)
                            <div class="stepper-item">
                                <div class="stepper-bar"></div>
                                <span class="stepper-text">
                                    <span class="stepper-eyebrow">Step {{ $loop->iteration }} of {{ count($steps) }}</span>
                                    <span class="stepper-label">{{ $step }}</span>
                                </span>
                                <span class="stepper-dot" aria-hidden="true"></span>
                            </div>
                        @endforeach
                    </nav>

                    @if ($errors->any())
                        <div class="alert-error-dmc">
                            <strong>Please check the following errors:</strong>
                            <ul class="mb-0 mt-1 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('survey.member.store') }}" id="member-survey-form" novalidate>
                        @csrf

                        @include('survey.partials.member._step_profile')
                        @include('survey.partials.member._step_program')
                        @include('survey.partials.member._step_marketing')
                        @include('survey.partials.member._step_additional')

                        <div class="step-actions">
                            <button type="button" class="btn-step-back" id="btn-step-back">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <small class="step-count">Step <span id="step-counter">1</span> of 4</small>
                            <button type="button" class="btn-draw-main" id="btn-step-next">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn-draw-main" id="btn-step-submit">
                                <i class="fas fa-paper-plane"></i> Submit Survey
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </main>
    </div>

    @if (session('ok'))
        {{-- Survey submitted: drop the local autosave copy --}}
        <script>
            try { localStorage.removeItem('dmc_member_survey_draft'); } catch (e) {}
        </script>
    @else
        @include('survey.partials.member._scripts')
    @endif
</body>

</html>
