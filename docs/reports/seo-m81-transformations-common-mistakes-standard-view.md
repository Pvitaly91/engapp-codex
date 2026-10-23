# M81 — Sentence Transformations / Common Mistakes

Статус: **локальне впровадження, браузерне приймання, post-UI DB/source перевірки та фінальні цільові ROOT/WT тести завершено.** Усі шість сторінок реально оновлено на `http://gramlyze.loc`. Scoped delivery описано внизу; точний commit SHA та результат незалежної remote-перевірки наведено у фінальному повідомленні до цієї редакції. Історичні failures та чотири нові M80 exact-source guards наведено окремо: твердження «всі регресії green» немає. Production не перевірявся й не змінювався.

## Шість цільових сторінок

| Owner | Сторінка | Page / category | Нова практика й widget | Display level | Continuing / нова практика | Meaningful details |
| --- | --- | --- | --- | --- | --- | ---: |
| A | [Sentence Transformations — Conditionals](http://gramlyze.loc/theory/sentence-transformations/conditionals) | 147 / 23 | B1 | B1–B2 | 6 / 1 | 1 |
| B | [Sentence Transformations — Relative Clauses / Linking Words](http://gramlyze.loc/theory/sentence-transformations/relative-clauses-linking-words) | 150 / 23 | B1 | B1–B2 | 6 / 1 | 1 |
| C | [Sentence Transformations — Articles / Quantifiers](http://gramlyze.loc/theory/sentence-transformations/articles-quantifiers) | 146 / 23 | B1 | B1–B2 | 6 / 1 | 1 |
| D | [Sentence Transformations — Word Order / Emphasis](http://gramlyze.loc/theory/sentence-transformations/word-order-emphasis) | 153 / 23 | B2 | B2–C1 | 6 / 1 | 1 |
| E | [Articles: Common Mistakes](http://gramlyze.loc/theory/common-mistakes/articles-common-mistakes) | 38 / 9 | A2 | A2–B1 | 6 / 1 | 0 |
| F | [Word Order: Common Mistakes](http://gramlyze.loc/theory/common-mistakes/word-order-common-mistakes) | 43 / 9 | A2 | A2–B1 | 6 / 1 | 0 |

Continuing у таблиці не включає окремий UK subtitle. Разом — 36 старих навчальних/навігаційних блоків, шість UK subtitle і шість нових native `practice-set`. Технічний рівень нової практики не підміняє display range і не змінює рівнів старих SQL-рядків.

Exact identities у [native bindings](../content/m81-native-bindings.v1.json):

- A–D: namespace `Database\Seeders\Page_V3\SentenceTransformations\`, класи `SentenceTransformationsConditionalsTheorySeeder`, `SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder`, `SentenceTransformationsArticlesQuantifiersTheorySeeder`, `SentenceTransformationsWordOrderEmphasisTheorySeeder`.
- E/F: namespace `Database\Seeders\Page_V3\CommonMistakes\`, класи `CommonMistakesArticlesTheorySeeder` та `CommonMistakesWordOrderTheorySeeder`.
- Continuing SQL level vectors: A/B/C — `[B1, NULL, NULL, B1, B1, NULL]`; D — `[B2, NULL, NULL, B2, B2, NULL]`; E/F — `[A2, NULL, NULL, A2, A2, NULL]`.

Зберігаються Page IDs, raw title/H1, ancestry, старі UUID, order/column/level, tags/anchors та EN/PL-рядки. Новий practice додається в `left`, source index 6 / sort order 7; зовнішні heading і CSS class для нього — `NULL`.

## Межі зміни

- Використовується чинний канонічний шаблон та native-компоненти. Нових в’юх, CSS, JS, форматера, grader або глобального runtime-порогу довжини detail немає.
- 36 нових власних graded tasks: 12 на форму, 12 на значення, 12 на ціле речення. Кожна сторінка має схему **2 + 2 + 2**. Ручне введення і токени — два способи виконати ті самі 12 full-sentence tasks, не додаткові питання.
- Окремо підключено й прийнято в браузері шість обов’язкових вбудованих sentence builders по п’ять тематичних питань: **30 selected existing / 30 reused / 0 new questions; кожен widget — 5/5**. Це не заміна власних 36 завдань.
- Чотири finite meaningful details належать конкретним пунктам A–D. Для E/F details немає; короткі правила, переклади та потрібні застереження залишаються visible basic.
- Питання, відповіді, options, pivots, hints, variants, збережені тести й прогрес не переписуються; нових тематичних банків не створюється.
- Caller-owned theory boundary зберігає курси, standalone tests і page-mixed поза новою авторською проєкцією. Їхній фактичний AFTER перевірено окремим read-only consumer audit.

## Авторська редакція: що саме виправлено

| Owner | Збережений навчальний фокус і виправлення | Семантична межа |
| --- | --- | --- |
| A — Conditionals | Збережено unless, wish / if only, would rather та had better. Для wish прямо задано час/подію; для would rather — виконавця. Пара should → had better отримала явний ризик останнього автобуса та задум попередження. | Unless не перетворено на універсальну заміну будь-якого if … not або заборону слова not. Виконання умови не гарантує результату; нейтральна порада не оголошена автоматично рівнозначною попередженню. |
| B — Relative / Linking | Збережено об’єднання, скорочення, причину, наслідок, поступку й мету. Which / where обираються за роллю в підрядній частині. Уточнено власника іспиту, наступний ранок, потрібні I/we та виконавців скорочення. | Назва місця не робить where автоматично правильним. Різних виконавців не можна злити в same-subject reduction. Office-input перевіряє ролі, а не точність ком: native punctuation normalizer не змінюється. |
| C — Articles / Quantifiers | Some advice відокремлено від однієї точної поради; потрібну одиницю задано явно. A few / a little не прирівняно до enough. Кількість, референт, учасники й тема збережені; для scarcity-пар вказано ті самі точні величини. | Few ≠ автоматична достатність, some ≠ exactly one, little ≠ not much у будь-якому контексті. У reviewed-парі 200 мл за потреби одного літра не зникає різниця акценту між майже відсутньою й невеликою кількістю. |
| D — Word Order / Emphasis | Збережено auxiliary, особу й час інверсії; only-subject відділено від restrictive adverbial. Cleft зберігає Tom / not me; emphatic do прив’язано до підтвердження факту, а не будь-якої інтенсивності really. | Зміна інформаційного фокусу навмисна, але не означає повної прагматичної тотожності. Neutral often order не є забороною всіх літературних інверсій; правильний імператив Do be careful не оголошено помилкою. |
| E — Articles: Mistakes | Діагностика: задум → тип іменникової групи → можливий збій → мінімальна зміна → перевірка змісту. A/an залежить від звуку, зокрема MBA; distinguished general/specific coffee, people і функція school. | Детермінатив не завжди має бути a/an: my/the/this зберігаються. Уже правильний specific-coffee приклад не виправляється навмання. Відповідь «Без артикля» є явним непорожнім варіантом. |
| F — Word Order: Mistakes | Розведено нейтральний порядок, frequency adverbs, be/modal і lexical do-support, direct / embedded / subject questions. Збережено main Do you know, потрібні підмети, not, учасників та прислівники. | SVO не означає обов’язкового додатка для кожного дієслова. Типове місце «часто» не забороняє будь-яке fronting; мінімальне виправлення не дозволяє довільно переписати зміст. |

Усі чотири transformation rows кожної A–D збережено на початкових позиціях: загалом 16 пар. І source, і target мають повні англійські версії та українські переклади; семантичне уточнення контексту описане явно, не приховане як механічне скорочення. E/F зберігають власні діагностичні функції, а не дублюють довідковий курс артиклів чи порядку слів.

## Native component map та metadata

| Source slot | M81 native component | Збереження / явний перехід |
| --- | --- | --- |
| Hero, header | `hero` | Початкові навчальні функції, title/H1 та owner збережено; оновлено авторський intro. |
| Перший box, left | `usage-panels` | Шість переходів `box` → native; зовнішній heading стає `NULL`, заголовок є в data. |
| Другий box, left | A–D `comparison-table`; E/F `usage-panels` | Ще шість явних переходів; UUID/order/column та SQL `NULL` level незмінні. |
| Помилки, right | `mistakes-grid` | Розмежовано неправильне, мінімальне виправлення, переклад і пояснення. |
| Підсумок, right | `summary-list` | Основні перевірки змісту й форми; без нового дизайну. |
| Навігація, footer | `navigation-chips` | A–D по вісім, E/F по шість: 44 items / 14 унікальних призначень. Усі items звірено, 14/14 destinations GET → 200; окремо шість footer UI-переходів. |
| Новий appended slot, left | `practice-set` | Сім блоків на owner; 2 selects + 2 choices + 2 full inputs. |

Дозволено рівно **12 continuing SQL rows / 24 metadata fields**: `type` і `heading`. Це підмножина тих самих continuing body updates, а не 24 додаткові SQL updates. Жодних змін `column`, `level`, `css_class`, UUID чи sort order у старих рядках не дозволено.

Finite detail owners: `conditionals-unless-negative-scope`, `relative-clauses-linking-words-different-actors`, `articles-quantifiers-advice-unit`, `word-order-emphasis-only-scope`. Вибір семантичний у frozen author projection; потрібні базові критерії та повні переклади не приховуються в detail.

## Versioned джерела і перевірка доступу до джерел

Основні артефакти: [author master](../content/m81-transformations-common-mistakes.v1.json), [українська авторська редакція](../content/m81-transformations-common-mistakes.v1.0.0.uk.md), [editorial/source ledger](../content/m81-transformations-common-mistakes.editorial.v1.json), [component/source map](../content/m81-transformations-common-mistakes.components.v1.json), [36-task map](../content/m81-transformations-common-mistakes.tasks.v1.json), [native bindings](../content/m81-native-bindings.v1.json), [sentence-builder contract](../content/theory-sentence-builders/m81-transformations-common-mistakes.v1.json), [bank audit](../content/m81-sentence-builder-bank-audit.v1.json), [finite widget selection](../content/m81-widget-selection.v1.json) та [source checksums](../content/m81-source-checksums.v1.json).

Frozen master SHA-256: `f425c97115830465b383795596632fe776fabfe122339dc2bc8d0906df298c77`. Пакет і шість active definitions materialized як UTF-8 без BOM, LF, без trailing whitespace, рівно з одним final LF. Reviewer-процес агентний; human review не заявляється. Видавничі вправи не копіювалися.

| Primary source | Фактичний доступ і використання |
| --- | --- |
| British Council — [Indefinite article](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/indefinite-article) | Direct main editorial body прочитано: singular indefinite reference та sound-based a/an. Learner comments не є основою правил. |
| British Council — [few / a few / little / a bit](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2-grammar/quantifiers-few-a-few-little-a-bit) | Direct editorial body прочитано; «often» не перетворено на гарантію достатності. Це окрема доступна сторінка. |
| British Council — [точний starting Quantifiers URL](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/quantifiers) | Main і author direct fetch завершилися timeout. Прочитання саме цієї адреси не заявляється. |
| Cambridge — [Unless](https://dictionary.cambridge.org/grammar/british-grammar/unless), [Had better](https://dictionary.cambridge.org/grammar/british-grammar/had-better), [Would rather](https://dictionary.cambridge.org/grammar/british-grammar/would-rather-would-) | Direct HTTP 403; прочитано indexed editorial body. Обмеження заміни, сила попередження та actor/time звірені з основним редакційним текстом. |
| Cambridge — [Relative](https://dictionary.cambridge.org/grammar/british-grammar/relative), [In spite of / despite](https://dictionary.cambridge.org/us/grammar/british-grammar/in-spite-of-and-despite), [Advice / advise](https://dictionary.cambridge.org/grammar/british-grammar/advice-or-advise) | Direct HTTP 403; indexed editorial body прочитано. Це не успішний direct fetch і не копіювання exercise bank. |
| British Council — [Wishes, indexed editorial section](https://learnenglish.britishcouncil.org/comment/190710) | Прочитано indexed основне пояснення; спроба canonical path дала tool error. Learner comments не використано для висновків. |
| Google — [People-first helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) | Main прочитав direct primary guidance як редакційний орієнтир. Гарантій індексації або позицій немає. |

Додаткові primary references для reason, scarcity, inversion, articles і adverb position та точний спосіб доступу наведено в ledger кожного owner. Статус одного доступного URL не поширюється на іншу адресу чи весь сайт.

## Власна практика та окремі existing-question widgets

Власні завдання мають видимі умови: тільки підтримані `select_intro`, `choice_intro`, `input_intro`, task labels та `inputs.after`. Непідтримані невидимі `practice-set.data.intro` і `selects[].prompt` відсутні. До відповіді не показується готовий порядок речення; `before` у full inputs містить перемішані групи. Приймаються лише явно reviewed aliases, не необмежений paraphrase grader.

| Owner | Form / meaning / full | Existing widget UUIDs: останній сегмент після спільного prefix |
| --- | --- | --- |
| A | 2 / 2 / 2 | `stcon-poly-b1-`: 01, 06, 07, 08, 12 |
| B | 2 / 2 / 2 | `strcl-poly-b1-`: 02, 05, 06, 07, 09 |
| C | 2 / 2 / 2 | `staq-poly-b1-`: 03, 05, 10, 11, 12 |
| D | 2 / 2 / 2 | `stwoe-poly-b2-`: 03, 05, 08, 11, 12 |
| E | 2 / 2 / 2 | `cma-poly-a2-`: 02, 03, 04, 08, 10 |
| F | 2 / 2 / 2 | `cmw-poly-a2-`: 01, 03, 04, 05, 12 |

Primary banks: namespace `Database\Seeders\V3\Polyglot\` і відповідні `PolyglotSentenceTransformations{Conditionals,RelativeClausesLinkingWords,ArticlesQuantifiers,WordOrderEmphasis}AllLevelsLessonSeeder`, `PolyglotCommonMistakes{Articles,WordOrder}AllLevelsLessonSeeder`. Exact identities та original IDs містяться в contract/bank audit, а не визначаються лише подібністю назви.

У свіжому page-wide BEFORE захоплено 888 original question rows, з них **456 type-4: 432 у шести primary banks + 24 legacy type-4 у E**. Ці 24 legacy questions залишаються read-only поза finite selected scope. Поле з успадкованою назвою `review_summary.pagewide_original_type4_pool` у frozen bank audit рахує тільки 432 primary-bank rows, не весь page-wide набір; повний незалежний BEFORE та old-consumer audit охоплюють також legacy 24. Із primary pool є 72 eligible rows — по 12 у точному тематичному банку й потрібному CEFR. Відібрано 30. Наявні keys, options, case та multiplicity збережені; condition-only clones застосовуються лише в canonical theory. Це не зміна моделей старих питань або formatter.

Відомі непридатні для цієї finite п’ятірки candidates виключено, а не «виправлено» в глобальному банку:

- B: `strcl-poly-b1-01/03/11`; D: `stwoe-poly-b2-04/10`; F: `cmw-poly-a2-08` — незмінні Who / Where / When лишалися з великої літери всередині речення.
- C: `staq-poly-b1-08/09` — medial capitals Glass / Students; `staq-poly-b1-06` — фактично відтворений незмінним native formatter дефект із No та пунктуацією. No / not any залишено у власній практиці, а widget використовує reviewed information correction.
- E: `cma-poly-a2-06` — lowercase the після крапки в чинному ordered surface.

Виключення не означає, що всі інші невідібрані питання є неправильними. Їхні банки, standalone membership, відповіді, options і прогрес залишаються незмінними. Offline replay усіх 30 selected для ROOT і WT виконано; після apply також прийнято всі 30 відповідей у шести реальних widgets, кожен із видимим результатом 5/5.

## Fresh BEFORE і source lineage

Accepted base: `36753e6bd0bd1f8257908f3b30af9856d21e8e9f` (M80). Робоча гілка — `codex/seo-m81-transformations-common-mistakes-standard-view`. M80 BEFORE не використовується замість свіжого M81 capture.

| Перевірка / захист | Фактичний BEFORE або вже виконана source-перевірка |
| --- | --- |
| Independent source inventory | 3 197 paths з окремими served ROOT / WT raw та LF profiles; 3 150 попередніх tracked profiles звірені з accepted M80. |
| Visual sources | 326 paths. Чинні ROOT/WT відмінності збережено, не вирівняно; новий M81 source guard підтверджує 0 visual edits. |
| Public immutable manifest | 2 856 paths: non-target frozen sources, 911 historical test paths та решта явно protected generic/routes/historical sources, включно з captured absences. |
| UK / non-UK | 42 continuing UK rows, включно з шістьма subtitle; 84 EN/PL rows. Первинні AFTER postconditions підтвердили exact-bound збереження. |
| Повна БД | Захоплено 46 table fingerprints; первинний AFTER і строгий post-UI repeat PASS, усі старі рядки/timestamps exact. |
| Sentence-builder registry | Фактично 181 = 175 exact попередніх + шість нових M81, 0 failures / 0 writes. Total виведено зі свіжого union, не з довічного fixed cap. |
| Shared registrations | Рівно 12 nonvisual additions у восьми файлах; 0 validator additions/removals. Новий guard перевіряє reverse до незалежного BEFORE у ROOT і WT. |

Вісім shared files — `TheoryContentSource`, `TheoryHtmlAdapter`, `TheoryLegacyAdapter`, `TheoryContentRenderer`, `PracticeCompletionLinkedPractice`, `TextBlockToQuestionsMatcherService`, `TheoryCourseManifestService`, `TestJsV2Controller`. Зміни обмежені реєстрацією шести exact owners; зворотне вилучення лише owned lines повертає raw/LF BEFORE. Старі тести, fixtures, engine, native factory, форматування й оцінювання не послаблюються.

Безпечні provenance SHA-256 pins приватних receipts:

- Independent source BEFORE: `567b6efe48063a18ae1c6267b211bf387f02699ea263db910c8d6cb373430d54`.
- Source/DB/HTTP BEFORE: `facabc5369230dac0958f2e2277c7cd0e4b0523fb794c041afd46116e095c023`.
- Old consumers BEFORE: `4cedbaa44c8aaf90d6e1b231072b37403f90f657f710a7b434253a281a84db5c`.
- Owned-hunk fixture: `63cd447c601642e04bbd918821487f7f1b96ccff5bc2e72e1d7e0d294d8da48f`.

Raw DB rows, timestamps, runtime identity, nonce/proof contents, дампи/backups, секрети та приватні діагностичні файли до цього звіту не включаються.

Фінальний незалежний scan охопив ті самі **3 197 paths у кожному ROOT/WT context**. Content/runtime delta — рівно 14 paths: шість цільових definitions і вісім shared registration files. Інших record changes — 0. Окремий delivery delta — лише WT `.gitattributes` з незміненим старим raw prefix; це не visual/content edit.

Підтверджено exact bytes усіх 326 visual paths, 911 historical test paths, усіх captured absences/routes, 31 historical M79 та 19 M80 runtime/diagnostic sources. Збережено 167 foreign visual raw differences і сім LF differences між ROOT/WT, а не нормалізовано їх. Усі 17 materialized artifacts/definitions відповідають frozen outputs та окремим raw/LF pins; historical frozen group має 1 896 paths з рівно шістьма погодженими definition exceptions. Audit — 0 source writes / 0 DB writes / 0 HTTP.

Повторні source captures після cleanup та post-UI байтово тотожні: AFTER SHA-256 `ea4171807d06468f3ed875b918757bc2758ef40a57dd0c3479da1f24a56aed56`. Фінальне незалежне порівняння — PASS, receipt SHA-256 `9bb81105ba440fe77b1c78f802e5e67839f6815c6e44b9c16bf4005147300a8b`. Це source-аудит; DB та UI мають окремі receipts нижче.

## Fresh isolated BEFORE: історичні suite lists

Незмінений isolated runner: `testing`, SQLite `:memory:`, array cache/session, working `.env` не завантажується, CLI OPcache вимкнений. У кожному запуску — 675 protected files / 0 changes.

| Suite | Methods | Assertions | Failures | Errors | Skips | Warnings | Deprecations | PHPUnit exit |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| M78 | 41 | 22 119 | 4 | 0 | 0 | 0 | 1 | 1 |
| M75 | 33 | 9 522 | 1 | 0 | 0 | 0 | 1 | 1 |
| M76 | 46 | 10 575 | 0 | 0 | 0 | 0 | 1 | 0 |
| M77 | 30 | 22 051 | 1 | 0 | 0 | 0 | 1 | 1 |
| Generic | 30 | 14 735 | 0 | 0 | 0 | 1 | 1 | 1 |
| Interaction | 134 | 1 588 | 8 | 1 | 0 | 0 | 1 | 2 |
| Wide | 136 | 27 221 | 15 | 0 | 0 | 0 | 1 | 1 |
| M79 | 28 | 7 915 | 4 | 0 | 0 | 0 | 1 | 1 |
| M80 ROOT | 20 | 21 067 | 0 | 0 | 0 | 0 | 1 | 0 |
| M80 WT | 20 | 37 453 | 0 | 0 | 0 | 0 | 1 | 0 |

Разом **518 method executions / 449 unique methods / 69 overlapping executions**. Порівняння свіжого M81 BEFORE з accepted M80 AFTER: 0 added / missing / changed methods; assertions і повні failure/error messages збігаються. Comparator receipt SHA-256: `9d6b069d924cbc502b4023d6a6f111e166b233a009a9411d52ddc0d8ed118d2c`.

Це саме BEFORE. Чотири M79 exact-source failures уже є в ньому після accepted M80 registrations. M81 AFTER порівняно method-by-method нижче; нові failures не названо історичними лише за назвою класу, historical assertions не редагувалися.

## Фактичний isolated AFTER: baseline delta

| Suite | Methods | Assertions | Failures | Errors | Skips | Warnings | Deprecations | PHPUnit exit |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| M78 | 41 | 22 119 | 4 | 0 | 0 | 0 | 1 | 1 |
| M75 | 33 | 9 522 | 1 | 0 | 0 | 0 | 1 | 1 |
| M76 | 46 | 10 575 | 0 | 0 | 0 | 0 | 1 | 0 |
| M77 | 30 | 22 051 | 1 | 0 | 0 | 0 | 1 | 1 |
| Generic | 30 | 14 777 | 0 | 0 | 0 | 1 | 1 | 1 |
| Interaction | 134 | 1 588 | 8 | 1 | 0 | 0 | 1 | 2 |
| Wide | 136 | 27 227 | 15 | 0 | 0 | 0 | 1 | 1 |
| M79 | 28 | 7 915 | 4 | 0 | 0 | 0 | 1 | 1 |
| M80 ROOT | 20 | 5 919 | 4 | 0 | 0 | 0 | 1 | 1 |
| M80 WT | 20 | 5 931 | 4 | 0 | 0 | 0 | 1 | 1 |

Inventory незмінний: 518 executions / 449 unique methods / 69 overlapping executions, 0 added/missing methods. У кожному запуску 675 protected paths / 0 changes. AFTER comparison receipt SHA-256 — `80b47292891e77766800819e34f3ad90037b49fcb5d9433699ad5cd00bff997b`.

Delta класифіковано явно:

- M78, M75, M76, Interaction і M79: ті самі methods/assertions/full outcomes, що у свіжому BEFORE.
- M77: той самий existing failure; змінено actual source hash у його повідомленні через owned M81 registrations. Це не byte-identical failure message, тому зміна не прихована.
- Wide: три existing exact-source failures мають змінені actual hashes (`M73SharedLineageTest` та два `M74OwnedSharedSourceDeltaTest` methods); кількість failures/errors не збільшилася. Окремий registry PASS method має +6 assertions для шести нових owners.
- Generic: чинний catalogue PASS method має +42 assertions (1 228 → 1 270) для шести нових targets; це не нові methods і не новий failure. Existing warning/deprecation залишаються.
- **M80 ROOT/WT: чотири нові unique failures**, відтворені в обох contexts (вісім failing executions). Вони були PASS у свіжому M81 BEFORE, тому не є старим baseline. Це guards точного M80 whole-file AFTER profile, який уже містить інший набір registration lines, а не доведена behavioral regression. Решта 16 methods кожного run незмінні.

Чотири нові methods у `M80OwnedSharedSourceDeltaTest`: `test_twelve_owned_additions_reverse_to_the_fresh_independent_raw_and_lf_before`, `test_theory_caller_and_old_retained_practice_guards_remain_unchanged_nonvisual`, `test_all_326_visual_profiles_and_existing_token_shift_label_binding_are_raw_exact`, `test_all_historical_sources_and_903_test_paths_keep_individual_raw_profiles_and_absences`. Вони відхиляють actual controller через відсутність точного старого ROOT/WT whole-file profile й виходять раніше; зменшення assertions у M80 AFTER не видається за скорочення test inventory.

Для локалізації причини виконано окремий **приватний counterfactual**, не actual AFTER PASS: тільки 12 M81 registration additions та шість цільових definitions повернуто до свіжого BEFORE у приватних копіях. Незмінені п’ять historical M80 methods дали **5 / 31 745 assertions / 0 failures / 0 errors / 0 skips / exit 0**, точні BEFORE methods/assertions/outcomes відновлено. 675 protected paths / 0 changes, working DB connections/writes 0, actual source writes 0, historical test/fixture edits 0. Counterfactual receipt SHA-256 — `4e05e26bb3dc82d9ed41f11d85b72f17d71d4a6f7749d7e899172c25dec52793`.

Це підтверджує finite source-lineage причину, але **не перефарбовує чотири actual AFTER failures у green**. Їх залишено в реальному результаті; старі raw-byte assertions і fixtures не послаблено. Попередні невдалі підготовки приватної копії (Windows path limit і перевірки точних route snapshots до PHPUnit) збережено окремо, не зараховано до цього PASS.

## Нові M81 тести: фактичні результати

П’ять нових класів: `M81TransformationsCommonMistakesPackageTest`, `M81SemanticFixturesTest`, `M81NativePracticeTest`, `M81ContentPatchTest`, `M81OwnedSharedSourceDeltaTest`.

| Source profile | Methods | Assertions | Failures / Errors / Skips | Warnings | Deprecations | PHPUnit / runner exit |
| --- | ---: | ---: | --- | ---: | ---: | --- |
| ROOT target v2 | 24 | 22 758 | 0 / 0 / 0 | 0 | 1 | 0 / 0 |
| WT target v2 через ROOT isolated runner з абсолютними WT test paths | 24 | 39 384 | 0 / 0 / 0 | 0 | 1 | 0 / 0 |
| Фінальний ROOT rerun після runtime/browser перевірок | 24 | 22 758 | 0 / 0 / 0 | 0 | 1 | 0 / 0 |
| Фінальний WT rerun після runtime/browser перевірок | 24 | 39 384 | 0 / 0 / 0 | 0 | 1 | 0 / 0 |

Це **24 unique methods**, повторені у двох contexts і двох завершених rounds: 96 executions у чотирьох наведених runs, не 96 різних тестів. Кожен запуск підтвердив 675 protected paths / 0 changes. Різна кількість assertions спричинена перевіркою окремого WT і explicit served ROOT lineage context, не додатковими test methods.

Єдина PHP deprecation — чинна `PDO::MYSQL_ATTR_SSL_CA` у `config/database.php:62` на PHP 8.5; її trigger дають п’ять нових `M81ContentPatchTest` methods. Це одна унікальна deprecation, не п’ять різних проблем; вона не прихована як повністю чистий run. PHPUnit і runner в обох випадках завершилися exit 0.

ROOT target v1 мав 24 methods / 22 700 assertions / 1 failure / 0 errors / exit 1. Новий semantic fixture помилково очікував застаріле «без ком», тоді як frozen visible criterion — «не точність розстановки ком». Виправлено лише очікування нового M81 тесту; frozen payload, historical tests і правило оцінювання не змінювалися. Ця невдала спроба збережена в історії; її не класифіковано як стару регресію. Початковий WT-cwd wrapper мав окрему preflight-проблему до запуску PHPUnit; його не включено в counts успішних тестів.

Provenance pins цільових receipts: ROOT v1 — `f4083548560b53ddd76083d1c21901e076d215b90e97ecd97ada2e1d1c02a54b`; ROOT v2 — `4b9ccf9bda08bdaaa0fee1a1719e8d1ad9d7075975da8f7b7dd60383bb93309b`; WT v2 — `c129c3177c80509eeb2f683a3fced215a24598adebf1dddecdd36635933804bb`.

Фінальні rerun receipts: ROOT — `8f2c7aecc0fff97d283cb58bd8d7ef0a08377ee7cc331d8a6e54ca1816f5fd3a`; WT — `c95c5abb44875c38d8024445893b354ee120ddf43cc4296d7603256648ad934b`. Збережено ту саму одну PDO deprecation, 0 new failures/errors/skips та exit 0.

Native practice offline replay перевірив 36 tasks / 47 accepted variants / 60 options, retry/reset/окремі scores, 12 full inputs, 24 manual cases, 24 token cases, 24 delete + 24 rebuild, 12 mixed-mode cases та 42 незалежні semantic probes. Feedback keys перевіряються саме literal native `trim().toLowerCase()`, без вигаданої нормалізації апострофів. Було 0 network/SQL/file writes. Номінальні width probes не видаються за браузерний layout acceptance.

## Guarded writer і фактичне локальне застосування

Незалежний read-only review `M81ContentPatch`, `M81LocalTargetGuard` і command проти actual frozen source та успадкованих primitives — **PASS, blocking mismatch не виявлено**. Перевірено finite six-owner scope, source pins, actual SQL identity/levels, exclusive `fopen(x)` backup до першого запису, одну транзакцію, lock/replan, rollback і no-op. Сам цей review не робив DB/HTTP calls і не дозволяє пропустити fresh runtime proof.

Виконано fresh local-target proof → fresh preview → exact diff review → exclusive backup → transactional local apply за [versioned patch](../../database/content-patches/m81-transformations-common-mistakes.v1.json). Фактичні операції збігаються з reviewed планом:

- 48 updates: шість `Page.text` + шість UK subtitle bodies + 36 continuing bodies.
- Шість inserts: по одному новому `practice-set` на owner; 0 deletes.
- 12 із continuing rows додатково змінюють тільки погоджені type/heading — 24 fields у тих самих update operations.
- 54 distinct target rows загалом; 0 question-family writes, 0 EN/PL writes, 0 progress writes.

Результат apply: **48 updated / 6 inserted / 0 deleted**. Preview digest — `831171c958e261431f2f7296c2333324c232fbe854648cbf1471624a20de9518`. Exclusive backup raw SHA-256 — `afab148407cbffdbd876a1a5477c614a3f9643da905f510de3f61106b57c964d`; backup bytes точно дорівнюють reviewed preview bytes. Приватний backup не включається до репозиторію.

Новий read-only postconditions helper використовує незмінений accepted scanner primitive. Offline перед реальним запуском перевірено 12 exact substitutions і синтаксис composed PHP. Первинний working-DB запуск завершився PASS: усі 46 BEFORE projections реконструйовано з єдиним allowlist; 42 continuing UK / 84 незмінні EN/PL / шість нових practices; 0 question-family writes; усі старі рядки, timestamps, protected/progress projections, source pins і sync exact. Scanner сам має 0 writes / 0 HTTP. Його SQL/source результат не підміняє UI/SEO acceptance.

Primary postconditions receipt SHA-256 — `e0c00a4649e653aaee4712d3faa8b4841d3145186c7c4284eb380bced7f3df19`. Він підтвердив 326 unchanged visual files та 2 763 frozen source hashes у DB-BEFORE профілі. Це окремий набір від нового public immutable manifest на 2 856 paths; їхні назви й counts не змішуються.

Повторний preview має `state=after`, 0 updates / 0 inserts, digest `bdf4f3a8e89061f3b20c2f9013d814606a4e875617da33b90bfeb7f32f6cb8dd`; фактичний повтор apply — **0 updated / 0 inserted / 0 deleted**, нового backup не створено.

Тимчасовий proof route видалено: вихідні foreign route bytes та EOL відновлено точно, physical hashes незмінні. Окремий GET видаленого nonce endpoint дав **404**, без redirects і DB writes. Cleanup receipt SHA-256 — `1d0fc5bca5aca8595c472afce83154948e3e84a66e51a90e4bbb11a118a66a46`. Сам nonce, runtime identity та вміст proof не публікуються.

Після browser/HTTP acceptance виконано **строгий post-UI repeat v2 — PASS**: усі 46 AFTER fingerprints точно дорівнюють первинному v1; шість new practice rows, усі старі rows/timestamps, source lineage, backup і source sync незмінні. Повторний scanner — 0 writes / 0 HTTP. Receipt SHA-256 — `b50c88d6bf9ddbee2941f7ada9870ed510a3ebac08a7e316a8b76fce0a2e3ecb`.

## Реальні HTTP / SEO / navigation та старі consumers

Read-only HTTP/SSR acceptance на `http://gramlyze.loc` — **шість theory GET → 200**, 719 basic/detail learning fields звірено з frozen author projection, усі 44 navigation source items точні, 14/14 унікальних destinations → 200. Перевірено H1/title/canonical/robots/breadcrumb, stable LearningResource identity/schema, protected anchors, 12 погоджених metadata transitions та відсутність unsupported invisible source fields. Canonical URL збережено як значення метаданих; production-запитів не виконано.

Початкова спроба нового HTTP helper зупинилася **до HTTP** через відсутній `heading` у projected block. Виправлено тільки новий helper: omitted/NULL heading читається через `?? null`, відповідно до frozen contract. Контент, native view та historical assertions не змінювалися. Повторний прийнятий receipt SHA-256 — `d426b4af0aa36e8f507eb22d2f7f4c896187616a2a199045bb80a7e1ea6e1703`; 0 SQL access / 0 content writes / 0 runtime/route edits.

Registry acceptance — **181 targets / 0 failures / 0 writes**: усі 175 попередніх targets збережено й додано рівно шість M81. У semantic projection не виключено жодного owner/config/selected-question/payload field; лише volatile whole-response HTTP hash не є стабільною registry identity. Receipt SHA-256 — `a8d3207aa7584c7f4d2a09fa336d215afc7ca42d446a3d6ce0466e619ede1641`.

Old-consumer AFTER audit — **51/51 GET → 200**, 888 original questions і 13 saved tests/memberships, course boundaries, standalone SSR/JSON/compose та page-mixed перевірено проти fresh BEFORE. Усі 46 table fingerprints залишилися однаковими до/після цих GET. Receipt SHA-256 — `2eb4994dcb673c68b97cc5ab2460aa90dc1d5d3614e13cf0e9f7fead2b24db28`. Це read-only перевірка; старий UI і банки не пересівалися й не переписувалися.

## Фактичне browser acceptance

Усі шість реальних theory pages перевірено при **1440×1000, 390×844 і 320×844, light/dark**: 36 unique owner/viewport/theme states. Fonts завантажено, canonical typography/components збережено, потрібні basic/detail тексти та українські переклади видимі. Масштаб не змінювався; після завершення повернуто 1280×720 / light.

| Browser scope | Підтверджений результат |
| --- | --- |
| Власна практика | На кожній сторінці 2 form / 2 meaning / 2 full; 36 tasks. Усі 60 options пройдено через три form rounds і два meaning rounds. |
| Повні речення | 12 inputs × manual/token × desktop/mobile = 48 cases. Blank/wrong/correct, retry/reset і separate scores перевірено; додатково 15 paired alias rounds. |
| Токени | 24 deletion/rebuild cases і 24 mixed manual/token cases; після видалення потрібні токени знову доступні. |
| Вбудовані widgets | 30 фактично складених відповідей; кожен із шести widget показав 5/5, visible settled sentence звірено з exact selected native answer. |
| Meaningful details | A–D по одному: всі чотири відкрито/закрито; E/F — 0. Інший owner не відкривається разом. |
| Footer navigation | По одному реальному UI-переходу з кожного owner — шість; destination URL/H1 підтверджено. Решта items окремо покриті HTTP audit. |
| Console | 0 нових warning/error за проведене приймання. |

У навчальних блоках — **0 learning overflow**. Таблиці A–D на mobile мають локальний горизонтальний scroll для 576 px table content; він залишається всередині native table wrapper. Окремо зафіксовано **15–29 px overflow декоративної shell** на mobile. Це не названо нульовим page overflow і не «виправлялося» новим CSS поза content scope; навчальні блоки не виходять за свої межі.

Automation history збережено: початковий broad locator переплутав повторюваний I і answer token із bank token. Виконано native restart та bank-only scoped clicks, після чого всі п’ять відповідей widget прийнято. На B navigation timeout уже мав settled URL/H1, тому повторної навігації не робили. На D mobile перший evaluation click не показав feedback; окрема видима повторна перевірка дала 2/2 й зафіксована як остаточний manual case. Ці harness retries не приховано як безпомилковий перший прохід і не спричинили змін engine/content.

Browser receipt SHA-256 — `3a318c9913be94d6897d1aef87f179b3191c79cf970387d91eacf9aa14a663f8`. Screenshots і повні UI receipts приватні, не комітяться. Print, no-JS та повний WCAG audit не входили в проведене acceptance і не заявляються перевіреними.

## Віддача змін

Delivery dry allowlist має рівно **61 path: 15 modified** (WT attributes, вісім shared files, шість definitions) **+ 46 нових M81 files**. Приватних файлів чи ROOT foreign changes у ньому немає. WT `.gitattributes` отримує 20 рядків / 18 rules append зі збереженням усього старого raw prefix; стороннє видалення ROOT attributes не зачіпається.

Default `git diff --check` дав exit 2 лише на чотирьох доданих controller lines із збереженим CRLF: CR позначається як trailing whitespace. Це не привід нормалізувати controller або послаблювати raw-byte fixtures. Окремі per-invocation `git -c core.whitespace=cr-at-eol diff --check` та staged equivalent завершилися exit 0; незалежний скан справжніх trailing spaces/tabs у staged sources дав 0. Рівно 12 additions у shared diff переглянуто; старі visual/runtime/test файли поза allowlist не staged.

Локальне content/runtime/browser завдання завершено з явними baseline caveats вище. Перевірений scoped allowlist — 61 path, включно з цим звітом. Ця редакція передається через commit і normal push у `codex/seo-m81-transformations-common-mistakes-standard-view`; фінальний commit SHA та незалежна звірка remote SHA == HEAD наведені у фінальному повідомленні, щоб не вбудовувати self-referential SHA у власний commit. Backups, приватні діагностичні дані, screenshots, секрети та сторонні незавершені зміни виключено. `main`, PR, production та deploy не змінюються.
