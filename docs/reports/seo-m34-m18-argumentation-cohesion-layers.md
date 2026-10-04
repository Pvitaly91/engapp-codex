# M34 — M18 Argumentation / Cohesion / Paraphrase: native basic

Scope: тільки робочий `http://gramlyze.loc`. Production `.com`/`.ub`, main, PR, deploy, migrations, reseed, caches/sessions, dependencies, build/vendor, Apache/hosts/config — поза завданням. Авторські accepted M18 тексти не переписуються.

## База і точні цілі

Accepted base: `736a00dc7ade11bfc2e1b6b4ef282fa8a8d64118`, M33. Після `git fetch origin` локальна база дорівнювала `origin/codex/seo-m33-m17-academic-english-layers`; ancestry check exit0. Нова гілка `codex/seo-m34-m18-argumentation-cohesion-layers` створена у чистому reusable worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/m30`. Основний dirty checkout збережений на `41820a2bebdf69004fa7209a2a38457f93efabbd` / `codex/production-ready-a14788dac`; сторонні зміни й index не очищаються й не stage-яться.

| Theory / primary test | Exact Page.seeder | Category | Accepted definition Git blob |
|---|---|---|---|
| [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) / [тест](http://gramlyze.loc/test/academic-english/argumentation-and-academic-tone) | `Database\Seeders\Page_V3\AcademicEnglish\ArgumentationAndAcademicToneTheorySeeder` | academic-english | `a2ab4114f979ddc6a81e9b79d3b11836a7f9f7de` |
| [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) / [тест](http://gramlyze.loc/test/clauses-and-linking-words/discourse-markers-and-cohesion) | `Database\Seeders\Page_V3\ClausesAndLinkingWords\DiscourseMarkersAndCohesionTheorySeeder` | clauses-and-linking-words | `93801c32885c33c451918022997081dabf2a094d` |
| [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) / [тест](http://gramlyze.loc/test/formal-english/paraphrase-and-reformulation) | `Database\Seeders\Page_V3\FormalEnglish\ParaphraseAndReformulationTheorySeeder` | formal-english | `57f0c9536cb92fae21bafd39dd17cb8b8b2e82e4` |

Усі три UK/theory/C2, у трьох різних root categories; mapping finite, не generic academic-english assumption. Raw Git blobs підтверджено до projection. Прочитані AGENTS.md, point-detail-quality.md, accepted M33/M18 reports і всі три повні definitions.

## Початковий read-only inventory

Fresh SELECT-only snapshot `storage/app/seo-m34-local/m34-bank-inventory-v1.json`, 2026-10-04T15:30:03Z. Physical Windows MySQL `localhost:3306/gr2`, server `DESKTOP-3C05HGF`; це ще не заміна fresh CLI/web proof перед apply. Actual owners: Argumentation323/category54/blocks8856–8858; Discourse310/category34/blocks8817–8819; Paraphrase322/category55/blocks8853–8855. Усі UK/theory/root identities підтверджені.

| Primary bank (prefix `Database\Seeders\V3\Polyglot\`) | Type / level | Actual count / IDs |
|---|---|---|
| PolyglotArgumentationAndAcademicToneC2LessonSeeder | 4 / C2 | 48 /19137–19184 |
| PolyglotDiscourseMarkersAndCohesionC2LessonSeeder | 4 / C2 | 48 /18513–18560 |
| PolyglotParaphraseAndReformulationC2LessonSeeder | 4 / C2 | 48 /19089–19136 |

Це спостережені counts, не припущення зі slug. Single-level primary selectors відокремлені від actual all-level bank groups. Protected snapshot:26 M26–M33 owners,20 table fingerprints,6415 non-target text blocks SHA-256 `89c59a8b6e44d3bca8f5be727beabada86dbab08ebea85637a19f9a0923f07fa`. Questions/options/answers/verb_hint/pivots/saved tests не змінюються.

## Семантичні рішення

Скінченне author mapping, не runtime threshold. Діагностичні B/D/S: basic words / candidate words / sentence endings; block boundaries дають пробіли, inline punctuation збережена. Незалежні fidelity та semantic reviews підтвердили всі 18 metric triples і рішення; substantive issues не знайдено.

| Page § | B/D/S | Decision / reason |
|---|---|---|
| A §1 | 127/40/3 | basic: model limits, attribution and complete argument table — core |
| A §2 | 175/44/5 | detail: coherent whole-paragraph four-role analysis; complete scenario, EN paragraph and UK translation basic |
| A §3 | 115/51/3 | basic: phone-legibility conditional and no completed phone test — core limit |
| A §4 | 107/37/3 | basic: partial acceptance and evidence/relevance weighting — core response |
| A §5 | 72/43/6 | basic: sequence is not cause — central contrast |
| A §6 | 71/25/3 | basic: short concrete-tone/register caveat |
| D §1 | 74/36/3 | basic: relationship first, marker or none — core mechanism |
| D §2 | 94/28/2 | basic: short not-full-synonyms/overmarking warning |
| D §3 | 88/54/7 | basic: qualification context and register, not separate lexical disclosure |
| D §4 | 167/32/2 | basic: robust does not prove reliable conclusion — explicit limitation |
| D §5 | 228/51/3 | detail: whole-pair continuity/antecedent/new-question/proposal analysis; complete EN/UK paragraphs and explicit reference guidance basic |
| D §6 | 122/64/6 | basic: clear version and author specification boundary — core contrast |
| P §1 | 179/22/3 | basic: short signal-is-not-equivalence warning |
| P §2 | 177/27/2 | basic: short marker functions/non-interchangeability note |
| P §3 | 230/51/4 | basic: own interpretation distinct from accurate paraphrase — primary contrast |
| P §4 | 0/151/16 | basic: entire meaning checklist foundational |
| P §5 | 82/42/3 | basic: focus does not prove evaluation — primary warning |
| P §6 | 86/62/6 | basic: exact log/unknown-reason contrast foundational |

18 candidates:16 basic /2 meaningful details; disclosures A/D/P1/1/0. Чотири short candidates (<30 diagnostic words:25/28/22/27) лишаються visible basic. Нуль Paraphrase disclosures — свідоме semantic decision, а не пропущена реалізація.

## 18 accepted practice cases

Кожен original prompt і повний explained key переносяться один раз; на сторінку2 select +2 choice +2 token/manual +власний linked widget. Mapping: A selects3/4, choices1/2, inputs5/6; D selects1/2, choices4/6, inputs3/5; P selects1/5, choices3/4, inputs2/6.

| Case | Зміст і незмінна межа |
|---|---|
| A1 | Roles sentences1/2/3, fourth is limitation; no measured faster booking |
| A2 | Actual phone legibility missing; every phone unsupported |
| A3 | Bounded desktop comparison, not every aspect/all users/metrics |
| A4 | Meaningful phone objection, partial acceptance and restricted recommendation |
| A5 | Monday poster/Tuesday attendance known sequence, no causal finding |
| A6 | Guide A no index/B index every definition; page-locating role and no search-time measure |
| D1 | Brief but allthree stages: nonetheless/nevertheless, not conversely |
| D2 | Glossary+index addition; moreover/furthermore/in addition or clear no-marker coordination |
| D3 | Same words, semicolon or period before however; comma splice rejected |
| D4 | These missing dates, unambiguous reference not clear titles |
| D5 | Colour distinction→black-and-white question; no invented test result |
| D6 | Two main sections summary/costs, no unnecessary causal transitions |
| P1 | Morning→9.15 specification/new precise information, not derivable paraphrase |
| P2 | Organiser, two unnamed volunteers, possible opening Monday if keys by noon; may not permission/will |
| P3 | Four readers returned afterone day, no recorded reasons; confusion only possible explanation |
| P4 | Not all≠no, at leastfour≠exactfour, some may≠allwill |
| P5 | Known eligible definition/allthree conditions and only; no registration guarantee |
| P6 | Museum audio-guide test one tablet/no phone; neither tablet outcome nor phone success/failure known |

Finite approved answer variants для interactive self-check не є універсальним semantic scorer. Повні author criteria і key лишаються доступні для інших природних відповідників; нові факти чи переписаний teaching prose не додаються.

## Початкові діагностичні прогони

Pre-projection Vitest: `node node_modules/vitest/vitest.mjs run tests/js` —8 files/61 tests PASS,70.51s. Final frozen-source repeat —8 files/61 tests PASS,44.09s. Public/legacy CSS compiled in memory; жодного build output. Спроба прочитати відсутній `vitest.config.js` перед командою дала file-not-found; це не test failure і не зміна конфігурації.

Перша fresh before HTTP capture завершилася timeout30s на M26 overview (`/theory/past-perfect-continuous`) після трьох target probes. Цей run — failure, не PASS. Незалежні повторні fresh GET: overview200/0.691141s, target200/0.272388s. Повторна complete capture успішна:33 HTML pages200 + sitemap.xml200. Ordered sitemap observed count554/SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`; acceptance порівнює actual baseline, не hardcoded count. Початковий окремий target GET200, remote127.0.0.1,time14.565154s. Мережеву причину не виведено без доказів; Apache/config/cache не змінюються.

