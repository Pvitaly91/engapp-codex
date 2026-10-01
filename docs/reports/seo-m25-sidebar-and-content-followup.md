# M25 follow-up — usable theory sidebar and quieter lesson cards

Date: 2026-10-02 (Europe/Kyiv). Base: `ce5776e45c10bc9ba49b2e3d5edb33b7b875d688`, working branch `codex/seo-m25-unified-theory-design`.

## Scope and outcome

Implemented and accepted against the real `http://gramlyze.loc` document root. The supplied screenshots and collapsible HTML preview were visual references, not application instructions. This follow-up simplifies the shared sidebar and nested presentation frames; it does not shorten lessons or introduce fake lazy loading or new hidden teaching sections.

- One desktop sidebar replaces separate topic-map, contents and tags cards. Lessons default to **Зміст уроку**; **Теми** opens the searchable topic map. Category pages show the topic map without empty tabs.
- Only the active pane scrolls. The original topic search, active-page navigation, tree expansion and sidebar collapse remain available. Tabs support arrow keys, Home and End with synchronized focus and ARIA state.
- A single CSS-sticky sidebar stays below the measured site header. Available height follows the actual viewport and header; the old fixed/absolute TOC pinning code is removed.
- Page tags use a compact native disclosure. Lesson text, examples, answers and existing practice remain visible and unchanged.
- Nested rule/item/exercise borders and separate header frames are reduced. Major lesson-section cards and readable table surfaces remain, with lighter example accents in light and dark themes.
- The mobile topic panel and lesson-contents disclosure are preserved. Legacy SSR teaching content and a normal no-JavaScript theory-index link remain available.

Shared implementation files: `resources/css/theory-unified-design.css`, `resources/js/theory-navigation.js`, `resources/js/catalog-public.js`, `resources/views/theory/partials/desktop-sidebar.blade.php`, `resources/views/theory/show.blade.php`, `resources/views/theory/category.blade.php`.

## Verified causes, not assumptions

At 1366 × 768 before the change, the topic-map card occupied about 399 px, search chrome about 140 px, and the actual tree had only **141 px** for a 3608 px list. Topic rows were about 78–102 px tall. The separate lesson TOC began around y=627 and extended below the viewport. Wheel input itself worked; the problem was competing panels and too little usable navigation space.

The first follow-up smoke exposed a separate sticky-ancestor issue: existing `.nd-page` computed to `overflow: hidden` on both axes. That prevented sticky navigation from following document scrolling. The desktop theory-only override uses horizontal `clip` and vertical `visible`, retaining horizontal paint clipping without creating that scroll container.

Final live checks at 1366 × 768 show **349–354 px** for the topic tree across the five sampled pages. After document scrolling, the sidebar begins at y=99 below the header ending at y=87 and ends at y=756, inside the 768 px viewport. First and last lesson links are unobstructed and anchors land below the header.

The initial failed smoke reports are retained. An intermediate last-anchor failure was a diagnostic timing issue: fixed-duration waiting sampled a long smooth scroll before it finished. The runner now waits for actual stable scroll position; the header-position assertions were not relaxed.

## Live acceptance pages

