# Виправлення структури сторінок 1–42 після зауваження користувача

Дата: 2026-10-10. Гілка: `codex/theory-single-reference-template`.
Вихідний commit: `9e00134beef708422a11c01a8a10364f61fdbcb2`.

## Чому потрібен цей follow-up

Попереднє [підключення до канонічного дизайну](theory-passed-pages-design-rollout.md) **не завершило потрібне візуальне виправлення**. Воно підключило спільний шлях і нормалізувало частину inline-оформлення, але залишило дві помітні проблеми з наданих користувачем скриншотів:

- у Complex Relative Clauses звичайне правило, приклад, переклад і коментар перетворювалися на окремі великі сірі картки;
- повні речення у варіантах практики відображалися великими центрованими uppercase-кнопками.

Спільний renderer сам по собі не означає правильну семантичну структуру. Цей прохід виправляє саме ці presentation-помилки, не переписує навчальний матеріал і не створює новий контентний пакет.

## Межі та статус доказів

Scope — **42 UK owners M11–M24 у чинній редакції M27–M40/M42**, рівно сторінки 1–42 попереднього звіту. M41/M43/M44/M45 не входять до цього виправлення. Чотири M26/PPC owners 162–165 залишаються еталоном збереження.

Перед редагуванням main-agent зберіг новий незалежний файловий BEFORE: `file-before-v1.json` у приватному `theory-layout-correction-local`, по 172 presentation-файли ROOT і WT. Це новий snapshot від base вище, не перейменований попередній proof.

За чинною вказівкою користувача робота виконується **тільки з файлами**: без HTTP, браузера, app bootstrap, тестів, БД, міграцій, seed/apply, запуску сервера чи production. Наведені кількості отримані читанням JSON/Blade/PHP як тексту й не є новими DB або browser acceptance results. Live-вигляд цього проходу не перевірявся.

## Встановлена структура джерел

Джерела: [M42 owner registry](../../database/content-patches/m42-native-design-registry.v1.json), accepted `targets[].after` пакетів M27–M40, M39 practice overlay, [публічний реєстр identities](theory-single-reference-registry.json). Для basic/detail прочитані незмінні `plans`. Проєкції та їхні hashes не змінюються заради дизайну.

- На перших 39 owners є **814 безіменних `sections[]`**: 809 у usage-panels та 5 додаткових у comparison-table. В усіх немає змістового `label`; це фрагменти тексту в заданому порядку, а не 814 самостійних правил.
- Додаткові table fragments: Inversion Basics, Page288 — 4; Ellipsis, Page311 — 1. Вони також не мають перетворюватися на картки правил.
- У безіменних sections немає додаткових presentation-полів поза `description/examples/note/label/color`.
- Page189 має **4 справжні іменовані usage-групи** в одному блоці: «Що вже готове?», «Чим ти займався?», «Дія ще триває чи вже зупинилася?», «Тривалий стан і досвід». Їхній panel-дизайн зберігається.
- Решта usage на M40 — intro-only blocks із порожнім `sections`: по два на 189, 280 і 327. Їм не потрібне штучне розбиття на картки.

Кількість fragments описує accepted source shape, не кількість нових уроків, вправ або UI-перевірок.

### Приклад точного source-порядку: Page300

[Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses), розділ «1. Whose: чия річ або чий зв’язок?» містить 8 безіменних фрагментів:

1. Правило: «Whose + іменник» пов’язує особу, організацію або річ з її частиною, належністю чи характеристикою.
2. `We met a potter whose kiln runs on electricity.` — «Ми зустріли гончарку, чия піч працює на електриці».
3. Коментар про опору `potter`, належність `kiln` і підмет `runs`.
4. `The cooperative whose members grow herbs rents this field.` — український переклад.
5. Коментар: `members` — учасники кооперативу; `rents` означає орендує, не володіє.
6. `They repaired the cabinet whose hinges had rusted.` — український переклад.
7. Коментар про `cabinet/hinges`, різницю `whose/who’s` та зайві `his/its`.
8. Примітка, що приклади — власні навчальні ситуації.

Тут немає восьми заголовків груп. Правильна presentation-модель — послідовний текст і окремі EN/UK example-компоненти; авторські коментарі залишаються поруч, у тому самому порядку. Перелік вище є поясненням дефекту; source не переписувався цим переказом.

### Межі розпізнавання прикладів

Окрема пара `<em ...>English.</em> — Українська.` у перевіреному полі може використовувати чинний example-компонент. Змішані fragments з кількома English-елементами, передмовою, формулою, inline-терміном чи коментарем не діляться за припущенням про мову або кінець перекладу. Нерозпізнана структура зберігається повністю як звичайний rich fragment. Не додаються переклади, приклади чи нові detail-блоки.

## Реєстр усіх 42 сторінок

U — безіменні usage fragments; T — безіменні comparison fragments; L — справжні labeled usage panels.
P — повні речення у native options отримують prose-оформлення; I — source має контекст/inline-em для читабельної інструкції й вирівнювання маркера. «Компактні» означає збереження вигляду коротких варіантів, не відсутність практики. Усі native36 мають presentational min-width containment; authored6 не переходять на іншу механіку.

