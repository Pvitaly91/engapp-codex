# M4 — sitemap готових навчальних сторінок

## Межі та версія

Робота виконується в Gramlyze, від фактичного HEAD
`7938eebb3356dc6747cd4ed19e88ffb3bc16787d` (завершений M3.2).
Після `git fetch origin` remote попередньої робочої гілки має той самий SHA;
`origin/main` — `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`.
Нова робоча гілка: `codex/seo-m4-indexable-sitemap`.
Прочитані AGENTS.md та звіти M1, M2, M3, M3.1 і M3.2.

HTTP/браузерне приймання — тільки `http://gramlyze.loc`. Адреси
`https://gramlyze.com` у XML/canonical — метадані, не ціль мережевих запитів.
Production-профіль перевіряється локальним Laravel kernel зі штучним Host
`seo.production.test` у приватному тестовому runtime.
Production SHA не встановлюється і production не перевіряється цим етапом.

Не змінюються .env, APP_KEY, серверні налаштування, навчальна БД, контент,
UUID, рівні, контракти прогресу чи робочі snapshots. Початкові незавершені
PPC-аудити, .codex, архіви та приватна діагностика залишаються поза commit.
Немає production HTTP/SSH/DB/API, деплою, PR/merge, Search Console,
очищення робочих кешів, міграцій або сідерів робочої БД.

## Початковий sitemap

До application edits виконано справжній анонімний GET
`http://gramlyze.loc/sitemap.xml`, без Cookie/Authorization/Referer,
без проксі, автоматичних редиректів і повторів.
Час: 2026-09-08 20:56:08–20:56:09 UTC.
HTTP 200, `application/xml; charset=UTF-8`, development noindex очікуваний.

| Група | До M4 |
|---|---:|
| Головна | 1 |
| Каталог теорії | 1 |
| Категорії теорії | 41 |
| Сторінки теорії | 254 |
| Разом | 297 |

60 859 bytes, 969 ms wall time; SHA-256 XML:
`c2e96a45f484dca8a62cf7ec8eb66eaea1abc38a1e3c6efe956caf02899adb2d`.
Незмінний baseline з кожним loc/lastmod збережений приватно як
`storage/app/seo-m4-local/m4-before.json`.
297 — виміряний початковий стан, не задана ціль чи критерій успіху.

## Політика включення та реалізація

Зберігається наявний `/sitemap.xml`, Blade XML-шаблон і robots.txt.
Origin береться з `site-mode.production_origin`; Host, cookie, Referer,
source/filter query не утворюють адрес. Категорія залишається за leaf slug,
урок теорії — за повним шляхом категорій. URL кодуються й XML-екрануються,
дублікати loc усуваються, порядок детермінований.

Основні mixed/all-level тести відбираються через спільні правила
`TheoryPagePromptLinkedTestsService`: постійний зв'язок SavedGrammarTest
з Page, ті самі seeder/locale override та mixed-filter правила, точний
roundtrip `TheoryPageTestSlug`/cold resolver. Не додаються всі saved tests,
рівневі пари, альтернативні режими чи session/query-only virtual tests.
Повний банк питань або questionLinks не гідратується для sitemap:
використовуються metadata projection, груповані кількості та пакетні агрегати
з реальними фільтрами й наявністю непорожньої відповіді. Кандидат також
відхиляється за будь-якого непридатного питання в matching pool: одна
придатна відповідь поза лімітом вибірки не доводить готовності обраного банку.
Для нових тестів перевіряється весь UK/theory ланцюжок предків, без orphan,
циклів або неоднозначного roundtrip. Старі theory URL від цього не змінюються.

Курси відбираються з фактичного CourseCatalogService, а не вигаданих slug:
канонічні branded destinations, наявний валідний blueprint, справжній
доступний перший навчальний елемент. Theory-course перевіряє першу сторінку
чинного дерева, українські підтримувані TextBlocks; Page.text, сам заголовок
або порожня JSON-структура не доводять наявності матеріалу.
Повний cache-writing manifest та рендеринг карток не викликаються з sitemap.

