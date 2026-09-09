# M6 — старі адреси, редиректи та внутрішні посилання

## Межі та база

Перевірка 9 вересня 2026 року, репозиторій `D:/DEV/htdocs/gramlyze.loc`, робоча гілка `codex/seo-m6-redirects-links`.

- Фактична стартова гілка: `codex/seo-m4-2-mysql-compatibility`.
- HEAD і відповідна remote-гілка після `git fetch origin`: `63002109cd075610818926e2429f65345a4f918a`.
- `origin/main` після fetch: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`, не використаний як база замість новішої роботи.
- M5 `3a7c26ecda1cc4ae4f727b2a378d4e19aa51cc70` є предком HEAD; успадковані M1–M4.2/M5 збережені. Прочитані AGENTS.md та всі чотири звіти, зазначені в завданні.
- Стартові сторонні зміни PPC-аудитів, `.codex/`, архіви, backups, приватні попередні аудити та `__pycache__` не скидалися й не включаються в commit.

Усі реальні HTTP/браузерні звернення — тільки `http://gramlyze.loc`. Production-профіль — окремі локальні Laravel kernel-тести із синтетичним `https://seo.production.test`, SQLite `:memory:`, array session/cache, приватними views/exports. Це **не перевірка production .com/.ub**. `.com` у canonical/sitemap залишається SEO-origin, не адресою мережевих звернень.

## Реалізовано

1. `routes/web.php`: дві наявні catalog aliases використовують `localized_route` замість `redirect()->route`. Однакові route names реєструються для UK/EN/PL; звичайний `route()` вибирав останню польську реєстрацію незалежно від мови відвідувача. Тепер destination відповідає поточній дозволеній локалі. Статус **302 збережено**: production-каталог закритий, тому підстав для постійного перенесення немає.
2. Там само збережено query із функціональними `levels[]`, `tags[]` та іншими параметрами. Раніше aliases втрачали всі фільтри. Довільний `next`, `redirect`, `url`, `return` або Referer не вибирає destination.
3. `PolyglotCourseController::show`: перевірка порожнього manifest виконується **до** завантаження blueprint. Невідомий `/courses/polyglot-english-a9` раніше повертав 500 через відсутній blueprint, тепер — справжній 404 без Location. Існуючі курси не змінені.

Не додавалися глобальні redirect middleware, HTTP-перевірки цілей у runtime, нові запити до банку питань, нові alias, нові SEO-origin або SQL. Квотування `option` із M4.2 не змінене. Coming Soon, мовна конфігурація, .env, серверні конфіги, навчальна БД та контент не редагувалися.

## Матриця: local HTTP

Позначення: paths у таблиці відкривалися тільки на `.loc`; `C(path)` — canonical `https://gramlyze.com{path}`. Числа після стрілок — HTTP-статуси. Для кожного ланцюга був новий гість; між його кроками тільки власний CookieJar у пам'яті. Кінцеву адресу додатково відкрито іншим гостем без cookies/Referer/admin/source. Для `source=theory` окремим контролем є чиста кінцева адреса.

