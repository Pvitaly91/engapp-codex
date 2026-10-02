# M27 — Linking Words B2–C2: застосовано й прийнято локально

## Статус і межі

2026-10-02. Guarded transactional apply виконано на робочому `http://gramlyze.loc`.
Успішні postconditions, повторний no-op і live acceptance трьох цілей.

База: `47491c43f820d2efd1756e6022e6c7114f85c1c5`.
Після fetch погоджена M26-гілка не мала новішого descendant. Робоча гілка:
`codex/seo-m27-m11-linking-words-layers` у clean tracked worktree
`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`.
Пов’язаний перевірений пакет призначений для commit і звичайного push у цю гілку.
Фінальний commit SHA та перевірка remote SHA наведені у фінальній відповіді, щоб не створювати циклічний SHA у самому commit.
Main, PR, merge, reset, force push, deploy, dependencies, XAMPP і hosts не змінювались.

Production не перевірявся й не змінювався.

## Три цілі

- [Linking Words for Reason, Result and Contrast — B2](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast)
- [Advanced Linking Devices — C1](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices)
- [Concessive And Contrastive Structures — C2](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures)

Identity prefix: `Database\Seeders\Page_V3\ClausesAndLinkingWords\`.
Точні suffix: `LinkingWordsReasonResultContrastTheorySeeder`,
`AdvancedLinkingDevicesTheorySeeder`, `ConcessiveAndContrastiveStructuresTheorySeeder`.
Усі — uk, theory, category `clauses-and-linking-words`.
Базові blobs звірені: `d2d9db46135adda6e1829593628e5b6047810310`,
`1ab74965a35d6677912b577384a817f3297f3263`, `1358fb75ffa5dd108a1a32b96befdbf2314837a9`.

## Джерела та повноцінний basic layer

Старий accepted M11: subtitle + hero + один великий авторський HTML box для кожної цілі.
Повний versioned before-state збережений у `database/content-patches/m27-m11-linking-words-before.json`;
це source manifest, не приватний DB dump.

Нова проєкція: незмінний subtitle/hero; box зі збереженими UUID/order перетворюється на native section;
інші h4 стають deterministic native blocks. У definition відповідно 9/10/10 blocks без subtitle.
Types: usage-panels, comparison-table, summary-list, practice-set.
Старий великий box не лишається прихованою копією. Повний basic+detail текст зберігається у native description;
при invalid/foreign/stale mapping показується повний текст без нової disclosure.

Видимими лишаються головні правила, café/oven, повна B2 таблиця, конструкції й core examples,
весь C1 авторський абзац з українським перекладом, типові помилки та next-step links.
У C2 while/whereas обидва contrast/time пояснення лишаються одразу видимими: порожнього detail немає.
Окремий символ `⌄` не повернуто.

## Матриця point details

Ці числа підтверджені package/Blade тестами, реальним server HTML та браузером після apply.

| Сторінка | Авторський h4 / новий block | Points | Details |
|---|---|---:|---:|
| B2 | 1 — Спершу визнач зв’язок | 1 | 0 |
| B2 | 2 — Reference table | 0 | 0 |
| B2 | 3 — Because / because of / due to | 1 | 1 |
| B2 | 4 — So / therefore | 1 | 1 |
| B2 | 5 — Although / despite / however | 1 | 1 |
| B2 | 6 — Помилки | 0 | 0 |
| B2 | 7 — Практика | 0 | 0 |
| B2 | 8 — Наступний крок | 1 | 0 |
| C1 | 1 — Логічна дія | 1 | 1 |
| C1 | 2 — Поступка, наслідок, додавання | 3 | 3 |
| C1 | 3 — Provided that | 1 | 1 |
| C1 | 4 — Insofar as | 1 | 1 |
| C1 | 5 — Пунктуація і позиція | 1 | 1 |
| C1 | 6 — Авторський абзац | 1 | 1 |
| C1 | 7 — Помилки | 0 | 0 |
| C1 | 8 — Практика | 0 | 0 |
| C1 | 9 — Наступний крок | 1 | 0 |
| C2 | 1 — Контраст і поступка | 1 | 1 |
| C2 | 2 — Even though / even if | 1 | 1 |
| C2 | 3 — While / whereas | 1 | 0 |
| C2 | 4 — Although / though / despite / in spite of | 4 | 4 |
| C2 | 5 — Reduced clauses / wrong subject | 4 | 4 |
| C2 | 6 — Позиція й акцент | 1 | 1 |
| C2 | 7 — Помилки | 0 | 0 |
| C2 | 8 — Практика | 0 | 0 |
| C2 | 9 — Наступний крок | 1 | 0 |

Разом: B2 3, C1 8, C2 11 = **22** незалежні disclosures усередині відповідних пунктів.

## Практика

На кожній цілі застосовано й перевірено всі погоджені 6 випадків: selects 2, choices 2, inputs 2.
Четверта вправа відфільтрована до exact linked primary bank поточної сторінки й рівня.
Read-only inventory робочої БД підтвердив 48 linked type-4 питань у кожному з трьох основних банків:
`PolyglotLinkingWordsReasonResultContrastB2LessonSeeder`, `PolyglotAdvancedLinkingDevicesC1LessonSeeder`,
`PolyglotConcessiveAndContrastiveStructuresC2LessonSeeder` під namespace `Database\Seeders\V3\Polyglot\`.
Браузер показує 5 питань linked widget; ID кожного перевірено як підмножину exact primary bank:
B2 17409–17456, C1 18081–18128, C2 18849–18896.
Bank, answers, options, verb_hint та pivots не редагувались.

JS перевіряє правильні/неправильні відповіді, 2/2, reset, accepted alternatives,
token click/manual editing/Backspace reuse та заборону suggestions/fetch для token bank.
Для C1 dataset editing opt-in `punctuation_sensitive` приймає semicolon або full stop,
але відхиляє comma splice. У B2 token вправі лишається чинна generic нормалізація пунктуації;
строга перевірка внутрішнього semicolon/comma для цієї вправи не заявляється.

## Fresh local-target proof і фактичне застосування

Read-only inventory встановив локальний MySQL `gr2`, host `DESKTOP-3C05HGF`, port 3306.
Три робочі Page та їхні поточні M11 records відповідають accepted before-state.
Файли робочого root звірені перед перенесенням: шаблони відповідали M26 baseline,
три старі canonical definitions не мали незакомічених правок відносно власного ROOT HEAD.
До root перенесені лише цільові definitions, M27 package/support/command/service та 5 необхідних templates.
Сторонні незавершені зміни не перезаписані.

Початковий standalone PHP probe був заборонений Apache (403), без DB writes.
Після прямого дозволу користувача створено тимчасовий read-only Laravel nonce route:
`GET http://gramlyze.loc/api/_local/m27-target-ad819468ef131ae5e1d18894329e4b0a`.
GET: 200 з IP 127.0.0.1; HEAD: 404. Handler допускав тільки HTTP, точний host,
GET і raw loopback REMOTE_ADDR; відхиляв forwarded headers; не використовував session/cookies,
не повертав credentials, APP_KEY чи tokens і виконував тільки SELECT.
Жодних Apache/XAMPP/hosts змін або production-запитів.

