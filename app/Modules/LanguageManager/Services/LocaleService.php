<?php

namespace App\Modules\LanguageManager\Services;

use App\Modules\LanguageManager\Models\Language;
use Illuminate\Support\Facades\Schema;

class LocaleService
{
    protected static ?array $cachedLanguages = null;
    protected static ?Language $cachedDefault = null;

    /**
     * Get the localized display name for a locale in the current app language.
     */
    protected static function getLocalizedLanguageName(string $code, ?string $fallback = null): string
    {
        $baseCode = strtolower(strtok($code, '-'));

        $translationKey = match ($baseCode) {
            'uk' => 'public.language.ukrainian',
            'en' => 'public.language.english',
            'pl' => 'public.language.polish',
            default => null,
        };

        if ($translationKey === null) {
            return $fallback ?: strtoupper($code);
        }

        $translated = __($translationKey);

        return $translated !== $translationKey
            ? $translated
            : ($fallback ?: strtoupper($code));
    }

    /**
     * Check whether the languages table can be queried safely.
     */
    public static function hasLanguagesTable(): bool
    {
        try {
            return Schema::hasTable('languages');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get configured supported locales with the default locale included.
     *
     * @return array<int, string>
     */
    public static function getConfiguredSupportedLocales(): array
    {
        $defaultLocale = self::getConfiguredDefaultLocale();
        $locales = config('app.supported_locales', [$defaultLocale]);

        if (! in_array($defaultLocale, $locales, true)) {
            $locales[] = $defaultLocale;
        }

        return array_values(array_unique(array_filter($locales)));
    }

    /**
     * Get the configured default locale.
     */
    public static function getConfiguredDefaultLocale(): string
    {
        return config('app.locale', config('language-manager.fallback_locale', 'uk'));
    }

    /**
     * Get all active languages.
     */
    public static function getActiveLanguages()
    {
        if (!self::hasLanguagesTable()) {
            return collect();
        }

        try {
            if (self::$cachedLanguages === null) {
                self::$cachedLanguages = Language::getActive()->all();
            }
        } catch (\Throwable $e) {
            self::$cachedLanguages = [];
        }

        return collect(self::$cachedLanguages);
    }

    /**
     * Get default language.
     */
    public static function getDefaultLanguage(): ?Language
    {
        if (!self::hasLanguagesTable()) {
            return null;
        }

        try {
            if (self::$cachedDefault === null) {
                self::$cachedDefault = Language::getDefault();
            }
        } catch (\Throwable $e) {
            self::$cachedDefault = null;
        }

        return self::$cachedDefault;
    }

    /**
     * Get the active locale codes, falling back to config if the database is unavailable.
     *
     * @return array<int, string>
     */
    public static function getSupportedLocaleCodes(): array
    {
        $codes = self::getActiveLanguages()
            ->pluck('code')
            ->filter()
            ->values()
            ->toArray();

        return $codes !== [] ? $codes : self::getConfiguredSupportedLocales();
    }

    /**
     * Get the default locale code, falling back to config if the database is unavailable.
     */
    public static function getDefaultLocaleCode(): string
    {
        return self::getDefaultLanguage()?->code ?? self::getConfiguredDefaultLocale();
    }

    /**
     * Get current locale code.
     */
    public static function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Check if current locale is the default.
     */
    public static function isDefaultLocale(): bool
    {
        return self::getDefaultLocaleCode() === app()->getLocale();
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
        $activeCodes = self::getSupportedLocaleCodes();
        $defaultCode = self::getDefaultLocaleCode();

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
            $fallbackName = $language->native_name ?: $language->name ?: strtoupper($language->code);

            return [
                'code' => $language->code,
                'name' => $language->name,
                'native_name' => $language->native_name,
                'localized_name' => self::getLocalizedLanguageName($language->code, $fallbackName),
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
        
        // Parse the URL to extract components
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';
        
        // Get current segments
        $segments = array_values(array_filter(explode('/', $path)));
        $activeCodes = self::getSupportedLocaleCodes();
        
        // Always remove existing locale prefix if present (even for default language)
        if (!empty($segments) && in_array($segments[0], $activeCodes)) {
            array_shift($segments);
        }

        // Admin area should never be localized
        if (!empty($segments) && $segments[0] === 'admin') {
            $newPath = '/' . implode('/', $segments);
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $host = $parsedUrl['host'] ?? '';
            $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

            return $absolute ? "{$scheme}://{$host}{$newPath}{$query}" : $newPath . $query;
        }
        
        // Add locale prefix only if locale is not the default
        if ($locale !== $defaultCode) {
            array_unshift($segments, $locale);
        }
        
        $newPath = '/' . implode('/', $segments);
        
        // Rebuild URL
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = $parsedUrl['host'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        
        return $absolute ? "{$scheme}://{$host}{$newPath}{$query}" : $newPath . $query;
    }
}
