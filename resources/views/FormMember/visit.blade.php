<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DMC – Booth Visitor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/css/intlTelInput.css" />

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
            padding: 26px 0 40px;
        }

        .page-inner {
            max-width: 860px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .page-header img {
            max-width: 200px;
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

        .card-shell {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .08);
        }

        .form-card {
            padding: 20px 24px 26px;
            border-top: 3px solid var(--dmc-red);
            border-radius: 18px;
        }

        .form-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .chip-step {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
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

        .form-section {
            border: 1px solid var(--dmc-border);
            border-radius: 12px;
            padding: 14px 16px 10px;
        }

        .form-section-title {
            font-size: .88rem;
            font-weight: 600;
            margin-bottom: 10px;
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
            box-shadow: 0 0 0 .15rem rgba(200, 16, 46, .16);
        }

        .btn-apply {
            border-radius: 999px;
            padding: .55rem 1.6rem;
            font-weight: 600;
            background: var(--dmc-red);
            border-color: var(--dmc-red);
        }

        .btn-apply:hover {
            background: var(--dmc-red-dark);
        }

        .iti {
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="page-wrapper">
        <div class="page-inner">

            <!-- HEADER -->
            <div class="page-header">
                <img src="{{ asset('image/dmc.png') }}">
                <h1 class="page-title">Booth <span>Visitor</span></h1>
            </div>

            <!-- FORM -->
            <div class="card-shell">
                <div class="form-card">

                    <div class="form-header-row">
                        <div class="chip-step"><span>1</span> Visitor Form</div>
                        <small class="text-muted">* Required</small>
                    </div>

                    <form action="{{ url('visit') }}" method="POST" class="needs-validation" novalidate>
                        @csrf

                        <div class="form-section">
                            <div class="form-section-title">Visitor Information</div>

                            <div class="row">
                                <!-- NAME -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name <small>*</small></label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                </div>

                                <!-- INSTITUTION -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Institution <small>*</small></label>
                                        <input type="text" name="institution" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-1">
                                        <label>Title <small>*</small></label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                </div>
                                <!-- EMAIL -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <small>*</small></label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>

                                <!-- PHONE -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone <small>*</small></label>
                                        <input type="tel" name="phone" id="phone" class="form-control"
                                            required>
                                    </div>
                                </div>

                                <!-- TITLE -->

                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary btn-apply" type="submit">
                                Submit
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        var phone = document.querySelector("#phone");
        if (phone) {
            window.intlTelInput(phone, {
                initialCountry: "id"
            });
        }
    </script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    @if (session('success'))
        <script>
            function getGiveawayResult() {
                const rand = Math.floor(Math.random() * 100) + 1;

                if (rand <= 45) {
                    return {
                        title: "Giveaway 🎁",
                        message: "Selamat! Anda mendapatkan  🥛 Gelas",
                        icon: "success"
                    };
                } else if (rand <= 90) {
                    return {
                        title: "Giveaway 🎁",
                        message: "Selamat! Anda mendapatkan  🥛 Gelas",
                        icon: "success"
                    };
                } else {
                    return {
                        title: "Giveaway 🎁",
                        message: "Selamat! Anda mendapatkan 🥛 Gelas",
                        icon: "warning"
                    };
                }
            }

            const giveaway = getGiveawayResult();

            // 1️⃣ SUCCESS — auto close
            swal({
                title: "Success 🎉",
                text: "{{ session('success') }}",
                icon: "success",
                buttons: false,
                timer: 1800,
                closeOnClickOutside: false,
                closeOnEsc: false,
            }).then(function() {

                // 2️⃣ GIVEAWAY — must click OK
                swal({
                    title: giveaway.title,
                    text: giveaway.message,
                    icon: giveaway.icon,
                    button: "OK",
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                }).then(function() {

                    // 3️⃣ reset form
                    document.querySelector('form').reset();

                    // reset intl-tel-input
                    if (window.intlTelInputGlobals) {
                        var phoneInput = document.querySelector("#phone");
                        if (phoneInput && phoneInput.intlTelInput) {
                            phoneInput.intlTelInput.setNumber("");
                        }
                    }
                });
            });
        </script>
    @endif



</body>

</html>
