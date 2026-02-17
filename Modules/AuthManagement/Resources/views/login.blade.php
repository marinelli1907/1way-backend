<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Login - {{ config('app.name', 'Laravel') }}</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.css') }}">

    @if($recaptcha_status == 1 && $recaptcha_site_key)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha_site_key }}"></script>
    @endif

    <style>
        :root{
            --bg1:#070a12;
            --bg2:#0b1222;
            --card: rgba(255,255,255,0.06);
            --cardBorder: rgba(255,255,255,0.12);
            --textMuted: rgba(255,255,255,0.70);
        }

        body{
            background: radial-gradient(1200px 700px at 20% 20%, rgba(59,130,246,0.18), transparent 55%),
                        radial-gradient(900px 600px at 80% 70%, rgba(16,185,129,0.12), transparent 55%),
                        linear-gradient(180deg, var(--bg1), var(--bg2));
            min-height: 100vh;
        }

        .login-shell{
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .left-panel{
            display:none;
            padding: 56px 56px;
            border-right: 1px solid rgba(255,255,255,0.10);
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.00));
        }
        @media (min-width: 992px){
            .left-panel{ display:flex; flex-direction:column; justify-content:space-between; }
        }

        .brand-row{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .brand-logo{
            height: 44px;
            width: auto;
            display:block;
        }

        .brand-sub{
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.60);
        }
        .brand-title{
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
            margin-top: 2px;
        }

        .headline{
            font-size: 44px;
            font-weight: 800;
            line-height: 1.05;
            margin: 0;
        }
        .subhead{
            margin-top: 14px;
            color: var(--textMuted);
            max-width: 520px;
            font-size: 15px;
            line-height: 1.55;
        }

        .pill{
            display:inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.78);
            font-size: 12px;
            margin-right: 8px;
            margin-top: 10px;
        }

        .env-badge{
            padding: 6px 10px;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.70);
            font-size: 12px;
            white-space: nowrap;
        }

        .right-panel{
            flex: 1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 28px 18px;
        }

        .login-card{
            width:100%;
            max-width: 460px;
            padding: 28px;
            border-radius: 18px;
            background: var(--card);
            border: 1px solid var(--cardBorder);
            box-shadow: 0 30px 60px rgba(0,0,0,0.45);
            backdrop-filter: blur(10px);
        }

        .mobile-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom: 18px;
        }
        @media (min-width: 992px){
            .mobile-top{ display:none; }
        }
        .mobile-logo{
            height: 34px;
            width:auto;
        }

        .login-title{
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .login-subtitle{
            color: rgba(255,255,255,0.65);
            margin-bottom: 18px;
            font-size: 13px;
        }

        .form-label{ color: rgba(255,255,255,0.80); }
        .form-control{
            background: rgba(10,15,28,0.65);
            border: 1px solid rgba(255,255,255,0.14);
            color: #fff;
            border-radius: 12px;
            padding: 12px 14px;
        }
        .form-control:focus{
            background: rgba(10,15,28,0.75);
            border-color: rgba(255,255,255,0.24);
            box-shadow: 0 0 0 .2rem rgba(255,255,255,0.08);
            color:#fff;
        }

        .btn-primary{
            border-radius: 12px;
            padding: 12px 14px;
            font-weight: 700;
        }

        .captcha-img-box{ cursor:pointer; }
        #captcha-image{ height: 42px; display:block; }

        .muted-foot{
            margin-top: 16px;
            text-align:center;
            color: rgba(255,255,255,0.45);
            font-size: 12px;
        }

        .divider{
            height:1px;
            background: rgba(255,255,255,0.10);
            margin: 18px 0;
        }
    </style>
</head>

<body>

<div class="login-shell">

    {{-- LEFT PANEL --}}
    <div class="left-panel col-lg-6">
        <div>
            <div class="brand-row">
                <img src="{{ asset('images/1way-logo.png') }}" class="brand-logo" alt="1Way">
                <div>
                    <div class="brand-sub">1Way Admin</div>
                    <div class="brand-title">System Control</div>
                </div>
            </div>

            <div class="mt-5">
                <h1 class="headline">System Control Panel</h1>
                <p class="subhead">
                    Secure access for 1Way administrators only. Manage operations, dispatch, calendar events,
                    promotions, payouts, and platform health from one dashboard.
                </p>

                <div class="mt-2">
                    <span class="pill">Operations</span>
                    <span class="pill">Dispatch</span>
                    <span class="pill">Calendar</span>
                    <span class="pill">Promotions</span>
                    <span class="pill">Finance</span>
                    <span class="pill">AI</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between" style="color: rgba(255,255,255,0.45); font-size:12px;">
            <span>api.1wayride.com</span>
            <span class="env-badge">
                {{ app()->environment('production') ? 'Production' : ucfirst(app()->environment()) }}
            </span>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="right-panel col-lg-6">

        <div class="login-card">

            {{-- MOBILE HEADER --}}
            <div class="mobile-top">
                <img src="{{ asset('images/1way-logo.png') }}" class="mobile-logo" alt="1Way">
                <span class="env-badge">
                    {{ app()->environment('production') ? 'Production' : ucfirst(app()->environment()) }}
                </span>
            </div>

            <div class="login-title">🔒 Admin Sign In</div>
            <div class="login-subtitle">Authorized personnel only.</div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.auth.login') }}" id="admin-login-form">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@1wayride.com"
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                </div>

                {{-- CAPTCHA AREA --}}
                @if($recaptcha_status == 1 && $recaptcha_site_key)
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                @else
                    <input type="hidden" name="captcha_key" id="captcha_key" value="">

                    <div class="mb-3">
                        <label for="default_captcha_value" class="form-label">Enter Captcha</label>
                        <div class="input-group">
                            <input
                                type="text"
                                name="default_captcha_value"
                                id="default_captcha_value"
                                class="form-control"
                                required
                                autocomplete="off"
                                placeholder="Type the code"
                            >
                            <span class="input-group-text p-0 captcha-img-box" onclick="refreshCaptcha()" title="Click to refresh" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.14); border-left: 0; border-radius: 0 12px 12px 0;">
                                <img src="" alt="Captcha Image" id="captcha-image">
                            </span>
                        </div>
                        <small class="form-text" style="color: rgba(255,255,255,0.55);">Click the image to refresh</small>
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label class="form-check-label" for="remember" style="color: rgba(255,255,255,0.75);">Remember Me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Access Dashboard →</button>

                <div class="muted-foot">
                    If you experience access issues, contact system administration.
                </div>
            </form>
        </div>

    </div>
</div>

<script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/toastr.js') }}"></script>

<script>
    function refreshCaptcha() {
        fetch("{{ url('/admin/auth/code/captcha/default') }}", { cache: "no-store" })
            .then(r => r.json())
            .then(data => {
                document.getElementById('captcha-image').src = data.img;
                document.getElementById('captcha_key').value = data.key;
            })
            .catch(() => {
                toastr.error('Captcha failed to load. Refresh the page.');
            });
    }

    $(document).ready(function() {

        // Load captcha on page load (only if custom captcha mode)
        @if(!($recaptcha_status == 1 && $recaptcha_site_key))
            refreshCaptcha();
        @endif

        // reCAPTCHA flow
        @if($recaptcha_status == 1 && $recaptcha_site_key)
            $('#admin-login-form').on('submit', function(e) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ $recaptcha_site_key }}', {action: 'login'}).then(function(token) {
                        $('#g-recaptcha-response').val(token);
                        $('#admin-login-form').off('submit').submit();
                    });
                });
            });
        @endif

        @if(Session::has('error'))
            toastr.error('{{ Session::get('error') }}');
        @elseif(Session::has('success'))
            toastr.success('{{ Session::get('success') }}');
        @endif
    });
</script>

</body>
</html>
