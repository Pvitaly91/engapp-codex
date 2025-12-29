# Multilingual URL Fix Summary

## Problem

The website is multilingual (Polish, Ukrainian, English) but had two related issues:

1. **Initial Problem:** All links on the site were pointing to Polish language URLs (`/pl/...`) regardless of the selected language.
2. **Secondary Problem (discovered during fix):** When accessing URLs without a language prefix (e.g., `/catalog/tests-cards`), the site was using the language preference stored in the session/cookie instead of the default language (Ukrainian). This caused:
   - Ukrainian pages to display English text if the user had previously visited an English page
   - Links to generate English URLs (`/en/...`) even though the current URL had no prefix

## Root Cause

### Initial Issue
The Laravel `route()` helper function generates routes without locale prefixes. When `route('catalog.tests-cards')` was called, it generated `/catalog/tests-cards` instead of `/pl/catalog/tests-cards`, `/uk/catalog/tests-cards`, or `/en/catalog/tests-cards`.

### Secondary Issue
The `SetLocale` middleware was reading from session/cookie when the URL had no language prefix, instead of treating URLs without prefix as explicitly requesting the default language. This violated the principle that URL structure should be the primary source of truth for language selection.

## Solution

### 1. Created `LocaleService::localizedRoute()` Method

Added a new method to `LocaleService` that:
- Takes a route name and parameters
- Generates the base route URL using Laravel's `route()` helper
- Adds the current locale prefix to the URL (except for the default language)
- Returns the properly prefixed URL

**File:** `app/Modules/LanguageManager/Services/LocaleService.php`

```php
public static function localizedRoute(
    string $name,
    $parameters = [],
    bool $absolute = true,
    ?string $locale = null
): string
```

### 2. Created `localized_route()` Helper Function

Added a global helper function for easy use in views and controllers.

**File:** `app/Modules/LanguageManager/helpers.php`

```php
function localized_route(
    string $name,
    $parameters = [],
    bool $absolute = true,
    ?string $locale = null
): string
```

### 3. Fixed SetLocale Middleware

**File:** `app/Http/Middleware/SetLocale.php`

Updated the middleware to properly handle URL-based locale detection:

**Before (incorrect):**
```php
if ($firstSegment && in_array($firstSegment, $supportedLocales)) {
    $locale = $firstSegment;
} else {
    // Would read from session/cookie, causing wrong language
    $preferredLocale = session('locale') ?? $request->cookie('locale');
    $locale = $preferredLocale ?: $defaultLocale;
}
```

**After (correct):**
```php
if ($firstSegment && in_array($firstSegment, $supportedLocales)) {
    // URL has locale prefix - use it
    $locale = $firstSegment;
} else {
    // No prefix means default language (ignore session/cookie)
    $locale = $defaultLocale;
}
// Always store the resolved locale for consistency
$this->storeLocale($locale);
```

**Key principle:** URL structure is the single source of truth for language selection:
- `/catalog/tests-cards` → Ukrainian (default)
- `/pl/catalog/tests-cards` → Polish  
- `/en/catalog/tests-cards` → English

### 4. Registered Blade Directive and URL Macro

In `LanguageManagerServiceProvider`:
- Registered `@localizedRoute` Blade directive
- Registered `URL::localized()` macro
- Loaded the helpers file in the `register()` method

### 4. Updated All Public Views

Replaced all instances of `route()` with `localized_route()` in:

- **Main Layout:**
  - `resources/views/layouts/engram.blade.php` - Navigation, footer, search form

- **Home & Search:**
  - `resources/views/home.blade.php` - Hero links, feature cards, search form
  - `resources/views/search/results.blade.php` - Search form

- **Catalog:**
  - `resources/views/engram/catalog-tests-cards-aggregated.blade.php` - Filter form, test links

- **Pages:**
  - `resources/views/engram/pages/partials/page-grid.blade.php` - Page links
  - `resources/views/engram/pages/partials/sidebar.blade.php` - Category and page navigation
  - `resources/views/engram/pages/show.blade.php` - Mobile navigation

- **Theory:**
  - All theory view files (category, index, show variants, partials)

- **Tests:**
  - Test V2 views (step, select, input variants)

