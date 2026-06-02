# Polyglot English A2 Release Notes

- Course slug: `polyglot-english-a2`
- Runtime state: `16 / 16` implemented lessons
- Final lesson: `polyglot-final-drill-a2`
- Final theory page: `/theory/basic-grammar/a2-mixed-revision`

## Lesson Inventory

1. `polyglot-present-perfect-basic-a2`
2. `polyglot-present-perfect-vs-past-simple-a2`
3. `polyglot-first-conditional-a2`
4. `polyglot-be-going-to-a2`
5. `polyglot-should-ought-to-a2`
6. `polyglot-must-have-to-a2`
7. `polyglot-gerund-vs-infinitive-a2`
8. `polyglot-past-continuous-a2`
9. `polyglot-present-perfect-time-expressions-a2`
10. `polyglot-relative-clauses-a2`
11. `polyglot-passive-voice-basics-a2`
12. `polyglot-reported-speech-basics-a2`
13. `polyglot-used-to-a2`
14. `polyglot-question-tags-basics-a2`
15. `polyglot-second-conditional-basics-a2`
16. `polyglot-final-drill-a2`

## Entry Points And Key Routes

- Home page CTA: `/`
- English home CTA: `/en`
- A1 complete-state CTA source: `/courses/polyglot-english-a1`
- Public course landing: `/courses/polyglot-english-a2`
- Final lesson compose route: `/test/polyglot-final-drill-a2/step/compose`
- Final theory route: `/theory/basic-grammar/a2-mixed-revision`
- Public navigation label: `Polyglot курс` / `Polyglot course`
- Home card label: `Polyglot English A2`

## Release Check Commands

```bash
php artisan polyglot:course-report polyglot-english-a2
php artisan polyglot:release-check polyglot-english-a2
php artisan polyglot:release-check polyglot-english-a2 --json --write-report
php artisan polyglot:release-check polyglot-english-a2 --strict
```

- JSON report artifact path: `storage/app/polyglot-reports/polyglot-english-a2-release-check.json`

## Current Course Report Wording

```text
Planned total: 16
Implemented total: 16
Missing / planned lessons: none
Next recommended lesson: none
Broken previous/next refs: none
```

## Final UX Notes

- Course landing now renders a clear content-complete state for A2: `16 / 16 lessons available`.
- Learner completion remains a separate browser-state flag and only appears after all lessons are completed on that device.
- No planned lesson cards or disabled planned actions should render on `/courses/polyglot-english-a2`.
- The final lesson completion panel exposes course-complete hooks, back-to-course CTA, repeat-course CTA, and restart-course CTA without a next-lesson CTA.
- Fully-available banner copy is now course-aware, so A2 does not render A1-specific text.

## Theory Bindings

- Every implemented lesson is bound through `saved_test.filters.prompt_generator.theory_page`.
- The final drill uses the dedicated theory page `A2 Mixed Revision`.
- Related tests on `/theory/basic-grammar/a2-mixed-revision` are accepted as the current aggregated/virtual theory-page behavior.

## Manual-Only Checks

- Real browser completion of lesson 16 and verification that the learner-complete banner appears on `/courses/polyglot-english-a2`.
- Real browser verification of restart/reset behavior from the fully complete A2 state across refreshes and tabs.
- Visual QA for mobile layout and CTA placement on the A2 course landing page and final lesson page.

## Non-Goals And Known Blockers

- No new lessons, no lesson 17, and no new course.
- No new database tables.
- No heavy E2E stack such as Dusk, Playwright, or Cypress.
- Broad `php artisan test --filter=Polyglot` and full `tests/Feature/PolyglotTheoryPageTest.php` may time out in this local environment; release confidence is based on targeted Polyglot suites plus smoke checks.
- Full repo-wide `php artisan test` can still include unrelated legacy failures outside the targeted Polyglot scope.
