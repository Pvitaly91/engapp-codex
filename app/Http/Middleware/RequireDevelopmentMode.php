<?php

namespace App\Http\Middleware;

use App\Support\SiteMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDevelopmentMode
{
    public function __construct(private SiteMode $siteMode) {}

    public function handle(Request $request, Closure $next, ?string $feature = null): Response
    {
        abort_unless($this->siteMode->isDevelopment($request), 404);

        if ($feature !== null && $feature !== '') {
            abort_unless($this->siteMode->featureEnabled($feature, $request), 404);
        }

        return $next($request);
    }
}