Відомі консервативні виключення: definition-only/UUID-only тести без
постійного linked metadata для основного mixed slug; конфлікти з persisted
тестом того самого slug; неканонічні/неоднозначні slug; закриті або порожні
курси. Це не твердження, що кожне виключення є помилкою контенту.
Course lesson copies, які canonical веде до теорії, не додаються окремо.
Технічні endpoints, state/progress/JSON, mode/filter URL, EN/PL варіанти
не включаються. Навчальний HTML зі slug `/questions` не відсікається за суфіксом.

### lastmod

Для всіх URL optional `lastmod` опущений. Page/PageCategory.updated_at не
торкається при редагуванні/видаленні TextBlock. Test dates також не покривають
question/pivot updates, видалення, blueprints, опис/порядок курсу й переклади.
Максимальна дочірня дата не є deletion-aware revision. Немає now(), часу
deploy/build, filemtime або глобальної дати БД. Схема й робочі updated_at
не змінюються. Ізольовані regression fixtures контролюють час і перевіряють
стабільне omitted lastmod при додаванні, редагуванні й видаленні контенту.

## Приймання

### Автоматичні сценарії

Зелений PHPUnit до живого SQL-приймання, `m4-verified`:
**147 tests, 1 658 assertions, PASS**,
97.598 s, 84 MB. Це M4 sitemap/readiness, повторний M1 SEO-набір та
пов'язані filter/manifest/mixed/render/branding сценарії.

- Порожні array cache/session → прямий GET і порожні → sitemap → GET:
  4 підтеми Future Perfect та 4 Present Perfect Continuous, обидва порядки.
- HTML `/test/future-perfect/questions` зберігає canonical і indexability
  у синтетичному production-профілі; вкладений JSON endpoint лишається
  технічним noindex. Parent-slug collision перевірений окремо.
- XML після middleware: namespace/UTF-8/encoding, без BOM/debug/scripts,
  старі вкладені theory paths, unique loc, однаковий порядок за іншої сесії.
- Порожні/закриті/cache-only/неоднозначні кандидати не включаються;
  відбір не пише до registry/session/cache і не гідратує questionLinks.
- Курси: canonical branding, фактичний перший елемент, реальний HTML
  catalog/theory-driven home, закритий branded entry без обходу gate,
  course copies, непридатні links/answers/blueprints, зміни й видалення блоків.
- Offline diagnostics: **Node 34/34**, **Python 14/14**, обидва exit 0
  (додаткові fail-fast та screenshot-before-network regression тести).
  Перевірено приватність evidence, pre-navigation production guard,
  ліміт паралельності та збереження першої помилки.

### Вимірювання відбору після пакетної SQL-зміни

Це контрольовані SQLite fixtures у локальному production-kernel, не
production benchmark і не повторний M3.2 performance-цикл.

| Розмір | SQL нового MAIN metadata | Час metadata | SQL повного kernel sitemap* | Час sitemap* |
|---|---:|---:|---:|---:|
| 2 готові сторінки | 8 | 12.974 ms | 10 | 13.727 ms |
| 45 готових сторінок | 10 | 247.694 ms | 12 | 382.343 ms |

\* Для ізоляції тестової групи course provider повертає порожній набір.
Course entry metadata окремо має **3 SQL** і за 1, і за 42 saved lessons.
Кількість SQL росте за пакетами (MAIN по 40, course IDs по 250), не на кожну
сторінку/питання. Усередині SQL вартість EXISTS усе одно залежить від даних.
Новий кеш генератора не додавався. Час живого GET наведений нижче окремо.

### Перший живий GET — збережена невдала перевірка

`m4-after-initial.json`, 2026-09-08 21:49:26–21:51:47 UTC, exit 1:
перший `/sitemap.xml` — **TimeoutError після 45 032 ms**, без отриманого
HTTP-статусу/тіла. Два вже заплановані незалежні контексти також перевищили
45 секунд. Сім старих representative URL повернули 200. Browser не запускався.
Ці докази не видалені й не перезаписані повторним успішним результатом.
`removed: 297` у першому сирому артефакті не є втратою URL: capture порожній
через timeout, тому порівняння недоступне. Інструмент після цього отримав
fail-fast guard і явний `comparison_available: false` для failed capture,
перш ніж запускати наступну фазу; додана offline regression перевірка.

