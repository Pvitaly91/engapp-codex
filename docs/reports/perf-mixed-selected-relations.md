# PERF-07 — пов’язані дані лише для відібраних питань mixed-тесту

## Рішення

**Не впроваджено.** На двох цільових живих UK mixed-тестах кожний прохід фільтра отримав і зберіг ті самі **54 із 54** кандидатів; кількість питань, відкинутих PHP-добором, дорівнює **0**. Поточна гілка спочатку завантажує пов’язані дані, однак на цих маршрутах усі вони належать до питань, які залишаються. Перенесення eager loading після selection не забрало б жодної з цих моделей. Пакет PERF-06 збережено на сайті й у worktree. Робочий код, дані та схему не змінено.

## Фактичний BEFORE і відновлення

- Worktree `C:/Users/admin/.codex/worktrees/perf-delivery-02/gramlyze.loc`, початкова гілка `codex/perf-marker-tags-batch`, HEAD `8e48c44f34c117a637e20e1f1d93e91ebfdb1b89` (база `codex/perf-04-05-checkpoint`). Свіжий BEFORE містить незакомічений PERF-06, а не лише цей HEAD.
- Після приватного збереження створено `codex/perf-mixed-selected-relations` на тому самому HEAD. Для обох копій збережено окремі SHA/size/absence manifests і побайтові preimages 18 scoped шляхів, scoped binary tracked diff, всі 6 файлів PERF-06, включно з 3 untracked. Після зміни гілки всі початкові SHA WT збіглися.
- Served ROOT — окрема копія `D:/DEV/htdocs/gramlyze.loc`, `codex/production-ready-a14788dac` / `41820a2bebdf69004fa7209a2a38457f93efabbd`; локальний Apache vhost HTTP `gramlyze.loc` обслуговує `D:/DEV/htdocs/gramlyze.loc/public`. Підтверджено збіг web/CLI identity **4/4** за приватним відбитком ROOT/конфігурації з успішним read-only CLI ping. Точність PHP джерел важлива: filter service `9a2ab8e9bad960eed09bc660c95cf5680476d3babfc7a71bab77ae00a08d0d67`, controller PERF-06 `6c7f4cc417b4b153169df418baaa5dea99f9ee8698beb113e7a96d4c23341e3f`, marker service PERF-06 `8b6e3a67946b5871d3fe7e128b253f440128f3eddb0c8bcc2705c62983d350f8`; збігаються WT/ROOT. Немає копіювання всього дерева чи сторонніх змін.

## Шлях і потенційний кандидат

`GrammarTestFilterService::prepareData` у mixed all-levels з явними seeders читає повні scalar атрибути Question, eager-loads `category`, `answers.option`, `options`, `verbHints.option`, `source`, потім викликає `selectMixedAllLevelsQuestions`. Selection/alternate profiles/interleaving використовують поля `id`, `level`, `type`, `seeder`. У моделі немає default eager loading або retrieved hook; зареєстрований observer працює на saved, а не під час читання. Вибірка не торкається relations; у всіх зафіксованих selector spans **0 SQL**. SQL predicates `has/whereHas` для eligibility, blank count та tags не можна переносити за відбір.

Мінімальний потенційний кандидат для більшого pool: той самий повний query без цих eager relations лише в цій гілці; чинний selector; тимчасова Eloquent collection з **тими самими моделями** для `load`, і повернення original Support collection без requery Question, dedupe, нового сортування чи зміни RNG. Кандидат **не внесений** до runtime: в обох цільових банках цей підхід читав би той самий набір ключів і рядків. Незастосовані category-group (включно з overlapping групами), ordinary, fixed/saved і locale fallback не розширюють межі цієї оптимізації; спеціально не змінювали банки/профілі, щоб штучно створити надлишок. Фактичні live-результати стосуються UK; їх не переносимо на всі locale чи інші банки.

`prepare()` використовує category для назви/метаданих і зберігає довідники форми; `questionsFromFilters()` повертає гідратовану вибірку без цих UI-довідників. `SavedTestResolver::loadQuestions` може читати додаткові missing relations після selection, вони поза scope. Кожна виміряна відповідь мала два виклики добору: розпізнавання virtual slug у middleware й резолюція для контролера. Саме повторне отримання selected даних — інша робота, **не** гідратація відкинутих кандидатів. Resumed теж проходить раннє resolve/filter, але використовує готовий HTML dataset та не викликає marker batch.

