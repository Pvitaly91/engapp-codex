# M25 — єдиний дизайн теорії, етап 1

## Межі й робоча база

Перевірка та застосування виконані тільки для `http://gramlyze.loc`, document root
`D:/DEV/htdocs/gramlyze.loc`. База гілки — M24
`7307750ea503731499fd7d91f04be6442fe3b4c4`; робоча гілка
`codex/seo-m25-unified-theory-design`. Окремий вільний worktree використано через
сторонні незавершені зміни основного checkout; вони не скидались і не комітяться.

Затверджений HTML-референс перевірено за SHA-256
`e6f08194839ae7260c1c91fdbea60edb7405e675a74de452942e0792054fbc6c`.
Перенесено візуальне рішення, але не демонстраційні пояснення, вправи, банер,
`template` чи штучне «підвантаження» з референсу.

Це render-only зміна. Definitions, authored masters M23/M24, manifests,
навчальні HTML/JSON, IDs, UUID, sort order і зв’язки не редагувалися.
БД використовувалась лише для SELECT-інвентаризації. Не виконувались міграції,
сідери, content apply, очищення кешу, зміни `.env`, PHP, Apache чи hosts.
Production `.com`/`.ub` не перевірявся і не змінювався.

## Реалізація

- Scoped `theory-unified-design.css`: спокійні картки, компактний вступ і
  заголовки, авторські номери без дублювання, приклади/переклади, спільні
  light/dark tokens, локальне горизонтальне прокручування таблиць, focus,
  print і reduced motion. Header, logo, фон, пошук і каталоги не редизайнено.
- `TheoryPresentation` адаптує вже дозволений HTML для оформлення та стабільних
  anchors. Це **не sanitizer**; inline-поля залишають чинний `TheoryInlineHtml`.
  Пошкоджений HTML повертається до початкового дозволеного renderer без обрізання.
- Єдиний `theory/partials/content-block` із code-owned whitelist native views
  використовується theory, category description і course renderer. Відмінність
  списків M24 усунута: `lesson-rule-cards` та `tense-forms-table` перевірені також
  через course fixtures. Довільні назви Blade views з БД не виконуються.
- `theory-native-header` зберігає авторські `1.`/`1)` у badge; не трактує B1/C2
  чи числа всередині назви як номер. Прибрано дубльовані `block-*` IDs, які раніше
  одночасно належали native view і зовнішній обгортці.
- Реальний зміст уроку на desktop і компактний native зміст на mobile
  співіснують із чинною картою категорій. Навчальний DOM не дублюється.
- `<x-theory-section>` / `TheorySection` готові до наступного явно погодженого
  авторського пакета; `theory-sections.js` лише покращує fragments, history та друк.

**42 унікальні уроки M11–M24 отримали оформлення, але не короткі редакції.**
Усі інші охоплені уроки також зберегли повний нинішній матеріал. Нових кнопок
«Докладніше» на справжніх уроках M25 немає. Існуючі відкривні ключі вправ
збережені разом із початковим станом.

## Матриця інвентарю

Read-only інвентар: 254 уроки, 41 категорія; 35 категорій мають навчальні блоки,
6 — ні. 6 262 фактичні блоки uk/en/pl, 767 owner/locale fixtures
(289 uk, 239 en, 239 pl). Всі 254 уроки зіставлені з versioned sources.
Versioned coverage: 254 lesson definitions + 35 category definitions.
Кількість блоків нижче включає всі фактичні локалі; owners — унікальні уроки
або категорії, тому стовпчики не слід підсумовувати як унікальні сторінки.

