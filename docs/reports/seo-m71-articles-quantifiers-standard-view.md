# M71 — Articles, Zero Article, Quantifiers

Дата: 2026-10-20. Єдина ціль застосування й HTTP-приймання — `http://gramlyze.loc`. Production, `main`, PR, merge, force push, workflow dispatch і деплой не входять у M71.

**Статус: локальну реалізацію та приймання M71 завершено з явно зафіксованими native-shell обмеженнями.** Виконано guarded apply, незалежні SQL postconditions, target SSR gate, повний AFTER registry, old-consumer preservation, реальне browser/navigation acceptance та final read-only audit після браузера. Перевірено scoped staged allowlist і raw Git-index bytes. Результат Git delivery та full SHA наведено у фінальній відповіді. Browser результати нижче є окремими фактичними перевірками, не висновком лише з HTTP 200 або fixtures.

## База, межі та сторінки

Фактична accepted база: `1291b576b47dc28e5ba9e0c58faa052f6e49460e` — фінальний M70, а не старий M69. Робоча гілка за scope: `codex/seo-m71-articles-quantifiers-standard-view`. M69 1.0.1, M70 1.0.0, accepted PPC retry та інші пізніші виправлення з цієї бази не відкочувалися. Full delivery SHA й незалежна звірка remote SHA == HEAD повідомляються у фінальній відповіді; цей звіт входить у той самий scoped commit.

Змінюються лише три UK Page owners категорії `imennyky-artykli-ta-kilkist`, category ID **16**. Категорія, сусідні сторінки, курси, EN/PL, окремі тести й персональний прогрес не є цілями запису.

| Сторінка | Page / Category | Technical / display level | Збережений H1 |
|---|---|---|---|
| [Articles — A / An / The](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/articles-a-an-the) | 82 / 16 | NULL / A1–A2 | Articles: a / an / the |
| [Zero Article — Нульовий артикль](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/zero-article) | 91 / 16 | NULL / A1–A2 | Zero article |
| [Quantifiers — much, many, few, little](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/quantifiers-much-many-a-lot-few-little) | 90 / 16 | NULL / A2–B1 | Quantifiers: much, many, few, little |

Повні owner identities:

- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityArticlesTheorySeeder`
- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityZeroArticleTheorySeeder`
- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityQuantifiersTheorySeeder`

Відсутність source level не використано як доказ SQL NULL: фактичні binding/metadata встановлено окремим BEFORE і guarded preview. Нові базові завдання орієнтовані на A2; це не metadata transition і не зміна SQL level/bank filter.

## Редакція та OLD → AFTER

Збережено **39 continuing structured slots**, їхні типи, порядок, columns, UUID, levels, anchors, tags та зв'язки. Subtitle rows також continuing; нових Page owners, aliases, category segments, блоків практики або навігації немає.

- **Articles:** a/an пояснено як дві форми одного неозначеного артикля. Вибір визначає наступний вимовлений звук усієї іменникової групи; є adjective + noun, німа h, /j/ та задане читання MBA літерами. Перша згадка, of/that/which і сам факт уточнення не перетворюються на автоматичний the. Розмежовано професійний noun і nationality adjective, означену множину/незлічуване значення, my/this та обмежені усталені моделі. Загальне вподобання музики відділено від конкретної музики на заході.
- **Zero Article:** Ø залишається навчальним символом, не англійським словом і не порожньою відповіддю. Пояснено загальне значення та неозначені plural/mass у конкретному епізоді; немає твердження «усі без винятку» чи «ця категорія завжди без артикля». School/bed залежать від мети, hospital має UK/US межу; by bus/car — модель способу пересування, не універсальне правило для by. Прийоми їжі, дні, сезони та власні назви мають потрібні контрасти. Усунено хибне правило «без Lake завжди the» та неактуальний приклад про реального посадовця.
- **Quantifiers:** many/much у ствердженні не названо загальною граматичною помилкою; стиль відділено від форми. A lot of/lots of можливі в різних типах речень; of перед названою групою й відсутність of без повтореного noun показано окремо. Few/little підкреслюють малість, a few/a little — наявність кількох/трохи; достатність і бажаність установлюються фактами. Few time виправлено як noun-type помилку. Not many/not much перекладено як «небагато».

Межі трьох уроків збережено: Articles не дублює весь Zero Article; Quantifiers не замінює M70 Countability і не поглинає Some/Any. Корисний старий матеріал збережено за навчальною функцією, конкретні неточності виправлено, нові контексти й завдання позначено в maps. Повтори скорочено редакційно, не зменшенням шрифту чи приховуванням basic.

Повна editorial/component/task карта: `docs/content/m71-articles-quantifiers.editorial.v1.md` та `docs/content/m71-articles-quantifiers.components.v1.json`. Вони містять OLD task → AFTER group/index, native slot bindings, metadata й body hashes. Незалежний source BEFORE: `docs/content/m71-before-source.v1.json`.

Point-level details: **0 на кожній сторінці** за явними finite visible-basic рішеннями. Необхідні правила, переклади, застереження та приклади видимі одразу. Немає runtime word threshold або штучної квоти кнопок. Keyboard/owner/deep-link acceptance прихованих details — **N/A**, не вигаданий PASS.

Нові приклади та exercises авторські. Мовна перевірка — агентна перевірка за первинними редакційними джерелами, **не human review**. Editorial map розрізняє direct main body, indexed body, 403 та неперевірений окремий URL; learner comments не використано як нормативне джерело. Зокрема, direct Cambridge grammar fetches із 403 не названо успішним прямим читанням або redirect. SEO-орієнтир — корисний самостійний матеріал, не word quota чи гарантія індексації. Search Console, sitemap/robots refactor та production-індексацію не змінювали.

## Практика: збережено, виправлено, доповнено

| Урок | Старі позиції, збережені й виправлені/уточнені | Нові graded tasks | Own graded tasks AFTER | Select / meaning / input / graded rephrase | Ungraded demo |
|---|---:|---:|---:|---|---:|
| Articles | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 |
| Zero Article | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 |
| Quantifiers | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 |

Разом **39 own graded tasks = 27 старих навчальних позицій + 12 нових**; три ungraded demos рахуються окремо. Це не твердження, що всі 27 позицій коректно оцінювалися BEFORE: два порожні Zero keys не входили в grading. Старі rephrases збережено видимими, не заховано в JSON. Початкова відсутність старих task IDs/source_index збережена; стабільні IDs мають лише нові tasks.

Один старий Zero input форматно перенесено у підтриману full-sentence модель **в тому самому input index і practice UUID**. Це підмножина дев'яти збережених позицій, не додаткове завдання. Нових повних sentence inputs — шість; разом із цією виправленою старою позицією — **7 full-sentence paths**. Десять native token banks включають також три короткі Articles noun phrases; їх не зараховано замість повних речень.

Реальні BEFORE розбіжності були окремо відтворені в UI та unchanged factory:

- Articles просило ввести a/an, але приймало повну групу. AFTER прямо вимагає всю групу; короткий артикль не видається за виконане завдання.
- Zero відхиляло натиснуте `Ø (без артикля)` за ключа `Ø`; два `answer = ""` були виключені з grading. AFTER використовує свідомий непорожній вибір **«Без артикля»**; untouched, пробіли та Check після Reset не отримують бал. Старі два пропуски в одному control замінено повним реченням `I go to work by bus.` — «Я їжджу на роботу автобусом». У речення не вставляється Ø або український label.
- Quantifiers мало видимі ordinal options, які не збігалися зі словесними keys; AFTER варіанти є фактичними much/many тощо. Дозволені many/a lot of/lots of явно пов'язані із заданою кількістю та приймаються. Виправлення не змінюють час, виконавця, місце або сферу заперечення.

Isolated unchanged-factory run перевірив **39 tasks, 44 visible options, 46 accepted values**, усі canonical/aliases і distractors, empty/whitespace, wrong → edit correct, повторний Check без подвоєння score, Reset → empty Check, незалежні group scores, exact case/token instances, видалення/повернення та rebuild. Окремий synthetic repeated-a probe не додано до 39 own tasks. Feedback лишається plain text для x-text: англійська відповідь, український переклад і пояснення; без HTML/entities чи нового grader. Unsupported author fields у фінальній проєкції — **0**.

## Окремі тематичні конструктори

| Урок | Continuing practice UUID | Source index / sort | SQL technical level | Серверний widget/payload | Реальний browser 5/5 |
|---|---|---|---|---|---|
| Articles | `f1edde68-82a6-56d6-8aed-7b5dc7b969c0` | 10 / 11 | NULL | 1 widget / 5 питань | **5/5 виконано** |
| Zero Article | `e0348abd-f7ce-509e-9329-462c974db20a` | 12 / 13 | NULL | 1 widget / 5 питань | **5/5 виконано** |
| Quantifiers | `44661475-7c1a-5267-b9ba-261101001cc9` | 11 / 12 | NULL | 1 widget / 5 питань | **5/5 виконано** |

Точні тематичні банки:

- `Database\Seeders\V3\Polyglot\PolyglotArticlesAAnTheAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotZeroArticleAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotQuantifiersMuchManyALotFewLittleAllLevelsLessonSeeder`

Окремий contract: `docs/content/theory-sentence-builders/m71-articles-quantifiers.v1.json`; finite audit: `docs/content/m71-articles-quantifiers-bank-audit.v1.json`.

Обрано **15 різних reused питань type 4, intended A2**. Фактичний SQL NULL filter збережено; A2 selection досягається exact-bound clone-only exclusions, не зміною metadata чи послабленням owner/locale/theme/type. Нових питань, options, answers, links і hints — **0**. Render-only уточнення умов потребує повного snapshot binding; presenter не виконує SQL і не мутує original models. Невідомі, NULL або stale decorations мають повний fallback.

Target-only read-only gate: **3 targets, failed 0, writes 0**. Повний AFTER registry gate також виконано: **136 targets, failed 0, writes 0**. Усі **133 попередні owner-keyed structural records** збережено exact; додано рівно три M71 owners. Transport hashes і validated dynamic payloads обліковуються окремо від структурного contract. Число 136 є результатом цього run, не постійним hardcoded лімітом. Обидва gates підтверджують matcher/server payload; browser visibility/Check/Next і три completions 5/5 перевірено додатково окремим UI run.

## Єдина в'юха та невізуальна інтеграція

Залишено канонічний шлях `theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. Немає M71 Blade/CSS/JS, незалежного renderer, нового component type або practice-в'юхи. Header, sidebar, background, шрифти, spacing, example/header/feedback оформлення й grader не змінюються.

