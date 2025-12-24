<?php

namespace App\Modules\LanguageManager\Http\Middleware;

use App\Modules\LanguageManager\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LocaleFromUrl
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if languages table doesn't exist yet
        if (!Schema::hasTable('languages')) {
            return $next($request);
        }

        $defaultLanguage = Language::getDefault();
        $activeCodes = Language::getActiveCodes();

        // No languages configured, use fallback
        if (empty($activeCodes) || !$defaultLanguage) {
            app()->setLocale(config('language-manager.fallback_locale', 'uk'));
            return $next($request);
        }

        // Get the first segment of the URL
        $segments = $request->segments();
        $firstSegment = $segments[0] ?? null;

        // Check if first segment is a language code
        if ($firstSegment && in_array($firstSegment, $activeCodes)) {
            // Set locale from URL prefix
            app()->setLocale($firstSegment);
            
            // Store in session/cookie
            $this->storeLocale($request, $firstSegment);
        } else {
            // No language prefix - use default language
            app()->setLocale($defaultLanguage->code);
            
            // Store in session/cookie
            $this->storeLocale($request, $defaultLanguage->code);
        }

        return $next($request);
    }

    /**
     * Store locale in session and/or cookie.
     */
    protected function storeLocale(Request $request, string $locale): void
    {
        $storeIn = config('language-manager.store_locale_in', 'both');

        if (in_array($storeIn, ['session', 'both'])) {
            session(['locale' => $locale]);
        }

        if (in_array($storeIn, ['cookie', 'both'])) {
            $lifetime = config('language-manager.cookie_lifetime', 525600);
            cookie()->queue(cookie('locale', $locale, $lifetime));
        }
    }
}
