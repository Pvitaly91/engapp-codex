# M92 — Future Perfect / Future Perfect Continuous: стандартна в’юха

Статус: навчальні джерела впроваджено локально; фактичні SQL, SSR/HEAD, UI та registry перевірки завершені. Звіт фіксує стан перед фінальним Git handoff; його full commit SHA і remote equality наведено у фінальній відповіді. Це не all-green звіт: історичний strict regression — FAIL, literal consumer comparisons мають збережені FAIL, document overflow оболонки також не позначено PASS.

Пакет: `m92-future-perfect-continuous-basics`, revision `1.0.0`. Робоча гілка: `codex/seo-m92-future-perfect-continuous-basics-standard-view`; accepted base: `30cc8cf98bedaea73d4a3255717a175d6cbfa161`. Робота й реальні HTTP/UI перевірки — тільки `http://gramlyze.loc`. Production `.com`/`.ub`, deploy і `main` не змінювалися.

## 1. Точні сторінки, власники й рівні

| Owner | Сторінка | Page / category | SQL level | Display / editorial focus | Старі components / practice source index (existing/new) / footer source index |
|---|---|---|---|---|---|
| A | [Future Perfect: Forms and Use](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-forms) | 366 / 13 | NULL | B1–B2 / B1 | 8 / 6 / 7 |
| B | [Future Perfect: Negatives](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-negatives) | 367 / 13 | NULL | B1–B2 / B1 | 8 / 6 / 7 |
| C | [Future Perfect: Questions and Short Answers](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-questions) | 368 / 13 | NULL | B1–B2 / B1 | 8 / 6 / 7 |
| D | [Future Perfect: Time Expressions](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-time-expressions) | 369 / 13 | NULL | B1–B2 / B1 | 7 / 5 / 6 |
| E | [Future Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/maibutni-formy/future-perfect-continuous/future-perfect-continuous-forms) | 68 / 14 | NULL | B2 / B2 | 7 / new7 / 6 |
| F | [Future Perfect Continuous: Negatives](http://gramlyze.loc/theory/maibutni-formy/future-perfect-continuous/future-perfect-continuous-negatives) | 69 / 14 | NULL | B2 / B2 | 7 / new7 / 6 |

Власники (повна source identity, а не пошук за назвою):

- A: `Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectFormsTheorySeeder`.
- B: `Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectNegativesTheorySeeder`.
- C: `Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectQuestionsTheorySeeder`.
- D: `Database\Seeders\Page_V3\FutureForms\FuturePerfect\FuturePerfectTimeExpressionsTheorySeeder`.
- E: `Database\Seeders\Page_V3\FutureForms\FuturePerfectContinuous\FuturePerfectContinuousFormsTheorySeeder`.
- F: `Database\Seeders\Page_V3\FutureForms\FuturePerfectContinuous\FuturePerfectContinuousNegativesTheorySeeder`.

A–D мають ancestry `maibutni-formy/future-perfect`, E/F — `maibutni-formy/future-perfect-continuous`. Збережено raw titles, англійські H1/leading strong, page/category/slug/seeder identity та фактичні NULL-рівні всіх continuing/subtitle/practice рядків. Display/focus — навчальні дескриптори; вони не перетворені на SQL-фільтр банку.

## 2. Source findings і змістові виправлення

| Owner | OLD → AFTER та причина | Нові справжні meaning-контролі |
|---|---|---|
| A | Completion-only заголовні пояснення суперечили наявним lived/worked + for моделям. Розрізнено готовий результат до майбутньої точки, накопичену тривалість і процес у точці; lived for ten years не вимагає переїзду, finished report не доводить sending/approval, built bridge — opening. | Фіксована ситуація березень/червень 2027 і десять років проживання без вимоги припинення; готовий звіт без неназваних надсилання/схвалення. |
| B | Заперечення результату не означає, що роботу не почали; не гарантує завершення одразу після дедлайну. All/everything/enough перекладено зі збереженням кількісної області заперечення, не як nothing. Нейтральний порядок not і повна форма мають явно обмежений контекст, не універсальні стильові заборони. | Вісім речей: шість спаковано, дві ні; покупка за 500 грн і прогнозовані заощадження 350 грн — insufficient, а не zero. |
| C | Питання не підтверджує свій результат. Для короткої відповіді явно задано мовця, референта, факт і критерій найкоротшої навчальної моделі; немає категоричної заборони Yes, she will have. у всіх інших ellipsis-контекстах. | Не підтверджене питання про завтрашній полудень; відома підготовка о 9:30 для межі 10:00 і найкоротше Yes, she will. |
| D | By — не пізніше межі, before — раніше, until — не взаємозамінна підстановка. By then має явний референт; already не додає неназваної дати. Майбутня підрядна часу відмежована від інших значень will. | Inclusive 18:00 з дозволеним завершенням рівно на межі; фіксовані 10/12/15 травня 2030 і then саме про зустріч. |
| E | Continuous не вимагає початку до моменту мовлення, безперервності щосекунди чи продовження після точки. Розрізнено activity/result і значення state/dynamic, а не заборонено слово have/think взагалі. Старе виправлення finished→working змінювало діяльність; тепер неправильне been worked on the task виправляється на been working on the task, а completed-result модель пояснено окремо. | Навчальна дата 1 червня, майбутній початок 1 липня і накопичені три місяці до 1 жовтня; щоденні півгодинні тренування з перервами без гарантії спортивного результату. |
| F | Negated duration не зведено автоматично до zero або less-than без фактів. Not all afternoon не дорівнює жодній хвилині діяльності й не доводить провалу плану. | 11:00→12:00 = одна година, не дві; заплановане готування 16:00–17:00 усередині явно визначеного afternoon 12:00–18:00. |

Усі навчальні EN-моделі мають повноцінні українські відповідники, включно з репліками питання/відповідь, wrong/right парами й поясненнями. П’ятнадцять mistake items мають paired EN/UA та видиму note. Короткі застереження/приклади/переклади залишені у visible basic. Для цих шести finite author projections змістовних окремих detail-поглиблень не залишено: 0 details — явне семантичне рішення, не runtime word-count і не загальна квота для інших уроків.

## 3. OLD → AFTER компоненти й завдання

Збережено всі 45 старих components, їхні types, columns, source indices, порядок та метадані. A/B/C: hero0, forms-grid1, usage-panels2, comparison-table3, mistakes-grid4, summary-list5, practice-set6, navigation-chips7. D: hero0, forms-grid1, usage-panels2, comparison-table3, summary-list4, practice-set5, navigation-chips6. E/F: hero0, forms-grid1, usage-panels2, comparison-table3, mistakes-grid4, summary-list5, navigation-chips6; єдина нова practice-set додана в source7. Нових незалежних візуальних компонентів немає.

| Owner | Hero / формули / usage / таблиця / mistakes / summary | Практика OLD → AFTER |
|---|---|---|
| A | Той самий component set; усунено result-only суперечність, duration/result/process пояснено в basic, V3/auxiliary помилки з перекладами. | selects0/1 та inputs0/1 retained; додано choices0/1 на meaning. |
| B | Той самий set; scope not/everything/enough, deadline і neutral-form критерії видимі; українські explanatory headers. | selects0/1 та inputs0/1 retained; додано choices0/1 на meaning. |
| C | Той самий set; пряме питання, wh, shortest-reply/speaker/fact, видимі межі формату. | selects0/1 та inputs0/1 retained; додано choices0/1 на meaning. |
| D | Той самий set без штучно доданої mistakes-grid; time reference, position/form, inclusive by/before/already. | selects0/1 та inputs0/1 retained; додано choices0/1 на meaning. |
| E | Уточнено start/viewpoint/result/process/repetition; семантично узгоджена work-on-task correction. | Старої власної practice-set не було: нові2form +2meaning +2full inputs у source7. |
| F | Scope negation і конкретні часові/кількісні факти у visible basic. | Старої власної practice-set не було: нові2form +2meaning +2full inputs у source7. |

Чисельність не змішує різні сутності: 16 retained own controls A–D (8 selects +8 full inputs) +20 new own controls =36 graded; з них12 full-sentence inputs. Окремо —30 existing bank questions у шести native widgets. Ungraded demos0. Старі task IDs або їхня відсутність, group/index/order, ключі відповідей та всі старі accepted variants збережені; жодному старому task не вигадано нову identity.

Для A–D linked_practice збережено exact source/types/bank/intro; єдина дозволена зміна config — title «Вправа 3. Побудуй речення» → «Вправа 4. Побудуй речення», бо перед ним тепер form/meaning/full-input групи. Це data-header normalization, не зміна native view чи нумераційного engine. Нові meaning tasks мають stable m92 IDs; label/prompt містять усі факти, потрібні для вибору.

Повні literal component/task OLD→AFTER, field reasons, option feedback, alias/token feasibility та semantic limits збережені у `docs/content/m92-future-perfect-continuous-basics.components.v1.json`, `.tasks.v1.json`, `.editorial.v1.json`, `.coverage.v1.json` та author parts. Цей звіт стисло показує їхню навчальну функцію; він не підміняє exact maps.

### Повні input-ключі та aliases

| Owner / input | Canonical answer | Додаткові declared aliases |
|---|---|---|
| A/0 | We will have completed the project by noon. | We'll have completed the project by noon. |
| A/1 | Maya will have prepared everything before the guests arrive. | Maya'll have prepared everything before the guests arrive. |
| B/0 | Leo won't have finished the report by Friday. | Leo will not have finished the report by Friday. |
| B/1 | They will not have arrived before the meeting starts. | They won't have arrived before the meeting starts. |
| C/0 | Will you have finished the report by noon? | — |
| C/1 | What will they have completed by Friday? | — |
| D/0 | We will have saved enough money by next summer. | We'll have saved enough money by next summer. |
| D/1 | By the time you arrive, the train will have left. | — |
| E/0 | By noon, I will have been studying for three hours. | By noon, I'll have been studying for three hours. |
| E/1 | We will have been practising every morning for six months by July. | We'll have been practising every morning for six months by July.<br>We will have been practicing every morning for six months by July.<br>We'll have been practicing every morning for six months by July. |
| F/0 | By noon, we will not have been waiting for two hours. | By noon, we won't have been waiting for two hours. |
| F/1 | She will not have been working here for a year by September. | She won't have been working here for a year by September. |

Отже, full inputs мають23 declared answers =12 canonical +11 additional aliases. Разом із24 ключами form/meaning controls це47 accepted entries; це не47 окремих запитань. У60 displayed options —24 correct і36 distractors. Maya’ll — явний перевірений spoken alias у даних, не обіцянка автоматичної підтримки довільного name+’ll. Practising/practicing — явні BrE/AmE aliases.

Full inputs мають shuffled групи по1–3 слова, повну композиційність canonical, ручне введення та token-click. Видимі українські умови фіксують потрібний порядок/лексему/часовий референт без прихованого англійського target у hint. Plain feedback пояснює правильні/неправильні опції й accepted aliases. Ready-made tokens перевіряють побудову моделі та порядок; це не незалежне спонтанне словотворення. Чинний normalizer не перетворено на строгий екзамен пунктуації/регістру/правопису.

## 4. Вбудовані конструктори: exact банки й30 settled answers

Кожна сторінка має рівно один caller-owned native widget наприкінці «Практики», по5 питань exact своєї теми, type4. На кожній сторінці actual level-filter NULL:72 eligible main-bank questions, не query за editorial B1/B2. Разом configured pool432; ці pool не замінені сторонніми legacy Basics банками. Жодного нового question/answer/option/hint row не додавали й старі не переписували. Відображувані українські condition clones містять потрібні критерії; оригінальні bank conditions/keys/tokens/options залишені незмінними.

### A — Future Perfect: Forms and Use

Exact bank: `Database\Seeders\V3\Polyglot\PolyglotFuturePerfectFormsAllLevelsLessonSeeder`.

| Question ID | Очікуване settled EN-речення |
|---|---|
| 27519 | By five I will have finished the report. |
| 27520 | Before dinner she will have sent the emails. |
| 27522 | By then they will have found the keys. |
| 27524 | By Friday we will have completed the project. |
| 27526 | By noon Marta will have filled in the form. |

### B — Future Perfect: Negatives

Exact bank: `Database\Seeders\V3\Polyglot\PolyglotFuturePerfectNegativesAllLevelsLessonSeeder`.

| Question ID | Очікуване settled EN-речення |
|---|---|
| 27591 | By five I will not have finished the report. |
| 27592 | By then she won't have gone home. |
| 27594 | By Friday they will not have signed the contract. |
| 27596 | By next month Marta will not have saved enough money. |
| 27602 | By evening we will not have found all the answers. |

### C — Future Perfect: Questions and Short Answers

Exact bank: `Database\Seeders\V3\Polyglot\PolyglotFuturePerfectQuestionsAllLevelsLessonSeeder`.

| Question ID | Очікуване settled EN-речення |
|---|---|
| 28095 | Will you have finished the report by five? |
| 28097 | What will she have done by then? |
| 28099 | Will Marta have left by the time we arrive? |
| 28103 | Will we have bought the tickets before the trip? |
| 28104 | How many pages will you have written by tomorrow? |

### D — Future Perfect: Time Expressions

Exact bank: `Database\Seeders\V3\Polyglot\PolyglotFuturePerfectTimeExpressionsAllLevelsLessonSeeder`.

| Question ID | Очікуване settled EN-речення |
|---|---|
| 28167 | By five I will have finished the report. |
| 28168 | By then she will have sent the emails. |
| 28173 | By the deadline the team will have checked the data. |
| 28175 | By midnight we will have cleaned the room. |
| 28177 | In two years she will have finished her course. |

### E — Future Perfect Continuous: Forms and Use

Exact bank: `Database\Seeders\V3\Polyglot\PolyglotFuturePerfectContinuousFormsAllLevelsLessonSeeder`.

| Question ID | Очікуване settled EN-речення |
|---|---|
| 28251 | By noon I will have been working for two hours. |
| 28252 | By Friday she will have been living here for a month. |
| 28254 | By next year we will have been working on this product for three years. |
| 28256 | By Monday Marta will have been training since June. |
| 28257 | By tonight they will have been testing the new system all day. |

### F — Future Perfect Continuous: Negatives

Exact bank: `Database\Seeders\V3\Polyglot\PolyglotFuturePerfectContinuousNegativesAllLevelsLessonSeeder`.

| Question ID | Очікуване settled EN-речення |
|---|---|
| 28323 | By noon I will not have been working for very long. |
| 28325 | By then they won't have been waiting for two hours. |
| 28327 | By six Tom won't have been studying for four hours. |
| 28329 | By tonight they won't have been testing it all day. |
| 28334 | By the end of the day the team won't have been discussing it long enough. |

Семантичні межі selection: A — completed result і concrete future-reference; lived-duration theory/own meaning не видається за окрему widget перевірку. B — not all/enough/negated completion із явними quantity/reference фактами. C — yes/no, object What, How many і future-time clause; shortest short reply/subject-who не заявлені як widget coverage. D — by/by then/deadline/in-two-years reference, не only V3; before залишається theory/own coverage, його немає в цих п’яти widget keys. E — future start, temporary living, regular work/training із перервами, since/all-day; немає гарантії result або подальшого продовження. F — short positive duration, not-two-hours і not-all/enough scopes лише за явними фактами. Це finite coverage, не доказ повного засвоєння всієї теми чи рівня.

Використано незмінний M89 opt-in `complete-loaded-verb-hints-v1`: full ordered hint+option snapshots не обрізаються. У цих30 selected фактично empty hint arrays; це підтверджена властивість selection, не вимкнення підтримки hints. Не-opt-in історичний contract і M89 helper byte-exact, включно з hash `4866bcda9ea8644bae5f46a30709d30b0838a775c6fa7dd07da8ea46d66d0664`. Synthetic mutation/boundary tests не підміняють реальну кількість loaded hints.

## 5. Первинні мовні джерела й межі доступу

Використано авторські пояснення British Council, Cambridge і BBC, не скопійовані publisher exercises/learner comments. Повний per-owner catalogue з claim/access status є у editorial artifact. Ключові перевірки:

| Джерело / URL | Фактичний доступ і роль |
|---|---|
| [British Council: Future continuous and future perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/future-continuous-future-perfect?page=2) | Direct editorial body прочитано; інші canonical/alias спроби timeout не названі прочитаними. Future viewpoint/result/process. |
| [Cambridge: Future perfect](https://dictionary.cambridge.org/grammar/british-grammar/future-perfect-) | Primary indexed editorial excerpt; direct403. Duration/look-back моделі; не твердження про доступ до blocked body. |
| [Cambridge: All](https://dictionary.cambridge.org/grammar/british-grammar/all), [Enough](https://dictionary.cambridge.org/dictionary/english/enough), [Not](https://dictionary.cambridge.org/dictionary/english/not) | Indexed primary editorial/dictionary evidence; All/Enough direct403. Quantifier/negation scope; не повне читання blocked сторінок. |
| [Cambridge: When](https://dictionary.cambridge.org/grammar/british-grammar/when) | Indexed primary editorial excerpt для future-time clause; без заяви direct-body доступу. |
| [British Council: Short forms](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/short-forms), [Ellipsis](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/ellipsis) | Direct editorial bodies; shortest teaching reply не перетворено на універсальну заборону інших ellipsis. |
| [British Council: Perfect aspect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/perfect-aspect), [Time/if clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/verbs-time-clauses-if-clauses) | Direct editorial bodies для aspect і підрядної часу. |
| [Cambridge: Wh-questions](https://dictionary.cambridge.org/grammar/british-grammar/questions-wh-questions%23questions-wh-questions__86), [By](https://dictionary.cambridge.org/grammar/british-grammar/by), [Until](https://dictionary.cambridge.org/uk/grammar/british-grammar/until) | Indexed primary excerpts; By direct blocked. Wh/temporal reference/межа значень. |
| [Cambridge: Before](https://dictionary.cambridge.org/dictionary/learner-english/before), [Already](https://dictionary.cambridge.org/dictionary/learner-english/already) | Indexed primary learner-dictionary entries. |
| [Cambridge: Future perfect continuous](https://dictionary.cambridge.org/us/grammar/british-grammar/future-perfect-continuous-i-will-have-been-working-here-ten-years.) | Indexed primary editorial excerpt; окремий direct403 body не прочитано. |
| [BBC: Future perfect continuous PDF](https://downloads.bbc.co.uk/learningenglish/intermediate/unit30/b2_u30_6min_grammar_future_perfect_continuous.pdf) | Direct primary PDF editorial body. |
| [BBC: Tenses with Georgie, 21 June 2024](https://www.bbc.com/learningenglish/english/features/tenses_with_georgie/ep-240621) | Cached primary editorial body, не fresh direct-origin claim. |
| [British Council: Stative verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/stative-verbs), [Cambridge: Practise](https://dictionary.cambridge.org/us/dictionary/english/practise) | BC direct editorial body; Cambridge indexed dictionary entry. Значення state/dynamic та BrE/AmE spelling alias. |

Cambridge Ellipsis direct blocked і BBC Future Perfect 14June robots-blocked залишені в catalogue як unread/non-evidence, без нормативного claim на їхній підставі. З source прикладів не виводили автоматичне припинення стану/діяльності, гарантований результат, later completion чи безперервність щосекунди.

## 6. Metadata, navigation, downstream teasers

Збережено усі шість raw titles/H1, canonical identity і caller-owned theory boundary. Page.text змінено лише для A/E/F — IDs366/68/69, разом із відповідними subtitle_html для coherence. B/C/D Page.text і subtitle rows literal unchanged. A HTML/plain передають той самий зміст, але не оголошуються буквально однаковими після strip_tags; E/F явні reviewed descriptors. Жоден generic SEO resolver не змінений.

Footer review охоплює24 entries: labels/order/current flags усі збережені. A–D16 href untouched; E/F8 записів виправлено у даних після actual proof: чотири unique short paths давали404, чотири ancestry-canonical destinations —200. Не додано alias route, redirect, generic resolver fix або посилань «#».

Дві category main teaser області закономірно відображають3 схвалені Page.text переходи: `/theory/future-perfect` (category13→366) і `/theory/future-perfect-continuous` (category14→68/69). Їхні raw category descriptions не змінені. Category DOM не оголошується цілком незмінним: чотири text/outerHTML difference перевірені exact підстановкою тільки BEFORE-bound дозволених descriptors; два outer_html_sha256 differences залишаються strict FAIL.

Фактичний metadata аудит: шість200 GET bodies з незалежного postconditions і шість окремих HEAD requests;30 widget questions,36 own controls,15 visible mistake notes,24footer entries. GET bodies не підмінені preview. Canonical `.com` origin перевірявся лише як metadata string у локальній відповіді, production не запитувався.

## 7. Локальний apply, exact diff і збереження БД

Fresh independent BEFORE:12 target theory/course HTTP200 і46 tables unchanged навколо читань. Перевірено actual physical local target, fresh CLI/web proof, exact preview та незалежний review; exclusive backup створено перед transactional apply. Тимчасовий read-only nonce route мав лише локальний proof scope; після apply/no-op route file відновлено byte-exact і окремий GET повернув404. Nonce/route body/runtime identity/DB credentials у звіт не експортуються.

| Операція / сутність | Фактичний результат |
|---|---|
| Existing text_block.body updates |48 =45 continuing component bodies +3 subtitle bodies. |
| Page.text updates |3, тільки IDs366/68/69. |
| UPDATE operations / distinct existing rows |51 /51:48 text_blocks +3 pages. |
| INSERT operations / distinct new rows |2 /2:тільки own practice text_blocks E/F. |
| Усього distinct changed rows |53, не51 і не кількість source positions. |
| Question/answer/option/hint/variant/progress writes; deletes |0. |
| Два repeated apply calls / no-op |Обидва0 updates /0 inserts;0 повторних writes. |
| Independent initial postconditions |46tables;51 continuing UK rows і102 EN/PL rows перевірені;2 new practice rows тільки за exact allowlist. |
| Independent post-UI repeat |Ті самі46 AFTER fingerprints/source lineage exact після всіх UI actions;0 DB writes. |

«All old rows and timestamps exact» означає exact reconstruction з урахуванням дозволених51 updates/2 inserts: усі старі рядки/поля поза exact allowlist та timestamps збережені. Це не твердження, що схвалені teaching bodies залишились literal unchanged. Жодного стороннього metadata rewrite немає. Збережено всі14 old banks/936 question rows та повні relations/options/hints,14 saved pools/membership/order, поза-scope progress і EN/PL. Окремі EN/PL HTTP GET не заявляються: їхній доказ тут SQL/source preservation.

Final post-UI audit виконав додатковий цільовий single pass6 GET з перевіркою46 tables — окремий AFTER-UI purpose, не reroll consumer regression. Перша sandbox-спроба post-UI audit не пройшла Windows CIM/listener-vhost inspector; її збережено як environment failure. Незмінний helper під дозволеним local inspection завершився PASS; guard не послаблювали.

## 8. Source boundaries, native дизайн і тестові результати

Контент працює у погодженій native в’юсі. Збережені кольори/сірі cards/colored labels/frames/типографіка/відступи/табличний локальний scroll, існуючі компонентні types і caller-owned межа.0 visual-source змін серед326 captured files.0 generic validator/formatter/answer-variants/grader/design-rule змін. Старі releases, maps, registries/contracts і3045 historical frozen files byte-exact. M89 hint snapshot helper reused byte-exact.

Вісім shared files отримали рівно12 finite additive registration lines/context; це дозволена dispatch wiring, не новий renderer algorithm. ROOT foreign-controller/manifest differences та EOL збережені незалежно для ROOT/WT; in-memory reversal12 тільки M92 lines відтворює exact BEFORE.28 bank-source paths/56 contexts незмінні. WT-only `.gitattributes` — exact old prefix +39 explicit M92 `text eol=lf` rules; ROOT `.gitattributes` залишається absent. Інші сторонні незавершені зміни не копіювались у delivery.

| Перевірка | Реальне виконання / статус | Межа доказу |
|---|---|---|
| M92 targeted ROOT source |55 methods,22261 assertions,0F/0E/0skip,exit0;1 deprecation. |ROOT application, isolated SQLite-memory preflight; working .env/DB/HTTP не використовуються. |
| M92 targeted WT source |55 methods,22261 assertions,0F/0E/0skip,exit0;1 deprecation. |Також ROOT application/composer bootstrap з WT factory/test source; не повний WT application/HTTP acceptance. |
| Native own factory replay |Обидва source contexts:36 controls,12 full inputs,47 accepted,60 options,18 group scores,16 legacy controls preserved;48 nominal +negative contexts. |Offline actual native factory, не браузерний substitute. |
| Existing widget native replay |30 selected у кожному source context strict settled/literal/native cases PASS. |Exact native bank/loaded hints; не доказ real widget visibility. |
| Materialization/source-sync |93 contexts/47 paths; source-sync43paths,326visual/3045frozen unchanged; independent FS audit PASS. |Pure source/filesystem, не DB/UI acceptance. |
| Target registry |6/6,failed0,exact source/IDs/type4/pools/widget bindings. |Read-only registry/GET audit, не interaction score. |
| Whole registry |247/247 =241 old +6 M92;failed0,46tables unchanged,0 writes/retries. |Old population preserved; не «всі старі historical tests PASS». |
| Actual target SSR/HEAD |6 GET200 +6 HEAD200; exact prospective metadata,30widget/36own/15notes. |Actual served local page; runtime grading окремо нижче. |

Цільові тести охоплюють package/bindings/runtime/patch/localguard/sourceboundary/nativepractice/hintboundary (8 PHP suites,55 methods) і literal native CJS probe. Обидва target процеси залишили наявну PHP8.5 deprecation; її не відфільтровано.675 protected runner inputs unchanged у кожному isolated run.

### Historical22-suite strict regression — FAIL, не waived

| Метрика | Fresh BEFORE | Actual AFTER v1 |
|---|---:|---:|
| Profiles / method executions / unique methods / overlap |22 /1191 /1098 /93 |22 /1191 /1098 /93 |
| Assertions |264322 |263834 |
| Failure executions / error executions |74 /1 |78 /1 |
| Unique failing methods / unique error |66 /1 |70 /1 |
| Skipped / warning / deprecation events |0 /1 /22 |0 /1 /22 |

Strict comparator exit1 збережено. Чотири newly nonpassing методи M91SourceBoundaryTest:

- `test_attributes_keep_root_absent_and_worktree_exact_prefix_or_explicit_reviewed_suffix`;
- `test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile`;
- `test_m89_complete_hint_snapshot_helper_is_reused_byte_exact_without_new_delta_authority`;
- `test_native_validator_formatter_and_grading_engine_keep_their_independent_raw_bytes`.

Усі чотири фактично зупинились на старому exact controller-byte-profile gate («profile matches=0») до пізніших learner/native assertions. Незалежний source attribution показує рівно12 нових M92 additive registrations/8files і окремий WT attributes suffix; native primitives лишилися exact. Це пояснює observed failing assertion, але не виконує пропущені гілки і не змінює FAIL на PASS.

Також literal повідомлення чотирьох already-failing M73/M74/M77 source guards змінились через current raw SHA; не нормалізовані. Два still-passing dynamic registry методи мають закономірні assertion-count збільшення, але загальний delta−488 збережено повністю. Existing error `SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` — той самий SQLite missing `question_hints` table error. Generic warning «No tests found in class Tests\Feature\SavedTestStepWrongAnswerTest» лишається nonzero warning-only process, не PASS. Старі tests/parser/runner не редагувались; повторів для отримання green не було.

### Literal downstream consumers — strict FAIL збережені

- 54 old consumers:21 повний entry literal exact,33 strict FAIL; усі33 changed leaves — лише `$/body_sha256`. Фактично порівняно47 parsed keyed payloads і33 непорожні HTML-facts snapshots (27SSR +6course), усі exact; status/route/kind/count збережені;0 changed payloads. Шість course learning DOM snapshots exact. Причина raw-body inequality не доведена й не названа просто random/session effect.
- Відомий BEFORE route collision `/test/future-perfect/questions`: requested SSR, actual JSON route `test.js.questions`, handler fetchSavedTestJsQuestions,404. Requested-kind acceptance=false збережено; це не SSR PASS і route не виправляли.
- 31 extra controls:0 full-entry exact,31 strict FAIL. Bookkeeping/raw hashes та відмінності повністю збережено. Три непорожні category-description DOM snapshots (`/theory/maibutni-formy`, `/theory/future-perfect`, `/theory/future-perfect-continuous`) literal exact; у решті28 це empty fields, не28 додаткових category-description перевірок. Only two approved category main teaser paths змінені як описано в metadata розділі. Чотири text/outerHTML відмінності corroborated exact дозволеними Page.text substitutions; два outer_html_sha256 leaves не waived. Unknown main paths0.
- Inspector/corroborator — завершені pure diagnostics, а не success waiver. Variant-membership cause-proof не заявляється: changed payload rows0. Усі вихідні full-entry failures, key order, presence та raw SHA збережені; rerolls/normalization/old-comparator edits0.

## 9. Фактичне UI-приймання

Усі6 сторінок перевірені у реальному локальному браузері при1440/390/320, light/dark:36 unique states. У кожному стані фактично завантажений Manrope, bad images0,3 own groups +1 widget, learning cards fit. Це не довідка з fixture/SSR замість браузера.

| Взаємодія | Фактична кількість / результат |
|---|---|
| Full-input nominal manual/token |48 =12inputs ×2modes ×desktop/mobile; правильні answers прийняті. |
| Displayed option checks |60 =24correct +36distractors. |
| Additional alias checks |11 explicit extra aliases, включно Maya’ll і practising/practicing. |
| Edge probes |66; wrong-context/partial/reset/recheck/availability cases;content-grading failures0. |
| Native widget interaction |30 unique selected IDs,усі30 settled перевірено;6 widgets фактично5/5. Це окремо від36 own controls. |
| Native table scroll |Усі6 at320dark:scrollLeft0→324→0,права колонка доступна; не заявлено всі widths/themes одразу. |
| Point details |N/A:authored details0. |

Наявна межа:22/24 вузьких states мають document-level overflow; BEFORE також22/24, але це не тотожність конкретних ширин і не глобальний no-overflow PASS. Збережено перший C390dark documentWidth1343 (BEFORE395), окремий diagnostic1377 і пізніше395, без заміни початкового результату. Read-only diagnosis вказав absolute spans у native `shell-random-shapes` та незмінний180ms resize debounce; CSS/JS не ремонтували. Learning-card bounds fit не підміняє document-overflow acceptance. Широкі tables скроляться локально, не показують усі колонки на вузькому екрані одночасно.

Zoom не змінювали, global storage/progress не очищували. Print, no-JS і WCAG — `not_checked`, не PASS.36-state records — фактичні DOM/font/bounds observations, не36 screenshots; вони не самостійний доказ5/5. Completion і30settled мають окремі реальні interaction records; фінальний screenshot шостого widget показує own2/2 іwidget5/5.

## 10. Основні evidence pins і privacy

Нижче лише opaque hashes завершених receipts; самі runtime/SQL/proof/backup/browser files приватні й не входять у commit. Публічні навчальні Page/question IDs та source maps належать content contract, але сирі SQL rows/timestamps/runtime identities не експортуються.

| Evidence | SHA-256 |
|---|---|
| Fresh independent BEFORE | `86f2f244d5cbb3854ff452fb427e59cd88d64754730d0d228910de80a38bf65c` |
| Independent source BEFORE | `7f648e3368c79650404721c3cd8f6ee2c1035d0e48dc10347130c72750fa2a1e` |
| Reviewed frozen proposal | `3b82960fb19f29e56e4af3ad38238a880b85577b0219d485bd2fd1d4a7f6d5e2` |
| Source materialization | `4ea8a50b68fe99a641798a03c18638892c7db30c157fb82908740ae6b9719cca` |
| Source-sync | `180df021343f072b7fe46181227fc653021aaf845535d1d5c42d116d83bd8f56` |
| Independent materialized FS audit | `9e8ea429bb910a07e9480acb1481c7ef2c16a6f7b0d3ed020e1aebf83e3c14e1` |
| Preview exact review | `a96fa91c316ef8efa4457ce7a2633363b80766406052e89436d39f0854d096da` |
| Initial final SQL/GET postconditions | `73247aa004e8e233b53782f14d2a7a79a49aa12927eb6555487ff9d79dfac016` |
| Post-UI SQL/source/GET repeat | `a41eaa075283793d2df775d93d2578fc4c6080b4c5c3a7e9edce3fce1526c8af` |
| Nonce cleanup /404 | `4bd1658fc34c00eec099988d91ff94e3d585f721e4c64b750c92c57c983c5c9f` |
| Actual metadata/HEAD | `1f3f7bdb316cdeb7adc8d90d0dbb504bd4a0f32aba67386126eac7d9673079bd` |
| Target ROOT test receipt | `ca8ce3ea0eaba79081b72fec55aba7263a29614b4d196591aa6cd7904beae09e` |
| Target WT-source test receipt | `49380a96ac3ecd154f59e0e56f80a5125af57c1ccb16340298e065a4e6ce9bde` |
| Historical strict comparison | `82535a638156fa68b9f93d813f07afede06e2313ae132aabb7b125bbb0d24221` |
| Historical source attribution | `c5c567daaebd3954eefb899d0a8f19719ce976bdebc4e78044602c0e566722c8` |
| Consumer complete-entry inspection | `e78a62c3fa61abf556cf463729b9535c00f4652e88cd346234e856b733deccb5` |
| Approved teaser corroboration | `d17ac62ca45b8c955f7e05c1e5ca4403ca6a388c3121f0bea93833cdf9de2b9d` |
| Target +global registry run | `58acce71b5a2ce317bb26e11ff47349ab1ab84018ab9242bc8164f7654937554` |
| Actual browser acceptance | `2460ee2577784a7af9a93dbe6c0e244790a84a0dbc8fb0bf1a6d2c31bf37a28c` |

## 11. Scoped delivery

- Report path: `docs/reports/seo-m92-future-perfect-continuous-basics-standard-view.md`; accepted base і робоча гілка зазначені на початку. Full commit SHA та незалежна перевірка remote equality містяться у фінальному handoff, не як self-referential hash усередині report.
- Фактично staged рівно48 дозволених paths:47 materialized source/integration paths і цей report.25 сторонніх untracked files виключено; initial index був порожнім.12 additive shared lines переглянуто окремо, frozen teaching/test bytes звіряються з reviewed exact proposals, не приймаються лише за назвою файла.
- Staged path/privacy review виключив `.env`, secrets, SQL dumps/backups, caches, приватні diagnostics/proofs/receipts/screenshots, старі тести та сторонні dirty changes. Збіги RFC1918-адрес у targeted local-guard tests — перевірені негативні fixtures відмови, не exported runtime identity чи доступи.
- Перший cached whitespace check також знайшов зайвий blank EOF цього нового report; його виправлено. Після виправлення literal `git diff --cached --check` exit1 лише через4 нові controller lines552/611/1493/1549 з історично збереженим CR-at-EOL. EOL-aware `git -c core.whitespace=cr-at-eol diff --cached --check` exit0. Unstaged precheck мав той самий4CR/aware0 результат. `.gitattributes` попереджає про майбутню LF→CRLF Git conversion; captured raw mixed-prefix/shared bytes не нормалізували заради green.
- Після цього звіту виконуються final independent filesystem/index gates, scoped commit, normal push тільки M92 branch та remote SHA comparison. Їхні фактичні кінцеві результати зафіксовано у фінальному handoff. Main merge, PR, workflow dispatch і deploy не входять у scope.

Підсумок меж приймання: local content/native own practice/шістка5-question widgets/цільові тести/registry/SQL preservation перевірені фактично; historical strict suite та literal consumers не all-green, document-shell overflow retained. Print/no-JS/WCAG не перевірені. Production не використовувався.
