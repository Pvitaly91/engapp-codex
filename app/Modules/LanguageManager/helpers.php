<?php

use App\Modules\LanguageManager\Services\LocaleService;

if (!function_exists('localized_route')) {
    /**
     * Generate a localized route URL.
     * 
     * @param string $name Route name
     * @param mixed $parameters Route parameters
     * @param bool $absolute Whether to generate an absolute URL
     * @param string|null $locale Locale code (uses current locale if null)
     * @return string
     */
    function localized_route(string $name, $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        return LocaleService::localizedRoute($name, $parameters, $absolute, $locale);
    }
}

if (!function_exists('current_locale')) {
    /**
     * Get the current locale.
     * 
     * @return string
     */
    function current_locale(): string
    {
        return LocaleService::getCurrentLocale();
    }
}

if (!function_exists('is_default_locale')) {
    /**
     * Check if the current locale is the default.
     * 
     * @return bool
     */
    function is_default_locale(): bool
    {
        return LocaleService::isDefaultLocale();
    }
}
