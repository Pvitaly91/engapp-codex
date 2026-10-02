# M28 — Cleft, Inversion, Advanced Fronting: локальне приймання

Дата: 2026-10-03. Зміни застосовано до робочого `http://gramlyze.loc`; production не перевірявся й не змінювався.

## База та межі

Прийнятий M27: `32f3ffd2059df357f7ccccadacbb2c2984a8fdb3`.
Перед роботою виконано fetch, перевірено HEAD, remote ref та ancestry: погоджений M27 і origin/M27 збігалися; новішого погодженого descendant не знайдено.
Робоча гілка: `codex/seo-m28-m12-emphasis-inversion-layers`, clean tracked worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`.
Основний checkout мав сторонні незавершені зміни; їх не скидали, не stash-или й не комітили.

Внесено тільки технічну проєкцію трьох accepted M12 definitions, власні point-level details та погоджену адаптацію self-check → practice.
Навчальні тексти не переписані, нових граматичних правил/explanations не згенеровано.
Синхронізація у робочий .loc виконана лише для цільових definitions і runtime-файлів; перед нею перевірено відсутність накладання на незавершені зміни. Попередні локальні публічні source-файли збережено приватно у `storage/app/seo-m28-local/preexisting-source`.

| Сторінка | Identity | Accepted Git blob |
| --- | --- | --- |
| [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | `Database\Seeders\Page_V3\SentenceStructure\CleftSentencesBasicsTheorySeeder` | `c393771d52f8a8c5afc96d21331de246e2c50477` |
| [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | `Database\Seeders\Page_V3\BasicGrammar\WordOrder\InversionBasicsTheorySeeder` | `3de7b63966769b5c347119ccf87ab5e61b7e48db` |
| [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | `Database\Seeders\Page_V3\BasicGrammar\WordOrder\AdvancedFrontingAndEmphasisTheorySeeder` | `a600bafd6db9632b1b76b6149311b79736e92f8c` |

Locale uk, type theory. Exact ancestry: Cleft → sentence-structure; обидва word-order уроки → basic-grammar → word-order.
Metadata, slug, level, subtitle, hero, tags та test relations незмінні.

## Native structure та basic layer

Було: subtitle, hero, один великий accepted box на кожну сторінку, статична самоперевірка.
Стало: ті самі subtitle/hero та 7/8/8 native sections відповідно. UUID та order першого старого box збережено.
Основні таблиці, правила, приклади, переклади, warnings, related links та повний Fronting cohesion paragraph видно одразу.

| Урок | Секція | Native type | Points | Own details |
| --- | --- | --- | ---: | ---: |
| cleft-sentences-basics | 1. Один факт, різні запитання до нього | comparison-table | 0 | 0 |
| cleft-sentences-basics | 2. It-cleft: форма та межі вибору that/who | usage-panels | 5 | 2 |
| cleft-sentences-basics | 3. What-cleft: назвати саме те, чого бракує | usage-panels | 4 | 3 |
| cleft-sentences-basics | 4. Збережи час і смисловий зв’язок | usage-panels | 4 | 1 |
| cleft-sentences-basics | 5. Що не є cleft і як перевірити себе | usage-panels | 1 | 0 |
| cleft-sentences-basics | 6. Практика | practice-set | 0 | 0 |
| cleft-sentences-basics | 7. Пов’язані способи акцентування | usage-panels | 1 | 0 |
| inversion-basics | 1. Змінюємо акцент, а не перетворюємо твердження на запитання | usage-panels | 1 | 1 |
| inversion-basics | 2. Допоміжне дієслово, do/does/did і базова форма | comparison-table | 4 | 0 |
| inversion-basics | 3. Never, rarely, seldom: важлива початкова позиція | usage-panels | 1 | 1 |
| inversion-basics | 4. Only after, only when, not until: знайди головну частину | usage-panels | 4 | 1 |
| inversion-basics | 5. Not only ... but also: речення чи частини підмета? | usage-panels | 3 | 1 |
| inversion-basics | 6. Три помилки, які змінюють конструкцію | usage-panels | 1 | 0 |
| inversion-basics | 7. Практика | practice-set | 0 | 0 |
| inversion-basics | 8. Далі за темою | usage-panels | 1 | 0 |
| advanced-fronting-and-emphasis | 1. Три різні механізми, а не одна «перестановка» | comparison-table | 0 | 0 |
| advanced-fronting-and-emphasis | 2. Додатки й доповнення на початку | usage-panels | 4 | 3 |
| advanced-fronting-and-emphasis | 3. Обставина на початку може лише задати сцену | usage-panels | 2 | 1 |
| advanced-fronting-and-emphasis | 4. Locative inversion: місце, дієслово, новий підмет | usage-panels | 5 | 1 |
| advanced-fronting-and-emphasis | 5. Особові займенники й доречність | usage-panels | 3 | 2 |
| advanced-fronting-and-emphasis | 6. Як порядок слів пов’язує абзац | usage-panels | 4 | 3 |
| advanced-fronting-and-emphasis | 7. Практика | practice-set | 0 | 0 |
| advanced-fronting-and-emphasis | 8. Порівняй інші конструкції | usage-panels | 1 | 0 |

Підсумок: Cleft **6**, Inversion **4**, Fronting **10** — 20 непорожніх disclosures.
Формули, короткі самодостатні warnings, agreement/no-do/neutral-order правила та related links не отримують штучних порожніх кнопок, якщо accepted source не має окремого додаткового пояснення.
Зокрема locative section розділено на п’ять незалежних points; додатковий accepted текст є для першого point. Для решти повний самодостатній текст залишено у basic, без вигаданих explanations.
Person-focus who/that та object/time правила також залишені повністю видимими: їх не скорочували лише заради кнопки.

Кнопка міститься всередині свого point. Деталі одночасно доступні, не впливають на сусідів, є в initial server HTML, працюють native/no-JS, без fetch після розкриття.
Збережено старі `lesson-block-…-section-…` і `self-check-…` anchors та додано унікальні code-owned point IDs.
Fail closed для чужого/stale/invalid owner, UUID, order, locale або mapping: повний stored текст залишається видимим, disclosure не додається.
Окремий символ `⌄` не повернуто.

## Practice та основні тести

Кожна сторінка: 2 selects + 2 choices + 2 token/manual cases + linked widget.
Усі шість погоджених випадків кожного уроку адаптовані до practice-set.

| Тест | Вправа 1 | Вправа 2 | Вправа 3 |
| --- | --- | --- | --- |
| [Cleft](http://gramlyze.loc/test/sentence-structure/cleft-sentences-basics) | Nora; we need | technician без зайвого she; cleft behind the library | It was Nora who/that found the spare key yesterday.; It was on Friday that we signed the agreement. |
| [Inversion](http://gramlyze.loc/test/word-order/inversion-basics) | speak; repair | Only after the bell rang did the guard open the door.; повні A/B/C/D, правильний C | Never have I heard this orchestra live.; Not until the lights came on could we read the sign. |
| [Fronting](http://gramlyze.loc/test/word-order/advanced-fronting-and-emphasis) | they come; At nine, the meeting starts. | усі три речення та дві повні classification maps; new model + повний neutral-order варіант | That condition I can accept.; Behind the curtain stood a tall mirror. |

Після SELECT-only inventory підтверджені exact primary banks (спільний namespace `Database\Seeders\V3\Polyglot\`):

| Урок | Seeder class suffix | Рівень | Bank count | IDs |
| --- | --- | --- | ---: | --- |
| Cleft | PolyglotCleftSentencesBasicsB2LessonSeeder | B2 | 48 | 17361–17408 |
| Inversion | PolyglotInversionBasicsB2LessonSeeder | B2 | 48 | 17313–17360 |
| Fronting | PolyglotAdvancedFrontingAndEmphasisC2LessonSeeder | C2 | 48 | 18657–18704 |

Linked widget показує 5 випадково обраних type-4 питань лише з власного primary bank; у всіх 12 browser states IDs належали exact bank.
Question banks, answers, options, verb_hint, pivots та saved-test relations не змінювалися.

Практика приймає correct 2/2, показує wrong state, reset; ручне введення, token clicks, Backspace reuse, accepted alternatives, autocomplete off та відсутність word suggestions у token-bank tasks перевірено.
Cleft приймає погоджені who/that alternatives; зайві/неправильні відповіді відхиляються.
Для Inversion renderer тепер підтримує per-item A/B/C/D без зміни глобального A/B default.

## Guarded локальний apply

Фізична ціль підтверджена: Windows DESKTOP-3C05HGF, Apache httpd.exe listener/PID file 33460, один gramlyze.loc vhost, document root `D:/DEV/htdocs/gramlyze.loc/public`.
Домен резолвився лише у 127.0.0.1; локальний mysqld.exe PID 6640, port 3306. CLI/web збіглися:
APP environment production, SiteMode development, MySQL localhost:3306, database **gr2**.
Production-профіль тут означає локальну конфігурацію цього фізично перевіреного .loc, не звернення до .com/.ub.

Погоджений тимчасовий nonce Laravel route: exact HTTP host, GET-only, raw loopback, reject Forwarded/X-Forwarded headers, SELECT-only, API без session/cookie.
GET повернув тільки 8 allowlisted safe runtime полів і nonce-bound fingerprint; HEAD і forwarded GET — 404.
Початковий sandboxed Windows-inspection timeout не обходили: повторна read-only перевірка з дозволеним доступом до OS processes пройшла.
Write без exact --database спочатку відхилений; БД до цього не змінювалася.

Fresh proof → fresh preview → exact source/row/field diff review → exclusive record backup → transaction → protected postconditions → repeated no-op → browser acceptance виконано.

Фактичний diff:
- text_blocks: **3 updates** тільки type/body (8757, 8758, 8828);
- **20 inserts**: 12334–12353, по 6/7/7 на урок;
- **0 deletes**;
- Page/category/ancestry/subtitle/hero/locale/level/order старих рядків/relations/tags та всі question-related fingerprints незмінні.

Приватний exact record backup:
`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m28-local/m28-before-v1.json`
SHA-256 `f2c76186dba78525f13bb402ba2739c9e5cc9a6b38837037c6d6450d18392f19`.
Backup дорівнює fresh preview побайтово; у Git не включений.
Repeated no-op: updated 0, inserted 0; `m28-unused-noop-v1.json` не створений.
Зворотне відновлення перевірено лише в ізольованому fixture; робочу БД не відновлювали.

Route видалено після no-op; GET повертає **404**.
`routes/api.php` byte-identical before/after:
SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`.
У worktree/commit route не додавався.

