# M33 — M17 Academic English: native basic і meaningful point details

Scope: тільки робочий `http://gramlyze.loc`, точні три UK M17 lessons. Production `.com`/`.ub`, main, PR, deploy, migrations, reseed, cache/session clearing, assets/vendor/config/Apache/hosts — не входять у завдання й не змінювалися.

Base: `872fc9b939818155ca1765f25adac94c1e855702`, accepted M32; після `git fetch origin` відповідає `origin/codex/seo-m32-m16-formal-english-layers`. Гілка: `codex/seo-m33-m17-academic-english-layers`. Основний dirty checkout `D:/DEV/htdocs/gramlyze.loc` збережений на `41820a2bebdf69004fa7209a2a38457f93efabbd`; сторонні зміни й index не очищалися й не stage-илися.

## Сторінки та accepted sources

| Level | Theory URL | Exact accepted definition Git blob | Page / old box ID |
|---|---|---|---|
| B2 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | `d5eb376fd2c55a30133704a61a6ecca3b8ea2220` | 293 / 8766 |
| C1 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | `01f6fa20ed67d6cd01b101061e22f505750867c1` | 298 / 8783 |
| C2 | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | `5ec8de96bf6a11c19548ec1a5922905b0fd6b888` | 314 / 8831 |

Canonical sources: `database/seeders/Page_V3/AcademicEnglish/{HedgingAndCautiousLanguageBasicsTheorySeeder,HedgingAndCautiousLanguageTheorySeeder,StanceRegisterAndEvaluationTheorySeeder}/definition.json`.

Frozen accepted before `m33-m17-academic-english-before.json`: SHA-256 `428b8633237ee232925c66876cf126637a04b2cdd42a147a01b4614f5806dd1b`. Final finite projection `m33-m17-academic-english.v1.json`: SHA-256 `037e99e37051f22e12f23597777fcd27fe70512703b19430bcc16bf20b25b73e`. Обидва hash-bound JSON мають `-text` у `.gitattributes`. Це versioned author sources, не backups БД.

Старий формат: subtitle + hero + одна велика HTML box. Новий: subtitle/hero unchanged + 8 native blocks (6 teaching, practice §7, continuation §8), deterministic UUID keys; 10 DB rows/page. Teaching points B2/C1/C2: 15/14/18. Видимого дубля старої box або прихованого другого lesson немає. Exact wording/examples/fictional data/translation/punctuation/order/links збережено; змінена лише презентація. Native table widths збережено: B2 880px/first column130px, C1 800px/first170px, C2 780px; горизонтальне прокручування всередині table wrapper, не main.

## Семантичний point-detail audit

Finite author decision, не глобальний runtime word threshold. Числа B/D/S — діагностичні whitespace tokens basic / candidate / sentence endings: block/br/table-cell boundaries → spaces, inline tags stripped зі збереженням attached punctuation, whitespace normalized. Це не linguistic word tokenizer. 18 candidates → 16 visible basic / 2 meaningful details.

| Page § | B/D/S | Decision / reason |
|---|---|---|
| B2 §1 | 79/55/6 | basic: tendency vs relative meaning — core |
| B2 §2 | 158/34/4 | basic: suggest interpretation vs proposal — core |
| B2 §3 | 163/39/3 | basic: adverb functions and tend — core |
| B2 §4 | 28/52/4 | basic: explicit 20 vs 45 comparison essential |
| B2 §5 | 17/26/4 | basic: short request vs possibility caveat |
| B2 §6 | 0/49/6 | basic: complete known fact/possible cause contrast |
| C1 §1 | 81/41/4 | basic: alternative cause and causal limits |
| C1 §2 | 177/23/3 | basic: short concrete claim-limit caveat |
| C1 §3 | 89/41/4 | basic: unsupported proves vs records contrast |
| C1 §4 | 0/181/16 | basic: all four negation meanings essential |
| C1 §5 | 78/47/6 | detail: coherent paragraph/recommendation/should-scope analysis, point1 |
| C1 §6 | 32/38/3 | basic: corrected known result — core |
| C2 §1 | 150/20/2 | basic: short criterion boundary |
| C2 §2 | 218/56/5 | basic: significance warning and direct measure — core |
| C2 §3 | 136/54/4 | basic: hedge is not evidence — core |
| C2 §4 | 123/22/2 | basic: one source is not consensus, short caveat |
| C2 §5 | 118/32/3 | detail: coherent source/own stance and criterion boundary analysis, point1 |
| C2 §6 | 25/58/5 | basic: constructive method critique — primary contrast |

