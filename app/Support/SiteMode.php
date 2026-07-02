<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteMode
{
    public const DEVELOPMENT = 'development';

    public const PRODUCTION = 'production';

    public function current(?Request $request = null): string
    {
        return $this->forHost(($request ?? request())->getHost());
    }

    public function forHost(string $host): string
    {
        $host = $this->normalizeHost($host);

        foreach ($this->productionDomains() as $productionDomain) {
            if ($this->hostMatches($host, $productionDomain)) {
                return self::PRODUCTION;
            }
        }

        return self::DEVELOPMENT;
    }

    public function isProduction(?Request $request = null): bool
    {
        return $this->current($request) === self::PRODUCTION;
    }

    public function isDevelopment(?Request $request = null): bool
    {
        return ! $this->isProduction($request);
    }

    public function featureEnabled(string $feature, ?Request $request = null): bool
    {
        $feature = Str::lower(trim($feature));

        return $feature !== ''
            && $this->isDevelopment($request)
            && in_array($feature, $this->developmentFeatures(), true);
    }

    public function localeAllowed(string $locale, ?Request $request = null): bool
    {
        return $this->isDevelopment($request)
            || in_array(Str::lower(trim($locale)), $this->productionLocales(), true);
    }

    public function defaultProductionLocale(): string
    {
        return $this->productionLocales()[0] ?? 'uk';
    }

    /** @return array<int, string> */
    public function availableLocales(array $locales, ?Request $request = null): array
    {
        $locales = collect($locales)
            ->map(fn (mixed $locale): string => Str::lower(trim((string) $locale)))
            ->filter()
            ->unique()
            ->values();

        if ($this->isDevelopment($request)) {
            return $locales->all();
        }

        $available = $locales->intersect($this->productionLocales())->values()->all();

        return $available !== [] ? $available : [$this->defaultProductionLocale()];
    }

    /** @return array<int, string> */
    public function availableFeatures(?Request $request = null): array
    {
        return $this->isDevelopment($request) ? $this->developmentFeatures() : [];
    }

    /** @return array<int, string> */
    public function productionDomains(): array
    {
        return collect(config('site-mode.production_domains', []))
            ->map(fn (mixed $domain): string => $this->normalizeHost((string) $domain, true))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public function developmentFeatures(): array
    {
        return collect(config('site-mode.development_features', []))
            ->map(fn (mixed $feature): string => Str::lower(trim((string) $feature)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public function productionLocales(): array
    {
        return collect(config('site-mode.production_locales', ['uk']))
            ->map(fn (mixed $locale): string => Str::lower(trim((string) $locale)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function hostMatches(string $host, string $productionDomain): bool
    {
        if (Str::startsWith($productionDomain, '*.')) {
            $suffix = Str::after($productionDomain, '*.');

            return $host !== $suffix && Str::endsWith($host, '.'.$suffix);
        }

        return hash_equals($productionDomain, $host);
    }

    private function normalizeHost(string $host, bool $preserveWildcard = false): string
    {
        $host = Str::lower(trim($host));
        $wildcard = $preserveWildcard && Str::startsWith($host, '*.') ? '*.' : '';

        if ($wildcard !== '') {
            $host = Str::after($host, '*.');
        }

        if (Str::contains($host, '://')) {
            $host = (string) parse_url($host, PHP_URL_HOST);
        }

        $host = trim(Str::before($host, ':'), " \t\n\r\0\x0B.");

        return $host === '' ? '' : $wildcard.$host;
    }
}
