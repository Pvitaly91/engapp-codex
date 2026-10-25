# M89 — Present Perfect / Present Perfect Continuous

Локальне впровадження шести сторінок завершене. Інтерактивна практика прийнята на реальних сторінках; strict regression/body parity та decorative-shell responsive обмеження наведено окремо, без загального «усе PASS». Ціль усіх HTTP/UI дій — http://gramlyze.loc. Main, .com/.ub, сервери, PR і merge не змінювалися.

## Сторінки, ідентичність і фактична практика

Identity у таблиці — suffix точного `Database\\Seeders\\Page_V3\\Tenses\\...`; повні identity/category/ancestry bindings зафіксовано в `docs/content/m89-native-bindings.v1.json`.

| Owner / перевірена сторінка | Identity suffix; Page/category | Display; SQL practice level | OLD → AFTER own controls; form / meaning / full input | Widget |
|---|---|---|---|---|
| A [Present Perfect: Questions and Short Answers](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-questions) | PresentPerfect\\PresentPerfectQuestionsTheorySeeder; 176/29 | A2–B1; NULL | 4 → 6; 2 / 2 / 2 | existing 10638, 5/5 |
| B [Present Perfect: часові маркери](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-time-expressions) | PresentPerfect\\PresentPerfectTimeExpressionsTheorySeeder; 177/29 | A1–C2; NULL | 4 → 6; 2 / 2 / 2 | existing 10665, 5/5 |
| C [Present Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms) | PresentPerfectContinuous\\PresentPerfectContinuousFormsTheorySeeder; 375/30 | A2–B1; NULL | 6 → 8; 4 / 2 / 2 | existing 12192, 5/5 |
| D [Present Perfect Continuous: Negatives](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-negatives) | PresentPerfectContinuous\\PresentPerfectContinuousNegativesTheorySeeder; 376/30 | A2–B1; NULL | 6 → 8; 4 / 2 / 2 | existing 12219, 5/5 |
| E [Present Perfect Continuous: Questions and Short Answers](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-questions) | PresentPerfectContinuous\\PresentPerfectContinuousQuestionsTheorySeeder; 377/30 | A2–B1; NULL | 6 → 8; 4 / 2 / 2 | existing 12246, 5/5 |
| F [Present Perfect Continuous: Time Expressions](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-time-expressions) | PresentPerfectContinuous\\PresentPerfectContinuousTimeExpressionsTheorySeeder; 378/30 | A2–B1; NULL | 6 → 8; 4 / 2 / 2 | existing 12273, 5/5 |

Усі шість HTTP 200. B зберігає raw title/H1 «Present Perfect: часові маркери»; широкий display A1–C2 не став question-level фільтром. Категорії 29/30, ancestry tenses/present-perfect або tenses/present-perfect-continuous, усі continuing levels і фактичний NULL practice filter збережені. Відібрано 29 A2 та одне A1 питання (F №22744), а не вигадане exact-A2 обмеження.

Разом: 32 retained/corrected source controls + 12 нових meaning controls = 44 graded controls (20 form, 12 meaning, 12 full inputs). Це не 44 нові DB questions. Окремо — 30 наявних type-4 widget questions; вони не додані до own-control total. Нуль ungraded demos і нуль нових practice rows.

## Контент, OLD → AFTER і мовні рішення

Збережено native component sequence кожного owner: hero/rule cards → forms grid → usage panels → comparison table → mistakes → summary → практика → нижня навігація. Усі вісім source block slots продовжуються, практика лишається source index 6 / SQL sort_order 7 / left. Змінюються навчальні дані, не компоненти, кольори, рамки, шрифти чи відступи.

