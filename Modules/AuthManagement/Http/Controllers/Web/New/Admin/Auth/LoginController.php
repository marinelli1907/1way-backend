<?php

namespace Modules\AuthManagement\Http\Controllers\Web\New\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mews\Captcha\Facades\Captcha;

class LoginController extends Controller
{
    /**
     * Where to redirect admins after login
     */
    protected string $redirectTo = '/admin';

    /**
     * Show admin login page
     */
    public function loginView()
    {
        if (auth()->check()) {
            return redirect($this->redirectTo);
        }

        $recaptchaStatus = 0;
        $recaptchaSiteKey = null;

        if (function_exists('businessConfig')) {
            $recaptcha = businessConfig('recaptcha')?->value ?? null;

            if (is_array($recaptcha)) {
                $recaptchaStatus  = (int) ($recaptcha['status'] ?? 0);
                $recaptchaSiteKey = $recaptcha['site_key'] ?? null;
            }
        }

        return view('authmanagement::login', [
            'recaptcha_status'   => $recaptchaStatus,
            'recaptcha_site_key' => $recaptchaSiteKey,
        ]);
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $recaptchaStatus = 0;
        $recaptchaSecret = null;

        if (function_exists('businessConfig')) {
            $recaptcha = businessConfig('recaptcha')?->value ?? null;

            if (is_array($recaptcha)) {
                $recaptchaStatus = (int) ($recaptcha['status'] ?? 0);
                $recaptchaSecret = $recaptcha['secret_key'] ?? null;
            }
        }

        // Base validation
        $rules = [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];

        // Captcha rules
        if ($recaptchaStatus === 1) {
            $rules['g-recaptcha-response'] = 'required';
        } else {
            // IMPORTANT: these must exist because we validate with Captcha::check_api()
            $rules['default_captcha_value'] = 'required|string';
            $rules['captcha_key'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        /**
         * Validate default captcha (API mode: value + key)
         */
        if ($recaptchaStatus !== 1) {
            $value = (string) $request->input('default_captcha_value');
            $key   = (string) $request->input('captcha_key');

            if (!Captcha::check_api($value, $key)) {
                return back()
                    ->withErrors(['default_captcha_value' => 'Captcha Failed. Please try again.'])
                    ->withInput();
            }
        }

        /**
         * Validate Google reCAPTCHA
         */
        if ($recaptchaStatus === 1) {
            if (!$recaptchaSecret) {
                return back()
                    ->withErrors(['g-recaptcha-response' => 'reCAPTCHA is enabled but secret key is missing.'])
                    ->withInput();
            }

            $response = Http::asForm()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret'   => $recaptchaSecret,
                    'response' => $request->get('g-recaptcha-response'),
                ]
            );

            if (!($response->json('success') ?? false)) {
                return back()
                    ->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed.'])
                    ->withInput();
            }
        }

        /**
         * Attempt login (default guard)
         */
        if (!Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput();
        }

        $user = Auth::user();

        /**
         * ACCOUNT SAFETY CHECKS
         */
        if (($user->is_active ?? 1) != 1 || ($user->is_temp_blocked ?? 0) == 1) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'User account has been disabled. Please contact the administrator.',
            ]);
        }

        /**
         * ROLE CHECK (Super Admin bypass)
         */
        if (($user->user_type ?? null) !== 'super-admin') {
            $roleActive = DB::table('roles')
                ->where('id', $user->role_id ?? null)
                ->value('is_active');

            if ($roleActive != 1) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Your assigned role is inactive.',
                ]);
            }
        }

        /**
         * Login success — reset flags
         */
        if (isset($user->failed_attempt)) $user->failed_attempt = 0;
        if (isset($user->is_temp_blocked)) $user->is_temp_blocked = 0;
        if (isset($user->blocked_at)) $user->blocked_at = null;
        $user->save();

        $request->session()->regenerate();

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Logout admin
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.login');
    }

    /**
     * Generate captcha (API JSON: { key, img, sensitive })
     */
    public function captcha($tmp)
    {
        return response()->json(Captcha::create('default', true));
    }
}
