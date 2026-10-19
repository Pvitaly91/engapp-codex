# M49 — Had better, Can / Could, May / Might

## Результат і поточний статус

M49 застосовано до трьох наявних українських theory pages на `http://gramlyze.loc`. Навчальний контент і практика використовують чинні стандартні компоненти. Редизайну, нового practice engine або production-деплою немає.

Actual base: `3b0081eda875b3fb5f07cfd8f1441e3ac7102670` — завершений M48.
Робоча гілка: `codex/seo-m49-advice-modals-standard-view`.
Source checkout: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`; served ROOT: `D:/DEV/htdocs/gramlyze.loc`, document root — його `public`.

Guarded apply, repeated no-op, HTTP/source/metadata/course audits, ізольовані перевірки, усі 18 live practice controls, повна viewport/theme matrix та фінальна read-only звірка завершені. Git-доставка відокремлена нижче; підсумковий SHA подається у фінальному handoff після commit/push.

## Точні owners, категорії та рівні

| Сторінка | Exact identity | Page ID | Locale / ancestry | Display / technical level |
|---|---|---:|---|---|
| [Had better](http://gramlyze.loc/theory/conditionals/had-better) | `Database\Seeders\Page_V3\Conditionals\ConditionalsHadBetterTheorySeeder` | 45 | uk / conditionals | A2–B1 / A2 |
| [Can / Could](http://gramlyze.loc/theory/modal-verbs/can-could) | `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder` | 72 | uk / modal-verbs | A1–A2 / A1 |
| [May / Might](http://gramlyze.loc/theory/modal-verbs/may-might) | `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMayMightTheorySeeder` | 74 | uk / modal-verbs | A2–B1 / A2 |

Дві категорії збережені окремими exact bindings: category ID 10 для Had better та 15 для Can/Could і May/Might. Сторінки не переносилися, нових Page owners/маршрутів уроків не створено. Поза scope: інші уроки, EN/PL, курси, банки, окремі `/test`, saved tests, attempts і progress. Main, production, серверна конфігурація, hosts, ACL, залежності, workflows і generated assets не змінювалися.

## Потреби читача та редактура BEFORE → AFTER

| Урок | Доповнення й виправлення | Межі |
|---|---|---|
| Had better | Ситуативна сильна порада й можливий наслідок; bare verb, not, теперішній/майбутній зміст; ’d = had, на відміну від ’d rather = would; природні питання з поясненням регістру; advice / preference / requirement. | Should не подано як еквівалент особистої переваги. Немає універсальної шкали ввічливості, погрози або заборони than. Старе речення про чай збережено в контекстному поглибленні, а не оголошено безумовною помилкою. |
| Can / Could | Здатність, можливість за обставин, дозвіл, прохання; короткі відповіді; майбутня доступність із can; past ability, конкретний успіх/невдача та сприйняття; be able to там, де потрібна інша граматична форма. | Could не означає лише минуле. Простий A1–A2 basic не перетворено на довідник складної дедукції. Старе printer-at-last речення уточнено через реально завершений ремонт: was able to / managed to; couldn't і could hear не заборонено. |
| May / Might | Можливість і дозвіл; базова форма, not, be; два контекстні значення may not; may be / maybe; власна дія з May I та формальне Might I. | Без фіксованих відсотків; may/might приймаються обидва, якщо умова їх не розрізняє. «Можливо, не» не прирівняно до неможливості. Неоднозначний старий уривок Maybe be late замінено повним He maybe late із чітким задумом і прозорим виправленням. |

До зміни кожна definition містила шість блоків. Їхні slots 0–5, типи, column, technical level і порядок продовжено; нові блоки додані стабільними keys. Корисні старі приклади збережені або явно перенесені; кожна змістова заміна має BEFORE/AFTER pointer і пояснення у `docs/content/m49-advice-modals-editorial.v1.0.0.md`. Це не мовчазне переписування accepted прикладів.

Мова/переклади, семантика альтернатив, складність, компактність, basic/detail ownership і native-сумісність переглянуті до freeze. Had better та May / Might пройшли незалежний cross-review, Can / Could — окремий main-review. Зовнішньої сертифікації чи неіснуючого рецензента не заявлено.

## Джерела та people-first SEO

Точний URL, перевірене питання та спосіб доступу записані у versioned editorial. Для [Cambridge Had better](https://dictionary.cambridge.org/grammar/british-grammar/had-better), [Could](https://dictionary.cambridge.org/grammar/british-grammar/could), [May](https://dictionary.cambridge.org/grammar/british-grammar/may), [Might](https://dictionary.cambridge.org/grammar/british-grammar/might) і [Could, may and might](https://dictionary.cambridge.org/grammar/british-grammar/could-may-and-might) прямі запити повертали 403; використано офіційний індексований редакційний текст, не заявлено успішного full direct read. Для Can/could/may і maybe/may be окремо зафіксовані фактичні indexed URLs та недоступні прямі адреси.

Успішно прочитано основний редакційний текст [British Council Ability](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/ability), [Past ability](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-ability), [Probability](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/probability) і [Suggestions and obligations](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/suggestions-obligations). Коментарі учнів не використовувалися як граматична норма. Нові ситуації й вправи авторські, не копії видавничих завдань.

SEO означає корисну відповідь про значення, форму, відмінності, переклад і практику. Орієнтир — фактично прочитані [Google Search Central: helpful, reliable, people-first content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content). Немає вигаданих search volumes, CTR, ranking gains, keyword stuffing або word-count quota. Локальне приймання не доводить індексацію чи зростання позицій.

## Frozen sources 1.0.0

Master: `docs/content/m49-advice-modals.v1.0.0.json`.
Readable: `docs/content/m49-advice-modals.v1.0.0.uk.md`.
Editorial: `docs/content/m49-advice-modals-editorial.v1.0.0.md`.
Незалежний BEFORE та finite projection: `database/content-patches/m49-advice-modals-before.v1.json` і `database/content-patches/m49-advice-modals.v1.json`.
Повний inventory: `docs/content/m49-advice-modals-checksums.v1.json`.

| Файл | SHA-256 |
|---|---|
| Master | `554aace8763e3d2183c4ea3533dd781bc9171a5ca27b2a39a64d43c55f9c1662` |
| Readable | `a2863c8c350dcc53e8a83dd4e7a415cc8c4aed9db13b6b2e099a785f07731d27` |
| Editorial | `0273733f3d8711f6206e393b5746a2d16a62a047a8b58fc4c398c4652f259a7f` |
| BEFORE | `010c743a4d39dec669d49fe80025557f2f5aa4afbf97fc9419066be6f0bccc8e` |
| Projection | `0e51746220c01b35d7112ed452b30b5bfe64a8839378cfe8c59fb7d3775d4d93` |
| Had better definition | `07d0d00309eba043454714961712721b524b4e09908115efd7b56afe419fc476` |
| Can / Could definition | `29d8ecd6a74827b9ca97167c3eb7a501c380b586f856d96ff79f7ea943d90141` |
| May / Might definition | `3b3065fd8be6650718efedd76ed4636d549c609869dfa5eca25df05fbdbca5b0` |

Усі вісім файлів перевірено в ROOT і WT: 16 raw checksum checks, 0 mismatches. Definitions структурно точно відповідають projection; native practice data відповідають master після видалення лише технічного marker. Package constants збігаються з реальними master/BEFORE/projection hashes. Після freeze навчальні дані не підмінялися; May hero-prefix normalization виконана до freeze.

## Канонічні компоненти й фактичні counts

Шлях незмінний: `theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. Практика використовує існуючий `practice-set` і `theory-practice-*`.