| Owner | Retained/corrected навчальна функція | Два нові справжні meaning controls | Paired mistake notes |
|---|---|---|---|
| A | have/has, V3, нейтральні Yes/No та wh/subject questions; заданий запит про конкретний минулий час; дві повні побудови питань | хто виконав дію у Who…; що підтверджує позитивна відповідь однієї мовчині про досвід | 5 |
| B | значення/позиція already/yet/just, for/since, відкритий/закритий часовий контекст; дві повні часові конструкції | since як початкова точка, не кількість років; результат so far у ще відкритому ранку | 5 |
| C | have/has been + -ing; узгодження; збережені дві старі a/b form choices; дві повні відповіді | тривала активність/недавні ознаки процесу без вигаданого точного результату | 3 |
| D | haven't/hasn't been + -ing; старі a/b form choices; enough/much/long у заданому негативному контексті | недостатня/відсутня активність без висновку «ніколи» або довільного періоду | 3 |
| E | auxiliary-before-subject, wh, how long, subject questions та короткі відповіді; старі a/b збережені | тривалість проти частоти; видимий смисл питання/відповіді без додавання фактів | 3 |
| F | for/since, all morning, lately/recently, відкритий часовий інтервал; старі a/b збережені | значення початкової точки/тривалості та поточного/недавнього періоду | 3 |

Повні finite component/task/coverage maps: `m89-present-perfect-completion-continuous.components.v1.json`, `.tasks.v1.json`, `.coverage.v1.json`. Старі grammar choices C–F залишаються form controls, а не перейменованими meaning tasks. Дванадцять нових meaning controls мають власні IDs/ключі/пояснення в author master; жодного question-family insert/update не виконано.

22 пояснення до пар wrong/right видимі в basic. Semantic audit залишив 0 meaningful point details: це короткі пов'язані ремарки, їх не приховано за word-count runtime rule і не переписано заради квоти.

