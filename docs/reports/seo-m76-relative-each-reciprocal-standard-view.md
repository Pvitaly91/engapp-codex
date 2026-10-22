# M76 — Relative Pronouns, Each / Every / All, Reciprocal Pronouns

## Статус і межі

Локальні apply/no-op1.0.0 і1.0.1 та браузерне приймання трьох сторінок завершено. Окремий прямий дозвіл користувача дозволив мінімальне виправлення підпису токена в спільному конструкторі: тільки власне текстове значення label, інакше незмінений token.value. Дизайн, formatter, grader і правила практики не змінено. Фінальна незалежна звірка, tests та delivery описані нижче.

- Actual accepted base: `e0720938bad114c7e43e8e6efafa8355c1993fc1`.
- Робоча гілка: `codex/seo-m76-relative-each-reciprocal-standard-view`.
- Ціль виконання: тільки `http://gramlyze.loc`. Production, `.com`/`.ub`, `main`, PR, deploy, merge/rebase/force-push не входять у scope.
- Навчальні дані використовують чинні native-компоненти. Нових Blade/CSS/JS дизайну, view, grader, normalizer, formatter або автоматичного apply немає. Є рівно одна окремо дозволена поправка x-text BANK button у shared Blade; усі embedded script bytes та answer-chip binding незмінні. Новий JavaScript-файл у `tests/Browser` — offline test незміненої factory, не frontend implementation.
- Served ROOT має окремий стан і сторонні зміни. У робочу гілку включаються тільки перевірені M76 hunks; це не дозвіл синхронізувати весь ROOT чи переписати сторонні файли/EOL.

## Незалежний BEFORE і цільові сторінки

Незалежний локальний BEFORE знято до source sync/реєстрації M76. Raw capture, HTTP HTML, runtime identity та SQL-рядки залишаються приватними. SHA-256 `before-v1.json`: `b0cae6f629aae1b4354aefcf8d704b73030ef2022846c2cd1ea2f9f5d3a05985`.

| Урок / збережений theory H1 | Локальна сторінка | Page / category | Display | Technical SQL level | Own-practice focus |
|---|---|---|---|---|---|
| Відносні займенники | [Relative Pronouns](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/relative-pronouns-who-which-that-whose) | 129 / 20 | B1–B2 | NULL | B1 |
| Each / Every / All | [Each / Every / All](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/each-every-all) | 121 / 20 | A2–B1 | NULL | A2 |
| Reciprocal pronouns | [Reciprocal Pronouns](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another) | 127 / 20 | A2–B1 | NULL | A2 |

Owners залишаються у `Database\Seeders\Page_V3\PronounsDemonstratives`:

1. `PronounsDemonstrativesRelativePronounsTheorySeeder`.
2. `PronounsDemonstrativesEachEveryAllTheorySeeder`.
3. `PronounsDemonstrativesReciprocalPronounsTheorySeeder`.

BEFORE HTTP: три theory URL і три відповідні `/courses/english-grammar-theory/lesson/zaimennyky-ta-vkazivni-slova/...` повернули 200, кожний з одним H1. Theory та course H1 не прирівнюються: Relative course має `Relative Pronouns — who, which, that, whose`, Reciprocal course — `Reciprocal Pronouns — each other / one another`.

| Continuing practice | TextBlock | UUID | source index / sort | BEFORE → AFTER type |
|---|---:|---|---|---|
| Relative | 4140 | `9391a281-14c4-5d53-aea8-d14f4a73b3d5` | 10 / 11 | practice-set → practice-set |
| Each | 8937 | `c49b6f66-a028-59a7-819b-e73cc565709c` | 10 / 11 | practice-set → practice-set |
| Reciprocal | 4086 | `44d5c5f4-df97-5ac1-aa6d-ccc490091d7a` | 9 / 10 | box → practice-set |

Кожен урок має 12 continuing structured blocks + один subtitle: разом 36 + 3 = 39 UK TextBlocks. SQL NULL не замінюється ані точним CEFR, ані display range. Стан source level absent і відповідний SQL NULL збережено.

## Джерельні знахідки та авторські виправлення

Повністю прочитано три definitions і фактичний `JsonPageSeeder` loader. Loader використовує основні type/column/heading/css_class/body/level; legacy `content`, `columns`, `exercises`, `mistakes`, `summary_points`, `links`, `layout` та `hero_image` не є паралельними активними native payloads.

Relative:

- `The man who he called ...` має object who та subject he. Видалення he змінює ролі учасників; додано явне зіставлення, meaning task і full-sentence input.
- Who для домашньої тварини не названо універсально неправильним. Retained rewrite з dog явно просить that/which.
- Перевага that після superlative не перетворена на заборону which: best-film select приймає обидві доречні форми.
- Non-defining охоплює who/which/whose/whom; omission та that пояснено в межах конкретної конструкції. Кома сама не визначає кількість братів, розмір групи чи whole-clause reference.
- Whose визначає всю noun phrase, включно з прикметником; окремо пояснено interrogative whose та who’s = who is / who has.

Each / Every / All:

