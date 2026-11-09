@php($title = 'Annual Member Feedback Survey')
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#c8102e">
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

    <div class="container py-4">

        {{-- HEADER --}}
        <div class="header-ribbon mb-4 d-flex align-items-center">
            <img src="https://www.djakarta-miningclub.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2FLogo-DMC.8bf844a3.png&w=640&q=75"
                alt="Djakarta Mining Club">
        </div>

        <div class="card-survey">

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
                {{-- INTRO --}}
                <p>We value your input and would love to hear your feedback to help us improve our programs and services
                    in 2027. This survey will take approximately 5-10 minutes to complete.</p>
                <p>Your feedback will help us:</p>
                <ul>
                    <li>Plan programs that align with your interests and expectations.</li>
                    <li>Better understand your awareness and use of our communication channels.</li>
                    <li>Recognize outstanding contributions to the Indonesian mining industry.</li>
                </ul>
                <p>All responses will be kept confidential and used solely to enhance the value of your membership
                    experience.</p>
                <p class="mb-4">Thank you for taking the time to share your feedback. We truly appreciate your
                    participation!</p>

                <div id="survey-steps" class="stepper">
                    @foreach (['Member Information Update', 'Program Expectations & Feedback', 'Marketing & Communications Awareness', 'Additional Feedback'] as $label)
                        <div class="stepper-item">
                            <div class="stepper-bar"></div>
                            <span class="stepper-label">{{ $loop->iteration }}. {{ $label }}</span>
                        </div>
                    @endforeach
                </div>

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
                        <small class="text-muted">Step <span id="step-counter">1</span> of 4</small>
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
    </div>

    @unless (session('ok'))
        @include('survey.partials.member._scripts')
    @endunless
</body>

</html>
