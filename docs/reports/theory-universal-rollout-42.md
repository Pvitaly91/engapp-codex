# Універсальна в’юха: покриття 42 сторінок M11–M24

Дата: 2026-10-11. Гілка: `codex/theory-single-reference-template`.
Базова реалізація універсальної в’юхи: `a962eaebd`; резервний checkpoint перед нею: `a802ca0ae`.

## Результат і фактичний обсяг змін

Попередня реалізація вже підключила всі theory content blocks, а не лише чотири сторінки-еталони. Окреме перемикання кожної з 42 сторінок або пересівання БД не потрібне.

Цей follow-up закріплює **42/42 owners і 296/296 контентних блоків** через `TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.node`:
- у node-гілку спільної в’юхи додано невидимий HTML-коментар `theory.content:node`, без нової обгортки, CSS чи зміни видимого DOM;
- додано `TheoryUniversalRolloutTest` для всіх 42 owner identities, exact source bindings, контентних anchors, meaningful details, практики й non-theory compatibility;
- виконано GET усіх 42 реальних сторінок `gramlyze.loc`;
- створено цей звіт із повним переліком посилань.

Контент: 216 usage-panels, 38 comparison-table, 37 summary-list, 3 forms-grid, 2 mistakes-grid. Окремі 42 hero, 42 practice та 3 navigation blocks навмисно зберігають свої чинні шляхи — вони не є пропущеними контентними блоками.

Навчальні джерела, тексти, basic/detail рішення, CSS, практика, відповідні JS-моделі та робоча БД не змінювалися. Production і main не змінювалися, деплою не було.

## Локальна перевірка

**42 HTTP 200; 296/296 входів у спільну в’юху; 42 сторінки з моделлю практики; 20 point-level disclosures.** Кількість входів зіставлена з очікуваною кількістю контентних блоків кожної сторінки. Не виявлено invalid-native-data або unknown-format markers.

В одного GET `participle-clauses` під паралельним навантаженням був timeout 35 секунд; окремий повтор без паралельного навантаження повернув HTTP 200 за 2,2 секунди. Це не приховано як безпомилковий перший прогін.

HTML-маркер доводить використання в’юхи, а не проходження всіх source guards чи візуальну тотожність різних навчальних структур. Exact binding і ownership окремо перевіряються ізольованими тестами. Нового browser/pixel acceptance у цьому follow-up немає.

## Усі сторінки

Порядок відповідає списку користувача. «Блоки» — фактичні входи у в’юху / очікувана кількість.

| № | Сторінка | HTTP | Блоки | «Докладніше» |
|---|---|---:|---:|---:|
| 1 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | 200 | 7/7 | 3 |
| 2 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | 200 | 8/8 | 7 |
| 3 | [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 200 | 8/8 | 3 |
| 4 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | 200 | 7/7 | 0 |
| 5 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | 200 | 6/6 | 0 |
| 6 | [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | 200 | 7/7 | 0 |
| 7 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | 200 | 8/8 | 0 |
| 8 | [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | 200 | 9/9 | 0 |
| 9 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | 200 | 8/8 | 0 |
| 10 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | 200 | 7/7 | 0 |
| 11 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | 200 | 7/7 | 0 |
| 12 | [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | 200 | 7/7 | 0 |
| 13 | [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | 200 | 7/7 | 0 |
| 14 | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | 200 | 7/7 | 1 |
| 15 | [Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | 200 | 7/7 | 1 |
| 16 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | 200 | 7/7 | 0 |
| 17 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | 200 | 7/7 | 1 |
| 18 | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | 200 | 7/7 | 0 |
| 19 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | 200 | 7/7 | 0 |
| 20 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | 200 | 7/7 | 1 |
| 21 | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | 200 | 7/7 | 1 |
| 22 | [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | 200 | 7/7 | 1 |
| 23 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | 200 | 7/7 | 0 |
| 24 | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | 200 | 7/7 | 1 |
| 25 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | 200 | 7/7 | 0 |
| 26 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | 200 | 7/7 | 0 |
| 27 | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | 200 | 7/7 | 0 |
| 28 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | 200 | 7/7 | 0 |
| 29 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | 200 | 7/7 | 0 |
| 30 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | 200 | 7/7 | 0 |
| 31 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | 200 | 7/7 | 0 |
| 32 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | 200 | 7/7 | 0 |
| 33 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | 200 | 7/7 | 0 |
| 34 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | 200 | 7/7 | 0 |
| 35 | [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | 200 | 7/7 | 0 |
| 36 | [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | 200 | 7/7 | 0 |
| 37 | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | 200 | 7/7 | 0 |
| 38 | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | 200 | 7/7 | 0 |
| 39 | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | 200 | 7/7 | 0 |
| 40 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | 200 | 8/8 | 0 |
| 41 | [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | 200 | 6/6 | 0 |
| 42 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | 200 | 4/4 | 0 |

## Межі спільного шаблону

Спільна в’юха й компоненти визначають дизайн, але різні навчальні структури не стають однаковими за кількістю пунктів. Уже погоджені finite whole-point mappings перших трьох уроків збережені. Решта безіменних абзаців не перетворюється автоматично на окремі framed panels; нові labels або basic/detail mappings не вигадувалися. Невідомий trusted legacy HTML і надалі має повний compatibility fallback. Цей звіт не заявляє окремої авторської перегруповки решти 39 уроків.

## Ізольовані тести

Пройшли `TheoryUniversalRolloutTest`, `TheoryUniversalContentViewTest`, `TheoryCanonicalTemplateTest` і `M42NativeDesignPackageTest`: **258 tests, 33213 assertions, exit 0**, одне повідомлення PHPUnit deprecation. Час PHPUnit: 5 хв 39,561 с. Новий тест перевіряє кожний із 296 блоків окремо: точний UUID/body/plan, один вхід у в’юху, власний section ID, відсутність втрати anchors і незмінність переданих даних. Для details перевірено власника, закритий початковий стан та fragment IDs. Для практики — точний passthrough і незмінні raw models усіх 42 блоків. Non-theory compatibility перевірена для всіх 14 пакетів.

Прогін використовував SQLite in-memory й окремий runtime/storage/views/cache, без робочого `.env` та без HTTP або робочої БД з тестів. HTTP-перевірка вище виконувалася окремо.

Після прогону контроль 46661 захищеного файла показав **0 змін**. Private receipt: `storage/app/seo-m2-local/theory-universal-rollout-v1-e78bf8fcc7064486b7a275e1ed90f87d-result.json` (не комітиться).
