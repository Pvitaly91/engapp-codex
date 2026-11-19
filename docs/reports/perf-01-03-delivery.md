# PERF-DELIVERY-01 — повний source-стан PERF-01/02/03

## Стан до доставки та коміти

Ціль: `codex/perf-theory-menu-html-cache` у репозиторії `Pvitaly91/engapp-codex`. На початку live remote і робочий WT вказували на `9337430efec58f310dc19c0ec8eb2937eb528202` (батько `1c0e9d8323552c6e7a4e849c571598b8b580b3f3`). PERF-03 уже був там; PERF-01/02 були лише локальними змінами. Apache `gramlyze.loc` обслуговував окремий ROOT із `public` на `41820a2bebdf69004fa7209a2a38457f93efabbd`, гілка `codex/production-ready-a14788dac`, із численними сторонніми змінами. Git HEAD, файл на диску та відповідь вебсервера не вважалися взаємозамінними.

- PERF-01 (31 шлях) і PERF-02 (3 шляхи): `d4c5df288998faaf18a751acde5f688cfa51c0cd`, перевірене дерево source `f238fbf3a6cafb9a12478827b950a150660e8d91`.
- PERF-03: початковий `9337430efec58f310dc19c0ec8eb2937eb528202` без amend, rebase або зміни історичного звіту.
- Окремий наступний коміт містить лише цей delivery report; жодних runtime-відмінностей від перевіреного дерева.

Приватний recovery створено **до** підготовки кандидата. Він містить окремі WT/ROOT копії потрібних файлів (включно з untracked), Git status та два SHA-256 manifests по 42 шляхи із записами про відсутність. Із фінальним historical receipt PERF-03 збіглися всі 39 доступних для порівняння шляхів на кожній стороні. Після smoke повторні 42/42 на кожній стороні без змін. Усі 34 шляхи обрано явно; сторонній ROOT diff не переносився. Спільні файли `.gitignore`, `PageController`, `ApplySiteMode`, `M42NativeDesignPackage` окремо звірено за призначенням hunks. Чужих hunks у WT для цих шляхів не було.

Для п'яти пар із різним SHA відмінність лише CRLF/LF; у кандидат взято байти served ROOT. Інші наявні runtime-файли збігалися точно. WT має два звіти, відсутні у ROOT; його Composer lock відрізнявся від ROOT. Candidate lock-файли взято з Git-бази, не з активної копії. Перевірений staged blob для кожного доданого/зміненого файла збігається з байтами кандидата без Git filters. Локальний checkout із `core.autocrlf=false` зберіг зафіксовані Git-байти hash-pinned JSON M42/M27/M39; системні правила не змінювалися. Whitespace check проведено з явним `cr-at-eol` для збережених CRLF, без вимкнення перевірок зайвих пробілів. Глобальної нормалізації не було.

