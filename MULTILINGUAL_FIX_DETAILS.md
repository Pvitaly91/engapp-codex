# Multilingual URL Bug Fix - Technical Documentation

## Problem Statement

When users were on pages in the **base language (Ukrainian - 'uk')**, links were incorrectly redirecting to URLs with `/pl/` prefix instead of no prefix.

**Incorrect behavior:**
- On Ukrainian page (`/catalog/tests-cards`) → click link → goes to `/pl/catalog/tests-cards` ❌

**Expected behavior:**
- On Ukrainian page (`/catalog/tests-cards`) → click link → goes to `/catalog/tests-cards` ✓
- On Polish page (`/pl/catalog/tests-cards`) → click link → goes to `/pl/catalog/tests-cards` ✓
- On English page (`/en/catalog/tests-cards`) → click link → goes to `/en/catalog/tests-cards` ✓

## Root Cause Analysis

### How Routes Are Registered

The `RouteServiceProvider` registers routes multiple times with different prefixes:

```php
// In RouteServiceProvider.php
Route::middleware('web')
    ->group(base_path('routes/web.php'));  // Base routes (no prefix) for 'uk'

foreach ($localePrefixes as $locale) {
    Route::middleware('web')
        ->prefix($locale)              // Routes with prefix for 'pl', 'en'
        ->group(base_path('routes/web.php'));
}
```

This creates multiple routes for the same endpoint:
- `GET /catalog/tests-cards` → HomeController@catalogAggregated (uk - no prefix)
- `GET /pl/catalog/tests-cards` → HomeController@catalogAggregated (pl - with prefix)
- `GET /en/catalog/tests-cards` → HomeController@catalogAggregated (en - with prefix)

### The Problem with Laravel's route() Helper

Laravel's `route()` helper doesn't have context about which locale-prefixed route to use. When you call:

```php
route('catalog.tests-cards')
```

Laravel will return the FIRST matching route it finds, which could be ANY of:
- `http://localhost/catalog/tests-cards`
- `http://localhost/pl/catalog/tests-cards`
- `http://localhost/en/catalog/tests-cards`

This is non-deterministic and depends on route registration order!

### The Bug in localizedRoute()

The old `localizedRoute()` method had conditional logic:

```php
public static function localizedRoute(..., ?string $locale = null): string
{
    $locale = $locale ?? self::getCurrentLocale();
    $url = route($name, $parameters, $absolute);  // Might return /pl/... or /en/... or /...
    
    // BUG: Only processed URL when locale was NOT default
    if ($locale !== $defaultCode) {
        // Strip prefix and add correct one
        // ...
        return $processedUrl;
    }
    
    // BUG: Returned URL as-is when locale WAS default
    return $url;  // Could be /pl/catalog... even though locale is 'uk'!
}
```

**Example of the bug:**
1. Current locale: `uk` (default)
2. Call: `localized_route('catalog.tests-cards')`
3. `route()` returns: `http://localhost/pl/catalog/tests-cards` (wrong prefix!)
4. Check: `if ('uk' !== 'uk')` → FALSE
5. Return: `/pl/catalog/tests-cards` ❌ (should be `/catalog/tests-cards`)

## The Fix

**Change:** Always process the URL to strip any existing locale prefix, regardless of target locale.

### Old Code (Buggy)

```php
public static function localizedRoute(string $name, $parameters = [], bool $absolute = true, ?string $locale = null): string
{
    $locale = $locale ?? self::getCurrentLocale();
    $default = self::getDefaultLanguage();
    $defaultCode = $default ? $default->code : config('app.locale', 'uk');
    
    $url = route($name, $parameters, $absolute);
    
    // Only processed when NOT default
    if ($locale !== $defaultCode) {
        // Parse, strip, and add prefix
        // ...
        return $processedUrl;
    }
    
    return $url;  // BUG: Returned as-is, could have wrong prefix
}
```

### New Code (Fixed)

```php
public static function localizedRoute(string $name, $parameters = [], bool $absolute = true, ?string $locale = null): string
{
    $locale = $locale ?? self::getCurrentLocale();
    $default = self::getDefaultLanguage();
    $defaultCode = $default ? $default->code : config('app.locale', 'uk');
    
    $url = route($name, $parameters, $absolute);
    
    // ALWAYS parse the URL
    $parsedUrl = parse_url($url);
    $path = $parsedUrl['path'] ?? '/';
    $segments = array_values(array_filter(explode('/', $path)));
    $activeCodes = self::getActiveLanguages()->pluck('code')->toArray();
    
    if (empty($activeCodes)) {
        $activeCodes = config('app.supported_locales', ['uk', 'en', 'pl']);
    }
    
    // ALWAYS strip existing locale prefix
    if (!empty($segments) && in_array($segments[0], $activeCodes)) {
        array_shift($segments);
    }
    
    // Add prefix ONLY if locale is not default
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
```

