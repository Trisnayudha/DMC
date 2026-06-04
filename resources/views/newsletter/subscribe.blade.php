<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to DMC Newsletter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .subscribe-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.08);
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
        }

        .dmc-logo {
            height: 56px;
            object-fit: contain;
        }

        .subscribe-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-top: 24px;
            margin-bottom: 8px;
        }

        .subscribe-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 32px;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #374151;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 10px 14px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #c8a96e;
            box-shadow: 0 0 0 3px rgba(200, 169, 110, 0.15);
        }

        .btn-subscribe {
            background: #c8a96e;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            padding: 12px;
            width: 100%;
            transition: background 0.2s;
        }

        .btn-subscribe:hover {
            background: #b5954d;
            color: #fff;
        }

        .privacy-note {
            font-size: 0.8rem;
            color: #9ca3af;
            text-align: center;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="subscribe-card">
        <div class="text-center">
            <img src="{{ asset('image/logo-dmc-cci2.png') }}" alt="DMC Logo" class="dmc-logo">
            <h1 class="subscribe-title">Stay in the Loop</h1>
            <p class="subscribe-subtitle">
                Get the latest news, events, and insights from Djakarta Mining Club delivered to your inbox.
            </p>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-3 d-flex align-items-center gap-2" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-circle-fill flex-shrink-0" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info rounded-3" role="alert">
                {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-3" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('newsletter.subscribe') }}" method="POST" novalidate>
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                           value="{{ old('first_name') }}" placeholder="John">
                </div>
                <div class="col-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                           value="{{ old('last_name') }}" placeholder="Doe">
                </div>
            </div>
            <div class="mb-4">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       placeholder="you@example.com" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-subscribe">
                Subscribe Now
            </button>
        </form>

        <p class="privacy-note">
            We respect your privacy. Unsubscribe at any time.
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
