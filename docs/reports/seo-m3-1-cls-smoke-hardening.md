# M3.1 — стабільність верстки та ізоляція smoke-приймання

Локальне приймання: `http://gramlyze.loc`, 8 вересня 2026. Production HTTP,
SSH, БД, Search Console і deployment API не використовуються. Це конкретна
стабілізація M3, не повторний SEO-аудит.

## База й межі

- Базовий M3: `7c649117e747fb922da166c2e1fba59474fee203`.
- Робоча гілка: `codex/seo-m3-1-cls-smoke-hardening`, створена від фактичного M3.
- Після fetch `origin/main`: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`;
  remote M3 відповідав базовому SHA. M1 `d123a73800ded20ceca3ef6476597787fb153bd0`
  і M2 `b58c2769b96ad19846da1050d48e32ada6bf5ed0` присутні в історії.
- Сторонні PPC-аудити, `.codex/`, дампи, backups і попередні приватні докази
  збережені поза комітом. Reset, stash, clean і force push не застосовуються.
- `.env`, APP_KEY, Apache, php.ini, hosts і навчальні дані не змінюються.
  Робочі міграції, сідери, Passive Voice apply та очищення кешу не запускалися.
- Історичні звіти [M1](seo-m1-local-fixes.md), [M2](seo-m2-local-stability.md),
  [M3](seo-m3-public-assets.md) залишені незмінними.

## Відтворена причина зсуву

У новій трасі M3, Sentence Types / desktop / repeat 1:

| Подія | Час від navigation start, мс | Геометрія |
|---|---:|---|
| Load; sidebar ще завантажується | 1147,7 | Навігація 410 × 170,5; TOC top 398,5 |
| AJAX response end | 1266,6 | Завершено отримання `/theory/navigation` |
| Вставлено дерево | 1357,2 | Навігація 410 × 888; TOC top 1116 |
| Layout shift | 1367,0 | 0,103928; affected node — `data-theory-toc-pin-root` |
| Sidebar ready, два animation frames | 1449,1 | Дерево готове |

Main залишається на x=491, y=204, ширина 892 px. Причина — вертикальне
розширення навігаційної картки, а не рух самого affected node: у
[show](../../resources/views/theory/show.blade.php) заданий тільки max-height,
а [loader](../../resources/views/theory/partials/desktop-navigation-loader.blade.php)
замінює короткий placeholder довгим деревом. TOC — наступний звичайний sibling.
Перед Alpine одночасно існували видимі loading/error блоки (висота 254,5 px).

Окремі fault-injection сценарії (не LCP-медіани):

| Сценарій до правок | Підтверджений рух | CLS у fault-вікні |
|---|---|---:|
| Desktop AJAX +2200 ms | Навігація 170,5 → 888 px; TOC +717,5 px | 0,103928 |
| Mobile AJAX +2200 ms | Layout height 126 → 716 px; навчальний блок +590 px | 0,123411 |
| Desktop HTTP 500 | Коротка картка лишається 170,5 px | 0,001858 |
| Mobile HTTP 500 | 126 → 146 px; наступний блок +20 px; close/reopen retry працює | 0 |
| Saved collapsed + Livewire затримано на 1200 ms | Aside 410 → 116 px; main x491 → x197, width892 →1186 | 0,171238 |

Останній випадок — саме намальована, а не лише pre-paint геометрія:
FCP 2564 ms, expanded painted frame 2570,7 ms, Alpine init 4101,8 ms,
initialized 4150,4 ms. Затримка локального asset є окремою причинною пробою.
У всіх п'яти сценаріях один Alpine init і нуль заборонених запитів.

### Адресне виправлення

- [Public CSS](../../resources/css/catalog-public.css) резервує вже наявний
  штатний footprint: desktop `calc(100vh - 7rem)` тільки від 1024 px;
  mobile panel `calc(100vh - 8rem)`. Це розміри попереднього loaded-стану,
  не довільна велика висота. Закрите mobile-меню лишається `display:none`.
- [Desktop loader](../../resources/views/theory/partials/desktop-navigation-loader.blade.php)
  і [mobile navigation](../../resources/views/theory/partials/mobile-navigation.blade.php)
  приховують початково false error/content через `x-cloak`; loading, error і
  готове дерево взаємовиключні. Збережені AJAX, дерево, внутрішнє прокручування,
  пошук, autoscroll та анімація відкриття. Mobile отримав aria-expanded,
  aria-controls і aria-busy. Новий desktop retry не додавався.
- Ранній [head script](../../resources/views/layouts/catalog-public.blade.php)
  читає наявну collapsed-preference. Scoped fallback у
  [tree-nav](../../resources/views/theory/partials/tree-nav.blade.php) діє
  тільки доки Alpine не встановив власний `data-collapsed`, тому не перекриває
  подальші кліки. Main/body не ховаються й перший paint не відкладається.
- [Шість source/behavior регресій](../../tests/js/theorySidebarLayout.test.js):
  перед правками 5 failed / 1 passed; після правок усі шість проходять.
- [Fault/geometry probe](../../tools/diagnostics/theory-sidebar-stability.cjs)
  відрізняє layout allocation (`offsetWidth/offsetHeight`) від transform
  `x-transition`, зберігаючи raw rects. Перевіряє і рух сусіднього контенту.
  Його дев'ять Node-тестів не зараховують штатний scale-transition як AJAX resize
  або першу появу раніше cloaked TOC як переміщення вже видимого елемента.

### Функціональне приймання sidebar після правок

21 сценарій / 394 checks PASS: дві сторінки × два viewport × new guest,
saved expanded, saved collapsed, delayed AJAX та HTTP 500; додатково reload
зі saved collapsed і затриманим Alpine. Нуль page errors і forbidden-origin
requests. Пошук, active link, внутрішнє прокручування, collapse, mobile
close/reopen retry і навігація працюють; закрите mobile-меню не займає висоти.

| Відтворений сценарій | До | Після |
|---|---:|---:|
| Desktop: рух TOC після AJAX | 717,5 px | 0 px |
| Mobile: рух наступного навчального блоку | 590 px | 0 px |
| Delayed Alpine: рух main по x | 294 px | 0 px |
| Delayed Alpine: CLS | 0,171238 | 0,001423 |

У collapsed-перевірці після правок aside має ширину 116 px вже при першому
paint (1252 ms), до Alpine init 2370 ms / initialized 2395,1 ms. Діапазон CLS
фінальних fault/state-сценаріїв — 0–0,019930; це окремі сценарії, не LCP-вибірка.

Прозорість повторів: перший `m31-after` із 20 сценаріїв завершився exit 1
(17 PASS / 3 flags). Три desktop flags включали некоректне порівняння TOC,
який ще мав `x-cloak`, з його першою видимою геометрією; перший запит також
зафіксував transient overlap без збережених raw-response markers, тому його
не оголошено успішним і причина цього окремого overlap не доведена.
Після адресного виправлення probe всі п'ять desktop Sentence Types сценаріїв
повторено як `m31-after-confirm`: PASS, перевірені нові HTML/CSS markers,
жодного overlap чи руху. Delayed Alpine — `m31-after-collapse`: PASS.
Фінальне покриття 21 складається з решти 15 первинних + 5 підтверджених + 1
додаткового сценарію; первинні невдалі raw-дані не перезаписані. Cache clear
або зміни Apache/OPcache для повтору не застосовувалися.

Незакрите спостереження: у першому запиті overlap був **після paint**, не
лише артефакт probe: FCP 4764 ms, loading/error box-presence 4869–4953 ms,
Alpine init 4959,8 ms. Внутрішній content DIV потім двічі змістився на 84 px;
другий shift 0,0108824, загальний CLS 0,02434895. Зовнішня висота sidebar
888 px і main залишилися стабільними. Немає raw HTML/attributes того запиту,
тому stale compiled template — лише гіпотеза, не встановлена причина.
Остаточні підтверджені відповіді чисті, але цей одиничний початковий збій не
перейменовано на harmless transition і його точну причину не оголошено закритою.

## Методика нового порівняння

[Метричний reducer](../../tools/diagnostics/cls-session-window.cjs) реалізує
максимальне session window за перевіреним контрактом
[web-vitals 6.2.1 LayoutShiftManager](https://github.com/GoogleChrome/web-vitals/blob/v6.2.1/src/lib/LayoutShiftManager.ts)
і [onCLS](https://github.com/GoogleChrome/web-vitals/blob/v6.2.1/src/onCLS.ts):
розрив строго менше 1000 ms, вік строго менше 5000 ms; recent input не
враховується у метриці, але такі записи залишаються в raw evidence.

[Новий probe](../../tools/diagnostics/public-layout-shifts.cjs) установлює
observer через pre-navigation init script. Однакове вікно 0–12000 ms для до/після;
load, fonts, Alpine і sidebar мають бути готові щонайменше за 2000 ms до кінця.
Timeout, hidden document, JavaScript error, неготові шрифти/навігація або
суттєве запізнення cutoff дають `incomplete`, не успішний нуль CLS.

Три сторінки × desktop 1440×1000 / mobile 390×844 × п'ять cold/reload пар:
60 навігацій у кожній фазі. Cold — новий гостьовий контекст; repeat — reload
того самого контексту з реальним HTTP cache. UK locale, light, штатний фон,
без CPU/network throttling. Production і заборонені runtime CDN
(Tailwind compiler / Alpine unpkg; не Google Fonts) блокуються до навігації через
origin-scoped CDP guard; route-all у performance не використовується.
Build/PHP suites і вимірювання не виконуються паралельно.

Raw evidence зберігає всі shifts, recent-input flags, attribution/rects,
DOM geometry/state timeline, readiness timings, LCP, версії, source/manifest
SHA і навігаційні epochs. Скриншоти/fault injection відділені від performance;
декоративна випадковість і анімації у performance не змінені.

`clsSessionWindow` і `legacyShiftSum` наведені окремо. Друга метрика — сума
за **нове** 12-секундне вікно, не історичне load + fonts + 1200 ms.
Старі M3 raw-дані не мають усіх потрібних entries, тому точний перерахунок
історичного CLS неможливий. Дані `renderers.json` після ручного декоративного
стабілізування screenshot не є природною performance-трасою.

Це main-frame лабораторні вимірювання в обмеженому вікні, **не польові Core
Web Vitals** і не гарантія CLS за весь час життя сторінки.

Baseline завершено 12:49:40–13:01:48 UTC: 60/60 complete, кожна група 5/5.
Chromium 147.0.7727.15, Playwright 1.62.1, Node 22.15.0, Vite 5.4.19,
Tailwind public 3.4.17 / legacy 4.1.16, Vitest 2.1.9. Незалежний повторний
розрахунок з raw entries збігається з summary. Перевірені render-source файли
до правок відповідають M3 HEAD (після нормалізації робочих CRLF/LF).
Вбудований `sourceSha256` probe — не повний inventory шаблонів: він не включав
head layout. Додаткова LF-normalized provenance для
`resources/views/layouts/catalog-public.blade.php`: M3
`018fc6dd5905f9df564649f4eca55871613c8abf82347b731fc6b1f6cef1a91c`, M3.1
`67255acbea7d638e5f1d91cb6580c140c0e714e3508ca3731fc79479fe46b442`.

Обмеження mobile baseline: Chromium позначив усі 28 cold-shifts як
`hadRecentInput=true`, хоча probe не виконував взаємодій. Стандартний алгоритм
їх виключає; нуль mobile CLS **не означає відсутність фізичних зсувів**.
Flags не переписувалися, raw entries збережені. Окремий delayed-AJAX mobile
сценарій вище має не виключений зсув 0,123411. Початковий viewport/emulation
може впливати на browser flags; точну внутрішню причину Chromium не доведено.
Cold означає новий HTTP-cache контекст, не очищення OS/DNS cache або новий
процес браузера. Однакові geometry observers додають діагностичне навантаження
обом фазам; це не оцінка сайту без інструмента.

### M3 → M3.1: підтверджене порівняння 60 + 60

Фінальний after `m31-after-network-parity`: 14:39:44–14:51:50 UTC, **60/60
complete**, кожна з 12 груп — 5/5. Окремий read-only
[comparison verifier](../../tools/diagnostics/verify-layout-comparison.cjs)
незалежно перерахував raw CLS/legacy/LCP та summaries: PASS. Conditions,
версії браузера/залежностей, package-lock і два metric-probe SHA однакові.
У кожній фазі реально отримано 60 Google stylesheet HTTP 200 і 180 font HTTP
200; немає font/resource failures. По 20 local Questions state 204/ERR_ABORTED
зіставлено з Fetch204, явно пораховано й не прирівняно до втрати прогресу.
Нуль page/guard/console errors і production requests у цих двох фазах.
Відхилена 8-record серія не пройшла verifier (exit 1, 0 verified records).

Тест verifier має вісім синтетичних regressions, включно з denial при
`FontFaceSet.loaded`, неповними/дубльованими runs, parity і raw/summary mismatch.
Він не змінює observer, результати або джерела baseline і не оголошує
самостійно покращення швидкості.

Нижче формат: **медіана [мінімум–максимум]**; дробові метрики наведено до
шести знаків, не округлено до формального нуля.

#### LCP, мілісекунди

| Сторінка / viewport / cache | M3 | M3.1 | Δ медіани |
|---|---:|---:|---:|
| Sentence Types / desktop / cold | 1344 [1160–4744] | 1120 [1064–1328] | -16.7% |
| Sentence Types / desktop / repeat | 1064 [964–1180] | 920 [892–1024] | -13.5% |
| Sentence Types / mobile / cold | 1080 [964–1116] | 1108 [1016–1352] | +2.6% |
| Sentence Types / mobile / repeat | 1040 [912–1328] | 864 [808–1016] | -16.9% |
| Present Perfect / desktop / cold | 1028 [984–1104] | 964 [828–1012] | -6.2% |
| Present Perfect / desktop / repeat | 1044 [924–1348] | 744 [660–832] | -28.7% |
| Present Perfect / mobile / cold | 848 [756–916] | 960 [856–1072] | +13.2% |
| Present Perfect / mobile / repeat | 732 [612–816] | 640 [476–736] | -12.6% |
| Questions / desktop / cold | 3636 [3328–4092] | 2784 [2512–3084] | -23.4% |
| Questions / desktop / repeat | 3588 [3096–4108] | 2196 [2056–2536] | -38.8% |
| Questions / mobile / cold | 3672 [3544–3696] | 2560 [2312–2708] | -30.3% |
| Questions / mobile / repeat | 3012 [2668–3204] | 2096 [1996–2120] | -30.4% |

#### Стандартний CLS і окрема legacyShiftSum

Legacy тут також за нове 12-секундне вікно; це **не** історична коротка сума M3.

| Сторінка / viewport / cache | CLS M3 | CLS M3.1 | Legacy M3 | Legacy M3.1 |
|---|---:|---:|---:|---:|
| Sentence Types / desktop / cold | 0.108136 [0.106000–0.117714] | 0.009828 [0.002009–0.015214] | 0.108136 [0.106000–0.117714] | 0.009828 [0.002009–0.015214] |
| Sentence Types / desktop / repeat | 0.105351 [0.105351–0.120472] | 0.008289 [0.002769–0.021640] | 0.105351 [0.105351–0.120472] | 0.008289 [0.002769–0.021640] |
| Sentence Types / mobile / cold | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] |
| Sentence Types / mobile / repeat | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] |
| Present Perfect / desktop / cold | 0.106557 [0.105975–0.122845] | 0.007612 [0.002021–0.026332] | 0.106557 [0.105975–0.122845] | 0.007612 [0.002021–0.026332] |
| Present Perfect / desktop / repeat | 0.105351 [0.103928–0.120103] | 0.002995 [0.001423–0.036855] | 0.105351 [0.105351–0.120103] | 0.002995 [0.001423–0.036855] |
| Present Perfect / mobile / cold | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] |
| Present Perfect / mobile / repeat | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] |
| Questions / desktop / cold | 0.026293 [0.005737–0.058238] | 0.011358 [0.003501–0.029890] | 0.026293 [0.005737–0.058238] | 0.011358 [0.003501–0.029890] |
| Questions / desktop / repeat | 0.002229 [0.000000–0.058847] | 0.001423 [0.000000–0.011950] | 0.002229 [0.000000–0.058847] | 0.001423 [0.000000–0.011950] |
| Questions / mobile / cold | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] |
| Questions / mobile / repeat | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] | 0.000000 [0.000000–0.000000] |

У всіх 60 after-вимірюваннях CLS ≤ 0,1; найгірший — **0,036855**
(Present Perfect / desktop / repeat 1). Desktop-медіани теорії зменшилися
з 0,105–0,108 до 0,003–0,010. Це узгоджується з причинними geometry/fault
пробами, що показують відсутність великого руху TOC та main після AJAX.

LCP не оголошується кращим усюди: Present Perfect / mobile / cold має
**+112 ms (+13,2%)**, Sentence Types / mobile / cold **+28 ms (+2,6%)**.
Інші десять медіан нижчі. Це дві послідовні локальні серії, не рандомізований
A/B експеримент; відмінності сервера/шрифтів у часі не усунені. Зміни LCP
контрольної Questions не приписуються sidebar-коду. Позитивні mobile cold
дельти збережені як обмеження; причинну істотну UI-регресію ними не доведено,
але й твердження «LCP не погіршився в жодній групі» було б неправильним.

У Present Perfect mobile cold погіршення не пояснюється повільнішим TTFB:
медіана responseStart стала ранішою, **633,1 → 599,0 ms**. Медіана per-run
LCP−TTFB зросла **219,0 → 348,4 ms**; fontsReady **959,1 → 1155,6 ms**,
load **949,5 → 1143,1 ms**. LCP — той самий вступний DIV, FCP=LCP в усіх
десяти відповідних навігаціях. Це зафіксоване погіршення вибірки, а не
автоматично «шум»; його причинність щодо layout edits лишається непідтвердженою.

Mobile raw cold-shifts: **28 → 30**, усі мають `hadRecentInput=true`; repeat
shifts 0 → 0. Найбільший виключений за стандартним правилом after-shift —
**0,031869**, Present Perfect mobile cold 5, 1414,6 ms: вступний текст став
на один рядок / 28 px вищим біля fontsReady 1416,3 ms. Цей фізичний рух не
приховано за нульовим standard CLS; точну причину browser recent-input flags
не встановлено.

Найгірший standard after-shift 0,036855 (Present Perfect desktop repeat 1)
містить background chat/quote і header attribution, не TOC. Affected nodes
самі по собі не доводять причину: решта дрібних font/header/decoration shifts
збережені; декорації не прибиралися заради метрики.

Незалежний geometry audit усіх 20 after desktop-theory навігацій: sidebar
**888 px** в усіх збережених painted frames, main **x491 / width892**, видимий
TOC **y1116** стабільний. TOC-attributed shifts **20 → 0**. Дрібні зміни
загальної висоти main до 2 px і шрифтового тексту лишаються; це не заява
про незмінність кожного пікселя. У всіх 30 repeat навігаціях кожної фази
обидва public assets мають transferSize 0 (60 cache hits на фазу).

## Результати приймання

### Build та JS/Python

| Перевірка | Фактичний результат |
|---|---|
| `npm ci --no-audit --no-fund` | PASS, 192 packages |
| `npm run build` | PASS, 55 modules, 1m42s |
| Source-only M3 public verification, `m31-source-only-build` | PASS; чиста source-копія без `.env`, vendor і БД; npm ci + build, 55 modules |
| Чинний Vitest frontend набір | 30 tests / 5 files PASS, 34,05s, включно з 6 новими layout regressions |
| Node persistence/classification + CLS + sidebar + comparison diagnostics | 50/50 PASS, 199,615ms; 6 + 6 + 21 + 9 + 8 відповідно |
| Python runner regressions | 6/6 PASS, включно з cp1251/UTF-8, batch child runtimes, exit codes, зміненим fingerprint, reverse order та приватним startup override |

Public CSS `catalog-public-DkOvOqwd.css`: 96 944 bytes,
SHA-256 `58b449fa028e356005e026c71c65358be8817704196c9517d30456579be9c92a`.
Public JS `catalog-public-CzFNw-3Z.js`: 9 531 bytes,
SHA-256 `7cdbd9ecbb6dc5b50abb3dc4e4cc740c43dcfc4eb00b8aebb6ac5e08b3670487`.
Обидва public hashes однакові у working і source-only build. Public JS не
змінений від M3; legacy Tailwind 4 entry лишився окремим від public Tailwind 3.
Ignored `public/build` не додається у Git. HMR/CDN fallback не використовуються.
Попередження npm про deprecated `whatwg-encoding` і застарілу Browserslist
базу не приховані; масового оновлення залежностей не виконувалося.

Перший Vitest tool session втратився без фінального результату й не врахований.
Вказані 30 PASS — повторний завершений процес з exit 0; це не історичні 36 JS.

### Browser/HTTP функціональні перевірки

| Набір, label `m31-final` | Результат |
|---|---|
| Взаємодії desktop/mobile | 42/42 checks PASS: тема й blue/cards/custom persistence, меню, search, x-collapse, один Alpine init, відповіді, reorder/manual input, course progress |
| П'ять renderer-сімейств × два viewport | 10/10 PASS: hero/rules, forms grid, comparison table, usage panels, mistakes grid; немає сирих inline HTML тегів |
| GET без JavaScript | 8/8 PASS: H1, основний HTML, canonical, development noindex, CSS/JS asset GET 200 з належним MIME |
| M2 state desktop/mobile | 28/28 PASS: reload, rapid/latest snapshot, navigation, injected reject/delay, queue recovery, server-only restore без localStorage, розділення mode/test/guest |
| M1 GET regressions | 9/9 PASS; Questions bank 84, SHA-256 `47f7cf6c04b9ddad174749eacab6aa5aa6863d1f80438e49c2f6811e0e2cf101` незмінний |

Вісім no-JS URL (усі на `http://gramlyze.loc`): `/`, `/theory`,
`/theory/future-perfect`, `/theory/basic-grammar/sentence-types`,
`/theory/tenses/present-perfect/present-perfect-forms`,
`/test/future-perfect/questions`, `/courses/english-grammar-theory`,
`/courses/english-grammar-theory/lesson/basic-grammar/sentence-types`.
Теорія/урок містять навчальний HTML; у Questions без JS перевірений вступний
HTML, а інтерактивні 84 питання перевіряються окремими browser сценаріями.
Прямий Questions HTML — 200 з canonical, який зберігає кінцевий `/questions`;
`source=theory` дає штатний 302 на чистий `.loc` URL; JSON Accept на навчальному
URL — 404, реальний `/questions/questions?mode=saved-test-js-v2` API — 200.
Canonical уроку курсу веде на відповідну теорію, development noindex збережений.