Final pure Node M34 suites: practice87 + runtime-tooling10 + browser-contract8 =105/105 PASS. Practice включає51 actual changed-answer semantic negatives,33 baseline cases, ownership negative contract і2 alias contracts. Це реальні зміни facts/modality/conditions/source/reference/causal limits/punctuation, не absent-string mutations або проголошений універсальний semantic scorer. Дозволені finite aliases перевірено позитивно. PHP lint16/16 PASS.

## Статус приймання

Final combined pure Node:18 files /435 tests PASS, failures/skips0 (M27–M34 practice/runtime/acceptance contracts + opt-in rich CSS). Ізольований PHP `m34-integrated-v1` PASS:36 suites /467 tests /8464 assertions, runner/child0, PHP8.5.10/PHPUnit12.5.35,08:35.402,106MB; stderr empty.32 accepted M26–M33 suites395/7177;4 M34 suites72/1287: Package6/716, Patch22/59, Guard5/10, Fidelity39/502, включно з33 substantive negative semantic fixtures. Working `.env` not loaded, testing/SQLite`:memory:`, array cache/session, CLI OPcache off.46648 actual protected files —0 changes, before/after digest `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`. Private evidence `storage/app/m30/storage/app/seo-m2-local/m34-integrated-v1-3e2843c433af4e7db07de468b3c48b57-result.json`. Counted deprecation1 не прихована; її exact message цей runner не вивів у stdout/stderr/JUnit. Попередній accepted M33 теж мав1 deprecation; нове джерело цього попередження не встановлено.

