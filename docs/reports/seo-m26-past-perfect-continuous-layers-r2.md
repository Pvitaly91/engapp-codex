# Gramlyze M26-R2 — Past Perfect Continuous: basic + exact author detail

Дата: 2026-10-02. Зміни застосовано до робочого `http://gramlyze.loc`.

Подальше погоджене оновлення перенесло «Докладніше» всередину карток та замінило статичну практику інтерактивною. Поточний результат описано в [звіті follow-up](seo-m26-past-perfect-continuous-interactive-followup.md); нижче збережений історичний результат первинного перенесення.

Пакет і перевірки його контенту завершені. Функціональна браузерна матриця пройдена, **але повне layout-приймання не є зеленим**: незалежно від M26 відтворено mobile-overflow старого фонового декору. Це обмеження не приховано й не виправлялося всупереч забороні змінювати background/global catalog.

## Версія та погоджені джерела

- Фактичний starting SHA: `32409982b819d1596dea802ab2857e79216e296e`.
- Робоча гілка: `codex/seo-m26-past-perfect-continuous-layers-r2`; повторно використано чистий worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`.
- Реальний Apache application root: `D:/DEV/htdocs/gramlyze.loc`. Його сторонні незавершені зміни не включалися до commit; reset/stash/clean не виконувалися.
- Author master SHA-256: `2aeb5d65b07f05dc540e8d35fb6779a8775ac8f364a1b906d2d93a4cdac891d0`. Versioned копія та файл користувача byte-identical.
- Повний before-manifest п’яти definitions SHA-256: `d4328511e38d9ff10edfb98158e5fd18ebef04aefa448d2a1bacf0c93d9b6aa4`.
- Очікуваний approved Negatives preview SHA-256: `b48419772e00939aba70fdc8c6c085120a3d4ededffa632ecc1ddba7f1fcfb5f`. Файл недоступний; користувач окремо дозволив продовжити з коректним master. Pixel-parity з відсутнім preview не заявляється.
- Старі `short_html`/replacement `body_json` не використовувалися. Авторські тексти не переписувалися. [Правила перенесення](../content/m26-past-perfect-continuous-detail-source-notes.md).

## П’ять фактично перевірених цілей

| Сторінка | Локальний URL | Basic native blocks | Detail | Завдання / ключі |
| --- | --- | ---: | ---: | ---: |
| Overview | [Past Perfect Continuous](http://gramlyze.loc/theory/past-perfect-continuous) | 3 | 2 | 0 / 0 |
| Forms and Use | [Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 4 | 3 | 6 / 6 |
| Negatives | [Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives) | 4 | 3 | 6 / 6 |
| Questions and Short Answers | [Questions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions) | 4 | 3 | 6 / 6 |
| Time Expressions | [Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) | 4 | 3 | 6 / 6 |
| Разом | | **19** | **14** | **24 / 24** |

Усі GET — новий гість, без cookie/Referer, HTTP 200. Overview-resolver фактично дає `/theory/past-perfect-continuous`; це не підмінено очікуваним nested slug.

## Реалізація та fidelity

- Усі existing native keys/values збережені; лише в 14 точних body додано `progressive_v2`. Hero, subtitle, basic-only summary/navigation, H1/title, taxonomy, locale, existing UUID/ID/sort order, tags/pivots не змінювалися.
- Для всіх 19 basic native blocks початковий SSR → після apply SSR: **повні DOM-піддерева збігаються**, поза `<details>`. Не використано скорочення чи bag-of-words замість повного контенту. Дозволена тільки whitespace/entity normalization.
- 14 exact `native_data` перенесено в author-bound detail з ordered SHA. Перевірено їхній порядок і текст у SSR та реальному Chromium після відкриття. Detail рендерить той самий code-owned finite native view, без другого HTML renderer/DB-selected view.
- Embedded mode не дублює зовнішній header/tags/practice controls; усі внутрішні labels/descriptions/examples/UA translations/rows/warnings/notes збережені.
- M25 disclosure markup спільний: native `<details>`, initial closed, локалізовані «Докладніше / Згорнути», контекст summary, server DOM. Немає fetch/template/spinner/detail injection.
- У чотирьох practice bodies прибрано тільки obsolete зовнішній disclosure «Відкрити 6 завдань». Усі 24 повні prompt/key піддерева дорівнюють master; завдання видимі одразу, кожна відповідь має свій disclosure.
- Invalid/foreign/edited metadata показує повний basic без нової кнопки.
- Реальні моделі виявили дві інтеграційні помилки до остаточного приймання: integer Eloquent ID cast для detail-проекції та HTML4 parser rejection штатного `@click` у тегах. Виправлено view-only проекцією та **окремим explicit native HTML5 parser mode**, без зміни basic DOM, ігнорування parse errors чи послаблення hash/reference/ID checks. Старий M25 validation mode незмінний. Додано регресії з реальним `TextBlock` і непорожніми tags.

## Адресне застосування до локальної БД

CLI/web runtime equality, hosts/vhost/Apache/MySQL physical guard підтвердили локальний Windows `gramlyze.loc`, Apache root та MySQL `gr2`. Існуючий production-профіль локального `.env` не редагувався; scope розблоковано тільки explicit local-target proof.

Final preview: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m26-local/html5-preview.json`; plan SHA-256 `868b2f4b40274fa4513991aa9010a919bbf4f383854a45a34f38f3b56023f6b9`.

Після final preview source bytes не змінювалися. Exclusive record-backup створено **до першого запису**; транзакція має allowlist рівно п’яти identities, exact before/source/owner/locale/UUID/type/position checks і postconditions.