Fresh proof звірив Windows listener/vhost/application/document identity та web/CLI runtime HMAC.
Безпечний allowlist відповіді: environment `production`, SiteMode `development`,
application `d:/dev/htdocs/gramlyze.loc`, document root `d:/dev/htdocs/gramlyze.loc/public`,
driver `mysql`, configured host `localhost`, port `3306`, database `gr2`.
APP_ENV сам по собі не був доказом production: фізичну локальну ціль встановлено окремо.

Fresh preview `m27-preview-v2.json`: before-state, 3 updates / 23 inserts.
Plan SHA-256: `5fa2ee5cc042623510f86e0e5a6f38c7ad64b33360977d13ff8a986feebf7654`.
Exact diff перевірений до exclusive backup і транзакції.

**Фактичний DB diff: updated 3 / inserted 23 / deleted 0.**
Оновлені тільки type/body старих UK box rows 8759, 8795, 8840;
їхні UUID/order/owners та subtitle/hero незмінні. Нові native rows 12311–12333.
Жодних Page, question-bank, tags, pivots, EN/PL, progress або інших lesson writes.
Protected fingerprints після транзакції збіглися, включно з M26 та іншими text blocks.

Exclusive record backup (не повний дамп), збережений локально й виключений із Git:
`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m27-local/m27-before-v2.json`.
SHA-256: `a4cfabf4bb1eaee7f79b0c3cc2ce31f02a848ab41afdbbcc06954612d5583121`.
Повторний apply з тим самим plan: **no-op, updated 0 / inserted 0**.
Додатковий backup `m27-unused-noop-v2.json` не створено.
Restore перевірено ізольованими тестами; робочий apply не відкочувався.

Після apply/no-op route видалений. Окремий GET цього nonce: **404**, IP 127.0.0.1.
`routes/api.php` відновлено байт у байт, SHA-256 до/після:
`2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`.
Routes не входять до M27 commit. Постійного proof endpoint або автоматичного apply немає.

## Перевірки

- Окремий запуск `m27-final-core-v4`: **100 tests / 20816 assertions**, 0 failures/errors.
  Охоплює M27 source/patch, native presentation, TheorySection, M26 point/practice/package/patch regressions і token UI.
- Окремий фінальний запуск `m27-precommit-v8`: **46 tests / 341 assertions**, 0 failures/errors, exit 0.
  Охоплює актуальні M27 fidelity/patch/guard та M11 physical guard.
  Негативні fixtures відхиляються; stale plan змінює захищений question record після preview, без patch writes.
  English `not` зберігається нормалізацією на synthetic fixture; у справжньому author source polarity fixture видаляє «Не».
  Окремий тест перевіряє safe allowlist runtime identity без APP_KEY.
  Ізольований runner не виявив змін захищених файлів.
