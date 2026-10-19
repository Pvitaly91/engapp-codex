# M58 — Past Perfect Passive / Future Simple Passive / Future Perfect Passive

## Результат і підтверджені сторінки

Реалізовано й застосовано **локально**, під чинну стандартну в’юху. Дизайн, Blade, CSS, шрифти, frontend JS, grader і practice engine не змінювалися.

| Урок | Page / category | Рівень | Continuing blocks | Власна практика | Окремий widget |
| --- | --- | --- | --- | --- | --- |
| [Past Perfect Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-perfect) | 109 / 18 | B1 | 12 | 2 form + 2 meaning + 2 input | 5/5 |
| [Future Simple Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple) | 107 / 18 | B1 | 11 | 2 form + 2 meaning + 2 input | 5/5 |
| [Future Perfect Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-perfect) | 106 / 18 | B2 | 12 | 2 form + 2 meaning + 2 input | 5/5 |

Exact identities: `Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePastPerfectTheorySeeder`, `PassiveVoiceFutureSimpleTheorySeeder`, `PassiveVoiceFuturePerfectTheorySeeder` у тому самому namespace. Ancestry всіх трьох: `passive-voice → passive-voice-tenses`; locale `uk`. Штатний resolver, БД і direct local GET підтвердили вкладені URL. Flat aliases не створювалися.

Actual base: `7c4bac183d93227fbb5544b197538b75c3d579dc` — accepted M57, із M56 1.0.1 в ancestry. Робоча гілка: `codex/seo-m58-past-future-passives-standard-view`. Main перед доставкою: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`; main не змінювався. Final commit SHA та незалежне remote==HEAD підтвердження надаються у фінальному повідомленні/приватній delivery receipt, без самопосилального SHA всередині цього commit.

Served ROOT заново підтверджено як `D:/DEV/htdocs/gramlyze.loc`, document root `D:/DEV/htdocs/gramlyze.loc/public`. Versioned source worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`. Наявність коду в worktree не використовувалася як заміна web/CLI proof.

## Редактура, контент і frozen release

Повні OLD definitions прочитані, незалежний source/DB/HTTP BEFORE збережений до синхронізації джерел. Збережено 35 continuing identities, UUID, source indices, type, column, level і старі anchors; додано лише 3 practice-set наприкінці (sort order 13/12/13). Стара навігація лишилася navigation-chips, форми — forms-grid, таблиці — comparison-table; немає анонімного потоку правил чи картки для кожного речення.

- Past Perfect: минула точка огляду, had been + V3, питання/заперечення/скорочення; before не змушує Perfect. Досвід, повтори й тривалий стан не зведено до одноразового завершення. Виправлено старе хибне wrong→right між граматичними Simple/Perfect, задано зрозумілу точку відліку; не домислено пізніший результат.
- Future Simple: will be + V3 відділено від ready/available та прикметникового стану. Прогноз/рішення/запевнення не є гарантією. By-deadline допускає Simple; until/not until мають власний зміст. When-питання відділено від звичайної часової частини; застаріле in 2025 замінено без runtime-переписувача років.
- Future Perfect: will have been + V3 і майбутня точка огляду; will be finished by Friday не оголошується помилкою. Not all та None не змішані; not completed не перетворюється на not started. Короткі відповіді з will, subject-wh, часові межі, повтори та тривалість пояснено в контексті. Медичний прогноз замінено нейтральною навчальною ситуацією.
- У перетвореннях збережено учасників, час, аспект, заперечення й обставини; пропуск невизначеного someone пояснено. Sent не підмінено received/delivered.
- EN приклади й UA переклади рознесено підтриманими полями, пояснення українські. У 6 meaning choices точний переклад стимулу відкривається після Check; контекст і критерій видимі відразу.

Meaningful details — 2/1/2, семантичне рішення без квоти чи word threshold:

| Detail key | Точний owner |
| --- | --- |
| past-perfect-before-perspective | past-perfect-uses /sections/0 |
| past-perfect-before-noncompletion | past-perfect-affirmative /sections/0 |
| future-simple-when-clause | future-simple-questions /sections/1 |
| future-perfect-deadline-perspective | future-perfect-uses /sections/0 |
| future-perfect-duration-continuity | future-perfect-uses /sections/2 |