| Вхідний path | До | Після | Кінцевий canonical | Призначення / джерело mapping |
| --- | --- | --- | --- | --- |
| `/catalog-tests/cards` | 302 → `/pl/catalog/tests-cards` → 200 | 302 → `/catalog/tests-cards` → 200 | `C(/catalog/tests-cards)` | Явний legacy route у `routes/web.php`; неправильна мова виправлена |
| `/tests/cards` | 302 → `/pl/catalog/tests-cards` → 200 | 302 → `/catalog/tests-cards` → 200 | `C(/catalog/tests-cards)` | Та сама сім'я |
| `/en/catalog-tests/cards` | 302 → `/pl/catalog/tests-cards` → 200 | 302 → `/en/catalog/tests-cards` → 200 | `C(/catalog/tests-cards)` | EN зберігається, canonical за чинною мовною політикою |
| `/pl/tests/cards` | 302 → `/pl/catalog/tests-cards` → 200 | Без зміни | `C(/catalog/tests-cards)` | Уже правильна локаль |
| `/tests/cards?levels[0]=A1&levels[1]=B2&tags[0]=…&tags[1]=…&mode=manual&launch=lesson&next=…` | 302, усі query втрачені, PL → 200 | 302, UK + всі значення query → 200 | Без query: `C(/catalog/tests-cards)` | Вхід був реально percent-encoded; нижче наведений контракт |
| `/catalog/tests-cards` | 200 | 200 без Location | `C(/catalog/tests-cards)` | Чиста ціль, development bypass |
| `/courses/polyglot-english-a1` | 200 | 200 без Location | `C(/courses/polyglot-english-a1)` | Legacy home; `SentenceBuilderBranding::canonicalCourseSlug`, manifest service; постійний redirect відкладено |
| `/courses/sentence-builder-english-a1` | 200 | 200 без Location | `C(/courses/sentence-builder-english-a1)` | Чинна home-адреса, незалежний гість |
| `/en/courses/polyglot-english-a1`, `/pl/courses/polyglot-english-a1` | 200 | 200 | `C(/courses/polyglot-english-a1)` | Локальні мови доступні; не вводиться новий мовний redirect |
| `/test/polyglot-to-be-a1/step/compose` | 200 | 200 без Location | `C(/test/polyglot-to-be-a1)` | Робочий старий режим проходження; branding lesson mapping |
| `/test/sentence-builder-to-be-a1/step/compose` | 404 | 404 без Location | Немає | Нова branded-ціль закрита гостю; перенесення сюди не додається |
| `/courses/polyglot-english-a9` | 500 | 404 без Location | Немає | Невідомий course slug, не новий alias |
| `/theory/future-simple/future-simple-forms` | 404 | 404 без Location | Немає | `TheoryCanonicalLessonUrlTest` явно вимагає відсутності короткого неналежного hierarchy path |
| `/theory/maibutni-formy/future-simple/future-simple-forms` | 200 | 200 | `C` цього повного path | Строгий `PageController::resolveCategoryBySlugPath`; навігація вже містить повний шлях |
| `/en/theory/future-simple`, `/pl/theory/future-simple` | 200 | 200 без Location | `C(/theory/future-simple)` | Дозволені локальні мови; контроль збереження теми |
| `/test/future-perfect/questions` | 200 HTML | 200 HTML | `C(/test/future-perfect/questions)` | Не плутається з технічним JSON маршрутом |
| `/test/future-perfect/questions?source=theory` | 302 → чистий Questions → 200 | Без зміни | `C(/test/future-perfect/questions)` | Тимчасове очищення launch/query, не SEO-перенесення |
| `/test/future-perfect/questions/step` | 200 | 200 без Location | `C(/test/future-perfect/questions)` | UI-режим збережений |
| `/test/future-perfect/questions/questions` | 200 JSON, 84 питання | Так само | Не HTML / немає canonical | Справжній endpoint питань |
| `/theory/future-perfect`, `/courses`, `/courses/english-grammar-theory` | 200 | 200 без Location | `C` відповідного path | Чисті контрольні сторінки |
| `/courses/english-grammar-theory/lesson/academic-english/argumentation-and-academic-tone` | 200 | 200 без Location | `C(/theory/academic-english/argumentation-and-academic-tone)` | Реальний урок, знайдений у course navigation; не redirect на теорію |
| `/m6-definitely-unknown-page` | 404 | 404 без Location | Немає | Не маскується головною сторінкою |

24 основні рядки capture + 2 додаткові випадки, не crawl усіх сторінок. У кожному capture збережені час, status, точний Location, final URL, Content-Type, X-Robots-Tag, title/description/H1/canonical/robots і очищені navigation paths. HTML та cookie values не зберігалися. На `.loc` залишився development `noindex, nofollow, noarchive`.

Приватні матеріали: `storage/app/seo-m6-local/{before-http,after-http,before-extra,after-extra}.json`. Початковий `before.json` містить лише заголовок незапущеного capture: імпортований helper затінив ім'я стандартного `http.cookiejar`; виправлено до першого запиту цього запуску. Реальна baseline — `before-http.json`; записи не перезаписувалися. Інструмент не виконує retries, зупиняє зовнішній Location/цикл та має межу 5 кроків.

## Матриця: isolated local production-profile kernel

Реальні router/controller/middleware, синтетичні fixtures, не виклики .com/.ub. Production gate увімкнений із поточними префіксами та `production_locales=['uk']`. У початковому red-прогоні для aliases виміряно неправильний перший Location; зайвий наступний PL fallback встановлено з чинного `EnforceSiteModeLocale`. Після правки весь production-profile ланцюг перевіряє окремий kernel regression.

