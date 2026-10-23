# M79 — шість сторінок типів питань, стандартна в’юха

Дата: 2026-10-23. Прийнята база: f33d1a0e2eff08e66044d9b746a46bc33f207bce. Робоча гілка: codex/seo-m79-question-types-completion-standard-view.

Статус цього документа: фінальні source/SQL/HTTP/registry та реальні браузерні перевірки редакції 1.0.1 пройдено в зазначених нижче межах. Після браузера повторено SELECT-only fingerprint усіх 46 tables. Межі Git-доставки описано в delivery checkpoint; фактичний commit SHA й незалежний remote verification наводяться у фінальному повідомленні, без self-referential SHA всередині власного коміту.

## 1. Межі й результат

Змінено лише шість погоджених UK owners; сьомий урок не додавався. Застосування та HTTP/browser acceptance — тільки http://gramlyze.loc. Main, production, PR, merge, force push, workflow dispatch і deploy не виконувалися.

Результат, підтверджений фінальним HTTP/SQL gate:

- 57 власних graded завдань = 31 збережена/уточнена стара функція + 26 нових; окремо 3 збережені ungraded demos.
- 20 inputs = 16 повнореченнєвих manual/token + 4 збережені partial tag gaps. Partial gaps не зараховано до full-sentence count.
- На кожному owner щонайменше 2 form / 2 meaning / 2 full-sentence завдання.
- 6 окремих тематичних widgets, по 5 точно визначених type4 питань: 30 = 22 reused + 8 мінімальних нових.
- 4 змістовні point-level details; потрібні правила, переклади й умови залишаються у visible basic.
- 0 нових visual-source змін; formatter, grader, global normalization і practice engine не змінено.
- 70 distinct старих рядків змінено 72 update operations у двох шарах; 3 нові practice blocks і 131 insert-only question-family рядок. Видалень і metadata transitions — 0.
- Реєстр: 169 targets / 0 failed / 0 writes; попередні 163 записи збережено.
- 51 старий HTTP consumer, 13 saved tests і 888 старих питань з їхніми залежними рядками збережено точно.

Використано чинний ланцюг theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*. Незалежних M79 views/styles/JS немає.

## 2. Підтверджені owners, URL, рівні та practice identities

Спільний top-level prefix identities: Database\Seeders\Page_V3\QuestionsNegations\TypesOfQuestions\.
Спільна фактична ancestry: pytalni-rechennia-ta-zaperechennia → types-of-questions; category_id=22; locale=uk. Повні identities, physical bindings і незмінні вектори рівнів є у [native bindings](../content/m79-native-bindings.v1.json).

