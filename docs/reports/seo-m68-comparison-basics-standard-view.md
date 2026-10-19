# M68 — ступені порівняння під стандартною в’юхою

Локальне застосування, HTTP/SSR, профільні ізольовані тести, браузерне приймання та заключний post-UI preservation audit завершено. Цей звіт входить до scoped M68 delivery; повний commit SHA й незалежне підтвердження remote наведено у фінальному повідомленні після normal push.

## Scope і погоджена база

База M67: `2161cb0b03afd203933ad022ccb4e77e8e8ca363`.
Робоча гілка: `codex/seo-m68-comparison-basics-standard-view`.
Єдина ціль реалізації, HTTP і браузера — `http://gramlyze.loc`. Main, PR, merge/rebase, force push та production/deploy не виконувалися.

| Урок | Підтверджений локальний URL | Page / category | Continuing slots | Нова практика / display |
| --- | --- | --- | ---: | --- |
| Degrees of Comparison | [Відкрити](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/theory-degrees-of-comparison) | 274 / 52 | 9 | A2 / A2 |
| Comparative vs Superlative | [Відкрити](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative) | 273 / 52 | 11 | A2 / A2–B1 |
| As ... As / Not As ... As | [Відкрити](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/equality-comparison-asas-not-asas) | 276 / 52 | 12 | A2 / A2–B1 |

Точний category slug `prykmetniky-ta-pryslinknyky` і різницю між `theory-degrees-of-comparison` та двома іншими slugs збережено. Четверта сторінка Adjectives vs Adverbs залишилася read-only сусідом, не ціллю модернізації.

## Редактура, структура і межі дизайну

Degrees пояснює утворення форм: типові односкладові, consonant + y, написання -e та подвоєння, перевірені двоскладові альтернативи, прикметники й прислівники, irregular forms і стислий огляд кількості. Немає універсального «всі 1–2 склади → -er/-est», CVC не породжує newwer/slowwer. Farther/further для відстані приймаються; further «додатковий» відокремлено. The не є частиною суфікса. By far перекладено зі збереженням значного відриву. Yours замінює автомобіль, а not bigger за явно заданою місткістю допускає рівність.

Comparative vs Superlative обирає форму за повідомленням, еталоном і повнотою даних, не за кількістю предметів у сцені. Один повний набір посилок показує пару, порівняння з рештою, максимум і мінімум без self-comparison. Taller/better залишаються comparative у the taller/better of the two; the, in та of самі не визначають ступінь. Є присвійні визначники, one of, однаковий максимум, невідомий третій маршрут, контекстне omitted than і коректні pronoun/clause alternatives. Емоційне the best відділено від формального most «дуже».

Equality зберігає рівність за названою ознакою та напрямок not as ... as, а не автоматичне «гірше». Розділено adjective/adverb, many/much, питання/заперечення, повні й скорочені comparator clauses, сталі вирази та кратність на однакових одиницях. Перефразування не змінює рівність на перевагу. Граматична відповідь поза явно заданою моделлю отримує саме таке пояснення, а не неправдиву заборону.

Повні англійські навчальні речення мають український переклад; приклад, переклад і коментар розділені. У meaning tasks переклад відкривається після Check там, де це обіцяє інструкція. Feedback для x-text — plain text, без HTML/entities. Editorial map явно відрізняє збережене джерело, виправлення, нове доповнення й усунутий повтор. Перевірка агентна, не вигадане human review; чужі exercise banks не копіювалися. Direct editorial body, publisher-indexed body, excerpt, 403, tool error і підписані staff responses у журналі не змішані. SEO не спирається на квоту слів або обіцянки ranking/CTR.

Використано чинний канонічний шлях theory.show → content-block → TheoryContentSource/Renderer → спільні theory.components. Blade/CSS, типографіка, кольори, відступи, приклади, sidebar/header/background і practice engine не змінювалися. Нових m68-section/detail/styles або окремого renderer немає.

