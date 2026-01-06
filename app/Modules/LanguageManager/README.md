# Language Manager Module

A portable Laravel module for managing site languages with URL prefix support.

## Features

- 🌐 Manage multiple languages from admin panel
- 🔗 URL prefix routing (default language at `/`, others at `/en/`, `/pl/`, etc.)
- 🔄 Easy language switching with URL-based locale detection
- 📦 Portable - easy to add to other Laravel projects
- 🎨 Multiple switcher styles (pills, dropdown, links, flags)

## Installation

### 1. Copy the Module

Copy the entire `app/Modules/LanguageManager` directory to your Laravel project.

### 2. Register the Service Provider

Add to `config/app.php` providers array:

```php
'providers' => [
    // ...
    App\Modules\LanguageManager\LanguageManagerServiceProvider::class,
],
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Configure Middleware (Optional)

For URL prefix detection, add the middleware to your routes:

```php
// routes/web.php
Route::middleware(['locale.url'])->group(function () {
    // Your localized routes
});
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=language-manager-config
```

### Config Options

```php
// config/language-manager.php

return [
    // Admin route prefix
    'route_prefix' => 'admin/languages',
    
    // Middleware for admin routes
    'middleware' => ['web', 'auth.admin'],
    
    // Fallback locale if no default set
    'fallback_locale' => 'uk',
    
    // Use URL prefixes for non-default languages
    'use_url_prefix' => true,
    
    // Where to store locale: 'session', 'cookie', or 'both'
    'store_locale_in' => 'both',
    
    // Cookie lifetime in minutes (default: 1 year)
    'cookie_lifetime' => 525600,
];
```

## Usage

### Admin Panel

Access the language management panel at `/admin/languages` (or your configured prefix).

### Language Switcher Component

Include in any Blade template:

```blade
{{-- Pills style (default) --}}
@include('language-manager::components.switcher')

{{-- Dropdown style --}}
@include('language-manager::components.switcher', ['style' => 'dropdown'])

{{-- Links style --}}
@include('language-manager::components.switcher', ['style' => 'links'])

{{-- Flags style --}}
@include('language-manager::components.switcher', ['style' => 'flags'])
```

### Blade Directives

```blade
{{-- Generate localized URL --}}
<a href="@localizedUrl('en', '/about')">About in English</a>

{{-- Switch locale URL for current page --}}
<a href="@switchLocaleUrl('en')">English</a>

{{-- Generate localized route (recommended) --}}
<a href="@localizedRoute('pages.show', ['slug' => 'about'])">About Page</a>
```

### Localized Routes

The module provides a `localized_route()` helper function that automatically adds the current locale prefix to route URLs:

```blade
{{-- Standard Laravel route() --}}
<a href="{{ route('catalog.tests-cards') }}">Tests</a>
{{-- Generates: /catalog/tests-cards (missing locale prefix!) --}}

{{-- Localized route (recommended) --}}
<a href="{{ localized_route('catalog.tests-cards') }}">Tests</a>
{{-- Generates: /pl/catalog/tests-cards (for Polish locale) --}}
{{-- Generates: /en/catalog/tests-cards (for English locale) --}}
{{-- Generates: /catalog/tests-cards (for default locale) --}}
```

**Parameters:**

```php
localized_route(
    string $name,           // Route name
    mixed $parameters = [], // Route parameters
    bool $absolute = true,  // Generate absolute URL
    ?string $locale = null  // Locale code (defaults to current locale)
)
```

**Examples:**

```blade
{{-- Simple route --}}
<a href="{{ localized_route('home') }}">Home</a>

{{-- Route with parameters --}}
<a href="{{ localized_route('pages.show', ['category' => 'grammar', 'page' => 'present-simple']) }}">
    Present Simple
</a>

{{-- Route with specific locale --}}
<a href="{{ localized_route('home', [], true, 'en') }}">English Home</a>

{{-- Relative URL --}}
<a href="{{ localized_route('catalog.tests-cards', [], false) }}">Tests</a>
```

**PHP Usage:**

```php
use App\Modules\LanguageManager\Services\LocaleService;

// Generate localized route
$url = LocaleService::localizedRoute('pages.show', ['slug' => 'about']);

// Or use the helper function
$url = localized_route('pages.show', ['slug' => 'about']);

