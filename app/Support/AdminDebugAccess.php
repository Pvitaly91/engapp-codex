<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDebugAccess
{
    public static function allowed(?Request $request = null): bool
    {
        $request ??= request();

        $session = $request->hasSession() ? $request->session() : null;
        $sessionAdmin = $session !== null
            && (
                (bool) $session->get('admin_authenticated', false)
                || (bool) $session->get('admin_user_id', false)
            );
        $userAdmin = (bool) data_get(Auth::user(), 'is_admin', false);

        if ($sessionAdmin || $userAdmin) {
            return true;
        }

        if (! $session || ! self::hasValidRememberCookie($request)) {
            return false;
        }

        $session->put('admin_authenticated', true);

        return true;
    }

    private static function hasValidRememberCookie(Request $request): bool
    {
        $rememberToken = (string) $request->cookie('admin_remember_token', '');
        if ($rememberToken === '') {
            return false;
        }

        $expectedToken = hash('sha256', config('admin.username') . '|' . config('admin.password_hash'));

        return hash_equals($expectedToken, $rememberToken);
    }
}