Невізуальні зміни обмежені:

1. Thin M71 data/release, existing-writer/guard та command profiles на чинних `TheoryNativeContentRelease`, `TheoryNativeContentPatch`, `TheoryLocalReleaseGuard`; новий framework не створено.
2. Exact owner/locale/body registration та finite clone selection для трьох continuing practices.
3. M71 dispatch hunks у восьми shared compatibility/resolver файлах: канонічний source, adapter corrections, native bindings, matcher exclusions, course та standalone-help preservation. Object-only callbacks захищені перевірками null; foreign consumers не отримують theory redesign.

Контент користується дозволеними semantic accents чинних компонентів. Немає пакетних CSS-класів чи вибору дизайну за URL/M-номером. Metadata transitions — **0**. New `M71SharedLineageTest` відновлює accepted M70 код копій лише через exact owned hunks; старі raw assertions не переписано.

## Freeze 1.0.0 та checksums

UTF-8 без BOM, LF, один кінцевий LF і whitespace перевірено до hashes. Найбільший serialized body — **15 946 bytes** із фактичної TEXT-місткості **65 535**; DDL для обходу ліміту не змінено. Перед freeze виконано агентну мовну, option/key/alias/plain-feedback та static width перевірку: 72 title/formula fields × 4 fallback fonts, 0 unbreakable-token risks за 194 px. Це static sizing, **не browser layout PASS**.

| Frozen artifact | SHA-256 raw bytes |
|---|---|
| `docs/content/m71-articles-quantifiers.v1.0.0.json` | `95f15dcbb849bc6ecb8e666d51dec521c0b6c598800e3f90cf4458a98f94f6d0` |
| `docs/content/m71-before-source.v1.json` | `75d6fbf91ccbee4f695c91c0c3e2118de66f03fa213c6bf595d961b5d711aeb8` |
| `database/content-patches/m71-articles-quantifiers.v1.json` | `6478604f7b9a4af998cf462d4031467519a407c1303f0698ec662cfa5d514d2a` |
| `docs/content/theory-sentence-builders/m71-articles-quantifiers.v1.json` | `3fdee3c0a39bc03845b8a6c1698d2d866b7f15423e863b29036b4953edbcf8e7` |
| `docs/content/m71-articles-quantifiers.components.v1.json` | `999a5e9dc467c4752d5e29858edd782e78a40f0fe89fe5bac617bb3c5175b647` |
| `docs/content/m71-articles-quantifiers-bank-audit.v1.json` | `5bb43042588c644fd3f88e1dbf2a6fc36e803a5d453a4c64895d347c58b81c26` |
| `docs/content/m71-articles-quantifiers.v1.0.0.uk.md` | `36698c1ca62a6c07f94677c10288d175cb832d7f054144274c83a6229a67589d` |
| `docs/content/m71-articles-quantifiers.editorial.v1.md` | `16c4a942cd031c1cac02a8fcd05b50663c4a4e39642dceacad75f986a056c02b` |
| `docs/content/m71-source-checksums.v1.json` | `7a98d657c93b8ddbf0a5f8ae011724a1168a35ac0b96ce7783d592dd595ba99d` |

Active definition canonical Git-index raw SHA-256:

| Owner definition directory | SHA-256 |
|---|---|
| `NounsArticlesQuantityArticlesTheorySeeder/definition.json` | `771d9fb73b038db9757a8a5a5e816a7c909f30fdb6faa5037eaaceec10e45e7b` |
| `NounsArticlesQuantityZeroArticleTheorySeeder/definition.json` | `96fde371fccb71f855f9948f4e1d71b6f15267ee13d8aa05e0d1f3510135c6b4` |
| `NounsArticlesQuantityQuantifiersTheorySeeder/definition.json` | `db4410156c683f216120b92a5f4f71bb3b314b47256606a2505531a3c319bcd8` |

