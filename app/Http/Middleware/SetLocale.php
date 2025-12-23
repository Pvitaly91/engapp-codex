<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');

        if (! $locale) {
            $locale = $request->cookie('locale');
        }

        $locale = $locale ?: config('app.locale');

        app()->setLocale($locale);

        return $next($request);
    }
}