- Прибрано довільну максимальну межу «2–10». Відмінності індивідуального фокусу та повноти групи не означають обов’язкової одночасності.
- Узгодження прив’язано до фактичного підмета: each of us has, але we / the students each have.
- Додано every one of; не прирівняно його до everyone. All поєднується з plural і uncountable nouns.
- Of перед визначеною noun phrase може бути optional; all of us/them потребує of. All day та every day мають різний зміст.
- Not all/not every не означає none. Завдання не вигадують власності, різних назв книг або зміни числа books → a book.
- `All are welcome` перекладено як запрошення/радість відвідувачам, не як взаємне вітання.

Reciprocal:

- Сучасні each other / one another пояснено для двох і більшої групи; each other частіше. Немає жорсткого two-versus-more discriminator.
- Взаємність відділена від дії на себе через явні ролі тих самих учасників. Team не відкидається лише через морфологічну форму однини.
- Прийменники, possessive each other’s / one another’s, число наступного іменника, запитання, заперечення та прохання пояснено у visible basic.
- Збережено окрему функцію very good → very well у тому самому correction slot; займенник там не є помилкою.
- У `He talked ...` не замінено he на they для штучного ремонту reciprocal: збережено одного учасника та потрібний himself.

Це агентський author/semantic review, не людська редакторська рецензія. Повні джерельні рішення та OLD → AFTER map записані у frozen editorial/components/tasks, а не відновлені з самого AFTER як незалежний expected.

## Зовнішні мовні джерела і фактичний доступ

Мовні уточнення відокремлено від внутрішніх source findings. Вправи видавців не копіювалися; learner comments не використано як нормативний доказ.