- **Words:**
  - `resources/views/words/complete.blade.php` - Reset form

- **Components:**
  - `resources/views/components/related-test-card.blade.php` - Test links
  - `resources/views/components/test-mode-nav-v2.blade.php` - Mode navigation links

## How It Works

### Before (Broken)
```blade
<a href="{{ route('catalog.tests-cards') }}">Tests</a>
<!-- Generated: /catalog/tests-cards (no locale prefix!) -->
```

### After (Fixed)
```blade
<a href="{{ localized_route('catalog.tests-cards') }}">Tests</a>
<!-- Generated based on current locale:
     Ukrainian (default): /catalog/tests-cards
     Polish: /pl/catalog/tests-cards
     English: /en/catalog/tests-cards
-->
```

## URL Structure

| Locale | Is Default | Example URL |
|--------|-----------|-------------|
| Ukrainian (`uk`) | ✅ Yes | `/catalog/tests-cards` |
| Polish (`pl`) | No | `/pl/catalog/tests-cards` |
| English (`en`) | No | `/en/catalog/tests-cards` |

## Benefits

1. **Correct Language Routing:** All links now point to the correct language version
2. **Consistent User Experience:** Users stay on their selected language when navigating
3. **SEO-Friendly:** Each language has distinct URLs for search engines
4. **Easy to Use:** Simple helper function works just like `route()`
5. **Backward Compatible:** Existing admin routes remain unchanged

## Testing

To test the fix:

1. Navigate to the home page
2. Switch to Polish language using the language switcher
3. Click on any link (catalog, theory, tests)
4. Verify the URL includes `/pl/` prefix
5. Repeat for Ukrainian and English languages
6. Verify the default language (Ukrainian) has no prefix

## Documentation

Updated `app/Modules/LanguageManager/README.md` with comprehensive documentation about:
- The `localized_route()` helper function
- Usage examples in Blade templates
- PHP usage examples
- Available helper functions

## Files Modified

### Core Logic
- `app/Http/Middleware/SetLocale.php` - **Fixed to use default locale for URLs without prefix**
- `app/Modules/LanguageManager/Services/LocaleService.php` - Added `localizedRoute()` method
- `app/Modules/LanguageManager/LanguageManagerServiceProvider.php` - Registered helpers, directive, and macro
- `app/Modules/LanguageManager/helpers.php` - New file with helper functions
- `app/Modules/LanguageManager/README.md` - Updated documentation

### Views (21 files)
- Layout: `layouts/engram.blade.php`
- Home: `home.blade.php`
- Search: `search/results.blade.php`
- Catalog: `engram/catalog-tests-cards-aggregated.blade.php`, `engram/catalog-tests-cards.blade.php`
- Pages: `engram/pages/partials/*.blade.php`, `engram/pages/show.blade.php`
- Theory: All theory view files (8 files)
- Tests: Test V2 views (5 files)
- Words: `words/complete.blade.php`
- Components: `components/related-test-card.blade.php`, `components/test-mode-nav-v2.blade.php`, and others

## Key Behaviors

### URL Structure Determines Language
- **`/catalog/tests-cards`** → Ukrainian (default) - no prefix means default
- **`/pl/catalog/tests-cards`** → Polish - explicit language prefix
- **`/en/catalog/tests-cards`** → English - explicit language prefix

### Language Persistence
- Language is stored in session/cookie when visiting any page
- BUT: The URL structure always takes priority over stored preferences
- This ensures URLs are shareable and bookmarkable with consistent behavior

### Why This Matters
Before the fix, a user could:
1. Visit `/en/catalog/tests-cards` (English version)
2. Then visit `/catalog/tests-cards` (expecting Ukrainian)
3. But see English content because session/cookie was used

After the fix:
1. Visit `/en/catalog/tests-cards` → English content
2. Visit `/catalog/tests-cards` → Ukrainian content (correctly ignores session)
3. URLs are now self-documenting and predictable

## Future Improvements

1. Consider updating admin routes to use localized routing (if needed)
2. Add tests to ensure localized routing works correctly
3. Consider caching language data for better performance
4. Add route model binding support for localized routes
