<?php

namespace App\Http\Middleware;

use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableDebugbarForGuests
{
    /**
     * Handle an incoming request.
     * Disable debugbar for non-authenticated users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (class_exists(Debugbar::class) && ! $request->session()->get('admin_authenticated', false)) {
            Debugbar::disable();
        }

        return $next($request);
    }
}