## Свіжий HTTP-профіль A (після PERF-06)

Профіль для локальної development/UK guest, тільки loopback GET з випадковим одноразовим ключем на scoped URL; без нових маршрутів, сирих SQL/bindings, cookies, сесій, відповідей чи приватних даних у receipts. Профайлер розділив той самий Eloquent шлях: `applyScopes → getModels → eagerLoadRelations → newCollection → applyAfterQueryCallbacks`. Порядок дій не змінено; інструментовано тимчасово лише 7 точних шляхів з перевіреними preimages. Після профілювання їх відновлено, probe/control відсутні, і це перевірено перед фінальним HTTP.

Усі 4 відповіді HTTP 200, 54 питання. Два записи на режим означають **два окремі проходи**, а не 108 різних питань. У кожному проході HMAC множини candidate IDs збігся з selected, обидві множини по 54 унікальні. Також повністю збігаються counts моделей кандидатів і відібраних. Probe рахує лише вже завантажені relations, без lazy loading; у таблиці наведено SQL count / SQL ms / повний час фази ms. Остання пара — TTFB / total ms. Phases вкладені у filter/resolve; їх не додаємо до повного часу запиту. `relation` включає час SQL плюс створення і прив'язку моделей та накладні витрати фази, не окремий CPU-профіль.

| Сценарій | Кандидати → selected | Candidate query + Question models | Selection: SQL / ms | Relation SQL + hydration | Усі SQL / мс | Випадки marker batch | Peak active MiB | Body / payload, B | TTFB / total, мс |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Word Order — новий HTML | 54 → 54 × 2 | 2 / 62.36 / 64.33 | 0 / 6.25 | 14 / 18.74 / 551.12 | 43 / 213.73 | 1 | 33.16 | 837199 / 401345 | 44344.38 / 44364.06 |
| Word Order — повторне HTML | 54 → 54 × 2 | 2 / 62.43 / 65.37 | 0 / 4.96 | 14 / 16.82 / 114.29 | 44 / 187.97 | 0 | 25.59 | 837199 / 401345 | 776.58 / 782.55 |
| Word Order — `/questions` JSON | 54 → 54 × 2 | 2 / 67.22 / 70.55 | 0 / 6.37 | 14 / 19.02 / 125.71 | 37 / 209.86 | 0 | 25.18 | 225473 / 140618 | 933.73 / 938.07 |
| Present Simple Passive — новий HTML | 54 → 54 × 2 | 2 / 92.13 / 93.77 | 0 / 4.69 | 14 / 25.95 / 127.12 | 40 / 245.87 | 1 | 45.71 | 873702 / 437179 | 4060.30 / 4104.55 |

Кожен новий HTML мав marker batch 1 × (1 schema + 1 joined read), resumed і окремий JSON — 0. Не змінювали це. Перший профіль Word Order повільний зовні: 44,36 с на повний запит під інструментацією; SQL лише 213,73 мс. Не приписуємо цей розкид фазі relations, не приховуємо та не замінюємо іншим спостереженням.

### Кількість моделей за проходом

Кількість унікальних за object identity інстансів на певному relation path (і, в дужках, кількість прив’язок до батьків). Усі п’ять основних relations мали loaded state на всіх 54 моделях кожного проходу. Pivot входить до того самого SQL, що options, але має окремі PHP-інстанси.

| Relation path | Word Order: candidates = selected | Present Simple Passive: candidates = selected |
|---|---:|---:|
| category | 2 (54) | 2 (54) |
| answers | 190 (190) | 192 (192) |
| answers.option | 146 (190) | 146 (192) |
| options | 735 (735) | 742 (742) |
| options.pivot | 735 (735) | 742 (742) |
| verbHints | 190 (190) | 192 (192) |
| verbHints.option | 54 (190) | 54 (192) |
| source | 1 (54) | 1 (54) |
| Лише для відкинутих | **0** | **0** |

### Незалежна перевірка MariaDB і синтетичний контроль