| Джерело | Використана межа | Фактичний доступ |
|---|---|---|
| [British Council: defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-defining-relative-clauses) | Subject/object, alternatives та omission | Direct editorial body прочитано |
| [British Council: non-defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-non-defining-relative-clauses) | Known reference, whose, commas | Direct editorial body прочитано |
| [Cambridge: relative pronouns](https://dictionary.cambridge.org/grammar/british-grammar/relative-pronouns/) і пов’язані relative/whose/who’s сторінки у editorial | Pet who, ролі, whose phrase, whom, clause reference | Direct fetch403; прочитано офіційний publisher-indexed editorial body, не заявлено direct-body PASS |
| [Cambridge: each or every](https://dictionary.cambridge.org/grammar/british-grammar/each-or-every), [each](https://dictionary.cambridge.org/grammar/british-grammar/each), [all or every](https://dictionary.cambridge.org/grammar/british-grammar/all-or-every) | Кількість/фокус, agreement та group reference | Direct fetch403; publisher-indexed editorial body |
| [Cambridge: all](https://dictionary.cambridge.org/grammar/british-grammar/all), [every](https://dictionary.cambridge.org/grammar/british-grammar/every), [word order: all](https://dictionary.cambridge.org/grammar/british-grammar/word-order-all) | Of, mass/plural, partial negatives, intervals і position | Publisher-indexed editorial body; для every/word-order direct success не заявлено |
| [British Council: reciprocal pronouns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/reciprocal-pronouns) | Взаємність, reflexive contrast, possessives | Direct editorial body, без learner comments |
| [Cambridge: each other / one another](https://dictionary.cambridge.org/us/grammar/british-grammar/each-other-one-another) | Сучасна близькість, частотність, написання й possessives | Canonical direct fetch403; офіційний indexed body через domain-filtered search |
| [Google: helpful, reliable, people-first content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) | Самодостатність, оригінальна навчальна цінність, відсутність word quota та вигаданих authors | Direct official body прочитано; це орієнтир самоперевірки, не гарантія індексації |

## Canonical component map і legacy migration

Relative та Each зберігають усі 12 джерельних native types/columns/order. Порядок, identity/UUID, locale, sort, category, CSS, tags і зовнішні relations не перепризначаються. Details: **0 / 0 / 0**, явне семантичне рішення finite author projection. Короткі пояснення, переклади, застереження та продовження прикладів лишаються visible basic; runtime word-count threshold відсутній.

Reciprocal — in-place міграція того самого inventory:

| Index | BEFORE | AFTER | Column |
|---:|---|---|---|
| 0 | box | hero | header |
| 1 | box | forms-grid | left |
| 2, 3, 4 | box | usage-panels | left |
| 5 | comparison-table | comparison-table | left |
| 6 | box | usage-panels | left |
| 7 | box | mistakes-grid | left |
| 8 | box | summary-list | left |
| 9 | box | practice-set | left |
| 10 | box | forms-grid — дві native linkcards | footer |
| 11 | navigation-chips | navigation-chips — дев’ять пунктів | footer |

Рівно **10 type transitions + 4 heading→NULL = 14 metadata field transitions на 10 continuing rows**. Це не 14 додаткових SQL row updates. Усі levels/columns/UUIDs/timestamps зберігаються. Старі secondary payloads видалено лише за exact full-source SHA та повним поелементним removal list; дані одноразово спроєктовано у native body. Старі static tasks не губляться всередині `exercises` або HTML.

Мінімальний opt-in `legacy_box_projection` у чинній primitive не є новим renderer. Він прив’язаний до owner/slot/raw block SHA, підтримуваного type/column, NULL/CSS і точної metadata transition. Writer M76 дозволяє тільки `body`, `type`, `heading` для UK rows і `Page.text`; generic default інших releases не послаблено. Незалежний BEFORE лишається raw, не projected.

Важлива фактична межа caller: `theory/show` обирає `firstWhere('navigation-chips')`. Дві такі групи приховали б дев’ять останніх пунктів. Тому index10 використовує вже наявні URL-backed forms-grid linkcards, а index11 — єдину navigation-chips групу. Збережено обидві identities та всі 2 + 9 labels/current/URLs; Blade/design не змінено. Comparison title `Кожен учасник чи вся група?` не прирівнює all до одночасності.

Курси та standalone help отримують exact raw BEFORE на клонах: body, type, heading, column, level, CSS; оригінальні Eloquent models не мутуються. Restoration працює лише для повного exact AFTER binding; foreign/stale/partial моделі залишаються full-stored fallback, без SQL queries.

## Власна практика — 46 graded + 2 demos

| Урок | Selects | Meaning choices | Inputs | Graded rephrase | Усього graded | Ungraded demos |
|---|---:|---:|---:|---:|---:|---:|
| Relative | 5 | 2 | 6 | 3 | 16 | 1 |
| Each | 5 | 2 | 6 | 3 | 16 | 1 |
| Reciprocal | 5 | 2 | 2 | 5 | 14 | 0 |

- 34 старі навчальні позиції збережено/виправлено/перенесено: 12 + 12 + 10. Останні десять Reciprocal були static HTML/exercises, не десятьма старими працюючими graded controls.
- 12 нових позицій: на кожний урок 2 meaning choices + 2 full-sentence manual/token inputs. Тільки ці нові позиції мають нові стабільні IDs; відсутність старих task IDs/source_index не підмінено вигаданими значеннями.
- Два source demos залишаються неоцінюваними. Reciprocal demo не вигадано.
- Усі graded tasks мають явні непорожні accepted arrays і plain literal trim-lower feedback для accepted values та видимих options.
- Власні inputs не підміняють окремий нижній п’ятипитальний widget. У нових назвах немає слова «поліглот».

| Урок | Full-sentence input | Другий full-sentence input |
|---|---|---|
| Relative | `The man who he called is my boss.`; whom/that/omission і manager у заявлених комбінаціях | `I know the woman whose new bike is blue.`; bicycle |
| Each | `Each of us has a ticket.`; Every one / We each / We all / All of us у заявлених моделях | `All the books are on the table.`; All of the |
| Reciprocal | `We listen to each other.`; one another | `Do they know each other's names?`; one another’s |

Рівно **6** full-sentence inputs, а не 8: Relative[4,5], Each[4,5], Reciprocal[0,1]. Токени shuffle, chunks1–3слова; manual, token, clear-return-rebuild і mixed paths — окремі перевірені сценарії.

Normalization boundaries незміненого engine:

- Порожнє значення не є відповіддю для omission. У retained Relative input приймається непорожнє `Займенник не потрібен`.
- Own grader не гарантує суворе оцінювання commas/terminal punctuation. Потрібна пунктуація залишається у visible canonical answer, але бал не видається за proof редакторської пунктуації.
- Whose та who’s не колізійні так само, як its/it’s: після чинної нормалізації `whose` і `whos` різні.
- Apostrophe-only Reciprocal correction у тому самому old rephrase[1] перенесено до semantic A/Б: приймаються `речення Б` / `Б`; неправильний вибір, empty та обидва English stimuli як відповідь реально відхиляються. Це не новий apostrophe-sensitive grader.
- Не змінюються учасники, число книг чи напрямок дзвінка для отримання механічно «правильної» відповіді.

## Окремі sentence builders — точні банки, 15 reused

Frozen contract: `docs/content/theory-sentence-builders/m76-relative-each-reciprocal.v1.json`.
Audit: `docs/content/m76-sentence-builder-bank-audit.v1.json`.

| Урок | Exact bank у `Database\Seeders\V3\Polyglot` | Actual type-4 pool / technical filter | Selected IDs | Selected levels | Live5/5 |
|---|---|---|---|---|---|
| Relative | `PolyglotRelativePronounsAllLevelsLessonSeeder` | 72 / NULL | 33928, 33930, 33933, 33936, 33937 | 5 × B1 | 5/5, settled sentences звірено |
| Each | `PolyglotEachEveryAllAllLevelsLessonSeeder` | 72 / NULL | 47523, 47527, 47528, 47531, 47533 | 5 × A2 | 5/5, every видно; settled sentences звірено |
| Reciprocal | `PolyglotReciprocalPronounsAllLevelsLessonSeeder` | 72 / NULL | 33843, 33847, 33851, 33852, 33854 | 5 × A2 | 5/5, shift видно; settled sentences звірено |

15 selected / 15 reused / 0 new. Нових questions/options/answers/links або bank supplement немає. Actual matcher при NULL використовує всі рівні точного explicit-linked банку; старе diagnostic eligible_count=0 від строгого SQL NULL не є цим matcher pool.

Audit зберігає 216 pool snapshots: 15 selected + 201 scope exclusions. Scope exclusion не означає, що питання неправильне, і не видається за повний мовний acceptance всіх 201 питань. Casing, multiplicity, ordered answers і complete original option/answer/pivot/tag attributes збережені; finite presentation змінює лише явну умову/відбір на клонах.

Registry BEFORE: **148** попередніх records, failures0/writes0. Actual AFTER — **151**, failures0/writes0: усі148 owner-keyed structural records збережено, додано тільки три M76 owners. Не постійний глобальний ліміт. Raw HTTP hashes порівнюються окремо, byte-identical HTML не заявляється. Receipt SHA `aa63da001d96ed2e2a40999adbc2dd43bd9f251bce56345e4eb27ad24589a071`. Окремий M76 audit:3targets/0failed/0writes, SHA `9680416b5afe46e07f8ea7e1834d23cfb2edca598696085709958ce997c09133`.

## Frozen artifacts і checksums

Active version1.0.1: manifest `docs/content/m76-source-checksums.v1.0.1.json`, SHA-256 `3a8d147f12436087489fcba478f9fcda6442fdf28c3c4c5b550909b0f64f2e6c`. Історичний manifest1.0.0 збережено byte-exact, SHA-256 `c1006fa98d4cca96bb0bbcb4177737210cdef646c518675dad5f1671777b923c`.

| Artifact | SHA-256 |
|---|---|
| Active master1.0.1 | `33fa160344fcc980d8d980aadf19e0db03212b1a54a443100e2f24c3bc033738` |
| Preserved master1.0.0 | `acd759984a5e3129d46137ab8f99227f3944311da8b8617687f94308c27f681b` |
| Source BEFORE | `08184ba09866bbf50d7f26a497ffa509c53da2f125f65c33ab7da4cd4854caad` |
| Active patch1.0.1 | `e5320ab32be7f15684797173a46f68892fc492e2821b2c41f6306cd730c8e5fc` |
| Preserved patch1.0.0 | `ab79812b334f489898f2820ed6115b6b5d01372135169a0310063a54b845e3d3` |
| Builder contract | `77abdac91ab3fa6eedba9b19ae4ccf241f73c8ac004e874b1255fd6e48419789` |
| Bank audit | `efb4bffc246ee8779cef476fd5c0fd956163735d59e4788295d2e19d41a8fffc` |

Manifest також pin-ить components/tasks/editorial/readable author edition і три active definitions. Pre-hash profile: UTF-8 без BOM, LF, один final LF, trailing whitespace0. Frozen дані не «підчищаються» після обчислення SHA.

Anchor-only successor1.0.1 створено окремими новими master/patch/checksums, не переписуванням1.0.0. У першому strong Reciprocal hero intro додано лише `id="block-4077"`, щоб зберегти старий anchor continuing row4077. Відповідний expected intro SHA та version оновлено; навчальний текст, усі46tasks, accepted arrays, feedback,15widgetquestions і компоненти не змінено. Exact lineage declaration: `docs/content/m76-anchor-revision.v1.0.1.json`, SHA `9d30fa87cd71ba53730f7f48d77eec73fb66c245f4149db2a296ee64a8b1bbfb`. Offline test відновлює тільки ці три master deltas і порівнює весь master з pinned predecessor; довільні інші зміни не дозволено.

Source-sync-v2 прив’язує26точних paths і окремі reviewed ROOT/WT pins. Це не твердження, що ROOT і WT мали тотожні backend bytes, і не whole-tree replacement. Окремий локальний anchor-only apply для вже застосованої1.0.0 виконано; нижче наведені його власні докази.

## Автоматичні тести — виконані результати

Усі PHP runs нижче — guarded isolated SQLite `:memory:`, testing environment, окрема environment path, array cache/session, CLI OPcache off. Робоча `.env` не завантажена. 675 protected files: changes0 у кожному наведеному run.

| Run | Tests | Assertions | Failures / errors / skips | Warnings / deprecations | Exit |
|---|---:|---:|---|---|---:|
| Fresh BEFORE `m76-before-m75-own` | 33 | 9529 | 0 / 0 / 0 | 0 / 1 | 0 |
| Fresh BEFORE `m76-before-preservation` | 136 | 27201 | 14 / 0 / 0 | 0 / 1 | 1 |
| Fresh BEFORE `m76-before-generic` | 30 | 14546 | 0 / 0 / 0 | 1 / 1 | 1 |
| Fresh BEFORE old saved-test discovery | 0 | 0 | No tests executed | 1 / not reported | 1 |
| AFTER `m76-after-m75-own` | 33 | 9529 | 0 / 0 / 0 | 0 / 1 | 0 |
| AFTER `m76-after-preservation` | 136 | 27204 | 14 / 0 / 0 | 0 / 1 | 1 |
| AFTER `m76-after-generic` | 30 | 14567 | 0 / 0 / 0 | 1 / 1 | 1 |
| `m76-projection-retry` — early subset | 8 | 87 | 0 / 0 / 0 | 0 / 1 | 0 |
| `m76-frozen-own` — frozen1.0.0,7files | 39 | 10453 | 0 / 0 / 0 | 0 / 1 | 0 |
| `m76-v101-own` — перша1.0.1 спроба, stale test version assertion | 39 | 10434 | 1 / 0 / 0 | 0 / 1 | 1 |
| `m76-v101-corrected-own` — active1.0.1,7files | **39** | **10453** | **0 / 0 / 0** | **0 / 1** | **0** |
| `m76-anchor-synthetic-v2` — окремий one-body writer | 6 | 82 | 0 / 0 / 0 | 0 / 1 | 0 |
| `m76-independent-profile` — SiteMode / PageMetadata | 15 | 75 | 0 / 0 / 0 | 0 / 1 | 0 |
| Final display successor — усі9M76files | **46** | **10575** | **0 / 0 / 0** | **0 / 1** | **0** |
| Final unchanged old M75 suite після дозволеної display-поправки | 33 | 9522 | 1 / 0 / 0 | 0 / 1 | 1 |
| Final exact wider preservation | 136 | 27204 | 14 / 0 / 0 | 0 / 1 | 1 |
| Final exact generic | 30 | 14567 | 0 / 0 / 0 | 1 / 1 | 1 |
| Додатковий interaction complement,11files | 134 | 1588 | 8 / 1 / 0 | 0 / 1 | 2 |

Кінцевий M76 run: content8/2582, builder6/4770, legacy projection7/76, queryless runtime7/2468, transactional writer9/526, explicit saved wrong-answer1/11, actual native factory wrapper1/20. Evidence1.0.0: private `m76-frozen-own-82cea388c7d24303983a8387875e4a89-result.json`; active1.0.1: `m76-v101-corrected-own-76a25351da3f4d26a2fe436f98113a88-result.json`.

Перша1.0.1 спроба зупинила лише offline factory wrapper на старій test-only умові `version === 1.0.0`;38інших tests пройшли. Виправлено тільки новий тест: exact active policy version з finite1.0.0/1.0.1 та додаткова whole-master anchor-only lineage перевірка. Frozen artifacts, runtime, grader і assertions старих tests не змінено. Повний повторний run, а не лише один case, завершився39/10453PASS.

Покриття нових tests: exact source/metadata/removal pins; secondary-field/body/UUID/order/owner/column/NULL drift; старий M75 default не отримує legacy-box permissions; full-stored fallback; theory-only caller boundary; course/standalone old-body/type/heading clones без original mutation; rollback після першого й останнього update; no-op; exclusive backup; foreign/partial/stale target; progress/source/relation guards; три continuing practices без inserts. Synthetic writer banks не видаються за live thematic-bank acceptance.

BEFORE preservation має ті самі 14 явних failures, що були зафіксовані до M76:

- 2 старі `M74ContentPatchTest` fixture/ID та shortage очікування.
- 5 `M74BuilderSelectionPresentationTest`: historical audit `v1.json` проти active `v1.0.1.json`.
- 3 `M74OwnedSharedSourceDeltaTest`: whole-file/shared/controller/visual SHA очікування.
- 1 `PracticeCompletionPackageTest`: exact expected не включає наявний runtime linked_practice для Zero Conditional. Це current-runtime mismatch, не лише старий hash.
- 3 `M73SharedLineageTest`: historical shared-source/primitive/visual SHA очікування.

Ці тести не переписано й не послаблено, baseline не названо green. AFTER preservation повторив ті самі14failure methods/classes; нових failure methods не додалося. Whole-file actual SHA закономірно відрізняються після scoped registration/primitive changes, тому це не claim byte-identical повідомлень. AFTER generic зберіг warning про невиявлений старий saved-test class, без assertion failures/errors; AFTER M75own лишився33/9529PASS. Runs із перекриттям не додаються як кількість унікальних tests;8early projection/retry вже входять до39, а9active M74 cases перетинаються у wider suites.

Старий `SavedTestStepWrongAnswerTest` з `/** @test */` PHPUnit12 не виявляє. Новий discoverable wrapper явно викликає його фактичний метод: wrong POST → redirect → GET із користувацькою відповіддю/кольоровими spans, PASS. Це не вигаданий corrected-submit retry: повний wrong→correct→empty/reset для own practice окремо виконано actual factory. Один deprecation у PHP runs збережено явно; його точна причина не розкрита в output, тому не вигадується.

Actual unchanged native factory: frozen package PASS для обох exact profiles — WT і independently captured served ROOT. На кожному: **46graded,2demos,80accepted values,49displayed options,46wrong→correct→empty retry paths,6full-sentence inputs × 4paths =24,21semantic cases**. Перевірено manual/token/delete-return-rebuild/mixed, repeated Check без подвійного score, незалежність інших груп, word-suggestion suppression. Network/SQL/filewrites0; input bytes unchanged. Mock focus calls81 — це VM stub, не доказ відсутності browser-native focus UI. Factory test не підміняє live browser acceptance.

Дозволена display-поправка має окремий discoverable `M76TokenLabelDisplayTest`: фактична x-text expression і bundled Alpine evaluator,110cases(11fixtures×10tokens),8own-string labels/102literal fallbacks,0functioncalls/0prototype getter reads/0reactive writes. Selected-answer chip лишається `token.value`. Перший combined run46/10304 мав2test-only failures: WT vendor dependency path та закритий старий M76widget profile. Виправлено тільки нові tests: Composer dependency-root resolution і pin sidecar +exact one-binding reversal до початкового frozen audit; arbitrary hash tuple не допускається. Повний повторний9file run46/10575PASS, private evidence `m76-display-final-own-9f86a1ee39bb42848f0aa74b88cf4df5-result.json`. Targeted lineage6/4792 також PASS; ці runs перекриваються й не додаються як унікальні tests.

Фінальний старий M75 suite чесно не green:32із33tests PASS, один `M75PronounFormsRuntimeTest::test_native_visual_and_interaction_sources_and_active_m74_artifacts_are_unchanged` зупиняється на повному старому widget hash. Це **нова діагностична відмінність після прямо дозволеної однорядкової display-поправки**, не приховано як частину14старих baseline failures. Exact sidecar та новий M76 test доводять єдину зміну та незмінність embedded JS/formatter. Старий M75 test не переписано/не послаблено; решта M75content/supplement/selection/explicit-absence cases пройшла. Evidence `m76-display-after-m75-e14165a24f1e4dbb9b68cdac1fac3aa5-result.json`.

Фінальний exact wider136 повторив14→14тотожних class::method failures, added0/removed0; private evidence `m76-display-after-preservation-467599caa84544e09db2ef5b4facab30-result.json`. Exact generic30/14567 повторив тільки старий discovery warning, assertion failures/errors0; `m76-display-after-generic-c8f7c7e88ce944afbb23bbcb52b3a0d3-result.json`.

Додатковий interaction complement не мав повного окремого BEFORE run і не видається за before-green regression suite. Його actual134/1588,8failures+1error задокументовано без зміни старих tests:4`TheoryPracticeSetTokenBankUiTest` шукають стару literal<input>/span/@foreach розмітку у власному practice-set Blade, який побайтово збігається з independent BEFORE;2`SavedTestClientAnswerLogicTest` очікують старе manualWordIndex присвоєння без вже наявної passive branch у незмінених card-easy/step-easy/helper;1`ComposePayloadBuilderTest` очікує the, тоді як exact BEFORE PastPerfectBasicsB1V3 q04/a10 уже містить that. Ще1failure+1error у `FeatureSavedTestJsStateTest` спричинені відсутньою SQLite fixture table question_hints: helper створює verb_hints, а controller eager-loads hints до рендеру. Для цих двох schema cases formal BEFORE run відсутній; їх не названо доведеним baseline PASS чи новою runtime регресією. Extra evidence `m76-display-interaction-ccf944af93054d14af88049bf944c449-result.json`. У цьому самому run пройшли PPC6/161,ComposeTokenCase5/14,AcceptedVariants6/213,UnitSavedJs19/39,UnitSynonyms1/4,M68feedback27/385 та explicitwronganswer1/11. Усі675protectedfiles unchanged; робоча MySQL не використовувалася. Окреме виправлення цих історичних tests/schema не входило до M76.

## Guarded local apply і незалежні postconditions

1.0.0 reviewed preview та реальний transactional apply: **42update operations на42distinctrows,0inserts,0deletes** — 3Page.text +39UK body (3subtitle +33teaching/navigation +3practice).10metadata rows /14changed metadata fields входять до тих самих continuing TextBlock updates, не42+14нових operations.

Незалежний `final-readonly-v1.json` підтвердив backup==reviewed preview, усі39continuing UK rows,56non-UK rows unchanged, усі старі rows/timestamps поза exact approved updates, normalized BEFORE та protected projection exact на46SQLtables. Gate сам виконав0DBwrites/0HTTPrequests. Усі три continuing practice IDs/UUIDs/NULL збережені, жодного нового TextBlock.

Повторний фактичний apply з reviewed `preview-v1` повернув `no-op`, updated0/inserted0/deleted0; unused no-op backup не створено. Тимчасовий nonce proof route видалено: route bytes exact restored, physical hashes unchanged, окремий GET endpoint404 без follow redirects. Це receipts для1.0.0; після source-only successor1.0.1 потрібні окремий anchor apply/no-op і фінальні active-source postconditions.

Під час1.0.0 gate підтверджено326visual files та2642historical frozen files unchanged, source-sync-v1/preview sources exact. Після anchor окремий gate підтвердив active1.0.1/source-sync-v2; після дозволеної display-поправки застосовується новий finite gate, не переписування старих receipts.

1.0.1: окремий свіжий nonce proof → exact one-body preview → exclusive backup → transaction → repeated no-op. Змінено тільки body рядка4077,0inserts/0deletes. Разом з початковим apply: **43 update operations на42distinct rows**, а не43нових рядки. Обидва unused no-op backups не створено.

Anchor preview digest: `f429d09a51b0d6f3a8b62ecfd44f7b9152922cb741718cda4f6b04344364fa8d`; backup raw SHA `ab8636b1c0c98b284be88cdba2e710480f29c1175647aecfc6b18cb96aa54b1a`. Незалежний `anchor-final-readonly-v1.json`, SHA `5f6c6f732b87d551e5107d9ab8934e4116d2d4c5e05eaff387aac341e18a162b`, нормалізує тільки цю body-зміну до фактичного1.0.0 AFTER, а потім відтворює original pre-M76 BEFORE усіх46таблиць. Старі рядки/timestamps,39UK і56nonUK, protected projection,326visual і2642historical frozen файли перевірені. Записів/HTTP у gate0.

Перший anchor gate зупинився на diagnostic-only зверненні до відсутньої captured schema нецільової таблиці. Оригінальний gate/plan pins не переписано. Окремий `m76-anchor-postconditions-v2.php` звіряє наявні captured schemas і обов’язково всі46повних row fingerprints; він пройшов повністю. Це не повторне застосування контенту. Synthetic tests теж мали першу test-fixture помилку realpath ще не створеної папки; після виправлення тільки setup6/82PASS.

Другий тимчасовий route також видалено, exact foreign-inclusive routes bytes відновлено та окремий GET404 підтверджено. Cleanup SHA `91da53987f4ef1e2d8126de68d398dcaed283718fa33f4b8d9e107303878a753`. У реальному DOM є рівно один `#block-4077`, на наявному strong hero intro. Learning text і plain SEO descriptions не змінені anchor-поправкою.

Фінальний read-only gate після дозволеної display-поправки: `tools/diagnostics/m76-anchor-postconditions-v3.php`; receipt `anchor-final-readonly-v2.json`, SHA `c338abcbf44011f58b737f5c20b3b26fe0fe81485bd6e13d33b02f13841fc0e6`, exit0. Усі46таблиць exact збігаються з попереднім anchor-final і нормалізуються до обох predecessor states. Перевірено325visual raw-unchanged +1exact authorized binding,2642historical frozen unchanged, усі26source-sync-v2 paths, full embedded script та відповідні formatter bytes ROOT/WT. Записів у БД/HTTP у цьому gate0. Sidecar SHA `7b705e98f0c2be7593d1e9cb0baad57a8562c151d684ccc150f0b374b8146edd` дозволяє рівно один coherent before/after профіль у кожному checkout; зворотна заміна однієї expression відновлює точний старий raw hash. Це не глобальний visual allowlist.

Metadata/navigation gate `nav-metadata-v3.json`, SHA `0f2ed765794752c9091b119d4b529a6e5258d63f5cf4ebdffdb5dfd2847215e3`:9destinationGET200,29source items(27chips+2linkcards),26clickablelinks, усі anchors і protected H1/title/canonical/breadcrumb/LearningResource/robots збережені; expected descriptions обчислені до apply. Під час GET усі46таблиць незмінні.

Protected-consumer gate перед дозволеною display-поправкою: `after-old-consumers-v1.json`, SHA `0fe4d10992627885e80a1e8496cb9959bbfbcc6067071b2f0f640535580407a3`:24/24GET200(3course+12SSR+9JSON),6saved tests/432old questions, old course/standalone learning DOM і question payload projections exact до незалежного BEFORE. Усі46таблиць до/після GET незмінні. Цей receipt збережено, а не переписано під новий Blade hash; глобальна однорядкова зміна label binding обліковується окремо.

Окремі **повторні HTTP gates після display fix** також пройшли. `after-old-consumers-v2.json`, SHA `7af45f201b28016d216c3ac3e1fae5f774a1142465f6001cb3ddcff2eee1a99b`:24/24GET200,6saved tests/432questions,3course learning DOM і21SSR/JSON payload/facts strict-exact до original BEFORE; жодної normalization/replacement не знадобилося й не виконано. `nav-metadata-v4.json`, SHA `4b24d571e5986898e84a311176f4fbdad8b4720bbd95fe197120d1846cb727d6`:9/9GET200,29sourceitems,26clickable links, повні metadata/H1/anchors/footer checks. Обидва gates перевірили всі46SQL fingerprints до/після GET як exact finalv3; writes0, sources unchanged,325visual+1finite approved exception та2642frozen unchanged. Raw whole-page SSR/auth chrome не порівнювалися; neighbors перевірено resolver/source/native metadata та GET, а не вигаданим neighbor BEFORE DOM. Нові три diagnostic-only files не переписують старі wrappers/receipts і не дозволяють записів у БД.

## Visible-output / live acceptance

| Перевірка | Relative | Each | Reciprocal |
|---|---|---|---|
| Реальна target page після apply, H1/metadata/anchors/navigation | PASS | PASS | PASS; anchor4077 збережено |
| 1440×1000 /390×844 /320×844, light/dark | 6фактичних станів | 6фактичних станів | 6фактичних станів |
| Own graded output | 16/16 у UI | 16/16 у UI | 14/14 у UI після дозволеної поправки |
| Обидва full-sentence manual/token inputs | 8task/mode/size cases PASS | 8task/mode/size cases PASS | 8task/mode/size cases PASS |
| Окремий thematic widget до5/5 + settled sentence після Check | 5/5, кожне settled речення звірено | 5/5, включно з every і final period | 5/5, включно з shift і двома the |
| Курс / standalone fallback і попередні M75 targets | protected clone/HTTP PASS | protected clone/HTTP PASS | protected clone/HTTP PASS |

Окремий prefreeze formula SSR/320 preview не є live target-page acceptance. Наведена матриця спирається на реальні сторінки після apply. Print, no-JS, повний accessibility audit та мовне/UI-проходження всіх216pool rows — not_checked, не PASS. Для відсутніх point details owner/keyboard/deep-link acceptance — N/A, не вигаданий positive case.

Relative: перший automation locator path охопив також answer chips і видалив повторний is; тестовий прохід перезапущено та завершено BANK-only path до5/5, з двома is у правильному видимому реченні. Це не зміна engine і не PASS невдалого запуску. Each після дозволеного display fix завершив усі п’ять питань, включно з We meet every Monday morning. Reciprocal завершив усі п’ять, включно з The volunteers encouraged one another during the long shift. та двома окремими the у питанні про сестер. Після Check прочитано фактичні settled sentences, не тільки score.

Private browser receipt `browser-acceptance-v1.json` збережено як неповний історичний прохід із явними initial failures. Новий `browser-acceptance-v2.json`, SHA `e06e28c1710e87d2f88d79e296c5f54c9437adad5a900f47a7552e6dbbb0848b`, додає завершені результати:18унікальних actual viewport/theme станів,24full-sentence task/mode/size cases,46own graded і триwidgets5/5. Старий failed Reciprocal record не вилучено: окремий наступний record містить правильні scores5/5,2/2,2/2,5/5. Semantic apostrophe choice А спочатку отримав4/5, після виправлення на Б —5/5. На Reciprocal після fix перевірено також dark→light round trip; fresh Each/Reciprocal tabs не мають error/warn logs. У Relative/Reciprocal learning cards не мають horizontal overflow; у Each comparison native table має contained overflow:auto (576px усередині252/322px), не learning-body overflow. Стан decorative shell окремий. Перегляд не є WCAG/print/no-JS сертифікацією.

Початковий blocker і дозвіл: сторонній ROOT component задавав `token_labels` як JSON array і читав label без own-property/type guard. Для every/shift це успадковані методи Array.prototype, які Alpine намагався виконати. Для shift replay фактичного bundled коду відтворив незакритий reactive batch і вимкнений tracking, через що не оновлювалися також власна практика, sidebar та theme. Користувач прямо дозволив мінімальне виправлення відображення токена. Тепер BANK x-text приймає лише власний string label, інакше показує token.value. Зміна застосована окремим точним hunk у ROOT та WT; сторонні ROOT features не скопійовано у гілку. Дизайн, token values, answer chips, embedded JS, grader і formatter не змінено. Заморожений sidecar `docs/content/m76-token-label-display-fix.v1.json` pin-ить обидва профілі та exact reversal до старих bytes; початкові author packages/receipts лишаються незмінні.

## Delivery

Final scope review, frozen-source validation та staged allowlist виконано:0route diffs,325/326visual unchanged +1explicitly approved display-binding exception.22entries обох frozen manifests(історичні artifacts і active artifacts/definitions) мають exact staged raw bytes/length; staged Blade зберігає accepted CRLF і coherent approved raw/LF profile. Не включено `.env`, секрети, дампи/backups, приватні proofs/SQL/HTTP captures/screenshots, caches/generated assets, authoring-only drafts/scripts або сторонні dirty hunks. Історичні звіти не переписуються.

Whitespace: звичайний staged `diff --check` після accepted CRLF профілю повертає nonzero тільки через5вже pinned EOF blank-line exceptions у нижченаведених нових anchor workflow/test files. Їх не нормалізовано після фактичного apply/receipts. Finite checker перевірив exact path+rawSHA+EOF для всіх5; інших whitespace errors0. `core.whitespace=cr-at-eol,-blank-at-eof` перевірено разом із цими finite pins; безумовний default-check PASS не заявляється.

| Збережений EOF exception | Raw SHA-256 |
|---|---|
| app/Console/Commands/ContentPatchM76Anchor.php | 38c75c106917f875d49a1a46d99599b1b276bfeb8a5b743b4f37fefd9f7a1505 |
| app/Services/M76AnchorPatch.php | e96b34b2f89fa314ce25f4ed950160e5263a48d7476cda44a3c8d40bdc5fa514 |
| tests/Feature/M76AnchorPatchTest.php | 3ea9a89ab4e2386cf2665f49b64d2a76d718694fbaac4c38029743bb0ba2c965 |
| tools/diagnostics/m76-anchor-postconditions-v2.php | 08fb1f397ef56a2595fca2573a5a171ae61f39fbb7ee602d2730e4b47bd7d2d2 |
| tools/diagnostics/m76-anchor-postconditions.php | e5a69523214962f44c9b9d1bec747d76467e89594125f5e8227b7781a2ff448b |

Implementation commit: `0775763d856d6ad2bf484b65e360e3ad194cded0` — `feat(content): complete M76 pronoun theory and practice`,66explicit files. Normal push виконано тільки в `codex/seo-m76-relative-each-reciprocal-standard-view`; окремий `git ls-remote --heads` підтвердив remote SHA == implementation HEAD. Цей delivery record додано окремим documentation-only follow-up, без зміни implementation/artifacts. Поточний фінальний full HEAD наведено у повідомленні доставки; усі implementation bytes залишаються у зазначеному commit. Production, `main`, PR і deploy не змінено.