## Fidelity і автоматичні перевірки

Accepted manifest → finite projection → actual DB postconditions → initial HTTP HTML → DOM after reload.
Frozen package SHA-bound; canonical definitions мусять точно збігатися з package.
Word multiplicity всієї авторської теорії перевіряє відсутність пропусків/дублів (погоджена practice адаптація виключена).
Initial HTML та reload DOM додатково порівнюють точні basic/detail/table-cell тексти, включно з punctuation та not; унікальність ID перевіряється в навчальній області.
12 semantic negative fixtures: not, who/that, what question order, does speaks, did noticed, wrong subordinate inversion, Here come they, втрачений переклад/example, neighbour detail, duplicate anchor, practice answer.

Окремі прогони, не сумарна вигадана статистика:

| Прогін | Результат |
| --- | --- |
| PHP m28-core-v3: M28 package/patch/guard + M27 package/patch + M26 package/points/patch | **74 tests, 2373 assertions, PASS** |
| PHP m28-final-guards-v1: M28 fidelity/fallback/guard + M11/M27 guards + M26 interactive package/patch | **68 tests, 878 assertions, PASS** |
| Node M28 practice + M27 practice + generic theory contractions | **32 tests, PASS** |
| Vitest theorySections / theorySidebarLayout / theoryNavigation | **32 tests, 3 files, PASS** |
| JSON parse | **5 documents PASS** |
| PHP lint | **10 new PHP files PASS** |