| Сценарій | До / причина | Очікуваний і перевірюваний контракт M6 |
| --- | --- | --- |
| UK catalog alias | 302 на `/pl/catalog/tests-cards`; наступний мовний fallback зайвий | 302 одразу на `/catalog/tests-cards`, фінал 404; незалежний гість також 404 |
| EN/PL catalog alias | Мовний fallback + зайвий PL з named route | 302 на той самий alias без префікса → 302 на UK catalog → 404; немає повернення на PL |
| Чистий catalog | Закритий production gate | 404 із noindex; `.loc` 200 не підміняє цей результат |
| `/en/…`, `/pl/…` courses | Тимчасовий мовний fallback | 302 на той самий course path без префікса → 200; без нового рішення про закриття мов |
| Обидві A1 course home адреси | Mapping є, home доступні, але branded entry закритий | 200; новий home має queryless `.com` canonical і не отримує випадковий noindex |
| Old compose lesson / branded compose lesson | 200 / 404 | Та сама доступність; SEO-redirect не обходить gate |
| Невідомий або порожній курс | Невідомий blueprint давав 500 | 404; не redirect до схожого курсу/головної |
| Questions HTML / JSON | Контракт M1/M4/M5 | HTML `/questions` 200, повний canonical, без noindex; `Accept: application/json` на parent — 404; справжній nested questions endpoint — 200 JSON із технічним noindex |
| `source=theory`, `/step`, course lesson | Launch/UI-переходи, не aliases | Тимчасовий 302 для query cleanup; step і course lesson — 200, власний інтерфейс збережений |
| State/progress та non-GET | Не SEO-навігація | Реальні state/progress routes не перенаправляються; GET-only catalog/home rules не перехоплюють POST/PUT/PATCH/DELETE |

### Свідомо не змінено / відкладено

- **Catalog 302 не перетворений на 301:** production target закритий. Виправлені тільки мова та втрата query.
- **Course/test branding aliases:** обидві A1 home-сторінки доступні, але перше branded compose-завдання повертає 404 навіть чистому локальному гостю; production-profile відтворює цей gate. Без рішення про доступність курсу не створено нове постійне перенесення його старих робочих URL. Дублювання home під двома canonical лишається відкладеним випадком, а не заявленим виправленням.
- **EN/PL fallback 302:** тимчасовий; topic/course path та query зберігаються. `production_locales`, переклади, hreflang не змінені.
- **Короткий theory path:** наявний regression contract навмисно повертає 404 для неправильного hierarchy path. Нове правило за схожістю назви не створене.
- **Step/manual/input, source cleanup, course lesson:** не прирівнювалися до SEO-дублів. Уже чинний 301 legacy localized admin переходу збережений і перевірений на регресію.

## Query, безпека та внутрішні посилання

Змінена тільки сім'я двох catalog aliases. Політика: destination походить із фіксованого named route + дозволеної локалі; зберігаються всі query-значення, без інтерпретації їх як URL призначення. Symfony нормалізує порядок ключів та percent encoding; байтовий порядок query не є контрактом. Масиви і порядок їхніх значень збережені. Перевірені Unicode `тема з пробілом`, пробіли, `50% + /`, `déjà`, `tags[]`, `levels[]`, вкладені `filters[level][]`, `mode`, `launch`. `next=//attacker.invalid` лишається значенням query, а не origin. Підміна через зовнішній Referer також не змінює ціль. Canonical не містить query.

Пошук у `resources/views`, routes і публічних helpers/services та links із обмеженої HTTP-матриці не знайшов посилань на два старі catalog aliases або старі branded course/lesson URLs у перевірених публічних navigation/breadcrumb/card/related/course сценаріях. Джерела вже використовують `localized_route` і `SentenceBuilderBranding`: `layouts/catalog-public`, `layouts/engram`, `home`, catalog views, related/auto-generated cards, test-mode nav, course show, `CourseCatalogService`, `PolyglotCourseManifestService`.

Тому штучних template-правок або глобального replace немає. Legacy slug у progress payload — ідентифікатор стану, не застаріле навігаційне посилання; він збережений. Історичні docs, admin генератор промптів і seeded identifiers не переписувалися. Серед посилань навчального контенту, реально відрендерених перевіреними сторінками, цільових legacy aliases не виявлено. Це не повний аудит посилань усіх text_blocks; БД не переписувалася.