Збережено 32 continuing slots, їхні UUID, порядок, columns і technical levels. У Degrees всі дев’ять source slots лишилися без explicit level keys, а SQL continuing level — null. В інших уроках збережено змішані A2/B1/B2 та footer A1. Додано по одному A2 practice-set; metadata transitions — 0.

Equality має історичні block-config keys Page_v2, але верхньорівневий source owner і фактичний SQL owner — Page_V3. Ці різні bindings збережено без глобальної заміни або дублювання Page. Нейтральний checklist Comparative лишився SQL/source `mistakes-grid`, right/A2; finite exact-bound semantic projection показує його через чинний `usage-panels`, без wrong/right для правильних контрольних запитань.

| Змістовне «Докладніше» | Точний point owner |
| --- | --- |
| `comparative-evaluation-not-ranking` | `comparative-special` `/sections/2` |
| `equality-comparator-clause` | `equality-usage` `/sections/1` |

У Degrees немає штучно доданої деталі. Необхідні формули, переклади й короткі застереження залишено в basic. Глобального runtime-порога слів немає.

Невізуальна інтеграція реєструє лише exact owners/locale/body та зберігає course і standalone-help callers на копіях моделей. Невідомі/часткові decorations не приймаються; original models/shared options не змінюються. Після виявленого NULL у ланцюжку standalone preservation додано перевірку лише у двох нових M68 dispatch lines TestJsV2Controller: уже виключений попереднім пакетом null не передається в наступний object-only callback. Сторонні байти/EOL і старі accepted clauses збережено.

## Практика і тематичні конструктори

На кожному уроці працює структура 2 selects + 2 choices + 2 inputs із ручним і token-способом: 18 власних завдань, 30 options, шість input tasks і 15 accepted input values. Два способи відповіді не рахуються як два завдання. Canonical token banks зберігають strict case та повторювані instances. Чинний normalizer лишається punctuation-insensitive; оцінювання ком у manual input не заявляється.

| Урок | Окремий тематичний type-4/A2 добір | Practice ID |
| --- | --- | ---: |
| Degrees | 16251, 16253, 16255, 16256, 16258 | 12750 |
| Comparative | 2640, 2641, 2642, 2647, 2649 | 12751 |
| Equality | 2712, 2713, 2714, 2718, 2720 | 12752 |

П’ять питань на сторінку дібрано з трьох існуючих тематичних pools по 12 eligible питань: 15 selected із 36 eligible. Нових questions/answers/options/hints/links не потрібно. Українські clone-only уточнення прив’язані до exact snapshots; SQL keys і shared token formatter не переписано. Окремий widget не підмінений inputs або посиланням на /test.

## Frozen release 1.0.0

Усі дев’ять артефактів перевірено ROOT/WT raw-byte identical: UTF-8 без BOM, LF, один final LF, без trailing whitespace. Checksum companion не включає власний hash усередину себе.

| Артефакт | SHA-256 |
| --- | --- |
| `docs/content/m68-comparison-basics.v1.0.0.json` | `2bbc2d702435d2fd45cf6b53d0655b7efbc293c02a1c0b63676eddf44d16c5d1` |
| `docs/content/m68-before-source.v1.json` | `6f95b0d6174417f29221636377911b3bd05c3a918e62a4cd20ff4b1987d61455` |
| `database/content-patches/m68-comparison-basics.v1.json` | `2e3aa667469414d98ed258dea07004817487e3026cb7577fa1c13d6f0f05e794` |
| `docs/content/theory-sentence-builders/m68-comparison-basics.v1.json` | `d76d289f343c6f2a4bb588ba8e0909c8e852ab0affb0bb4c61788b9d4832b91a` |
| `docs/content/m68-comparison-basics.components.v1.json` | `39005a1356590a520571d3a7dfa38b86584daad350c11a7260ed91a2af3538a1` |
| `docs/content/m68-comparison-basics-bank-audit.v1.json` | `355ade208fd6bce2ebcf122d2492985805d3d185e23d982b7b9357e70c164842` |
| `docs/content/m68-comparison-basics.v1.0.0.uk.md` | `525c1617b08165dd1b1f428b533b08353719df48078da25c7391222c84a52832` |
| `docs/content/m68-comparison-basics.editorial.v1.md` | `aa9764902d88787baf7da3698c080001f7abdcc987b12ab1c9f9eb8fce510530` |
| `docs/content/m68-source-checksums.v1.json` | `55a1e154d0948720465f5d6a20fc6c4800ac0b1af3c9c4c0d9fbf1e2d5833453` |