PHP 8.5.10 / PHPUnit 12.5.35. Кожен final PHP run повідомляє одну вже наявну deprecation `PDO::MYSQL_ATTR_SSL_CA` у config/database.php; її не змінювали поза scope.
PHP runs ізольовані: SQLite :memory:, private runtime/cache/views, array session/cache, CLI opcache off; working .env не завантажувався.
Попередні діагностичні помилки (неіснуюча назва M26 test file, fixture без parent ancestry) виправлено; вони не зараховані як PASS.
Full repository suite не запускали.
CSS/JS/Vite inputs та залежності не змінені; Blade використовує вже зібрані існуючі utility classes. Generated assets не додавали, build не запускався.

## Реальний HTTP/browser, no-JS, screenshots

Приватні докази у `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m28-local`:
- `before-http.json`: fresh GET 10 pages + ordered sitemap до apply;
- `acceptance-v4-http.json`, `acceptance-v4-browser.json`: **12 states PASS**, 3 M28 no-JS + 3 M27 no-JS, **7 controls PASS**;
- `fidelity-v1-http.json`: exact punctuation/basic/details/table cells initial HTML PASS;
- `focused-v2-elements.json`: **5 reload-DOM fidelity contexts PASS**, усі **4 M26 lesson practice pages PASS**, 10 focused screenshots;
- `m28-bank-inventory-v1.json`: SELECT-only primary bank evidence.

