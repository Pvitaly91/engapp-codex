# Polyglot A1-C2 Release Hardening

Date: 2026-04-30

## Scope

Release hardening covered the public Polyglot course system for:

| Course | Planned | Implemented | Next recommended | Course report | Release check |
| --- | ---: | ---: | --- | --- | --- |
| polyglot-english-a1 | 16 | 16 | none | clean | PASS: 12 / 0 / 0 |
| polyglot-english-a2 | 16 | 16 | none | clean | PASS: 12 / 0 / 0 |
| polyglot-english-b1 | 16 | 16 | none | clean | PASS: 12 / 0 / 0 |
| polyglot-english-b2 | 16 | 16 | none | clean | PASS: 12 / 0 / 0 |
| polyglot-english-c1 | 16 | 16 | none | clean | PASS: 12 / 0 / 0 |
| polyglot-english-c2 | 16 | 16 | none | clean | PASS: 12 / 0 / 0 |

## Route Smoke

| Area | Result |
| --- | --- |
| Home page `/` | 200, contains links to A1, A2, B1, B2, C1, C2 |
| Course pages | 6 / 6 return 200 |
| Compose lesson routes | 96 / 96 return 200 |
| Theory routes | 96 / 96 return 200 |
| Related tests by theory page | 96 / 96 verified after direct Polyglot related-test fallback |
| DB link integrity | 96 / 96 lessons pass; orphan links 0; duplicate links 0 |
| Guest debug safety | 0 guest pages contain `data-polyglot-admin-debug="1"` |
| `php artisan view:cache` | PASS |

## Cross-Level Navigation

| From | To | Status |
| --- | --- | --- |
| Polyglot English A1 | Polyglot English A2 | present |
| Polyglot English A2 | Polyglot English B1 | present |
| Polyglot English B1 | Polyglot English B2 | present |
| Polyglot English B2 | Polyglot English C1 | present |
| Polyglot English C1 | Polyglot English C2 | present |
| Polyglot English C2 | all levels complete | present |

Labels verified:

| Locale | Continue label | Final all-levels label |
| --- | --- | --- |
| uk | `Продовжити з Polyglot English {LEVEL}` | `Усі рівні Polyglot завершено` |
| en | `Continue with Polyglot English {LEVEL}` | `All Polyglot levels are complete` |
| pl | `Kontynuuj z Polyglot English {LEVEL}` | `Wszystkie poziomy Polyglot są ukończone` |

## Hardening Changes

- Theory routes now support nested category paths such as `/theory/tenses/present-simple/present-simple-questions` while preserving legacy leaf-category pages.
- Related-test selection now returns direct Polyglot course tests when a theory page also has older non-Polyglot tests.
- Guest compose HTML no longer contains the admin debug data attribute literal unless the admin debug block is actually rendered.
- C2 course/final completion states show an explicit all-levels-complete message instead of a next-level CTA.

## DB Integrity Blocker Resolution

GLZ-PROMPT-0065 found 21 legacy A1/A2/B1 lessons where `saved_grammar_test_questions` contained `question_uuid` links for rows absent from `questions`. The root cause was stale local DB state after earlier legacy seeding work: canonical V3 JSON still contained the expected 24/48 items, and direct canonical seeder execution restored the missing question rows.

A shared repair flow was added:

- `App\Services\PolyglotLessonLinkIntegrityService`
- `php artisan polyglot:repair-link-integrity`
- `php artisan polyglot:repair-link-integrity --apply`

The repair command scans implemented Polyglot lessons, reports orphan and duplicate links, re-runs canonical JsonTestSeeder classes in apply mode, prunes any remaining orphan/duplicate link rows, and re-normalizes link positions.

Before / after:

| Stage | Total lessons checked | Passed | Failed | Orphan links | Duplicate links |
| --- | ---: | ---: | ---: | ---: | ---: |
| GLZ-0065 initial smoke | 96 | 75 | 21 | 43 | 0 |
| GLZ-0066 dry-run after one diagnostic seeder run | 96 | 76 | 20 | 42 | 0 |
| GLZ-0066 after apply | 96 | 96 | 0 | 0 | 0 |

Affected lesson slugs:

- `polyglot-articles-a-an-the-a1`
- `polyglot-comparatives-a1`
- `polyglot-final-drill-a1`
- `polyglot-present-perfect-vs-past-simple-a2`
- `polyglot-be-going-to-a2`
- `polyglot-must-have-to-a2`
- `polyglot-present-perfect-time-expressions-a2`
- `polyglot-passive-voice-basics-a2`
- `polyglot-reported-speech-basics-a2`
- `polyglot-question-tags-basics-a2`
- `polyglot-second-conditional-basics-a2`
- `polyglot-final-drill-a2`
- `polyglot-present-perfect-continuous-basics-b1`
- `polyglot-present-perfect-continuous-vs-present-perfect-b1`
- `polyglot-past-perfect-basics-b1`
- `polyglot-narrative-tenses-basics-b1`
- `polyglot-future-continuous-basics-b1`
- `polyglot-future-perfect-basics-b1`
- `polyglot-passive-voice-with-modals-b1`
- `polyglot-reported-questions-b1`
- `polyglot-wish-if-only-basics-b1`

Final status: resolved.

## Commands Run

- `composer dump-autoload --no-scripts`
- `php artisan package:discover --ansi`
- `php artisan polyglot:repair-link-integrity`
- `php artisan polyglot:repair-link-integrity --apply`
- `php artisan polyglot:course-report polyglot-english-a1`
- `php artisan polyglot:course-report polyglot-english-a2`
- `php artisan polyglot:course-report polyglot-english-b1`
- `php artisan polyglot:course-report polyglot-english-b2`
- `php artisan polyglot:course-report polyglot-english-c1`
- `php artisan polyglot:course-report polyglot-english-c2`
- `php artisan polyglot:release-check polyglot-english-a1`
- `php artisan polyglot:release-check polyglot-english-a2`
- `php artisan polyglot:release-check polyglot-english-b1`
- `php artisan polyglot:release-check polyglot-english-b2`
- `php artisan polyglot:release-check polyglot-english-c1`
- `php artisan polyglot:release-check polyglot-english-c2`
- `php artisan route:list --path=courses`
- `php artisan route:list --path=theory`
- `php artisan route:list --path=test`
- `php artisan view:cache`
- targeted Laravel kernel smoke scripts for course pages, compose routes, theory routes, related tests, labels, debug-safety, route stability after repair, and DB counts

## Manual-Only Remaining Work

- Decide whether the existing composer PSR-4 warnings for wrapper-style seeder files should be cleaned up in a separate maintenance pass.
- Broad PHPUnit was intentionally skipped for this pass.

No migrations or new tables were added.