Readable UK edition містить basic/detail; components map — OLD slot → AFTER native component/data; editorial companion — keep/correct/new/repetition і джерела. Сім public JSON не містять raw SQL rows, фактичних DB timestamps, runtime identities чи credential values. Bank audit лишає teaching IDs/conditions/tokens і opaque snapshot hashes; `original_ordered_instances` видалено.

Незалежна pre-apply projection: `d2a8a7c970a364aca94eb5dd5a23dc41345b8ab3654615b9f8982ce5ac449ffb`. Її patch точно відповідає frozen patch; expected SEO/LearningResource descriptions обчислені до apply. Найбільший body — 16,307 UTF-8 bytes при захопленій TEXT capacity 65,535; DDL не було. Global title extractor не змінювався; H1/title/breadcrumb/canonical/schema identity та локальний noindex збережені.

## BEFORE, proof і guarded local apply

Working BEFORE `e23f859491c59bcb182dc22a73b69b2b8bad14fb85b565e3bd8fd6cbcc1dac46`: 46 таблиць точні до/після шести HTTP-200 captures, 326 visual files і 2,497 historical frozen files.
Old-consumer BEFORE `ac3ff01b09dab2655806e868411f816e69eb6452667632b40c73d3d9d3c9db9b`: 57 HTTP consumers, вісім saved tests, 480 старих питань і 40 source hashes.
Registry BEFORE `da3f3c46af1673c13e72c9e9f36c0764a1f79c93e48bf803109d0babd4d52261`: фактичні 124 owners, failed/writes 0.

Fresh physical/web/CLI proof `b768c002a67ff36cdd8957d45a383a90ce1c691225cd3284e3e3177e90ad43b7` підтвердив саме локальний served target. Environment label сам по собі не використовувався як дозвіл. Тимчасовий GET-only nonce endpoint був exact-host/loopback і не повертав секретів.

Після виправлення двох NULL guards перевидано filesystem-only source-sync v2: `a118e93a6974166c29d230bf820c513ab75e7ee1f25f37e6f8e4a31bba8a5e81`, 28 paths. Саме v2 включено у preview; stale v1 не використовувався для apply. Для foreign-inclusive ROOT/WT controller bytes діють окремі reviewed pins; сторонні ROOT-only loader hunks не замінено whole-file copy.

План `62f008a78d1a49f4396e8db8889cabbbfe5c3cff8c313b55c6714526d60f3bb2` пройшов exact allowlist review. Raw preview та exclusive `backup-v1.json` мають SHA `3218c3263bb28c927e5482126444528924f069d057356d7263e76e3ebfb4c99b`. Writer спожив ordinary preview, не read-only aggregate.

| Дозволене сімейство | Фактичний результат |
| --- | ---: |
| Page.text | 3 updates |
| UK subtitle body | 3 updates |
| Continuing teaching body | 32 updates |
| Разом updates | 38 |
| Нові A2 practice-set | 3 inserts |
| Metadata transitions | 0 |
| Questions / answers / options / pivots / hints / links | 0 changes |
| Deletes | 0 |

Transaction applied: 38 updated / 3 inserted. Practice IDs 12750/12751/12752 підтверджені незалежним SSR UUID/body binding. Backup створено exclusively до записів, без overwrite.

Повторний writer із початковим reviewed plan і повторний writer зі свіжим AFTER plan обидва повернули no-op: updated/inserted/deleted 0/0/0. Fresh after-preview має state=after та порожні updates/inserts; raw SHA `cfd791e3cdaac22529b16ca989734c683d873c883b6d9868a8a6b51c503b7260`. Повторних записів контенту не було; no-op backup files не створені. Фактичний exclusive backup має той самий raw SHA, що й reviewed preview.