// Use URL facade macro
$url = URL::localized('pages.show', ['slug' => 'about']);
```

### View Variables

These variables are automatically shared with all views:

```blade
{{-- All active languages --}}
@foreach($__languages as $language)
    {{ $language->code }} - {{ $language->native_name }}
@endforeach

{{-- Current locale --}}
{{ $__currentLocale }}

{{-- Language switcher data array --}}
@foreach($__languageSwitcher as $lang)
    <a href="{{ $lang['url'] }}" @if($lang['is_current']) class="active" @endif>
        {{ $lang['native_name'] }}
    </a>
@endforeach
```

### LocaleService

Use the service directly:

```php
use App\Modules\LanguageManager\Services\LocaleService;

// Get active languages
$languages = LocaleService::getActiveLanguages();

// Get default language
$default = LocaleService::getDefaultLanguage();

// Get current locale
$currentLocale = LocaleService::getCurrentLocale();

// Check if current locale is default
$isDefault = LocaleService::isDefaultLocale();

// Generate localized URL
$url = LocaleService::localizedUrl('en', '/about');

// Get switch URL for current page
$switchUrl = LocaleService::switchLocaleUrl('en');

// Generate localized route (recommended for routes)
$routeUrl = LocaleService::localizedRoute('pages.show', ['slug' => 'about']);

// Get switcher data
$data = LocaleService::getLanguageSwitcherData();
```

### Helper Functions

The module provides convenient helper functions:

```php
// Generate localized route
$url = localized_route('pages.show', ['slug' => 'about']);

// Get current locale
$locale = current_locale(); // e.g., 'uk', 'en', 'pl'

// Check if default locale
$isDefault = is_default_locale(); // true/false
```

### Model Methods

```php
use App\Modules\LanguageManager\Models\Language;

// Get default language
$default = Language::getDefault();

// Get all active languages
$active = Language::getActive();

// Get active codes
$codes = Language::getActiveCodes(); // ['uk', 'en', 'pl']

// Find by code
$english = Language::findByCode('en');

// Set as default
$language->setAsDefault();
```

## Route Structure

With the module configured:

| Language | Default | URL Example |
|----------|---------|-------------|
| Ukrainian | ✅ Yes | `/about`, `/contacts` |
| English | No | `/en/about`, `/en/contacts` |
| Polish | No | `/pl/about`, `/pl/contacts` |

**SEO-Friendly URL Schema:**
- Default language (Ukrainian) has NO prefix for clean, canonical URLs
- All other languages use their language code as prefix (e.g., `/pl/`, `/en/`)
- Languages are defined in the `languages` database table
- System automatically scales to support additional languages

## Middleware

The `SetLocale` middleware (registered in `app/Http/Middleware/SetLocale.php`) automatically:

1. **Detects locale from URL prefix** - First URL segment checked against active language codes
2. **URL is the single source of truth:**
   - If URL has prefix (e.g., `/pl/...`) → Uses that language
   - If URL has NO prefix (e.g., `/catalog`) → Uses default language, **ignoring session/cookie**
3. **Sets `app()->setLocale()`** - Makes locale available throughout the application
4. **Stores in session/cookie** - For consistency across requests

**Key Behavior:**
```php
// User visits /pl/catalog (Polish cookie set)
// Then visits /catalog (no prefix)
// Result: Ukrainian content (URL overrides cookie)
```

This ensures URLs are shareable and bookmarkable with predictable language behavior.

## Publishing Assets

```bash
# Publish config
php artisan vendor:publish --tag=language-manager-config

# Publish views
php artisan vendor:publish --tag=language-manager-views

# Publish migrations
php artisan vendor:publish --tag=language-manager-migrations
```

## Testing

For comprehensive testing instructions, see:
- **Manual Testing Guide:** `/MULTILINGUAL_URL_TESTING.md`
- **Implementation Summary:** `/MULTILINGUAL_URL_FIX.md`

Key test scenarios:
1. Default language pages (no prefix) generate links without prefix
2. Non-default language pages (with prefix) generate links with prefix
3. URL overrides session/cookie (visit `/pl/...` then `/...` shows default language)
4. Language switcher generates correct URLs
5. Database-driven language support (new languages work automatically)

## Requirements

- PHP 8.1+
- Laravel 10+

## License

MIT
