# Testing Guide for Multilingual URL Fix

## What Was Fixed

The website previously had all links pointing to Polish (`/pl/`) URLs regardless of the selected language. This has been fixed so that links now correctly reflect the current language selection.

## How to Test

### 1. Language Switcher Test

1. Open the homepage
2. Note the current URL (should be `/` for Ukrainian or `/en/` for English or `/pl/` for Polish)
3. Click on the language switcher in the header
4. Select a different language (e.g., Polish)
5. **Expected Result:** The page should reload with the new language prefix in the URL

### 2. Navigation Test

**Test for Ukrainian (Default Language):**
1. Switch to Ukrainian language
2. Click on navigation links:
   - Catalog → URL should be `/catalog/tests-cards`
   - Theory → URL should be `/theory`
   - Words Test → URL should be `/words/test`
   - Verbs Test → URL should be `/verbs/test`

**Test for Polish:**
1. Switch to Polish language
2. Click on navigation links:
   - Catalog → URL should be `/pl/catalog/tests-cards`
   - Theory → URL should be `/pl/theory`
   - Words Test → URL should be `/pl/words/test`
   - Verbs Test → URL should be `/pl/verbs/test`

**Test for English:**
1. Switch to English language
2. Click on navigation links:
   - Catalog → URL should be `/en/catalog/tests-cards`
   - Theory → URL should be `/en/theory`
   - Words Test → URL should be `/en/words/test`
   - Verbs Test → URL should be `/en/verbs/test`

### 3. Deep Link Test

**Test Catalog Filters:**
1. Switch to Polish language
2. Navigate to Catalog (`/pl/catalog/tests-cards`)
3. Select some filters
4. Submit the form
5. **Expected Result:** URL should remain with `/pl/` prefix

**Test Theory Navigation:**
1. Switch to English language
2. Navigate to Theory (`/en/theory`)
3. Click on a category
4. **Expected Result:** URL should be `/en/theory/{category-slug}`
5. Click on a page in that category
6. **Expected Result:** URL should be `/en/theory/{category-slug}/{page-slug}`

### 4. Test Mode Navigation

1. Switch to Polish language
2. Navigate to any test (e.g., `/pl/test/some-test-slug`)
3. Click on different test modes (Easy, Medium, Hard, Expert)
4. **Expected Result:** All mode URLs should maintain the `/pl/` prefix

### 5. Search Functionality

1. Switch to Ukrainian language
2. Use the search box in the header
3. Submit a search query
4. **Expected Result:** Search results page should have no prefix (`/search?q=...`)
5. Switch to Polish and repeat
6. **Expected Result:** Search results page should have `/pl/` prefix (`/pl/search?q=...`)

### 6. Footer Links Test

1. Switch to each language (Ukrainian, Polish, English)
2. Scroll to the footer
3. Click on footer links (Catalog, Theory, Words Test, Verbs Test)
4. **Expected Result:** All links should maintain the current language prefix

### 7. Related Tests / Components

1. Navigate to any test page in Polish (`/pl/test/some-test`)
2. Check if related test cards show
3. Click on a related test card
4. **Expected Result:** Should navigate to `/pl/test/{related-test-slug}`

## Expected URL Patterns

### Ukrainian (Default Language) - No Prefix
```
/
/catalog/tests-cards
/theory
/theory/{category}
/theory/{category}/{page}
/test/{slug}
/words/test
/verbs/test
/search?q=query
```

### Polish - `/pl/` Prefix
```
/pl
/pl/catalog/tests-cards
/pl/theory
/pl/theory/{category}
/pl/theory/{category}/{page}
/pl/test/{slug}
/pl/words/test
/pl/verbs/test
/pl/search?q=query
```

### English - `/en/` Prefix
```
/en
/en/catalog/tests-cards
/en/theory
/en/theory/{category}
/en/theory/{category}/{page}
/en/test/{slug}
/en/words/test
/en/verbs/test
/en/search?q=query
```

## Common Issues to Check For

### ❌ Incorrect Behavior (Before Fix)
- Link shows `/catalog/tests-cards` when Polish is selected
- Language switches but URLs remain without prefix
- Clicking a link loses the language context

### ✅ Correct Behavior (After Fix)
- Link shows `/pl/catalog/tests-cards` when Polish is selected
- Link shows `/en/catalog/tests-cards` when English is selected
- Link shows `/catalog/tests-cards` when Ukrainian is selected
- Language context is maintained across all navigation

## Browser Console Check

Open browser console (F12) and verify:
1. No JavaScript errors related to routing
2. Language switcher data is properly loaded
3. All AJAX requests maintain the current locale

## Testing Checklist

- [ ] Language switcher changes URL prefix correctly
- [ ] Main navigation maintains language prefix
- [ ] Footer links maintain language prefix
- [ ] Catalog filters maintain language prefix
- [ ] Theory navigation maintains language prefix
- [ ] Test mode navigation maintains language prefix
- [ ] Search maintains language prefix
- [ ] Related test cards maintain language prefix
- [ ] Forms submit to correct language URLs
- [ ] AJAX requests use correct language URLs
- [ ] No broken links or 404 errors
- [ ] Browser back/forward buttons work correctly
- [ ] Direct URL access works (e.g., typing `/pl/theory` in address bar)

## Notes for Developers

- All public views now use `localized_route()` instead of `route()`
- Admin routes remain unchanged and don't have language prefixes
- The language is stored in both session and cookie
- The `SetLocale` middleware handles locale detection from URL

## Troubleshooting

**If links are still showing wrong language:**
1. Clear browser cache
2. Clear Laravel cache: `php artisan cache:clear`
3. Clear view cache: `php artisan view:clear`
4. Check if the language is properly set in the database

**If language switcher doesn't work:**
1. Check browser console for JavaScript errors
2. Verify `$__languageSwitcher` is available in the view
3. Check if languages are properly configured in the database
