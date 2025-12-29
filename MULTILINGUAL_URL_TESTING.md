# Multilingual URL Testing Guide

## Overview

This guide describes how to manually test the SEO-friendly multilingual URL routing implementation.

## URL Structure

The system implements a SEO-friendly URL schema where:

| Language | Is Default | URL Pattern | Example |
|----------|-----------|-------------|---------|
| Ukrainian (uk) | ✅ Yes | `/{path}` | `/catalog/tests-cards` |
| Polish (pl) | No | `/pl/{path}` | `/pl/catalog/tests-cards` |
| English (en) | No | `/en/{path}` | `/en/catalog/tests-cards` |

**Key Principle: URL is the single source of truth**
- URLs **without** prefix → Ukrainian (default language)
- URLs **with** `/pl/` prefix → Polish
- URLs **with** `/en/` prefix → English

## Implementation Details

### A. Routing (RouteServiceProvider)

Routes are registered twice:
1. Once without prefix (for default language - uk)
2. Once for each non-default active language with prefix (`/pl`, `/en`, etc.)

**File:** `app/Providers/RouteServiceProvider.php`
- Method: `registerLocalizedRoutes()`
- Reads active languages from `languages` table
- Registers route groups with locale prefix for non-default languages

### B. Locale Detection (SetLocale Middleware)

The middleware runs on every request and:
1. Checks the first URL segment
2. If it matches an active language code → sets that language
3. If NO prefix → sets default language (uk), **ignoring** session/cookie
4. Stores the resolved language in session and cookie

**File:** `app/Http/Middleware/SetLocale.php`

**Critical Logic:**
```php
if ($firstSegment && in_array($firstSegment, $supportedLocales)) {
    // URL has prefix - use that language
    $locale = $firstSegment;
} else {
    // NO prefix - use default language (ignore session/cookie)
    $locale = $defaultLocale;
}
```

### C. Link Generation

**Never use `route()` directly in public views!**

Use `localized_route()` instead:

```blade
{{-- ❌ WRONG - generates URLs without locale prefix --}}
<a href="{{ route('catalog.tests-cards') }}">Tests</a>

{{-- ✅ CORRECT - adds locale prefix when needed --}}
<a href="{{ localized_route('catalog.tests-cards') }}">Tests</a>
```

**File:** `app/Modules/LanguageManager/Services/LocaleService.php`
- Method: `localizedRoute()`
- Generates base URL using `route()`
- Adds locale prefix ONLY if current locale ≠ default

**Helper:** `app/Modules/LanguageManager/helpers.php`
- Function: `localized_route()`

## Manual Testing Checklist

### Test 1: Default Language (Ukrainian) Pages

**Goal:** Verify Ukrainian pages have NO prefix and generate links without prefix

1. Open browser in incognito/private mode (clean cookies)
2. Navigate to: `http://your-domain/catalog/tests-cards`
3. ✅ **Verify:** URL has NO prefix (no `/uk/`)
4. ✅ **Verify:** Page displays in Ukrainian
5. ✅ **Verify:** All navigation links have NO prefix:
   - Home link → `/`
   - Catalog link → `/catalog/tests-cards`
   - Theory link → `/theory`
   - Test links → `/test/{slug}`
6. ✅ **Verify:** Clicking links stays on Ukrainian pages without prefix

### Test 2: Polish Language Pages

**Goal:** Verify Polish pages have `/pl/` prefix and generate links with prefix

1. Open browser in incognito/private mode (clean cookies)
2. Navigate to: `http://your-domain/pl/catalog/tests-cards`
3. ✅ **Verify:** URL has `/pl/` prefix
4. ✅ **Verify:** Page displays in Polish
5. ✅ **Verify:** All navigation links have `/pl/` prefix:
   - Home link → `/pl/`
   - Catalog link → `/pl/catalog/tests-cards`
   - Theory link → `/pl/theory`
   - Test links → `/pl/test/{slug}`
6. ✅ **Verify:** Clicking links stays on Polish pages with prefix

### Test 3: English Language Pages

