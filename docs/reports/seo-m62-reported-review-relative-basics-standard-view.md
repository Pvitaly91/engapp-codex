# M62 — Reported Speech Review та базові Relative Clauses

Три UK уроки редакції 1.0.0 застосовано до перевіреної локальної БД gramlyze.loc і прийнято на реальних сторінках. На кожній сторінці є 2 selects, 2 meaning choices, 2 manual/token inputs та окремий тематичний конструктор, фактично пройдений до 5/5. Канонічний дизайн, frontend і grader не змінено. Нижче відокремлено виконані перевірки, чинні обмеження та Git delivery, який на момент підготовки цього звіту ще не виконувався.

## Сторінки, база та межі

| Сторінка | Actual Page / category | Technical practice level | OLD → AFTER blocks |
| --- | --- | --- | --- |
| [Advanced Reported Speech](http://gramlyze.loc/theory/reported-speech/advanced-reported-speech) | 283 / 3, reported-speech | B2 | 2 → 7 |
| [Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/relative-clauses) | 202 / 34, clauses-and-linking-words | B1 | 7 → 8 |
| [Defining Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/defining-relative-clauses) | 31 / 34, clauses-and-linking-words | B1 | 7 → 8 |

Exact owner identities у тому самому порядку:

- `Database\Seeders\Page_V3\ReportedSpeech\AdvancedReportedSpeechTheorySeeder`.
- `Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsRelativeClausesTheorySeeder`.
- `Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder`.

Accepted base: `17572e80bad9b968fd40f394d012cb0414a35ccf`, завершений M61. Робоча гілка: `codex/seo-m62-reported-review-relative-basics-standard-view`. Served ROOT і document root встановлені заново: локальний Gramlyze та його public directory, не інший worktree. Source WT — `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.

Запис охоплює лише ці три UK Page owners. Категорія 34 в actual DB зберегла назву `Clauses and linking words`; окрема українська source/localization назва теж збережена, категорію не перейменовували. Окреме дерево relative-clauses з Complex Relative Clauses не підставляли як ancestry. Category learning descriptions, EN/PL, курси, окремі тести, банки й особистий прогрес не є цілями цього пакета. Main, PR, merge/rebase, force push, workflow dispatch та production deploy не виконували. HTTP/browser acceptance — тільки gramlyze.loc; `.com`/`.ub` не відкривали.

## Незалежний BEFORE та author projection

Fresh source/DB/HTTP BEFORE має SHA `c1d652a764102b5abc9b5dd8a8c9226cd79126302dce58a69e52aab7a9b3c55d`. До редагування зчитано actual Page IDs, обидві category ancestries, 2/7/7 початкових slots, titles/H1, subtitle, metadata, anchors, питання та їхні фізичні relations. Три theory й три відповідні course GET дали HTTP 200; усі 46 SQL fingerprints до і після BEFORE HTTP однакові. Збережено 326 visual files та 2390 попередніх frozen source files.

Початковий Advanced справді містив hero та службовий B2 focus box про course anchor. Relative і Defining мали hero, п’ять HTML boxes без заданого level та navigation-chips. Непідтверджені припущення про однаковий six-slot layout M61 не застосовували.

Погоджений master 1.0.0 проєктується незмінним serializer у 23 blocks: 7/8/8. Pure projection SHA `43e88842bf895c83b2dd45d672827230edccb81471d0b33df71281bcb724653c`; найбільший UTF-8 body — 17 436 bytes за SQL TEXT capacity 65 535. Expected native fields і metadata отримані до apply, не з AFTER GET. Definitions семантично дорівнюють цій проєкції; raw EOL/EOF нормалізовано до freeze.

Нові provenance, content_type та diagnostic descriptors мають M62 labels. Старі frozen описові недоліки M60/M61 не виправляли заднім числом. DRAFT artifacts залишалися поза active release discovery.

## OLD → стандартні компоненти та metadata

Індекси в таблиці — 0-based source slots; continuing UUID, порядок і початкові columns/levels збережено.

| Owner / slot | OLD type / heading | AFTER type / heading | Незмінні column / level |
| --- | --- | --- | --- |
| Advanced / 1 | box / B2 focus | forms-grid / null | left / null |
| Relative / 1 | box / 1. Що таке relative clause | usage-panels / null | left / null |
| Relative / 2 | box / 2. Основні слова | comparison-table / null | left / null |
| Relative / 3 | box / 3. Subject clause vs object clause | usage-panels / null | right / null |
| Relative / 4 | box / 4. Типові помилки | mistakes-grid / null | right / null |
| Relative / 5 | box / 5. Коротке правило | summary-list / null | right / null |
| Defining / 1 | box / 1. Коли використовуємо defining clause | usage-panels / null | left / null |
| Defining / 2 | box / 2. Типова форма | forms-grid / null | left / null |
| Defining / 3 | box / 3. Як змінюється значення | comparison-table / null | right / null |
| Defining / 4 | box / 4. Типові помилки | mistakes-grid / null | right / null |
| Defining / 5 | box / 5. Коротке правило | summary-list / null | right / null |

Рівно 11 type/heading transitions включено в reviewed allowlist. Це не прихований body-only update; жодного continuing level або column не підвищено механічно. Hero Advanced лишився header/B2 з display B2; обидва Relative hero — header/B1 з display B1-B2. Старі navigation slots двох Relative залишилися footer/null з тими самими навчальними items/order/current flags. Serializer provenance означає, що raw navigation body не оголошується byte-identical.

Advanced отримав п’ять нових blocks: usage-panels/B2/left, mistakes-grid/B2/left, summary-list/B2/left, власну navigation-chips/footer/null та practice-set/B2/left. Це нові дані, а не зміна старих right columns. Інші дві сторінки отримали тільки appended practice-set/B1/left. Загалом сім inserts, DB IDs 12728–12734; practice IDs 12732, 12733, 12734. Детерміновані practice UUID:

- Advanced, index 6 / sort 7: `d080362c-1e0e-5f48-afb5-2f1fb6c399f6`.
- Relative, index 7 / sort 8: `1f1197e5-20ab-5895-b7f5-0157d127524e`.
- Defining, index 7 / sort 8: `9b99274d-d53b-5aea-9d40-6ec27ade411f`.

Не додано незалежних m62-section/detail/styles або іншого renderer. Незмінними залишилися Blade, CSS, frontend JS, шрифти, header/sidebar/background та grader. Всі дані проходять чинну theory.show → content-block → TheoryContentSource → TheoryContentRenderer → theory.content → shared components межу.

## Редакційні зміни та basic/detail

Advanced перетворено з технічної заглушки на самодостатній B2 урок: намір мовця → конкретна reporting-модель → виконавець → часові/просторові орієнтири. Короткі самостійні мінідіалоги охоплюють clause, to-infinitive, object + to, -ing та preposition + -ing. Promise/offer/refuse/agree, advise/remind/warn, admit/deny/suggest, apologise for/insist on не оголошуються взаємозамінними. Нейтральне said не перетворюється без контексту на зізнання/обіцянку; порада, згода чи пропозиція не доводить виконання. Tried to persuade відрізнено від успішного persuaded. Видимий basic зберігає різницю didn't promise to / promised not to і denied doing / admitted not doing. Backshift, no-backshift та time/place узгоджені з M61; шість попередніх уроків не дублюються великими таблицями.

Relative виправляє конкретне хибне правило про обов’язкове видалення he після who. У повних реченнях задано, хто телефонує, кому телефонують і хто є головним підметом; object who може потребувати іншого підмета he/she. Справжнє дублювання subject або object показано окремо. Ролі перевіряються всередині relative clause незалежно від ролі іменникової групи у головній частині. Where залежить від місця дії, не від будь-якого place noun; whose не зводиться лише до людей та не замінюється who's. Which у defining і that для людей не заборонені. Subject agreement, головне питання та проста defining object omission пояснені без глобального видалення займенників.

Defining зосереджений на виборі референта або підгрупи. Essential information не означає, що кожне видалення clause робить речення неграматичним. Склад групи й відоме співрозмовникам задано явно; з граматики не виводиться невказана кількість сестер або існування іншої групи. Англійські defining commas не копіюються в український переклад; сусідня кома може мати іншу функцію. Старий білінгвальний/task-switching приклад замінено нейтральною авторською ситуацією, без заяви, що наукову тезу спростовано.

Корисні старі базові ідеї та навігацію збережено; не кожне старе спрощення названо помилкою. Source-derived основа та нові самостійні приклади/доповнення розмежовані в editorial BEFORE/AFTER. Немає зовнішніх скопійованих exercises, вигаданого рецензента, word quota чи ranking guarantees.

Усі основні правила, переклади й короткі застереження видимі в basic. Три meaningful details мають конкретних власників, а не runtime word-count threshold:

| Detail | Owner |
| --- | --- |
| advanced-reported-hypothetical | advanced-reported-decisions /sections/3 |
| relative-two-subject-positions | relative-structure /sections/2 |
| defining-preposition-position | defining-patterns /items/1 |

У CUA кожний спочатку закритий без fragment; Enter і Space незалежно відкривають/закривають потрібний owner. Deep link та reload відкривають тільки його; повний текст доступний без clipping. Перевірені fragment IDs: `advanced-reported-hypothetical-detail-0`, `relative-two-subject-positions-detail-0`, `defining-preposition-position-detail-0`.

## Власна практика та межі оцінювання

Разом 18 власних завдань: на кожній сторінці 2 form selects, 2 contextual meaning choices, 2 inputs з ручним введенням і банком токенів. Шість inputs — шість завдань, не дванадцять. Шість canonical full answers плюс чотири явно дозволені aliases дають 10 accepted variants: apologised/apologized, who/that у заданій subject-моделі та that/which/zero у заданій object-моделі. Не створено декартових комбінацій непередбачених alternatives. Нові own tasks не дублюють selected widget keys.

Actual unchanged shipped factory пройшов 23/23 offline JS tests: 18 tasks, 36 options, усі 10 variants, шість manual і шість token controls, deferred translations шести meaning choices, empty/partial/wrong/repeated wrong/edit correct/recheck/reset/mixed/delete-return-rebuild та independent scores. Working .env, Laravel bootstrap, DB, HTTP і network calls відсутні; engine не змінено. Receipt SHA `8cb0fe205a413a4e6c5c3abef4891c25e07fe05aefb74ec52d515472c56fb1a2`. Це окрема offline перевірка, не заміна browser acceptance.

У реальному браузері прийнято всі 18 reviewed answers. Для всіх шести inputs виконано manual і BANK tokens на desktop 1440 та mobile 390: 24 task/mode/size cases. Окремо прийнято всі чотири aliases, тобто 10 унікальних accepted input variants разом із canonical. Перевірені empty, partial, finite wrong, repeated wrong, edit correct, repeated Check без подвоєння score, Reset, змішане редагування, видалення/повернення токена й rebuild. Scores трьох груп незалежні.

Прочитано actual feedback усіх 36 select/choice options та шести finite wrong inputs: повні EN + UK + пояснення, без literal HTML/entities/double escaping; declared wrong inputs не отримують бал. У meaning choices переклад відкривається після Check. Поля x-text створено plain text від початку; x-html або runtime-stripper не додавали. Невідомий довільний typo/partial може отримати чинний generic fallback без авторського перекладу — це збережене обмеження, не нова обіцянка engine.

Пунктуаційна межа перевірена фактично, не прихована: UI прийняв `The box, that Oksana labelled, contains cables.` за чинної default normalization. Отже, input не перевіряє defining/non-defining лише комами. Відповідне значення перевіряє choice із явним контекстом і різними українськими відповідями на A/B stimuli; видимі правильні речення все одно мають правильну пунктуацію. Grader заради цього уроку не змінювали.

## Окремі конструктори — 15 reused questions, 5/5 кожний

Незалежний contract: `docs/content/theory-sentence-builders/m62-reported-review-relative-basics.v1.json`. Точні V3 Polyglot banks і CEFR встановлені з source та actual DB, не вгадані з випадкового matcher output:

| Сторінка / level | Bank basename | Reviewed selected IDs |
| --- | --- | --- |
| Advanced / B2 | PolyglotAdvancedReportedSpeechAllLevelsLessonSeeder | 41427, 41429, 41430, 41433, 41434 |
| Relative / B1 | PolyglotRelativeClausesAllLevelsLessonSeeder | 44656, 44658, 44659, 44661, 44665 |
| Defining / B1 | PolyglotDefiningRelativeClausesAllLevelsLessonSeeder | 30905, 30908, 30910, 30911, 30913 |

Усі — type 4. Actual page-wide inventory: 552 старі питання, 240/168/144. Переглянуто 84 придатні за practice level: 60/12/12; exact contract pools — 36, по 12. Додаткові 48 Advanced legacy B2 rows з hints належать окремому старому банку, який збережено для курсу/тестів. Reviewed inventory не означає quality PASS кожного unselected legacy key: зокрема Following/Students uppercase cases не виправляли масово.

Відібрано 15 reused, створено 0 new questions і 0 new links. Перевірено 118 strict ordered token instances та 40 існуючих theory links; selected hints/verbHints — 0. Регістр та multiplicity контролюються на actual ordered answers, а не на deduplicated shared options. Actual café у 44659 збережено; source cafe відмінність задокументована, не переписана.

Лише чотири snapshot-bound render-only case clones: 44656/answer229364/a3/option128 Who→who; 44659/229391/a5/50 Where→where; 30911/169693/a3/128 Who→who; 30913/169707/a3/128 Who→who. Кожний має raw option SHA, exact owner/locale/category/practice/body guards та whole-collection stale fallback. Клонуються видима UK умова і тільки погоджені answer/option presentation instances; original models, shared option rows, answers, hints, variants і links не мутують. Глобального lowercase, нового grader або copied snapshots для невідомих питань немає. Object who зберігає we, object that — I; старе помилкове правило he не переноситься.

У реальному CUA всі 15 питань пройдено через видиму українську умову, BANK selectToken buttons, Check, Next та завершення. Локатори не брали вже вибрані answer chips. Фактичний client shuffle відрізнявся від frozen SSR order:

| Сторінка | Actual accepted order | Результат |
| --- | --- | --- |
| Advanced | 41433, 41430, 41429, 41434, 41427 | 5/5 |
| Relative | 44656, 44661, 44665, 44659, 44658 | 5/5 |
| Defining | 30905, 30911, 30913, 30908, 30910 | 5/5 |

Dynamic scoped audit: 3 targets, 0 failed/0 writes, SHA `3c1a4e9bfe3a61e73c2e4fb439b3feec8afeab56d49afc557f14e167baa1b46f`. Full registry: 109/109; усі попередні 106 збережені за стабільними полями, SHA full receipt `87fbcebb4a7b6f5924251a487e5f31678041c47404a274c05ebc68f9919154ea`, prior-preservation receipt `02915bdca0671fe98f9ef25ac8bec3a3f99bd3d8b59a0ba46422f68afb4f9173`. Volatile HTTP SHA розглядається окремо; permanent hardcoded cap 109 не додавався. CLI не зараховується як browser interaction.

## Metadata, anchors, internal destinations та consumers

Очікування SEO обчислені чинним сервісом у pure projection до apply:

| Raw title / H1 | Expected title | Expected description |
| --- | --- | --- |
| Advanced Reported Speech | Advanced Reported Speech — правила \| Gramlyze | Advanced Reported Speech поєднує граматичну форму з рішенням про зміст: людина повідомила факт, запропонувала допомогу, пообіцяла чи відмовилася? |
| Relative Clauses | Relative Clauses — правила \| Gramlyze | Relative Clauses приєднують опис до іменника або іменникової групи. |
| Defining Relative Clauses | Defining Relative Clauses — правила \| Gramlyze | Defining Relative Clauses уточнюють, про яку людину, річ або групу йдеться. |

Actual AFTER GET усіх трьох — direct 200, один правильний H1, expected title/description, незмінні canonical/breadcrumb та LearningResource identity/schema types. LearningResource description містить повний погоджений hero intro. Збережений canonical має `.com` origin і точний вихідний theory path; це локальна SEO metadata, не звернення до production і не заміна local navigation URL. Local robots/noindex не змінено.

Незалежний SSR audit перевірив 420 learning fields: 152/144/124, всі переклади/таблиці/структуровані дані; старі anchors 15/19/19, missing 0. На кожній сторінці один owned detail, три own groups по два tasks та один native widget з п’ятьма exact IDs/tokens. Receipt SHA `23cb7e42d7d676a8b5b9890146b5eae450bd1a194ba4c2552c6112e6ecf28c48`.

Advanced navigation веде до шести прийнятих M60/M61 і поточного Advanced; обидві Relative navigation зберігають свої дев’ять items у clauses-and-linking-words, зокрема Non-defining та Omitting. Усі нові/змінені destinations перевірені actual local GET, не через вигадане дерево relative-clauses або короткі aliases.

До source sync інвентаризовано реальних readers Advanced B2 anchor. AFTER read-only consumer gate підтвердив 53/53 direct HTTP 200 та exact BEFORE facts: 3 course pages, 3 course indexes, 13 JSON consumers, 20 SSR consumers, 2 categories, 12 neighbor pages. Зокрема збережено `/courses/sentence-builder-english-b2`, B2/C1/All-levels Advanced saved tests, course-td-advanced-reported-speech JSON і standalone theory-help. Перевірено дев’ять saved tests та всі 552 old linked questions. Access gates не обходили; course presentation зберігає OLD body через clone-only BEFORE reconstruction, а не отримує новий standalone lesson мовчки.

Consumer AFTER SHA `893ed55e10029b246c0dcc84a85b2af1012ebc1f430fc81e10cece9247a2de7a`, independent BEFORE SHA `c3cc41897ec5a331a8a142279b6e0504a48bf630a5ee22fff92619f63e3931e4`. Усі 46 SQL fingerprints навколо цих GET однакові, writes 0. Початковий diagnostic profile надмірно згенерував branded alias і отримав 404; його виправлено до actual source lesson slugs перед успішним BEFORE, без application route change. Це не прихований first-run PASS.

## Guarded apply, backup та repeated no-op

Fresh physical/web/CLI proof виконано лише для локального target, з випадковим nonce і read-only GET/loopback, без секретів або DB writes. Proof SHA `2b36d61cf499a757d19cff863f20622ac4d7f29719b8624dd5c59e430fbeff1c`; freshness перевірена перед preview/apply. Серверну конфігурацію, hosts, .env, ACL і залежності не змінювали.

Reviewed preview digest `522288145774e4684ef518ff38e720a049ad38176baa17fbe77801ade5394abf`, raw SHA `608520779767058687ae7160ba32357bca55477282adc085ba1a2d0a605823a5`. Exact diff звірено з незалежним BEFORE та frozen author projection. До транзакції створено exclusive private backup-v1; його raw SHA той самий `608520…823a5`, оскільки writer зберігає immutable reviewed before-values plan, не full SQL dump. Приватний backup content не входить у Git.

Фактичний transactional apply: 22 updated / 7 inserted / 0 deleted. Updates — 3 Page.text і 19 continuing UK text-block rows, включно з subtitle rows та рівно 11 погодженими type/heading transitions. Сім inserts — тільки перелічені нові native blocks. Questions/options/answers/hints/variants/theory links та інші question families: 0 inserted/updated/deleted. Жодного reseed, migration, truncate, mass update, progress/session clear або автоматичного apply hook.

Виконано два фактичні repeated apply зі status no-op: обидва 0 updated / 0 inserted / 0 deleted. Перший повторив reviewed before-plan після успішної транзакції; другий використав fresh after-preview з digest `306ba446cbdeece10b40130977da4c76b1a8c6d83d414b2a78576759c3b9a696`. Одну спробу другого guard відхилив через web timeout до запису, writes 0; після завершення паралельного HTTP audit повторення успішне. Відхилену спробу не зараховано як успішний no-op. Додаткових unused no-op backup files не створено. Actual after-v1 raw SHA `4da6a9c464b7b4974d34db921816e52a417284e5dfdb1975e1494e84d1bc4cc8`.

Temporary proof route видалено, exact routes preimage відновлено зі сторонніми початковими bytes. Before/after route SHA однаковий: `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Окремий GET nonce endpoint повернув 404 без redirect; physical config hashes незмінні. Cleanup receipt SHA `7d32f57e5b82f3f5aa96f1e4fd8cf62a5505dcc4040ae3ff6648ef3ed11c7807`. Route не входить у commit.

Фінальний read-only audit після всього UI підтвердив 46 tables, 19 continuing UK rows, 32 unchanged EN/PL rows та рівно 7 new blocks. Усі old rows і timestamps exact поза reviewed allowlist; independent BEFORE reconstruction та protected projection збігаються. Нових question families немає, writes 0. Post-UI receipt SHA `6d84f01f7cca76c77e500f2ca6ad53971f3d0e8f13bc2e64f3b605f37cea8aca`. Category learning descriptions, course anchor/references, saved tests, M60/M61, question payloads і personal progress збережені.

## Невізуальна інтеграція та source preservation

У восьми shared files — рівно 12 exact hunks / 15 added lines / 0 removals: canonical source registration, point-panel/correction dispatch, course/standalone preservation та три чинні bank seams. Нові finite package/patch/guard/CLI використовують TheoryNativeContentRelease, TheoryNativeContentPatch і M26 writer; не скопійовано окремий framework або renderer. Профіль явно задає 2/7/7 slots та дві категорії, а не послаблює старі M60/M61 scopes.

Bank presenter працює лише з canonical theory caller, exact owner/locale/Page/category/practice/body та всією перевіреною п’ятіркою. WeakMap-маркер не переноситься на довільний clone. Чужі/неповні/stale collections отримують повний попередній fallback; runtime queries/writes у presenter немає.

Source-sync звірив 31 path між served ROOT і source WT; власні M62 sources/definitions перенесено, незмінені shared primitives та old banks лише звірено. Source-sync receipt SHA `4659247edd8ce7c4f6f2c40c0cb93f7aa5d3e688ab4a820c7aac2d47b8d98390`. Independent reverse-hunk audit реконструює всі 20 shared BEFORE paths окремо для ROOT і WT: 40 exact checks, з них 16 стосуються восьми змінених paths. Обидва різні controller preimages відновлюються exact, чужі ROOT/WT differences та EOL збережені. Private independent review SHA `6617affc02a388a08c93a3d5bf52b9e87672cfc183f42d9c90200a7ef05811c9`.

326/326 visual files та 2390/2390 попередніх frozen files byte-exact. M60 1.0.0/1.0.1, M61 та старі releases/manifests/maps не переписували. Нова finite M62 секція .gitattributes фіксує raw artifacts у WT; стороннє видалення ROOT .gitattributes лишилося видаленням. Secrets, .env, runtime caches, private diagnostics/screenshots/proofs/backups і сторонні незавершені файли не призначені до commit.

## Ізольовані PHP перевірки

Два успішні runs використовували тільки SQLite :memory:, array cache/session та private owned runtime, без working .env/MySQL/progress writes. У кожному збережено 46 661 protected files, changes 0.

| Bundle | Tests | Assertions |
| --- | --- | --- |
| Дев’ять нових M62 classes, включно з 9 bank tests | 56/56 | 8 691 |
| Accepted current-runtime regression bundle | 385/385 | 73 534 |
| Deduplicated union двох runs | 441/441 | 82 225 |

Це не один full-repository rerun. Повторні runs не збільшують унікальний total. У new run є одна чинна deprecation. Receipts: new `0290a8addb7f44dd9d907f4d9d027bd1e65a2facf6604bfe21e906a82b40e6cf`, regressions `a81e2688dfa9c75f88cf88f1f02411259a1c38a97c50cf8eca6e4a5a1593a3a7`, deduplicated identities `d2993acefc8e8f55699c20f783151f44ed1f3d84ddede1f9b38cc6ed79e31656`.

Нові класи в namespace Tests\Feature: M62ReportedReviewRelativeBasicsPackageTest, M62ContentPatchTest, M62NativeTransitionTest, M62LocalTargetGuardTest, M62SentenceBuilderContractTest, M62PlainFeedbackTest, M62ControllerCompatibilityTest, M62CurrentRuntimeCompatibilityTest та M62BuilderSelectionPresentationTest. Covered negatives: foreign owner/locale/category/ancestry, Page vs Category, окремий relative-clauses tree, stale body/plan, непогоджена metadata transition, Advanced course anchor, короткий/чужий pool, duplicate IDs/widget, missing owner, original-model mutation, exact case instance та HTML у plain feedback. Role/punctuation fixtures перевіряють конкретні авторські ситуації, не універсальний лінгвістичний parser. Accepted regressions виконують актуальний shared runtime на M60/M61 та інших accepted даних, зберігаючи історичні assertions/snapshots.

Окремий поточний historical literal-UI run: 6 tests, 23 assertions, 2 PASS / 4 FAIL, exit 1. Всі шість виключені з passing union 441; assertions не послаблювали, старий view/test source exact за historical LF hashes. Чотири відомі failures:

- `Tests\Unit\TheoryPracticeSetTokenBankUiTest::test_manual_practice_inputs_suppress_native_browser_suggestions` — literal locator не знаходить input після чинного component rendering.
- `Tests\Unit\TheoryPracticeSetTokenBankUiTest::test_token_bank_input_keeps_manual_sync_without_registering_word_suggestion_handlers` — той самий старий literal input locator.
- `Tests\Unit\TheoryPracticeSetTokenBankUiTest::test_token_bank_source_is_rendered_as_buttons_without_the_plain_slash_text` — exact старий span-regex не відповідає чинному native presentation attribute.
- `Tests\Unit\TheoryPracticeSetTokenBankUiTest::test_separate_choice_exercise_has_its_own_answers_checking_and_reset_state` — очікує старий literal foreach замість чинного item/options fallback.

Два PASS того самого current run: `Tests\Unit\TheoryPracticeSetTokenBankUiTest::test_token_bank_suggestions_are_rejected_by_the_alpine_state_as_a_second_guard` і `Tests\Unit\TheoryPracticeSetTokenBankUiTest::test_backspace_and_manual_edits_recalculate_used_tokens_from_current_input`. Receipt SHA `6db4df4a97883f0374053f36dd95d3a847c2018ee622829b7bdb16a2a3212ff1`. Ці чотири baseline failures не виправляли редизайном та не називали новими M62 content failures або PASS.

## Реальне browser acceptance та обмеження

MAIN використав computer-use skill, actual локальну IAB/CUA вкладку й read-only DOM для спостережень. Не було hidden Alpine mutation, альтернативного browser driver після safety stop, zoom changes або очищення global localStorage/особистого прогресу. Matrix 3 × 1440×1000 / 390×844 / 320×844 × light/dark = 18 states, actual DOM viewports. Усі сторінки переглянуто до footer; по три own groups та одному linked widget у кожному state. Приватно збережено 18 BEFORE та 18 AFTER full-page captures.

| Width | Main client / scroll | Table / table-region client |
| --- | --- | --- |
| 1440 | 877 / 877 | 825 / 825 |
| 390 | 358 / 358 | 576 / 322 |
| 320 | 288 / 288 | 576 / 252 |

У Advanced таблиці немає; у Relative й Defining одна native table. Learning main не має horizontal overflow у всіх 18 states; table regions мають власний overflow:auto. Whole-document widths за light/dark наведено окремо:

| Сторінка | BEFORE 390 / 320 | AFTER 390 / 320 |
| --- | --- | --- |
| Advanced | 404/390 ; 330/330 | 398/406 ; 329/329 |
| Relative | 401/391 ; 328/321 | 405/397 ; 324/333 |
| Defining | 397/390 ; 324/324 | 398/398 ; 325/324 |

На 1440 BEFORE/AFTER document scroll width 1425. Невеликий variable shell overflow існував до M62; не весь AFTER overflow оголошено незмінно успадкованим і не заявлено blanket zero-overflow для документа. CSS/layout fix поза scope не робили. Перші dark desktop footer reads застали smooth-scroll у русі; окремі settled checks досягли всіх footer без зміни верстки.

Browser console errors — 0. Одне Relative navigation CDP timeout відновлено fresh snapshot, який підтвердив завантаження правильної сторінки; це не application 500. Невідповідний role-based summary locator замінено actual native DOM locator, не зміною застосунку. Actual plain feedback, accepted inputs/aliases, case/multiplicity, widget shuffle та detail interactions описані вище. Browser receipt SHA `65c524dfdda6f0fd14c800f4cab68a5478463b36a6476191c15ced07b37cd2c4`; BEFORE geometry receipt `679fc01cc8167c97040af0f426f2857b310b49d8590f781612643bd6993434aa`.

Окреме actual DOM читання на фінальній Defining сторінці підтвердило computed H1 family `Archivo, sans-serif`, size `36px` та body family `Manrope, sans-serif`. Два реально підключені локальні bundles `catalog-public-Cy8aaDRf.css` і `catalog-public-CgUm4gux.js` повернули GET 200 без переходу за redirects. Перший sandbox network attempt повернув 000; явний elevated read-only loopback retry — 200. Це перевірка computed families і двох assets, не доказ завантаження зовнішніх font files чи всіх network resources. Supplement receipt SHA `dc7eac1ab65e3adc52be0ede2755860b15914c97d2b1a3eeb2876d0a788353a7`.

Final deliverable — Defining Relative Clauses, light/top, чистий local URL, viewport override reset; private screenshot final-defining-content.png. No-JS, print, повний WCAG та blanket external font/network audit не перевірено: це не PASS і не твердження про відсутню підтримку. Особисті restored cached attempts не очищали й не примушували до fresh-session сценарію. Generic arbitrary-typo feedback і comma-insensitive default input matching залишилися чинними обмеженнями.

## Джерела та доступ

[British Council reporting verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-reporting-verbs), [reported statements](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-statements), [defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-defining-relative-clauses) та [Google helpful-content guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) прочитані як direct editorial main bodies. Learner comments не використовували як нормативні правила. Формулювання видавця про persuade не скопійовано як доказ згоди або виконання без контексту.

Сім exact Cambridge direct opens повернули 403. Прочитані official publisher-indexed bodies, а не вигадана direct-success/redirect перевірка: [relative pronouns](https://dictionary.cambridge.org/us/grammar/british-grammar/relative-pronouns/), [non-defining relative clauses](https://dictionary.cambridge.org/grammar/british-grammar/non-defining-relative-clauses), [suggest](https://dictionary.cambridge.org/grammar/british-grammar/suggest), [deny](https://dictionary.cambridge.org/dictionary/english/deny), [refuse](https://dictionary.cambridge.org/dictionary/english/refuse?topic=rubbish-and-waste), [apologize](https://dictionary.cambridge.org/dictionary/english/apologize), [persuade](https://dictionary.cambridge.org/dictionary/english/persuade). Для relative pronouns journal додатково фіксує publisher-indexed URL із encoded closing bracket; exact requested defining-and-non-defining URL не оголошено доведеним redirect до знайденої non-defining статті. У refuse відокремлено verb sense від waste noun; corpus/learner content не стає нормативним доказом. Повний actual URL/access/question журнал — у versioned editorial artifact.

Нові приклади та own tasks самостійні; зовнішні exercises не копіювали. Немає SEO word quota, прихованих копій, fake authors/reviewers, certification, rating/FAQ schema або вигаданих ranking/CTR gains.

## Frozen raw checksums

| Артефакт | SHA-256 |
| --- | --- |
| Master 1.0.0 | `d269da15989c7e3c89eb3cda133901b7333e66b82cf2d1fe1dd2596c522353fe` |
| Public M62 BEFORE | `59cddc12d615922751eaac37e35fb40fcc0a44730dda1b535a831fbe127b35b1` |
| Native content patch | `ba6bab4ae430976097d60835bb5ad5dffdf927211e9a9c27b81b8f5ee76c1904` |
| Builder contract | `77a99a050162e14ec8370545e2f291eeeb90fdc26ca47285c65aced08a2004c6` |
| Bank audit | `8fdc925b5d6c83604131c8bb833bc4aebe4bab5fe7eb45bf123a92aa9448cd65` |
| Readable UK edition | `cad77aa9b8d15a5e49546a320fbf75990d9df15538f458b25376000e1c2f50f3` |
| Editorial BEFORE/AFTER | `4504e9a02b835edd6cf0e69f24988f9a694a3f7aa709430db480f932dd841255` |
| Component/basic-detail map | `93b2ae2602118af248f795ad112f3fc1b7872dcc3bf4c1687f43a0fcd1000416` |
| Advanced definition | `cb2aee8b504acdd87273cb79bf4f3468836653bd0d8e05c70f156c9fac4d92ee` |
| Relative definition | `713fbe7ab9fccdc42fd547d06f2dc9fb411a26000b3c1a257ce0af9864649334` |
| Defining definition | `a006252885a78e5db85d7aea6992501965d5030ae0b6cc5c90b29c5f611d89f8` |
| Finite bank helper | `aa549f10ade64f7383c6c7c5928c01f24e54b24ec2dad8b9a9fcf55ac64f7773` |
| Offline JS suite | `3339ff3e6d4914e8d1443a93605db5ba1c6769b189ca300ac7459e15eacc98ba` |

Checksum manifest `m62-reported-review-relative-basics-checksums.v1.json` має raw SHA `7bcc2beb8c7660248dd0421939f18da3e2b20abe01057eab41edd50d0c8beb2b` та 11 artifact hashes; helper/JS перевірені окремо. Історичні manifests не переписували. Private evidence hashes вище ідентифікують перевірки, але їхні runtime/backup/screenshot contents не додаються до Git.

## Git delivery

Branch: `codex/seo-m62-reported-review-relative-basics-standard-view`; base `17572e80bad9b968fd40f394d012cb0414a35ccf`. Усі контентні, apply/no-op, preservation та live acceptance outcomes вище виконані. На момент написання ще належить MAIN: scoped staged diff review, git diff --check, фінальна frozen-source validation, scoped commit, normal push та незалежний origin SHA == HEAD. Цей текст не оголошує їх успішними наперед.

Фінальний delivery commit визначається як commit, що вперше додає цей звіт; його повний SHA та незалежний origin==HEAD результат надаються після commit/push у фінальній відповіді. Другого report-only commit для самопосилального SHA не потрібно. Деплой потребує окремого дозволу; commit/push не є таким дозволом.
