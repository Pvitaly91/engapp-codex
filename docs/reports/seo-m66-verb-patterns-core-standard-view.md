# M66 — Bare Infinitive, Gerund vs Infinitive, Verbs + Gerund

## Статус: 1.0.2 локально застосовано й прийнято

Цей звіт фіксує фактичне локальне застосування teaching 1.0.1, окрему body-only корекцію 1.0.2, repeated no-op, фінальні read-only SQL/source/HTTP/registry gates, ізольовані тести та реальне браузерне приймання всіх трьох сторінок. HTTP/SSR і fixture-тести не підміняють браузерні взаємодії. Доставка виконується scoped commit і normal push у вказану нижче робочу гілку; остаточний повний SHA та незалежний remote check наводяться в повідомленні доставки, без самопосилання commit усередині його ж звіту.

Робоча гілка: `codex/seo-m66-verb-patterns-core-standard-view`.
Accepted base M65: `c96e6b4e0c042f98fb0c105f61225f72d85a4850`.
Scope — рівно три UK theory pages, Page IDs 193/196/199, category ID 33:

| Сторінка | Технічний рівень практики | Чинний display badge | Фінальний local HTTP/SSR |
| --- | --- | --- | --- |
| [Bare Infinitive](http://gramlyze.loc/theory/verb-patterns/bare-infinitive) | A2 | A2–B1 | 200 / PASS |
| [Gerund vs Infinitive](http://gramlyze.loc/theory/verb-patterns/gerund-vs-infinitive) | B1 | B1 | 200 / PASS |
| [Verbs + Gerund](http://gramlyze.loc/theory/verb-patterns/verbs-plus-gerund) | B1 | B1 | 200 / PASS |

Main, PR, merge/rebase, production `.com`/`.ub` і deployment не входять у M66. Served ROOT та commit-worktree різні; сторонній dirty ROOT не є дозволеним staged scope. Секрети, runtime identity, raw DB rows, backups, screenshots і приватні receipts не включаються у commit.

## Навчальна редакція і native-дизайн

У кожному уроці збережено шість continuing slots: типи, columns, sort/source indices, рівні та восьмиелементну navigation. Metadata transitions: 0. Додається по одному native `practice-set`. Контентний пакет не створює нового renderer, Blade/CSS/frontend JS, `m66-section`/`m66-detail` стилів або глобального word-count правила.

- Bare Infinitive: конкретні modal/let/make/help моделі; object forms та виконавці; help з `to` і без нього; permission не доводить виконання дозволеної дії. Make може позначати спричинену реакцію, а не лише навмисний примус. Perception розрізняє whole-event viewpoint і process, без вимоги бачити кожну секунду та без твердження, що `-ing` ніколи не завершилося. Додані питання, main-clause negation, короткі active/passive межі; `be made to` та permission-specific `be allowed to` не змішані з active моделями.
- Gerund vs Infinitive: fixed models, допустиме перекриття обох форм і контекстні зміни значення stop/remember/try. Like/start/begin/continue не мають універсального general/specific або pause/no-pause алгоритму. Намір, спроба, згадка, нагадування та досягнутий результат розведені; критерій конкретної корекції видно до відповіді. Повні EN/UK приклади відокремлені від grammatical notes.
- Verbs + Gerund: конкретні enjoy/mind/dislike, avoid/consider, suggest/recommend, keep/finish моделі; семантичне групування лише мнемонічне. Continue з обома complements збережений. Ролі у mind/opening/me opening/my opening, suggest/recommend/that-clause та розгляд можливості не підмінено автоматичним рішенням або виконанням. Finished writing не означає sent.

Короткі пояснення, переклади, приклади та застереження залишені visible basic. По одному meaningful point-level detail на урок:

| Owner point | Detail key | Окреме поглиблення |
| --- | --- | --- |
| bare-usage / sections/4 | bare-perception-viewpoint | Погляд на подію в perception models |
| comparison-usage / sections/2 | comparison-event-status | Форма, намір і фактичний результат |
| verbs-gerund-usage / sections/0 | verbs-gerund-mind-performer | Хто виконує дію в моделях із mind |

Редакційний review агентський; зовнішнє human review, сертифікація або гарантований SEO/ranking ефект не заявляються. Повний BEFORE/AFTER mapping і спосіб доступу до кожного джерела записані в editorial companion.

British Council [Verbs followed by the infinitive](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/verbs-followed-infinitive) прочитаний як direct editorial body. Cambridge [Make](https://dictionary.cambridge.org/grammar/british-grammar/make), [Help somebody (to) do](https://dictionary.cambridge.org/grammar/british-grammar/help-somebody-to-do) та [perception models](https://dictionary.cambridge.org/grammar/british-grammar/hear-see-etc-object-infinitive-or-ing) перевірені за publisher-indexed editorial body після direct 403; прямий повний доступ до них не заявляється. Основні editorial articles не підмінено learner comments або exercise banks. Окремі signed replies позначені у джерельному журналі саме як replies.

## Явна семантична редакція 1.0.0 → 1.0.1

Frozen 1.0.0 не переписано. Створено нові `m66-verb-patterns-core.v1.0.1.json`, UK companion і `m66-verb-patterns-core.revision-1.0.1.md`. Це навчальне уточнення, не technical EOF correction.

Єдиний task diff: `m66-bare-meaning-2`, `lessons/0/blocks/6/data/choices/1`.
Для стимулу “My aunt let me use her bike, but I chose to walk.” правильну опцію «Тітка дозволила, але я велосипедом не скористався» замінено на «Тітка дозволила; я обрав піти пішки». Оновлено тільки `options/0`, `answer`, `accepted/0` та відповідний lowercase feedback key; саме пояснення незмінне. Вибір піти пішки не видається за окремий доказ завершеної ходьби чи фактичного невикористання велосипеда. EN stimulus, повний переклад, інші tasks, aliases, tokens, metadata, navigation і bank selection не змінені.

| Артефакт | Raw SHA-256 |
| --- | --- |
| Історичний master 1.0.0 | 2c50168b8df7320df427999a07083213e97e1ab99baea856dddee86b74c95069 |
| Історична UK 1.0.0 | 7c9a19b5aab2bea9a52c529b877773c7e60c006c1c05aa06067fe5fd1d196b30 |
| Новий master 1.0.1 | 1a3abd237a413f33f053726185450d9a814c5f01a3c12dfba370268cdc709e4e |
| Нова UK 1.0.1 | 73df282df87f34e4279db6e4b4587d3a609e8a8d4dab4388c5c57057bc9e0798 |

Exact 1.0.1 projection, bank binding, preview/backup та локальне apply підтверджені окремими receipts; ця редакція є застосованим незмінним predecessor. Старі private-draft receipts не перейменовані на докази нових frozen bytes. Teaching 1.0.0 і technical EOF 1.0.1 lineage M65 не змінені.

## Явна редакція 1.0.1 → 1.0.2: перенесення формули без редизайну

Під час приймання 1.0.1 нерозривний перелік `can/could/must/should/may/might/will` у першому заголовку Bare forms-картки спричиняв learning-content overflow на вузьких viewport. Виправлено лише навчальний title `lessons/0/blocks/1/data/items/0/title`:

- Було: `підмет + can/could/must/should/may/might/will + базова форма`.
- Стало: `підмет + can / could / must / should / may / might / will + базова форма`.

Це природні пробіли для перенесення чинним компонентом, не CSS/Blade/renderer fix. Нові master/UK/revision files мають версію 1.0.2; frozen 1.0.0/1.0.1 та їхню history не переписано. Patch v3 відрізняється від v2 рівно одним body `targets/0/after/page/blocks/1/body`; усередині native JSON — лише `items/0/title`. Формула, решта teaching fields, усі 18 tasks, aliases/feedback/tokens, три practice bodies, 15 widget questions, UUID, navigation і metadata залишилися незмінними. Bank audit v2 зберігає свою версію 1.0.1, бо його дані та exact practice bindings не змінилися.

| Фінальний source artifact | Raw SHA-256 |
| --- | --- |
| Master 1.0.2 | 39f5a58004cd14dcb17818851ebd3442e1c84ba5535a8d748c1aeb51ab030e2c |
| UK 1.0.2 | 7dd800082a40c2b410639a79b6008a90e20127e35843cd059a6170715a229761 |
| Revision 1.0.2 | 92c6aadcbc179cda2a2af145fd6499117c1e4c7b5e8c8bf2eaab5869528abc68 |
| Patch v3 | 7339ef045e403c2473ed5e8a034d54bbe1639d179ba4782a84b9ce64088135a7 |
| Components v3 | 54f6a392260d63894268f6e0c3615cafe730a7eead974f5f867475762ddc97ce |
| Checksums v3 | 7d06b0c11561c74239ee19b7955e98a3c3244f948934910c71fa738cfd6bf773 |
| Незмінний bank audit v2 | ad99635d51a6d9bfa3d246e1458f3e18f37e59bd9377ba585969689bc3e87e5e |
| Незмінний builder contract v1 | 702cb32d4dceb208baf7efb4512dd0443f0138b27f222aed1db3c60449d7d641 |

Forms body SHA: `7ea19957a78de791e3ef52d69fd2cf351365870fb5d1d8e06c0c7beab955ceda` → `381606e009fa10b27ca4d495842f0459b6df22434645c29265b8e4a4842d60f1`. Explicit `TheoryNativeContentRelease` profile version 1.0.2 validation пройдено. Усі 21 checksum artifacts перевірено за raw bytes/lengths, strict UTF-8 без BOM, LF, один кінцевий LF, без trailing whitespace та з ROOT/WT parity. Власні public artifacts і private projections не нормалізують історичні shared files.

## Власна практика та окремий TYPE 4 bank

Власні 18 tasks — по `2 selects + 2 choices + 2 inputs` у кожному уроці. Це не 15 selected widget questions. Для власних inputs передбачені manual і tokens; відповіді й aliases явно зберігають виконавця, предмет, час і область заперечення. Довгі пояснення/повні переклади не заховані у величезних текстах кнопок. Фінальні real-browser grading/feedback/reset перевірки головний агент фіксує тільки в окремій секції Browser acceptance нижче.

Read-only bank audit охоплює всі 36 eligible existing questions, 226 ordered answer instances і 105 links, включно з question attributes, shared options/pivots, hints, verb hints, variants і loaded relations. Snapshot digests непрозорі; raw SQL rows та timestamps не публікуються.

Обрано 15 reused, 0 new. Фактичні writes у questions/options/answers/pivots/question-theory links: 0 за фінальною реконструкцією всіх 46 tables. По п’ять різних тематичних питань для кожного native widget:

| Урок | Selected existing IDs | Покриття |
| --- | --- | --- |
| Bare Infinitive | 40107, 40109, 40110, 40111, 40112 | modal, let, make, help, perception |
| Gerund vs Infinitive | 40047, 40050, 40051, 40052, 40056 | enjoy, start, stop, reminder, try-a-method |
| Verbs + Gerund | 39688, 39689, 39690, 39691, 39692 | avoid, suggest, finish, mind, keep |

Видимі UK clone-only умови обмежують потрібну модель, де українська допускає інші граматичні відповіді. Help з `to`, start/continue alternatives, try success/failure та remember reminder/report не оголошуються універсально неправильними. Усі первинні ordered instances, repeated articles та option identities зберігаються. Question 40117 виключений через inherited mid-sentence `Let`; shared token formatter і option rows не переписані. Чинний `ComposeTokenCase` використовується без нового форматера.

## Повторне використання primitives і shared seams

M66 package/policy/selector/guard — finite wrappers над чинними `TheoryNativeContentRelease`, `TheoryNativeContentPatch`, `TheoryLocalReleaseGuard` і native practice engine. Вони не замінюють загальний planner, transactional writer, renderer, grader чи progress model. Нові diagnostics делегують hash-bound існуючим read-only M52/M53 procedures; автоматичного apply/startup/hook/scheduler немає.

Вісім shared integration seams містять лише M66 dispatch/guards:

| Shared файл | M66 роль |
| --- | --- |
| TheoryContentSource | Finite package context у чинному canonical source flow |
| TheoryHtmlAdapter | Native point panels і exact design validation |
| TheoryLegacyAdapter | Тільки finite wrong/right correction fields |
| TheoryContentRenderer | Exact canonical widget bindings |
| PracticeCompletionLinkedPractice | Caller-owned canonical block clone |
| TextBlockToQuestionsMatcherService | Finite exclusions лише для позначеного clone |
| TestJsV2Controller | Збереження старих standalone-test theory bodies; виключення нового practice body |
| TheoryCourseManifestService | Збереження попереднього course content |

Selector fail-closed звіряє owner/category/level/config, selected IDs та loaded snapshots. Condition presentation працює на clones, не змінює original models і не виконує SQL. Фінальні source/SQL/old-consumer gates пройдено; exact scope і receipts наведено нижче.

## Незалежний BEFORE і source evidence

BEFORE знятий до active M66 projection: 46 SQL tables, 326 visual hashes, 2456 frozen hashes, шість HTTP 200 (три theory та три course URLs). Три continuing definitions і accepted M63/M64/M65 evidence збережені окремо.

Dynamic registry BEFORE: 118 targets, 0 failed, 0 writes. Фінальний discovery під source v3: 121 targets, 0 failed, 0 writes; source-filtered M66: рівно 3 targets, 0 failed, 0 writes. Обидва фінальні CLI gates завершилися exit 0 та створили нові v2 receipts; історичні v1 receipts збережені exact. Це фактичний discovery, не hardcoded registry limit і не browser interaction acceptance.

| Приватне evidence / source | Raw SHA-256 |
| --- | --- |
| before-v1.json | 517118d0fe7cf764229b5926e82fa7044b10a92535923a7301bc46602311d6a3 |
| before-old-consumers-v1.json | 1cafef083ae1d25457be74188a3590b847265d256d18a4d4433de167b465be40 |
| registry-before-sentence-builders-v1.json | 11fcd797579b37884cb93c8a131fbdfe5f799446d96e9597793629a5285805b0 |
| browser-before-v1.json | 84cddbd723270a15b48f9b001da48fec8d21adecce73c64b54e40f55c81eca4d |
| Bare definition BEFORE | d5e25e0b56ed9dcc7523c5507f63573378c0317528465572ee5b42c97441f263 |
| Comparison definition BEFORE | 4d716f1d993b8db8d790aff1082a72a2de0145f51d80d7b4c4a6cb08c37bad4f |
| Verbs + Gerund definition BEFORE | b9111cde702d11850f4ed67caabbb3ecb28ee4e1836fc7aa0bccc9794bf85bf8 |
| TheoryNativeContentRelease BEFORE | c6d93539fa4b0fa70309af2bd9f27d0317ba267e016e2c5a6820182f51b49632 |
| TheoryNativeContentPatch BEFORE | f521ac6f93f77dd240e70b1a24b4c69e668830ddf3fc484f9ad4cd8c27351a38 |

До apply actual browser BEFORE охопив 18 states: три сторінки × desktop/390/320 × light/dark. Власних practice groups та sentence builders було 0. Learning-content overflow не знайдено; animated shell мав 0–15px overflow залежно від стану. Це historical baseline, не AFTER UI PASS.

У новому old-consumer diagnostic прибрано зайву metadata replacement: вихідний allowlist лишається `pages/text_blocks`. Історичний immutable BEFORE receipt не переписаний. Audit звіряє exact routes/source hashes/old SQL/HTTP payloads і reviewed final table fingerprints; metadata label не розширює SQL-авторизацію. Wrapper source не є прихованим self-binding immutable BEFORE.

## Raw EOL і Git blobs

Runtime/source-sync hashes рахуються за фактичними raw bytes ROOT/WT, а не за LF-normalized Git blob. У WT `core.autocrlf=true`: історичні controller/matcher мають CRLF, course service — mixed EOL. Видалення тільки M66 hunks реконструює captured accepted BEFORE; його LF-normalized bytes збігаються з відповідними accepted HEAD blobs. Це не дозвіл на whole-file normalization.

Finite M66 attributes зберігають frozen own files і CRLF трьох definitions. Лише п’ять already-pure-LF support seams можна зберігати exact LF; controller/matcher/course не переводяться сліпо у `-text`. Наявну сторонню відсутність `.gitattributes` у ROOT не відновлюють. Source-sync v3 підтверджує фінальні M66 public source/definition bytes у ROOT/WT та дозволену shared-hunk lineage. Фінальний staged diff/checkout gate належить окремій Git delivery секції, не підміняється source-sync.

## Ізольований baseline: чотири відомі failures

Fresh M65-base BEFORE regression: 228 tests, 42136 assertions, 4 failures, 1 deprecation; exit 1. У protected 46661 paths змін 0, before/after fingerprint exact. Це не green suite.

Усі чотири failures — historical literal-source expectations у `Tests\Unit\TheoryPracticeSetTokenBankUiTest`:

- `test_manual_practice_inputs_suppress_native_browser_suggestions`
- `test_token_bank_input_keeps_manual_sync_without_registering_word_suggestion_handlers`
- `test_token_bank_source_is_rendered_as_buttons_without_the_plain_slash_text`
- `test_separate_choice_exercise_has_its_own_answers_checking_and_reset_state`

Вони очікують попередні Blade literals після чинного native component refactor. Assertions не послаблюються й старі tests не переписуються заради green. Історичний широкий AFTER run до 1.0.2: 282 tests, 49600 assertions, ті самі 4 failures, 1 deprecation, exit 1. Окремий scoped run 1.0.1: 162 tests, 31870 assertions, ті самі 4 failures, 1 deprecation, exit 1. Ці overlapping runs не складаються в унікальний total та не перейменовуються на full-run фінальних bytes 1.0.2. Відомі чотири literal-source failures залишаються non-PASS; повний suite не оголошено green.

Фактичні фінальні ізольовані перевірки 1.0.2:

| Середовище / scope | Tests | Assertions | Failures | Інші результати | Protected paths / changed |
| --- | --- | --- | --- | --- | --- |
| Served ROOT policy/lineage | 61 | 7739 | 0 | 1 deprecation; 1 historical WT-only skip; exit 0 | 231 / 0 |
| Commit WT data/lineage | 43 | 1341 | 0 | 1 deprecation; exit 0 | 46661 / 0 |

Fingerprints before/after кожного фінального run exact; робочі DB, progress, caches та protected files тестами не змінені. Ці scopes частково перекриваються, тому 61+43 не називаються кількістю унікальних тестів. Чинний JS native factory: ROOT 24/24 і WT 24/24; це два виконання тих самих 24 tests, не 48 унікальних. Вони не замінюють реального натискання кнопок на сторінках.

| Private isolated evidence | Raw SHA-256 |
| --- | --- |
| BEFORE regression 228 | 81aaf9d0fe2e19acfe0aca8a43643f850440c6d0b730929f3f33f44583bf746e |
| Історичний широкий AFTER 282 | 6cf486ef717943516d77b238b967dbec16293e7668f4ad1e0e41a61cd0e4bf5a |
| Scoped 1.0.1 run 162 | f3f12a82743a10f918f569b89acc7173fc1eeaf79924c3b6fa74d2f43b9931fa |
| Final 1.0.2 ROOT 61 | cc5f8027026a3ddf65eed60d47e929df967e72084c1c9e4dfda184b6f34e260f |
| Final 1.0.2 WT 43 | 59aa27022d8dae153a6c57f4dbc2a646a40ea3e5859bf88c1e20a31209b7c403 |

## Фактичне локальне застосування та repeated no-op

Fresh local web/CLI proof, source sync, точний preview review і exclusive backup виконані окремо для застосованої 1.0.1 та для finite 1.0.2 correction. Дозволена ціль — тільки `http://gramlyze.loc`; production requests: 0. Тимчасові GET proof routes були loopback-only, не повертали секретів і не писали в БД.

| Фактичний крок | Updates | Inserts | Deletes / question-family writes |
| --- | --- | --- | --- |
| Початкове transactional apply teaching 1.0.1 | 24: 3 `pages.text`, 3 subtitle bodies, 18 continuing teaching bodies | 3 native practice rows | 0 / 0 |
| Повтор 1.0.1 / fresh AFTER no-op preview | 0 | 0 | 0 / 0 |
| Окреме transactional correction apply 1.0.2 | 1: тільки `text_blocks.id=5757.body` | 0 | 0 / 0 |
| Повтор finite correction 1.0.2 | 0 | 0 | 0 / 0 |
| Фінальний ordinary M66 apply/no-op з fresh full preview під source v3 | 0 | 0 | 0 / 0 |

`preview-v2.json` і exclusive `backup-v2.json` мають однакові raw bytes, 24 updates + 3 inserts. `correction-preview-v1.json` і `correction-backup-v1.json` також exact, 1 body update + 0 inserts. Корекція повторно змінює один із вже застосованих рядків; її не видають за додатковий новий block або повторне повне пересівання.

`aggregate-final-v102.json` — **окрема read-only композиція**, не actual SQL preview і не застосовний apply-plan. Вона поєднує original actual BEFORE preview з exact follow-up correction, щоб реконструювати кінцеву 1.0.2 від первинного незалежного BEFORE. Її `projection_kind` прямо містить `NEVER APPLY this aggregate`. Фактичні writes відбулися тільки двома окремими транзакційними кроками вище; aggregate не передавався writer-у.

`final-readonly-v3.json` звірив усі 46 tables з незалежним BEFORE через точну inverse projection дозволених змін: 21 continuing UK rows, 42 EN/PL rows, три appended native practices; усі інші старі rows і timestamps exact. Після всіх фінальних HTTP/browser взаємодій повторний `final-readonly-v4.json` підтвердив ті самі postconditions. `new_question_families=[]`, metadata transitions 0. Збережено 326 visual і 2456 frozen file hashes. Сам postcondition audit: DB writes 0, HTTP requests 0; браузерне приймання ним не виконується.

## Фінальні read-only HTTP, consumers, metadata та cleanup

`live-pages-v2.json`: усі три actual local URLs дали HTTP 200 без redirect; 383 SSR learning fields перевірені проти кінцевого master/patch і 3 нових native practice bindings. H1/SEO, visible basic, detail ownership/fragments, 18 власних tasks, widget payloads та navigation відповідають кінцевій projection. SQL access: false, content writes 0, production requests 0. Це свіжа HTTP/SSR перевірка, не браузерні кліки/видимість.

Три expected SEO і LearningResource descriptions незалежно перераховані на projected Page/TextBlock models через чинні `PageMetadata::theory` і `AddStructuredData::pageDescription`; вони exact незмінні між author-projection v5 (1.0.1) і v6 (1.0.2). Expected descriptions не скопійовані з фінального SSR.

Фінальний `after-old-consumers-v2.json` авторизований саме проти `final-readonly-v3.json`: 40/40 actual local HTTP 200 та exact BEFORE comparisons, 7 saved-test declarations, 456 старих questions, 23 source hashes, всі 46 SQL tables unchanged during HTTP. Перевірено standalone SSR/JSON/compose, page-local tests, course lessons, category та neighbor pages; accepted M65/Advanced Gerund and Infinitive Patterns збережені. Нові M66 theory targets не зараховуються до unchanged neighbors. Audit тільки read-only; authenticated/progress submission не виконувався. Історичний v1 consumer receipt збережений окремо й не перейменований на final 1.0.2.

Обидва proof routes видалено після apply/no-op. Для кожного окремий anonymous loopback GET повернув 404, без redirect; routes file exact повернуто до foreign-inclusive snapshot. Raw routes BEFORE = AFTER: `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Налаштування Apache/MySQL/hosts і production не змінені.

| Private final evidence | Raw SHA-256 |
| --- | --- |
| Source sync v2 — застосована 1.0.1 | 9baff5e5bb47d10e9cbb28e3bd9a8685cea6140d99b557115c3e9d60a5dfbe24 |
| Source sync v3 — кінцева 1.0.2 | fd7f8553a9b7dbe4de7dfb867f3464e0d3055407b71a41790194d3c0ea09d855 |
| Author projection v5 — 1.0.1 | 92460ed2dcbf7a7896dd4e542aa78e289520d8705b3be255d5a52cc6f7c98692 |
| Author projection v6 — 1.0.2 | dcef670c4be0a29465b666ec0545601b5390eaf543d7da3b1cd7a70c5f52c898 |
| Preview v2 / backup v2 — actual 1.0.1 | 93da4a63ff7b0d6e9b1256900d3c44235f66e7bdd06c61f0c71f21e62c65895c |
| No-op preview v1 — 1.0.1 | 975d7267c9e9ab7f815974cb6d21aa2786c7daa45bc1a2889d92cab8abcd6dd7 |
| Correction preview v1 / backup v1 — actual 1.0.2 | 6e683d3b46fc25636403d2208a959fd72075b4271e26e3c1a5462a283ab8ecf7 |
| Aggregate final v102 — read-only, не apply-plan | f41e234bac9b9ae829b273a73197e095ff544eb058daee04831546ed4439673e |
| Final read-only v3 — 46-table audit | 8073a9d7e58d0b6ead22bdaebf51987d112642e3654f41789a439592a7cccfeb |
| Final read-only v4 — після всіх UI/HTTP взаємодій | b4ad237a46c655f71f1a804887571c1f8f12bbe97f07fe86e2aa8a320113abf2 |
| Final full no-op preview v1 — source v3 | 121821a4e46da0a928e1af04b8b3566ff3f680e8fa22e44f653f797346140a42 |
| Live pages v2 — final HTTP/SSR | 018808b84429e0b318e3f74181c7183dd4733727635cc779bb22443a1317bc17 |
| Old consumers v2 — final 1.0.2 | db8d3bc5e2dcf9a86bf4831bc8057963cdccc5ef5f4d054139e1e6603ce75777 |
| M66 sentence builders v2 — 3/3 | b8577acdbd26d09275196b8ff4b5b690532ca02b1d144bb6338c8c424a775a86 |
| Registry sentence builders v2 — 121/121 | 76059150ab4b6bf3fefa4200697dd1b08a26a5b4abbb9b753f156a077b8039c2 |
| Proof 1 cleanup — nonce 966da… | 00ea957683f9a6f77fc6d5cde398a326f5ad7a800e37cebf4782186436f4e8de |
| Proof 2 cleanup — nonce 76e75… | e246426318b91099706691a4923e3e4343896f3d5da416b82131da2413957021 |

## Browser acceptance — фінальні bytes 1.0.2

Перевірено реальні сторінки в IAB через видимі UI controls і read-only DOM observations; без прихованої мутації Alpine state, очищення global localStorage, особистих cached attempts або progress submissions. Browser zoom не змінено. Після роботи повернуто світлу тему й початковий viewport; вкладку Verbs + Gerund залишено як deliverable.

Матриця — рівно 18 станів: три уроки × 1440×1000 / 390×844 / 320×844 × light/dark. У кожному стані actual DOM підтвердив потрібний viewport, один правильний H1, три власні exercise groups, один sentence builder, loaded fonts і досяжний footer. Learning-content overflow — 0/18. Animated shell overflow спостерігався в межах 0–13px, окремо від навчального контенту; незалежний BEFORE мав 0–15px. Це не твердження, що кожен AFTER shell pixel успадкований. Справжній content overflow нерозривної Bare формули в 1.0.1 явно задокументований, виправлений новою 1.0.2 та повторно перевірений; CSS не маскував його.

| Реальні UI перевірки | Фактичний результат |
| --- | --- |
| Власні tasks | 18/18 reviewed answers прийняті |
| Select/choice options | Усі 32: Bare 6+4, Comparison 6+4, Verbs 6+6; correct прийняті, кожний distractor відхилений |
| Plain feedback | Після Check реальний DOM показує plain-text відповідь/переклад/пояснення; немає literal span/br або double-escaped markup |
| Inputs manual/tokens | Усі шість inputs у двох режимах на 1440 і 390px: 24 task/mode/size cases |
| Accepted variants | Усі 9 явних canonical/alias значень прийняті, включно з didn't, help me to open і likes checking |
| Error/retry inputs | 39 observations: 3 empty-both checks, 6 partial, 12 wrong включно з повторами, 12 grammatical-but-noncompliant включно з повторами, 6 edit-correct |
| Recheck/reset | Повторний Check не додає score понад 2/2; reset очищає власну групу |
| Mixed/delete/return/rebuild | Окрема реальна послідовність Bare: tokens → manual edit → token, видалення wait., повернення кнопки, перебудова з повернутого токена й усього bank |
| Незалежність scores | Після input reset selects/choices зберегли окремі 1/2; додатково кожна з шести select/choice груп перевірена на 2/2 і повторне 2/2 |
| Details | Усі 3 initial closed; Enter відкриває, Space закриває; exact owner, direct fragment і reload; повний стабільний текст без clipping на desktop та 320px |

Ці counts не видаються за повний cross-product кожної можливої retry/mixed/reset послідовності для кожного task. Шість різних grammatical-but-noncompliant input values перевірено по два рази; це 12 attempts, не 12 різних речень. Різниця між граматичною правильністю та невиконанням конкретної умови явно присутня у feedback.

Кожний новий widget починався з видимого 1/5 до відповідей, мав UK умову й справжній word bank. Усі 15 різних selected questions пройдено через BANK buttons з точними регістром і multiplicity, Next та фінальним restart control. Обрана chip не використовувалась як token locator. Фактичний client-shuffle порядок:

| Widget | Порядок question IDs у браузері | Завершення |
| --- | --- | --- |
| Bare Infinitive, desktop | 40109 → 40107 → 40111 → 40112 → 40110 | 5/5; після reload відновилося 5/5 |
| Gerund vs Infinitive, desktop | 40051 → 40056 → 40047 → 40050 → 40052 | 5/5 |
| Verbs + Gerund, 390×844 | 39692 → 39690 → 39689 → 39691 → 39688 | 5/5 |

Fresh state та restored own-widget state розведені; жодних особистих кешів не очищено. Фінальний видимий Verbs + Gerund мав scores 2/2 у кожній власній групі та 5/5 у widget.

Приватний compact browser receipt `browser-final-v102-v1.json`: SHA-256 `f4f5fd0cef94be5781234eef43d22391ad4678da3f9879818ed71e58b914f35a`.
Фінальний screenshot `browser-final-v102-widget.jpg`: SHA-256 `c76d1a27f56c4aacb94d373af662b54d82d67c643ea4547678afb339ca385785`.
Обидва залишаються поза Git. Print, no-JS та WCAG — `not_checked`, не PASS і не твердження про відсутність підтримки. Чотири historical literal-UI failures залишаються non-PASS, без редизайну або послаблення assertions.

## Git delivery

Фактичний staged scope — 63 файли: нові M66 runtime/diagnostic/test/artifact files, рівно три definitions, вісім мінімальних shared integration seams, finite `.gitattributes` і цей report. Виключені сторонні dirty files, `.env`, storage/private proofs, screenshots, backups, caches, generated assets та тимчасові routes. Перед commit пройдено staged diff/scope review, staged і working `git diff --check`, 21 frozen artifact hash/length перевірку, 35 PHP syntax checks та strict UTF-8/LF/EOF перевірку 51 нового finite файла. Усі 63 index blobs збігаються з відповідними робочими bytes: 59 raw і 4 історичні normalized paths; whole-file EOL rewrite не виконувався.

Доставка — один scoped commit і normal push у `codex/seo-m66-verb-patterns-core-standard-view`, з незалежною перевіркою remote SHA == HEAD. Повний SHA та результат remote verification подаються у фінальному повідомленні. Main/PR/production/deployment не змінюються.
