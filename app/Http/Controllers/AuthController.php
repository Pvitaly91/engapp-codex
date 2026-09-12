<?php

namespace App\Http\Controllers;

use App\Models\User;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $debugbar = class_exists(Debugbar::class)
            ? Debugbar::class
            : 'Barryvdh\\Debugbar\\Facades\\Debugbar';
        if (class_exists($debugbar)) {
            $debugbar::disable();
        }

        if ($request->session()->get('admin_authenticated', false)) {
            return Redirect::route('admin.dashboard');
        }

        if ($request->hasCookie('admin_remember_token') && $request->cookie('admin_remember_token') === $this->rememberToken()) {
            $request->session()->put('admin_authenticated', true);
            $this->attachAdminUserToSession($request);

            return Redirect::route('admin.dashboard');
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
        $expectedPassword = config('admin.password');
        $expectedPasswordHash = config('admin.password_hash');

        if ($credentials['username'] !== $expectedUsername || ! $this->passwordMatches($credentials['password'], $expectedPassword, $expectedPasswordHash)) {
            return Redirect::back()
                ->withErrors(['username' => __('auth.failed')])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);
        $this->attachAdminUserToSession($request);

        if ($request->boolean('remember')) {
            Cookie::queue(
                Cookie::make(
                    'admin_remember_token',
                    $this->rememberToken(),
                    60 * 24 * 30,
                    null,
                    null,
                    false,
                    true,
                    false,
                    'lax'
                )
            );
        } else {
            Cookie::queue(Cookie::forget('admin_remember_token'));
        }

        return Redirect::intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget([
            'admin_authenticated',
            'admin_user_id',
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Cookie::queue(Cookie::forget('admin_remember_token'));

        return Redirect::route('login.show');
    }

    private function rememberToken(): string
    {
        return hash('sha256', config('admin.username').'|'.config('admin.password_signature'));
    }

    private function passwordMatches(string $candidate, ?string $expectedPassword, ?string $expectedPasswordHash): bool
    {
        if (is_string($expectedPassword) && $expectedPassword !== '') {
            return hash_equals($expectedPassword, $candidate);
        }

        return is_string($expectedPasswordHash)
            && $expectedPasswordHash !== ''
            && password_verify($candidate, $expectedPasswordHash);
    }

    private function attachAdminUserToSession(Request $request): void
    {
        $adminUser = $this->resolveAdminUser();
        if (! $adminUser) {
            return;
        }

        $request->session()->put('admin_user_id', $adminUser->id);
        Auth::login($adminUser);
    }

    private function resolveAdminUser(): ?User
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $candidates = array_values(array_filter(array_unique([
            trim((string) config('admin.user_email', '')),
            trim((string) config('admin.username', '')),
        ])));

        foreach ($candidates as $candidate) {
            $user = User::query()
                ->where('email', $candidate)
                ->orWhere('name', $candidate)
                ->first();

            if ($user) {
                return $user;
            }
        }

        $username = trim((string) config('admin.username', 'admin')) ?: 'admin';
        $email = $candidates[0] ?? $username;

        return User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $username,
                'password' => Str::random(40),
            ]
        );
    }
}