Read-only `information_schema.PROCESSLIST` локальної MariaDB 12.0.2 показав активні
приблизно 49 KB UNION запити readiness. `SHOW INDEX` підтвердив: у questions
є PRIMARY(id) та unique(uuid), але немає seeder/level/difficulty індексу.
Оцінка таблиці — близько 46 тисяч питань; `question_answers` — 197 312 рядків
із question_id-leading unique індексом, option має PK. Повторювані
EXISTS/NOT EXISTS сканування на кожен кандидат виявилися неприйнятними
на фактичній локальній БД, попри малу кількість SQL у SQLite fixtures.
Жодних індексів, schema-міграцій чи даних для цього не змінювали.

Виправлення обмежене sitemap: один aggregate SELECT на chunk до 40 тестів
замість EXISTS/NOT EXISTS повного pool на кожен кандидат. SUM/CASE повертає
лише matching/unusable counts; WHERE та bindings компілюються з тих самих
Laravel builders, що використовує звичайний filter path. Перевірка
відповідей виконується всередині matching CASE. Readiness не послаблена,
банки не гідратуються, кеш не додається. Наступний захищений прогін та
повторне локальне приймання мають окремі labels нижче.

PHP після цієї зміни, `m4-aggregate`: **148 tests,
1 668 assertions, PASS**, 107.488 s, 84 MB. Додаткова перевірка зіставляє
агрегати з попередніми EXISTS semantics для difficulty/type/category/flags,
blank count, several answers і порожніх marker/option/question. SQL-shape
регресія вимагає один aggregate scan на chunk, без UNION readiness.

### Живий sitemap після оптимізації (тільки .loc)

`m4-after-aggregate.json`: усі три captures — HTTP **200**, валідний
`application/xml`, **554 loc**, 82 263 bytes. Початковий успішний GET:
2026-09-08 22:00:40–22:01:07 UTC. Fresh repeat і sitemap після звичайного
відвідування `/theory` мають **ідентичні bytes**, порядок і loc/lastmod.
SHA-256: `9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885`.

| Група | До | Після | Додано |
|---|---:|---:|---:|
| Головна | 1 | 1 | 0 |
| Каталог теорії | 1 | 1 | 0 |
| Категорії теорії | 41 | 41 | 0 |
| Сторінки теорії | 254 | 254 | 0 |
| Основні mixed/all-level тести теорії | 0 | 254 | 254 |
| Каталог курсів | 0 | 1 | 1 |
| Головні сторінки курсів | 0 | 2 | 2 |
| **Разом** | **297** | **554** | **257** |

Збережено **297/297**, втрачено **0** старих loc. З усіх 554 URL lastmod
опущено; у 295 старих прибрано недостовірну дату, у 2 її й раніше не було.
82 KB/554 URL не потребують sitemap-index або поділу.

Включені контрольні сторінки (production origin тут лише метадані;
перевірялися відповідні шляхи на `http://gramlyze.loc`):