Fresh physical/vhost/CLI-web proof PASS2026-10-04T16:01:55Z: sole active Apache vhost ROOT/public, loopback127.0.0.1, ApachePID12688 == pidfile, local MySQLPID6640/listener3306. Safe identity environment`production`, SiteMode`development`, mysql/localhost:3306/gr2; CLI==web nonce-bound runtime digest. Temporary stateless GET route тільки raw `.loc`, port80, loopback, exact document/application roots; forwarded headers rejected404. Positive guest GET200 and forwarded GET404, no Set-Cookie. No credentials/config payload returned.

Finite source sync16 files з exact exclusive backup `storage/app/seo-m34-local/source-backup-78e48df3970da974`; unrelated ROOT edits rejected rather than overwritten. Package BEFORE SHA-256 `8cc917160b507f115356fd800244a01c76bebda5ec3b609bf59c7421ea6777ce`, SOURCE `20336d9cade50eac61200601af47442dcac366ac95d0c47dc16559a63e58e045`; all3 canonical definitions==after. Summary-list bytes unchanged; shared5 view diffs finite M34 only.

Fresh preview `m34-preview-v1.json`, independent exact-field review PASS:3 updates type/body only,21 inserts,0 deletes; plan digest `37596bed588127cea786450b77b0d08781a59c720b28c60444dc7f356c6544a1`. Exclusive backup before transaction `storage/app/seo-m34-local/m34-before-v1.json`, SHA-256 `a81d5ce6a35f283fbb8876a6f7b62b21f13ded4f019143eadee25c7eb2b71521`. Apply PASS3/21, internal postconditions PASS; repeated same-plan apply no-op0/0, `m34-unused-no-op.json` does not exist. Independent actual DB verifier `m34-after-apply-v1.json` PASS:6418→6439 text_blocks,6415 non-target blocks exact unchanged,19 other protected tables unchanged,26 M26–M33 owners/payloads unchanged. Each target now10 rows, old boxID/UUID reused; subtitle/hero bytes and protected owner metadata/relations retained. Actual own banks/IDs/counts remain48 each.

Route removed after apply/no-op. Fresh GET4042026-10-04T16:06:37Z, routes/api.php byte-exact SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; ROOT `.env` unchanged SHA-256 `b02694c5c2643f7ae217edbf2f6362638bcb0f8d5f8f74fb5388df25a07d514b`. No route/nonce evidence committed.