Fragment перевіряється лише браузером: існуючий `#polyglot-course-lessons`, не серверною інтерпретацією `#`.

## Незмінність sitemap і метаданих

- Повний **ordered** набір: 554 URL до/після; ті самі loc/lastmod, aliases не додані.
- XML: 82 263 байти до/після, SHA-256 `9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885`.
- Приватні XML/JSON: `storage/app/seo-m4-1-local/m6-before*`, `m6-after*`; один реальний GET до і один після, без cache clear/retry. Це контроль цілісності, не performance campaign.
- Title/description/H1/canonical/robots/OG/Twitter у незмінених поточних контрольних сторінок ті самі до/після. Серед 24 основних captures відрізняються тільки три помилкові мовні destinations catalog aliases, які виправлено.
- Додатково точне порівняння з прийнятим M5 inventory (`m5-accepted.json` + `m5-courses-final.json`) для п'яти спільних поточних URL: повний Future Simple forms theory path, Future Perfect Questions, Future Perfect category, `/courses`, grammar theory course — усі метадані однакові.
- SQL не змінено; повторний MySQL/MariaDB compatibility cycle не запускався. Build не потрібний: Blade/JS/CSS/Vite inputs не змінені.

## Автоматичне та браузерне приймання

Фінальний PHP-прогін: **137 tests, 1 361 assertions, PASS**, з них нові M6 регресії — **11 tests / 260 assertions**. PHP 8.2.12 / PHPUnit 10.5.63, 63.145 s власне PHPUnit. Evidence: `storage/app/seo-m2-local/m6-release-final-af6edb18b2b341c197224d304a00cac2-result.json`. Фінальний file guard: **46 739 фактичних захищених файлів, 0 змін**, runner exit 0. HTTP/браузерні запуски не перетинали його before→after вікно.

Перший контрактний прогін (`m6-red-e7582643557448c7b6e70b8cbd677f92-result.json`) — 9 tests, 44 assertions, 8 failures. Він відтворив неправильну мову/втрату query, 500 невідомого курсу; також відхилено початкові очікування 301 course home після перевірки закритого entry та виправлено неправильний URI progress у новому тесті. File guard: **46 739 фактичних файлів, 0 змін**. Жоден snapshot не відновлювався після тестів.

Новий `LegacyPublicRedirectTest` використовує справжній kernel, private fixtures та явне очищення тільки власної array-session між незалежними гостями. Старі branding, locale, sitemap, SEO, Questions та state регресії запускаються разом із ним.

Перший спільний прогін `m6-release-41d86641c92c4dc5b75f3c6943ccc9ea-result.json`: 137 tests, 1 321 assertions, 2 failures, guard 46 739 / 0 змін. Обидві помилки — нові мовні сценарії з неповною фікстурою: без `languages` helper брав default із `app.locale`, який змінює `setLocale`. На живій локальній версії активні UK/EN/PL і стабільний default UK вже підтверджені HTTP/браузером. До **приватної SQLite-фікстури** додано ці три language records, скидання static language cache між тестами та початкової locale між незалежними гостями. Робочу БД і `LocaleService` не редагували. Поведінка під час відсутності таблиці мов — окремий DB-outage сценарій; він не видається за повністю відремонтований M6 випадок.

Diagnostic self-tests: Python **4/4 PASS** (local origin guard, одна cookie jar усередині chain / нова для final, зовнішній Location, loop/limit без retry); Node browser guard **1/1 PASS** (блокування всіх nonlocal URL, planned navigation та ресурсів).

Браузер: **10/10 PASS**, Chromium `147.0.7727.15`, viewport desktop `1440×1000`, mobile `390×844`, окрема гостьова context для кожного сценарію; фінальний запуск 19:18:51–19:19:36 UTC. Перевірені UK catalog alias, EN alias → реальний PL switcher URL, чинна A1 course home з кліком `#polyglot-course-lessons`, Questions (84 initial/rendered/API questions, 0 answered), grammar theory course → реальний hero link `/courses/english-grammar-theory/lesson/basic-grammar/parts-of-speech`. Course lesson не редиректить на теорію, але має правильний theory canonical. Меню відкривається/закривається на mobile, посилання theory/courses доступні.

