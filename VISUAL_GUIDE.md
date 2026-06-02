# Multilingual URL Fix - Visual Guide

## The Problem

```
User on Ukrainian page (base language)
URL: /catalog/tests-cards
Expected: Links should be /... (no prefix)
Actual: Links were /pl/... ❌ (incorrect prefix)
```

## Why This Happened

### Route Registration
```
RouteServiceProvider registers routes multiple times:

Route::group(base_path('web.php'));           // No prefix (for 'uk')
Route::prefix('pl')->group(base_path('web.php')); // /pl/ prefix
Route::prefix('en')->group(base_path('web.php')); // /en/ prefix

Result:
├─ GET /catalog/tests-cards → CatalogController
├─ GET /pl/catalog/tests-cards → CatalogController  
└─ GET /en/catalog/tests-cards → CatalogController
```

### The Bug
```
Call: route('catalog.tests-cards')
Returns: Could be ANY of these URLs!
  - /catalog/tests-cards
  - /pl/catalog/tests-cards  ← Sometimes this!
  - /en/catalog/tests-cards  ← Or this!

OLD localizedRoute() logic:
  if (currentLocale !== 'uk') {
    strip and re-add prefix
  } else {
    return URL as-is  ← BUG: Could have /pl/ prefix!
  }
```

## The Fix

### Flow Chart

```
                    localized_route('catalog.tests-cards')
                                    ↓
                    Get current locale (e.g., 'uk')
                                    ↓
                    Call route('catalog.tests-cards')
                                    ↓
            Could return: /catalog/tests-cards
                     OR: /pl/catalog/tests-cards  
                     OR: /en/catalog/tests-cards
                                    ↓
              ┌─────────────────────────────────────┐
              │  NEW FIX: ALWAYS normalize URL      │
              │                                     │
              │  1. Parse URL path                  │
              │  2. Split into segments             │
              │  3. Strip ANY locale prefix         │
              │  4. Add prefix only if NOT default  │
              └─────────────────────────────────────┘
                                    ↓
              Is locale 'uk' (default)?
                    ↙          ↘
                  YES           NO
                   ↓             ↓
          Return /catalog  Return /pl/catalog
          (no prefix)      (with prefix)
```

## Before vs After

### Scenario 1: Ukrainian Page
```
Current locale: uk (default)
route() returns: /pl/catalog/tests-cards (wrong!)

OLD LOGIC:
  if ('uk' !== 'uk') → FALSE
  return /pl/catalog/tests-cards ❌
  
NEW LOGIC:
  Parse: /pl/catalog/tests-cards
  Strip: /catalog/tests-cards
  'uk' === 'uk' → Don't add prefix
  return /catalog/tests-cards ✓
```

### Scenario 2: Polish Page
```
Current locale: pl
route() returns: /catalog/tests-cards

OLD LOGIC:
  if ('pl' !== 'uk') → TRUE
  Strip and add: /pl/catalog/tests-cards ✓
  
NEW LOGIC:
  Parse: /catalog/tests-cards
  Strip: /catalog/tests-cards (nothing to strip)
  'pl' !== 'uk' → Add prefix
  return /pl/catalog/tests-cards ✓
```

### Scenario 3: English Page
```
Current locale: en
route() returns: /pl/catalog/tests-cards

OLD LOGIC:
  if ('en' !== 'uk') → TRUE
  Strip /pl/, add /en/: /en/catalog/tests-cards ✓
  
NEW LOGIC:
  Parse: /pl/catalog/tests-cards
  Strip: /catalog/tests-cards
  'en' !== 'uk' → Add prefix
  return /en/catalog/tests-cards ✓
```

## Test Results Matrix

```
Input URL                    Target Locale    OLD Result              NEW Result
────────────────────────────────────────────────────────────────────────────────────
/catalog/tests-cards         uk (default)     /catalog/tests-cards ✓  /catalog/tests-cards ✓
/pl/catalog/tests-cards      uk (default)     /pl/catalog/tests-cards ❌  /catalog/tests-cards ✓
/en/catalog/tests-cards      uk (default)     /en/catalog/tests-cards ❌  /catalog/tests-cards ✓
────────────────────────────────────────────────────────────────────────────────────
/catalog/tests-cards         pl               /pl/catalog/tests-cards ✓  /pl/catalog/tests-cards ✓
/pl/catalog/tests-cards      pl               /pl/catalog/tests-cards ✓  /pl/catalog/tests-cards ✓
/en/catalog/tests-cards      pl               /pl/catalog/tests-cards ✓  /pl/catalog/tests-cards ✓
────────────────────────────────────────────────────────────────────────────────────
/catalog/tests-cards         en               /en/catalog/tests-cards ✓  /en/catalog/tests-cards ✓
/pl/catalog/tests-cards      en               /en/catalog/tests-cards ✓  /en/catalog/tests-cards ✓
/en/catalog/tests-cards      en               /en/catalog/tests-cards ✓  /en/catalog/tests-cards ✓

OLD LOGIC: 2 failures (marked ❌)
NEW LOGIC: 0 failures - ALL PASS! ✓
```

## Language Switching Example

```
User Journey:

1. Visit homepage: /
   Language: Ukrainian (default)
   All links: /catalog, /theory, /words/test ✓
   
2. Switch to Polish
   URL changes: / → /pl/
   All links: /pl/catalog, /pl/theory, /pl/words/test ✓
   
3. Switch to English  
   URL changes: /pl/ → /en/
   All links: /en/catalog, /en/theory, /en/words/test ✓
   
4. Switch back to Ukrainian
   URL changes: /en/ → /
   All links: /catalog, /theory, /words/test ✓ (no prefix!)
```

## Code Diff

```diff
 public static function localizedRoute(...): string
 {
     $locale = $locale ?? self::getCurrentLocale();
     $url = route($name, $parameters, $absolute);
     
+    // ALWAYS parse and normalize the URL
+    $parsedUrl = parse_url($url);
+    $path = $parsedUrl['path'] ?? '/';
+    $segments = array_values(array_filter(explode('/', $path)));
+    $activeCodes = self::getActiveLanguages()->pluck('code')->toArray();
+    
+    // ALWAYS strip existing locale prefix
+    if (!empty($segments) && in_array($segments[0], $activeCodes)) {
+        array_shift($segments);
+    }
+    
-    // Old: Only processed when NOT default
-    if ($locale !== $defaultCode) {
-        // Parse, strip, add prefix...
-        return $processedUrl;
-    }
-    return $url; // BUG: Could have wrong prefix
     
+    // NEW: Add prefix only if NOT default
+    if ($locale !== $defaultCode) {
         array_unshift($segments, $locale);
+    }
+    
+    $newPath = '/' . implode('/', $segments);
+    // Rebuild and return full URL
+    return ...;
 }
```

## Summary

### What Was Wrong
- ❌ `localizedRoute()` only normalized URLs for non-default languages
- ❌ For default language (uk), it returned `route()` output as-is
- ❌ `route()` could return URLs with wrong prefixes (/pl/, /en/)

### What's Fixed
- ✅ `localizedRoute()` now ALWAYS normalizes URLs
- ✅ Always strips any existing locale prefix first
- ✅ Then adds correct prefix only for non-default languages
- ✅ Default language (uk) URLs never have a prefix

### Result
- ✅ Ukrainian pages: /catalog/tests-cards (no prefix)
- ✅ Polish pages: /pl/catalog/tests-cards
- ✅ English pages: /en/catalog/tests-cards
- ✅ All 9 test scenarios pass