M2 state має по 15 HTTP 204 і по 15 успішних fetch-resolved для кожного
viewport. Є requestfailed/ERR_ABORTED; одна справжня відмова на viewport
навмисно інжектована й зафіксована як fetch-rejected. Після неї черга
відновлюється, останній snapshot перемагає. Відповіді, counters, позиція й
порядок після server-only restore збігаються; втрати прогресу не відтворено.

Межа цього першого функціонального прогону: sandbox блокував Google Fonts
(`net::ERR_NETWORK_ACCESS_DENIED`: 18 у interactions, 10 у renderers, 8 у
no-JS). Це збережені console/resource errors, а не помилки JavaScript логіки;
page errors і production attempts — 0. No-JS також фіксує очікуваний `csp`
для виконання JS при `javaScriptEnabled:false`, але прямий GET самого asset
перевірено окремо як 200. Скриншоти цієї серії показують fallback-font стан
і не використовуються як доказ шрифтового або performance-паритету.

Через ту саму мережеву відмінність початковий `m31-after-final` performance
зупинено після восьми навігацій і **виключено** з до/після порівняння.
`FontFaceSet.status=loaded` саме по собі не доводить успішне отримання Google
stylesheet. Raw-дані не видалено; це незавершений прогін, не новий baseline.
Для рівних умов повтор використав незмінний observer/probe з доступом до
штатних font resources й тим самим pre-navigation блокуванням `.com`/`.ub`.