- Фактичний результат final apply: **14 existing `text_blocks.body` updates + 4 new UK practice boxes** (`sort_order=7`). Category insert — 0. Інших полів/рядків не оновлено.
- Backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m26-local/record-backup-html5-20261002.json`. Не видалено й не додано до Git.
- Повторний apply: **no-op, updated 0, inserted 0**. `record-backup-noop-must-not-exist.json` не створено.
- До final apply невдалі інтеграційні спроби були адресно restored: лише власні 14 bodies/4 boxes. Restore пройшов ownership/relations/manual-state/postcondition checks; не застосовувався full DB restore.
- Без migrations, working seeders, truncate, mass update, кеш/session wipe чи автоматичних apply hooks. Тимчасовий nonce-bound SELECT-only runtime-proof route вилучено після no-op; він не входить до Git.

20 protected fingerprints збігаються до/після та під час no-op. Вони охоплюють усі pages/categories, 6 229 нецільових text blocks, tags/pivots, **44 460 questions**, answers/options/marker relations/hints/variants/saved tests/seed runs — включно з prior 42 lessons та Mixed banks, а не лише вибірковими прикладами. Aggregate digest цього набору: `0a7b6163bc1618745510972990a4966d3e30e019d4fa795b07caeee8dc15c02a`.

## HTTP, SEO та контролі

Title, description, H1, canonical, robots headers, статуси/redirects і чинні internal links порівняно до/після. Незмінні. Локальний профіль повертає `X-Robots-Tag: noindex, nofollow, noarchive`; canonical host лишився чинним `https://gramlyze.com` з точними відповідними paths. Це не production GET та не зміна production-конфігурації.

Один ordered sitemap before/after: **554 loc**, однаковий порядок; SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Full sitemap crawl не запускався.

Контрольні HTTP 200; learner content, IDs/links/metadata незмінні, нових M26 disclosures немає:

- [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous).
- [Present Perfect Forms](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms).
- [M24 Narrative Tenses](http://gramlyze.loc/theory/tenses/narrative-tenses).

Для Present Perfect текст random question-bank `<script>` закономірно варіюється; learner comparison виключає лише non-teaching script/style, не контент уроку. Незмінність банків окремо підтверджена DB fingerprints.

## Автоматичні тести та браузер

Дві вузькі isolated SQLite suites: **125 PHP tests, 20 639 assertions, 0 failures**:

- M26 package/patch + TheorySection + unified/native rendering: 50 / 20 027.
- M11/M12 physical guard + category smoke + canonical/course/sitemap metadata + prior authored native packages/patches: 75 / 612.

Негативні fixtures: втрата not/been/long/well/for/since/before/after/numbers/UA translation; reorder/drop author data; wrong owner/locale/category/type/order; stale source; malformed JSON; duplicate/colliding IDs; rollback; changed bank; foreign/manual relations before restore; no-op/backup/production refusal.

**32 JS tests пройшли**: theory sections, sidebar layout, navigation. Штатна Vite build з уже встановленими залежностями пройшла; manifest/assets не змінилися, manifest SHA-256 `0bf06ba1b59c2f5782f2f5348e2319c507058fd12eb32bf272b538250614286a`. Dependencies не оновлювалися. Full repository suite/Lighthouse/performance campaign не запускалися.

Реальний Chromium на `gramlyze.loc`, без fixtures/fulfill/setContent: **5 targets × 2 viewports (1440×1000, 390×844) × light/dark = 20 states**. Пройшли full basic visibility, initial closed, mouse/Enter/Space/focus-visible, independent disclosure states, SSR/live detail parity, immediate practice/separate keys, local table scroll, reload, deep fragment, native print-media open/restore. Додатково 5 no-JS contexts пройшли native open. Кліки disclosure не роблять мережевих запитів; page errors і unexpected local failures — 0.

Category → усі 4 lessons та lesson → main tests перевірені (local GET 200). [Course copy](http://gramlyze.loc/courses/english-grammar-theory/lesson/tenses/past-perfect-continuous/past-perfect-continuous-forms): HTTP 200, SSR 3 details, guest gate лишився locked/content hidden. Unlock/progress bypass не використовувався.

20 screenshots/evidence залишено лише локально в `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m26-local/`; desktop Negatives light і mobile Negatives dark також візуально оглянуто. Google Fonts: 20 окремо зафіксованих environment `net::ERR_NETWORK_ACCESS_DENIED`; fallback fonts використовувалися у screenshots.

## Обмеження та невиконані критерії

1. **Строгий document horizontal overflow = 0 не пройдено.** Desktop — 0; у 8/10 mobile theme states старі `#shell-random-shapes span` виходили на 1–17 px. Незалежна перевірка незмінної Present Perfect control також відтворила цей декор за viewport 390. Новий learning content не має unclipped overflow; tables scroll locally. Фоновий декор/global layout не змінювався. `strictDocumentOverflowPass=false` у browser evidence — це не замаскований зелений результат; окреме виправлення background потребує нового scope.
2. Старий глобальний SVG ID `grad` дублюється вже в before HTML. Набір дублів незмінний; усі нові M26 section/control/task IDs унікальні. Header/background не редагувався.
3. Відсутній approved Negatives visual preview; звірено exact author master, не pixel-parity з відсутнім файлом.
4. Вузькі PHP runs мають одну відому pre-existing PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA`; це не новий test failure.

Production `.ub`/`.com` не перевірявся й не змінювався. No deployment, PR, merge, force push або зміни main. До Git входять лише M26 versioned sources/support/patch/manifest/tests/notes/report; backup/evidence/screenshots/vendor/.env/runtime не входять.