- [/test/future-perfect/forms](https://gramlyze.com/test/future-perfect/forms)
- [/test/future-perfect/negatives](https://gramlyze.com/test/future-perfect/negatives)
- [/test/future-perfect/questions](https://gramlyze.com/test/future-perfect/questions)
- [/test/future-perfect/time-expressions](https://gramlyze.com/test/future-perfect/time-expressions)
- [/test/basic-grammar/sentence-types](https://gramlyze.com/test/basic-grammar/sentence-types)
  та інші теми: **250** доданих test URL поза Future Perfect.
- [/courses](https://gramlyze.com/courses)
- [/courses/english-grammar-theory](https://gramlyze.com/courses/english-grammar-theory)
- [/courses/theory-driven](https://gramlyze.com/courses/theory-driven)

Виключені приклади:

| Кандидат | Чому не додається |
|---|---|
| `/test/future-perfect/questions/questions` | Технічний endpoint даних, не HTML урок; M1 noindex збережений |
| `/test/future-perfect/forms/step`, `?source=theory`, `?filters=...` | UI/query варіанти, не основна адреса |
| `/courses/polyglot-english-a1` | Старий alias, не canonical branding destination |
| `/courses/sentence-builder-english-a1` … `-c2` | У поточному guest gate branded перший test entry закритий; metadata не обходить його |
| `/courses/english-grammar-theory/lesson/...` | Курсові копії з canonical на теорію; окремі loc не створюються |
| cache-only або definition-only без persistent linked metadata | Не доведений незалежний cold MAIN у межах цього пакета |

Причина branded-course виключення підтверджена кодом і контрольованим
cold fixture, не production-запитом. Відсутність цих адрес у sitemap
не додає їм нового noindex і не є автоматично діагнозом помилки контенту.

Час живої генерації: **27.313 s**, fresh repeat **19.797 s**, ordinary
session **18.516 s**. Це все ще помітно повільніше початкових 0.969 s,
тому затримка залишається явним обмеженням. Успішний GET не означає хорошу
швидкість або покращення Core Web Vitals. Подальша оптимізація фактичної
MariaDB/metadata-політики не маскується кешем і не розширюється до нового
performance-циклу в M4.

Повний HTTP-обхід завершений **exit 0, PASS 264/264**: кожен із **257 нових
URL** і **7 старих representative URL** перевірено окремим анонімним GET
із `Accept: text/html`, без Cookie/Authorization/Referer, без redirect/retry,
паралельність максимум 2. Усі — 200 HTML з точним canonical,
очікуваним development noindex, H1 і навчальним вмістом; у тестах є
непорожні питання, у курсах — навчальні посилання. Звичайна session
використовувалася тільки для окремого sitemap comparison, не для crawl.
Це не production HTTP-перевірка й не перевірка семантики кожного питання.

### Browser: перша спроба в обмеженому network runtime

`m4-browser-initial-browser.json`, Chromium headless 147.0.7727.15,
2026-09-08 22:07:56–22:08:45 UTC, **exit 1, не PASS**.
У 8 окремих гостьових contexts (desktop 1440×1000 / mobile 390×844)
пройшли всі **32** перевірки документа, canonical/контенту, меню й
питань/курсу. Але всі 8 strict network checks не пройшли через єдиний
спільний зовнішній ресурс Google Fonts CSS:
`net::ERR_NETWORK_ACCESS_DENIED`. Код помилки підтверджений точним
SHA-зіставленням з очищеною діагностикою; немає app HTTP 4xx/5xx,
production blocks або подвійного Alpine runtime. Це мережеве обмеження
першого запуску, не підтверджений дефект sitemap чи блокування Googlebot.

Перший артефакт збережений без змін. Скриншоти в ньому не створилися,
оскільки strict assertion стояв раніше. Інструмент перенесено на збереження
скриншота перед останньою assertion (сама перевірка не послаблюється),
з додатковим offline regression тестом. Наступний запуск дозволяє звичайні
вбудовані публічні font resources через execution permission; захист
`.com`/`.ub` і redirect guard залишаються ввімкненими.

Останнє peer review також додало metadata-only guard: raw page slug першого
уроку курсу має точно дорівнювати нормалізованому slug. Інакше чинний
lesson resolver може отримати 404 через case/whitespace; такий курс тепер
відсікається без зміни самого renderer чи робочих даних. Регресія має
valid-before/after controls та обидва некоректні варіанти.
Фінальний повний цільовий PHPUnit `m4-final`: **149 tests,
1 672 assertions, PASS**, 110.375 s, 84 MB.

Після закриття цього guard виконано один фінальний GET sitemap,
`m4-final-sitemap.json`: **200, 554 URL, 82 263 bytes, 21.797 s**.
Порядок loc/lastmod і SHA XML побайтово однакові з `m4-after-aggregate`.
Цей metadata-only guard не змінив жодної адреси фактичного набору;
257 нових HTML URL не обходилися повторно, бо їхні маршрути/рендерери
незмінні та повний попередній crawl уже успішний.

### Фінальний browser — PASS

`m4-browser-network-browser.json`, той самий Chromium 147.0.7727.15,
2026-09-08 22:15:33–22:16:25 UTC,
desktop 1440×1000 / mobile 390×844: **exit 0, 8/8 сценаріїв,
40/40 перевірок, 8 скриншотів**. Дозвіл на нормальні зовнішні font resources
усунув `ERR_NETWORK_ACCESS_DENIED`; app/config/font source не змінювалися.
Попередній невдалий evidence залишається окремо.

| Сторінка на .loc | Desktop | Mobile | Підтверджений навчальний вміст |
|---|---|---|---|
| `/test/future-perfect/questions` | PASS | PASS | 84 initial/rendered/cards/API questions, answered 0 |
| `/test/future-perfect/forms` | PASS | PASS | 84 initial/rendered/cards/API questions, answered 0 |
| `/courses` | PASS | PASS | 8 course links, доступний canonical theory-course link |
| `/courses/english-grammar-theory` | PASS | PASS | 254 lessons/cards/links, доступний перший CTA |

Немає неочікуваних network failures, HTTP 4xx/5xx або script errors;
один Alpine runtime, коректне mobile menu відкривається/закривається,
горизонтальний overflow відсутній. Власні гостьові state POST 204/ERR_ABORTED
класифікуються окремо й не приховують інші помилки. Відповіді на питання
не вводилися. Actual questions endpoints перевірені окремими JSON GET,
зокрема змістовне подвійне `/questions/questions` для Questions.

Переглянуто всі 8 збережених viewport screenshots: заголовки, меню,
картки й лічильники відображаються коректно на desktop/mobile. Вони мають
префікс `storage/app/seo-m4-local/m4-browser-network-` та суфікси
`{questions,forms,courses,theory-course}-{desktop,mobile}.png`.
Скриншоти приватні, у Git не додаються. Це функціональне приймання,
не новий дизайн-аудит або вимірювання LCP/CLS.

### Ізоляція PHP

Чинний M3.1 runner `tools/diagnostics/run-isolated-tests.py` не змінюється.
Він виконує preflight і PHPUnit лише після повного before-inventory, з
SQLite `:memory:`, array cache/session, приватними storage, exports, views,
bootstrap caches, filesystem disks і PHP startup override
`opcache.enable_cli=0`. Робочий .env не завантажується. Після PHP знову
перевіряються ті самі захищені шляхи; побічні ефекти не «відновлюються».
Під час guard-вікна немає паралельного браузерного чи HTTP-прогону.

Окремий початковий preflight успішний: 46 735 файлів, 0 змін;
`m4-preflight-f3da2bf70a404627bcdf84d9f3bf0602-result.json` у приватному
`storage/app/seo-m2-local/`. Тести схеми/контенту використовують тільки
контрольовані ізольовані fixtures, не робочу БД.

Початковий широкий `m4-initial` мав план 158 cases, але **не завершений і
не PASS**. Після приблизно 20 хвилин зупинено тільки перевірений власний
PHPUnit child; Python-runner завершив after-inventory. Причина обмеження:
старі PolyglotCourseBlueprintTest і PolyglotCourseLandingPageTest запускають
по 41 V2 seeder перед кожним методом; для M4 їх повторний повний контентний
прогін виявився надмірним. Частковий stdout із невдалими assertions та stderr
збережений, не переписаний сприятливішим результатом. Фінального JUnit цього
перерваного процесу немає. Перевірки M4/SEO/manifest/branding запускаються
окремим завершуваним набором нижче, а повні два legacy suites не заявляються
перевіреними.

Доказ `m4-initial-7428bd4c1d724382b93891725bdf6f64-result.json`,
21:14:52–21:35:05 UTC 2026-09-08, child/runner exit 1;
46 735 файлів, 0 змін. Before/after aggregate SHA однаковий:
`4fdd2df2c3e5d448f008b8523a1bef9400e3759edd5d299d84e6b27628a51efb`.

Перший завершений `m4-focused`: 147 tests, 1 632 assertions, 7 failures,
63.532 s PHPUnit, 84 MB. Артефакт
`m4-focused-24ee00557a3545c6bb034a56b0988f12-result.json`,
2026-09-08 21:39:22–21:40:51 UTC. Захищені 46 735 файлів: 0 змін,
той самий aggregate SHA. Це збережений невдалий прогін, не фінальний PASS.
Він виявив некоректний legacy-string fixture нового тесту та застарілі
очікування старих тестів: probe батьківського slug у middleware,
виключення старого seeder за наявності all-level package,
query-cleanup redirect перед HTML. Application behavior заради цих
fixtures не змінювався; перевірки контенту й точних фільтрів збережені.

Runner `m4-verified` також завершився **exit 0**: 46 735 захищених файлів,
**0 змін**, той самий before/after aggregate SHA. Повний результат і
JUnit: `m4-verified-a8d6e2660cd742b9a1e835692e1156f0-result.json` та
відповідний private runtime у `storage/app/seo-m2-local/`.

Після SQL-зміни, `m4-aggregate-5e6523ddecd6456bbf7829d3b3aa37d5-result.json`:
runner **exit 0**, 46 735 захищених файлів, **0 змін**. Перевірка після PHP
завершена до дозволу будь-яких наступних HTTP/browser запитів.

Після фінального raw-slug guard:
`m4-final-b88889058be543b3922e9594f0fa642c-result.json`,
2026-09-08 22:11:08–22:13:47 UTC, **exit 0**, **46 737 файлів, 0 змін**.
Два додаткові runtime views з'явилися під час дозволених звичайних HTTP
запитів між guard-вікнами й включені до нового before/after inventory.
Фінальний aggregate SHA обох inventories:
`c9dba555e036246fe1c7c31bc5653912b54d0aab7dab58aa6b22cdaeb663e386`.

## Пов'язані файли

- `app/Http/Controllers/SitemapController.php` — розширення існуючого urlset,
  спільний production origin, dedup, обґрунтоване omitted lastmod.
- `app/Services/TheoryPagePromptLinkedTestsService.php`,
  `app/Services/GrammarTestFilterService.php` — read-only MAIN metadata та
  спільні filter predicates, без зміни selection/progress контракту.
- `app/Services/CourseSitemapMetadataService.php`,
  `app/Services/CourseCatalogService.php`,
  `app/Services/PolyglotCourseManifestService.php`,
  `app/Services/TheoryCourseManifestService.php` — канонічні destinations і
  bounded readiness фактичного першого елемента.
- `tests/Feature/SitemapTest.php`, `MainTheoryTestSitemapReadinessTest.php`,
  `CourseSitemapMetadataTest.php` у тому самому каталозі — M4 acceptance.
- `tests/Feature/TheoryPagePromptLinkedTestsTest.php`,
  `tests/Feature/MixedTheoryPageTestRenderTest.php` — виправлення застарілих
  fixtures/очікувань зі збереженням змістовних regression assertions.
- `tools/diagnostics/seo-m4-sitemap.py`, `seo-m4-browser.cjs` у тому самому
  каталозі; `tests/diagnostics/test_seo_m4_sitemap.py`,
  `tests/Browser/seo-m4-browser.test.cjs` — багаторазове local-only приймання.
- `.gitignore` — тільки `/storage/app/seo-m4-local/`; цей звіт.

Routes, robots middleware, Coming Soon configuration, ResolvedHtmlTest,
SavedTestResolver, XML Blade template та frontend build inputs не змінені.

## Межі приймання та публікації

- Production `.com`/`.ub`, SSH/API/БД серверів і Search Console не
  перевірялися й не змінювалися. Production SHA не встановлено.
- Немає Lighthouse, польових CWV або нового M3.2 A/B. Build не запускався:
  його inputs не змінені, browser використовує наявні зібрані ресурси.
- Два повні legacy course suites з масовими V2 fixtures не завершені
  у першому перерваному прогоні; вони не видаються за green full CI.
  Натомість готовність course catalog/home/entry перевірена малими
  контрольованими fixtures, спільним manifest suite і живим browser.
- Повний crawl старих 297 URL не повторювався: їх побудову збережено,
  повний XML loc diff і 7 релевантних старих GET виконані.
- Не виконувався новий аудит усіх навчальних питань або timestamps;
  metadata policy свідомо консервативна. Затримка sitemap 18–27 s лишається
  виміряним обмеженням, а не прихованим «PASS швидкодії».
- Ціль публікації — тільки `origin/codex/seo-m4-indexable-sitemap`.
  Перед push перевірено versioned `.github/workflows`: три smoke workflows
  мають push тільки в `main`, ContentOps gate — PR у main/manual dispatch.
  Push робочої гілки не має такого trigger; сторонні webhooks не інспектувалися.
- До commit явно обираються тільки файли M4 та цей звіт. Перевіряються
  staged diff, `git diff --cached --check` і secret patterns. `.env`, runtime,
  SQL/backups, .codex, початкові PPC-аудити та приватні evidence виключені.
  PR, merge, main push, workflow dispatch і deploy не виконуються.

Немає твердження про індексацію URL у Google. Жоден sitemap не надсилався
до зовнішніх систем.

### Команди

Нижче локальні команди від кореня репозиторію. Labels не перезаписують
попередні докази. Жодна HTTP-команда не звертається до production.

```powershell
git fetch origin
git switch -c codex/seo-m4-indexable-sitemap

$P = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$PHP = 'C:/Program Files/xampp/php/php.exe'
& $P -B tools/diagnostics/seo-m4-sitemap.py --baseline --label m4-before
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m4-preflight --preflight-only

$suite = @(
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
  'tests/Feature/PolyglotCourseBlueprintTest.php',
  'tests/Feature/PolyglotCourseLandingPageTest.php',
  'tests/Feature/SentenceBuilderPublicBrandingTest.php',
  'tests/Feature/FuturePerfectMixedUkrainianTest.php',
  'tests/Feature/PresentPerfectContinuousMixedProductionFlowTest.php'
)
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m4-initial @suite

$focusedSuite = $suite | Where-Object { $_ -notin @(
  'tests/Feature/PolyglotCourseBlueprintTest.php',
  'tests/Feature/PolyglotCourseLandingPageTest.php'
) }
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m4-focused @focusedSuite
# Після виправлення fixtures, без зміни application behavior:
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m4-verified @focusedSuite
# Після пакетної SQL-оптимізації та додаткової regression перевірки:
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m4-aggregate @focusedSuite
# Остання metadata-only перевірка точного raw page_slug курсу:
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m4-final @focusedSuite

node --test tests/Browser/seo-m4-browser.test.cjs tests/Browser/m32-local-acceptance.test.cjs tests/Browser/state-request-classification.test.cjs tests/Browser/theory-sidebar-stability.test.cjs
& $P -B -m unittest discover -s tests/diagnostics -p test_seo_m4_sitemap.py -v

# Тільки після закриття PHP/hash guard; перший timeout не перезаписується:
& $P -B tools/diagnostics/seo-m4-sitemap.py --compare m4-before.json --label m4-after-initial --workers 2
& $P -B tools/diagnostics/seo-m4-sitemap.py --compare m4-before.json --label m4-after-aggregate --workers 2
& $P -B tools/diagnostics/seo-m4-sitemap.py --baseline --label m4-final-sitemap

$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m4-browser.cjs m4-browser-initial
# Окремий дозволений network execution, незмінний production guard:
node tools/diagnostics/seo-m4-browser.cjs m4-browser-network
```

Нові багаторазові HTTP/browser інструменти зберігають тільки очищені
метадані/лічильники/хеші, не повний HTML, відповіді, cookie чи CSRF.
Browser screenshots — приватні локальні артефакти, не додаток до Git.
Production-запити й document redirects блокуються до навігації.
