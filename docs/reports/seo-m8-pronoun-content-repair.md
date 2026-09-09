# M8 — відновлення блоків One/Ones і Reciprocal Pronouns

Виконано 10 вересня 2026 **локально**, у `D:/DEV/htdocs/gramlyze.loc`. Відновлено **9 блоків One/Ones і 10 блоків Reciprocal Pronouns**, без пересівання робочих сторінок. Із них 17 містять навчальний матеріал, 2 — джерельну навігацію. Порожніх українських блоків на цих двох сторінках після repair немає.

## База та межі

Прочитані AGENTS.md, [M7](seo-m7-editorial-descriptions.md) та [M1](seo-m1-local-fixes.md). Після `git fetch origin` фактичні початкові HEAD і `origin/codex/seo-m7-editorial-descriptions` — `fd4ad5d1eec74fb16f320bdf07649333b1ab17b5`; `origin/main` — `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`. Створено `codex/seo-m8-pronoun-content-repair` від M7, не від старого main. M1–M7 не скидались.

Початкові сторонні зміни збережені: два `storage/framework/testing/present-perfect-continuous-theory-links-audit.*`, `.codex/`, сім `storage/app/gramlyze-*.tar.gz`, каталоги попередньої приватної діагностики та Python `__pycache__`. Вони не належать до commit M8. Не використовувалися reset, clean, stash або force push.

Production .com/.ub не запитувалися й не змінювалися; .com у canonical/sitemap — тільки SEO-origin. Схема/індекси БД, `.env`, APP_KEY, Apache/php.ini/hosts, питання, відповіді, рівні, verb_hint, Page metadata, redirects/robots, доступ до курсів, прогрес, M4 SQL/sitemap, M5/M7 descriptions і дизайн не редагувалися.

## Встановлена причина

Обидва тонкі seeder-класи успадковують `JsonPageSeeder` й читають власний `definition.json`. Повний перевірений шлях:

1. `JsonPageSeeder::loadDefinitionFromFile()` читає JSON; `JsonPageDefinitionIndex::resolveContentConfig()` повертає `page` без адаптації legacy-структур.
2. `seedPageDefinition()` читає `type ?? box`, `heading ?? null`, `body ?? null`. Він не перетворює `layout/content`, `columns`, `mistakes`, `summary_points` або `exercises`.
3. Тому текст із джерела ставав `box / null / null` у `text_blocks`. `comparison-table` вже мала `type/body` і працювала.
4. Index так само бачив порожні поля. Localization manager будує цей index, а EN/PL бере з окремих localization JSON; він не відновлює український legacy-вміст.
5. `PageController` і `TheoryCourseController` обирають українські TextBlocks. `theory/show.blade.php` та `courses/partials/theory-page-content.blade.php` віддають наявний `box.body` у серверному HTML. Порожня картка — наслідок порожнього запису, не JS, SEO-description або прихованого перекладу.

До нормалізації запущено failing regression через **реальний seeder і localization manager**, але в приватній SQLite fixture: джерело містило пояснення «Замість повторення іменника…», а завантажений body був `''` при приведенні `null` до рядка. Результат: 1 test / 2 assertions / 1 expected failure. Доказ: `storage/app/seo-m2-local/m8-before-source-d0c576d4d76a40529e8cd6591c1fc821-result.json`. Захищених змін — 0. Фінальна regression окремо відтворює legacy-втрату для всіх 19 блоків.

**Звичайний seeder видаляє старі TextBlocks перед записом. На робочій БД він не запускався.**

## Зміни джерел та коду

Нормалізовано тільки два definitions:

- `database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json`;
- `database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesReciprocalPronounsTheorySeeder/definition.json`.

