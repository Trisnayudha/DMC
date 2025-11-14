{{-- resources/views/index.blade.php --}}
@php($title = $title ?? 'Djakarta Mining Club — Survey')
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title }}</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        body {
            background: #f5f7fb;
        }

        /* ===== Topbar ===== */
        .header-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.25rem;
            border-radius: 20px;
            background: radial-gradient(120% 140% at 0% 0%, #c53227 0%, #e34d32 45%, #f06e57 100%);
            color: #fff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .12)
        }

        .brand img {
            height: 44px;
            display: block
        }

        .burger {
            width: 28px;
            height: 18px;
            position: relative;
            opacity: .9
        }

        .burger span {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: #fff;
            border-radius: 2px
        }

        .burger span:nth-child(1) {
            top: 0
        }

        .burger span:nth-child(2) {
            top: 8px
        }

        .burger span:nth-child(3) {
            bottom: 0
        }

        /* ===== Card & sections ===== */
        .section-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(22, 31, 56, .08);
            padding: 1.5rem
        }

        .section-title {
            text-align: center;
            font-weight: 700;
            letter-spacing: .2px
        }

        /* ===== Field labels & helper ===== */
        .form-title {
            font-weight: 600;
            color: #1f2a44
        }

        .required-bullet {
            color: #ff3b3b;
            margin-left: .25rem
        }

        .invalid-inline {
            color: #dc3545;
            font-size: .875rem
        }

        /* ===== 1–5 score as buttons ===== */
        .score-wrap {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap
        }

        .score-option input {
            display: none
        }

        .score-btn {
            min-width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1px solid #ced4da;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            font-weight: 600;
            color: #2c3e55;
            cursor: pointer;
            transition: all .15s ease
        }

        .score-option input:checked+.score-btn {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15)
        }

        .score-option input:focus+.score-btn {
            outline: none;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25)
        }

        /* ===== Checkbox “pill cards” ===== */
        .checkcard {
            position: relative;
            border: 1px solid #e6e9f0;
            border-radius: 12px;
            padding: .9rem 1rem;
            transition: border-color .15s, box-shadow .15s, background .15s;
            background: #fff
        }

        .checkcard:hover {
            border-color: #b9c2d3;
            box-shadow: 0 6px 16px rgba(32, 40, 70, .06)
        }

        .checkcard input {
            position: absolute;
            opacity: 0;
            pointer-events: none
        }

        .checkcard.checked {
            border-color: #0d6efd;
            background: #f2f7ff;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .08)
        }

        .checkcard .tick {
            position: absolute;
            right: .85rem;
            top: .85rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #b6c0d2
        }

        .checkcard.checked .tick {
            background: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .18) inset
        }

        /* ===== Submit ===== */
        .btn-primary.btn-lg {
            padding: .8rem 1.4rem;
            border-radius: 12px;
            font-weight: 700
        }

        /* Banner */
        .survey-banner .banner-img {
            width: 100%;
            display: block;
            object-fit: cover;
            border-radius: 14px 14px 0 0
        }
    </style>
</head>

