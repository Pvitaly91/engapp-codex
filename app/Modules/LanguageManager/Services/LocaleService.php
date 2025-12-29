<?php

namespace App\Modules\LanguageManager\Services;

use App\Modules\LanguageManager\Models\Language;
use Illuminate\Support\Facades\Schema;

class LocaleService
{
    protected static ?array $cachedLanguages = null;
    protected static ?Language $cachedDefault = null;

    /**
     * Get all active languages.
     */
    public static function getActiveLanguages()
    {
        if (!Schema::hasTable('languages')) {
            return collect();
        }

        if (self::$cachedLanguages === null) {
            self::$cachedLanguages = Language::getActive()->all();
        }

        return collect(self::$cachedLanguages);
    }

    /**
     * Get default language.
     */
    public static function getDefaultLanguage(): ?Language
    {
        if (!Schema::hasTable('languages')) {
            return null;
        }

        if (self::$cachedDefault === null) {
            self::$cachedDefault = Language::getDefault();
        }

        return self::$cachedDefault;
    }

    /**
     * Get current locale code.
     */
    public static function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Resolve the default locale code for routing.
     *
     * Prioritizes the configured application locale when it is available
     * in the active language list to ensure the base language uses URLs
     * without a prefix. Falls back to the database default (if present)
     * or the configured locale.
     */
    public static function getDefaultLocaleCode(): string
    {
        $configDefault = config('app.locale', config('language-manager.fallback_locale', 'uk'));
        $activeCodes = [];

        if (Schema::hasTable('languages')) {
            $activeCodes = self::getActiveLanguages()->pluck('code')->toArray();
            $default = self::getDefaultLanguage();

            if (in_array($configDefault, $activeCodes, true)) {
                return $configDefault;
            }

            if ($default) {
                return $default->code;
            }

            if (!empty($activeCodes)) {
                return $activeCodes[0];
            }
        }

        return $configDefault;
    }

    /**
     * Check if current locale is the default.
     */
    public static function isDefaultLocale(): bool
    {
        return app()->getLocale() === self::getDefaultLocaleCode();
    }

    /**
     * Generate URL for a specific locale.
     */
    public static function localizedUrl(string $locale, ?string $url = null): string
    {
        $url = $url ?? request()->url();
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';

        // Get current segments
        $segments = array_values(array_filter(explode('/', $path)));
        $activeCodes = self::getActiveLanguages()->pluck('code')->toArray();
        $defaultCode = self::getDefaultLocaleCode();

        // Ensure the default locale is considered active for prefix stripping
        if (!in_array($defaultCode, $activeCodes, true)) {
            $activeCodes[] = $defaultCode;
        }

        // Remove existing locale prefix if present
        if (!empty($segments) && in_array($segments[0], $activeCodes)) {
            array_shift($segments);
        }

        // Add new locale prefix (except for default language)
        if ($locale !== $defaultCode) {
            array_unshift($segments, $locale);
        }

        $newPath = '/' . implode('/', $segments);
        
        // Rebuild URL
        $scheme = $parsedUrl['scheme'] ?? request()->getScheme();
        $host = $parsedUrl['host'] ?? request()->getHost();
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

        return "{$scheme}://{$host}{$newPath}{$query}";
    }

    /**
     * Generate URL for switching to a specific locale from current page.
     */
    public static function switchLocaleUrl(string $locale): string
    {
        return self::localizedUrl($locale, request()->fullUrl());
    }

    /**
     * Clear cached data (useful after language changes).
     */
    public static function clearCache(): void
    {
        self::$cachedLanguages = null;
        self::$cachedDefault = null;
    }

    /**
     * Get language switcher data for views.
     */
    public static function getLanguageSwitcherData(): array
    {
        $languages = self::getActiveLanguages();
        $currentLocale = self::getCurrentLocale();

        return $languages->map(function ($language) use ($currentLocale) {
            return [
                'code' => $language->code,
                'name' => $language->name,
                'native_name' => $language->native_name,
                'url' => self::switchLocaleUrl($language->code),
                'is_current' => $language->code === $currentLocale,
                'is_default' => $language->is_default,
            ];
        })->toArray();
    }

    /**
     * Generate a localized route URL.
     * 
     * @param string $name Route name
     * @param mixed $parameters Route parameters
     * @param bool $absolute Whether to generate an absolute URL
     * @param string|null $locale Locale code (uses current locale if null)
     * @return string
     */
    public static function localizedRoute(string $name, $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();
        $defaultCode = self::getDefaultLocaleCode();
        
        // Generate the base route URL
        $url = route($name, $parameters, $absolute);
        
        // If locale is not the default, add locale prefix
        if ($locale !== $defaultCode) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '/';
            
            // Get current segments
            $segments = array_values(array_filter(explode('/', $path)));
            $activeCodes = self::getActiveLanguages()->pluck('code')->toArray();
            
            // If no active languages from DB, use config
            if (empty($activeCodes)) {
                $activeCodes = config('app.supported_locales', ['uk', 'en', 'pl']);
            }

            // Make sure the default locale is present for prefix stripping
            if (!in_array($defaultCode, $activeCodes, true)) {
                $activeCodes[] = $defaultCode;
            }
            
            // Remove existing locale prefix if present
            if (!empty($segments) && in_array($segments[0], $activeCodes)) {
                array_shift($segments);
            }
            
            // Add locale prefix
            array_unshift($segments, $locale);
            
            $newPath = '/' . implode('/', $segments);
            
            // Rebuild URL
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $host = $parsedUrl['host'] ?? '';
            $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
            
            return $absolute ? "{$scheme}://{$host}{$newPath}{$query}" : $newPath . $query;
        }
        
        return $url;
    }
}
