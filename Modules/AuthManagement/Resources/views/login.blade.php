<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login – 1Way</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.css') }}">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(900px 600px at 20% 10%, rgba(53,194,255,.12), transparent 60%),
                radial-gradient(900px 600px at 80% 85%, rgba(42,242,197,.10), transparent 60%),
                linear-gradient(180deg, #0b1f3a, #071425);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 22px;
            padding: 44px 44px 36px;
            box-shadow: 0 40px 100px rgba(0,0,0,.45);
        }

        .logo-wrap {
            background: #ffffff; /* match logo white */
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 16px;
            margin: 0 auto 22px;
            width: fit-content;
        }

        .login-logo {
            display: block;
            height: 110px; /* BIG LOGO */
            width: auto;
        }

        h1 {
            font-size: 26px;
            font-weight: 800;
            text-align: center;
            margin: 0 0 6px;
            color: #0b1f3a;
            letter-spacing: -0.02em;
        }

        .login-sub {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 28px;
        }

        /* Force perfect alignment */
        .form-shell {
            max-width: 340px;
            margin: 0 auto;
        }

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: #0b1f3a;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            border-radius: 12px;
            padding: 12px 12px;
            border: 1px solid #dbe4f0;
        }

        .form-control:focus {
            border-color: #0b1f3a;
            box-shadow: 0 0 0 2px rgba(11,31,58,.12);
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .remember-row input {
            margin-top: 0;
        }

        .remember-row label {
            margin: 0;
            font-size: 13px;
            color: #475569;
        }

        .btn-login {
            width: 100%;
            border-radius: 14px;
            padding: 12px 14px;
            font-weight: 700;
            background: #0b1f3a; /* match page background */
            border: none;
            color: #ffffff;
        }

        .btn-login:hover {
            background: #102a4d;
        }

        .tip {
            margin-top: 18px;
            font-size: 12px;
            text-align: center;
            color: #94a3b8;
        }
    </style>
</head>

<body>

<div class="login-card">

    {{-- LOGO (this path matches what exists on server: storage/app/public/business/1way-logo.png) --}}
    <div class="logo-wrap">
        <img
            src="{{ asset('storage/business/1way-logo.png') }}"
            alt="1Way"
            class="login-logo"
        >
    </div>

    <h1>Control Center</h1>
    <div class="login-sub">Secure access to operations, drivers, rides, and payments.</div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-shell">
        <form method="POST" action="{{ route('admin.auth.login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
            </div>

            <div class="mb-2">
                <label class="form-label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    required
                >
            </div>

            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

    <div class="tip">If the screen appears blank, clear cache and refresh.</div>
</div>

<script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/toastr.js') }}"></script>

<script>
@if(Session::has('error'))
    toastr.error(@json(Session::get('error')));
@elseif(Session::has('success'))
    toastr.success(@json(Session::get('success')));
@endif
</script>

</body>
</html>