| Тип | Блоки уроків / owners | Блоки категорій / owners | Renderer / перевірка |
| --- | ---: | ---: | --- |
| hero | 658 / 252 | 91 / 31 | чинний hero у theory/course/category; inventory fidelity |
| subtitle | 661 / 253 | 103 / 35 | чинний intro/subtitle шлях; inventory fidelity |
| box / rich / legacy HTML | 439 / 83 | 129 / 11 | TheoryPresentation + theory-rich-box; HTML/source/actual fidelity |
| forms-grid | 517 / 165 | 19 / 7 | native registry, lesson-rule-cards widget; native/source/actual fidelity |
| lesson-rule-cards | 0 / 0 | 36 / 12 | спільний native registry; category + course fixture |
| usage-panels | 1 134 / 168 | 69 / 18 | native registry; native/source/actual fidelity |
| comparison-table | 460 / 131 | 18 / 6 | native registry, локальний table scroll; native/source/actual fidelity |
| mistakes-grid | 519 / 171 | 0 / 0 | native registry; native/source/actual fidelity |
| summary-list | 565 / 187 | 79 / 26 | native registry; native/source/actual fidelity |
| practice-set | 178 / 62 | 0 / 0 | незмінена логіка вправ, presentation hooks; practice/native fidelity |
| navigation-chips | 584 / 209 | 3 / 1 | чинна навігація; href/source/actual fidelity |
| hero-v2 / null-type / tense-forms-table | не знайдені в робочих записах | не знайдені | лише ізольовані compatibility fixtures, не заявлені як live coverage |

