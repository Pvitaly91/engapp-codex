# M78 — phrasal verbs і базові питання

Локальний guarded apply завершено; SQL postconditions/no-op/cleanup, HTTP/navigation, registry 163/163, old consumers та actual main-browser matrix пройдено. Фактично перевірено 36 unique layout states і 72 full-input cases, усі 67 graded завдань та 6 widgets × 5 питань. Нові M78 tests пройшли; історичні baseline failures/warnings залишені видимими нижче. Доставка ще pending: commit/push SHA не вигадано, production не змінено.

База `fe61b3634af5d307cc32167a16e224c1d5fb1591`, гілка `codex/seo-m78-phrasal-question-basics-standard-view`. Єдина ціль HTTP/apply — [gramlyze.loc](http://gramlyze.loc). M77, M76 token labels, native-дизайн і formatter/grader/engine збережено. Production, main/PR merge та deploy не виконувались у цьому scope.

## Шість сторінок і фактичний стан

| Сторінка | Точна прив’язка | Авторська практика після змін | Apply / HTTP / widget payload | Actual main browser |
| --- | --- | --- | --- | --- |
| [Separable and Inseparable Phrasal Verbs](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/separable-and-inseparable-phrasal-verbs) | Page117/cat19; 6 continuing slots; нова практика B1 на source6 | 6 нових graded; 2 inputs, обидва full | PASS; 200; exact 5/5 | PASS: 6 states; 8 full-input cases; widget 5/5 |
| [Common Phrasal Verbs by Topic](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/common-phrasal-verbs-by-topic) | Page114/cat19; 6 slots; нова практика B1 на source6 | 6 нових graded; 2 inputs, обидва full | PASS; 200; exact 5/5 | PASS: 6 states; 8 full-input cases; widget 5/5 |
| [Question Forms](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/question-forms) | Page131/cat21; 13 slots; continuing practice NULL на source11 | 14 graded: 12 retained + 2 meaning; 1 retained demo; 4 retained full inputs | PASS; 200; exact 5/5 | PASS: 6 states; 16 full-input cases; widget 5/5 |
| [Yes / No Questions](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/yesno-questions-general-questions) | Page140/cat22; 12 slots; continuing practice A1 на source10 | 13 graded: 11 retained + 2 meaning; 1 demo; 4 retained full inputs | PASS; 200; exact 5/5 | PASS: 6 states; 16 full-input cases; widget 5/5 |
| [Wh-Questions](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/wh-questions-special-questions-who-what-where-when-why-how) | Page139/cat22; 14 slots; continuing practice A1 на source12 | 13 graded: 11 retained + 2 meaning; 1 demo; 4 retained full inputs | PASS; 200; exact 5/5 | PASS: 6 states; 16 full-input cases; widget 5/5 |
| [Answers to Questions](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/answers-to-questions-yes-i-do-no-i-dont) | Page133/cat22; 14 slots; continuing practice A1 на source12 | 15 graded: 11 retained/corrected + 2 meaning + 2 full; 1 demo; 6 inputs = 4 retained partial + 2 full | PASS; 200; exact 5/5 | PASS: 6 states; 8 full-input cases; widget 5/5 |

HTTP 5/5 — окрема звірка server payload та canonical tokens. Колонка Actual main browser спирається на фактичні main-browser дії й settled results, а не DOM/fixture replacement. Full-input cases рахуються як 18 inputs × manual/token × desktop/mobile320, не як повторна кількість завдань.

## Редактура та OLD → AFTER

Погоджена finite-редакція заморожена як 1.0.0. Повні відповідності доступні у [editorial review](../content/m78-phrasal-question-basics.editorial.v1.json), [task map](../content/m78-phrasal-question-basics.tasks.v1.json), [component/source map](../content/m78-phrasal-question-basics.components.v1.json), [master](../content/m78-phrasal-question-basics.v1.json) та [українському author master](../content/m78-phrasal-question-basics.v1.0.0.uk.md). Кожна карта адресує точний owner/source index, не загальну зміну шаблону.

| Урок / авторська частина | Збережено й уточнено |
| --- | --- |
| [A — separable](../content/m78-author-parts/separable.v1.json) | Конкретне значення та модель додатка; it/them між частинами цільового separable verb; обидва допустимі noun orders; long object як перевага, не заборона; flight take off відокремлено від take something off; wh-object не дублюється займенником. Таблиця та right-column correction/summary збережені. |
| [B — topical](../content/m78-author-parts/topical.v1.json) | Шість тематичних груп і корисні вихідні вирази; wake up/get up, транспортні та соціальні значення, find out/work out, модель доповнення; намір/поточна робота не підмінені досягнутим результатом. Рекомендації не оформлено як граматичні помилки. |
| [C — question forms](../content/m78-author-parts/question-forms.v1.json) | Усі 12 old graded positions і demo; звичайні нейтральні питання відділено від echo/emphasis; ролі учасників, запитувана інформація, допоміжні й базова форма. Шість карт-посилань мають точні вкладені topic URLs. |
| [D — yes/no](../content/m78-author-parts/yesno.v1.json) | Усі 11 old graded positions і demo; be/do/modals, час та підмет; повні/скорочені заперечення перевіряються за конкретними authored alternatives, без Cartesian-розгортання скорочень. |
| [E — wh](../content/m78-author-parts/wh.v1.json) | Усі 11 old graded positions і demo; who-subject проти who-object, why/how та where/when, учасники, час і напрямок. Чотири старі повні inputs залишились на тих самих позиціях і отримали token/manual режим, без нових дублікатів. |
| [F — answers](../content/m78-author-parts/answers.v1.json) | Усі 11 old graded functions і demo; 4 partial-answer gaps не видано за full sentences. Додано 2 meaning choices та 2 нові full inputs. Три rephrase keys явно виправлено з Q:/A: діалогів на саму відповідь. Позитивне коротке Yes, I am не прирівняно до кінцевого Yes, I'm; mustn't/needn't не змішано. |

Усього 67 graded = 45 retained/corrected + 22 new; 4 old demos обліковуються окремо. 22 input controls = 18 full-sentence + 4 retained partial-answer gaps. Старі ID/source_index або їхня відсутність збережені; нові завдання мають стабільні ID. Captured native identity guard також перевіряє retained demos, а не пропускає їх як виняток.

Усі навчальні умови, пояснення та переклади залишені у visible basic. Point-level details — 0, із явними семантичними рішеннями в author projection. Wh/Answers hint/explanation дослівно перенесені у штатне note, щоб текст не зник у correction component. Повні речення мають видиму українську умову, shuffled 1–3-word groups та ті самі native manual/token mechanics.

Шість редакцій independently read/reviewed main + agents; це не human review. Фактичний доступ до primary publisher sources і змістові рішення записані у frozen editorial ledger; приклади й завдання авторські, publisher exercises не копіювались.

Master SHA `4fa08e891f7a44538bb29801dc8f5287368232c9c3a7524252d575f19374fd67`; checksums SHA `8b1b57d6f42e60f8256fa0754981545d1257e972cf2a9412f3f932f66edf8864`. LF policy задана лише M78 paths та шести active definitions, без широкого renormalize.

Явний frozen-metadata erratum: top-level checksums.pre_hash_checks.exactly_one_final_lf=true є завеликим твердженням лише для author_inputs[6], `docs/content/m78-widget-selection.v1.json`. Цей файл уже був frozen з двома кінцевими LF (tail 7D0A0A), 39019 bytes, SHA `4b009bc23f878b27d6feee2ca6a8c919f892fe21c0f93f1842afdb6f32512e30`. Pin/hash/length правильні й не змінені; frozen bytes, manifest і pins не «виправлялись» після freeze. Інші 10 core artifacts + 6 definitions + 6 author parts проходять canonical single-final-LF policy.

## Source/bank scope та локальна БД

Fresh independent BEFORE: `before-v1.json`, SHA `327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422`: 12/12 target/course GET200; усі 46 таблиць незмінні під час GET; 326 visual paths та 2686 historical frozen files. ROOT/WT шість definitions були byte-exact до редактури. A/B не мали прихованої додаткової практики; чотири existing practices C–F збігалися зі source. Embedded Page_v2 у D–F не підмінює SQL owner: JsonPageSeeder бере top-level Page_V3 identity. Plain subtitles C–F та H1 збережено.

[Bank audit](../content/m78-sentence-builder-bank-audit.v1.json): 30 selected / 30 reused / 0 new; eligible pools 12/12/72/12/12/12, page-wide type4 originals 432. NULL у Question Forms — відсутність exact SQL level filter, не display range; вибрані п’ять лишаються A1. Умови — source-bound caller-owned clones. Answers legacy weren't має явну contracted-bank вимогу; повна форма граматична, але не заявлена widget alias. Для A manual noun-order alias підтримано; clicked alternative з up. має punctuation-only boundary і не видано за exact alternate-token PASS. Canonical clicked path exact.

M78 додає finite PHP dispatch і nonvisual retained-practice validation, не новий дизайн чи engine. Blade/CSS/JS/formatter/grader не змінювались; M76 own-string token-label fix та M77 variable-owner/empty-package guards збережені.

Фактично застосовано 77 updates на 77 distinct existing rows:

- 6 Page.text;
- 71 continuing body = 6 subtitles + 61 teaching + 4 existing practices;
- 16 type/heading metadata fields на 8 із тих самих rows, не ще 16 operations;
- 2 нові practice rows A/B; delete 0; question-family writes 0.

Незалежні postconditions відновили весь BEFORE для 46 таблиць, включно з усіма старими rows/timestamps. 71 continuing UK та 142 non-UK rows звірені точно; protected tables, категорії, question families, grader/progress не змінені. Результат не отримано повним reseed.

## Guarded apply, backup, no-op та cleanup

Source-sync-v2 SHA `8575b0927a8dab9573463af9bd48ccd570a32a6e35f20d88057a56815b2e734b`: 34 exact paths, ROOT/WT bytes та 326 visual / 2686 historical frozen files unchanged. Freshness SHA `bade6ff2900424262e060982d280650d75462c5ec8d722b0d775278527043d32`: 46 таблиць exact BEFORE та 11 captured schemas перед apply.

| Доказ | Фактичний результат / SHA256 |
| --- | --- |
| Fresh nonce web/CLI proof | 6-owner scope; runtime-proof digest `2cb69d6b1779cd8ba52f035d661ce0f6ac0be40730ae932245c588bace3a1ebf`; private proof file raw SHA `cc6680453e57e7f29c6be7c5eb2eb83b38701180de357115fe1ada2c4ab6ba6c`; fresh for apply, без renewal |
| Reviewed preview-v1 | state before; 77 updates / 2 inserts / 0 deletes; plan digest `95efd0c2eb414d08905e3031aa1b1dd0077d684594af53b940238f0269c0a413` |
| Exclusive backup-v1 | byte-exact reviewed preview; raw SHA обох `03c3cdb77b7253db63862ef6648ed144cbe4827c7f6cd85fc93d6942ace088fa` |
| Independent final-readonly-v1 | SHA `9d0837e9fdc59bb031130d1527bffdc3ee0f59a56887fc661f5897ae2c64e6c4`; 46 tables, 77/2 exact operations, normalized BEFORE/protected projections PASS; writes цим read-only audit 0 |
| Repeat final-readonly-v2 | SHA `599f84b117c1f0e2f72641188ddc42d24f98bcbba7c08458c11d1ee7300e93b4`; 46 tables, old rows/timestamps та BEFORE/protected projections exact, source-sync unchanged; DB/HTTP writes цим SQL-only gate 0 |
| Final repeat після browser recovery — final-readonly-v3 | SHA `e2d3249fdd9daba2b4dbc157c25ff6928d0146122bcd64a88ad1d55a128d629b`; 46 tables, ті самі 77/2 operations та 16 metadata fields, exact old rows/timestamps; sql_after byte-structure equal v2, BEFORE/protected/source pins PASS; SQL-only writes 0 |
| Repeat preview-v2 / no-op | state after; updates 0 / inserts 0; digest `8bb8e01d0a0bad944e2daca3ef452242c5daf015726ca06d4cf434edc9b7512e`; raw SHA `f0472aa814d2c8d99ee99415fa6068e441fdbf94e3d80e73fa3da204a5b88067` |
| Cleanup | raw receipt SHA `3bff2c2ec495d25977ae2e8c8f92b8473c9331600d0e3a7f2c8fe2121c5a265c`; exact route bytes restored; removed endpoint GET404, redirects 0; physical hashes unchanged; cleanup DB/route writes 0 |

`routes/api.php` SHA до/після restoration однаковий: `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Це не hash іншого, foreign-inclusive routes/web.php. Тимчасовим GET-only nonce route main керував окремим scoped кроком; proof/cleanup helper сам не писав routes. Nonce не є постійним маршрутом. Приватні proof/backup/raw HTML/runtime identity не входять у commit.

## HTTP/navigation та sentence-builder registry

`nav-metadata-v2.json` SHA `12d2668719e40eeae72e8bc91d27df26e61f1ffceb72cee95a48aa8019bb83b5`: PASS. Diagnostic ROOT=WT SHA `fc763ff2431b8eeb199d316d21a29de3f2f7aa16480cb38daac36f70c2038485`.

- 17/17 direct anonymous loopback GET200: 6 targets, 8 read-only theory neighbors, 3 categories.
- 42 navigation chips = 6 current nonanchors + 36 clickable noncurrent; ще 6 Question Forms linked formula cards. Усі 42 clickable destinations GET200.
- Native SSR exact: 67 graded + 4 demos; 22 inputs, 18 designated full; 6 widgets × 5 exact selected IDs/clone conditions/unchanged canonical tokens; details/fallback 0.
- H1/title/breadcrumb/canonical/robots, LearningResource identity та всі old anchors exact BEFORE. Descriptions exact prospective-metadata-v1, незалежно розрахованого до apply, SHA `de3794f44897ab6c5f08b736ba9aeed3d77fc55faf076b6068e1c5c590fca234`.
- All-46 fingerprints до/після resolver/HTTP однакові та збігаються з final.sql_after. Visual/frozen/source-sync pins незмінні; production requests і DB writes 0. Canonical .com — лише перевірений рядок, не мережевий запит.

Виправлений лише новий diagnostic expectation: category22 відкривається штатним одно-slug [category route](http://gramlyze.loc/theory/types-of-questions), тоді як сторінки мають повний вкладений categoryPath. Перший v1 attempt з вигаданим nested CATEGORY URL отримав 404 і не створив PASS receipt; це не дефект authored href і не зміна сайту. Вимогу GET200 не послаблювали.

Точковий sentence-builder audit `m78-sentence-builders-v1.json` SHA `5f8a1641d6a3bba79947defc19d92b20f3a80467f609df673a354dcb35ef3bd9`: 6 targets / 0 failed / 0 writes; actual CLI matcher + fresh local HTTP, не browser acceptance.

Broad registry AFTER: 163 targets / 0 failed / 0 writes, `registry-sentence-builders-v1.json` SHA `e948b7ebdebb587757ec8759d4498ee02358127b4520a25c3114e9cb90e17e6f`. BEFORE 157/157 SHA `7ceda52f9a91263d01d5167edabf579b97a6adf882db05cbd73834c56bed8b1c`. Read-only preservation receipt SHA `07e21a93d4e7486f6bf091b5fab52624d7730fa8dc91d92c2bcec1a6dbe95016`: усі 157 old rows exact за identity+url, 6 new M78, missing/changed0. Єдиний виключений row field — http_sha256; інші row fields exact, без іншої нормалізації. Top-level runtime/at не видаються за registry row comparison.

Old-consumers AFTER: 12 saved banks, 864 original questions, 48/48 local GET200. `after-old-consumers-v1.json` SHA `3c16520decc0aee9bfdfaf481135297e0e44d39d630dc8bef3f20d05c939831c`, BEFORE SHA `073f1da1354c6227146a920ff86997dcfccb35b25b1f9068da2aa290012dada3`. Усі 46 SQL tables unchanged під час HTTP; captured old question/answer/options/hints/variant/tag/theory-link/saved-bank families та protected consumers збережені. Повторний final SQL v2 підтвердив exact old rows/timestamps і source pins.

## Автоматичні тести

Один final combined guarded run, лише final frozen sources, seven absolute WT test paths, ROOT lineage profile. PHP 8.5.10 ZTS / PHPUnit 12.5.35; Node задано process-only. Жодних draft/factory overrides.

| Новий M78 файл | Унікальні методи | Assertions |
| --- | ---: | ---: |
| PhrasalQuestionBasicsPackage | 5 | 8661 |
| RetainedPracticeIdentity | 10 | 159 |
| NativePractice | 1 | 39 |
| BuilderSelectionPresentation | 7 | 10919 |
| ContentPatch | 9 | 761 |
| LocalTargetGuard | 4 | 77 |
| OwnedSharedSourceDelta | 5 | 6695 |
| Разом, без повторного ROOT/WT підсумовування | 41 | 27311 |

F0 / E0 / W0 / skipped0 / D1; PHPUnit exit0, runner exit0. D1 — наявний PHP 8.5 deprecation `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA`; причина окремо підтверджена ізольованим focused run, конфігурацію не змінено. Safe preflight: SQLite `:memory:`, array cache/session, working_env_loaded=false, opcache CLI=false. 675 protected paths та всі 7 test-file hashes exact до/після.

Receipt: `m78-final-isolated-v1-c097dd4672974416899c1102b18d066d-result.json`, SHA `acd6fdf588a85251a433bc4ae326ed0061eea02eb8fc530d642ba1a50d4af0bb`; actual JUnit SHA `9661af4c0a8a279125236bd6b63591de30b5f868e7bf7cd576ebb2c7f7919f94`.

Попередні спроби не замінюють final totals: sandbox preflight не запускав tests; prefreeze package assertion знайшов пропущений coherent-BEFORE guard, виправлений у package. Початковий новий builder test також знайшов власну fixture-path помилку під час temporary synthetic app-base; виправлений тільки test fixture, фінальний combined run вище не має цього error. Старі тести не переписані задля green.

## AFTER-baseline порівняння

Шість AFTER runs зіставлено з тими самими strict BEFORE lists; SQLite `:memory:`, working_env_loaded=false. У кожному run 675 protected files changed0. Числа нижче — T/A/F/E/W/D/exit, не обіцянка green:

| Набір | BEFORE | AFTER |
| --- | --- | --- |
| M75 | 33/9522/1/0/0/1/1 | 33/9522/1/0/0/1/1 |
| M76 | 46/10575/0/0/0/1/0 | 46/10575/0/0/0/1/0 |
| M77 | 30/22258/0/0/0/1/0 | 30/22051/1/0/0/1/1 |
| Generic | 30/14609/0/0/1/1/1 | 30/14651/0/0/1/1/1 |
| Interaction exact | 134/1588/8/1/0/1/2 | 134/1588/8/1/0/1/2 |
| Wide preservation exact | 136/27203/15/0/0/1/1 | 136/27209/15/0/0/1/1 |

У шести списках 409 method executions / 360 unique methods, без added/missing methods; 49 перекриттів не підсумовані як нові tests. Окремі diagnostic/root/WT replays не додаються до coverage. Новий M78 combined run — окремі 41 unique methods.

Єдиний newly-failing method: `M77OwnedSharedSourceDeltaTest::test_twelve_finite_registrations_and_one_cardinality_hunk_reverse_to_complete_accepted_m76`, WT line67. Перший зупинений guard — complete historical working bytes `app/Support/TheoryContentSource.php`: expected accepted-M77 SHA `2494ae1f5df3b9850ab733a4c54ec730ca1a95f1615f41471558a11f5189c44d`, actual finite-M78-dispatch SHA `1819c27fe542002b7f50ac44a0cb37d6c31a42ce2746fc0117c76104139abe32`. Це historical whole-file pin conflict через потрібну M78 registration, не виявлений behavioral defect; 29 інших M77 methods PASS. Саме TheoryContentSource — перша причина, не пізніший generic-validator diff. Method assertions 218→11 пояснюють suite 22258→22051.

Нова exact lineage fixture [m78-owned-source-hunks.json](../../tests/Fixtures/m78-owned-source-hunks.json), SHA `4de1993fe881b844f6421bd56243a451299858a7556cbcb93c4a7777ad8181e5`, перевіряє всі 9 ROOT+WT afterprofiles: reverse 12 finite dispatch та 4 source-validator hunks відновлює captured raw predecessors. WT accepted-M77 LF та ROOT foreign-inclusive controller/mixed EOL збережені. Старий M77 test/pin не переписаний.

Решта відхилень не приховані:

- M75: той самий `M75PronounFormsRuntimeTest::test_native_visual_and_interaction_sources_and_active_m74_artifacts_are_unchanged`, complete historical profile guard; failure text і metrics ідентичні.
- M76: F0/E0, без нових збоїв.
- Generic: старий discovery warning `No tests found in class Tests\Feature\SavedTestStepWrongAnswerTest`; actual retry wrapper `M76SavedTestRetryRegressionTest` проходить. +42 assertions — лише dynamic catalogue у TheorySentenceBuilderAcceptanceTest для 6 нових owners.
- Interaction: ті самі 8 failures + 1 error; 9 XML failure/error bodies byte-identical. Старі markup/static contracts, PastPerfect fixture та SQLite fixture без question_hints не виправлялись у content scope.
- Wide: ті самі 15 failing methods; 11 failure texts byte-identical, 4 historical source-pin messages мають новий M78 actual SHA, не нові methods. +6 assertions — лише future-owner registry discovery. Старі M74 pool/audit/source-lineage, stale ROOT render-only linked_practice expectation і M73 lineage лишені видимими.
- D1 у кожному наборі — той самий existing PDO constant deprecation, без config change.

Точні AFTER receipts з приватного `storage/app/seo-m2-local/` (raw bytes independently rehashed; comparison зроблено read-only за існуючими JUnit, окремий comparison artifact не вигадувався):

| Набір / receipt | SHA256 |
| --- | --- |
| M75 — `m78-after-m75-v1-6c0821be92f94176b26b7d26c09a36b9-result.json` | `5532a76eb81a73bdadc64aa9b1c51eb80eb2dc3d745c6af6e3980726dfd07a8c` |
| M76 — `m78-after-m76-v1-563139989d364f61b0ce15772fbfbab8-result.json` | `89cabd2fc370c81e8b9c7ec37cf10ab5a9579e6ba321fc8b31101c4162bd6ab7` |
| M77 — `m78-after-m77-v1-b6b5ebe573ab465f8c00e21d026d42d7-result.json` | `fbd330a886f00d4206cf72d78eaec358c6ff3add6e980e41c62a9ed1b53c9a7d` |
| Generic — `m78-after-generic-wt-v1-2cf1d31d49684d33bd56bcfc980b4cfa-result.json` | `cd57be0255fb7531cf1d1600a4328dc717fb5c3161ce651c8ceca31e6bb776d5` |
| Interaction — `m78-after-interaction-exact-baseline-v1-801188b540064bf191dbde2a54159c57-result.json` | `4f66dd974330bc08dee30dfb446f97b6e8996a3115b86d0e2a4c9665ab2e4a89` |
| Wide — `m78-after-preservation-exact-baseline-v1-54a47864172c4bccb96bb888251542d4-result.json` | `73409b3e1566dce25503b95da5f3de0a3bedde90239039a1497fe63d3b4d8be0` |


## Browser acceptance — фактичні main-browser спостереження

Перевірку виконав main у реальному локальному браузері, після actual apply. Offline native-factory/model tests та HTTP audit — окремі докази, не підміна цих результатів. Приватний `browser-acceptance-v1.json` SHA `7b847932f8f44130ad66598a32116babf5a7af18866e093372641ec36fce3689` — summary фактичних main-browser observations, не raw trace. Він посилається на SQL v2; додатковий final SQL v3 вище виконано вже після browser recovery і підтвердив той самий sql_after.

Screenshot `browser-widget-proof-v1.jpg` SHA `e21d23877199e2746b8c4a5840c814fcc0a76601bc05aeb349dafa1df8edd03a` збережений main через CUA: A widget 5/5 із settled реченням. Це один actual screenshot, не заява про окремий raw screenshot кожного з 36 states.

### Layout

36 unique settled states: 6 сторінок × 1440×1000 / 390×844 / 320×844 × light/dark. У кожному стані завантажено Manrope; learning-content overflow 0. Локальний scroll таблиць та невеликий зовнішній decorative/shell overflow обліковані окремо, не приховані як zero document overflow: раніше спостерігались A390 — 13px, A320 — 10px, C390 — 5px. Початковий transient A390 з шириною 988 після resize не зарахований; recovered A390 dark перевірено settled як actual390×844, dark=true, document overflow1px, learning0. Декоративна геометрія змінна; 13px не оголошено фіксованою шириною всіх A390 states. Після перевірки повернено 1280×720 і світлу тему (dark=false), zoom не змінювався.

### Практика й правильність відповідей

- 72 unique full-input cases = усі 18 full inputs × manual/token × desktop/mobile320; це включає і перевищує мінімальний designated48 subset. Canonical text кожного case settled та правильний, score очікуваний.
- 85 distinct selectable options перевірено на правильний/неправильний score: A/B/C/D/E/F = 10/10/16/16/16/17.
- 30 authored input accepted values: A/B/C/D/E/F = 3/3/4/5/4/11. Повні негативні aliases прийнято; це не заявка на довільне Cartesian-розгортання скорочень.
- 13 graded rephrase завдань і 4 retained demos перевірено; всі 67 graded functions пройшли фактично, demos не додані до score/count.
- Разом 76 task-bound accepted-value cases: 33 selectable-option accepted values + 30 input values + 13 graded rephrase values; це не 76 нових завдань або довільних синонімів.
- Native full-input delete→token-return перевірено на першому full input кожного уроку — 6 actual probes, не заявляється браузерна перевірка кожного з 18. Empty/whitespace, wrong→repeated-check→correct перевірено на всіх 6 сторінках.
- На D явно перевірено незалежність group reset та mixed manual/token. На B widget перевірено token remove→return. Native Next і completion пройдено на всіх 6 widgets.

Explicit semantic score probes:

- E: emphatic замість neutral — 2/3, після потрібної neutral форми — 3/3.
- E: When замість потрібного Where — 3/4, після виправлення — 4/4.
- F: incomplete Yes, I'm / Yes, I am — 5/6, не false-positive full answer. Дубльоване Yes у partial gap також дає 5/6, не accepted response.
- A: alternative noun order приймається вручну. Alternate clicked punctuation boundary не оголошено exact alternate-token PASS; canonical clicked path пройдено.
- F legacy widget з weren't вимагає contracted bank form; full-negative widget alias не заявлено.

### Вбудовані widgets

Усі 6 widgets пройдено у реальному браузері 5/5, разом 30 settled речень. Main прочитав усі 30 і звірив canonical visible text, selected IDs та цільову умову з frozen public bank audit. Token/answer/options/formatter/grader джерел банку не переписано. Успішні completion/Next не виведені лише з offline replay.

### Відновлення автоматизації та межі доказу

Невдалі transient actions не приховані й не пораховані як PASS states/cases:

- ambiguous it selector звужено до потрібного native control;
- selector-вираз для weren't замінено exact-role вибором, без зміни навчального тексту;
- resize/heading click випадково відкрив каталог; відновлено canonical URL, хибну навігацію не зараховано;
- rapid.fill чотирьох legacy partial gaps на F залишив word-suggestion overlay; початковий token probe дав 4/6. Явний input click закрив overlay, токени зібрано заново, actual score 6/6; engine не змінювався;
- 36 states та 72 cases завершені до пізнішого optional feedback replay timeout/reset REPL. Із timeout не вигадано додаткового PASS чи відсутніх interaction facts.

Console/no-JS, print та WCAG audit — not_checked. Результат не заявляє повної відсутності decorative overflow, перевірки всіх історичних банк-питань чи всіх можливих punctuation variants.

## Read-only scope/privacy review

64 owned M78 paths: 17 tracked + 47 new; 14 older M75/M76 untracked drafts виключаються з доставки. Дев’ять shared sources × ROOT/WT exact raw reverse — 18/18 PASS. Visual pins 326 × ROOT/WT — 652/652 PASS. Усі 23 manifest hash/byte records збігаються; явний single-final-LF metadata erratum для widget-selection наведено вище, frozen bytes незмінні.

Після додавання new paths до index початковий staged `diff --check` з одним `cr-at-eol` виявив 19 blank-at-EOF notices у нових thin PHP/test/diagnostic files і frozen widget-selection. Це не було охоплено попередньою unstaged tracked-only перевіркою. Щоб не змінити вже перевірені raw source/proof pins, фінальний EOL-профіль явно зберігає ці кінцеві порожні рядки: `core.whitespace=cr-at-eol,-blank-at-eof`. З цим профілем staged diff перевірено без інших whitespace errors; звичайний default check не названо green. Frozen release outputs/шість definitions не переформатовувались після apply.

Окрема binary index-перевірка: усі 23 frozen artifact/definition/author-input blobs мають точні raw SHA/length у working files та index. У чотирьох нефрозенних paths (`.gitattributes`, `.gitignore`, `TextBlockToQuestionsMatcherService.php`, `TheoryCourseManifestService.php`) штатний index LF дорівнює working bytes після тільки CRLF→LF; інших raw/index відмінностей у 64 staged paths немає. Це не заява про raw тотожність усіх робочих EOL-профілів; controller CRLF збережений окремо.

У 18 public JSON не внесено credentials, сирі приватні SQL rows або timestamp values. Немає app/Blade/CSS/JS redesign чи grading/formatter зміни. Історичні звіти/старі тести не переписувались для green.

## Доставка

Local content/apply/SQL/HTTP/registry/old-consumer/browser acceptance завершено в описаних межах; історичні baseline failures/warnings залишаються у звіті, а не названі green. Цей звіт входить у scoped commit. Наразі доставка pending; точний full commit SHA і результат normal working-branch push main надасть у фінальному повідомленні та Git після виконання. Self-referential commit SHA не вигадується й не потребує окремого amend лише для вбудовування в цей файл.

Production, main merge та deploy не виконано. Приватні diagnostics/backup/proof/runtime identity, сторонні старі drafts і секрети не включаються в scoped commit.