| Файл | PERF і призначення | Git до доставки | WT | Served ROOT | Перевірені байти / стан |
|---|---|---|---|---|---|
| `.gitignore` | 01: Ігнорування лише runtime menu snapshots | стара версія | є (M) | є (M) | WT = ROOT |
| `app/Http/Controllers/PageController.php` | 01: Desktop SSR, revision endpoint, fallback, порядок карток | стара версія | є (M) | є (M) | WT = ROOT |
| `app/Http/Middleware/ApplySiteMode.php` | 01: Cache headers для theory HTML | стара версія | є (M) | є (M) | WT = ROOT |
| `app/Support/M42NativeDesignPackage.php` | 02: Request-scoped memo з чинними integrity guards | стара версія | є (M) | є (??) | WT = ROOT |
| `resources/views/theory/category.blade.php` | 01: Інтеграція меню категорії | стара версія | є (M) | є (M) | WT = ROOT |
| `resources/views/theory/partials/desktop-navigation-loader.blade.php` | 01: Вилучено замінений partial | старий, вилучено | немає (D) | немає (D) | відсутність |
| `resources/views/theory/partials/desktop-sidebar.blade.php` | 01: Inline SSR і стан поточної сторінки | стара версія | є (M) | є (??) | ROOT, лише EOL |
| `resources/views/theory/partials/mobile-navigation-content.blade.php` | 01: Вилучено замінений partial | старий, вилучено | немає (D) | немає (D) | відсутність |
| `resources/views/theory/partials/mobile-navigation.blade.php` | 01: Alpine і відкладена pinned revision | стара версія | є (M) | є (M) | WT = ROOT |
| `resources/views/theory/partials/tree-nav-mobile.blade.php` | 01: Вилучено замінений partial | старий, вилучено | немає (D) | немає (D) | відсутність |
| `resources/views/theory/partials/tree-nav.blade.php` | 01: Вилучено замінений partial | старий, вилучено | немає (D) | немає (D) | відсутність |
| `resources/views/theory/show.blade.php` | 01: Інтеграція меню уроку | стара версія | є (M) | є (M) | WT = ROOT |
| `tests/Browser/theory-search-ranking.test.cjs` | 01: Пошук і ранжування | стара версія | є (M) | є (M) | ROOT, лише EOL |
| `tests/Feature/Theory/TheoryCategorySmokeTest.php` | 01: HTTP-контракт категорій | стара версія | є (M) | є (M) | ROOT, лише EOL |
| `tests/Feature/Theory/TheorySidebarActiveStateTest.php` | 01: SSR, active state, hit/miss, HTTP-контракт | стара версія | є (M) | є (M) | ROOT, лише EOL |
| `tests/Feature/TheoryCanonicalLessonUrlTest.php` | 01: Canonical URL і lazy navigation | стара версія | є (M) | є (M) | WT = ROOT |
| `tests/js/theorySidebarLayout.test.js` | 01: Геометрія й поведінка sidebar | стара версія | є (M) | є (M) | ROOT, лише EOL |
| `app/Console/Commands/BuildTheoryMenuCommand.php` | 01: CLI build, перевірка й rollback | відсутній | є (??) | є (??) | WT = ROOT |
| `app/Services/TheoryMenuBuilder.php` | 01: Свіже дерево, локалізація, генерація й валідація | відсутній | є (??) | є (??) | WT = ROOT |
| `app/Services/TheoryMenuCatalog.php` | 01: Доступ до каталогу меню | відсутній | є (??) | є (??) | WT = ROOT |
| `app/Services/TheoryMenuStore.php` | 01: Публікація, перевірка, попередня ревізія | відсутній | є (??) | є (??) | WT = ROOT |
| `docs/reports/perf-menu-page-latency-followup.md` | 02: Звіт PERF-02 і межі вимірів | відсутній | є (??) | немає | WT |
| `docs/reports/perf-theory-menu-html-cache.md` | 01: Звіт PERF-01 і межі вимірів | відсутній | є (??) | є (??) | WT = ROOT |
| `docs/theory-menu-cache.md` | 01: Експлуатація, перший build і rollback | відсутній | є (??) | є (??) | WT = ROOT |
| `public/js/theory-menu.js` | 01: Active state, пошук, згортання та mobile lifecycle | відсутній | є (??) | є (??) | WT = ROOT |
| `resources/views/theory/partials/menu-cache-assets.blade.php` | 01: Інтеграція JS і стилів | відсутній | є (??) | є (??) | WT = ROOT |
| `resources/views/theory/partials/menu-cache-children.blade.php` | 01: Вкладеність дерева | відсутній | є (??) | є (??) | WT = ROOT |
| `resources/views/theory/partials/menu-cache-fallback.blade.php` | 01: Резервне посилання | відсутній | є (??) | є (??) | WT = ROOT |
| `resources/views/theory/partials/menu-cache-fragment.blade.php` | 01: Нейтральний reusable HTML-фрагмент | відсутній | є (??) | є (??) | WT = ROOT |
| `resources/views/theory/partials/menu-cache-tree.blade.php` | 01: Розмітка повного дерева | відсутній | є (??) | є (??) | WT = ROOT |
| `tests/Browser/theory-menu-snapshot.test.cjs` | 01: Меню, BFCache, mobile та fallback | відсутній | є (??) | є (??) | WT = ROOT |
| `tests/Feature/Theory/TheoryMenuBuildCommandTest.php` | 01: Чистий build, незмінна ревізія та locale isolation | відсутній | є (??) | є (??) | WT = ROOT |
| `tests/Unit/M42RequestArtifactMemoTest.php` | 02: Перший, повторний і наступний HTTP request | відсутній | є (??) | є (??) | WT = ROOT |
| `tests/Unit/TheoryMenuStoreTest.php` | 01: Атомарність, rollback, fallback і двоє writer-процесів | відсутній | є (??) | є (??) | WT = ROOT |
| `app/Support/QuestionsNegationsPracticeQuality.php` | 03: Ранній відбір власних identity | PERF-03 | є | є (??) | commit 9337430 |
| `app/Support/FinitePracticeQualityCatalog.php` | 03: Dispatch без матеріалізації чужих каталогів | PERF-03 | є | є (??) | commit 9337430 |
| `app/Support/PassivePracticeQuality.php` | 03: Ранній відбір зі збереженням guards | PERF-03 | є | є (??) | commit 9337430 |
| `tests/Unit/QuestionsNegationsDisplayCountPerformanceTest.php` | 03: Регресія question dispatch і кількості варіантів | PERF-03 | є | є (??) | commit 9337430 |
| `tests/Unit/FiniteCatalogDispatchPerformanceTest.php` | 03: Finite prefixes, source guards і before-oracle | PERF-03 | є | є (??) | commit 9337430 |
| `docs/reports/perf-category-mixed-tests.md` | 03: Історичний звіт PERF-03, незмінний | PERF-03 | є | немає | commit 9337430 |

