# Host-based site modes

The application selects its mode from the HTTP request host. `gramlyze.com` and
`www.gramlyze.com` are production hosts by default. Every other host is treated
as development.

Configure production hosts in `.env`:

```dotenv
SITE_PRODUCTION_DOMAINS=gramlyze.com,www.gramlyze.com,gramlyze.ub,*.production.example
SITE_PRODUCTION_ORIGIN=https://gramlyze.com
SITE_DEVELOPMENT_ORIGIN=http://engapp-codex.loc
SITE_PRODUCTION_LOCALES=uk
```

Exact hosts and `*.` wildcard subdomains are supported. Never read `env()`
directly outside `config/site-mode.php`, so the behavior remains compatible
with `php artisan config:cache`.

`SITE_PRODUCTION_LOCALES` is the production language allowlist. Add a language
by appending its code, for example `SITE_PRODUCTION_LOCALES=uk,en`. Development
hosts continue to expose all active languages. A disabled locale prefix is
redirected to the first production locale, and the language switcher only shows
allowed production languages.

## Development-only features

Enable feature names with:

```dotenv
SITE_DEV_FEATURES=mode-inspector,experimental-ui
```

Protect a route on both mode and feature name:

```php
Route::get('/dev/experiment', ExperimentController::class)
    ->middleware('site.dev:experimental-ui');
```

The route returns 404 on production hosts and when the feature is not enabled.
Use `site.dev` without a parameter when any development host may access it.

Blade templates can use:

```blade
@devMode
    <x-experimental-panel />
@enddevMode

@siteFeature('experimental-ui')
    <x-experimental-panel />
@endsiteFeature
```

Controllers and services can inject `App\Support\SiteMode` and call
`isProduction()`, `isDevelopment()`, or `featureEnabled()`.

## Caching

Development responses receive `no-store` and `noindex` headers. Anonymous HTML
responses on production hosts receive browser/proxy cache headers, except for
configured sensitive paths. Responses that set cookies are marked `private`.

```dotenv
SITE_PRODUCTION_RESPONSE_CACHE=true
SITE_PRODUCTION_RESPONSE_CACHE_TTL=300
SITE_PRODUCTION_CACHE_EXCEPT=admin,admin/*,login,logout,test,test/*,api,api/*,health
```

`/dev/site-mode` is a development-only diagnostics endpoint controlled by the
`mode-inspector` feature.

Coming Soon pages can be selectively opened on development hosts while staying
closed in production:

```dotenv
COMING_SOON_DEV_BYPASS_PREFIXES=/catalog/tests-cards
CATALOG_MAX_TESTS=100
```