<body>
    <div class="container py-4">

        {{-- Header --}}
        <div class="header-ribbon mb-4">
            <div class="brand">
                <img src="https://www.djakarta-miningclub.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2FLogo-DMC.8bf844a3.png&w=640&q=75"
                    alt="Djakarta Mining Club">
            </div>
            <div class="burger" aria-label="menu" role="button"><span></span><span></span><span></span></div>
        </div>

        {{-- Notifikasi sukses (opsional) --}}
        @if (session('ok'))
            <div class="alert alert-success alert-dismissible fade show section-card mb-4" role="alert">
                <strong>Thank you!</strong> for taking the time to complete the survey. We truly value the information
                you have provided.
                <p>We look forward to having you again at our next event!
                </p>
                <p>To access the presentation please click the link below:
                    <a href="https://drive.google.com/drive/folders/17rLl_ayC8m2b2FbgsjErmSV_x7TNH5zo?usp=drive_link">https://drive.google.com/drive/folders/17rLl_ayC8m2b2FbgsjErmSV_x7TNH5zo?usp=drive_link
                    </a>
                </p>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
        @endif

        {{-- Form Survey --}}
        <div class="section-card">
            <div class="survey-banner mb-4">
                <img src="{{ asset('image/survey.png') }}" alt="Survey Banner" class="banner-img">
            </div>

            <h3 class="section-title mb-2">Thank You For Attending Indonesia Energy Market Briefing 2025</h3>
            <p class="text-muted mb-1">Please take a moment to complete the post-event survey. Your feedback is
                important for us to improve the quality of our next event.
            </p>
            <p class="text-muted">After completing the survey, we will provide you with links to download speaker
                presentations.
            </p>

            <form method="post" action="{{ route('survey.store') }}" novalidate id="surveyForm">
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-title">Email <span class="required-bullet">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        placeholder="you@company.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 1. On scale 1–5, how informative was the event? --}}
                <div class="form-group">
                    <label class="form-title">
                        On scale 1–5, how informative was the event?
                        <span class="required-bullet">*</span>
                    </label>
                    <div class="score-wrap mt-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="score-option m-0">
                                <input type="radio" id="score{{ $i }}" name="informative_score"
                                    value="{{ $i }}" {{ old('informative_score') == $i ? 'checked' : '' }}
                                    required>
                                <div class="score-btn">{{ $i }}</div>
                            </label>
                        @endfor
                    </div>
                    @error('informative_score')
                        <div class="invalid-inline mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 2. Which presentation was most relevant to the information you need? --}}
                <div class="form-group">
                    <label class="form-title">Which presentation was most relevant to the information you need? <span
                            class="required-bullet">*</span></label>

                    <div class="mt-2">
                        <label class="checkcard mb-2 d-block">
                            <input type="checkbox" name="most_relevant_presentations[]"
                                value="Spotlight on US Tariffs: Impact on Global Coal Supply and Demand"
                                {{ in_array('Spotlight on US Tariffs: Impact on Global Coal Supply and Demand', old('most_relevant_presentations', [])) ? 'checked' : '' }}>
                            <span class="tick"></span>
                            Spotlight on US Tariffs: Impact on Global Coal Supply and Demand
                        </label>

                        <label class="checkcard mb-2 d-block">
                            <input type="checkbox" name="most_relevant_presentations[]"
                                value="Choosing the Right Coal Index and Managing Risk"
                                {{ in_array('Choosing the Right Coal Index and Managing Risk', old('most_relevant_presentations', [])) ? 'checked' : '' }}>
                            <span class="tick"></span>
                            Choosing the Right Coal Index and Managing Risk
                        </label>

                        <label class="checkcard mb-2 d-block">
                            <input type="checkbox" name="most_relevant_presentations[]"
                                value="Chinese Coal Policy: Impact on Supply and Demand"
                                {{ in_array('Chinese Coal Policy: Impact on Supply and Demand', old('most_relevant_presentations', [])) ? 'checked' : '' }}>
                            <span class="tick"></span>
                            Chinese Coal Policy: Impact on Supply and Demand
                        </label>

                        <label class="checkcard mb-2 d-block">
                            <input type="checkbox" name="most_relevant_presentations[]"
                                value="Met Coal Challenges and Opportunities for Indonesia"
                                {{ in_array('Met Coal Challenges and Opportunities for Indonesia', old('most_relevant_presentations', [])) ? 'checked' : '' }}>
                            <span class="tick"></span>
                            Met Coal Challenges and Opportunities for Indonesia
                        </label>

                        <label class="checkcard d-block">
                            <input type="checkbox" name="most_relevant_presentations[]"
                                value="An Introduction to Minespans"
                                {{ in_array('An Introduction to Minespans', old('most_relevant_presentations', [])) ? 'checked' : '' }}>
                            <span class="tick"></span>
                            An Introduction to Minespans
                        </label>
                    </div>

                    <div id="presentationsError" class="invalid-inline mt-2" style="display:none;">
                        Please select at least one option.
                    </div>
                    @error('most_relevant_presentations')
                        <div class="invalid-inline mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 3. Are you a customer of McCloskey? --}}
                <div class="form-group">
                    <label class="form-title">Are you a customer of McCloskey? <span
                            class="required-bullet">*</span></label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="mYes" name="is_member" value="1"
                            class="custom-control-input" {{ old('is_member') === '1' ? 'checked' : '' }} required>
                        <label class="custom-control-label" for="mYes">Yes</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="mNo" name="is_member" value="0"
                            class="custom-control-input" {{ old('is_member') === '0' ? 'checked' : '' }} required>
                        <label class="custom-control-label" for="mNo">No</label>
                    </div>
                    @error('is_member')
                        <div class="invalid-inline mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 4. If not, would you be interested in learning more about McCloskey’s services? --}}
                <div class="form-group">
                    <label class="form-title">If not, would you be interested in learning more about McCloskey’s
                        services? <span class="required-bullet">*</span></label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="wYes" name="wants_more_info" value="1"
                            class="custom-control-input" {{ old('wants_more_info') === '1' ? 'checked' : '' }}
                            required>
                        <label class="custom-control-label" for="wYes">Yes</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="wNo" name="wants_more_info" value="0"
                            class="custom-control-input" {{ old('wants_more_info') === '0' ? 'checked' : '' }}
                            required>
                        <label class="custom-control-label" for="wNo">No</label>
                    </div>
                    @error('wants_more_info')
                        <div class="invalid-inline mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 5. What can we do better for the next Indonesia Energy Market Briefing? --}}
                <div class="form-group">
                    <label class="form-title">What can we do better for the next Indonesia Energy Market Briefing?
                        <span class="required-bullet">*</span></label>
                    <textarea name="feedback" rows="3" class="form-control" required>{{ old('feedback') }}</textarea>
                    @error('feedback')
                        <div class="invalid-inline mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 6. Which topics are you interested in knowing about for the next event? --}}
                <div class="form-group">
                    <label class="form-title">Which topics are you interested in knowing about for the next event?
                        <span class="required-bullet">*</span></label>
                    <textarea name="topics_2026" rows="3" class="form-control" required>{{ old('topics_2026') }}</textarea>
                    @error('topics_2026')
                        <div class="invalid-inline mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary btn-lg" type="submit" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>

    <!-- JS deps -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        (function() {
            var groupSelector = 'input[name="most_relevant_presentations[]"]';
            var errorEl = document.getElementById('presentationsError');
            var group = [].slice.call(document.querySelectorAll(groupSelector));

            function refreshCheckcards() {
                group.forEach(function(cb) {
                    var card = cb.closest('.checkcard');
                    if (!card) return;
                    if (cb.checked) card.classList.add('checked');
                    else card.classList.remove('checked');
                });
            }

            function validateGroup(showNow) {
                var atLeastOne = group.some(function(cb) {
                    return cb.checked;
                });
                if (showNow) errorEl.style.display = atLeastOne ? 'none' : 'block';
                return atLeastOne;
            }

            document.addEventListener('change', function(e) {
                if (e.target && e.target.matches(groupSelector)) {
                    refreshCheckcards();
                    validateGroup(true);
                }
            });

            // initial state from old()
            refreshCheckcards();

            var form = document.getElementById('surveyForm');
            var submitBtn = document.getElementById('submitBtn');
            form.addEventListener('submit', function(e) {
                var ok = validateGroup(true);
                if (!ok) {
                    e.preventDefault();
                    return;
                }
                setTimeout(function() {
                    submitBtn.disabled = true;
                }, 0);
            });
        })();
    </script>
</body>

</html>