Усі три definition directories розташовані в `database/seeders/Page_V3/NounsArticlesQuantity/`. Історичні frozen releases, manifests, maps і звіти не переформатовувалися. Зміна frozen навчальних bytes після цієї версії потребуватиме нового exact diff/version, не старого receipt.

### Точна EOL-межа активних working copies

Фінальна staged-byte перевірка виявила, що три активні ROOT/WT definition copies зберегли **один кінцевий CRLF**, тоді як уже frozen canonical проєкції вище мають LF. Навчальні JSON bytes до останнього newline однакові. Цю першу невдалу перевірку не названо PASS; frozen пакет/manifest не переписували й нову навчальну редакцію не створювали.

Виправлено лише публікацію в Git index: для **цих трьох точних шляхів** встановлено `text eol=lf` і виконано path-scoped renormalize. Всі 12 frozen/active Git blobs збігаються з уже frozen raw hashes і sizes; широкого renormalize не було. Served ROOT/WT files, runtime код і SQL не змінювали, тому їхні source-sync/preview/postcondition pins залишилися чинними. Для кожної копії окремо звірено, що єдина відмінність від Git blob — один фінальний CR; raw working equality з LF не заявляється.

| Working ROOT/WT definition | Незмінний raw SHA-256 із source-sync |
|---|---|
| Articles | `dca38bceac03accdee1dede687a6b6960da9bc3fdaa59e96baefca51368570b3` |
| Zero Article | `721caf13ac1353948acc3fbb0d749bccfdcce67fe8f3e8364e93eb2008ffb0ed` |
| Quantifiers | `0a84b9d0eb76fcf16ca678c6a3874e2a05d0a7706deffa63c3544805a7cfd70b` |

## Guarded local apply та незалежне збереження

Порядок виконання: незалежні source/DB/HTTP BEFORE → fresh physical/web/CLI local proof → fresh preview → reviewed exact allowlist → exclusive private backup → transaction → незалежні postconditions → два повторні no-op → target HTTP/SSR gate → повний registry та old-consumer HTTP/payload preservation → реальне browser/navigation acceptance → фінальний read-only preservation audit після браузера.

Reviewed plan digest: `553a453b298512e27d9ef7aa0210ce7b3e832627dafdb104b99546fc26722f21`.

- Preview/exact review містив **45 update operations, 0 inserts**.
- Перший apply attempt у sandbox не зміг відкрити PDO connection: **0 DB writes, backup не створено**. Повторний запуск із необхідним exec permission пройшов той самий local-target guard і погоджений preview/diff та виконав apply; guard, environment label або allowlist не послаблювалися заради обходу помилки.
- Після apply незалежно підтверджено **45 distinct changed existing rows**: 3 `Page.text` та 42 existing UK text-block bodies. У 42 входять 3 subtitles, 3 existing practice bodies та 36 інших continuing structured bodies.
- **0 inserted, 0 deleted rows**; три practices залишилися на своїх UUID/source index/sort/NULL level. Нових practice blocks — 0; metadata transitions — 0.
- Обидва repeated apply дали **0 updated / 0 inserted / 0 deleted**. Це окремі no-op, не додаткові 45 змін.
- Збережено всі **42 continuing UK rows**, **84 EN/PL rows**, timestamps і metadata поза дозволеними body/text полями.
- Незалежний read-only audit відновив exact BEFORE projection усіх **46 таблиць**, нормалізуючи лише переглянуті цільові поля. Question-family rows, links, hints, персональний прогрес, курси й category learning descriptions не змінено.
- Hash inventory підтверджує **326 visual files** і **2545 файлів контрольованого frozen inventory** без byte drift; M70/попередні accepted sources і навчальні дані залишилися чинними. Окреме HTTP/payload preservation old consumers і navigation/non-target learning-DOM acceptance також підтверджено нижче.
- Тимчасовий proof route видалено. Перша cleanup EOF-звірка виявила розбіжність в один кінцевий newline; її виправлено точним відновленням початкового формату. Остаточні routes bytes збігаються; SHA до/після `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Окремий GET після exact restoration повернув **404**, без redirect. Початкову невдалу EOF-звірку не названо PASS. Серверні конфігурація/ACL/hosts не змінювалися; runtime secrets у report/commit не включаються.
- Після browser acceptance повторний незалежний READ ONLY audit v3 підтвердив той самий дозволений стан: **45 distinct changed rows / 46 таблиць**, без нових inserts/deletes, metadata/question-family/progress змін або source drift. Початковий post-apply audit і остаточний after-browser audit мають окремі receipts.

Ключові непрозорі private receipt hashes, без копіювання snapshots/SQL або runtime identity:

- exact diff review: `6b5a023d2af377f4b5cf3b8237d6406977807d7372bcfb6115464988ed4c7500`;
- post-apply read-only SQL/source preservation v1: `378d2fba5bc50c8d78cdcb5f5157921a11629909bf9121302e7b24f40896cc41`;
- final after-browser read-only preservation v3: `44afe7ad14cd2965854d193b628a8c1bff8f791c93190cb63c6f7ad8bdac5bda`;
- proof cleanup/404: `772cdd20610fbf958f243482b59f8b489142d74c0ea85fe675793283cd720664`.

## HTTP/SSR, metadata й фактичне live-приймання

Три exact target GET повернули **200**. З frozen author projection перевірено **918 SSR learning fields**, **1268 classified author fields**, **0 unsupported author fields**. Невидимі JSON fields не зараховано як навчальний матеріал.

На кожній сторінці один H1; raw Page.title, H1/title, breadcrumb, canonical і LearningResource identity збережено. Canonical не підмінено local URL; локальний `noindex, nofollow, noarchive` лишився. Expected descriptions обчислено до apply чинним сервісом і звірено після. Збережені старі anchors: **24 / 26 / 25**, missing — **0**.

У кожній навігації збережено 9 labels/current states, destinations установлені підтриманим URL field через фактичний resolver. Target SSR підтвердив очікувані local destinations без вигаданих segments або `#`. Окремий navigation run виконав **20 actual local GET 200**: три M71 targets і 17 non-target pages/categories; усі resolved paths доступні.