Після завершення performance повторено functional приймання з нормальним
доступом до шрифтів, label **`m31-font-confirm`**: interactions **42/42**,
renderers **10/10**, no-JS **8/8**, усі exit 0. У кожному з цих трьох наборів
console/page errors і production attempts — **0**; font responses успішні
(68 / 40 / 30 HTTP 200 відповідно). Renderers не мають network failures;
interactions зберігає шість local state ERR_ABORTED, no-JS — вісім зазначених
JS-disabled `csp` записів із незалежно підтвердженим asset GET 200.
Візуально переглянуті фінальні desktop hero/sidebar та mobile comparison-table
скриншоти зі штатними шрифтами; скриншоти зберігаються тільки локально.

## Ізоляція smoke та історичні failures

[Test bootstrap](../../tests/bootstrap.php) викликає
[IsolatedTestEnvironment](../../tests/Support/IsolatedTestEnvironment.php)
до Laravel bootstrap. Кожен дочірній процес має власний runtime, випадковий
тестовий ключ, SQLite, array cache/session, приватні storage/disks, exports,
views і bootstrap caches. Робочий `.env` не завантажується. Перед schema
операціями перевіряються testing mode, драйвер реального PDO та
`PRAGMA database_list`, включно з фактичними main/attached SQLite paths.
Перевірка realpath відхиляє спільні шляхи й junction/symlink вихід за runtime.
Ownership конфігурованого файла перевіряється ще **до** lazy PDO resolver,
щоб помилкова конфігурація не могла навіть створити сторонню порожню SQLite.
Нова regression closure доводить, що такий resolver взагалі не викликається.

