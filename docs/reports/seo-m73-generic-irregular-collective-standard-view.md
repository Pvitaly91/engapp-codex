# M73 — Generic Reference, Irregular Plurals, Collective Nouns

Фінальний звіт локального впровадження M73, content release 1.0.0.

Дата: 2026-10-21. Реалізація й усі справжні HTTP/браузерні звернення — тільки `http://gramlyze.loc`. Production, main, PR, merge, force push, workflow dispatch і deploy не використовувалися. Звіт не містить runtime identity, секретів, DB credentials, nonce, приватних SQL rows або screenshots.

Локальні content apply 1.0.0, HTTP/SSR, registry, повне браузерне interaction-приймання всіх трьох уроків, fresh after-UI SQL postconditions і повторний no-op завершені. Перед delivery перевірено exact staged implementation scope та frozen ROOT/WT/index bytes. Commit і normal push виконуються після включення цього звіту; повний SHA і незалежне remote підтвердження наведено у фінальній відповіді.

## База, гілка та підтверджені сторінки

Accepted база: `cb55b329827e52a56cb6d41edb21e70768cf86f4`, M72 teaching 1.0.0 + bank-only 1.0.1. Базова гілка — `codex/seo-m72-partitives-negatives-geography-standard-view`; робоча — `codex/seo-m73-generic-irregular-collective-standard-view`. M72 й попередні accepted роботи не відкочувалися.

Усі три owners — UK theory, category ID 16, ancestry `imennyky-artykli-ta-kilkist`. Slugs, raw Page.title, category, URLs, levels, metadata та first-strong H1 збережені; display level не підміняє SQL level.

| Сторінка | Page / Category | Practice technical / hero display | Чинний H1 |
|---|---|---|---|
| [Generic Reference](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/advanced-articles-generic-reference-the-rich-a-tiger-people) | 80 / 16 | NULL / B2–C1 | Generic Reference: the rich, a tiger, Ø people |
| [Irregular Plurals](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/irregular-plurals) | 86 / 16 | A2 / A1–A2 | Irregular Plurals |
| [Collective Nouns](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/collective-nouns) | 84 / 16 | B1 / B1 | Collective Nouns |

Повні identities:

- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder`
- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityIrregularPluralsTheorySeeder`
- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityCollectiveNounsTheorySeeder`

Generic raw title — «Generic Reference — Узагальнення», але actual H1 довший. Його підтверджено independently captured subtitle та BEFORE HTTP, не вигадано з Page.title. «Рівень B2» для редакційного фокусу Generic не перетворюється на SQL equality filter: practice level лишається NULL.

## Редакція та межа source findings

Збережено 26 continuing structured source slots: 13 / 7 / 6, їхні native types, columns, порядок, source indices, UUID, levels, tags та navigation. Додано лише два append-only practice-set для Irregular/Collective, після наявних slots; footer не переведено в інший type і не використано замість практики. Три subtitle rows continuing.

- Generic Reference розрізняє загальну характеристику, kind/type як ціле та визначену групу. A/an, the й bare plural не подано як вільнозамінні форми для кожного predicate або лише «рівні формальності». Generic не прирівнюється до all/every без винятків. The + adjective, nationality names, the same, only/main та the unknown мають окремі функції; приклади не приписують національностям професію, характер чи поведінку. Для protected-species bank key явно задано вигадане правило, не реальну норму права.
- Irregular Plurals зберігає всі вихідні noun pairs, але доповнює контекст this/these, that/those, is/are/was/were та узгодження. People, formal persons і peoples за значенням «народи» не оголошені взаємозамінними. Fish/fishes не оцінюється за прихованим «тільки species» правилом; для device mouse можливі словникові варіанти, тому їх не використано як однозначний distractor. Sheep/deer не додають -s у цільовій звичайній моделі. Вимову чи зоологічні факти не вигадано.
- Collective Nouns розрізняє одну групу, її членів та кілька груп; agreement залежить від задуму й різновиду англійської. BrE singular/plural і звичайніша AmE singular не перетворені на абсолютну заборону альтернатив. Police як назва поліцейських має plural agreement; одна особа — police officer. Staff personnel, staff member і окремі словникові staffs/staves meanings не змішані; B1 exercise не навчає regional one-person staff. Family/families, group of…, company за різними значеннями та couple мають явні межі.

Первинні редакційні джерела й реальні способи доступу зафіксовані у master/editorial JSON. Для Generic: [British Council definite article](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/definite-article), [indefinite article](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/indefinite-article), [Cambridge nationality names](https://dictionary.cambridge.org/grammar/british-grammar/nationalities-languages-countries-and-regions), [Cambridge Core, §2–2.1 про generics/kind-reference](https://www.cambridge.org/core/journals/canadian-journal-of-linguistics-revue-canadienne-de-linguistique/article/acquisition-of-kindreference-by-arabic-chinese-and-turkish-l2-learners-of-english/D48C35B4E7DF513B6A09050F856FF6CF). Для Irregular: [British Council count nouns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/count-nouns), [demonstratives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/demonstratives), [Cambridge person/persons/people](https://dictionary.cambridge.org/grammar/british-grammar/person-persons-or-people?q=person), [fish](https://dictionary.cambridge.org/dictionary/english/fish), [mouse](https://dictionary.cambridge.org/dictionary/english/mouse?q=mouse_1). Для Collective: [British Council group nouns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/group-nouns), [BrE/AmE grammar](https://takeielts.britishcouncil.org/blog/british-or-american-english-ielts), [Cambridge plural nouns](https://dictionary.cambridge.org/grammar/british-grammar/plural-nouns), [staff countability](https://dictionary.cambridge.org/us/grammar/british-grammar/countability-staff), [Collins staff](https://www.collinsdictionary.com/dictionary/english/staff).

Direct editorial/research body, publisher-indexed body та failed direct fetch розділено. 403 не названо успішним direct читанням або redirect. Publisher exercises/keys, learner comments та corpus snippets не копіювалися як авторські вправи або універсальні правила. Нові приклади й переклади авторські; перевірка агентна, не external human review.

Механічні/адаптерні source findings обліковані окремо від мовних висновків. До apply браузер Generic реально показав 0/4 після вибору правильних ordinal позицій: старий adapter не перетворював ordinal values на word keys. Старі три source input slots реально мали denominator 0/2: empty canonical третього не був graded. Це не твердження, що порожній input отримував бал. Старий builder показував off-theme fallback; не-current footer destinations були #. Жоден source-only casing finding не названо доведеним native visible defect без actual formatter/DOM перевірки.

Повні maps:

- `docs/content/m73-before-source.v1.json` — independently captured source BEFORE.
- `docs/content/m73-generic-irregular-collective.components.v1.json` — component map, task map та counts.
- `docs/content/m73-generic-irregular-collective.editorial.v1.json` і `docs/content/m73-generic-irregular-collective.editorial.v1.md` — OLD→AFTER, author/source review, context/key decisions.
- `docs/content/m73-generic-irregular-collective.v1.0.0.uk.md` — повний readable UK author layer.

Details — 0 / 0 / 0. Є 46 finite decision records: Generic 23, Irregular 8 aggregate records, Collective 15. Це не 67 рішень і не квота на disclosure. Потрібні короткі пояснення, умови, переклади й приклади видимі в basic. Runtime word-count threshold немає. Keyboard/deep-link приймання відсутніх details — N/A, не PASS.

У Generic чотири точні author data pointers purple→blue проєктовано до freeze, бо closed native usage accent model purple не підтримує. Hero purple не змінено. Це finite content choice, не CSS/renderer/runtime правило або redesign.

## Практика: retained/corrected/new

| Урок | Збережені/corrected навчальні функції | Нові own tasks | Own graded AFTER | Select / meaning / input / graded rephrase | Demo |
|---|---:|---:|---:|---|---:|
| Generic Reference | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 ungraded |
| Irregular Plurals | 0 existing practice tasks | 6 | 6 | 2 / 2 / 2 / 0 | 0 |
| Collective Nouns | 0 existing practice tasks | 6 | 6 | 2 / 2 / 2 / 0 | 0 |

Разом 25 own graded = 9 retained/corrected Generic functions + 16 new tasks. Transferred to another group — 0. Не всі дев’ять source functions були працездатними BEFORE; actual factory/browser defects описані окремо. Один demonstration не входить у graded total.

Generic зберігає існуючі control indices й відсутність старих task IDs/source_index. Stable IDs додано лише 16 новим tasks. Старий zero-article input тепер має непорожню свідому відповідь «Без артикля»; Ø/український label не вставляються як англійські слова. Старі short inputs не видано за full-sentence exercise.

Додано шість full-sentence inputs — по два на урок, manual та clickable token paths. Умови UK видимі до Check; context не прихований у feedback. Контракція Irregular і два змістовно дозволені Collective variants зафіксовані в accepted локально: вони не вмикають глобальний fuzzy matcher і не змінюють bank keys. Wrong/empty/whitespace/reset→empty не зараховуються. Feedback plain text для x-text: answer, точний переклад і пояснення; HTML/entities та answer-leaking hints не додавалися.

Own factory run перевірив 25 tasks / 32 visible options / 27 accepted values, шість manual + шість token paths; 5 BEFORE defect records. Покрито mixed scores, group resets, wrong→correct/recheck, aliases/distractors, normalization collisions, case/order/multiplicity; окремий synthetic repeated-token probe не є 26-м authored task. Pre-freeze actual run має exit0; frozen rerun main-observed PASS, без вигаданого окремого receipt hash.

## Вбудовані тематичні widgets

| Урок | Practice UUID | Source index / sort | Actual SQL filter / pool | Selected teaching focus | Стан реального browser 5/5 |
|---|---|---|---|---|---|
| Generic | `4ec54834-db1f-55f8-983a-e6537527e477` continuing | 11 / 12 | NULL / 72 | existing B2 ×5 | 5/5, усі п’ять видимих settled answers exact |
| Irregular | `0a415e02-446b-58ac-b3a8-8bd12cec769f` new append | 7 / 8 | A2 / 12 | A2 ×5 | 5/5, усі п’ять видимих settled answers exact |
| Collective | `da76d650-7531-58f0-afca-0c6187764073` new append | 6 / 7 | B1 / 12 | B1 ×5 | 5/5, усі п’ять видимих settled answers exact |

Точні банки:

- `Database\Seeders\V3\Polyglot\PolyglotAdvancedArticlesGenericReferenceAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotIrregularPluralsAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotCollectiveNounsAllLevelsLessonSeeder`

Selected native order: Generic `31638,31640,31641,31642,31643`; Irregular `32548,32551,32554,32555,32557`; Collective `32631,32632,32634,32639,32641`. Реальний widget може показувати ці самі п’ять у presentation order, тому browser order не підміняє selection contract.

В actual eligible pools 96 questions: 72+12+12. Семантично прочитано 36 кандидатів (12 B2 Generic, 12 A2 Irregular, 12 B1 Collective); решта 60 лише scope-excluded, не удавано повністю reviewed. Selected — 15 existing distinct type4 questions; нових questions/options/answers/hints/links/progress rows — 0.

Condition-only exact-bound clones обмежують видимий UK задум, не змінюючи original SQL model, source answer keys, ordered tokens, casing/multiplicity, formatter або grader. Snapshot pins перевіряють exact owner/Page/category/locale/practice UUID/body/source/selection. Generic NULL не підмінено B2 equality, а A2/B1 не розширено на all-level.

Offline native formatter: 20/20 tests PASS; 15 selected questions → 30 actual correct/assembled strings. Source includes negative controls, зокрема existing visible internal-capital defects excluded через selection, без recasing engine. Це не заміна browser Check acceptance.

Target gate — 3 / failed 0 / writes 0. Full registry — 142 / failed 0 / writes 0: усі 139 попередніх owner-keyed structural records exact, додано тільки три M73 owners. 142 — результат цього snapshot (139+3), не постійний global cap. Per-request raw HTTP hashes збережені окремо; byte-identical raw HTML не заявляється.

## Native-дизайн і точна інтеграція

Збережено шлях `theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. Content package не вибирає view/CSS/class/style. Немає M73 Blade/CSS/JS presentation, нового компонента, font hook, grader, normalizer або redesign. Підтверджено незмінність 326 visual sources.

