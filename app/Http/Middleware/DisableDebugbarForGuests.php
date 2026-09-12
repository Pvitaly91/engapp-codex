<?php

namespace App\Http\Middleware;

use App\Support\SiteMode;
use Closure;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
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
        $debugbar = class_exists(Debugbar::class)
            ? Debugbar::class
            : 'Barryvdh\\Debugbar\\Facades\\Debugbar';
        if (class_exists($debugbar)) {
            $adminAuthenticated = $request->session()->get('admin_authenticated', false);

            if ($this->siteMode->isProduction($request) || ! $adminAuthenticated) {
                $debugbar::disable();
            } elseif ((bool) config('debugbar.enabled', false)) {
                $debugbar::enable();
            }
        }

        return $next($request);
    }
}
