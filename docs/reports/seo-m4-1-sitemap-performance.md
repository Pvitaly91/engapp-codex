# M4.1 — sitemap performance та приймання курсів

Дата: 2026-09-09. Проєкт: Gramlyze. Перевірки HTTP — **тільки `http://gramlyze.loc`**.

## Результат

Генерація повного sitemap прискорена з **18,937–21,875 с** до **3,079–3,437 с** у прямих гостьових GET. Медіана: **19,266 → 3,360 с**, приблизно **5,73×** (−82,6%). Інженерний орієнтир 1–3 с наближений, але сувора верхня межа 3 с для кожного HTTP-запиту **не досягнута**. CLI kernel profile після оптимізації: 2,261 с, SQL 1,264 с; це не заміна HTTP-вимірювання.

**554 URL збережено побайтно**, не лише за кількістю. Added = 0, removed = 0, змін порядку = 0, змін `lastmod` = 0. Кеш готового XML/result не додавався і не використовується діагностичним шляхом.

Обидва course suites завершені окремо, разом і у зворотному порядку; усі чотири course runs PASS. Production не перевірявся і не оновлювався.

## База та межі

Після `git fetch origin`:

| Ref | SHA |
| --- | --- |
| Початковий HEAD, `codex/seo-m4-indexable-sitemap` | `9b8a16872fcc4b1d9d0d3a30cf8af788ccf16c6c` |
| `origin/codex/seo-m4-indexable-sitemap` | `9b8a16872fcc4b1d9d0d3a30cf8af788ccf16c6c` |
| `origin/main` | `c77b4326a92b2c1e92c80b07393d8e7000c0fe33` |
| Нова робоча гілка | `codex/seo-m4-1-sitemap-performance` |

Прочитано кореневий `AGENTS.md` і `docs/reports/seo-m4-indexable-sitemap.md`. Новішої роботи поверх M4 у refs не було. Сторонні незакомічені PPC-аудити, `.codex/`, archives/dumps, попередні приватні evidence та `__pycache__` збережені й не включаються в commit.

Не змінювалися `.env`, APP_KEY, Apache, php.ini, hosts, schema/індекси робочої БД, навчальні дані, session/CSRF/progress/autosave, availability/Coming Soon, canonical/robots, assets/верстка. Робочі міграції, seeders, cache clear, deployment і production-запити не запускалися. Усі сідери цього етапу — лише в приватних SQLite fixtures під захищеним runner.

## Середовище і методика

- Windows, локальний Apache/XAMPP, PHP 8.2.12; фактично підтверджено **MariaDB 12.0.2** через loopback connection.
- HTTP samples: Python, реальний GET, новий гість, без Cookie/Authorization/Referer, proxy, redirect і retry. Timeout лишився 45 с.
- До змін: три послідовні GET, без паралельного build/test/crawl. Кожна спроба й XML збережені. Після змін — п'ять послідовних GET, потім одна пара одночасних GET (максимум 2).
- Дані, конфігурація та ordered XML незмінні. Глобальні DB/OS caches не очищалися: це нові запити, **не доказ повністю холодної БД**.
- Wall-time GET зібрано без CLI profiling. Опційний profiler використовує окремий process/private storage, array cache/session, CLI OPcache off, той самий локальний DB dataset та Coming Soon = true. `DB::listen` збирає лише timing; bindings залишаються лише в пам'яті для EXPLAIN і не потрапляють у report.
- Profiler дозволяє лише loopback DB, встановлює **session-only READ ONLY transaction**, rollback після читання і session-only `max_statement_time=40`. Публічного debug endpoint немає. Ці session settings не змінюють глобальну конфігурацію MariaDB або вебзапити.
- EXPLAIN виконано після вимірюваного kernel interval. `ANALYZE FORMAT=JSON`, `ANALYZE TABLE` та schema-зміни не застосовувалися. Оцінки rows у плані — не виміряна кількість фізичних reads.

## HTTP до/після і точна еквівалентність

| Серія | Послідовні wall times, с | Результат |
| --- | --- | --- |
| M4 baseline | 18,937; 19,266; 21,875 | 3 × 200, повний XML |
| Проміжна: reverse metadata index + partitioned collections | 4,094; 3,640; 3,813; 3,281; 3,922 | 5 × 200, той самий XML; не приховано |
| Фінальна: також один matching predicate на candidate | 3,437; 3,360; 3,250; 3,079; 3,422 | 5 × 200, той самий XML |
| Пара після фінальних samples | 3,140; 3,125 | 2 × 200, повний XML |