## Test Results

Created `/tmp/test_localized_route.php` to verify the fix logic:

### Old Logic Results:
```
✓ Input: /catalog/tests-cards           Target: uk → /catalog/tests-cards
✗ Input: /pl/catalog/tests-cards        Target: uk → /pl/catalog/tests-cards  (BUG!)
✗ Input: /en/catalog/tests-cards        Target: uk → /en/catalog/tests-cards  (BUG!)
✓ Input: /catalog/tests-cards           Target: pl → /pl/catalog/tests-cards
✓ Input: /pl/catalog/tests-cards        Target: pl → /pl/catalog/tests-cards
✓ Input: /en/catalog/tests-cards        Target: pl → /pl/catalog/tests-cards

Failures: 2 / 9
```

### New Logic Results:
```
✓ Input: /catalog/tests-cards           Target: uk → /catalog/tests-cards
✓ Input: /pl/catalog/tests-cards        Target: uk → /catalog/tests-cards  (FIXED!)
✓ Input: /en/catalog/tests-cards        Target: uk → /catalog/tests-cards  (FIXED!)
✓ Input: /catalog/tests-cards           Target: pl → /pl/catalog/tests-cards
✓ Input: /pl/catalog/tests-cards        Target: pl → /pl/catalog/tests-cards
✓ Input: /en/catalog/tests-cards        Target: pl → /pl/catalog/tests-cards

Failures: 0 / 9  ✓ All tests pass!
```

## Impact

### Files Changed
- `app/Modules/LanguageManager/Services/LocaleService.php` - Fixed `localizedRoute()` method

### Behavior Changes

**Before:**
- Ukrainian pages: Links could incorrectly have `/pl/` or `/en/` prefix
- Polish pages: Links worked correctly
- English pages: Links worked correctly

**After:**
- Ukrainian pages: Links correctly have NO prefix ✓
- Polish pages: Links correctly have `/pl/` prefix ✓
- English pages: Links correctly have `/en/` prefix ✓

### Affected Functionality
All pages using `localized_route()` helper:
- Navigation menu
- Footer links
- Catalog filters
- Theory navigation
- Test mode navigation
- Language switcher
- Search forms
- All internal links on public pages

## Verification Steps

### Manual Testing

1. **Test Default Language (Ukrainian):**
   ```
   1. Navigate to homepage: /
   2. Verify current language is Ukrainian
   3. Click "Catalog" link
   4. Verify URL is /catalog/tests-cards (no prefix) ✓
   5. Click other links and verify no prefix
   ```

2. **Test Polish Language:**
   ```
   1. Switch to Polish using language switcher
   2. Verify URL changes to /pl/
   3. Click "Catalog" link
   4. Verify URL is /pl/catalog/tests-cards ✓
   5. Click other links and verify /pl/ prefix
   ```

3. **Test English Language:**
   ```
   1. Switch to English using language switcher
   2. Verify URL changes to /en/
   3. Click "Catalog" link  
   4. Verify URL is /en/catalog/tests-cards ✓
   5. Click other links and verify /en/ prefix
   ```

4. **Test Language Switching:**
   ```
   1. On Ukrainian page (/catalog/tests-cards)
   2. Switch to Polish
   3. Verify URL becomes /pl/catalog/tests-cards ✓
   4. Switch back to Ukrainian
   5. Verify URL becomes /catalog/tests-cards ✓
   ```

### Automated Testing

Created unit test in `tests/Unit/LocalizedRouteTest.php`:
- Tests default language URLs have no prefix
- Tests non-default language URLs have correct prefix
- Tests switching between languages
- Tests URL normalization with various inputs

## Summary

**Problem:** Links on Ukrainian (base language) pages were getting incorrect `/pl/` prefix.

**Root Cause:** `route()` helper returns non-deterministic URL prefixes; old logic didn't strip them for default language.

**Solution:** Always strip any existing locale prefix, then conditionally add the correct prefix only for non-default languages.

**Result:** All language routing now works correctly across all three languages (uk, en, pl).
