# M46–M49 — Practice Completion

## Статус і межі

Фінальний звіт приймання станом на 2026-10-13, підготовлений до scoped commit. Редакцію 1.0.1 успішно застосовано до всіх 12 локальних UK practices: 12 body updates / 0 inserts / 0 deletes. Postconditions, repeated no-op v4, фінальні PHP/Node перевірки, браузерні 12/12 сторінок та свіжий післябраузерний audit after-v6 підтверджені. Службовий EOF cleanup не змінив learning/design bytes або UI 48/96. Commit/push і full commit SHA підтверджуються окремим handoff.

Перша транзакційна спроба 1.0.0 завершилася SQL 1406: practice body Third Conditional перевищив місткість чинного TEXT через надмірну кількість дубльованих feedback keys. Повний rollback підтверджено окремим read-only аудитом; невдалу спробу не зараховано як apply.

Редакція 1.0.1 зменшує finite feedback maps із 661 до 315 keys: це точна підмножина старих keys із незміненими значеннями. Усі 24 умови, банки, canonical/accepted відповіді та їхні пояснення збережені. Максимальний повний AFTER body — 47 184 bytes при фактичній місткості TEXT 65 535 bytes. Міграцій або розширення SQL-колонки немає. Результати 1.0.0 нижче відокремлені від нового coherent приймання.

Actual base: 7d339c237060d76e9f7c7981ab71f07dfad3529d.
Робоча гілка: codex/m46-m49-practice-completion.
Served ROOT: D:/DEV/htdocs/gramlyze.loc; worktree: C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc.

Scope — body 12 наявних UK practice-set блоків: десять conditionals і дві modal-verbs сторінки. Не створюються нові Page owners, practice blocks, question banks, шаблони, CSS, JS чи grader. Теорія, meaningful details, anchors, metadata, EN/PL, навігація, курси й окремі /test не є навчальним write scope.

## Точний склад 12 сторінок

Числа у двох колонках практики означають selects / choices / inputs. BEFORE — незалежно зафіксований стан; AFTER — точна versioned проєкція 1.0.1, підтверджена після локального apply. Rephrase дорівнює 0 в обох станах. Для кожної сторінки live підтверджені чотири desktop/mobile × light/dark states і вісім task/mode/size cases: два ті самі inputs × manual/tokens × desktop/mobile.