Для **13 non-target learning DOM** підтверджено exact повний збіг. Ще на чотирьох старих noun pages чинний native selector формує випадкові п'ятірки. Для них exact решту static learning DOM перевірено після окремого підтвердження одного native five-question JSON array; тільки No/None також має selection-dependent native hint. Це **не** full raw HTML equality і **не** твердження про семантичну еквівалентність довільних нових random IDs.

Перевірені native TAG/fallback candidate windows: Partitives — **12**, No/None/Neither/Either — **200**, Articles with Geographical Names — **12**, Generic Reference — **68**. До і після вибір належить відповідному чинному native window; versioned sources, SQL і статичний learning DOM перевірені незалежно. M71 не розширює фільтри цих нецільових уроків і не підставляє свої A2 banks у їхні widgets.

Початковий приватний navigation audit v1 мав неправильне припущення про link-only добір. Виправлено лише private audit відповідно до незмінного native TAG/fallback механізму; runtime, sources і старі питання не змінено. Невдалий v1 receipt збережено, остаточний v2 має explicit legacy-random boundary.

SSR receipt SHA: `be5f926b3a3565406a96af54411798737edaae243943611f4f986173fa45a2c4`.
Target sentence-builder gate SHA: `69c966b0b2be01d8ceb7a34efc1ade6b9451e2beae806f4fcb88f6735c76dbe6`.
Full dynamic registry gate SHA: `1205ed0ed3ae225fe293edb1ee392c56d908f357251683009922850fbd8ab4ad`.
Navigation v2 receipt SHA: `0f780f018edda6d2cb327d5293cee0e5085e8140e424ae1a826452c7173227e3`.

### Old-consumer preservation

Виконано **27 фактичних local HTTP 200**: 3 courses, 14 SSR responses (включно з 4 compose responses) та 10 JSON responses. Scope охоплює **7 saved tests і 456 старих questions**; це окремі consumer inventories, не 456 нових M71 завдань.

Збережено exact `old_sql` і всі **27 structured consumer records** проти незалежного BEFORE. Там, де чинний consumer shuffle змінює порядок, порівняння виконується за перевіреними question/record identities; це не твердження про побайтову однаковість усіх full HTML responses. Course learning DOM збігається exact. Усі **46 SQL tables** лишилися незмінними під час GET і відповідають уже перевіреному final AFTER: ці HTTP checks не створили сторонніх DB змін.

Old-consumer receipt SHA: `c54b9c77e8eb9841277ac6d578df840a910c0316435193f785ca9e3c721cc6d6`. Ця перевірка є окремою від browser acceptance цільових сторінок та navigation/non-target learning-DOM приймання.

### Реальний браузер: 18 states, практика й 5/5

Пройдено **18 фінальних states**: три уроки × actual DOM viewport **1440×1000 / 390×844 / 320×844** × light/dark. Кожний урок переглянуто до footer з усіма навчальними компонентами та перекладами; fonts loaded, own practice і рівно один linked widget видимі. Static sizing не підміняє цю перевірку.

