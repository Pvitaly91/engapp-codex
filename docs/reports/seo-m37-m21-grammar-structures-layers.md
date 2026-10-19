# M37 — M21 Gerund/Infinitive / Relative Clauses / Negative Inversion

Дата локального приймання: 2026-10-06. Scope: три точні UK theory owners M21, тільки `http://gramlyze.loc`.

M37 застосовано до робочого `gramlyze.loc`. Реальний transactional apply, SELECT postconditions, repeated no-op, nonce cleanup, after HTTP comparison, PHP/Node/Vitest та live browser acceptance завершені.

## Версія, межі та Git

Accepted base: `f6f1f22e00103118dd1641bbcf29ed4e69a9a33a`, гілка `codex/seo-m36-m20-modals-subjunctive-layers`.
Робоча гілка M37: `codex/seo-m37-m21-grammar-structures-layers`.
Ізольований checkout: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
Реальна application/document root: `D:/DEV/htdocs/gramlyze.loc`, локальна MySQL `gr2`.

Основний checkout зі сторонніми незавершеними змінами не reset/stash/clean. ROOT не використовується як commit source. Синхронізуються тільки finite M37 sources; shared views мають additive M37 hooks зі збереженням початкових ROOT/PPC hunks. Existing M26–M36 author packages не переписуються. Окрема accepted M28 Inversion Basics залишається власним уроком, а не частиною нового C1 runtime.

Не виконуються main/PR/merge main/force push/deploy/workflow dispatch, dependency update, Apache/XAMPP/hosts changes, working seeders/migrations, cache/session clear або повний DB restore. `.com`/`.ub` не запитуються. Production canonical як рядок у локальному HTML не є production HTTP перевіркою.

Accepted-base ancestry перевірено, exit 0. Основний ROOT HEAD залишився `41820a2bebdf69004fa7209a2a38457f93efabbd` у `codex/production-ready-a14788dac`; prior PPC checkout HEAD `cfbbfa57929b779a9ef315cb178f7d08ba46c9e4`, його 561 staged file не змінено. Commit scope — тільки 36 перевірених M37 файлів; staged allowlist/secret/private artifact audit описаний нижче. Full commit SHA і remote equality фіксуються у фінальному handoff після normal push.

## Точні URL, identities та accepted blobs