У C1/C2 §5 увесь EN paragraph/mini-review і повний UK translation залишаються перед кнопкою; відкривається лише відповідний точний author analysis. Details counts **0/1/1**. Чотири short candidates <30 words (26/23/20/22) visible basic, без переписування. Unknown/mismatched runtime payload показує повний stored native basic, не обрізаний текст.

## Інтерактивна практика й linked banks

Усі 18 original self-check prompts і повні explained keys збережено буквально; кожен case рівно один раз. На сторінку 2 select + 2 choice + 2 token/manual, власний linked widget. Finite source mappings:

| Page | selects | choices | inputs |
|---|---|---|---|
| B2 | 2,5 | 1,3 | 4,6 |
| C1 | 2,4 | 1,3 | 5,6 |
| C2 | 2,5 | 1,4 | 3,6 |

C2 reporting-verb choice: A/B/C (suggests/states/writes) accepted за author key, D proves rejected. C1 negation feedback пояснює strength/scope, не оголошує grammatically valid do not універсальною помилкою. Open paraphrase tasks мають visible author criteria; finite scorer приймає лише явно схвалені examples/aliases, не претендує на універсальну семантичну оцінку. B2 two-sentence answer зберігає спостереження і cautious cause. Токени 1–3 words, canonical answer доступна click-only; initial test виявив 4-word C1 group, механічно split `may have helped,` + `but` без зміни відповіді. Tests не послаблено.

Fresh SELECT inventory перед projection: MySQL `localhost:3306`, physical database `gr2`; exact UK/theory root category54 `academic-english`. Actual own type4 single-level primary banks (`Database\\Seeders\\V3\\Polyglot\\`), all-level banks не підставлялися:

