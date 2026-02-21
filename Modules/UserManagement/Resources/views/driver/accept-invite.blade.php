<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1Way — Activate Your Driver Account</title>
    <link rel="stylesheet" href="{{ asset('assets/admin-module/css/bootstrap.min.css') }}">
    <style>
        body { background: #0A0E1A; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .invite-card { background: #fff; border-radius: 12px; padding: 2.5rem; max-width: 480px; width: 100%; }
        .logo-text { font-size: 2rem; font-weight: 900; color: #CC0000; letter-spacing: -1px; }
        .btn-brand { background: #CC0000; border: none; color: #fff; padding: .75rem 2rem; border-radius: 6px; font-size: 1rem; width: 100%; }
        .btn-brand:hover { background: #990000; color: #fff; }
    </style>
</head>
<body>
<div class="invite-card shadow-lg text-center">
    <div class="logo-text mb-3">1WAY</div>
    <h4 class="fw-bold mb-1">{{ translate('Activate Your Driver Account') }}</h4>
    <p class="text-muted mb-4">{{ translate('Welcome! Set your password to get started.') }}</p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('driver.invite.set-password') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token->token }}">

        <div class="mb-3 text-start">
            <label class="form-label fw-semibold">{{ translate('Email') }}</label>
            <input type="email" class="form-control" value="{{ $token->driver->email }}" readonly>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label fw-semibold">{{ translate('New Password') }} <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   required minlength="8" placeholder="{{ translate('At least 8 characters') }}">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4 text-start">
            <label class="form-label fw-semibold">{{ translate('Confirm Password') }} <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
        </div>

        <button type="submit" class="btn btn-brand">{{ translate('Activate Account') }}</button>
    </form>

    <p class="text-muted small mt-3 mb-0">{{ translate('This link expires') }} {{ $token->expires_at->diffForHumans() }}.</p>
</div>
</body>
</html>
