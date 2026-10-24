# M84 — Word Order і Have got / has got: стандартна в’юха

## 1. Статус і scope

Локальний apply і actual browser acceptance завершено: 70 цільових updates, один новий practice-set Have got / has got та 25 нових тематичних питань конструктора. Два повторні no-op — 0 writes. Незалежна реконструкція всіх 46 SQL-таблиць і її post-UI повтор підтвердили погоджений scope. Browser consolidation містить 36 states, 68 input cases та 6 widgets 5/5. Перевірені зміни закомічено й доставлено normal push у робочу codex-гілку; remote SHA content-коміту незалежно підтверджено. Нижче не приховано старі test failures, random-variant HTTP limitations і невиконаний WT runtime repeat.

Accepted base: `91c718a119a6d42f180c2a7121fbbbf94ef5afa4`. Робоча гілка: `codex/seo-m84-word-order-have-got-standard-view`. Реалізація й HTTP/UI — лише `http://gramlyze.loc`. Production-профіль перевірявся тільки ізольованим локальним тестом; запитів до production або gramlyze.ub не було.

Scope — шість точних UK theory owners, їхні канонічні definitions, навчальні дані, власна практика й тематичні widgets. Курси, окремі saved/mixed tests, їхні банки, memberships, grader, формати відповідей і progress не модернізуються. Існуючі сторонні зміни ROOT не привласнюються M84.

Дизайн не змінено: ті самі native-компоненти, кольори, рамки, приклади, заголовки, типографіка та відступи. 326 visual files і 2858 frozen files збережено; окремих M84 views/CSS/JS engine немає. У восьми shared files — лише 12 additive nonvisual registrations для шести owners, без removals. Незалежний paired source BEFORE охоплює 3390 шляхів ROOT/WT; finite reverse-hunk перевірки зберігають окремі RAW/LF/EOL-профілі та попередні foreign differences. Остаточний staged/delivery review залишається окремим gate.

## 2. Сторінки, заголовки й рівні