| Test URL | Primary seeder | Level / count | IDs |
|---|---|---|---|
| [B2 test](http://gramlyze.loc/test/academic-english/hedging-and-cautious-language-basics) | PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder | B2 /48 | 17457–17504 |
| [C1 test](http://gramlyze.loc/test/academic-english/hedging-and-cautious-language) | PolyglotHedgingAndCautiousLanguageC1LessonSeeder | C1 /48 | 17889–17936 |
| [C2 test](http://gramlyze.loc/test/academic-english/stance-register-and-evaluation) | PolyglotStanceRegisterAndEvaluationC2LessonSeeder | C2 /48 | 18705–18752 |

## Перевірки, local apply та acceptance

Перед apply: primary inventory `storage/app/seo-m33-local/m33-bank-inventory-v1.json`; 23 exact M26–M32 regression owners, 19 protected tables plus text_blocks. Non-target6394 blocks SHA-256 `0f3d11710af4e7d1376b1488a0fe0108924c7e65987339a312ffffbaee458721`.

Перша guest HTTP capture завершилася timeout30s на M26 overview після 3 target GET200; не замовчується й не приписується серверу без доказів. Окремі fresh GET200 probes: overview0.862384s, B2target0.339372s. Окрема повторна full capture **30/30 GET200**, ordered sitemap554 SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Private failure evidence `failed-before-v1.json`, baseline `before-http.json`.

Pre-apply Node M33: 108/108 PASS (practice94, browser contracts7, runtime contracts7); практика містить 58 real changed-answer semantic negatives. Vitest existing JS: 8 files /61 tests PASS; source CSS compilation in-memory, assets output не змінювався.

Перший integrated PHP run `m33-integrated-v1` — **NON-PASS**, runner/child exit1: 394 tests /7091 assertions, 1 failure у diagnostic word-count assertion, 1 existing deprecation. Причина: generator `strip_tags` і тестове ordered-corpus plain (кожен tag → space) використовували різні whitespace proxies; другий міг утворювати окремий punctuation token. Core wording/order/translation/punctuation assertions не провалилися. Stderr empty; 46648 protected file hashes unchanged, before/after digest `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`. Private evidence: `storage/app/m30/storage/app/seo-m2-local/m33-integrated-v1-d2c0aa8c05a746c48960fc4a521215b2-result.json`. Apply не запускався; потрібний повний повторний прогін після виправлення узгодженості діагностичного counter, без послаблення fidelity перевірок.

Correction: окремий diagnostic helper з block-boundary separators, inline punctuation attached; ordered-core plain незмінний. Додано explicit boundary/punctuation fixture, усі 20 semantic mutations та ordered-copy assertions збережено. Незалежне порівняння зі saved v1 fixture довело: лише 13 `basic_words` чисел змінилися; 3 canonical byte hashes, усі `after`/`plans`/practice/author text, candidate fragments/reasons/decisions і всі detail word/sentence counts unchanged. Package hash змінився лише через diagnostics. Pure 18 source-section total/candidate checks і PHP lint PASS; повний integrated-v2 потрібен до apply.

Final integrated PHP `m33-integrated-v2` **PASS**: 32 suites /395 tests /7177 assertions, runner/child0, 07:02.240,100MB, stderr empty, failures/errors/skips0. Existing unique `PDO::MYSQL_ATTR_SSL_CA` deprecation1 не прихована. Own M33 suites: Package5/513, Patch20/50, Guard5/10, Fidelity26/433. SQLite `:memory:`, testing, array cache/session, CLI OPcache off, working `.env` not loaded. 46648 protected file hashes unchanged, before/after digest `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`. Private evidence: `storage/app/m30/storage/app/seo-m2-local/m33-integrated-v2-b67e429ce86e4b069227b9b4fc108ebc-result.json`. Final combined Node 14 files /328 tests PASS, failures/skips0; Vitest61/61 separately. PHP lint16/16 and5JSON parse/exact hash/canonical checks PASS. Working sources залишалися frozen протягом тестового run.

Finite ROOT source sync після всіх PASS: 16 files, exclusive backup `storage/app/seo-m33-local/source-backup-7b19ae3b51f4a73f`; accepted old bytes перевірені до запису. Перший read-only sync check у sandbox уперся в Git ownership; повторено в owner context без global `safe.directory` changes, no-write check PASS.

Fresh physical/vhost/CLI-web proof 2026-10-04T11:57:31Z: Windows local MySQL listener/PID/server підтверджено, Apache sole vhost → `d:/dev/htdocs/gramlyze.loc/public`, application ROOT той самий, MySQL `localhost:3306/gr2`. APP environment `production`, але SiteMode для `gramlyze.loc` = `development`; це локальний production-profile, не запит до production server. Read-only temporary nonce route: exact HTTP/host/port, raw loopback, GET only, forwarded headers rejected; GET з `X-Forwarded-For` →404. Safe runtime identity і nonce-bound digest only, без credentials. Raw route before SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; `.env` before SHA `b02694c5c2643f7ae217edbf2f6362638bcb0f8d5f8f74fb5388df25a07d514b`.

Fresh preview `m33-preview-v1.json`: before, 3 updates /21 inserts /0 deletes, digest `921ebc758ac6e5a598f16611af93cf337162878ba463af45eb4cb5879573e082`. Exact-field review PASS: all row fields/UUIDs/owner/type/body/order/locale, protected metadata/relations/timestamps, all20 fingerprints against fresh inventory, and16-file source backup verified. Existing box IDs8766/8783/8831 and UUIDs retained; update allowlist only `type/body`.

Actual transactional apply — PASS: **3 updates /21 inserts /0 deletes**. Exclusive DB backup `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m33-local/m33-before-v1.json`, SHA-256 `a9bf45d399fa0d981faf5c5037930539b331afb20354f684735c074c98bec1c1`; не додається до Git. Повторна та сама guarded команда повернула **no-op,0 updates/0 inserts**; вказаний `m33-unused-no-op.json` не створено.

Після apply/no-op тимчасовий proof-route видалено. Raw routes SHA повернувся точно до `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, `.env` SHA незмінний; fresh guest GET nonce path → **404**. Read-only after-apply inventory 2026-10-04T12:08:24Z і exact verifier — PASS: text_blocks **6397→6418**, exact bodies/type/locale/order/UUID match preview; **19 інших таблиць**, **23 M26–M32 owners**, subtitle/hero IDs та **6394 non-target blocks** unchanged. У кожного M33 owner10 rows і48 власних bank questions. Evidence `storage/app/seo-m33-local/m33-after-apply-v1.json`, SHA-256 `8df0da34f78934725901f00aeca357d97a325c0106f0f3fd2b81fcee053e0a6c`.

Fresh after HTTP capture 2026-10-04T12:10:09.102Z: **30/30 GET200**, Content-Type/X-Robots-Tag та title/H1/description/OG/Twitter/canonical/robots unchanged; accepted old fragment anchors retained. Ordered sitemap **554/554**, before == after SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Exact unchanged control main-text hashes і accepted detail counts PASS, target actual disclosures **0/1/1**. Evidence `storage/app/seo-m33-local/m33-acceptance-v1-http.json`.

Live Chromium acceptance `m33-acceptance-v1` — **PASS**, exit0: **12 M33 states** (1440×1000/390×844, light/dark,3 lessons), **21 no-JS basic/markup checks**, **18 M27–M32 normal-JS regression pages**. Guest/GET-only, no production or policy violations; максимум4 contexts/process. На M33/М30–M32 no-JS буквально перевірені всі6 author prompts і повний explained key. Для M27–M29 цей matrix перевіряє basic/practice markup, не маскує старі hidden token banks як повне no-JS practice приймання.

M33 normal-JS: correct/wrong/reset, approved aliases, contextual C2 wrong-proves feedback, manual typing, optional final punctuation, click-only complete token answer, backspace/reuse, no autocomplete popup/token fetch, linked own bank/48, actual changed-answer semantic negatives — PASS. Detail mouse/Enter/Space/focus-visible/own-point placement/independence, reload closed, own detail or preserved basic/practice deep fragments, print expansion and state restoration, no detail fetch — PASS. На кожному M33 уроці максимум1 detail, тому simultaneous-open на ньому not applicable; multi-detail regression pages перевірено окремо. Повний basic і EN/UK paragraph перед кнопкою залишаються visible.

Усі51 page contexts: page errors0, unclassified console errors0, failed local requests0, HTTP errors0, font failures0; **21 expected disabled-script events** у no-JS — окремо, не помилки застосунку. 92 bounded PNGs лише в private storage; root переглянув representative desktop/mobile frames, independent visual QA —25 frames light/dark/all3lessons. Контент читабельний, tables retain widths880/800/780 і columns130/170.

**Learning overflow0** для main і всіх visible cards/points у всіх12 M33 states; таблиці мають власний horizontal scroll. Strict document overflow не називається нульовим: desktop0; mobile B2 light/dark6/8px, C1 7/0px, C2 9/0px. Random decorative-shape excursions виміряно окремо: max12.98px у M33 mobile; це не learning overflow. Фон не змінено, глобального `overflow-x:hidden` не додано. Окрема inherited shared-style observation: слабкий контраст жовтого C2 level badge у dark mode (private `...stance-register-and-evaluation-390-dark-section-7.png`), unchanged shared header style, не M33 author/projection regression; поза цим scope.

Extended M26 `m33-regression-v1` — **PASS**, exit0:5 fresh GET200,20 desktop/mobile/light/dark states,5 no-JS native-detail pages, **56 exact accepted point details**. Keyboard/mouse/own-point semantics, full basic/detail fidelity, reload, deep fragment, print and native no-JS opening/closing without fetch preserved. Learning overflow PASS/0 unclipped producers; strict document overflow false (mobile max15px), measured separately. Page errors/local failed requests/HTTP/font failures/policy violations0; цей inherited harness не збирає console errors, тому нуль для них не заявляється. Evidence `storage/app/seo-m26-local/m33-regression-v1-browser.json`, accepted HTTP baseline SHA-256 `98cf3ea7185151d99d8dbb20be23226c1847bef0bbc28ad56bfb90eb2b03c484`.

| Accepted package | Actual retained detail count |
|---|---|
| M26 | 56 |
| M27 | 13 (3+7+3) |
| M28 | 0 |
| M29 | 0 |
| M30 | 0 |
| M31 | 2 (0+1+1) |
| M32 | 1 (0+1+0) |
| M33 | 2 (B2/C1/C2:0/1/1) |

Final after-browser SELECT 2026-10-04T12:27:40Z і exact verifier — **PASS**, exit0:3 updates/21 inserts/0 deletes, text_blocks6397→6418,19 protected tables unchanged,23 regression owners unchanged,6394 non-target blocks exact old digest, all actual target body/type/order/locale/UUID match fresh preview; own banks48/48/48 unchanged. Evidence `storage/app/seo-m33-local/m33-after-browser-v1.json`, SHA-256 `623c1f03985cfbf9612772c99fce166fc21da79c1e310234d3eea9a42f1e3b73`. Один verifier було викликано перед завершенням SELECT-inventory і створенням evidence: file-not-found / exit1, не DB mismatch; після фактичного завершення inventory перевірка PASS. Final ROOT HEAD41820a2 unchanged, raw routes/.env SHA match baseline; no cache/session/config clearing.

## Окреме історичне no-JS обмеження

Actual strict probe `m33-legacy-nojs-v1` — **NON-PASS, exit1**, окремо від M33 acceptance: normal-JS M26 practice **4/4 PASS**, no-JS M26 full token context **0/4**, M27–M29 full practice context **2/9**. Саме **11 pages /22 hidden token banks**; network/console/page/HTTP/font/policy errors0. Accepted token branch unchanged byte-for-byte against `ceb2162c862978ca461607be3913a9f717f380b2`, fragment SHA-256 `1cd9f920295ad119a31242fb308d0201d5f6c5daaf339b4f4088e39c4b000d77`. Це не маскується як повне no-JS practice PASS і не ремонтується поза M33 scope. M33 full author prompts/keys без JavaScript — окремо PASS.

Affected pages,2 missing banks each:

| Package | URL |
|---|---|
| M26 | [Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) |
| M26 | [Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives) |
| M26 | [Questions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions) |
| M26 | [Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) |
| M27 | [Linking Words: Reason, Result, Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) |
| M28 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) |
| M28 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) |
| M28 | [Advanced Fronting and Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) |
| M29 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) |
| M29 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) |
| M29 | [Ellipsis, Substitution and Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) |

Evidence `storage/app/seo-m31-local/m33-legacy-nojs-v1-browser.json`. Advanced Linking Devices і Concessive/Contrastive Structures мають fully visible plain-text manual context —2/2 PASS; не зараховуються до11 affected pages. Усе це pre-existing accepted limitation, не погіршення M33.

## Versionable scope

Перша **staged** diff-check виявила new blank line at EOF у12 нових PHP files; попередній unstaged check не охоплював ці untracked files. Прибрано лише2 trailing LF у кожному з12 WT files і4 синхронізованих ROOT copies. Pure comparison against pre-clean staged content через `trimEnd()` —12/12 exact; ROOT4 == WT4. Independent apply-bound check: для4 змінених code source fingerprints `hash(current_bytes + 0a0a)` буквально дорівнює reviewed preview hash. Отже preview/source-backup hashes описують historical pre-format apply, а не нові raw EOF hashes; author packages/canonical definitions/views/DB незмінні. Після cleanup: Node M33 **108/108 PASS**, PHP lint **16/16 PASS**, saved after-browser DB verifier знову PASS.

Post-EOF isolated PHP `m33-post-eof-v1` — **PASS**, child/runner0:4 suites /56 tests /1006 assertions,01:09.412,68MB, stderr empty,1 unchanged existing deprecation. Working `.env` not loaded, SQLite`:memory:`, array cache/session, CLI OPcache off. **46648 protected files /0 changes**, before == after digest `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`. Private evidence `storage/app/m30/storage/app/seo-m2-local/m33-post-eof-v1-1bd635cfa8474f3db28e56b45e56ae6b-result.json`. Final restaged **git diff --cached --check PASS**; ні tests, ні fidelity assertions не послаблювалися.

33 пов'язані M33 files; shared views finite `m33_v1` opt-in, prior branches preserved. `.env`, temporary route, private backups/runtime evidence/screenshots/dumps, vendor/build і сторонні незавершені зміни не входять до commit scope. Independent exact source/scope/secret review PASS; JSON parse5/5, PHP lint16/16, targeted tests та diff check PASS. Normal commit/push тільки у `codex/seo-m33-m17-academic-english-layers`; full commit SHA, commit/report URL і фактичний remote SHA == local HEAD фіксуються у фінальному повідомленні після push, без self-referential report commit. Production не перевірявся й не змінювався. Автоматичного переходу до M18 немає.