Повний префікс identities: `Database\Seeders\Page_V3\`.

| Урок / theory URL | Identity suffix | Category ancestry / level / page ID | Accepted definition Git blob | M37 point details |
| --- | --- | --- | --- | ---: |
| [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | `VerbPatterns\AdvancedGerundInfinitivePatternsTheorySeeder` | `verb-patterns` root / B2 / 286 | `166cf0821826cf55a90347ccb57db61eba91884a` | 0 |
| [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | `RelativeClauses\ComplexRelativeClausesTheorySeeder` | `relative-clauses` root / C1 / 300 | `e6ac6befb2eb11ec3f1584cfc4b59054ce1212ca` | 0 |
| [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | `BasicGrammar\WordOrder\InversionAfterNegativeAdverbialsTheorySeeder` | `basic-grammar → word-order` / C1 / 295 | `72e194f72431210c1c80708b192064809a07468b` | 0 |

Відповідні test URL, підтверджені actual linked-bank inventory і fresh guest GET:

- [Gerund/Infinitive — test](http://gramlyze.loc/test/verb-patterns/advanced-gerund-infinitive-patterns).
- [Relative Clauses — test](http://gramlyze.loc/test/relative-clauses/complex-relative-clauses).
- [Negative Inversion — test](http://gramlyze.loc/test/word-order/inversion-after-negative-adverbials).

Category IDs: 33 / 56 / 48. Історично відсутній `category.type` Gerund source лишається відсутнім; фактична existing category має `type=theory`. Isolated resolver test підтверджує збереження цього type, а також existing parent `basic-grammar` у word-order. Parent не вгадується з filesystem namespace і не перетворюється на root.

Frozen before manifest SHA-256: `3c978f6b91dfea0ce988d9ed3f7fb34ef1189ba4e485ca1d86b2f7449ba7fc52`.
Finite M37 projection SHA-256: `0006e0ecbb6d29aee047527bb1739884ad29930ec74eb4e499e5010e3764dbc5`.
PHP незалежно відтворює всі три accepted Git blob hashes із точних original JSON bytes, а не лише звіряє labels у manifest.

## Native structure і source fidelity

Кожна сторінка має незмінний hero та 8 native sections: **5 usage-panels, 1 comparison-table, 1 practice-set, 1 summary-list**. Разом 9 definition blocks і 10 DB rows із subtitle. Колишній box стає першою native section зі збереженням його original ID/UUID/order/protected fields; сім наступних sections мають детерміновані `uuid_key`. Hidden old one-big box або прихована дубльована копія повного уроку не додаються.

Таблиці збережені повністю: 4 колонки × 4 body rows, original cells/порядок/пунктуація, min-width 1000px і 250px на колонку. Горизонтальний scroll належить їхній локальній wrapper, не всьому document.

Повний basic — це весь accepted навчальний урок до будь-якого кліку, не short summary. Збережені wording, examples/translations, participants, numbers, time/aspect/voice/modality, commas/referents, порядок sections та author links. Тільки static self-check presentation стала інтерактивною; всі original prompts і keys залишаються окремо доступними як author source, з no-JS educational fallback.

Особливо перевірені межі:

- Gerund: decision не стає completed action; -ing не є автоматично past, infinitive не є автоматично future; prepositional to відрізняється від infinitive marker; remember має три контексти; stop/try зберігають meaning/viewpoint без домисленого success/failure. Accepted checklist містить **п’ять** li: stop і try об’єднані в одному пункті. Його не переписано в шість нових li.
- Relative: whose для persons/organisations/things; cooperative **rents**, не owns; семантичні with/on/to; роль займенника та конкретні межі omission; nested subject who; defining/non-defining групи; object/whole-event which; owner/electrician не обмінюються професіями. Числа і comma meanings не змінені.
- Inversion: перший auxiliary/modal рухається без втрати решти verb phrase; close succession не стає cause; accepted `No sooner was the display ready…` не замінено довільно на had been; only-after subordinate order збережено; fact/prohibition/frequency не змішані; only/not-only scope і passive participants не переписані. M28 Basics не дублюється.

Finite presentation opt-in потребує exact owner/UK locale/UUID/order/type/body. Wrong/empty locale, foreign owner, wrong UUID/order/type або edited body не обирають M37 projection і лишають complete content у fallback. Tampered immutable file bytes відхиляються hash guard. Це не глобальний word-count runtime filter.

Source → finite projection → canonical definitions перевірені автоматично. Після real apply actual DB native bodies точно збігаються з canonical sources. Initial live HTML і reload DOM перевірені на реальних сторінках; full author fidelity не підмінена fixture render.

## Detail-quality audit: усі 18 candidates

Усі candidates мають `point=source-final-paragraph`, `decision=visible_basic`. Повні exact candidate HTML і явні причини versioned у finite projection. У таблиці наведені точні діагностичні `basic_word_count / detail_word_count / detail_sentence_count`; це інвентар, не runtime selection rule.

| Page / section | Basic / candidate words / sentences | Candidate та явна причина залишити basic |
| --- | ---: | --- |
| Gerund 1 | 152 / 12 / 1 | Scope note про власні навчальні приклади; не окреме поглиблення. Керування, часовий контекст і decision/completion — базове розмежування. |
| Gerund 2 | 77 / 37 / 2 | Enjoy drawing проти student drawing: різні функції однакового -ing, основний матеріал поруч із двома to. |
| Gerund 3 | 68 / 22 / 2 | Remembered to do у цьому affirmative context — виконане доручення, попри infinitive; core meaning. |
| Gerund 4 | 128 / 30 / 2 | Viewpoint try doing/to do та невстановлений result; видима межа повної comparison table. |
| Gerund 5 | 60 / 30 / 3 | Putting — запропонований спосіб, to adjust — goal attempt; без цього аналізу діалог легко перетворити на вигаданий успіх. |
| Gerund 6 | 69 / 36 / 2 | Три конкретні correction models і межа їх узагальнення; завершення exact five-item checklist. |
| Relative 1 | 139 / 8 / 1 | Короткий scope caveat, а не depth. Ролі whose, належність і rents/owns уже є core. |
| Relative 2 | 92 / 32 / 3 | Rely on та whom/which після fronted preposition, не that/порожнє місце; основа моделі. |
| Relative 3 | 190 / 46 / 4 | Who — subject can restore, we — subject believe; nested-role analysis потрібний біля full omission table. |
| Relative 4 | 74 / 46 / 4 | Група без/із комами, українська пунктуація і невиведена кількість людей; central comma meaning. |
| Relative 5 | 110 / 18 / 2 | Ясна редакція referent не додає cleaning або dusty cabinet; коротка фактична межа. |
| Relative 6 | 88 / 40 / 5 | Власник ремонтує годинники, електрик перевіряє wiring; with whom/date which і збереження абзацу — direct role analysis. |
| Inversion 1 | 118 / 18 / 2 | Вигадані сцени та emphasis не як автоматично кращий стиль; коротка scope/register note. |
| Inversion 2 | 207 / 36 / 3 | Valid was example не скасовує заданий perfect; distant/unknown interval не стає immediate; core temporal boundary. |
| Inversion 3 | 170 / 17 / 2 | Then має antecedent — inspection complete; continuation не ховається за click. |
| Inversion 4 | 83 / 21 / 2 | Rarely — frequency, не never/prohibition; preserved tense/aspect/modal/voice/participants — central distinction. |
| Inversion 5 | 144 / 21 / 2 | Never не fronted, тому інверсія тут не required; коротке пояснення конкретного порядку. |
| Inversion 6 | 105 / 44 / 4 | Нейтральна та emphatic редакції з тими самими facts; не вигадувати actor/opening result. |

M37 disclosures: **0 / 0 / 0**, семантично обґрунтований результат. Retained meaningful details відсутні; нічого не вигадано заради однакового UI. Short candidates `<30 words`: **2 / 2 / 4 = 8**, усі visible basic. Довші core role/meaning explanations також залишені basic: довжина сама по собі не виправдовує click.

## Усі 18 original practice cases

На кожній сторінці — exact 6 author cases, по 2 selects / choices / token-manual inputs, плюс власний linked widget. `source_index` має одного owner; багатокомпонентні case subparts не викинуті для підгонки interaction type. Original prompts/translations/constraints/keys побайтно звірені через незалежний DOM extraction. Plain feedback зберігає межі author paragraphs, а не склеює останнє слово з наступним.

### [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns#self-check-advanced-gerund-infinitive-patterns)

| Source index / original heading | Interaction | Ключ і збережені subparts |
| --- | --- | --- |
| 1. Форма після заданого слова. | selects | `We avoid storing paint near the heater. We decided to move the tins.`; avoid + -ing, decide + to; відповідь також зберігає питання про невстановлений факт переставлення банок. |
| 2. Два різновиди to. | selects | `I look forward to meeting the curator. I plan to meet her on Friday.`; обидва пропуски, function preposition/infinitive marker, curator/her/Friday і переклади. |
| 3. Три remember. | choices | `I remember packing the vase yesterday.`; `Remember to label the box before sending it.`; `I remembered to return the trolley yesterday.`; memory / reminder / completed obligation, всі три translations. Returning було б іншим ракурсом, не заданою відповіддю на третій subpart. |
| 4. Що припинили, заради чого зупинилися? | choices | `The students stopped whispering.` / `The students stopped to read the sign.`; припинили whispering, у другій ситуації призупинили задану ходьбу, reading — purpose. |
| 5. Try: мета чи спосіб? | inputs | `Mila tried to lift the heavy lid. Mila tried opening the side vent to cool the room.`; goal/method, не failed lift або successful cooling. Два finite accepted word orders наведені нижче. |
| 6. Редагування без домислів. | inputs | `Yesterday we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.`; усі три речення, вилучений unsupported success, не додано failure/completed repair. Вісім finite natural formulations. |

Gerund case 5 accepts:

1. `Mila tried to lift the heavy lid. Mila tried opening the side vent to cool the room.`
2. `Mila tried to lift the heavy lid. To cool the room, Mila tried opening the side vent.`

Gerund case 6 accepts:

1. `Yesterday we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.`
2. `Yesterday, we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.`
3. `We decided to fix the shelf yesterday. We tried to loosen the screw. We look forward to seeing our helper.`
4. `Yesterday we decided to fix the shelf and tried to loosen the screw. We look forward to seeing our helper.`
5. `Yesterday, we decided to fix the shelf and tried to loosen the screw. We look forward to seeing our helper.`
6. `We decided to fix the shelf yesterday and tried to loosen the screw. We look forward to seeing our helper.`
7. `Yesterday we decided to fix the shelf. We tried to loosen the screw, and we look forward to seeing our helper.`
8. `We decided to fix the shelf yesterday. We tried to loosen the screw, and we look forward to seeing our helper.`

### [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses#self-check-complex-relative-clauses)

| Source index / original heading | Interaction | Ключ і збережені subparts |
| --- | --- | --- |
| 1. Належність без зміни власника. | selects | `We rented a studio whose windows face the river.`; studio — anchor, windows — related noun і subject face; not who's. |
| 2. Прийменник перед займенником. | selects | `The conservator to whom we spoke recommended a softer brush.` / `The frame on which the portrait rests is wooden.`; speak to / rest on, два учасники й translations. Original final-preposition versions граматичні, але інша задана модель. |
| 3. Коми за контекстом, а не на смак. | choices | `The photographs which have white frames were moved.`; дві з шести, no commas. Comma version приписує ознаку всій відомій групі й не відповідає заданим фактам. |
| 4. Роль і опущення. | choices | Subject who не опускаємо в simple а); defining object which у б) можна опустити; non-defining which у в) лишається. `The restorer who we believe can repair the frame is away.`: who — subject can repair, не whom через сусіднє believe. |
| 5. Ясне which. | inputs | `They placed the folder beside the lamp. The folder was damaged.`; пошкоджена folder, не lamp; два clear sentences без нового cause/event. Три finite alternatives нижче. |
| 6. Редакторська точність. | inputs | `The workshop whose three presses need servicing has hired Iva, who maintains them.`; одна з двох workshops, три presses, need servicing не completed repair; Iva вже identified, comma перед who; duplicate she вилучено, object them збережено. |

Relative case 5 accepts:

1. `They placed the folder beside the lamp. The folder was damaged.`
2. `The folder was damaged. They placed it beside the lamp.`
3. `The folder was damaged. They placed the folder beside the lamp.`

### [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials#self-check-inversion-after-negative-adverbials)

| Source index / original heading | Interaction | Ключ і збережені subparts |
| --- | --- | --- |
| 1. Збережи складну дієслівну форму. | selects | `Rarely have the samples been stored outside the cold room.`; Present Perfect Passive, frequency і place, без did/simple past/new agent. |
| 2. Близька послідовність. | choices | `Hardly had the dispatcher finished checking the list when the alarm sounded.`; immediate sequence, не cause; three-day gap не виконує ту саму relation. No sooner…than не виконує прямо задані Hardly/when. |
| 3. Інверсія не в тій частині. | inputs | `Only after the editor had confirmed consent was the material released.`; subordinate Past Perfect normal order, main Past Simple Passive inversion; не приписує editor роль publisher. |
| 4. Only: підмет або час. | selects | `Only the archivist could open the vault.` / `Only after the check was complete could the vault be opened.`; only-subject без інверсії, only-after main inversion; active/passive і possibility не conflated. |
| 5. Факт і заборона — різні твердження. | choices | `Under no circumstances may visitors remove the seals.` / `At no time during the trial was the access code shared.`; may prohibition без другого not, past passive fact без modal/agent. |
| 6. Редагування фрагмента. | inputs | `No sooner had the display been installed than the visitors arrived. Not only the architect but also the caretaker welcomed them. The visitors rarely speak loudly in this hall.`; than, previous passive, two actors, subject-level not only, no unsupported emphatic did/do; architect не названа installer. |

Manual cases з цільовою correction/model мають exact canonical form; відкриті cases з author-permitted порядком мають finite natural alternatives. Це не універсальний semantic answer scorer і не обіцянка прийняти будь-який новий paraphrase.

Автоматично перевірені mapping/keys/alternatives/token groups по 1–3 слова, internal punctuation і `autocomplete=off`. Реальне browser acceptance підтвердило wrong/correct score/reset, всі explicit alternatives, manual/token click, Backspace re-availability та optional terminal punctuation у всіх 12 M37 states.

## Semantic negative fixtures

PHP має **41 мутацію реальних author fragments**: Gerund 12, Relative 15, Inversion 14. Відхиляються decision→completion, automatic past/future, неправильне to/remember/stop/try, invented result, whose humans-only/who's/ownership, неправильні with/on/that, omission/subgroup commas/nested subject/referent/duplicate actor/completed repair; auxiliary/aspect/voice loss, rarely→never, causality/immediacy, than/when, subordinate inversion, prohibition→fact, extra not, only/not-only scope, unsupported emphatic do, invented actor і lost translation.

Окремі structural negatives: detail на сусідньому point, duplicate section anchors, missing original case, comma mutation і author-order swap.

Node/live input fixtures мають **35 finite invalid answers**: 12 / 10 / 13. Усі реально змінюють existing canonical input answer; жоден не дорівнює explicit accepted alternative. Вони перевіряють змістові межі саме заданих вправ, не універсальну правильність інших англійських конструкцій. Node run PASS; live **140/140 rejection checks** PASS: 35 fixtures × 4 viewport/theme states.

## Actual linked-bank inventory

SELECT-only inventory виконаний перед projection/apply. Primary class не вгадується зі slug. Повний префікс bank classes: `Database\Seeders\V3\`.

| Page | Exact bank class suffix | Type / levels / actual count | Actual question IDs |
| --- | --- | --- | --- |
| 286 | `Polyglot\PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder` | 4 / B2 / 48 own | 17217–17264 |
| 286 | `VerbPatterns\AdvancedGerundInfinitivePatternsAllLevelsV3Seeder` | 0 / A1–C2 / 72 | 40311–40382 |
| 286 | `Polyglot\PolyglotAdvancedGerundInfinitivePatternsAllLevelsLessonSeeder` | 4 / A1–C2 / 72 | 40383–40454 |
| 300 | `Polyglot\PolyglotComplexRelativeClausesC1LessonSeeder` | 4 / C1 / 48 own | 17985–18032 |
| 300 | `RelativeClauses\ComplexRelativeClausesAllLevelsV3Seeder` | 0 / A1–C2 / 72 | 44343–44414 |
| 300 | `Polyglot\PolyglotComplexRelativeClausesAllLevelsLessonSeeder` | 4 / A1–C2 / 72 | 44415–44486 |
| 295 | `Polyglot\PolyglotInversionAfterAdverbialsC1LessonSeeder` | 4 / C1 / 48 own | 17649–17696 |
| 295 | `Polyglot\PolyglotInversionAfterNegativeAdverbialsAllLevelsLessonSeeder` | 4 / A1–C2 / 72 | 50391–50462 |
| 295 | `WordOrder\InversionAfterNegativeAdverbialsAllLevelsV3Seeder` | 0 / A1–C2 / 72 | 50823–50894 |
| 295 | `Polyglot\PolyglotThereIsThereAreAllLevelsLessonSeeder` | 4 / C2 / 3 foreign | 6485, 6487, 6491 |
| 295 | `Polyglot\PolyglotVerbToBeFutureAllLevelsLessonSeeder` | 4 / C1=1, C2=5 / 6 foreign | 6550, 6556, 6559, 6560, 6564, 6566 |
| 295 | `Polyglot\PolyglotVerbToBePastAllLevelsLessonSeeder` | 4 / C2 / 6 foreign | 6698, 6704, 6705, 6706, 6708, 6709 |

Кожний власний AllLevels group має 12 questions на кожному A1/A2/B1/B2/C1/C2. Observed all-linked totals: **192 / 192 / 207**. Own primary counts: **48 / 48 / 48**, не весь linked pool. Historical foreign 15 Inversion IDs явно виключені з M37 own widget; самі їхні questions/pivots не змінюються як unrelated scope. Widget обмежений exact proven primary class + type + level. У кожному browser state всі 5 sample IDs належать exact actual own 48-ID bank, type 4 і B2/C1/C1 відповідно.

## Guarded local apply та незалежний exact review

Physical proof підтверджує Windows ROOT/application/public identity, XAMPP Apache vhost, raw loopback HTTP exact host, local MySQL `localhost:3306`, server `DESKTOP-3C05HGF`, database `gr2`; safe CLI/web identities збігаються. APP environment `production`, SiteMode для `.loc` — `development`; environment label не перетворено на дозвіл звернутися до production.

Fresh proof → inventory → finalized source → fresh preview → exact review → exclusive source/record backup → transactional apply → SELECT postconditions виконані на реальній робочій цілі. New source hashes після concurrent regeneration зробили ранній plan stale: guard його відхилив, а не застосував неперевірені bytes. Fresh `m37-preview-v2.json` відповідає точним sources на момент apply/no-op.

Після завершення повного PHP-прогону прибрано лише зайві EOF newlines у 13 нових PHP-файлах та чотирьох exact owned ROOT counterparts. Preflight порівняв повні попередні ROOT/WT bytes; prefix до EOF не змінювався. Author snapshots, projection, definitions, shared views та DB не змінені. `sync-m37-working-sources.php --check` після форматування підтвердив 16 finite/shared records. Raw code hashes історичного preview залишаються доказом bytes на момент apply; їх не переписано заднім числом. Post-format targeted run PASS, без повторного DB apply або нового nonce route.

Reviewed preview file SHA-256: `b502c936be0fd9a1013554a69f671739c419eb220ed06778213f34e1728ee22e`.
Plan digest: `fc17aa9b643bc276ad205f275d2b3863db267b9b43a338bf952a6fd1700ae303`.
Main review helper PASS і окремий read-only field loop підтвердили 3 exact updates/21 exact inserts, 22 source hashes, root/root/nested ancestry, 20 protected table fingerprints, 35 prior package owners та source backup completeness. Повторний незалежний helper не перезаписав вже наявний exclusive review artifact; його final open правильно відхилив File exists. Це не DB failure і не другий успішний exclusive artifact.

| Факт | Реальний результат |
| --- | --- |
| Exact owners | UK pages 286 / 300 / 295 |
| Updated original IDs | 8747 / 8789 / 8774; тільки type + body |
| Original UUIDs | `aa914463-7516-5090-a42c-079f10a1667e` / `6f901cf0-93b4-5c02-9398-0ca329b0da4a` / `9f93b74a-98d9-5f7d-87a9-8831c852b6c3` |
| Inserts / deletes | 21 / 0; по 7 native sections, orders 3–9 |
| Total text_blocks | 6489 → 6510 |
| Non-target text_blocks | 6486; SHA-256 `d0799b5cde57fb5c84faf879a7d05c65d327142458de047dcaf5e3efe3b0b3fc`, unchanged |
| Other protected tables | 19 fingerprints unchanged |
| M26–M36 DB owners | 35 snapshots unchanged |
| New native bodies | exact canonical source match |
| Pages/categories/subtitle/hero/tags/relations/other locales | unchanged |
| Questions/options/answers/hints/pivots/saved tests/progress | unchanged |
| Repeated original-plan apply | status no-op, updated=0, inserted=0 |
| Repeated preview | state after, updates=0, inserts=0; digest `37c5ec7ac9f8995818d32a615fb699acddd7b6abb0bf80c8981b5689c1600773` |
| Repeated SELECT snapshot | Same exact three × 10 target rows, 35 protected owners, 6486 non-target rows |
| No-op unused backup | Не створено зайвого backup-файлу |

Exclusive record backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m37-local/m37-backup-v1.json`.
SHA-256: `b502c936be0fd9a1013554a69f671739c419eb220ed06778213f34e1728ee22e`.
Exclusive source backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m37-local/source-backup-e16e73c2527bb69a`.
Backup/proof/runtime/screenshots не включаються в Git.

After-noop snapshot `m37-after-noop-v1.json` SHA-256: `9e065ce46c8034bb35aa5f2498e995f551b1688a515d56c1df44aba3e7f1d2d3`. SELECT postconditions після repeated no-op тотожні після-apply snapshot, крім часу фіксації.

Fresh final SELECT після EOF-only cleanup: `m37-after-v2.json`, SHA-256 `0d5292d2fde9ef72c160211998019753642792010a61881292f4f8625e3e94da`; exact postconditions PASS з тими самими 3×10 target rows, 35 protected owners, 19 unrelated tables і 6486 non-target blocks.

Temporary nonce route контракт: GET only / exact `http://gramlyze.loc` / raw loopback / exact document identity; forwarded/auth/cookie requests reject; SELECT only; тільки safe allowlisted fields без DB password/APP_KEY/tokens/session. Route видалений після apply/no-op. Реальний fresh guest GET nonce URL повернув **404**, remote IP 127.0.0.1. Sandbox curl спочатку мав DNS error; actual GET з дозволом на локальну мережеву перевірку завершився 404, а не був підмінений fixture. `routes/api.php` відновлено raw byte-identical: SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Тимчасовий route ніколи не входить у commit.