Postconditions v1 `1299822aab8ba3f4ac7fa0e3261acb63fffa3a3c471c79f35fb8d6592d4d7714` перевірили всі 46 таблиць і відновили independent BEFORE projection, віднімаючи лише exact approved content updates та три доведені practice rows. Усі старі rows/timestamps, 70 EN/PL rows, course/category/progress і нецільові question families збережені; question-family exemptions немає. 35 continuing UK rows у цьому receipt — 32 teaching + 3 subtitles, не додаткові teaching slots. Visual 326 і frozen 2,497 files незмінні.

Cleanup `be37a5f3b9b977b01747653c917425e6758256d309afcce89dc71bc9fe645013`: routes відновлено byte-for-byte разом зі сторонніми змінами; before/after SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Physical configuration незмінна; окремий anonymous loopback GET вилученого nonce endpoint дав 404, без redirects. Тимчасовий route не належить delivery.

Окремий post-UI `final-readonly-v2.json` має SHA `6d7b70815de8ba39123bc1a5864c86543c9c5b0f2771883c3eb666b731665d76`: після всіх браузерних взаємодій знову PASS для всіх 46 таблиць, 35 continuing UK rows (32 teaching + 3 subtitles), 70 EN/PL rows та трьох practice rows. Старі rows/timestamps, 326 visual files і 2,497 historical frozen files точні; question-family exemptions — порожній список. Повторний frozen validator підтвердив усі дев’ять артефактів у двох копіях і max body 16,307/65,535 bytes. Final finite review підтвердив 16 shared copies та exact reversal 26 owned hunks; runtime code після фінального 159-test run не змінювався. Пізніше виправлення стосувалося тільки нового diagnostic metadata normalizer, не runtime.

Після додаткового явного reset усіх шести select/choice groups виконано ще один заключний read-only audit `final-readonly-v3.json`, SHA `ca04441229e0c4c76f7aeed362b475f7b22d95e3e83cca7c814a44f16e357131`. Результат знову exact для всіх 46 таблиць, старих rows/timestamps, EN/PL і нецільових families; додаткових записів не виявлено.

## Реальний HTTP/SSR, навігація і registry

Strict live SSR `9093175732c7b9c407d6955a95682ce18d4edff454a623a366f2116edc5488d0`: три direct local HTTP 200, 619 навчальних fields (171/199/249). Перевірені native fields/переклади, семантичні accents, таблиці, усі старі anchors, initially closed exact-owned details, metadata та actual practice IDs/UUIDs. Кожна сторінка має 2/2/2 own groups і один widget із п’ятьма exact selected IDs, ordered token case/multiplicity. SQL access і writes у цьому gate — 0.

До успішного SSR gate виправлено лише новий M68 diagnostic wrapper: нормування learning-text punctuation помилково стискало пробіл у metadata `As ... As`. H1/title/breadcrumb тепер зберігають literal title spacing. Сайт, мовні відповіді та immutable M53 primitive цим виправленням не змінювалися.

Чотири unique navigation/contextual destinations — три target URLs та Adjectives vs Adverbs — мають фактичне GET 200 coverage у live SSR + consumer receipts. Нові href не вгадувалися лише за англійською назвою.

AFTER old-consumer audit `904e106e8dde82a3a300ed3e09b913c6edfd7ac2adb3cf82829c6ad56636ac37`: 57/57 HTTP 200 — 3 courses, 16 SSR, 11 JSON, 6 neighbor categories, 21 neighbor pages. Вісім saved tests/480 питань, route declarations, 40 source hashes та точні payload/theory/course/neighbor learning DOM порівняння з BEFORE збережені. Всі 46 таблиць відповідали reviewed AFTER і лишилися точними під час read-only HTTP; final normalized BEFORE також exact. M67, M66 та інші включені сусіди не підмінені fixture-відповідями.

Source-filtered M68 gate `cfe5665abfe39d2095c3778e3f654c7c5b73814c5cfa64686afec590e65cd6e8`: 3 targets, failed/writes 0.
Full registry `4a3e5be6300c67d9c25fc03e2b43a2f363174662a8a1cf69ad43d54cf4bb27bf`: фактично 127 targets, 127 HTTP 200, по одному widget/п’ять selected questions, failed/writes 0.