Нові thin profiles:

| Шлях | SHA-256 |
|---|---|
| `app/Support/M73GenericIrregularCollective.php` | `71a0674c0a097a2d268fe0620571f90df6e2a5ffcc4e9a37c3c86d4ebc3823c3` |
| `app/Support/M73BuilderSelectionPresentation.php` | `75c56c169e6f9f5b9ba0af4009788e9eb1ef4ec9750f407cc56d492798a92f83` |
| `app/Services/M73ContentPatch.php` | `2289926dff756fe8d28360a4e4c81f52bba5225b129536c0755956b0d59efcf8` |
| `app/Services/M73LocalTargetGuard.php` | `2dc4b77bd86b5eddcb9cf651c65c0d2d2449cbab1a8cb5aaf4191eda250b50fe` |
| `app/Console/Commands/ContentPatchM73.php` | `8fb315abd8ddf7b34d8f6a2af9383b72a6eacf7e83a717afd8cc2a2ca219f897` |

У восьми shared files — 12 exact hunks / 13 added lines / 0 deleted: source registry, HTML/legacy adapters, renderer bindings, linked practice matching, question matcher, course manifest та чотири guarded standalone controller callbacks. Чотири shared generic primitives незмінні. M73 source-hunk reversal відновлює independently accepted cb55 blobs, не придуманий ROOT BEFORE.