Мовний audit та межі джерел збережено в `m89-present-perfect-completion-continuous.editorial.v1.json` і трьох `m89-author-parts`. Зокрема:
- [British Council: Present perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/present-perfect) і [Cambridge: wh-questions](https://dictionary.cambridge.org/grammar/british-grammar/questions-wh) — neutral inversion, subject-wh та часовий задум A.
- [British Council: just, yet, still, already](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/present-perfect-just-yet-still-already) — B; формальні позиції не подано як універсальні заборони всіх інших контекстів.
- [Cambridge: Present perfect continuous](https://dictionary.cambridge.org/grammar/british-grammar/present-perfect-i-have-been) і [Simple or continuous](https://dictionary.cambridge.org/us/grammar/british-grammar/present-perfect-simple-or-present-perfect-continuous) — C/D, activity/state, процес проти результату.
- E/F: wh/short-answer, for/since і lately/recently висновки мають окремі primary-source записи в editorial. Стани, позитивні короткі відповіді, неправильні дієслівні форми й невизначені результати не зведено до механічного «завжди Continuous».

## Ручні відповіді, aliases і токени

Дванадцять canonical full inputs:
- A: Have you ever been to Canada? / How long has she lived here?
- B: Leo has already finished the task. / We have not heard from her yet.
- C: I have been studying English for two hours. / Maya has been working on the project all morning.
- D: Olena hasn't been studying enough lately. / They haven't been checking the equipment this week.
- E: How long have you been waiting? / Has she been working on the report?
- F: It has been raining all morning. / They have been travelling a lot recently.

Видимі умови задають потрібну construction/лексику/порядок; target answer не перенесений у hint/placeholder. Перемішані групи по 1–3 слова утворюють canonical рядок; manual criterion не звужений прихованою умовою token-only. Успадкований accepted-only behavior перевірено окремо (3 controls / 6 aliases), не замінено глобальним normalizer.

Всього 50 явних positive aliases для 44 controls. Для B0 `Leo's already finished the task.` додано саме новий explicit accepted alias, видимий дозвіл і plain feedback. Старий matcher не приймав Leo's автоматично — це підтвердив окремий probe; попереднє хибне audit-формулювання виправлено. Canonical і старий DB bank не змінено. B1/D/F також перевірені повні/скорочені дозволені варіанти. Один набір токенів доводить canonical, а не всі можливі manual aliases.

Окремо дозволене користувачем shared виправлення: opt-in `complete-loaded-verb-hints-v1` у exact-bound presenter. Приймається лише точний повний loaded snapshot усіх атрибутів hints та окремих hint options, з точним owner/keyset/order/multiplicity/types/hash. Hint option не підмішується в NULL/0 token BANK. Змінені, відсутні або зайві hint дані відхиляються; legacy без opt-in лишається строгим. Жодного запису/очищення verb_hint, formatter/grader чи original-model mutation.

## Шість thematic widgets: фактичні 5/5

Збережено шість existing practice IDs/UUIDs; внизу кожного — рівно один «Вправа 4. Побудуй речення». Використано тільки наявні головні тематичні банки, 72 eligible rows на owner. 30 exact-bound selections і всі settled answers опубліковані в `m89-sentence-builder-bank-audit.v1.json` / `m89-widget-selection.v1.json`.

| Owner | Exact selected existing question IDs | Фактично пройдено |
|---|---|---|
| A | 22109, 22110, 22114, 22115, 22117 | 5/5 |
| B | 22182, 22183, 22185, 22186, 22189 | 5/5 |
| C | 22539, 22540, 22542, 22545, 22548 | 5/5 |
| D | 22611, 22612, 22613, 22617, 22621 | 5/5 |
| E | 22683, 22684, 22686, 22691, 22692 | 5/5 |
| F | 22744, 22755, 22757, 22758, 22759 | 5/5 |

Client штатно перемішує п'ять питань. Browser перевірив весь unique ID set через точну видиму умову, не вигаданий fixed display order; усі 30 visible settled strings після Check збіглися. Submit після оцінки прихований; UI це підтверджує, а offline unchanged-native replay окремо перевіряє idempotence.

Межа №22548: незмінений native normalizer приймає case-only перестановку The/the. Canonical output правильний; strict spelling assessment не заявляється. 12 169 adversarial cases у кожному offline source context не дали wrong-lexical acceptance; це не 24 338 browser cases.

## Локальний apply, резервування та postconditions

Свіжий physical + web/CLI proof підтвердив саме Windows served ROOT, loopback Apache та одну MySQL gr2:3306 connection. Тимчасовий nonce GET: тільки gramlyze.loc/http/loopback, без forwarding/cookies/auth/origin/query; read-only transaction із rollback, лише safe identity і nonce-bound digest. Секрети не поверталися. Після apply/no-op файл routes/api.php точно відновлений разом із сторонніми bytes, окремий anonymous loopback GET повернув 404.

Перед apply незалежний exact diff review вивів 50 операцій над 50 distinct existing text_blocks rows: [8,8,9,9,8,8] по A–F, з них 48 continuing block bodies + 2 subtitle bodies C/D. Це не «48 total». Page.text лишився NULL. Zero Page updates/inserts/deletes/question-family writes.

Exclusive backup створено до першого update, він byte-equal reviewed preview (raw SHA 2712db0f2c55f1411560d1493f8f44689b42e9ef3f4ce516a10f73e738bc016b). Транзакція applied 50; repeated apply дав no-op, updated/inserted/deleted 0, backup не перезаписувався.

Два незалежні SELECT-only AFTER scans, другий після UI:
- 46 таблиць і повна schema звірені; нормалізація лише рівно погоджених 50 body fields відновлює весь незалежний BEFORE.
- 54 continuing UK та 108 EN/PL rows, усі metadata/UUID/order/timestamps поза body збережені.
- Усі 14 старих банків / 912 original questions, options/answers/hints/tags/links і 14 saved tests/membership/order/timestamps exact.
- Нецільові pages, категорії/descriptions, progress/session-related SQL, інші banks і seed_runs незмінні.
- Фінальний repeat raw SHA: 9c767e20b0a5a98e95d9ed3f9c72ec83a8798fd70106821580161c77a3e6f0a9; попередній AFTER: 9e57fff3d57786cdfd5295a88da29ffe0d4258b02f1848809997a0fac6898140. Обидва — private receipts, не Git payload.

Нижня навігація: 24 labels/current/order/URLs дослівно збережені; усі перевірені links exact, нового secondary footer немає. Нові canonical/SEO routing policy не вводилися; production canonical у наявному local HTML — лише прочитані metadata, жодного production GET.

## Автоматичні й браузерні перевірки

M89 targeted profile: 65 unique methods (49 нових package/runtime/source/native + 16 hint unit), кожен із двох запусків — 7808 assertions, failures/errors/skips 0, child/runner exit 0. Разом 130 executions / 65 unique, 15 616 assertions. Окремий попередній запуск hint unit входить у ці methods, не збільшує unique total.

Обидва процеси: isolated SQLite :memory:, array cache/session, робоча .env не завантажена, CLI opcache off; 675 protected paths незмінні. Другий — ROOT application із WT test/source/native-factory profile, не окремий WT application bootstrap. Кожен process зберіг один існуючий PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` у config/database.php:62 (24 triggering methods). Це не прихований warning-free PASS.

Фактичний Browser API на шести справжніх сторінках, не DOM-preview:
- 36 distinct states: 6 × 1440/390/320 × light/dark; усі H1/native practice/widget/raw-marker та input-bound checks виконані.
- 48 distinct canonical manual/token cases: 12 inputs × 2 modes × 1440/390.
- Усі 72 option cases і 50 explicit positive aliases — correct/incorrect feedback і literal пояснення перевірені.
- 24 delete-return + 24 mixed manual/token + 24 clear/rebuild, 12 wrong-context cases.
- 18 group probes: empty 0, усі canonicals дають full score, повторний Check не додає балів, reset очищує score/answers.
- 22 paired notes видимі в basic; 24 footer items звірені.
- 6 × 5/5 widgets, 30 settled strings; screenshots приватно збережені.
- Тему повернено до початкової dark, viewport override скинуто, zoom не змінювався, personal progress не очищувався.

Layout кваліфікація: усі 36 input bounds помістилися; у шести реальних 390px practice regions internal overflow = 0. 12 desktop states чисті за document-width guard; 24 resize-time mobile states мають decorative shell overflow, інколи великий transient після desktop resize. Окремі fresh 390px readings дали document scroll 401/398/394/406/398/390 при viewport 390 — не learning-card overflow. Shared shell використовує 100vw і rotated random empty spans без clipping; відповідні CSS/JS/layout bytes точні BEFORE. Exact попередні viewport values не виміряні, тому це existing mechanism/risk, не доведена рівність конкретного старого overflow. Table min-width 36rem має власний horizontal scroller — третій, окремий результат. 36 безумовно чистих responsive states НЕ заявляються; CSS не маскує дефект.

Print/no-JS/WCAG: not_checked. Browser controls не використовували приховане mobile menu; page evaluate лише читає DOM, не викликає Alpine/app/grader methods. Preflight-помилки UI harness (scrollbar/client-width, hidden mobile theme control, regex apostrophe, fixed question-order assumption, parse error before actions) збережені приватно; це не виправлення застосунку і не підміна фактичних interaction results.

## Реєстр і строгі старі регресії

Whole-registry AFTER: 229 targets, 0 failed, 0 writes; exact fresh BEFORE union 223 + шість тепер завершених M89. Усі 223 попередні semantic rows/order exact (виключений тільки whole-HTTP hash, cause не припускається), new-owner field deltas/missing/extra порожні. Це не permanent hardcap і не browser acceptance замість UI. Raw registry SHA 8db9c0efc9e30c462cb93be3249c1092ba7a83793c917d288bce0bbc93b55251.

Старі 19 profiles запущено по одному разу до і після, accepted test bytes/parser/runner не змінені:
- BEFORE: 1016 executions / 923 unique / 93 overlap, 214693 assertions, 63 failure executions / 55 unique failing methods, 1 error.
- AFTER: 1016 / 923 / 93, 214223 assertions, 66 failure executions, 1 error, skips 0, warning 1, deprecations 19. Child/runner exits: 2 × 0, 16 × 1, 1 × 2. Collector wrapper exit0 не означає suite PASS.
- 3 NEW M88 failures: `M88SourceBoundaryTest` methods `test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile`, `test_native_validator_builder_formatter_and_grading_engine_keep_their_independent_raw_bytes`, `test_attributes_keep_root_absent_and_worktree_exact_prefix_or_explicit_reviewed_suffix`. Усі зупиняються на старому controller raw-profile guard, line 88, до downstream assertions.
- Точне read-only видалення лише чотирьох M89 controller lines відновлює captured BEFORE і accepted M88 AFTER в ROOT/WT. Це пояснює byte-guard failure, але НЕ перетворює його на PASS і не доводить непройдені downstream assertions.
- 5 existing nonpassing methods мають змінені literal source-hash messages; 2 still-passing registry-loop methods отримали +42/+6 assertions. Нуль resolved/added/missing methods. Строгий comparison status — FAIL / STRICT_DIFFERENCES_REPORTED; raw receipt b6a6f92dd0be77fe7c5d34d835bac673d42eeaba12510d5850d156d4c504231b.
- Історичні тести/snapshots не послаблено. M88 behavior, aliases і навчальні sources збережені; source-boundary strict PASS не заявляється.

## Старі HTTP consumers: не нормалізований PASS

Одноразовий old54 AFTER: 52 × 200 + 2 відомі 404 (`/test/present-perfect/questions`, `/test/present-perfect-continuous/questions`: requested SSR route collision з JSON route). Ці 404 не прийняті як SSR200 і мовчки не ремонтовані. 22 complete entries exact, 32 відрізняються лише body hash: 6 course + 26 successful SSR; 20 JSON exact, дві 404 entries exact. Причина whole-body drift не виводиться з припущення про shuffle. Full SQL/bank/saved membership exact.

Одноразовий extra22 AFTER: 22 × 200; усі 46 schema/fingerprints exact. Повна literal entry parity 0/22 через snapshot basename + body hash; у чотирьох Past Perfect lessons додатково main_regions. Окремі H1/title/canonical/description/robots/breadcrumb/LearningResource/main-links/anchor observations exact — не заміна full-entry PASS.

Фактична read-only attribution цих чотирьох main_regions: змінився рівно inline `questions: [...]` рядок, prefix/suffix поза ним дослівно однакові. Всі п'ять serialized question IDs на кожній сторінці інші, а не лише nonce/style fields; це може змінити інтерактивні питання браузера. Collector textContent включає script text, тому text/hash також різні. Статичний authored lesson HTML поза цим рядком точний; ЧОМУ сервер відібрав інший набір — unknown. Немає додаткового GET до випадкового збігу, нормалізації або FAIL waiver. Це не доводить причину 32 old54 hashes.

## Source boundary, privacy та доставка

0 нових visual-source змін: 326 visual files і 2979 historical frozen files exact; усі 8478 paired primary source paths враховані. Лише 8 shared dispatch files / 12 additive M89 lines + окремо погоджені 3 hint-presenter hunks. Жодного m89-style/section/detail renderer. Caller-owned theory boundary залишає курси, EN/PL і standalone question presentation на captured old bodies/data. Усі 25 сторонніх untracked files у WT raw-exact і виключені зі staging.

Фінальний scoped allowlist — 49 файлів. WT `.gitattributes`: тільки 39 точних -text rules для 33 нових owned paths і 6 definitions; історичний raw prefix не змінений, ROOT .gitattributes лишився absent. Стандартний Git clean для matcher/manifest і .gitattributes переводить CRLF у LF; index цих трьох не дорівнює disk raw. Для shared services це HEAD + одна дозволена line; для attrs — HEAD LF prefix + 39 rules. Controller існуючий -text зберігає exact CRLF. Не було forced-raw index/config/EOL rewrites.

Default diff --check відмічає чотири CRLF controller additions як trailing whitespace; per-invocation `-c core.whitespace=cr-at-eol` перевіряється окремо без зміни config. Попередження attrs LF→CRLF не приховано: майбутній checkout не відтворить captured mixed attrs bytes автоматично. Поточні raw prefix/suffix та приватні guards збережені; checkout/reset не виконувалися.

У Git не включаються .env, secrets, private SQL/HTTP snapshots, proof/routes, preview HTML, screenshots, dumps/backups, runtime caches/generated assets або сторонні dirty changes. Public author/bank artifacts містять навчальний contract, не runtime credentials.

Робоча гілка: `codex/seo-m89-present-perfect-completion-continuous-standard-view`; accepted base `f0181dc0c85444900a8696c7f0c6edbe07b2a5d6`. Цей звіт входить у scoped M89 commit; його повний SHA та незалежний remote-SHA результат наведені у фінальній відповіді, без самопосилального SHA всередині того самого коміту. Normal push не є deploy.