BEFORE 124 → AFTER 127: жодного втраченого owner, рівно три M68 additions, жодних unexpected owners. Усі 20 старих contract/matcher/widget/status fields точні. Окремо пораховано зміну всіх 124 raw full-HTML `http_sha256`; ці digest не названі byte-identical і не нормалізовані мовчки. Registry BEFORE не зберігає raw bodies, тому причину відмінностей або повну raw HTML equivalence з нього не виводимо. Exact learning DOM/payload preservation доводить окремий consumer audit у своєму scope. Це dynamic discovery, а не постійний ліміт 127; CLI/SSR не є client interaction acceptance.

## Ізольовані тести та відомі baseline failures

Незмінний isolated runner виконував actual WT code у testing/SQLite із приватними runtime/compiled views, без working .env/MySQL/progress writes, з CLI OPcache off. Фінальний один запуск 25 файлів: **159 tests / 35,051 assertions / 0 failures / 0 errors / 0 skips / 1 deprecation**, exit 0, stderr порожній. Receipt SHA: `939a9936c466b3157b84471955c8866fa480f310b441521693404dadf1e547cb`.

Scope фінального run: десять M68 Feature files, дев’ять current-runtime M67 files і шість shared canonical/groups/builder/case/variants files. Protected inventory — 46,661 files, changed 0; BEFORE/AFTER digest `e1ff9fe48ab06d5f3ca47e8478bfe95ff3778dd459be19248b59beacf6383933`. Lineage на accepted fixture відрізняється від actual current-runtime checks; одне не підміняє інше.

| Окремий запуск | Фактичний результат / статус | Receipt SHA |
| --- | --- | --- |
| BEFORE M67/shared | 97 tests / 25,274 assertions / 1 deprecation, exit 0 | `cb5ab889da508d67ee9f7155bcb06d117403aadc01cc877d44385e1917062f9b` |
| Початковий M68 v1 | 65 tests / 9,748 assertions / 1 error / 1 deprecation, exit 2 — не PASS | `615c0896372112228b20e91c8f006b313f421f74d3f5796a9b4e10efb5477af9` |
| M68 v2 після NULL guards | 65 tests / 10,325 assertions / 1 deprecation, exit 0 | `42bdb5c15e3e8a3ed80d044f9ffd1b7a57e6e23ecadefdbbf57bea074c964cbb` |
| Historical literal UI AFTER | 6 tests / 23 assertions / 4 ті самі failures, exit 1 — не PASS | `249c50da2afa76c15e7676f6909a690660c8d7a264e907d1a5a8014efdad72fa` |

Початковий M68 error був реальним NULL-dispatch дефектом у current standalone compatibility; його виправили до guarded apply, не послабленням assertions. Окрема historical група так само не проходила ДО M68 (6/23/4; SHA `668b6277a58a7cf30e9f041b2cef7b7cf608588d50afb8d3aca29c08e1642735`). Її чотири literal source очікування manual suggestions/sync/token-buttons/choice-loop не виправляли редизайном і не зарахували до PASS. У цих runs protected drift 0. Default result не розгортав deprecation trace, тому походження єдиного deprecation не вигадане.

Повторні runs і overlapping BEFORE/AFTER результати не підсумовані в унікальні tests чи нібито повний suite rerun. Окремий pre-freeze formula fixture — 1 test/84 assertions/1 deprecation, а не додатковий live target acceptance.

Frozen Node native-factory run: ROOT served-local 24/24 і WT accepted-worktree 24/24; **це ті самі 24 унікальні tests, не 48**. Fail/skip/cancel/todo — 0, stderr порожній, network calls 0. Кожний run охоплює 18 tasks, усі 30 options, 6 manual + 6 token inputs, 15 accepted values, 6 deferred meaning translations і 12 punctuated/unpunctuated grammatical-but-instruction-rejected attempts. Exact shipped factory script SHA `43b4bd3e266594ce7b6ffe697e979ba64c3a824cfb816af06fd8beef31847f8b`.