| № | Page ID / сторінка | Пакет | U / T / L | Структура теорії | Практика |
|---:|---|---|---|---|---|
| 1 | 290 · [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | M27 | 5 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 2 | 302 · [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | M27 | 9 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 3 | 315 · [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | M27 | 13 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 4 | 288 · [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | M28 | 11 / 4 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 5 | 289 · [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | M28 | 15 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 6 | 313 · [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | M28 | 19 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P |
| 7 | 297 · [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | M29 | 22 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 8 | 311 · [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | M29 | 29 / 1 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 9 | 312 · [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | M29 | 18 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Компактні |
| 10 | 285 · [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | M30 | 17 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | I |
| 11 | 299 · [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | M30 | 22 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | I |
| 12 | 318 · [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | M30 | 29 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | I |
| 13 | 287 · [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | M31 | 15 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 14 | 294 · [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | M31 | 14 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 15 | 309 · [Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | M31 | 16 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 16 | 291 · [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | M32 | 17 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | I |
| 17 | 301 · [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | M32 | 18 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | I |
| 18 | 307 · [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | M32 | 20 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | I |
| 19 | 293 · [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | M33 | 15 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 20 | 298 · [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | M33 | 14 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 21 | 314 · [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | M33 | 18 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 22 | 310 · [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | M34 | 18 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 23 | 322 · [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | M34 | 28 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 24 | 323 · [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | M34 | 15 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 25 | 296 · [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | M35 | 16 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 26 | 305 · [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | M35 | 19 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 27 | 319 · [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | M35 | 18 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 28 | 303 · [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | M36 | 29 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 29 | 304 · [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | M36 | 27 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 30 | 320 · [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | M36 | 27 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 31 | 286 · [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | M37 | 27 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 32 | 295 · [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | M37 | 31 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 33 | 300 · [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | M37 | 34 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 34 | 306 · [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | M38 | 22 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 35 | 316 · [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | M38 | 24 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 36 | 317 · [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | M38 | 22 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | P + I |
| 37 | 321 · [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | M39 | 28 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Authored, без зміни механіки |
| 38 | 329 · [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | M39 | 30 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Authored, без зміни механіки |
| 39 | 330 · [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | M39 | 38 / 0 / 0 | Безіменні fragments → текст + розпізнані приклади | Authored, без зміни механіки |
| 40 | 189 · [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | M40 | 0 / 0 / 4 | 4 named panels зберігаються; intro-only окремо | Authored, без зміни механіки |
| 41 | 280 · [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | M40 | 0 / 0 / 0 | Intro-only; без штучних panels | Authored, без зміни механіки |
| 42 | 327 · [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | M40 | 0 / 0 / 0 | Intro-only; без штучних panels | Authored, без зміни механіки |

## Реалізація

Файлові правки реалізовано та перенесено точковими hunks до `D:/DEV/htdocs/gramlyze.loc`. **Цей звіт не стверджує, що всі 42 сторінки пройшли нове візуальне приймання.**

`TheoryLegacyAdapter` розрізняє безіменний текстовий fragment та іменовану usage-групу лише для чинного verified M42 plan, правильного UK block UUID і типу. 814 source fragments на 39 owners тепер йдуть через `fragment`/`paragraph`, з незмінними індексами, порядком, прикладами, notes і власниками «Докладніше». П'ять безіменних передмов таблиць також мають paragraph flow та спільні відступи. Чотири іменовані panels Page189 й intro-only M40 не перекласифіковано.

`TheoryHtmlAdapter` оформлює окремий EN—UK приклад лише у exact hash-bound field з підтвердженим EN елементом на початку paragraph/явного double-break segment, явним тире та пунктуацією речення. Формули з +/→, скорочення-терміни й неоднозначні змішані пояснення лишаються повністю доступними. Пунктуація поза EN tag зберігається; переклад, дочірня розмітка та anchors не видаляються. Пара використовує чинний shared example із лівою лінією та окремим UK рядком; у таблиці — прямі paragraph-рядки `table-cell`, без нової картки всередині клітинки.

Native практика використовує optional `prose` у спільному option: normal case, font-medium, text-left, wrap/fullwidth. Короткі chips залишаються попередніми. Scoped instruction wrappers отримали лише `not-italic` для em; marker довгої умови вирівнюється зверху, а контейнер не має зайвого min-width. Original option передається в handlers без перетворення; JS/scoring/reset не редагуються.

Нативна практика: за прочитаними accepted/historical payloads 130 prose options на 22 owners; контекст/inline-em на 27 owners; об'єднання — 28 owners з фактичною типографічною відмінністю. Це source inventory, не новий runtime count. Короткі лексичні варіанти не робляться великими текстовими відповідями лише за довжиною.

## Збереження та обмеження

- Frozen masters, accepted plans, definitions, body/translation text, answers, aliases, tokens, scoring, reset, field/control IDs і linked-bank scope не редагуються.
- Point-level details залишаються на початкових indexes/keys, не переносяться між фрагментами.
- Theme/navigation/sidebar, незалежні тести й courses не є цільовим redesign.
- Дані БД та production не змінювалися. Без live-перевірки немає твердження browser PASS або підтвердження зовнішнього вигляду на сервері.
- Для нової utility-класи виконано лише штатну локальну catalog-public build: CSS `catalog-public-J5S6zCLQ.css`, JS `catalog-public-CgUm4gux.js`; інші manifest entries збережені. Build є кроком застосування стилів, не browser/test acceptance. Generated assets та приватний BEFORE до commit не входять.
- Власні runtime файли: `TheoryLegacyAdapter.php`, `TheoryHtmlAdapter.php`, shared `example.blade.php`, `theory-practice-control.blade.php`, `theory-practice-option.blade.php`, native `practice-set.blade.php`. Також оновлено canonical contract, додано цей report та явне посилання на виправлення в попередньому report. Сторонні PPC hooks у ROOT practice-set збережено; весь checkout не синхронізувався.
- Scoped diff оглянуто як текст. Commit і normal push робочої гілки завершують handoff; full SHA повідомляється після push. Нові автоматичні та візуальні перевірки не запускалися.