Lineage fixture `tests/Fixtures/m73-owned-source-hunks.json` SHA `a63f7829d4d0009b8ce615af46ced46a2065659781df8834abbcb7177917bf0a` захищає 15 M72 frozen/active files, включно з bank-only 1.0.1, чотири primitives та всі 326 visual sources. Raw controller SHA `79ff053c79076ac10781397d8de2b75f01e054de0850f7906770e50e7528ba82`: 1529 CRLF / 1529 LF. Matcher working CRLF469; course manifest mixed598 CRLF+104 LF. Широкої EOL-нормалізації не було; Git LF/index profiles перевіряються окремо від working raw bytes. Staged/index gate реалізації — PASS: 44 exact owned paths, 43 ROOT/WT raw mirrors, усі 13 frozen pins також exact у Git index. Для matcher/course manifest перевірено погоджене Git LF-представлення окремо від working raw EOL; controller index CRLF exact.

## Freeze 1.0.0 і checksums

13 frozen/active files: дев’ять artifacts, три active definitions, checksum manifest. Public frozen sources UTF-8 без BOM, LF, рівно один кінцевий LF, без trailing whitespace. Найбільший serialized body — 19 208 bytes із фактичних 65 535; DDL не змінено. Static risk check: 62 title/formula fields ×4 fallback fonts =248 вимірів, 0 unbreakable-token risks на194px; не підміна browser layout.