До навігації встановлено context route guard для всіх nonlocal requests та додатковий Chromium Network block для production-доменів, включно з server redirects. 14 запитів Google Fonts CSS **навмисно заблоковані** локальною політикою; відповідні console resource errors не видаються за помилки застосунку. Несподіваних network failures, JS errors або HTTP 4xx/5xx у браузерних сценаріях — 0. Це smoke з fallback fonts, не production typography/performance-приймання. Збережено 10 viewport PNG; вибірково візуально перевірені catalog desktop, Questions mobile і course lesson mobile.

Перший браузерний запуск `m6-acceptance-browser.json` завершився 2/10 PASS через помилку діагностичного скрипта: виклик неекспортованого helper `menuEvidence`. Помилка інструмента виправлена локально; новий запуск із новим label `m6-browser-final-browser.json` пройшов повністю. Початковий результат збережено, не приховано retry; код застосунку через це не змінювався. Evidence та screenshots залишаються в ignored `storage/app/seo-m6-local/`.

## Команди відтворення

```powershell
$P = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$PHP = 'C:/Program Files/xampp/php/php.exe'
git fetch origin
git rev-parse HEAD origin/main origin/codex/seo-m4-2-mysql-compatibility
git merge-base --is-ancestor 3a7c26ecda1cc4ae4f727b2a378d4e19aa51cc70 HEAD
git switch -c codex/seo-m6-redirects-links

# Labels у фактичних запусках унікальні; не перезаписуйте існуючі evidence.
& $P tools/diagnostics/seo-m4-1-sitemap.py --label m6-before --requests 1
& $P tools/diagnostics/seo-m6-redirects.py --label before-http
& $P tools/diagnostics/seo-m6-redirects.py --label before-extra --extra
# Після runtime-правок, поза PHP guard:
& $P tools/diagnostics/seo-m6-redirects.py --label after-http --compare before-http.json
& $P tools/diagnostics/seo-m6-redirects.py --label after-extra --extra --compare before-extra.json
& $P tools/diagnostics/seo-m4-1-sitemap.py --label m6-after --requests 1 --compare m6-before.json
& $P -m unittest discover -s tests/diagnostics -p test_seo_m6_redirects.py
node --test tests/Browser/seo-m6-browser.test.cjs

$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m6-browser.cjs m6-browser-final

# Тільки після закриття всіх HTTP/browser запусків:
$suite = @(
  'tests/Feature/LegacyPublicRedirectTest.php',
  'tests/Feature/SiteModeTest.php',
  'tests/Feature/AdminLocaleRoutingTest.php',
  'tests/Feature/SentenceBuilderPublicBrandingTest.php',
  'tests/Feature/TheoryCanonicalLessonUrlTest.php',
  'tests/Feature/PageLocaleContentTest.php',
  'tests/Feature/CanonicalUrlTest.php',
  'tests/Feature/SeoRobotsTest.php',
  'tests/Feature/ResolvedLearningPageSeoTest.php',
  'tests/Feature/MainTheoryTestSitemapReadinessTest.php',
  'tests/Feature/CourseSitemapMetadataTest.php',
  'tests/Feature/SitemapTest.php',
  'tests/Feature/SavedTestJsStateTest.php',
  'tests/Unit/PageMetadataTest.php',
  'tests/Unit/LocaleDatabaseFallbackTest.php',
  'tests/Unit/ComingSoonMiddlewareTest.php',
  'tests/Unit/SavedTestJsStateTest.php',
  'tests/Unit/SavedTestJsStateSynonymsTest.php'
)
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m6-release-final @suite
```

## Обмеження і публікація

Production SHA не встановлювався: production не запитувався. HTTP→HTTPS, www/без www, TLS, reverse-proxy правила, реальні production cookies, Search Console, індексація та зовнішні deployment integrations не перевірені. Серверні конфіги не змінювалися; приріст позицій чи негайна індексація не обіцяються. Нові LCP/CLS, Lighthouse, A/B, full crawl 554 сторінок і оптимізація повільного course GET не виконувалися.

Перед push перевірені всі чотири `.github/workflows`: три smoke workflows мають push тільки на `main`, ContentOps — pull_request/main та manual dispatch; звичайний push M6-гілки не запускає ці workflows. Немає активних local git hooks або `core.hooksPath`. Не виконуються PR, merge, workflow dispatch, push у main або deploy. Історичні звіти не редагуються. Фактичні commit/remote SHA наводяться у фінальній відповіді після перевірки remote refs.
