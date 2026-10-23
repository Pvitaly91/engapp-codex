# M80 — Negative Forms / Double Negatives / Sentence Transformations

Статус: **локальне впровадження, браузерне приймання та незалежні фінальні аудити виконано; commit/push оформлюються окремо після staged review**. Сторінки реально оновлено на `http://gramlyze.loc`. Production не перевірявся й не змінювався. Історичні failures та чотири нові exact-source guards нижче наведено окремо; твердження «всі регресії green» немає.

## Шість цільових сторінок

| № | Сторінка | Page | Рівень нової власної практики | Continuing / нова практика | Point-level details |
| --- | --- | ---: | --- | --- | ---: |
| A | [Negative Forms](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/negative-forms) | 331 | A2 | 5 / 1 | 0 |
| B | [Double Negatives](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/double-negatives) | 332 | B1 | 5 / 1 | 1 |
| C | [Sentence Transformations — Tenses](http://gramlyze.loc/theory/sentence-transformations/tenses) | 152 | B1 | 6 / 1 | 1 |
| D | [Sentence Transformations — Modals](http://gramlyze.loc/theory/sentence-transformations/modals) | 148 | B1 | 6 / 1 | 1 |
| E | [Sentence Transformations — Passive](http://gramlyze.loc/theory/sentence-transformations/passive) | 149 | B1 | 6 / 1 | 1 |
| F | [Sentence Transformations — Reported Speech](http://gramlyze.loc/theory/sentence-transformations/reported-speech) | 151 | B1 | 6 / 1 | 1 |

У таблиці continuing не включає окремий UK subtitle. Загалом зберігаються 34 continuing навчальні/навігаційні блоки та шість UK subtitle, додаються шість native `practice-set`. Наведені рівні нової практики не замінюють чинні SQL-рівні старих блоків.

## Межі реалізації

- Канонічна в’юха та чинні native-компоненти; без нової верстки, CSS, JS, форматера чи grader.
- 36 нових власних graded tasks: по два завдання на форму, значення та повне речення на кожній сторінці. Дванадцять повних речень підтримують native manual/token шлях.
- Окремо — шість вбудованих sentence builders по п’ять тематичних питань: **30 existing selected / 30 reused / 0 new questions**. Вони не підміняють 36 власних завдань.
- П’ять окремих змістовних details; короткі пояснення, переклади, приклади та обов’язкові критерії залишаються visible basic.
- Нуль змін старих question/answer/option/pivot/hint/variant/saved-test/progress рядків. Нові питання та нові тематичні банки для M80 не потрібні.
- Зберігаються page identity, raw title/H1, category ancestry, UUID/order/column старих блоків, усі EN/PL рядки та старі timestamps.

Для C–F явно погоджено рівно вісім переходів legacy `box` у чинні `usage-panels` / `comparison-table`: по два блоки на сторінку. Старі зовнішні headings прибираються (`NULL`), оскільки заголовки визначає native content. Це 16 дозволених metadata fields, не глобальне послаблення перевірок. Фактичні SQL `NULL` у level цих восьми блоків зберігаються.

A/B мають інший початковий профіль: п’ять блоків, plain subtitle, без існуючої практики та без source navigation. Для C–F зберігаються вісім навігаційних labels/current flags; URL перевіряються окремо. Жодних вигаданих старих task IDs або прив’язок.

## Авторські зміни та канонічні карти

| Owner | Навчальний фокус і точне виправлення source |
| --- | --- |
| A — Negative Forms | Розділено lexical do-support, звичайне be, допоміжні дієслова та наказ `Don't be`. Допустиме `to not` не оголошено помилкою: реальне missing-to виправлення відділено від явно заданої навчальної вимоги `not to`; збережено scope обіцянки й no/not. |
| B — Double Negatives | Нейтральну standard model відділено від negative concord без оцінювання мовців. Задумане одне заперечення, логічна сфера дії, `not unhappy/impossible` та нормативне `neither…nor` не зведені до підрахунку негативних слів. |
| C — Tenses | Виправлено film chronology: раніший початок фільму тепер задано прямо. Тривання навчання, завершеність, fixed clock/date та reference point визначено до трансформації; маркер сам собою не диктує час і не додає невідомий факт. |
| D — Modals | `could` + `and we did` прямо підтверджує досягнутий результат; сама можливість цього не доводить. Збережено modal force: порада відомому адресату, заборона, відсутність потреби, минулий обов’язок та дедукція не взаємозамінні. |
| E — Passive | Збережено ролі виконавця/одержувача, потрібний named agent, час, процес/результат, модальність, заперечення та question force. Зміна інформаційного фокусу не дозволяє втратити необхідний факт; пропуск агента має явно заданий контекст. |
| F — Reported Speech | Відомі speaker/addressee/reporter і frame часу/місця визначають займенники та deictics. Розмежовано request/command, scope `not`, контекстно допустимий backshift; переказ слів не перетворено на незалежно підтверджений факт. |

Versioned навчальні джерела: [frozen author master](../content/m80-negatives-transformations.v1.json), [український текст](../content/m80-negatives-transformations.v1.0.0.uk.md), [editorial/source ledger](../content/m80-negatives-transformations.editorial.v1.json), [component/source map](../content/m80-negatives-transformations.components.v1.json), [task map](../content/m80-negatives-transformations.tasks.v1.json), [native bindings](../content/m80-native-bindings.v1.json) і [sentence-builder contract](../content/theory-sentence-builders/m80-negatives-transformations.v1.json). Карти явно пов’язують retained source functions, авторські уточнення, native slots, власні tasks і окремий existing-question widget; вони не є новим дизайном чи необмеженим paraphrase grader.

Exact owner identities у native bindings: для A/B namespace `Database\Seeders\Page_V3\QuestionsNegations\` з класами `QuestionsNegationsNegativeFormsTheorySeeder` / `QuestionsNegationsDoubleNegativesTheorySeeder`; для C–F namespace `Database\Seeders\Page_V3\SentenceTransformations\` з класами `SentenceTransformationsTensesTheorySeeder`, `SentenceTransformationsModalsTheorySeeder`, `SentenceTransformationsPassiveTheorySeeder`, `SentenceTransformationsReportedSpeechTheorySeeder`. Збережено відповідні Page IDs із першої таблиці, ancestry, continuing UUIDs, order/column та фактичні SQL level vectors: **A і B — `[A1-B1, A1-B1, A2-B2, A2-B2, A1-B2]`; кожен C–F — `[B1, NULL, NULL, B1, B1, NULL]`**. Лише нова практика має A2 для A та B1 для B–F.

Source-access межа за frozen editorial ledger: основні пояснення British Council прочитані напряму — [Questions and negatives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/questions-negatives), [Past perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-perfect), [Present perfect simple and continuous](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/present-perfect-simple-continuous), [Past ability](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-ability), [Passives](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/passives), [Reported questions](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-questions). Для Cambridge ledger окремо фіксує indexed editorial-body access та direct HTTP 403 або direct-not-claimed; indexed прочитання не назване успішним direct fetch. Named editorial replies відділено від learner comments; видавничі вправи не копіювалися. Main-агент також напряму прочитав [Google people-first helpful content guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content): це редакційний орієнтир, не гарантія індексації/позицій. Авторський і незалежний мовний review виконували агенти; human review не заявляється.

## Fresh BEFORE

Базовий accepted commit: `b56f29df4b3409edc1ad6cfb34b7fedd901e893d` (M79). M80 не використовує M79 BEFORE як власний baseline.

| Незалежна перевірка | Фактичний BEFORE результат |
| --- | --- |
| Source inventory | 3 150 унікальних tracked paths × окремі served ROOT / WT profiles; raw SHA, LF projection та EOL counts |
| Visual sources | 326; окремі ROOT/WT відмінності збережено, не вирівняно |
| Historical frozen sources | 1 877; лише шість явно цільових definitions дозволені до зміни |
| Historical test paths | 903; включно з raw-byte tests і старими fixtures, без їх переписування |
| Target HTTP | 12/12 theory/course GET → 200 |
| SQL | 46 повних row fingerprints до/після HTTP однакові; detailed schema capture охоплює 11 таблиць, не 46 |
| UK / non-UK | 40 continuing UK rows / 56 незмінних EN/PL rows |
| Old consumers | 48/48 GET → 200; 12 persisted saved tests, 864 original questions, шість course DOM boundaries |
| Dynamic page mixed | Шість native mixed SSR/JSON, по 84 питання; перевірено обидва exact banks для кожної сторінки |
| Sentence-builder registry | 169 targets / 0 failures / 0 writes |

Безпечні provenance pins незалежних приватних receipt-файлів:

- Source BEFORE: `30159ae3600430cf23673307e01ac37836e47a0556d4b22652c482156d1cbf2a`.
- Source/SQL/HTTP BEFORE: `75a9e58003f9364c45e4e3c2696aab7c0b8e5d5073a2bb2acefe54fb198dbefb`.
- Old consumers BEFORE: `bed6e6b56dfef3c6c9b880688dc9076dbd5d5970d0c38f01e337a8ef18299cd0`.
- Registry BEFORE: `31c537a05a7aecca2a55fca54816c3151e0efa9e1ae8cf42c66ab9bfe721c287`.

Raw SQL, runtime identity, nonce/proof bytes, backups та приватні діагностичні файли не включаються до versioned звіту.

## Тематичні банки та native surface audit

Для кожного owner збережено primary Polyglot та відповідний Standard V3 bank. У primary є по 12 existing type-4 питань потрібного рівня; Standard V3 не містить eligible type-4 для цієї прив’язки. Відбір п’яти є finite та exact-bound до старих snapshots; він не змінює глобальний форматер.

Виключені з локальної п’ятірки, але не виправлялися в старих банках:

- `negf-poly-a2-11`, `dbln-poly-b1-04`: незмінений native surface дає `No,` усередині потрібного звичайного речення.
- `stmod-poly-b1-04`: shared option `Food` має неприродну велику літеру посеред речення.
- `strsp-poly-b1-08`, `strsp-poly-b1-11`: shared option `Where` лишається з великої літери посеред reported question.
- `dbln-poly-b1-12`: shared option `Anyone` має medial capital.
- `stten-poly-b1-04`: shared option `When` має medial capital.
- Додатково не відібрано canonical key із повтореним `today`, неприродне поєднання `never/again/today` та перший політ без достатнього reference context.

Усі 30 відібраних речень і видимі українські умови перевірено як finite author projection з незміненими native correct tokens. Це перевірка джерел/фабрики, не заміна фактичного browser acceptance.

## Fresh isolated BEFORE: вісім suite lists

Ранер незмінений. Кожен запуск: `testing`, SQLite `:memory:`, array cache/session, CLI OPcache вимкнений, working `.env` не завантажений. У кожному запуску 675 protected paths залишилися незмінними.

| Suite | Methods | Assertions | Failures | Errors | Skips | Warnings | Deprecations | PHPUnit exit |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| M78 | 41 | 22 119 | 4 | 0 | 0 | 0 | 1 | 1 |
| M75 | 33 | 9 522 | 1 | 0 | 0 | 0 | 1 | 1 |
| M76 | 46 | 10 575 | 0 | 0 | 0 | 0 | 1 | 0 |
| M77 | 30 | 22 051 | 1 | 0 | 0 | 0 | 1 | 1 |
| Generic | 30 | 14 693 | 0 | 0 | 0 | 1 | 1 | 1 |
| Interaction | 134 | 1 588 | 8 | 1 | 0 | 0 | 1 | 2 |
| Wide | 136 | 27 215 | 15 | 0 | 0 | 0 | 1 | 1 |
| M79 — п’ять класів | 28 | 13 636 | 0 | 0 | 0 | 0 | 1 | 0 |

Разом: **478 method executions / 429 unique methods / 49 overlapping executions**. Не можна складати ці suite counts і називати їх 478 унікальними тестами.

Fresh M80 BEFORE порівняно з accepted M79 AFTER: **0 added methods / 0 missing methods / 0 changed methods**; assertions та повні failure/error messages збігаються. Це не твердження, що AFTER M80 уже пройшов. Зокрема, M79 був green у свіжому BEFORE: новий AFTER lineage failure не можна перейменувати на «стару помилку» лише за назвою класу.

## Нові M80 тести: після source mirror, до apply

| Перевірка | Поточний результат |
| --- | --- |
| `M80NegativesTransformationsPackageTest` + `M80NativePracticeTest` | 5 methods / 4 682 assertions / 0 failures/errors; isolated WT frozen package + native factory |
| `M80SemanticFixturesTest` | 5 methods / 594 assertions / 0 failures/errors; незалежні semantic fixtures |
| `M80OwnedSharedSourceDeltaTest` | 5 methods / PASS для actual ROOT та WT; окремий WT-запуск додатково перевіряє served ROOT profile |
| `M80ContentPatchTest` — перші чотири methods | PASS: atomic six-owner apply/no-op, first/last update/insert rollback, foreign identity/metadata/bank, stale body/progress/plan та backup collision |
| `M80ContentPatchTest` — combined pre-mirror run | 5 methods / 424 assertions / 1 error: останній CLI method не знайшов нову command у ще не mirrored ROOT; це не повний PASS |
| Той самий CLI method в окремому fresh isolated process | 1 method / 17 assertions / PASS, одна чинна PDO deprecation; working DB не використовувалась |

Після ROOT mirror виконано два повні isolated запуски всіх п’яти M80 classes:

| Source profile | Methods | Assertions | Failures / Errors / Skips | Deprecations | PHPUnit / runner exit |
| --- | ---: | ---: | --- | ---: | --- |
| ROOT | 20 | 21 079 | 0 / 0 / 0 | 1 | 0 / 0 |
| WT + explicit served ROOT lineage context | 20 | 37 453 | 0 / 0 / 0 | 1 | 0 / 0 |

У кожному — 675 protected files без змін, testing/SQLite `:memory:`/array/no working `.env`/OPcache off. Обидва combined writer runs тепер мають 5 methods / 432 assertions / PASS, включно з реальною новою CLI command. Єдина deprecation — чинна `PDO::MYSQL_ATTR_SSL_CA` у test configuration, не M80 runtime failure. Попередній pre-mirror command-discovery error лишається в історії, але не відтворився після фактичного mirror. Жоден historical assertion/fixture не послаблювався.

## Незалежний source lineage

Новий M80 ledger фіксує рівно **12 additions у восьми shared nonvisual files**, нуль validator additions/removals. Зворотне вилучення лише цих дванадцяти рядків має повернути raw та LF SHA до свіжого незалежного M80 BEFORE.

Окремі whole-file ROOT/WT profiles не змішуються. Foreign served controller зберігається; mixed EOL у served course manifest не нормалізується. Public preservation manifests охоплюють усі 326 visual paths та 2 810 immutable paths: 1 871 frozen без шести цільових definitions, 903 старі test paths і 36 інших захищених generic/routes/M79 paths. Вони враховують також captured absences.

Actual ROOT mirror восьми shared files перевірено новим lineage test: усі after raw hashes відповідають незалежному finite forecast, усі 12 additions reverse до точного BEFORE. Це source acceptance, не підтвердження apply чи браузерної роботи.

## Фактичне локальне застосування

Виконано fresh local-target proof → fresh preview → exact review → exclusive backup → transactional apply. Робоча гілка: `codex/seo-m80-negatives-transformations-standard-view`.

- **46 update operations**: шість `Page.text`, шість UK subtitle bodies і 34 continuing навчальні/навігаційні bodies. У восьми з цих самих rows додатково змінено тільки погоджені type/heading; це не 16 додаткових update operations.
- **6 insert operations**: лише нові UK native `practice-set`, по одному на кожного owner.
- **0 deletes / 0 question-family writes / 0 changes старих progress rows**.
- Незалежний final SQL/source audit відновив усі **46 повних BEFORE fingerprints**, повернувши лише точні погоджені поля та вилучивши лише шість повністю доведених нових practice rows.
- Усі 40 continuing UK rows, 56 EN/PL rows, старі IDs/UUIDs/levels/order/columns/timestamps і всі інші старі рядки збережено за цим exact-bound projection.
- Повторний apply з original preview і apply зі свіжого AFTER preview дали **no-op 0 updates / 0 inserts / 0 deletes**. Після видалення proof route повторних apply/no-op не запускали.
- Тимчасовий route видалено; foreign-inclusive routes file відновлено byte-for-byte, окремий anonymous loopback GET повернув **404**. Cleanup receipt SHA: `819872d339de250357a19f5f2415d6abc81fae83584bce3bd2f64ca9881912bd`.

Після завершення всіх браузерних взаємодій проведено ще один **SELECT-only** full 46-table audit, без apply/restore/no-op. Він підтвердив exact старі рядки та progress; `sql_after`, protected і normalized-before fingerprints збігаються з першим post-apply audit. Post-UI receipt SHA: `a0e1f6dec16bd65ed9032dd5cec963837a0a43061056735e1a6c5ce0c8a62f37`.

## AFTER HTTP / registry / source acceptance

| Перевірка | Результат |
| --- | --- |
| Перший AFTER old-consumer sweep | 48/48 GET 200; усі 12 saved tests, 864 original questions, шість course DOM та шість dynamic mixed SSR/JSON exact; 0 DB writes |
| Post-UI old-consumer sweep | Повторно 48/48 GET 200; exact 12 saved tests / 864 original questions / course+mixed payloads; all 46 fingerprints незмінні протягом GET, 0 DB writes |
| M80 sentence-builder audit | 6 targets / 0 failures / 0 writes; кожен payload має exact п’ять selected IDs/UUID order/level/type |
| Повний registry | 175 targets / 0 failures / 0 writes; усі попередні 169 semantic rows exact, виключено лише volatile whole-response `http_sha256` |
| Final HTTP/SSR v2 | 6/6 target GET 200, **740** checked rendered learning fields, exact widget conditions/native cased tokens, metadata, LearningResource description та старі anchors |
| Navigation destinations | 8/8 actual local GET 200, без redirects; source labels/current flags збережено |
| Inline HTML href destinations | Усі 4 unique destinations GET 200 + nonempty H1, без redirects: Must/Have to, Need/Needn't та A↔B |
| Final source comparison | 3 150 paths × ROOT/WT; тільки 14 changed paths у кожному context: 6 definitions + 8 shared files; 0 інших record changes |
| Старі source/test profiles | 326 visual paths, 903 historical test paths, 31 historical M79 runtime/diagnostic paths та всі captured absences exact |

Збережено всі початкові foreign visual differences: 167 raw ROOT/WT відмінностей і сім LF-content відмінностей. Вони не названі новими регресіями й не нормалізувалися.

Корисні посилання на фактично перевірені mixed-тести:

- [Negative Forms — mixed](http://gramlyze.loc/test/pytalni-rechennia-ta-zaperechennia/negative-forms)
- [Double Negatives — mixed](http://gramlyze.loc/test/pytalni-rechennia-ta-zaperechennia/double-negatives)
- [Sentence Transformations — Tenses mixed](http://gramlyze.loc/test/sentence-transformations/tenses)
- [Sentence Transformations — Modals mixed](http://gramlyze.loc/test/sentence-transformations/modals)
- [Sentence Transformations — Passive mixed](http://gramlyze.loc/test/sentence-transformations/passive)
- [Sentence Transformations — Reported Speech mixed](http://gramlyze.loc/test/sentence-transformations/reported-speech)

Final evidence pins:

- Registry: `2f3c34f70baa1bc832f8651b1e1d4c5a9b857409a1e9ec04ff6965c8916269fc`.
- HTTP/SSR v2: `4a4b0fa61ae8ba76b8f7cac458eecbcab0e7ec622a400d7d7198aec12ac08f24`.
- Independent final source comparison: `8e40ebe2aa82d4802b292b300f726ab6291d04e3a24d6043b73c39ee24bb07d6`.
- Post-UI old consumers v2: `2d95f7ccb3385aedaa0682413b0d29f2c5ae05aec3fd6b84b873c2cfbfe51de8`.
- Inline href acceptance: `5a7ec47397e75b853d2b3f70ddd76cc3a84227a7ebf3b324530cf59fd4d40fd5`.

Цільові inline destinations: [Must / Have to](http://gramlyze.loc/theory/modal-verbs/must-have-to), [Need / Needn't / Don't have to](http://gramlyze.loc/theory/modal-verbs/need-neednt-dont-have-to) та дві сторінки A/B із першої таблиці. Формальні package/bank-target validation пройшли в ROOT і WT: 6 owners / 6 bank targets без application bootstrap/DB; lint усіх 23 нових PHP files — PASS.

### Точна межа visibility audit

Незмінений native `practice-set` **не рендерить generic `data.intro`**. Шість таких M80 fields залишилися в frozen source як надлишкова загальна інструкція; вони явно записані в HTTP v2 receipt як **`unsupported/not_rendered`**, а не як SSR PASS. Усі необхідні критерії незалежно присутні у visible `select_intro` / `choice_intro` / `input_intro`, усіх шести labels/inputs.after та відповідних stimuli кожної сторінки. Views/engine/frozen payload не змінювалися заради діагностики.

Перший HTTP v1 receipt не використовується як final acceptance: старий M53 diagnostic припускав тільки table `row.cells`, тоді як чинний native adapter також підтримує `en/ua/note`. Новий M80-only diagnostic посилено для перевірки **кожної з трьох колонок обох форм**, nullable optional choice prompt та fail-closed PHP warnings/notices. Повторний v2 пройшов без цих warnings і перевірив 740, а не 704 fields. Історичний primitive не змінено.

## Actual browser acceptance

Проведено в реальному in-app browser на всіх шести локальних сторінках, не у fixture/preview. Browser receipt SHA: `5f07965e23fe593c617cad93af7c5cab07946f6cbcdc496bb8f04805ec123312`.

- **36 required states**: 1440×1000, 390×844, 320×844 × light/dark × шість сторінок. Fonts loaded, footer reachable; learning content не має viewport overflow. Широкі таблиці прокручуються у власному контейнері, без CSS змін.
- **48 required full-task/mode/size cases**: обидва повні inputs × manual/token × 1440/390 × шість сторінок. Ще **14** accepted full-sentence aliases перевірено окремо.
- **60 option instances**: усі варіанти двох form і двох meaning tasks на кожній сторінці; scores/visible values/feedback звірено.
- Empty/whitespace → 0; wrong → edit → correct; повторний Check стабільний; Reset, змішане ручне введення/токени та відновлення токенів після видалення перевірено.
- **30 settled widget sentences**, усі шість widgets доведено до **5/5** з реальною видимою відповіддю, не лише натисканням останнього токена.
- Усі п’ять details спочатку закриті; Enter відкриває/закриває власний detail, deep link + reload відкриває потрібну ціль; текст прочитано.
- Додаткові E/passive 1280px states і чотири full-mode cases не зараховано до required 36/48.
- Відновлено початкові 1280×720 і light theme; zoom не змінювали. Global storage/session clear не виконували; чіпали лише нові M80 page-specific widget states.

Після фінальної LF-корекції виконано browser supplement: **18 fresh-empty group Checks (6×3) → 0/2**, ще три повні rejection cases A (включно з `to not`, коли видима умова прямо вимагає навчальний порядок `not to`, без оголошення split infinitive загалом неграматичним). Повторне реальне reload F зберегло три групи практики й один widget 5/5. Ці додаткові випадки не збільшують required 36/48 counts. Supplement receipt SHA: `8619cb3eab2522e965a3214ae15d0d28f21e5d9a56439a85a1cc13e9e86b4f59`; основний browser receipt не переписувався.

Print, no-JS та повний WCAG audit не виконувалися й не заявляються.

Додатковий 320px DOM-аудит відділив навчальну частину від декоративного shell: поза viewport лишаються лише порожні animated `span`, без навчального тексту. Shell client/scroll widths для A–F: 305/322, 305/320, 305/321, 305/327, 305/327, 305/330; це не твердження про нульовий overflow всієї сторінки. Навчальні блоки не виходять за viewport; таблиці мають власний scroll. Дизайн/CSS не змінювалися; початкові 1280×720/light відновлено.

## Ті самі вісім AFTER suite lists

| Suite | Methods | Assertions | Failures | Errors | Skips | Warnings | Deprecations | PHPUnit exit |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| M78 | 41 | 22 119 | 4 | 0 | 0 | 0 | 1 | 1 |
| M75 | 33 | 9 522 | 1 | 0 | 0 | 0 | 1 | 1 |
| M76 | 46 | 10 575 | 0 | 0 | 0 | 0 | 1 | 0 |
| M77 | 30 | 22 051 | 1 | 0 | 0 | 0 | 1 | 1 |
| Generic | 30 | 14 735 | 0 | 0 | 0 | 1 | 1 | 1 |
| Interaction | 134 | 1 588 | 8 | 1 | 0 | 0 | 1 | 2 |
| Wide | 136 | 27 221 | 15 | 0 | 0 | 0 | 1 | 1 |
| M79 — п’ять класів | 28 | 7 915 | **4 нові exact-source** | 0 | 0 | 0 | 1 | 1 |

Знову **478 executions / 429 unique methods / 49 overlaps**; 0 added/missing methods. У кожному запуску test file bytes і всі 675 protected paths залишилися незмінними. Fresh BEFORE→AFTER comparison SHA: `a327a4a45c80a1dacaa9c13360b396b23ffddcac71d51ff1b70988d58fae8bee`.

Без змін повних method/messages/assertions: M78, M75, M76 та Interaction. Наявні failures/errors цих наборів не «виправляли» послабленням guards.

### Чотири нові M79 exact-source guard failures — не historical

У свіжому M80 BEFORE M79 був green. В actual AFTER чотири methods незміненого `M79OwnedSharedSourceDeltaTest` зупиняються на точному старому controller profile gate:

1. `test_twelve_new_registration_lines_reverse_to_exact_independently_captured_predecessors`
2. `test_theory_caller_guards_m77_variable_owner_and_m78_retained_identity_guards_stay_nonvisual`
3. `test_all_326_visual_source_profiles_and_every_shift_label_binding_are_unchanged`
4. `test_historical_sources_raw_byte_tests_and_captured_absences_are_not_rewritten`

Точне повідомлення в усіх чотирьох: `Actual complete controller bytes must match exactly one known ROOT/WT profile. Failed asserting that actual size 0 matches expected size 1.` Це **expected 1 matching old profile → actual 0**, а не доказ зміни visual файлів.

| Controller profile | Accepted M79 / fresh M80 BEFORE raw SHA | M80 actual AFTER raw SHA |
| --- | --- | --- |
| WT | `36f50dd099f531009dcb94da76992527ba8d5dd3341f3b9beb9153e7315a7470` | `a744d9efdf37cd07c96d86d66efc79a43ea0e24d7490e644c70749539196faa0` |
| ROOT, foreign-inclusive | `e5fc33251b082401405134e475925db756875d8cf143e0c8cfd9f0e3961d281d` | `1da05f5a9ba29958b7597928af48f9bf9d144cbd5935d22a9602eb4f4d924a8a` |

Незалежний counterfactual зроблено **тільки у приватних копіях**: той самий old M79 test і fixture залишено byte-exact; із восьми private shared copies знято лише 12 M80 additions, причому resulting raw/LF SHA звірено з незалежним M80 BEFORE. Actual ROOT/WT не змінювалися. Незмінений old M79 source test тоді дав **5 methods / 5 893 assertions / PASS** — точно той самий assertion count, що у fresh BEFORE. Усі 675 protected paths незмінні.

Цей доказ локалізує чотири failures до старої exact-current-source межі; він не підміняє actual AFTER зеленим звітом і не є rollback робочого коду, SQL або browser behavior. Решта 24 M79 methods пройшла. Новий M80 source guard окремо доводить нову exact lineage, caller guards, visual/test/source preservation.

Counterfactual runner receipt SHA: `1d1add0a07f82a7c7777f608cc92c7782af1ad638b1d7ebfc1a99a5d2ce3bc2a`.

### Інші точні AFTER deltas

- `TheorySentenceBuilderAcceptanceTest::test_dynamic_catalogue_requires_independent_contract_and_effective_connection_for_every_target`: **1186→1228 assertions**, PASS→PASS; шість нових contract targets.
- `M74BankRevisionTest::test_registry_preserves_all_old_contracts_and_discovers_future_owners_without_a_145_cap`: **175→181 assertions**, PASS→PASS; шість нових registry targets.
- Чотири вже failing raw-source methods зберегли failure status, але змінили actual hash у message. Їх не названо byte-identical unchanged messages:

| Method | Незмінений expected SHA | Actual BEFORE → AFTER SHA |
| --- | --- | --- |
| `M77OwnedSharedSourceDeltaTest::test_twelve_finite_registrations_and_one_cardinality_hunk_reverse_to_complete_accepted_m76` | `2494ae1f5df3b9850ab733a4c54ec730ca1a95f1615f41471558a11f5189c44d` | `db5055dc25e79a76961f4317bfa8b154a430b8844ac2c7f80a749706f1555d38` → `0e4ede573c7be6019f7c1393febf9c597dada88238c1bf7481953a7c00ca0b7d` |
| `M73SharedLineageTest::test_reversing_only_twelve_m73_hunks_recovers_all_eight_accepted_m72_sources_and_preserves_null_guards` | `9c83148ed813c3d635a10c4ed956184d3063fc6137b5304d33d37c5010655d1f` | `db5055dc25e79a76961f4317bfa8b154a430b8844ac2c7f80a749706f1555d38` → `0e4ede573c7be6019f7c1393febf9c597dada88238c1bf7481953a7c00ca0b7d` |
| `M74OwnedSharedSourceDeltaTest::test_only_twelve_dispatch_hunks_and_one_finite_plain_subtitle_hunk_reverse_to_the_full_accepted_m73_sources` | `6b369c9f3ff53023de9aae0fea8cf9329df479c9bd68d5c37ad0438e80944ef3` | `db5055dc25e79a76961f4317bfa8b154a430b8844ac2c7f80a749706f1555d38` → `0e4ede573c7be6019f7c1393febf9c597dada88238c1bf7481953a7c00ca0b7d` |
| `M74OwnedSharedSourceDeltaTest::test_standalone_callbacks_keep_object_guards_and_exact_controller_crlf_while_page_controller_stays_unchanged` | `989daf62a0f6b5ccca77d07cb092e4667bc2cb99a65586ead6af3ea93e302fa4` | `e5fc33251b082401405134e475925db756875d8cf143e0c8cfd9f0e3961d281d` → `1da05f5a9ba29958b7597928af48f9bf9d144cbd5935d22a9602eb4f4d924a8a` |

Cause звірено з новим M80 ledger: це рівно погоджені additions у відповідних complete shared source profiles, не довільні EOL/guard/formatter edits. Historical tests і fixtures не переписувалися.

## Остаточний raw-EOL delivery gate

На фінальній перевірці встановлено важливу різницю: перші actual six active definitions у ROOT/WT мали CRLF, тоді як вже frozen `active_definitions` checksum pins задавали LF. Попередні package validation перевіряли semantic projection, а перший source audit фіксував фактичні raw bytes; їх не слід називати доказом збігу з intended frozen LF pins.

Перед будь-яким записом для всіх 12 context/path pairs доведено: **заміна тільки CRLF→LF дає точно вже frozen SHA і byte length**, без зміни JSON values. Механічно виправлено лише ці шість definitions у ROOT та WT; frozen artifacts, навчальні дані, DB, вісім shared files, їхні EOL та foreign profiles не змінювалися.

Незалежний фінальний gate повторно побудував старі CRLF bytes із actual frozen LF bytes і звірив їх із попереднім незалежним AFTER raw SHA/length. Новий capture знову містить 3 150 paths × context; від початкового BEFORE лишаються рівно 14 approved changed paths/context. Від попереднього AFTER змінилися тільки 12 raw definition profiles; усі інші записи, зокрема 326 visual / 903 old tests / routes / shared EOL, exact. Усі actual active definitions тепер збігаються з intended frozen **raw LF SHA + lengths**.

- Earlier CRLF-inclusive source capture: `313f4e7ff17bcab5ba2b2295ae41a755a3923570979b1aa5d9db1a10c0ed4c07`.
- Final LF source capture: `6907572c75d6fefe7e37957a6e45d5db69278d60f8808355c437302eeb4eb4f4`.
- Independent final source/EOL reconciliation: `5078ec4078d4fd8530a9e2a57ff5a3a78f09fe75c2399aca27ad4d458745f699`.

Original preview, backup та source-sync v1 receipt не редагувалися заднім числом: вони чесно фіксують raw source profiles на момент apply. Final reconciliation дозволяє лише ці шість перевірених source-pin transitions для **read-only аудиту**, не дає нової apply/restore/no-op authority. Після форматування ще один незалежний SELECT-only scan отримав усі 46 повних fingerprints, **byte-for-byte рівні post-UI v2**, включно з усіма старими rows/timestamps/progress. Receipt: `f7438db541c52fc399543c283c6ac9337f7db979265581fe6aa901754eae47c5`.

Останній незалежний SELECT-only scan виконано вже **після browser supplement**: знову всі 46 повних fingerprints byte-for-byte рівні post-UI v2; old rows/timestamps/progress exact, 0 DB writes, без apply/no-op/restore. Final receipt SHA: `1c29b56ee9468035810bacd27aea53fbff55a1c8e026905e03c81bd5efd8662a`.

Після LF-корекції повторено всі п’ять M80 classes незміненим isolated runner:

| Profile | Methods / assertions | F / E / skips / warnings / deprecations | Exit | Protected paths changed |
| --- | --- | --- | --- | --- |
| ROOT | 20 / 21 067 | 0 / 0 / 0 / 0 / 1 | 0 | 0 із 675 |
| WT + served ROOT context | 20 / 37 453 | 0 / 0 / 0 / 0 / 1 | 0 | 0 із 675 |

ROOT має на 12 assertions менше попереднього 21 079 тільки через відсутність redundant explicit-self-root environment option: `contexts()` більше не виконує три додаткові self-root assertions у чотирьох methods. Жоден тест не послаблено. Вісім широких AFTER lists наведено вище до цієї newline-only корекції; після неї підтверджено exact JSON values, новий raw source capture, full 46-table equality та ці повторні M80 target suites.

Окремо додано **WT-only** `.gitattributes` append для нових M80 paths і exact six active definitions: 18 LF rules / 20 appended CRLF lines. Старий working CRLF prefix збережено raw byte-exact (`e3d13b4c6d93ddf5f7ef065dafc035602ba59c83bfe84ee9b945c56f29cba72e`); лише його LF projection порівнювали з Git base. Це delivery-only додатковий tracked path **поза** 3 150-path inventory, не прихований п’ятнадцятий source delta в тій capture. Effective `text/eol` перевірено для всіх 52 нових owned paths поза старими shared profiles; existing ROOT deletion `.gitattributes` не відновлювали й не змінювали.

## Delivery

Versioned scope — 61 явно обраний path; приватні receipts/proofs/backups, SQL snapshots, runtime caches та 15 сторонніх незавершених author drafts виключено. Commit/push виконуються main-агентом після staged review; цей звіт не стверджує, що delivery вже відбувся. Deployment, production `.com`/`.ub`, `main`, Apache/XAMPP/hosts не змінювалися.
