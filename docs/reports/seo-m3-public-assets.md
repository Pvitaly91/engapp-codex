# M3 — production-збірка ресурсів публічного інтерфейсу

Дата: 8 вересня 2026. Перевірено тільки на [http://gramlyze.loc](http://gramlyze.loc).
Production не оновлювався; деплой, PR і merge не виконувалися.

## База і межі

- Початкова гілка: `codex/seo-m2-local-stability`.
- Базовий HEAD: `b58c2769b96ad19846da1050d48e32ada6bf5ed0`.
- Після fetch `origin/main`: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`.
- Робоча гілка: `codex/seo-m3-public-assets`, створена від фактичного HEAD;
  M1/M2 присутні в історії. Reset, stash, clean і force push не застосовувалися.
- Прочитані AGENTS, історичні [M1](seo-m1-local-fixes.md) і [M2](seo-m2-local-stability.md),
  структура ресурсів і всі чотири GitHub workflows.
- Мігровано тільки `catalog-public`. Його 14 прямих споживачів:
  home, catalog-tests-cards, test-show, theory/index/category/show,
  courses/index/show/theory-course/theory-lesson, search/results,
  words/test, verbs/test, test-modes/theory-mixed. Legacy/admin layouts не мігровані.

`.env` і APP_KEY залишилися незмінними (перевірено SHA-256 без публікації ключа).
Apache, php.ini, hosts, schema і навчальні дані не змінені. Не запускалися
робочі міграції/сідери/Passive Voice repair, очищення кешу, SSH або production HTTP.
Гостьові браузерні сценарії створювали тільки звичайний локальний session-прогрес.

## Обрана схема

Публічний layout використовує штатний `@vite` з окремими entries:

- [catalog-public.css](../../resources/css/catalog-public.css) — публічний Tailwind,
  перенесений статичний CSS shell/тем/sidebar/карток/декорацій;
- [catalog-public.js](../../resources/js/catalog-public.js) — наявна логіка sticky-header
  і декоративного фону, винесена без переписування алгоритмів;
- [tailwind.public.config.js](../../tailwind.public.config.js) — точні старі кольори,
  Archivo/Manrope, shadow-panel/card, class-based dark mode і джерела класів.

Фактичний Play CDN у baseline — **Tailwind 3.4.17**, а наявний npm Tailwind —
**4.1.16**. Заміна на загальний `app.css` внесла б інші preflight/theme/variants
і глобальне `.text-base { line-height: 2.2rem !important }`.
Тому додано точний npm alias **tailwindcss-public@npm:tailwindcss@3.4.17**.
Це не глобальний downgrade: старі entries app.css/app.js збережені.
[PostCSS dispatcher](../../tools/build/tailwind-by-entry.js) обирає рівно один
компілятор для кожного entry; публічна сторінка завантажує тільки публічний CSS.

Інші зафіксовані версії: Vite 5.4.19, laravel-vite-plugin 1.3.0,
PostCSS 8.5.6, @tailwindcss/postcss 4.1.16, Vitest 2.1.9, Node 22.15.0.
Lockfile змінено для alias і його транзитивних залежностей. Єдиний апдейт уже
наявного пакета — hasown 2.0.2 → 2.0.4: новий is-core-module 2.16.2 вимагає
^2.0.3; повернення 2.0.2 перевірено і спричиняє помилку `npm ci`.
Масового оновлення залежностей або audit fix не було.

Порядок власного статичного CSS збережений після utilities. Дві URL-залежні
CSS-змінні залишені у Blade з `asset()`. Мінімальний ранній script відновлює
тему/фон до ініціалізації Alpine. Hashed filenames не захардкоджені, fallback
на CDN відсутній: готовий build є явною передумовою запуску.

### Alpine і Livewire

До M3: Livewire завантажував свою Alpine, а unpkg підключав другу Alpine
**3.17.2**. Браузер фіксував два `alpine:init`, попередження про multiple
instances і відсутній collapse-plugin у пізнішій копії.

Після M3 єдиний власник — наявний локальний **Livewire 3.8.0**, який постачає
**Alpine 3.15.11** і реєструє collapse до start. Збережено `@livewireScripts`;
другого npm Alpine, імпорту Alpine або `Alpine.start()` не додано.
Прибрано також зайвий кореневий `x-init="init()"`: Alpine сам викликає init.

`themeController`, `searchBox`, `languageSwitcher` залишені глобальними
Blade-функціями, бо містять серверні URL/переклади. Дві функції декоративного
фону явно доступні через window, як і раніше. Autosave і код тестових режимів
не переписувалися. Після — один alpine:init, робочий x-collapse, нуль console/page
warnings/errors у фінальних 32 візуальних сценаріях і 42 інтерактивних перевірках.

Обмеження успадкованої PHP-збірки: composer.lock уже був ignored і не tracked.
M3 не заморожує весь Composer-граф і не комітить vendor; наведені Livewire/Alpine
версії — фактично встановлені локально. Нове відтворюване npm build не потребує vendor.
При майбутньому встановленні Composer-залежностей потрібно повторно перевірити
контракт єдиного Alpine і плагінів.

## Класи навчального контенту

Публічний Tailwind сканує тільки явні source globs: Blade споживачів і shared
components, engram/theory, resources/js, app/Support, Page_V3/V3 PHP/JSON.
Використані динамічні кольори вже задані повними назвами у PHP mappings.
Storage, SQL, backups, дампи й діагностичні звіти не є джерелами публічного CSS.

Read-only [інвентаризація](../../tools/diagnostics/audit-public-content-classes.php)
6259 text_blocks і 254 pages виявила 10 class tokens. П'ять utilities збережені
у вузькому [manifest](../../resources/css/catalog-public-classes.json):
font-mono, font-semibold, line-through, text-slate-500, text-xs.
П'ять gw-* — власні CSS/JS hooks, не підстава генерувати Tailwind rules.
Build не читає БД, паролі або сайт. Allowlist TheoryInlineHtml не змінений.

Перевіряються справжні utility-селектори, responsive lg:shadow-panel,
dark/responsive/disabled variants, стани correct/incorrect і legacy v4 contract;
не вимагається rule для кожного DOM-hook.

## Локальні сторінки й браузер

Desktop 1440×1000, mobile 390×844, Chromium headless (Playwright build 1217),
uk-UA; окремі гостьові контексти, без авторизації. Production .com, його піддомени
і .ub блокуються **до першої навігації** діагностичним CDP guard.
Після перемикання також блокуються старі Tailwind/Alpine CDN; звернень до них
у фінальному прийманні немає. Google Fonts залишені, як у baseline.

| Сторінка | Локальний URL |
|---|---|
| Головна | [/](http://gramlyze.loc/) |
| Каталог теорії | [/theory](http://gramlyze.loc/theory) |
| Future Perfect | [/theory/future-perfect](http://gramlyze.loc/theory/future-perfect) |
| Sentence Types | [/theory/basic-grammar/sentence-types](http://gramlyze.loc/theory/basic-grammar/sentence-types) |
| Present Perfect Forms | [/theory/tenses/present-perfect/present-perfect-forms](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms) |
| Mixed Questions | [/test/future-perfect/questions](http://gramlyze.loc/test/future-perfect/questions) |
| Курс | [/courses/english-grammar-theory](http://gramlyze.loc/courses/english-grammar-theory) |
| Урок курсу | [/courses/english-grammar-theory/lesson/basic-grammar/sentence-types](http://gramlyze.loc/courses/english-grammar-theory/lesson/basic-grammar/sentence-types) |
| Verb to Be Future | [/theory/basic-grammar/verb-to-be/verb-to-be-future](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-future) |
| Collective Nouns | [/theory/imennyky-artykli-ta-kilkist/collective-nouns](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/collective-nouns) |
| One / Ones | [/theory/zaimennyky-ta-vkazivni-slova/one-ones](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/one-ones) |
| Used to / Would | [/theory/tenses/used-to-would](http://gramlyze.loc/theory/tenses/used-to-would) |

Є 32 пари visual-сценаріїв: 12 URLs × 2 viewport у light/blue;
ще три основні URLs × 2 viewport у dark; cards/custom у desktop.
Перед screenshots стабілізується лише декоративна випадковість у probe,
не робоча рандомізація питань. Для уроку встановлені prerequisites тільки
у localStorage власного тимчасового браузера.

Переглянуто screenshots і вибірку computed styles (до 1800 вузлів/сценарій):
кольори, fonts/line-height, padding/margin, borders/shadows, card dimensions.
Горизонтального переповнення до/після немає. Основні стилі збігаються;
порівняння не оголошується pixel-perfect: є випадковий порядок чотирьох sidebar
labels, різний sticky-state після reload уроку та 27 px різниці повної висоти
Verb to Be desktop без зміни його перевірених типографічних/spacing властивостей.
Значної втрати дизайну або контенту у перевірених блоках не виявлено.
Фінальний visual probe чекає завершення AJAX-навігації: проміжний placeholder
не порівнюється з уже завантаженим sidebar.

Окремо **10/10** перевірок п'яти renderer-сімейств:
hero rules — Present Perfect; forms/rule cards і comparison table — Verb to Be;
usage panels — Collective Nouns; mistakes grid — Used to/Would.
Правила, таблиці, inline strong/span, monospace і закреслення працюють на обох viewport.

Старий, окремий дефект: One/Ones уже в baseline має порожні legacy-картки.
Це не використано як доказ повного renderer-покриття; для нього перевірені
інші справжні блоки вище. Навчальний контент поза M3 не виправлявся.

**42/42** інтерактивних перевірки: theme/reload, blue/cards/custom і кольори,
desktop sidebar/reload, mobile menu, AJAX navigation/search, x-collapse,
правильна/неправильна відповідь, token add/remove/re-enable, ручний builder,
course progress/reload. Settings widget admin-only; фон перевірявся через його
наявний Alpine controller і localStorage, без входу в admin. Для wrong-answer
лише у поточній браузерній пам'яті заготовлено explanation cache, щоб probe
не викликав зовнішній AI API.

**28/28 M2 browser checks**: відповіді/позиція/лічильники/порядок після reload,
швидкі edits, internal navigation/back, reload біля debounce, recovery після
одного контрольованого network failure, server-only restore після видалення
localStorage/sessionStorage snapshot при збережених у пам'яті cookies,
ізоляція іншого режиму/тесту/гостя. 204/ERR_ABORTED не приховуються і не
вважаються доказом втрати даних; перевірений саме відновлений стан.

**8/8 без-JS перевірок**: main HTML не став порожньою оболонкою, один H1,
чинні canonical і development noindex. Кожний public CSS/JS — HTTP 200,
text/css або application/javascript. Manifest файли наявні. public/hot,
HMR client і dev-server URL відсутні; npm run dev не запускався.
Інтерактивні тести все ще потребують JavaScript — це не новий no-JS режим тестування.

## Відтворюваність і автоматичні тести

[Source-only build probe](../../tools/diagnostics/verify-public-build.cjs) створює
нову приватну директорію без .env, vendor, node_modules, runtime або DB dumps,
копіює явні source inputs і запускає npm ci та npm run build.

- Чистий npm ci: exit 0, 192 packages.
- Чистий Vite build: exit 0, 55 modules, 34.33 s.
- Робочий фінальний build: exit 0, 55 modules, 37.05 s.
- Public CSS: **96783 bytes**, gzip приблизно **16.10 kB**.
- Public JS: **9531 bytes**, gzip приблизно **3.28 kB**.
- SHA-256 CSS: `49c2eb770e13fed37cd4515b8506d7857267fa658e7709f399ea9b0cd1bf89e0`.
- SHA-256 JS: `7cdbd9ecbb6dc5b50abb3dc4e4cc740c43dcfc4eb00b8aebb6ac5e08b3670487`.

Публічні CSS/JS у чистій і робочій збірках byte-for-byte однакові.
Legacy app.css зберігає v4 та його line-height override; його старий automatic
content discovery дає інший розмір у source-only копії з вужчим набором файлів.
Цей asset не підключено до catalog-public. Спільний dispatcher перевірено
обома компіляторами.

| Набір | Фактичний результат |
|---|---|
| Ізольовані M1/M2 PHP | **107 tests / 797 assertions**, exit 0, 100.105 s |
| Існуючі frontend + нові M3 | **24 tests / 4 files**, exit 0, 42.48 s |
| Node persistence/classification | **12/12**, exit 0 |
| M1 HTTP smoke | **9/9**, development noindex і SEO contracts збережені |
| Банк Future Perfect Questions | 84; нормалізований SHA-256 `47f7cf6c04b9ddad174749eacab6aa5aa6863d1f80438e49c2f6811e0e2cf101`, без змін |
| Додатковий об'єднаний legacy PublicFlows+Theory+AdminFlows | **40 tests / 342 assertions, 22 failures**; не видається за успішний CI |

В останньому, додатковому наборі: minimal PublicFlows schema не має text_blocks;
різні suites у спільному процесі видаляють свої compiled views після першої
ініціалізації compiler, що викликає filemtime/stat failures. Це окремо від
успішних M1/M2 і браузерного приймання. Весь GitHub CI не оголошується зеленим.
Сам runner додатково завершив друк stderr помилкою cp1251; JSON результат збережено.
Два відомі fixture snapshots 11111111… і 44444444…, які observer цього smoke
перезаписав на диску, повернено до вихідного Git-вмісту. Їхній фінальний diff
порожній; сторонні PPC-аудити не чіпалися. Робоча MySQL не перебудовувалася.
Повторний запуск цих legacy suites потребує окремої перевірки файлової ізоляції;
команда для них навмисно не рекомендована як безпечний M3 runner.

У CI workflows додано Node/npm ci/build перед PHP rendering tests, оскільки
manifest тепер обов'язковий. Triggers і permissions не розширені: push лише main,
PR/main або ручний dispatch; push робочої гілки не запускає deployment.
Дії GitHub не запускалися вручну.

### Команди

```sh
node tools/diagnostics/verify-public-build.cjs
npm run build
npm test
python tools/diagnostics/run-isolated-tests.py --php php --include-m2 --label m3-regression
node --test tests/Browser/saved-test-persistence.test.cjs tests/Browser/state-request-classification.test.cjs
python tools/diagnostics/local-seo-smoke.py --label m3-final
node tools/diagnostics/public-assets-browser.cjs baseline visual
node tools/diagnostics/public-assets-browser.cjs baseline-final perf
node tools/diagnostics/public-assets-browser.cjs built-stable visual
node tools/diagnostics/public-assets-browser.cjs built-final perf
node tools/diagnostics/public-assets-interactions.cjs built-final
node tools/diagnostics/public-assets-http.cjs built
node tools/diagnostics/public-assets-renderers.cjs built-verified
node tools/diagnostics/local-state-browser.cjs m3-built
node tools/diagnostics/compare-public-assets.cjs baseline built-stable
```

Baseline-команди виконувалися **до** зміни layout; не запускайте їх після зміни,
видаючи новий стан за старий. У фактичних командах використовувалися встановлені
PHP/Playwright/Chromium через CLI paths і PLAYWRIGHT_MODULE/CHROMIUM_EXECUTABLE.
Нові phase/label зберігають попередні докази. Артефакти локальні у
storage/app/seo-m3-local і seo-m2-local, поза Git.

## Лабораторні вимірювання

Це **не production і не польові Core Web Vitals**. Один і той самий Chromium,
desktop 1440×1000, light/blue, без CPU/network throttling, Apache .loc.
Три нові контексти на сторінку; cold = перша навігація, repeat = reload
у тому самому контексті з дозволеним HTTP cache. CDP guard обмежений забороненими
origin, а не Playwright route-all, який вимкнув би cache.

Baseline-final: 11:27–11:29 UTC; built-final: 11:57–11:58 UTC.
Під час цих двох серій не запускалися наші паралельні builds/tests.
Спостереження: load + fonts.ready + 1200 ms. LCP — PerformanceObserver LCP,
не DOMContentLoaded; CLS — сума layout-shift без recent input у цьому вікні;
ScriptDuration — CDP ScriptDuration, не INP/TBT. Long tasks збережені в raw metrics.
Шрифти, випадкові декорації, AJAX timing і фонове навантаження машини дають розкид.
Пізні AJAX-відповіді можуть не потрапити у вікно одного cold-вимірювання.
Lighthouse CLI/module недоступні; оцінок Lighthouse або INP не вигадано.

Клітинки: **медіана [мінімум–максимум]**, по три вимірювання.

| Сторінка / кеш | До: FCP, мс | Після: FCP, мс | До: LCP, мс | Після: LCP, мс |
|---|---:|---:|---:|---:|
| Sentence Types / холодне | 6932 [4076–8428] | 1284 [1268–1896] | 7652 [5692–8832] | 1284 [1268–1896] |
| Sentence Types / повторне | 6136 [1992–6472] | 1224 [1140–1244] | 6392 [2996–6984] | 1224 [1140–1244] |
| Present Perfect Forms / холодне | 3996 [1144–5088] | 972 [904–1144] | 4340 [1388–5308] | 972 [904–1144] |
| Present Perfect Forms / повторне | 948 [784–5908] | 784 [776–832] | 1152 [912–6160] | 784 [776–832] |
| Future Perfect Questions / холодне | 3752 [3624–5304] | 3196 [3172–3392] | 3752 [3624–5304] | 3196 [3172–3392] |
| Future Perfect Questions / повторне | 2992 [2540–4288] | 2756 [2540–3132] | 2992 [2540–5132] | 2756 [2540–3132] |

| Сторінка / кеш | До: CLS | Після: CLS | До: ScriptDuration, мс | Після: ScriptDuration, мс |
|---|---:|---:|---:|---:|
| Sentence Types / холодне | 0.1275 [0.1141–0.1536] | 0.1061 [0.1058–0.1139] | 270.9 [264.1–304.3] | 141.6 [89.1–150.6] |
| Sentence Types / повторне | 0.0053 [0.0014–0.1149] | 0.1054 [0.1054–0.1283] | 395.0 [150.3–461.0] | 53.7 [52.7–54.8] |
| Present Perfect Forms / холодне | 0.1059 [0.0537–0.1060] | 0.1059 [0.1058–0.1473] | 204.0 [191.2–212.4] | 78.8 [72.1–137.3] |
| Present Perfect Forms / повторне | 0.0014 [0.0014–0.0042] | 0.1054 [0.1054–0.1139] | 129.8 [112.3–172.2] | 45.7 [34.5–57.3] |
| Future Perfect Questions / холодне | 0.0162 [0.0151–0.0283] | 0.0255 [0.0057–0.0316] | 315.1 [302.9–414.4] | 92.6 [81.4–97.3] |
| Future Perfect Questions / повторне | 0.0603 [0.0014–0.0612] | 0.0087 [0.0014–0.0188] | 310.0 [275.7–387.2] | 55.2 [50.5–64.2] |


FCP/LCP і виміряний JS runtime зменшилися. **CLS не покращився всюди**:
repeat теорії виріс приблизно з 0.001–0.005 до 0.105; cold Questions теж гірший
за медіаною. Додатковий observer показав великий зсув при заміні sidebar
placeholder (приблизно 0.104). Раніший paint робить наявні пізні зміни помітними
метриці; це пояснення за спостереженням, не доказ єдиної причини всього CLS.
Декорації збережені; їхню зміну позицій також видно у діагностиці, але ручне
стабілізування screenshots не входило в performance-серію.
Оптимізацію AJAX/SQL, переробку sidebar та видалення декорацій поза M3 не робили.

### Bytes і мережа

На всіх трьох сторінках external JS decoded bytes:
**627646 → 174154** (в усіх runs однаково): прибрані 407279 bytes browser Tailwind
і 55744 bytes другої Alpine, додано 9531 bytes public JS; Livewire 164623 bytes збережений.
External CSS decoded bytes: **14770 → 111553** (Google Fonts + новий public CSS).
Runtime Tailwind генерував inline CSS, тому external CSS окремо не є повною
мірою старого CSS. Частина inline CSS/JS перенесена з HTML у кешовані файли;
не слід двічі додавати inline bytes до HTML.

| Сторінка / кеш | HTML до, bytes | HTML після, bytes | Ресурси з відповіддю до → після | Передано всього до, bytes | Після, bytes |
|---|---:|---:|---|---:|---:|
| Sentence Types / холодне | 390656 [390287–391270] | 342686 [342350–342745] | 10 [10–10] → 10 [10–10] | 1238610 [1238243–1239250] | 1150381 [1150045–1150440] |
| Sentence Types / повторне | 390436 [389589–390857] | 341773 [341690–342006] | 10 [10–10] → 10 [10–10] | 1013480 [1012633–1013900] | 964816 [964733–965049] |
| Present Perfect Forms / холодне | 277353 [277047–277406] | 228097 [227863–228358] | 10 [9–10] → 10 [10–10] | 1125474 [668686–1125500] | 1035932 [1035698–1036193] |
| Present Perfect Forms / повторне | 277012 [276778–277206] | 228204 [227658–229159] | 10 [10–11] → 10 [10–10] | 900389 [900195–1356401] | 851387 [850841–852342] |
| Future Perfect Questions / холодне | 557750 [557750–557750] | 508944 [508944–508944] | 10 [9–10] → 9 [9–10] | 949409 [949409–949412] | 860346 [860346–860459] |
| Future Perfect Questions / повторне | 557750 [557750–557750] | 508944 [508944–508944] | 10 [9–10] → 10 [9–10] | 724500 [724500–724501] | 675694 [675694–675694] |


«Ресурси з відповіддю» — CDP response records, включно зі шрифтами/AJAX;
проміжні redirect hops CDN не рахуються окремо. Wire bytes — encodedDataLength,
у repeat cache може давати 0; це не decoded size. Дані без відсутнього CDN:
baseline дійсно був стилізований і обидва старі scripts повертали 200.

## Файли M3 і майбутній деплой

Source: catalog-public Blade, два нові CSS/JS entries, class manifest,
tailwind.public.config.js, tools/build/tailwind-by-entry.js,
postcss.config.js, vite.config.js, package.json і package-lock.json.
Тести: tests/js/publicAssets.test.js.
Інструменти: audit-public-content-classes.php, public-assets-browser.cjs,
public-assets-interactions.cjs, public-assets-http.cjs, public-assets-renderers.cjs,
compare-public-assets.cjs, verify-public-build.cjs.
CI: чотири існуючі .github/workflows; документація: цей звіт.
Історичні M1/M2 звіти не переписані.

За окремого майбутнього дозволу на deploy потрібно встановити залежності
`npm ci` з devDependencies для build, виконати `npm run build` і доставити
**разом** public/build/manifest.json та відповідні assets до запуску нових views.
Не запускати HMR і не підставляти CDN fallback. Перевірити локальний Livewire
asset/єдину Alpine та зробити аналогічне браузерне приймання цільового оточення.
M3 не потребує міграції чи дампа БД. Composer graph і серверні конфігурації
мають окрему політику; APP_KEY не генерувати заново.

Generated public/build лишений ignored згідно з чинною політикою. У коміт не
входять .env, vendor, node_modules, hot-files, кеші, DB dumps/backups,
приватні screenshots/logs або сторонні PPC-аудити/.codex.

Зміни перевірені на http://gramlyze.loc. Публікація — тільки звичайний commit/push
у робочу гілку; production gramlyze.com не оновлювався, деплой не виконувався.