До кожного встановленого legacy-блока додані підтримувані `type: box`, `heading`, `body`. Оригінальні структуровані поля залишені як перевірюване джерело; regression зіставляє кожний фрагмент, а не лише непорожній body. Не додано hero intro, uuid_key, нового блока або нового питання. Позиції масиву та всі старі поля збережені точно; це додатково порівняно з JSON на M7 SHA. UUIDv5 з незмінного `seeder::uk` і порядку лишилися тими самими.

Для колонок використано наявні `grid gap-4 md:grid-cols-2`, дві HTML-секції та обидва вихідні заголовки. Масиви помилок зберігають incorrect, correct і explanation; summary — список; exercises — обидві інструкції та списки. Наявні static answers One/Ones збережені. У Reciprocal source не було готового answer key — новий не вигадувався.

Навігаційний блок One/Ones містив тільки `category_back=true/category_next=false`, не втрачений абзац уроку. Відновлено посилання назад на категорію з її джерельними slug/title. Reciprocal navigation відновлює обидва наявні title/url буквально. Чинні `navigation-chips`, subtitle та comparison-table не переписувались.

Нові файли:

- `app/Services/PronounContentRepair.php` — scoped in-place план, перевірки, backup, apply/no-op і guarded restore;
- `app/Console/Commands/RepairPronounContent.php` — default dry-run `content:repair-pronoun-blocks`;
- `tests/Feature/PronounContentRepairTest.php` — реальний імпорт, відновлення, рендер та запобіжники;
- `tools/diagnostics/seo-m8-context.php` — read-only PDO inventory/hashes;
- `tools/diagnostics/seo-m8-http.py` — bounded local GET, пошук course mapping, metadata/block evidence;
- `tools/diagnostics/seo-m8-browser.cjs` — 8 приватних browser scenarios;
- `tools/diagnostics/seo-m8-compare.py` — точний allowlisted diff джерел, БД, HTML, metadata/sitemap;
- цей звіт.

`.gitignore` доповнено лише `/storage/app/seo-m8-local/`. Shared importer/index/localization manager, renderer-и, sanitization M1, frontend/CSS/JS та історичний M7 не змінені.

## Кожний відновлений блок

Усі рядки нижче мають `locale=uk`, власний незмінний Page.seeder і старий стан `type=box, heading=null, body=null`. Порядок — `sort_order`, не нові IDs. Після — той самий `box`, джерельний heading (якщо він був) і повний HTML-вміст. O = One/Ones; R = Reciprocal Pronouns.

| Page / порядок / ID | UUID | Відновлений вміст |
|---|---|---|
| O / 1 / 8966 | `427b06c1-0936-5a88-a229-61e36dd45de9` | Займенники-замінники: вступний абзац |
| O / 2 / 8967 | `ef5ddf37-823b-5664-9d94-4f2d3a5d9f14` | Основне правило, 4 приклади, застереження про обчислюваність |
| O / 3 / 8968 | `71063781-2443-552b-8daa-657b983d9d17` | Використання з прикметниками, 4 приклади |
| O / 4 / 8969 | `98099880-67eb-54f3-a7d3-f5098d63a1b1` | Означення, 8 моделей і 2 додаткові приклади |
| O / 5 / 8970 | `115cadd5-44b6-5c2e-a717-bbc5a6d92615` | Три групи обмежень із усіма прикладами |
| O / 7 / 8972 | `12cd9500-df9d-51fb-b94d-6e02385cdb07` | 6 типових помилок і пояснення, включно з суперечливим coffee |
| O / 8 / 8973 | `cef2db8b-03f8-5c63-91e1-b787a22c7381` | Резюме: вступ і 7 пунктів |
| O / 9 / 8974 | `3e5975ed-f03b-5639-b9fe-f5f0f051f734` | 8 статичних вправ та початкові відповіді 1–8 |
| O / 10 / 8975 | `7849ff62-5f18-56d2-ae5c-b5e44b44df9c` | Навігація назад до «Займенники та вказівні слова» |
| R / 1 / 4077 | `a4b3630c-46f0-5de6-914d-459208252fc6` | Вступ про взаємну дію |
| R / 2 / 4078 | `8caccdaf-3e2e-54c6-b908-bdbe4381dff7` | Обидві колонки Each Other / One Another, пояснення та 4 приклади |
| R / 3 / 4079 | `0fb6196c-3dea-5572-8d53-b5f7e3c93a9c` | Сучасне використання, 2 приклади і рекомендація |
| R / 4 / 4080 | `bb2af966-437c-55c1-97fd-e9b6ab56e77e` | Обидві колонки взаємної/невзаємної дії, усі 7 рядків прикладів |
| R / 5 / 4081 | `d31979b0-bc8f-512d-a3e6-41741a3c9e63` | Присвійні форми, 2 моделі та 3 приклади |
| R / 7 / 4083 | `dc9f37fd-f27f-567d-9ff0-22a2673d1760` | 10 типових виразів із перекладами |
| R / 8 / 4084 | `fb489ba0-f9bf-5820-b1ff-ff997805ac4d` | 4 incorrect/correct/explanation групи |
| R / 9 / 4085 | `08395d00-d521-5fd8-bba4-56e48758a260` | Усі 5 summary points |
| R / 10 / 4086 | `44d5c5f4-df97-5ac1-aa6d-ccc490091d7a` | 2 інструкції та по 5 статичних завдань |
| R / 11 / 4087 | `639fef47-d2f1-5bc2-9705-a78c0a9db1eb` | Два вихідні посилання Reflexive / Each-Every-All |

