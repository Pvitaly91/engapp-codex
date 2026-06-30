<?php

namespace App\Http\Middleware;

use App\Support\SiteMode;
use Barryvdh\Debugbar\Facades\Debugbar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableDebugbarForGuests
{
    public function __construct(private SiteMode $siteMode) {}

    /**
     * Handle an incoming request.
     * Disable debugbar for non-authenticated users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debugbar is never available on a production host, even for admins.
        if (class_exists(Debugbar::class)) {
            $adminAuthenticated = $request->session()->get('admin_authenticated', false);

            if ($this->siteMode->isProduction($request) || ! $adminAuthenticated) {
                Debugbar::disable();
            } elseif ((bool) config('debugbar.enabled', false)) {
                Debugbar::enable();
            }
        }

        return $next($request);
    }
}
