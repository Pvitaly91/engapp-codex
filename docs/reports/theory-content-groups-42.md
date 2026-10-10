# Повне оформлення контентних пунктів 42 сторінок

Дата: 2026-10-11. Гілка: `codex/theory-single-reference-template`.
База перед виправленням: `89a7f14ca`; загальний backup перед універсальною в’юхою: `a802ca0ae`.

## Що було не так

Попереднє покриття спільною в’юхою доводило лише шлях рендерингу. Воно не означало,
що решта 39 уроків отримала ті самі змістові панелі, що й перші три M27.
Безіменні фрагменти лишалися plain flow: без фону цілого пункту та кольорової назви.
Цей follow-up виправляє саме видимий контентний дизайн, а не лише HTTP/маркер.

## Зміни

- 39 уроків: 193 блоки з явною семантичною картою → 376 штатних usage-пунктів.
- 785 первинних індексів збережено по одному разу в початковому порядку; ще 6 вступних сценаріїв залишені цілими.
- Перша трійка M27 зберігає свої 24 погоджені пункти. Разом у 42 сторінках — 400 reference-панелей.
- Фон — штатний світло-сірий; обводка пункту — 0; кольорові лише назва й круглий номер. Приклади використовують чинний компонент, шрифти та синю ліву лінію.
- Немає нових CSS, package-в’юх, рамок навколо кожного абзацу або додаткової оболонки навколо всього контенту.
- `TheoryContentGroups` додає тільки презентаційні обгортки й labels. Джерельні child nodes, таблиці, порядок та власники «Докладніше» залишаються незмінними.
- Перевіряються finite map SHA, owner/UUID/index, точний accepted source binding і повна basic-проєкція. Змінені/невідомі дані отримують повний попередній fallback.
- Практика, авторські тексти, source packages, робоча БД, курси та окремі тести не змінювалися. Production і main не чіпалися; деплою немає.

Таблиці, summary-list, forms-grid та навігація не перетворені на usage-панелі: вони
використовують власні погоджені компоненти. Шість navigation-only usage blocks
залишені звичайними списками посилань, а не навчальними картками.

## Перевірки

HTTP: усі 42 локальні сторінки повернули 200; фактична кількість нових груп кожної
сторінки збігається з finite map. Перша трійка — 4/8/12 існуючих панелей.
Перший обхід частково застав оновлення hash-bound карти M28–M31; після фіксації
всіх hash pins перші 12 сторінок повторно перевірено — кількості збіглися.

Browser/computer-use: перевірено Complex Relative Clauses, Advanced Conditionals,
Inversion Basics, B1 Mixed Revision та еталон Past Perfect Continuous Forms.
У нових пунктах і еталоні фактичний computed background однаковий:
`color(srgb 0.957548 0.968977 0.984081 / 0.685686)`, border `0px`.
На Relative Clauses перевірено синій, зелений і помаранчевий заголовки.
На Advanced Conditionals пояснення початково закрите, відкривається у власній
групі, авторський текст пояснення видимий. Для B1 перевірено intro-only сценарій.
Це вибіркова візуальна перевірка різних структур, не 42 pixel-perfect порівняння.
Локальний знімок: `storage/app/theory-universal-local/content-groups-relative.jpg`
(приватний runtime-артефакт, не входить у Git).

Ізольовані тести:

- `TheoryContentGroupsTest`: 6 tests, 14544 assertions, exit 0 (1:23.020).
- `TheoryUniversalRolloutTest` + `TheoryUniversalContentViewTest`: 10 tests,
  8622 assertions, exit 0 (3:21.895).
- Разом: 16 tests / 23166 assertions. Кожний прогін показав одне наявне
  PHPUnit deprecation; failures/errors у фінальних прогонах немає.
- Обидва guarded runners завершили after-fingerprint: 46661 захищений файл,
  змін 0; тести використовували ізольовану SQLite, не робочу БД.
- Перевірено 193 оформлені блоки, 376 панелей, точну незмінність дочірніх
  фрагментів і detail objects, M40 intro/named cases, незмінну кінцеву таблицю,
  відмову для чужих/змінених даних, усі 42 owners, 20 details,
  практику та caller-owned межу курсів.

Початковий focused прогін виявив помилкове тестове очікування, що пробіл після
JSON має змінити accepted payload. Existing source guard порівнює decoded дані;
тест виправлено на реальну зміну навчального поля і додано перевірку whitespace
parity. Runtime задля проходження тесту не послаблювався.

## Посилання на всі сторінки

«Пункти» — фактично отримані reference-панелі, не кількість усіх контентних блоків.

| № | Сторінка | HTTP | Пункти |
|---|---|---:|---:|
| 1 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | 200 | 4 |
| 2 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | 200 | 8 |
| 3 | [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 200 | 12 |
| 4 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | 200 | 8 |
| 5 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | 200 | 8 |
| 6 | [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | 200 | 12 |
| 7 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | 200 | 15 |
| 8 | [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | 200 | 15 |
| 9 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | 200 | 11 |
| 10 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | 200 | 9 |
| 11 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | 200 | 12 |
| 12 | [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | 200 | 12 |
| 13 | [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | 200 | 10 |
| 14 | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | 200 | 9 |
| 15 | [Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | 200 | 8 |
| 16 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | 200 | 12 |
| 17 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | 200 | 9 |
| 18 | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | 200 | 11 |
| 19 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | 200 | 10 |
| 20 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | 200 | 9 |
| 21 | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | 200 | 10 |
| 22 | [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | 200 | 12 |
| 23 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | 200 | 13 |
| 24 | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | 200 | 7 |
| 25 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | 200 | 9 |
| 26 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | 200 | 9 |
| 27 | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | 200 | 8 |
| 28 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | 200 | 13 |
| 29 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | 200 | 11 |
| 30 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | 200 | 11 |
| 31 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | 200 | 9 |
| 32 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | 200 | 12 |
| 33 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | 200 | 11 |
| 34 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | 200 | 8 |
| 35 | [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | 200 | 8 |
| 36 | [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | 200 | 9 |
| 37 | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | 200 | 8 |
| 38 | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | 200 | 7 |
| 39 | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | 200 | 11 |
| 40 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | 200 | 6 |
| 41 | [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | 200 | 2 |
| 42 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | 200 | 2 |

## Відкат

Виправлення ізольоване в окремому commit. Для відкату слід revert саме його в робочій
гілці; дані БД не мігрувалися, тому відновлення дампу не потрібне. Не робити reset
брудного ROOT checkout і не включати сторонні зміни/приватні артефакти.
