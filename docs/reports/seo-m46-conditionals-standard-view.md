# M46 — Zero / First / Second Conditional у стандартній в’юсі

Дата локального застосування: 2026-10-11. Реалізація — versioned навчальні дані, не редизайн.

## База, ціль і межі

- Actual base / HEAD до M46: `c9d6e77f66f91688fd405463ed25acc8c18591ab`; ancestry перевірено, exit 0.
- Робоча гілка: `codex/seo-m46-conditionals-standard-view`.
- Git checkout: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Фактичний served ROOT: `D:/DEV/htdocs/gramlyze.loc`, Apache document root — його `public`.
- Єдина HTTP/DB ціль: `http://gramlyze.loc`. Фізичний Windows web/CLI proof: MySQL, localhost:3306, database `gr2`; APP environment production, SiteMode development. Назва APP environment не означає звернення до production-сайту.
- Main, production .com/.ub, PR, merge, force push, deploy, .env, hosts, сервери й залежності не змінювалися. Сторонні dirty changes ROOT та приватні незавершені файли WT збережено.

| Урок / підтверджений URL | Page ID | Seeder identity | Locale / category | Display / block level |
| --- | --- | --- | --- | --- |
| [Zero Conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | 53 | Database\Seeders\Page_V3\Conditionals\ConditionalsZeroTheorySeeder | uk / conditionals | A2–B1 / A2 |
| [First Conditional](http://gramlyze.loc/theory/conditionals/first-conditional) | 44 | Database\Seeders\Page_V3\Conditionals\ConditionalsFirstTheorySeeder | uk / conditionals | A2–B1 / A2 |
| [Second Conditional](http://gramlyze.loc/theory/conditionals/second-conditional) | 48 | Database\Seeders\Page_V3\Conditionals\ConditionalsSecondTheorySeeder | uk / conditionals | B1–B2 / B1 |

До запису звірено Page owners, category ancestry, локалі, canonical local routes, старі UUID, IDs і metadata блоків. Нові маршрути уроків не створювалися.

## Навчальний і редакційний результат

Потреби читача — редакційна карта, не виміряні search volumes, CTR або позиції.

- Zero: закономірність, звична реакція й правило конкретної системи; Present Simple проти imperative; заперечення й питання; порядок частин і кома; межі if/when. Прибрано false-error framing для граматичного First зі will у результаті. Tomorrow не оголошено механічним критерієм помилки: приклад про скасування матчу пояснено як чинне рішення.
- First: можлива майбутня умова й умовний результат, без гарантії події або відсотка ймовірності. Додано питання/заперечення/коротку відповідь/порядок частин, обмежені modal/imperative результати. Відокремлено звичайну майбутню if-умову від will зі значенням готовності. File opens / will open пояснюється контекстом; will to start залишається справжньою помилкою форми.
- Second: уявне теперішнє/майбутнє, не тільки неможлива подія; past form не дорівнює past time. Would/could/might, If I were you, were/розмовне was, питання, заперечення й контраст із First. Старий yesterday mixed-time приклад вилучено з basic з явним editorial поясненням, а не «виправлено» мовчазним видаленням часу. Глобального blacklist if + would немає.

Корисні старі приклади збережено або причини заміни задокументовано по старих English fields. Basic містить коротке правило, потрібні приклади/переклади й необхідні ремарки; короткі пояснення не сховані у формальні details.

Фінальний author/master: [m46-conditionals.v1.0.1.json](../content/m46-conditionals.v1.0.1.json), читабельна редакція: [m46-conditionals.v1.0.1.uk.md](../content/m46-conditionals.v1.0.1.uk.md).
Повний editorial BEFORE/AFTER та фактичні джерела: [editorial 1.0.1 r2](../content/m46-conditionals-editorial.v1.0.1.r2.md).
R1 розгортає об’єкти before/after/reason замість технічних placeholders. Фінальна документаційна r2 точно звіряє57старих English fields:40дослівно збережені з current block/key/JSON paths,17змінені або вилучені з конкретною підставою; непідтверджені індивідуальні мотиви не вигадані. Frozen master, runtime payload і попередні editorial файли не переписані.

Після freeze 1.0.0 виявлено одну примітку First із застарілим `give` після збереження прикладу з `tell`. Створено окремі master/source/readable 1.0.1, застосовано точкову поправку `tell — вказівка співрозмовнику`. Frozen 1.0.0 збережено незмінним.

| Джерело | SHA-256 |
| --- | --- |
| Master 1.0.0 | `6fc9887f108a35a7c6e39c92d6f8611ad80f71bd6f5f5b31c3d7f1024c8dcc71` |
| Master 1.0.1 | `9d9f41d4616fdda57e551de97534642dba8c6c2b6be85cdeeef522667fc29048` |
| Незалежний versioned BEFORE | `c7393d6f5f7b3b16f535f8ed755b41265865b21cda17fbbd98e2af9d1e0b2c38` |
| Content patch 1.0.0 | `e3b7bf9bcbc0d5293f000d922010f0022d3f182d5b13113f769dcbccfc2648d5` |
| Content patch 1.0.1 | `22f6d02e2bedf737a02ced86a6f8af3c75880cdc893d0d20688c4b64e4047c56` |

Повні raw checksums обох версій, readable/editorial та трьох definitions: [m46-conditionals-checksums.v1.json](../content/m46-conditionals-checksums.v1.json). Джерела містять власні приклади й вправи; видавничі завдання не копіювалися. British Council editorial section прочитано без learner comments. Для Cambridge прямі opens повертали403: використано доступний індексований текст видавця, не заявлено повного успішного читання сторінок.

## Карта чинних компонентів

| Урок | Source blocks без subtitle | Forms cards | Named usage points | Table rows | Mistakes | Meaningful details | Tasks / controls |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| Zero | 10 | 4 | 6 | 2 | 2 | 0 | 6 / 6 |
| First | 7 | 6 | 4 | 0 | 3 | 1 | 6 / 6 |
| Second | 11 | 4 | 8 | 5 | 3 | 0 | 6 / 6 |
| Разом | 28 | 14 | 18 | 7 | 8 | 1 | 18 / 18 |

Форми — forms-grid, змістові пункти — usage-panels, зіставлення — comparison-table, помилки — mistakes-grid, підсумок — summary-list, практика — practice-set, навігація — navigation-chips. Не кожний абзац став панеллю; формули/таблиці/навігація не перетворені на usage-пункти.

Єдиний detail належить першій формулі First: `m46-first-will-willingness`, пояснює готовність, містить контраст і дві EN/UK пари. Повний текст у серверному HTML; відкриття не завантажує його новим запитом.

На кожній сторінці 3 selects + 3 choices: 6 явних canonical answers із перевіреними aliases; разом45 видимих option labels. Переклади та розбір — feedback після check, не довгі keys у кнопках. Нові manual/token controls не створювалися: це вибір підтримуваної механіки даними, без зміни engine. Чинні own-bank widgets та links лишилися.

## Метадані й внутрішні посилання

Чинний PageMetadata::theory отримує hero intro; summary першого повного українського речення має пріоритет над editorial fallback. Page.text/subtitle не прирівняно до автоматичного meta override. Title, H1, slug, category та canonical ідентичні BEFORE. Titles: «Zero / First / Second Conditional — правила | Gramlyze».

| Урок | Description BEFORE | Description AFTER |
| --- | --- | --- |
| Zero | Zero Conditional використовуємо, коли результат є завжди правдивим, регулярним або звучить як інструкція. | Zero Conditional описує зв’язок, який зазвичай повторюється: за певної умови отримуємо типовий результат. |
| First | First Conditional використовуємо, коли умова є реальною або можливою, а результат стосується майбутнього. | First Conditional потрібен, коли ми розглядаємо майбутню умову як можливу й кажемо, що зробимо або чого очікуємо за цієї умови. |
| Second | Second Conditional використовуємо для уявних, нереальних або малоймовірних ситуацій у теперішньому чи майбутньому. | Second Conditional допомагає уявити іншу ситуацію зараз або в майбутньому та її результат. |

Description, OG і Twitter description збігаються з незалежним очікуванням. Чинні WebSite/BreadcrumbList/LearningResource збережені; LearningResource.description тепер відповідає новому intro. Дати, identity та schema types не змінені; FAQ/rating/нового автора/SEO-дати не додано.

Canonical у локальному HTML, як і BEFORE, містить HTTPS .com URL; це лише прочитане значення markup, production не відвідувався. Локальний `X-Robots-Tag: noindex, nofollow, noarchive` збережено; вимога meta robots не вигадувалася.

Zero footer скорочено з10 до2 доречних тем Zero/First. First і Second зберегли10 старих destinations; Zero↔First і First↔Second присутні. Усі10 унікальних локальних destinations повернули200. Сусідні уроки не переписувалися.

## Guarded apply та збереження нецільових даних

Незалежний live BEFORE — source/DB/6HTTP pages/326visual hashes — збережено до source sync. Exact old6slots кожного уроку продовжують старі IDs/UUID/type/column/heading/level/css/order/timestamps; нові блоки отримали stable keys.

- Fresh proof → source-sync locks → fresh preview → independent exact reviewed allowlist → exclusive private backup → transaction → postconditions.
- Початкова 1.0.0: **24 updated / 10 inserted / 0 deleted**. Оновлення — 3Page.text і21UK text_blocks.body; 10 нових UK blocks.
- Повтор 1.0.0: **0 / 0 / 0**; непотрібний no-op backup не створений.
- Окрема 1.0.1: **1 updated / 0 inserted / 0 deleted** — лише body старого First usage block1757. Exclusive backup до запису; exact old/new payload й protected fingerprints звірено.
- Повтор correction1.0.1: **0 / 0 / 0**. Фінальний generic preview поточної1.0.1: state after, **0 / 0 / 0**.
- Разом25 update operations на24 distinct старих rows, 10 inserts, 0 deletions. Не було reseed/migrate/truncate/full restore/session clear/startup hooks.
- Усі46 non-target/bank/progress table fingerprints незмінні; EN/PL та всі інші поля/rows збережено.
- Linked bank relations BEFORE: Zero432, First519, Second518, разом1469; questions/answers/options/verb_hint/pivots/saved tests/progress не змінені.
- Три доступні course pages зафіксовано до source sync; після apply усі200, title/H1/teaching text дорівнюють live BEFORE. Курсова projection — копія моделей у пам’яті, не DB rollback або зміна course rendering.

Приватні evidence у served ROOT `storage/app/seo-m46-local/`, не у Git:

| Artifact | SHA-256 |
| --- | --- |
| before-v3.json | `f20ec1f54292e78b4a6615670fd5b1fc180e04637c9680e099c0fc17029e0a3d` |
| preview-v1.json | `d6d60a508da240d0ab784f2bfef8b41cfd6e5c656accbd3f3063dd086261cfa8` |
| backup-v1.json | `e6a4fd739af910ced4d6c5277e6418b180d61a00c13fd179f57e30edf0d08d15` |
| editorial-backup-v1-0-1.json | `0aa3984696fe7ffd0c1b5cc7d8d34779ade1df218fbc7b02fb06422213cdffbe` |
| final-preview-v1-0-1.json, raw file | `ae4787fa84114c33d3c4b94a7299c7c8c61223bb439c90a99e53e320a1f1c1cb` |
| after-v3.json | `bb915ae9e1da361fe6099a59c1664140c53f0837febc555d580918a15c52089e` |

Обидва випадкові temporary proof routes видалені; GET кожного після видалення404. routes/api.php повернуто до pre-proof raw SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Routes не входять до коміту. Proof повертав лише дозволену safe runtime identity/fingerprint, без секретів.

## Універсальна в’юха, компоненти теорії, оформлення практики та CSS/JS не змінювалися

Підтверджено незалежними BEFORE/AFTER raw SHA всіх326 visual source files (views/CSS/frontend JS/public JS) та Git scope. Жодного Blade/CSS/font/frontend JS/practice engine/generated asset diff. Frozen42 maps і попередні пакети не переписані.

Мінімальна невізуальна інтеграція:

- app/Support/TheoryContentSource.php — реєстрація exact-bound M46 source presenter у чинному списку.
- app/Support/TheoryHtmlAdapter.php — перевірка finite semantic plan M46 і передача назв/акцентів у вже чинні native panels; frozen42 карту не розширено.
- app/Support/TheoryLegacyAdapter.php — перенесення явно схвалених mistakes EN/UK fields у чинний bilingual node.
- app/Services/TheoryCourseManifestService.php — caller-owned повернення незалежного BEFORE для exact-matching M46 UK pages лише курсів, без мутації оригінальної моделі.
- Новий M46ConditionalsPackage перевіряє hashes/identity/body, визначає semantic projection і point owner. Це data adapter для незмінного theory.show → content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*, не альтернативний executable view.

Окремі patch/guard/CLI diagnostics використовуються тільки explicit CLI workflow. Автоматичного apply з HTTP, startup, hooks або scheduler немає.

## Браузерне приймання та тести

Computer-use працював через документоване in-app browser керування; production URLs і safety bypass не використовувалися. Browser artifacts приватні.

- Основна матриця:3уроки ×1440×1000/390×844 ×light/dark = **12 states**.
- Додатково320×844 light/dark на всіх3: **6 states**.
- Переглянуто всі секції; native usage panels neutral background/border0, semantic colored labels/numbers, examples із синьою лівою лінією3px, UK без курсиву. Forms/tables/summary/navigation лишаються власними компонентами; fallback0.
- Фактичні шрифти: body Manrope40016px і h1 Archivo80036px; document.fonts.status loaded, checks true, stylesheet і3woff2 assets зафіксовані. Початкову Zero desktop capture повторено після font readiness.
- Чотири read-only еталони перевірено окремо при тих самих1440×1000/light/expanded-sidebar/assets. Reference counts не додано до M46 totals. Масштаб гарячими клавішами не змінювався; temporary viewport скинуто.
- First detail: default closed, Enter open/close, own formula ownership, повний текст, deep link до summary `theory-details-452207faaf2b98b86f6a`, reload із hash — PASS.
- Кожний із18 controls у реальному UI: empty → wrong → wrong → edit correct → repeated correct → reset. **84 recorded states**,72 feedback states точно звірено з незалежним master, issues0; score1/3→2/3→3/3 не подвоювався, reset прибрав feedback.
- Старий приватний flow-summary мав2 false negatives через пошук слова «Неправильно» у Second feedback. Незмінні записи повторно reviewed exact author-feedback comparison у flow-review-final-v1-0-1.json; це виправлення audit heuristic, не відповідей сайту.
- Фінальний First1.0.1 reload підтвердив tell note, default closed detail і незмінний native вигляд. Скриншот: `storage/app/seo-m46-local/browser/first-conditional-final-v1-0-1.jpg`.

Ізольовані suites з testing/SQLite:memory:, working_env_loaded=false, без завантаження робочої .env:

| Run | Результат |
| --- | --- |
| M46LocalTargetGuardTest | 50tests /118assertions PASS |
| M46ContentPatchTest, initial | 6tests /111assertions PASS |
| M46ConditionalsPackageTest + TheoryContentGroupsTest + TheoryUniversalRolloutTest + TheoryUniversalContentViewTest | 22tests /24023assertions PASS |
| Фінальний master1.0.1: M46Package + M46Patch повторно | 12tests /969assertions PASS |
| Фінальний offline Node: M46 native state + contractions + manual retry + manual contractions | **214/214 PASS**,0skip |

Початкові PHP78tests/24252assertions і фінальний повтор12tests — окремі runs, не90 унікальних tests. Кожний ізольований runner підтвердив46661 protected files, changes0. Наявний PHP8.5 deprecation у config/database.php:62, PDO::MYSQL_ATTR_SSL_CA → Pdo\Mysql::ATTR_SSL_CA; конфігурація не змінювалася. Offline214 охоплюють aliases/apostrophes/commas/question mark/manual retry та duplicate token regressions чинного engine; це не заявлено новими manual/token browser controls M46.

Фінальний read-only after-v3:6HTTP200 (3targets+3courses),46protected tables/326visual files без змін поза дозволеним scope; course changes0. Читабельність реальних сторінок перевірена окремо від HTTP200.

## Обмеження й доставка

- Підключений браузер не надає API для JS-disable або print-media emulation. Full no-JS browser run та видимий print preview **не підтверджені**. Серверний HTML містить увесь basic/detail, чинні no-JS fallback links і незмінні print styles перевірені; це не заміна заяви про повний browser PASS цих двох режимів.
- На mobile/320 root document має невеликий overflow від анімованих порожніх декоративних spans; content panels не виходять за viewport. Те саме read-only підтверджено на PPC reference. Global decoration CSS поза M46 не змінено.
- SEO-копія й структура реалізовані локально. Зростання позицій, індексація, CTR та production-впровадження не перевірялися й не заявляються.
- Commit/push — тільки явно staged пов’язані source/code/tests/docs у робочу гілку; backups/proofs/screenshots/runtime/private diagnostics і сторонні файли виключені. Whitespace перевірено через git diff --check і git diff --cached --check з core.whitespace=blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol: це враховує raw CRLF byte-pinned Windows definitions, не ігнорує trailing spaces. Normal push без main/PR/deploy; остаточний full SHA та remote==HEAD повідомляються у відповіді після доставки.