| Сторінка | Page / category ID | Practice UUID | BEFORE → AFTER | Live states / mode cases |
| --- | --- | --- | --- | --- |
| [Zero Conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | 53 / 10 | 03b70723-7110-5194-bc15-29355a9fe57a | 3 / 3 / 0 → 3 / 3 / 2 | 4 / 8 PASS |
| [First Conditional](http://gramlyze.loc/theory/conditionals/first-conditional) | 44 / 10 | e1d69f49-298e-56dc-808b-53131f1f2b6b | 3 / 3 / 0 → 3 / 3 / 2 | 4 / 8 PASS |
| [Second Conditional](http://gramlyze.loc/theory/conditionals/second-conditional) | 48 / 10 | 7640dc2b-5d95-5ea5-bccb-3617854df98e | 3 / 3 / 0 → 3 / 3 / 2 | 4 / 8 PASS |
| [Third Conditional](http://gramlyze.loc/theory/conditionals/third-conditional) | 49 / 10 | 386140a6-2c9c-5c1a-9a30-35a089f1ee3c | 2 / 4 / 0 → 2 / 4 / 2 | 4 / 8 PASS |
| [Mixed Conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals) | 47 / 10 | ddee7c10-9260-54d8-8b38-5204ad9f77a3 | 2 / 4 / 0 → 2 / 4 / 2 | 4 / 8 PASS |
| [Unless / Provided / As long as](http://gramlyze.loc/theory/conditionals/unless-provided-as-long-as) | 50 / 10 | 2cf832f6-8b24-53b5-9c62-9552feaae1fb | 2 / 4 / 0 → 2 / 4 / 2 | 4 / 8 PASS |
| [In case](http://gramlyze.loc/theory/conditionals/in-case) | 46 / 10 | 06f78006-775f-551c-994c-7539cadc0220 | 3 / 3 / 0 → 3 / 3 / 2 | 4 / 8 PASS |
| [Wish / If only](http://gramlyze.loc/theory/conditionals/wish-if-only) | 51 / 10 | 951b4b66-dc6f-5e1d-b833-f2ab4afc0431 | 3 / 3 / 0 → 3 / 3 / 2 | 4 / 8 PASS |
| [Would rather](http://gramlyze.loc/theory/conditionals/would-rather) | 52 / 10 | 6e5976e3-22db-59b1-955e-16c0033052eb | 3 / 3 / 0 → 3 / 3 / 2 | 4 / 8 PASS |
| [Had better](http://gramlyze.loc/theory/conditionals/had-better) | 45 / 10 | b86091c3-5bdc-55bb-a5c9-5db128088d97 | 2 / 4 / 0 → 2 / 4 / 2 | 4 / 8 PASS |
| [Can / Could](http://gramlyze.loc/theory/modal-verbs/can-could) | 72 / 15 | 240cf788-b9ba-547d-8b31-1672a489235a | 2 / 4 / 0 → 2 / 4 / 2 | 4 / 8 PASS |
| [May / Might](http://gramlyze.loc/theory/modal-verbs/may-might) | 74 / 15 | 83055bed-7a20-579c-9bac-6824d4df325e | 4 / 2 / 0 → 4 / 2 / 2 | 4 / 8 PASS |

Exact identities і нові стабільні IDs:

| Page | Exact identity | Input source_index 7 | Input source_index 8 |
| ---: | --- | --- | --- |
| 53 | Database\Seeders\Page_V3\Conditionals\ConditionalsZeroTheorySeeder | pc-m46-m49-zero-conditional-habit-soup | pc-m46-m49-zero-conditional-negative-dictionary |
| 44 | Database\Seeders\Page_V3\Conditionals\ConditionalsFirstTheorySeeder | pc-m46-m49-first-conditional-future-poster | pc-m46-m49-first-conditional-question-guests |
| 48 | Database\Seeders\Page_V3\Conditionals\ConditionalsSecondTheorySeeder | pc-m46-m49-second-conditional-imaginary-sign-language | pc-m46-m49-second-conditional-advice-estimate |
| 49 | Database\Seeders\Page_V3\Conditionals\ConditionalsThirdTheorySeeder | pc-m46-m49-third-conditional-past-cake | pc-m46-m49-third-conditional-past-question-guide |
| 47 | Database\Seeders\Page_V3\Conditionals\ConditionalsMixedTheorySeeder | pc-m46-m49-mixed-conditionals-past-trees-now-shade | pc-m46-m49-mixed-conditionals-stable-taste-past-curry |
| 50 | Database\Seeders\Page_V3\Conditionals\ConditionalsUnlessProvidedAsLongAsTheorySeeder | pc-m46-m49-unless-provided-as-long-as-exception-library | pc-m46-m49-unless-provided-as-long-as-permission-photo |
| 46 | Database\Seeders\Page_V3\Conditionals\ConditionalsInCaseTheorySeeder | pc-m46-m49-in-case-7 | pc-m46-m49-in-case-8 |
| 51 | Database\Seeders\Page_V3\Conditionals\ConditionalsWishIfOnlyTheorySeeder | pc-m46-m49-wish-if-only-7 | pc-m46-m49-wish-if-only-8 |
| 52 | Database\Seeders\Page_V3\Conditionals\ConditionalsWouldRatherTheorySeeder | pc-m46-m49-would-rather-7 | pc-m46-m49-would-rather-8 |
| 45 | Database\Seeders\Page_V3\Conditionals\ConditionalsHadBetterTheorySeeder | pc-m46-m49-had-better-7 | pc-m46-m49-had-better-8 |
| 72 | Database\Seeders\Page_V3\ModalVerbs\ModalVerbsCanCouldTheorySeeder | pc-m46-m49-can-could-7 | pc-m46-m49-can-could-8 |
| 74 | Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMayMightTheorySeeder | pc-m46-m49-may-might-7 | pc-m46-m49-may-might-8 |

Проєкція зберігає всі 72 старі завдання без зміни source_index 1–6, умов, options, answer/accepted, feedback і наявних ID. 42 старі завдання мають явні ID; 30 історично ідентифікуються source_index, і нових ID їм не вигадано. Додано 24 inputs: підсумок 96 власних завдань, по 8 на сторінку.

У нових банках 169 token instances; повторювані слова не злиті. 50 declared accepted entries включають 24 canonical і 26 додаткових рядків. Це не 50 нових завдань і не число всіх автоматичних variants. Повні українські умови збережені у штатному after, shuffled slash banks — у before; сім полів нового input: id, source_index, before, after, answer, accepted, feedback.

## Sources та незалежні знімки

Активне покоління 1.0.1:

| Артефакт | SHA-256 |
| --- | --- |
| [Master](../content/m46-m49-practice-completion.v1.0.1.json) | f98fb2e19a8bd619802dcd2f3e350b19414f81a508c69a75a2de3147f3652b89 |
| [Незалежний public BEFORE](../../database/content-patches/m46-m49-practice-completion-before.v1.json) | b59cc3c523244fb1285b149a4e27418df80ebcedb7b03968de497ec062edd099 |
| [Точний patch BEFORE → AFTER](../../database/content-patches/m46-m49-practice-completion.v1.0.1.json) | 51b35c0b099d4ab2f72c33ccfee070763e781a5c54888adde11079b0482b78a8 |

[Читабельний пакет 1.0.1](../content/m46-m49-practice-completion.v1.0.1.uk.md) — SHA-256 7bc5f832d9ba58a0ed80cc9f5508e7f9898b7ee45aaa70e16f77da28738fd866; [редакторська записка 1.0.1](../content/m46-m49-practice-completion-editorial.v1.0.1.md) — SHA-256 d7de950051ef8781370a17abb6576be4fd0862d2172597fb5ea95419f2bbe4bd. Копії ROOT/WT ідентичні.

[Історичний checksum manifest 1.0.0](../content/m46-m49-practice-completion-checksums.v1.json) містить 18 тодішніх файлів і не перепризначається на bytes 1.0.1. Старі sources/docs і manifests M46–M49 не переписані. [Новий checksum manifest 1.0.1](../content/m46-m49-practice-completion-checksums.v1.0.1.json) містить рівно 18 актуальних файлів: master 1.0.1, незалежний BEFORE v1, source 1.0.1, readable/editorial 1.0.1, theory-template.md і 12 активних definitions. Для всіх 18 actual ROOT/WT raw SHA-256 збіглися. SHA-256 manifest: 0336ad6bc5e3a9125cda31096883b5f512c4a808a11d0f53e00ac366feaafb22.

Приватний before-v3.json: SHA-256 c3e045006579df0193be1bafa5ad6e54e5a4d6828b4d333598d62714d375c1d6. Він містить незалежні Page/row/source/HTTP fingerprints: 12 theory + 12 course URL повернули 200. Це 12 цільових уроків, а не 24.

Приватний frozen-before-v2.json: SHA-256 8a30c976ed5d214dcf122724ad8e4a1f6e2c3703c742a909782b210b79075c87, 1533 незмінні історичні/нецільові source paths. Єдиний дозволений process-document виняток — [theory-template.md](../content/theory-template.md): до нього додано критерій повноти практики та acceptance checklist. Він включений у reviewed source-sync, а не мовчки виключений із контролю.

Поточний source-sync-v4 зв’язує 23 точні ROOT/WT paths і 326 visual source hashes із before-v3; його SHA-256 — 6dc1ba7766de9ccf5c545bcdba8c11d22b1900017e9a92f43fe4391673b5a05b. Source-sync-v2 належав успішному початковому apply 1.0.1, v3 — narrow guard-order correction, v4 — фінальному EOF cleanup. Існуючі сторонні ROOT зміни не замінювалися всім worktree. PHP EOL-відмінності допускаються лише визначеним невізуальним sync-контрактом; frozen learning sources перевіряються raw hashes. Assets не перебудовувалися.

Перед фінальним staged review прибрано лише один зайвий порожній EOF-рядок у кожному з дев’яти власних code/test/doc файлів. Теорія, master/BEFORE/source, 12 active definitions, readable/editorial і всі visual files не змінені. У theory-template.md навчальний/process текст той самий; його raw SHA тепер 4dd38be95f1a89f9b5f13a13f5cc504d805066e8686ccbc81c803fface871bd1. Новий 18-file manifest оновив лише цей doc pin; усі 18 pins ROOT/WT/Git index збіглися. Patch змінив тільки службову назву SYNC_RECEIPT v3 → v4. CRLF-aware strict staged check `git -c core.whitespace=blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol diff --cached --check` — PASS, без `-blank-at-eof` exemptions і без зміни постійного Git config. Plain default check позначив лише 12 змінених body lines через успадковані CRLF у hash-bound definitions із `-text`: це не нові trailing spaces. Прийняті raw bytes/EOL цих 12 definitions збережено; їх не переписували для проходження check. Final staged review та доставка — окремий крок.

## Невізуальна інтеграція й closed guards

- Новий PracticeCompletionPackage окремо перевіряє historical M46–M49 BEFORE і весь відомий supplemental AFTER. Немає заміни count === 6 на count >= 6 або ігнорування невідомих полів.
- Пакет зв’язує identity, ancestry, locale, Page ID, page_category_id, UUID, type, column, heading, level, css_class, sort_order та точний raw body. Дозволена body-різниця — тільки input_title, input_intro, inputs і technical marker practice_completion_v1.
- TheoryContentSource додає code-owned presenter лише всередині наявної caller-owned theory-межі. Після повної перевірки AFTER тимчасовий clone із BEFORE доводить старий source binding, а незмінений renderer отримує весь новий practice payload із двома inputs.
- TheoryCourseManifestService використовує окремий exact-bound clone: тільки supplemental practice body нормалізується до historical BEFORE, після чого працює старий all-or-nothing course-preservation guard. Оригінальні models не мутуються; foreign owner, locale, sibling drift або невідомий body не нормалізуються.
- PracticeCompletionPatch успадковує чинну транзакцію, exclusive backup і postconditions. Write allowlist — тільки body 12 старих UK practice rows, без inserts, Page updates, restore, reseed або schema changes. На початку plan виконується connection/local-target guard: чужа конфігурація БД відхиляється до capacity SQL і читання source files. Лише після цього preflight читає фактичний CHARACTER_OCTET_LENGTH body через INFORMATION_SCHEMA.COLUMNS та перевіряє byte length усіх 12 AFTER до backup/write. Pure capacity guard відхиляє нульову місткість і перевищення.
- Незалежний metadata baseline перевіряє всі поля Page і всіх target-owner rows. SQL fingerprints охоплюють усі фактичні таблиці й виключають лише body цих 12 рядків; їхні metadata та timestamps залишаються захищеними.
- Фізичний local guard вимагає точний gramlyze.loc ROOT/document root, loopback GET, свіжий nonce proof, дозволений DB target/port, без query/cookie/auth/proxy headers і без альтернативних URL/socket/read-write configurations. Немає HTTP/startup/scheduler apply hooks.

## Банки, linked cards і курси

На кожній із 12 сторінок BEFORE є власний існуючий блок посилань на тематичні тести, включно з Sentence Builder та mixed test. Це **linked card**, а не обов’язково вбудований інтерактивний sentence-builder widget. Еталон PPC Forms має один embedded widget; його наявність не є підставою додавати такий widget до кожної цілі.

Нове доповнення не створює embedded widgets або question banks. Дві власні inputs-вправи не підмінені linked-bank питаннями. Before і after captures підтвердили 24/24 HTTP 200: 12 існуючих Sentence Builder і 12 mixed consumers. У всіх 24 повний server HTML, включно з question/answer datasets, scope links, head metadata, assets і scripts, збігся після лише визначеної per-request CSRF нормалізації. Після apply також 12 course HTTP відповіли 200, їхній повний content DOM збігся з BEFORE.

CSRF-політика точна: під час capture редагується один csrf-token meta content; під час порівняння — рівно чотири спостережені входження того самого request token у const CSRF_TOKEN, saved-test-js-v2 config.token, X-CSRF-TOKEN meta-lookup fallback і Livewire data-csrf. Кожний контекст має трапитися рівно один раз; невідоме залишкове входження спричиняє failure. Навчальні рядки, answers/aliases, URLs, scripts, asset paths, whitespace, punctuation та IDs не нормалізуються. Raw captured HTML без цієї нормалізації не заявляється byte-equal.

Receipt bank-consumers-comparison-v2.json — PASS, SHA-256 8e2318bee19e0d0767b4f697682789210a0a13a489bdd5677f4ac5e7b841a6a6. Попередній comparison-v1 врахував лише один CSRF context і коректно провалив complete-HTML checks; його передчасний unconditional висновок замінено виміряним v2. Це read-only GET/HTML comparison, не answer submissions або scoring тестових consumers. Видимість і точний scope існуючих linked cards перевірені також у target-page live matrix; новий embedded widget не додавався.

## Виконані ізольовані перевірки

### Фінальне PHP-покриття 1.0.1 без подвійного рахунку

Підсумок — **393 унікальні тести / 31 935 assertions**: 316 історичних тестів із завершеного 18-class проходу плюс 77 актуальних completion тестів після narrow early-guard correction. Це не один запуск із 393 тестами і не сума 392 + 77.

| Зарахована частина | Унікальні тести | Assertions | Результат |
| --- | ---: | ---: | --- |
| PracticeCompletionPackageTest, актуальний rerun | 6 | 1994 | PASS |
| PracticeCompletionPatchTest, актуальний rerun | 12 | 530 | PASS |
| PracticeCompletionLocalTargetGuardTest, актуальний rerun | 59 | 279 | PASS |
| Актуальні completion suites разом | 77 | 2803 | PASS |
| Історичні M46–M49 / universal, 15 classes попереднього завершеного проходу | 316 | 29 132 | PASS |
| Разом, без повторного підсумовування підгруп | 393 | 31 935 | PASS |

Попередній coherent прохід 18 classes мав 392 / 31 879 PASS: 316 історичних + тодішні 76 completion. Після виправлення раннього guard повторено тільки три completion suites — 77 / 2803 PASS; після EOF/source-sync-v4 повторено ті самі 77 / 2803. Останній результат замінює попередні completion reruns, а не додається до всіх 392. У зарахованих проходах failures/errors/skips 0, child і runner exit 0.

PHP 8.5.10 / PHPUnit 12.5.35; environment testing, SQLite :memory:, cache/session array, CLI opcache false, working_env_loaded false. Робоча MySQL не використовувалася. Одна чинна deprecation PDO::MYSQL_ATTR_SSL_CA походить із config/database.php:62; у 18-class проході її зафіксовано під час 112 тестів. В останньому 77-test проході та сама одна deprecation спрацювала під час 18 тестів. Це одна проблема, не 112 або 18 різних дефектів і не failure. Config не змінювався.

У зарахованих завершених protected windows — 46 661 файл, змін 0. Докази:

- Попередній повний прохід: practice-completion-final-v1_0_1-84924a2733584484b579dca5214b69b9-result.json, SHA-256 1dd7874325c3df8a2a4b99463394558cf2d159e340a5564e779a2b2fc8532636; JUnit SHA-256 2a48b6061fbf4b2066f480f69c2b9c3b65c3515f8c5977d71faad25d45053db3.
- Актуальні три suites після EOF/source-sync-v4: practice-completion-final-eof-sync4-9adc14e08f114a6991b409ba517f6fd2-result.json, SHA-256 97664171e6cd385255e85a4452c598ab6a51e323ba14087b490b75a806d56560; JUnit SHA-256 b26f21b570c1ad62d8400a54225a15f6f0429105df8d8ca0bebddee50a63bf40. Попередній early-guard rerun practice-completion-guard-order-final-44ddcf8a188e4673974ce70e7a147052-result.json (SHA-256 9f6751081eb9edeceb916bff416cf346eab77fcf5d14c340d48f220618947d16) підтвердив ті самі 77 / 2803 і не додається до унікального підсумку.

Pure capacity case перевіряє ASCII/UTF-8 65 535 bytes як дозволені, 65 536 bytes і max=0 як відхилені. Новий 59-й GuardTest фіксує ранню відмову чужої DB-конфігурації до capacity query/source I/O. Фактичну MySQL TEXT capacity окремо підтвердив local preflight; SQLite-перевірка не видається за MySQL storage test.

Історія запусків не додається до підсумку. 1.0.0 new-v2 мав 75 / 2739 PASS, але не передбачив SQL TEXT limit. Попередній failed v1 мав помилку mutation fixture: column іноді вже дорівнював right; виправлено тільки fixture на гарантовано інше значення з assertNotSame. Старий 15-suite regression прохід 1.0.0 перервано як superseded після закриття protected window — це не PASS.

### Незалежний file-only review редакції 1.0.1

134 перевірки — PASS: усі 24 task conditions/banks/canonical/accepted поля незмінні; 315 retained feedback keys — точна підмножина 661 зі старими значеннями, 346 надлишкових keys видалено; 122 mandatory variant keys збережені. Підтверджено 12 lessons, 72 old tasks, 24 inputs, 50 declared accepted, 169 token instances і max body 47 184 bytes при capacity 65 535. Receipt revision-v101-review.json SHA-256 c1e91ce82267dadf4910907b9667b3b42edcbb97eb47640b8bc831e564b7bc60. Це окрема file-only перевірка без bootstrap/DB/HTTP/browser, не додаткові PHPUnit або Node test cases.

### Offline Node

Фінальний WT-прохід після EOF/source-sync-v4 має ті самі 137 унікальних PASS: M46 — 14, M47 — 19, M48 — 20, M49 — 21, completion — 63; failures/skips 0. Private node-final-eof-sync4.tap SHA-256 42839d747e00c9b42b36523c06ba52170d0c2186ba37c4bf5d160258e1e10a1d. Попередній coherent 1.0.1 node-main-v101.tap мав SHA-256 a7d7029b0bd7a87fee2d8300f5118ec1df6a473cbe04c9e9448b35e68e664ea3. Reruns і ROOT/WT mirror executions не додаються як різні тести.

Completion state suite працює з фактичною незміненою native Alpine factory та EnglishAnswerVariants, без Laravel bootstrap, мережі чи БД. Для 24 inputs перевірені manual/token paths, canonical і declared aliases, усі 169 instances, повторювані слова, wrong/repeated wrong/edit/correct, повтор check без подвійного score, reset/rebuild, незалежність старих groups. Окремий попередній 63-test ROOT mirror 1.0.0 також PASS; це ті самі 63 тести, не ще 63 унікальних і не новий ROOT-прохід 1.0.1.

Рендеринг 12 сторінок в ізольованому PHP підтвердив третій штатний зелений inputs-блок, по два text inputs, українську after-умову і незмінні старі mappings. Це fixture render, не live browser acceptance.

## Guarded local workflow і Git-доставка

| Етап | Стан |
| --- | --- |
| Незалежні source/DB/HTTP BEFORE, фізичний local-target proof | PASS для зафіксованого BEFORE v3 |
| Fresh preview та exact diff review | PASS: 12 body updates / 0 inserts / 0 deletes; 0 Page writes |
| Exclusive backup і transactional apply 1.0.0 | FAILED: SQL 1406 на Third Conditional; повний rollback підтверджено |
| Нова редакція 1.0.1, свіжі pins/sync/preview/review | PASS: apply за source-sync-v2, guard correction v3, фінальна 23-file source-sync-v4 звірка; fresh preview/no-op v4 завершено |
| Успішний apply, postconditions, фактичний DB diff | PASS: 12 body updates / 0 inserts / 0 deletes, backup-v2, after-v2 |
| Repeated no-op 0 / 0 / 0 | PASS для v2/v3/v4 без нових backups; актуальний v4 підтвердив state AFTER |
| PHP: completion + історичні/universal regressions | PASS: deduplicated 393 / 31 935; фінальний повтор 77 / 2803 після EOF/source-sync-v4 замінює попередні completion результати |
| Live matrix 12 × desktop/mobile × light/dark = 48 states | PASS: 12 / 12 сторінок, 48 унікальних states |
| Усі 24 inputs: manual і tokens на desktop та mobile | PASS: 96 унікальних task/mode/size cases, по 8 на сторінку |
| Старі 72 завдання, teaching DOM і courses після apply | PASS: data/engine/DOM parity; 72 унікальні старі controls пройдено в browser на всіх 12 mobile pages; 12 додаткових desktop executions |
| Postconditions і свіжий післябраузерний audit | PASS after-v6: 1533 frozen, 326 visual, protected projection усіх 46 tables без нецільових змін; 24 / 24 HTTP 200 |
| Exact nonce route restore + GET 404 | PASS: усі чотири тимчасові routes прибрані; чотири GET 404; routes/api.php byte-exact відповідає BEFORE |
| Git-доставка | CRLF-aware strict staged check PASS; звіт фіналізовано до commit. Scoped commit/push і remote SHA == HEAD підтверджуються окремим handoff |

Rollback-v1 receipt SHA-256: 2a6843b473b36a01be60dc90fa1235e7e553fd8df641e00e4410054809ecc1b3. Після failed 1.0.0 підтверджено 12 BEFORE / 0 AFTER / 0 other, незмінні 283 text blocks і 46 tables / 1 290 504 rows.

Reviewed preview-v2 SHA-256: ec5d28c0649625160084126842d82d1536178f559c608bc57d3439c077cd1cfd. Після успішного apply 1.0.1 after-v2.json SHA-256 c927c5f62098c8d346263432f9259aa0a7767a0bb9b9851817cc7de3fe5ea1a4 підтверджує 12 theory + 12 course HTTP 200, збереження 72 old tasks / 24 new inputs, teaching DOM поза practice та повний course content DOM без змін. Перший after-v1 мав один course timeout 28 і не зарахований як PASS; retry after-v2 — один завершений набір 12 + 12, не додаткові унікальні сторінки.

Для кожної з 12 сторінок у таблиці вище збереження старих шести завдань підтверджено data/engine/post-apply/browser перевіркою. Усі 24 нові inputs пройдено вручну та токенами на desktop і mobile: 96 унікальних task/mode/size cases. Старі 72 controls пройдено на всіх 12 mobile pages; Zero і First дали ще 12 desktop executions. Разом це 84 виконання старих controls, але лише 72 унікальні завдання.

Основна matrix — 48 унікальних states. У кожному є три own-practice cards, два input fields, видима українська умова і чинні linked cards; поля й картки практики поміщаються в viewport. Усі 48 states зберегли відповідний BEFORE detail count; базовий набір 12 сторінок має 9 details. Теорія/semantic panels не втратились через новий practice body.

Окремо перевірено May / Might на narrow 320 px у двох темах; ці два стани не додано до 48. PPC Forms відвідано як одну reference page, не як тринадцяту ціль.

Private browser receipt ui-live-v101-complete.json SHA-256 d43875045390abcb5fe0dc6a0c4a61b6092df8b7695f8fd8fdab6b3ff7dac3b0 фіксує 48 unique matrix states і 96 unique mode cases. Після завершення власну browser tab 2 залишено як deliverable; user tab 1 не змінювалася. Viewport повернуто до 1280×720/light; фінальний screenshot final-may-might-practice-v101.jpg збережено приватно.

У 15 із 48 document states зафіксовано змінний overflow 2–13 px від анімованих порожніх декоративних SPAN поза main. Весь власний practice content залишається в межах viewport. Це окремо зазначене спостереження, не прихований practice overflow або дозвіл змінювати CSS. Visual sources у цьому завданні не редагувалися.

Після early-guard correction виконано fresh preview/apply no-op v3; після EOF/source-sync-v4 повторено v4: обидва дали 0 / 0 / 0, нових backups для них не створено. Фінальний preview-noop-v4 підтвердив state AFTER і protected projection 46 tables; plan SHA-256 61d2d48d913b55b4ad117daa963f1c95fc6160ca50a3bbaebac4d82295f92cd4. Фактичний apply no-op v4 також дав 0 / 0 / 0, backupnoop4 не створено. Усі чотири тимчасові nonce routes прибрані; routes/api.php має початковий raw SHA-256 2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689, усі чотири nonce GET повернули 404.

Після завершення UI спроба audit v3 зупинилася на Zero theory timeout, v4 — на Zero course timeout; їх не названо PASS. Наступний after-v5.json завершився PASS (SHA-256 dc58298f2207ac48b3c63488e4dcff4577a4d7ad729e101a8f1dd7053e6aa281). Після EOF/source-sync-v4 виконано фінальний fresh after-v6.json — PASS, SHA-256 a51699071923a8a70f7f78293a02c753aa6d569f51b0616c2e1d8b43cc54a2a5. Він підтвердив 24/24 HTTP 200 (12 theory + 12 courses), збереження 72 старих завдань і 24 нових inputs, protected projection усіх 46 SQL tables, 326 visual files та відсутність змін у theory/metadata/course content. Повторна raw звірка 1533 frozen ROOT paths також пройшла. Незалежний read-only Zero course GET повернув 200; налаштування сервера не змінювалися. Повторні HTTP спроби не додано до кількості унікальних сторінок або browser states.

## Обмеження

- Для довільної невідомої неправильної ручної відповіді unchanged native fallback показує стандартний wrong result і EN canonical без повного українського перекладу/пояснення. Finite feedback не покриває нескінченну множину помилок.
- Автоматично прийнятий EnglishAnswerVariants рядок не є author-approved grammar whitelist. Нормативні рядки пакета — точні answer/accepted.
- Synthetic standalone ? має відому межу used-state після manual synchronization: token-word matching ігнорує голу пунктуацію. Реальні банки використовують приєднані question marks; engine не виправлявся.
- Одна чинна PHP 8.5 deprecation: PDO::MYSQL_ATTR_SSL_CA у config/database.php:62. Це не failure; сторонній config не змінювали.
- Offline state/render tests не доводять реальну DOM-видимість, клавіатурні події, paste/Backspace, mobile layout або browser score. Live результати додаються лише за фактичними evidence.
- JS-off і print не виконані: поточний browser interface не надає потрібної capability. Serialized answers не названі доступним ключем без JS. Нового no-JS механізму немає; browser zoom поза scope.
- Main, production .com/.ub, server configuration і deploy не змінюються. Наявні production canonical URLs у локальному HTML не означають production-візитів.

## Доставка

Звіт фіналізовано до commit після завершення контентного, UI, ізольованого test і свіжого local приймання. Scoped staged review, normal commit/push робочої гілки та звірка remote SHA == HEAD завершують доставку; їхній фактичний результат і full commit SHA наводяться в окремому handoff. Цей звіт не заявляє наперед уже виконаний push і не містить само-посилального commit hash.

.env, secrets, DB backups/dumps, private captures/proofs/screenshots, caches, generated assets та сторонні dirty changes не включаються до commit. Історичні M46–M49 звіти й frozen learning releases не переписуються.

## Follow-up: вбудована «Вправа 4. Побудуй речення»

Окреме виправлення після завершеного вище Practice Completion, за прямим уточненням користувача: на цих самих 12 сторінках потрібний вбудований sentence-builder widget, як на PPC Forms, а не лише linked card до окремого тесту. Історичний звіт вище, його counts і receipts залишені без змін. Source-backup commit перед цим follow-up: 10ceafdb668554027325db1832cd8ec4a2af0378; гілка — codex/m46-m49-practice-completion.

### Що змінено

Додано тільки render-only підключення наявного native widget до 12 точних UK practice owners. У stored practice body не дописується linked_practice; всі frozen master/BEFORE/source 1.0.1 та 12 активних definitions залишаються незмінними. Це display-only follow-up: DB writes — 0; нового content apply чи DB backup для нього не потрібно.

[Скінченний manifest](../content/practice-completion-linked-widgets.v1.json) містить 12 exact metadata/body bindings і лише шість наявних config keys: source, question_types, seeder_classes, title, intro, footer. SHA-256 manifest — af9883ee79793d3f0182fe2bd41e5846c905722de1e1b1f380073079c3f2ccc2. Новий app/Support/PracticeCompletionLinkedPractice.php має SHA-256 5b356e7c97505851d7933a39f6bc4216925c3409a46994560177a6d12058e6a5; ROOT/WT копії ідентичні.

- PracticeCompletionPackage додає config лише до повернених presentation data після підтвердження відомого AFTER. Original model і raw body не мутуються.
- PageController використовує matching-only clone виключно за pageType === theory та showView === theory.show. Clone передається чинному TextBlockToQuestionsMatcherService; оригінальні page/textBlocks залишаються незмінними.
- Helper вимагає canonical caller, app locale UK, block locale UK, точні owner/Page/category/UUID/type/column/heading/level/css_class/sort_order, raw AFTER hash і відповідність decoded data. Manifest та source 1.0.1 перевіряються за pinned SHA. Foreign/courses/EN/PL/змінений body повертають попередні data/model без opt-in.
- Чинний matcher використовує source=theory_links, лише type 4, один точний bank seeder, page-wide linked UUID pool і незмінений exact technical CEFR level. Він бере максимум п’ять питань. Немає broad fallback до іншого банку або зняття CEFR filter.
- Існуючий practice-set уже передає questions/title/intro/footer у text-block-practice-questions. Нових Blade, CSS, JS, шрифтів, кнопок, grader або форми вправи не створено. Курсовий caller не отримує цей opt-in.

Усі 18 pins чинного content manifest 1.0.1, 326 visual files і 12 source definitions linked banks незмінні. Question banks, pivot relations, saved tests, answers/aliases та локалізації не редагуються.

### Рахунок завдань і початковий доказ

72 старі власні завдання + 24 власні inputs = 96, без змін. Вбудований widget обліковується окремо, не замінює inputs і не збільшує їхній score.

У 12 чинних банках є 864 наявні питання (по 72), а не 864 нові. Read-only inventory підтвердив для кожного exact bank/page pool 12 питань потрібного technical CEFR level, достатніх для штатного limit 5. Прямих links на practice UUID немає; чинний page-wide matcher використовує links на інші theory blocks тієї самої сторінки. Окремий fresh HTTP audit підтвердив фактичне відображення: 12 theory URL повернули 200; на кожній сторінці рівно один native data-sentence-builder і п’ять JSON questions, разом 60. Усі питання мають type 4 і точний target CEFR. Це 60 відібраних existing linked questions, а не новий банк і не 60 browser interactions.

На May / Might фактичний DOM уже показав H4 «Вправа 4. Побудуй речення», лічильник 1/5, українську умову й банк слів; попередні три own-practice cards і два manual inputs залишилися. Це початковий доказ однієї сторінки, не завершена matrix для всіх 12.

### Приймання follow-up

| Перевірка | Поточний стан |
| --- | --- |
| Новий helper/manifest і ROOT/WT parity | PASS; обидва PHP-файли helper/test пройшли lint |
| Ізольовані чотири suites: PracticeCompletionLinkedPracticeTest, PracticeCompletionPackageTest, TextBlockToQuestionsMatcherTest, M26InteractivePracticePackageTest | PASS: 27 унікальних тестів / 3541 assertions; protected window CLOSED, 46 661 файл без змін |
| 12 HTTP widget datasets / scope | PASS: 12 / 12 HTTP 200, один widget і п’ять питань на кожній сторінці, разом 60; exact type 4 / CEFR |
| Browser desktop/mobile, native interactions | ОБМЕЖЕНО: May initial presence + п’ять dataset records підтверджені; click acceptance цього follow-up не завершено через недоступність керування browser |
| Fresh read-only after-v7: 24 theory/course HTTP та protected projection 46 SQL tables | PASS: 24 / 24 HTTP 200, 46 protected tables, 326 visual files, 0 theory/metadata/course changes; DB apply не виконувався |
| Scoped Git-доставка follow-up | Звіт фіналізовано з явним browser limitation; actual normal commit/push і full SHA підтверджуються окремим handoff |

Фінальний ізольований прохід follow-up: PracticeCompletionLinkedPracticeTest — 6 / 1073; PracticeCompletionPackageTest — 6 / 2054; TextBlockToQuestionsMatcherTest — 11 / 24; M26InteractivePracticePackageTest — 4 / 390. Разом 27 унікальних тестів / 3541 assertions, failures/errors/skips 0, child/runner exit 0. Receipt practice-completion-linked-widget-v2-7ca764e7503a4d758c4b01527522f999-result.json SHA-256 ef069823a6154dbb73ddc7a53b302237bdf31fb37649d1d5e016b3d5d79cd38e; JUnit SHA-256 de6785f265e4ae1c7600effc40b9c965224bf940825708e8a4fa0acc7bd97249. PHP 8.5.10 / PHPUnit 12.5.35, SQLite :memory:, array cache/session, working environment не завантажувався. Protected BEFORE/AFTER: 46 661 файл, змін 0. Та сама одна PDO constant deprecation з config/database.php:62 спрацювала під час 27 тестів; config не змінено.

Перший follow-up запуск мав 27 / 3412, 1 failure і 1 error; він не є PASS і не додається до фінального coverage. Виправлено тільки дві нові test fixtures: reference equality для intro помилково брала старе WT формулювання PPC замість уже погодженого actual ROOT «за українською умовою»; temporary-path assertion викликався до створення owned child directory. Перевірка parent ownership, створення child і його повторна перевірка зберігають isolation guard. Runtime/manifest/PPC sources для проходження тестів не змінювалися. Initial receipt SHA-256 7c138b15fabc7bf694f667d0975661b7782ad100dd1f12a425a35ed503f40c59; protected changes також 0.

Browser limitation поточного follow-up: на May / Might підтверджено лише початкову видимість widget та dataset із п’яти питань. Жодний новий click acceptance не завершено. Спроби відновлення керування повторно закінчилися timeout у CDP Runtime.evaluate / Page.getFrameTree; після reset новий inventory показав власну tab 2 на Zero, але binding не встановився через Emulation.setFocusEmulationEnabled. open_in_codex повернув queued, що не є доказом успішної взаємодії. Інтерактивне покриття 12 сторінок, mobile або dark у цьому follow-up не заявляється. Попередні UI 48 / 96 у незмінній історичній частині стосуються іншого завершеного етапу й не підміняють цю перевірку.

HTTP receipt linked-widget-http-v1.json SHA-256 7d3a0f6374f268df98acf79e7325fd7bfaee0288900277af01f7526182cda311 підтверджує 12 actual widget datasets. Це server-response/JSON evidence, не browser state, click, score, retry/reset або mobile acceptance.

Read-only after-v7.json SHA-256 5f86425d650028f42d46de7313cf8a801c5b2334b4f9e2bcaf4ff35cf460b1df підтвердив 24 theory/course HTTP 200, protected projection усіх 46 SQL tables, 326 visual files, незмінні 72 old tasks і 24 inputs, відсутність theory/metadata/course changes. Окремого apply або backup не створювали: database content не змінюється.

Цей додаток не перепризначає історичні counts/результати Practice Completion: наведені вище 393 тести не є новим follow-up coverage. Actual scoped commit/push і full SHA підтверджуються окремим handoff, без заяви наперед або само-посилального hash у звіті.
