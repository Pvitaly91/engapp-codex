# M69 — Adjectives vs Adverbs / Order of Adjectives / -ed / -ing

## Delivery scope

- Base: `c41f65749e39768a69e62dff4e4b942ffa751fe4` (accepted M68).
- Working branch: `codex/seo-m69-adjectives-adverbs-standard-view`.
- Only `http://gramlyze.loc` was applied and accepted. Served application/document roots were freshly proved independently of the worktree; APP_ENV was not used as permission.
- No main changes, PR, merge, force push, production request/deployment, server/configuration/hosts/ACL/dependency change, or personal-progress reset.
- Intensifiers and the three M68 lessons remain read-only.

| Local page | Owner suffix in Database\\Seeders\\Page_V3\\Adjectives | Page / category | Continuing slots | Practice / hero |
| --- | --- | --- | --- | --- |
| [Adjectives vs Adverbs](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/adjectives-vs-adverbs) | AdjectivesVsAdverbsTheorySeeder | 279 / 52 | 11, SQL levels all NULL | A2 / A2–B1 |
| [Order of Adjectives](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/order-of-adjectives) | AdjectivesOrderOfAdjectivesTheorySeeder | 278 / 52 | 6: B1 × 5, footer NULL | B1 / B1 |
| [-ed / -ing Adjectives](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/ed-ing-adjectives) | AdjectivesEdIngAdjectivesTheorySeeder | 275 / 52 | 6: A2 × 5, footer NULL | A2 / A2 |

All owners are UK theory Pages with the single ancestor `prykmetniky-ta-pryslinknyky`. IDs, category spelling, URLs, order, columns, anchors and the 23 continuing SQL identities/levels remain intact. Display level is not substituted for SQL level.

## Teaching and canonical presentation

Adverbs now teaches the function of the word, including modification of an adjective, another adverb and a whole message. Linking verbs are contrasted with actions; good/well, flat forms, -ly formation, hard/hardly and the other lexical pairs have contextual meanings. Manner/frequency/degree positions are bounded, not universal blacklists.

Order uses short natural noun phrases, article/sound, quantity and fixed item names. Age/shape variation is not rejected by a mechanical sorter. Coordinate punctuation and cumulative modification are distinguished; the two meaning tasks assess semantic values, not automatic comma grading.

Ed/Ing distinguishes a stated condition from an impression, permits -ing adjectives for people, retains word-specific prepositions and simple be questions, and separates adjective examples from progressive/passive constructions without requiring an A2 theoretical label where context is ambiguous.

English sentences have UK translations; formulas/comments/translations use separate native fields. The readable author document and editorial map distinguish retained source content, corrections, new additions and removed repetition, with source-access limitations recorded.

- Active author revision: [1.0.1](../content/m69-adjectives-adverbs.v1.0.1.json).
- [Readable author text](../content/m69-adjectives-adverbs.v1.0.1.uk.md).
- [Editorial map and source verification](../content/m69-adjectives-adverbs.editorial.v1.0.1.md).
- [Exact OLD → native component map](../content/m69-adjectives-adverbs.components.v1.json).

The 11/6/6 source structures were not forced into a common six-slot layout. There are 26 native body strings including the three new practice sets. Existing forms-grid, usage-panels, comparison-table, mistakes-grid, summary-list and navigation-chips remain responsible for presentation.

Meaningful details: 0 / 1 / 0. The sole detail belongs to Order's short neutral-description point and explains contextual age/shape variation with two translated examples. No detail quota, runtime word-count cutoff, parallel renderer or M69 visual component was added.

## Subtitle finding and revision 1.0.1

Actual BEFORE found existing plain subtitles for the first two owners, but Page275.text was NULL, no UK subtitle row existed, and both subtitle fields were absent from the EdIng definition. This was captured explicitly, not guessed from source absence.

Initial frozen 1.0.0 added one stable subtitle. Live acceptance then found that a leading strong title was trimmed by the existing title extractor, losing the initial hyphen in EdIng's H1 and the category's derived description.

Revision 1.0.1 changes only:
- master version and explicit post-apply metadata proof;
- the EdIng subtitle_html and subtitle_text;
- their exact corresponding patch/definition fields.

All 26 body strings, practice, bank selection and component map are byte-identical across these revisions. Frozen 1.0.0 and its manifest remain unchanged. The active plain paragraph makes the unchanged real extractor return NULL, preserving raw Page.title `-ed / -ing Adjectives`. No global title/SEO/controller redesign was made. Actual H1, breadcrumb, LearningResource and independent category metadata acceptance pass.

## Practice and thematic banks

Every page contains 2 form + 2 meaning + 2 manual/token sentence tasks, plus a distinct five-question type-4 widget.

