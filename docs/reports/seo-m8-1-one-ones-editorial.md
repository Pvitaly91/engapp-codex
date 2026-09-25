# M8.1 — редакційна точність One/Ones

## Межі та база

Тільки український урок `/theory/zaimennyky-ta-vkazivni-slova/one-ones` і його справжня курсова копія. Це не аудит усієї граматики й не редагування окремих тестових банків.

База: `be84d48e3071d6988c381f8020c89672e84938c4` (M8). Після `git fetch origin`: `origin/codex/seo-m8-pronoun-content-repair` збігається з базою, `origin/main` — `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`. Робоча гілка `codex/seo-m8-1-one-ones-editorial` створена від M8, не від старого main.

Сторонні зміни PPC-аудитів, `.codex/`, архіви та приватні діагностичні каталоги залишаються поза пакетом. Історичні звіти та backups M8/M7 не змінюються.

## Редакційний план, складений до зміни definition і БД

Джерела:

- [Cambridge, One](https://dictionary.cambridge.org/grammar/british-grammar/one): прочитаний повний текст статті, повернутий пошуковим інструментом, з розділами про присвійні означники та означення перед ones. Пряме відкриття повернуло `403 Forbidden`; це обмеження доступу, а не відсутність правила. Прямі сторінки `some`, `nouns-countable` і `substitution` також недоступні. Для доступної незалежної перевірки основних конструкцій використані наступні джерела.
- [British Council, one and ones](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/one-ones): основний текст про однину/множину й означення; відповідь Peter M., LearnEnglish Team, 04.03.2023 про допустимість this як самостійного займенника та this one. Прочитано фактичну сторінку, не лише заголовок.
- [Cambridge Dictionary blog, countable and uncountable](https://dictionaryblog.cambridge.org/2018/04/04/i-love-coffee-would-you-like-a-coffee-words-that-can-be-countable-and-uncountable/): Liz Walter, 04.04.2018; напій як речовина проти окремої порції. Прочитано фактичний текст.

| Старий фрагмент | Підтверджена проблема | Мінімальне редакційне рішення | Блоки / джерело |
|---|---|---|---|
| Однакове замовлення coffee позначене і ❌, і ✅ | Взаємовиключні оцінки одного контексту | Залишити допустиме замовлення порції; окремо показати необчислювану каву | №7; Cambridge blog |
| Категоричні формулювання про water; неприродне `a cold some` у вправі №7 | Речовина змішана з неозвученою одиницею; ключ не утворює речення | Water у глечику → some; one лише для прямо названої bottle/glass | №2 (лише застереження), №5, №7, №8, №9; Cambridge One/blog |
| Заборона my/your + one; ❌ your one; вправа №5 | Стиль подано як граматичну заборону; обидва варіанти можливі, а відмова від тієї самої книги нелогічна | Розрізнити нейтральні mine/yours і розмовні my one/your one; у вправі №5 незалежний контекст позичання та пара your/yours | №5, №7, №8, №9; Cambridge One |
| Абсолютна заборона після some/any/both/either/neither | Суперечить власному some wooden ones | Розрізнити some/any без ones і some/any + означення + ones; прибрати непотрібне узагальнення either/neither | №5, №7, №8; Cambridge One, British Council |
| Вправи №1–2: the blue ___ / bigger ___ | Контекст не задає кількості бажаних сумок/яблук; обидва варіанти можливі | №1: a blue ___; №2: two bigger ___ | №9; редакційне застосування правила числа British Council |
| Вправа №8: this one / this | Обидва варіанти граматично допустимі | Зберегти речення з is, змінити лише альтернативу на these ones | №9; British Council, відповідь команди |

Отже, дозволені рівно п'ять блоків: №2, №5, №7, №8, №9; тільки `content.html` і `body` у definition, тільки `body` у БД. №2 включено лише тому, що його категоричне застереження потребує уточнення значення іменника. Таблиця comparison-table, subtitle, навігація, заголовки й оформлення не змінюються. Вправи №3, №4 та №6 залишаються дослівно незмінними.

## Остаточні редакційні рішення

- №2: уточнення «іменник у необчислюваному значенні» замість безумовної заборони для назви речовини. Це узгодження з прикладами напою/порції, не новий розділ.
- №5 і №7: нейтральні `mine/yours` не змішуються з присвійними означниками `my/your`. Розмовні `my one/your one` більше не позначені помилкою. Збережена справжня помилка `The ones mine are broken`; пояснення тепер стосується саме цієї побудови, не всіх присвійних форм.
- №7: замість подвійної оцінки старого coffee — допустиме замовлення `I'd like a coffee, please. A hot one.` та окремий контекст кави в кавнику із `some`. Власні приклади, не велика цитата довідника.
- №5, №7 і №9: вода в глечику замінюється `some`. Для `a cold one` прямо названо `a bottle of water`; приховане припущення про пляшку не потрібне.
- №5, №7 і №8: розрізнено самостійні `some/any` і конструкції з означенням `some wooden ones / any smaller ones`. Непотрібна абсолютна заборона для either/neither вилучена, а не поширена на нові випадки.
- №8 узгоджений із правилами. Таблиця, subtitle, навігація, всі заголовки та решта блоків не змінені.

Описані факти підтверджують довідники вище. Добір коротких контекстів, усунення двозначності й конкретні українські формулювання — редакційне рішення цього пакета. Автотести фіксують прийнятий текст, але самі по собі не доводять граматичну правильність.

### Перевірка всіх восьми статичних вправ

Кожен варіант прочитаний у повному реченні. У таблиці наведені обидві отримані побудови; неправильний рядок не є рекомендованим прикладом.

| № | Правильне повне речення | Результат з іншою опцією і причина відхилення | Що змінено |
|---|---|---|---|
| 1 | I don't like this bag. Can I see a blue **one**? | I don't like this bag. Can I see a blue **ones**? — a вимагає однини | the → a: старий контекст допускав і кілька синіх сумок |
| 2 | These apples are too small. Do you have two bigger **ones**? | These apples are too small. Do you have two bigger **one**? — two вимагає множини | Додано two: попередній контекст не виключав бажання взяти одне яблуко |
| 3 | Which car is yours? — The red **one**. | Which car is yours? — The red **ones**. — множина не відповідає питанню про одну машину | Дослівно без змін |
| 4 | I need a pen. — Here's a black **one**. | I need a pen. — Here's a black **ones**. — a/is не узгоджуються з множиною | Дослівно без змін |
| 5 | My book is damaged. Can I borrow **yours**? | My book is damaged. Can I borrow **your**? — присвійний означник your не стоїть тут самостійно | Новий короткий контекст; your/yours замість двох допустимих your one/yours |
| 6 | I need some chairs. — We have some wooden **ones**. | I need some chairs. — We have some wooden **one**. — у відповіді про потрібні стільці використана множинна група some wooden ones | Дослівно без змін |
| 7 | There is some water in the jug. Would you like **some**? | There is some water in the jug. Would you like **one**? — неназвана обчислювана одиниця; потрібно замінити воду як речовину | Усунуто конструкцію a cold ___, яка з ключем some давала неправильне речення |
| 8 | I like both dresses, but **this one** is more expensive. | I like both dresses, but **these ones** is more expensive. — these ones потребує are | Лише друга опція this → these ones; this саме по собі не оголошене помилкою |

Ключ: **1 one; 2 ones; 3 one; 4 one; 5 yours; 6 ones; 7 some; 8 this one**. Кількість — рівно 8. Змінені №1, №2, №5, №7, №8 (п'ять вправ), незмінені №3, №4, №6. Послідовність відповідей у ключі залишилася тією самою, але тепер узгоджується з однозначними формулюваннями.

## Реалізація

Canonical definition: `database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json`. У п'яти блоках синхронно змінені `content.html` та `body` (10 JSON-значень), без перестановки масиву, uuid_key або нових блоків.

`database/content-patches/one-ones-m8-1.json` — окремий versioned маніфест переходу з повними п'ятьма прийнятими старими й новими body. Це не DB-дамп і не універсальний редактор; IDs робочої БД в ньому немає. Канонічна нова редакція залишається у definition. Маніфест звіряється з нею.

`OneOnesEditorialPatch` фіксує точні semantic SHA-256 всього старого й нового definition. Історичний стан реконструюється лише заміною п'яти пар body/content.html, тож маніфест не може мовчки легалізувати інший старий текст. Preview містить також raw hashes definition/маніфесту, фактичне connection, повні Page/category/блоки локалей і зв'язки. У робочих записах перевіряються Page.seeder, locale, UUID, порядок, тип, heading, column, css_class і level. Точна нова редакція дає no-op; будь-який невідомий body, порожній запис, відсутній/неоднозначний target — conflict.

Команда `content:patch-one-ones-m8-1` за замовчуванням тільки створює новий preview. Apply в транзакції повторно читає locked snapshot, вимагає незмінного плану, закриває exclusive backup до першого UPDATE і змінює лише body. Postcondition перевіряє весь цільовий snapshot. IDs, UUID, timestamps, порядок та зв'язки не переписуються; seeder/model events не запускаються. Існуючі файли не перезаписуються. Віддалені connection не підтримуються.

`PronounContentRepair` та його попередні тести не змінені. Його guard і далі відмовляє на непорожньому старому тексті: нова редакція не застосовується під виглядом ремонту порожнього body. Legacy → M8 repair з актуальним source → правильний M8.1 перевіряється лише в ізольованих fixtures.

## Виконання й приймання

### Проміжні перевірки та виправлення тестової оснастки

Початковий exec-сеанс `77363` став недоступним (`Unknown process id`) без фінального result; залишився лише baseline у `test-runtime-5851a239f88d42bf990b3bda408118ef`. Активних php/python процесів при перевірці не було. Ця спроба не зарахована як PASS і не підмінена вигаданим результатом.

Наступний завершений protected run зупинився до виконання тестів: приватний helper `patch()` конфліктував із Laravel TestCase. Helper-и перейменовані в `editorial()` та `importEdition()`, щоб не затіняти framework API. Доказ `m8-1-editorial-retry-9896e77fe58141558205c4584e9cdf53-result.json`: exit 255 у PHPUnit, **46 739 protected files / 0 changes**.

Наступний run: 126 tests / 2 995 assertions / 1 failure, 71,387 с. Виявив помилковий селектор EN-fixture: EN TextBlocks належать тій самій Page, але мають окремий localization-seeder. Виправлено відбір за Page, додано обов'язковий nonzero affected-count, окремий сценарій manual body після preview та helper відмови, який не може спіймати власний `self::fail`. Доказ `m8-1-editorial-verified-98e1cc959a6b474faa1a280bc980bcdd-result.json`: **46 739 files / 0 changes**. M8 production-код і його старі assertions не послаблювалися.

Кінцевий PHPUnit: **126 tests / 3 008 assertions, exit 0**, PHP 8.2.12 / PHPUnit 10.5.63, 36,162 с, 68 MiB. Suites: `OneOnesEditorialPatchTest`, `PronounContentRepairTest`, `TheoryInlineHtmlTest`, `TheoryInlineHtmlRenderingTest`, `PageLocaleContentTest`, `TheoryEditorialDescriptionsTest`, `ResolvedLearningPageSeoTest`.

Окремі діагностичні регресії: **4 Python tests + 1 Node test — PASS**. Вони перевіряють exact source diff від M8, відмову comparator на зміні metadata/question bank/контрольного уроку/іншого поля, розбір списків/сирих тегів, local-only GET та браузерний guard. Немає мережевих запитів у цих unit-тестах.

Усі schema/seed тести виконуються незміненим protected runner: SQLite `:memory:`, приватні database/localization copies, storage/views/bootstrap caches/exports, array cache/session; робочий `.env` не завантажується. CLI OPcache вимкнений тільки для дерева тестового процесу. HTTP/browser і робочий patch не запускаються протягом before/after guard. Жодних відновлень protected-файлів після тесту.

Фінальний доказ: `storage/app/seo-m2-local/m8-1-editorial-final-d526d28b291644f49677e95851931e04-result.json`, **runner exit 0, 46 739 protected files, 0 changes**. Fingerprint до/після: `00cc38fe5731920c4cef2b8d1a35b5e11a705e08519dbe862e88b756ce8efe04`. Локальний preview/apply почався тільки після завершення guard.

### Preview → backup → apply → no-op

Фактично перевірено MySQL `gr2`, `@@hostname=DESKTOP-3C05HGF`, порт 3306; hostname збігається з локальною Windows-машиною. Додатково діє перевірка PDO driver/connection status і точного `--database=gr2`, не тільки конфігураційного host. Робочий `.env` має APP_ENV=production: для дозволеної локальної команди тимчасово передано APP_ENV=local у процесі PowerShell з відновленням попереднього значення у `finally`; сам файл і конфігурація сайту не змінені.

Preview: **5 змін**, `storage/app/seo-m8-1-local/m8-1-plan-v1.json`, внутрішній SHA-256 `1b6e81f8ac0e50aac91f06291fcf4d790b719120d109bf2ffb6980d15c37d1cc`. Повні старі body звірені з окремим before-DB inventory та прийнятим маніфестом, нові — з approved source; кожний diff має тільки поле body.

| Порядок / ID | UUID | Поле |
|---|---|---|
| 2 / 8967 | `ef5ddf37-823b-5664-9d94-4f2d3a5d9f14` | body |
| 5 / 8970 | `115cadd5-44b6-5c2e-a717-bbc5a6d92615` | body |
| 7 / 8972 | `12cd9500-df9d-51fb-b94d-6e02385cdb07` | body |
| 8 / 8973 | `cef2db8b-03f8-5c63-91e1-b787a22c7381` | body |
| 9 / 8974 | `3e5975ed-f03b-5639-b9fe-f5f0f051f734` | body |

Усі — `locale=uk`, Page.seeder `Database\Seeders\Page_V3\PronounsDemonstratives\PronounsDemonstrativesOneOnesTheorySeeder`. IDs наведені лише як локальний доказ; команда не використовує переносний список ID.

Новий backup **збережений**: `storage/app/seo-m8-1-local/m8-1-before-editorial-v1.json`, 59 167 bytes. Preview і backup мають той самий файловий SHA-256 `4aed0d44129309794497b93d18e5f3cdedfd67d8a4b06051ce0eaa2b05fddc20`. Apply завершився `applied / updated=5`; повтор із тим самим preview — `no-op / updated=0`, файл `m8-1-unused-repeat.json` не створений. Старі `seo-m8-local/m8-plan-v1.json` і `m8-before-repair-v1.json` збережені без перезапису. Старий план не робили валідним для нового source hash.

Конфлікти, конкурентні правки після preview, існуючий backup, неповний re-hashed план, зміна source/маніфесту та failure після двох UPDATE перевірені на приватних fixtures. Відкат залишає точний початковий snapshot; робочий урок не повертався до помилкового тексту заради тестування.

### Точний diff БД, metadata та sitemap

`storage/app/seo-m8-1-local/comparison.json`: **PASS**.

- Із 6 259 TextBlocks змінені рівно п'ять погоджених body. **6 254 інші записи незмінні** за row hashes; повні записи цільових сторінок зіставлені також структурно. Кількість блоків One/Ones 16 і Reciprocal 17 не змінилася (включно з локалями).
- **21 інша таблиця** має ті самі ordered hashes/counts: зокрема 44 460 questions, 197 312 question_answers, 27 154 question_options, 541 151 option links, 133 737 question/theory links, 17 004 verb_hints. Отже, пов'язані тести й весь перевірений банк не змінені.
- Page/title/slug/category/seeder, timestamps, UUID, sort_order, links і EN/PL незмінні. Source Reciprocal Pronouns також збігається з M8; жоден його DB/HTML-блок не змінений.
- На всіх чотирьох theory/course сторінках збережені title, H1, description, OG/Twitter, canonical, meta robots і `X-Robots-Tag`. Чинний development `noindex, nofollow, noarchive` залишився. Курсова canonical і далі посилається на теорію, `.com` використано лише як metadata origin.
- Зміна видимого тексту One/Ones обмежена тими самими п'ятьма блоками. Решта HTML-блоків має незмінні текст і структуру тегів.
- Повний ordered sitemap — **ті самі 554 loc у тому самому порядку**, без lastmod. XML SHA-256 до/після однаковий: `9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885`. Обходу цих 554 сторінок не було.

### Локальні URL та браузер

Справжні course paths знайдені через поточну навігацію `/courses/english-grammar-theory`, не вгадані. До/після — реальні GET без JavaScript, авторизації, cookie/Referer, proxy або follow-redirect. Усі відповіді — 200 HTML; кожний потрібний source body присутній буквально у серверному HTML.

| URL | GET / точні body | Desktop 1440×1000 | Mobile 390×844 |
|---|---|---|---|
| [One/Ones theory](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/one-ones) | PASS / 9 з 9 | PASS | PASS |
| [One/Ones course](http://gramlyze.loc/courses/english-grammar-theory/lesson/zaimennyky-ta-vkazivni-slova/one-ones) | PASS / 9 з 9 | PASS | PASS |
| [Reciprocal theory — контроль](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another) | PASS / 10 з 10, незмінні | Не повторювався | Не повторювався |
| [Reciprocal course — контроль](http://gramlyze.loc/courses/english-grammar-theory/lesson/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another) | PASS / 10 з 10, незмінні | Не повторювався | Не повторювався |

Chromium **147.0.7727.15**, `acceptance-v1-browser.json`: **4/4 PASS**. Кожний сценарій — новий ephemeral guest. До навігації route guard блокує .com/.ub і піддомени, сторонні document paths/redirects та stateful requests. Звичайні ресурси дозволені. У кожному course context підтверджено початковий locked; через штатний `TheoryCourseProgress.createStore().markLessonCompleted()` завершено лише predecessor у тимчасовому localStorage, після чого target став current. Context знищений; глобального відкриття або server progress POST немає.

В усіх чотирьох сценаріях прокручені й перевірені всі 9 source-блоків One/Ones: точний видимий текст, порядок, кількість li, відсутність сирих тегів і горизонтального overflow. Збережено **20 PNG** (блоки №5/7/8/9 і повна сторінка для кожного viewport/page). Візуально переглянуті desktop theory mistakes, mobile theory exercises, desktop course exercises та mobile course rules. Усі вісім вправ і ключ присутні; наявний стиль ul/ol без видимих номерів не перероблявся.

У кожному сценарії середовище заблокувало Google Fonts: `https://fonts.googleapis.com/css2`, `net::ERR_NETWORK_ACCESS_DENIED`. Інструмент цей ресурс дозволяє. Це **fallback-font приймання**, не повний typography parity. Інших failed requests, HTTP ≥400, pageerrors або неочікуваних console errors немає; guard не зафіксував жодної production/write спроби. Наявні `/build/assets` використовуються без HMR.

Build не запускався: inputs Vite/Tailwind, renderer, CSS/JS та набір layout-класів не змінені. Не виконувалися повторні MySQL/performance/CI-матриці, повний crawl, LCP/CLS/Lighthouse, Search Console, HTTP/SSH/БД production, deployment API, PR/merge/workflow dispatch. Дані браузера не містять cookies, CSRF або learner-session exports.

## Команди

PowerShell, cwd `D:/DEV/htdocs/gramlyze.loc`:

```powershell
git fetch origin
git rev-parse HEAD origin/codex/seo-m8-pronoun-content-repair origin/main
git status --short --branch
git switch -c codex/seo-m8-1-one-ones-editorial
$php = 'C:/Program Files/xampp/php/php.exe'
$python = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
& $php tools/diagnostics/seo-m8-1-context.php --local-read-only before
& $python -B tools/diagnostics/seo-m8-1-http.py --label before
# Той самий список suites у проміжних retry/verified і фінальному запуску:
& $python -B tools/diagnostics/run-isolated-tests.py --php $php --label m8-1-editorial-final tests/Feature/OneOnesEditorialPatchTest.php tests/Feature/PronounContentRepairTest.php tests/Unit/TheoryInlineHtmlTest.php tests/Feature/TheoryInlineHtmlRenderingTest.php tests/Feature/PageLocaleContentTest.php tests/Unit/TheoryEditorialDescriptionsTest.php tests/Feature/ResolvedLearningPageSeoTest.php
& $python -B -m unittest tests/diagnostics/test_seo_m8_1_editorial.py
node --test tests/Browser/seo-m8-1-browser.test.cjs
# Лише після завершення повного protected guard, з переглядом preview до apply:
$previous = $env:APP_ENV
try {
    $env:APP_ENV = 'local'
    & $php -d opcache.enable_cli=0 artisan content:patch-one-ones-m8-1 --plan=m8-1-plan-v1.json
    # Окремо звірені старі/нові body, ownership та allowlist полів.
    & $php -d opcache.enable_cli=0 artisan content:patch-one-ones-m8-1 --apply --database=gr2 --plan=m8-1-plan-v1.json --backup=m8-1-before-editorial-v1.json
    & $php -d opcache.enable_cli=0 artisan content:patch-one-ones-m8-1 --apply --database=gr2 --plan=m8-1-plan-v1.json --backup=m8-1-unused-repeat.json
} finally { $env:APP_ENV = $previous }
& $php -d opcache.enable_cli=0 tools/diagnostics/seo-m8-1-context.php --local-read-only after
& $python -B tools/diagnostics/seo-m8-1-http.py --label after
& $python -B tools/diagnostics/seo-m8-1-compare.py
$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m8-1-browser.cjs acceptance-v1
git diff --check
```

Preview/backup-команди наведені як журнал, не для сліпого повтору: назви вже зайняті приватними доказами, новий preview потребує нового імені. Старі M8-плани не переносні на M8.1 або іншу БД.

## Commit/push та межі публікації

Перевірено всі чотири `.github/workflows/*.yml`: push smoke workflows обмежений main; ContentOps — pull_request/main або ручний dispatch. За цією конфігурацією push `codex/seo-m8-1-one-ones-editorial` не запускає деплой. Активних Git hooks/core.hooksPath не виявлено. У Git явно обрані 13 пов'язаних файлів: definition, versioned patch manifest, service/command, три regression-файли, чотири diagnostic tools, `.gitignore` та цей звіт. Staged diff/whitespace/секрети перевірені до commit; credential-pattern scan чистий. Backups, private evidence, `.env`, runtime caches, public/build та сторонні зміни не включені.

Після звичайного push із upstream remote SHA звіряється з local HEAD. Фактичний SHA й GitHub-посилання на звіт наводяться у фінальному повідомленні, без самопосилального SHA у файлі.

```powershell
git add -- .gitignore app/Console/Commands/PatchOneOnesEditorial.php app/Services/OneOnesEditorialPatch.php database/content-patches/one-ones-m8-1.json database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder/definition.json docs/reports/seo-m8-1-one-ones-editorial.md tests/Browser/seo-m8-1-browser.test.cjs tests/Feature/OneOnesEditorialPatchTest.php tests/diagnostics/test_seo_m8_1_editorial.py tools/diagnostics/seo-m8-1-browser.cjs tools/diagnostics/seo-m8-1-compare.py tools/diagnostics/seo-m8-1-context.php tools/diagnostics/seo-m8-1-http.py
git diff --cached --stat
git diff --cached --check
git diff --cached
git commit -m "fix(content): clarify One/Ones rules and static exercises"
git push -u origin codex/seo-m8-1-one-ones-editorial
git rev-parse HEAD
git ls-remote origin refs/heads/codex/seo-m8-1-one-ones-editorial
```

Редакційні зміни й адресне оновлення даних виконані локально. Окремі тестові банки та Reciprocal Pronouns не змінені. Commit/push містять код, джерело, тести та звіт, а не виконану операцію БД. Production .com/.ub не перевірялися й не оновлювалися; PR, merge та деплой не виконувалися.
