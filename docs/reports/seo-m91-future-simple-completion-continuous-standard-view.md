# M91 — Future Simple completion / Future Continuous

Дата: 2026-10-26. Локальне впровадження шести UK-уроків виконано. Повний історичний regression suite **не зелений**; відмінності та межі приймання наведені нижче, без waiver або послаблення assertions.

Погоджена база: `03a72ee95374385995b29e00d7961ee396632fde` (M90). Робоча гілка: `codex/seo-m91-future-simple-completion-continuous-standard-view`. Єдина HTTP/apply ціль — `http://gramlyze.loc`. Main, production, PR, merge/rebase, force push та deploy не використовувались.

## Сторінки, фактична identity та практика

| # | Урок | Практика OLD → AFTER | Full inputs |
|---|---|---|---:|
| A | [Future Simple: Questions and Short Answers](http://gramlyze.loc/theory/maibutni-formy/future-simple/future-simple-questions) | 4 retained +2 new meaning | 2 |
| B | [Future Simple: Time Expressions](http://gramlyze.loc/theory/maibutni-formy/future-simple/future-simple-time-expressions) | 4 retained +2 new meaning | 2 |
| C | [Future Continuous: Forms and Use](http://gramlyze.loc/theory/maibutni-formy/future-continuous/future-continuous-forms) | 0 →6 new | 2 |
| D | [Future Continuous: Negatives](http://gramlyze.loc/theory/maibutni-formy/future-continuous/future-continuous-negatives) | 0 →6 new | 2 |
| E | [Future Continuous: Questions and Short Answers](http://gramlyze.loc/theory/maibutni-formy/future-continuous/future-continuous-questions) | 0 →6 new | 2 |
| F | [Future Continuous: Time Expressions](http://gramlyze.loc/theory/maibutni-formy/future-continuous/future-continuous-time-expressions) | 0 →6 new | 2 |

8retained +28new =36graded controls:12form,12meaning,12full inputs. У28new входять8form+12meaning+8full inputs; це не лише28meaning. На кожній сторінці окремо5наявних type4 questions, разом30; вони не заміняються власними inputs. 0ungraded demos.

| Owner | Page / category | Display / editorial focus | SQL practice level | Practice row |
| --- | --- | --- | --- | --- |
| A | 344 / 62 | A1–A2 / A1 | NULL | retained 10376 |
| B | 345 / 62 | A1–A2 / A1 | NULL | retained 10400 |
| C | 54 / 11 | B1 / B1 | NULL | inserted 12805 |
| D | 55 / 11 | B1 / B1 | NULL | inserted 12806 |
| E | 56 / 11 | B1 / B1 | NULL | inserted 12807 |
| F | 57 / 11 | B1 / B1 | NULL | inserted 12808 |

A/B мають owner prefix `Database\Seeders\Page_V3\FutureForms\FutureSimple\`; C–F — `Database\Seeders\Page_V3\FutureForms\FutureContinuous\`. Точні назви seeder, URL, UUID, ancestry та bindings — у [native bindings](../content/m91-native-bindings.v1.json). Для всіх ancestry — `maibutni-formy` → відповідна Future Simple/Continuous category. Немає підміни NULL фільтра hero-рівнем або фокусом.

## OLD → AFTER та редакційні рішення

| Сторінки | Попередні компоненти | Додано |
|---|---|---|
| A/B | Усі7slot/type/column/order збережено; практика source5/sort6 з чинною UUID | Лише2meaning controls до кожної чинної практики |
| C–F | Усі7slot/type/column/order збережено, включно з footer source6 | По1practice-set source7/sort8/left/NULL, без вигаданих старих tasks |

Разом42continuing native components +4appended practices =46components;6subtitle positions окремо. Фізичні SQL levels лишаються NULL; editorial focus A1 для A/B і B1 для C–F не підміняє фільтр. Жодного примусового додавання нового типу компонента до сторінки, де його не було.

У A/B старі2selects+2inputs зберігають порядок, ключі, канонічні відповіді та відсутність старих id/source_index. Нові supported accepted/feedback поля не називаються виправленням зламаного старого runtime. Збережені4full canonicals: Will you come tomorrow?; When will they arrive?; I'll call you tomorrow.; It will start in an hour. У token-bank даних виправлено лише групування/регістр/пунктуацію для складання цих точних старих ключів; engine не змінено.

- A: Уточнено різницю між who-підметом і питанням з окремим підметом; заборона стосується зайвого допоміжного do/does, а не смислового do в What will you do?. Короткі відповіді спираються на видимі питання, факт і мовця; позитивне ’ll наприкінці найкоротшої відповіді не змішується з коректним They’ll arrive. Для прохання показано природну відповідь Sure, а не один універсальний діалог. Усі початкові англійські моделі й старі ключі збережено; ua-поля тепер містять переклади, а пояснення — підтримувані notes/descriptions. Старі token-stimuli виправлено даними, щоб регістр і пунктуація складали точні старі канонічні питання.

- B: Tomorrow/next/soon/later/one day та in + період пояснено як часові орієнтири, не автоматичний вибір will. In + період у відповідних прикладах означає відлік до початку/події, а не універсальне правило для кожного дієслова. Задано явну навчальну дату для next week і точний now для відліку; soon/later/one day не отримали вигаданих хвилин чи гарантованої дати. Українські переклади повні, коментарі винесені в note. Збережено обидва source порядки часу в теорії; окремі inputs явно просять час наприкінці. It має референт webinar; call/phone і start/begin та повні/скорочені will оголошені точними ручними aliases.

- C: Форма описує процес у майбутній перспективі, також очікувану/заплановану чи тимчасову діяльність; план не оголошено несумісним із Continuous, а здійснення плану — гарантованим. Політ не перетворено на завершений приліт, написання — на завершення/надсилання; фоновий процес не мусить зупинятися й не стає автоматично причиною. All day не вимагає відсутності звичайних перерв. Розрізнено значення have/think; will be ready залишається правильною іншою конструкцією. Write/lie/sit збережено з точними -ing уточненнями, без універсального подвоєння кожної приголосної. Додано нову власну практику замість вигадування старих завдань.

- D: Will not / won’t be + V-ing не означає автоматичну відмову, неможливість чи грубість; нейтральну позицію not задано явно, без заборони всіх контрастних порядків. У контексті роботи 9–12 not ... all day заперечує весь день, а не будь-яку роботу. Відсутність користування авто не є дозволом; початкове so you can take it збережено як окремо висловлений дозвіл. Майбутнє заперечення не стало never. Won’t be late — правильна інша модель; have lunch має діяльнісне значення. Збережено англійські моделі, додано повні переклади, парні виправлення з note і власні form/meaning/full-sentence controls.

- E: Абсолютну source-заборону Yes, I will be прибрано: cached первинний BBC-приклад її не підтримує. Власна вправа перевіряє явно названу найкоротшу відповідь, не заперечує всі довші можливості. Для нейтрального прямого питання інвертується will, не вся група will be; who-підмет не отримує додаткового he/they. Інші розмовні echo-порядки не оголошено неграматичними взагалі. Питання про план користування кімнатою/офісом не надає дозволу й не гарантує тону. No на staying long може заперечувати довгий візит, не сам візит. Повні Q/A збережено англійською та перекладено цілими діалогами.

- F: Розділено часову точку, відносний орієнтир і період: later tonight не є тривалістю, next week at this time не означає весь тиждень. All afternoon передано як другу половину дня, не весь день. This time tomorrow у meaning-control має фіксовані дату й годину, без залежності від годинника пристрою. While означає перекриття процесів, не обов’язково спільні початок/кінець чи причину. Правило теперішньої форми в звичайній підрядній часу when розмежовано з прямим When will ...? та embedded питанням про невідомий час. At 8 tomorrow саме по собі не забороняє інші майбутні форми; потрібний процесний погляд задано явно.

Повні попозиційні карти: [42 continuing + 4 appended components](../content/m91-future-simple-completion-continuous.components.v1.json), [36 tasks](../content/m91-future-simple-completion-continuous.tasks.v1.json), [editorial rationale](../content/m91-future-simple-completion-continuous.editorial.v1.json), [31 coverage rows](../content/m91-future-simple-completion-continuous.coverage.v1.json), [читабельна UK редакція](../content/m91-future-simple-completion-continuous.v1.0.0.uk.md).

## Покриття та aliases

Покриття finite:31змістовний рядок карти враховує всі36own controls і30widgets, але не означає повного курсу, усіх CEFR-умінь або перевірки кожного нюансу. Два режими manual/token одного input — це одне завдання. Готовий -ing токен не є самостійним написанням дієслова; граматична нормалізація регістру/пунктуації не є strict spelling assessment. Meaning перевіряється окремими12контекстними choices, а не автоматично приписується кожній production-вправі.

52явно оголошені accepted entries у всіх36controls:24MC/form keys і28full-sentence варіантів у12inputs. Оголошені call/phone,start/begin,full/contracted will/won’t та BrE/AmE travelling/traveling застосовуються тільки там, де це явно задано відповідним авторським завданням; це не необмежене розпізнавання всіх синонімів.

Усі6details[] порожні після явних змістовних рішень: короткі межі, переклади, нотатки й контексти потрібні одразу у basic. Нуль — результат semantic review, не runtime-квота.

Групи токенів1–3слова перемішані й відтворюють канонічні відповіді; альтернативні ручні aliases не зобов’язані одночасно бути токенами канонічного банку. Повний finite ledger з36tasks/31coverage rows/12input aliassets — у frozen tasks/editorial/coverage artifacts. Непокриті nuance залишені basic-only, а не видані за assessed.

## Existing-bank widgets і фактичні settled відповіді

### A. Future Simple: Questions and Short Answers

Банк: `Database\Seeders\V3\Polyglot\PolyglotFutureSimpleQuestionsAllLevelsLessonSeeder`; actual filter NULL, pool72.

- 53890 — Will you help at home?
- 53891 — Will she visit grandma?
- 53893 — Will we study English?
- 53895 — Will my sister watch the film?
- 53897 — When will the team wait outside?

### B. Future Simple: Time Expressions

Банк: `Database\Seeders\V3\Polyglot\PolyglotFutureSimpleTimeExpressionsAllLevelsLessonSeeder`; actual filter NULL, pool72.

- 54035 — She will visit her grandmother next week.
- 54036 — He will play football soon.
- 54037 — We will study English later.
- 54041 — The team will wait outside tomorrow afternoon.
- 54043 — Researchers will travel by train in two days.

### C. Future Continuous: Forms and Use

Банк: `Database\Seeders\V3\Polyglot\PolyglotFutureContinuousFormsAllLevelsLessonSeeder`; actual filter NULL, pool72.

- 27231 — Tomorrow at eight I will be sitting on the train.
- 27233 — This time tomorrow we will be flying to Berlin.
- 27238 — All morning tomorrow I will be training the new staff.
- 27240 — This time next week Julia will be relaxing by the sea.
- 27242 — During the conference I will be taking notes.

### D. Future Continuous: Negatives

Банк: `Database\Seeders\V3\Polyglot\PolyglotFutureContinuousNegativesAllLevelsLessonSeeder`; actual filter NULL, pool72.

- 27304 — This time tomorrow she won't be answering emails.
- 27307 — At six I will not be calling you because I will be driving.
- 27308 — Later tonight he will not be using the car.
- 27310 — At ten tomorrow Marta won't be having lunch.
- 27311 — Next week at this time I will not be living in Kyiv.

### E. Future Continuous: Questions and Short Answers

Банк: `Database\Seeders\V3\Polyglot\PolyglotFutureContinuousQuestionsAllLevelsLessonSeeder`; actual filter NULL, pool72.

- 27375 — Will you be working at six tomorrow?
- 27377 — Where will she be staying next week?
- 27379 — Will he be driving then? Yes, he will.
- 27382 — Will you be using the office later?
- 27385 — Will we be meeting tomorrow? No, we won't.

### F. Future Continuous: Time Expressions

Банк: `Database\Seeders\V3\Polyglot\PolyglotFutureContinuousTimeExpressionsAllLevelsLessonSeeder`; actual filter NULL, pool72.

- 27447 — This time tomorrow I will be flying to Prague.
- 27448 — At six tomorrow we will be having dinner.
- 27450 — Next week at this time they will be presenting the results.
- 27454 — This time next month we will be living in Lisbon.
- 27458 — All afternoon tomorrow she will be taking calls.

Це exactselected models у UUID-order, не порядок випадкового показу BANK у браузері.30questions наявні,0newquestions,402із432main-pool questions не вибрані. Theory-only condition clones не змінюють originalSQL question/answer/option/hint/variant. Native source replay обох файлових contexts пройдено окремо. У реальному браузері всі шість widgets завершені 5/5; кожне з наведених 30 settled речень після Check дослівно збіглося з frozen expected.

Фактичний shuffled порядок показу, не UUID-order:

| Owner | Observed IDs | Completion |
| --- | --- | --- |
| A | 53893, 53895, 53897, 53890, 53891 | 5/5 |
| B | 54036, 54035, 54043, 54037, 54041 | 5/5 |
| C | 27233, 27242, 27240, 27238, 27231 | 5/5 |
| D | 27308, 27310, 27311, 27304, 27307 | 5/5 |
| E | 27377, 27375, 27382, 27385, 27379 | 5/5 |
| F | 27450, 27448, 27447, 27454, 27458 | 5/5 |

Перевірені case, BANK labels, selected chips, повторювані I/will/be у 27307, Q/A пунктуація у 27379/27385, Next та фінальний стан. Порядок не зашитий у тестовий сценарій: кожне питання зіставлялось за фактично видимою умовою. Hints/verb_hints старих 30 models реально порожні; їх не очищали для PASS. M89 opt-in exact-bound complete-loaded-verb-hints-v1 збережено byte-exact; неповні/змінені/чужі hydration snapshots тестуються відмовою. [Bank audit](../content/m91-sentence-builder-bank-audit.v1.json) і [contract](../content/theory-sentence-builders/m91-future-simple-completion-continuous.v1.json) містять точні selections, а не нові SQL-питання.

## Guarded local apply і збереження даних

Свіжі незалежні source/SQL/schema/HTTP BEFORE зняті для M91, не запозичені з M90 AFTER. Physical Apache/vhost/MySQL і web/CLI identity підтверджені nonce proof. Тимчасовий route працював тільки для plain HTTP GET exact host/path і loopback, без cookies/authorization/forwarded/origin; повертав лише safe identity та nonce digest. Налаштування Apache/XAMPP/hosts/.env/ACL не змінювались.

Preview зіставлено з незалежним BEFORE та final author projection по кожному полю/ID/UUID/порядку. До запису створено exclusive private backup, byte-exact до preview. Apply транзакційний; штатні внутрішні postconditions успішні.

| Операція | Фактичний результат |
| --- | ---: |
| Continuing teaching bodies | 42 updates |
| Subtitle bodies (лише E/F) | 2 updates |
| Page.text (лише 56/57) | 2 updates |
| Усього update operations / distinct existing rows | 46 / 46 |
| New C–F practice-set | 4 inserts |
| Distinct changed rows total | 50 |
| Question-family writes / deletes | 0 / 0 |
| Repeated same apply | no-op, 0 updates / 0 inserts |

Максимальний новий body — 12,309 bytes; DDL для TEXT не застосовувався. Continuing 48 UK positions, їхні IDs/UUID/order/column/level та timestamps збережені; 96 EN/PL rows незмінні. Повний SELECT-only reconstruction усіх 46 таблиць відновив незалежні BEFORE fingerprints, віднімаючи тільки точні погоджені поля й чотири нові practices. 13 старих банків / 888 original questions / 13 saved groups з усіма options, hints, variants, memberships і timestamps точні. Нецільові рядки, progress та курси не записувались.

Route видалений після apply/no-op; `routes/api.php` повернутий до exact foreign-inclusive попередніх bytes. Окремий anonymous loopback GET видаленого nonce повернув 404 без redirect. Незалежний postcondition scanner запускався після cleanup, тому source audit не мав route-exception.

Після основної UI-матриці й усіх grading-взаємодій повторний незалежний reconstruction знову підтвердив усі46 AFTER fingerprints, source lineage, original rows і timestamps проти першого final receipt. Це окрема post-UI звірка, не повтор old-consumer GET до випадкової парності. Пізніші шість native table-scroll probes були лише read-only навігацією/прокруткою; viewport override наприкінці скинуто.

## Metadata й footer navigation

Усі шість H1, raw title, slug/category, breadcrumb, canonical, LearningResource name/headline/language та старі anchors збережені. Опис PageMetadata і LearningResource обчислений до apply з погоджених даних, потім зіставлений із шістьма actual HTML captures. У E/F синхронно змінені subtitle_html і plain Page.text; A/B plain NULL збережений, C/D explicit plain збережений. Global extractor, resolver та local noindex не редагувались.

26 footer href виправлені до дев’яти підтверджених full canonical destinations: короткі старі paths реально дали 404, повні мали 200. Labels/order/current flags збережені; Will vs Be Going To має власний перевірений owner. В actual HTML усі 26 chips точні. Шість окремих HEAD підтвердили старий `noindex, nofollow, noarchive`; додаткових target GET для metadata-проєкції не було. [Navigation review](../content/m91-navigation-review.v1.json).

Дві approved Page.text зміни також відобразилися у двох lesson-card teasers read-only Future Continuous category. Це явний downstream metadata ефект: category authored description/source не змінені. Main-region DOM цієї category відтворюється дослівно рівно двома цими замінами; не заявляється повна byte-парність цієї category.

## Реальні браузерні перевірки

Фактична матриця: 6 × (1440×1000, 390×844, 320×844) × light/dark = **36 states**. Viewport і theme прочитані з DOM; browser zoom не змінювався. Усі уроки переглянуті до footer; Manrope завантажений, broken images 0, один widget / три own groups / два full inputs на сторінку. Практика C–F відображається перед нижньою навігацією, хоча source practice appended після source footer. Point details — N/A: авторських «Докладніше» 0; два DOM details — mobile TOC і штатна widget hint, не приховані авторські секції.

Actual interactions:

- 48 nominal full-input cases: 24 manual +24 token, кожен з 12 inputs на desktop і 320 px.
- 32 додаткові manual alias probes: усі 16 noncanonical aliases у двох widths.
- 120 option cases: усі 60 displayed form/meaning options у двох widths; правильні/неправильні keys та видимий plain feedback.
- Empty/whitespace, wrong→edit→correct, recheck, reset, delete→return→rebuild та mixed manual/token для всіх 12 inputs у двох widths. Оцінки груп незалежні; після reset поля/score очищуються. Токени після видалення знову доступні.
- Шість separate widgets реально до 5/5, 30 settled visible sentences перевірені після Check. Global localStorage/progress не очищався; попередній M90 UI-прогрес не скидався.

Окремі смислові probes охопили попереднє will-question/факт, who-підмет, лексичне do, next week 9–15 березня за сталою датою, 10:00→10:10, webinar it/in an hour, політ/Мадрид/напрямок, process≠completion, not…all day за графіком 9–12, room/car plan≠permission, staying long≠відсутність візиту, while overlap та when-clause≠question.

### Межі layout acceptance

У 36 states перевірені h1/h2/h3/p/button/input без table: **0 виходів за bounds навчального main**. Це не глобальний overflow PASS: усі 24 narrow states мають document/shell scrollWidth більше clientWidth (390: приблизно390–406 проти375;320:320–338 проти305). Причину не приписано M91 без доказу; CSS не змінювався для маскування.

Таблиці мають власний `overflow-x:auto` контейнер: на вузьких widths ширина таблиці576 проти322/252 viewport, праві колонки не видимі одночасно. Окремо після матриці пройдено native right-scroll усіх6 таблиць у320dark: scrollLeft0→305 при max324, праві notes доступні. Це не перевірка кожної width/theme-комбінації, зворотного або keyboard scrolling. Довгі single-line input values частково видимі всередині поля; їхні повні feedback/settled результати перевірені окремо. Overlay/autocomplete, що закривав би learning у переглянутих фрагментах, не спостерігався. Faint dark A1 badge відмічений як візуальне спостереження, не контрастний аудит. Print, no-JS, WCAG/contrast certification — not_checked.

Під час автоматизації були невдалі scroll-key / stale-owner / apostrophe-selector probes. Сценарії скориговано за фактичним DOM без зміни source/design/engine, без reroll GET і без очищення прогресу. Це не були відмови приймання правильної навчальної відповіді.

## Registry та read-only consumers

Targeted audit: **6/6**, whole registry: **241/241**, failed0, child exits0, stderr0. Збережено всі 235 previous semantic rows, їхній відносний порядок і шість exact new owners. Population — fresh235 union six, не permanent cap241. До/між/після обох команд усі46 SQL fingerprints точні. CLI acceptance не підміняє actual UI.

Старі 51 consumers зібрані одним проходом після apply. **Strict complete entries:19/51 exact,32 FAIL-retained** (28 raw-body-only,4 raw+payload), не full consumer PASS. Усі51 HTML facts, route/kind/count/acceptance fields та шість course learning DOM точні; усі theory-help hashes точні. 47/51 complete keyed payloads точні. Чотири payload відмінності — Future Simple Time Expressions V3/mixed SSR та JSON: змінюються question strings і похідні reorder-поля; не ID/UUID/answers/options/hints/memberships. Усі129 changed instances мають обидва варіанти дослівно в captured unchanged canonical/variant rows. Це доказ membership, **не доказ випадковості чи session-причини**. Порівняння не нормалізоване, GET до збігу не повторювались.

Два старі requested-SSR `/test/future-simple/questions` та `/test/future-continuous/questions` залишаються JSON-route 404 і не прийняті як SSR. Дві точні time-expression named aliases збережені з окремим requested/actual route-name mismatch, без router fix. Перша AFTER collector спроба помилково використала failed BEFORE-v1 predecessor: відмовилась до GET-loop (0 GET); streams збережено. Виправлено тільки private collector filename/SHA до actual successful BEFORE-v3, потім один finite51 pass, без послаблення comparator.

Extra25: 25/25 HTTP200, SQL unchanged; **strict full entries0/25, raw bodies0/25, inline projections0/25 — не PASS**. Main DOM24/25; одна category відмінність — рівно approved E/F teasers вище. У18 wrapper projections відрізняються static_prefix/static_suffix та їхні derived hashes; самі serialized questions/IDs/answers/tokens залишаються точними. Усі25 non-target definitions та category authored descriptions збережені. Різні bookkeeping filenames/raw bodies теж лишені literal mismatch, а не виключені з comparator.

## Автоматичні тести

Нові M91: 8 PHP files, **55 unique methods**, ROOT та WT filepath/factory runs — **110 executions**, по **20,560 assertions,0F/0E/0S**, child/wrapper exits0. Обидва використовували ROOT application bootstrap; WT run не називається окремим WT application. Кожен мав одну PHP8.5 deprecation про `PDO::MYSQL_ATTR_SSL_CA` (25 triggered methods), не warning-free PASS. Source/input113 paths та всі675 protected files точні.

Isolated native CJS окремо:36 controls/12 full inputs/52 accepted entries/60 options,48 nominal mode cases і24 delete/24 mixed/24 rebuild probes у кожному context; це не browser coverage. Widget source replay30×2 також окремо. Нові negative cases захищають owner/keyset/order/metadata/NULL/foreign-bank/stale-body/partial-snapshot/alias/token feasibility/original-model mutation/невидимий критерій та неправильне settled речення при правильному score.

| Historical profile | Executions / unique / overlap | Assertions | F / E / S | Warnings / deprecations |
| --- | --- | ---: | --- | --- |
| Fresh BEFORE | 1136 /1043 /93 | 244250 | 70 /1 /0 | 1 /21 |
| AFTER run1 | 1136 /1043 /93 | 243762 | 74 /1 /0 | 1 /21 |
| AFTER run2 | 1136 /1043 /93 | 243762 | 74 /1 /0 | 1 /21 |

Це21 profiles у кожному run, не додаткові унікальні methods. Обидва collector exits0 лише за завершений збір; child exits:2×0,18×1,1×2. Обидва strict comparators exit1/FAIL. Усі1136 method records і21 issue tails у двох AFTER літерально однакові. Runtime preflight paths і один stderr різні та збережені без normalization. Усі42 isolated profiles: SQLite `:memory:`, cache/session array, CLI OPcache off, робоча `.env` не завантажена,675 protected fingerprints і старі test bytes незмінні.

Чотири **нові** FAIL у `M90SourceBoundaryTest`, а не історичні лише через назву класу:

1. `test_attributes_keep_root_absent_and_worktree_exact_prefix_or_explicit_reviewed_suffix`
2. `test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile`
3. `test_m89_complete_hint_snapshot_helper_is_reused_byte_exact_without_new_delta_authority`
4. `test_native_validator_formatter_and_grading_engine_keep_their_independent_raw_bytes`

Усі зупиняються в спільному `contexts():73` перед власними subchecks: `Actual foreign-inclusive controller must match exactly one independently reviewed AFTER profile. Failed asserting that actual size 0 matches expected size 1.` Причина byte-guard — додані чотири дозволені M91 controller callbacks. Незалежне raw/LF reverse лише12 M91 registration lines у8files×2 відновило exact M90 AFTER;16 окремих native byte checks теж точні. Це **не waiver**, не PASS цих subchecks і не редагування M90 fixture/assertion.

Ще4 existing FAIL messages змінені лише observed source SHA (`M77OwnedSharedSourceDeltaTest`, `M73SharedLineageTest`, два `M74OwnedSharedSourceDeltaTest`). Два still-passing methods мають +42/+6 assertions через six new owner discovery. Старий interaction error `SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` — SQLite відсутня `question_hints`, без зміни literal body. Generic profile process exit1 через `No tests found in class Tests\Feature\SavedTestStepWrongAnswerTest`, навіть без власних F/E. Глобальний regression PASS не заявляється, старі тести не послаблені.

## Source boundaries, докази та доставка

42попередні native component positions та6subtitle positions збережено; C-F отримують рівно4practice appends. Канонічний дизайн/кольори/рамки/типографіка, native JS formatter, grader, normalizer і validator не переписувалися. Спільні8файлів мають лише12точних additive package-registration рядків на контекст; caller-owned theory межі захищають курси, standalone/saved tests і theory-help. M89 complete-loaded-verb-hints-v1 opt-in використано байт-у-байт, без нового delta.

Усі13historical banks (13JSON+13outerPHP source profiles) збережено raw/EOL, включно зlegacy24-question bank. Native formatter/grader/validator та M89hint helper4866… незмінні. Незалежний audit f62cb580…:76/76target/new context hashes,16/16shared rawreverse,17,060non-target paired profiles,326visual paths і25foreign WTfiles exact;0errors/0unapprovednewpaths. WTattributes — точний старий prefix+39explicit text eol=lf entries;ROOTattributes absent. Це qualified source-only47paths, не приймання48threport чи Gitdelivery.

Мінімальна nonvisual реєстрація обмежена existing dispatch/projection seams. П’ять нових M91 PHP classes — package/presenter/guard/patch/CLI thin descriptors на чинних primitives; немає нового framework, Blade/CSS/JS. M90 повні36controls/12inputs/26footer entries та235registry bindings, попередні releases/maps, M85 note projection, M79 visible criteria, M77 cardinality, M76 labels/every/shift, PPC retry/identity та M89 hint helper збережені відповідними isolated/source/SQL доказами. Історичні звіти не переписувались.

Selected immutable evidence SHA-256 (receipts/screenshots/backup приватні й не входять у commit):

| Доказ | SHA-256 |
| --- | --- |
| Frozen master | d9bc3dfcb596932140436e3213ebc838b97743e7c6cce2c9930025267ab93041 |
| Source-sync | 76cd10ca421ca45a8a46b2bd87639bd9b82f78b5bbcc4d646193704e5dd365f6 |
| First independent all46 postconditions | 6a3b5b5cd788178c7bafbd7786fb3d3a9b6208581b5d70e8fede319f24c7c2b2 |
| Post-UI all46/source repeat | f8d6aeb599c40a383e6a6c0a83bb2ed8aed0b78e61c07c074a5889552f49fb23 |
| Browser36 /48 nominal /30 settled | 3fd274cd009839febe1d14d9a1b803a481061085b45c9d4df51405bec049094e |
| Browser evidence plus6 native table-scroll probes | 29870990e6498d10d257bf6f767087705434306b3e43f1dc2493af8307bd6aab |
| Registry union241 | 57c7a5d54e0355b841eaca97ca23b6b0d30ef964ff56ffb301e777417621af0a |
| Strict old consumers | 63b91a25259c0561d719063d46ba727627a2d7e0df4edbd36d5d53d3a846113c |
| Strict extra25 | e688a24fb3f5e7c88dd38e9ff5eedf10d370395193425055ebd4054a3e67d934 |
| Consumer delta corroboration | 4c461e6eacb5c74643d8f38ca3aa9b275d1afe9c34c412f891599c152011025b |
| Historical AFTER summary | b1cb72de90916966cc9f8b482ac30747801c10525a8c37be92f1e195b48be11c |

Delivery allowlist —39 explicit M91 data/code/test/report paths +8 shared registration files +WT `.gitattributes` =48paths. ROOT foreign dirty tree не staging/committing; WT foreign untracked файли не включаються. `git -c core.whitespace=cr-at-eol diff --check` використовується зі збереженням raw CRLF shared profiles; звичайний check позначає CR у чотирьох controller additions, а не нові trailing spaces. Index LF bytes, frozen hashes і staged allowlist перевіряються окремо. Поточна реалізація не є дозволом на deploy. Full delivery commit SHA та normal-push remote confirmation наводяться у фінальному повідомленні, без self-referential commit hash у цьому файлі.

## Редакційні першоджерела

- [British Council: Question forms](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/question-forms) — direct editorial body.
- [British Council: Short forms](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/short-forms) — direct editorial body.
- [British Council: Talking about the future](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/talking-about-future) — direct editorial body.
- [British Council: Future continuous and future perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/future-continuous-future-perfect) — direct editorial body.
- [British Council: Stative verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/stative-verbs) — direct editorial body.
- [BBC: Future continuous](https://www.bbc.co.uk/learningenglish/english/features/tenses_with_georgie/ep-240607) — cached primary editorial body; live origin robots-blocked.
- [Cambridge: When](https://dictionary.cambridge.org/grammar/british-grammar/when) — indexed primary editorial excerpt; no direct-body claim.
- [Cambridge: Wh-questions](https://dictionary.cambridge.org/grammar/british-grammar/questions-wh) — indexed primary editorial excerpt; no direct-body claim.

Повний frozen catalogue містить23унікальні URL, із точними access modes та межами. Заблоковані Cambridge ellipsis403/body-not-read не використовуються як мовний доказ. CD cambridge-spelling-indexed має неоднозначну catalogueURL /plurals при Spelling-scope/direct_attempt /spelling403; не представляти це як прямо прочитану Spelling-сторінку. Publisher exercises і learner comments не копіювалися.