| Урок | Blocks без subtitle | Forms / rule rows | Usage points / EN–UK pairs | Table rows | Mistakes / summary | Details / внутрішні панелі / EN–UK pairs | Tasks / controls / options |
|---|---:|---:|---:|---:|---:|---:|---:|
| Had better | 7 | 4 / 6 | 4 / 8 | 0 | 3 / 5 | 1 / 1 / 3 | 6 / 6 / 14 |
| Can / Could | 8 | 4 / 8 | 5 / 9 | 3 | 3 / 5 | 1 / 1 / 2 | 6 / 6 / 17 |
| May / Might | 7 | 4 / 5 | 5 / 17 | 0 | 3 / 5 | 1 / 2 / 2 | 6 / 6 / 16 |
| Разом | 22 | 12 / 19 | 14 / 34 | 3 | 9 / 15 | 3 / 4 / 7 | 18 / 18 / 47 |

22 блоки = 18 continuing + 4 нових: три practices і одна Can/Could comparison-table. Це 13 основних theory section blocks без hero/navigation/practice. Hero має загалом 9 карток; navigation — 26 entries. Форми, таблиця, summary і navigation не перетворювалися на usage-панелі.

Три disclosure objects містять чотири змістові detail-панелі. Сім EN–UK example units у них містять 10 англійських речень і 10 відповідних українських речень; не слід називати це лише сімома реченнями.

