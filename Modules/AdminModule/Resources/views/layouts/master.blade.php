{{-- Modules/AdminModule/Resources/views/layouts/master.blade.php --}}
@php
    use Illuminate\Support\Facades\Route;

    // Safe route helper: never crashes if a route doesn't exist yet
    $safeRoute = function ($name, $params = [], $fallback = 'javascript:void(0)') {
        try {
            return Route::has($name) ? route($name, $params) : $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $user = auth('web')->user();
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') - {{ config('app.name', '1Way') }}</title>

    {{-- Core vendor CSS (keep) --}}
    <link rel="stylesheet" href="{{ asset('assets/admin-module/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin-module/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin-module/css/style.css') }}">

    
    <link rel="stylesheet" href="{{ asset('assets/admin-module/css/custom.css') }}">
{{-- Optional vendor CSS used by dashboard selects/charts in admin-module --}}
    @if (file_exists(public_path('assets/admin-module/css/toastr.min.css')))
        <link rel="stylesheet" href="{{ asset('assets/admin-module/css/toastr.min.css') }}">
    @endif
    @if (file_exists(public_path('assets/admin-module/plugins/select2/select2.min.css')))
        <link rel="stylesheet" href="{{ asset('assets/admin-module/plugins/select2/select2.min.css') }}">
    @endif

    {{-- 1Way Theme --}}
    <link rel="stylesheet" href="{{ asset('assets/1way/1way-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin-module/css/1way-brand.css') }}"/>
    @include('adminmodule::layouts.css')

    @stack('css_or_js')
</head>

<body>

{{-- Global loader (admin JS expects this id sometimes) --}}
<div id="resource-loader" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.25);">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:14px 18px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
        Loading…
    </div>
</div>

<div class="oneway-app-shell d-flex">

    {{-- Sidebar --}}
    @include('adminmodule::partials._sidebar')

    {{-- Main --}}
    <div class="oneway-main flex-grow-1 d-flex flex-column">

        {{-- Top bar --}}
        <div class="oneway-topbar d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-2">
                <div class="fw-semibold">@yield('title', 'Dashboard')</div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="fw-semibold">{{ $user?->first_name ?? 'Admin' }}</div>
                    <div style="font-size:12px; opacity:.7;">Admin</div>
                </div>

                <a class="oneway-topbar__icon" href="{{ $safeRoute('admin.profile-settings') }}" title="Profile">
                    <i class="bi bi-person-circle"></i>
                </a>

                <a class="oneway-topbar__icon" href="{{ $safeRoute('admin.business.setting') }}" title="Settings">
                    <i class="bi bi-gear"></i>
                </a>

                <a class="oneway-topbar__icon" href="{{ $safeRoute('admin.auth.logout') }}" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Content --}}
        <div class="oneway-content flex-grow-1">
            @yield('content')
        </div>

    </div>
</div>

{{-- Core vendor JS --}}
<script src="{{ asset('assets/admin-module/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/admin-module/js/bootstrap.bundle.min.js') }}"></script>

{{-- Optional vendor JS used by admin-module views --}}
@if (file_exists(public_path('assets/admin-module/js/toastr.min.js')))
    <script src="{{ asset('assets/admin-module/js/toastr.min.js') }}"></script>
@endif

{!! Toastr::message() !!}

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof toastr !== 'undefined') {
                toastr.error({{ json_encode(session('error')) }});
            } else {
                alert('Error: ' + {{ json_encode(session('error')) }});
            }
        });
    </script>
@endif
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof toastr !== 'undefined') {
                toastr.success({{ json_encode(session('success')) }});
            }
        });
    </script>
@endif
@if (file_exists(public_path('assets/admin-module/plugins/select2/select2.min.js')))
    <script src="{{ asset('assets/admin-module/plugins/select2/select2.min.js') }}"></script>
@endif

{{-- Select2 init (safe) --}}
<script>
    (function () {
        try {
            if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
                jQuery(function () {
                    jQuery('.js-select').each(function () {
                        if (!jQuery(this).data('select2')) {
                            jQuery(this).select2({ width: '100%' });
                        }
                    });
                });
            }
        } catch (e) {}
    })();
</script>

{{-- SweetAlert2 (used by .form-alert / .form-alert-warning delete buttons) --}}
@if (file_exists(public_path('assets/admin-module/js/sweet_alert.js')))
    <script src="{{ asset('assets/admin-module/js/sweet_alert.js') }}"></script>
@endif

{{-- Global handlers for form-alert pattern used across admin views --}}
<script>
(function($) {
    if (!$ || !$.fn) return;

    $(document).on('click', '.form-alert', function() {
        var formId = $(this).data('id');
        var message = $(this).data('message') || 'Are you sure?';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function(result) {
                if (result.value) {
                    $('#' + formId).submit();
                }
            });
        } else if (confirm(message)) {
            $('#' + formId).submit();
        }
    });

    $(document).on('click', '.form-alert-warning', function() {
        var message = $(this).data('message') || 'This action is not allowed.';
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: message, type: 'warning' });
        } else {
            alert(message);
        }
    });

    if (typeof window.loadPartialView === 'undefined') {
        window.loadPartialView = function(url, target) {
            $.get(url).done(function(html) {
                $(target).html(html);
            }).fail(function() {
                $(target).html('<p class="text-muted p-3">Could not load data.</p>');
            });
        };
    }

    if (typeof window.setFilter === 'undefined') {
        window.setFilter = function(url, value, key) {
            var separator = url.indexOf('?') !== -1 ? '&' : '?';
            window.location.href = url + separator + key + '=' + encodeURIComponent(value);
        };
    }
})(window.jQuery);
</script>

@stack('script')

{{-- A3: Sidebar scroll persistence + active state --}}
<script>
(function() {
    var aside = document.querySelector('.oneway-aside__nav');
    if (!aside) return;
    var key = 'ow_sidebar_scroll';
    var saved = sessionStorage.getItem(key);
    if (saved) aside.scrollTop = parseInt(saved, 10);
    aside.addEventListener('scroll', function() {
        sessionStorage.setItem(key, aside.scrollTop);
    });

    // Ensure active link is visible
    var activeLink = aside.querySelector('.oneway-nav__link.active');
    if (activeLink) {
        setTimeout(function() { activeLink.scrollIntoView({block: 'nearest', behavior: 'auto'}); }, 50);
    }
})();
</script>
</body>
</html>
