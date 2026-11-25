<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMC Admin Login Verification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { max-width: 400px; width: 100%; }
        .login-header { background: #1a3a5c; color: #fff; text-align: center; padding: 24px; border-radius: 4px 4px 0 0; }
        .login-header img { max-height: 48px; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="login-card"><div class="card shadow-sm">
    <div class="login-header"><img src="{{ asset('image/logo-dmc-cci3.png') }}" alt="DMC"><h5>Verify your login</h5></div>
    <div class="card-body p-4">
        <p>Enter the six-digit code sent to <strong>{{ $email }}</strong>. It expires in {{ $minutes }} minutes.</p>
        @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
        <form method="POST" action="{{ route('cms.login.otp.verify') }}">
            @csrf
            <div class="form-group"><label for="otp">Verification code</label><input id="otp" name="otp" type="text" class="form-control" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus></div>
            <input type="hidden" name="remember" value="0">
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember', $remember) ? 'checked' : '' }}><label class="form-check-label" for="remember">Remember me on this device</label></div>
            <button type="submit" class="btn btn-primary btn-block">Verify and sign in</button>
        </form>
        <form method="POST" action="{{ route('cms.login.otp.resend') }}" class="mt-3">@csrf <button type="submit" class="btn btn-link p-0">Send a new code</button></form>
        <form method="POST" action="{{ route('cms.logout') }}">@csrf <button type="submit" class="btn btn-link p-0">Cancel</button></form>
    </div>
</div></div>
</body>
</html>
