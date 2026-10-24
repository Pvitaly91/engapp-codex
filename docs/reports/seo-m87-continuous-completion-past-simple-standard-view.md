# M87 — Continuous completion та Past Simple у standard view

Статус: локальні контент, guarded apply, repeated no-op, AFTER SSR, live UI шести сторінок і registry217 завершені. Цільовий M87 profile — **PASS**. Загальна historical strict-регресія — **FAIL**, із точним BEFORE/AFTER порівнянням нижче; old-consumer raw-body comparison також не оголошено PASS. Production не перевірявся й не змінювався.

Гілка: `codex/seo-m87-continuous-completion-past-simple-standard-view`. Accepted base: `443032b85caf8a27fffb372bb81bc2f0b023314b`. Scope — локальний `http://gramlyze.loc`; production/deploy не виконувались.

## Сторінки й кінцеве навчальне охоплення

Усі шість URL мали HTTP 200 у свіжому незалежному BEFORE та в actual AFTER SSR. SSR не підмінює реального UI-приймання.

| Пара | Сторінка | Own graded | Demo, не graded | Full inputs | Окремий widget |
| --- | --- | ---: | ---: | ---: | ---: |
| A | [Present Continuous: Questions and Short Answers](http://gramlyze.loc/theory/tenses/present-continuous/present-continuous-questions) | 6 | 0 | 2 | 5 |
| B | [Present Continuous: Time Expressions](http://gramlyze.loc/theory/tenses/present-continuous/present-continuous-time-expressions) | 6 | 0 | 2 | 5 |
| C | [Past Simple: Forms and Use](http://gramlyze.loc/theory/tenses/past-simple/past-simple-forms) | 11 | 1 | 3 | 5 |
| D | [Past Simple: Negatives](http://gramlyze.loc/theory/tenses/past-simple/past-simple-negatives) | 11 | 1 | 3 | 5 |
| E | [Past Simple: Questions and Short Answers](http://gramlyze.loc/theory/tenses/past-simple/past-simple-questions) | 11 | 1 | 3 | 5 |
| F | [Past Simple: Time Expressions](http://gramlyze.loc/theory/tenses/past-simple/past-simple-time-expressions) | 12 | 1 | 3 | 5 |
| Разом | 6 owners | 57 | 4 | 16 | 30 |

Збережено 36 old graded controls C–F і чотири окремі демонстрації; додано 21 graded control. A/B мають по 2 form + 2 meaning + 2 full-input завдання; C/D/E — по 2 нові meaning; F — 3 окремі meaning про момент/тривалість, календар/контекст місяця та інший часовий орієнтир.

### Identity, levels і OLD → AFTER карта

Owner identity має префікс `Database\\Seeders\\Page_V3\\Tenses\\`. Локаль усіх шести — uk, parent tenses/category32. Нижче наведені саме підтверджені bindings, не пошук за схожим заголовком.

| Пара | Owner suffix | Page/category | Display/focus | Practice source index OLD → AFTER | Own map |
| --- | --- | --- | --- | --- | --- |
| A | `PresentContinuous\\PresentContinuousQuestionsTheorySeeder` | 172/28 | A1–A2 / A1 | absent → 8 | 0 → 6 new |
| B | `PresentContinuous\\PresentContinuousTimeExpressionsTheorySeeder` | 173/28 | A1–A2 / A2 | absent → 7 | 0 → 6 new |
| C | `PastSimple\\PastSimpleFormsTheorySeeder` | 338/61 | A1–A2 / A1 | 6 → 6 | 9 retained/corrected + 2 meaning |
| D | `PastSimple\\PastSimpleNegativesTheorySeeder` | 339/61 | A1–A2 / A1 | 6 → 6 | 9 retained/corrected + 2 meaning |
| E | `PastSimple\\PastSimpleQuestionsTheorySeeder` | 340/61 | A1–A2 / A1 | 6 → 6 | 9 retained/corrected + 2 meaning |
| F | `PastSimple\\PastSimpleTimeExpressionsTheorySeeder` | 341/61 | A1–A2 / A2 | 6 → 6 | 9 retained/corrected + 3 meaning |

У кожного owner sources0–5 зберігають свої ролі hero/forms-grid/usage-panels/comparison-table/mistakes-grid/summary-list. A/B sources6 та A7 з навігацією залишаються на місцях; C–F practice6 і footer7, а також E secondary8 збережені. Жоден old component/task не перенесено на іншого owner.

Для кожного C–F old selects0–2 → selects0–2; inputs0–2 → inputs0–2; demo rephrase0 → ungraded demo0; graded rephrase1–3 → rephrase1–3. Answers/aliases, існуючі IDs або їхня відсутність, source indices та group/index mappings збережено; явно виправлено український задум, контекст, дозволений порядок і здійсненність token banks. Нові meaning мають choices0–1; F також choices2 за дозволеним одним додатковим тематичним пунктом. Детальний контроль кожного retained/corrected/new item — у [finite task ledger](../content/m87-continuous-completion-past-simple.tasks.v1.json), [component map](../content/m87-continuous-completion-past-simple.components.v1.json) і [coverage matrix](../content/m87-continuous-completion-past-simple.coverage.v1.json).

Technical practice level залишається NULL у всіх шести: display range і editorial focus не перетворені на exact CEFR filter. Pools72 кожен; фактичні selected levels A/C/D/E — A1×5, B — A1×4+A2, F — A1+A2×4. Повний [bank audit/settled answers](../content/m87-sentence-builder-bank-audit.v1.json) і [widget selection](../content/m87-widget-selection.v1.json) фіксують усі30 речень. Це не перевірка всіх рівнів або72 питань на owner.

Manual і натискання токенів — два режими одного input, не два результати. Готові inflected tokens забезпечують здійсненність canonical речення, але їх натискання не доводить самостійної кон’югації або spelling. Незмінений normalizer не є строгою орфографічною перевіркою регістру, апострофа й кінцевої пунктуації. Coverage кінцевий, не full-course або CEFR certification.

## Навчальні зміни та незмінний дизайн

Збережено чинні native components, кольори, рамки, typography, renderer, formatter, grader і caller-owned theory межу. M87 додає навчальні дані та рівно 12 additive nonvisual registrations у восьми shared paths, не окремий дизайн.

- A/E відмежовують нейтральне питання від контекстного перепитування, who-підмет від іншого учасника, допоміжне дієслово від потрібного лексичного doing/do. Короткі відповіді мають мовця, факт або явну полярність; питання саме не підтверджує події.
- B оцінює форму й значення at the moment / this week, reference поточного періоду, можливі паузи, тимчасову повторюваність та задану позицію часу — не тільки am/is/are.
- F окремо оцінює ago/for, last week не як одну точку рівно сім днів тому, in May у заданому році та before/earlier від іншої встановленої точки. Навчальні дати сталі, не дата комп’ютера.
- C/D зберігають old answers/aliases і навчальні функції. Canonical форми та auxiliary реально присутні в перемішаних групах по 1–3 слова. Зміна полярності названа прямо.
- Source EN приклади та обидва варіанти таблиць збережені з українськими перекладами. Mistakes мають paired wrong/right EN/UA й видимі notes. Full inputs мають явні позиційні/лексичні критерії, зокрема F call/arrive/move.
- Потрібні ремарки, переклади, приклади й критерії лишаються visible basic; details визначаються семантично, не runtime word-count порогом.

Зміни внесені до canonical definitions та frozen author/native пакета. Усі 30 widget питань — existing type 4; display-clone умови не змінюють original SQL questions/answers/options/hints/variants. Нових питань немає. SQL filter залишається NULL, а target pool — 72 existing questions на owner; editorial focus не підмінює технічний фільтр.

Основні 24 footer URL уточнені в даних за фактичною tenses ancestry, зі збереженням labels/order/current і незмінним resolver. Secondary footer A/source7 та E/source8 збережені, але були відсутні в actual BEFORE DOM через чинну native footer-поведінку. Це pre-existing omission, не visible PASS.

Первинні мовні джерела включають [British Council Present Continuous](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/present-continuous) і [British Council Past Simple](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/past-simple). Cambridge indexed primary excerpts не названі direct-body читанням при direct403; publisher exercises не копіювались.

## Локальний apply та незалежні postconditions

Working BEFORE SHA: `c9b7cffb07a0440e5051a0b7c522eb016ce4cdcc80107edf6573e8780eb151c5`. Paired source BEFORE SHA: `4345c31ffe0233766e3edba90e5c1996f1a7d29ee23dee301fdb0a11306d42a5`.

Guarded apply підтверджений receipts:

- Reviewed logical digest: `1ddb3cd5d9e27d2d06999aa0b02a3f7c3c706c73fb1be8c1720e1313622a2b05`.
- Exclusive backup exact reviewed-preview bytes: raw SHA `df929ec805cb1f2afd97f4c5f33907f5289fcdfd7921961e4105d15a8a8e9515`. Це private guarded-plan backup, не публічний повний DB dump.
- 52 body updates: 48 native bodies + 4 subtitles. A/B subtitles byte-exact.
- Разом 54 distinct written rows:52 existing updates +2 inserts. Із54 continuing UK rows52 оновлені,2 subtitles незмінні; operations та кількість усіх retained rows рахуються окремо.
- Дві нові UK practice rows A/B: source8/7, sort9/8, left/NULL. Чотири C–F existing practices збережені source6/sort7 зі своїми UUID/metadata.
- Нуль Page.text, metadata, question-family updates/inserts/deletes.
- Два actual repeated plans: 0 updates / 0 inserts, однаковий digest `5af3b84f0c4f78b8ead948f5de1065ea8d9d4acc26e140c0d717fc4eb40d8355`.
- Proof route видалений, routes raw bytes exact restored; незалежний GET повернув404 без redirect. Cleanup SHA `60d77e97790a88f6eefb42dc3241006f897766c3189624765a7b4c0700ce1e46`.

Independent `final-readonly-v1.json` SHA `e6dd798acaf85fd8c3784714faf6b57fc999dd0b5969f354e2af2f75be343982` підтвердив 54 continuing UK, 108 unchanged EN/PL, 2 new practices та exact BEFORE reconstruction всіх46 SQL tables. Old rows/timestamps/progress/non-target data залишились exact.

Незалежний post-UI `final-readonly-v2.json`, SHA `130ad4cfff92d94241f0af36ceac2258f4c541eeb3781d694c1481eeee2c95cb`, повторно підтвердив усі46 AFTER fingerprints, exact new-practice rows та source lineage відносно v1. Old rows/timestamps і BEFORE reconstruction лишилися exact;326 visual та2,935 prior frozen files незмінні, DB writes0, HTTP requests0. Це фактична звірка зазначених SQL/source полів, не твердження про байтову рівність двох receipts і не UI PASS.

V2 виконаний до останніх12 supplemental UI-перевірок. Заключний `final-readonly-v3.json` після них — **PASS**, SHA `f3929c46bd84c474104f2c9eef37448e71f4c35b473f22b258ece976ed07ada1`: усі46 SQL fingerprints,54 continuing UK,108 unchanged EN/PL,2 new practices, old rows/timestamps та raw/LF source lineage exact до v2; DB writes0. Після цього браузерних дій, apply або нових змін навчальних джерел не було.

Новий M87-only `m87-postconditions.php` після old-test run отримав finite policy точного55-rule suffix для delivery attributes; поточний SHA `3fd402919604dceff9c2a3d739d7c9538b37563a743300121eff0e60451611c8`. Historical guards/tests не переписувались. Stable disclosure у вихідному summary збережено від v1; post-UI еквівалентність підтверджують actual fingerprint/row/lineage checks, а не лише незмінний текст summary.

Окремо exact: 14 original banks,912 questions,14 saved tests,912 memberships і повні old relation families. Це current M87 capture, не M86 membership hash. Source audit перевірив8,386 paired paths,326 visual paths,25 foreign untracked,16 raw/LF shared reversals і28 bank/course context checks. ROOT/WT profiles та EOL не приводились один до одного. Цей SELECT/source audit не є runtime проходженням курсу або браузерним прийманням.

## Actual AFTER SSR — completed, не browser acceptance

Один bounded receipt `live-pages-v1.json`, SHA `9b45d66aabc10320dede1ebb9bda71c736f50eaf3da3ab94074309ef71a5a951`:15 unique anonymous loopback GET без cookies/proxy/redirects, SQL access=false, content/runtime/route writes0.

На шести M87 сторінках звірено760 SSR learning fields: A117, B114, C132, D128, E133, F136. Збережені H1/title/breadcrumb/canonical/robots, усі118 попередніх anchors без втрат і LearningResource identity; нові description точно відповідають незалежній prospective projection. Перевірені native semantic accents, таблиці/переклади,18 owned notes та72 ordered paired EN/UA mistake fields.

Practice SSR:16 selects,13 meaning choices,16 full inputs,16 rephrase entries включно з4 неоцінюваними demos —57 graded controls і12 scored rephrases. Exact serialized task keys/answers/aliases/plain feedback, власні visible labels/criteria, ordered options, manual-input attributes/sync bindings і token-bank templates звірені з frozen data.12 generic `selects.prompt` C–F класифіковані лише як exact BEFORE-bound nonrendered metadata; це не visible acceptance і не глобальний виняток. Окремі6 widgets містять30 existing type4 питань із точними IDs/order/native tokens/options/multiplicity. Check/Reset, кліки та client feedback не названі перевіреними цим SSR receipt.

Усі24 primary-footer items і18 noncurrent href/label звірені; прямі GET охопили8 footer destinations, [Stative Verbs](http://gramlyze.loc/theory/tenses/stative-verbs) і [пов’язану категорію](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia). Secondary A/source7 та E/source8 залишились source-exact, але невидимими, як у незалежному BEFORE: receipt має status `accepted-with-pre-existing-secondary-navigation-omission`, visible acceptance=false. Details0; keyboard — N/A, не PASS.

Сім окремих SSR controls повернули200: [PPC Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms), M86 [Present Simple Forms](http://gramlyze.loc/theory/tenses/present-simple/present-simple-forms), [Negatives](http://gramlyze.loc/theory/tenses/present-simple/present-simple-negatives), [Questions](http://gramlyze.loc/theory/tenses/present-simple/present-simple-questions), [Adverbs of Frequency](http://gramlyze.loc/theory/tenses/present-simple/present-simple-adverbs-of-frequency), [Present Continuous Forms](http://gramlyze.loc/theory/tenses/present-continuous/present-continuous-forms) і [Negatives](http://gramlyze.loc/theory/tenses/present-continuous/present-continuous-negatives). Для кожного звірено exact H1, один native widget, п’ять type4 питань і prior selected IDs/order за свіжим registry BEFORE. Це GET/H1/widget-identity controls, не перевірка всіх їхніх teaching fields, не whole-HTML equality і не UI interaction.

## Повний registry AFTER — PASS у read-only CLI/SSR межі

Один unchanged `theory:audit-sentence-builders --target=gramlyze.loc` без source filter, cap або retry:217 targets = exact fresh211 BEFORE +6 M87, failed0, writes0, exit0, stderr0. Попередній порядок і всі211 semantic records точні; усі6 нових records відповідають frozen selections, NULL-filter pool72 і п’яти тематичним питанням на сторінку.

Погоджене порівняння виключає лише `http_sha256`; усі211 whole-HTTP hashes змінилися й збережені окремо. Причину цих raw-HTML differences не встановлено; вони не названі доведено випадковими. Інші поля не нормалізувалися. Registry не доводить браузерної видимості/взаємодії та не є окремим SQL rescan.

Повний stdout SHA `e15c176c9cd69bb10ece381adabf635a5c184a0d30edbb9c0763175f698b9009`; receipt SHA `012a0854c1e1dd8d2fa71a7405727e103bf55922b6e9add346ee53c14291489c`.

## Одноразова strict BEFORE/AFTER регресія — FAIL

Один all-17 AFTER run через unchanged wrapper `--phase after --iteration v1 --workers 2`; жодних retry-to-green. Старі tests/assertions/parser/runner не редагувались. Кожний run: testing,SQLite :memory:,array cache/session,CLI opcache off,working .env not loaded. Усі17 runs зберегли675 protected fingerprints і old test bytes.

| Профіль | Tests | BEFORE assertions | AFTER assertions | AFTER F/E | Exit |
| --- | ---: | ---: | ---: | ---: | ---: |
| m78 | 41 | 22119 | 22119 | 4/0 | 1 |
| m75 | 33 | 9522 | 9522 | 1/0 | 1 |
| m76 | 46 | 10575 | 10575 | 0/0 | 0 |
| m77 | 30 | 22051 | 22051 | 1/0 | 1 |
| generic | 30 | 14987 | 15029 | 0/0 | 1 |
| interaction | 134 | 1588 | 1588 | 8/1 | 2 |
| wide | 136 | 27257 | 27263 | 15/0 | 1 |
| m79 | 28 | 7915 | 7915 | 4/0 | 1 |
| m80 ROOT | 20 | 5919 | 5919 | 4/0 | 1 |
| m80 WT | 20 | 5931 | 5931 | 4/0 | 1 |
| m81 ROOT | 24 | 7373 | 7373 | 4/0 | 1 |
| m81 WT | 24 | 7385 | 7385 | 4/0 | 1 |
| m82 | 36 | 7631 | 7631 | 6/0 | 1 |
| m83 | 46 | 23790 | 23790 | 0/0 | 0 |
| m84 | 63 | 11180 | 11180 | 2/0 | 1 |
| m85 | 75 | 8729 | 8729 | 2/0 | 1 |
| m86 | 80 | 5547 | 5328 | 2/0 | 1 |
| Разом | 866 | 199499 | 199328 | 61/1 | FAIL |

866 executions /773 unique methods /93 overlap executions. BEFORE59F1E → AFTER61F1E,0skipped,1runner warning,17deprecation occurrences. Generic0F0E, але exit1 через `No tests found in class "Tests\Feature\SavedTestStepWrongAnswerTest".` — не PASS. PHP8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` збережена.

### Exact8 observed method differences

Нові2 failures, обидва зупинились у `contexts()` line56: `Actual foreign-inclusive controller must match exactly one independently reviewed AFTER profile.`

1. `Tests\Feature\M86SourceBoundaryTest::test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile` — assertions182→15; outcome SHA `0909df4445d954439e6ed587adf2e5a8ec6a2689273922db88094522c266a3a5`.
2. `Tests\Feature\M86SourceBoundaryTest::test_native_validator_builder_formatter_and_grading_engine_keep_their_independent_raw_bytes` — assertions67→15; outcome SHA `2d2b2dd25e74191842b2c61c3d39ade802b7d41c354da565ded84b04805f2f98`.

Специфічні native assertions другого методу після цієї boundary не були досягнуті; вони не названі такими, що пройшли.

Чотири existing failures мають змінені literal bodies:

3. `Tests\Feature\M77OwnedSharedSourceDeltaTest::test_twelve_finite_registrations_and_one_cardinality_hunk_reverse_to_complete_accepted_m76`.
4. `Tests\Feature\M73SharedLineageTest::test_reversing_only_twelve_m73_hunks_recovers_all_eight_accepted_m72_sources_and_preserves_null_guards`.
5. `Tests\Feature\M74OwnedSharedSourceDeltaTest::test_only_twelve_dispatch_hunks_and_one_finite_plain_subtitle_hunk_reverse_to_the_full_accepted_m73_sources`.
6. `Tests\Feature\M74OwnedSharedSourceDeltaTest::test_standalone_callbacks_keep_object_guards_and_exact_controller_crlf_while_page_controller_stays_unchanged`.

Методи3–5 бачать actual TheoryContentSource SHA `254398a087aafe49a115b1dff5d6f2eab90228116eaa7b87d23fad1e6a47bafb` → `fb81352e7147fb50762a573d65cfcbc44167cea990da6f36985688d5fb641e7c`; метод6 — ROOT controller `76f79b5d17b68d9b422780b88aad86b2cdb150d85a6a6580af100af2f45b9cf9` → `063a04a33ad161161c5c222990c585a89ea9ab3e0391f17f34c203e0762a4848`. Старі expected hashes не переписані.

Два still-passing methods змінили assertions:

7. `Tests\Feature\TheorySentenceBuilderAcceptanceTest::test_dynamic_catalogue_requires_independent_contract_and_effective_connection_for_every_target`:1480→1522.
8. `Tests\Feature\M74BankRevisionTest::test_registry_preserves_all_old_contracts_and_discovers_future_owners_without_a_145_cap`:217→223.

Exact comparison:2newly nonpassing,4changed existing nonpassing,2changed still passing;0resolved/added/missing. Збережено3profile count changes і3literal issue-report-tail changes. Failure bodies,paths,EOL/messages не нормалізовані.

### Окрема non-PASS атрибуція до 12 owned additions

Лише після actual comparison незалежна read-only реконструкція перевірила current16 profiles. Reverse тільки12 M87 lines/context у8files повертає exact accepted M86 AFTER raw bytes; old M86 lines збережені.

Для controller reverse лише4 M87 additions повертає:

- ROOT `063a04a33ad161161c5c222990c585a89ea9ab3e0391f17f34c203e0762a4848` → `76f79b5d17b68d9b422780b88aad86b2cdb150d85a6a6580af100af2f45b9cf9`.
- WT `8662488b69306d0a0ad3f5b70525cd0185b6f1142c8178b2155bd5e2d22173d6` → `2f4f128fe2e9209d28dd437f86a37c7a367bc8bb603842d743d37c02ef85c7a6`.

Actual old M86 test context був ROOT; WT статична реконструкція не видається за виконану WT test branch. Це пояснення спостереженої boundary, **не waiver/PASS** і не дозвіл змінити historical guard expectations.

## Дві pre-existing route collisions /404

У fresh BEFORE requested SSR URL фактично оброблялись JSON route та повертали404:

- [Past Simple questions test](http://gramlyze.loc/test/past-simple/questions).
- [Present Continuous questions test](http://gramlyze.loc/test/present-continuous/questions).

Requested `test.show`/NewDesignTestController не дорівнювала actual `test.js.questions`/GrammarTestController@fetchSavedTestJsQuestions. Baseline мав strict requested-kind acceptance=false і strict HTTP200=false. Обидві відповіді404 та їхні повні entries збережені в AFTER; вони не стали accepted. Routes/standalone tests не ремонтувались у M87.

## Old-consumer AFTER — collection complete, strict FAIL збережений

Один GET кожного з54 endpoints через незмінені pinned collectors/comparator:22 literal entries точні,32 strict mismatches. У всіх32 відрізняється лише `body_sha256`:6 course HTML і26 test SSR HTML. Обидва наявні404 входять до збережених baseline exceptions і не названі PASS.

HTTP facts, status/kind/count/route metadata та всі інші entry fields точні для54/54. Це окрема класифікація, **не заміна strict FAIL на PASS**. Причину raw-HTML drift не встановлено; normalization/field exclusions/variants pinning/retries не застосовувались. CLI exit0 означає завершений збір, а не успішне literal порівняння.

14 banks,912 original questions,14 saved tests,912 memberships, relation families, source hashes і route declarations exact BEFORE. Усі46 SQL fingerprints до/після54 GET точні та прив’язані до independent final AFTER/normalized BEFORE; table exceptions немає. DB/content/source writes0; browser interaction не перевірявся цим collector.

Actual AFTER SHA `bd7be027efb25e5252abdb1531b039fdce7ed769cc727c28eef3363142782f1f`; окремий classification-summary SHA `74ca3f2f0a31f1bc138f1a880ad427f2648f335886e6d8e1b59b48db470de7c5`. Усі54 повні BEFORE/AFTER entries та strict mismatches залишаються у bound private receipts.

## Цільові автоматичні M87 перевірки

Actual ROOT isolated run:83 methods,5,951 assertions,0 failures,0 errors,0 skips, exit0. Вісім нових PHP test files: package14, content-patch8, local-guard8, builder-presentation9, bindings5, source-boundary3, native-practice1 та live-diagnostic-contract35. Останній JS diagnostic виконує незмінений native engine на57 controls,4 demos,16 inputs, aliases/options, score/reset і adversarial token sequences; це не UI.

SQLite :memory:, testing, array cache/session, CLI opcache off, working .env не завантажена.675 protected fingerprints,116 context entries, runner/profile/parser/comparator та Git HEADs незмінні під час run; HTTP0, working DB access=false. ROOT autoload із перевірками WT source bytes не названий повним WT application run. Новий read-only postscanner після цього уточнений лише для exact55 attributes suffix і реально виконаний final v2/v3; historical tests/guards не редагувались.

Перший M87 run мав83 methods/5,951 assertions/F1: у новому synthetic bindings-test лишилося очікування10 controls замість правильних11. Виправлений тільки цей новий test literal10→11, без зміни контенту/engine; failed receipt збережений. Другий run тих самих83 methods пройшов. Повтор не додає83 нові унікальні методи: old773 + new83 =856 unique; old866 + два target83 =1,032 method executions. Загальний historical FAIL не скасований.

Final target receipt SHA `5e982abffa87bb9cf3d027ec24c48cd9a268b3cf35b6a45fd1ca6d7f5f3f424b`; first failed receipt SHA `9bad570c8f3f5233bf20da6f47896405580c8fff30f395d9fe95881d817be2ec`.

## Реальне браузерне приймання

На всіх шести точних local URL виконані UI дії через штатні кнопки/поля, без hidden-state injection, zoom або змін механіки:

- 36 DOM viewport/theme states:6 owners ×1440×1000 /390×844 /320×844 ×light/dark. H1 та один widget точні; learning overflow у перевірених article/p/h1/h2/exercise елементах —0. Table-local scroll обліковано окремо; це не глобальний shell-overflow або повносторінкова сертифікація.
- 64 canonical input vectors:усі16 inputs ×manual/tokens ×desktop/mobile. Empty, whitespace, wrong→edit→correct, repeated Check, Reset, clear→token return і mixed manual/token rebuild перевірені.
- Додатково12 owner×width partial-input runs:0/N, повторна неправильна0/N, одна правильна1/N, усі правильніN/N; у кожного input видалений останній фрагмент знову робить відповідний токен доступним, після натискання складений результат правильний. При цьому suggestion listbox/datalist не з’являвся в DOM пробах.
- 70 option outcomes та66 literal accepted aliases перевірені з visible correct/wrong feedback; усі задані feedback literals точні. Серед aliases — Who is/Who's, outside/outdoors і повні/скорочені заперечення. Для всіх власних groups звірені full score, повторний Check без приросту й Reset;4 demos не мають graded input і не збільшують знаменник.
- Кожен із6 окремих widgets пройдено5/5:30 різних тематичних IDs і всі30 видимих settled sentences звірені з незалежним BEFORE-derived очікуванням, а не тільки green badge. A IDs20368/20369/20373/20375/20376; B20440/20441/20445/20446/20454; C20800/20801/20803/20804/20808; D20871/20872/20873/20880/20881; E20943/20944/20946/20952/20953; F21022/21030/21033/21035/21036. Фактичний UI порядок відрізнявся від source order; перевірена кожна з цієї п’ятірки. Це не пояснення причин raw-HTML hash drift.

Independent expected payload SHA `f9291dfc0bb54977df18cc432826582e7a5fbfcf9ed201fcc962835c1cfdfa1f`; actual final UI receipt SHA `93f056703f442605ce4e02841cb36f6253ccb14f65c95aaa9786948e1ba6e778`. Приватні screenshots/повні UI ledgers не комітяться.

Окремо візуально переглянуто41 збережений viewport screenshot, SHA receipt `942a612726d3f2e367222b6882283644f0a40813ce5cfb32efdf392ecfd0b180`. У видимих non-table текстах/заголовках/картках несподіваного обрізання справа не помічено. Три початкові кадри частково закривало mobile menu; вони збережені з цим обмеженням. Чотири додаткові closed-menu кадри D/E показують незакритий top/H1, але іншу scroll position, не доводять видимість раніше прихованих карток. П’ять table frames мають штатну внутрішню горизонтальну прокрутку. Raster dimensions відрізняються від CSS viewport; файли початкової серії з .png містять JPEG, що зафіксовано, не використано як вимір viewport.

Три locator race під час mobile-menu transitions усунуті свіжим DOM observation і visible/hidden waits; не зміною CSS/JS. Перед фінальним screenshot F був відновлений уже завершений стан П5/5/✓5; це не зараховано як друге проходження. Повернено початкові default1280×720 і dark theme; user tab не змінено.

Details0 за явним семантичним рішенням; keyboard/deep-link detail — N/A, не PASS. Print, no-JS, WCAG/contrast і full-page visual audit — not_checked. Старі standalone tests/courses не проходилися вручну; їхні SQL/source/static/keyed та HTTP facts збережені окремими перевірками, raw-body FAIL залишений нижче.

## Evidence та доставка

Приватні snapshots/backups/runtime identity/nonce/progress і сирі DB rows не публікуються. Нижче лише opaque receipt digests.

| Evidence | SHA256 |
| --- | --- |
| BEFORE method maps | `5b85dac48c1a2a62ef0eb79dd165b13440837ec15c7a0bb6800a02bb93f2ce9c` |
| BEFORE summary | `2bbcfeda004550e71ad51e3944d3e7ccc0e8db392ce5b6690c39aec373dc19b9` |
| All17 AFTER index | `22d4b76f293af17b9e28c7fff8a31af901feb7954bbdf1cbf1840b34bda270b7` |
| Strict comparison | `6c8d21bd02b80d5979fc525b609fa75b708cf8b6ef24a019935ea85d7b9fe142` |
| Post-observation attribution | `fa9c07ded76a79793019850d9cd04aa3e76e38772aa2cb60607b729a9a0f3c77` |
| Final SELECT/source audit | `e6dd798acaf85fd8c3784714faf6b57fc999dd0b5969f354e2af2f75be343982` |
| Actual AFTER SSR,15 GET | `9b45d66aabc10320dede1ebb9bda71c736f50eaf3da3ab94074309ef71a5a951` |
| Full registry217 stdout | `e15c176c9cd69bb10ece381adabf635a5c184a0d30edbb9c0763175f698b9009` |
| Registry comparison receipt | `012a0854c1e1dd8d2fa71a7405727e103bf55922b6e9add346ee53c14291489c` |
| Old-consumer54 actual AFTER, strict FAIL | `bd7be027efb25e5252abdb1531b039fdce7ed769cc727c28eef3363142782f1f` |
| Old-consumer classification, not a strict waiver | `74ca3f2f0a31f1bc138f1a880ad427f2648f335886e6d8e1b59b48db470de7c5` |
| Independent SELECT/source repeat v2, до supplemental12 UI | `130ad4cfff92d94241f0af36ceac2258f4c541eeb3781d694c1481eeee2c95cb` |
| Final post-UI repeat v3 | `f3929c46bd84c474104f2c9eef37448e71f4c35b473f22b258ece976ed07ada1` |
| Final target M87 tests | `5e982abffa87bb9cf3d027ec24c48cd9a268b3cf35b6a45fd1ca6d7f5f3f424b` |
| Actual browser interactions + supplemental12 | `93f056703f442605ce4e02841cb36f6253ccb14f65c95aaa9786948e1ba6e778` |
|41-image visual review, disclosed limits | `942a612726d3f2e367222b6882283644f0a40813ce5cfb32efdf392ecfd0b180` |

- **COMPLETED — M87 target automatic tests:**83 unique methods/5,951 assertions/F0E0S0/exit0; перша new-test fixture failure не стерта.
- **COMPLETED — actual AFTER SSR:** усі6 target pages200,760 learning fields,metadata/serialized practice/30widget questions та7 read-only reference controls; secondary omissions лишаються explicit false. Не UI PASS.
- **COMPLETED — live UI:**36 viewport/theme states,64 manual/token vectors,70 options,66 aliases,12 supplemental partial-input runs і6widgets5/5; visual/not_checked limits вище.
- **COMPLETED — registry:** global217 = exact211+6,0fail/0writes; raw HTTP hashes винесені окремо. Old-consumer54 collection completed, але strict22exact/32body-hash FAIL і2 baseline404 не перейменовані на PASS.
- **COMPLETED — read-only repeat v2:** усі46 SQL fingerprints,new-practice rows іsource lineage exact до first final; цей receipt передує12 додатковим UI-перевіркам.
- **COMPLETED — final repeat v3:** actual SQL/source parity після останніх12 supplemental UI-перевірок.
- Delivery scope:64 explicit paths (49 additions/15 modifications),23 frozen payloads,8 shared files/12 additive registrations,55 exact new/definition `-text` rules; historical attribute prefix і25 foreign untracked збережені. ROOT Git не використовується для delivery; ROOT і WT source profiles перевірені окремо.
- Staged whitespace policy: `git -c core.whitespace=cr-at-eol diff --cached --check`. Strict default check зберігає рівно4 warnings через CRLF у чотирьох нових controller lines; чужий controller не переформатовувався.
- Commit, normal push та independent remote SHA verification виконуються лише у вказану робочу гілку після finite staged audit. Full SHA commit із цим звітом і результат remote verification наводяться у супровідному фінальному повідомленні, щоб не створювати самопосилання на ще не існуючий commit.

Production/deploy не входить до завершення M87.
