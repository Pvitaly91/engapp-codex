<?php

namespace App\Http\Middleware;

use App\Support\AdminDebugAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminDebugAccess::allowed($request)) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('login.show');
        }

        return $next($request);
    }
}