| Artifact / active definition | SHA-256 raw UTF-8 bytes |
|---|---|
| `docs/content/m73-generic-irregular-collective.v1.0.0.json` | `9c3663e9e27aedd29a1ebc5cd33b4ce34a20a87e75ba18431408742e84aac015` |
| `docs/content/m73-before-source.v1.json` | `f27fd3004060b79a939fc1c41db63683269b28b2b7b1c5a83c65251bf40ebcb5` |
| `database/content-patches/m73-generic-irregular-collective.v1.json` | `8628f99afc29eb66c50b2996bbd75a360366680aeb362995c50a274af9e87eb5` |
| `docs/content/theory-sentence-builders/m73-generic-irregular-collective.v1.json` | `0a081d823b09ac1e04292c48cf27c4b94e6957f2b64a617f15bc3afb24726621` |
| `docs/content/m73-generic-irregular-collective.components.v1.json` | `efad2bfdea4e7b3255049248ec95fe30db7e69ab37482cdb0a08d11985943a02` |
| `docs/content/m73-generic-irregular-collective-bank-audit.v1.json` | `a17f7f03501efe1db4c232736f04fd2b811d4913fcf8aef2102227e9a6581042` |
| `docs/content/m73-generic-irregular-collective.v1.0.0.uk.md` | `eae869529d80cdc929e39ef452b1b65bea5b72b6416ca7e808ed6068da62f998` |
| `docs/content/m73-generic-irregular-collective.editorial.v1.md` | `28040680e0db770a4e092c9dcabf992b231df5670fd94d9e050f618670863368` |
| `docs/content/m73-generic-irregular-collective.editorial.v1.json` | `d46cf5ccc1c868fd0a2ca9e5efcb34df604a2b9b78e33b9f6e27a475f743ac58` |
| `database/seeders/Page_V3/NounsArticlesQuantity/NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder/definition.json` | `ad32ecb745cff04062d6da0f444038d0860878f3346a5a12ae5aadedd899dcd8` |
| `database/seeders/Page_V3/NounsArticlesQuantity/NounsArticlesQuantityIrregularPluralsTheorySeeder/definition.json` | `c93e8755395336a46182c9a9138f1c2d2033e0ad3cb23d67acfd52ef465d6bd5` |
| `database/seeders/Page_V3/NounsArticlesQuantity/NounsArticlesQuantityCollectiveNounsTheorySeeder/definition.json` | `02a3b8cd12999cf652dcade83c92e793d4060cc41b11643533b61aaa2a1aed43` |
| `docs/content/m73-source-checksums.v1.json` | `1dfc1cf0aa72fdcd376633a79beb334e5e67582925ebb0bffa69f7fa0f1c304b` |

Own Node source `tests/Browser/m73-own-practice.cjs` SHA `dd5ceee600c86ee725afe5728d60640fd0cc920a541a79211f7607d0eabf7101`; linked formatter source `tests/Browser/m73-linked-builder-formatting.test.cjs` SHA `41df310b373de56bae0b56340450bd5890221874aef421c0f360e9afb946ff90`.

## Локальний apply та незалежні postconditions

Fresh local web/CLI proof → fresh preview → exact diff review → exclusive backup → transactional apply → independent read-only reconstruction → repeated no-op виконано. Apply тільки CLI, не HTTP/startup/scheduler hook; Apache/XAMPP/hosts/ACL не змінено.

- Update operations: 32 = 3 Page.text +29 continuing UK body updates.
- У 29 bodies: 3 subtitles +25 teaching/native non-practice slots +1 existing Generic practice. Existing practice вже входить у 26 structured slots, не рахується вдруге.
- Inserts: 2 нові native practices. Distinct updated existing rows: 32; distinct changed rows після apply: 34, а не 32.
- Deletes, metadata transitions, old question-family writes, progress writes — 0.
- 58 EN/PL rows, усі old timestamps/UUID/columns/levels/tags/relations та всі нецільові rows збережені.
- Усі 46 BEFORE table fingerprints відновлено тільки поверненням exact reviewed Page.text/body і вилученням саме двох independently source-bound, allocation-bound rows. Інші text_blocks rows або timestamps не виключалися blanket-rule.
- Allocation capture перед apply зв’язує source/preview/BEFORE/runtime. Для 45 tables counters/max IDs exact; text_blocks advance рівно 2 з predicted ID/order, повними exact metadata та new created_at=updated_at. У receipt all 46 allocations exact.
- 326 visual sources і 2575 historical frozen files незмінні, source sync ROOT↔WT exact. Збережено M72 teaching 1.0.0, bank-only 1.0.1 та інші accepted releases/maps.
- Actual repeated apply main-observed stdout — no-op, updates/inserts/deletes 0/0/0. Окремий вигаданий no-op receipt/hash не додається.
- Тимчасовий proof route видалений: foreign-inclusive routes bytes до/після exact; окремий GET nonce endpoint повернув 404, redirects 0; фізичні hashes незмінні. Тимчасовий route не входить у delivery.
- Після завершення всього UI виконано fresh незалежний read-only audit v2: ті самі 32 update operations + 2 exact inserts = 34 distinct changed rows, усі 46 table BEFORE projections/allocations exact, 58 EN/PL, 326 visual sources та 2575 frozen files незмінні, source sync exact, додаткові SQL writes 0. Це actual after-UI receipt, а не перейменований pre-UI audit.