| Page | Own tasks | Existing widget questions | New question-family rows |
| --- | --- | --- | --- |
| Adverbs | 2 / 2 / 2 | 2496, 2497, 2502, 2504, 2505 | 0 |
| Order | 2 / 2 / 2 | 2797, 2802, 2803, 2804, 2807 | 0 |
| Ed/Ing | 2 / 2 / 2 | 2856, 2857, 2859, 2860, 2862 | 0 |

All 36 level-appropriate candidates were reviewed; selection/rejection reasons and opaque fingerprints are in the [bank audit](../content/m69-adjectives-adverbs-bank-audit.v1.json). The banks are respectively PolyglotAdjectivesVsAdverbsAllLevelsLessonSeeder, PolyglotOrderOfAdjectivesAllLevelsLessonSeeder and PolyglotEdIngAdjectivesAllLevelsLessonSeeder. No previous-topic snapshots or new questions, answers, options, hints, saved tests or links were inserted.

The finite trusted selector is derived from the accepted policy, validates actual owner/locale/body/bank contract, uses clone-only condition presentation and does not mutate original question models. Existing case, token multiplicity, bounded UK hints and token formatting remain unchanged.

## Guarded local database result

1. Fresh physical/web/CLI proof, independent source/DB/HTTP BEFORE, reviewed exact allowlist and source sync.
2. Initial transaction: 28 updates (3 Page.text + 25 existing UK block bodies), 4 inserts (3 practices + one explicitly absent subtitle), 0 deletes. Exclusive private backup. Both old-plan and fresh-after-plan repeated apply returned 0 updates / 0 inserts.
3. Separate reviewed correction transaction: only Page275.text and subtitle12755.body, 2 updates / 0 inserts / 0 deletes, exclusive backup.
4. Independent correction postconditions scan all 46 tables, restoring only the two old values in a comparison copy. Non-target values and full target metadata/timestamps are exact.
5. Correction repeated apply with both reviewed old plan and fresh after plan: no-op 0 / 0; unused no-op backup paths were not created.
6. Final chained independent scan reconstructs the original BEFORE exactly and binds the actual final 46-table state. Old consumers also scanned SQL before/after their HTTP audit with no drift.
7. Both temporary GET/loopback-only proof routes were removed; routes file is byte-exact to its pre-proof SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Separate GETs returned 404.

Net result against original BEFORE: 28 changed existing rows and 4 added text blocks. The correction is two additional update operations, not two additional distinct old rows. No SQL technical metadata transition, DDL, mass reseed, restore or question-family write occurred. Largest native UTF-8 body is 12,177 bytes against actual TEXT capacity 65,535.

Evidence stays private. Selected opaque receipt hashes:
- independent BEFORE: `3fbcc654435119c5607d0ede041bead091bce4527efadde6a2b1bef661de4d62`;
- initial postconditions: `89c7e301a2bd03b82e4eb448ff3c4a28edb3faba218f64502ce1c54e006b5d8e`;
- correction exact review: `756f397e92711ca1a903f325e18087086f9136089eb43cb8a77047b8ed34b6f4`;
- correction postconditions: `e92af3b4f90e114a03c9d249e9af502a9707296d21e3568ca210c2754169a8e1`;
- final 46-table chain: `324ac74302b902f45e743104537fa9d58d61e1cfb2fd01db4ddef75e4c813a0a`.

## Nonvisual integration and preservation

Eight shared registration files gain 13 exact lines in 12 owned hunks for theory presentation, builder binding and excluded-consumer preservation. Every new standalone callback keeps the M68 NULL-dispatch guard. Generic native support handles independently captured missing subtitle/zero-counter UUIDs; a finite opt-in exact-hash AFTER paragraph supports the EdIng fallback. Other owners keep their default validation.

M69's correction service delegates locks, exclusive backup, stale-plan equality, transactional writes and no-op to the accepted writer; it cannot change a partial/mismatched pair or anything outside the two exact fields. No apply mechanism was added to HTTP, startup, scheduler or course flow.

Fresh preservation results:
- 326 visual files unchanged; 2,511 historical frozen files unchanged.
- Both M69 manifests/artifacts match raw SHA-256 and byte lengths; finite Git attributes prevent checkout EOL conversion of new pinned bytes.
- M68/M67/M66 accepted content, course learning DOM, category learning descriptions, EN/PL, old question payloads and progress preserved.
- 51 read-only HTTP consumers, 6 old banks / 432 questions pass; category metadata and learning DOM are exact to BEFORE after the hyphen correction.
- Actual registry: 130 targets, failed 0, writes 0; 127 old owner-keyed structural records preserved + exactly 3 M69 owners.
- The 127 old full-HTML hash pairs differ and are retained separately. Structural preservation is not presented as byte-identical full HTML.