Public/Theory/Admin fixtures більше не чистять робочі views і не міняють
шлях живого singleton Blade compiler. Runtime утримується до завершення
процесу й лишається приватним доказом. Події Question вимкнені лише навколо
відповідних fixtures; активний observer перевіряється окремо.
[QuestionExportService](../../app/Services/QuestionExportService.php) отримав
конфігурований шлях з незмінним штатним fallback `database/seeders/questions`.
Тест із guard безпосередньо перед файловим записом перевіряє ізольований export
і безпечно падає при поверненні старого hardcoded шляху, не пошкоджуючи snapshot.

[Runner](../../tools/diagnostics/run-isolated-tests.py) зберігає byte-exact
stdout/stderr, декодує UTF-8 з видимим backslash escape для некоректних байтів,
повертає справжній child exit code і додатково відхиляє файлові побічні ефекти.
Кожен child result негайно записується, докази мають унікальні назви.
Повне SHA-256 хешування snapshots, `.env`, робочих compiled views/bootstrap
caches, PPC-аудитів, `.codex/` та архівів виконується до й після всієї матриці.
Вісім read-only workers прискорюють обхід; жодні snapshots не виключені.
Перші повільні single-thread preflight-спроби не дійшли до PHP: одна втратила
tool session, іншу зупинено за її точним PID до bootstrap. Це не PHPUnit
результати і вони не зараховані як PASS.