HTTP/SSR receipt перевірив 557 learning fields; 816 author fields classified, unsupported 0; три actual local 200 pages, own 25 / demos 1, linked 15, native manual/token data. Це серверний contract і serialized factory data, не client Check/visibility PASS.

Navigation guard: 21 actual fixed-loopback GETs (19 pages + 2 categories), 18 non-target learning DOM+metadata exact без Generic/random exemptions. На target footer — 9/6/8 chips; 20 noncurrent href occurrences, кожен відповідає real resolver-derived 200 GET. Targets зберегли H1/title/canonical/robots; description й teaching content змінені явно. Category rows/all-locale learning та native URLs/raw titles exact; SQL unchanged during HTTP.

Old-consumer guard: 24 strict HTTP endpoints, 432 old questions та пов’язані answer/option/hint/link snapshots, 6 saved-test records і 6 saved-question-link groups exact; courses/standalone JSON/reports не переведено на M73 teaching, SQL unchanged during HTTP. Повні HTTP та source/SQL evidence окремі, не підміняють одне одного.

Приватні receipts не комітяться; у таблиці лише opaque evidence checksums:

| Evidence | SHA-256 |
|---|---|
| Independent BEFORE | `41cb3ac163738e4d2662b4c53a3b2288de3303161d2da6ef5a1f42a9632d0ac9` |
| Old-consumer BEFORE | `e48d6886d85dab0bc5c96ebf8b9b8b7723ff626f2df08ab1609711bde56cda3c` |
| Preview raw / exclusive backup raw | `64b5ccd36ffdae84ff5fc83fc4870a79a56d92cb6e2adbd714dbc040691a88b5` |
| Reviewed plan digest (не raw-file hash) | `2d7ade7e6bec093cb19c767ad668e9ae1798cef206665545b1c935f2863516ce` |
| Exact diff review | `196d420cd344fed2c42205fa6c2ebe134078a297ad2d31139c83d2f7b02c1589` |
| Pre-apply allocation | `4f36db307712b98c3c8b82b6251615176152efea1fcb7527be2c5e42aca6d840` |
| Independent post-apply read-only v1 | `73741309840b02b2a6f7fa097881e5d8c12ddba68d5dbdf3b5a801cf462b3c99` |
| Fresh independent after-UI read-only v2 | `dda8fc0a749a6fe756282a15c5532149d01d4138c6c54ddedd3285584d9fe86f` |
| Real browser acceptance v1 | `b683d53c31fb9e973a0eebaffa359012e1a90184142614160ec503ca523adfd3` |
| Browser cleanup v2 | `077d04c0ab5307d76daac2ce28a905d2e9e34add06dcb1b14d8b77d40537bbfe` |
| HTTP/SSR live pages v1 | `d86837fb86821fc2ced5eb7dc483dc161e4079e45adf953975a8ab294bb4fcdd` |
| Fresh HTTP/SSR live pages v2 | `fad18c05857d8d2575a994b2245b699270fde32918c2a44234a848b3aaccb1b2` |
| Final after-HTTP read-only v3 | `5fccdfdf248051ad18d3fb252f09e34f5ed8191147e543a3666282163a5bd2af` |
| Navigation AFTER | `f60f7135f988416d8892d780a41c6c87de8b2166c8fad212875767e69e38d847` |
| Old consumers AFTER | `8cd543ea12f572cc9dcffe1e78250da7e1fa75871d2d162e89b344eba4b9209a` |
| M73 sentence-builder gate | `4deb670e9023b9c67b3c5d1e44f8729f222590ebf7eba52159eac713ceb6fddb` |
| Full registry142 | `4c8481c7833414fa391fe813368d22c99431453dbdd587d2385b8321f23f6345` |
| Cleanup404/routes exact | `2fcd9ffbfa987ea51530f210a9ee3dae4e82c16335390f3b276e23e166969cf4` |

## Ізольовані тести: фактичні результати, не сума повторів

