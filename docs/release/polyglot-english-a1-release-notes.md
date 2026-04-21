# Polyglot English A1 Release Notes

- Course slug: `polyglot-english-a1`
- Runtime state: `16 / 16` implemented lessons
- Final lesson: `polyglot-final-drill-a1`
- Final theory page: `/theory/basic-grammar/a1-mixed-revision`

## Lesson Inventory

1. `polyglot-to-be-a1`
2. `polyglot-there-is-there-are-a1`
3. `polyglot-have-got-has-got-a1`
4. `polyglot-present-simple-verbs-a1`
5. `polyglot-can-cannot-a1`
6. `polyglot-present-continuous-a1`
7. `polyglot-past-simple-to-be-a1`
8. `polyglot-past-simple-regular-verbs-a1`
9. `polyglot-past-simple-irregular-verbs-a1`
10. `polyglot-future-simple-will-a1`
11. `polyglot-articles-a-an-the-a1`
12. `polyglot-some-any-a1`
13. `polyglot-much-many-a-lot-of-a1`
14. `polyglot-comparatives-a1`
15. `polyglot-superlatives-a1`
16. `polyglot-final-drill-a1`

## Entry Points And Key Routes

- Home page CTA: `/`
- English home CTA: `/en`
- Public course landing: `/courses/polyglot-english-a1`
- Final lesson compose route: `/test/polyglot-final-drill-a1/step/compose`
- Final theory route: `/theory/basic-grammar/a1-mixed-revision`
- Public navigation label: `Polyglot курс` / `Polyglot course`
- Home card label: `Polyglot English A1`

## Theory Bindings

- Every implemented lesson is bound through `saved_test.filters.prompt_generator.theory_page`.
- The final drill uses the dedicated theory page `A1 Mixed Revision`.
- Related tests for the final drill page are accepted as the current aggregated virtual test behavior, not a raw lesson-title card.

## Release Check Commands

```bash
php artisan polyglot:course-report polyglot-english-a1
php artisan polyglot:release-check polyglot-english-a1
php artisan polyglot:release-check polyglot-english-a1 --json --write-report
php artisan polyglot:release-check polyglot-english-a1 --strict
```

- JSON report artifact path: `storage/app/polyglot-reports/polyglot-english-a1-release-check.json`

## Final UX Notes

- Course landing page now distinguishes content completeness (`16 / 16 lessons available`) from learner completion (`all lessons completed`).
- Lesson 16 exposes final-course completion hooks and restart/repeat CTAs instead of a next-lesson CTA.
- No planned lesson cards should render on the course page in the fully complete runtime state.
- Public-ready `polyglot-*` compose routes bypass the generic `/test/` coming-soon gate while the wider under-development test catalog stays hidden.

## Manual-Only Checks

- Real browser click-through to finish lesson 16 and observe the learner-complete state on the course page.
- Real browser verification of localStorage reset/restart behavior across tabs and refreshes.
- Visual QA for mobile layout and CTA placement on course landing and final lesson pages.

## Non-Goals

- No lesson 17 or additional course content.
- No new database tables.
- No heavy E2E stack such as Dusk, Playwright, or Cypress.
- Full repo-wide `php artisan test` may still include unrelated legacy failures outside the targeted Polyglot scope.