| Owner / confirmed H1 | Локальна сторінка | Identity suffix | Page ID | Display / точний practice focus |
|---|---|---|---:|---|
| A — Alternative Questions | [Відкрити](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/alternative-questions-coffee-or-tea) | TypesOfQuestionsAlternativeQuestionsTheorySeeder | 132 | A2–B1 / A2 |
| B — Question Tags | [Відкрити](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/question-tags-disjunctive-questions-dont-you-isnt-it) | TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder | 137 | B1–B2 / B1 |
| C — Negative Questions | [Відкрити](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/negative-questions-dont-you-know) | TypesOfQuestionsNegativeQuestionsTheorySeeder | 136 | B1–B2 / B1 |
| D — Subject Questions | [Відкрити](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/subject-questions) | TypesOfQuestionsSubjectQuestionsTheorySeeder | 138 | A2–B1 / A2 |
| E — Embedded Questions | [Відкрити](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/embedded-questions) | TypesOfQuestionsEmbeddedQuestionsTheorySeeder | 134 | B1 / B1 |
| F — Indirect Questions | [Відкрити](http://gramlyze.loc/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/indirect-questions) | TypesOfQuestionsIndirectQuestionsTheorySeeder | 135 | B1 / B1 |

| Owner | Practice ID | UUID | Source index / sort_order | Стан |
|---|---:|---|---|---|
| A | 4243 | 37f2ff28-6385-5261-bee9-c30d5ec77172 | 11 / 12 | existing, продовжено |
| B | 4415 | 1f25fd18-30c2-54c6-b5a2-54d05067620d | 12 / 13 | existing, продовжено |
| C | 4372 | 221438bd-95b6-57fd-87cf-b1bb6a06a987 | 11 / 12 | existing, продовжено |
| D | 12768 | e04c1aa9-440d-535c-8ef6-e5eb2e8670a2 | 6 / 7 | додано після незалежного inventory |
| E | 12769 | 101ed940-43b2-5519-a682-6670b655476c | 6 / 7 | додано після незалежного inventory |
| F | 12770 | 497819b4-d9b5-5440-acba-a176f1e8a819 | 6 / 7 | додано після незалежного inventory |

Actual SQL підтвердив відсутність пізніших сторонніх practices D/E/F перед вставками. Existing UUIDs, IDs, sort, columns, levels, tags і зв’язки не перевизначалися заради добору банку.

Технічні continuing-level vectors, source index від нуля:

- A: A2,A2,A2,A2,A2,B1,B1,A2,B1,B1,A2,A2,A2.
- B: B1,B1,B1,B1,B1,B1,B1,B2,B2,B1,B1,B1,B1,B1.
- C: B1,B1,B1,B1,B1,B2,B2,B2,B1,B1,B1,B1,B1.
- D: A2–B1,A2,A2–B1,A2,A2,A2; нова практика A2.
- E/F: B1,B1,B1,B1,B1,NULL; нова практика B1.

NULL E/F footer підтверджено SQL, а не виведено лише з відсутнього поля definition. Display range не підміняв exact CEFR filter. Історичні embedded Page_v2 seeder fields A/B/C збережено; top-level owner залишається Page_V3. Plain subtitle/fallback H1 extraction не замінено новою евристикою.

## 3. Канонічні артефакти й OLD→AFTER карта

Усі definitions лежать у database/seeders/Page_V3/QuestionsNegations/TypesOfQuestions/<Identity suffix>/definition.json.

Активні versioned джерела:

- [Master 1.0.1](../content/m79-question-types-completion.v1.0.1.json) і [читабельна UK редакція](../content/m79-question-types-completion.v1.0.1.uk.md).
- [Editorial map](../content/m79-question-types-completion.editorial.v1.0.1.json), [component map](../content/m79-question-types-completion.components.v1.0.1.json), [повний task map](../content/m79-question-types-completion.tasks.v1.0.1.json).
- [Content patch 1.0.1](../../database/content-patches/m79-question-types-completion.v1.0.1.json), [checksum manifest](../content/m79-source-checksums.v1.0.1.json).
- [Source BEFORE](../content/m79-before-source.v1.json), [native bindings](../content/m79-native-bindings.v1.json), [builder contract](../content/theory-sentence-builders/m79-question-types-completion.v1.json).
- [Вибір 30 питань](../content/m79-widget-selection.v1.json), [bank audit 1.0.1](../content/m79-sentence-builder-bank-audit.v1.0.1.json), [мінімальний supplement](../content/m79-question-bank-supplement.v1.json).
- [Exact label-only revision ledger](../content/m79-practice-criteria-revision.v1.0.1.json).

Попередні frozen v1 master/patch/maps/audit/checksums залишилися історичними входами без переписування. Нові 1.0.1 bytes мають окремі hashes; старий receipt не видається за доказ нових bytes.

Читабельний UK Markdown 1.0.1 повторно згенеровано підтриманою проєкцією: його редакційне форматування відрізняється від 1.0.0, але навчальні тексти не вилучено. Exact semantic change master/patch — лише шість видимих select-label prefixes. Пізнішу косметичну Markdown-пропозицію не застосовано й до доставки не включено; frozen bytes обох редакцій не підчищалися.

| Owner | OLD→AFTER components | OLD→AFTER практика |
|---|---|---|
| A | 13 блоків, source0…12 → ті самі 0…12; hero/forms/usage/table/mistakes/summary/practice/navigation збережено за типом та column | selects0…2, inputs0…3, graded rephrase1…3 і demo rephrase0 → ті самі позиції; додано choices0…1 |
| B | 14 блоків, source0…13 → ті самі 0…13 | selects0…3, partial inputs0…3, graded rephrase1…3 і demo0 збережено; додано choices0…1 та full inputs4…5 |
| C | 13 блоків, source0…12 → ті самі 0…12 | selects0…2, inputs0…3, graded rephrase1…3 і demo0 збережено; додано choices0…1 |
| D | 6 блоків source0…5 без перестановки; append practice source6 | раніше 0, додано 2 selects + 2 choices + 2 full inputs |
| E | 6 блоків source0…5 без перестановки; append practice source6 | раніше 0, додано 2 selects + 2 choices + 2 full inputs |
| F | 6 блоків source0…5 без перестановки; append practice source6 | раніше 0, додано 2 selects + 2 choices + 2 full inputs |

Разом: 58 continuing structured blocks → 61; ще 6 continuing UK subtitle rows. У D/E/F mistakes source3 і summary source4 залишилися праворуч. Усі старі anchors збережені.

31 стара graded функція не видалена й не перенесена між group/index; її canonical answer збережено. Уточнено критерії, переклади, допустимі aliases та plain feedback. Відсутність старих id/source_index збережено як відсутність; новим завданням надано m79-* IDs. Demos не отримали answer/accepted і не стали graded.

## 4. Редакційний результат за owner

### A. Alternative Questions

Збережено паралельні noun/adjective/verb alternatives, повні/скорочені моделі, both/neither і відповідь за змістом. Усунено категоричне «or завжди означає вибір, Yes/No неможливе»: письмовий контекст тепер явно розрізняє справжній вибір і перевірку наявності хоча б однієї можливості.

Is it blue or green? не вимагає зайвого main verb після be. Can you swim or dive? не отримує штучно єдиного наміру без умови. You prefer…? не оголошено неможливою англійською; select вимагає повної нейтральної інверсії. And може бути граматичним, але не виконувати вимогу вибору. У cash/card пояснено типові in cash/by card без нової абсолютної заборони by cash.

10 source-функцій і demo збережено; додано 2 meaning choices. Existing 4 full inputs отримали перемішані логічні 1–3-слівні групи та ручний ввід; дублікатів заради count не створено. Details=0: потрібні уточнення залишено basic. Власні declared aliases понад canonical не додавалися.

### B. Question Tags

Basic навчає confirmation-pattern із протилежною полярністю, auxiliary і потрібним pronoun. Same-polarity tags не оголошено загалом неправильними: у correction явно названо confirmation-задум. Формальне am I not приймається там, де умова не обмежує форму скороченим tag.

Уточнено I am/I am not, imperative, let’s проти інших let us; never/nobody/hardly, there, this/that та people-referents. Lexical have з do не названо виключно американським. Must/рідші modal tags пояснюються через конкретне значення, не механічне копіювання. Yes/No відповіді спираються на факт головного повідомлення та визначеного мовця.

11 старих graded + demo збережено. Чотири старі partial tag gaps залишилися частковими; додано 2 meaning та 2 повні речення, а не зараховано partial як full. Один detail — реакція на нову інформацію проти звичного confirmation.

Reviewed aliases:

- selects3: aren't I / am I not.
- partial inputs0: isn't she / is she not; inputs2: can't he / can he not.
- full inputs4: Marta works here, doesn't she? / Marta works here, does she not?
- full inputs5: There isn't a bus, is there? / There is not a bus, is there?
- rephrase1: You like coffee, don't you? / You like coffee, do you not?
- rephrase2: She can swim, can't she? / She can swim, can she not?
- rephrase3: I am right, aren't I? / I am right, am I not?

### C. Negative Questions

Збережено правильну відповідь за змістом; додано явний порядок contracted n’t при auxiliary і full not після subject. Скорочення не розгортаються механічною перестановкою символів. Очікування, здивування, запрошення, уточнення й можлива критика розділені без твердження «завжди емоційне/ввічливіше».

You don’t…? може бути перепитуванням; цільова повна нейтральна модель названа у видимій умові. Isn’t she coming? саме по собі не доводить відповіді: факт приходу/неприходу повідомлено окремо. Переклади запрошень природні, без обов’язкового «Хіба». Перетворення positive→negative не видається за збереження ідентичного тону.

10 старих graded + demo й чотири full inputs збережено; додано 2 meaning choices. Details=0: основні застереження й переклади visible basic.

Full-input aliases: Don't you know? / Do you not know?; Isn't she coming? / Is she not coming?; Can't they help? / Can they not help?; Didn't you see it? / Did you not see it? Rephrase aliases додатково включають Doesn't she like it? / Does she not like it? та Can't you swim? / Can you not swim? Усі зберігають особу, час і заперечення.

### D. Subject Questions

Збережено subject/object contrast з однаковими відомими учасниками: Who contacted Anna? проти Who did Anna contact? Граматичний subject не зведено до активного виконавця: додано стан/пасив.

«Без do/does/did» обмежено нейтральним positive Simple question do-support; лексичне do, потрібні is/has/can, negative та emphatic do не видаляються. Who did call?/What did happen? не заборонено поза контекстом. Показано цілі which student/which students/how many people групи й узгодження.

Нові 2/2/2 перевіряють neutral form, plural state, ролі/пасив, повну групу subject та Continuous auxiliary. Reviewed aliases понад canonical не потрібні. Один detail пояснює negative/emphatic do на відміну від нейтрального question do-support.

Чесна межа widget: його п’ять A2 source-питань охоплюють who/what, present/past, невідомого референта/причину й неактивну подію. Буквальні which/how many noun groups покрито уроком і власною практикою, а не приписано цьому конкретному старому банку.

### E. Embedded Questions

Фокус — структура вкладеної питальної частини, у тому числі всередині твердження; embedded не визначається лише ввічливістю. Інверсія Do you know…? у головній частині зберігається. Усередині прибирається лише neutral positive question do-support із відновленням часу/дієслова; lexical do, negative auxiliary і доречне підсилення не заборонені.

Wh-subject і wh-object не переставляються механічно; після subject who не додається зайвий he. If/whether приймаються для названої звичайної finite clause, але не оголошені універсальними синонімами перед infinitive, після preposition або безпосередньо перед or not. Embedding не означає автоматичного backshift; Reported Speech не переписано.

Додано 2/2/2. Aliases: I don't know what Lina does. / I do not know what Lina does.; Do you know if the door is locked? / Do you know whether the door is locked? Один detail поглиблює позиційні обмеження whether; основне правило залишено basic.

### F. Indirect Questions

Визнано термінологічний перетин із Embedded. Розмежування є редакційним фокусом цих двох уроків, не універсальною класифікацією: тут запит інформації, рамка, доречна відповідь і ціле речення.

Пряме питання не названо автоматично грубим; довша know/could-рамка не гарантує ввічливості. I was wondering може бути теперішнім м’яким запитом; справжнє reporting окремо зберігає час і учасників. У Could-перетворенні не підставляється Can без дозволу. Буквальне Yes може не дати потрібної інформації — meaning task називає цю функцію.

Три старі ua-поля з аналізом отримали справжні переклади, а аналіз збережено у note/поясненні. Додано 2/2/2. Для поточного запиту про кімнату reviewed four-way aliases: if/whether × available/free у I was wondering … the room is … tonight. Це конкретно перевірена множина, не декартова генерація всіх скорочень. Один detail відділяє current polite past від реального історичного повідомлення.

## 5. Власна практика: точні counts і нові IDs

| Owner | Retained/corrected graded | New | Selects | Meaning choices | Full inputs | Partial gaps | Graded rephrase | Demos | Graded total |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| A | 10 | 2 | 3 | 2 | 4 | 0 | 3 | 1 | 12 |
| B | 11 | 4 | 4 | 2 | 2 | 4 | 3 | 1 | 15 |
| C | 10 | 2 | 3 | 2 | 4 | 0 | 3 | 1 | 12 |
| D | 0 | 6 | 2 | 2 | 2 | 0 | 0 | 0 | 6 |
| E | 0 | 6 | 2 | 2 | 2 | 0 | 0 | 0 | 6 |
| F | 0 | 6 | 2 | 2 | 2 | 0 | 0 | 0 | 6 |
| Разом | 31 | 26 | 16 | 12 | 16 | 4 | 9 | 3 | 57 |

Нові IDs, group/index від нуля:

| Owner | Позиція | Stable IDs / перевірювана функція |
|---|---|---|
| A | choices0…1 | m79-alternative-meaning-drink; m79-alternative-meaning-existence — реальний вибір проти наявності |
| B | choices0…1 | m79-tags-meaning-confirmation; m79-tags-meaning-factual-answer |
| B | inputs4…5 | m79-tags-full-work; m79-tags-full-there |
| C | choices0…1 | m79-negative-meaning-coming; m79-negative-meaning-know — явні факти, не висновок із питання |
| D | selects0…1 | m79-subject-form-neutral-past; m79-subject-form-plural-state |
| D | choices0…1 | m79-subject-meaning-participants; m79-subject-meaning-passive |
| D | inputs0…1 | m79-subject-sentence-many-children; m79-subject-sentence-continuous |
| E | selects0…1 | m79-embedded-form-lexical-do; m79-embedded-form-negative-past |
| E | choices0…1 | m79-embedded-meaning-main-clause; m79-embedded-meaning-if-whether |
| E | inputs0…1 | m79-embedded-sentence-lexical-do; m79-embedded-sentence-if-whether |
| F | selects0…1 | m79-indirect-form-where-manager; m79-indirect-form-speaker-preservation |
| F | choices0…1 | m79-indirect-meaning-information-reply; m79-indirect-meaning-current-wondering |
| F | inputs0…1 | m79-indirect-sentence-current-work; m79-indirect-sentence-current-room |

Усі відповіді/варіанти, including ordinal option correspondence Tags, перевірені через фактичну підтриману модель, а не лише порівняння підпису з JSON answer. Перед Check видно необхідний комунікативний/граматичний критерій. Feedback для x-text — plain text із відповіддю, перекладом і поясненням.

Full inputs підтримують ручне редагування, перемішані логічні token groups по 1–3 слова, delete→return→rebuild; browser autocomplete вимкнено штатною моделлю. Зміни спільного Blade/JS для цього не вносилися.

### Meaningful details

| Owner | Key / точний point owner | Зміст |
|---|---|---|
| A | N/A | усе потрібне у basic |
| B | m79-tags-same-polarity → tags-8 /sections/2 | Реакція на нову інформацію проти confirmation |
| C | N/A | усе потрібне у basic |
| D | subject-do-context → subject-2 /sections/5 | Negative/emphatic do, не звичайний question do-support |
| E | embedded-whether-positions → embedded-2 /sections/2 | Позиційні обмеження whether |
| F | indirect-time-and-reporting → indirect-2 /sections/2 | Поточний м’який запит проти історичного reporting |

Це finite semantic author decisions, не глобальний word-count threshold. Короткі примітки, переклади й продовження речення не сховані механічно.

## 6. Тематичні банки й усі 30 canonical sentences

Спільний prefix банків: Database\Seeders\V3\Polyglot\.

| Owner | Точні банки | Eligible / selected / excluded | Reused / new |
|---|---|---|---|
| A | PolyglotAlternativeQuestionsAllLevelsLessonSeeder | 12 / 5 / 7 | 5 / 0 |
| B | PolyglotQuestionTagsDisjunctiveQuestionsAllLevelsLessonSeeder | 12 / 5 / 7 | 5 / 0 |
| C | PolyglotNegativeQuestionsAllLevelsLessonSeeder + M79NegativeFactsSupplementSeeder | 14 / 5 / 9 | 3 / 2 |
| D | PolyglotSubjectQuestionsAllLevelsLessonSeeder | 12 / 5 / 7 | 5 / 0 |
| E | PolyglotEmbeddedQuestionsAllLevelsLessonSeeder + M79EmbeddedStructureSupplementSeeder | 15 / 5 / 10 | 2 / 3 |
| F | PolyglotIndirectQuestionsAllLevelsLessonSeeder + M79IndirectInformationSupplementSeeder | 15 / 5 / 10 | 2 / 3 |

80 eligible кандидатів переглянуто, 30 прийнято, 50 явно виключено з цієї selection, а не оголошено всі виключені речення граматично неправильними. Старий pagewide type4 pool432 збережено; після insert-only supplement — 440.

Причини мінімального supplement:

- C: старі 12 B1 питань перевіряли форми negative questions, але не містили fact-based answer. Додано 2 прозорі відповіді з визначеним мовцем і відомим фактом.
- E/F: старі mid-clause-capitalized wh candidates не виправлялися через shared option або formatter. Додано по 3 окремі тематичні питання для потрібного різноманіття й точного visible output.
- TagsBasics не підставлявся замість потрібного AllLevels банку.

Нові question IDs59723…59730 належать трьом окремим supplement banks. Старі questions/options/answers/links/UUIDs/saved-test memberships не редагувалися; нові рядки не дописані до старого seeder identity. C1 «Yes, I do.» природно має три токени; finite M79 validation допускає цю реальну коротку відповідь, не додає штучне filler-речення і не змінює shared engine.

Нижче canonical output узято з frozen public selection. Це source/native replay + server payload proof; browser-completion status — тільки у розділі8.

| Owner | ID | Походження | Canonical settled sentence |
|---|---:|---|---|
| A | 34923 | reused | Would you like tea or coffee? |
| A | 34926 | reused | Do you want the blue pen or the black pen? |
| A | 34927 | reused | Are you travelling by bus or by train? |
| A | 34929 | reused | The blue pen, please. |
| A | 34934 | reused | Will you pay by card or in cash? |
| B | 35007 | reused | The train leaves soon, doesn't it? |
| B | 35008 | reused | The documents weren't ready, were they? |
| B | 35013 | reused | Send me the address, will you? |
| B | 35014 | reused | Let's review the plan, shall we? |
| B | 35015 | reused | I am responsible for this, aren't I? |
| C | 59723 | нове: m79-neg-b1-01 | Yes, I do. |
| C | 59724 | нове: m79-neg-b1-02 | No, she is not. |
| C | 35080 | reused | Doesn't the printer work? |
| C | 35081 | reused | Didn't Maria explain the rule? |
| C | 35089 | reused | Shouldn't the team review it first? |
| D | 34491 | reused | Who lives in this house? |
| D | 34492 | reused | What fell off the table? |
| D | 34496 | reused | Who called my mother? |
| D | 34500 | reused | What broke this morning? |
| D | 34502 | reused | What caused the noise? |
| E | 35444 | reused | Do you know if he is ready today? |
| E | 35445 | reused | I am not sure whether they can join us today. |
| E | 59725 | нове: m79-emb-b1-01 | I know where Nina works. |
| E | 59726 | нове: m79-emb-b1-02 | Do you know who sent the parcel? |
| E | 59727 | нове: m79-emb-b1-03 | I know what he doesn't do on Fridays. |
| F | 35516 | reused | Could you tell me if the bank is open today? |
| F | 35517 | reused | Do you know whether they have replied today? |
| F | 59728 | нове: m79-ind-b1-01 | Could you tell me when the museum closes? |
| F | 59729 | нове: m79-ind-b1-02 | The ticket office is beside the entrance. |
| F | 59730 | нове: m79-ind-b1-03 | I was wondering whether I can pay by card. |

Усі selected contexts/переклади/coverage, raw tokens і settled tokens збережено у public selection та bank audit. Це не перевірка інших hundreds питань у UI.

### Межі grading і native replay

Незмінені actual native methods програно окремо для ROOT і WT: по30 canonical sequences і6173 finite adversarial sequences на профіль. Empty, token omission, swaps, punctuation moves та доступні bank replacements перевірялися у визначеній скінченній множині; це не exhaustive permutation claim. Неправильних lexical visible sentences із правильним score —0.

На кожному профілі виявлено2 formatting-only normalization matches: A34926 і B35015 дозволяють переставлену punctuation без зміни lexical sequence. Це задокументована межа незміненого matcher, НЕ PASS суворої пунктуації. Apostrophe/negative polarity й lexical content розглянуто окремо; інтонація, вимова та «ступінь ввічливості» текстовим score не вимірюються.

Ручні aliases визначено точково, не автоматичним розгортанням contractions. Empty/whitespace, not/n’t, person/tense і repeated Check не маскуються новим normalizer.

## 7. Guarded local apply, два шари DB та preservation

Перед першим записом незалежно знято source/DB/HTTP BEFORE усіх6 owners, schema46 tables, metadata/anchors, bank/consumer inventory, actual practice state та physical web/CLI local proof. Preview переглянуто за exact allowlist; створено exclusive private backup; apply транзакційний і лише цільовий. Повне пересівання, DDL, migrations, truncate, restore, mass update, startup/apply hooks не використовувалися.

| Категорія | Перший apply v1 | Successor1.0.1 | Підсумок |
|---|---:|---:|---:|
| Page.text | 6 | 0 | 6 |
| UK subtitle bodies | 6 | 0 | 6 |
| Continuing teaching bodies | 55 | 0 | 55 |
| Continuing practice bodies | 3 | 2 повторні оновлення A/C | 3 distinct / 5 operations |
| Metadata transitions | 0 | 0 | 0 |
| Старі update operations | 70 | 2 | 72 |
| Distinct старі змінені rows | 70 | ті самі2 з70 | 70 |
| Нові practice blocks | 3 | 0 | 3 |
| Question-family insert rows | 131 | 0 | 131 |
| Deleted rows | 0 | 0 | 0 |

131 question-family inserts = questions8 + question_options27 + question_answers31 + question_option_question57 + question_theory_text_blocks8. Разом нових rows134, включно з3 practice blocks; не плутати з72 updates або70 distinct старих rows.

Після первинного freeze реальний UI показав, що native select відображає label, а окремий prompt не є видимим критерієм. 1.0.1 додає рівно шість існуючих prompt-prefixes до labels selects0…2 A і C; відповіді, options, accepted, feedback, type/order/metadata і решта learning data незмінні. Змінено2 bodies IDs4243/4372, без timestamp або non-body changes. Окремий successor ledger зберігає original v1 bytes та exact diff.

Розклад initial distinct updates за owner: A15, B16, C15, D8, E8, F8. Successor додає по1 operation до A/C, не нові distinct rows. Редакція1.0.1 не є підставою повторювати первинні вставки.

Повторні apply обох шарів — no-op:0 updated/inserted/deleted у дозволених сімействах. Initial no-op не створив повторного backup. Позаскоупні consumers/прогрес не змінювалися.

Фінальний chain:

1. Незалежний initial BEFORE8c261… → primary preview/backup → initial postconditionsc9c8….
2. Свіжий current-v1 baseline43b480… точно дорівнює initial all46 AFTER.
3. Criteria postconditionsb9f38… нормалізують лише2 exact successor bodies назад до baseline43b480….
4. Pure composite3a05… поєднує обидва незалежні докази; сам не bootstrap-ить Laravel і не звертається до SQL/HTTP.
5. Фінальні HTTP gates повторно fingerprint-ять усі46 tables до/після й звіряють із successor AFTER.

Збережено64 continuing UK rows поза погодженими body/text змінами,128 non-UK rows, старі timestamps/IDs/UUIDs/levels/columns/tags/links. Усі незмінні старі rows реконструйовано exact full-row normalization, не лише count.

Два тимчасові proof endpoints були GET/loopback/local-only із nonce, без секретів/DB writes; обидва видалені. routes file відновлено byte-exact відносно відповідного snapshot із збереженням сторонніх changes; окремі GET після cleanup дали404. Hosts/Apache/XAMPP/ACL/.env не змінено.

Незалежний final source comparison:3081 accepted paths ×2 profiles;3067 unchanged paths на профіль,14 змінених =8 мінімальних невізуальних integration files +6 target definitions. Точне повернення12 registration/exclusion hunks у8 файлах відновлює відповідний predecessor ROOT/WT. 896 старих test paths та1840 historical frozen paths поза6 targets збережено. Не послаблено assertions або snapshots.

326 visual files незмінні окремо для ROOT і WT. До M79 між профілями вже були167 raw/EOL differences та7 LF-content differences; їх збережено, а не насильно прирівняно чи включено до M79. Нуль нових visual-source changes означає відсутність M79 visual edits, а не вигадану тотожність початково різних робочих профілів.

M78 1.0.0 з67 graded/4demos,18 full/4partial,6 selections і identities збережено. M77 variable-owner/empty-source validation і M76 every/shift label fix та token values не змінено. Category descriptions, EN/PL, Negative Forms/Double Negatives, Reported Questions/Backshift/Say-Tell-Ask, Relative Pronouns, старі targets, окремі tests, курси й особистий progress не редагувалися.

## 8. Metadata, навігація та реальне браузерне приймання

### Фінальний metadata/navigation gate — PASS

До HTTP застосовано pre-apply prospective metadata18f0…; expected не отримано з самого AFTER. На всіх6 targets збережено1H1, raw/display title, breadcrumb, canonical, local noindex header, robots meta, LearningResource identity і всі original anchors. Змінені descriptions точно відповідають погодженій prospective projection;1.0.1 metadata не змінює.

| Owner | Фактичний meta description |
|---|---|
| A | Alternative Questions. Альтернативне питання пропонує два або більше варіантів через or. |
| B | Question tags додають короткий запит до головної частини. |
| C | Negative Questions. Заперечне питання містить not або n’t. |
| D | Subject Questions. Питальна група сама є підметом: невідомо, хто телефонував, що сталося або які учні готові. |
| E | Embedded Questions. Embedded question — питальна за змістом частина всередині більшого речення. |
| F | Indirect Questions. Терміни indirect і embedded questions часто перетинаються. |

Title pattern збережено: «<H1> — правила | Gramlyze». Canonical містить production URL як метадані; жодного production HTTP request не робилося. Local X-Robots-Tag: noindex, nofollow, noarchive.

46 navigation items =6 current nonanchors +40 реальних noncurrent anchors; усі labels/order/current flags збережено. A/B/C отримали явні підтверджені URLs. D/E/F25 source occurrences коротких href виправлено на full parent paths.

Фактичні GET:10 theory destinations (6 targets+4 neighbors) та2 categories =12GET200. Дев’ять distinct старих /theory/types-of-questions/<slug> paths перевірено окремо: native resolver відхиляє ancestry-less path, HTTP404, redirects0; nested replacement кожного — GET200. Це actual AFTER boundary proof, не вигаданий BEFORE HTTP capture старих short URLs. Global redirect/category rename не додано.

Категорійні descriptions відповідають одній із двох незалежно обчислених native ordering branches; кеші не очищалися/не редагувалися. Whole-page byte identity не заявляється.

### Реальний браузер — PASS у перевіреній матриці

Фінальний actual-browser progress збережено окремо від source/SQL/HTTP receipts. Всі наведені нижче завершення виконано у справжній локальній в’юсі, не в автономному preview.

Обов’язкова матриця:6×(1440×1000,390×844,320×844)×light/dark=36 target states. Actual viewport/theme мають підтверджуватися DOM; zoom не змінюється. Expected full-input coverage:16×manual/token×desktop/mobile=64 cases. Partial tag gaps рахуються окремо.

Фактичне покриття:

| Owner | Widget | Full-input cases | Details / додатковий стан |
|---|---|---|---|
| A | реальний5/5,5 settled answers прочитано | 16 | details N/A; усі6 states |
| B | реальний5/5,5 settled answers прочитано | 8 | keyboard/owner/full-text/deep-link/reload PASS; усі6 fresh states |
| C | реальний5/5,5 settled answers прочитано | 16 | details N/A; усі6 states |
| D | реальний5/5,5 settled answers прочитано | 8 | keyboard/owner/full-text/deep-link/reload PASS; усі6 states |
| E | реальний5/5,5 settled answers прочитано | 8 | keyboard/owner/full-text/deep-link/reload PASS; усі6 states |
| F | реальний5/5,5 settled answers прочитано | 8 | keyboard/owner/full-text/deep-link/reload PASS; усі6 states |

Завершено36 unique target states: кожний owner у1440×1000,390×844,320×844, light/dark. Viewport/theme підтверджено actual DOM. Повний reread від H1 до footer включив native компоненти, typography/assets, формули, переклади, практику й один widget.

Завершено64 unique full-input cases: усі16 inputs ×1440/320 px ×manual/tokens. Чотири partial tag gaps перевірено окремо й не додано до64. Всі72 authored option feedback пройдено у UI: A13/B16/C13/D10/E10/F10. Retained rephrases A/B/C пройдено з усіма declared accepted;3 demos прочитано як ungraded, без зарахування бала. Всі6 widgets реально завершено5/5; усі30 settled sentences прочитано й збережено у приватному progress receipt.

Додатково пройдено17 explicit semantic wrong→edit-canonical UI probes: A2/C2/D3/E4/F5 та1same-polarity probe B. Перевірено заданий вибір/yes-no, form/polarity та явно названу комунікативну вимогу, не уявну інтонацію. У Tags reset/empty/whitespace дають0/6; repeated Check зберігає6/6 без повторного бала; wrong5/6→edit6/6 працює; scores різних груп незалежні. Manual/token mixed editing, delete→return→rebuild, Next/completion і multiplicity перевірено без очищення особистого прогресу.

Усі4 details: initial closed, keyboard toggle, точний owner, повний текст і deep-link/reload PASS; A/C —2N/A. Для E після reload знадобився свіжий snapshot після settle: це не записано як дефект або автоматичний PASS без читання. Первинне відкриття native mobile menu F перехопило кліки; меню закрито, всі8 F full-input cases повторно виконано й пройдено. Непройдені intercepted clicks не зараховані до64. Свіжі6 B states замінили transient попередній checkpoint; повтори не збільшують unique count.

Після приймання відновлено original1280×720 light стан; actual DOM це підтвердив. Фінальний активний урок — Negative Questions з own-input4/4 та widget5/5. Private screenshot F показує власні2/2 full inputs і widget5/5; зображення та їхні локальні шляхи до публічного звіту не додано.

Після останнього браузерного стану незалежний readonly helper ще раз пройшов усі46 tables: поточний sql_after точно дорівнює combined final AFTER, всі non-body fields/timestamps/questions/progress/інші rows exact, DB writes0, HTTP requests0. Receipt postbrowser-readonly-v1.json наведено нижче. Лічильник body_updates=2 у цьому SELECT-only receipt означає дві перевірені раніше застосовані successor bodies, НЕ два нові записи.

У всіх 36 states межі навчальних блоків залишаються всередині viewport; у додатково оглянутих мобільних controls/paragraphs також немає виходу за його межі. Це не повний WCAG-аудит кожного текстового фрагмента. Широкі таблиці A/B/C мають власний native scroll: client/scroll width 322/576 px на 390 px та 252/576 px на 320 px. Окремо декоративна оболонка має document scrollWidth на 15–29 px більшу за clientWidth у мобільних станах; це не названо нульовим page overflow й не маскувалося CSS-змінами в контентному пакеті.

Browser screenshots/proofs/сесійні дані залишаються приватними. Особистий global localStorage/progress не очищався. Safety/URL guards не обходилися. Print/no-JS/WCAG — not_checked.

## 9. Автоматичні перевірки й неприховані baseline failures

### Ізольований M79 profile — PASS

28 unique tests у5 класах, не56 через повтор ROOT/WT:

| Клас | Tests | ROOT assertions | WT assertions |
|---|---:|---:|---:|
| M79QuestionTypesCompletionPackageTest | 5 | 7333 | 7333 |
| M79NativePracticeTest | 1 | 38 | 38 |
| M79OwnedSharedSourceDeltaTest | 5 | 2772 | 5893 |
| M79QuestionInsertScopeTest | 9 | 254 | 254 |
| M79PracticeCriteriaRevisionTest | 8 | 118 | 118 |
| Разом | 28 | 10515 | 13636 |

Обидва:0failures/0errors/0skips/0warnings, exit0. Є1PDO deprecation issue у17 SQLite methods; це задокументовано, не приховано як «без deprecations». Ізольовано testing+SQLite:memory:, робоча .env/робоча MySQL/progress для цих тестів не використовуються.

Покрито exact six-owner source/metadata scope, existing/new practices, Page_v2 і NULL/range boundaries, wrong locale/category/parent/owner, missing/empty owner set, stale body/plan, duplicate practice/widget, втрату retained function/demo, aliases/option-key collision, negative polarity, exact supplement inserts і source successor labels. Native acceptance перевіряє correct/incorrect/empty/retry/aliases та токени без заміни engine.

### Сім regression suites: результати final1.0.1

| Suite | Tests | Assertions | Failures | Errors | Skips | Warnings | Deprecations | Exit |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| M78 | 41 | 22119 | 4 | 0 | 0 | 0 | 1 | 1 |
| M75 | 33 | 9522 | 1 | 0 | 0 | 0 | 1 | 1 |
| M76 | 46 | 10575 | 0 | 0 | 0 | 0 | 1 | 0 |
| M77 | 30 | 22051 | 1 | 0 | 0 | 0 | 1 | 1 |
| Generic | 30 | 14693 | 0 | 0 | 0 | 1 | 1 | 1 |
| Interaction | 134 | 1588 | 8 | 1 | 0 | 0 | 1 | 2 |
| Wide preservation | 136 | 27215 | 15 | 0 | 0 | 0 | 1 | 1 |

450 method executions =401 unique methods +49 повторних executions між suites; ці counts не сумуються з repeated ROOT/WT runs як нові унікальні тести. Final comparison018369… підтверджує однакові outcomes усіх7 suites між initial M79 AFTER і final1.0.1 AFTER. Це не означає «все зелене».

Відносно свіжого accepted-M78 BEFORE є4 НОВІ failures M78 raw whole-file profile guards, а не історичні failures лише через назву старого класу. Усі4 з повідомленням «An exact whole-file ROOT or WT profile is required»:

1. M78OwnedSharedSourceDeltaTest::test_all_326_visual_source_bytes_and_existing_m76_every_shift_binding_are_unchanged.
2. M78OwnedSharedSourceDeltaTest::test_caller_boundaries_and_exact_retained_identity_guard_remain_nonvisual.
3. M78OwnedSharedSourceDeltaTest::test_historical_m76_m77_sources_and_old_whole_file_tests_are_not_weakened.
4. M78OwnedSharedSourceDeltaTest::test_twelve_dispatch_lines_and_source_validator_reverse_to_exact_predecessor_profiles.

Причина: historical test приймає тільки цілі попередні code-byte profiles, тоді як M79 додає finite nonvisual registrations/exclusions. Тест рано зупиняється на raw-profile guard; це не доказ, що326 visual bytes змінилися. Новий незалежний M79 reversal/source comparison доводить саме finite delta,326visual і frozen-source preservation. Старі assertions не послаблено; ці4 failures НЕ названо PASS і НЕ приховано.

Окремі вже наявні причини:

- M75PronounFormsRuntimeTest::test_native_visual_and_interaction_sources_and_active_m74_artifacts_are_unchanged — старий цілий accepted-M74/served-BEFORE visual-profile guard.
- M77OwnedSharedSourceDeltaTest::test_twelve_finite_registrations_and_one_cardinality_hunk_reverse_to_complete_accepted_m76 — старий raw TheoryContentSource hash conflict після подальших accepted dispatch. У final message містить поточний hash; не змішано з новими4M78 failures.
- Interaction8failures:4 TheoryPracticeSetTokenBankUiTest static-source expectations,1 ComposePayloadBuilderTest past-perfect fixture,2 SavedTestClientAnswerLogicTest easy-mode static-source expectations,1 SavedTestJsStateTest filter fetch expected200/actual500.
- Interaction1error: SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test — ізольована SQLite fixture не має question_hints. Це не помилка робочої MySQL і не прихований PASS.
- Wide15failures залишаються окремим historical-preservation набором; raw-hash messages M73/M74 змінюють current actual hash після accepted dispatch, тому method/messages зіставлено, а не просто скопійовано total.
- Generic має0failure/0error, але1discovery warning таexit1; його не позначено чистим exit0.
- M76 behavioral/every/shift suite:46tests/10575assertions/0F0E, без ослаблення labels/token-value guards.

Final1.0.1 не додав нових outcomes до initial M79 AFTER. Водночас первинні4M78 raw-guard failures залишаються явною межею доставки.

### Реальні readonly gates — PASS

- Six-source builder gate:6targets/0failed/0writes.
- Full builder registry:169/0/0; усі163 попередні semantic rows exact. Від порівняння виключено лише whole-response http_sha256 із volatile chrome; owner/config/IDs/types/counts/widget fields не виключались.
- Old consumers:51GET200,13saved tests/888questions exact; per-question fields/options/answers/linked theory незмінні. Нормалізовано лише top-level shuffle order за identity.
- Six dynamic topic pools:84questions кожний, без потрапляння8supplement questions у старі memberships.
- Six course learning DOM boundaries exact до BEFORE; це не whole-page/auth-chrome byte identity.
- Final nav gate:12GET200,40footer anchorsGET200,9legacy404,57graded/3demos/20inputs/16full/6widgets×5, all46SQL exact.
- Source independent audit: explicit old→current lineage, frozen versions, profiles та route cleanup exact.

Inherited informational sql_allowlist_during_apply=['pages','text_blocks'] у старому consumer primitive не є повним описом M79 insert scope. Фактичний gate звіряє всі46 tables з комбінованим AFTER;131bank inserts окремо доведено postconditions. Цей службовий legacy підпис не використовується для твердження «питання не вставлялися».

## 10. Первинні мовні джерела та доступність

Це редакційна перевірка агентами, не human review. Нові приклади/вправи авторські; видавничі exercises не копіювалися. Повні access ledgers збережено у author parts/editorial map. Зазначений HTTP403/timeout не видається за успішне direct читання.

| Первинне джерело | Що перевірено | Реальний спосіб доступу |
|---|---|---|
| [Cambridge Alternative Questions](https://dictionary.cambridge.org/us/grammar/british-grammar/questions-alternative-questions-is-it-black-or-grey) | choice/yes-no readings, alternatives | direct403; прочитано індексований основний editorial body |
| [Cambridge Statement Questions](https://dictionary.cambridge.org/grammar/british-grammar/questions-statement-questions-you-re-over-18) | можливе перепитування без звичайної інверсії | індексований editorial body; direct success не заявлено |
| [Cambridge pay in cash](https://dictionary.cambridge.org/dictionary/english/pay-in-cash?q=pay-cash) | cash/card collocations без абсолютної заборони | індексований словниковий body |
| [Cambridge Tags](https://dictionary.cambridge.org/grammar/british-grammar/question-tags) | confirmation/same-polarity, auxiliary/pronoun | direct403; індексований основний body |
| [British Council Question Tags](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/question-tags) | special cases та фактичні replies |2direct timeouts; індексований Grammar explanation |
| [LearnEnglish Team: am I not](https://learnenglish.britishcouncil.org/comment/154218) | формальна повна форма am I not | іменована editorial reply PeterM24/07/2020; не learner comment і не main-body цитата |
| [LearnEnglish Team: lexical have](https://learnenglish.britishcouncil.org/comment/148452) | do з lexical have не лише US | іменована editorial reply PeterM06/05/2020 |
| [Cambridge Must](https://dictionary.cambridge.org/grammar/british-grammar/must) | modal meanings/tag caution | індексований основний editorial body |
| [Cambridge Negation](https://dictionary.cambridge.org/grammar/british-grammar/negation_2) | negative words, не тільки n’t | індексований editorial body |
| [Cambridge Not](https://dictionary.cambridge.org/grammar/british-grammar/not) | full not-order / contracted n’t | direct403; індексований основний body, включно з questions |
| [Cambridge Wh-questions](https://dictionary.cambridge.org/us/grammar/british-grammar/questions-wh-questions) | subject/object, emphatic/negative do | direct403; офіційний індексований editorial body |
| [British Council Questions and negatives](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/questions-negatives) | subject groups/agreement, main/inner clauses | частина author direct спроб timeout; індексований body прочитано; окреме main-читання body успішне. Іменовані Team replies відділено від learner comments |
| [Cambridge Do](https://dictionary.cambridge.org/grammar/british-grammar/do) | lexical/auxiliary do | direct403; індексований офіційний grammar excerpt |
| [Cambridge If or whether](https://dictionary.cambridge.org/grammar/british-grammar/if-or-whether) | finite alternation/позиційні обмеження | direct403; індексований editorial body |
| [Cambridge Reported speech](https://dictionary.cambridge.org/grammar/british-grammar/reported-speech-indirect-speech) | embedding проти actual reporting | direct403; окремий індексований reporting editorial excerpt |
| [Cambridge Politeness](https://dictionary.cambridge.org/pl/grammar/british-grammar/politeness_2) | past-shaped current requests/context | офіційний індексований English Grammar Today body з англійським поясненням |
| [Cambridge Wondering](https://dictionary.cambridge.org/dictionary/english/wondering) | wish-to-know/request opening | індексовані редакційні словникові senses; corpus examples не копійовано як правило |

SEO/helpful-content орієнтація не є гарантією індексації або ranking. Production доступність/індексація у scope не входили.

## 11. Доказові receipts та delivery checkpoint

Raw receipts/SQL snapshots/backup/proofs/screenshots залишаються у ignored private storage; до commit не включаються. Нижче лише basename та opaque SHA256 для exact-bound review, без runtime identity, секретів або raw SQL.

| Receipt | SHA256 |
|---|---|
| before-v1.json | 8c261b17b3f7e332fc5fc86eef8b555af83de19938aee7ae83d2a23e7dbb3add |
| before-old-consumers-v1.json | ada89c6d6961dd7446313e401caec85a7dc40e9c6e673dc9042ee63ba8f80c63 |
| prospective-metadata-v1.json | 18f0f141aa84e6ed7317207c39af9de57b48038be191a9308c0345d67c9877a9 |
| final-readonly-v1.json | c9c8f0d1b42d5a0158555ef29f3843c66a448cbcacb963348f4f46c1e5c5fb67 |
| m79-practice-criteria-before-v1.json | 43b480a36883d551a6b5f05320c228f1fed8e79584a6d5b7a0eb22fadb0d5b09 |
| criteria-preview-v1.json = criteria-backup-v1.json | 3cbc96de22a9b1aafd6c90b65c9a67521b822d7f977c04a9b685bb1971369800 |
| criteria-postconditions-v1.json | b9f38d0767c8baba56443f7e4aab286e483986bd800c4d8d013e5eeacbf009a3 |
| final-readonly-v2.json, pure2-layer composite | 3a05df3eab0b664fb076386868908b8b91d94d1c30410b0419f0b441a2fe9b14 |
| nav-metadata-v2.json | 453553bb2ffa2c4cd5602bb68a4bde5709d00bcf73922f9c5fa6d76bdccaabff |
| m79-sentence-builders-v2.json | d0648826640b85080707d5dc89909725eb7d59d0362d8f16297a54b66529eb04 |
| registry-sentence-builders-v2.json | 43dbeeccfa40a105245327231a91295839e663195496a20dbff4123409c85396 |
| after-old-consumers-v2.json | 3337043b484fbbaa957b31bb9da068a0bbf9cf4414d10fd4cc0b046a08adb7fc |
| postbrowser-readonly-v1.json | 7536ca794010be46216054f6fef8cf9825afe953d56b278d1cd841ffd5c24ebe |
| browser-progress-v1.json, final36/64/30/72/17 | 9a0bdf090aac041ea8f2751348119843d96c63bcdafed8c57faf0e9d48a53100 |
| baseline-after-v2-comparison.json | 018369501ebc408b9d8b8125b5492297a7cb7ca362e171ded3bc479b6abddcc7 |
| independent-source-final-comparison-v2.json | e1178a0338580588d9dd1f8434760771f59bdce1e052e245d0bad7902c7799d3 |
| Final M79 ROOT test receipt | dd0fb4f46305f0c615a0df46cfb9a07d6be85c9fb40c7a32c31f68160cf3ed0c |
| Final M79 WT test receipt | 67eb2682299e56727263efb7ee3097d05b7b8522d0efd744f5db333767fa0731 |

Primary plan digest8652453e1ddb4ce55ec4ee8ca3df2ca048b8e56b73a7cb123658e1ca291cf859; successor plan digest68936ba88db70843e581cf6f8368621bae4e9ec6e787d207b18b079d1e6f3ceb. Digests відрізняються від raw-file SHA і не підміняють їх.

Initial nav attempt під час переходу1.0.1 завершився fail-closed на source-pin drift і не створив успішного receipt. Його не використано як доказ final bytes. Усі наведені v2 HTTP receipts отримані після final criteria apply.

<!-- M79_DELIVERY_FINAL: main agent records staged review/normal push verification in the final delivery message. -->

Delivery checkpoint після приймання:

- Фінальний staged allowlist/scope review: рівно 84 погоджені файли, без сторонніх dirty changes; список індексу точно дорівнює переглянутому allowlist. Shared delta: 8 файлів, 12 доданих невізуальних dispatch lines, жодного visual edit. Виключено 14 старих M75/M76 drafts/helpers і одну незастосовану M79 Markdown-пропозицію; приватні evidence файли не staged.
- Frozen-source validation повторено без Laravel bootstrap/.env/SQL/HTTP у ROOT і WT: по 25 pinned sources, два exact criteria transitions, обидва manifests 1.0.0/1.0.1 перевірені за raw SHA256 і byte size.
- Звичайний git diff --check повідомляє рівно4 додані CR-at-EOL controller lines: frozen TestJsV2Controller.php зберігає CRLF. Це не4 зайві пробіли й не дозвіл нормалізувати весь файл.
- Для перевірки цього exact профілю використано тільки command-local git -c core.whitespace=blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol diff --cached --check: exit 0. Глобальна Git-конфігурація/правила whitespace не вимикаються; інші source bytes не переписуються.
- Scoped commit → normal push лише в codex/seo-m79-question-types-completion-standard-view → незалежна перевірка remote SHA==HEAD. Main/production не є ціллю доставки.
- Full SHA коміту, який містить цей report, відтворюється через git log -1 --format=%H -- docs/reports/seo-m79-question-types-completion-standard-view.md; actual SHA та результат remote verification наводяться у фінальній відповіді. Майбутній self-referential SHA не вбудовується у власний commit.

Нові author/diagnostic/report файли створюються UTF-8, LF, рівно1final LF; historical M78 frozen EOF erratum не «виправляється» заднім числом. .env, секрети, dumps/backups, private proof/screenshot payloads, caches, generated assets, temporary routes та сторонні dirty changes до доставки не входять.