## Чистий кандидат і збірка

Окремий чистий checkout бази PERF-03, накладені лише scoped файли зі snapshot. Новий локальний Composer autoload: перевірено PSR lookup roots, всі 9 934 classmap entries, включені файли та Reflection для генератора, menu store, M42, finite catalog і bootstrap тестів. Усі локальні; посилань на PHP-файли із served ROOT немає. Власного `.env` в кандидата немає. PHPUnit примусово ізолює testing, SQLite, Blade/cache, session та storage, а schema guard перевіряє реальний PDO driver/шлях. Робочу MySQL, БД прогресу та особисті сесії не змінено.

`composer install --no-plugins --no-scripts`: 122 пакети з lock, exit 0. `npm ci --offline --ignore-scripts`: 192 пакети, exit 0. `npm run build`: справжня production-збірка Vite 5.4.19 (57 modules, 4 outputs + manifest), exit 0. Виходи збірки збережено приватно й не додано в Git. Composer мав 1 415 повідомлень про PSR-4 у наявних seeders і deprecation свого інструментарію на PHP 8.5; npm повідомив про deprecated `whatwg-encoding@3.1.1`, Vite — про застарілий Browserslist dataset. Залежності та lock-файли не оновлювалися.

## Тести

PHP 8.5.10, PHPUnit 12.5.35, bootstrap і тести самого кандидата. Збережено JUnit і коди завершення поза Git.

| Набір | Унікальні методи / виконання | Assertions | Failures / errors / skips | Exit |
|---|---:|---:|---:|---:|
| Core/menu/M42: 8 класів | 28 / 30 | 21 923 | 0 / 0 / 0 | 0 |
| Ширший regression: 8 класів | 41 / 53 | 43 730 | 5 / 1 / 0 | 2 |
| Node: два файли меню й пошуку | 12 перевірок | — | 0 / 0 / 0 | 0 |
| Vitest: `theorySidebarLayout.test.js` | 7 перевірок | — | 0 / 0 / 0 | 0 |