| Frozen Node profile | Receipt SHA |
| --- | --- |
| ROOT served-local | `a7302959fa4af18848ebbe1f4b7b0e1efb66854efdc977e9e2b20c3181ba45a5` |
| WT accepted-worktree | `943865be0b107f5c1e11f41f2718a3a978706593f634c2e8698bea5625fb92d1` |

Offline factory не є браузером, widget 5/5, DB/no-JS/print/WCAG прийманням; кількість окремих JS assertions не інструментувалася.

## Фактичне браузерне приймання

Основний агент переглянув усі три real target pages до footer у 18 states: 1440×1000 і 390×844 × light/dark для трьох уроків (12), плюс 320px × light/dark (6). Viewport підтверджено actual DOM, шрифти/ресурси завантажені, browser zoom не змінювався. Learning main вміщується, clipped learning headings/text — 0. Шість native tables мають bounded internal horizontal scroll, scroll width 576px на 320px. Окремий shell-only document overflow 1–14px не приховано й не названо learning overflow; CSS заради нього не змінювали.

Основний агент завершив реальні interaction checks на всіх трьох уроках: 30/30 options (12 correct, 18 distractors); 24 input task/mode/size cases — усі шість inputs × manual/tokens × 1440/390; усі 15 source accepted values та п’ять curly-apostrophe aliases приймаються. Окремо перевірено 18 error states (кожен із шести inputs: empty/partial/wrong), 12 grammatical-but-instruction-rejected спроб (шість відповідей із/без кінцевої пунктуації) і шість mixed delete/return/rebuild cases. У трьох input groups та шести select/choice groups перевірені reset/retry/recheck; повторний Check не подвоює score. Це конкретне покриття, не заявлений Cartesian-добуток усіх помилок × mode × viewport × theme, і не ті самі 24 tests, що у Node.

Усі 15 тематичних widget questions фактично складені й прийняті зі strict bank case та repeated as/the. Три набори пройдено до 5/5; після трьох restart кожний повернувся до fresh 1/5. Це live client interaction, не лише server-presence assertion.

Comparative detail відкрито Enter і закрито Space; Equality — відкрито Space і закрито Enter. Обидва deep links після reload на 320px відкривають лише свій правильний point owner (/sections/2 та /sections/1). Повні фрагменти 907 і 961 символ без clipping; detail body clientWidth/scrollWidth — 222/222. Degrees має 0 details, як у frozen author decision.

Корекції браузерного harness стосувалися case/CSS locator, overmatching disabled-token classes та окремих instances `bag` / `bag.`. Вони не вимагали змін додатка чи answer keys; хибне спрацювання locator не назване UI-дефектом.

Приватний browser receipt `browser-acceptance-v1.json`, SHA `ddf26d4233bee8ef3c782d939abd2b1df5c37d4a4a16384a39628c6db8ffb7cc`, фіксує ці реальні спостереження окремо від SSR і Node.

Окремий companion `browser-group-reset-v1.json`, SHA `d495dc0614d761ea9144dea27263a3387db283ac4135ee97e7e544765f18c3d8`, додатково підтверджує reset кожної з шести select/choice groups: після reset і Check — 0/2, інша група лишається 2/2, після повторної правильної відповіді — 2/2.

Pre-freeze sizing fixtures не підміняють ці real target states. No-JS, print і повний WCAG audit — not_checked; це не доводить ні PASS, ні відсутності підтримки. Приватні screenshots/proofs не вбудовані у звіт і не є commit payload.

## Доставка

Цей звіт входить до scoped M68 commit; повний SHA й independent remote verification наведено у фінальному повідомленні після normal push. Перед доставкою основний агент перевіряє explicit staged allowlist, diff --check, final privacy/frozen hashes і відсутність visual/foreign drift; саме посилання на звіт не підміняє remote verification. Секрети, .env, raw runtime identities/SQL snapshots/timestamps, backups, caches, приватні screenshots/receipts та тимчасові routes не входять до commit payload. Дозволу на production deployment немає.
