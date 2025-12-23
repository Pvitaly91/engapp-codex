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
    'middleware' => ['web', 'admin'],
    
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

// Generate localized URL
$url = LocaleService::localizedUrl('en', '/about');

// Get switch URL for current page
$switchUrl = LocaleService::switchLocaleUrl('en');

// Get switcher data
$data = LocaleService::getLanguageSwitcherData();
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

## Middleware

The `locale.url` middleware automatically:

1. Detects locale from URL prefix
2. Sets `app()->setLocale()` 
3. Stores in session/cookie (based on config)

## Publishing Assets

```bash
# Publish config
php artisan vendor:publish --tag=language-manager-config

# Publish views
php artisan vendor:publish --tag=language-manager-views

# Publish migrations
php artisan vendor:publish --tag=language-manager-migrations
```

## Requirements

- PHP 8.1+
- Laravel 10+

## License

MIT