| Detail key | Owner | Поглиблення |
|---|---|---|
| `had-better-tea-context` | `had-better-uses /sections/2` | Особистий смак і практичний вибір чаю через обставини |
| `can-could-other-forms` | `can-could-uses /sections/1` | Be able to, коли потрібна інша граматична форма, а не механічна заміна can |
| `may-might-not-uncertainty-not-impossibility` | `may-might-uses /sections/3` | Два відкриті результати проти відомої недоступності людини |

Basic самодостатній. Формули, переклади та необхідні межі правил видимі одразу; details не використовуються для коротких ремарок або приховування ключової умови. Повний текст усіх чотирьох detail-панелей наявний у server HTML під правильним owner.

Практика: Had better 2 selects + 4 choices; Can/Could 2 + 4; May/Might 4 + 2. Разом 8 selects + 10 choices, по одному control на задачу. Canonical answers — 18; правильних видимих alternatives — 23 (6/9/8). Declared accepted values — 24, бо додаткова повна форма would rather є невидимим alias. Альтернативи не додають другого бала одному control. Keys/розбори не перенесені в короткі initial options.

## Metadata, schema, anchors і destinations

Очікування metadata обчислені до apply через чинні PageMetadata/AddStructuredData services зі frozen hero; фактичний AFTER звірено з цим незалежним очікуванням. Hero, а не припущення про назву subtitle field, визначає description.

| Урок | Meta description BEFORE | Meta/OG/Twitter description AFTER |
|---|---|---|
| Had better | Had better використовуємо для сильної поради або попередження. | Had better виражає сильну пораду в конкретній ситуації: мовець бачить можливий небажаний наслідок, якщо не дослухатися. |
| Can / Could | Can / Could. У цій темі ти побачиш, як can і could працюють для здатності, можливості, дозволу та ввічливих прохань. | Can / Could допомагають говорити про здатність і можливість, просити дозволу та звертатися по допомогу. |
| May / Might | May / Might. У цій темі ти побачиш, як may і might допомагають говорити про можливість, ймовірність і формальніший дозвіл. | May / Might допомагають висловити припущення: щось можливе, але не певне. |

Titles/OG/Twitter titles незмінні: «Had better — правила | Gramlyze», «Can / Could — правила | Gramlyze», «May / Might — правила | Gramlyze». H1 окремо збережені: «Had better», «Can / Could», «May / Might». Canonical лишається `https://gramlyze.com` із відповідним незмінним path: це прочитане значення локального HTML, не production-запит. X-Robots-Tag `noindex, nofollow, noarchive` збережений.

Повний JSON-LD graph AFTER дорівнює BEFORE після заміни тільки `LearningResource.description`; типи WebSite, BreadcrumbList, LearningResource збережені. Опис LearningResource — повний hero intro, не лише короткий meta excerpt:

- Had better: «Had better виражає сильну пораду в конкретній ситуації: мовець бачить можливий небажаний наслідок, якщо не дослухатися. Йдеться про теперішнє або майбутнє, хоча у формі є had.»
- Can / Could: «Can / Could допомагають говорити про здатність і можливість, просити дозволу та звертатися по допомогу. Значення залежить від ситуації: could не завжди позначає минуле, а can може стосуватися завтра.»
- May / Might: «May / Might допомагають висловити припущення: щось можливе, але не певне. May також використовують для дозволу. Спочатку визнач зміст — можливість чи дозвіл, а потім обери форму.»