[Master 1.0.0](../content/m58-past-future-passives.v1.0.0.json), [читабельна UK редакція](../content/m58-past-future-passives.v1.0.0.uk.md), [editorial BEFORE/AFTER і primary-source access journal](../content/m58-past-future-passives.editorial.v1.0.0.md), [component/basic-detail map](../content/m58-past-future-passives.components.v1.json), [checksums 12 artifacts](../content/m58-past-future-passives-checksums.v1.json).

| Artifact | SHA-256 |
| --- | --- |
| Master | `3e82d91b64eb123fdbdbb343ebedbb8fb30b7bbf79183efeac46d81ca32a52bd` |
| Public source BEFORE | `ab62337aea493548229910e4b908cbdeb15c444ae8638b69242b61a4f0147097` |
| Content patch | `863b2a6eeb826892d399d8e5de28a99d94f5e9ff76a6516a1b5604fc0e69df70` |
| Independent builder contract | `c6c45bffdb8313360c2b0d3e1944d4574fa2cc4a1a9a401e7b6f16e86edc5cc1` |
| Widget question source | `8c71919ee6adcab859d0756468a9d6797243f362124280d3725bc3e8e9fce62a` |
| Bank quality audit | `89e1dfebdb2c590da181176c8e21eff42ac7da366e2c35b0860f1c8ff8503568` |

Незалежний main та bank review схвалили повний DRAFT v3 до freeze. Frozen teaching точно дорівнює схваленому v3; різниця лише у status/review metadata. Після freeze навчальні дані не змінювалися. Нові author artifacts UTF-8/LF/one EOF; 3 continuing definitions зберегли CRLF/one EOF. Усі 13 artifact+manifest bytes збережені Git clean filter, без BOM. Найбільший body 11413 bytes при actual SQL TEXT limit 65535, без DDL.

Первинні редакційні джерела: Cambridge Passive forms / Past perfect / Future will / Future perfect / By / Until / Before; British Council Past perfect та Will have and would have; Google Search Central helpful content. Повний журнал містить фактичні URLs і розділяє direct main-body, publisher-indexed editorial body, авторський 403 та tool InternalError без HTTP status. Signed Peter M Team response відокремлена від main body й learner comments. Видавничі exercise banks не копіювалися; ranking gain або human certification не стверджуються.

## Власна практика та банк конструктора

18 власних tasks і 15 selected bank questions — різні totals. Кожний урок має 3 незалежні групи по 2 завдання: form-select, meaning-choice, sentence input з ручним введенням і банком токенів. Шість canonical input keys + п’ять явних contraction aliases = 11 accepted variants. Усі 36 варіантів мають змістовний bilingual feedback, потрібні умови реально відображені. Немає готової відповіді в hint/placeholder.

[Фактичний bank audit](../content/m58-past-future-passives-bank-audit.v1.json) не покладався на назву seeder. Page-wide OLD question counts — **144/157/144**; exact old banks — **72 кожний**. На потрібних B1/B1/B2 рівнях було 12/12/12 type-4 candidates, але якісний природний пул **0/0/0**: artificial tails “in this passive context” / “in this context” / “in the grammar exercise”, метаправила й нецільові відповіді. Два чужі Future Simple candidates виключено. Це окремий фактичний аудит, не механічне копіювання рішення M57.

Мінімальне доповнення — 3 exact тематичні банки M58 по 5 питань; type 4, category 45, existing source IDs 348/345/349, B1/B1/B2. Старі питання, options, answers, hints та links не переписано й не пересіяно. Нових options **0**, reused physical option rows **57**. Усі 132 answer instances та регістр/повторення кожного токена перевірено строго через чинний ComposeTokenCase, без нової case map чи grader change. До freeze питання FP04 переформульовано на “What will have been sent by noon?”: це усунуло внутрішній raw What, а не змінило shared option.

Exact practice UUIDs:

- Past: `deeaa567-4e1c-5648-96a2-94c8be3c26c4`.
- Simple: `4990c3a9-12f3-5228-b52f-145c2b747389`.
- Perfect: `7ea4ed25-1826-5b16-88b2-7edcc4a595b9`.

