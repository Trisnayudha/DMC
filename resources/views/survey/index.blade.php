{{-- resources/views/index.blade.php --}}
@php($title = $title ?? 'DMC – Event Survey')
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
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
            padding: 30px 0 50px;
        }

        .page-inner {
            max-width: 860px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-header img {
            max-width: 200px;
        }

        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-top: 12px;
            letter-spacing: .03em;
        }

        .card-shell {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        }

        .form-card {
            padding: 22px 26px 30px;
            border-top: 3px solid var(--dmc-red);
            border-radius: 18px;
        }

        .form-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .chip-step {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #f3f4f6;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .08em;
        }

        .chip-step span {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            background: var(--dmc-red);
            color: #fff;
            font-size: .7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 6px;
        }

        .form-section {
            border: 1px solid var(--dmc-border);
            border-radius: 12px;
            padding: 16px 18px 14px;
        }

        .form-section-title {
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 12px;
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
            box-shadow: 0 0 0 .15rem rgba(200, 16, 46, .15);
        }

        .score-wrap {
            display: flex;
            gap: .6rem;
        }

        .score-option input {
            display: none;
        }

        .score-btn {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--dmc-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .score-option input:checked+.score-btn {
            border-color: var(--dmc-red);
            box-shadow: 0 0 0 .15rem rgba(200, 16, 46, .2);
        }

        .btn-submit {
            border-radius: 999px;
            padding: .6rem 1.8rem;
            font-weight: 600;
            background: var(--dmc-red);
            border-color: var(--dmc-red);
        }

        .btn-submit:hover {
            background: var(--dmc-red-dark);
        }

        .banner-img {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .intro-text p {
            font-size: .88rem;
            line-height: 1.6;
            color: #4b5563;
        }

        .intro-text strong {
            color: #111827;
        }
    </style>
</head>

<body>

    <div class="page-wrapper">
        <div class="page-inner">

            <!-- HEADER -->
            <div class="page-header">
                <img src="{{ asset('image/dmc.png') }}">
                <div class="page-title">
                    THE 69TH DJAKARTA MINING CLUB NETWORKING EVENT<br>
                    <span style="color: var(--dmc-red-dark)">2026 MINING INSIGHTS</span>
                </div>
            </div>

            <div class="card-shell">
                <div class="form-card">

                    <div class="form-header-row">
                        <div class="chip-step"><span>1</span> Event Survey</div>
                        <small class="text-muted">* Required</small>
                    </div>

                    <img src="{{ asset('image/the69banner.png') }}" class="banner-img">

                    <!-- INTRO TEXT -->
                    <div class="intro-text mb-4">
                        <p>
                            <strong>Thank you for attending The 69th Djakarta Mining Club Networking Event: 2026 Mining
                                Insights.</strong>
                        </p>
                        <p>
                            We kindly invite you to take a few moments to complete our post-event survey.
                            Your feedback is invaluable in helping us enhance the quality of future events.
                        </p>
                        <p>
                            Once the survey is completed, you will receive access to links for downloading
                            the speakers’ presentation materials.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('survey.store') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="form-section">
                            <div class="form-section-title">Survey Form</div>

                            <div class="form-group">
                                <label>Email <small>*</small></label>
                                <input type="email" name="email" class="form-control" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email.
                                </div>
                            </div>

                            <div class="form-group">
                                <label>On a scale of 1–5 (5 highest), how would you rate the event?
                                    <small>*</small></label>
                                <div class="score-wrap mt-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="score-option m-0">
                                            <input type="radio" name="event_rating" value="{{ $i }}"
                                                required>
                                            <div class="score-btn">{{ $i }}</div>
                                        </label>
                                    @endfor
                                </div>
                                <div class="invalid-feedback d-block">
                                    Please select a rating.
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Comments or suggestions to improve future events <small>*</small></label>
                                <textarea name="improvement_feedback" rows="3" class="form-control" required></textarea>
                                <div class="invalid-feedback">
                                    This field is required.
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label>Topic or speaker recommendations for upcoming events <small>*</small></label>
                                <textarea name="topic_recommendation" rows="3" class="form-control" required></textarea>
                                <div class="invalid-feedback">
                                    This field is required.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary btn-submit" type="submit">
                                Submit
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script>
        (function() {
            'use strict';

            var forms = document.getElementsByClassName('needs-validation');

            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {

                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        var btn = form.querySelector('button[type="submit"]');
                        btn.disabled = true;
                        btn.innerHTML = "Submitting...";
                    }

                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>

    @if (session('ok'))
        <script>
            swal({
                title: "Thank You 🎉",
                text: "Your feedback has been successfully submitted.",
                icon: "success",
                button: "Open Presentation",
                closeOnClickOutside: false,
                closeOnEsc: false,
            }).then(function() {

                window.open(
                    "https://drive.google.com/drive/folders/15HugkNkkXW3v7sMvUFBUuAosrfP5fGIv?usp=sharing",
                    "_blank"
                );

                document.querySelector("form").reset();
            });
        </script>
    @endif

</body>

</html>
