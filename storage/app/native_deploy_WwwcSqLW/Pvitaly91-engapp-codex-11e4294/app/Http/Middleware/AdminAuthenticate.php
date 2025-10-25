<?php

namespace App\Http\Middleware;

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
        if (! $request->session()->get('admin_authenticated', false)) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('login.show');
        }

        return $next($request);
    }
}
