<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;
        $supportedLocales = config('app.supported_locales', ['uk', 'en']);

        // First, check session
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }
        // Then, check cookie
        elseif ($request->cookie('locale')) {
            $locale = $request->cookie('locale');
        }

        // Validate locale
        if ($locale && in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
        } else {
            // Fallback to default config locale (uk)
            app()->setLocale(config('app.locale', 'uk'));
        }

        return $next($request);
    }
}