## Мінімальна невізуальна інтеграція

Використано чинні TheoryNativeContentRelease / TheoryNativeContentPatch / TheoryLocalReleaseGuard, успадковані M26 backup/transaction і finite question primitives. M58 package — descriptor/bindings/pins, не паралельний renderer. Explicit CLI command не викликається HTTP/startup/hooks/scheduler.

Лише п’ять shared-файлів мають власні точні hunks:

- TheoryContentSource — exact-bound M58 source registration.
- TheoryHtmlAdapter — підтримані point panels/design guards.
- TheoryLegacyAdapter — підтримані correction fields.
- TheoryCourseManifestService — clone-only OLD content для виключеного course consumer.
- TestJsV2Controller — clone-only OLD theory-help для standalone consumers; новий practice block не підмішується.

Caller-owned theory boundary збережена; новий контент не поширюється на курси/окремі тести. ROOT foreign-inclusive controller має власний exact SHA, не wholesale-копію з worktree. Для всіх 5 shared hunks доведено byte-exact reversal до незалежних BEFORE hashes, включно зі змішаним чужим EOL. Сторонню ROOT .gitattributes deletion не відновлено.

## Guarded local apply та preservation

Fresh physical/web/CLI proof підтвердив єдиний loopback gramlyze.loc, активний document root і local MySQL gr2/3306. Environment “production” у локальній конфігурації не підміняв SiteMode development або фізичну локальну перевірку; production сайт не викликався. Паролі/APP_KEY/cookies/tokens не поверталися.

Послідовність: independent BEFORE → reviewed author projection → finite source sync → fresh nonce proof → fresh preview → independent exact diff review → exclusive private backup → одна транзакція → postconditions → original-plan no-op → fresh AFTER-plan no-op → route cleanup/GET404 → live acceptance → final read-only preservation.

Фактичний diff:

| Сімейство | Updated | Inserted | Deleted |
| --- | ---: | ---: | ---: |
| pages.text | 3 | 0 | 0 |
| Existing UK text_blocks.body | 38 (3 subtitle + 35 continuing) | 0 | 0 |
| New practice-set text_blocks | 0 | 3 | 0 |
| questions | 0 | 15 | 0 |
| question_options | 0 | 0 | 0 |
| question_answers | 0 | 132 | 0 |
| question_option_question | 0 | 175 | 0 |
| question_theory_text_blocks | 0 | 15 | 0 |

Preview digest `cd8af95c59c06d28474d05e46d69fb2aba06afd5c0a505f9f0f6a5faa535d71a` перевірено незалежно від AFTER: 41 exact update, 3 practice inserts, 46 protected tables; question plan reconstructed independently від frozen source+fresh options/schema/ID inventory. Backup `backup-v1.json` створений exclusive до запису; SHA `7f003a8968d8f75f1f25d816a45a290cf22447f2324c9c2be4990a22204a59f9`. Він приватний і не комітиться.