| № | Перевірена сторінка / збережений H1 | SQL-рівень | Авторський фокус / display | Native-блоки BEFORE → AFTER |
| --- | --- | --- | --- | --- |
| A | [Basic Word Order — Базовий порядок слів](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | NULL | A1 / A1–A2 | 10 → 10 |
| B | [Word Order in Questions and Negatives — Порядок слів у питаннях і запереченнях](http://gramlyze.loc/theory/basic-grammar/word-order/theory-word-order-questions-negatives) | NULL | A1 / A1–A2 | 10 → 10 |
| C | [Word Order with Adverbs and Adverbials — Прислівники й обставини](http://gramlyze.loc/theory/basic-grammar/word-order/theory-word-order-adverbs-adverbials) | NULL | A2 / A2–B1 | 12 → 12 |
| D | [Word Order with Verbs and Objects — Дієслова й додатки](http://gramlyze.loc/theory/basic-grammar/word-order/theory-word-order-verbs-objects) | NULL | A2 / A2–B1 | 10 → 10 |
| E | [Advanced Word Order / Emphasis — Інверсія й акцент](http://gramlyze.loc/theory/basic-grammar/word-order/theory-advanced-word-order-emphasis) | NULL | B2 / B1–B2 | 11 → 11 |
| F | [Have got / has got](http://gramlyze.loc/theory/basic-grammar/have-got-has-got) | NULL | A1 / A1 | 5 → 6 |

Усі шість SQL-рівнів справді NULL. Фокус і display не підміняють NULL на A1/A2/B2 і не звужують банківський фільтр. Raw titles, двомовні H1 A–E, slug, owner, ancestry, UUID continuing-блоків, їхні type/column/sort/level збережено. F — саме BasicGrammarHaveGotHasGotTheorySeeder, не інший lesson/course owner. E — AdvancedWordOrderEmphasisTheorySeeder, не accepted Advanced Fronting and Emphasis.

У шести mixed-сторінок HTTP 200; це read-only render/payload acceptance, не проходження mixed-тестів:

| Тема | Mixed URL |
| --- | --- |
| A | [Basic Word Order — mixed](http://gramlyze.loc/test/word-order/theory-basic-word-order) |
| B | [Questions and Negatives — mixed](http://gramlyze.loc/test/word-order/theory-word-order-questions-negatives) |
| C | [Adverbs and Adverbials — mixed](http://gramlyze.loc/test/word-order/theory-word-order-adverbs-adverbials) |
| D | [Verbs and Objects — mixed](http://gramlyze.loc/test/word-order/theory-word-order-verbs-objects) |
| E | [Advanced Word Order / Emphasis — mixed](http://gramlyze.loc/test/word-order/theory-advanced-word-order-emphasis) |
| F | [Have got / has got — mixed](http://gramlyze.loc/test/basic-grammar/have-got-has-got) |

Збереження їхніх старих payloads має явне random-variant обмеження в §8; «усі mixed outputs exact» не заявляється.

## 3. Авторська редакція й native component map

Повна [component map](../content/m84-word-order-have-got.components.v1.json) фіксує всі 59 AFTER-блоків, old/new identity, type/column/level, UUID і body hashes: 6 hero, 6 forms-grid, 19 usage-panels, 6 comparison-table, 5 mistakes-grid, 6 summary-list, 6 practice-set і 5 navigation-chips. З них 58 continuing slots; новий тільки F practice. Поза hero/practice/navigation є 42 content sections: 38 у A–E та 4 у F.

У всіх шести уроках семантично обрано **0 point-level «Докладніше»**. Короткі правила, переклади, межі моделі та застереження залишено visible basic; це не глобальний word-count threshold і не приховане скорочення навчальної функції.

| Урок | Основні виправлення | Явна межа покриття |
| --- | --- | --- |
| A | SVO не оголошено єдиною схемою кожного речення; відділено object від необов’язкових обставин і complement. Початковий час не названо загалом помилковим; конкретні завдання мають видимий нейтральний order criterion. Табличні UK поля — справжні переклади. | Не автоматичний розбір усіх членів речення або всіх можливих позицій обставин. |
| B | Розмежовано lexical do-support, be, modal і auxiliary have; перед підметом переміщується потрібне перше auxiliary. Have you finished не названо lexical-have моделлю. Subject wh-question має коректну межу; declarative checking questions не заборонено глобально. | Не повний курс negative/embedded questions чи інтонації; вимоги конкретної нейтральної моделі видимі. |
| C | Розмежовано adverb і adverbial, frequency біля lexical verb/be/auxiliary, manner після object і звичний, не універсальний порядок обставин. Only має явний scope. Not always заперечує «кожного разу», не означає never. | Не доведено всі допустимі позиції кожного adverb, spoken stress або окреме оцінювання коми. |
| D | Object потрібен не кожному verb. Збережено конкретні дієслівні моделі та учасників; to/for не довільні заміни. Separable particle відділено від prepositional verb, noun/pronoun order пояснено окремо. Modal + base form не змінює решту факту. | Не всі double-object alternations, всі phrasal/prepositional verbs або всі значення get/put. |
| E | Коректне What I need is to rest не позначено граматично хибним. Who/that для особи обидва можливі. Початкове Not only обставинне виділення відділено від Not only … but also як subject. Emphatic do/does/did, cleft/fronting і inversion мають власні критерії. | Не тест spoken emphasis/intonation; дані не перетворюють усі способи акцентування на одну обов’язкову конструкцію. |
| F | Have/has got пояснено для теперішньої наявності, родини й характеристик; have/get meanings і lexical-have модель не змішано. Короткі відповіді мають заданих мовця/адресата. Teacher не визначає стать англійського займенника. | Не повний курс have/get, минулого або обов’язку; A1 course policy не підміняється редакцією theory. |

Для E stimulus узгоджено з уже наявними фактами target у emphatic transformations; прихованої нової події чи нового object у відповіді не вимагається. В усіх inputs/rephrase правила нормалізації чинного engine лишаються тими самими: не заявляється окреме оцінювання punctuation, capitalization або просодії.

Первинні редакційні опори наведено у master та author parts; серед них [British Council: clause patterns](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/clause-structure-verb-patterns), [question forms](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/question-forms), [adverbial position](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/where-adverbials-go-sentence), [phrasal-verb word order](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/word-order-phrasal-verbs), [Cambridge: clefts](https://dictionary.cambridge.org/grammar/british-grammar/cleft-sentences) і [have got / have](https://dictionary.cambridge.org/grammar/british-grammar/have-got-and-). Це редакційні джерела, не додаткові змінені сторінки сайту.

## 4. Власна практика: OLD → AFTER

[Exact task ledger](../content/m84-word-order-have-got.tasks.v1.json) зберігає кожну стару позицію, її learning function, old/after group/index, фактичні id/source_index та їхню відсутність. У A–E всі 40 старих graded positions і 5 demos збережені; додано по 2 meaningful choices. F отримав 6 нових graded controls. Разом — **56 graded**, **5 окремих ungraded demos**, **17 повних sentence inputs**. Widget questions рахуються окремо.

| Урок | Старі → AFTER graded | Нові graded | Selects | Meaning choices | Full inputs | Graded rephrase | Demos |
| --- | --- | --- | --- | --- | --- | --- | --- |
| A | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 |
| B | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 |
| C | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 |
| D | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 |
| E | 8 → 10 | 2 | 3 | 2 | 3 | 2 | 1 |
| F | 0 → 6 | 6 | 2 | 2 | 2 | 0 | 0 |
| Разом | 40 → 56 | 16 | 17 | 12 | 17 | 10 | 5 |

Нові task IDs належать лише новим controls. Старі відсутні IDs не вигадані заднім числом; demos не отримують answer/score. Наявні correctors/rephrase не замінено демонстраціями.

У всіх 17 inputs — повна українська умова, задані факти й видимий criterion, чистий shuffled bank із групами по 1–3 слова, accepted keys/aliases і plain feedback. Manual, token, delete-return, mixed editing та reset використовують чинний native engine. Прийняття ручного who/that, initial-time або contracted alias не означає новий автоматичний synonym-token matcher.

[Coverage artifact](../content/m84-word-order-have-got.coverage.v1.json) має 31 finite row: A5/B6/C5/D4/E6/F5. Кожна прив’язує topic до реального content block, graded control/demo та конкретних widget IDs або прямо вказує theory-only / own-only / widget-only межу. «П’ять питань» не видається за вичерпне покриття уроку, усього курсу чи CEFR certification.

## 5. Вбудовані sentence builders: 25 нових + 5 повторно використаних

Старі Word Order type-4 банки A–E містили фрагменти відповіді, а не повні речення. Їх не переписували: створено рівно п’ять окремих insert-only supplement banks по 5 повних речень. Для F використано точний all-levels HaveGotHasGot банк, не 24-question A1 course bank.

| Урок | Editorial selection | Нові IDs / source UUIDs | Eligible pool після apply | Вибрано |
| --- | --- | --- | --- | --- |
| A | A1 | 59731–59735; m84-bwo-a1-01…05 | 72 старі + 5 нових = 77 | 5 нових |
| B | A1 | 59736–59740; m84-woqn-a1-01…05 | 77 | 5 нових |
| C | A2 | 59741–59745; m84-woaa-a2-01…05 | 77 | 5 нових |
| D | A2 | 59746–59750; m84-wovo-a2-01…05 | 77 | 5 нових |
| E | B2 | 59751–59755; m84-woadv-b2-01…05 | 77 | 5 нових |
| F | A1 | 47079–47083; bg-hg-poly-a1-01…05 | 72 старі, без insert | 5 existing |

Усі шість фактичних technical filters залишаються NULL: 77/77/77/77/77/72 — повні eligibility pools, не штучно звужені до editorial focus. Finite selection/exclusions прив’язані до exact owner/category/page/practice body/UUID/sort/locale та snapshot hashes. Тільки canonical-theory clones отримують локалізовану condition presentation; оригінальні models, relation snapshots, courses та standalone callers не змінюються. Відповіді/токени, casing і grader — штатні.

Для F native formatter приводить початкові Have/you до правильного видимого question case без редагування shared option rows. Усі 30 selected sentences replay-нуті автоматично; actual UI completion 6 × 5/5 і читання 30 settled sentences мають окремі browser receipts.

Повний source/audit: [supplement questions](../content/m84-question-bank-supplement.v1.json), [frozen bank audit](../content/m84-sentence-builder-bank-audit.v1.json), [builder contract](../content/theory-sentence-builders/m84-word-order-have-got.v1.json). Старі банки, їхні saved tests, links, питання/відповіді/options/hints/variants і прогрес не оновлювалися; 25 нових питань не додано до старих saved-test memberships.

## 6. Local apply, postconditions і cleanup

Завершено fresh physical/runtime proof → fresh preview → exact diff review → exclusive backup → transactional apply → postconditions → no-op ×2. HTTP/startup/scheduler apply hooks не додано.

70 updates складаються з 64 text-block body updates (6 subtitles, 53 teaching, 5 continuing practices) та 6 page text updates. Новий F practice — один insert. Question insert families: 25 questions + 51 новий question_option + 99 answers + 169 option pivots + 25 theory links = **369 нових rows**, окремо від нового practice row. Немає deletes або edits старих question families.

Незалежний final read-only audit підтвердив 64 continuing UK rows, exact збереження 128 EN/PL rows, усіх решти старих rows/timestamps, старих saved memberships, прогресу та інших таблиць. SQL AFTER усіх 46 таблиць точно реконструюється з BEFORE лише погодженими змінами. Цей audit сам має 0 DB writes.

У п’яти Word Order footers додано 20 точних URL (по 4 destination links, 5 unique destinations). Labels/order/current flags не змінені; router/resolver не переписано. Для F навігаційного блока не вигадано. Read-only navigation audit перевірив binding усіх 20 entries та 5 destinations, зберігши 46 таблиць.

Тимчасовий proof route видалено. Routes file відновлено exact: before/after SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Окремий GET видаленого nonce endpoint — 404, redirects 0, physical configuration unchanged. DB password/APP_KEY/cookies/tokens не публікувалися; Apache/XAMPP/hosts не змінювалися.

Teaching package — 1.0.0. Active checksum/owned-source fixture metadata 1.0.1 виправляє evidence profiles; це не друга навчальна редакція або додатковий DB apply. Історичні v1 artifacts збережено, не переписано.

Final read-only v3 повторно підтвердив post-UI exact стан усіх 46 таблиць і full-raw prefix збереження WT .gitattributes при погодженому append; ROOT .gitattributes лишається відсутнім. Це delivery metadata, не зміна навчального пакета або дизайну. Scoped sentence-builder registry: 6 targets PASS. Загальний actual registry: **199 targets / 0 failed / 0 writes / command exit0** — exact union 193 попередніх semantic rows і 6 M84. Старі semantic fields exact, крім volatile http_sha256: 0 старих whole-HTML hashes equal, 193 different; HTML byte equality не заявляється. Усі 457 snapshots/links і 30 condition-only clones пройшли, усі 46 SQL fingerprints exact до/після.

## 7. Автоматичні перевірки

ROOT V5: **63 unique methods, 11393 assertions, F0/E0/skipped0, warnings0, PHPUnit notices0, PHPUnit exit0 / runner exit0**. Є одна унікальна PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` у config/database.php:62, яку спричинили 32 tests; це не приховано як «без issues». Усі 675 захищених runtime/config/cache fingerprints runner залишилися незмінними.

Покрито package/semantic fixtures, exact ledger, усі 56 власних controls/options/aliases, 5 demos окремо, 17 full inputs, scalar/native rendering, caller-owned fallback/clone boundary, усі NULL levels, 30 bank selections, pool partition, stale/foreign/locale guards, one-F-practice/insert-only transaction/rollback/no-op, nonce/local guard/privacy, finite source/checksum boundaries. Offline native JS replay: 17 × manual/tokens × nominal desktop/mobile = 68 cases; це **не browser evidence**.

Production-profile content parity — ізольований локальний тест, без production HTTP. У WT відсутній vendor: окрема WT спроба зупинилася на preflight, **0 methods**. ROOT V5 і static paired source checks не названі виконаним WT test run.

### Старі 14 списків: literal BEFORE/AFTER

| Scope | Executions / unique / overlaps | Assertions BEFORE → AFTER | Фактичні outcomes |
| --- | --- | --- | --- |
| Старі 13 lists | 602 / 509 / 93 | 150109 → 150157 | F55/E1/W1/D13/skipped0 — counts unchanged |
| M83 окремо | 46 / 46 / 0 | 23790 → 23790 | F0/E0/W0/D1/skipped0; усі methods, bodies, messages і exits exact |
| Усі 14 | 648 / 555 / 93 | 173899 → 173947 | F55/E1/W1/D14/skipped0; 0 added/missing methods |

Нових failing methods/errors немає, але literal equality всіх старих outputs **немає**: шість explicit deltas не приховано. F55 у таблиці — executions; це 47 unique failing methods, E1 — один unique error. D13/D14 — суми за окремими запусками, не 13/14 різних першопричин PHP deprecation.

| Зміна | Method / факт |
| --- | --- |
| Існуючий source-guard failure body | M77OwnedSharedSourceDeltaTest::test_twelve_finite_registrations_and_one_cardinality_hunk_reverse_to_complete_accepted_m76 — лише actual TheoryContentSource SHA замінено на новий |
| Існуючий source-guard failure body | M73SharedLineageTest::test_reversing_only_twelve_m73_hunks_recovers_all_eight_accepted_m72_sources_and_preserves_null_guards — та сама one-SHA substitution |
| Існуючий source-guard failure body | M74OwnedSharedSourceDeltaTest::test_only_twelve_dispatch_hunks_and_one_finite_plain_subtitle_hunk_reverse_to_the_full_accepted_m73_sources — та сама one-SHA substitution |
| Існуючий source-guard failure body | M74OwnedSharedSourceDeltaTest::test_standalone_callbacks_keep_object_guards_and_exact_controller_crlf_while_page_controller_stays_unchanged — лише actual TestJsV2Controller SHA |
| Still PASS, +42 assertions | TheorySentenceBuilderAcceptanceTest::test_dynamic_catalogue_requires_independent_contract_and_effective_connection_for_every_target — 6 нових contract targets × 7 незмінених assertions |
| Still PASS, +6 assertions | M74BankRevisionTest::test_registry_preserves_all_old_contracts_and_discovers_future_owners_without_a_145_cap — 6 нових owners × 1 assertion |

У чотирьох existing failure bodies expected values, outcome types/messages, решта body та assertions не змінені. Їх не названо новими failures або exact baseline. Старі тести, lists, parser, runner і gates не редагувалися. Перевірки виконані в SQLite :memory:, array cache/session, без завантаження робочого .env та без CLI opcache.

## 8. Старі consumers: усі 51 GET охоплено, strict FAIL не приховано

Свіжий BEFORE і AFTER boundary охоплює 13 saved tests, 888 старих question rows та 51 anonymous loopback GET: 6 course DOM, 19 JSON і 26 SSR. Усі 51 — HTTP 200. Усі 46 таблиць до/після GET exact; old source hashes, routes, question SQL і saved memberships exact.

**Strict результат: 43 entries exact, 8 entries FAIL.** Перший незмінений gate двічі зупинився на старому advanced-word-order polyglot consumer. Два окремі replays тоді дали exact entry; це не використано для roulette-PASS. Оригінальний gate/primitive не змінено; successful `after-old-consumers-v1.json` не створено. Окремий дозволений private collector дійшов до всіх 51 URL, зберігши кожен strict diff як FAIL, без normalization або заміни очікувань.

На 3240 question-payload occurrences є 9 text differences у 5 старих UUIDs. Кожна BEFORE/AFTER value вже exact існувала в BEFORE як canonical question або question_variant: primary Verbs/Objects; poly Adverbs A1-10; poly Advanced B1-08; poly Verbs/Objects A1-01 та A2-10. Це не нові M84 supplement questions.

Існуючий anonymous SSR вибирає fresh variants; JSON /questions завжди перебудовує fresh dataset. QuestionVariantService обирає canonical/legacy text випадково й може виключати попередній вибір. Тому незалежні fresh GET можуть мати різний visible question за незмінної БД.

Для одного primary Verbs/Objects питання стара canonical версія вже містила technical prefix `[Word Order with Verbs and Objects #01.1]`, а стара variant — ні. В одному mixed JSON це спричинило також 3 derived differences: reorder_answer, reorder_tokens, reorder_source_question. Обидва outputs чисто replay-нуті фактичним існуючим ROOT reorder builder із незмінною answer_map. Похідні digest differences теж збережено. Усі інші питання/поля, underlying answers/options/hints/linked theory та 6 course DOM exact. Старий prefix/variant дефект поза scope M84 не «виправлявся» редагуванням старих банків.

Cookie може стабілізувати повторний SSR із persisted question data, але не JSON endpoint, який примусово перебудовує variants. mode очищає state, source=theory змінює consumer, compose_shuffle_seed керує порядком питань, не text variants. Same-URL public deterministic variant seed не знайдено; cookies/config/code заради green check не змінювалися. Це **класифікований strict HTTP limitation**, не загальний PASS старих outputs і не доказ нової M84 DB/teaching regression.

## 9. Фактичне браузерне приймання

Actual локальні перевірки відокремлено від SSR і offline JS replay. Уже є 12 desktop/mobile full-input receipts: **68 actual manual/token cases** (A–E по 12, F — 8), включно з 34 token delete-return та 34 mixed-editing випадками. Видимих listboxes у цих receipt checks — 0. Зафіксовано empty/whitespace/wrong → correct, повторний Check і reset штатними controls.

Шість option receipts містять **64 actual option checks**; старі graded rephrase й усі 5 demos перевірені окремо, demos без score. Є 7 додаткових manual aliases. Separate group scoring F має окремий receipt. Усі шість реальних widgets доведено до **5/5**, прочитано **30 settled English sentences** після Check; не лише token count або bank JSON.

Матриця сторінок завершена: 1440×1000, 390×844, 320×844 у light/dark — 36 actual states і 36 matrix screenshots у шести state receipts. Consolidated browser receipt зв’язує 12 input receipts, 6 widget receipts, 6 state receipts, 6 option receipts і alias checks; bad viewports 0, visible listboxes 0. Practice boxes fit у перевірених states; таблиці зберігають штатний локальний horizontal scroll. Це не заява про нульовий global document overflow або повний visual/WCAG PASS.

Невеликий змінний mobile document overflow відтворено також на незміненому PPC reference: viewport 390, client 375, frame 390, scroll 392. Actual M84 practice right edge — 357 при viewport 390 та 287 при 320. Це shell/decorative boundary, відділений від fit навчального контенту, не прихований новим CSS. Контентне M84 не є design-fix.

Початкові uppercase A/B locators виправлено на фактичні accessible a/b без змін джерел. Одна browser sequence перевищила 30 s і скинула REPL після B widget 5/5: відновлено відому вкладку, прочитано actual state і повторено всі evidence-bearing A/B випадки з окремими receipts. Transient stale viewport observations не зараховувалися. Особисті старі sessions/global storage не очищалися; restart controls використовувалися тільки для поточних agent-owned M84 widgets. Наприкінці відновлено original dark theme, скинуто viewport override; zoom не змінювався.

Post-UI read-only v2 і v3 повторно підтвердили exact SQL AFTER усіх 46 таблиць. Browser canonical crosscheck звірив усі 30 actual records із frozen conditions/settled sentences: mismatches 0, усі шість widgets мають ✓5. Browser automation не робила DB writes.

Fresh SSR перевірив 1076 learning fields, 56 graded controls, 5 demos, 17 full inputs, 30 linked questions та exact шість H1. Read-only regression GETs PPC і шести M83 neighbors підтвердили по одному native widget та їхні selected IDs; це не нове повне проходження старих UI.

Print, повний WCAG audit, повна no-JS працездатність практики, course progress session і mixed answer interaction — **not_checked**. SSR читання не замінює цих перевірок. Прийняття canonical/alias відповідей не доводить автоматичне зіставлення всіх синонімічних token inventories.

## 10. Докази, hashes і доставка

Приватні receipts, SQL snapshots, runtime identity, backups/dumps, cookies/secrets і raw diagnostics не призначені для Git. Нижче лише basename/hash references; teaching artifacts і source contracts versioned.

| Доказ | SHA-256 |
| --- | --- |
| Незалежний DB/source/HTTP BEFORE | babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916 |
| Paired 3390-path source BEFORE | 9066938a376b3f4eeae6fa65f6fea6afc7a2bdd624a4ed93dc32e37c59b8e47e |
| Old-consumer BEFORE v2 | 1a3cf5b616783d0740650b498a8fb3b036dd64fbcfea79011edd20fe6687a98a |
| Independent final read-only reconstruction | ec2c724ce1c88c64eb68f953b9209d6993591485d22cd3406e42be8a9cde0131 |
| Post-UI read-only v2, all46 exact | 2d1e1c876a7b9a1d9975ad7babf122820da52c4931b5f5acbc46092e31bcdda3 |
| Final read-only v3, all46 / WT attribute raw-prefix exact | 2ae86f6c53c8242f8049ff42fe121949620b56577a7a04bf131515930dd4c55d |
| Scoped sentence-builder registry, 6 targets | 09c6284bf3d08d126a1d6e6d1d3aa8e83593ba3a3456c3853abdf871ed09659e |
| Global registry, 193 previous + 6 M84 = 199 | d96330cd25edce3165aa920534b8855a367698f07101a3cd48fe3e520a8ecc0d |
| Consolidated actual browser acceptance | 53c8feac307325776d567d43357f865846182a7775847f202631908f868d3139 |
| Exact route restoration / 404 cleanup | 56a24721911709fbab754d814985ee4b7736bd8698e1ea201beef8b3277327af |
| Fresh live-pages SSR | 88de6aea5fe7333e0e2c67ba33efa1f301f2fbecf7ee479250d8105fdb1bd904 |
| Navigation review | a9eb119f3b37d57339d61b4e579fa263c5c36d56f706dc315d48cdd8dfa18c0d |
| ROOT own tests V5 | 3045fc272e186592f91177f799673fe71c647090dbc538776fbf93c5d14332f7 |
| Old14 exact comparison | 1354b0e118816f13215259b81b901f88618b736562b6732051df5f2443a4e053 |
| Six old-test delta classification | 1bf534414ac7f78af1084eda4e8408d1d5d385f54780ef0af97368c02c0e3e71 |
| 51-consumer AFTER collection v2, strict FAIL retained | 24bbeaab186d069ba1d61f2070317f0bcce46d7fc96524f0c2b36a360368d7c8 |
| Exact existing-variant / derived-field classification | 5a0a4320f258e9fcacfb60248dbc48f3e03da2c44b8c3e65492f950325c94e0e |
| Active owned-source fixture metadata 1.0.1 | 9bf427faa6045c7060e2d369c9caea1c797dcd80f573e8f7e0cd4eafe2cdee84 |
| Active source checksums metadata 1.0.1 | 74a9bd8d44ed9aee037fb7cdf232bcd68b43eca330b0dd89d09a9259462b5c0a |

Versioned source entry points: M84WordOrderHaveGot, M84BuilderSelectionPresentation, M84QuestionSupplement, M84ContentPatch, M84QuestionInsertScope, M84LocalTargetGuard, ContentPatchM84; незмінені shared native primitives використовуються повторно. Master, patch, definitions, component/task/editorial/coverage maps і builder contract мають exact frozen checksums.

Staged review завершено: exact allowlist 74 files, 23 frozen index artifacts/definitions збігаються з SHA manifest, усі 8 shared RAW profiles і reverse-to-HEAD hunks exact. Нових visual sources, routes, секретів, private receipts/screenshots/backups або foreign files в index немає. Звичайний diff-check позначає тільки чотири CRLF registrations у controller; CRLF свідомо збережено. `git -c core.whitespace=cr-at-eol diff --cached --check` PASS. За чинними Git attributes index нормалізує LF лише в .gitattributes і двох existing text services; working RAW profiles та історичний attribute prefix лишаються exact. Full frozen-source validation: 6 owners / 25 supplement questions / 6 bank targets, 0 SQL connections/writes.

Content commit: **0b566cd3333dc89be71fd89fa9d312c6bf9ce720**. Normal push у `codex/seo-m84-word-order-have-got-standard-view` успішний; окремий `git ls-remote` підтвердив remote SHA == цьому content HEAD. Фіналізація цього звіту — окремий report-only commit у тій самій гілці, без додаткових навчальних/DB змін; його кінцевий HEAD наведено у фінальній відповіді й окремо звіряється з remote. Це не дозвіл або свідчення deploy. Main, PR, merge/rebase/cherry-pick, force-push, production/gramlyze.ub mutations не виконувалися.
