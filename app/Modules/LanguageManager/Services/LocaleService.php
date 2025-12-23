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
     * Check if current locale is the default.
     */
    public static function isDefaultLocale(): bool
    {
        $default = self::getDefaultLanguage();
        
        return $default && $default->code === app()->getLocale();
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
        $default = self::getDefaultLanguage();

        // Remove existing locale prefix if present
        if (!empty($segments) && in_array($segments[0], $activeCodes)) {
            array_shift($segments);
        }

        // Add new locale prefix (except for default language)
        if ($default && $locale !== $default->code) {
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
}