Для **кожного** успішного XML:

- Content-Type: `application/xml; charset=UTF-8`;
- 82 263 bytes;
- SHA-256: `9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885`;
- development `noindex` збережено;
- 554 ordered loc; жодного `lastmod`.

Склад: 297 старих адрес (головна, індекс теорії, 41 категорія, 254 сторінки) + 254 MAIN mixed tests + `/courses` + `/courses/english-grammar-theory` + `/courses/theory-driven`. Число 554 ніде не використовується у runtime як критерій відбору. Branded course homes, які M4 виключає через чинний entry gate, не відкривалися.

Приватні evidence: `storage/app/seo-m4-1-local/m41-before.json`, `m41-after.json`, `m41-final-get.json`, `m41-pair.json` та відповідні XML. Кожен JSON містить час спроби, статус, bytes/hash і повний ordered loc/lastmod. Існуючий evidence label/XML не перезаписується повторним запуском.

## Де витрачався час

Порівняння однаково інструментованих CLI kernel runs (`m41-before3-profile.json` / `m41-final-profile.json`), мс:

| Етап | Wall до | Wall після | SQL до | SQL після |
| --- | ---: | ---: | ---: | ---: |
| Categories/pages | 43,975 | 35,570 | 6,790 | 5,030 |
| Page/stable MAIN slug mapping | 113,070 | 129,124 | 2,680 | 2,640 |
| Persistent linked metadata | 4 507,999 | 166,197 | 4 422,730 | 51,630 |
| PHP linked collections | 13 761,806 | 192,371 | 0 | 0 |
| Question statistics | 475,825 | 240,321 | 230,340 | 217,710 |
| PHP candidate collections/collisions | 67 423,992 | 323,279 | 34,270 | 2,020 |
| Matching/usable aggregation | 2 604,542 | 965,836 | 2 206,010 | 916,720 |
| Course readiness | 469,566 | 140,254 | 131,980 | 68,150 |
| XML render | 45,455 | 16,306 | 0 | 0 |
| Middleware перед/після controller | 45,599 / 18,278 | 40,834 / 6,298 | 0 | 0 |

Kernel wall: **89 554,621 → 2 261,009 мс**; total SQL: **7 034,800 → 1 263,900 мс**, SQL count **86 → 86**, peak PHP allocated memory **38 → 38 MiB**. Bootstrap: 225,393 → 301,033 мс, окремо від request interval. CLI без OPcache значно дорожчий за Apache baseline; ці числа служать атрибуції витрат, а не твердженню про 40× HTTP-прискорення. Коливання OS/DB warm state не усувалися.

Матеріалізація не зростає: 295 category/page models на старті; persistent metadata stage — 678 SavedGrammarTest + 788 link rows; після precedence 620 linked references; 3 259 grouped seeder/level/type statistics; 254 candidate descriptors; readiness повертає **7 aggregate rows**, тепер загалом 254 скаляри замість 508; 2 eligible course paths; 554 XML entries. Повні question bodies/answers/options/questionLinks у PHP sitemap path не гідратуються. `checkpoint_rows` у profiler позначає outputs/checkpoint counts конкретного етапу, не physical SQL reads.

### Плани SQL та перевірені гіпотези