M28: 1440×1000 та 390×844, light/dark, чисті незалежні guest contexts.
Кожен disclosure перевірено: own point, own text, sibling state unchanged, click/close, Enter/Space, focus-visible, simultaneous open, reload closed, deep fragment, print expansion/restoration, no disclosure fetch.
Console/page errors, failed local requests, HTTP errors та policy violations — 0 у завершеному прийманні.
No-JS scripts blocked by browser CSP при javaScriptEnabled=false класифіковано окремо як очікуване вимкнення, не site failure.

Screenshots включають first-screen/basic, It-cleft, What-cleft, Inversion full table та Only after/not until, Fronting locative, full cohesion paragraph, mobile table зі scroll-right, practice A/B/C/D та dark.
Focused practice viewport 1440×2400 використаний лише для повного знімка високого блоку без sticky-header перекриття; це не підміна основних 12 states.
Візуально переглянуті focused It-cleft, locative, cohesion, Inversion practice та mobile/dark screenshots.
Initial shared SVG logo має два `id="grad"` поза learning area; це старий shell issue, не M28-generated anchor. Не змінений поза scope.

## M26/M27 та інші controls

M26: `seo-m26-local/m28-regression-v1-browser.json` — **56 unique details PASS**, 5 actual GET pages, 20 states, 5 no-JS.
Додатково чотири практики Forms/Negatives/Questions/Time Expressions реально пройшли correct/wrong/reset/token/manual acceptance.
M27: **22 details (3+8+11)**, mouse, print, deep/reload, практика та no-JS — PASS; source/content/metadata/anchors незмінні.
Ordinary Present Perfect та Present Perfect Continuous: без M28 disclosures, unchanged metadata/content.
Accepted M13+ control [Ellipsis, Substitution and Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference): content hash/metadata/anchors незмінні, без M28 disclosures.

## Overflow, metadata, sitemap

Learning main і всі видимі cards у 12 M28 states: **0 px**.
Mobile tables мають локальне scrolling: client 322 px, scroll 576 px, overflow-x auto; документ не розпирають.
Decorative shell background оцінено окремо:
- desktop document overflow 0 px;
- mobile M28 light: Cleft 3 / Inversion 14 / Fronting 4 px;
- mobile M28 dark: Cleft 6 / Inversion 9 / Fronting 5 px;
- найбільший geometric decorative protrusion ≈14.19 px;
- M27 historical baseline: mobile document overflow 0–9 px; поточний M28 3–14 px — не оголошено «нульовим», random background не змінений.
M26 окремий run: decorative document overflow до 10 px, learning acceptance PASS; strict whole-document overflow PASS **не** заявляється.
Не додавали global overflow-x:hidden.

Before/after title, H1, description, canonical, robots, X-Robots-Tag, OG, Twitter — точно незмінні для всіх 10 GET pages.
Ordered sitemap unchanged: фактично **554 URLs**, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`; кількість виміряна, не hardcode acceptance.

## Git та обмеження

До commit лише explicit M28 definitions, author-source manifest/package, guard/command/renderer, tests/diagnostics/report/.gitattributes.
.env, backups, snapshots приватного runtime, screenshots, vendor, generated assets, сторонні зміни та proof route не додаються.
Git diff/check, staged diff та secret/path scan виконуються перед commit; normal push тільки в M28 branch, remote SHA перевіряється після push.
Commit hash та GitHub links подано у фінальній відповіді (щоб не створювати self-referential commit hash у цьому звіті).
Main, PR, merge, force, workflow dispatch, deploy, production HTTP, migrations, seeds, cache/session clear не використовувалися.