Числа 9+10 підтверджені фактично; repair не використовує їх як ліміт або SQL-список IDs. У робочій БД загалом 16/17 блоків цих Page з усіма локалями; їхня кількість лишилася 16/17.

## Адресне локальне оновлення

Read-only PDO перевірив локальний MySQL, actual `DATABASE()=gr2`, порт 3306; `@@hostname` збігся з іменем цієї Windows-машини. Команда додатково перевірила PDO driver/connection status, відсутність read/write split і точне `--database=gr2`. Секрети не друкувалися.

Перша спроба CLI без override безпечно відмовила: локальний `.env` має `APP_ENV=production` та старий локальний APP_URL. `.env` не змінювався. Для адресної команди застосовано **лише тимчасову змінну APP_ENV=local у дочірньому процесі**, після виконання відновлено попереднє значення. Це не зміна конфігурації сайту і не дозвіл на віддалену БД.

Приватні артефакти залишені в `storage/app/seo-m8-local/`, поза public/Git:

- `m8-plan-v1.json` — повний preview старе→нове, IDs/UUID/locale/owner/order, Page/category, усі блоки обох Page та tag links, hashes джерел і значень;
- `m8-before-repair-v1.json` — exclusive backup, закритий до першого UPDATE; жоден попередній файл не перезаписано;
- `before-db.json`, `after-db.json`, `before-http.json`, `after-http.json`, `comparison.json`, browser JSON і 24 PNG.

Plan SHA-256: `d727ca9dd0d1bbd2a3368ac475e4649436110e275d2c1cc040e5755851ae7459`. Apply змінив **19** рядків у транзакції; повторний apply з тим самим preview повернув **no-op / 0**, нового backup для no-op не створював. Дані вже відновлені локально; Git не містить виконаної DB-операції.

Repair відмовляє при іншій Page/category/seeder/locale/UUID/order, непорожньому ручному body/heading, пропущеному/неоднозначному записі, stale source/preview, іншому connection/database, існуючому backup, некоректному плані. Перед UPDATE повторно читає записи з locks, порівнює повний план, оновлює лише `type/heading/body` через Query Builder без model events, перевіряє результат. IDs, UUID, timestamps, sort_order, зв’язки не змінює. Rollback/restore перевірені **тільки на ізольованих fixtures**, не на робочій БД.

## Доведені інваріанти

`seo-m8-compare.py` — PASS:

- Із 6 259 TextBlocks рівно 19 мають тільки дозволений diff; **6 240 решта записів побайтово еквівалентні** за впорядкованим row JSON hash. У цільових Page зіставлені повні старі/нові записи, не тільки body hash.
- Ще **21 навчальна/структурна таблиця** має однакові counts і SHA-256 у повному впорядкованому read-only inventory. Зокрема: 254 pages, 41 page_categories, 51 758 tag_text_block, 44 460 questions, 197 312 question_answers, 27 154 question_options, 541 151 option-pivots, 133 737 question/theory links, 17 004 verb_hints. Також незмінні saved tests/links, question hints/variants/tags, page tags і site tree.
- Обидві comparison-table, subtitle, navigation-chips та EN/PL записи незмінні. Не було delete/reinsert, schema/seed операцій або question export на робочій БД.
- На 4 target theory/course URL і 2 control lessons title, H1, description, OG/Twitter, canonical, meta robots та `X-Robots-Tag` ідентичні before. Теорія має той самий короткий H1 `One` / `Reciprocal pronouns`; курс — повні попередні назви. Development `noindex, nofollow, noarchive` збережений.
- Усі нецільові HTML-блоки мають ті самі текст, структуру тегів і hash. Для цільових body, навпаки, доведена потрібна зміна — не вимагався незмінний hash усього уроку.
- Повний ordered sitemap — **ті самі 554 loc**, без lastmod; XML SHA-256: `9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885`. URL з sitemap не обходилися.

## HTTP і браузерне приймання

Справжні course copies знайдені за посиланнями чинної `/courses/english-grammar-theory`, не вгадані. До/після виконані GET без JS, cookie, авторизації, Referer, proxy або follow-redirect. Усі шість адрес — 200 HTML:

| Сторінка | Серверний HTML після | Desktop 1440×1000 | Mobile 390×844 |
|---|---|---|---|
| [One/Ones theory](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/one-ones) | 9/9 точних body | PASS | PASS |
| [One/Ones course](http://gramlyze.loc/courses/english-grammar-theory/lesson/zaimennyky-ta-vkazivni-slova/one-ones) | 9/9 | PASS | PASS |
| [Reciprocal theory](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another) | 10/10 | PASS | PASS |
| [Reciprocal course](http://gramlyze.loc/courses/english-grammar-theory/lesson/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another) | 10/10 | PASS | PASS |

HTTP-контролі: [Narrative Tenses](http://gramlyze.loc/theory/tenses/narrative-tenses), [Conditional Alternatives and Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) — незмінні metadata і blocks.

Chromium 147.0.7727.15, **8/8 PASS**. Кожний сценарій — окремий ephemeral guest; route guard встановлено до навігації, забороняє .com/.ub та піддомени, сторонню document-навігацію/redirect. Звичайні ресурси дозволені. Для course спочатку підтверджено locked, потім через штатний `TheoryCourseProgress.createStore().markLessonCompleted()` додано тестове завершення попереднього уроку лише у власний тимчасовий localStorage. Target став current; після закриття context тестовий стан зник. Немає акаунта, server progress POST або глобального unlock.

Кожний відновлений блок прокручено у viewport: перевірені точний текст із source body, порядок, кількість li, відсутність буквальних тегів, відсутність горизонтального overflow. Обидві колонки Reciprocal розміщені поруч на desktop і послідовно на mobile. Збережені full-page та below-fold screenshots, зокрема вправи, summary і колонки; візуально оглянуто desktop course columns та mobile One/Ones practice. Списки збережені як ul/ol/li в чинному box renderer; його поточний стиль без окремого numbering/bullet оформлення не перероблявся.

У кожному сценарії середовище заблокувало Google Fonts (`fonts.googleapis.com`, `net::ERR_NETWORK_ACCESS_DENIED`), хоча інструмент дозволяв ресурс. Це **fallback-font перевірка, не повний typography parity**. Інших network failures, HTTP ≥400, pageerrors або неочікуваних console помилок немає. Використані наявні зібрані `/build/assets`, без HMR. Build не запускався: inputs Vite/Tailwind не змінені; потрібні grid-класи вже є у зібраному CSS, що перевірено фактичною геометрією колонок.

## Автоматичні перевірки

Перший після нормалізації protected batch: **107 tests / 2 717 assertions, exit 0**, 47,246 с, PHP 8.2.12 / PHPUnit 10.5.63. `m8-repair-first-2b9f94d8ca2d4975839246b365a74e98-result.json`: 46 739 protected files, 0 changes.

Фінальний batch після додавання непорожніх question-reference fixtures і перевірок CLI/connection: **109 tests / 2 726 assertions, exit 0**, 36,645 с, 66 MiB. Доказ: `storage/app/seo-m2-local/m8-final-f8f337fc17764409b1699229660b30be-result.json`; **46 739 protected files, 0 changes**, runner exit 0.

Suites: `PronounContentRepairTest`, `TheoryInlineHtmlTest`, `TheoryInlineHtmlRenderingTest`, `PageLocaleContentTest`, `TheoryEditorialDescriptionsTest`, `ResolvedLearningPageSeoTest`. Перевірено fresh/legacy loader, private localization discovery/copies, кожний вихідний фрагмент, обидва renderer-и, EN/PL, звичайний box, no-op/відсутність дублів, backup/restore, атомарну відмову після першого UPDATE, неповний re-hashed план/backup, ручні/чужі записи, source/concurrent edits. M1 escaping/XSS і M5/M7 SEO-регресії збережені. Жодного тестового seeder/export у робочі definitions.

Усі schema/seed тести — лише через незмінений protected runner, SQLite `:memory:`, окремі bootstrap/storage/views/exports, array session/cache, без робочого `.env`. Guard-вікна **не перетинались** із HTTP/browser або робочим repair. Snapshots не відновлювались після тестів. Повні MySQL/smoke/performance матриці, LCP/CLS/Lighthouse, crawl 554 URL і production SEO не запускались — поза M8.

## Редакційні зауваження — не виправлені мовчки

1. One/Ones, UUID `12cd9500-df9d-51fb-b94d-6e02385cdb07`: однакове **“I want a coffee. A hot one.”** має і ❌, і ✅; пояснення нижче трактує coffee як чашку кави. Відновлено всі позначки/пояснення буквально. Потрібне окреме редакційне рішення.
2. One/Ones, UUID `3e5975ed-f03b-5639-b9fe-f5f0f051f734`, вправа 7: “Can I have water? — Do you want a cold ___? (one / some)” та answer key `7. some` залишені за джерелом; відповідність формулювання цьому ключу потребує окремої перевірки. M8 не створює новий варіант або питання.

Це технічне відновлення, **не підтвердження методичної правильності всіх тверджень уроків**. Історичні описи M7 не розширювалися під новий видимий матеріал.

## Команди та майбутнє застосування

Фактичні основні команди, PowerShell у корені репозиторію:

```powershell
git fetch origin
git rev-parse HEAD origin/main origin/codex/seo-m7-editorial-descriptions
git status --short
git switch -c codex/seo-m8-pronoun-content-repair
$php = 'C:/Program Files/xampp/php/php.exe'
$python = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
& $python tools/diagnostics/run-isolated-tests.py --php $php --label m8-before-source tests/Feature/PronounContentRepairTest.php
& $php -d opcache.enable_cli=0 tools/diagnostics/seo-m8-context.php --local-read-only before
& $python tools/diagnostics/seo-m8-http.py --label before
# Після нормалізації; для m8-final виконано той самий список suites.
& $python tools/diagnostics/run-isolated-tests.py --php $php --label m8-repair-first tests/Feature/PronounContentRepairTest.php tests/Unit/TheoryInlineHtmlTest.php tests/Feature/TheoryInlineHtmlRenderingTest.php tests/Feature/PageLocaleContentTest.php tests/Unit/TheoryEditorialDescriptionsTest.php tests/Feature/ResolvedLearningPageSeoTest.php
# Перша команда без APP_ENV override безпечно відмовила, без запису плану/БД.
& $php -d opcache.enable_cli=0 artisan content:repair-pronoun-blocks --plan=m8-plan-v1.json
$previous = $env:APP_ENV
try {
    $env:APP_ENV = 'local'
    & $php -d opcache.enable_cli=0 artisan content:repair-pronoun-blocks --plan=m8-plan-v1.json
    & $php -d opcache.enable_cli=0 artisan content:repair-pronoun-blocks --apply --database=gr2 --plan=m8-plan-v1.json --backup=m8-before-repair-v1.json
    & $php -d opcache.enable_cli=0 artisan content:repair-pronoun-blocks --apply --database=gr2 --plan=m8-plan-v1.json --backup=m8-unused-repeat.json
} finally { $env:APP_ENV = $previous }
& $php -d opcache.enable_cli=0 tools/diagnostics/seo-m8-context.php --local-read-only after
& $python tools/diagnostics/seo-m8-http.py --label after
$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m8-browser.cjs acceptance-v1
& $python tools/diagnostics/seo-m8-compare.py
& $python tools/diagnostics/run-isolated-tests.py --php $php --label m8-final tests/Feature/PronounContentRepairTest.php tests/Unit/TheoryInlineHtmlTest.php tests/Feature/TheoryInlineHtmlRenderingTest.php tests/Feature/PageLocaleContentTest.php tests/Unit/TheoryEditorialDescriptionsTest.php tests/Feature/ResolvedLearningPageSeoTest.php
git diff --check
```

Для **іншого середовища** потрібний окремий дозвіл; жодне перенесення зараз не виконане:

1. Перенести перевірений код/definitions звичайним погодженим процесом. Не запускати ці звичайні seeders на наявній БД.
2. Окремо встановити фактичні Page/locale/UUID/order і реальне connection/database. Ця команда свідомо обмежена локальним MySQL/ізольованою SQLite; не використовувати tunnel чи удаваний host для обходу запобіжника. Інший backend/політика потребує окремого контрольованого рішення.
3. Створити **новий** preview на даних того середовища, переглянути повний diff та conflicts, зафіксувати джерельні hashes. Локальні numeric IDs, `m8-plan-v1.json` і backup **не є готовим планом для іншої БД**.
4. Створити новий приватний exclusive backup; лише після перевірки виконати адресний транзакційний repair, не seed/sync/delete/reinsert. Непорожні відмінні записи — редакційний conflict, не примусовий overwrite.
5. Повторити no-op, точний DB diff та локальні для того середовища theory/course/metadata/sitemap checks. Restore має використовувати тільки backup своєї БД й власні актуальні передумови; робочий дефект не відновлювати заради тесту.

## Публікація

Обраний пакет: два definitions, scoped service/command, regression, чотири diagnostic tools, `.gitignore` та цей звіт. Backup, SQL, приватні JSON/PNG, cookies/CSRF/sessions, `.env`, runtime caches, public/build і сторонні зміни не публікуються.

Перевірені чотири `.github/workflows/*.yml`: три smoke workflows мають push лише в main; ContentOps — PR/main або manual dispatch. Push робочої M8-гілки не запускає їх і не є деплоєм. Перед commit файли обираються явно, перевіряються staged diff та whitespace; після звичайного push звіряються remote SHA і HEAD. Фактичний commit SHA/результат push наводиться у фінальному повідомленні, без самопосилального SHA в цьому файлі.

Виправлення коду та адресне оновлення даних виконані локально. У Git публікуються відтворювані код, джерела, тести та звіт, **не локальна DB-операція**. Production .com/.ub не перевірялися й не оновлювалися; PR, merge та деплой не виконувалися.