У приватному CLI на фактичній MariaDB, read-only repeatable-read транзакція з rollback, той самий ROOT і exact normalized filters, узгоджені з web. Для обох UK банків порівняли чинний eager path та приватний прототип load-after-selection без зміни source. Збіглися candidate membership і counts моделей із HTTP. В обох варіантах **8 SQL на прохід** у candidate+eager фазах (1 Question + 7 relation), ті самі statements і bindings у тому самому порядку, **0 SQL** під час selection; count availability не входить у ці 8. У receipts залишились булеві результати та агрегати, не SQL/bindings. Порівняння без сортування: strict IDs/order, модельні типи, raw і cast attrs, loaded relations, pivot flags, multiplicity, null, порядок опцій/відповідей/підказок та повний guest builder payload двох варіантів (strict array і JSON bytes, з однаковими in-memory варіантами). Load-after-selection працював на тих самих Question object instances, не перечитував їх. Цей доказ показує **нуль** зекономлених рядків на реальних банках; не є HTTP-виміром прототипу.

Приватний незалежний SQLite oracle: **4 тести / 1350 assertions**, без failures/errors, одна відома PHP 8.5 deprecation. До runtime-експериментів зафіксовано exact service preimage і 15 baseline cases; повторно звірено повні raw/cast attributes, ordered relations і форму. Покрито A1–C2/subset, декілька seeders і round-robin, balanced/compact та контрольований test-only RNG state, interleave on/off, empty/small pool, availableCount проти limit, blank-count, теги/aggregated tags, AI flags, overlapping groups/duplicates, ordinary й missing-relation failure. Окремо контроль 102 → 54 показав, що **на штучному** більшому pool 48 відкинутих кандидатів справді мали baseline eager relations; це не доказ економії на сайті. Не залишено нового versioned тесту, що вимагав би невпровадженої поведінки. Збережено незалежні versioned регресії PERF-05 і PERF-06.

### A/B і висновок щодо швидкості

A — фактичний PERF-06. B для HTTP не встановлювався: на виміряних маршрутах немає тих пов’язаних рядків, які B мав би прибрати. **0/20 пар**, контрольних A/B пар — 0. Не підмінювали відсутній B порівнянням двох A або результатами PERF-05/06. Немає виміряного Δ на повній сторінці, SQL чи пам’яті, немає median/p95 або заяви про виграш. Наведені окремі абсолютні HTTP й memory спостереження — профіль A і smoke A, не парні вимірювання. Великий синтетичний pool лишається контролем механізму, а не підставою міняти чинний шлях без користі на реальному сценарії.

## Фінальна перевірка на точному незмінному A

Фінальні запити без профайлера. Усі відповідні повні question payload, їх порядок та CSRF-нормалізовані HTML/окремий JSON збіглися з попереднім точним PERF-06; теоретичні документи й menu revision теж збіглися, обидва уроки мають по 5 вбудованих питань. Це equivalence чинного A, не твердження про production або неперевірений B.