- Пройдено всі **39 унікальних own graded tasks**. Перевірено **44 унікальні visible options**; загалом 45 option attempts, бо одна locator/context спроба повторювала вже перевірений варіант. Повтор не додавався до унікального count.
- В actual DOM перевірено **28 input/rephrase accepted values** і **18 select/choice canonicals** — разом **46**. Після Check прочитано feedback; це не лише наявність keys у JSON.
- Виконано **24 cases для шести нових full-sentence tasks**: 3 уроки × 2 tasks × 2 способи (manual/tokens) × 2 розміри (1440 desktop / 390 mobile), усі пройдено. Окремо пройдено ще **4 short/retained token paths**, включно з переробленим Zero input0; їх не зараховано до 24 нових cases.
- Retry/reset cases виконано на всіх трьох уроках: empty, whitespace, wrong → edit correct, повторний Check, Reset, незалежність group scores. У Zero свідомий непорожній вибір підняв score **0/4 → 1/4**; untouched/whitespace/reset не отримали бал за відсутність артикля.
- Articles також перевірено mixed editing: вручну введено a, токеном додано university; після ручного видалення токени повернулися до банку. Штатні token instances, case і multiplicity збережено.
- Усі **три linked widgets завершено до 5/5**, разом **15 унікальних тематичних питань**. Перевірено visible українські conditions, банк/область складання, Check, Next і completion; BANK locators не плуталися з answer chips.
- Details — **0**, тому initial-closed/keyboard/detail-owner/deep-link acceptance є N/A. Не створено штучного detail для отримання PASS.

Learning-area неконтрольоване горизонтальне переповнення — **0 поза чинними table `overflow-x-auto` containers**. Native comparison tables зберігають штатне локальне прокручування. Окремі raw shell metrics не прирівнюються до цього результату:

| Actual viewport | Articles scrollWidth/clientWidth | Zero Article scrollWidth/clientWidth | Quantifiers scrollWidth/clientWidth |
|---|---|---|---|
| 1440 desktop | 1425 / 1425 | 1425 / 1425 | 1425 / 1425 |
| 390 mobile | 398 / 375 | 404 / 375 | 395 / 375 |
| 320 mobile | 323 / 305 | 327 / 305 | 331 / 305 |

Body має чинне `overflow-x: hidden`; mobile shell raw metrics **не нульові**. M71 не заявляє їх виправлення або redesign і не маскує новими CSS. Browser verdict має явну межу: `local-browser-accepted-with-native-shell-metric-limits`.

Viewport override скинуто, залишено світлу тему; browser zoom не змінювався. Global localStorage і персональний прогрес не очищалися. Screenshots/checkpoints — private, не commit artifacts. Print, no-JS та повний WCAG-аудит — **not_checked**, не PASS.

Browser receipt SHA: `73c4d216a8b11f3377e40ebb9f023b089fdb2d499e7575684bead894e73bfc25`.

| Приймання | Поточний статус |
|---|---|
| Exact target HTTP, metadata, SSR learning fields і один widget/5 payload questions | виконано |
| 18 browser states: 3 owners × 1440×1000 / 390×844 / 320×844 × light/dark | виконано, actual DOM viewport підтверджено |
| Перегляд до footer, fonts/assets, learning/shell overflow окремо | виконано; shell metric limits явно наведено, не названо нульовими |
| Щонайменше 24 desktop/mobile full-sentence manual/token cases | виконано: 24 нових cases + 4 окремі retained/short token paths |
| Усі retained/corrected/new tasks, фактичний DOM feedback, scores/retry/reset | виконано: 39 tasks / 44 unique options / 46 accepted values |
| Кожен linked widget до реальних 5/5 | виконано: 3 × 5/5, 15 унікальних питань |
| Navigation/non-target HTTP/learning-DOM preservation | виконано: 20 GET 200, exact 13 full + 4 validated static DOM |
| PPC interaction identity regression | окремий isolated gate: 6 tests / 161 assertions, не live PPC browser matrix |
| Old course/saved-test/standalone-help consumers AFTER проти BEFORE | виконано: 27 HTTP 200; structured records, old SQL і course learning DOM збережені |
| Повний dynamic registry AFTER і структурне збереження попередніх 133 | виконано: 136 targets, failed 0, writes 0; 133 old + 3 new |
| Print, no-JS, повний WCAG-аудит | **not_checked**, не PASS |

Змінений full HTML на старих noun pages не названо ні автоматичною регресією, ні автоматично безпечним: navigation v2 окремо звірив source/SQL/static learning DOM і чинні dynamic selection windows. Global storage не очищено; недоступні print/no-JS/WCAG режими лишаються not_checked. Safety stop не обходився.

## Ізольовані тести: фактичні counts і межі

Робоча `.env` не завантажувалася. Bootstrap переносить environment/cache/view/storage до owned test runtime; перед kernel bootstrap задає SQLite `:memory:`, array cache/session та sync queue. `assertSafeDatabase` перевіряє SQLite PDO і PRAGMA до schema operations. Fresh runtime proof показав рівно одну resolved connection — SQLite `:memory:`, actual MySQL count **0**. Synthetic progress rows належать лише in-memory fixtures; персональний прогрес не відкривається.

Runtime: **PHP 8.5.10 CLI NTS**, PHPUnit **12.5.35**, `opcache.enable_cli=0`.

