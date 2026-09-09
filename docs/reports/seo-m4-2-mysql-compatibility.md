# M4.2 — сумісність sitemap із MySQL

Дата: 2026-09-09. Проєкт: Gramlyze. Межі: адресна SQL-сумісність, локальне приймання, без деплою та без звернень до production.

## База й історичні докази

- Робота почалася від `codex/seo-m5-page-metadata`, SHA `3a7c26ecda1cc4ae4f727b2a378d4e19aa51cc70`, тобто з усіма змінами M5.
- Після `git fetch origin`: `origin/codex/seo-m5-page-metadata` — той самий SHA; `origin/main` — `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`.
- Створена робоча гілка `codex/seo-m4-2-mysql-compatibility`. Незакомічені PPC-аудити, `.codex/`, архіви, дампи та попередні діагностичні матеріали збережені; до цього commit не входять.
- Прочитані AGENTS.md, звіти M4, M4.1 та M5. Історичні звіти не редагувалися.

Адресно знайдений локальний доказ: `storage/app/seo-m4-1-local/ub-deploy-20260909/RESULT.md`. У ньому записані історичний deployed SHA `4fdeb75b66440a5d46cdc65fbd32b4f7001d0932`, MySQL **8.4.11**, HTTP 500 для sitemap та синтаксична помилка біля незаекранованого `option`. Повного stack trace, SQLSTATE/error code та sql_mode у цьому доказі немає. Вони не домислюються.

Поточний Production SHA не підтверджений. Історичний deployment-звіт не доводить поточний стан `.ub` або `.com`; жоден із цих серверів у M4.2 не опитувався. MySQL 9.4.0 нижче — обрана локальна тестова версія, не приписана production.

## Точний дефект і мінімальна зміна

На реальному MySQL 9.4.0 виконано поточний до виправлення Laravel builder із синтетичною fixture. Відтворено той самий SQL-дефект, що описаний в історичному звіті, але на іншій версії сервера:

| Test ID | До виправлення | Після виправлення |
| --- | --- | --- |
| `usable_builder` | SQLSTATE `42000`, error `1064` | `exists = true` |
| `sitemap_kernel` | SQLSTATE `42000`, error `1064` з реального `MIN(CASE …)` запиту | HTTP 200, очікуваний `/test/future-perfect/forms` у XML |

Непридатний фрагмент:

```sql
... exists (select * from `question_options`
  where `question_answers`.`option_id` = `question_options`.`id`
    and `option` is not null and TRIM(option) <> '')
```