| Gate | Фактичний run | Результат / межа |
|---|---|---|
| M73 whole PHP final rerun v4 |34 unique tests /8351 assertions, failures0/errors0/skips0 | Assertions PASS, але process exit1 через issues:57 existing discovery warnings +1 existing PDO PHP8.5 deprecation. Не exit0. |
| M73 initial PHP v1 |30 /5538, errors1/failures0 | Початковий новий fixture locale error виправлено лише в новому test fixture. Failed run не видано за PASS; остаточний результат — v4. |
| M73 intermediate PHP v3 |34 /8202, failures1/errors0/skips0 | Надмірно строге нове lineage assertion виправлено лише в новому M73 test; старі assertions/fixtures не послаблювалися. Остаточний rerun v4 вище. |
| Own native Node factory |25 own /32 options /27 accepted;6manual+6token | PASS; pre-freeze receipt і main-observed frozen rerun, не browser test. |
| Native linked formatter Node |20/20 tests,30 selected strings | PASS; actual unchanged native methods, negative controls; не browser DOM. |
| Current-runtime previous releases/native |210 /42612, failures0/errors0/skips0, exit0 |32 classes: M68 3;M69 89;M70 23;M71 34;M72 35;universal26. Existing PDO deprecation1 distinct, triggered207 tests. |
| Historical-source literal tests |15 /5079, failures8/errors0/skips0, exit1 |7 pass/8 fail; старі literal hashes/hunks/extraction assumptions лишаються FAILED, не переписані й не послаблені. |
| Historical UI source tests |6 /23, failures4/errors0/skips0, exit1 |2 pass/4 fail; raw Blade-literal expectations, не actual browser failure/PASS. |
| SiteMode isolated |10 /39, F0/E0/S0, exit0 |Synthetic production-domain local kernel tests only; real production requests0. Existing PDO deprecation10 triggers. |
| PPC identity isolated |6 /161, F0/E0/S0, exit0 |Окрема scope-limited identity/retry regression, не повний PPC browser rerun. Existing PDO deprecation6 triggers. |

210 current-runtime не є сумою всіх історичних gates; SiteMode10/PPC6 наведені окремо. Повтори ROOT/WT не додають унікальних tests; deduplicated union не названо full rerun. Фінальний M73 JUnit v4 SHA `b1e6b096ef5d7af8039f7c080eeedf6041c3ed0727a047e68d9121c445021577`; 34 unique = попередні 30 + чотири нові `M73SharedLineageTest` cases, не сума повторів v1–v4. Private prior-regression summary SHA `5e0e47ac54293ab52e255bf8d3a8e94a9b94d4c70a02bcc644336da7bcfd1878` містить точні fresh failed methods/diagnostics та окрему current-runtime partition.

Нові M73 source-lineage/metadata assertions перевіряють accepted cb55+exact M73 hunks; це не робить старі M68–M72 literal tests «зеленими». Старі snapshots/assertions не змінено заради total. Ізоляція: owned test runtime, SQLite :memory:, cache/session array, queue sync; working .env/DB не використані; HTTP/network0 у цих isolated suites.

## Браузер: повне actual UI-приймання

Справді знято 18 fresh layout states: 3 pages × 1440/390/320px × light/dark. У всіх 18 content learning main scrollWidth=clientWidth, один widget, fonts status loaded; zoom не змінювався. Tables мають наявний локальний overflow:auto, не розтягують learning main. Matrix SHA `8ac4dd18ff3f3f43812abca1788ad243508a508b64f914d56d58ce0589272fb5`.

Це не blanket shell PASS: анімовані absolute decorative spans давали приблизно 6–13px за межами mobile viewport; окремий 320px snapshot має shell width330px, learning main288/288px. Shell observation SHA `5be2c8328b85d6cd830475be06891fbbdb0188a8c01f90d25c106a206e27b3f6`. Декоративний shell observation відділено від learning overflow; CSS/layout не змінювалися і дефект не маскувався контентною правкою.