Page `datePublished = dateModified` збережені: Had better `2026-04-23T17:34:14+03:00`, Can/Could `2026-04-23T17:35:07+03:00`, May/Might `2026-04-23T17:35:10+03:00`. Нові TextBlocks не створюють фальшиву дату оновлення Page. Нових schema types, FAQ, authors або ratings немає.

Збережено 15 наявних content IDs і 12 distinct старих fragment targets: 0 missing/duplicate target failures. Навігаційні дані збережені; 26 entries ведуть на 18 distinct local destinations у двох категоріях (10 conditionals + 8 modal-verbs). Усі 18 перевірені GET і повернули HTTP 200; сусідні уроки не переписувалися. Перша sandbox-спроба не отримала HTTP-відповіді через недоступний loopback; успішний host-authorized read-only повтор не є production request.

## Guarded local apply і збереження даних

Зроблено незалежні source/DB/HTTP BEFORE до запису, physical served-root proof і звірку web/CLI connection identity без публікації секретів. Ціль визначали exact owners, UK, ancestry, application/document root і локальний host, не лише slug або назву APP environment.

Порядок: fresh proof → fresh preview → exact diff review → exclusive private backup → guarded transaction → postconditions → fresh AFTER preview → repeated no-op → live acceptance → read-only comparisons.

Фактичний diff: **24 updates / 4 inserts / 0 deletes**:

- 3 `pages.text` updates: IDs 45/72/74.
- 21 `text_blocks.body` updates наявних UK rows, включно з трьома subtitle blocks.
- 4 нові UK TextBlocks: три practice-set і Can/Could comparison-table.
- IDs/UUID, type/column/level/sort, решта старих metadata й timestamps збережені; EN/PL не змінено.

Приватний backup створено exclusive до transaction та залишено в ignored local evidence; він не входить у Git. Повторний AFTER preview має state after і 0 changes; repeated apply завершився no-op **0 updated / 0 inserted / 0 deleted**, без повторного backup. Масового reseed/migration/restore або автоматичного HTTP/startup apply не було.

Фактичний `after-v2` і фінальний `after-v3` після browser acceptance підтвердили 6/6 HTTP 200 (три theory + три course), 46 protected SQL table fingerprints без позаскоупних змін, bank/progress changes 0, visual changes 0, course changes 0. Linked-bank inventory — 432 + 504 + 294 = 1230 links; існуючі banks/widgets/relationships не переписані.

Фінальний GET локального sitemap повернув 200: 554 URL, точний URL-set і raw XML SHA-256 незмінні щодо свіжого BEFORE. Production URL у XML прочитані як дані й не відвідувалися.

Окреме парне порівняння з реального BEFORE підтвердило course teaching DOM byte-exact 3/3: 181434 serialized bytes, 1066 elements, 9412 punctuation-sensitive text characters. Metadata та robots header також однакові. Це точна teaching-DOM parity, а не необґрунтована заява про byte-exact усього HTTP із динамічними tokens; access gate не обходився.