Колонка в `whereNotNull()` уже квотувалася Laravel; колонка всередині `whereRaw()` — ні. На перевіреному MySQL `INFORMATION_SCHEMA.KEYWORDS` підтвердив `OPTION: RESERVED = 1`. Правила квотування також описані в [офіційній документації MySQL](https://dev.mysql.com/doc/refman/8.4/en/keywords.html).

У `app/Services/GrammarTestFilterService.php::constrainUsableQuestions()` тепер використовується:

```php
$column = $options->getQuery()->getGrammar()->wrap($options->qualifyColumn('option'));
$options->whereNotNull('option')->whereRaw("TRIM({$column}) <> ''");
```

На MySQL/MariaDB фактично виконаний фрагмент тепер такий:

```sql
TRIM(`question_options`.`option`) <> ''
```

На SQLite використовується квотування відповідною grammar. Це ідентифікатор моделі, не інтерполяція bindings чи користувацького значення.

Причиною не були `ONLY_FULL_GROUP_BY`, агрегація, scope корельованих підзапитів або кількість bindings. Старий aggregate-запит мав 9 bindings із типами `int, int, string, string, string, int, int, string, string`; SQL shape з placeholders і типи збережені приватно без значень. Агрегати, JSON predicates, порядок bindings, request-local partitions, reverse lookup і пакети по 40 не змінювалися. Жодного `ANY_VALUE`, нового GROUP BY, послаблення strict mode, fallback XML чи гідратації банку не додано.

## Ізоляція справжнього MySQL

Використано встановлений `C:/Program Files/MySQL/MySQL Server 9.4`, окремий процес із `--no-defaults --no-monitor`, новий datadir та вільний порт на `127.0.0.1`; X Protocol вимкнений. XAMPP і робоча MariaDB не зупинялися й не переналаштовувалися.

`tools/diagnostics/run-mysql-compatibility.py` вимагає явний `--opt-in-disposable-mysql` і не приймає адресу існуючої служби. Він створює унікальну БД, окремого користувача лише для цієї БД та ownership marker. Перед bootstrap-записами перевіряються фактичні `DATABASE()`, `CURRENT_USER()`, datadir, server UUID і порт. Перед кожною подальшою мутацією перевіряються також marker та `SHOW GRANTS`. Root password не передається PHP-тестам; тимчасові паролі — лише process environment/пам'ять, не argv чи `.env`.

Laravel використовує приватні storage/views/cache/session/export/bootstrap paths, array cache/session та синтетичні fixtures. Робочий `.env` не завантажується тестовим kernel. Звичайний `IsolatedTestEnvironment::assertSafeDatabase()` залишився SQLite-only; нові unit-тести доводять відмову для MySQL, відсутнього opt-in і не-owned БД навіть на loopback.

`tests/MySql` не входить до звичайних Unit/Feature suites. Opt-in класи успадковують ті самі `MainTheoryTestSitemapReadinessTest` і `CourseSitemapMetadataTest`, отже сценарії та очікування спільні з SQLite. Єдина адаптація synthetic schema: три індексовані TEXT-поля сторонньої таблиці `chatgpt_explanations` стають `VARCHAR(128)` тільки у MySQL-профілі, бо MySQL не дозволяє такий індекс на TEXT без prefix. Робоча schema і звичайна SQLite fixture не змінені.

## Матриця engines та регресії

MySQL: **9.4.0**, `MySQL Community Server - GPL`; PHP **8.2.12**, PDO client **mysqlnd 8.2.12**, Laravel **10.50.2**. Connection charset/collation — `utf8mb4 / utf8mb4_unicode_ci`; server collation — `utf8mb4_0900_ai_ci`.

Session і global SQL modes однакові, залишені стандартними:

```text
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,
ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

Історичні modes невідомі, тому окремий «production sql_mode» профіль не імітувався.

| Engine | Фактично перевірене середовище | Результат |
| --- | --- | --- |
| **MySQL PASS** | 9.4.0 Community, disposable loopback instance, synthetic fixtures, default strict modes | Builder + kernel; фінально 35 tests / 390 assertions, exit 0 |
| **MariaDB PASS** | 12.0.2-MariaDB, `mariadb.org binary distribution`, чинний локальний сайт | Read-only builder; три повні sitemap GET та чотири representative GET, exit 0 |
| **SQLite PASS** | 3.39.2, PDO SQLite, приватна in-memory БД | 111 tests / 1 254 assertions, exit 0 |
| **НЕ ПЕРЕВІРЕНО** | Історичний MySQL 8.4.11 та production | Немає звернень або заяви про PASS |

MariaDB: charset/collation connection — `utf8mb4 / utf8mb4_unicode_ci`, server collation — `utf8mb4_uca1400_ai_ci`. Фактичний Laravel session sql_mode збігається з наведеним strict-набором MySQL; global MariaDB mode — `STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION`. Це чинні різні global/session налаштування, не зміна конфігурації M4.2. PHP/Laravel/mysqlnd — ті самі 8.2.12 / 10.50.2 / 8.2.12. Для SQLite: UTF-8; sql_mode і version_comment не застосовуються, collation test schema — стандартна BINARY.

Попередній позитивний MySQL run: **35 tests / 390 assertions / exit 0**, 69,024 с. На 2/45 synthetic pages readiness робить 8/10 queries, sitemap — 10/12; пакетна поведінка M4.1 збережена. Це не timing повного dataset або production.

Фінальний MySQL run після завершення tooling: **35 tests / 390 assertions / exit 0**, **64,320 с**, 52 MiB. Structured evidence містить SQL shape і типи bindings фактично виконаних уже виправлених builder та aggregate-запитів; усі 9 типів bindings aggregate збігаються з негативним run. Shutdown exit 0; власний процес завершений.

SQLite protected run: **111 tests / 1 254 assertions / exit 0**, 52,671 с, 80 MiB. Охоплено sitemap, readiness, shared filters, linked tests, manifests, PageMetadata, resolved page SEO, canonical, robots, locale, HTML rendering і branding. M5 formatter: 2 000 викликів, 0 SQL.

Спільні синтетичні перевірки покривають:

- empty pool, all-valid pool, один invalid match і invalid поза matching pool;
- invalid за межами видимого/обмеженого префікса, NULL/empty/whitespace у UUID/question/marker/option;
- кілька answers/options/tags без множення question counts;
- difficulty, type, level, flags, category/source, tags/aggregated tags, blank count та порядок bindings;
- JSON scalar/string/numeric/Unicode predicates, перекриття seeders, зміни/видалення та chunk boundary 40/45;
- locale/seeder overrides, persistent/virtual precedence, slug і mode collisions;
- готовий, закритий, порожній курс, непридатний перший урок за наявності пізнішого придатного;
- HTML `/questions` з правильним canonical і robots та окремий JSON endpoint із technical noindex, який відсутній у sitemap.

Очікувані URL та виключення задаються fixtures/assertions незалежно від нового SQL; slow reference є додатковою перевіркою, не єдиним oracle.

## Локальний HTTP і повний sitemap

До зміни збережено три послідовні fresh-guest GET `/sitemap.xml`: **2 406 / 2 468 / 1 969 мс**, медіана **2 406 мс**. Усі HTTP 200, `application/xml; charset=UTF-8`, development `noindex, nofollow, noarchive`, **82 263 bytes**, SHA-256:

```text
9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885
```

Після зміни, три послідовні GET: **2 047 / 1 922 / 1 953 мс**, медіана **1 953 мс**, усі HTTP 200. Розмір і SHA-256 кожного XML збігаються з baseline. Повний ordered entries-масив порівняно, не лише кількість: **554 URL**, з них `/` — 1, `/theory…` — 296, `/test…` — 254, `/courses…` — 3. Усі loc та порядок однакові, **0 lastmod** до/після. Повернення до 18–27 с для sitemap не спостерігається. Це звичайні послідовні запити без XML result cache, retries або очищення OS/DB caches; різниця медіан не приписується оптимізації.

Representative HTTP перевірено окремо, після protected runs:

| Локальний path | GET / час | Результат |
| --- | --- | --- |
| `/theory/tenses/present-perfect-continuous/present-perfect-continuous-questions` | 200 / 250 мс | Metadata M5 повністю однакові, H1 і навчальний контент присутні |
| `/test/future-perfect/questions` | 200 / 2 031 мс | Metadata/H1/canonical/robots M5 однакові; реальний JSON endpoint присутній у HTML |
| `/courses/english-grammar-theory` | 200 / 15 610 мс | Metadata M5 однакові, навчальна сторінка відкрита, не Coming Soon |
| `/test/future-perfect/questions/questions` | 200 / 1 343 мс | JSON, 84 питання, technical noindex |

На `.loc` development noindex збережений. Для production-профілю robots/canonical перевірені лише ізольованим kernel, без мережевих production-запитів. Порівняння metadata включає title, description, H1, canonical, meta robots та Open Graph/Twitter; X-Robots-Tag також порівняний із M5.

Перший повільний GET курсу (15,610 с) не приховано й не замінено швидшим повтором. Це не sitemap timing; наявний повний course renderer/manifest і frontend не змінювалися. Поточного before/after performance-baseline курсу в межах M4.2 немає, тому цей одиничний замір не доводить регресію або прискорення. Додатковий browser-performance цикл не запускався.

## Докази, невдалі спроби й обмеження

Приватні матеріали збережені, не додані до Git:

- `storage/app/seo-m4-2-local/run-aad0579f2c8e4326a62c1b792c76c11f/result.json`: точний негативний builder/kernel, `--expect-defect`, exit 0 означає успішне відтворення очікуваної помилки, не MySQL PASS старого коду.
- `storage/app/seo-m4-2-local/run-158f731ef22c4e74a302496333a218eb/result.json`: перший позитивний MySQL run, 35/390, exit 0.
- `storage/app/seo-m4-2-local/run-3b2280291e0641f0ab9e1ddf3badd6ea/result.json`: фінальний MySQL run, 35/390, exit 0; виконані SQL shapes до/після можна зіставити без raw bindings.
- `storage/app/seo-m2-local/m42-regression-346c502ba8c7477987d13cfd61c8d3f5-result.json`: SQLite 111/1254, exit 0.
- `storage/app/seo-m4-1-local/m42-before.json` і три XML-файли: baseline повного локального sitemap.
- `storage/app/seo-m4-1-local/m42-after.json` і три XML-файли: повний ordered entries comparison, exit 0.
- `storage/app/seo-m4-2-local/local-engine-73a14f824eeb433d.json`: фактичні MariaDB/SQLite versions і modes, read-only builder, exit 0.
- `storage/app/seo-m4-2-local/m42-representative.json`: чотири representative GET, M5 equivalence, exit 0.

Дві початкові спроби setup не названі PASS. `run-20289852706d4c718b1bf4c99a446a5a`: exit 2, PID guard виявив Windows monitor child; перевірено точний datadir/порт дочірнього процесу і завершено тільки цей instance через mysqladmin, exit 0. Runner надалі використовує підтримуваний `--no-monitor`. `run-30f03b6b2b0d4f51a97275d2cfab5323`: exit 2 до probe через resolution DB service раніше реєстрації providers; перевірку профілю перенесено після RegisterProviders. Це помилки нового діагностичного setup, не додаткові дефекти застосунку.

У завершених protected runs: **46 737 файлів, 0 змін**; before/after SHA-256 інвентарю:

```text
94c583c4737ae144c09e2f90a3f4d05f3a3daf33a5c575aae47138384c990fc9
```

HTTP/browser/profile не запускалися всередині before→after guard-вікон. Snapshots не відновлювалися після тестів. Власні DB-процеси завершуються runner-ом; datadir і матеріали залишаються приватними доказами, не дампом робочої БД.

Не перевірено: виконання на MySQL 8.4.11, невідомі історичні modes, production SHA/HTTP/DB, повний dataset на MySQL 9.4.0. Це не висновок про поточну працездатність production. Повний 554-page crawl, браузерний performance, LCP/CLS і редакційні описи 43 сторінок не повторювалися.

## Відтворення

Запускати послідовно; під час protected runners не відкривати `.loc` у браузері та не запускати HTTP/profile:

```powershell
$P = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$PHP = 'C:/Program Files/xampp/php/php.exe'
& $P -B tools/diagnostics/run-mysql-compatibility.py --opt-in-disposable-mysql --mysql-home 'C:/Program Files/MySQL/MySQL Server 9.4' --php $PHP
# --expect-defect застосовувався тільки ДО runtime-правки.
$suite = @(
  'tests/Unit/MySqlCompatibilityIsolationTest.php', 'tests/Unit/GrammarTestFilterDeterminismTest.php',
  'tests/Unit/SitemapProfilerSafetyTest.php', 'tests/Feature/MainTheoryTestSitemapReadinessTest.php',
  'tests/Feature/CourseSitemapMetadataTest.php', 'tests/Feature/TheoryPagePromptLinkedTestsTest.php',
  'tests/Feature/PolyglotCourseManifestTest.php', 'tests/Unit/PageMetadataTest.php',
  'tests/Feature/ResolvedLearningPageSeoTest.php', 'tests/Feature/SeoRobotsTest.php',
  'tests/Feature/CanonicalUrlTest.php', 'tests/Feature/TheoryCanonicalLessonUrlTest.php',
  'tests/Feature/TheoryInlineHtmlRenderingTest.php', 'tests/Feature/PageLocaleContentTest.php',
  'tests/Feature/SitemapTest.php', 'tests/Feature/SentenceBuilderPublicBrandingTest.php'
)
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m42-repeat @suite
# Тільки після завершення protected runs; labels мають бути новими:
& $PHP -d opcache.enable_cli=0 tools/diagnostics/m42-local-engine.php --local-read-only
& $P -B tools/diagnostics/seo-m4-1-sitemap.py --label m42-repeat-after --requests 3 --compare m42-before.json
& $P -B tools/diagnostics/m42-local-http.py --label m42-repeat-html
```

Для HTTP equivalence використовуються збережені приватні M5 `m5-accepted.json` і `m5-courses-final.json`; без цих файлів скрипт не підміняє baseline припущеннями. Вони не потрібні для відтворення MySQL-приймання. PHP/MySQL binary paths можна замінити на відповідні встановлені Windows runtimes. Docker не використовувався: локальний Docker Engine був недоступний; наявного native MySQL достатньо.

## Git та межі публікації

Перед push перевірено доступні `.github/workflows`: push-тригери smoke тільки для `main`; ContentOps — PR/manual, без автоматичного деплою цієї робочої гілки. Активних локальних Git hooks немає, application scheduler не запускає деплой. Deployment routes потребують окремої команди; вони не викликалися. Зовнішні deployment-системи та приховані server-side hooks не опитувалися згідно з межами завдання.

У commit включаються тільки адресна source-правка, тестовий профіль/runner, діагностичні інструменти та цей очищений звіт. Без PR, merge, workflow dispatch, push у main і без деплою.
