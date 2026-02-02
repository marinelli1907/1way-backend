<?php

namespace Modules\AuthManagement\Http\Controllers\Web\New\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    protected string $redirectTo = '/admin';

    public function loginView()
    {
        if (auth()->check()) {
            return redirect($this->redirectTo);
        }

        return view('authmanagement::login', [
            'recaptcha_status' => 0,
            'recaptcha_site_key' => null,
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput();
        }

        $user = Auth::user();

        if (($user->is_active ?? 1) != 1 || ($user->is_temp_blocked ?? 0) == 1) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'User account has been disabled.',
            ]);
        }

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

        if (isset($user->failed_attempt)) $user->failed_attempt = 0;
        if (isset($user->is_temp_blocked)) $user->is_temp_blocked = 0;
        if (isset($user->blocked_at)) $user->blocked_at = null;
        $user->save();

        $request->session()->regenerate();

        return redirect()->intended($this->redirectTo);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.login');
    }
}
