<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMC Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { max-width: 400px; width: 100%; }
        .login-header { background: #1a3a5c; color: #fff; text-align: center; padding: 24px; border-radius: 4px 4px 0 0; }
        .login-header img { max-height: 48px; margin-bottom: 8px; }
        .login-header h5 { margin: 0; font-weight: 600; letter-spacing: .5px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card shadow-sm">
            <div class="login-header">
                <img src="{{ asset('image/logo-dmc-cci3.png') }}" alt="DMC">
                <h5>Admin Panel</h5>
            </div>
            <div class="card-body p-4">

                @if (session('error'))
                    <div class="alert alert-danger py-2" style="font-size:13px;">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('cms.login.submit') }}">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required autofocus placeholder="admin@dmc.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            required placeholder="••••••••">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="remember" class="custom-control-input" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="custom-control-label small" for="remember">Remember me</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
