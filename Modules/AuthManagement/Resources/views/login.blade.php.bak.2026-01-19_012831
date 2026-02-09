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
        .login-page { min-height: 100vh; display:flex; align-items:center; justify-content:center; background:#f8f9fa; }
        .login-box { width:100%; max-width:420px; padding:30px; border-radius:8px; box-shadow:0 0 20px rgba(0,0,0,0.1); background:#fff; }
        .captcha-img-box { cursor:pointer; }
        #captcha-image { height: 42px; display:block; }
    </style>
</head>
<body>

<div class="login-page">
    <div class="login-box">
        <h2 class="text-center mb-4">Admin Sign In</h2>

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
                <input type="email" name="email" id="email" class="form-control"
                       value="{{ old('email') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            {{-- CAPTCHA AREA --}}
            @if($recaptcha_status == 1 && $recaptcha_site_key)
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            @else
                {{-- This hidden field stores the captcha "key" from the JSON response --}}
                <input type="hidden" name="captcha_key" id="captcha_key" value="">

                <div class="mb-3">
                    <label for="default_captcha_value" class="form-label">Enter Captcha</label>
                    <div class="input-group">
                        <input type="text" name="default_captcha_value" id="default_captcha_value"
                               class="form-control" required autocomplete="off">
                        <span class="input-group-text p-0 captcha-img-box" onclick="refreshCaptcha()"
                              title="Click to refresh">
                            <img src="" alt="Captcha Image" id="captcha-image">
                        </span>
                    </div>
                    <small class="form-text text-muted">Click the image to refresh</small>
                </div>
            @endif

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>
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
                // data.img is the base64 image
                document.getElementById('captcha-image').src = data.img;
                // data.key is required for validation
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