1. **Persistent JSON metadata — підтверджено.** До: кожна гілка UNION для page читала `saved_grammar_tests` через `ALL`, estimate 678 rows. Після: той самий JSON predicate плюс `WHERE id IN (...)`; на перевіреному плані `range`, key `PRIMARY`, estimate 4 rows на гілку. Це звуження можливих ID, а не інший критерій linkage. Schema/індексів не додано.
2. **Повторні PHP scans — підтверджено.** `$saved->whereIn('id', ...)` для кожної page замінено lookup за ID зі збереженням початкового порядку. Весь набір 3 259 statistics більше не фільтрується для кожної сторінки: request-local partition за seeder включає default та locale override pools.
3. **Повторний matching predicate — підтверджено.** До 254 predicates були вставлені двічі. Тепер один `MIN(CASE WHEN matching THEN CASE WHEN usable THEN 1 ELSE 0 END END)` на candidate: NULL означає порожній pool, 0 — є непридатний match, 1 — усі matches придатні. Це еквівалент `matching_count > 0 AND matching_unusable_count = 0`.
4. Readiness SQL: CASE count **762 → 508**, bindings count **6 670 → 3 600**, SQL text bytes **202 161 → 160 255** для семи batches. Answer/option EXISTS count лишився **508**; не стверджуємо, що usability тепер обчислюється глобально один раз. Correlated lookups використовують `question_marker_option_unique` (estimate 3) та option `PRIMARY` (eq_ref, estimate 1).
5. Base `questions` scan — `ALL`, estimate 46 509 rows, бо `seeder` index відсутній. Chunk size **40 не змінювався**. Питання іншого matching pool не стають готовністю поточного тесту. Це найбільша залишкова SQL-вартість (~917 мс). Можливий окремий майбутній експеримент з index на `questions(seeder)` потребує дозволу/schema-валідації; тут не застосовувався, прискорення від нього не вимірювалося і не гарантується.
6. Course metadata/provider — 64 SQL, ~68 мс після; blueprint/definition parsing не був основним bottleneck. Course providers не переписувалися. Повне Saved metadata читання збережено для fallback/precedence; з нього не гідратуються зв'язки.

MariaDB повернула невалідний JSON для EXPLAIN великого UNION linkage-запиту (`JsonException: Syntax error`); збережені error type/bytes/hash та резервний **табличний EXPLAIN**. Плани readiness/statistics/course отримано через `EXPLAIN FORMAT=JSON`. Attached conditions, SQL bindings та навчальні тексти не зберігаються у звіті.

Перші два CLI diagnostic attempts не вважаються успішними: `m41-before-profile.json` — bootstrap без request (виправлено в інструменті); `m41-before2-profile.json` — незавершене читання JSON plan. Перший завершений профіль — `m41-before3-profile.json`, ще до алгоритмічних змін. Порожній/started artifact не використовується як baseline втрати URL. Інструмент тепер завершується nonzero та позначає incomplete при ранньому обриві.

## Зміни та доказ семантичної еквівалентності

- `SitemapController::renderSitemap()` дає opt-in CLI checkpoints; звичайний маршрут не додає debug output або metadata в XML.
- `TheoryPagePromptLinkedTestsService::possiblePrimarySitemapLinks()` — консервативний reverse index лише на час виклику. Неочевидні scalar types/Unicode зберігають SQL fallback; жодного нового persisted cache. Фінальну відповідь завжди визначає попередній `linkedTestsQueryForPage()`.
- Primary query має попередній пріоритет над normalized fallback, збережено natural-ID fallback ordering, mixed-package preference, locale overrides і slug collisions.
- Shared `GrammarTestFilterService` і runtime selection не змінювалися. Ті самі compiled scoped predicates використовуються в scalar aggregate; не створюється tests × questions JOIN.
- Додані differential checks: ID/nested ID/array/scalar/string/float linkage, slug/category/seeder/Unicode/legacy linkage; subset відносно старого SQL; overlapping seeders; повторна генерація після update/delete; tags та aggregated tags; categories/sources/difficulty/types/flags/blank count; кілька tags/answers; NULL/empty/whitespace uuid/question/marker/option.
- Старі M4 checks зберігають bounded batches, registry/session independence, locale/package precedence, whole matching pool readiness, course first entry, stable slugs і omitted lastmod. Повільний reference builder використовується лише на малих test fixtures.

SQL compatibility перевіряється захищеними SQLite suites та фактичним read-only MariaDB path. **Production MySQL не перевірявся**; ні production indexability, ні deployed SHA з цих локальних результатів не виводяться.

## Course suites: початкові failures та fixtures

Прочитано приватний `m4-initial-7428bd4c1d724382b93891725bdf6f64-result.json`. Перерваний прогін мав `F` у stdout, exit 1 та незавершений JUnit; це **не PASS** і не доказ лише проблеми швидкості. Точно відновлюється зі stack trace:

`Tests\Feature\TheoryPagePromptLinkedTestsTest::test_questions_suffix_theory_test_url_renders_the_test_page_instead_of_the_questions_api` — `Mockery NoMatchingExpectationException` для `resolveTheoryPageSlug('past-simple')` (500 замість сторінки). Це вже виправлено в M4. Інші `F` не можна надійно зіставити з IDs лише за progress dots; не вигадуємо їх.

Перший завершений M4.1 прогін із незміненими assertions, але оптимізованими private fixtures:

- `m41-course-initial-96c5361e4011467c8939c92a6b5100a0-result.json`;
- **15 tests, 356 assertions, 4 failures, exit 1**, 220,591 с, 70 MiB;
- реальний fixture build: 197,943 с, 41 V2 seeder один раз;
- усі 9 початкових LandingPage tests пройшли.

| Точний failing Blueprint test ID (namespace `Tests\Feature\PolyglotCourseBlueprintTest`) | Failure | Класифікація і виправлення |
| --- | --- | --- |
| `test_blueprint_file_loads_with_unique_lesson_orders_and_slugs` | expected `basic-grammar`, actual `mixed-revision` | Застарілий canonical category expectation; точна категорія оновлена, assertion збережено |
| `test_a2_blueprint_file_loads_with_unique_lesson_orders_and_slugs` | те саме | Те саме для A2 |
| `test_b1_blueprint_file_loads_with_unique_lesson_orders_and_slugs` | expected `planned`, actual `implemented` | Поточний blueprint уже має 16 implemented lessons; status/graph checks збережено з актуальними значеннями |
| `test_b1_blueprint_status_layer_reports_nine_implemented_lessons_and_next_planned_lesson` | expected empty `missing_lessons`, actual 7 missing records | Fixture навмисно має 9 із 16. Тепер перевіряються точні 7 missing slugs, orders 10–16 та availability `missing`; partial-course coverage не вилучено |

Доказ контракту: `database/seeders/V3/Polyglot/Course/polyglot-english-{a1,a2,b1}.json` та `PolyglotCourseBlueprintService::buildCourseStatus()`. Зміни цих JSON передують M4 (останній commit B1 blueprint — `9ac1e0dbe`). Навчальні definitions не редагувалися. Історичні test IDs не перейменовані, навіть якщо назви згадують колишню roadmap-модель.

`CourseFixtureSnapshot` готує реальні 41 V2 seeder лише один раз на **окремий PHPUnit process**. `VACUUM INTO` створює private immutable snapshot, для кожного методу робиться нова копія DB з перевіркою ownership/hash. Structural blueprint methods не запускають seeders. Cache/session та application створюються заново; export path унікальний; compiled views не видаляються. Новий LandingPage test змінює DB/cache/session, відновлює application і перевіряє незалежну DB та export directory і відсутність попереднього стану. Snapshot не переноситься між process runs і не комітиться.

Чинне `coming-soon.enabled=false` у цих двох старих integration fixtures збережено без змін; middleware/branding/Vite не вимикалися додатково. Production availability окремо покривають M4 course/SEO tests. Окремих app regressions або підстав відкривати branded courses у цих failures не встановлено.

## Фінальне автоматичне приймання

| Окремий PHPUnit process | Tests | Assertions | Exit | Час, с | Peak MiB |
| --- | ---: | ---: | ---: | ---: | ---: |
| Blueprint | 6 | 228 | 0 | 73,905 | 64 |
| LandingPage | 10 | 145 | 0 | 79,798 | 70 |
| Обидва разом | 16 | 373 | 0 | 81,529 | 68 |
| Обидва, `--order-by=reverse` | 16 | 373 | 0 | 76,310 | 68 |
| Повторний повний regression suite | 154 | 1743 | 0 | 90,928 | 86 |

Fixture preparation у цих незалежних process runs: 73,127 / 73,482 / 74,321 / 71,048 с; усередині кожного процесу 41 seeder виконується лише раз. Розкид щодо першого build (197,943 с) не подається як гарантований алгоритмічний ефект: OS/file warm state не очищався.

