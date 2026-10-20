# M72 — Partitives, No / None / Neither / Either, Geographical Names

Дата: 2026-10-21. Ціль реалізації та HTTP/браузерного приймання — тільки `http://gramlyze.loc`. Production, `main`, PR, merge, force push, workflow dispatch і деплой не входять у M72.

**Локально прийнято M72 content 1.0.0 + bank-only 1.0.1.** Підтверджено 39 own tasks, 24 NEW manual/token cases, fresh 18 viewport/theme states та всі 15 linked questions із правильним видимим реченням. HTTP/SSR, registry, isolated tests, repeated no-op і остаточні after-UI/HTTP SQL postconditions завершені. Ранній formatter defect виправлено лише existing bank selection, без зміни engine/дизайну. **Exact 47-file staged scope та 15 frozen ROOT/WT/index pins перевірено. Full commit SHA й незалежне підтвердження push наводяться у фінальній відповіді.**

## База, межі та сторінки

Фактична accepted база — `2bcd468b25af3c44dcf34ccee0e012f3f683335d`, фінальний M71 1.0.0. Робоча гілка — `codex/seo-m72-partitives-negatives-geography-standard-view`. Попередні M70, M69 1.0.1, accepted PPC retries та інші погоджені зміни цієї бази не відкочувалися.

Змінюються три UK Page owners категорії `imennyky-artykli-ta-kilkist`, category ID **16**. Довгий чинний slug Partitives збережено; нових aliases, Page owners або category segments немає.

| Сторінка | Page / Category | SQL technical / hero display | Збережений H1 |
|---|---|---|---|
| [Partitives — a piece of, a cup of...](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/partitives-with-uncountable-nouns-a-piece-of-a-cup-of) | 88 / 16 | NULL / A2–B1 | Partitives: a piece of, a cup of, a bit of |
| [No / None / Neither / Either](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/no-none-neither-either) | 87 / 16 | NULL / B1–B2 | No, none, neither, either |
| [Articles with Geographical Names — Географічні назви](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/articles-with-geographical-names) | 83 / 16 | NULL / A2–B1 | Articles with Geographical Names |

Повні identities:

- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityPartitivesTheorySeeder`
- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder`
- `Database\Seeders\Page_V3\NounsArticlesQuantity\NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder`

Фактичний SQL NULL установлено незалежним BEFORE, а не виведено з відсутності source level key. Teaching focus — A2 / B1 / A2; bank-only 1.0.1 явно добирає для Negatives чотири B1 та один existing B2 question. Це не зміна SQL technical level або page-wide CEFR filter.

## Редакція та OLD → AFTER

Збережено **42 continuing structured slots: 14 / 13 / 15**, їхні native types, порядок, columns, UUID, levels, anchors, tags і зв’язки. Три subtitles та три practice rows також continuing. Немає другого practice-set, нового component type або заміни навігації практикою.

