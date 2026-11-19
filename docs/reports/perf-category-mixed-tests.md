# PERF-03 — категорія теорії та тематичний mixed-тест

Дата: 2026-11-10. Статус: **локальну оптимізацію завершено; окремі та сумарна A/B серії, фінальний HTTP/browser smoke виконані. Відомі baseline-обмеження наведено явно.**

## Межі роботи

Продовження чинного PERF-02, а не повернення до стану до PERF-01. Оптимізація лише локального `http://gramlyze.loc`, без змін навчального контенту та банків робочої БД, особистого прогресу, дизайну, оцінювання, server configuration, `.com` або `.ub`. Штатні гостьові session/cache operations і окрема власна перевірочна сесія не прирівнюються до записів навчального контенту. Під час вимірювань commit, push і deploy не виконувалися.

| Призначення | Сторінка |
|---|---|
| Основна категорія | [Порядок слів](http://gramlyze.loc/theory/word-order) |
| Основний mixed-тест | [Basic Word Order — mixed](http://gramlyze.loc/test/word-order/theory-basic-word-order) |
| Контроль уроку Word Order | [Basic Word Order](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) |
| Контроль іншого уроку | [Past Perfect Continuous: Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) |

HTML snapshots меню, ручна перебудова меню, request-only memo PERF-02 для M42 та перевірка джерел на першому доступі нового request зберігаються. Не додано persistent cache для авторських джерел або готових відповідей. Наявні звіти PERF-01/PERF-02 не переписуються.

## Вихідний стан та відтворюваність

Baseline — фактичні байти поточного незакоміченого PERF-02, зафіксовані до внесення PERF-03, **не** сам історичний HEAD.

| Середовище | Ідентичність |
|---|---|
| Worktree | Окремий локальний checkout |
| Робоча гілка | `codex/perf-theory-menu-html-cache` |
| Історичний WT HEAD | `1c0e9d8323552c6e7a4e849c571598b8b580b3f3` |
| Фактичний served ROOT | окремий локальний checkout; DocumentRoot — `public` |
| Served branch / HEAD | `codex/production-ready-a14788dac` / `41820a2bebdf69004fa7209a2a38457f93efabbd` |
| HTTP runtime | PHP 8.5.10, CGI/FastCGI, OPcache увімкнений, debug вимкнений |
| Режим | `development`, locale `uk`, guest |
| Локальна БД профілювання | локальний MySQL; без записів контенту |

Незалежні приватні raw-byte snapshots WT і served ROOT збережені під `storage/logs/perf-03/`:

- Приватний BEFORE manifest: чинні PERF-01/PERF-02 файли;
- Приватний BEFORE manifest: точні preimages кандидатів PERF-03;
- `delivery-manifest.json`: окремі baseline/candidate SHA-256 для контрольованого накладання файлів;
- `profile-raw-before-v1/` та приватні profile overlays: матеріали діагностики, не нове джерело навчального контенту.

WT і served ROOT не ототожнюються за Git HEAD або хешем робочої копії. Для перемикання A/B використовуються окремо збережені served preimages.

| Файл кандидата | SHA-256 served baseline |
|---|---|
| `app/Support/QuestionsNegationsPracticeQuality.php` | `88db60f22eae034ac9cca511f3e3bb7bf4fb7b42e51a4c9fb15a90f7944c612b` |
| `app/Support/FinitePracticeQualityCatalog.php` | `24eb95f5f6d9ddcebbc4a37855a467339c1c559543bf82bc2fdfa38d3820c201` |
| `app/Support/PassivePracticeQuality.php` | `38ccabdf70cb3feabac9d1352370287a5e693dca5cf2e98a635959edce796581` |

Контрольний SHA-256 `M42NativeDesignPackage.php` у збережених WT/ROOT однаковий: `79137e92501516da6d1ee57f6533046872a5e1344f5f9a076137f887a5422ced`. Його PERF-02 memo не є новою зміною PERF-03.

## Закриття залишкової браузерної перевірки PERF-02

У поточному завданні завершено попередній незакритий smoke: native-вправа Word Order пройшла неправильну відповідь → редагування → правильну відповідь; меню обох контрольних уроків перевірено, зокрема mobile-відкриття без дублювання меню. Це нова перевірка, а не ретроспективне виправлення старого звіту.

На baseline mixed-сторінці до PERF-03 вже спостерігався `QuotaExceededError` під час запису в `localStorage`. Наявний особистий прогрес не скидався. Цей стан не можна приписувати кандидату PERF-03; без окремого дозволу не очищалися browser storage чи особисті відповіді. Фінальний smoke повторно виявив той самий клас помилки; результат наведений окремо нижче.

Окремий браузерний smoke кандидата виконано у справжньому step mode з окремою перевірочною сесією, який спочатку був порожнім. Перевірено три типи вправ:

- gap: неправильна відповідь → правильна `drinks milk`;
- compose: неправильна відповідь → `Ben has a red bicycle` послівно;
- reorder: неправильна відповідь → очищення → токени `Mia` + `likes cats`.

Результат власного launch — три правильні відповіді; reload відновив усі три. Потім скинуто лише цей власний launch до першого порожнього питання; JSON reset спрацював. Особистий прогрес основної карткової сторінки залишився незмінним. Помилки quota зберегли той самий клас, що й baseline; storage не очищався. Це smoke кандидата, не заміна повторної перевірки фінальних файлів після видалення діагностики.

## Діагностика: категорія

Фактичний ланцюжок: `PageController::category` → `categoryPagesForAggregatedTopicTests` → `TheoryPagePromptLinkedTestsService::buildForCategory` → для восьми сторінок `findForPage` / `loadTestsForPage` → `hydrateDisplayMetadata` → `QuestionsNegationsPracticeQuality::savedBuilderDisplayCount`.

До зміни `savedBuilderDisplayCount` для будь-якого UK UUID спочатку запускав `metadataPatches()`, і лише потім порівнював UUID зі своїми builder-тестами. На категорії Word Order це 27 викликів повного стороннього каталогу, хоча жодна з цих ідентичностей не належить дванадцятьом builder-тестам Questions/Negations.

Нижче — другий діагностичний запит кожного стану, мілісекунди. Inclusive включає дочірні spans; exclusive виключає лише інструментованих нащадків. **Рядки не можна додавати як незалежні витрати**, а recursive inclusive-лічильники не дорівнюють wall time.

| Span | BEFORE виклики | BEFORE inclusive / exclusive | AFTER виклики | AFTER inclusive / exclusive |
|---|---:|---:|---:|---:|
| `buildForCategory` | 1 | 4477.656 / 111.467 | 1 | 395.309 / 128.039 |
| `findForPage` | 8 | 4336.079 / 3.747 | 8 | 224.171 / 4.917 |
| `loadTestsForPage` | 8 | 190.021 / 190.021 | 8 | 213.081 / 213.081 |
| `hydrateDisplayMetadata` | 27 | 4142.310 / 7.024 | 27 | 6.173 / 6.113 |
| `savedBuilderDisplayCount` | 27 | 4135.287 / 6.401 | 27 | 0.060 / 0.060 |
| `QuestionsNegations…::metadataPatches` | 27 | 4128.886 / 155.780 | 0 | — |
| `QuestionsNegations…::compile` | 1296 | 2963.431 / 490.162 | 0 | — |
| `QuestionsNegations…::readJson` | 4100 | 2122.868 / 1932.264 | 0 | — |
| `files::read` | 4100 | 190.604 / 190.604 | 0 | — |
| `files::hash` | 2156 | 291.436 / 291.436 | 0 | — |
| `promptLinkedSeederDefinitionsForPage` | 8 | 4.697 / 4.697 | 8 | 7.111 / 7.111 |
| `Blade::render` | 1 | 12.242 / 12.177 | 1 | 22.397 / 22.297 |

Вибрана мінімальна зміна: перед `metadataPatches()` перевірити точне входження UUID до дванадцяти frozen builder identities з `ORIGINALS`. Для власного UUID **увесь** попередній шлях валідації залишається: повний каталог, slug, filters, locale, source hashes та fail-closed. Не замінено перевірку повного каталогу перевіркою одного обраного topic. Невласні UUID повертають те саме `null`, але не відкривають сторонні джерела.

## Діагностика: mixed-тест

SSR entry point: `NewDesignTestController::showSavedTestJsNewDesign`; step entry point — `showSavedTestJsStepNewDesign`; спільна реалізація shell успадкована з `TestJsV2Controller::renderSavedTestShell`. Вона використовує `SavedTestResolver` / `TheoryPagePromptLinkedTestsService::buildForPage` і `GrammarTestFilterService::prepare`. Перший запит створює dataset через `buildQuestionDataset` / `PassivePracticeQuality::decorateKnown`; повторний запит перевіряє вже збережений dataset через `PassivePracticeQuality::knownCatalogNeedsRefresh`.

Фактичні допоміжні адреси: step `/test/word-order/theory-basic-word-order/step`, JSON `/test/word-order/theory-basic-word-order/questions` (`GrammarTestController::fetchSavedTestJsQuestions`, запит із JSON Accept), state POST `/test/word-order/theory-basic-word-order/state`. JSON/reset перевірено через штатний браузерний сценарій власної сесії. SSR HTML і JSON не змішувалися в benchmark. Відоме неоднозначне трактування `/questions` при HTML-навігації не виправлялося як частина PERF-03; route files не редагувалися.

Друга домінантна причина — dispatcher пробував усі finite catalogs для питань Word Order. Через це сторонні каталоги проходили завантаження та source validation, хоча не могли володіти UUID. Наприклад, у BEFORE повторного запиту `NounsArticlesQuantityPracticeQuality::cachedNeedsRefresh` займав 1658.061 ms inclusive, `PassiveVoicePracticeQuality::cachedNeedsRefresh` — 481.921 ms. Це вкладені частини наведеної нижче загальної перевірки, не додатковий час понад неї.

| Span / режим | BEFORE виклики | BEFORE inclusive / exclusive, ms | AFTER виклики | AFTER inclusive / exclusive, ms |
|---|---:|---:|---:|---:|
| `buildQuestionDataset`, перший dataset | 1 | 4773.584 / 302.679 | 1 | 2209.648 / 432.289 |
| `decorateKnown`, перший dataset | 54 | 3260.282 / 23.519 | 54 | 185.387 / 28.723 |
| `getAllMarkerTags`, перший dataset | 54 | 88.908 / 88.908 | 54 | 117.814 / 117.814 |
| `knownCatalogNeedsRefresh`, повторний | 1 | 3096.585 / 19.485 | 1 | 139.388 / 21.816 |
| `decorateKnown`, повторний | 72 | 9.710 / 1.414 | 72 | 6.860 / 0.585 |
| `savedBuilderDisplayCount`, повторний | 6 | 1148.175 / 1.839 | 6 | 0.015 / 0.015 |
| `GrammarTestFilterService::prepare`, повторний | 2 | 928.805 / 666.735 | 2 | 1034.637 / 731.849 |
| `persistedQuestionData`, повторний | 1 | 0.531 / 0.110 | 1 | 0.481 / 0.106 |
| `files::read`, повторний | 2001 | 126.049 / 126.049 | 101 | 7.379 / 7.379 |
| `files::hash`, повторний | 1153 | 166.025 / 166.025 | 61 | 8.302 / 8.302 |
| `Blade::render`, повторний | 1 | 13.154 / 12.346 | 1 | 14.111 / 13.523 |

Вибрана зміна: консервативний `FinitePracticeQualityCatalog::mayOwnQuestionUuid()` використовується лише як dispatcher-prefilter у `decorateKnown` та `knownCatalogNeedsRefresh`. Відсіюються string UUID з іншими гарантовано непересічними префіксами. Matching prefix, невідомий UUID зі своїм prefix та malformed non-string input зберігають колишній шлях. Три legacy-каталоги, що не успадковують finite base, викликаються як раніше. Для власних питань компіляція, повне порівняння source payload та fail-closed не вимикаються.

Це не кеш, не перевизначення правильних відповідей і не прискорення за рахунок меншої кількості завдань.

## Діагностичні totals: не acceptance benchmark

Джерела — `profile-before-*.json`, `profile-after-*.json` і відповідні HTTP JSONL у приватному каталозі PERF-03. Усі вісім запитів: HTTP 200, locale UK, development. Профілювання додає overhead; лише по два спостереження на target/arm, без статистичних висновків. `app` і `pipeline` — серверні вимірювання, TTFB/total — спостереження HTTP-клієнта.

| Target / стан / запит | app / pipeline, ms | TTFB / total, ms | SQL count / ms | Peak MiB | HTML bytes |
|---|---:|---:|---:|---:|---:|
| Категорія BEFORE #1 | 4217.617 / 4205.710 | 4227.708 / 4229.745 | 50 / 261.51 | 26 | 394044 |
| Категорія BEFORE #2 | 4574.240 / 4572.134 | 4577.645 / 4579.185 | 50 / 266.62 | 20 | 394044 |
| Категорія AFTER #1 | 3472.075 / 3461.761 | 3482.719 / 3486.034 | 76 / 1068.92 | 310 | 394044 |
| Категорія AFTER #2 | 546.876 / 541.628 | 552.276 / 554.553 | 50 / 303.86 | 156 | 394044 |
| Mixed BEFORE #1 | 6917.547 / 6916.435 | 6919.438 / 6921.835 | 173 / 830.96 | 116 | 837199 |
| Mixed BEFORE #2 | 5403.036 / 5401.793 | 5404.151 / 5407.099 | 68 / 776.32 | 116 | 837199 |
| Mixed AFTER #1 | 3449.919 / 3449.056 | 3452.367 / 3455.993 | 173 / 910.82 | 82 | 837199 |
| Mixed AFTER #2 | 1435.332 / 1433.170 | 1436.665 / 1440.388 | 68 / 798.45 | 58 | 837199 |

**Пам'ять категорії не покращилася у цих профілях.** Повторний AFTER показав 156 MiB проти 20 MiB BEFORE; перший AFTER — 310 MiB. Цей результат не приховується за виграшем wall time. Перший AFTER також містить cold-роботу: 8 викликів `uncachedPromptLinkedSeederDefinitionsForPage` (2830.854 ms inclusive), 8800 `loadSeederDefinition` (1700.566 ms inclusive), 1083 читання файлів і 76 SQL замість 50. Отже, його не можна трактувати як однаково прогріту пару з BEFORE. Це пояснює додатковий шлях, але **ще не є достатнім доказом причини всього приросту peak memory повторного запиту**.

### PERF03-MEMORY: збалансований follow-up

Після основних неінструментованих benchmark-серій виконано окремий діагностичний ABBA з однаковим probe/wrapper v3. A — початковий PERF-02, B — фінальні три runtime-зміни PERF-03; перемикалися лише ці три реалізації. Джерела: `profile-balanced-{category,mixed}-{1,2,3,4}.json`, `profile-balanced-*.jsonl`, наперед визначений `profile-balanced-plan.json`. Peak не скидався, сервер і caches не перезапускалися. Усі вісім запитів — HTTP 200, development/UK; жодного invalid/excluded receipt.

| Target / arm / запит | PHP-процес | app / pipeline, ms | TTFB / total, ms | SQL count / ms | HTML bytes |
|---|---:|---:|---:|---:|---:|
| Категорія A #1 | P1 | 7301.885 / 7291.450 | 7312.186 / 7315.462 | 50 / 307.10 | 394044 |
| Категорія B #2 | P1 | 593.081 / 591.696 | 596.392 / 600.666 | 50 / 297.18 | 394044 |
| Категорія B #3 | P1 | 520.706 / 519.692 | 522.841 / 525.635 | 50 / 303.36 | 394044 |
| Категорія A #4 | P1 | 7450.912 / 7447.364 | 7453.132 / 7455.540 | 50 / 303.79 | 394044 |
| Mixed A #1, новий dataset | P1 | 9295.873 / 9292.535 | 9297.675 / 9302.557 | 173 / 930.15 | 837199 |
| Mixed B #2, warm | P2 | 1593.864 / 1583.005 | 1595.897 / 1598.567 | 68 / 821.75 | 837199 |
| Mixed B #3, warm | P2 | 1504.937 / 1504.327 | 1506.099 / 1509.486 | 68 / 898.56 | 837199 |
| Mixed A #4, warm | P1 | 7017.044 / 7015.434 | 7017.490 / 7020.645 | 68 / 794.70 | 837199 |

`real` нижче — зарезервовані блоки PHP allocator (`memory_get_usage(true)`), `used` — активні PHP-виділення (`false`); це не RSS/working set усього Apache/FastCGI. `arm` — початок opt-in probe до основної роботи, `finish` — завершення; peak охоплює повний request. Усі значення MiB (1024² bytes).

| Target / arm / запит | real arm → finish → peak | used arm → finish | used peak |
|---|---:|---:|---:|
| Категорія A #1 | 70 → 70 → 70 | 0.568 → 9.707 | 11.335 |
| Категорія B #2 | 42 → 42 → 42 | 0.570 → 5.281 | 6.064 |
| Категорія B #3 | 26 → 26 → 26 | 0.570 → 5.093 | 5.938 |
| Категорія A #4 | 18 → 18 → 18 | 0.570 → 9.648 | 11.278 |
| Mixed A #1, новий dataset | 16 → 116 → 118 | 0.565 → 107.237 | 113.258 |
| Mixed B #2, warm | 72 → 72 → 72 | 0.570 → 21.747 | 26.854 |
| Mixed B #3, warm | 52 → 52 → 52 | 0.570 → 21.558 | 26.666 |
| Mixed A #4, warm | 66 → 116 → 116 | 0.570 → 107.304 | 112.413 |

Категорія виконувалася в **тому самому PHP-процесі P1**. Reserved peak 70/42/26/18 MiB вже був присутній на `arm` і не зріс усередині жодного request; послідовність зменшується незалежно від A/B. Це пряме свідчення впливу retained allocator reservation, а не приросту резервування під час роботи кандидата. Водночас used peak прив'язаний до arm: A 11.335/11.278 MiB, B 6.064/5.938 MiB. У цьому збалансованому профілі кандидат не збільшив активні PHP-виділення категорії. **Старі 156/310 MiB без PID та arm/used-метрик лишаються дійсними сирими спостереженнями, але не перетворюються заднім числом на доведений memory PASS.** Follow-up пояснює, чому один real peak не можна вважати доказом регресії; він не доводить точну історичну причину всіх байтів того запиту.

Для mixed перший A створює dataset (173 SQL), тому його не прирівнюємо до warm B. У зіставних warm-запитах B #2/#3 мають used peak 26.854/26.666 MiB, A #4 — 112.413 MiB. Це узгоджується з відсутністю матеріалізації сторонніх каталогів після dispatch gate. Проте B виконувався в процесі P2, A — у P1; не робимо висновків про точний відсоток економії process RSS або довгостроковий memory leak за цими трьома точками.

Додаткова звірка SQL: категорія у всьому ABBA має рівно 50 SQL і той самий набір із 14 normalized fingerprints з ідентичними counts. Warm mixed #2/#3/#4 — 68 SQL, однакові 31 fingerprints/counts; перший dataset має 34 fingerprints і 173 SQL, як окремий режим. SQL duration коливається, але зменшення кількості SQL не заявляється як результат цих змін. Fingerprints не містять bindings і самі по собі не доводять рівність запитуваних значень.

У цьому ABBA також незмінні card fingerprints та menu revision категорії, 54 IDs/order/types/levels і повний mixed payload hash `2406552e9a83ba6806d79f923662205d7686b34d28e1b8f1b0a1a310cbfc5b5b`. HTML receipt hash однаковий між усіма arms **всередині цього follow-up**. Ці профільні числа не включаються в 20-парні latency median/p95; вони лише уточнюють діагноз SQL/пам'яті. Після follow-up діагностику прибрано точним відновленням 15 записаних шляхів.

## Відхилені або відкладені гіпотези

- Повторне переписування HTML-меню/M42: профіль основної категорії вказує на unrelated catalog work, не на меню; чинний menu revision збережений. Контроль M42 — окремі уроки та regression tests.
- Глобальний persistent cache авторських каталогів: не потрібен для усунення невласних викликів і створює ризик пропуску source verification. Не додано.
- Масове SQL batching/N+1 refactor: повторна категорія мала 50 SQL приблизно за 267 ms проти понад 4.1 s у сторонньому `metadataPatches`. SQL шлях, relations, filters та SQL fingerprints не змінювалися кандидатом; лишаються можливістю окремої роботи після основного виграшу.
- Оптимізація warm seeder-definition lookup: у профілі категорії лише 4.697 ms; не домінує. Cold lookup окремо зафіксований вище, не замовчується.
- Переписування `GrammarTestFilterService::prepare`: повторний mixed має істотний залишковий час, але спершу усувається значно більший unrelated-catalog dispatch. Selection, deduplication, sequence та level/type interleaving не змінювалися.
- Переписування Blade, grading або frontend: Blade приблизно 12–22 ms у warm профілях, active saved state — близько 0.014 ms. Це не причина секундної затримки; browser quota defect не використано як привід змінити прогрес.

## Еквівалентність результату

У діагностичних receipts уже збігаються:

- category: 394044 bytes, шість A1–C2 test cards, точні підписи та URL; card fingerprint `9408ae06962e7fb63a2688f20be938846cc49e9f9dd33693f6ba7a795ca4f349`;
- UK desktop menu revision `45d9688239f51e117650842a619b42c6a47f6d5a5414ff35b4b8de97343eae1a`;
- mixed: 837199 bytes, 54 questions; повний payload SHA-256 `2406552e9a83ba6806d79f923662205d7686b34d28e1b8f1b0a1a310cbfc5b5b` у всіх чотирьох receipts;
- mixed question IDs, їх порядок, types та levels. На кожному A1–C2 — дев'ять питань; 36 source type `0` і 18 source type `4` загалом, як до зміни;
- links між картковим і покроковим тестом незмінні.

Повний HTML hash у початкових діагностичних BEFORE/AFTER receipts різний, хоча всередині кожної серії стабільний: category `be6f…e760` → `ce24…57d0`; mixed `dfe3…61d3` → `19bb…01bc`. Ці міжсесійні результати не переписано як рівні. У старій нормалізації виявлено конкретну прогалину: вона не покривала bare `token:` у `JS_TEST_PERSISTENCE`, де також передається CSRF. Саме лише виявлення цього поля не є відтвореним exact diff усіх старих відповідей і не замінює перевірку повного HTML.

Окремі неінструментовані парні серії дали безпосередній доказ для порівнюваних станів:

| Інваріант | Категорія, 20 пар | Mixed, 20 пар |
|---|---:|---:|
| Повний HTML після описаної нижче нормалізації CSRF | 20/20 рівні | 20/20 рівні |
| URLs і тексти test links | 20/20 рівні; 12 посилань на шести картках | 20/20 рівні |
| Повний question payload, включно з answers/options/hints/theory/variants | Не застосовується: категорія не містить question dataset | 20/20 рівні; 54 питання |
| IDs, порядок, types, levels, кількість | Не застосовується до question dataset | 20/20 рівні |
| HTML bytes | 394044 → 394044 у кожній парі | 837199 → 837199 у кожній парі |
| Меню/revision, desktop presence, locale, mode, content type та encoding | 20/20 рівні | 20/20 рівні; sidebar не додається |

Категорія запускалася зі старим normalizer, але обидві warm-руки використовували **одну й ту саму гостьову сесію**. Тому неохоплений ним CSRF не змінювався між A/B, і всі 20 пар повного HTML збіглися. Рівність порожніх масивів payload у технічному comparator категорії не називається перевіркою питань.

До mixed-серії застосовано вузьку нормалізацію: з `meta[name="csrf-token"]` береться точне значення токена; замінюються лише входження цього самого literal у документі, включно з `token:`. У записах mixed знайдено п'ять таких входжень; значення токена не зберігається в receipt. Інші scripts, session state, навчальні поля чи довільні `token`-властивості не відкидаються. Повний payload SHA-256 лишився `2406552e9a83ba6806d79f923662205d7686b34d28e1b8f1b0a1a310cbfc5b5b` у всіх warm-спостереженнях. Це окрема нова перевірка, не ретроспективне «виправлення» hash попереднього профілю.

## Наперед зафіксований протокол парних вимірювань

Приватний `measurement-protocol.json` зафіксовано до acceptance series. Профілювання SQL/spans/memory відокремлено від вимірювання latency.

1. Категорія: A = verified current PERF-02; B = лише UUID gate `savedBuilderDisplayCount`. Інші два source-файли лишаються baseline.
2. Mixed: після окремого рішення щодо категорії A = прийнятий UUID gate + baseline finite dispatcher; B = усі три кандидати. Перемикаються лише два dispatcher-файли.
3. Для кожного target — одна cold observation/arm із новим guest session (не OS/cache restart), одна окрема warm-up/arm, потім 10 блоків ABBA: 20 сусідніх A/B пар, 20 warm observations на arm.
4. Послідовні HTTP-запити до `127.0.0.1:80` з `Host: gramlyze.loc`, свіжим TCP, `Accept: text/html`, identity encoding. Один тимчасовий cookie jar на target для обох warm arms; без публікації cookie contents. Лише повний SSR HTML, не menu endpoint.
5. За фактичного перемикання source bytes — 2200 ms settle поза вимірюваним HTTP, перевірка exact preimage/hash та відсутності чужих паралельних змін. Не очищати normal warmed OPcache/Blade/menu/app caches між arms.
6. Без profiling wrapper/header у final series, без паралельних важких власних тестів/builds. Browser readiness перевіряється окремо.
7. Заздалегідь визначені виключення: transport error, timeout 60 s, non-200, неправильний response kind/locale, parse failure. Такі записи зберігаються, серія зупиняється, без тихих повторів. Semantic mismatch — регресія, не статистичне виключення.
8. Звіт: median та nearest-rank p95 TTFB/total, paired B−A median/range, кількість пар, усі exclusions; cold і warm не змішуються. Для кожного спостереження — ті самі payload/card/menu invariants.

## Окремі A/B результати без профайлера

Обидві серії завершено: по **20 валідних пар / 20 warm-запитів на руку**, без технічно невдалих спроб, виключень або повторних замін запитів. Додаткові дві cold і дві warm-up відповіді кожної серії збережені окремо; вони не входять до warm median/p95. Перед першим запитом та після зміни source bytes витримано 2200 ms поза HTTP-таймером для штатної перевірки OPcache timestamps; exact source hashes зафіксовано для кожного запиту. Налаштування PHP/debug і спосіб доставки меню не змінювалися.

Усі значення нижче — **мілісекунди**, p95 — nearest rank. Парна різниця B−A обчислюється всередині сусідніх AB або BA пар, а не відніманням двох загальних медіан.

| Окрема зміна | Median TTFB A → B | p95 TTFB A → B | Median total A → B | p95 total A → B | Парна median B−A | Кращі / гірші / рівні |
|---|---:|---:|---:|---:|---:|---:|
| Категорія: лише UUID gate | 6587.45 → 512.76 | 7002.60 → 571.31 | **6589.02 → 514.15** | 7004.25 → 572.82 | **−6038.60** | 20 / 0 / 0 |
| Mixed: лише dispatch gate, UUID gate уже є в A і B | 5376.06 → 1483.65 | 5820.31 → 1550.21 | **5379.04 → 1486.24** | 5822.99 → 1553.01 | **−3908.23** | 20 / 0 / 0 |

Парні діапазони B−A: категорія **−6653.67…−5769.54 ms**; mixed **−4357.01…−3456.41 ms**. Обидва виграші спостерігаються в усіх 20 парах і стосуються повного SSR документа, не лише helper. Збережено розміри відповідей і всі наведені вище інваріанти. За результатами окремих latency-серій обидві зміни залишено; це не закриває окремі питання пам'яті та фінального браузерного приймання.

Перші відвідини у нових гостьових сесіях, окремо від warm: категорія A **7123.76**, B **641.58 ms**; mixed A **7137.59**, B **3260.27 ms**. Це по одному спостереженню на руку, без очищення OPcache/Blade/серверного кешу, тому вони **не** характеризують cold server. Новий mixed dataset не порівнюється з resumed dataset як однаковий режим.

Сирі записи й незалежні агрегати в `storage/logs/perf-03/`:

- `category-qn-gate-abba-*.jsonl`, `.summary.json`, `.summary.md`;
- `mixed-dispatch-gate-abba-*.jsonl`, `.summary.json`, `.summary.md`.

Діагностичні SQL/spans/memory наведені вище окремо; вони не вимірювалися прихованим профайлером усередині цих acceptance-серій. У звіт не переносяться cookie/session contents чи CSRF значення. Безпечні числа всіх пар наведено в додатку.

### Сумарний результат і контрольні уроки

Окрему серію **поточний original PERF-02 → усі три кандидати PERF-03** завершено: mixed — 20 валідних пар, кожний контрольний урок — чотири. Порівнювалися зафіксовані source variants у тому самому локальному середовищі за ABBA-протоколом вище, без профайлера; failures, exclusions і повторних замін запитів немає. Інкрементальний mixed-результат **5379.04 → 1486.24 ms** не є цією сумарною серією; виграші двох різних сторінок не додаються.

| Серія | Валідні пари | Median TTFB A → B, ms | p95 TTFB A → B, ms | Median total A → B, ms | p95 total A → B, ms | Парна median B−A, ms | Кращі / гірші / рівні |
|---|---:|---:|---:|---:|---:|---:|---:|
| Mixed: original PERF-02 → усі три кандидати | 20/20 | 6851.42 → 1521.90 | 7220.13 → 1629.03 | **6853.66 → 1524.43** | 7223.51 → 1631.94 | **−5385.09** | 20 / 0 / 0 |
| Контроль Word Order lesson | 4/4 | 4986.55 → 4015.20 | 5120.37 → 4152.54 | 4991.09 → 4019.53 | 5124.55 → 4158.40 | −904.77 | 4 / 0 / 0 |
| Контроль PPC lesson | 4/4 | 4348.20 → 3422.29 | 4458.56 → 3730.59 | 4352.71 → 3426.59 | 4463.14 → 3735.14 | −941.32 | 4 / 0 / 0 |

Парні діапазони B−A: mixed **−5882.84…−5009.67 ms**, Word Order **−1107.11…−804.15 ms**, PPC **−1060.19…−607.50 ms**. У всіх 28 парах збіглися повний HTML після вузької literal-CSRF нормалізації, cards/links, доступні question payloads, IDs/порядок/types/levels/counts, меню/revision та параметри відповіді. Обсяг повного SSR документа збережений: mixed **837199 → 837199**, Word Order **606958 → 606958**, PPC **564389 → 564389 bytes**. Це не порівняння JSON із SSR.

Нові гостьові сесії, окремо від warm-статистики (по одному спостереженню на руку, **не cold server**): mixed A **8819.78**, B **3475.27 ms**; Word Order A **5019.66**, B **4360.97 ms**; PPC A **4268.82**, B **3419.01 ms**. Серверні кеші між руками не очищалися.

Чотири контрольні пари є вузькою перевіркою збереження PERF-02 на двох конкретних уроках, а не оцінкою середнього прискорення сайту чи надійною оцінкою його tail latency. При n=4 nearest-rank p95 дорівнює максимуму чотирьох спостережень. Старі абсолютні секунди PERF-01/PERF-02 не використано замість нового baseline. Пам'ять і браузерне приймання оцінюються окремо, не виводяться з latency.

Приватне джерело всіх чисел: `storage/logs/perf-03/final-original-v-candidate-abba-1794265100265.jsonl` та його `.summary.json` / `.summary.md`; 28 сирих warm-пар наведено нижче.

#### Додаток: усі warm-пари original → final candidate

У кожній парі повний HTML і доступні payload-контракти рівні; пропущених або невдалих пар немає. `BA` означає зворотний порядок запитів, а не зміну назв рук. Округлення застосовано лише до таблиці; агрегати обчислено з повних чисел.

| Target | Пара | Порядок | A total, ms | B total, ms | B−A, ms |
|---|---:|---|---:|---:|---:|
| Mixed | 1 | AB | 6799.18 | 1546.63 | −5252.55 |
| Mixed | 2 | BA | 6802.71 | 1413.94 | −5388.77 |
| Mixed | 3 | AB | 6694.53 | 1585.77 | −5108.77 |
| Mixed | 4 | BA | 6908.00 | 1360.61 | −5547.39 |
| Mixed | 5 | AB | 7015.31 | 1608.45 | −5406.85 |
| Mixed | 6 | BA | 6845.81 | 1464.39 | −5381.42 |
| Mixed | 7 | AB | 6728.76 | 1521.43 | −5207.32 |
| Mixed | 8 | BA | 6653.48 | 1382.36 | −5271.11 |
| Mixed | 9 | AB | 6640.04 | 1630.37 | −5009.67 |
| Mixed | 10 | BA | 7369.63 | 1486.79 | −5882.84 |
| Mixed | 11 | AB | 7223.51 | 1551.71 | −5671.80 |
| Mixed | 12 | BA | 6985.04 | 1753.47 | −5231.56 |
| Mixed | 13 | AB | 6768.23 | 1550.79 | −5217.44 |
| Mixed | 14 | BA | 6982.07 | 1509.35 | −5472.72 |
| Mixed | 15 | AB | 7029.34 | 1553.07 | −5476.27 |
| Mixed | 16 | BA | 6693.87 | 1476.42 | −5217.45 |
| Mixed | 17 | AB | 6841.70 | 1631.94 | −5209.76 |
| Mixed | 18 | BA | 6962.28 | 1460.91 | −5501.36 |
| Mixed | 19 | AB | 7025.86 | 1527.42 | −5498.44 |
| Mixed | 20 | BA | 6861.52 | 1373.21 | −5488.31 |
| Word Order | 1 | AB | 5124.55 | 4017.44 | −1107.11 |
| Word Order | 2 | BA | 5012.97 | 4158.40 | −854.57 |
| Word Order | 3 | AB | 4969.22 | 4014.24 | −954.98 |
| Word Order | 4 | BA | 4825.78 | 4021.63 | −804.15 |
| PPC | 1 | AB | 4463.14 | 3470.82 | −992.31 |
| PPC | 2 | BA | 4272.69 | 3382.36 | −890.33 |
| PPC | 3 | AB | 4342.64 | 3735.14 | −607.50 |
| PPC | 4 | BA | 4362.79 | 3302.59 | −1060.19 |

### Додаток: усі warm-пари двох окремих серій

Порядок `BA` означає, що B запитано першим; колонки A і B однаково називають відповідні руки. Числа округлено лише для таблиці; агрегати отримано з неокруглених observations. Для кожної наведеної пари збіглися full-HTML, cards та доступні payload-контракти; невдалих пар немає.

| Пара | Порядок | Категорія A, ms | Категорія B, ms | B−A, ms | Mixed A, ms | Mixed B, ms | B−A, ms |
|---:|---|---:|---:|---:|---:|---:|---:|
| 1 | AB | 7212.68 | 559.01 | −6653.67 | 5081.01 | 1473.58 | −3607.43 |
| 2 | BA | 7004.25 | 511.56 | −6492.69 | 5525.78 | 1367.93 | −4157.85 |
| 3 | AB | 6711.44 | 519.07 | −6192.37 | 5135.64 | 1543.30 | −3592.35 |
| 4 | BA | 6685.00 | 498.47 | −6186.53 | 5279.95 | 1570.42 | −3709.54 |
| 5 | AB | 6600.76 | 572.82 | −6027.95 | 4859.14 | 1402.73 | −3456.41 |
| 6 | BA | 6437.43 | 530.38 | −5907.06 | 5075.27 | 1362.08 | −3713.18 |
| 7 | AB | 6397.60 | 540.78 | −5856.82 | 5173.69 | 1524.41 | −3649.29 |
| 8 | BA | 6620.45 | 481.62 | −6138.83 | 5316.52 | 1415.27 | −3901.25 |
| 9 | AB | 6286.27 | 516.73 | −5769.54 | 5254.91 | 1535.80 | −3719.11 |
| 10 | BA | 6413.90 | 507.38 | −5906.51 | 5847.82 | 1490.81 | −4357.01 |
| 11 | AB | 6349.19 | 532.18 | −5817.00 | 5564.20 | 1553.01 | −4011.19 |
| 12 | BA | 6622.84 | 506.65 | −6116.18 | 5380.06 | 1466.97 | −3913.08 |
| 13 | AB | 6411.48 | 576.70 | −5834.78 | 5822.99 | 1506.41 | −4316.58 |
| 14 | BA | 6318.80 | 502.51 | −5816.29 | 5577.53 | 1481.68 | −4095.85 |
| 15 | AB | 6590.49 | 541.24 | −6049.26 | 5520.00 | 1512.38 | −4007.62 |
| 16 | BA | 6849.55 | 459.46 | −6390.09 | 5634.60 | 1412.12 | −4222.48 |
| 17 | AB | 6436.62 | 504.74 | −5931.88 | 5531.17 | 1508.97 | −4022.19 |
| 18 | BA | 6587.55 | 504.95 | −6082.60 | 5481.96 | 1418.85 | −4063.11 |
| 19 | AB | 6414.50 | 540.15 | −5874.35 | 5378.01 | 1508.51 | −3869.50 |
| 20 | BA | 6601.34 | 487.48 | −6113.87 | 5336.17 | 1432.79 | −3903.37 |

## Регресійні тести

Додані окремі тести без HTTP, без Laravel DB fixtures і без benchmark thresholds:

- `tests/Unit/QuestionsNegationsDisplayCountPerformanceTest.php`: before-oracle для 12 builder identities, правильні/неправильні slug/filters/locale/context, standards/course/unknown UUID, відсутність file read/hash для невласних UUID, збереження fail-closed при missing/bad-pin/stale sources, зокрема іншого topic у власному повному каталозі. Очікування не отримуються з результату оптимізованого методу.
- `tests/Unit/FiniteCatalogDispatchPerformanceTest.php`: 25 finite prefixes / 193 topics, непересічність і збереження prefix у persistent UUID; 54 реальні source payloads Word Order проти before-oracle; question/type/answer/revision mutations, source guards, foreign/non-string/unknown matching prefix, три legacy dispatchers, відсутність матеріалізації невласних каталогів. Canonical definitions порівнюються з незмінними checksum pins, а не переписуються.

PHP-перевірки виконувалися локально з установленими залежностями served ROOT (окремий локальний checkout, PHP 8.5.10), а не через HTTP до production. Початкові спроби у WT не мали потрібних `vendor` / `jsdom`; це обмеження середовища запуску, не помилка застосунку. Коректні повторні запуски використовували встановлені залежності ROOT.

Вісім тестів `FiniteCatalogDispatchPerformanceTest` пройшли після test-only уточнення checksum pins щодо LF/CRLF: 20 519 assertions. Окреме порівняння WT/ROOT підтвердило однакові байти після нормалізації лише CRLF→LF (1620 перенесень у standard definition, 4070 у builder); canonical sources, чинні source pins та відповіді не змінювалися. Залишено сувору рівність повних декодованих definitions і before-oracle payloads.

Результати ширшої серії за приватним receipt `storage/logs/perf-03-runtime/targeted-tests.xml`:

| Клас | Passed | Failures | Errors | Assertions |
|---|---:|---:|---:|---:|
| `Tests\Feature\MixedTheoryPageTestRenderTest` | 1 | 3 | 0 | 8 |
| `Tests\Feature\QuestionsNegationsQualityRuntimeTest` | 3 | 1 | 0 | 20394 |
| `Tests\Feature\SavedTestJsStateTest` | 9 | 1 | 1 | 71 |
| `Tests\Feature\WordOrderQualityRuntimeIntegrationTest` | 3 | 0 | 0 | 2680 |
| `Tests\Unit\FiniteCatalogDispatchPerformanceTest` | 8 | 0 | 0 | 20519 |
| `Tests\Unit\GrammarTestFilterDeterminismTest` | 3 | 0 | 0 | 15 |
| `Tests\Unit\SavedTestJsStateSynonymsTest` | 1 | 0 | 0 | 4 |
| `Tests\Unit\SavedTestJsStateTest` | 19 | 0 | 0 | 39 |
| **Разом: 53 тести** | **47** | **5** | **1** | **43730** |

Окремий запуск тих самих шести невдалих cases на збереженому baseline (`baseline-failures.xml`): 6 tests, 84 assertions, 5 failures, 1 error. Для кожного case зіставлено точну назву, failure/error kind, exception type і початкові рядки повідомлення; усі шість збігаються:

| Точний тест | Однакова причина BEFORE / AFTER |
|---|---|
| `Tests\Feature\MixedTheoryPageTestRenderTest::test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes` | Очікується HTTP 302, фактично 404. |
| `Tests\Feature\MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2` | Очікується HTTP 302, фактично 404. |
| `Tests\Feature\MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level` | Очікується HTTP 302, фактично 404. |
| `Tests\Feature\QuestionsNegationsQualityRuntimeTest::test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices` | Очікується 4 варіанти, фактичний розмір — 5. |
| `Tests\Feature\SavedTestJsStateTest::test_it_fetches_questions_for_filter_based_saved_test` | Очікується HTTP 200, отримано 500: у SQLite fixture немає таблиці `question_hints`. |
| `Tests\Feature\SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` | `Illuminate\Database\QueryException`: у SQLite fixture немає таблиці `question_hints`. |

Ці шість результатів не є новою регресією PERF-03, але **ширша серія не є повністю зеленою**. Сторонні тести, fixtures і очікування не переписувалися, щоб приховати baseline-проблеми.

Додатково підтверджені результати запусків:

- новий `QuestionsNegationsDisplayCountPerformanceTest`: 4/4 passed;
- чинні menu/M42 regression tests: 18/18 passed;
- JavaScript: 12/12 Node tests і 7/7 Vitest tests passed з установленими залежностями ROOT;
- попередній сукупний PHP-запуск із 30 тестів і 21746 assertions мав лише дві невідповідності LF/CRLF у нових finite-oracle тестах; їх виправлено лише в новому тесті та підтверджено фінальним сукупним запуском нижче.

### Фінальний core/menu/M42 запуск після відновлення коду без профайлера

Каталог запуску: корінь локального checkout, який обслуговує сайт. Використано PHPUnit **12.5.35**, PHP **8.5.10**, `phpunit.xml` і `tests/bootstrap.php`. Команда нижче передбачає локальний baseline PERF-01/PERF-02, зокрема його тести меню та M42. У коміті PERF-03 окремо доступні перші два regression test-файли; лише з ним сукупну серію й вихідні вимірювання не відтворити. Команда PowerShell (шлях до локального PHP скорочено):

```powershell
& php vendor/bin/phpunit --configuration phpunit.xml --do-not-cache-result --colors=never --log-junit storage/logs/perf-03-runtime/core-final.xml tests/Unit/QuestionsNegationsDisplayCountPerformanceTest.php tests/Unit/FiniteCatalogDispatchPerformanceTest.php tests/Unit/M42RequestArtifactMemoTest.php tests/Unit/TheoryMenuStoreTest.php tests/Feature/Theory/TheoryMenuBuildCommandTest.php tests/Feature/Theory/TheorySidebarActiveStateTest.php tests/Feature/Theory/TheoryCategorySmokeTest.php tests/Feature/TheoryCanonicalLessonUrlTest.php
exit $LASTEXITCODE
```

Результат: **exit 0; 30/30 tests, 21923 assertions, 0 failures, 0 errors, 0 skipped**. JUnit receipt — `storage/logs/perf-03-runtime/core-final.xml` у served ROOT; безпечний запис exit code і підсумку — `storage/logs/perf-03/core-final-receipt.json` у WT. PHPUnit також повідомив **1 deprecation** (`OK, but there were issues!`): це не failure, але запуск не називається вільним від попереджень. Текст цієї deprecation у JUnit не записаний; причину не вигадано й не приховано.

| Клас фінального запуску | Passed | Assertions |
|---|---:|---:|
| `QuestionsNegationsDisplayCountPerformanceTest` | 4 | 644 |
| `FiniteCatalogDispatchPerformanceTest` | 8 | 20519 |
| `M42RequestArtifactMemoTest` | 3 | 318 |
| `TheoryMenuStoreTest` | 5 | 56 |
| `TheoryMenuBuildCommandTest` | 2 | 55 |
| `TheorySidebarActiveStateTest` | 4 | 91 |
| `TheoryCategorySmokeTest` | 1 | 220 |
| `TheoryCanonicalLessonUrlTest` | 3 | 20 |
| **Разом** | **30** | **21923** |

Перед запуском прочитано bootstrap, `IsolatedTestEnvironment`, `CreatesApplication` і schema guards. Вони примусово задають `testing`, SQLite та випадковий приватний test runtime, не завантажують робочий `.env`, ізолюють storage/cache/session; перед schema operations перевіряють SQLite та належність шляху. Навчальний контент і робоча MySQL БД не змінювалися.

Production-profile перевірено **лише ізольовано в локальному test kernel**, без зовнішніх HTTP-запитів чи production DB:

- `TheoryMenuBuildCommandTest::test_command_respects_enabled_locales_and_isolates_the_synthetic_production_mode`: синтетичний `production.invalid`, окремі menu snapshots і дозволені locales;
- `TheorySidebarActiveStateTest::test_production_profile_isolated_from_development_and_private`: синтетичний `perf-production.test`, development/production isolation та private/no-cache headers.

Фінальна серія з 30 тестів не підміняє ширшу серію з 53: усі шість підтверджених baseline-проблем вище залишаються явно зазначеними.

## Фінальне приймання та очищення

Після останнього balanced profile відновлено **15 точних preimages**: змінені профайлером файли повернуто до фінального кандидата; `public/index.php` — до вихідних байтів, runtime `probe.php` та nonce видалено. Не залишено нових routes, profiling includes/headers або diagnostic payload у public runtime. Приватні receipts зберігаються лише під ignored `storage/logs/`.

Read-only перевірка дала **141/141**, 0 failures. Приватний receipt залишено поза Git. Зіставлено обидва BEFORE manifests, candidate-файли WT/ROOT, незмінність HEAD/branches, PERF-01/PERF-02 файлів і відсутність runtime-profiler markers. Відмінність від збереженого runtime BEFORE — лише три файли нижче: 21 доданий, 2 замінені рядки за diff.

| Фінальний runtime-файл | Bytes | SHA-256 |
|---|---:|---|
| `app/Support/QuestionsNegationsPracticeQuality.php` | 23322 | `f98c275274d990082866da743772a9f9f898bb8add4bf262fd00121c7484b5e7` |
| `app/Support/FinitePracticeQualityCatalog.php` | 32226 | `b22c44cf49df74331db8efd684c0f05e769cd55ed7b9d531519989e2c9f5ef01` |
| `app/Support/PassivePracticeQuality.php` | 7145 | `f50f0223b4e5db4cfe91feb7885b973571eede697034a42362da792e799414ee` |

Додано лише два нові regression test-файли та цей звіт. У WT/ROOT залишаються попередні незакомічені PERF-01/PERF-02 зміни; вони не приписуються PERF-03. `.env`, конфігурація сервера, навчальні definitions/snapshots, answers/hints/aliases/CEFR, selection/grader та дизайн не редагувалися. Жодного content apply, міграції чи reseed не було. Попередні звіти збережено.

Після завершення браузерного повтору й core tests виконано ще одну файлову звірку: знову **141/141**. Runtime bytes залишилися тими самими; `git diff --check` для трьох source-файлів не виявив whitespace errors.

Файли меню development/UK перевірено за активним manifest: revision `45d9688239f51e117650842a619b42c6a47f6d5a5414ff35b4b8de97343eae1a`, desktop/mobile hashes і bytes незмінні. `theory:menu-build` збережено. M42-код exact BEFORE, а його 3 regression tests / 318 assertions підтвердили першу перевірку файлів, reuse лише в межах request і перевірку в новому request. Незмінний full payload і HTML у A/B підтверджують незмінність питання/відповіді/підказки/marker/theory references та metadata на вибраних цілях, а не на довільних сторінках усього сайту.

### Короткий HTTP smoke точних фінальних bytes

`final-clean-smoke-*.jsonl`: по одному свіжому guest GET без профайлера; source hashes перевірено до і після відповіді. Усі чотири — HTTP 200, UK/development, очікуваний HTML, без profiling headers. Це функціональний smoke, не ще одна статистична серія.

| Сторінка | Total, ms | HTML bytes |
|---|---:|---:|
| Категорія Word Order | 567.9 | 394044 |
| Mixed, новий dataset | 3510.6 | 837199 |
| Урок Word Order | 4017.8 | 606958 |
| Урок Past Perfect Continuous | 3417.8 | 564389 |

Новий dataset у цьому smoke не порівнюється з медіаною warm/resumed dataset.

### Реальний фінальний браузерний повтор

Виконаний **після** видалення останньої діагностики, на тих самих hashes. Усі чотири URL відвідано у браузері, не лише перевірено текст HTML. Перевірено 1440, 390 і 320 px та обидві теми в сукупності; це не твердження про повний pixel-by-pixel тест усіх комбінацій.

| Сторінка / сценарій | Фактичний результат |
|---|---|
| Категорія | 8 уроків, 6 A1–C2 test cards; active «Порядок слів»; пошук Past Perfect Continuous знаходить його 4 уроки та порівняння; clear відновлює дерево; згортання/розгортання змінює `aria-expanded`. |
| Mobile category menu | Відкрити → закрити → повторно відкрити; рівно 1 mobile fragment поряд із 1 прихованим desktop fragment, без додаткових копій. |
| Word Order lesson | Заголовок/практика доступні на 320/390/1440; mobile search відкривається; native manual `wrong` дає error-state, очищення і токени `She` → `often` → `reads` → `books` дають success-state. Наявний результат embedded builder не скидався. |
| PPC lesson | Правильний active lesson; теми й структура видимі на 320/390/1440; mobile menu двічі відкривається з одним mobile tree; практика та тематичний builder присутні. |
| Mixed | 54 питання; card layout і перша доступна вправа працюють; особистий прогрес до/після незмінний. |
| Власний step launch | Повторно перевірено gap, compose й reorder: wrong → edit → correct; manual і токени; «Далі»; reload відновлює правильну третю вправу; штатний reset повертає власну сесію до порожнього першого питання. Після повернення до card особистий прогрес незмінний. |

Готовність першої **доступної** вправи в тому самому card state (частину вправ уже виконано): BEFORE **8097 ms**, final **1909 ms** від початку browser reload до visible+enabled input через tool. Це одиничні **верхні межі з overhead керування браузером**, не Navigation Timing/FCP, не 20-парна метрика і не оцінка всіх пристроїв. У фінальному вимірі власні важкі тести ще не запускалися; пізніші функціональні кліки не використовуються для timing claims.

Theme повернуто до початкової світлої, тимчасовий viewport override скинуто. Збірку контенту та особистий прогрес не очищено.

### Відомі обмеження, не замасковані під PASS

- `QuotaExceededError` у browser `localStorage` існував на baseline і лишився. Серед переглянутих фінальних console entries інших класів помилок не виявлено; це **не** «нуль JS errors». Власний step reset/resume працює, але переповнене browser storage потребує окремого завдання без довільного видалення персональних даних.
- У mixed на 320 px DOM зафіксував `scrollWidth=397` при `clientWidth=305`; видимий верх сторінки переносився, перевірені input/button/article/nav не виходили праворуч. Причину загального overflow не встановлювали й не робили окремого baseline geometry A/B. CSS/HTML не змінювалися в PERF-03; повного responsive/pixel-perfect PASS не заявляємо.
- Ширша PHP-серія має 6 підтверджених baseline failures/errors; core30 зелений із 1 deprecation. Не змінено unrelated fixtures або assertion thresholds.
- Старий memory peak 156/310 MiB не має arm/PID/used деталізації; новий balanced profile не переписує старе спостереження. Пам'ять без профайлера та process RSS окремо не вимірювалися, а production runtime не перевірявся через HTTP.
- У served ROOT `routes/api.php` був сторонньо змінений до задачі, без raw-byte BEFORE snapshot. Підтверджено незмінний path/status і відсутність profiler markers, але не заявляємо недоступну byte-for-byte BEFORE перевірку цього файла. PERF-03 його не редагував.
- Контрольні уроки мають по 4 пари, не 20; це локальна перевірка збереження PERF-02, не сайтова статистика. Відомий route collision не виправлявся.

**Рішення:** обидві точкові оптимізації залишено локально. Вони усувають виміряні домінантні зайві читання/компіляції сторонніх каталогів, прискорюють повні SSR-сторінки в усіх основних парах, зберігають результат та власні integrity guards. PERF-01/PERF-02 збережено; фінальний код перевірено після очищення діагностики. Для публікації підготовлено лише PERF-03; локальний baseline PERF-01/PERF-02 не включено до цього коміту. `main` і production не змінювалися.
