@php($title = 'Submit a Question – Indonesian Energy Market Briefing 2026')
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
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
    <div class="page-wrapper"><div class="page-inner container">
        <div class="page-header">
            <img src="{{ asset('image/dmc.png') }}" alt="Djakarta Mining Club">
            <div class="page-title">Indonesian Energy Market Briefing 2026</div>
        </div>
        <div class="card-shell"><div class="form-card">
            <div class="form-header-row">
                <div class="chip-step"><span>1</span> Submit a Question</div>
                <small class="text-muted">* Required</small>
            </div>
            <img src="{{ asset('image/banner_mccloskey_v8.png') }}" class="banner-img" alt="Indonesian Energy Market Briefing 2026">
            @if(session('success'))
                <div class="alert alert-success" role="status"><strong>Thank you!</strong> Your question has been submitted.</div>
            @else
                <div class="intro-text mb-4">
                    <p><strong>Have a question for our speakers?</strong></p>
                    <p>Share your question ahead of the Indonesian Energy Market Briefing 2026.</p>
                </div>
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">Please check the fields below and try again.</div>
                @endif
                <form method="POST" action="{{ route('event-questions.store') }}">
                    @csrf
                    <div class="form-section">
                        <div class="form-section-title">Your Details &amp; Question</div>
                        <div class="form-group">
                            <label for="name">Full name <small>*</small></label>
                            <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $participant->name ?? '') }}" maxlength="255" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email <small>*</small></label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $participant->email ?? '') }}" maxlength="255" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="company">Company / organization</label>
                            <input id="company" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company', $participant->company_name ?? '') }}" maxlength="255">
                            @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="question">Your question <small>*</small></label>
                            <textarea id="question" name="question" rows="5" class="form-control @error('question') is-invalid @enderror" maxlength="3000" required>{{ old('question') }}</textarea>
                            @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-4"><button class="btn btn-primary btn-submit" type="submit">Submit Your Question</button></div>
                </form>
            @endif
        </div></div>
    </div></div>
</body>
</html>