Тимчасовий GET-only local proof route не писав у БД й не повертав secrets; після apply/no-op видалений. `routes/api.php` відновлено byte-exact до BEFORE разом зі сторонніми початковими змінами; окремий GET колишнього nonce path повернув 404. SHA відновленого routes file: `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Сам nonce, proof і connection fingerprints не публікуються.

## Незмінність дизайну та finite невізуальна інтеграція

Свіжий visual inventory: **326 файлів, 0 змін**. Незалежний immutable non-target source inventory: **1530 файлів, 0 mismatches**, включно з попередніми packages/maps. Source sync перевірив 15 scoped paths; raw checks відповідають receipt, для `TheoryCourseManifestService.php` окремо задокументована тільки дозволена EOL-відмінність між checkout copies. Вісім frozen content files мають повну raw ROOT/WT parity без EOL-винятків.

M49 не змінював Blade, універсальну в’юху, CSS, fonts, frontend JS, practice engine, header/sidebar/background або канонічні visual components. Чужий повний ROOT Blade diff збережено, не замінено WT-файлом. Порівняння актуальної factory/AnswerVariants не слід плутати із твердженням, що весь ROOT Blade тотожний WT.

Чотири shared data hooks:

- `app/Support/TheoryContentSource.php`: реєструє exact-bound M49 source context у чинному registry.
- `app/Support/TheoryHtmlAdapter.php`: finite M49 validDesign/pointPanels dispatch, без нового renderer.
- `app/Support/TheoryLegacyAdapter.php`: M49 bilingual correction за перевіреним власним plan; старі predicates не послаблює.
- `app/Services/TheoryCourseManifestService.php`: повертає cloned BEFORE presentation лише для трьох exact M49 course consumers; не змінює model/DB або чужі callers.

Нові `M49AdviceModalsPackage`, `M49ContentPatch`, `M49LocalTargetGuard`, `PatchM49Content` та `tools/diagnostics/m49-working-local.php` — finite package/guard/explicit CLI entry на чинних primitives. Вони перевіряють identities, дві ancestry, locale, body/source hashes, stale plan, UUID collisions і точні detail owners. Контент не задає CSS-класи, executable view names чи scripts. Немає M49-specific sections/styles/practice views або прихованого редизайну.

## Автоматичні перевірки

### Ізольовані PHP

Тести працювали з isolated test storage/SQLite, не з робочою MySQL або working `.env`. Guard/patch fixtures перевіряють різні категорії, foreign owner/locale/ancestry, substantive source mutation, stale plans, collisions, rollback, no-op, незмінність metadata/ENPL та protected consumers.

| Фінальний coverage | Унікальні tests | Assertions | Результат |
|---|---:|---:|---|
| M49/M46/M47/M48 patch і local guards | 271 | 1567 | PASS |
| Packages M49/M46/M47/M48, TheoryContentGroups, universal view, rollout 42 | 45 | 27517 | PASS за завершеними suites із correction нижче |
| Разом без повторного рахування rerun | 316 | 29084 | Фінальних failures/errors/skips немає |

Перший combined package run: 45 tests / 27412 assertions, одна failure у новому M49 test harness — він не врахував чинний subtitle content anchor. Очікування виправлено за незалежним наявним dispatcher контрактом, не runtime/master. 37 інших tests пройшли тоді; повний виправлений M49 file окремо пройшов 8 tests / 1260 assertions. 45/27517 — фінальне покриття цих непересічних результатів, не вигаданий другий 45-test run. Ці вісім не додані вдруге до 316.

Runs повідомляли одну deprecation, не failure; без окремого trace її причину не вигадуємо. Runner підтвердив **46661 protected filesystem files**, 0 changes. Це кількість файлів, не SQL tables. Додавання нового versioned Node test під час початку protected-window coordination було явно повідомлено; protected-file receipts лишилися 0 changes, їхній scope не розширюється заднім числом.

### Offline actual practice factory

| Окремий run | Результат |
|---|---|
| Новий frozen M49 suite | 21/21 PASS; 18 controls, 47 options, 23 правильні видимі альтернативи, 173 combinations |
| M48 native practice regression | 20/20 PASS |
| M47 native practice regression | 19/19 PASS |
| ROOT M46 + legacy practice/retry/contractions aggregate | 208 PASS / 6 FAIL із 214 |
| Уже чинний WT contractions wrapper, окремо | 6/6 PASS |

Усі шість ROOT failures — старий partial-method contractions harness `this.item is not a function`; його не переписували/не синхронізували задля PASS. Окремий WT run не підміняє ROOT aggregate. Runs не підсумовані як додаткові унікальні passing tests.

M49 виконує фактичний незмінений inline Alpine factory та AnswerVariants: ’d better розгортається як had, `would better` відхиляється; ’d rather — would. Перевірено accepted alternatives, actor/negation/punctuation, empty/partial, wrong→wrong→edit correct, очищення stale feedback, repeat без duplicate score, reset та exact authored translated feedback усіх 47 options. Factory/master/variants/contractions bytes до й після однакові; network calls 0.

Pre-freeze приватний run спочатку мав 20/21 через надто вузьке harness-очікування лише canonical Could-you перекладу. Додано явні точні переклади інших accepted answers, не послаблено semantic grading; фінальний pre-freeze 21/21. Це виправлення fixture, а не прихована зміна frozen content.

## Live browser acceptance

Фактично перевірено всі 18 controls і 47 видимих options: оцінювання та score збігаються з незалежними очікуваннями; 23 correct visible options. Для кожного уроку виконано empty → wrong → wrong → edit correct → repeat check → reset. Кілька accepted alternatives лишаються одним control/балом.

Browser receipt містить generic feedback-prefix flag, який не є exact текстовим порівнянням: частина правильних пояснювальних feedback має інший початок. Це не перетворено на вигаданий 47/47 live exact-feedback PASS; повний текст усіх feedback окремо підтверджує frozen Node suite.

Усі три point-level disclosures перевірено з initial closed, незалежним Enter/Space, правильним owner, deep link і reload. Текст не завантажується після натискання: SSR audit окремо підтверджує всі 4 фрагменти та 7 EN–UK units.

### Viewport/theme matrix — завершено

Основна матриця: 3 × (1440×1000, 390×844) × (light, dark) = 12 states; додатково 3 × 320×844 × (light, dark) = 6 states. Разом 18 унікальних станів з actual dimensions і темами. У кожному послідовно пройдені всі H2-секції до footer: Had better 6, Can/Could 7, May/Might 6, включно з існуючим own-bank блоком. Full-page screenshots збережені й візуально переглянуті; fonts loaded і перевірки Manrope/Archivo успішні у всіх 18 станах.

Чотири свіжі read-only еталони (PPC forms і три M27) перевірені окремо, не додані до 18 target states: однакові 1440×1000/light, expanded sidebar, CSS/font environment. Нові змістові пункти мають ті самі нейтральні панелі без кольорової обводки; computed border — 0px. Приклади, headings і практика лишаються штатними.

Непоміщення навчального контенту поза керовані області не виявлено. Чотири Can/Could mobile table states мають cells поза viewport, але вони обмежені власною існуючою horizontal-scroll областю (252px container / 576px scroll width); клавіша ArrowRight реально змінила scrollLeft із 0 до 45. Невеликий document overflow 3–16px окремо віднесений до наявних декоративних SPAN background elements, а не навчального контенту. Global overflow:hidden або CSS-маскування не додавалися.

Для disclosures Enter відкриває, Space закриває потрібний owner; власники — block-1778, block-2399, block-8996. Deep link/reload зберігає native initial closed. Після приймання viewport override скинутий, light theme відновлена, початкову користувацьку вкладку не змінено. Browser zoom не використовувався.

### Чесні межі JS-off, print та zoom

Live JS-off і print preview не виконані: поточний browser interface не надає потрібної capability. Server HTML містить basic і full details, але serialized answers у script не є доступним no-JS ключем. Окремого native no-JS answer key не додано; grading залежить від JS. Наявність print CSS не доводить друк. Browser zoom поза scope і не змінювався. Ці обмеження не маскуються заявою про повне приймання.

## Доставка

Branch: `codex/seo-m49-advice-modals-standard-view`.

Контентне приймання та фінальні after-v3/sitemap comparisons завершені. Доставка — scoped commit і normal push цієї робочої гілки після explicit staged scope review та `git diff --check`. Підсумковий повний SHA й результат remote SHA == HEAD наводяться у фінальному handoff; звіт не містить само-посилального SHA власного commit. Main/production/PR/merge/force push/deploy не входять у виконання.

У commit мають увійти лише пов’язані перевірені versioned source/hook/test/documentation files. `.env`, backup/dumps, private proof/receipts/screenshots, caches, generated assets і сторонні dirty changes виключені. Історичні звіти M46–M48 не переписувалися.