- Обидва PHP запуски мають 1 наявний deprecation з `config/database.php:62`:
  `PDO::MYSQL_ATTR_SSL_CA` deprecated у PHP 8.5. Це не failure M27; конфігурацію не змінювали.
- Node test run: **19 passed** (M27 practice + чинні contractions regressions).
- Vitest run: **32 passed** у theorySections, theorySidebarLayout, theoryNavigation.
- PHP lint 10 цільових PHP files: PASS. JSON parse та `git diff --check`: PASS.
- Незалежний extraction test зберігає весь author word bag без доданих/втрачених/повторених слів,
  крім прямо погодженої заміни self-check. Hash-bound package додатково фіксує точну пунктуацію/point ownership.
- JS/CSS build inputs не змінені; Vite build не запускався. Inline practice script покритий Node тестами.

Тестові запуски наведені окремо, не сумуються в один вигаданий загальний PASS.

## Live HTTP і браузерне приймання

До apply збережений HTTP baseline 3 цілей + 4 control pages, ordered sitemap **554 URL**,
SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.
Baseline metadata збережені локально у private `storage/app/seo-m27-local/before-http.json`.

Фінальний `acceptance-v4`: **PASS — 12 states, 3 no-JS pages, 4 controls**.
Реальні guest GET без cookie/авторизації, лише `.loc`, окремі browser contexts.
Title, H1, description, canonical, robots/X-Robots-Tag, OG/Twitter та старі anchors збережені.
Ordered sitemap before = after: фактично **554 URL**, той самий наведений вище SHA.

Для кожного з 22 details: own-point placement/content, mouse/Enter/Space, focus-visible,
незалежні simultaneous opens/close, reload closed, deep fragment, print open/restore — PASS.
Detail є у server HTML; click не викликає додаткових запитів. Native no-JS — PASS.
Практика на всіх трьох: correct 2/2, wrong 1/2, reset, manual/token/Backspace,
accepted alternatives, C1 punctuation sensitivity та exact linked-bank IDs — PASS.

Контролі: M26 PPC Forms, Present Perfect Continuous Forms, ordinary Present Perfect Forms,
accepted Cleft Sentences Basics. Немає сторонніх M27 disclosures; control content/metadata незмінні.
На перших двох також виконані фактичні correct/wrong/reset interactions практики.

Console errors / failed actual network requests / HTTP errors / font failures / policy violations: 0.
При вимкненому JS Chromium повідомляє `csp` для JS resource — це окремо зафіксований
очікуваний наслідок `javaScriptEnabled:false`, не помилка завантаження зі звичайним JS.

Learning main/card overflow: **0 px** у всіх 12 станах.
B2 mobile table: локальний scroll container 322 px / content 576 px, доступний з клавіатури.
Decorative document overflow окремо: desktop 0 px; mobile light B2/C1/C2 — 9/4/5 px,
mobile dark — 2/0/6 px. Це чинний зовнішній random-shapes фон; у M27 його не змінювали,
не додавали глобальний overflow-x:hidden і не видавали decorative overflow за нуль.

Фокусоване visual supplement `visual-v1`: 5 сценаріїв, **17 screenshots** — first-screen basic,
detail, C2 4+4 підпункти, practice, mobile table/scrolled table, dark. Переглянуто візуально.
Screenshots та HTTP/browser JSON залишені приватно в `storage/app/seo-m27-local`, не в Git.

## Повна M26 регресія

Фінальний existing M26 acceptance `m27-regression-v2`: **PASS**.
5 реальних guest GET, **20 desktop/mobile light/dark states**, 5 no-JS сторінок,
усі **56** point details. Незмінність complete basic/author fragments, ownership/anchors,
mouse/keyboard/focus, independent opens, reload, deep links, print і відсутність extra fetch — PASS.
Learning overflow PASS; strict document overflow не PASS через чинний декоративний фон
(desktop 0 px, mobile 1–13 px), що окремо збережено у приватному JSON, не приховано.
Початковий прогін `m27-regression-v1` зупинився на timeout першого GET до перевірок;
без application/DB змін повторено під новим label. Це не приховано як PASS.

## Межі та завершення

Не переписували прийняті авторські тексти, не змінювали question banks або наступні три уроки.
Rollback before-state збережений у versioned manifest і приватному exclusive record backup.
Hash-bound package має fail-closed presentation: unknown/invalid/stale дані не ховають повний текст.
Не запускали reseed/migration/truncate/cache/session clear чи full DB restore.
У commit тільки цільові sources, adapter/guard/command, необхідні templates, tests, diagnostics і цей звіт.
Runtime evidence, backups, screenshots, .env, vendor та generated build виключені.
Production не перевірявся й не змінювався.
