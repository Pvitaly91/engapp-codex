# M61 Reported Speech Changes

Три уроки version 1.0.0 застосовано до перевіреної локальної БД gramlyze.loc і прийнято на реальних сторінках. На кожній сторінці працюють 2 selects, 2 meaning choices, 2 manual/token inputs та окремий тематичний конструктор, пройдений до 5/5. Чинний канонічний дизайн не змінено. Цей звіт підготовлено перед scoped delivery commit; точний commit SHA і незалежне підтвердження remote == HEAD наводяться у фінальній відповіді після фактичного push.

## Сторінки та межі

| Сторінка | Page / category | Погоджена структура |
| --- | --- | --- |
| [Backshift of Tenses](http://gramlyze.loc/theory/reported-speech/backshift-of-tenses) | 141 / 3 | 6 continuing slots + 1 practice-set |
| [Time and Place Changes](http://gramlyze.loc/theory/reported-speech/time-and-place-changes) | 145 / 3 | 6 continuing slots + 1 practice-set |
| [Say / Tell / Ask](http://gramlyze.loc/theory/reported-speech/say-tell-ask) | 144 / 3 | 6 continuing slots + 1 practice-set |

Exact UK Page identities, у тому самому порядку:

- `Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechBackshiftTheorySeeder`.
- `Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechTimePlaceChangesTheorySeeder`.
- `Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechSayTellAskTheorySeeder`.

Branch: `codex/seo-m61-reported-speech-changes-standard-view`. Base: `d60a288565f841dceefc16ac6ee10fa5c8c55395`, accepted M60. Тільки три UK theory owners; category «Непряма мова», її description, M60, інші уроки, курси, окремі тести й EN/PL залишаються поза записом цього контентного пакета. Production, `.com` і `.ub` не застосовуються; наведений нижче `.com` canonical — збережена локальна SEO-метаінформація, а не факт звернення до production.

## Незалежний BEFORE і проєкція

Свіжий source/DB/HTTP BEFORE: SHA `ba414b3857d4a6d430f7037a62a9d50b8fac589be8bea30eaeeeed99df71059d`. Зафіксовано 46 таблиць, 326 visual files і 2377 попередніх frozen files. Фінальна незалежна звірка після UI та HTTP підтвердила їхнє збереження поза точним дозволеним diff; деталі й receipts нижче.

Три theory та три відповідні course GET у BEFORE повернули HTTP 200. Actual raw titles/H1, category 3, locale UK, навігація й шість continuing slots зчитані до редагування. Технічний hero level B1 / display B1-B2 збережено; чотири старі box slots мають null level та columns left,left,right,right. Перед перезаписом кожної WT definition її raw SHA збігся з незалежним BEFORE.

Єдина погоджена pure-проєкція чинного serializer: SHA `0af08d215f98ecdc5aaa3d144795ba5eeba60999900d71aff4bdc00941da4731`. Вона містить 21 native block, найбільший body — 25 507 bytes за місткості 65 535. Три опубліковані definitions семантично дорівнюють саме цій проєкції, з CRLF і одним EOF. Паралельного serializer або нового renderer не додано.

Public frozen BEFORE має SHA `da392078eead44cb058e98b1d45496771cc44352f57eb4c9ce4c771372b0b6af`. Його описове поле content_type зберегло успадкований напис `M60_source_before`; цей недолік назви явно зафіксовано, frozen bytes після freeze не переписували. Base SHA, усі три M61 identities, paths, старі definitions і raw hashes перевірені незалежно; guard не використовує цей описовий напис для розширення scope. Pure-проєкція окремо має M61_source_before, а live wrapper перевіряє обидва очікувані labels разом із точними owners/base.

## OLD і AFTER map

| Owner | Slot 1 | Slot 2 | Slots 3 і 4 |
| --- | --- | --- | --- |
| Backshift | HTML box → comparison-table | HTML box → usage-panels | mistakes-grid і summary-list |
| Time and Place | HTML box → comparison-table | HTML box → usage-panels | mistakes-grid і summary-list |
| Say / Tell / Ask | HTML box → forms-grid | HTML box → comparison-table | mistakes-grid і summary-list |

Finite map дозволяє рівно 12 type/heading переходів. Старі headings прибрано, щоб не дублювати native titles; column, level, continuing UUID, index/order і anchors зберігає serializer. Hero/header та navigation/footer лишаються на старих місцях. Новий slot — тільки final practice-set/left/B1 для кожного уроку. Власного CSS, Blade, frontend JavaScript, typography або grader у навчальних артефактах немає. Остаточний hash-gate: 326/326 visual files без змін.

Точні metadata transitions (slot — 0-based індекс у початкових шести blocks):

| Owner / slot | OLD type / heading | AFTER type / heading | Незмінні column / level |
| --- | --- | --- | --- |
| Backshift / 1 | box / 1. Найтиповіші зміни | comparison-table / null | left / null |
| Backshift / 2 | box / 2. Коли backshift є типовим, а коли ні | usage-panels / null | left / null |
| Time and Place / 1 | box / 1. Найуживаніші зміни | comparison-table / null | left / null |
| Time and Place / 2 | box / 2. Коли зміни справді потрібні | usage-panels / null | left / null |
| Say / Tell / Ask / 1 | box / 1. Основна різниця | forms-grid / null | left / null |
| Say / Tell / Ask / 2 | box / 2. Приклади вживання | comparison-table / null | left / null |
| Кожний із трьох / 3 | box / 3. Типові помилки | mistakes-grid / null | right / null |
| Кожний із трьох / 4 | box / 4. Коротке правило | summary-list / null | right / null |

Останні два рядки означають по три окремі переходи. Slot 0 лишився hero/header/B1, slot 5 — nav/footer/null. Жодного запису level або column. Appended practice source index 6 / sort_order 7: DB IDs 12725, 12726, 12727 відповідно; deterministic UUIDs `bef1ae7b-f5b7-58d1-9342-ee9aef153571`, `431dfd7f-7dd2-5232-bc03-8d971534eaea`, `57c63efb-6c9f-5c23-9bb5-434b9474f1d7`.

## Предметні зміни

Backshift зберігає правильне старе застереження проти автозаміни. Таблиця показує типові переходи й обидві перекладені версії; Past Perfect та Past Perfect Continuous далі не зсуваються. Will після said не позначено універсальною помилкою, а worked/opened не оголошує актуальний факт неправдивим. У процесі й пасиві не губляться being, not або учасники. Can як уміння/дозвіл, may як можливість/дозвіл та must як вимога/висновок розділені; must not не перетворюється на didn't have to.

Time and Place прив’язує слова до фактичної дати, місця та предмета. У фіксованому навчальному календарі понеділок 1 червня 2026 року → вівторок 2 червня може дати tomorrow → today; п’ятничний переказ не пересуває ту саму подію ще раз. Два дні після 1 червня — 3 червня навіть у переказі 5 червня. Here може залишатися here в тій самій кімнаті; this — з тим самим показуваним ключем в іншому місці. That that має дві різні функції, а граматична версія з одним that не оголошується помилковою поза явно заданою умовою вправи.

Say / Tell / Ask розрізняє намір і ролі. Say має змістовий додаток та to перед адресатом; tell у моделі повідомлення називає адресата без to. Told someone where коректно передає надання інформації, asked someone where — її пошук. Ask to та ask someone to мають різних виконавців; ask for вводить потрібну річ. Didn't tell/ask не означає told/asked not to. Прохання не доводить виконання; ask не завжди ввічливе, tell не завжди грубе.

## Basic і три meaningful details

Основні правила, короткі застереження, переклади й критерії вправ видимі без «Докладніше». Три поглиблення обрано за змістом, не за квотою чи кількістю слів:

- Backshift: `backshift-modal-meaning` → `backshift-use/sections/2`, різні значення may і must.
- Time and Place: `time-place-motion-perspective` → `time-place-context/sections/2`, come/go з явною просторовою перспективою.
- Say / Tell / Ask: `say-tell-ask-utterance-intent` → `say-tell-ask-patterns/items/2`, намір Could you tell me… та формальне ask that.

У реальному браузері кожний detail спочатку закритий без fragment. Enter відкриває тільки його owner; Space закриває й відкриває; Enter знову закриває. Deep link і reload відкривають потрібний detail, повний текст присутній без height clipping. Summary IDs: `theory-details-317e681daad93e805f78`, `theory-details-493eaba5cf591d5ec682`, `theory-details-7bf7c94038373c67da1d` відповідно. На кожній сторінці рівно одне погоджене поглиблення.

## Практика

18 власних завдань: на кожній сторінці 2 form-select, 2 meaning-choice, 2 inputs з ручним і token способом. Це 18, а не 24 чи 30 завдань. Шість canonical input answers і 13 aliases утворюють 19 accepted variants, не 19 aliases. Умови явно задають referents, часову/календарну перспективу, потрібний лексичний матеріал і цільову модель.

Допустимі варіанти включають конкретні that/no-that, had not/hadn't, the day before/the previous day, the next/the following day, if/whether та she'd = she had. Банки токенів перемішані, з точним написанням, пунктуацією й окремими повторними that, The/the та the. Граматичні позацільові відповіді пояснені як невиконання явної умови, а не як загальна заборона форми.

48 різних пояснень одразу створено plain text для чинного x-text: повне англійське речення, український переклад і коротке пояснення. HTML/entities в feedback немає. Підтримані HTML labels, English meaning prompts і приклади залишаються в їхніх native fields; x-html або глобальний stripper не додається. У шести meaning choices точний переклад відкривається після перевірки, український контекст і критерій видно одразу.

Окремий обов’язковий widget має п’ять тематичних B1/type 4 питань на сторінку: 15 reused questions, 0 new questions, 0 new links у погодженому плані. Це не заміна own inputs. Контракт `docs/content/theory-sentence-builders/m61-reported-speech-changes.v1.json`, SHA `cfc3290513297530d6d4848b844e7bf1614023e3fbec808cacac4570978f95cf`, прив’язує фактичні банки:

- `PolyglotReportedSpeechBackshiftOfTensesAllLevelsLessonSeeder`.
- `PolyglotReportedSpeechTimePlaceChangesAllLevelsLessonSeeder`.
- `PolyglotReportedSpeechSayTellAskAllLevelsLessonSeeder`.

На кожній сторінці widget фактично пройдено до 5/5. Читалася видима українська умова; локатор обмежений BANK selectToken buttons, не вже вибраними removeToken словами. Перевірені всі 122 строгі token instances, регістр, фінальна пунктуація та два окремі that. Фактичний client shuffle:

| Сторінка | Фактична послідовність прийнятих IDs | Результат |
| --- | --- | --- |
| Backshift | 40986, 40987, 40990, 40988, 40984 | 5/5 |
| Time and Place | 41281, 41275, 41277, 41273, 41274 | 5/5 |
| Say / Tell / Ask | 41352, 41344, 41349, 41346, 41348 | 5/5 |

Fresh SSR payload перевірено окремо від UI: exact curated IDs і ordered tokens. Особисті cached attempts не очищали; окремий особистий restored-attempt сценарій у UI не примушували й не зараховували до fresh-payload PASS. Reload під час подальших detail-перевірок скинув лише видимий ephemeral стан; screenshots фактичного завершення 5/5 збережені приватно. Original questions/options/answers/hints/variants/links не змінені.

## Очікувана metadata до apply

Наведені очікування отримані pure-проєкцією з чинним PageMetadata до запису, не виведені з AFTER GET. Actual BEFORE canonical/breadcrumb збережено окремо від локальних navigation URLs.

| H1 і raw title | Очікуваний SEO title | Очікуваний SEO description |
| --- | --- | --- |
| Backshift of Tenses | Backshift of Tenses — правила \| Gramlyze | Backshift of Tenses. Backshift — типовий перехід часової форми в непрямій мові після минулого reporting verb. |
| Time and Place Changes | Time and Place Changes — правила \| Gramlyze | Time and Place Changes допомагає зберегти ту саму подію, коли змінюється точка відліку оповідача. |
| Say / Tell / Ask | Say / Tell / Ask — правила \| Gramlyze | Say / Tell / Ask. Say, tell та ask не є взаємозамінними позначками «сказати». |

Canonical: `https://gramlyze.com/theory/reported-speech/` + відповідний незмінний slug. Breadcrumb: «Головна / Теорія / Непряма мова /» + відповідний English H1. Після apply усі три direct GET дали HTTP 200 з одним H1, точними expected title/description/canonical/breadcrumb і незмінною LearningResource identity; description LearningResource збігається з повним frozen hero intro. Перевірено 377 незалежно очікуваних learning fields (136/127/114), по 18 старих anchors на сторінку, missing 0. Local robots header лишився noindex, nofollow, noarchive.

Внутрішня навігація кожного уроку має точні локальні destinations до двох інших M61 та трьох M60: `/theory/reported-speech/reported-statements`, `/reported-questions`, `/reported-commands-and-requests` у тому самому reported-speech prefix. Це navigation URLs, не заміна canonical. SSR receipt SHA `9a102d922c920a342f541383c31da02491b58db81d66b67f41949b11ed4719e4`.

## Frozen author checksums

| Артефакт | SHA-256 |
| --- | --- |
| Master 1.0.0 | `b7bc95d9e8a2aedf5303d5c44b995ceaaa49eeec56ecf7804562c04f6d57afa4` |
| Читабельна UK редакція | `2c6d8167661373fb2f02efcc6af9492c7547340810744957fc64335076e46f3e` |
| Editorial audit | `6b3b3d8faf84e4ae767b85f2cbd583187711b2c912c9f690233ec014587c9074` |
| Native component map | `f0da481b658628efc29e57d263dc5e2eadff678668c01bdec8619c093ca4717f` |
| Backshift definition | `3ade591e0cd79c3b0a1b74f2e369dd55c2afc91c5e91fefb3ebfe41716d6287a` |
| Time and Place definition | `ed76e9a8d0e7c90f20ce77cc95f8877215215faf0045aaba78cd1b1937f46774` |
| Say / Tell / Ask definition | `32d0ec9d9ad8501a656ef367292d21e5250ea738742d3b87bf447441691d2abc` |
| Offline JS suite | `dc851e3bc7daa59726c864ea8541a4cb1ac174d2f14db3239eb76de623de5278` |

Native patch SHA `9abc29dd8b2ec736e62ec5b9ccbecaf011526b8ab89e91624cd4c2e1263ca79c`; bank audit SHA `17bac6cda7ee8be7d749b98c9ba049378b4e70c8b87e58fed04bb4204a44b21f`. Checksum manifest перевіряє 11 raw artifacts, а package окремо перевіряє BEFORE/master/patch/contract і семантичну проєкцію всіх definitions. Frozen integrity gate PASS; історичні M60 1.0.0, M60 1.0.1 та попередні releases/maps не редагували.

## Джерела та спосіб доступу

[British Council Reported Speech Statements](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-statements) і [Google helpful-content guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) прочитані напряму як повні editorial main bodies. Learner comments не є нормативним джерелом; signed staff replies відрізнені від основного тексту.

П’ять точних Cambridge direct requests повернули 403. Замість вигаданої direct-success перевірки прочитано повні офіційні publisher-indexed bodies: [Reported Speech Indirect Speech](https://dictionary.cambridge.org/us/grammar/british-grammar/reported-speech-indirect-speech), [Say or Tell](https://dictionary.cambridge.org/uk/grammar/british-grammar/say-and-tell), [Reported Speech Reporting Verbs](https://dictionary.cambridge.org/grammar/british-grammar/reported-speech-reporting-verbs), [Ask and Ask For](https://dictionary.cambridge.org/grammar/british-grammar/ask-and-ask-for), [Word Patterns Ask For](https://dictionary.cambridge.org/grammar/british-grammar/word-patterns-ask-for). Requested reporting-verbs indexed body мав заголовок Reported speech; це не підтверджений redirect. Додатково прочитано publisher-indexed main body [Must](https://dictionary.cambridge.org/grammar/british-grammar/must).

Приклади та завдання авторські, publisher exercises не скопійовані. Немає word-count quota, вигаданого авторства або гарантій ранжування. Докладний журнал доступу й предметне OLD/AFTER рішення збережені в versioned editorial audit.

## Guarded local apply та збереження

Доведена ціль: environment production, SiteMode development, application root `d:/dev/htdocs/gramlyze.loc`, document root `d:/dev/htdocs/gramlyze.loc/public`, mysql / localhost / 3306 / gr2. Назва environment сама по собі не була доказом production-цілі: physical vhost/listener/runtime, web/CLI identity і фіксований loopback звірені разом. HTTP лише gramlyze.loc; .com/.ub не відкривали.

Свіжий read-only nonce proof створено 2026-10-16T17:22:27Z, SHA `c506cea1c02360dc8a5513038b443c6ce49e2074383677bb9667d67c71be5bfc`. Тимчасовий GET був обмежений HTTP gramlyze.loc, loopback, випадковим nonce, без cookies/auth/forwarding/секретів, із rollback без DB writes. Guard повторно перевіряв freshness перед preview та apply.

Fresh preview digest `be40dbe9d1a6c213cbd1392d95371bb474caa134033dc2970ac6a0d52416d8e4`, raw SHA `c5a0ae5582da8c650257fd37317fddc65d869817aff4539b2b1a031d0f5bd770`. MAIN переглянув exact 24 updates і 3 inserts проти незалежного BEFORE та frozen projection. Exclusive private backup збережено до транзакції (backup receipt raw SHA той самий c5a0…; це reviewed before-values plan, не full SQL dump).

Фактичний transactional apply: **24 updated / 3 inserted / 0 deleted**. Updates — 3 Page.text і 21 UK text-block row; 12 із цих блоків мають рівно погоджені type/heading transitions. Inserts — лише три appended practice blocks. Questions, answers, options, hints, variants, links та інші question families: 0 inserted/updated/deleted. Жодного reseed, migration, truncate, session clear або startup/HTTP apply hook.

Виконано два окремі fresh after previews і два фактичні repeated apply: обидва status no-op, **0 updated / 0 inserted / 0 deleted**, digest `219ac79e01a029c82bd2b852e8d76d8c9a3e1d9be5dbabaa85d79b81a53e81a9`. No-op не зараховано як нову content transaction.

Proof route видалено. Спочатку cleanup guard двічі відхилив тільки mixed-EOF/blank-line drift; ці спроби не оголошувалися успішними. Після перевірки поточного SHA і текстової тотожності поза EOF файл механічно відновлено exact preimage зі збереженого snapshot, без втрати сторонніх змін. Final route SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; окремий GET nonce URL дав **404**, redirects 0. Cleanup receipt SHA `73f88685d3ac5b8b957eec1a205b02eb15aa271abfdae2df849d9435572a0a2a`. Routes file не входить у commit.

Final read-only postconditions після UI: SHA `957abd5be25aba943ca0ab281daa474e37858ee69a2e8d590520368d6d162a1f`. Усі 46 таблиць тотожні фактичному стану одразу після apply до UI; незалежна BEFORE reconstruction підтверджує всі старі rows/timestamps поза allowlist, 21 continuing UK row, 42 EN/PL rows і лише 3 нові native blocks. Збережено category learning descriptions, progress, null levels, columns, aliases і нецільові question payloads. 326 visual files та 2377 попередніх frozen source files byte-exact; source sync exact, 0 writes.

Read-only старі HTTP consumers: **49/49 direct 200**, exact BEFORE facts: 3 course-help, 3 course-index, 9 JSON, 12 SSR, 3 categories, 19 neighbor pages; 6 saved tests і 432 old questions. Після цих GET усі 46 SQL fingerprints лишилися exact final-v2; source/route declarations/old SQL exact, 0 writes. AFTER receipt SHA `09287a784d951a2c1b23d07f2d41cff15be65d151545d67d068fca00b9d4eb59`. Під час початкового BEFORE capture був permission-bound фізичний guard failure і один transport timeout старого сусіда; після дозволеного read-only доступу та окремого warm GET незмінний capture пройшов усі 49. Це recovered setup/transport failure, не новий дефект M61 і не прихований first-run PASS.

## Невізуальна інтеграція

У восьми чинних shared files додано лише **12 точних hunks / 15 lines**: canonical source/presenter dispatch, HTML/legacy fallback, theory-course BEFORE preservation, standalone-test BEFORE reconstruction, exact appended-block exclusion і native linked-practice binding. Чужі callers/locale/owner/category/stale bodies не активують M61. Нові finite M61 package/patch/guard/CLI використовують чинні TheoryNativeContentRelease, TheoryNativeContentPatch і M26 writer, а не новий engine чи renderer.

Bounded bank presenter клонує лише видиму умову п’яти перевірених моделей у canonical theory caller; оригінальні relations, questions, shared options, ordered answers, hints і links не мутує. WeakMap/identity/body/locale/category/practice guards та whole-collection fallback не дозволяють часткове застосування до stale/чужої колекції. M60 case restoration не копіювали: обрані M61 keys працюють із чинним ComposeTokenCase. 36 actual B1/type4 candidates перевірені до відбору 15; 0 нових questions/links/case corrections.

Вісім нових діагностичних thin wrappers використовують попередні перевірені helpers, фіксуючи M61 scope/hashes/expectations; нового загального framework немає. .gitattributes фіксує збереження raw UTF-8 bytes артефактів M61 та трьох definitions; нові PHP-файли перевірені як LF/no-BOM. У ROOT стороннє видалення .gitattributes і відмінності TestJsV2Controller збережені; exact reverse-hunk checks відновлюють обидва окремі попередні controller SHA. У Git — лише WT M61 diff, не сторонній capture script, runtime, приватні proofs/screenshots/backups або generated assets.

## Ізольовані перевірки

Підтверджено frozen offline JS 22/22: 36 options, 6 manual + 6 token inputs, усі 19 accepted variants і curly apostrophes, empty/partial/wrong/repeated Check/edit/recheck/reset, exact token multiplicity та незалежні бали. Використано actual unchanged shipped factory; network calls 0. Receipt SHA `cd3c2e4c890d51b34b6105dbe4d79a7189502b393ef5107e9fd865d1fb8b6d20`. Перший приватний запуск виявив лише double-escaped test namespace і lowercase test locator для The/the; виправлення обмежене тестом, не app/data/engine.

PHP виконано трьома успішними ізольованими runs, тільки SQLite :memory:, array cache/session, без working .env/MySQL. У кожному runner збережено 46 661 protected files:

| Bundle | Tests | Assertions |
| --- | --- | --- |
| Нові M61 infrastructure/package/guard/transition/feedback/compatibility/contract | 47/47 | 4 981 |
| Новий M61 bank selection/presenter | 8/8 | 6 768 |
| Попередній accepted regression bundle | 332/332 | 62 041 |
| Deduplicated union цих трьох runs | 387/387 | 73 790 |

Це deduplicated union, **не один повний успішний rerun усього репозиторію**. Повторні запуски не збільшують унікальний total. У кожному run є одна чинна deprecation. Дві оригінальні M60 controller-chain checks виконані без зміни assertions у private historical fixture через M61 compatibility wrapper; окремо повторно до total не додані. Чотири відомі historical literal-UI failures не виправляли редизайном, їх не зараховано до цього PASS і не оголошено повторно пройденими.

Union JUnit identity receipt SHA `3a68c65fd2f599ea50b2ed35e213c635b44978fb4c49b849546816977880267e`. Нові native tests receipt SHA `08884177384ada4ea5f7239d2f48ab55fa0612407041954fd83ae85ce376dfbb`; bank tests SHA `878b22dbcca411ba0587bc4212bb0f21d8b58045752a4c199bd78bf9ae599809`. Покрито чужі owner/locale/category/Page-vs-Category, stale body/plan, metadata/null/column відхилення, missing bank/short pool/type/CEFR/duplicate IDs/widget, original-model mutation, HTML у plain feedback і finite calendar fixture. Це не універсальний лінгвістичний аналізатор.

Scoped sentence-builder audit: 3 targets, 0 failed/0 writes, SHA `3564e9ae0a4c5d6a95038942ca33d9a00999c6bf68adc69eb068bfe13b088e9a`. Full dynamic registry: **106/106**, попередні **103/103** exact за всіма стабільними полями (volatile HTTP body SHA порівнюється окремо, не маскує learning payload). Full receipt SHA `78c9e6e34f3cb1396040fc569cf7e22416190f38d384580dc2fd7d9af2b4d1e8`. Жодного permanent hardcoded registry cap. CLI audit не зарахований як browser acceptance.

## Реальне браузерне приймання

Використано computer-use skill і фактичну локальну вкладку, без Playwright поза дозволеним UI runtime, без hidden state/Alpine mutation. Масштаб не змінювали, global localStorage/personal progress не очищали. Matrix: **3 × 1440×1000/390×844/320×844 × light/dark = 18 states**, actual DOM viewport, усі сторінки до footer, 18 приватних full-page PNG. У кожному state три own groups і один linked widget.

| Viewport width | Main client/scroll width | Table scroll/region client width |
| --- | --- | --- |
| 1440 | 877 / 877 | 825 / 825 |
| 390 | 358 / 358 | 576 / 322 |
| 320 | 288 / 288 | 576 / 252 |

Learning-container horizontal overflow: **0/18**; таблиці мають власний overflow:auto. На mobile Time and Place додатково фактично перевірено keyboard horizontal scroll: tabindex 0 region, 8 ArrowRight, scrollLeft 253, остання колонка видима. На інших двох таблицях перевірено geometry/overflow, не окрему keyboard interaction.

Whole-document/shell scroll widths наведено окремо (light/dark):

| Сторінка | BEFORE 390 / 320 | AFTER 390 / 320 |
| --- | --- | --- |
| Backshift | 403/393 ; 324/330 | 402/401 ; 335/330 |
| Time and Place | 398/390 ; 332/327 | 400/403 ; 334/328 |
| Say / Tell / Ask | 402/392 ; 322/331 | 390/403 ; 326/324 |

На 1440 BEFORE та AFTER scroll width 1425. Значення shell змінюються разом із декоративною геометрією; **весь AFTER overflow не названо успадкованим**, zero-overflow claim стосується learning container. Footer фактично досягнуто в усіх 18 states. Повний learning text/table/detail не обрізаний.

Усі **18 own tasks** фактично прийняті, кожна група дає 2/2. Окремо **24 input task/mode/size cases**: усі 6 inputs, manual і BANK tokens, desktop та mobile. Окремо всі **19 accepted variants** (6 canonical + 13 aliases) і ще 2 curly-apostrophe cases прийняті; це не 19 aliases і не додаткові завдання. Для всіх 6 inputs пройдено empty/partial/finite wrong/repeated wrong/edit correct/recheck/reset/manual-token mixed delete-return-rebuild. Бал після repeated Check не подвоюється. Незалежність груп фактично прочитано на Say/Tell/Ask та ізольовано перевірено для всіх груп.

Фактичний feedback прочитано після Check: authored correct/accepted та finite declared wrong mappings містять повне англійське речення, український переклад і пояснення без literal HTML/entities/double escaping. Переклад усіх шести meaning choices відкривається після перевірки. Чинне обмеження engine не приховане: довільний невідомий partial/typo може потрапити в generic «Неправильно. Правильна відповідь: …» без авторського перекладу; empty має чинне «Обери або введи відповідь». Глобальний grader і frontend не змінювали заради розширення цього fallback.

Фактичні computed fonts: H1 Archivo, main Manrope; document.fonts.status loaded на всіх трьох. Локальний CSS HEAD 200, text/css, 123 022 bytes; actual styled screenshots прочитані. Browser errors/warnings log порожній. DOM bridge повернув false для link.sheet попри видиму застосовану стилістику; це не оголошено ні asset failure, ні blanket network PASS. Зовнішній font/asset network audit не виконувався.

Приватний compact browser receipt SHA `a72b424ec9f14d5f6a2bdd6258f9305c64d386544725955ca4d3262f275a49ce`. Final screenshot `final-backshift-native.png` знято на 1440×1000/light; після цього emulation reset, вкладку з чистим локальним URL позначено deliverable. Приватні screenshots/proofs не входять у commit.

Перед commit повторено pure author projection без DB: SHA знову `0af08d215f98ecdc5aaa3d144795ba5eeba60999900d71aff4bdc00941da4731`, тотожний переглянутому до apply. Усі 11 manifest artifacts мають exact raw SHA та raw blob == staged blob. PHP `-n -l` пройшов для всіх 30 пов’язаних PHP paths без конфігурації/.env. Staged scope — 45 явно вибраних файлів; git diff --cached --check exit 0, shared hunks переглянуті окремо.

Перший scoped stage відхилив давній порожній worktree index.lock від 16:05:18 UTC. О 18:00:43 UTC не було Git/SSH процесів, exclusive read lock успішний, index SHA незмінний; після повторних exact checks файл збережено приватно поза Git і прибрано з active lock path. Чужий index не переписували, незавершені дані не втрачено; scoped stage після цього успішний.

No-JS, print і повний WCAG audit не перевірено: це не PASS і не твердження про відсутню підтримку. Особистий restored-attempt UI сценарій та повний external-asset network audit не перевірено. Main, production, PR, merge, force push, workflow dispatch і deploy не виконувалися. Delivery commit міститиме лише ці 45 шляхів; remote == HEAD підтверджується незалежним ls-remote після normal push, не цим передcommitним текстом.