| Контроль | HTTP / формат | TTFB / total, мс | Розмір, B |
|---|---|---:|---:|
| [Word Order — новий](http://gramlyze.loc/test/word-order/theory-basic-word-order) | 200 HTML | 4052.40 / 4092.35 | 837199 |
| Word Order — resumed цього guest dataset | 200 HTML | 1564.80 / 1586.68 | 837199 |
| [Present Simple Passive](http://gramlyze.loc/test/passive-voice/theory-passive-voice-present-simple) | 200 HTML | 4073.53 / 4103.36 | 873702 |
| [Урок Word Order](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | 200 HTML | 39973.17 / 39985.84 | 606958 |
| [Урок Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 HTML | 6680.19 / 6700.81 | 564389 |
| [Word Order — /questions](http://gramlyze.loc/test/word-order/theory-basic-word-order/questions) | 200 JSON | 1746.98 / 1756.40 | 225473 |
| [Категорія Word Order](http://gramlyze.loc/theory/word-order) | 200 HTML | 2641.13 / 2648.57 | 394044 |

Перший контроль уроку досяг майже 40 с, але віддав повний документ за один запит; повторів для відбору кращого часу не було. Історичний timeout не розслідували й не виправляли; попередні звіти не змінено.

**Серверне збереження — PASS (не UI):** дві власні нові анонімні сесії, по одній на Word Order і Passive. Для обох 54 завдання, HTTP card/step 200, запис 204, reload 200, reset 204, повторне відкриття 200. Збіглися відповіді, позиція, нормалізовані поля ручного вводу, snapshot і dataset; reset лише своєї нової step-спроби не змінив її card. Особистих сесій не торкалися.

**Браузер: BLOCKED (поточний URL guard).** Дозволений інструмент не зміг засвідчити адресу в Windows-браузері для ізольованої спроби, зупинив роботу до першої взаємодії. Не перемикали інструмент, не змінювали особисту спробу. Не перевірялися браузерні gap/compose/reorder, ручне введення/токени, wrong → edit → correct, пояснення, Next, reload/resume і reset. Це не причина PERF-06 (там відкрився тест із наявним прогресом) і не новий PASS за PERF-05. HTTP і JS не зараховуються як UI.

### Повторні автоматичні регресії

PHP 8.5.10 / PHPUnit 12.5.35, ізольовані SQLite DB/storage, array sessions, без робочої `.env`. У лічильнику executions враховано data providers; набори частково перетинаються. Lint закріплених PHP-файлів пройшов. Тести не читали контентну БД.

| Серія | Методи / виконання | Assertions | Failures / errors / skips |
|---|---:|---:|---:|
| PERF-06 unit/SSR marker batch | 9 / 9 | 134 | 0 / 0 / 0 |
| PERF-05 підготовка / selection | 10 / 10 | 608 | 0 / 0 / 0 |
| PERF-01–04 core (меню, M42, finite dispatch, owner gates) | 30 / 32 | 21966 | 0 / 0 / 0 |
| Broad PHP | 41 / 53 | 43730 | 5 / 1 / 0 — усі збіглися з PERF-06 |
| PublicFlows | 11 / 11 | 195 | 1 / 0 / 0 — збіг із PERF-06 |
| JS Node + Vitest | 12 + 7 | — | 0 failures |

Broad (exit 2) та PublicFlows (exit 1) окремо звірені з точними попередніми JUnit: усі cases, методи, вид відхилення, класи винятків, основні повідомлення і counts збіглися. Порівняння не перетворює ці набори на PASS; нових відхилень немає. Жодних змін fixtures/assertions для проходження:

| Метод | Незмінне відхилення |
|---|---|
| `MixedTheoryPageTestRenderTest::test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes` | Очікував 302, одержав 404 |
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2` | Очікував 302, одержав 404 |
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level` | Очікував 302, одержав 404 |
| `QuestionsNegationsQualityRuntimeTest::test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices` | Фактичний розмір 5, очікував 4 |
| `SavedTestJsStateTest::test_it_fetches_questions_for_filter_based_saved_test` | Очікував 200, одержав 500; SQLite `no such table: question_hints` |
| `SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` | `Illuminate\Database\QueryException`; SQLite `no such table: question_hints` |
| `PublicTestRoutesSmokeTest::test_representative_public_test_routes_render_deterministically` | Очікував 200, одержав 500; SQLite `no such table: question_hints` |

Відома PHP 8.5 deprecation в конфігурації PDO лишається; точні байти runtime/тестів PERF-06 не змінені після запусків.

## Пакет змін

- **PERF-07:** лише `docs/reports/perf-mixed-selected-relations.md` (untracked). Runtime, тести і versioned fixtures не змінено. Ігноровані preimages/receipts і ізольовані експерименти не є залежностями checkout.
- **Незакомічений PERF-06, не частина PERF-07:** `app/Http/Controllers/TestJsV2Controller.php`, `app/Services/MarkerTheoryMatcherService.php`, `tests/Feature/PublicFlows/SeededPublicFlowTestCase.php`; також три untracked — `tests/Feature/MarkerTagsDatasetBatchTest.php`, `tests/Unit/MarkerTheoryMatcherBatchTest.php`, `docs/reports/perf-marker-tags-batch.md`.

Поза scope: повторний resolve/filter, інші банки/локалі, legacy TextBlock/matcher, overflow/QuotaExceededError, історична причина таймауту, production readiness. Без staging/commit/push, `main`, deploy, контентних записів, міграцій, reseed або серверних налаштувань.