First post-apply live `m34-acceptance-v1` NON-PASS exit1: first guest HTTP Argumentation GET timeout30s, before Chromium launch;0 browser states. Saved failure note `storage/app/seo-m34-local/m34-acceptance-v1-failure.json`; fresh followup Argumentation/Cohesion GET200 in0.801642/0.811069s, loopback. No source/config/cache changes or timeout relaxation.

Parallel first extended M26 `m34-regression-v1` NON-PASS exit1: first overview GET500;0 browser states/0 no-JS. Failure evidence retained `storage/app/seo-m26-local/m34-regression-v1-browser.json`. Narrow same-time Laravel log2026-10-04 19:08:57 records Windows Blade compiled-file rename Access denied(code5); this is observed with M26 failure, not asserted as the cause of separate M34 timeout. Fresh overview GET200/0.686105s. No caches cleared or framework/Apache changes. Subsequent complete browser runs will be sequential and use new evidence labels, with the failures retained.

## Live M34 приймання

Sequential `m34-acceptance-v2` PASS, exit0.33 fresh HTML GET200 + sitemap200, then full author DOM fidelity for24 M27–M34 pages. Title/H1/description/OG/Twitter/canonical/meta robots/X-Robots-Tag and ordered sitemap unchanged; local canonical remains inherited `.com` value and local X-Robots-Tag remains `noindex, nofollow, noarchive`. Це metadata прочитаного `.loc` HTML, не production HTTP check. Existing lesson-block IDs і all3 legacy self-check IDs preserved; initial repository+live DB anchor-reference inventory found0 external references, compatibility anchors nevertheless retained.

Browser:12 M34 states (1440×1000/390×844 × light/dark ×3 pages),24 no-JS basic/markup/native-detail states,21 normal-JS M27–M33 regressions.57 total browser contexts, no fixtures/page.setContent; at most4 contexts per Chromium process. M34 each page має2 select+2 choice+2 token/manual, повні6 original prompts/explained keys і linked own primary bank. Widget actually loads5 unique questions sampled from its verified48-question primary bank; every loaded ID belongs to exact own type4/C2 bank, no foreign/all-level pools. Primary test URLs з таблиці вище теж GET200.

Correct/wrong/reset, finite approved aliases, contextual-feedback boundaries, optional terminal punctuation, click-only token answer, manual entry, backspace/reuse, no token autocomplete/fetch — PASS. Real browser changed-answer semantic probes9/5/9 per A/D/P across4 viewport/theme states =92 rejection checks, plus24 contextual-feedback checks; pure Node51 mutation cases і PHP33 semantic negatives — separate executed counts, не додані до92 як один набір. Internal punctuation in discourse editing task remains significant; comma splice is rejected.

All native basic/core examples/full EN+UK paragraphs/conditions/source/negation/data limits visible before disclosure; full literal no-JS author prompts and keys readable on3 M34 and applicable M30–M33 pages. Native details mouse/Enter/Space/focus-visible, own-point placement, reload closed, deep-link opening, print expansion/restoration, no extra detail request — PASS, including no-JS on3 M34. Max1 disclosure per M34 page, so simultaneous-open there not applicable; multi-detail M27 regression separately verifies independent/simultaneous behavior. Paraphrase has0 empty disclosure wrappers.

M34 teaching native point counts15/18/28; comparison tables6×3/7×3/5×3 preserve accepted min-width900 and column minima[190,270,330]/[240,250,300]/[210,270,300].100 bounded PNGs retained privately; root reviewed6 focused desktop/mobile frames, independent visual QA25/25 covers all3 topics, both widths/themes, practice, closed/open details and right-scrolled tables. No M34 visual blocker. Inherited nonblocking cosmetics: dark C2 white-on-light-yellow badge low contrast; faint sidebar tags behind sticky TOC on some desktop-light frames. Neither style/source changed by M34; not claimed as new regressions or silently fixed.

Across57 contexts: page errors0, unclassified console errors0, failed local requests0, HTTP errors0, font failures0, expected-font console errors0, policy violations0.24 expected disabled-script events in no-JS tracked separately, not hidden application errors.

Learning main/cards/points overflow0 in all12 M34 and21 JS regression states. Tables own horizontal scroll and keep full cell text; right-scrolled PNGs intentionally show a shifted table slice, not clipped outer lesson. Document/decorative overflow measured separately: desktop0; mobile document A light/dark1/14px, D0/5px, P0/3px; max decorative excursion14.2759px. Strict document overflow is **not** claimed zero. Random background unchanged; no global overflow-x:hidden/CSS masking added.

