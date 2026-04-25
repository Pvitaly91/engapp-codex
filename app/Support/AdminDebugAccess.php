<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDebugAccess
{
    public static function allowed(?Request $request = null): bool
    {
        $request ??= request();

        $sessionAdmin = $request->hasSession()
            && (bool) $request->session()->get('admin_authenticated', false);
        $userAdmin = (bool) data_get(Auth::user(), 'is_admin', false);

        return $sessionAdmin || $userAdmin;
    }
}