**Goal:** Verify English pages have `/en/` prefix and generate links with prefix

1. Open browser in incognito/private mode (clean cookies)
2. Navigate to: `http://your-domain/en/theory`
3. ✅ **Verify:** URL has `/en/` prefix
4. ✅ **Verify:** Page displays in English
5. ✅ **Verify:** All navigation links have `/en/` prefix:
   - Home link → `/en/`
   - Catalog link → `/en/catalog/tests-cards`
   - Theory link → `/en/theory`
   - Test links → `/en/test/{slug}`
6. ✅ **Verify:** Clicking links stays on English pages with prefix

### Test 4: Cookie Override (Critical Test)

**Goal:** Verify URL overrides cookie/session preferences

**This is the main bug fix - URL must be the single source of truth!**

1. Open browser in incognito/private mode
2. Navigate to: `http://your-domain/pl/catalog/tests-cards`
3. ✅ **Verify:** Page is in Polish with `/pl/` prefix
4. ✅ **Verify:** Cookie is set to `pl` (check browser DevTools → Application → Cookies)
5. **Manually change URL** to: `http://your-domain/catalog/tests-cards` (remove `/pl/`)
6. ✅ **Verify:** Page switches to Ukrainian (ignoring Polish cookie)
7. ✅ **Verify:** All links are now WITHOUT prefix (not `/pl/`)
8. ✅ **Verify:** Cookie is updated to `uk`

**Why this matters:** Before the fix, step 6-7 would show Ukrainian content but links would still be `/pl/...` because it was reading from cookie instead of URL.

### Test 5: Language Switcher

**Goal:** Verify language switcher generates correct URLs

1. Navigate to: `http://your-domain/catalog/tests-cards`
2. Open language switcher dropdown
3. ✅ **Verify:** Current language shows as Ukrainian
4. ✅ **Verify:** Polish option links to `/pl/catalog/tests-cards`
5. ✅ **Verify:** English option links to `/en/catalog/tests-cards`
6. Click Polish language
7. ✅ **Verify:** Redirects to `/pl/catalog/tests-cards`
8. Open language switcher dropdown again
9. ✅ **Verify:** Ukrainian option links to `/catalog/tests-cards` (no prefix)

### Test 6: Direct URL Access

**Goal:** Verify different URL patterns work correctly

Test these URLs directly:

| URL | Expected Locale | Expected Content Language |
|-----|----------------|--------------------------|
| `/` | uk | Ukrainian |
| `/pl/` | pl | Polish |
| `/en/` | en | English |
| `/catalog/tests-cards` | uk | Ukrainian |
| `/pl/catalog/tests-cards` | pl | Polish |
| `/en/catalog/tests-cards` | en | English |
| `/theory` | uk | Ukrainian |
| `/pl/theory` | pl | Polish |
| `/test/some-slug` | uk | Ukrainian |
| `/pl/test/some-slug` | pl | Polish |

### Test 7: Test V2 Pages

**Goal:** Verify test pages generate correct localized links

1. Navigate to: `http://your-domain/test/some-test-slug`
2. ✅ **Verify:** URL has NO prefix (Ukrainian default)
3. ✅ **Verify:** Mode navigation links have NO prefix:
   - "All questions" → `/test/{slug}`
   - "Step by step" → `/test/{slug}/step`
   - etc.
4. Navigate to: `http://your-domain/pl/test/some-test-slug`
5. ✅ **Verify:** URL has `/pl/` prefix
6. ✅ **Verify:** Mode navigation links have `/pl/` prefix

### Test 8: Pages and Theory Sections

**Goal:** Verify category and page navigation works correctly

1. Navigate to: `http://your-domain/theory`
2. Click on a category
3. ✅ **Verify:** URL is `/theory/{category}` (no prefix)
4. Click on a theory page
5. ✅ **Verify:** URL is `/theory/{category}/{page}` (no prefix)
6. Navigate to: `http://your-domain/pl/theory`
7. Click on a category
8. ✅ **Verify:** URL is `/pl/theory/{category}`
9. Click on a theory page
10. ✅ **Verify:** URL is `/pl/theory/{category}/{page}`

