<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('staff.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt(array_merge($credentials, ['role' => 'user']), $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('attendance'));
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    public function authenticated(Request $request, $user)
    {
        if (!$user->last_login_at) {
            $user->last_login_at = now();
            $user->save();
            return redirect('/attendance');
        }

        return redirect('/attendance/list');
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect()->route('staff.login');
    }
}