Початковий regression child цієї матриці: **154 tests, 1741 assertions, 1 error, exit 2**, 120,485 с, 84 MiB. Точний ID: `MainTheoryTestSitemapReadinessTest::test_partitioned_metadata_matches_slow_reference_with_tags_sources_and_answer_multiplicity`. Негативна fixture намагалася зробити однаковими marker двох answers з тим самим option_id, порушуючи unique `(question_id, marker, option_id)`. Класифікація: **fixture/schema**, не app regression. Другому answer надано інший валідний synthetic option; кількість answers, matching predicates та перевірка непридатності після очищення markers збережені. Повторний повний regression suite завершений: **154/154 PASS, 1743 assertions**, без подальших змін application source.

Runner `--matrix courses` розширено лише планом послідовних приватних child runs: окремі Blueprint/Landing, combined/reverse, плюс явний список regression files. Початкова/кінцева повна інвентаризація, byte-exact stdout/stderr, exit codes, JUnit, isolation preflight і failure propagation не послаблені. Одна failed regression дитина не перетворюється на PASS через зелені course runs.

Offline diagnostics: **38 Python tests PASS**, **34 Node tests PASS**. Нові Python checks охоплюють immutable evidence, bounded concurrency, no retry, exact URL/order equivalence (включно з однаковим count при іншому URL), course-copy navigation/canonical та course runner matrix. Невдала перша Python спроба була помилкою mock (`side_effect` перекривав `return_value`), виправлено без зміни критерія інструмента.

## HTTP/browser приймання

`m41-representative.json`: **9/9 GET PASS**, усі 200, development noindex:

- `/test/future-perfect/forms`;
- `/test/future-perfect/questions`;
- `/test/future-perfect/questions/questions` — реальний JSON, **84 questions**;
- `/test/basic-grammar/sentence-types`;
- `/courses`;
- `/courses/theory-driven`;
- `/courses/english-grammar-theory`;
- `/theory/basic-grammar/parts-of-speech`;
- `/courses/english-grammar-theory/lesson/basic-grammar/parts-of-speech`.

Урок знайдено через HTML navigation курсу, не вгадано. Курсовій копії перевірено canonical на відповідну теорію. Sitemap перевірений окремими серіями вище. Оскільки весь XML побайтно незмінний, routes/renderer не змінювалися і selector equivalence перевіряється окремо, повторний crawl усіх 264 M4 addresses не запускався.

Browser: `storage/app/seo-m4-local/m41-functional-browser.json`, Chromium **147.0.7727.15**, desktop 1440×1000 / mobile 390×844; чотири шляхи (Forms, Questions, courses catalogue, theory course), вісім нових гостей: **8/8 scenarios, 40/40 checks PASS**. Перевірено menu/toggle, основний контент, canonical, Alpine init, непорожні test UI та actual nested questions endpoint з `mode=saved-test-js-v2`, доступний перший course lesson. Production domains заблоковані до navigation; redirect/navigation outside plan заборонено. Відповіді на навчальні питання не надсилалися. 0 unexpected network failures, 0 script errors; 8 screenshots збережено приватно, desktop Questions/mobile course також переглянуто візуально.

Нові LCP/CLS/A-B/Lighthouse series не запускалися. Build не потрібен: assets inputs не змінювалися, чинні assets доступні. Google Fonts завантажувалися браузером у дозволеному виконанні; це не production HTTP.

## Відтворювані команди

PowerShell, cwd `D:/DEV/htdocs/gramlyze.loc`:

```powershell
$P = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$PHP = 'C:/Program Files/xampp/php/php.exe'
git fetch origin
git switch -c codex/seo-m4-1-sitemap-performance
& $P -B tools/diagnostics/seo-m4-1-sitemap.py --label m41-before --requests 3
& $PHP -d opcache.enable_cli=0 tools/diagnostics/seo-m4-1-profile.php --local-read-only m41-before3
# Після алгоритмічних змін:
& $PHP -d opcache.enable_cli=0 tools/diagnostics/seo-m4-1-profile.php --local-read-only m41-final
& $P -B tools/diagnostics/seo-m4-1-sitemap.py --label m41-final-get --requests 5 --compare m41-before.json
& $P -B tools/diagnostics/seo-m4-1-sitemap.py --label m41-pair --requests 2 --workers 2 --compare m41-before.json
& $P -B tools/diagnostics/seo-m4-1-sitemap.py --label m41-representative --representative
$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m4-browser.cjs m41-functional
& $P -B -m unittest discover -s tests/diagnostics -p 'test_*.py'
node --test tests/Browser/seo-m4-browser.test.cjs tests/Browser/m32-local-acceptance.test.cjs tests/Browser/state-request-classification.test.cjs tests/Browser/theory-sidebar-stability.test.cjs

& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m41-course-initial tests/Feature/PolyglotCourseBlueprintTest.php tests/Feature/PolyglotCourseLandingPageTest.php
$regressions = @(
  'tests/Feature/SitemapTest.php',
  'tests/Feature/MainTheoryTestSitemapReadinessTest.php',
  'tests/Feature/CourseSitemapMetadataTest.php',
  'tests/Feature/ResolvedLearningPageSeoTest.php',
  'tests/Feature/SeoRobotsTest.php',
  'tests/Feature/CanonicalUrlTest.php',
  'tests/Feature/SiteModeTest.php',
  'tests/Feature/TheoryCanonicalLessonUrlTest.php',
  'tests/Unit/TheoryInlineHtmlTest.php',
  'tests/Feature/TheoryInlineHtmlRenderingTest.php',
  'tests/Unit/PassiveVoiceDebugContentRepairTest.php',
  'tests/Feature/PageV3UnseedFolderCommandTest.php',
  'tests/Feature/TheoryPagePromptLinkedTestsTest.php',
  'tests/Feature/MixedTheoryPageTestRenderTest.php',
  'tests/Unit/GrammarTestFilterDeterminismTest.php',
  'tests/Unit/TheoryPageMixedInterleaveMetadataTest.php',
  'tests/Unit/ComingSoonMiddlewareTest.php',
  'tests/Feature/PolyglotCourseManifestTest.php',
  'tests/Feature/SentenceBuilderPublicBrandingTest.php',
  'tests/Feature/FuturePerfectMixedUkrainianTest.php',
  'tests/Feature/PresentPerfectContinuousMixedProductionFlowTest.php',
  'tests/Unit/SitemapProfilerSafetyTest.php'
)
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m41-final --matrix courses @regressions
# Після виправлення лише негативної fixture:
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m41-regressions @regressions
```

Існуючі evidence labels навмисно не можна використовувати повторно. Для відтворення слід обрати нові labels. Exact protected PHPUnit command/selected paths збережені в result JSON; фінальна matrix запускає regressions, Blueprint окремо, Landing окремо, обидва разом та `--order-by=reverse`, кожного разу в окремому process/runtime з JUnit.

## Protected-file proof, Git та обмеження

Початковий завершений course run: фактично **46 737 protected files**, changes **0**; aggregate before = after:

`c9dba555e036246fe1c7c31bc5653912b54d0aab7dab58aa6b22cdaeb663e386`.

Матриця `m41-final-e41380791aca4ef1b0f784143270b634-result.json` також підтвердила **46 737 files / 0 changes**, той самий before/after aggregate hash. Її загальний exit 2 збережено чесно через первинну fixture error; кожний course child має власний exit 0 і завершений JUnit.

Фінальний повторний regression runner `m41-regressions-2368287b7dd44e2bbff77fbbcf0bfdbe-result.json`: **154 tests / 1743 assertions / PHPUnit exit 0 / runner exit 0**; завершені before/after inventories — **46 737 files / 0 changes**, aggregate hash в обох той самий, наведений вище. Preflight: testing, private SQLite, array cache/session, CLI OPcache off, working `.env` не завантажений. Завершений JUnit: `storage/app/seo-m2-local/test-runtime-2368287b7dd44e2bbff77fbbcf0bfdbe/phpunit.junit.xml`. Повні inventories і JUnit зберігаються приватно, не в Git. Під час protected PHP runners HTTP/browser/profile не запускалися.

Залишкові обмеження: HTTP дещо перевищує 3 с; немає доведеного production MySQL результату; некоректний JSON EXPLAIN одного типу UNION потребував табличного fallback; курсова availability збережена, а не розширена. Не заявляється завершення GitHub CI, deployed SHA чи виправлений production. `.com` в XML/canonical — лише SEO metadata origin.

За поточними `.github/workflows/*.yml` push triggers обмежені `main`; ContentOps gate має PR/manual triggers. Push у цю робочу гілку не запускає production deploy. PR, merge, workflow dispatch і deployment у межах M4.1 заборонені й не виконуються.