Live прив’язка матриці: hero/subtitle/navigation-chips та forms-grid,
usage-panels, comparison-table, mistakes-grid, summary-list і practice-set —
[Present Perfect Forms](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms);
lesson-rule-cards — [Present Simple category](http://gramlyze.loc/theory/present-simple);
rich HTML — [Linking Words](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast);
legacy box — [Reported Statements](http://gramlyze.loc/theory/reported-speech/reported-statements).

Для rich/legacy, native і category приклади URL наведені нижче. Наявність типу
в registry не підміняє доказ його використання в реальній БД. Навчальні category
описання окремих локалей відрізняються від definitions, тому вони додатково
перевірені за незалежними фактичними записами, а не лише sources.

## Незалежне збереження вмісту

Початкові HTML-відповіді і views збережені **до застосування M25**. Порівняння
не використовує новий результат як власний expected baseline. Нормалізуються
тільки пробіли звичайного block-flow та implementation UI з `data-theory-ui`.
Пунктуація, заперечення, числа, переклади і ключі не відкидаються.

Окремо перевіряються ordered text, href, старі anchors, таблиці по рядках і
клітинках, запитання/ключі, початкові `details` states, H1, title, description,
canonical, robots, JSON-LD і ordered sitemap. Negative comparator tests
спеціально відхиляють втрату заперечення/числа/крапки/ключа/anchor,
перестановку клітинок, duplicate IDs, SEO-зміни й нові live disclosures.

Початковий комбінований content fingerprint БД:
`3b2edd79a8cef2715907027d17e6d0166261ded7b6cc5c03bdba10967bf4856c`.
Початковий sitemap: 554 ordered URL, SHA-256
`6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.
554 — виміряний результат цього запуску, не hardcoded правило застосунку.

Після застосування комбінований content fingerprint залишився тим самим.
Canonical live HTTP: **292/292 GET → 200**, **292/292 strict comparisons → PASS**:
254 уроки + 35 навчальних category URL + незмінений test/course/sitemap controls.
Оригінальний інвентар спочатку містив 17 помилково вкладених candidate category
URL із 404. Це помилка діагностичного складання адрес, не регресія застосунку:
category route використовує leaf slug. Canonical baseline зібрано виключно
з незалежних початкових GET та 35-category supplement, не з нового output.
Фінальний sitemap має ті самі 554 URL у тому самому порядку і той самий SHA.

Окреме ізольоване порівняння до/після: **1 974/1 974 rendering scopes — PASS**:
508 source theory/course, 35 category definition, 1 431 actual-record
theory/course/category scopes. Фактичні записи охоплюють 664 lesson/locales
(254 uk, 205 en, 205 pl) та 103 category/locales (35 uk, 34 en, 34 pl).
Перевірено **9 298 авторських native headers** за незмінним source title;
помилок або непогоджених різниць немає.

Авторські native headings порівнюються з точним незмінним title із джерела:
старий renderer відкидав leading `N.` або витягував цифри B1/C2 як UI badge.
Виправлене представлення повертає повний авторський номер/пунктуацію.
Дозволена лише ця конкретна перевірена UI-різниця, не blanket видалення чисел
чи пунктуації з уроку. Старі `block-*` anchors підзаголовків збережено порожніми
унікальними alias targets; intro повторно не вставляється.

П’ять **попередньо пошкоджених** native summary-list JSON залишено без запису
в sources/БД та без довільного ремонту JSON. Вони мають той самий порожній
native fallback, tags/practice/anchor, що й до M25, і технічний
`data-theory-render-fallback="invalid-native-data"`. Сирий JSON не стає абзацом
теорії. Це обмеження давніх даних, не нові короткі редакції:

- [Bare Infinitive](http://gramlyze.loc/theory/verb-patterns/bare-infinitive), uk block 5760;
- [Stop / Remember / Forget / Try / Regret](http://gramlyze.loc/theory/verb-patterns/stop-remember-forget-try-regret), uk 5844 і pl 5858;
- [Verbs + Gerund](http://gramlyze.loc/theory/verb-patterns/verbs-plus-gerund), uk 5886;
- [Verbs + Infinitive](http://gramlyze.loc/theory/verb-patterns/verbs-plus-infinitive), uk 5907.

Їхнє майбутнє контентне виправлення потребує окремого погодженого scope;
M25 не приховує причину fallback і не змінює невалідні payloads мовчки.

## Автоматичне приймання

Ізольований runner: PHP 8.5.10, SQLite `:memory:`, приватні compiled views,
без testing connection до робочої MySQL. Фінальний основний набір:
**143 tests / 21 968 assertions — PASS**. Окремий inventory capture:
**3 tests / 5 105 assertions — PASS**, 254 source lessons, 35 category definitions,
767 actual owner/locale fixtures, всі 6 262 блоки.
Vitest: **15/15 PASS**; negative fidelity comparator: **13/13 PASS**.
Повторні запуски під час розробки не додаються до цих фінальних counts.
Після останньої зміни UI-підпису змісту та CSS окремо повторено
34 presentation/component tests / 19 134 assertions — PASS;
15 JS/CSS та 13 negative fidelity tests також повторно пройшли.
Останній повний набір включає додатковий regression dataset:
колізія main/detail ID з generated summary control ID → full-source fallback.

Основні файли тестів: UnifiedTheoryPresentationTest,
UnifiedTheoryNativePresentationTest, TheorySectionRenderingTest,
TheoryRichContentTest, TheoryInlineHtmlRenderingTest, M25TheoryInventoryFidelityTest,
M25CategoryInventoryFidelityTest, M25ActualRecordFidelityTest;
SEO/canonical/robots/course metadata та обидва SavedTestJsState suites включені.
`tests/js/theorySections.test.js`, `unifiedTheoryDesign.test.js`, `publicAssets.test.js`
і `tests/Browser/seo-m25-fidelity.test.cjs` перевіряють JS/CSS та сам comparator.

Є **одна наявна PHP 8.5 deprecation** `PDO::MYSQL_ATTR_SSL_CA` у
`config/database.php:62`; M25 не змінює PHP/database configuration.
Нових PHP fatal/warning у готових локальних HTTP-відповідях не знайдено.

Ізольований paired-disclosure Chromium fixture: **4/4 PASS**
(1280/390 px × JS/no-JS), 8 screenshots переглянуто.
Enter/Space/Tab, незалежні теми, вкладений ключ, unique IDs, deep anchors,
Back/Forward з JS, видимий print explanation з JS і без JS, повернення
початкового open state після друку, reduced-motion 0s — пройшли.
Chromium відкривав deep-fragment ancestors і без JS; no-JS control link також
доступний. Console/page errors і document overflow відсутні.
Це route-fulfilled fixture тільки в browser runner, **не** endpoint сайту,
не live короткий урок і не обхід course gate.
SSR fixture після останнього DTO guard байт-ідентичний прийнятому:
3 906 bytes, SHA-256
`affb705084d613a38eb2da371530916072ceec38600b143223ba071ddc21f454`.

## Frontend build і локальний apply

`vite build --outDir storage/app/seo-m25-build --emptyOutDir false` успішний,
56 modules; залежності та lockfiles не оновлювалися. Фінальний bundle:

- `catalog-public-CesOy8M-.css`: 116.00 kB, gzip 19.47 kB;
- `catalog-public-blpGjyje.js`: 10.85 kB, gzip 3.78 kB;
- `app-BtBPfOXL.css` і `app-DNxiirP_.js` також скопійовано як частину manifest;
- SHA-256 manifest: `d2943aa101da41c998ebab06246ace3b1530aea7c290ccf3f88979226f47d5d3`.

24 implementation-файли адресно застосовані через patch після перевірки,
що наявні 16 файлів збігаються з M24 backup, а 8 нових ще не існують.
Manifest і всі 4 referenced assets перевірено за hashes та скопійовано разом;
попередні assets не видалялись. Main implementation збігається з worktree.
`.env`, composer/package files та всі 4 старі referenced assets байт-незмінні.
Остаточний proof: **24/24 source paths** main↔worktree (тільки CRLF→LF)
та **5/5 manifest/assets** byte-exact ↔ погоджена private build.
Приватний `final-guard-applied-files.json` фіксує адресні hashes;
aggregate 29-row SHA-256
`4637c71d6f236d43c18420fe7cba4afad2d583dbf61a88feee673e42d39ad77a`.
Байт-ідентичні assets повторно не перезаписувались. Windows утримував mapped
manifest відкритим, тому останню заміну виконано через адресне перенесення
попереднього manifest у приватний backup і встановлення нового; Apache/PHP
не перезапускались, налаштування й кеші не змінювались.

Browserslist повідомив про стару caniuse-lite базу; її не оновлювали.
Проміжний діагностичний selector спричинив Tailwind auto-scan warning;
selector literals виправлено, **фінальна збірка без CSS syntax warnings**.
Це не заміна asset verification або перевірки справжніх сторінок.

## Справжні representative URL

Кожна адреса перевіряється на реальному локальному сервері, без підміни HTML:

- [Linking Words (M11)](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast)
- [Nominal Style (M23)](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density)
- [Inversion (M12)](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics)
- [Hedging](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language)
- [Present Perfect vs Present Perfect Continuous (M24)](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous)
- [Narrative Tenses (M24)](http://gramlyze.loc/theory/tenses/narrative-tenses)
- [B1 Mixed Revision (M24)](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision)
- [Past Simple vs Past Continuous, старий урок](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous)
- [Present Perfect Forms, native](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms)
- [Reported Statements, legacy HTML](http://gramlyze.loc/theory/reported-speech/reported-statements)
- [Present Simple, category description + lesson-rule-cards](http://gramlyze.loc/theory/present-simple)

Додаткові контролі: [незмінений тест](http://gramlyze.loc/test/future-perfect/questions),
[гостьовий course gate](http://gramlyze.loc/courses/english-grammar-theory/lesson/tenses/narrative-tenses).
Запити зміни стану/прогресу блокуються діагностичним runner; persistence
приймається ізольованими автоматичними тестами, не реальними answer POST.

## Браузерне приймання та межі

Фінальна збірка `CesOy8M-`: **11 унікальних representative theory/category
сторінок, 33/33 viewport-сценарії, 66/66 light/dark checks — PASS**.
Ширини: 1440, 390 і 320 px; top/middle/bottom, long headings,
sidebar/category search/menu, TOC anchors/focus/collapse, theme + reload,
existing answer disclosures, таблиці й native-практика. Заголовки змісту
уроку та карти категорій тепер різні. Реальних нових «Докладніше» немає.

Збережено **232 final screenshots**; переглянуто representative top/middle/
bottom кадри всіх 11 сторінок і focused table/practice feedback.
На dark-вправах реально обрано A, виконано «Перевірити», видно selected
answer/ring, результат та correct/missing feedback; retry і token bank читабельні.
Багаторядкові code examples мають цілу межу; широкі таблиці прокручуються
всередині локального wrapper, а не обрізають дозволені відповіді.

Окремі 17 controls: 6 no-JS (3 уроки × desktop/mobile), 3 zoom-layout,
незмінений тест, live guest course gate, 6 focused native dark scenarios.
**16/17 повних controls пройшли; raw browser report має pass=false** через
одне застереження нижче. Повторні діагностичні проби не додаються як нове покриття.

При еквіваленті 200% layout (720 CSS px, DPR 2; не UI Ctrl+zoom) для
Nominal Style один запуск зафіксував document width 727 px при viewport 720.
Unclipped offenders — порожні декоративні `SPAN` у `#shell-random-shapes`,
не навчальний матеріал. Три додаткові fresh GET дали 730/725/720 px:
результат залежить від випадкових фігур старого фону. Навчальна таблиця
ширша за viewport, але має `overflow-x:auto` ancestor і не розширює документ.
M25 не змінює layout або generator/styles цього фонового шару; поріг
overflow не послаблювався, початковий failed control збережено.
Фон не ремонтували поза погодженим presentation scope.

Додаткова діагностика `zoom-learning-proof-m25-20261001-browser.json`:
9/9 повторних **live learning-layout** перевірок (3 уроки × 3) пройшли;
два повтори мали лише декоративний document overflow. Це локалізація
попередження, не дев’ять нових representative сторінок або повторне
performance-приймання. Три вибрані background source fragments
(layout, generator JS, CSS rules) мають однакові hashes на M24 SHA,
worktree та main. Висновок про незмінне джерело — code proof.

Окремі три replay-проби frozen before HTML **не пройшли**: synthetic
route.fulfill response спричинив Chromium PNA/CORS address-space refusal
для old CSS/JS: insecure request client → more-private loopback address space.
Security не вимикалась. Це не live-site відмова й не доказ before-геометрії;
історичне відтворення цього конкретного 200% випадку **не підтверджене**.
Оригінальні live before screenshots/HTTP та дев’ять before performance
samples залишаються незалежними чинними доказами, replay їх не замінює.

У shell також залишився попередній duplicate SVG `id="grad"` header/footer.
Це не duplicate learning anchor: learning DOM перевірено окремо, old IDs
збережено й нових дублів немає. Наявний logo/layout не змінювали.

Чинний live course gate зберігся: навчальна курсова копія доставляється
SSR, але прихована гостьовим UI gate. Before HTML proof і gate wrapper/
progress code незмінні; before browser visibility окремо не вимірювалась.
Це не нова гарантія server-side secrecy. Відкритий course renderer перевірено
тільки дозволеними ізольованими fixtures, live gate не обходився.

## Лабораторний performance-контроль

Chromium 147.0.7727.15, desktop 1440×900, по три нові ізольовані contexts
для кожного URL до/після; cache вимкнено routing, без взаємодії,
5 секунд observation. Жодних паралельних важких тестів або build під час
остаточних дев’яти вимірювань. TTFB — responseStart − requestStart;
LCP і CLS session-window отримані реальним PerformanceObserver.

| Урок | TTFB median, ms до → після | LCP median, ms до → після | CLS median до → після |
| --- | ---: | ---: | ---: |
| Past Simple vs Past Continuous | 497.2 → 387.9 | 736 → 704 | 0.012289 → 0.005560 |
| Narrative Tenses | 396.3 → 302.1 | 600 → 612 | 0.001561 → 0.004876 |
| Nominal Style | 315.9 → 361.9 | 568 → 632 | 0.004528 → 0.001561 |

Не відкинуто холодний перший before LCP 4 116 ms для Past Simple;
медіани — опис трьох збережених samples, а не обіцянка прискорення.
До/після recorded samples не підмінялись повторними вдалішими запусками.
Дані не є польовими Core Web Vitals або Lighthouse-оцінками.

Public CSS: **105 506 → 115 996 bytes**, public JS: **9 531 → 10 850 bytes**;
число public bundle CSS/JS requests залишилось 2. Навчальні DOM-елементи
не дублюються для mobile/desktop; TOC повторює лише заголовки/посилання.
Новий presentation adapter і TOC працюють із уже завантаженою колекцією;
не додано SQL/fetch/full-bank loading для змісту. SQL query count окремо
не профілювався — це висновок із code diff, не вигаданий SQL benchmark.

У before та final after фактично відмовив зовнішній
`fonts.googleapis.com/css2`: **net::ERR_NETWORK_ACCESS_DENIED**.
Fallback fonts залишено природними; мережеву відмову не видаємо за app JS error.
Локальні document/assets/navigation GET мають 200, page errors відсутні.
Для no-JS Chromium очікувано відхиляє script resource через script-disabled
policy; це окремо записано, не приховано як довільне network exception.

Приватні raw performance докази: `before-m25-20261001-browser.json` та
`after-accepted-m25-20261001-browser.json` у
worktree `storage/app/seo-m25-browser-local/`. Before почався
2026-10-01T19:40:50.833Z; остаточний after — 2026-10-01T20:35:04.599Z.

## Готовність наступного етапу

Контракт: [theory-summary-detail-contract.md](../content/theory-summary-detail-contract.md).
Автор передає готовий main, стабільний section key/heading/number, впорядковані
refs до незмінних details і точний source SHA-256. Codex не генерує скорочень.
Відсутня/порожня/пошкоджена/застаріла пара показує весь початковий дозволений
текст без кнопки й без 500, із технічним fallback code.

Native `details/summary` обрано замість click-only lazy text: обидва дозволені
шари є звичайним DOM у початковій SSR-відповіді, controls працюють без JS.
Це узгоджується з [WHATWG details](https://html.spec.whatwg.org/multipage/interactive-elements.html#the-details-element)
і вимогою Google не залежати від взаємодії для виявлення контенту
([lazy-loading](https://developers.google.com/search/docs/crawling-indexing/javascript/lazy-loading),
[mobile-first](https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing)).
Компонент не є новим access-control і не змінює чинний course gate.
Paired behavior приймається тільки на ізольованому fixture, не live-уроках.

## Backup / rollback

Приватні резервні копії до адресного локального apply:

- `storage/app/seo-m25-local/code-before/` — 16 змінених наявних implementation-файлів;
- `storage/app/seo-m25-local/assets-before/` — повний попередній `public/build`;
- `storage/app/seo-m25-local/before-protected-files.json` — fingerprints `.env`,
  package/composer files, manifest та referenced assets без самих секретів;
- `storage/app/seo-m25-local/final-guard-applied-files.json` — точні 24 source
  paths + 5 generated files, hashes погодженого застосованого стану;
- `storage/app/seo-m25-local/` і worktree `storage/app/seo-m25-browser-local/` —
  приватні read-only докази; не включені до Git.

Для відкату адресно повернути 16 implementation-файлів із code-before,
прибрати лише 8 нових M25 implementation-файлів після перевірки їхніх hashes
та повернути погоджений manifest + referenced assets із assets-before.
Не виконувати reset/clean, не видаляти весь `public/build`, не чіпати сторонні
файли або `.env`. Навчальну БД відновлювати не потрібно: вона не змінюється.
Новими файлами M25 є TheoryPresentation, TheorySection, scoped CSS/JS,
theory-native-header, theory-section, content-block і lesson-toc.

## Git і workflow scope

До коміту входять лише M25 implementation, UI-переклади, тести, діагностичні
інструменти, цей звіт і контракт. Не входять vendor/build, runtime caches,
screenshots, snapshots фактичної БД, backups/dumps, `.env` або сторонні зміни.
Поточні чотири workflow перевірено: push-trigger лише для main, PR-trigger
лише для PR у main, ручний dispatch не викликається. M25-гілка не запускає
production deploy; permissions/triggers не змінювались. PR не створюється.