Обидва повторні apply: **0 updated / 0 inserted / 0 deleted**, усі 5 question families — 0, unused backups не створені. Тимчасовий GET-only nonce route працював лише для безcookie loopback HTTP gramlyze.loc. Route видалений; exact foreign-inclusive routes SHA до/після `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, фізична конфігурація незмінна; незалежний anonymous GET **404**, redirects 0. Route не входить у commit.

Фінальний read-only audit після браузерних відповідей: **46 таблиць**, old rows і timestamps exact після віднімання лише перевірених нових rows і повернення лише дозволених content fields. Незмінні **70 EN/PL rows**, усі старі question payloads/options/hints/relations, taxonomy та progress. Без reseed, migrations, truncate, mass update, session clear, full restore або серверних змін.

**326 visual files і 2330 historical frozen sources/maps — 0 змін.** M57 learning/questions і M56 1.0.1/href correction збережено. Курси, category learning descriptions та standalone-help з незалежним BEFORE: 36/36 HTTP consumers, 6 saved tests / 432 old questions exact. Actual current shared source/view/controller/course pipeline додатково перевірений ізольовано на всіх 9 M56/M57/M58 owners; це не лише historical controller fixture.

## HTTP, metadata та browser acceptance

Direct local HTTP 200 — 3/3. Перевірено **575 SSR learning fields**: 193/182/200; усі 5 detail fragments присутні в server HTML, правильні owners, спочатку closed. Старі anchors збережено: 24/23/24. Повний basic/detail доступний crawler у HTML, не прихована SEO-копія.

Raw title, actual English H1, breadcrumb, canonical, LearningResource identity/name/types і local noindex збережено. Нові SEO/LearningResource descriptions зіставлено з незалежними pre-apply очікуваннями чинного сервісу, не з власним AFTER. Дев’ять footer destinations підтверджено resolver+actual GET, 24 non-current href occurrences точні. Present/Past Simple лишилися flat, інші сім — nested. Category learning text не змінювався; очікувана зміна target preview у каталозі відокремлена від category content.

Shared read-only sentence-builder gate: **3/3 M58**, потім **97/97 dynamic owners**, failed 0, writes 0; попередні 94 збережено. 97 не зашито як постійну межу.

Computer-use skill і URL guard використано на реальних локальних сторінках. Browser zoom не змінювався. Матриця **18 унікальних target states**: 3 уроки × 1440×1000 / 390×844 / 320×844 × light/dark. Розміри підтверджені actual DOM; назва/піксельний розмір screenshot не використовувалися як доказ viewport.

- Усі **18 own tasks** прийняли reviewed answers; **9 group flows**: empty → partial → wrong → repeated wrong → edit correct → recheck без double score → reset.
- **24 input task/mode/size cases**: 6 inputs × manual/tokens × desktop/mobile; усі PASS. Додатково 11 accepted variants, 6 mixed edit/delete/return/rebuild cases; 3 незалежні score/reset checks.
- Усі **5 meaningful details**: незалежні Enter/Space, правильний owner, повний unclipped content, exact inner-fragment/reload. Fresh no-fragment reload починає closed. Штатне відкриття fragment не закриває сусідній detail — тест не підміняє цей контракт.
- Усі **3 widgets пройдено до 5/5**, по 5 різних reviewed questions, видимі умови/банк/зібрана відповідь, strict case і multiplicity, Next і фінальна native “Почати спочатку”. Client shuffle враховано зіставленням **видимої умови**, а token locator обмежений тільки банком, не вже вибраними словами.
- Усі 18 states: 0 learning-container horizontal overflow, 0 broken images, font status loaded; body Manrope / heading Archivo спостережено. 5 фактично підключених local CSS/JS assets дали GET200; console error/warn для власної вкладки — 0. Зовнішній Google Fonts CSS окремо HTTP не переперевірявся.
- Повні desktop captures усіх 3 уроків додатково прочитані до footer. Lazy topicTests card окремо активовано реальним скролом: чинні test links видно, це не порожня learning card. Фінальний viewport повернуто до default 1280×720, light; оновлений урок залишено відкритим.

Межі: залишковий outer-shell overflow **0–19px відносно innerWidth** і візуально низький dark-контраст CEFR badges/secondary text не виправлялися CSS і не проголошуються виправленими; точна mobile BEFORE-атрибуція новизни цих значень не встановлювалася. Learning overflow — окремо 0. WCAG ratio не сертифіковано. JS-off і print **not_checked** (відповідних switches не використано), не “unsupported”; serialized answers не названі доступним no-JS ключем. Personal restored standalone attempts не інспектувалися й не очищалися; fresh widget payload/сесії прийняті окремо. Глобальний localStorage не очищався.

Harness-only уточнення (без app edits): не припускати source order після client shuffle; native reset label “Почати спочатку”; між keyboard/deep-link сценаріями потрібен fresh closed baseline. Повільний goto звірявся actual URL/title/DOM без blind retry. Один transient narrow screenshot замінений окремим стабільним capture; частина .png-named bytes фактично JPEG. Повторні captures/reference visits не збільшують 18 states.

## Ізольовані тести та приватна доказова база

PHP: **250 унікальних tests / 46234 assertions / 0 failures / 0 errors / 0 skips**, 52 explicit suite files. SQLite :memory:, testing, isolated environment/storage/views, array cache/session, working .env не завантажувалася, working MySQL/progress writes 0; **46661 protected files, змін 0**. Є 1 deprecation, не прихована.

Перший full run мав 249/250 PASS: тільки новий test помилково вимагав hero/navigation усередині content-source registry. Test виправлено на чинну boundary + actual theory.show перевірки на 9 owners; усі 3 cases збережено й повторно PASS. Унікальний union замінив ті самі 3 identities: **250, не 253**; повторні runs не додають tests. App/content/design для виправлення test не змінювалися.

Frozen shipped-factory JS: **22/22 PASS**; 36 options, 6 manual, 6 token inputs, 11 accepted variants, 6 deferred meanings, retry/reset/mixed/independent scores, network 0. Це окремо від browser/SQL acceptance.

Історичний `TheoryPracticeSetTokenBankUiTest`: **6 tests / 23 assertions, 2 PASS / 4 FAIL** у підтвердженому baseline. Literal input/template assertions не відповідають уже чинним canonical component bindings. Їх не виправлено редизайном, не видалено, snapshots не оновлено й не включено до passing union. Це окремо від виправленої нової test-boundary помилки.

Приватні receipts/backups/screenshots у `storage/app/seo-m58-local`, не в Git. Основні SHA:

| Receipt | SHA-256 |
| --- | --- |
| Independent BEFORE | `89dbf1213d3ebabcb1f4b30680c8600b58a4f7eb957038750addc5ed8cfe8ef0` |
| Pre-apply metadata | `6e993792058aa99d80bcae78561e8911e7a3728e9e7d9532305fc81238f92a70` |
| Independent author projection | `05ff60152f17306b7c1e98ce97d46a6deb8d9374ef5c314fac5b657e2c43e7b7` |
| Question inventory v3 | `b605656b578dcc83471a359674dfd23b52d7628be397c86d2f773a986f0fb8ce` |
| Question plan | `db2fe1cfef7c085c4b0c9817e1ec692ff6beff8bdc52800fa69094c19d00f22d` |
| Final SQL/source preservation v2 | `b8a328ed33e5879e196ca00db76ea7d652351ea65966aa8b08c67eb861b4adaa` |
| Old-consumer AFTER | `d639a1b736adb0e9b8d4a578a6ba94f8dcdb2865577954055b4d137016fea91b` |
| HTTP/SSR v2, warning-free | `b93f0bcd4c1fc68f4bb25cb07c068d2186fd1834f6653e5d9da253bc66c3ee12` |
| LearningResource descriptions + assets | `c6779841ff0a02b7f71daedf747423bcbaeb7a7c8ca10346f0fda9584d94fb1a` |
| Dynamic registry AFTER97 | `7d65a21b36e9b6cc6fa87df3292df133db7b7ddd86d295cd1817570c8b4e3347` |
| Browser acceptance | `09927e32f632972c990702f0bf844d39c6aef6966f489cf22413cd29302d17ec` |
| PHP unique union | `081f8cae5cbb460be618400d1804d43adfb7e2e4498c5577b3300ba27de7d902` |
| Proof cleanup /404 | `cae479e7b5984f32d8733de12a0af512783a78bfa8ba1b52435b2d3da617d8e5` |

Початкові sandbox socket/CIM відмови не трактувалися як defect сайту; дозволені read-only/local команди виконано з потрібним доступом без зміни ACL/config. Перший SSR diagnostic виявив необов’язковий missing label warning; виправлено лише thin M58 read-only profile на null-safe читання й повторено warning-free 575-field run, без зміни learning release/runtime.

## Доставка

Лише M58 sources, definitions, finite integration/test/diagnostic files, цей report і M58 Git-byte rules підлягають scoped staging. Секрети, .env, runtime caches, приватні diagnostics/proofs, backups/dumps/screenshots, тимчасовий route та сторонній `tools/diagnostics/capture-practice-template.cjs` виключені. Історичні звіти не переписані.

Завершення: staged diff/scope review, diff --check, frozen-source/index validation → scoped commit → normal push цієї гілки → незалежний remote SHA == HEAD. Без main changes, PR, merge, rebase, force push, workflow dispatch, .com/.ub requests або deployment.
