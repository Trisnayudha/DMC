<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Register now for the Djakarta Mining Club event and be part of the mining industry!">
    <meta name="author" content="djakarta-miningclub.com">
    <title>DMC – Event Registration</title>

    <!-- Open Graph -->
    <meta property="og:title" content="Register for the Djakarta Mining Club Event!" />
    <meta property="og:description" content="Join the leading mining event and connect with industry professionals. Register now!" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ url('image/meta.png') }}">
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:site_name" content="Djakarta Mining Club" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Register for the Djakarta Mining Club Event!">
    <meta name="twitter:description" content="Join the leading mining event and connect with industry professionals. Register now!">
    <meta name="twitter:image" content="{{ url($image) }}">

    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <link href="{{ asset('new-zoom/form-validation.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- JS Libraries -->
    <script src="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

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
            padding: 26px 0 48px;
        }

        .page-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 22px;
        }

        .page-header img {
            max-width: 180px;
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

        /* Event Info Card */
        .event-info-card {
            background: #fff;
            border-radius: 14px;
            border-left: 4px solid var(--dmc-red);
            box-shadow: 0 4px 16px rgba(15, 23, 42, .06);
            padding: 18px 22px;
            margin-bottom: 18px;
        }

        .event-info-card h6 {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--dmc-red);
            margin-bottom: 12px;
        }

        .event-detail-grid {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 6px 0;
            font-size: .88rem;
        }

        .event-detail-grid .label {
            font-weight: 600;
            color: #6b7280;
        }

        .event-detail-grid .value {
            color: var(--dmc-text);
        }

        /* Two-column layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 18px;
            margin-bottom: 18px;
            align-items: start;
        }

        @media (max-width: 767px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Description Card */
        .desc-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .06);
            padding: 20px 22px;
        }

        .section-label {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--dmc-red);
            margin-bottom: 10px;
        }

        .desc-card p,
        .desc-card li,
        .desc-card div {
            font-size: .88rem;
            line-height: 1.7;
            color: #374151;
        }

        .desc-card p {
            margin-bottom: .85rem;
        }

        .desc-card strong, .desc-card b {
            font-weight: 700;
        }

        /* Rundown Card */
        .rundown-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .06);
            padding: 20px 22px;
        }

        .rundown-timeline {
            position: relative;
            padding-left: 22px;
        }

        .rundown-timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: #e5e7eb;
        }

        .rundown-item {
            position: relative;
            margin-bottom: 18px;
        }

        .rundown-item:last-child {
            margin-bottom: 0;
        }

        .rundown-dot {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--dmc-red);
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px var(--dmc-red);
        }

        .rundown-time {
            font-size: .75rem;
            font-weight: 700;
            color: var(--dmc-red);
            letter-spacing: .04em;
            margin-bottom: 2px;
        }

        .rundown-name {
            font-size: .88rem;
            font-weight: 600;
            color: var(--dmc-text);
            line-height: 1.3;
        }

        .rundown-speakers {
            margin-top: 6px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .speaker-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .speaker-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: #f3f4f6;
        }

        .speaker-avatar-placeholder {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f3f4f6;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            font-weight: 700;
            color: #9ca3af;
        }

        .speaker-info .name {
            font-size: .78rem;
            font-weight: 600;
            color: var(--dmc-text);
            line-height: 1.2;
        }

        .speaker-info .title {
            font-size: .72rem;
            color: #6b7280;
            line-height: 1.2;
        }

        /* Card Shell */
        .card-shell {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        }

        .form-card {
            padding: 22px 26px 28px;
            border-top: 3px solid var(--dmc-red);
            border-radius: 18px;
        }

        .form-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .chip-step {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            background: #f3f4f6;
            font-size: .78rem;
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

        /* Form Sections */
        .form-section {
            border: 1px solid var(--dmc-border);
            border-radius: 12px;
            padding: 16px 18px 10px;
            margin-bottom: 14px;
        }

        .form-section-title {
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--dmc-red-dark);
            margin-bottom: 12px;
        }

        label.form-label,
        label {
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .form-control,
        .custom-select {
            font-size: .88rem;
            border-radius: 8px;
            border-color: var(--dmc-border);
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: var(--dmc-red);
            box-shadow: 0 0 0 .15rem rgba(200, 16, 46, .16);
        }

        /* Select2 styling */
        .select2-container--default .select2-selection--single {
            border-radius: 8px !important;
            border-color: var(--dmc-border) !important;
            height: calc(1.5em + .75rem + 2px) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + .75rem) !important;
            font-size: .88rem;
            padding-left: 10px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem) !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--dmc-red) !important;
            box-shadow: 0 0 0 .15rem rgba(200, 16, 46, .16) !important;
        }

        .myDiv {
            display: none;
        }

        .iti {
            width: 100%;
        }

        /* Notice text */
        .notice-text {
            font-size: .8rem;
            color: #6b7280;
        }

        /* Submit button */
        .btn-submit {
            border-radius: 999px;
            padding: .6rem 2rem;
            font-weight: 600;
            font-size: .95rem;
            background: var(--dmc-red);
            border-color: var(--dmc-red);
            color: #fff;
            transition: background .18s;
        }

        .btn-submit:hover {
            background: var(--dmc-red-dark);
            border-color: var(--dmc-red-dark);
            color: #fff;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            margin-top: 32px;
            font-size: .8rem;
            color: #9ca3af;
        }

        .page-footer a {
            color: #9ca3af;
            text-decoration: underline;
        }

        /* ── Mobile collapsible ── */
        @media (max-width: 767px) {
            .mob-collapsible {
                position: relative;
            }

            .mob-collapsible-body {
                max-height: 200px;
                overflow: hidden;
                transition: max-height .35s ease;
            }

            .mob-collapsible-body.expanded {
                max-height: 9999px;
            }

            /* gradient fade at bottom when collapsed */
            .mob-collapsible-fade {
                position: absolute;
                bottom: 44px; /* sit above the button */
                left: 0;
                right: 0;
                height: 60px;
                background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
                pointer-events: none;
                transition: opacity .25s;
            }

            .mob-collapsible-fade.hidden {
                opacity: 0;
            }

            .mob-toggle-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                width: 100%;
                margin-top: 10px;
                padding: 6px 0;
                background: none;
                border: none;
                font-size: .8rem;
                font-weight: 600;
                color: var(--dmc-red);
                cursor: pointer;
                letter-spacing: .04em;
            }

            .mob-toggle-btn svg {
                transition: transform .3s ease;
                flex-shrink: 0;
            }

            .mob-toggle-btn.expanded svg {
                transform: rotate(180deg);
            }
        }

        /* On desktop: never collapse */
        @media (min-width: 768px) {
            .mob-collapsible-fade,
            .mob-toggle-btn {
                display: none !important;
            }

            .mob-collapsible-body {
                max-height: none !important;
                overflow: visible !important;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrapper">
        <div class="page-inner">

            <!-- HEADER -->
            <div class="page-header">
                <img src="{{ asset('image/dmc.png') }}" alt="DMC Logo">
                <h1 class="page-title">Event <span>Registration</span></h1>
            </div>

            <!-- EVENT DETAILS -->
            <div class="event-info-card">
                <h6>Event Details</h6>
                <div class="event-detail-grid">
                    <div class="label">Title</div>
                    <div class="value">{{ $name }}</div>
                    <div class="label">Date</div>
                    <div class="value">{{ date('l', strtotime($start_date)) . ' – ' . date('j F Y', strtotime($end_date)) }}</div>
                    <div class="label">Time</div>
                    <div class="value">{{ date('h.i A', strtotime($start_time)) . ' – ' . date('h.i A', strtotime($end_time)) }} (Jakarta Time)</div>
                    <div class="label">Location</div>
                    <div class="value">{{ $location }}</div>
                </div>
            </div>

            <!-- DESCRIPTION + RUNDOWN -->
            <div class="content-grid">

                <!-- Description -->
                @if (!empty($description))
                <div class="desc-card mob-collapsible">
                    <div class="section-label">Event Details</div>
                    <div class="mob-collapsible-body" id="descBody">
                        {!! $description !!}
                    </div>
                    <div class="mob-collapsible-fade" id="descFade"></div>
                    <button class="mob-toggle-btn" id="descToggle" onclick="toggleCollapsible('descBody','descFade','descToggle')" type="button">
                        See More
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>
                @endif

                <!-- Rundown -->
                @if (!empty($rundown))
                <div class="rundown-card mob-collapsible">
                    <div class="section-label">Event Rundown</div>
                    <div class="mob-collapsible-body" id="rundownBody">
                        <div class="rundown-timeline">
                            @foreach ($rundown as $item)
                            <div class="rundown-item">
                                <div class="rundown-dot"></div>
                                <div class="rundown-time">{{ $item['time'] }}</div>
                                <div class="rundown-name">{{ $item['name'] }}</div>
                                @if (!empty($item['speakers']))
                                <div class="rundown-speakers">
                                    @foreach ($item['speakers'] as $speaker)
                                    <div class="speaker-item">
                                        @if (!empty($speaker['image']))
                                            <img class="speaker-avatar" src="{{ asset($speaker['image']) }}" alt="{{ $speaker['name'] }}">
                                        @else
                                            <div class="speaker-avatar-placeholder">{{ strtoupper(substr($speaker['name'], 0, 2)) }}</div>
                                        @endif
                                        <div class="speaker-info">
                                            <div class="name">{{ $speaker['name'] }}</div>
                                            <div class="title">{{ $speaker['job_title'] }}@if(!empty($speaker['company'])), {{ $speaker['company'] }}@endif</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mob-collapsible-fade" id="rundownFade"></div>
                    <button class="mob-toggle-btn" id="rundownToggle" onclick="toggleCollapsible('rundownBody','rundownFade','rundownToggle')" type="button">
                        See More
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>
                @endif

            </div>

            <!-- FORM -->
            <div class="card-shell">
                <div class="form-card">

                    <div class="form-header-row">
                        <div class="chip-step"><span>1</span> Registration Form</div>
                        <small class="text-muted">* Required</small>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" style="border-radius:10px;font-size:.88rem;">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ url('/payment-personal') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="slug" value="{{ $slug }}">
                        <input type="hidden" name="paymentMethod" value="free">

                        <!-- Personal Information -->
                        <div class="form-section">
                            <div class="form-section-title">Personal Information</div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') }}" required>
                                        <div class="invalid-feedback">Valid name is required.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Job Title *</label>
                                        <input type="text" class="form-control" name="job_title"
                                            value="{{ old('job_title') }}" required>
                                        <div class="invalid-feedback">Please enter your Job Title.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" id="email"
                                            placeholder="Your work email" value="{{ old('email') }}" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Mobile Number *</label>
                                        <input type="tel" class="form-control" name="phone" id="phone"
                                            value="{{ old('phone') ? old('phone') : '+62' }}" required>
                                        <div class="invalid-feedback">Please provide a Mobile Number.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Information -->
                        <div class="form-section">
                            <div class="form-section-title">Company Information</div>
                            <div class="row">
                                <div class="col-md-3 col-sm-4">
                                    <div class="form-group">
                                        <label class="form-label">Prefix *</label>
                                        <select class="custom-select d-block w-100" id="prefix" name="prefix" required>
                                            <option value="PT">PT</option>
                                            <option value="CV">CV</option>
                                            <option value="Ltd">Ltd</option>
                                            <option value="GmbH">GmbH</option>
                                            <option value="Limited">Limited</option>
                                            <option value="Llc">Llc</option>
                                            <option value="Corp">Corp</option>
                                            <option value="Pte Ltd">Pte Ltd</option>
                                            <option value="Assosiation">Association</option>
                                            <option value="Government">Government</option>
                                            <option value="Pty Ltd">Pty Ltd</option>
                                            <option value="">Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a valid prefix.</div>
                                    </div>
                                </div>
                                <div class="col-md-9 col-sm-8">
                                    <div class="form-group">
                                        <label class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" name="company_name"
                                            placeholder="Your company name" value="{{ old('company_name') }}" required>
                                        <div class="invalid-feedback">Valid company name is required.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Address *</label>
                                        <input type="text" class="form-control" name="address" id="address"
                                            value="{{ old('address') }}" required>
                                        <div class="invalid-feedback">Address is required.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label">Country *</label>
                                        <select class="form-control js-example-basic-single" name="country"
                                            id="country" required>
                                            <option value="Indonesia" selected>Indonesia</option>
                                        </select>
                                        <div class="invalid-feedback">Please provide a valid Country.</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Company Category *</label>
                                        <select class="form-control js-example-basic-single d-block w-100"
                                            name="company_category" id="company_category" required>
                                            <option value="">-- Select --</option>
                                            <option value="Coal Mining">Coal Mining</option>
                                            <option value="Minerals Producer">Minerals Producer</option>
                                            <option value="Supplier/Distributor/Manufacturer">Supplier / Distributor / Manufacturer</option>
                                            <option value="Contractor">Contractor</option>
                                            <option value="Association / Organization / Government">Association / Organization / Government</option>
                                            <option value="Financial Services">Financial Services</option>
                                            <option value="Technology">Technology</option>
                                            <option value="Investors">Investors</option>
                                            <option value="Logistics and Shipping">Logistics and Shipping</option>
                                            <option value="Media">Media</option>
                                            <option value="Consultant">Consultant</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a Company Category.</div>
                                    </div>
                                </div>
                                <div class="col-md-12 myDiv">
                                    <div class="form-group">
                                        <label class="form-label">Company Other *</label>
                                        <input type="text" class="form-control" name="company_other" placeholder="">
                                        <div class="invalid-feedback">Please enter your Company Other.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notice + Submit -->
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mt-2">
                            <div class="notice-text mb-3 mb-sm-0">
                                <div>Exclusive <strong>FREE</strong> Registration – Subject to Approval</div>
                                <div>Seats are limited and allocated on a first-come, first-served basis.</div>
                            </div>
                            <button class="btn btn-submit" type="submit">Claim My Free Registration</button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="page-footer">
                <p>&copy; Djakarta Mining Club</p>
                <a href="{{ url('/privacy') }}">Privacy</a> &middot; <a href="#">Terms</a>
            </div>

        </div>
    </div>

    <!-- Mobile collapsible -->
    <script>
        function toggleCollapsible(bodyId, fadeId, btnId) {
            var body = document.getElementById(bodyId);
            var fade = document.getElementById(fadeId);
            var btn  = document.getElementById(btnId);
            if (!body) return;

            var isExpanded = body.classList.contains('expanded');

            if (isExpanded) {
                body.classList.remove('expanded');
                fade.classList.remove('hidden');
                btn.classList.remove('expanded');
                btn.childNodes[0].textContent = 'See More ';
            } else {
                body.classList.add('expanded');
                fade.classList.add('hidden');
                btn.classList.add('expanded');
                btn.childNodes[0].textContent = 'See Less ';
            }
        }

        // On mobile: hide toggle button if content is short enough (no need to collapse)
        document.addEventListener('DOMContentLoaded', function () {
            if (window.innerWidth >= 768) return;

            [
                { body: 'descBody',    fade: 'descFade',    btn: 'descToggle' },
                { body: 'rundownBody', fade: 'rundownFade', btn: 'rundownToggle' }
            ].forEach(function (ids) {
                var body = document.getElementById(ids.body);
                var fade = document.getElementById(ids.fade);
                var btn  = document.getElementById(ids.btn);
                if (!body || !btn) return;

                // If actual content height ≤ collapsed max-height, no need for toggle
                if (body.scrollHeight <= 200) {
                    body.style.maxHeight = 'none';
                    if (fade) fade.style.display = 'none';
                    btn.style.display = 'none';
                }
            });
        });
    </script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });

        $(document).ready(function() {
            $('#company_category').on('change', function() {
                if ($(this).val() == 'other') {
                    $('.myDiv').css('display', 'grid');
                } else {
                    $('.myDiv').css('display', 'none');
                }
            });
        });
    </script>

    <script src="{{ asset('new-zoom/form-validation.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script>
        @if (session('alert'))
            swal({ text: "{{ session('alert') }}", icon: "success" });
        @elseif (session('error'))
            swal({ text: "{{ session('error') }}", icon: "error" });
        @endif

        const xhttp = new XMLHttpRequest();
        const select = document.getElementById("country");

        let country;

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                country = JSON.parse(xhttp.responseText);
                assignValues();
                handleCountryChange();
            }
        };
        xhttp.open("GET", "https://restcountries.com/v3.1/all?fields=name", true);
        xhttp.send();

        function assignValues() {
            country.forEach(c => {
                const option = document.createElement("option");
                option.value = c.name.common;
                option.textContent = c.name.common;
                select.appendChild(option);
            });
        }

        function handleCountryChange() {
            const countryData = country.find(c => select.value === c.name.common);
        }

        select.addEventListener("change", handleCountryChange.bind(this));
    </script>

    <script>
        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
            initialCountry: "id",
        });
    </script>

</body>

</html>
