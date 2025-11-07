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
            background: #f8f9fa
        }

        .section-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
            padding: 1.25rem
        }

        /* Header ribbon mirip screenshot */
        .header-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 1rem;
            border-radius: 18px;
            background: linear-gradient(90deg, #c13025 0%, #e14a2f 45%, #ef6b54 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15)
        }

        .header-ribbon .brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            letter-spacing: .5px
        }

        .header-ribbon .logo {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: .6rem;
            font-size: .8rem;
            font-weight: 800
        }

        .burger {
            width: 28px;
            height: 18px;
            position: relative
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
    </style>
</head>

<body>
    <div class="container py-4">

        {{-- Header --}}
        <div class="header-ribbon mb-4">
            <div class="brand">
                <div class="logo">DMC</div>
                <div>
                    <div style="font-size:.9rem;line-height:1;opacity:.9">DJAKARTA</div>
                    <div style="margin-top:-2px;font-size:.9rem;line-height:1">MINING CLUB</div>
                </div>
            </div>
            <div class="burger" aria-label="menu" role="button"><span></span><span></span><span></span></div>
        </div>

        {{-- Notifikasi sukses (opsional): tampilkan kalau ada session flash "ok" --}}
        @if (session('ok'))
            <div class="alert alert-success alert-dismissible fade show section-card" role="alert">
                <strong>Thank you!</strong> Your response has been recorded.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Form Survey --}}
        <div class="section-card">
            <h3 class="mb-1">Thank You For Attending — DMC Briefing 2025</h3>
            <p class="text-muted mb-4">Please fill this short survey. After submission, you'll receive a link to
                download the speakers' slides.</p>

            {{-- ganti action ke URL kamu bila tidak memakai named route `survey.store` --}}
            <form method="post" action="{{ route('survey.store') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" placeholder="you@company.com"
                        required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>On a scale of 1 to 5 (1 = lowest, 5 = highest), how informative was this event?</label>
                    <div class="d-flex align-items-center">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="custom-control custom-radio mr-3">
                                <input type="radio" id="score{{ $i }}" name="informative_score"
                                    value="{{ $i }}" class="custom-control-input"
                                    {{ old('informative_score') == $i ? 'checked' : '' }} required>
                                <label class="custom-control-label"
                                    for="score{{ $i }}">{{ $i }}</label>
                            </div>
                        @endfor
                    </div>
                    @error('informative_score')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Which presentation was most relevant to the information you need?</label>
                    <select name="most_relevant_presentation"
                        class="form-control @error('most_relevant_presentation') is-invalid @enderror" required>
                        <option value="" disabled {{ old('most_relevant_presentation') ? '' : 'selected' }}>
                            Choose
                            one</option>
                        <option
                            {{ old('most_relevant_presentation') == 'Spotlight on US Tariffs: Impact on Global Coal Supply and Demand' ? 'selected' : '' }}>
                            Spotlight on US Tariffs: Impact on Global Coal Supply and Demand
                        </option>
                        <option
                            {{ old('most_relevant_presentation') == 'Choosing the Right Coal Index and Managing Risk' ? 'selected' : '' }}>
                            Choosing the Right Coal Index and Managing Risk
                        </option>
                        <option
                            {{ old('most_relevant_presentation') == 'Chinese Coal Policy: Impact on Supply and Demand' ? 'selected' : '' }}>
                            Chinese Coal Policy: Impact on Supply and Demand
                        </option>
                        <option
                            {{ old('most_relevant_presentation') == 'Met Coal: Challenges and Opportunities for Indonesia' ? 'selected' : '' }}>
                            Met Coal: Challenges and Opportunities for Indonesia
                        </option>
                    </select>
                    @error('most_relevant_presentation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Are you a DMC member?</label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="mYes" name="is_member" value="1"
                            class="custom-control-input" {{ old('is_member') === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="mYes">Yes</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="mNo" name="is_member" value="0"
                            class="custom-control-input" {{ old('is_member') === '0' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="mNo">No</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>If not, would you be interested in learning more about DMC services?</label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="wYes" name="wants_more_info" value="1"
                            class="custom-control-input" {{ old('wants_more_info') === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="wYes">Yes</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="wNo" name="wants_more_info" value="0"
                            class="custom-control-input" {{ old('wants_more_info') === '0' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="wNo">No</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>What can we do better for the next Briefing?</label>
                    <textarea name="feedback" rows="3" class="form-control">{{ old('feedback') }}</textarea>
                </div>

                <div class="form-group">
                    <label>What topics or updates would you like to see in 2026?</label>
                    <textarea name="topics_2026" rows="3" class="form-control">{{ old('topics_2026') }}</textarea>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="consent" name="consent"
                        {{ old('consent', true) ? 'checked' : '' }} required>
                    <label class="form-check-label" for="consent">
                        I consent to DMC storing my responses for follow-up.
                    </label>
                    @error('consent')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary btn-lg" type="submit" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 4 JS deps -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // optional: prevent double submit
        (function() {
            var btn = document.getElementById('submitBtn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                setTimeout(function() {
                    btn.disabled = true;
                }, 0);
            });
        })();
    </script>
</body>

</html>