| Окремий фактичний run | Tests / assertions | Failures / errors / skips | Deprecations |
|---|---:|---|---:|
| Final current-runtime, 28 explicit classes | **179 / 38 201** | **0 / 0 / 0** | 1 |
| Pre-apply M71, усі 6 класів | **38 / 8 860** | **0 / 0 / 0** | 1 |
| Historical literal-source/raw-lineage | **7 / 115** | **6 / 0 / 0** | 1 |
| Historical literal-UI | **6 / 23** | **4 / 0 / 0** | 0 |
| SiteMode isolated production profile | **10 / 39** | **0 / 0 / 0** | 1 |
| PPC interaction identity, окремий isolated run | **6 / 161** | **0 / 0 / 0** | 1 |

Final current-runtime — **один повний fresh targeted invocation**, не весь репозиторій і не deduplicated union. Повторний запуск тих самих M71/ROOT/WT cases не додається до кількості унікальних tests. Цей run включає шість M71 класів, п'ять current M70 класів, 12 current M69 класів, чотири generic/native suites та M68 current-runtime compatibility. Він перевіряє continuing practices, NULL selection, retained/rephrase support, snapshot clones, subtitle/NULL dispatch, owner/locale/category/body guards, no-op/rollback та consumer preservation.

Шість historical source failures розділені: M70SharedLineage — 1 assertion очікує старий finite hunk; M69AcceptedM68Lineage — 1 old raw-byte реконструкція; M69BuilderSelectionDerivation — 1 literal extraction assertion не містить уже прийнятого M70 optional practice-index коду; M68SharedLineage — 3 old raw-source assertions. Один інший derivation/API test у тому run пройшов. Старі тести не послаблено й не переписано.

Чотири historical UI failures стосуються старих literal source assumptions про inline input/foreach markup, а не actual-browser verdict. Немає redesign чи зміни engine заради цих assertions.

Єдина PHP deprecation: `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` deprecated у PHP 8.5; у final current run її спричинили 172 tests. Config/framework/dependencies у M71 не змінювалися.

На початкових runs новий M71 test помилково очікував український context у `rephrase.original`, а потім конкретне слово «взагалі» замість frozen «загальне вподобання». Виправлено лише новий test відповідно до незалежної авторської проєкції: context перевіряється в supported `rephrase_intro`, чистий EN original перевіряється exact assertion. Попередні failed receipts збережено; frozen навчальні bytes, runtime та старі assertions не змінювалися. Final pass не приписано ранньому failed run.

Node current: **140 pass / 0 fail / 0 skip**, один invocation шести files: M71/M70 actual factory, PPC manual-answer retry, punctuation, contractions і Practice Completion state. M71 частина перевірила frozen master `95f15d…`, 39 own tasks, 44 options, 46 accepted values та 7 повних/10 загальних token paths. M70 частина зберігає 34 own tasks, 38 options, 37 accepted values та 6 sentence paths.

Додатковий PPC `PpcTestInteractionIdentityTest` run: **6 tests / 161 assertions**, failures/errors/skips 0, одна deprecation. Це окремий invocation, не додаток до 179 current tests і не твердження про real-browser PPC matrix. Existing PPC retry/identity assertions не послаблювалися.

Окремий M68 Node run на explicit **served-local ROOT** profile: **24 pass / 0 fail / 0 skip**. Його не змішано з WT mixed-EOL raw profile та не додано до Node 140 як один invocation. Сам factory-код не виконує SQL/network/file writes; приватні TAP receipts створює test runner. Браузерне приймання ці тести не замінюють.

Непрозорі test receipt hashes:

- final current JUnit: `b26d9a437fac473ac4afa5d126684a5a76471bb0d820a27b33c2971a5fc7d190`;
- pre-apply M71 JUnit: `5e91ab92ef2c2a0e94be0dfd2ad4ec7102a26a1611be85db52aa80c246f33b20`;
- historical source JUnit: `f7e9c2880d8ba47645c0032851d7ffa5c0e1c58466272ea007dffae76e364696`;
- historical UI JUnit: `1c54bb021963b8987c117d2407a09df03d4ac6321d10941d831480856f83e9ef`;
- SiteMode JUnit: `321b6c4c380f1f313d6fc28133852fae5e486b9ab777c76d6a521d654da5679f`;
- PPC interaction identity JUnit: `4972c8020ead045959765ba69eed2ebf1799ff39cd8e160dd78cc87adcc32d14`;
- Node current TAP: `c454a5d2d54885be5aa339d9e374016bc49c60f9ead613a240b3b4a5a223f3f6`;
- M68 served-local TAP: `434e0089d6f998548739be35a8fc639c703bdd66d4fb72584216cea3f66c583f`;
- isolated runtime proof: `b3b4247824d973461d117f6f334a3eb99a22b523f1fc1ae8c88ccb4803abf835`;
- exact command/run-history summary: `b0efb50c90119a869807e3b157df8a049a201a0b16777f62ae7066ef5bddb023`.