Existing M26/PPC baseline чесно збережений: чотири UK practice rows 12307–12310 раніше відрізнялися від frozen M26 practice payload. Inspector перевіряє exact accepted ROOT/PPC body hashes/current canonical match лише для цих чотирьох rows; решта M26 frozen-exact. Їх не reset/overwrite і не видано за незмінний original frozen practice payload. M37 DB diff не містить PPC rows.

## Автоматичні перевірки

PHP 8.5.10 / PHPUnit 12.5.35; isolated SQLite `:memory:`, array cache/session, separate runtime/views, CLI OPcache off; working `.env` не завантажено.

| Команда / scope | Фактичний результат |
| --- | --- |
| M37AuthorFidelityTest + M37GrammarStructuresPackageTest, targeted v2 | **53 tests / 1415 assertions PASS**, exit 0, 29.803s, 62MB; protected 46648 files, changes=0 |
| Early targeted v1 | 52 tests / 926 assertions, 2 diagnostic schema errors; fixed field names before final projection. Не додається до успішного total. |
| M37ContentPatchTest + M37LocalTargetGuardTest, targeted final | **51 tests / 114 assertions PASS**, exit 0, 4:19.649; protected 46648 files, changes=0 |
| Final isolated PHP M26–M37, 48 Feature classes | **724 tests / 12415 assertions PASS**, exit 0, 27:17.965, 116MB; 0 failures/errors; protected 46648 files, changes=0 |
| Post-EOF isolated M37, four Feature classes | **104 tests / 1529 assertions PASS**, exit 0, 03:34.324, 70MB; protected 46648 files, changes=0 |
| Node related regression/practice/tooling run | **681/681 PASS**, 26 files, exit 0, 7130.1831ms; один actual run, не додано PHP/Vitest у fake total |
| Post-EOF Node M37 local/practice/tooling | **88/88 PASS**, 3 files, exit 0, 2653.979ms; окремий повторний subset, не додається до 681 |
| Initial Vitest v1 | Startup EPERM writing temporary bundled config file у sandbox; tests не запускалися, не рахується як completed suite |
| Fresh Vitest v2 relevant 8 files | **8/8 files, 61/61 tests PASS**, exit 0, 70.03s, після дозволу на isolated WT runtime |
| M37 author/package PHP lint + semantic fixture JS syntax | PASS для власних audit files |
| Final complete M37 JSON/PHP/JS/diff checks | **18 PHP / 6 JS / 5 JSON PASS**; staged `git diff --check` PASS після EOF-only cleanup |

