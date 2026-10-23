# M82 — типові помилки та змішане повторення: локальне застосування

Статус: **основну редакцію 1.0.1 та скінченну prose-корекцію 1.0.2 застосовано до робочого `gramlyze.loc`; локальні acceptance gates підтверджені**. Основний apply — 44 updates + 6 inserts; корекція — ще 2 body updates тих самих B rows, тому ефективний diff від незалежного BEFORE — 44 unique updates + 6 inserts. Обидва застосування мають backup, postconditions, no-op ×2 і exact proof-route cleanup + 404. Прийнято 36/36 layout states, 30 settled widget answers, 48 full-input cases, 60 option cases, registry 187 та фінальні SQL/source/old-consumer repeats. Dedicated M82 profile — 36 tests / 41 572 assertions, failures/errors 0. Повний історичний набір не названо green: його 8 нових M81 source-guard failing executions окремо пояснено в §8. Commit і push ще pending.

Робоча гілка: `codex/seo-m82-common-mistakes-level-revision-standard-view`.
База: `b8fecb29492038dc01d374955b61c500c718fa4c` — прийнятий M81. Commit M82 і push: **pending**. Main, production, `.com` і `.ub` не є ціллю застосування; production-запитів і деплою не виконано.

## 1. Шість точних цілей

| Код | Сторінка | Page / category ID | Category path | Display level | Рівень нової практики / widget filter |
| --- | --- | --- | --- | --- | --- |
| A | [Present Perfect: Common Mistakes](http://gramlyze.loc/theory/common-mistakes/present-perfect-common-mistakes) | 42 / 9 | `common-mistakes` | A2–B1 | A2 |
| B | [Countable vs Uncountable Nouns: Common Mistakes](http://gramlyze.loc/theory/common-mistakes/countable-vs-uncountable-nouns-common-mistakes) | 40 / 9 | `common-mistakes` | A2–B1 | A2 |
| C | [Prepositions: Common Mistakes](http://gramlyze.loc/theory/common-mistakes/prepositions-common-mistakes) | 41 / 9 | `common-mistakes` | A2–B1 | A2 |
| D | [B1/B2 Grammar Traps](http://gramlyze.loc/theory/common-mistakes/b1-b2-grammar-traps) | 39 / 9 | `common-mistakes` | B1–B2 | B1 |
| E | [A1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/a1-mixed-revision) | 325 / 59 | `mixed-revision` | A1 | A1 |
| F | [A2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/a2-mixed-revision) | 326 / 59 | `mixed-revision` | A2 | A2 |

E/F мають namespace `BasicGrammar`, але їхня фактична категорія — `mixed-revision`. Category URL не виводиться з розташування definition-файла. Display range не розширює SQL-фільтр запитань; наявні `NULL` levels теоретичних блоків не переписуються.

Точні owners:

- A: `Database\Seeders\Page_V3\CommonMistakes\CommonMistakesPresentPerfectTheorySeeder`.
- B: `Database\Seeders\Page_V3\CommonMistakes\CommonMistakesCountableUncountableTheorySeeder`.
- C: `Database\Seeders\Page_V3\CommonMistakes\CommonMistakesPrepositionsTheorySeeder`.
- D: `Database\Seeders\Page_V3\CommonMistakes\CommonMistakesB1B2GrammarTrapsTheorySeeder`.
- E: `Database\Seeders\Page_V3\BasicGrammar\BasicGrammarA1MixedRevisionTheorySeeder`.
- F: `Database\Seeders\Page_V3\BasicGrammar\BasicGrammarA2MixedRevisionTheorySeeder`.

## 2. Незалежний BEFORE та межі запису

Незалежний робочий BEFORE: `before-v1.json`, SHA256 `94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62`.

Read-only capture зафіксував шість теоретичних і шість theory-course відповідей HTTP 200. Відбитки всіх 46 таблиць залишилися точними до/після цього читання. Це доказ BEFORE, не приймання нового контенту.

У шести сторінках було 38 UK-блоків разом із шістьма subtitles: 32 продовжувані контентні блоки та 6 subtitles. Окремо зафіксовано 56 старих non-UK блоків, які не входять до write-scope. Продовжувані IDs/UUIDs, порядок, locale, колонки, рівні та зв’язки зберігаються; додається по одному UK `practice-set` на власника.

Основний фактичний write-set 1.0.1: **44 updates + 6 inserts, 0 deletes** — шість Page-текстів, шість subtitles, 32 продовжувані блоки та шість нових практик. Це відповідає exact allowlist review; для восьми `box`-рядків окремо дозволено 16 metadata field transitions (`type` і `heading`), а не додаткові DB-операції. Question-family writes — **0**: без нових банківських питань, переписування answers/options/hints, reseed, змін курсів, memberships або прогресу.

Виконано ланцюжок fresh local web/CLI proof → fresh preview → exact diff review → exclusive backup → transactional targeted apply → postconditions → повторний no-op двічі. `backup-v1.json` byte-exact відповідає погодженому `preview-v1.json`, SHA256 `65fd9f37c05ba7b52eb70656eb06514349aabc884c6820927e1ff3214ba26df5`. Резервна копія й приватні proofs не входять до commit.

`final-readonly-v1.json` підтверджує очікуваний AFTER, exact protected projection, 38 continuing UK rows, 56 незмінених EN/PL rows та 46-table audit. Старі рядки поза погодженим write-set і їхні timestamps збережено; це не твердження, що 44 навмисно змінені рядки залишилися незмінними. Перший тимчасовий proof route видалено: routes file відновлено byte-exact до попереднього foreign-inclusive стану, окремий GET повернув **404**, без redirects.

Фінальна скінченна корекція 1.0.2 виконала **2 body updates** лише B rows **1673/1675**, inserts/deletes/question-family writes — **0**. Додано пробіли у п’яти slash-списках трьох prose fields, без зміни слів, practice/answers/aliases/tokens/widget/navigation bytes чи дизайну. Це ті самі два рядки серед первинних 44, а не ще два нові target rows: ефективний підсумок — **44 unique updates + 6 inserts**. Fresh guarded correction apply мав власні exact preview/backup, postconditions і no-op ×2; його proof route також byte-exact видалено з окремим **404**. `final-whitespace-readonly-v2.json` підтвердив усі 46 AFTER fingerprints exact після фінального UI щодо першого correction AFTER.

## 3. Авторська редакція A–F

### A. Present Perfect: Common Mistakes

Повноцінний basic навчає послідовності «задум → час саме цієї події → форма → мінімальне виправлення → зміст». Збережено різницю Present Perfect/Past Simple та вже правильні контекстні речення. `Gone to` не оголошується доказом прибуття; `been in` не підмінюється поверненням. `Since 2022` не перераховується у фіксовану кількість років. Пояснено `already/yet/just`, have/has + V3, заперечення, питання, короткі відповіді та доречні варіантні межі.

Одне змістовне point-detail порівнює новину й минулі подробиці, не приховуючи необхідне базове правило.

### B. Countable vs Uncountable Nouns: Common Mistakes

Злічуваність визначається заданим значенням: інформація/порада/домашня робота, одиниці та порції не зведені до глобального списку заборон. Одна порада не замінюється невизначеним `some`; «багато інформації» не перетворюється на вигадане точне число. Узгодження залежить від головного іменника. `Few/little/a few` розглянуто окремо від достатності, яка перевіряється фактичним контекстом. Окремих details немає: потрібні пояснення лишаються видимими.

### C. Prepositions: Common Mistakes

Збережено часові, просторові й залежні моделі, `arrive at/in` та зміст `late`. Розрізнено рух усередині й перехід у простір, напрямок та фактичне прибуття. Нормативні `jump/put/fall` з `in/into`, а також контекстні `on/onto`, не оголошені автоматично неправильними. Відсутність прийменника має непорожній оцінюваний варіант «Без прийменника».

Одне meaningful detail зіставляє два контексти `jump` і не вводить blanket-правило «будь-який рух тільки into».

### D. B1/B2 Grammar Traps

Збережено чотири source-групи: conditionals, modal perfect, linkers, inversion. Звичайний Second Conditional не представлено як обов’язкову past/past невідповідність. Third/mixed conditions розрізнено за часовим задумом; must have + V3 — за висновком, а не минулим обов’язком. Поступка зберігає виконавців. Початкова заперечна обставина відрізняється від medial `rarely` та `only` при підметі. B2-поглиблення не є підставою змінювати технічний B1-фільтр.

Одне meaningful detail порівнює `only` при підметі й початковій часовій обставині. Додаткове finished-event widget-питання має явний часовий критерій, а не заяву про перевірку всієї системи часів.

### E. A1 Mixed Revision

Збережено чотири native-функції: `hero`, `summary-list`, `forms-grid`, `navigation-chips`; усі початкові levels A1. У basic залишаються 11 груп: be; there is/are; have got; can; Present Simple/Continuous; Past Simple; will; articles; some/any; quantity; comparison. Нова власна практика змішана за функціями, а не складається лише з Present Simple. Зв’язок із English Sentence Builder A1 та контрольним списком перед уроком 16 збережено. Окремих details немає.

### F. A2 Mixed Revision

Збережено ті самі чотири native-функції та початкові levels A2. Basic охоплює 12 груп: Present Perfect/Past Simple; time expressions; First/Second Conditional; going to; advice/obligation; gerund/infinitive; Past Continuous; relative clauses; passive; reported speech; used to; tags. Власні завдання зберігають reported-speaker roles, названого passive agent і різницю «не обов’язково / заборонено». Зв’язок із English Sentence Builder A2 збережено. Окремих details немає.

Усього meaningful details: **3**, розподіл A–F **[1, 0, 1, 1, 0, 0]**. Короткі ремарки, переклади та критерії лишаються у visible basic. Нового дизайну або окремого M82 view/CSS не вводиться.

## 4. Практика та фактична вибірка widget

На кожного власника застосовано **2 selects + 2 meaning choices + 2 full manual/token inputs**. Разом — 36 нових own tasks: по 12 кожного типу, з 12 повнореченнєвими inputs. Контекст і критерій знаходяться у підтримуваних видимих полях; готової відповіді в hint/placeholder немає. Токени згруповано по 1–3 слова й перемішано; aliases скінченні та контекстні. Feedback — plain text з буквальними native ключами. Реальне browser interaction acceptance підтвердило 48 унікальних full-input task/mode/size cases на 1440/390, усі 60 унікальних selects/meaning options та окремі aliases, empty/wrong/edit/recheck/reset/delete/rebuild/mixed-input сценарії. Повторні спроби не додаються до кількості унікальних cases.

Додатково кожна реальна сторінка віддає окремий вбудований widget із п’ятьма існуючими питаннями: live SSR перевірив по одному widget, рівно 5 payloads, точні IDs, послідовність і ordered tokens для всіх шести сторінок. В окремій реальній browser-вкладці пройдено **всі 30 питань, по 5/5 для A–F**; збережено фактичне settled sentence після native перевірки кожної відповіді. Це не лише очікуваний source key або миттєвий pre-settle текст.

Спільний prefix класів банків: `Database\Seeders\V3\Polyglot\`.

| Код | Клас банку після prefix | Eligible pool | Вибрані existing question IDs |
| --- | --- | --- | --- |
| A | `PolyglotCommonMistakesPresentPerfectAllLevelsLessonSeeder` | 12 / A2 | 44211, 44212, 44216, 44217, 44218 |
| B | `PolyglotCommonMistakesCountableUncountableAllLevelsLessonSeeder` | 12 / A2 | 43997, 43998, 43999, 44003, 44004 |
| C | `PolyglotCommonMistakesPrepositionsAllLevelsLessonSeeder` | 12 / A2 | 43851, 43852, 43857, 43858, 43860 |
| D | `PolyglotCommonMistakesB1B2GrammarTrapsAllLevelsLessonSeeder` | 12 / B1 | 44295, 44297, 44298, 44302, 44303 |
| E | `PolyglotA1MixedRevisionAllLevelsLessonSeeder` | 12 / A1 | 46575, 46577, 46578, 46584, 46586 |
| F | `PolyglotFinalDrillA2LessonSeeder` | 24 / A2 | 78, 7548, 7550, 7551, 7556 |

Підсумки незалежного банківського аудиту:

- **84 eligible** = 12 × 5 + 24; із них вибрано 30 існуючих, нових банківських питань — 0.
- **384** type-4 питання в обраних банках на всіх їхніх рівнях.
- **480** pagewide type-4 питань у всіх пов’язаних банках шести сторінок.
- **912** усіх pagewide питань усіх типів. Ці числа не є кількістю нових вправ.

Вибірка прив’язана до повних original question/answer/option/pivot/hint snapshots. На unsaved canonical-theory clones змінюється лише видима українська умова; raw keys, option multiplicity, native case/formatter/score не переписуються. Для F критерії `should` проти нормативного `ought to` та `learn` проти `study` названо до відповіді. Невибрані небезпечні casing/formatter кандидати не «полагоджено» зміною спільного рушія.

Coverage map містить **44 рядки**: A/B/C/D по 5, E — 11, F — 13. Рядки зв’язують source topic, actual theory point, own IDs, actual selected widget IDs та непокриту частину/посилання. `There is/are`, окремі A2 relative/gerund/time-expression підмоделі й інші неоцінювані форми залишаються видимими; 36 + 30 завдань не оголошуються повною оцінкою курсу чи CEFR.

## 5. Погоджена навігаційна редакція 1.0.1

У source E/F були п’ять label-only items. Чотири noncurrent labels кожної сторінки давали старі `#` через незмінений native resolver. Користувач окремо дозволив додати **рівно 8 explicit URL fields** у дані: labels, порядок, current flags і п’ятий current item збережено. Shared validator, view і resolver не змінюються; попередній finite label-navigation opt-in відкликано.

Застосовані та перевірені destination URLs:

| Source | Label | Перевірений destination |
| --- | --- | --- |
| E | Verb to be — Present | [Verb to Be: Present Forms](http://gramlyze.loc/theory/basic-grammar/verb-to-be/verb-to-be-present) |
| E | There is / There are | [There Is / There Are](http://gramlyze.loc/theory/basic-grammar/verb-to-be/there-is-there-are) |
| E | Some / Any — Things | [Some / Any — Things](http://gramlyze.loc/theory/some-any/theory-some-any-things) |
| E | Comparative vs Superlative | [Comparative vs Superlative](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative) |
| F | Present Perfect Forms | [Present Perfect: Forms and Use](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms) |
| F | First Conditional | [First Conditional](http://gramlyze.loc/theory/conditionals/first-conditional) |
| F | Reported Statements | [Reported Statements](http://gramlyze.loc/theory/reported-speech/reported-statements) |
| F | Second Conditional | [Second Conditional](http://gramlyze.loc/theory/conditionals/second-conditional) |

Облік `m82-navigation-review.v1.json` фіксує фактичні target identities і read-only local GET: 8/8 HTTP 200, без redirects, із відповідними H1/canonical paths. Після apply `live-pages-v1.json` окремо підтвердив усунення всіх восьми `#` у реальному SSR footer, точні destinations, незмінні labels/order/current та current fifth items без URL. Загалом перевірено 34 source navigation items і 14 різних destinations — усі HTTP 200. Додатково **всі вісім погоджених посилань фактично натиснуто в браузері** з перевіркою destination URL та H1. Непоточні URLs не отримані з namespace `BasicGrammar`.

## 6. Курси й незмінні споживачі

English Sentence Builder A1 і A2 мають по **16 уроків**, їхні фінальні drills — по **24 питання**. Збережено completion window **100** і minimum rating **4.5**. У перших двох A1 lesson pools фактично є по **72** питання, тоді як історичні seed definitions містять по 24; actual pools не замінюються історичними seed-даними.

Використання п’яти FinalDrillA2 originals у F widget не змінює фінальний урок, memberships, запитання/опції/підказки, порядок курсу, enrollment, score чи progress. Caller-owned межа відновлює BEFORE clones для курсів і standalone consumers, а новий theory widget не стає їхнім новим final drill.

Source delta обмежено **12 доданими nonvisual registration lines у 8 файлах**: `TheoryContentSource`, `TheoryHtmlAdapter`, `TheoryLegacyAdapter`, `TheoryContentRenderer`, `PracticeCompletionLinkedPractice`, `TextBlockToQuestionsMatcherService`, `TheoryCourseManifestService`, `TestJsV2Controller`. Не змінено спільний serializer/validator `TheoryNativeContentRelease`, frontend grader, formatter або native views. Реальний AFTER source audit перевірив обидва ROOT/WT profiles, 16 raw/LF reversals, 6 456 non-target source context checks і 202 course-source context checks; незалежний M82 source test зберігає 326 visual paths, 2 902 immutable paths і 101 course-source path. Це різні scopes, їх не додаємо як кількість унікальних файлів.

`after-old-consumers-v1.json`, а потім фінальний `after-old-consumers-v2.json` після 1.0.2 виконали по **56 anonymous local GET, усі HTTP 200**. Фінальний аудит зберіг exact original BEFORE source/per-question/course DOM bindings для **14 банків / 912 questions**; усі 46 table fingerprints до/після GET однакові. Адаптер 1.0.2 має сильні predecessor/final-two-body pins, а не глобальне виключення змін. Окремий SQL/source audit підтвердив 32 saved course bindings, 864 membership/question rows, по 24 питання у фінальних drills і exact rows/timestamps поза дозволеним write-set. No-JS course/standalone response не є перевіркою client-side course progress UI; course JS/progress submissions не виконувалися.

## 7. Freeze lineage та джерела

| Кандидат / доказ | Статус | SHA256 |
| --- | --- | --- |
| `freeze-plan-v1.json` | Frozen-кандидат 1.0.0; **не застосований до робочої БД**, superseded через 8 погоджених nav URLs | `fdc49ad56bd5fc4b9b0ce45fb06f1840e9ea680a0034e98991bc47f0e5aeb61c` |
| `freeze-plan-v2.json` | Погоджений immutable snapshot 1.0.1; apply доведено окремо | `0008adfeca8e77476d4a60a02fd6d108c21b2708b0d27b59995cdc59facc88b1` |
| `author-projection-v2.json` | Повний основний teaching projection 1.0.1 | `256cd5fa67379d596432b3b431588cce61450ac4b7d71fb9514c6c4d34101611` |
| `bank-audit-projection-v2.json` | Source-bound existing-question audit | `21e3d014243c4e11afb16cdee8f5dbdaeee71cf7d73e1852ec811eadc41060de` |
| `prospective-metadata-v2.json` | Очікувані описи, обчислені до apply; не AFTER metadata | `2e9d800395321e18a6e2f6c25d6f9dca28d7cd59c6a313d0f80690563835e9c3` |
| `m82-semantic-fixtures.v1.json` | Literal semantic expectations + 8 URL fields 1.0.1 | `20172800e3accc10abea102b168d99b11c0ea7ab8ebbf10ccbefd4b9d6d3b3be` |

V2 має **18 вихідних payloads загалом: 12 frozen artifacts + 6 active definitions**; шість definitions уже входять до числа 18, а не додаються до нього. UK readable edition 1.0.1, master, editorial/component/task/coverage maps, checksums і bank contract матеріалізовано синхронно. Старий Markdown 1.0.0 видалено лише за exact old bytes; V1 private plan збережено. Freeze/materialization не підміняють окремих доказів реальної БД та браузера.

Після browser layout на B/320 окремо оформлено й застосовано **корекцію 1.0.2**: рівно п’ять slash-списків у трьох prose fields двох B body rows 1673/1675 отримали пробіли без зміни слів, навчальних правил, вправ, ключів чи дизайну. **Початкові frozen public artifacts 1.0.1 збережено byte-exact**, до них додано **9 successor artifacts 1.0.2**; активна B definition відображає лише ці два оновлені body. Це не переписаний snapshot 1.0.1. `freeze-whitespace-correction-v1.json` має SHA256 `78ecd3a0be5aaef55f76b0a2b40601a1d956d893b4296e9fedf04faf1273e71c`; сам freeze не є доказом apply. Final source/DB/SSR/browser receipts наведено нижче.

У master є 41 запис обліку первинних джерел, не 41 унікальне джерело. Чітко розділено:

- Direct editorial body: наприклад [British Council — Present perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/present-perfect), [British Council — Third and mixed conditionals](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-third-mixed), [Cambridge — In](https://dictionary.cambridge.org/de/grammatik/britisch-grammatik/in) на доступній DE-shell сторінці з англійським body.
- Indexed publisher editorial body/excerpt: наприклад [Cambridge — At, in and to: movement](https://dictionary.cambridge.org/grammar/british-grammar/at-in-and-to-movement). Indexed-read не названо direct-read.
- Direct HTTP 403: зокрема запитані Cambridge варіанти для [Past simple or present perfect](https://dictionary.cambridge.org/grammar/british-grammar/past-simple-or-present-perfect), [On](https://dictionary.cambridge.org/grammar/british-grammar/on) та [Only](https://dictionary.cambridge.org/grammar/british-grammar/only). Якщо читався indexed excerpt або інший доступний publisher URL, це окремо записано; сам 403 не є прочитаним body.
- [Cambridge coffee entry](https://dictionary.cambridge.org/dictionary/english/coffee) недоступний direct; countable servings перевірено окремим доступним [TeachingEnglish — Countable](https://www.teachingenglish.org.uk/professional-development/teachers/teaching-knowledge-database/c/countable), а не вигаданим direct-read.

Named publisher-team replies відділено від основного body; learner comments не використано як авторитетне правило. Publisher exercises не копіюються. Agent/main editorial review не названо human review, а редакції не приписується офіційна CEFR-сертифікація чи гарантія індексації.

## 8. Автоматичні перевірки: точний BEFORE → AFTER

Свіжий BEFORE виконано тими самими 12 suite lists і незмінним comparator проти **exact accepted M81 AFTER**: **566 method executions / 473 unique methods / 93 overlaps**, 0 added / 0 missing / 0 changed. Це незалежно відтворений baseline, а не green PASS усіх тестів.

Після матеріалізації ROOT/WT, а потім повторно на фінальній 1.0.2 виконано ті самі 12 списків через незмінений isolated runner: SQLite `:memory:`, без working `.env`, array cache/session, CLI opcache вимкнено, два workers. У кожному прогоні всі **675 protected files** та bytes старих test files збережено. Final AFTER V2 також має 566 executions / 473 unique methods / 93 overlaps; доданих чи зниклих methods немає. Таблиця нижче порівнює незалежний fresh BEFORE із фактичним final AFTER 1.0.2.

| Suite | Executions | Assertions BEFORE → AFTER | Failures / errors BEFORE → AFTER | PHPUnit / runner exit BEFORE → AFTER | Changed executions |
| --- | --- | --- | --- | --- | --- |
| m78 | 41 | 22 119 → 22 119 | 4 / 0 → 4 / 0 | 1 / 1 → 1 / 1 | 0 |
| m75 | 33 | 9 522 → 9 522 | 1 / 0 → 1 / 0 | 1 / 1 → 1 / 1 | 0 |
| m76 | 46 | 10 575 → 10 575 | 0 / 0 → 0 / 0 | 0 / 0 → 0 / 0 | 0 |
| m77 | 30 | 22 051 → 22 051 | 1 / 0 → 1 / 0 | 1 / 1 → 1 / 1 | 1 |
| generic | 30 | 14 777 → 14 819 | 0 / 0 → 0 / 0 | 1 / 1 → 1 / 1 | 1 |
| interaction | 134 | 1 588 → 1 588 | 8 / 1 → 8 / 1 | 2 / 2 → 2 / 2 | 0 |
| wide | 136 | 27 227 → 27 233 | 15 / 0 → 15 / 0 | 1 / 1 → 1 / 1 | 4 |
| m79 | 28 | 7 915 → 7 915 | 4 / 0 → 4 / 0 | 1 / 1 → 1 / 1 | 0 |
| m80root | 20 | 5 919 → 5 919 | 4 / 0 → 4 / 0 | 1 / 1 → 1 / 1 | 0 |
| m80wt | 20 | 5 931 → 5 931 | 4 / 0 → 4 / 0 | 1 / 1 → 1 / 1 | 0 |
| m81root | 24 | 22 758 → 7 373 | 0 / 0 → 4 / 0 | 0 / 0 → 1 / 1 | 4 |
| m81wt | 24 | 39 384 → 7 385 | 0 / 0 → 4 / 0 | 0 / 0 → 1 / 1 | 4 |

Final AFTER 1.0.2 проти actual AFTER 1.0.1: **0 added / 0 missing / 0 changed methods**, exact assertions, outcome types, messages, bodies, suite counts та PHPUnit/runner exits у всіх 12 suites. `baseline-after-v2-versus-after-v1.json` фіксує **0 нових deltas** від prose-корекції; незалежний final-versus-fresh-BEFORE comparison зберігає ті самі 14 дельт, перелічені нижче. Це не перейменовує нові відносно BEFORE M81 failures на baseline.

У кожному suite BEFORE і AFTER: skipped 0 та одна deprecation. У `generic` зберігається одна warning, тому його exit 1 не називається green PASS попри 0 failures/errors; інші baseline suites мають warnings 0.

Підсумок exact method/assertion/outcome/message/body comparison: **14 changed executions / 10 unique changed methods / 552 повністю незмінені executions**. Загальна кількість failures у цих повторюваних suite executions: **41 → 49**; errors: **1 → 1**. Усі дельти мають окрему скінченну класифікацію:

1. **8 нових failing executions / 4 unique M81 source-guard methods.** Обидва ROOT/WT прогони раніше проходили, а тепер зупиняються на незмінному M81 whole-controller profile selection: `Every actual foreign-inclusive controller matches exactly one separate ROOT/WT AFTER profile`, фактично 0 збігів замість 1. Це нові AFTER failures після 12 погоджених M82 registration additions, **не історичний baseline**. Нижча кількість assertions відображає ранню зупинку, а не видалення assertions. Методи класу `M81OwnedSharedSourceDeltaTest`:

   - `test_twelve_owned_additions_reverse_to_the_fresh_independent_raw_and_lf_before`;
   - `test_theory_caller_and_old_retained_practice_guards_remain_unchanged_nonvisual`;
   - `test_all_326_visual_profiles_and_existing_token_shift_label_binding_are_raw_exact`;
   - `test_all_historical_sources_and_911_test_paths_keep_individual_raw_profiles_and_absences`.
2. **4 already-failing historical guard executions із новим body.** Один M77 guard і три M73/M74 guards у `wide` зберігають попередні failure type/message/assertion counts. Їхній body не названо exact baseline: окремо доведено, що змінився рівно один actual source/controller SHA, який збігається з незалежним V4 прогнозом; решта body byte-exact.
3. **2 passing registry/catalog methods.** `TheorySentenceBuilderAcceptanceTest::test_dynamic_catalogue_requires_independent_contract_and_effective_connection_for_every_target` отримав +42 assertions (шість owners × сім перевірок); `M74BankRevisionTest::test_registry_preserves_all_old_contracts_and_discovers_future_owners_without_a_145_cap` — +6. Outcomes лишаються порожніми; це не нові помилки.

Старі tests/fixtures не послаблювалися. Поточну збереженість source/visual/course boundaries доводить **окремий новий M82 source-lineage test**, а не несправедливо оголошений PASS старих M81 guards.

Окремий підтверджений M82 profile V2 для редакції 1.0.1: **30 tests / 41 426 assertions / failures 0 / errors 0 / skipped 0 / warnings 0 / deprecations 1; PHPUnit і runner exit 0**, protected675 unchanged. П’ять нових класів містять package 6, native practice 1, content patch 5, independent source 7 і semantic fixture 11 methods. Перший V1 прогін мав одну literal-fixture невідповідність; виправлено лише контекст нового M82 test зі збереженням усіх assertions, обидва receipts збережено. Цей попередній isolated V2 не підміняє фінальний прогін.

**Фінальний actual M82 six-class profile після 1.0.2: 36 unique tests / 41 572 assertions / failures 0 / errors 0 / skipped 0 / warnings 0 / deprecations 1; PHPUnit і runner exit 0; protected675 unchanged.** Додано шість finite whitespace-correction tests до первинних 30, без послаблення старих tests. Receipt `m82-final-six-classes-v1-01ca2ba5fbee4dac80dfab696814253d-result.json`, SHA256 `11299af9313113c560805d5dfb809fb586b7c7a6e86ed9680c09ac2ba0a09c8b`.

## 9. Фінальні локальні AFTER-докази та статус

Початковий `source-sync-v1.json` підтвердив 35 узгоджених runtime/source/input paths; фінальний `source-sync-whitespace-v1.json` — **42 paths** з окремими ROOT/WT hashes. `final-whitespace-readonly-v1.json` і V2 зберегли **326 visual files / 2 787 historical frozen files**, початкові raw bytes спільного native validator і всі інші rows/columns/timestamps exact щодо погодженої 1.0.1. Обидві final correction rows мають ті самі ID/UUID/locale/type/column/order/levels, зокрема SQL NULL; дозволена зміна — лише їхній body.

`live-pages-v2.json` після 1.0.2 підтвердив **6/6 HTTP 200, 640 basic/detail learning fields, 14 footer destinations HTTP 200**, без redirects, cookies чи production requests. Збережені anchors, H1, title/description/breadcrumb/canonical та локальний noindex; details [1, 0, 1, 1, 0, 0] початково закриті, unsupported not-rendered fields — 0. У real SSR лишаються 36 own tasks і 30 exact selected widget payloads. SSR є перевіркою віддачі навчального контенту без JavaScript, **не твердженням про повноцінно працездатну no-JS інтерактивну практику**.

### Browser: фінальна матриця та межі доказів

`browser-acceptance-v2-final.json` має статус `local-interactions-and-final-layout-observed`. Фактичні interactions виконані в локальній окремій theory-вкладці; фінальний receipt є **post-reset reconciliation** збереженого interim interaction receipt, реальних Cua tool outputs і фінальних captures, а не новою live-сесією після reset.

- **30/30 widget questions**, по 5/5 для A–F, із фактичними settled sentences після native оцінювання. Для десяти E/F відповідей native UI додав крапку; облік зберігає actual settled sentence, а не вигадує побуквену тотожність початковому key.
- **48 унікальних full-input task/mode/size cases**: 12 завдань × manual/tokens × 1440/390. Дубльовані спроби не додаються до unique count.
- **60 унікальних select/meaning option cases**; aliases, empty/wrong, edit-correct, recheck, delete-return/rebuild, mixed manual/token editing та **6 independent score/reset owner cases**.
- Усі **3 meaningful point-details** перевірено клавіатурою, у правильному owner, із deep link і reload. Усі **8 нових footer links** фактично натиснуто, перевірено destination URL/H1.
- **36/36 фінальних layout states**: кожна з шести сторінок на **1440/390/320 × light/dark**, включно з B після виправлення slash-списків. У спостережених learning regions немає horizontal overflow; наявний декоративний document overflow до 14 px не змінювали й не назвали нульовим. Фінальний B prose переноситься без зміни native дизайну.

Interaction cases завершені після 1.0.1; їх не видаємо за повторне проходження після 1.0.2. Finite 1.0.2 diff окремо доводить незмінні raw bytes усієї практики, answers/aliases/tokens/widgets/navigation, тому ці completed interactions лишаються застосовними; layout 36 реально зафіксовано вже після 1.0.2.

Для всіх 36 фінальних image artifacts звірено точний набір, dimensions і raw hashes. Native Cua capture bytes — **JPEG, хоча збережені filenames закінчуються .png**; формат визначено за bytes, конвертації/підміни не було. Попередні blank-compositor captures та wrong-tab observations виключено. Font metrics або нові DOM-спостереження з image headers не вигадувалися.

Після всіх 36 фінальних captures Cua REPL reset втратив робочу вкладку; стара панель показала internal error, а `getTab` був заблокований browser security policy. Обхід через іншу вкладку/browser/CDP не виконувався. Збережені tool outputs, завершені interactions та artifacts залишаються evidence, але після блокування не заявлено нового live observation.

| Owner | Effective source | Own interaction + widget | Targeted DB / no-op | Final SSR / layout | Delivery |
| --- | --- | --- | --- | --- | --- |
| A | reviewed 1.0.1, unchanged | accepted + widget 5/5 | applied; no-op ×2 | 200 / 6 of 6 | pending |
| B | reviewed 1.0.1 + prose 1.0.2 | accepted; practice bytes exact + widget 5/5 | correction 2 body updates; no-op ×2 | 200 / 6 of 6 after correction | pending |
| C | reviewed 1.0.1, unchanged | accepted + widget 5/5 | applied; no-op ×2 | 200 / 6 of 6 | pending |
| D | reviewed 1.0.1, unchanged | accepted + widget 5/5 | applied; no-op ×2 | 200 / 6 of 6 | pending |
| E | reviewed 1.0.1 + 4 nav URLs | accepted + widget 5/5 | applied; no-op ×2 | 200 / 6 of 6 | pending |
| F | reviewed 1.0.1 + 4 nav URLs | accepted + widget 5/5 | applied; no-op ×2 | 200 / 6 of 6 | pending |

### Фінальні registry / SQL / old-consumer gates

`registry-sentence-builders-v2.json` підтверджує **187 targets = exact 181 старий owner + 6 нових / failed 0 / writes 0**. По п’ять selected type-4 questions і HTTP 200; старі 181 порівняні з незалежним registry BEFORE. Для M82 явно збережено дво-source lineage: active 1.0.2 плюс immutable 1.0.1 tuple; це finite proof, без глобального registry filter чи зміни engine. Фінальний `m82-sentence-builders-v3.json` окремо підтвердив **6 scoped targets / failed 0 / writes 0**, по п’ять selected questions і HTTP 200. Його metadata flag про перевірку всього старого registry коректно false: scoped proof не є доказом незмінності всіх 181 owners; цю перевірку реально виконав повний registry V2. Помилковий unconditional flag попереднього scoped V2 виправлено лише в новому діагностичному wrapper/receipt, без зміни underlying SQL/HTTP guard або старих receipts; V1/V2 залишено immutable.

`final-whitespace-readonly-v2.json` після фінального UI підтвердив **усі 46 AFTER fingerprints exact** щодо correction AFTER V1, а також exact protected rows/source, 0 diagnostic writes/HTTP. Первинні 1.0.1 final receipts збережені; дозволені тільки дві body-корекції. `after-old-consumers-v2.json` завершився з exit 0: **56/56 GET HTTP 200, 14 banks / 912 questions**, exact original BEFORE source/per-question/course DOM payloads; усі 46 таблиць залишилися незмінними під час GET. No-JS consumer fetch не запускав course JS і не відправляв progress.

Контрольовані private receipts (до commit не включаються):

| Доказ | SHA256 |
| --- | --- |
| `baseline-after-v2-comparison.json` | `88fe608795f965f0a61c1ea0c50d410db1bfad449e4a83d04466537e59b21a57` |
| `baseline-after-v2-versus-after-v1.json` | `5af16fad2b994b4ce9078529b14a3df6a5549315f9cf7b9cbf1b3a951fd7c53b` |
| `baseline-after-v1-delta-classification.json` | `55bb8cd129a9c60392f05d2a73b40a7b0a4cd3fc5d5db83759c65b38a9f7f87c` |
| `m82-final-six-classes-v1-01ca2ba5fbee4dac80dfab696814253d-result.json` | `11299af9313113c560805d5dfb809fb586b7c7a6e86ed9680c09ac2ba0a09c8b` |
| `source-sync-whitespace-v1.json` | `51d274ab331d8acd20acd6a479978da7554cc912474501c19dffa5135ace96ff` |
| `final-whitespace-readonly-v1.json` | `57d4388ba2b261c8c66e4f7890a1717f752342b30da11dfc79e73d2b415864fb` |
| `final-whitespace-readonly-v2.json` | `a8b80f69288ddeccb8f62257023ebee8093869ff7dbf015a09b61bedbcdd752f` |
| `after-old-consumers-v2.json` | `cb89ec18b5ceb001c0e31dc6891f53acc01891eedae180e7d87c9cb76ca4fa1b` |
| `live-pages-v2.json` | `d0d8b9c075997aeccefdf28d12bbe7592854ed2844f7c1ebdcbe9437efb7d5f6` |
| `browser-acceptance-v2-final.json` | `5d343d75cfb71e3e541d0efb392a910e0b45578658ee884eef6929b135150b51` |
| `registry-sentence-builders-v2.json` | `293f961d9d7fe037b805f314c3b2dcba1cd94d143f21c7ac517c82f67314fa69` |
| `m82-sentence-builders-v3.json` | `dc859a3b865d87ecaa4a48fbe106906fea890f12b3ed206c165829fdc8a63996` |
| `cleanup-be91936b18ddcce2700d9541c730f3cd.json` | `3488be25e3088dea4e6648e1ab417d9d61186711267d29b69cabf54306a66048` |

## 10. Межі перевірки та доставка

Print layout і WCAG audit — **not_checked**. Не заявлено офіційної CEFR-сертифікації, production/indexing/ranking результату, course client JS acceptance, проходження всіх 187 сторінок у браузері чи повної no-JS інтерактивної практики. Пізніший browser policy block не обходили; final browser receipt прозоро відрізняє збережені реальні observations від post-reset reconciliation.

Залишилася лише Git-доставка: delivery/staged-diff review, scoped commit перевірених M82 файлів у робочу гілку, push і remote verification; потім окреме мінімальне report-доповнення з уже наявним фактичним content commit SHA та перевіреним push. **Full content commit SHA, delivery commit SHA та push-result — pending; майбутні SHA не вигадуються.** Main і production не змінюються; deployment не дозволений цим локальним завершенням.

Звіт навмисно не містить raw SQL rows, приватних snapshots/proofs, runtime identity, секретів, дампів або screenshots. Наведені basenames та opaque SHA256 служать обліком контрольованих доказів, а не публікацією їхнього приватного вмісту.