- **Partitives:** відділено одиницю, порцію, вміст посудини та точну міру. Після `of` можливі незлічуваний вміст і злічувана множина. Cup/glass залежать від посуду, не температури; carton не обмежено рідинами. Piece не є універсальною одиницею; paper/advice/information перекладено за значенням, додано потрібне `a bit of`. Множина loaves, determiner + unit + of, питання, заперечення й короткі відповіді видимі в basic. Serving-use `a water` не оголошене універсально неграматичним. Ці межі перевірено за [Cambridge про partitives](https://dictionaryblog.cambridge.org/2021/02/17/a-grain-of-rice-and-a-clove-of-garlic-making-uncountable-nouns-countable-1/), [British Council про кількість](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/quantifiers) та [count/uncount значення](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/common-problems-count-uncount-nouns).
- **No / None / Neither / Either:** розмежовано форму перед noun, заміну названої кількості, визначену групу та вибір із двох. Any для ширшого позитивного вибору не підмінено none. None agreement залежить від референта; для множини описано нормативну варіантність, а не «завжди is». Neither…nor — нормативне поєднання; not…either та Neither do/did/can I мають різні функції й явні контексти. Not all не означає none, дозвіл не є вже здійсненою дією. Джерела: [No, none and none of](https://dictionary.cambridge.org/grammar/british-grammar/no-none-and-none-of), [Neither, neither…nor and not…either](https://dictionary.cambridge.org/grammar/british-grammar/neither-neither-nor-and-not-either), [Either](https://dictionary.cambridge.org/grammar/british-grammar/either), [Either…or](https://dictionary.cambridge.org/grammar/british-grammar/either-or) і [Both з either-of agreement](https://dictionary.cambridge.org/grammar/british-grammar/both).
- **Geography:** подано типові форми конкретних назв у зв’язному реченні, не алгоритми «всі міста», «всі гори» або «країни-союзи». Розділено Mount і хребти, Lake і коротку назву, місто Dnipro та річку the Dnipro. Cambridge описує ranges та деякі окремі peaks із the; назви з Mount/Lake мають іншу модель. [Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/geographical-places) доповнено [прямо прочитаним Cambridge book excerpt](https://assets.cambridge.org/97805217/31751/excerpt/9780521731751_excerpt.pdf).
- Фактологічно: Амстердам — столиця; уряд і парламент розташовані в The Hague, переклад назви — «Гаага». Це підтверджено [офіційним сайтом ЄС](https://european-union.europa.eu/principles-countries-history/eu-countries/netherlands_en); твердження про «єдине місто з the» прибрано. [NPS уживає Lake Superior і Superior](https://www.nps.gov/apis/planyourvisit/its-not-just-another-lake-up-north.htm), тому відсутність Lake не вмикає the. [English Channel](https://dictionary.cambridge.org/dictionary/english/english-channel) — природний морський прохід, не штучний canal. [PCGN/FCDO](https://www.gov.uk/government/publications/country-names/country-names-the-permanent-committee-on-geographical-names-for-british-official-use) підтверджує сучасну The Gambia; нерозібраний список Congo/Sudan як «історичних винятків» не повторюється. Нових політичних статусів, рекордів або новин урок не додає.

Нові приклади та exercises авторські, не копії видавничих keys. Перевірка агентна за первинними редакційними джерелами, **не human review**. У versioned master `sources` записано реальний доступ: direct body, publisher-indexed body, PDF excerpt, 403, timeout та cache miss відділені. Cambridge grammar direct fetches із 403 не названо прямим успішним читанням або redirect; нормативні висновки спираються на індексоване редакційне тіло видавця й зазначені прямі джерела. Learner comments та corpus/web quotations не використані як нормативні правила.

Повні component/editorial/task maps:

- `docs/content/m72-partitives-negatives-geography.components.v1.json`
- `docs/content/m72-partitives-negatives-geography.editorial.v1.json`
- `docs/content/m72-partitives-negatives-geography.editorial.v1.md`
- незалежний source BEFORE: `docs/content/m72-before-source.v1.json`.

Зафіксовано **71 finite visible-basic рішення: 29 / 21 / 21**. Details — **0 / 0 / 0**: потрібні правила, переклади, приклади та короткі застереження видимі одразу. Квот кнопок і runtime word-count threshold немає. Keyboard/owner/deep-link приймання прихованих details — **N/A**, не вигаданий PASS.

## Практика: збережено, виправлено, доповнено

| Урок | Retained/corrected graded positions | Нові tasks | Own graded AFTER | Select / meaning / input / graded rephrase | Ungraded demo |
|---|---:|---:|---:|---|---:|
| Partitives | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 |
| No / None / Neither / Either | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 |
| Geographical Names | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 1 |

Разом **39 own graded = 27 старих навчальних позицій + 12 нових**. Три demos не graded. Це не твердження, що всі старі source keys були працездатними BEFORE: source audit, actual factory та реальні BEFORE спостереження обліковано окремо.

Збережено відсутність старих task IDs/source_index; stable IDs додано лише новим tasks. Три старі Geography inputs форматно перенесено в повнореченнєву модель **в тих самих індексах та continuing practice UUID**, не видалено й не названо новими. Вони приймають:

1. `I live in Ukraine.` — Я живу в Україні.
2. `The Dnipro is a beautiful river.` — Дніпро — красива річка; тип об’єкта прямо названий.
3. `I climbed Mount Everest.` — Я піднявся на Еверест.

Два старі порожні answer keys усунуто. Select із відсутністю артикля має непорожню свідому відповідь **«Без артикля»**. В англійські речення не вставляються Ø або український label; untouched, empty, whitespace і Reset → empty Check не зараховуються як правильна відповідь.

У Partitives умова прямо задає посудину/одиницю; у No/None — групу й задум заперечення або вибору. Canonical Neither старого input збережено; для двох явно відсутніх осіб додано один змістовно обґрунтований None alias з власним перекладеним feedback. Це finite author decision, не глобальний fuzzy matcher.

**Шість NEW full-sentence inputs** мають manual/token шлях. Разом із трьома перенесеними Geography — **9 full-sentence paths**; старі короткі inputs Partitives/Negatives не підраховано як нові речення. Видимі UK умови розміщені в підтриманому `after`; rephrase stimulus залишається EN, UK задум — у `rephrase_intro`. Повна готова відповідь у hint/placeholder не додається.

Незмінний actual factory перед freeze перевірив **39 tasks / 36 visible options / 40 accepted values / 525 raw state cases**, відтворив **11 BEFORE defect records**. Покрито canonical/aliases/distractors, empty/whitespace, wrong → correct, repeated Check, group reset, token order/case/multiplicity та окремий synthetic repeated-token probe. Це **не browser PASS**. Feedback — plain text для x-text: відповідь, переклад, коротке пояснення; HTML/entities не додаються. Raw trimmed-lowercase feedback aliases явно проєктовано до freeze, не змінено runtime normalizer.

## Окремі тематичні конструктори

| Урок | Continuing practice UUID | Source index / sort | Technical filter | SSR widget/payload | Реальні 5/5 |
|---|---|---|---|---|---|
| Partitives | `54ce9ed8-8e28-574c-9485-a780a04f9a37` | 12 / 13 | NULL | 1 / 5 | **5/5**, fresh bank 1.0.1, formatted exact |
| No / None / Neither / Either | `b2fd69af-1520-5e69-a5e9-6d315246ac01` | 11 / 12 | NULL | 1 / 5 | **5/5**, fresh bank 1.0.1, formatted exact |
| Geographical Names | `63edb425-3ba4-59e1-bfcd-aa676eac86a1` | 13 / 14 | NULL | 1 / 5 | **5/5**, fresh bank 1.0.1, formatted exact |

Точні thematic banks:

- `Database\Seeders\V3\Polyglot\PolyglotPartitivesWithUncountableNounsAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotNoNoneNeitherEitherAllLevelsLessonSeeder`
- `Database\Seeders\V3\Polyglot\PolyglotArticlesWithGeographicalNamesAllLevelsLessonSeeder`.

Selected IDs у native порядку:

- Partitives: `32115, 32120, 32122, 32123, 32124`.
- Negatives, active bank 1.0.1: `32775, 32778, 32779, 32780, 32788`.
- Geography: `31539, 31542, 31543, 31547, 31550`.

Це **15 різних existing type-4 questions**, не 15 нових SQL rows. Bank 1.0.1 має **37 semantic reviews: 36 початкових A2/B1 кандидатів + один B2 reserve**; решта **179** у трьох NULL-filter pools scope-excluded без удаваного детального semantic review. У кожному pool — **72 existing questions**, усі ID pools збережено. Нових questions/options/answers/hints/links — **0**.

Точний owner/locale/Page/category/UUID/body/snapshot binding та clone-only selection не змінюють page-wide bank або CEFR filter. Коригуються лише bounded visible UK conditions exact-bound render clones; presenter не робить SQL та не мутує original models. Original ordered instances, case й multiplicity збережено. Зокрема, native serializer уже подає sentence-initial the як The; source-only lower-case observation не видано за доведений native UI defect.

Fresh bank 1.0.1 target gate: **3 targets / failed 0 / writes 0**; повний registry: **139 targets / failed 0 / writes 0**. Усі **136 попередніх owner-keyed structural records** збережені exact, додано рівно три M72 owners. 139 — результат run, не постійний ліміт. Per-request transport hashes відділені від structural contract; byte-identical full HTML не заявляється.

## Bank-only revision 1.0.1

Browser v1 реально пройшов 15 selected questions за grading, але ID **32781** після Check показав **`I saw? No, mistakes in your answer.`** замість `I saw no mistakes in your answer.` Це змістовний visible defect, не PASS лише через правильний бал. Оригінальний question, ordered tokens, formatter та grader не ремонтувалися.

Після незалежного exact-diff/snapshot/semantic review цей один selection замінено на **existing same-bank ID 32788, B2**: `None of the water was clean.` — Жодна частина названої води не була чистою. Умова встановлює саме нуль чистої води, не not all; mass water узгоджується з was. Чотири інші B1 questions, обидва інші targets, усі practice bindings та три technical NULL pools залишаються exact. Повний BEFORE snapshot replacement має hash `d5b4150cd957c9ab1795b4bdf0c8683776d9edbddf2a31b9f734700284ee4f31`.

Це **лише versioned bank-audit/render-selection supplement**: master, patch, definitions, builder contract і всі 13 frozen 1.0.0 файлів залишаються raw byte-identical. Нових SQL questions, DB writes, engine/grader/normalizer змін — **0**. Початковий bad-UI receipt збережено, не переписано як PASS. Fresh 1.0.1 browser v3 реально пройшов усі **15 questions / 5/5 на owner**: replacement показує exact `None of the water was clean.`; offline formatter regression також перевіряє всі selected strings і зберігає старий bad case як negative control.

| Frozen bank-only supplement | SHA-256 raw UTF-8 bytes |
|---|---|
| `docs/content/m72-partitives-negatives-geography-bank-audit.v1.0.1.json` | `803855b45aa9f01d4ae7a9c6e01b8fa108f975104624ff3825bea21015999529` |
| `docs/content/m72-bank-selection-revision.v1.0.1.json` | `09d3e65edf74dc1e2e05e1bc0edd11a8b63e4b4062f502d375605ea9434d00b6` |

Revision manifest містить повний exact JSON-value diff і pins попереднього/active audit. Candidate-level B2 не видано за новий page CEFR: actual selected levels Negatives — **B1 × 4 + B2 × 1**, technical filter лишився **NULL**.

## Канонічна в’юха та невізуальна інтеграція

Шлях залишається `theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. Немає M72 Blade/CSS/JS, нової practice-в’юхи, font hook, grader або redesign. Кольори, рамки, typography, sidebar/background, spacing, приклади та feedback належать чинним native components.

Зміни коду — thin M72 release/writer/guard/command profiles на прийнятих primitives та finite builder presenter. У восьми shared файлах додано **12 exact dispatch hunks / 13 рядків**: native source/adapter, matching/bindings та preservation courses/standalone help. Чотири generic primitives не змінено.

`M72SharedLineageTest` відновлює accepted M71 код копій тільки зворотним застосуванням exact M72 hunks. Старі assertions не переписано. Захищено 12 M71 frozen/active sources, **39 own M71 tasks + 3 demos**. Для трьох M71 active definitions Git LF blob і окремий served BEFORE raw hash обліковуються різними полями; їхні CRLF bytes не нормалізувалися заради M72.

## Freeze 1.0.0 та повні SHA-256

Для 13 M72 frozen/active файлів підтверджено raw ROOT = WT = Git index = declared SHA/bytes; UTF-8 без BOM, LF, рівно один кінцевий LF. Обидва bank-only supplements також exact: разом **15 frozen raw pins**. Політику точних paths у `.gitattributes` визначено до freeze; working-byte equality та окремий raw-index gate перевірено незалежно.

Найбільший serialized body — **15 723 bytes** за фактичної TEXT-місткості **65 535**; DDL не змінено. Static width gate: **75 title/formula fields × 4 fallback fonts = 300 вимірів**, 0 unbreakable-token risks за 194 px. Це не підтвердження browser viewport або webfont layout.

| Artifact / active definition | SHA-256 raw UTF-8 bytes |
|---|---|
| `docs/content/m72-partitives-negatives-geography.v1.0.0.json` | `253da61264abd8544be816342b8e3522b11870c1f0ba1722b8b976eabc2f232c` |
| `docs/content/m72-before-source.v1.json` | `2567255a6e63255a474a6b6041ab2e99c69298a1b5ba0624e488ae44d09239bb` |
| `database/content-patches/m72-partitives-negatives-geography.v1.json` | `4437f03a77bac1cb9e7f49f73f0c68f0482d9708f2ffad8804972b07d74d84a3` |
| `docs/content/theory-sentence-builders/m72-partitives-negatives-geography.v1.json` | `baf496f103c9056cd03b1010e51dadee614df55afdd8e3fb9018b67c9dea6f22` |
| `docs/content/m72-partitives-negatives-geography.components.v1.json` | `6f692ddd8447e2031ce40347863c3c011d41e416ffb5c4301cd4b7f01e528adf` |
| `docs/content/m72-partitives-negatives-geography-bank-audit.v1.json` | `bc51862d48ec3764363574881b14fdc78f0b481730a3a72bd86b9f92264e2c1d` |
| `docs/content/m72-partitives-negatives-geography.v1.0.0.uk.md` | `96cc29b0acd9fc0f30dbfa7ae45f8eadaa8f556f710e3cc8dd91860ffb29be4e` |
| `docs/content/m72-partitives-negatives-geography.editorial.v1.md` | `403d29f16ae9b2a1095c97857063b33809b1ea485e9a19795877e7895425ee5d` |
| `docs/content/m72-partitives-negatives-geography.editorial.v1.json` | `59c683dd26ad8aa82f9265355117f6a3211d087496e049066dd936870dafccb4` |
| `docs/content/m72-source-checksums.v1.json` | `a7b32c1f05eb75c760073fcc53a5e0e752b045d788c0de46e116ad392536a4a2` |
| `database/seeders/Page_V3/NounsArticlesQuantity/NounsArticlesQuantityPartitivesTheorySeeder/definition.json` | `d4693fb201b9c7a4500b8328fa2579ad8475ed617474d2ce4784f9a9850ff95e` |
| `database/seeders/Page_V3/NounsArticlesQuantity/NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder/definition.json` | `1541b51276286cb9b44932d82c24eaa3533ea6935805cd8e41fbf301412b85ce` |
| `database/seeders/Page_V3/NounsArticlesQuantity/NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder/definition.json` | `e1260794e7fa92a2aaaffd73b31db3dd181517ba1f008579fc7ae6884f18d043` |

Окремий lineage fixture `tests/Fixtures/m72-owned-source-hunks.json`: `c4a27b89f25a97bd4a0596ac061285843dbef1d638c5d8a3f752e28b17493134`.

Raw controller ROOT/WT SHA — `082543611f02517c918aa90705eea8d00bf34294ed6750dfd2f13df537f3d26b`; **1525 CRLF із 1525 LF**. Чотири додані controller lines зберігають цей профіль. Звичайний diff-check показує CR як whitespace; CR-aware `git -c core.whitespace=cr-at-eol diff --check` проходить. Mixed EOL `TheoryCourseManifestService.php` також збережено без перезапису; Git warning про можливий майбутній checkout не приховується. Широкого renormalize історичних файлів не було.

Raw-index review окремо встановив чинні Git/working EOL profiles, не прирівнюючи їх до content drift: `TextBlockToQuestionsMatcherService.php` має working **468 CRLF / 468 LF**, index **468 LF**; `TheoryCourseManifestService.php` working **597 CRLF / 701 LF**, index **701 LF**. Для цих двох exact named sources ROOT = WT raw, index = accepted Git LF profile плюс рівно owned hunk; SHA та counts pinned окремо, canonical code exact. `.gitattributes` working **607 CRLF**, index **607 LF**, canonical policy exact; його стороннє видалення у served ROOT збережено. Початковий private index validator помилково вимагав raw equality matcher/index; виправлено лише це скінченне діагностичне припущення з exact SHA/count pins, без змін source bytes, frozen artifacts або Git attributes.

## Локальний guarded apply та незалежні postconditions

Використано чинний local-target proof/preview/review/backup/transaction workflow; секрети й runtime identity до цього report не копіюються. Веб/CLI target та фізичні paths перевірялися до запису. Виняток proof route не перетворюється на HTTP apply.

Незалежний read-only post-apply audit підтвердив:

- **48 distinct changed existing rows**: 3 `Page.text` + 45 continuing UK body updates.
- У 45 bodies: **42 structured + 3 subtitles**; три practice bodies входять у 42, не рахуються вдруге.
- **0 inserts / 0 deletes / 0 metadata transitions**. Practice UUID/source index/sort/NULL level збережено; нових practice blocks — 0.
- **90 EN/PL rows** та всі metadata/timestamps поза дозволеними text/body полями незмінні.
- Exact BEFORE projection відновлюється для всіх **46 таблиць** лише через незалежно переглянуті дозволені поля; question families, options, answers, links, hints, персональний прогрес, category learning descriptions та нецільові rows не змінювалися.
- **326 visual sources** та **2558 historical frozen inventory files** без drift; M71/M70 і попередні accepted releases/maps залишаються чинними.
- Actual repeated apply stdout підтвердив **status no-op**, updates/inserts/deletes **0/0/0**. Fresh bank 1.0.1 plan v2 також має state **after**, 0 updates/inserts/deletes; його reviewed plan digest — `44c0855b988eba20b0f53cc4cd9259138ac2e36fb78325b825392262e3143f94`. Це не ще 48 змін.
- Proof routes обох циклів видалено, routes bytes відновлені exact, фізичні hashes незмінні; кожний окремий cleanup GET повернув **404**, redirects — **0**. Другий cleanup також має route writes / DB writes **0/0**. Apache/XAMPP/hosts/ACL не змінювалися.

Після browser v1 виконано read-only audit v2; після bank revision — v3: ті самі **48** дозволених changed rows, exact незалежний BEFORE projection усіх **46 таблиць**, 0 додаткових writes/metadata transitions/inserts/deletes/progress writes. 326 visual sources і 2558 historical frozen files незмінні, ROOT↔WT source sync exact.

Перший original postconditions guard після bank revision правильно відмовив через **два змінені bank profile sources**; failed receipt не названо PASS. Власна діагностика отримала optional fresh no-op source proof, exact allowlist лише для `M72ContentPatch.php` і `M72BuilderSelectionPresentation.php`; решта original apply-preview sources exact. SQL loop/48 дозволених rows не змінено.

**Final read-only audit v4 після завершення current UI та HTTP підтвердив ті самі 48/46**, 90 EN/PL rows exact, 0 inserts/deletes/metadata transitions/progress writes, усі old rows/timestamps exact поза дозволеними полями. Original reviewed apply plan digest `652463610e4ccb2f0cd4303dfb7c15fdf893d05564e258ae6da119cc1d85eba2` збережено окремо від current no-op digest `44c0855b988eba20b0f53cc4cd9259138ac2e36fb78325b825392262e3143f94`. Guard не послаблено до довільного source drift: рівно дві named bank-profile supersessions, усі інші source pins exact.

Непрозорі hashes evidence, без snapshots/SQL/runtime secrets:

| Gate | Receipt SHA-256 |
|---|---|
| Exact reviewed diff | `509114c14e26916478f767656002ab7b6795860f45e8863e36b74b58fc8b38f0` |
| Post-apply independent read-only audit | `bee395e459ae83c1fa3027645ad218d3044003429a24202d24180d6823e291a2` |
| After-browser v1 read-only audit v2 | `281b16601a1bbba3bee643289aa014ac90469559bb10b17fe607faa4761fa3d4` |
| No-op plan | `b9d618ad8ee0b40f4bc80eefed944767b21a146e65b7b14b51362cb5a8fdd064` |
| Route restoration / GET 404, перший цикл | `dc2f5e282125c96fa449b9350054339943ec3052a8ac68c527e4769582764d51` |
| No-op plan v2 raw file (digest наведено вище окремо) | `2b87cfd2869621f7ed16ace54ee8c4ea30f23155883a829ac5703ac399345206` |
| Bank 1.0.1 read-only audit v3, до final UI | `dfc20a98783cdd2edc8e0497c688a9a36d4365d1c379d2c320d26eb65c0cb0f4` |
| Final after-current-UI/HTTP read-only audit v4 | `88356b3d82825e1c76c1c6a1f9f201e07c1b001d0ca1888c442b876c338f5884` |
| Route restoration / GET 404, другий цикл | `08a8e6211162a437c67743ad1863e9300a68dbe9fbe84d8d304e1e623a6aafe2` |

## HTTP/SSR, metadata, registry та old consumers

Три exact target GET повернули **200**. Незалежно перевірено **943 SSR learning fields / 1312 classified author fields / 0 unsupported fields**. Answers/aliases/feedback та client-only token bank звірено з exact native serialized config, а не видано за вже натиснуті browser controls.

На кожній сторінці один H1; raw Page.title, H1/title, breadcrumb, canonical та LearningResource identity збережено. Перший strong у subtitle враховано без зміни global title extractor. Expected descriptions зафіксовано до apply чинним сервісом, а не скопійовано як expected з AFTER. Local X-Robots-Tag — **noindex, nofollow, noarchive**. Старі anchors збережено: **26 / 25 / 27**, missing — **0**. SEO не гарантує індексації; sitemap/robots refactor і production crawl не виконувалися.

Навігація зберігає дев’ять labels/current flags на owner. За фактичним resolver перевірено **24 resource anchors + 3 current chips** та **20 різних actual local GET, усі 200**; вигаданих paths або `href="#"` немає. Для **14 non-target pages + 2 categories** full learning DOM exact. Для Generic Reference окремо exact решта static learning DOM після єдиного валідованого випадкового JSON масиву п’яти questions: це **не full learning DOM / raw HTML equality**. Source hashes і SQL fingerprints exact; під час navigation HTTP усі 46 таблиць незмінні. Browser clicks цього окремого diagnostic — not_checked; він доводить destinations/GET/parsed DOM, не натискання UI.

Fresh bank 1.0.1 builder gate: **3/0 failed/0 writes**; registry **139/0 failed/0 writes**, **136** old structural records exact. Raw HTTP hashes збережено окремо. Це server/structural evidence; реальне current 5/5 має незалежний browser v3 receipt.

Fresh old-consumer AFTER **v2** на current bank містить **24 actual HTTP 200**: 3 courses, 12 SSR (у тому числі compose), 9 JSON. Scope — **6 saved tests / 432 old questions**, не нові M72 вправи. Old SQL і structured consumer records збережено; усі таблиці незмінні під час HTTP та відповідають перевіреному AFTER. Це read-only GET/SELECT/ROLLBACK без auth/progress submission. Shuffle/transport hashes не прирівнюються до full-HTML byte equality.

Receipt hashes:

- current target SSR v2: `bed6037a0fd7209e8d7e4866e50f06b5d1cb122fffda1728b8fce7b54a299627`;
- current target builder gate v2: `e5ed65a748cf486762f2e5a08c128a44125a90aa4caf52ce65d25c6036578e3c`;
- current old-consumer AFTER v2: `1723bda3deee7dacd0d27dfd7920388b42fa805bcc60682342fdc63fc9e0289a`;
- navigation AFTER v2: `d173557bdccf10132c232d9074d0e79c349a8e5fe7089a17103236a70ea10505`;
- current full registry v2: `b52c1bed0fb93cb99742c7bb1cf497fd8ae13b670cd9b5a05a1f03879df86b6c`.

Ранні SSR/registry/consumer receipts збережено приватно як історію; наведені current v2 перевіряють active bank 1.0.1. Final SQL v4 виконано після поточного UI та цих HTTP gates.

## Реальний браузер: final bank 1.0.1

Фінальний browser v3 receipt: `e84b85c31c7892baf71c8aa145f36a22a2c9096131afc57c09dbeb69e8f65e21`. На current bank 1.0.1 **заново** пройдено **18 unique actual-DOM states** — 3 owners × 1440×1000 / 390×844 / 320×844 × light/dark — та **15 selected questions до реальних 5/5 на owner**, з exact видимим formatted answer після кожного Check. На кожному стані пройдено всі **12 / 11 / 13 section headers** до footer, native components/переклади/один widget; Manrope, sans-serif **14 px** підтверджено у learning DOM. Кожний v3 checkpoint записаний окремим private JSON/screenshot; 18 final layout screenshots.

Own practice лишилася byte-identical content 1.0.0 на незмінному engine: її **39 tasks / 36 options / 40 accepted values / 24 NEW mode-size cases** доводить первинний browser receipt `1242753ddca415d693a674ba7e9594ef093b412590f46963bbe2974182f60bcd`. Це **не новий 39-task rerun у v3**; evidence не додається як унікальні повторні cases.

| Browser evidence | Фактичний результат |
|---|---|
| Own graded tasks / видимі options / accepted values, v1 unchanged content | **39 / 36 / 40**, включно з None alias; feedback прочитано в DOM |
| NEW full-sentence manual/token × desktop/mobile | **24 distinct task/mode/size cases**; ще 2 manual repeats окремо, не додані до 24 |
| Retained Geography full sentences | Усі **3 manual + 3 token** paths; не підміняють NEW cases |
| Empty/whitespace/wrong/repeated wrong → correct | Перевірено; repeated Check не подвоює score, Reset і групи незалежні |
| Zero-article select | Untouched **0/4 → «Без артикля» 1/4 → Reset 0/4** |
| Geography full input | Empty/whitespace, `Без артикля` та `the` не прийняті замість повного речення, score **0** |
| Mixed editing / token return | Перевірено для всіх трьох owners, native ordered case/instances не змінені |
| Current linked widgets, fresh v3 | **5/5 × 3, усі 15 formatted answers exact**, включно з replacement 32788 |
| Console errors | **0** |
| Details keyboard/owner/deep-link | **N/A: 0 details** |
| Print / no-JS / повний WCAG audit | **not_checked**, не PASS |

Client shuffle враховано: bank controls не плуталися з answer chips; фінальне питання показує **«Почати спочатку»**, не Next. Історичний v1 мав grading 15/15, але лише 14 правильних formatted sentences; ID32781 лишається recorded defect/negative control. Final v3 повторно довів усі current 15, зокрема **`None of the water was clean.`**, без правки formatter. Незавершена v2 browser спроба втратила in-memory kernel state й відкинута; її checkpoints не з’єднані у fake full run.

Final v3 learning overflow — **0 у всіх 18 станах**. Native table wrappers client/scroll: desktop **825/825**, 390 px **322/576**, 320 px **252/576**; це штатний локальний horizontal scroll. Окремі final shell document widths на 390 px — **395 / 399 / 397**, на 320 px — **323 / 330 / 325**; їх не названо нульовим shell overflow і не приховано за body overflow-x:hidden.

Zoom не змінювався, global storage/персональний прогрес не очищалися. Viewport і theme відновлено до **1280×720 / light**. Проміжні pointer дії біля offscreen footer потрапили на сторонні local URLs; URL guard їх відхилив, у 18 accepted states не зараховано. Keyboard activation того самого existing theme button відновила керування; safety stop не обходився. Це діагностична навігаційна спроба, не контентна регресія. Screenshots/receipts приватні.

## Ізольовані тести: фактичні результати

Тести використовують `IsolatedTestEnvironment`: owned environment/storage/cache/view runtime, SQLite `:memory:`, array cache/session та sync queue. Робоча `.env` не завантажується; `assertSafeDatabase` перевіряє SQLite PDO/PRAGMA до schema operations. Жоден наведений isolated test не є дозволом працювати з реальною MySQL або прогресом.

Final bank 1.0.1 isolated summary — `e74ef8c5bdd31b496e02dc56531353cf588155f177bf3425a52c0a63dcf59676`. **Current-runtime run — один process / один fresh JUnit**, PHP **8.5.10 NTS / PHPUnit 12.5.35**, `opcache.enable_cli=0`, exact isolated XML; це не union попередніх reruns.

| Окремий run | Tests / assertions | Failures / errors / skips |
|---|---:|---|
| Current-runtime bank 1.0.1, 33 classes, один process | **214 / 45 270** | **0 / 0 / 0** |
| Із них M72 subset, ті самі 6 classes/той самий process | **39 / 9636** | **0 / 0 / 0** |
| Final M72-only formatting rerun v3, окремий process / 6 classes | **39 / 9636** | **0 / 0 / 0** |
| Historical literal-source/raw-lineage | **11 / 2549** | **7 / 0 / 0** |
| Historical literal-UI | **6 / 23** | **4 / 0 / 0** |
| SiteMode isolated profile | **10 / 39** | **0 / 0 / 0** |
| PPC interaction identity | **6 / 161** | **0 / 0 / 0** |

**39 M72 tests уже входять у 214**, не додаються до total. Решта — **27 classes / 175 tests / 35 634 assertions**; разом 33 / 214 / 45 270. Final окремий M72-only v3 ще раз перевірив ті самі 39 після EOF-only formatting: **exit 0**, failures/errors/skips/warnings **0**, один existing PDO deprecation / **35 triggering tests**. Він **не додається до 214** і не називається 253-test full rerun. Це targeted runs, не весь репозиторій; PPC/SiteMode окремі, production-host cases synthetic.

Final M72-only receipt: `564781ab1802e3fd475fbc433b94a6a9883236dc024cea183f0154809df9f090`; фактичний fresh JUnit: `adaa63942feb268f843fd9b46512f7aeec7bd7d45cc1ff49f27dcd64284633c6`. Configuration/isolated NTS runtime незмінні до/після запуску. Raw protection: **28 старих tests unchanged**, із 6 нових M72 — у `M72AuthoredReleaseTest`, `M72NativeMetadataTest`, `M72SharedLineageTest` видалено тільки один зайвий кінцевий LF, решта 3 raw unchanged. Для кожного з трьох `SHA256(current bytes + 0x0A) == tested v2 SHA256`; всі 34 test files незмінні під час final run. Жоден assertion або executable test byte не переписаний заради результату.

Final current-runtime CLI має **exit 0**, один existing PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` (207 triggering tests, не 207 різних deprecations). Historical-source exit **1**, 1 distinct deprecation/5 triggering tests; historical-UI exit **1**, deprecations **0**. Fresh PPC/SiteMode — exit **0**, по **1 deprecation**, failures/errors/skips **0**. Початковий standalone M72 v2 історично мав 39 / 9545 і exit 1 через **56 сторонніх discovery warnings + 1 deprecation**; це не підміняє final current run і не приховано як чистий старий exit.

Historical source failures: по одному M71SharedLineage, M70SharedLineage, M69AcceptedM68Lineage, M69BuilderSelectionDerivation та три M68SharedLineage assertions, що очікують попередній literal/shared-source стан. Чотири historical UI failures — старі literal markup assumptions `TheoryPracticeSetTokenBankUiTest`. Старі tests/snapshots не змінювалися. Новий M72 exact-hunk test і current runtime/preservation gates перевіряють чинний стан; historical failures не названі PASS і не приховані з denominator.

Початкові M72 runs мали нові test-only дефекти literal wording та choice-prompt paragraph whitespace. Виправлено лише нові tests, без послаблення semantic assertions або змін engine/старих tests. Надто широкий helper відхилено rerun; фінальне виправлення обмежене expected choice-prompt boundaries. Раннім failed receipts не приписується fresh v2 PASS.

Final regression receipt `6429c8ab7a13f866eedf2502af9696967ff3d2d11e1837f7baa25f5cb783a814` перевіряє active bank 1.0.1:

- **Node: один invocation / 8 files / 158 runner tests / 158 pass / 0 fail / 0 skips / exit 0**. Додані **17 formatter tests** охоплюють усі 15 selected questions, 30 correct/selected formatted strings та старий ID32781 negative control. M70/M71/M72 practice files враховуються як aggregate кожний; 158 не означає 158 унікальних M72 content cases.
- **M68 served-local: окремий invocation, 24 tests / 24 pass / 0 fail / 0 skips / exit 0**. Offline exact served factory, не HTTP/browser/SQL.
- **PPC isolated: 6 / 161; SiteMode isolated: 10 / 39**; окремі fresh runs, executable `C:/Program Files/xampp/php/php.exe`, **PHP 8.5.10 ZTS**. NTS current-runtime вище — інший exact executable/profile, результати не додаються як повторні унікальні tests.

Offline commands не читають робочу `.env` і MySQL. Sandbox bootstrap/network preflights, які не виконали tests або успішного GET, не рахуються PASS; same bounded commands після вузького дозволу завершилися. ACL/config/.env не змінено. JUnit/TAP/HTML/runtime receipts приватні.

## Незалежний scope review і Git delivery

Read-only review підтвердив **8 shared files / 12 hunks / 13 adds**, 4 generic primitives і старі tests без змін, **326** visual source hashes exact ROOT↔WT. Frozen original **13** файлів + **2** bank supplements = **15 raw pins**; нові M72 code/test/diagnostic files синхронні. Courses/standalone дизайн не змінено; question-family/progress preservation підтверджено final SQL/HTTP.

Navigation diagnostic також має лише один видалений зайвий кінцевий LF: `SHA256(current full bytes + 0x0A) == previously tested full SHA256`, ROOT = WT exact. Receipt `65e692439e42b8e98185bfe11b871dd630bbd4c45597697200460221519031de` підтверджує незмінний code prefix/SQL/source guard/transport; це **не новий HTTP run**. Navigation v2 evidence лишається окремим реальним run.

Усі local current UI/HTTP/SQL/test gates завершені. **Фактичний staged allowlist — рівно 47 files**, missing/foreign **0/0**: попередні 44 + два bank supplements + formatter regression test. CR-aware cached diff-check чистий. Raw index підтвердив **15 frozen pins**, 8 shared exact-hunk reversals до accepted base, 4 immutable primitives, 12 M71 index sources та 326 visual sources без drift; окремо served 326 visual / 2558 historical sources exact. ROOT/WT mirrors exact за винятком збереженого стороннього ROOT deletion `.gitattributes`; три названі Git/working EOL profiles обліковано вище, не нормалізовано.

До commit не входять `.env`, credentials, dumps/backups, private SQL snapshots/runtime identity, screenshots/proofs/nonces, JUnit/TAP/HTML, caches/generated assets, тимчасові routes, сторонні drafts або незавершені чужі зміни. Main і production не змінюються.

Доставка: scoped commit → normal push у `codex/seo-m72-partitives-negatives-geography-standard-view` → незалежний remote SHA == local HEAD. Full commit SHA та підтвердження доставки — у фінальній відповіді після виконання, не self-referential hash цього report.

Локальний M72 прийнято; стан Git delivery наведено окремо вище.