| Урок | Layout | Own Check/feedback/reset | Два sentence inputs manual+tokens | Widget settled answers/completion |
|---|---|---|---|---|
| Generic |6 states DONE |13 tasks / усі options та accepted DONE |1440px/390px, 8 cases DONE |5/5; усі п’ять visible answers exact; Next/completion; restart DONE |
| Irregular |6 states DONE |6 tasks / усі options та accepted DONE |1440px/390px, 8 cases DONE |5/5; усі п’ять visible answers exact; Next/completion DONE |
| Collective |6 states DONE |6 tasks / усі options та accepted DONE |1440px/390px, 8 cases DONE |5/5; усі п’ять visible answers exact; Next/completion DONE |

Actual own UI охоплює 25 graded tasks, 32 visible options, 27 accepted values. Для шести new full-sentence inputs перевірено 24 cases = 6 inputs × manual/token × 1440/390px; token return, повторний Check, wrong→correct, mixed scores та group resets обліковано в receipts. Generic zero-article input відхилив empty, whitespace, `Ø` і `Ø (без артикля)`, а непорожнє «Без артикля» прийняв. Collective actual UI прийняв обидві task-bound agreement variants та показав bounded feedback у mixed/correct states; це не глобальна заборона альтернатив.

Для всіх 15 linked questions окремо прочитано збережені settled DOM answer і score: assembled visible sentences exact і граматичні, кожний owner завершено 5/5. Generic restart реально повернув тільки його owned widget на питання 1/5; global storage та персональний прогрес не очищалися. Це actual browser evidence, не лише matching grader key або offline formatter PASS.

Обмеження доказів: три initial BANK snapshots не були збережені — Collective q2, Collective q5 та Irregular q5. Для цих трьох є independently read settled answer/score/completion; для всіх 15 є settled visible answers. Не заявляється «15 initial BANK screenshots» або відсутні до-складання screenshots.

Повний real-browser receipt `browser-acceptance-v1.json` SHA `b683d53c31fb9e973a0eebaffa359012e1a90184142614160ec503ca523adfd3`; console errors — 0. Cleanup v2 SHA `077d04c0ab5307d76daac2ce28a905d2e9e34add06dcb1b14d8b77d40537bbfe`: початкову light theme повернуто, viewport override reset, zoom unchanged; global storage і personal progress не очищені. Це не твердження про viewport рівно 1440px після reset — повернено фактичний початковий browser surface.

Print/no-JS/WCAG audit — not_checked, не PASS. Відсутні details не тестуються як наявні. Production не перевірявся й не змінювався.

## Scope review та Git delivery

44 implementation files staged за exact allowlist: 13 frozen/active джерел, 8 shared files, 5 thin profiles, `.gitattributes`, 8 diagnostics, 6 Feature tests, 2 Node tests і lineage fixture. Цей звіт — 45-й owned delivery file. Scope review не виявив сторонніх файлів, секретів, runtime caches, дампів, приватних screenshots/proofs або routes. Старі untracked runtime/diagnostics не включаються. ROOT foreign deletion `.gitattributes` не відновлювався; у WT додано тільки 17 M73 EOL-policy lines до freeze.

CR-aware `git diff --cached --check` пройдено. Виявлений один зайвий порожній EOF-рядок виправлено лише в новому діагностичному `m73-live-pages.php`, ROOT/WT синхронізовано; це не frozen навчальне джерело й не runtime зміна. Саме поточний helper повторно дав HTTP/SSR PASS v2, після нього незалежний read-only v3 підтвердив усі 46 таблиць/allocations, 326 visual та 2575 historical frozen джерел без drift. Source/master/component/task/bank freeze не змінювався.

Незалежний read-only scope review підтвердив exact ROOT/WT mirrors, 8 shared files / 12 hunks / 13 added lines, reversal до accepted base, 4 незмінні primitives й 15 M72 sources. Ancestry до accepted base `cb55b329827e52a56cb6d41edb21e70768cf86f4` збережена в робочій M73 branch; `main` не змінювався.

Delivery: scoped commit → normal push тільки `codex/seo-m73-generic-irregular-collective-standard-view` → незалежна `ls-remote` перевірка remote SHA == HEAD. Власний commit SHA неможливо вписати в його ж blob без циклічного переписування; повний фактичний SHA й результат remote verification наведено у фінальній відповіді після виконання. Production/deploy/PR/merge/force push не дозволені й не виконуються.