### Безпечний первинний запуск

`m31-initial`, preflight PASS: `testing`, фактична SQLite `:memory:`, cache/session
`array`, `working_env_loaded=false`; перевірені приватні compiler, disks, exports
і caches. Захищено 46 735 файлів, у тому числі всі 46 646 question snapshots
(404 506 816 bytes). Змін до/після **0**; aggregate SHA-256 однаковий:
`4fdd2df2c3e5d448f008b8523a1bef9400e3759edd5d299d84e6b27628a51efb`.
Ніякого відновлення файлів після тестів не було.

| Первинний child | Tests / assertions | Результат |
|---|---:|---|
| Isolation | 6 / 18 | 1 failure: synthetic SQLiteConnection не мав driver metadata; guard відхилив його раніше за очікувану перевірку mismatch |
| PublicFlows | 11 / 195 | 1 failure: точно відтворено відсутній `text_blocks` у fixture (#1) |
| Theory | 12 / 414 | 2 failures після усунення compiler: fixture очікує Will vs Going To без актуальної вкладеної категорії (#2, #7) |
| AdminFlows | 18 / 103 | 1 failure: dashboard 302 через неявний backend (#11) |

Ця матриця чесно повернула exit 1; усі її results/JUnit/raw збережені приватно.
Попередній compiled-view `filemtime/stat` не повторився. Нові видимі Theory
помилки не замовчуються: це другий шар за початковою compiler-помилкою,
а не доказ успішності цих двох тестів на цьому етапі.

### Проміжна повна матриця, не прийнята як фінальна

`m31-final-a70fefa1994f4e0fb52c77ef4fe2edc4` (13:55:32–14:07:17 UTC) також
збережена, wrapper exit 1, 46 735 protected files / 0 changes, той самий
aggregate SHA-256. У ній:

| Child | Tests / assertions | Результат |
|---|---:|---|
| Isolation | 6 / 18 | PASS |
| PublicFlows | 11 / 210 | PASS |
| Theory | 12 / 665 | 1 failure, #2 |
| AdminFlows | 19 / 111 | Assertions PASS, але stderr виявив оброблену помилку неповної `content_operation_runs` fixture |
| Combined | 42 / 986 | 1 failure, #2 |
| Reversed | 42 / 986 | 1 failure, #2; реальний `--order-by=reverse`, 5m01s |
| M1/M2 | 107 / 797 | PASS, 54,668s |

#2 уточнено за кодом [PageController](../../app/Http/Controllers/PageController.php):
картки категорії — лише прямі уроки; рекурсивне дерево завантажується через
`/theory/navigation`. Чинний definition кладе Will vs Going To у
`maibutni-formy → future-simple → will-vs-be-going-to`.
[TheoryRouteMatrix](../../tests/Support/TheoryRouteMatrix.php) тепер сіє потрібну
вкладену категорію **тільки в тестовій SQLite**, а перевірка зберігає весь шлях
учня: initial loader → його фактичний localized AJAX URL → child category і
canonical lesson link → картка уроку в child category → сам урок.
Category URL має leaf slug `/theory/future-simple`; lesson URL — повний шлях.
Жодного SSR дерева чи нового production SQL/cache механізму для цього не додано.

Для admin fixture додано тільки порожній latest-run контракт таблиці
`content_operation_runs` (`id`, `started_at`), щоб успішні assertions не
покладалися на обробку відсутньої таблиці. Dashboard GET виконує лише локальний
`git rev-parse --abbrev-ref HEAD`, не fetch/deploy. API redirect перевіряється
окремим новим тестом; штатну поведінку застосунку не змінено.

### Остаточна прийнята матриця

`m31-accepted-1d703aa88f3640b9926aea58714a7926`: усі child exit 0, wrapper exit 0.

| Child | Tests / assertions | Час | Результат |
|---|---:|---:|---|
| Isolation | 7 / 21 | 0,563s | PASS |
| PublicFlows | 11 / 210 | 8,295s | PASS |
| Theory | 12 / 850 | 68,073s | PASS |
| AdminFlows | 19 / 111 | 70,779s | PASS |
| Combined | 42 / 1171 | 175,703s | PASS |
| Reversed | 42 / 1171 | 291,990s | PASS |
| M1/M2 | 107 / 797 | 54,297s | PASS |

Усі початкові **40 method IDs** з HEAD збережені; жодного вилучення, skip або
послаблення assertions. Додані два smoke-методи: plaintext/hash credential
precedence та штатний API dashboard redirect. Усі початкові 22 failure IDs
присутні й проходять **окремо, combined і reversed**. JUnit доводить точний
42-item reverse, а не лише перестановку аргументів suites: forward починається
`PublicCatalogSmokeTest::test_catalog_cards_render_for_supported_locales`,
закінчується `AdminTestsAndTagsSmokeTest::test_grammar_test_builder_renders_for_authenticated_admin`;
у reversed — навпаки, весь список ідентичний у зворотному порядку.

Unexpected ERROR/fatal/warning у stderr немає; Theory лишає очікувані
SiteTree fixture INFO. Перед цим цільовий `m31-focal-confirm` пройшов
10 tests / 255 assertions; попередню невдалу пробу з вручну припущеним `/en/`
AJAX prefix збережено, fixture виправлено на URL, який реально видає helper.

Повне фінальне before/after хешування: **46 735 файлів, 0 змін**, той самий
aggregate SHA-256 `4fdd2df2c3e5d448f008b8523a1bef9400e3759edd5d299d84e6b27628a51efb`.
Склад: 46 646 snapshots, 53 compiled views, 3 bootstrap caches, 23 `.codex`
files, 7 archives, `.env`, 2 PPC audits. Відновлення після тестів не було.
Матриця й after-hash завершені до browser/performance приймання.

Разом із окремим CI49 нижче перевірено **205 тестових випадків PHP /
2151 assertions** (42 + 7 + 107 + 49), включно з data-provider наборами M1/M2.
Це не кількість унікальних PHP-методів. Повторні combined/reversed не
пораховані вдруге як нове покриття.

## Локальний CI-контракт

Три smoke workflows тепер викликають захищений Python runner замість Artisan
parent, який міг раніше завантажити робочий environment. Public workflow також
окремо запускає runtime/observer regressions і шість Python перевірок runner.
Permissions лишилися
`contents: read`; event/branch filters не розширені. Composer smoke aliases
викликають PHPUnit напряму, його bootstrap також ізольований.

Із [ContentOps script](../../scripts/contentops-ci-preflight.sh) окремо виконано
всі 16 наявних PHP-файлів — **49 tests / 162 assertions PASS**, 16,250s,
0 failures/errors/skips, порожній stderr. Усі 49 method IDs із HEAD збережені.
Чотири shell cases мають окремий PHP-процес і fail-closed mock конструктора
Symfony Process; service mocks не дозволяють реального git update/deploy.
П'ять fixtures задають synthetic API/backend/CI-gate налаштування без
успадкованих GitHub credentials, чотири dashboard fixtures мають мінімальну
порожню recent-runs таблицю. Assertions не видалялися й не послаблювалися.

Повний operational preflight **не запускався**: він містить `migrate --force`,
запис sync cursors через tinker, doctor-with-git та release aggregation.
Це суперечило б межам етапу. Вказаний у ньому optional
`tests/Feature/DeploymentContentLockGateTest.php` відсутній ще у HEAD;
його не створювали для формального рахунку і жоден наявний тест не пропустили.
GitHub Actions не запускалися/не оголошуються зеленими; локальна перевірка
відбулася на Windows, PHP 8.2.12 / Node 22.15.0, тоді як workflows використовують
Ubuntu, PHP 8.2 / Node 20. Production/deploy workflow dispatch не виконувався.

### Додатковий діагностичний збій CLI OPcache

Перший ContentOps запуск зберіг 49 tests / 147 assertions, 3 errors і exit 2;
46 методів завершилися. Це окрема категорія В, не нова помилка навчального UI.
Три IDs (префікс `Tests\Feature\`):

- `DeploymentContentCiStatusTest::test_shell_ci_status_endpoint_returns_json_payload`;
- `DeploymentContentDoctorTest::test_shell_doctor_endpoint_returns_json_payload`;
- `DeploymentContentGateTest::test_shell_deploy_is_blocked_before_git_update_starts`.

Помилки виникали до Laravel bootstrap: наступний PHPUnit child посилався на
вже видалений serialized configuration файл першого child. Мінімальна проба
без застосунку довела механізм: два різні PHP STDIN jobs `A`, `B` при
`opcache.enable_cli=1` повертали `A`, `A`; при child startup `-d
opcache.enable_cli=0` — `A`, `B`. Parent-only `-d0` не виправляв grandchildren.
PHPUnit 10.5.63 передає свої ізольовані jobs через STDIN.

Runner створює **лише у приватному runtime** additive ini override з
`opcache.enable_cli=0` і передає його через `PHP_INI_SCAN_DIR` у дерево тестових
процесів, зберігаючи існуючі/default scan paths. Preflight перевіряє
`opcache_enable_cli=false`; unit regression перевіряє успадкування override.
Робочий `php.ini`, vendor, Apache OPcache і середовище браузерних вимірювань
не змінені. Відновлення видалених тимчасових PHPUnit конфігів не використано.

Фінальний CI49 preflight і after-hash PASS: 46 735 files / 0 changes,
aggregate SHA-256 знову `4fdd2df2c3e5d448f008b8523a1bef9400e3759edd5d299d84e6b27628a51efb`.
Приватний доказ: `m31-contentops-contract-final-bcb956fe07da4847a1a79aef22252508-result.json`.

### Джерело й класифікація початкових 22 випадків

Приватний M3 raw `m3-smoke-result.json` містить 40 tests / 342 assertions /
22 failures, child exit 1. Нижче `500/200` означає точне повідомлення
`Expected response status code [200] but received 500.`; `302/200` — аналогічно
зі статусом 302. Для 19 compiled-view випадків відповідний stderr містить
`filemtime(): stat failed for …/framework/views/<hash>.php`; повні приватні
шляхи не потрібні для розуміння причини.

А — fixture/schema; Б — isolation/compiler/environment; В — diagnostic runner;
Г — application regression; Д — невстановлено. Помилка cp1251 — окрема В,
не додатковий PHPUnit failure і не заміна класифікації решти 22.

Усі IDs нижче мають префікс `Tests\Feature\`; namespace після нього збережено.
Статус остаточно звіряється з JUnit окремого, combined і reversed запусків.

| № | Test ID після спільного префікса | Початкове повідомлення | Категорія / конкретна причина | Новий статус |
|---:|---|---|---|---|
| 1 | `PublicFlows\PublicTestRoutesSmokeTest::test_representative_public_test_routes_render_deterministically` | 500/200 | А: `no such table: text_blocks`; eager-loaded question theory relation відсутня у minimal schema | PASS окремо / разом / reverse |
| 2 | `Theory\TheoryCategorySmokeTest::test_every_top_level_theory_category_page_renders_in_all_locales` | 500/200 | Б: compiled-view `filemtime/stat`; після ізоляції А: HTML 200 не містить застарілого direct lesson link | PASS окремо / разом / reverse |
| 3 | `Theory\TheoryIndexSmokeTest::test_theory_index_pages_render_all_top_level_categories_in_stable_order` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 4 | `Theory\TheoryLocaleLeakageTest::test_selected_english_theory_pages_do_not_show_obvious_locale_leakage` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 5 | `Theory\TheoryLocaleLeakageTest::test_selected_polish_theory_routes_do_not_show_known_stale_strings` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 6 | `Theory\TheoryOverlapDistinctionTest::test_overlap_sensitive_pages_keep_distinct_learner_facing_identity` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 7 | `Theory\TheoryRepresentativeLessonSmokeTest::test_representative_lessons_render_in_all_locales` | 500/200 | Б: compiled-view `filemtime/stat`; після ізоляції А: старий fixture route дає 404 замість 200 | PASS окремо / разом / reverse |
| 8 | `Theory\TheoryRepresentativeLessonSmokeTest::test_selected_category_pages_expose_expected_lesson_links` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 9 | `Theory\TheoryRepresentativeLessonSmokeTest::test_selected_lesson_pages_expose_sibling_navigation_labels` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 10 | `AdminFlows\AdminAccessControlTest::test_valid_admin_login_redirects_to_the_intended_admin_route` | `Failed asserting that null matches expected true.` | Б: inherited plaintext admin password має пріоритет над fixture hash; явна конфігурація й synthetic precedence regression | PASS окремо / разом / reverse |
| 11 | `AdminFlows\AdminDashboardSmokeTest::test_admin_dashboard_renders_for_authenticated_admin` | 302/200 | А: fixture очікує shell dashboard, але не задає backend; API-mode робить штатний redirect | PASS окремо / разом / reverse |
| 12 | `AdminFlows\AdminSeedRunsSmokeTest::test_seed_runs_index_renders_for_authenticated_admin` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 13 | `AdminFlows\AdminSiteTreeSmokeTest::test_site_tree_management_page_renders_for_authenticated_admin` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 14 | `AdminFlows\AdminSiteTreeWriteTest::test_authenticated_admin_can_create_a_site_tree_item` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 15 | `AdminFlows\AdminSiteTreeWriteTest::test_authenticated_admin_can_update_a_site_tree_item` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 16 | `AdminFlows\AdminSiteTreeWriteTest::test_authenticated_admin_can_delete_a_site_tree_item` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 17 | `AdminFlows\AdminTestTagsWriteTest::test_authenticated_admin_can_create_a_test_tag` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 18 | `AdminFlows\AdminTestTagsWriteTest::test_authenticated_admin_can_update_a_test_tag` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 19 | `AdminFlows\AdminTestTagsWriteTest::test_authenticated_admin_can_delete_a_test_tag` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 20 | `AdminFlows\AdminTestsAndTagsSmokeTest::test_test_tags_page_renders_for_authenticated_admin` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 21 | `AdminFlows\AdminTestsAndTagsSmokeTest::test_saved_tests_page_renders_for_authenticated_admin` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |
| 22 | `AdminFlows\AdminTestsAndTagsSmokeTest::test_grammar_test_builder_renders_for_authenticated_admin` | 500/200 | Б: compiled-view `filemtime/stat` | PASS окремо / разом / reverse |

## Команди й відтворення

Запуски виконуються з кореня репозиторію. У цьому середовищі `php` означає
PHP 8.2.12 із локального XAMPP; `python` — Python із bundled runtime.
Для browser tools задавалися `PLAYWRIGHT_MODULE` і `CHROMIUM_EXECUTABLE` на
наявні Playwright 1.62.1 / Chromium 147.0.7727.15. Це тільки environment
діагностичних процесів, не зміни `.env` або конфігурації сервера.

Завершені build/unit команди:

```powershell
npm ci --no-audit --no-fund
npm run build
node tools/diagnostics/verify-public-build.cjs m31-source-only-build
npm test
node --test tests/Browser/cls-session-window.test.cjs tests/Browser/theory-sidebar-stability.test.cjs tests/Browser/layout-comparison.test.cjs tests/Browser/saved-test-persistence.test.cjs tests/Browser/state-request-classification.test.cjs
python -m unittest discover -s tests/diagnostics -p 'test_*.py' -v
```

Ізольоване PHP-приймання:

```powershell
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m31-accepted --matrix smoke --include-m2
```

Matrix створює окремі child runtimes для isolation, PublicFlows, Theory,
AdminFlows, combined, combined з `--order-by=reverse` і M1/M2. Точний перелік
M1/M2 файлів versioned у константах runner; команди та JUnit зберігаються для
кожного child. Повне before/after хешування охоплює весь batch.

Окремий локальний ContentOps CI-контракт:

```powershell
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m31-contentops-contract-final tests/Feature/ContentReleaseGateCommandTest.php tests/Unit/ContentReleaseGateServiceTest.php tests/Feature/ContentCiStatusCommandTest.php tests/Unit/ContentOpsCiStatusServiceTest.php tests/Feature/ContentCiDispatchCommandTest.php tests/Unit/ContentOpsCiDispatchServiceTest.php tests/Feature/ContentDoctorCommandTest.php tests/Unit/ContentOperationsDoctorServiceTest.php tests/Feature/ContentLockStatusCommandTest.php tests/Feature/ContentSyncStatusCommandTest.php tests/Feature/ContentHistoryCommandTest.php tests/Feature/DeploymentContentReleaseGateTest.php tests/Feature/DeploymentContentCiStatusTest.php tests/Feature/DeploymentContentCiDispatchTest.php tests/Feature/DeploymentContentDoctorTest.php tests/Feature/DeploymentContentGateTest.php
```

Новий baseline зафіксовано **до** layout edits командою
`node tools/diagnostics/public-layout-shifts.cjs m3-new-baseline`.
Повтор цього label навмисно заборонений: для нового запуску потрібен новий
label. Не можна відтворювати baseline на вже виправленому layout і називати
його результатом M3.

Окремі sidebar сценарії після правок:

```powershell
node tools/diagnostics/theory-sidebar-stability.cjs m31-after accept
node tools/diagnostics/theory-sidebar-stability.cjs m31-after-confirm accept --page=sentence-types --viewport=desktop
node tools/diagnostics/theory-sidebar-stability.cjs m31-after-collapse accept --page=sentence-types --viewport=desktop --scenario=saved-collapsed-delayed-alpine
```

Перший із цих запусків мав exit 1 і збережений як проміжний результат;
фінальне покриття та незакрите transient-спостереження пояснені вище.

Функціональні browser/HTTP команди (кожна завершилася exit 0):

```powershell
node tools/diagnostics/public-assets-interactions.cjs m31-final
node tools/diagnostics/public-assets-renderers.cjs m31-final
node tools/diagnostics/public-assets-http.cjs m31-final
node tools/diagnostics/local-state-browser.cjs m31-final
python tools/diagnostics/local-seo-smoke.py --label m31-final
```

Це не паралельний batch з performance. Перші три команди мають зазначене
вище обмеження доступу до Google Fonts у sandbox; воно не замовчується.

Фінальне порівняння та незалежна перевірка (exit 0):

```powershell
node tools/diagnostics/public-layout-shifts.cjs m31-after-network-parity
node tools/diagnostics/verify-layout-comparison.cjs storage/app/seo-m3-1-local/m3-new-baseline-perf.json storage/app/seo-m3-1-local/m31-after-network-parity-perf.json
```

Контрольна перевірка зупиненого font-denied прогону навмисно повертає exit 1:

```powershell
node tools/diagnostics/verify-layout-comparison.cjs storage/app/seo-m3-1-local/m3-new-baseline-perf.json storage/app/seo-m3-1-local/m31-after-final-perf.json
```

Після performance, з доступними штатними шрифтами (усі exit 0):

```powershell
node tools/diagnostics/public-assets-interactions.cjs m31-font-confirm
node tools/diagnostics/public-assets-renderers.cjs m31-font-confirm
node tools/diagnostics/public-assets-http.cjs m31-font-confirm
```

## Залишкові обмеження та поза межами етапу

- Точна причина одиничного початкового post-paint overlap усередині sidebar
  не доведена. Пізніші marker-confirmed сценарії чисті; початковий raw не
  відкинуто й не перепозначено як успіх. Великий зовнішній sidebar/TOC стрибок
  має окреме підтверджене виправлення.
- Лабораторні mobile `hadRecentInput` flags не переписуються. Нуль стандартного
  CLS не доводить нуль фізичних змін геометрії; немає польових CWV або Lighthouse
  оцінок, які не запускалися.
- Mobile cold LCP зріс на 112 ms у Present Perfect та 28 ms у Sentence Types.
  Причинність не доведена; цей результат не відкидається й потребує окремого
  контрольованого дослідження перед заявою про повне performance-приймання.
- Старі порожні legacy-картки **One/Ones** зі звіту M3 лишаються окремою задачею;
  вони не були причиною цих smoke-failures і масово не змінювалися.
- Operational ContentOps preflight, GitHub Actions, production/SSH/deploy,
  робочі міграції/сідери/очищення кешу не виконувалися. Локальна перевірка
  команд CI не є твердженням про зелений remote Actions run.
- Production `.com` canonical/robots перевірені лише як метадані та в
  ізольованому локальному kernel; це не аудит фактично розгорнутого сайту.

Невдалих початкових PHP smoke-тестів або файлових побічних ефектів після
виправлення не залишилося. Публікується перевірена частина стабілізації;
одиничне непояснене внутрішнє transient-спостереження не оголошене закритим.

## Безпека публікації

Перед commit явно вибрано 46 пов'язаних файлів; staged diff і
`git diff --cached --check` перевірено. Staged inventory точно збігається
з allowlist; `.env`, storage/runtime, dumps, screenshots, PPC і `.codex/`
відсутні. Скан доданих рядків на provider tokens, private keys і session
material не знайшов збігів. Hooks не обходилися.

Перед push `git ls-remote --heads origin` підтвердив незмінний main
`c77b4326a92b2c1e92c80b07393d8e7000c0fe33` і відсутність зайнятої remote-гілки
`codex/seo-m3-1-cls-smoke-hardening`. Усі чотири workflows переглянуті:
цей branch push не запускає жодного з них; permissions/triggers не розширені.
Публікація обмежена робочою гілкою, без PR, merge, main push чи deploy.
SHA цього versioned стану доступний у GitHub commit metadata; відповідність
local HEAD і remote ref перевіряється після push та наводиться у підсумку.
