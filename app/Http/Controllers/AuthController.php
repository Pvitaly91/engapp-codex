<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if ($request->session()->get('admin_authenticated', false)) {
            return Redirect::route('home');
        }

        return View::make('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        $validator = Validator::make($credentials, [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $expectedUsername = config('admin.username');
        $expectedPasswordHash = config('admin.password_hash');

        if ($credentials['username'] !== $expectedUsername || ! password_verify($credentials['password'], $expectedPasswordHash)) {
            return Redirect::back()
                ->withErrors(['username' => __('auth.failed')])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return Redirect::intended(route('home'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login.show');
    }
}