## Extended M26 regression

Sequential `m34-regression-v2` PASS, exit0:5 actual GET200,20 desktop/mobile light/dark states,5 no-JS native-detail pages,56 exact accepted point details (8/12/12/12/12).20 viewport-only PNGs retained. Full basic/detail fidelity, mouse/keyboard/focus/own-point/independence, reload/deep-fragment/print/native no-JS and no detail fetch preserved. Baseline `storage/app/seo-m26-local/after-http.json` SHA-256 `98cf3ea7185151d99d8dbb20be23226c1847bef0bbc28ad56bfb90eb2b03c484`. Evidence `storage/app/seo-m26-local/m34-regression-v2-browser.json`, SHA-256 `aa974506da102c3c7b066da9dd3dab217f4e5124f638f3814943863f7393e374`.

Page errors/local failed requests/HTTP errors/font failures/policy violations0;5 expected disabled-script events separate. Console **not collected** by this accepted M26 runner, so its console errors0 is not claimed. Learning main/cards/unclipped producers0, `learningOverflowPass=true`; `strictDocumentOverflowPass=false`, mobile max12px. Mobile light overview/forms/negatives/questions/time0/5/3/12/6px; dark10/8/2/3/0px. No geometry-root-cause trace in this runner, so these document measurements are not automatically labelled decorative. First500 run retained independently; assertions, timeouts, source/cache/framework/config unchanged.

M26–M33 meaningful detail totals preserved:56/13/0/0/0/2/1/2. Final completed actual snapshot `m34-after-browser-v1.json` and independent verifier PASS again:3 updates/21 inserts/0 deletes,19 other tables+26 regression owners exact unchanged, all original metadata/relations/banks/anchors retained.

## Окреме історичне no-JS обмеження

Actual strict probe `m34-legacy-nojs-v1` **NON-PASS, exit1**, не включається до M34 PASS. Normal-JS M26 practice4/4 PASS; no-JS M26 full token context0/4; M27–M29 full practice context2/9 (Advanced Linking Devices і Concessive/Contrastive Structures). Exactly11 affected old pages/22 hidden token banks, same as accepted M33; no new or worsened limitation.13 expected disabled-script events separate; page/console/local request/HTTP/font/policy errors0, no per-row exceptions.

Accepted token branch byte-for-byte identical against `ceb2162c862978ca461607be3913a9f717f380b2`, fragment SHA-256 `1cd9f920295ad119a31242fb308d0201d5f6c5daaf339b4f4088e39c4b000d77`. Evidence `storage/app/seo-m31-local/m34-legacy-nojs-v1-browser.json`, SHA-256 `7c0141f1c5e5dd987597f1436b4b848d41679111f3d2e9b53f22b2dcfca12668`. Harness/assertions unchanged, no retry/masking or out-of-scope legacy repair. M34 literal full author prompts/keys and native details without JavaScript — separate PASS.

## Фінальна перевірка і Git scope

33 related M34 files explicitly selected. All new PHP EOFs were normalized before the frozen integrated run; staged diff check PASS. No post-acceptance code/author/alias/geometry changes; report only updated with actual results. Independent full staged review33/33 PASS, exact package/canonical/owner/category/fallback/shared opt-in/secret scope verified. Unknown or mismatched M34 package/ownership falls back to full stored native content, not runtime word-threshold hiding.

Final SELECT-only snapshot after all browser runs `m34-after-final-v1.json`,2026-10-04T16:35:17Z; independent verifier PASS:3/21/0,6418→6439,19 protected tables+26 old owners+all3 exact bodies/own banks unchanged except finite projection. One combined verification command returned exit1 after verifier PASS because optional Get-FileHash used wrong `../../` paths from nested worktree; no files at those paths existed or were edited. Absolute ROOT path repeat confirms original `.env`/routes hashes and verifier PASS; this lookup error is not treated as a successful combined command or a DB/test failure.

No `.env`, temporary route, private runtime evidence/screenshots/backups/dumps, vendor/build or unrelated unfinished changes included. ROOT HEAD/branch/index not reset/rebased/cleaned/staged; only finite source sync after exact conflict checks. Normal commit/push only `codex/seo-m34-m18-argumentation-cohesion-layers`; full actual commit SHA, commit/report URLs and remote SHA==local HEAD will be recorded in final message after push (no self-referential report SHA). Main/PR/production remain untouched; no automatic transition to M19.