- [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous)
- [Present Perfect: Forms and Use](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms)
- [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast)
- [Reported Statements](http://gramlyze.loc/theory/reported-speech/reported-statements)
- [Present Simple category](http://gramlyze.loc/theory/present-simple)

Fresh guest contexts used real local pages, without production requests, authenticated sessions, fixture replacement or state-changing requests. Tested desktop 1440 × 900 / 1366 × 768 and mobile 390 × 844 / 320 × 800, in light and dark themes: **24/24 scenarios passed**, plus **1/1 legacy no-JavaScript control**. There are 47 final screenshots.

Checks cover searchable/collapsible topics, tab keyboard controls, independent list wheel scrolling, visible clickable lesson links, sticky bounds, last-anchor placement, mobile panels, and absence of horizontal learning-content overflow. There were **0 browser runtime errors and 0 failed local resources**. Google Fonts requests failed in the environment and are recorded separately; this is not a claim that every external request succeeded. Without JavaScript, existing JS-dependent language/theme widgets remain uninitialized; only teaching SSR readability and the fallback link were accepted. No performance score or field Core Web Vitals is claimed.

The primary agent visually inspected the final desktop Present Perfect, Linking Words, category, legacy no-JS and mobile light/dark images, plus the final smoke's top/scrolled views.

## Regression and preservation evidence

| Check | Result |
| --- | --- |
| Existing targeted isolated PHP suites | 114 tests / 21,859 assertions passed |
| New sidebar presentation suite | 3 tests / 36 assertions passed |
| Unique JavaScript tests across the final runs | 32 passed |
| Real local GET coverage | 292/292 HTTP 200 |
| Strict comparison against independent pre-M25 HTTP baseline | 292/292 matched, 0 differences |
| Before/after authored browser-page contract | All five pages matched; verified in every final JS scenario |
| Live source synchronization | 6/6 implementation files match the worktree, normalized LF |
| Installed build verification | Manifest and all 4 referenced assets match the final candidate |

The HTTP comparison preserves teaching-text order, links, tables, original anchors, existing disclosures/keys, H1, title, description, canonical, robots, JSON-LD and sitemap. No source lesson text, route, SEO policy, course gate or answer logic was edited.

Read-only local database inventory remains 254 lessons, 41 categories and 6262 blocks across the recorded locales. Its content fingerprint is unchanged:

`3b2edd79a8cef2715907027d17e6d0166261ded7b6cc5c03bdba10967bf4856c`

PHP tests used isolated fixtures, not the live MySQL database. One existing `PDO::MYSQL_ATTR_SSL_CA` deprecation was recorded; it did not fail the tests.

New regression files: `tests/Feature/TheorySidebarPresentationTest.php`, `tests/js/theoryNavigation.test.js`; updated `tests/js/unifiedTheoryDesign.test.js`. The repeatable live runner is `tools/diagnostics/seo-m25-followup-browser.cjs`.

## Build and private evidence

Final public build uses `catalog-public-CjFSrXgb.css` (120.29 kB, gzip 20.11 kB) and `catalog-public-CVTfvi_o.js` (12.59 kB, gzip 4.29 kB). The shared app CSS/JS references are unchanged. Manifest SHA-256:

`225d77e8a8003a8c80c1551391b7fe2fd608ad6f10af0fc641086fb64cd8792a`

The generated build is installed only in the real local document root and is not included in this source commit. Private backups, sanitized diagnostics and screenshots are not committed:

- Main `storage/app/seo-m25-followup/before/`: guarded original source files and previous complete `public/build`.
- Main `storage/app/seo-m25-followup/sidebar-diagnosis-native-20261002.json`: original sidebar measurements.
- Main `storage/app/seo-m25-followup/before-independent-learning-native-20261002.json`: independent five-page learning baseline.
- Main `storage/app/seo-m25-followup/after-final-sidebar-native-20261002.json`: final browser acceptance, SHA-256 `e0c0d56f4d7c4f4bc209a6339a86862bdf8ecd437e48ce503c384c73fdd8230d`.
- Main `storage/app/seo-m25-local/after-followup-http-comparison.json`: strict 292-URL preservation result.
- Main `storage/app/seo-m25-local/followup-after-inventory.json`: read-only database inventory.
- Worktree `storage/app/seo-m2-local/m25-followup-regression-3df0f016f1f7459a80f307200493c53d-result.json` and `m25-sidebar-presentation-3a18b2bd082d452bbecdc2d1301544e0-result.json`: isolated PHP results.

For a scoped local rollback, restore the four changed implementation source files from this follow-up backup, remove only the two newly added owned module/partial files, and restore the previous complete build/manifest references. Do not reset or clean the dirty document root, replace unrelated work, or touch the database.

No `.env`, dependency lockfile, vendor files, runtime cache, database data, seed, migration, host mapping, server process, deployment workflow or production environment was changed. Normal source commit/push targets the existing working branch only; this is not a deployment or a direct update to `main`.