### Exact current-runtime invocation

Виконано в accepted робочому WT, не в dirty served ROOT. Нижче PowerShell command, який розгортає ті самі 28 exact paths:

```powershell
$tests = @(
  'M71AuthoredReleaseTest','M71NativeMetadataTest','M71BuilderSelectionPresentationTest',
  'M71LocalTargetGuardTest','M71ContentPatchTest','M71SharedLineageTest',
  'M70AuthoredReleaseTest','M70NativeMetadataTest','M70BuilderSelectionPresentationTest',
  'M70LocalTargetGuardTest','M70ContentPatchTest',
  'M69AuthoredReleaseTest','M69BuilderSelectionPresentationTest','M69ContentPatchTest',
  'M69CurrentRuntimeCompatibilityTest','M69DiagnosticProfilesTest','M69LocalTargetGuardTest',
  'M69NativeMetadataTest','M69SemanticBoundariesTest','M69SentenceBuilderContractTest',
  'M69SubtitleCorrectionPatchTest','M69SubtitleFallbackTest','M69SubtitleTransitionTest',
  'TheoryNativeContentReleaseTest','TheoryNativeContentPatchTest','TheoryContentGroupsTest',
  'TheorySentenceBuilderAcceptanceTest','M68CurrentRuntimeCompatibilityTest'
) | ForEach-Object { 'tests/Feature/' + $_ + '.php' }
& 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze/php.exe' -d opcache.enable_cli=0 `
  vendor/bin/phpunit --configuration phpunit.xml --display-deprecations `
  --log-junit storage/app/m71-final-current-runtime-junit.xml @tests
```

Historical suites і SiteMode запускалися окремо, не виключалися мовчки:

```powershell
& 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze/php.exe' -d opcache.enable_cli=0 vendor/bin/phpunit --configuration phpunit.xml --log-junit storage/app/m71-historical-source-junit.xml tests/Feature/M70SharedLineageTest.php tests/Feature/M69AcceptedM68LineageTest.php tests/Feature/M69BuilderSelectionDerivationTest.php tests/Feature/M68SharedLineageTest.php
& 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze/php.exe' -d opcache.enable_cli=0 vendor/bin/phpunit --configuration phpunit.xml --log-junit storage/app/m71-historical-ui-junit.xml tests/Unit/TheoryPracticeSetTokenBankUiTest.php
& 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze/php.exe' -d opcache.enable_cli=0 vendor/bin/phpunit --configuration phpunit.xml --log-junit storage/app/m71-site-mode-junit.xml tests/Feature/SiteModeTest.php
& 'C:/Program Files/xampp/php-8.5.10-nts-gramlyze/php.exe' -d opcache.enable_cli=0 vendor/bin/phpunit --configuration phpunit.xml --do-not-cache-result --log-junit storage/app/m71-ppc-identity-junit.xml tests/Feature/PpcTestInteractionIdentityTest.php
```

Node commands; `node` означає той самий bundled Node executable, використаний для receipts:

```powershell
node --test --test-reporter=tap --test-reporter-destination=storage/app/m71-node-current.tap tests/Browser/m71-native-practice-state.test.cjs tests/Browser/m70-native-practice-state.test.cjs tests/Browser/manual-answer-retry.test.cjs tests/Browser/answer-punctuation.test.cjs tests/Browser/theory-practice-contractions.test.cjs tests/Browser/practice-completion-state.test.cjs
$env:M68_ENGINE_PROFILE = 'served-local'
$env:M68_REPO_ROOT = 'D:/DEV/htdocs/gramlyze.loc'
node --test --test-reporter=tap --test-reporter-destination=storage/app/m71-m68-served-local.tap tests/Browser/m68-native-practice-state.test.cjs
```

SiteMode production-host cases — лише synthetic Laravel test kernel; реальних HTTP-звернень до .com/.ub не було.

## Scoped Git delivery

Переглянуто exact allowlist **42 файлів**, shared diff (8 files / 12 hunks / 13 added lines), нові thin profiles/diagnostics/tests і frozen sources. Пройдено `git diff --cached --check` з CR-aware whitespace policy; 12 frozen/active raw Git-index blobs і raw CRLF controller звірені окремо. Незалежний read-only code review не виявив actionable findings. Secrets, `.env`, backups/dumps, screenshots, private runtime diagnostics, caches/generated assets, тимчасові routes й сторонні dirty changes до stage не включено. Visual source changes — **0**.

Порядок доставки: scoped commit → normal push у `codex/seo-m71-articles-quantifiers-standard-view` → незалежний `ls-remote` SHA == local HEAD. Full SHA та фактичний результат доставки повідомляються у фінальній відповіді, а не самовключаються в hash цього report.

Цей report не є дозволом на production deploy, main changes або четверту сторінку; native-shell/print/no-JS/WCAG обмеження не перетворюються на глобальний accessibility/design PASS.