Opaque preservation receipts: old-consumers `275ecdec9ba6cabdbd597183fe65700b43a23e40d996d7b776ccfdde6049d99d`; registry `3726111f076ebba74251add4b120c25e26ce23288196cfc19a2d49a970e27bd5`; final-files `f757d29665a4542483a3ae11f2f08acfb35ca99274457a966a1341acda5c854d`.

## Actual acceptance and limitations

- Real SSR: 3 × HTTP200, 463 independently expected fields, native components, footer destinations, metadata and 0/1/0 detail ownership. Receipt `b1ac673b108f271537b08c03311845eba85db48052eaa85c1e2c78ac85b88d09`.
- Real browser: all 18 target states (1440×1000, 390×844 and 320×844, each light/dark), each viewed to footer. Actual DOM viewport recorded; fonts status loaded. No learning-area clipping/overflow; mobile tables use their existing internal horizontal scroll. Small document/shell overflow is recorded separately, not falsely reported as zero.
- All 30 correct/distractor option attempts, 24 input task/mode/size cases, 16 punctuated/unpunctuated accepted-value attempts, 12 wrong + 12 grammatical-but-task-noncompliant attempts, six mixed token-return rebuilds, retry/reset and stable repeated score pass. Feedback is plain bilingual text after Check.
- All 15 actual shuffled widget questions completed to 5/5 on each page, with visible condition, bank tokens, case and multiplicity checked. No global localStorage clearing.
- The one real detail is closed without fragment, responds independently to Enter and Space, retains owner block-8669, and opens after deep-link/reload without clipped text.
- Browser practice receipt `ecea112717f0fc50e1b98619c8817473ac9561a541c5e2e1f4b1ff61708730d8`; 18-state/detail receipt `018e3f87824aed633156e3a1e7e7bc6de8bb10d06f378614f21461dfc2320cad`.
- Source-only actual-factory runner: 23 custom cases, 18 tasks / 30 options / 8 accepted input values; no SQL/network/.env access. These are not counted as PHPUnit tests or browser/layout acceptance. Receipt `4f751f198648e478b763e1dcadd7aac2fecc6fa5322c5d21ca8d8ff5cb1c3f70`.
- Before-freeze native fixture: 1 test / 63 assertions; offline sizing checked 57 fields with four local fallback-font profiles. A data: preview was blocked by browser policy and was not rehosted or bypassed; real HTTP acceptance above is separate.
- Final actual one-run scoped set: 32 distinct files / 211 distinct class#method tests / 31,544 assertions, failures 0, errors 0, skips 0, one reported deprecation; 7:36.630. Isolated testing/SQLite :memory:, array cache/session, working .env not loaded, CLI OPcache off. Protected 46,661 files before/after: 0 drift. This is not an entire-repository green claim. Receipt `442256fd599dda1565210ed5a153668f8c332751e81aae1719d6bde8e1db74f2`; JUnit `1b0ecaf5145e0bdcd4a6a728c3194928ccab1ec7cc51cec755c95d2299c4eaa2`.
- Focused 13 / 203 subtitle/fallback/transaction tests pass and are included in the final set, not added as extra unique tests.
- Historical literal UI suite: 6 tests / 23 assertions / 4 known BEFORE failures, not counted as PASS. Historical M68 raw shared-hash suite: 3 tests / 9 assertions / 3 failures after the owned new integration hunks, not claimed inherited before M69 and not rewritten; current M68 runtime/NULL/clone preservation and M69 accepted-lineage gates pass.
- Intermediate new-run failures were retained: stale fixture definitions, one stale new M69 strong-subtitle assertion, and private helper naming/hash-method errors were corrected without weakening guards or historical assertions. Locator deadlines/render-timing failures were followed by successful state-derived reruns, not application changes.
- Print, no-JS and full WCAG audit: not_checked. They are neither PASS nor evidence of unsupported behavior.

## Frozen versions and Git handoff

Active [checksum manifest](../content/m69-source-checksums.v1.0.1.json):
- master SHA `662451a7f19cea5b59d5ca182bbae58b841251275cac8e48613b57d943222ea4`;
- patch SHA `419d3bd54ec9f3d9a9222a1033e86783639147cf3d420b56d56dc6a5933206ac`;
- checksum companion SHA `6a7dff78c0592c8f0d9138a50568215cc63496d1381df035a569f750872aae3c`.

Historical 1.0.0 companion SHA `fcf442ace35913c61277a25d5ab2da5d7f6329bdcdb40dfc88cbbd4b695bfa3a` remains immutable. Readable/editorial companions, before source, component map, contract and pedagogical bank audit are covered by their manifests.

Scoped staging excludes .env, runtime/private diagnostics, backups/dumps, screenshots, nonce routes, caches, generated assets and unrelated dirty work. The ROOT's absent .gitattributes was not restored; only the existing worktree attributes receive owned M69 EOL rules. Delivery uses one normal commit and push on the M69 branch, with an independent remote SHA == HEAD check; the final response supplies that full commit SHA.