Одна existing deprecation: `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` під PHP 8.5. Не приховано й не виправлено unrelated configuration. Initial diagnostic field mismatch не означав author-text failure: full author text/table/semantic/metadata/practice tests пройшли; фінальний v2 використовує exact prompt fields `basic_word_count/detail_word_count/detail_sentence_count`.

## HTTP, metadata, sitemap та live browser

Завершений before capture: **42 fixed guest GET URL / 42 responses 200**, без authorization/cookie/Referer, manual redirects. Theory targets, three tests, M26–M36 controls та PPC control перевірені реально, не з локальної fixture. Ordered sitemap before: observed **554 URLs**, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`; count не hardcoded acceptance condition.

Завершений after capture: **42/42 responses 200**. Title, controller-derived H1, description, canonical, meta robots, X-Robots-Tag, OG/Twitter і Content-Type exact before==after. Ordered sitemap before==after: observed 554 URL з незмінним порядком і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Це local live evidence; production HTML ним не перевірено.

Після фінального EOF-only cleanup три окремі fresh guest GET theory URLs знову дали **200**, а nonce URL — **404**, усі remote IP 127.0.0.1; raw routes SHA залишився початковим.

Live browser run `acceptance-v1` на real applied pages: **PASS, exit 0**:

- 3 M37 pages × desktop 1440×1000 / mobile 390×844 × light/dark = **12/12 states PASS**.
- Full basic до click; усі central examples/translations/table cells, no hidden one-big duplicate, 0 meaningless details.
- Wrong/correct/score/reset, всі original subparts, accepted variants, Enter/manual/token click/Backspace reuse, internal punctuation/autocomplete off та 35 input-negative fixtures.
- Legacy anchors, TOC/navigation, reload/print, no detail fetch PASS; **33/33 no-JS educational/readable states M27–M37**; **4/4 normal-JS regressions**: 3 M36 practice/render та M28 Inversion Basics 0-detail protection.
- Focused screenshots: по **6 / 6 / 7 sections на state** для Gerund / Relative / Inversion. Збережено **100 PNG**: 12 top, 76 focused sections, 12 table-right. Main візуально перевірив Gerund desktop/light practice, Relative mobile/dark role-table right і Inversion mobile/light only-after; browser reviewer також перевірив stop/try table left/right та practice.
- Across all 49 rows (12 + 33 + 4): page errors, console errors, HTTP errors, failed requests, font failures та network-policy violations — **0**.

Learning main і кожна content card — **0px overflow** у всіх 12 states. Таблиці мають local `overflow:auto`: desktop wrapper 840px / scroll 1000px; mobile 322px / scroll 1000px. Desktop document overflow — 0px. Mobile document overflow (light/dark): Gerund **6/0px**, Relative **4/0px**, Inversion **8/10px**; виміряні decorative shapes, не learning content. Фон не змінено, global `overflow-x:hidden` не додано. Document overflow чесно відокремлений від learning-only overflow.

Native no-JS details для M27–M37: **20** = M27 13 + M31 2 + M32 1 + M33 2 + M34 2; решта 0. Mouse/Enter/Space/focus, independence і no detail fetch PASS. M37 не має details за semantic audit; його повний basic і original keys видимі без JS.

Приватні докази: `storage/app/seo-m37-local/acceptance-v1-browser.json`, `acceptance-v1-http.json` і 100 PNG. Вони не потрапляють у Git. No-JS readability не оголошується інтерактивним scoring.

Known historical no-JS limitation окремо: **11 M26–M29 pages / 22 hidden token banks** з prior accepted baseline. Це NONPASS повної старої no-JS token usability, outside M37, не нова проблема й не замінене твердженням «усе no-JS працює». Full basic/keys readability не означає no-JS scoring. Новий M37 no-JS regression, якщо знайдений, входить у scope і має бути усунений.

Початкові локальні proof/HTTP запити мали transient timeouts; fresh proof і fresh finalized preview згодом успішні без зміни Apache/XAMPP/permissions/hosts/cache/session. Exact errors збережені приватно. Невдалий запит не підмінено search cache або DOM fixture і не названо production/Googlebot блокуванням.

## Regression counts

Захищені actual DB snapshots M26–M36 після apply та no-op незмінні; live after HTTP/render counts збігаються з accepted baseline.

| Accepted package | Protected expected point disclosures |
| --- | ---: |
| M26 | 56 = 8 + 12 + 12 + 12 + 12 |
| M27 | 13 = 3 + 7 + 3 |
| M28 | 0; Inversion Basics явно protected |
| M29 | 0 |
| M30 | 0 |
| M31 | 2 |
| M32 | 1 |
| M33 | 2 |
| M34 | 2 |
| M35 | 0 |
| M36 | 0 / 0 / 0 |
| M37 | 0 / 0 / 0, finite semantic result |

## Final handoff і staged audit

36 M37 файлів: `.gitattributes`, 4 app classes, 2 frozen manifests, 3 canonical definitions, 5 additive shared views, 4 PHP Feature tests, 3 Node Browser tests, 13 diagnostics і цей report. Staged diff перевірений; forbidden/private artifact paths, binary files, high-confidence secrets — 0. 105 protected M26–M36 versioned sources незмінні щодо accepted base. `.env`, secrets, private proof/backup/runtime/screenshots/dumps, vendor/build і temporary route виключені. `git diff --cached --check` PASS.

Normal push виконується тільки в `codex/seo-m37-m21-grammar-structures-layers`. Final commit SHA неможливо записати всередині того самого commit без самопосилання; повний SHA/commit/report URLs і remote SHA==HEAD наводяться у фінальному handoff після push. Main/PR/force/deploy не виконуються. M22 автоматично не починається.

Production не перевірявся й не змінювався.