Core включає `QuestionsNegationsDisplayCountPerformanceTest` (4), `FiniteCatalogDispatchPerformanceTest` (8), `M42RequestArtifactMemoTest` (3 dataset cases), `TheoryMenuStoreTest` (5), `TheoryMenuBuildCommandTest` (2), `TheorySidebarActiveStateTest` (4), `TheoryCategorySmokeTest` (1) та `TheoryCanonicalLessonUrlTest` (3). У паралельній перевірці publication два PHP child processes завершилися з 0, що є assertion тесту. Для PHP core — 1 deprecation `PDO::MYSQL_ATTR_SSL_CA`; у broad додатково 3 виклики `ReflectionMethod::setAccessible()` (усього 4). Попередження про відсутній snapshot у тестових логах очікувані в сценаріях fallback.

Ізольована команда зареєстрована: свіжий snapshot з fixture, повтор без зміни revision та без зайвої активації; читання SSR і pinned mobile; cache hit без SQL повного дерева і без його повторного Blade-render; відсутній/зіпсований snapshot та невдалий build дають безпечний fallback. M42 зберігає перший integrity check, повторне використання лише в межах запиту й перевірку в новому запиті. Finite dispatch зберігає guards та порівняний before-oracle. Fixture production mode ізольований; реальний production не викликався.

Усі шість broad відхилень збіглися з попереднім baseline за методом, failure/error kind, exception type, основним повідомленням і ознакою відсутньої таблиці:

| Метод | Той самий результат |
|---|---|
| `MixedTheoryPageTestRenderTest::test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes` | 302 → 404 |
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2` | 302 → 404 |
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level` | 302 → 404 |
| `QuestionsNegationsQualityRuntimeTest::test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices` | Очікується 4 варіанти, є 5 |
| `SavedTestJsStateTest::test_it_fetches_questions_for_filter_based_saved_test` | 200 → 500, у SQLite fixture немає `question_hints` |
| `SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` | `QueryException`: у SQLite fixture немає `question_hints` |

## Функціональний smoke gramlyze.loc

Реальний локальний браузер Chromium, нові гостьові контексти без персональних cookies, desktop 1440 px і mobile 390 px. Сценарії запису відповіді перевіряли settled DOM/state, не серверний прогрес: два write-запити перехоплено в тестовій сесії, зовнішні хости заблоковані.

| Сторінка | Результат |
|---|---|
| Категорія Word Order | Перший запуск: навігація перевищила 60 s, без HTTP-висновку. Один окремий повтор: 200, active category, пошук 24 → 1 → 24, згортання/розгортання. |
| Урок Word Order | 200, активний урок та конструктор речень. |
| Урок Past Perfect Continuous | 200, активний мобільний урок, два відкриття → один pinned fetch і рівно одне mobile дерево. |
| Mixed Word Order | 200, новий гостьовий dataset із 54 питань, без sidebar. Manual wrong → редагування скидає feedback → складання правильними токенами → settled correct, поле недоступне для редагування. |

У чотирьох успішних відповідях не було перевірених profiling/debug headers; `public/probe.php` відсутній. Нової runtime-діагностики не додано. У цих контекстах не зафіксовано page errors; давні `QuotaExceededError` і overflow не виправлялись і не оголошені усуненими. Smoke на served ROOT підтверджує видиму поведінку, а не тотожність усіх файлів ROOT та candidate. Нову 20-парну серію не запускали. Цей новий гостьовий dataset не є warm/resumed dataset і не підтверджує числових результатів на іншому сервері.

## Відновлення меню

```sh
php artisan theory:menu-build
php artisan theory:menu-build --check
```

Команди запускаються для потрібного режиму й мови з правильним `APP_URL`; див. [інструкцію генерації та rollback](../theory-menu-cache.md). Наявний snapshot development не копіюється до production. У Git є генератор, reader, templates, assets, тести й документація, але немає нових згенерованих menu HTML, активних runtime manifests, raw logs, HAR, дампів, секретів або recovery. Вихідні ROOT і WT, `main` і production не змінювалися. Дозвіл на деплой не використовувався; нового PERF-04 немає.
