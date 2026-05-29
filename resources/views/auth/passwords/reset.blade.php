@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 mt-5">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <h4 class="font-weight-bold">Set Your Password</h4>
                        <p class="text-muted small">Create a password to activate your Djakarta Mining Club membership account.</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <label for="email" class="small font-weight-bold">Email Address</label>
                            <input id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ $email ?? old('email') }}"
                                required autocomplete="email" autofocus readonly
                                style="background-color:#f8f9fa;">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="small font-weight-bold">Password</label>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password"
                                required autocomplete="new-password"
                                placeholder="Minimum 8 characters">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password-confirm" class="small font-weight-bold">Confirm Password</label>
                            <input id="password-confirm" type="password"
                                class="form-control"
                                name="password_confirmation"
                                required autocomplete="new-password"
                                placeholder="Re-enter your password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mt-4" style="background-color:#0f1f3d; border-color:#0f1f3d;">
                            Set Your Password
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
