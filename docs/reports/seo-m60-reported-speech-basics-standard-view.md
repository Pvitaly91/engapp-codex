# M60 — Reported Speech Basics у стандартній в’юсі

Статус: редакцію 1.0.1 реалізовано й прийнято на `gramlyze.loc`. Final PHP 334/334, JS 22/22, registry 103/103, AFTER consumers 58/58, repeated no-op, cleanup404, 18/18 visual states, 24/24 input cases, 17/17 accepted variants, refreshed widgets15/15, усі три details та фінальний post-browser SQL пройдено. Git delivery виконується після закриття цих gates; exact commit/remote SHA буде наведено у фінальному handoff. Нижче — межі coverage й відомі UI-обмеження.

## Сторінки й межі

| Сторінка | Page / category | Hero technical / display | Власна практика | Meaningful details |
| --- | --- | --- | --- | --- |
| [Reported Statements](http://gramlyze.loc/theory/reported-speech/reported-statements) | 3 / 3 | B1 / B1–B2 | 2 select + 2 choice + 2 inputs | 1 |
| [Reported Questions](http://gramlyze.loc/theory/reported-speech/reported-questions) | 143 / 3 | B1 / B1–B2 | 2 select + 2 choice + 2 inputs | 1 |
| [Reported Commands and Requests](http://gramlyze.loc/theory/reported-speech/reported-commands-and-requests) | 142 / 3 | B1 / B1–B2 | 2 select + 2 choice + 2 inputs | 1 |

Робоча гілка: `codex/seo-m60-reported-speech-basics-standard-view`. Base: `69994e34da410c66d6807a0921b588f85b81908c`. Рівно три UK theory owners; category «Непряма мова», сусідні Backshift / Time and Place / Say-Tell-Ask, курси, окремі тести й EN/PL не є об’єктом навчального переписування. Production не відвідували й не змінювали. Збереження production canonical у локальному HTML — не production request або deploy.

## Що зафіксовано в навчальних джерелах

- Statements: окремі said + clause, said to someone + clause, told someone + clause; that/no-that там, де це доречно. Учасники, мовець, адресат, оповідач, час і місце явно задані в контрольованих прикладах. Backshift не автоматичний і не доводить, що факт перестав бути правдивим; I може лишитися I. Заперечення reporting verb і reported clause не змішані.
- Questions: if/whether для «чи», wh без вкладеної інверсії, subject/object who за ролями; прибирається питальне auxiliary do, а не lexical do чи заперечення. Питання не оголошено готовою відповіддю. Outer Did…ask…? зберігає знак питання. Ability/request та ввічливе питання зараз / переказ минулого питання розділено без blanket ban asked if/could.
- Commands/Requests: object + to / not to із названими ролями; tell, ask, order, advise, warn за наміром. Ask to / ask someone to / ask if someone could, offer та suggest не зведено до однієї моделі. Negative instruction відрізняється від negated reporting verb; прохання або порада не доводить виконання. Інші природні that-моделі не оголошено неграматичними поза цільовою вправою.

Усі речення й сценарії авторські; publisher exercises не скопійовані. English examples мають повний український переклад у підтриманих native fields. У шести meaning choices переклад стимулу навмисно відкривається після Check: український контекст і критерій видно до відповіді.

## Єдиний native-дизайн і точна metadata map

На кожному owner збережено шість continuing source slots і всі їхні UUID/order:

| Source index | Було | Стало | Незмінна column / level |
| --- | --- | --- | --- |
| 0 | hero | hero | header / B1, body display B1–B2 |
| 1 | HTML box + старий heading | forms-grid | left / omitted-null |
| 2 | HTML box + старий heading | comparison-table | left / omitted-null |
| 3 | HTML box + старий heading | mistakes-grid | right / omitted-null |
| 4 | HTML box + старий heading | summary-list | right / omitted-null |
| 5 | navigation-chips | navigation-chips | footer / omitted-null |

Рівно 12 box→native transitions і вилучення лише 12 відповідних old headings; null levels не піднято до B1, right columns не перенесено в left. Додано по context usage-panels/B1 і final practice-set/B1: у проєкції 24 blocks, з них 18 continuing і 6 appended. Це finite data mapping, не новий тип дизайну, CSS, дрібніший шрифт або глобальний parser для довільного HTML. Caller-owned theory межа зберігає виключені consumers.

Три details — семантичне рішення без квоти й runtime word threshold: Statements current truth → context/sections/0; Questions ability/request → context/sections/3; Commands negation scope → context/sections/1. Коротке обов’язкове правило кожної теми лишається у visible basic.

### Nonvisual integration

Дев’ять shared finite registration/package-preservation hooks: `TheoryContentSource`, `TheoryContentRenderer`, `TheoryHtmlAdapter`, `TheoryLegacyAdapter`, `TheoryCourseManifestService`, `TextBlockToQuestionsMatcherService`, `PracticeCompletionLinkedPractice`, `TestJsV2Controller` і `TheoryNativeContentRelease`. M60 підключено тільки до точних owners/caller scopes; course/standalone consumers зберігають accepted матеріал. `preserved_native_metadata` підтримує лише явно оголошені finite null-level/right-column переходи з повним before/after binding, не fuzzy/global metadata fallback.

Три clone-only wh виправлення стосуються лише selected Questions IDs: **19278 Who→who, 40840 Where→where, 40841 Why→why** у відповідних physical answer-option copies. Exact canonical UK practice body/config та всі п’ять старих question/answer/option/hint bindings перевіряються all-or-nothing; на stale/incomplete bindings нова presentation не застосовується, original collection збережено. SQL/source keys і чужі consumers не змінюються. Це nonvisual integration, без нового UI, styles або grader logic.

## Практика

18 own tasks: 36 options і 6 manual/token inputs. 17 finite accepted variants = 6 canonical + 11 aliases, не 17 aliases. Залежно від конкретної умови приймаються that/no-that, if/whether, the next/following day, she would/she’d, was not/wasn’t. Умови задають referents і lexical the map; matcher не просить вгадувати приховану особу або артикль. Curly apostrophe feedback задано finite даними; shared grader не змінено.

Окремий вбудований sentence builder: main-approved 15 existing B1/type4 questions, по п’ять на owner, 0 new questions і 0 new links; exact bank classes 1/2/1. Не довільні перші п’ять: bank audit прочитав 108 actual candidates, contract-filtered 60, обрав 15, виключив 45 у contract pool та 48 інших exact-bank candidates. Три bounded lowercase-wh presentation clones не переписують old SQL/source question keys, answers, options, hints або course/standalone payloads. Final refreshed live acceptance на 1.0.1: **5/5 на кожній сторінці, 15/15 сумарно**, actual BANK-only clicks; це окремо від 18 own tasks і SSR payload audit.

## Незалежні докази до apply

- Independent source/DB/HTTP BEFORE: `2cf4f9d9038a66546b90aee3d26e25a1a9b31aa5fd552ea870a70ef2d81f705f`. Actual target H1, title, category, six source slots + subtitle зафіксовано до правок.
- Old consumers/nav BEFORE: `b15e05b5eb3471967a755f87ab60ef348b9149271c03c148a44cf63efc95d220`: 58/58 HTTP200, 9 saved banks, 528 old questions, 0 learning writes. Усі шість чинних native href підтверджено локальними GET до apply. Baseline registry: 100/100 accepted owners; після M60 фактично підтверджено 103/103, не hardcoded 100 cap.
- Pure author/native projection: `c0fed63ec9fdd4dcc6b658aecf13a1d2608929777631b538d83343b8fbedf135`. Existing serializer, no DB writes; усі 18 continuing UUID exact, 24 supported blocks. Найбільший body 24 721 bytes < 65 535.
- Historical frozen 1.0.0 master: `edbc02cd1032ee29fc32ae9c9da8c791c38716bc9d68cfdd4acd1f6d15b20633`; public source BEFORE `34b4dd07f1d216f8a74c7123af1bab17f7f1e6bf5a97bcf7e94f80ee33bbdaee`; historical AFTER patch `8efbafe7ab09d161dd16191ccd568bcc89855237a5ac3ebcd619b625e7fefe2f`; historical component map `a205be8d4894f8f49a3802a73c716b4a52fafb106786e3355b1799a0f7941c42`. Current 1.0.1 master/native patch наведено в секції корекції; старі версії збережено.
- Builder contract: `58ca869b0f34adb0c97f494780e9e267b6bec3f949fa7aabbf48516024647785`; bank audit v1 `544a3354650f276849c792ab310f1e005fdda26ade5356f03f38d1ba55a8dd9f`. Остаточна повна checksum manifest перевіряється разом із фінальними gates.

## Metadata й джерела

Pre-apply metadata **v2**: `bb5ff8906eb78fb8175a19f95bddf002cd216a5290f60252e0eb8523f5bff34e`; current PageMetadata `1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e`. Очікувані title/description та full LearningResource description обчислено до apply. Canonical/breadcrumb збережено з actual BEFORE HTML. V1 помилково підставив local navigation URL як canonical; це виявлено до freeze, v1 збережено як явно superseded, у frozen master використано тільки v2. Ця помилка не була застосована до сторінок і не є AFTER-as-oracle.

Три British Council grammar main bodies author прочитав direct: Statements, Questions, Reporting Verbs. П’ять requested Cambridge URL дали main direct403; official publisher-indexed main bodies прочитано окремо, включно з actual `/us/.../reported-speech-indirect-speech` та `/us/.../if-or-whether`. Також прочитано Cambridge Punctuation indexed body і Google Helpful Content direct. Direct403 не названо успішним direct read; learner comments не є нормативним джерелом, signed team replies не підміняють main body. Повний access journal — у versioned editorial audit. Немає word-count quota, вигаданого authorship або ranking guarantees.

## Локальне впровадження й HTTP/SSR

Fresh guarded local proof → preview → exact diff review → exclusive backup → transactional apply виконано тільки на перевіреній робочій БД `gramlyze.loc`. Source-sync підтвердив 32 scoped paths, receipt `9997b84da148ff2edc807c5f8a48566422997314bbcb4f295f0c852e87e68715`; 326 visual і 2 355 попередніх frozen files незмінні. Plan digest `5f7c3fdbd94bcf780bff6d0c5571de9312afab3a972152ec6ee6985c7ff76389`; exact review `d4ce798c8834af1a59bb3035fd9e3e11b4c1291fc282538ca80fdae851d742dc`. Exclusive private backup створено до запису, raw SHA `97213b331ee6afeb5a7ba72a5bc287c0cb9de28b46204052c08953536b5189d2`; це не plan digest.

Actual apply: 24 updates — 3 Page.text і 21 continuing UK body, включно з 12 approved type/heading pairs; 0 level/column updates. Додано 6 blocks — 3 context + 3 practice. Deletes, question/option/answer/link/progress writes — 0. Незалежний bank review exact plan пройдено до apply.

Postconditions `final-readonly-v1.json`, SHA `32a3d62422b362f40a0a2119e1d550d0fb4dea0d28be09e3c6fce121bca5542c`: усі 46 tables реконструюються до exact BEFORE за дозволеними дельтами; 21 old UK block має лише погоджені зміни, 42 EN/PL blocks і сторонні старі rows/timestamps незмінні, 6 нових blocks точні. Q-family/link writes відсутні. Fresh AFTER preview і обидва повторні apply — no-op: 0 updates / 0 inserts / 0 deletes, нуль записів. `noop-preview-v1.json` plan digest `037868d6d2a18d3a78592e8ca679061fe5ad19c21fbdb02170a742897ba4d904`.

Live HTTP/SSR `live-pages-v1.json`, SHA `014d571f0278f3b0782df1d36148d919b053ba0aed40f2140ad9ca8e9a669630`: 3/3 HTTP200, 366 повних навчальних fields. Native metadata/LearningResource descriptions, stable H1/canonical/breadcrumb/старі anchors, details 1/1/1, own practice 2/2/2 та точні п’ять тематичних widget payloads на owner — PASS. Це фактичний локальний SSR, не автономний preview, але він ще не підміняє інтерактивні browser clicks і widget5/5.

AFTER old-consumer audit `after-old-consumers-v1.json`, SHA `7074e5e813b07aa42731d6fba61640917b7126f84cdca5cfde07c41b9760b392`: 58/58 PASS, 9 saved banks і 528 old questions збережено, 0 writes. Порівняння з незалежним BEFORE не підмінене новим AFTER baseline.

Перший тимчасовий proof route після основного apply видалено; `routes/api.php` тоді повернуто до exact попереднього SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Окремий GET видаленого nonce route повернув **404**. Cleanup receipt `3fa8efac81f6d61da06406c23d1f8f4608b2ecee5d136602f0f1f4050c832647`. Другий scoped read-only nonce proof для corrective apply 1.0.1 також видалено: exact routes SHA знову той самий, окремий GET — **404**, final cleanup receipt `e0805dcf6f3ed75f42610f08d3c63f5add63cf9ae5d87fcd81ef7fa322b5b3ef`.

## Браузерна знахідка: plain-text feedback 1.0.1

Під час live browser acceptance основної редакції виявлено literal `<span>` / `<br>` у поясненнях: 94 author feedback values містили HTML, тоді як чинна native-в’юха навмисно виводить їх через безпечний `x-text`. Тому попередні SSR/VM PASS не оголошуються доказом правильного візуального відображення feedback.

Підготовлено новий frozen author master **1.0.1**, SHA `aabc1bdc0501b1a495799c75c3f22cc93835cf838a6c32fa9937465cea17ab01`; історичний 1.0.0 збережено. Незалежний повний deep diff: рівно 94 feedback-string зміни (48 unique explanations, по owners 52/22/20), `/version` і один provenance object — інших змін немає. Для кожного значення трансформація точна: прибрано лише exact `<span lang="en">`, `<span lang="uk">`, `</span>`, а exact `<br>` замінено на newline; усі інші символи збережено. Task conditions, 6 HTML labels + 6 HTML prompts, options, answers, 17 accepted variants, tokens, 3 details і linked widget data незмінні. Runtime/renderer/grader/CSS не змінюються; без переходу на `x-html`.

Corrective apply 1.0.1 **виконано**: рівно три `text_blocks.body` updates — IDs 12720 / 12722 / 12724; 0 inserts/deletes/інших table targets. Before-state основного apply не переписували; historical 1.0.0 джерела збережено. Source-sync v2 перевірив 42 exact scoped paths, receipt `e952581bf61a22dbd90334f2ab3bf49837a1a5faaf563aaf6884cf56bad10960`; 326 visual / 2 355 попередніх frozen files незмінні. Fresh scoped nonce-proof receipt `267899c1a220818768398a3550ed06a878b432c3c8b058b438d17686057dd4d7`; остаточний cleanup другого proof завершено, як зазначено вище.

Exact corrective preview digest `e8c25d242adedef78b7c0c6e4965305b3d1e791be1dcd6f7b999bb519b43b39b`; exact-review receipt `579aafb01e0bce3d113beb909474a2b07e08496942027345be38fbcdbf0bf95b`. Незалежний review перевірив усі 2 728 source pins, 63 old locale rows та 6 new metadata bindings у повному 46-table захисті. До apply створено exclusive `feedback-backup-v1`, raw SHA `739a895a26237e3d7dea765d3b2a1f03e6919115660ae6c55dbda260ee283acc`. Current native patch 1.0.1 SHA `4807643565ffedd36b56a2fe201ceae27837e9b5b455fff4105a83b51f4c9f26`.

Після корекції fresh zero-preview має digest `7700799ad6142d74ca93cfbbf15150b9f435d5922e79087e23a4df00277ae9dc` і 0 updates / 0 inserts; digest звірено з actual `feedback-noop-preview-v1.json.sha256`, без помилкового додаткового terminal0. Cumulative read-only v3 SHA `248d9a63731077a38aab88443af5a32d95ee6c5e7ae1c39f748fdc3b9594db0a` підтвердив усі 46 tables, лише три дозволені corrective bodies, exact metadata, 0 diagnostic writes. Окремі correction guards: 7/7 PASS, 438 assertions; їх не додають до фінального full-batch totals вдруге.

Обидва фактичні повторні corrective apply — 0 updates / 0 inserts / 0 deletes. Full active M60 1.0.1 normal preview має digest `f1459164523032e7a885fecf272d0d70ce350b02ef685e7cc2146c1e95b37fb7`; обидва його фактичні повторні apply також 0/0/0. Ідемпотентність перевірено окремо для коректора й нормального активного пакета, не лише за preview.

Corrected SSR `live-pages-v2.json`, SHA `da47b257fbb166262e9b2793ef51f7402241ad6da58e6c48f4469f42a04d373a`: 3/3 HTTP200 і 366 full fields. Corrected builder audit 3/3 PASS, receipt `364dcd46b3222356bf89e6cb723d88fe2669bd07cd688beaacd170a89a973e77`. Registry **103/103 PASS**, receipt `471c7f8c92d690f832e7a3c7d03c8c442959449841d6acfe392496a8a1b2931f`; усі 100 prior registry rows семантично тотожні, крім порівнюваних volatile full-response `http_sha256` значень, не навчальних полів.

Corrected AFTER old-consumer receipt `f56af4afaaed2e97ea18292a3c28aba1d43551b0b9d3776f07e24b0a43fa271e`: **58/58 HTTP200**, 9 saved banks / 528 old questions exact щодо actual independent BEFORE. Усі 46 tables незмінні протягом GET-перевірок і відповідають cumulative final-readonly-v3, writes0. SSR/audit/registry не підміняють інтерактивні widget clicks.

Повторний actual-factory JS для final master 1.0.1: **22/22 PASS**, receipt `acb5baf945d107e6647ee577cc2c6e34f9cec5b369c13eec4ae30336d51c7abb`. Початковий повторний запуск мав 21 PASS / 1 FAIL лише через застаріле exact version-очікування 1.0.0; після погодженої єдиної заміни на exact 1.0.1 повторний запуск успішний. Зворотна заміна відновлює початковий test-source SHA точно; answers/aliases/engine не послаблювали. Final test-source SHA `6f6b49cad098634cecdb8f9bc163e1e7fc110d365b4198c667b6728a6f2eee0d`.

Corrected live own-practice acceptance: **18/18 desktop tasks**, **24/24 input cases** = 6 inputs × manual/tokens × 1440/390 widths; окремо **17/17 accepted variants** і 2 curly-apostrophe cases. На всіх трьох сторінках у двох розмірах перевірено empty/partial/wrong/repeat зі score0 → edit до score2 → recheck без подвоєння → reset; literal HTML відсутній. Шість mixed delete-return-rebuild cases PASS. Actual UI group independence на кожній сторінці: input reset зберігає forms2/2; forms reset зберігає meaning2/2; input score indicator очищено. Global storage не очищали; reset стосувався лише нових M60 practice/widget controls.

## Фінальне браузерне приймання й post-browser SQL

Actual browser receipt `browser-final-v2.json`, final SHA `716fa822403a9c8e9e939be4d1472489bce8ca75587b7e9bd74cbe8c1e9882f3`: **18/18 unique states** — усі три сторінки × 1440×1000 / 390×844 / 320×844 × light/dark. У кожному state main client == scroll, clipped learning nodes0; footer повністю досягнуто до фіксації результату. Mobile learning sections 358 / 288 px; tables мають native keyboard-focusable overflow-auto region: client322/252, scroll576. Це контрольований внутрішній scroll таблиці, не обрізаний навчальний текст.

Whole-document shell overflow розкрито окремо: у mobile states document scrollWidth перевищував viewport на 1–12 px, хоча learning container вміщується. Before/after shell geometry залежить від state; не заявлено, що весь overflow напевно успадкований або що global shell пройшов blanket PASS. Shell/CSS redesign не виконували. У чинного native B1 badge в dark помічено слабкий contrast; його не перефарбовували в content task.

Final refreshed widgets перевірено на **320px dark**: усі **15/15**, кожна сторінка **5/5**. Перед кожною відповіддю прочитано фактичну перемішану українську умову; натискали лише видимі невикористані BANK buttons, не selected answer chips і не serialized answer state. Регістр та multiplicity точні, duplicate me використано двічі; для legacy question19278 не вигадано відсутній period token. Historical 1.0.0 widget observations не підсумовано як додаткові final cases.

Усі три point-level details після refresh на **320px dark**: Enter відкриває, Space закриває, deep-link + reload відкриває повний текст; client/scroll metrics рівні (Commands669, Questions698, Statements669), без прихованого залишку тексту. Перевірка details незалежна від practice scores. Після перевірок повернуто normal 1280×720 light; actual final Statements practice screenshot збережено (SHA `535c8b7f5bb8422600364828b9d7f03cf33cfe52f596e388b3ca9d894e3b94a7`), tab позначено deliverable. Actual final Statements CUA dev logs snapshot `levels error/warn, limit20` — `[]`; це конкретне фінальне спостереження, не blanket assertion про всі попередні стани. Один ранній mobile-menu animation випадково відкрив read-only `/verbs/test`; жодного тесту не запускали й не відповідали, target page відновлено.

Final **AFTER-browser** `final-readonly-v4.json`, SHA `248d9a63731077a38aab88443af5a32d95ee6c5e7ae1c39f748fdc3b9594db0a`, збігається з v3: усі 46 tables точні, cumulative дозволені дельти та лише три corrected practice bodies/їхня metadata відповідають 1.0.1. Сам read-only diagnostic виконав 0 writes / 0 HTTP requests. Браузерне приймання не спричинило сторонніх SQL-змін.

## Перевірки й відкриті кроки

Final corrected 1.0.1 full isolated PHPUnit: **334/334 PASS, 62 262 assertions**, 67 suites, 0 failures/errors/skips, 1 existing deprecation. Exact deduplicated union: 278 prior + 56 M60 identities, тобто попередні 327 cases + 7 correction guards без додавання overlapping focused runs. Result SHA `a2592cc43fdd943976d1bab7a1a9e29c1a0535d154d68b631ac06561e6f59667`; union-v2 `048cb07497471b080e2dcce116baabc5513dc75e520d9bcb667f7dc8da39c85d`. 46 661 protected files, 0 changes. Final JS22/22 — окремий gate, не ще 22 PHPUnit cases.

Основна редакція 1.0.0, historical frozen shipped-factory offline run: **22/22 PASS**, receipt `e88ba232e4bcda5d672f157799cb72be2567b0edffef94b55e3e36c75e6f9ee2`. 18 tasks, 36 options, 6 manual + 6 token, 17 variants і curly feedback, 6 deferred translations, empty/partial/wrong/repeated wrong/retry/correct/recheck/reset, shuffled multiplicity/delete/return/rebuild і незалежні scores; network0, protected master/engine bytes unchanged. Це VM-тест actual shipped factory, не live browser або widget acceptance; final 1.0.1 run наведено вище, totals не складаються.

Основна редакція 1.0.0, historical full isolated PHPUnit batch: **327/327 PASS, 61 824 assertions**, 0 failures/errors/skips, 1 existing deprecation; deduplicated-union receipt `40a31d41cefcb4465ac022f38c9ae4f16cf78114ce763ef1b6970500b34e2314`. 278 prior non-controller identities exact, 49 M60 cases; дві historical controller-chain перевірки виконано через M60 compatibility reconstruction без подвійного підрахунку nested/focused runs. Ізольовані SQLite `:memory:`, runtime/views; 46 661 protected files before/after exact, 0 protected changes. Historical 327 не додають до final334 повторно.

Fresh historical literal-UI baseline: 6 tests, 2 PASS / 4 FAIL, 23 assertions, 0 protected changes. Ці відомі literal-test failures не «виправляли» redesign або зміною заморожених історичних матеріалів і не підсумовували з PHPUnit як нові успішні tests.

No-JS, print і повний WCAG-аудит не перевірені й не позначені PASS; це межі coverage, не приховані успішні gates.

## Git delivery

Git delivery виконується після закриття наведених gates і review явно обраних файлів. Exact commit/remote SHA буде наведено у фінальному handoff; цей звіт не містить вигаданого delivery PASS або самопосилального commit SHA.