### Test 9: Search Functionality

**Goal:** Verify search form and results use correct locale

1. Navigate to: `http://your-domain/catalog/tests-cards`
2. Use the search box in header
3. ✅ **Verify:** Search form action is `/search` (no prefix)
4. Navigate to: `http://your-domain/pl/catalog/tests-cards`
5. Use the search box
6. ✅ **Verify:** Search form action is `/pl/search`

### Test 10: Database-Driven Languages

**Goal:** Verify system reads languages from database

1. Access database and check `languages` table
2. ✅ **Verify:** Contains records for uk, pl, en
3. ✅ **Verify:** uk has `is_default = 1`
4. ✅ **Verify:** All three have `is_active = 1`
5. In application, add a new language (e.g., 'de' for German) via admin panel
6. Set `is_active = 1` for the new language
7. ✅ **Verify:** Routes are registered with `/de/` prefix
8. ✅ **Verify:** Can access `/de/catalog/tests-cards`
9. ✅ **Verify:** Language switcher shows German option

## Common Issues and Solutions

### Issue 1: Links have wrong locale prefix

**Symptom:** On Ukrainian page, links show `/pl/...`

**Cause:** View is using `route()` instead of `localized_route()`

**Solution:** Replace all `route()` with `localized_route()` in public views

### Issue 2: URL without prefix shows wrong language

**Symptom:** Visiting `/catalog/tests-cards` shows Polish content

**Cause:** SetLocale middleware using session/cookie instead of URL

**Solution:** Verify SetLocale middleware logic - no prefix must always = default language

### Issue 3: Routes not found for `/pl/...` or `/en/...`

**Symptom:** 404 error for `/pl/catalog/tests-cards`

**Cause:** RouteServiceProvider not registering localized routes

**Solution:** 
- Check `languages` table exists
- Verify languages are marked as active
- Check RouteServiceProvider::registerLocalizedRoutes()

### Issue 4: Default language shows `/uk/` prefix

**Symptom:** URLs like `/uk/catalog/tests-cards` instead of `/catalog/tests-cards`

**Cause:** LocaleService::localizedRoute() adding prefix for default language

**Solution:** Verify `is_default` flag in database and logic in localizedRoute() method

## Developer Notes

### Where localized_route() is Used

Public views (all should use localized_route()):
- `resources/views/layouts/engram.blade.php` - Main navigation
- `resources/views/home.blade.php` - Hero links, feature cards
- `resources/views/engram/catalog-tests-cards-aggregated.blade.php` - Test catalog
- `resources/views/engram/pages/` - Pages section
- `resources/views/engram/theory/` - Theory section
- `resources/views/tests/` - Test V2 pages
- `resources/views/components/test-mode-nav-v2.blade.php` - Mode navigation
- `resources/views/components/related-test-card.blade.php` - Related tests
- All other components and partials

Admin views (can use regular route()):
- Admin routes don't need locale prefixes
- OK to use `route()` in admin views

### Key Files

**Routing:**
- `app/Providers/RouteServiceProvider.php` - Registers localized routes
- `routes/web.php` - Route definitions (loaded multiple times with prefixes)

**Middleware:**
- `app/Http/Middleware/SetLocale.php` - Detects and sets locale from URL
- Registered in `app/Http/Kernel.php` under 'web' middleware group

**Services:**
- `app/Modules/LanguageManager/Services/LocaleService.php` - Core logic
- `app/Modules/LanguageManager/helpers.php` - Helper functions

**Models:**
- `app/Modules/LanguageManager/Models/Language.php` - Language model

**Config:**
- `config/app.php` - Fallback locales (`locale`, `supported_locales`)

## References

- Main documentation: `app/Modules/LanguageManager/README.md`
- Fix summary: `MULTILINGUAL_URL_FIX.md`
- Ukrainian documentation: `ВИПРАВЛЕННЯ_МУЛЬТИМОВНИХ_URL.md`
