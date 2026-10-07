# M42 — M11–M24 у погодженій native-візуальній системі M41

Статус: **локальне M42 приймання завершено із застереженнями, 2026-10-07 (Europe/Kiev)**. Presentation-only реалізацію застосовано до рівно **42/42** погоджених уроків. Незалежний BEFORE збережено; **168/168 primary states**, особистий all-section BEFORE/AFTER перегляд **42/42**, 42 triples no-JS/320/enlargement, 32 reference/control states, final **148/148 GET** і strict DB proof **0 updated / 0 inserted / 0 deleted** завершені. Inherited visual/zoom diagnostic warnings і три підтверджені baseline test failures явно збережено: це не твердження «всі історичні тести PASS». Цей versioned звіт фіксує pre-commit acceptance; точний commit/push SHA після normal handoff буде наведено у фінальному повідомленні після remote verification.

## Межі, робоча база та фактична локальна ціль

- Репозиторій: `Pvitaly91/engapp-codex`.
- Погоджений native-reference і фактичний Git base: `053c6ddbe4b42b23f724ec336fdf472b6d219215`, гілка `codex/seo-m41-preserve-existing-design`.
- Робоча гілка M42: `codex/seo-m42-m11-m24-native-design`.
- Робочий worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Фактично served application ROOT: `D:/DEV/htdocs/gramlyze.loc`, DocumentRoot: `D:/DEV/htdocs/gramlyze.loc/public`; live URL тільки `http://gramlyze.loc`.
- Served ROOT має інший Git HEAD: `41820a2bebdf69004fa7209a2a38457f93efabbd`. Це dirty checkout з наявними сторонніми доробками. Його не прирівнювали до worktree, не виконували reset/clean та не перезаписували shared-файли цілком зі старого коміту.
- Read-only physical proof: MySQL `localhost:3306`, database `gr2`; APP environment `production`, ефективний SiteMode для `.loc` — `development`.
- Header, sidebar, фон, layout, global typography, standalone test дизайн, frozen masters, accepted content payloads, definitions, test banks, aliases/scoring/progress keys та БД не є об’єктом редагування.
- Немає дозволу або виконаного деплою, PR, merge, main change, force push, workflow dispatch чи перевірки production `.com/.ub`. Сідери, міграції, повторне M11–M40 apply та очищення кешу не запускалися.

ROOT source backup directories: `source-sync-before-v1` і `source-sync-mixed-before-v1` у приватному локальному evidence scope. Fresh `source-sync-proof-v3.json`, SHA `225ff33eebaf325edbc6d7f185715eba8c7de80fe2c4ec78318f167e770e4714`, підтверджує 9 shared + 4 new served-source paths. Existing foreign зміни `theory/show` збережено byte-exact, крім однієї нової caller-owned UK opt-in строки. `tools/diagnostics/verify-m42-root-source-sync.cjs` перевіряє файли read-only, без DB або HTTP requests. Served runtime freeze підтверджений у всіх final browser matrices; staged diff/bytes та remote SHA — окремі наступні Git handoff checks.

## Незалежні BEFORE докази

Приватний каталог доказів: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local`. Дані, скриншоти, runtime output та DB snapshots не входять до Git.

| Доказ | Файл | SHA-256 / результат |
|---|---|---|
| Фактичний vhost/application/runtime | `physical-target-before-v1.json` | `ac280272187fe6f30f6ac0cc40907642b3945dacc631e8b6722ae8be7ad805da` |
| Fresh read-only DB BEFORE | `m42-design-before-v2.json` | `1d2245812afde06ce56ddcb994661015bb87274d6a11b8272bd3922468bc6861` |
| Повний guest GET BEFORE | `before-v3-http.json` | `f4f4992287c75e6827e54962b5c025fb54c49083d2a9717fdd0d1cdd989f073c`; 148/148 |
| Незалежний навчальний DOM BEFORE | `browser-before-v2-semantic.json` | `60c059b246e7af053e624c5e54dc6e9dd9ccc8d0f55066e798e69bca03489794`; 42/42 |
| Об’єднаний повний screenshot BEFORE | `browser-before-complete-v1-before-complete.json` | `a37901d9f90eb4f51e1e65bf75f53874a44977f0a9403610ff9f2e0746265515`; 42 lesson rows, 380 screenshot references |
| M41/non-target/shared browser BEFORE | `browser-controls-complete-before-v1-controls.json` | `f7e76c5770e3bc3a4e1b00488e779057e79906fd4487c8ba810ed93d0238758d`; 32 control states |

Screenshot baseline зібрано з успішних незалежних до-змінних rows `browser-before-v1-pages.json` і `browser-before-v2-pages.json`, рівно по одному повному lesson BEFORE на identity. Невдалі спроби не видалено; AFTER не використовується як власний expected baseline. MAIN особисто переглянув усі 42 уроки, кожний повний all-section BEFORE/AFTER contact до footer; це зафіксовано у `main-personal-visual-review-v2.json`, а не виведено з факту наявності PNG.

HTTP inventory: 42 UK targets + 3 M41 references + 90 EN/PL locale paths + 9 shared HTML routes + 4 extras (`robots`, `sitemap`, `health`, development mode) = 148 реальних GET. Запити виконано як гостя, без authorization/cookies/Referer. Зафіксовано status, redirects/final URL, Content-Type, X-Robots-Tag, meta robots, canonical, title/H1, description, JSON-LD, anchors і навчальні компоненти.

DB BEFORE охоплює 46 raw tables, 6 563 `text_blocks`. Цільові 42 owners мають 425 UK rows і 443 all-locale rows; 3 окремі M41 reference owners — 30 UK / 80 all-locale rows. Non-42 rows — 6 120. Source registry містить 383 source blocks; різниця 425–383 — 42 збережені subtitle rows, а не приховано додані уроки чи втрачені source blocks.

## Точний реєстр і поурочне оформлення

Реєстр відновлено з accepted M11–M24 identities і поточних M27–M40 projections/reports, а не з довільного `/theory` crawl. M27/M28/M29 використовують v2 quality follow-up; M39 накладає accepted `m39-practice-ui.v1.json` на поточний M39 author package. Три уроки M41 не входять до наведених 42.

У колонці «Blocks BEFORE» спочатку source configs, потім фактичні UK DB rows (включно з subtitle). Числа details означають лише навчальні point-level поглиблення, не ключі відповідей. Exercises — 6 власних авторських завдань на сторінку; controls враховують compound частини, але не випадкове число питань linked-bank widget. Для кожного з 42 рядків live AFTER підтвердив exact source/component/content/detail/practice equality у всіх чотирьох viewport/theme states, а заключний read-only DB proof підтвердив незмінність наведених counts і кожного source row. **BEFORE = AFTER для кожного числового рядка таблиці.**

| № | Етап → пакет | Локальний canonical URL / урок | Blocks BEFORE source / UK DB | Details BEFORE | Tasks / controls BEFORE | Presentation-only виправлення |
|---|---|---|---:|---:|---:|---|
| 1 | M11 → M27 v2 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | 9 / 10 | 3 | 6 / 6 | Cause/result/contrast usage-panels отримують змістові акценти; збережено 4-колонкову карту конструкцій і summary-list помилок без guessed ❌. |
| 2 | M11 → M27 v2 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | 10 / 11 | 7 | 6 / 6 | Розрізнено поступку, умову, межу твердження та застосування в абзаці; English blockquote й переклад лишаються в source order; дві UK формульні em exceptions не перетворюються на English text. |
| 3 | M11 → M27 v2 | [Concessive and Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 10 / 11 | 3 | 6 / 6 | Видимі контрастні panels для even though/even if, while/whereas та скорочених допустових конструкцій; власні 3 details залишаються біля своїх пунктів. |
| 4 | M12 → M28 v2 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | 8 / 9 | 0 | 6 / 6 | Comparison-table фокусу збережено; it/what-cleft пояснення й EN конструкції оформлено native, застереження часу/узгодження видимі; zero details не доповнюються штучно. |
| 5 | M12 → M28 v2 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | 9 / 10 | 0 | 6 / 6 | Auxiliaries/do/does/did table має власний scroll; заперечні початки, only і not only зберігають окремі usage-panels; стрілки не породжують нових error pairs. |
| 6 | M12 → M28 v2 | [Advanced Fronting and Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | 9 / 10 | 0 | 6 / 6 | Порівняння трьох механізмів не згорнуто до двох колонок; object/fronting/locative пояснення мають різні семантичні акценти, межі моделі залишено в basic. |
| 7 | M13 → M29 v2 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | 10 / 11 | 0 | 6 / 6 | Контраст двох правильних фокусів лишається нейтральним comparison/usage content; приклади мінітексту й warning panels зберігають повний текст, zero details. |
| 8 | M13 → M29 v2 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | 10 / 11 | 0 | 6 / 6 | Карта компонентів noun phrase, розбори head/modifiers та неоднозначного приєднання збережені; формули акцентовано без monospace всього UK пояснення. |
| 9 | M13 → M29 v2 | [Ellipsis, Substitution and Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | 11 / 12 | 0 | 6 / 6 | Повний набір ellipsis/so/do/one/reference panels і абзац збережено; UK placeholder `don't + дієслово + so` має explicit exception; жодного приховування коротких правил. |
| 10 | M14 → M30 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | 9 / 10 | 0 | 6 / 6 | Актив/-ing і V3 пояснення, означальний table та subject warning оформлено чинними components; приклад помилкового приєднання не змінює accepted текст. |
| 11 | M14 → M30 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | 9 / 10 | 0 | 6 / 6 | Table форм збережено; simultaneity/reason/accompanying/result, having і заперечення не змішуються в один article; source абзац і переклад лишаються повними. |
| 12 | M14 → M30 | [Advanced Participle and Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | 9 / 10 | 0 | 6 / 6 | Absolute/ordinary clauses comparison збережено; власний subject, with, comma splice і межа речення отримують caution accents без перетворення всіх альтернатив на помилки. |
| 13 | M15 → M31 | [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | 9 / 10 | 0 | 6 / 6 | Три способи залежності залишено таблицею; unless caution відокремлено від constructive future/permission panels; practice має ті самі six required answers. |
| 14 | M15 → M31 | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | 9 / 10 | 1 | 6 / 6 | Часові фокуси збережено в comparison-table; would/could/might не позначено wrong/right; власний meaningful detail і bilingual paragraph залишилися незмінними. |
| 15 | M15 → M31 | [Conditional Alternatives and Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | 9 / 10 | 1 | 6 / 6 | Provided/assuming/otherwise/but for мають distinct purpose accents; should/were/had table зберігає всі рядки; єдиний detail не переміщено до спільного disclosure. |
| 16 | M16 → M32 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | 9 / 10 | 0 | 6 / 6 | Register comparison, constructive nominalisation та caution щодо formal verbs оформлено native; збережено 3 choice + 3 manual tasks, без вигаданого select group. |
| 17 | M16 → M32 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | 9 / 10 | 1 | 6 / 6 | П’ять кроків і status/agent table мають виразні правила й рамки; повний paragraph/detail не скорочено; всі 6 завдань лишаються manual. |
| 18 | M16 → M32 | [Register, Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | 9 / 10 | 0 | 6 / 6 | Точний paraphrase і summary не отримують false correction icons; контроль значення, tone та caution panels розрізнено; 3 choice + 3 manual mechanics збережені. |
| 19 | M17 → M33 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | 9 / 10 | 0 | 6 / 6 | Факт/пояснення/тенденція, cautious constructions і межі порівняння оформлено native; table та прохання-відмінне-від-припущення мають власні accents. |
| 20 | M17 → M33 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | 9 / 10 | 1 | 6 / 6 | Силу висновку й заперечення не уніфіковано в сіру панель; seem/suggest/indicate/prove та абзац збережено, meaningful detail залишено point-level. |
| 21 | M17 → M33 | [Stance, Register and Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | 9 / 10 | 1 | 6 / 6 | Критерій, reporting verbs, evidence limit і evaluation мають явну palette роль; усі table notes та власне поглиблення збережені. |
| 22 | M18 → M34 | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | 9 / 10 | 1 | 6 / 6 | Argument structure table, constructive paragraph і caution щодо broad conclusions не переписуються; example/translation та detail ownership незмінні. |
| 23 | M18 → M34 | [Discourse Markers and Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | 9 / 10 | 1 | 6 / 6 | Function map збережено; contrast/addition/consequence/contextual action розрізнено accents, punctuation warnings видимі; один meaningful detail не поширено на весь блок. |
| 24 | M18 → M34 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | 9 / 10 | 0 | 6 / 6 | Таблиця функції другої версії, контрольовані редакції мінітексту та checklist не стають guessed mistakes-grid; усі версії й source order залишено. |
| 25 | M19 → M35 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | 9 / 10 | 0 | 6 / 6 | Reporting/event time table і дві моделі оформлено окремо; warning щодо механічного часу та керування не приховується; bilingual examples читаються без обрізання. |
| 26 | M19 → M35 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | 9 / 10 | 0 | 6 / 6 | Causative/Past Perfect/get-passive різниця лишається змістовним comparison; форми, питання, план і небажана подія мають native borders/colors, не нові error labels. |
| 27 | M19 → M35 | [Complex Passive and Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | 9 / 10 | 0 | 6 / 6 | Два дієслівні рівні й active/passive time matrix збережено; заперечення та misleading been видимі caution panels; ні часу, ні voice у прикладах не змінено. |
| 28 | M20 → M36 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | 9 / 10 | 0 | 6 / 6 | Формули modal+have+V3, known/inferred table й could-have/should-have contrasts оформлено native; algorithm/error cautions залишено в basic. |
| 29 | M20 → M36 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | 9 / 10 | 0 | 6 / 6 | Базова that-clause, active/passive table, suggest/insist і regional alternatives збережені; formal expressions не сховані за штучними details. |
| 30 | M20 → M36 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | 9 / 10 | 0 | 6 / 6 | Advice, plausible explanation та necessity/execution не змішуються; dialog і caution щодо sense substitutions отримують різні role accents без aliases rewrite. |
| 31 | M21 → M37 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | 9 / 10 | 0 | 6 / 6 | Verb patterns, to contrast, remember та stop/try table збережено; словесні конструкції монопросторові тільки в verified EN fragments, checklist повний. |
| 32 | M21 → M37 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | 9 / 10 | 0 | 6 / 6 | Whose/preposition/comma/reference panels розрізнено; role/omission table не губить колонок і notes; дві правильні версії не стають ❌/✅. |
| 33 | M21 → M37 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | 9 / 10 | 0 | 6 / 6 | Hardly/no sooner timing table, main-clause inversion і scope of only/not only збережено; statement/prohibition/frequency contrasts мають нейтральний native вигляд. |
| 34 | M22 → M38 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | 9 / 10 | 0 | 6 / 6 | Countability, few/little matrix, article reference й institutional limits мають різні accents; source note і всі six controls незмінні. |
| 35 | M22 → M38 | [Precision with Articles and Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | 9 / 10 | 0 | 6 / 8 | Reference/subgroup table і not-all/none contrasts збережені; 2 додаткові semantic-choice controls compound tasks не губляться через стилізацію. |
| 36 | M22 → M38 | [Advanced Collocation and Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | 9 / 10 | 0 | 6 / 7 | Collocation role table, evidence/scale пояснення й before/after note залишено в order; manual + додаткова semantic choice зберігають exact scoring. |
| 37 | M23 → M39 UI | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | 9 / 10 | 0 | 6 / 9 | Status/agent table, head-word examples та paragraph editing мають native accents; accepted M39 UI select/choice/manual/multi і compound parts лишаються guard-valid. |
| 38 | M23 → M39 UI | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | 9 / 10 | 0 | 6 / 6 | Context map, conditional/inversion/passive/modal examples збережено; English/UK пари підкреслено без перетворення цього уроку на M41 текст; 6 manual tasks незмінні. |
| 39 | M23 → M39 UI | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | 9 / 10 | 0 | 6 / 8 | Negation/necessity/reporting contrasts лишаються правильними альтернативами; UK formula `had + підмет + V3` має explicit exception; manual/choice compound controls збережено. |
| 40 | M24 → M40 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | 11 / 12 | 0 | 6 / 10 | Original comparison-table, forms-grid, mistakes-grid, summary-lists і navigation збережено; додані authored usage/practice вбудовано в цю ж palette без заміни різних components одним article. |
| 41 | M24 → M40 | [Narrative Tenses](http://gramlyze.loc/theory/tenses/narrative-tenses) | 9 / 10 | 0 | 6 / 9 | Три часові форми, narrative role table, explicit errors і summary-list зберігають свою структуру; story і required compound task parts повні. |
| 42 | M24 → M40 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | 7 / 8 | 0 | 6 / 13 | Summary-list шести напрямів і forms-grid чотирьох перевірок не скорочені; дві source usage секції та 13 select/manual controls оформлено без нового каркаса. |

Підсумок **BEFORE = AFTER**: **42 identities / 383 source blocks / 425 UK DB rows / 443 all-locale rows / 20 навчальних details / 252 own tasks / 274 controls**. Source configurations і DB counts не підміняються одне одним. Усі **42/42** сторінки потребували адресного відновлення native оформлення, **0 цілих сторінок** класифіковано checked-without-changes; hero/header/sidebar/navigation є збереженими нецільовими частинами, а не приводом вважати цілий урок незміненим.

### Identity, definition, локалі та поточний source

Повний seeder prefix для наведених суфіксів — `Database\Seeders\Page_V3\`. Exact definition path для кожного рядка — `database/seeders/Page_V3/` + наведений identity suffix із `/` замість `\` + `/definition.json`; це literal mapping з registry, а не приблизний slug guess. Порядок і типи кожного з 383 блоків, UUID, heading/column/level/body hashes, owner index та source path зафіксовані у versioned `database/content-patches/m42-native-design-registry.v1.json`.

| № | Exact identity suffix |
|---|---|
| 1 | `ClausesAndLinkingWords\LinkingWordsReasonResultContrastTheorySeeder` |
| 2 | `ClausesAndLinkingWords\AdvancedLinkingDevicesTheorySeeder` |
| 3 | `ClausesAndLinkingWords\ConcessiveAndContrastiveStructuresTheorySeeder` |
| 4 | `SentenceStructure\CleftSentencesBasicsTheorySeeder` |
| 5 | `BasicGrammar\WordOrder\InversionBasicsTheorySeeder` |
| 6 | `BasicGrammar\WordOrder\AdvancedFrontingAndEmphasisTheorySeeder` |
| 7 | `SentenceStructure\CleftSentencesEmphasisTheorySeeder` |
| 8 | `SentenceStructure\ComplexNounPhrasesTheorySeeder` |
| 9 | `SentenceStructure\EllipsisSubstitutionAndReferenceTheorySeeder` |
| 10 | `ClausesAndLinkingWords\ParticipleClausesBasicsTheorySeeder` |
| 11 | `ClausesAndLinkingWords\ParticipleClausesTheorySeeder` |
| 12 | `ClausesAndLinkingWords\AdvancedParticipleAndAbsoluteClausesTheorySeeder` |
| 13 | `Conditionals\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder` |
| 14 | `Conditionals\AdvancedConditionalsTheorySeeder` |
| 15 | `Conditionals\ConditionalAlternativesAndNuanceTheorySeeder` |
| 16 | `FormalEnglish\FormalRegisterAndNominalisationBasicsTheorySeeder` |
| 17 | `FormalEnglish\NominalisationFormalRegisterTheorySeeder` |
| 18 | `FormalEnglish\RegisterToneAndParaphraseTheorySeeder` |
| 19 | `AcademicEnglish\HedgingAndCautiousLanguageBasicsTheorySeeder` |
| 20 | `AcademicEnglish\HedgingAndCautiousLanguageTheorySeeder` |
| 21 | `AcademicEnglish\StanceRegisterAndEvaluationTheorySeeder` |
| 22 | `AcademicEnglish\ArgumentationAndAcademicToneTheorySeeder` |
| 23 | `ClausesAndLinkingWords\DiscourseMarkersAndCohesionTheorySeeder` |
| 24 | `FormalEnglish\ParaphraseAndReformulationTheorySeeder` |
| 25 | `PassiveVoice\PassiveReportingStructuresTheorySeeder` |
| 26 | `PassiveVoice\ComplexPassiveAndCausativeTheorySeeder` |
| 27 | `PassiveVoice\ComplexPassiveImpersonalStyleTheorySeeder` |
| 28 | `ModalVerbs\ModalPerfectAndDeductionTheorySeeder` |
| 29 | `FormalEnglish\SubjunctiveAndFormalStructuresTheorySeeder` |
| 30 | `ModalVerbs\SubtleModalMeaningsTheorySeeder` |
| 31 | `VerbPatterns\AdvancedGerundInfinitivePatternsTheorySeeder` |
| 32 | `RelativeClauses\ComplexRelativeClausesTheorySeeder` |
| 33 | `BasicGrammar\WordOrder\InversionAfterNegativeAdverbialsTheorySeeder` |
| 34 | `ArticlesAndQuantifiers\AdvancedArticleAndQuantifierNuanceTheorySeeder` |
| 35 | `ArticlesAndQuantifiers\PrecisionWithArticlesAndDeterminersTheorySeeder` |
| 36 | `VocabularyAndCollocations\AdvancedCollocationAndLexicalChoiceTheorySeeder` |
| 37 | `FormalEnglish\NominalStyleAndInformationDensityTheorySeeder` |
| 38 | `BasicGrammar\C1MixedRevisionTheorySeeder` |
| 39 | `BasicGrammar\C2MixedRevisionTheorySeeder` |
| 40 | `Tenses\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder` |
| 41 | `Tenses\TensesNarrativeTensesTheorySeeder` |
| 42 | `BasicGrammar\BasicGrammarB1MixedRevisionTheorySeeder` |

У 41 owner є тільки `uk` DB content. Owner №40 має `en`, `pl`, `uk`. EN/PL route fallback для інших 41 не вважається створеним перекладом: content/locale settings збережено, requested locale не одержує M42 UK styling hook. Shared native/category/course/test browser regressions закриті у 20 non-target states; final 148 GET і full raw-table equality підтвердили також усі локалі/content settings без винятків.

| Original stage | Поточний immutable projection (`database/content-patches/`) |
|---|---|
| M11 | `m27-m11-linking-words.v2.json` |
| M12 | `m28-m12-emphasis-inversion.v2.json` |
| M13 | `m29-m13-sentence-structure.v2.json` |
| M14 | `m30-m14-participle-clauses.v1.json` |
| M15 | `m31-m15-conditionals.v1.json` |
| M16 | `m32-m16-formal-english.v1.json` |
| M17 | `m33-m17-academic-english.v1.json` |
| M18 | `m34-m18-argumentation-cohesion.v1.json` |
| M19 | `m35-m19-passive-reporting.v1.json` |
| M20 | `m36-m20-modals-subjunctive.v1.json` |
| M21 | `m37-m21-grammar-structures.v1.json` |
| M22 | `m38-m22-articles-collocations.v1.json` |
| M23 | `m39-m23-authored-revision.v1.json` + `m39-practice-ui.v1.json` |
| M24 | `m40-m24-tenses-b1.v1.json` |

## Реалізація та гарантії збереження

Використано `app/Support/M42NativeDesignPackage.php`, SHA-bound registry і нову metadata-only palette/annotation mapping. Усі source block types зберігаються: `hero`, `usage-panels`, `comparison-table`, `summary-list`, `practice-set`, а для M40 також `forms-grid`, `mistakes-grid`, `navigation-chips`. Hero/navigation рендерить чинний `theory/show`, не native block dispatcher. Жоден rich inline фрагмент не змінює DB type чи whitelist view; raw legacy box серед цих 42 current packages не лишився.

Existing M27–M41 guards не послаблено і не скопійовано в інші author packages. M42 rebind перевіряє exact owner, UK block locale, UUID, type, order, column, heading, level, css_class і повне decoded data. Prior presentation data/points/fragments must match owning existing package. M42 додає **тільки top-level** `m42_native_design`; всередині data нічого не вставляється, отже повторні M39/M40 practice guards залишаються valid. Foreign/mutated/non-theory data отримує повний existing fallback, а не прихований або порожній basic.

Scope opt-in надходить від caller `theory/show` і requested UK locale. Інші уроки, category/course/test paths та локалі не активуються лише через `/theory` URL або marker у БД.

Palette roles визначено скінченно для кожної accepted identity/slot: core explanation blue, constructive application emerald, comparison sky, caution/limit amber, source-explicit mistake warning rose, unchanged hero/navigation. Чинні meaningful native section colors мають пріоритет. Стрілка чи два різні правильні речення не перетворюють source type на mistakes-grid; однакової кількості секцій або disclosures не нав’язується.

M25 conflict усунуто scoped inline CSS тільки у `.theory-design .m42-native-design[data-m42-native-design]`. Header/sidebar/layout/global typography і compiled CSS/JS не змінюються. Hashed assets вручну не редагуються, залежності не оновлюються; штатна asset build для цього inline-only шару не потрібна.

Фінальний application freeze v4: `resources/views/engram/theory/blocks-v3/m42-native-design-styles.blade.php`, raw SHA `a2dd8f08025a7867b81b23b2fe247dd53352952a3d8e0413ee3a097ac064a3a8`. Виявлені локальні specificity конфлікти native forms і undefined `--surface-2` виправлено адресно; background fallback — `var(--surface-2,var(--surface))`. Це не глобальна зміна M25/M41 variables або M41 reference design. Усі старі AFTER спроби перед цим freeze зберігаються як diagnostic/superseded, а не фінальне visual acceptance.

Native приклади, формули, переклади, таблиці, notes, всі source columns/rows і повні summary списки зберігаються. Rich em helper додає лише verified attributes до original opening tags, без DOM serialization: всі інші HTML/text/link/order bytes лишаються exact. Metadata містить allowed field hashes для stored full/basic та 20 existing detail fragments. Unknown pointer/modified fragment/forged plan повертає original HTML. Чотири explicit UK formula exceptions:

- Advanced Linking Devices, slot 5: `/sections/0/description`, em indices 0/1.
- Ellipsis, slot 3: `/sections/3/description`, em index 2.
- C2 Mixed Revision, slot 2: `/sections/4/description`, em index 2.

Це не runtime language detector або threshold за довжиною. Educational data, exact detail IDs/type/value, accepted answers/aliases, compound scoring, reset і token banks не переписуються.

Нові frozen-after-review metadata SHA:

- `m42-native-design-registry.v1.json`: `49f1cab8eebaa4c12216ba4df96ed6e8ba875fcd3b7d21fceced2e07630e09b6`.
- `m42-native-design.v1.json`: `fea2c1d8999a237e58eb039579d4e20b23b867439bd29256f3ba79d216905ec0`.
- Для обох JSON додано explicit `-text` у `.gitattributes`. Read-only binary `git show :<path>` staged bytes check — exit 0: registry `49f1cab8eebaa4c12216ba4df96ed6e8ba875fcd3b7d21fceced2e07630e09b6` і mapping `fea2c1d8999a237e58eb039579d4e20b23b867439bd29256f3ba79d216905ec0` exact; staging не змінив hash-bound байти.

### Адресне виправлення 16 mixed EN/UK fields за independent review

Review виявив presentation P2: у семи structured correct fields цілий bilingual рядок одержував `lang=en`/English monospace, включно з UK перекладом; дев’ять forms subtitles залишали English example і UK пояснення пласким рядком. Виправлення стосується тільки двох exact M40 owners — Present Perfect vs Present Perfect Continuous і Narrative Tenses — source slot 2 `forms-grid` (6+3 subtitles) та source slot 4 `mistakes-grid` (4+3 right fields). B1 Mixed Revision pure-UK subtitles не зачіпаються.

Скінченні literal English selectors перевіряють одну точну появу в accepted source. Versioned metadata зберігає лише SHA та UTF-8 byte offsets/lengths/roles: optional UK formula context, English example, original separator+UK translation. Розбиття не виводиться runtime-евристикою з тире чи мови. Original `✅ ` збережено в English range, щоб glyph і речення не роз’їжджалися на різні рядки. Нові spans з `lang=en`/`lang=uk` змінюють тільки presentation; data/payload, source bytes після strip markup, punctuation, порядок, glyphs, links, aliases і scoring незмінні. Unknown/modified field або forged plan повертає existing original content.

Додано 16 parameterized tests з actual native renderer: exact source/DOM text та order, verified English substring, translation поза English/font-mono ancestors, unchanged data, tamper/unknown/fallback. Перевірені до цієї правки 28 AFTER states залишаються superseded diagnostic attempts. Після final source/style freeze всю основну матрицю перезапущено: **168/168 unique states, 42/42 уроків**, runtime CSS `a2dd8f…` і mapping `fea2c1…` однакові в кожному прийнятому state.

## Fresh автоматичні перевірки

Ізольовані PHP перевірки виконуються через `tools/diagnostics/run-isolated-tests.py`: SQLite `:memory:`, private runtime/view/cache/session paths, working MySQL не використовується, source bank files та робочі caches fingerprinted. Історичні PASS M41 не враховуються як новий запуск M42.

| Fresh перевірка | Результат | Evidence / застереження |
|---|---|---|
| M42 all42 source guards, actual native dispatchers, complete text/controls/anchors, 20 details, rich annotations/4 exceptions + 16 mixed EN/UK fields | 237 tests / 24 074 assertions, exit 0 | `m42-native-design-v6-2d37bf3383584c208c55d403d72c0912-result.json`; protected 46 661 files, changes 0; 1 existing PDO deprecation; 1:53.510 / 82 MB |
| M42 read-only evidence verifier | 87 tests / 89 assertions, exit 0 | `m42-db-evidence-v1-323c081d2ec64994b516644a0785f5df-result.json`; protected 46 661 files, changes 0 |
| Ізольовані production-profile / SEO / canonical regressions | 38 tests / 1 265 assertions, exit 0 | `m42-isolated-production-profile-v1-6449836722ba4ccba09796d8adc5c0ec-result.json`; protected 46 661 files, changes 0; жодного мережевого запиту до production |
| Node HTTP comparison contract | 345/345, exit 0 | `tests/Browser/m42-design-http.test.cjs` |
| Final Node practice/regression suite + style-policy | 1 169/1 169, exit 0 | 1 124 curated practice/regression + 45 style-policy; `m42-node-regressions-style-final-v5.log`, raw SHA `a586be327959a3128cf62e9e897ba25d7ec892273d24349919ffec0991f49ae1`, 2 844.5544 ms |
| Final Node browser acceptance harness | 45/45, exit 0, 21 554.8876 ms, 0 failures/skipped | `browser-final-diagnostic-tests-v1.json`, SHA `260e175a62ded49668874dab06d100ef4b36865639d07746e2667c74001bd2c7`; це synthetic diagnostic contracts, не заміна live states; ранні 44 tests — pre-adapter result |
| Existing combined PHP regression v3 (26 files) | 297 tests / 21 681 assertions; exit 1; 3 failures / 0 errors; 1 deprecation | **Не PASS.** Manifest environment errors усунуто штатною WT build; три попередні fixture/EOL assertions описано нижче; protected files changes 0 |
| Relevant Vitest | 61 tests / 8 files, exit 0 | `node node_modules/vitest/vitest.mjs run --reporter=dot`; source output evidence retained by MAIN |
| Штатна WT Vite build для missing-manifest test fixture | exit 0 | `node node_modules/vite/bin/vite.js build`; ROOT assets не копіювалися, build artifacts не комітяться |

Точні виконані команди (cwd — worktree; наведені PowerShell executables):

```powershell
& 'C:/Program Files/xampp/php/php.exe' tools/diagnostics/project-m42-registry.php --check
& 'C:/Program Files/xampp/php/php.exe' tools/diagnostics/project-m42-native-design.php --check
& 'C:/Program Files/xampp/php/php.exe' -l app/Support/M42NativeDesignPackage.php
& 'C:/Program Files/xampp/php/php.exe' -l tests/Feature/M42NativeDesignPackageTest.php
& 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe' tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m42-native-design-v6 tests/Feature/M42NativeDesignPackageTest.php
& 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe' tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m42-db-evidence-v1 tests/Feature/M42DesignEvidenceTest.php
& 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe' tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m42-isolated-production-profile-v1 tests/Feature/SiteModeTest.php tests/Feature/SeoRobotsTest.php tests/Feature/CanonicalUrlTest.php tests/Feature/ResolvedLearningPageSeoTest.php tests/Feature/TheoryCanonicalLessonUrlTest.php
& 'C:/Program Files/nodejs/node.exe' --test tests/Browser/m42-design-http.test.cjs
& 'C:/Program Files/nodejs/node.exe' --test --test-reporter=spec tests/Browser/seo-m42-local.test.cjs
& 'C:/Program Files/nodejs/node.exe' node_modules/vitest/vitest.mjs run --reporter=dot
& 'C:/Program Files/nodejs/node.exe' node_modules/vite/bin/vite.js build
```

Exact final Node command (WT cwd; curated список не є запуском усіх довільних Browser scripts):

```powershell
$env:NODE_PATH = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules'
$nodeCases = @(
  'tests/Browser/m42-native-style-policy.test.cjs',
  'tests/Browser/m27-theory-practice.test.cjs',
  'tests/Browser/m28-theory-practice.test.cjs',
  'tests/Browser/m29-theory-practice.test.cjs',
  'tests/Browser/m30-theory-practice.test.cjs',
  'tests/Browser/seo-m31-practice.test.cjs',
  'tests/Browser/seo-m31-m26-practice.test.cjs',
  'tests/Browser/seo-m32-practice.test.cjs',
  'tests/Browser/seo-m33-practice.test.cjs',
  'tests/Browser/seo-m34-practice.test.cjs',
  'tests/Browser/seo-m35-practice.test.cjs',
  'tests/Browser/seo-m36-practice.test.cjs',
  'tests/Browser/seo-m37-practice.test.cjs',
  'tests/Browser/seo-m38-practice.test.cjs',
  'tests/Browser/seo-m39-practice.test.cjs',
  'tests/Browser/m39-practice-ui.test.cjs',
  'tests/Browser/m40-practice-ui.test.cjs',
  'tests/Browser/m41-practice-ui.test.cjs',
  'tests/Browser/seo-m41-design-local.test.cjs',
  'tests/Browser/theory-practice-contractions.test.cjs'
)
& 'C:/Program Files/nodejs/node.exe' --test --test-reporter=spec @nodeCases
```

Exact isolated combined PHP v3 command (exit 1, три documented baseline assertions, не PASS):

```powershell
$phpCases = @(
  'tests/Feature/TheoryInlineHtmlRenderingTest.php',
  'tests/Feature/TheorySectionRenderingTest.php',
  'tests/Feature/UnifiedTheoryNativePresentationTest.php',
  'tests/Feature/UnifiedTheoryPresentationTest.php',
  'tests/Feature/TheorySidebarPresentationTest.php',
  'tests/Feature/M26PointDetailsTest.php',
  'tests/Feature/M26InteractivePracticePackageTest.php',
  'tests/Feature/M27LinkingWordsPackageTest.php',
  'tests/Feature/M28EmphasisPackageTest.php',
  'tests/Feature/M28DetailQualityFollowupTest.php',
  'tests/Feature/M29SentenceStructurePackageTest.php',
  'tests/Feature/M29DetailQualityFollowupTest.php',
  'tests/Feature/M30ParticipleClausesPackageTest.php',
  'tests/Feature/M31ConditionalsPackageTest.php',
  'tests/Feature/M32FormalEnglishPackageTest.php',
  'tests/Feature/M33AcademicEnglishPackageTest.php',
  'tests/Feature/M34ArgumentationCohesionPackageTest.php',
  'tests/Feature/M35PassiveReportingPackageTest.php',
  'tests/Feature/M36ModalsSubjunctivePackageTest.php',
  'tests/Feature/M37GrammarStructuresPackageTest.php',
  'tests/Feature/M38ArticlesCollocationsPackageTest.php',
  'tests/Feature/M39AuthoredRevisionPackageTest.php',
  'tests/Feature/M39PracticeUiPackageTest.php',
  'tests/Feature/M40TensesB1PackageTest.php',
  'tests/Feature/M41AuthoredTenseComparisonsPackageTest.php',
  'tests/Feature/M41ExistingDesignPackageTest.php'
)
& 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe' tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m42-native-regressions-v3 @phpCases
```

Генератори metadata, фінальні M42/Node/Vitest перевірки та WT build — exit 0. Окремий combined PHP regression v3 завершився exit 1 із трьома підтвердженими попередніми fixture/EOL failures, а не PASS. Ранні development attempts збережено: v1/v2 виправляли test harness assumption щодо hero/navigation, які рендеряться `theory/show`, а не `TheoryPresentation::nativeView`; v4 invalidated під час додавання requested detail annotation mapping і не є фінальним результатом. v5 був PASS перед independent-review mixed-field правкою; фінальний v6 після freeze пройшов повністю. Runtime/source guards заради PASS не послаблювалися, snapshots автоматично не оновлювалися.

Основні test/diagnostic files: `tests/Feature/M42NativeDesignPackageTest.php`, `tests/Feature/M42DesignEvidenceTest.php`, `tests/Browser/m42-design-http.test.cjs`, `tests/Browser/seo-m42-local.test.cjs`, `tools/diagnostics/project-m42-registry.php`, `project-m42-native-design.php`, `capture-m42-physical-target.cjs`, `capture-m42-design-http.cjs`, `inspect-m42-design-working-local.php`, `verify-m42-design-evidence.php`, `seo-m42-local.cjs`, `m42-visual-manifest.cjs`.

## Live AFTER, browser acceptance та особистий review

Early checkpoint після ROOT sync: `after-v1-http.json`, SHA `cb0eea47ee916c010dde877344f41ac2ac88d12a9ca730cf774eba74a412b4dc`; 148/148 guest GET і strict BEFORE-v3 comparison — PASS. Підтверджено на цьому checkpoint незмінність learning words/order/digits, 20 details, all table cells, practice keys/controls, anchors, SEO/JSON-LD, 3 M41 references, locale/shared controls, sitemap/robots/runtime. Це **не final AFTER після всіх browser requests**.

Primary acceptance artifact: `browser-accepted-final-v1-matrix.json`, SHA `cae3c5eed062c84e5e3cfd1100b051764e98c7df17240c39a5df7c6fc49a3bea`, PASS **42 lessons / 168 unique states**. Рівно 42 states кожного з desktop/light, desktop/dark, mobile/light, mobile/dark. Три retry duplicates обліковано окремо і не зараховано як додаткове покриття; невдалі attempts збережені окремо. Кожний accepted state має однакові frozen runtime hashes: CSS `a2dd8f08025a7867b81b23b2fe247dd53352952a3d8e0413ee3a097ac064a3a8`, mapping `fea2c1d8999a237e58eb039579d4e20b23b867439bd29256f3ba79d216905ec0`.

Supplemental artifact: `browser-accepted-extra-final-v1-supplemental-matrix.json`, SHA `7e7dc2cfdff7bcb701fc5b00ec6298ce4181d3b797a9034753e43dd6cd385513`, PASS **42/42 triples**: no-JS 42, narrow 320 px with actual keyboard TOC 42, enlargement/reflow equivalent 42. Final controls artifact: `browser-accepted-controls-final-v1-controls-matrix.json`, SHA `2d08502c51947a34838618661b7853506b66194976ba573379f761f03ef6525b`, PASS **32 states = 12 M41 + 20 non-target**. Runtime freeze однаковий; failed attempts збережені окремо від прийнятих rows.

| Приймання | Required | Фактичний завершений результат у цій чернетці |
|---|---:|---|
| 42 targets: desktop 1440×1000 light | 42 | 42/42 PASS у primary matrix |
| 42 targets: desktop 1440×1000 dark | 42 | 42/42 PASS у primary matrix |
| 42 targets: mobile 390×844 light | 42 | 42/42 PASS у primary matrix |
| 42 targets: mobile 390×844 dark | 42 | 42/42 PASS у primary matrix |
| Вся основна матриця | 168 | 168/168 unique accepted states; inherited warnings і три baseline test failures наведено окремо |
| 320 px / actual keyboard TOC | 42 уроки | 42/42 PASS: кожний source anchor єдиний, Enter переходить до видимого target, усі секції до footer |
| Browser-zoom-equivalent reflow: CSS viewport 720×500 + DPR 2, physical 1440×1000 | 42 уроки | 42/42 PASS; усі source секції, main/card overflow 0; це не UI Ctrl+zoom |
| No-JS complete learning content / native summary keyboard | 42 уроки | 42/42 PASS: exact source content, власні disclosures Space/Enter, наявні static answer keys доступні без JS |
| Власна практика у primary matrix | 252 tasks / 274 source controls | 252 own tasks і їхні initial/correct/wrong/reset/keyboard/token/manual механіки перевірено; references також закриті |
| Навчальні disclosures / deep links / print | 20 details | Point bindings/independent toggles/keyboard збережено; 20 unique real deep links; print open→screen closed assertions у всіх 168 states |
| Таблиці / локальний scroll / keyboard focus | Усі source tables | Повнота/order/columns у primary + 320/enlargement PASS; iterative settled ArrowLeft/ArrowRight до справжніх edges, без scrollLeft setters |
| Comparative contacts + особистий review усіх секцій кожного уроку | 42 уроки | 42/42 MAIN reviewed до footer, SHA register нижче |
| Learning main/card overflow у primary viewports | 168 states | Max main/card overflow 0; decorative/background оцінюється окремо; global overflow:hidden не використовується |
| Нові actual console/local HTTP errors у primary matrix | 168 states | 0 actual errors / 0 local HTTP errors; expected/blocked або failed diagnostic attempts обліковано окремо |

M41 окремі регресійні еталони (не включені до 42):

- [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous).
- [Present Simple vs Present Continuous](http://gramlyze.loc/theory/tenses/present-simple-vs-present-continuous).
- [Present Perfect vs Past Simple](http://gramlyze.loc/theory/tenses/present-perfect-vs-past-simple).

Їхні **14 навчальних details / 18 tasks / 32 controls**, дизайн/контент/практика та shared non-target/category/course/test paths: final browser, HTTP і DB comparisons PASS. Course gate не обходився. English/Polish content не перекладалося й не переписувалося.

Final browser controls: усі **12 M41 states** підтвердили exact author/basic/details/practice/styles/meta equality, 14 teaching details / 18 tasks / 32 controls. **20 non-target states** підтвердили unchanged native/category/course/test layout/styles/content і відсутність M42 hook. Course gate не обходився; standalone-test automatic persistence POST блокувався до відправлення на сервер, тому real-user progress writes не тестувалися і не симулювалися.

MAIN personal visual register: `main-personal-visual-review-v2.json`, SHA `48ea512e442f1332fea3e56ba2e957da98ac53e8f17dd2e7186d350cbb8ea47d`, status `complete-all42-with-inherited-warning`, reviewed **42/42 unique lessons**. Переглянуто кожний actual all-section BEFORE/AFTER contact до footer, не лише верх або CSS assertions. Додатковий secondary review охопив **42 mobile/dark full contacts + 89 коротших section PNG**; нових M42 defects не виявлено. Шістнадцять EN/UK pairs перевірені окремими actual section images.

Залишено явне успадковане visual warning: native level badge у dark слабоконтрастний через незмінений M25. Нові M42 content panels/controls узгоджені з reference, але це warning не приховується словами «без застережень». Whole-page unchanged classification — 0/42; незмінені hero/header/sidebar/navigation збережені. Controls/supplemental/no-JS/320/print/enlargement/deep-links/TOC, final 148 HTTP і read-only DB checks закриті; наступним є окремий normal Git handoff.

### Діагностичні спроби та адресні observer fixes

Ранні невдалі attempts залишено, expected source або application code не переписувалися заради PASS. Diagnostic-only corrections:

- Native table smooth scrolling може coalesce repeated key presses: observer тепер використовує справжні iterative ArrowLeft/ArrowRight з очікуванням settled scroll і досягає обох actual edges; programmatic scrollLeft setters не застосовуються.
- M39 acceptance helper бракував exported methods для нового generic driver: додано 9 bounded diagnostic adapter methods, а не змінено M39 factory, answer data або scoring.
- Owned M41 point має native `DIV.theory-item`, а не обов’язково ARTICLE: point ownership observer став tag-neutral `.theory-item`, зі збереженою перевіркою власного fragment/container. Навчальний DOM не підмінявся.
- No-JS summary перевіряється real keyboard Enter/Space, а не synthetic JS state toggle.
- Failed CSS-only `zoom:2` зберігається як окреме inherited-layout stress warning. Final enlargement використовує явно описаний viewport/DPR метод; невдалий метод не перейменовано на PASS.

Після всіх driver/adapter/selector/keyboard змін поточний diagnostic suite свіжо перевірено: **45/45, exit 0, 0 skipped**, command `node --test --test-reporter=spec tests/Browser/seo-m42-local.test.cjs`. Це isolated synthetic DOM контракт, без app/working MySQL або нових live browser calls.

### Детальна accepted матриця всіх 42 уроків

DL/DD — desktop 1440×1000 light/dark; ML/MD — mobile 390×844 light/dark. Кожний рядок походить із чотирьох actual accepted rows із незмінним runtime freeze, не з одного PNG. Contact links ведуть до приватних локальних BEFORE/AFTER all-section зображень, які MAIN особисто переглянув до footer; у Git картинки не входять.

| № | Урок | DL | DD | ML | MD | Accepted-state JSON | Особистий footer review / скриншот |
|---|---|---|---|---|---|---|---|
| 1 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g1-v1-linking-words-reason-result-contrast-before-after-all-sections.png) |
| 2 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g1-v1-advanced-linking-devices-before-after-all-sections.png) |
| 3 | [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g1-v1-concessive-and-contrastive-structures-before-after-all-sections.png) |
| 4 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g1-v1-cleft-sentences-basics-before-after-all-sections.png) |
| 5 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g1-v1-inversion-basics-before-after-all-sections.png) |
| 6 | [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g1-v1-advanced-fronting-and-emphasis-before-after-all-sections.png) |
| 7 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | PASS | PASS | PASS | PASS | `browser-accepted-g1-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g1-v1-cleft-sentences-emphasis-before-after-all-sections.png) |
| 8 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g2-v1-complex-noun-phrases-before-after-all-sections.png) |
| 9 | [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g2-v1-ellipsis-substitution-and-reference-before-after-all-sections.png) |
| 10 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g2-v1-participle-clauses-basics-before-after-all-sections.png) |
| 11 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g2-v1-participle-clauses-before-after-all-sections.png) |
| 12 | [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g2-v1-advanced-participle-and-absolute-clauses-before-after-all-sections.png) |
| 13 | [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g2-v1-conditionals-with-unless-provided-as-long-as-before-after-all-sections.png) |
| 14 | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | PASS | PASS | PASS | PASS | `browser-accepted-g2-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g2-v1-advanced-conditionals-before-after-all-sections.png) |
| 15 | [Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g3-v1-conditional-alternatives-and-nuance-before-after-all-sections.png) |
| 16 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g3-v1-formal-register-and-nominalisation-basics-before-after-all-sections.png) |
| 17 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g3-v1-nominalisation-formal-register-before-after-all-sections.png) |
| 18 | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g3-v1-register-tone-and-paraphrase-before-after-all-sections.png) |
| 19 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g3-v1-hedging-and-cautious-language-basics-before-after-all-sections.png) |
| 20 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g3-v1-hedging-and-cautious-language-before-after-all-sections.png) |
| 21 | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | PASS | PASS | PASS | PASS | `browser-accepted-g3-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g3-v1-stance-register-and-evaluation-before-after-all-sections.png) |
| 22 | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g4-v1-argumentation-and-academic-tone-before-after-all-sections.png) |
| 23 | [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g4-v1-discourse-markers-and-cohesion-before-after-all-sections.png) |
| 24 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g4-v1-paraphrase-and-reformulation-before-after-all-sections.png) |
| 25 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g4-v1-passive-reporting-structures-before-after-all-sections.png) |
| 26 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g4-v1-complex-passive-and-causative-before-after-all-sections.png) |
| 27 | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v3-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g4-v1-complex-passive-impersonal-style-before-after-all-sections.png) |
| 28 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | PASS | PASS | PASS | PASS | `browser-accepted-g4-v3-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g4-v1-modal-perfect-and-deduction-before-after-all-sections.png) |
| 29 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g5-v1-subjunctive-and-formal-structures-before-after-all-sections.png) |
| 30 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g5-v1-subtle-modal-meanings-before-after-all-sections.png) |
| 31 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g5-v1-advanced-gerund-infinitive-patterns-before-after-all-sections.png) |
| 32 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g5-v2-complex-relative-clauses-before-after-all-sections.png) |
| 33 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g5-v2-inversion-after-negative-adverbials-before-after-all-sections.png) |
| 34 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g5-v2-advanced-article-and-quantifier-nuance-before-after-all-sections.png) |
| 35 | [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | PASS | PASS | PASS | PASS | `browser-accepted-g5-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g5-v1-precision-with-articles-and-determiners-before-after-all-sections.png) |
| 36 | [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v1-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g6-v1-advanced-collocation-and-lexical-choice-before-after-all-sections.png) |
| 37 | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g6-v1-nominal-style-and-information-density-before-after-all-sections.png) |
| 38 | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g6-v1-c1-mixed-revision-before-after-all-sections.png) |
| 39 | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-progress-g6-v1-c2-mixed-revision-before-after-all-sections.png) |
| 40 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g6-v1-present-perfect-vs-present-perfect-continuous-before-after-all-sections.png) |
| 41 | [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g6-v1-narrative-tenses-before-after-all-sections.png) |
| 42 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | PASS | PASS | PASS | PASS | `browser-accepted-g6-v2-pages.json` | [Reviewed all sections](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m42-local/browser-accepted-comparison-g6-v1-b1-mixed-revision-before-after-all-sections.png) |

## БД, SEO та final consistency

Final contract підтверджено: **0 updated / 0 inserted / 0 deleted**. Write pipelines не запускалися. Sole publisher після закриття всіх browser requests виконав final HTTP → physical proof → **останній all46-table SELECT snapshot** → pure file equality. Після цього жодних нових GET/working-DB queries не виконувалося.

| Final доказ | Private file | SHA-256 / факт |
|---|---|---|
| Final guest HTTP AFTER | `after-final-v1-http.json` | `0709a6b0f0052439a97aead7560e73d697d8f8dd92dbaa0d4fe6f3466c2ad0da`; 148/148, 0 errors/retries |
| Final physical target | `physical-target-after-v1.json` | `e75f567e6f8e4176daa990092689c0a2a35cdce30dc018feba17523d32fdecd2`; фактичні DocRoot/DNS/HTTP process і всі 4 config/index hashes незмінні |
| Last read-only DB AFTER | `m42-design-after-final-v1.json` | `e949c152aaa98d0e10c41aa50eb01de8425213972e4a43fd122e6550868badf1`; UTC 01:42:52 / Kyiv 04:42:52 |
| Pure strict equality | `db-equality-v1.json` | `5a9bcbb61c2faa95a8aaa0e39d1c674e7d55bd36a99e598014409dbd6515bcf4`; 0/0/0, усі 46 raw tables exact, без exemptions |

Final HTTP інтервал: UTC **2026-10-07 01:39:56.530–01:41:37.870**, Kyiv **04:39:56–04:41:37**. Реальні GET без Authorization/Cookie/Referer, redirects only local origin; default timeout 30 s, category-only explicit 120 s budget, max 2 attempts дозволено, але final фактично **0 retries / 0 failed attempts**. Category response 5.438 s. Зіставлення з незалежним `before-v3-http.json` exact для ordered learner words/numerals, 20 target + 14 M41 details, table cells, controls/keys, anchors, URL/redirects, title/H1, description, canonical, robots/X-Robots-Tag і JSON-LD. Усі 140 наявних JSON-LD documents/scripts валідні.

Ordered sitemap незмінний: **554 URLs**, SHA `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`; robots/runtime також exact BEFORE. Read-only physical probe (UTC 01:42:28.672) не додавав nonce route і не змінював конфігурацію. Final DB equality охоплює всі source bodies, metadata, timestamps, UUID, all locales, 3 M41 references, 6 120 non-target rows, **усі banks/progress/raw tables без винятків**. Це фактичний working-MySQL proof, не isolated SQLite result.

Exact sole-publisher final sequence (WT cwd; усі commands exit 0; наведено як record вже виконаного запуску, не інструкція повторно робити GET після last DB snapshot):

```powershell
$env:NODE_PATH = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules'
& 'C:/Program Files/nodejs/node.exe' tools/diagnostics/capture-m42-design-http.cjs after-final-v1 before-v3-http.json
& 'C:/Program Files/nodejs/node.exe' tools/diagnostics/capture-m42-physical-target.cjs after-v1
& 'C:/Program Files/xampp/php/php.exe' tools/diagnostics/inspect-m42-design-working-local.php m42-design-after-final-v1.json
& 'C:/Program Files/xampp/php/php.exe' tools/diagnostics/verify-m42-design-evidence.php m42-design-before-v2.json m42-design-after-final-v1.json db-equality-v1.json
```

Остання команда — pure file comparison без Laravel/DB bootstrap. Final acceptance source/definition bytes і accepted M27–M40 revisions збережені; нових залежностей, migrations, seeds, apply hooks або production writes не було.

## Зафіксовані застереження та межі перевірки

- Previous BEFORE attempt v1 двічі отримав category timeout на `/theory/clauses-and-linking-words`: 30 000 ms + 30 000 ms, `TimeoutError: The operation was aborted due to timeout`; UTC 2026-10-06 23:12:49.402–23:13:49.425 = Kyiv 2026-10-07 близько 02:12–02:13. Artifact `before-v1-http.json`, SHA `d2ada97f34a6189f3330a5135e543d23153b858c58b4723a26b7e354d1e2349b`, збережено. Fresh v3 та early AFTER успішні; це не доказ SQL defect або Googlebot blocking.
- `httpd -S` показав попереднє unrelated missing DocumentRoot warning іншого vhost; M42 server configuration не змінював.
- Browser standalone-test automatic POST state persistence перехоплюється **до відправлення** на сервер для read-only contract. GET-rendered layout/questions перевіряються, але autosave/progress persistence навмисно не тестується. Заблокований write не класифікується як новий дефект сайту; replacement HTML/response не інжектується.
- Existing PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` (`config/database.php:62`) не виправляється цим presentation-only scope.
- Успадковане visual warning: позначки рівня `B2/C1/C2` у native header мають слабкий контраст у dark. Незмінений `text-block-level-badge.blade.php:4` задає `bg-amber-100 text-amber-700`, але незмінений M25 `theory-unified-design.css:123` переводить amber text у `var(--text)`. Empty diff цих shared header/badge/CSS проти accepted base та ROOT/WT equality підтверджують, що це попередня поведінка, а не новий M42 spillover. Її не приховано формулюванням «усі візуальні застереження усунено»; окреме shared-badge contrast maintenance не входить у виконані правки.
- Cold first GET був повільнішим; warm identical GET не підтвердив steady-state slowdown. Source/author guards не послаблювалися і performance metrics/Core Web Vitals не вигадувалися.
- Independent browser BEFORE `networkidle` timeout на першій спробі Conditionals with Unless збережено; повторна перевірка справжнього DOM/Alpine/linked questions readiness успішна. Це не підміна HTML або власний AFTER baseline.
- Окремий `body { zoom: 2 }` stress attempt провалився на першому уроці: overflow 111 px через inherited untouched hero 3-column/min-width 172 px layout. Read-only порівняльні probes дали 71 px на M41 reference і 48 px на non-target PPC. Цей CSS-body-zoom метод не є UI/browser zoom і не є основною матрицею; failed attempt збережено, PASS не вигадано і snapshots не переписано. Source layout/hero/sidebar не змінювалися, бо це явно поза scope. Окремий коректно названий enlargement метод (CSS viewport 720×500 + DPR 2, physical 1440×1000, browser-zoom-equivalent reflow, не UI Ctrl+zoom) завершено 42/42 PASS.
- Primary personal review зафіксував inherited dark level-badge contrast warning; нові M42 visual defects після final freeze не знайдені, але позаскопне warning лишається в handoff.

### Три попередні fixture/EOL assertions — baseline evidence

Combined existing regression attempt не оголошується PASS. Для двох assertion failures виконано окремий read-only reproduction з accepted Git `053c6ddbe4b42b23f724ec336fdf472b6d219215`. Вісім залежностей — обидва test files, `TheorySection`, `TheoryInlineHtml`, section component/disclosure та обидві M41 definitions — LF-byte-identical між accepted base і worktree. Repro читає Git objects, завантажує тільки Composer autoload, не boot Laravel, не читає `.env`, не робить DB queries або source/Git writes.

1. `TheoryInlineHtmlRenderingTest` / Present Perfect vs Past Simple: старий `richFields` selector шукає `<strong>/<span>` тільки в hero examples, forms subtitles, wrong fields, rows.en/ua та usage examples. Погоджена M41 definition вже містить нові author-section/cells і plain hero formulas; на **accepted base** `affected = 0`, отже old `assertGreaterThan(0, affected)` падає до нового M42 presentation.
2. `TheorySectionRenderingTest` / `changed` fixture: old mutation `str_replace('Past Simple', 'Past Changed', detail)` має **0 replacements** у погодженому M41 hero. Modified HTML exact дорівнює original, тому valid `diagnosticCode = null`; old assertion очікує `detail-reference-changed` для mutation, яка не відбулася. Контрольна фактична mutation `Що сталося` → `Змінений факт` дає `detail-reference-changed`, тобто runtime guard чинний.
3. `UnifiedTheoryPresentationTest:101` порівнює raw M23 master SHA з Git-LF SHA. Current Windows file має 194 CRLF і raw SHA `3d919d5894ac37a11a107d31075078c419394a0b3f537f21e303523bd4213c32`; accepted Git base має 0 CRLF і raw SHA `eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6`. Current LF-normalized bytes **exactly** дорівнюють accepted Git bytes і SHA `eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6`; Git blob `34a03a7146141fbff50c66ec8e41f3fc2a59b787`. Frozen master і old raw-hash assertion не змінювалися. Renderer assertions до цієї raw-SHA перевірки пройшли.

Приватний доказ: `storage/app/seo-m2-local/m42-accepted-base-assertion-repro-v1.json`, SHA `6e3445d2ca0761f673f8ccfe8c71a0dd6ca87e75c2720fb8753be0aa2650a300`. Команда read-only діагностики, exit 0:

```powershell
& 'C:/Program Files/xampp/php/php.exe' storage/app/seo-m2-local/m42-accepted-base-assertion-repro.php
```

EOL доказ збережено окремо приватно: `storage/app/seo-m2-local/m42-m23-master-eol-proof-v1.json`. Final combined v3 evidence: `m42-native-regressions-v3-e4a4c169a1294f0199f309e4380fec84-result.json`, child/runner exit 1, 297 tests / 21 681 assertions / 3 failures / 0 errors; protected files changes 0.

Це diagnosis proof, а не PASS старих assertions. Старі tests/fixtures/snapshots не переписано задля зеленого результату; accepted learning content не змінювався. Окреме fixture/EOL test maintenance лишається застереженням. Попередні три Vite-manifest errors відокремлено: штатна WT build завершилася exit 0, ROOT public/build не копіювався; у final combined v3 цих errors вже немає. Exact 26-file combined command/output evidence наведено у приватному result JSON; воно не приховується завершеним M42 browser/DB acceptance. Git handoff виконується окремо після staged review.

## Git handoff

Primary **168 states / all42 personal visual review**, усі supplemental triples, **32 reference/control states**, final **148 GET** і strict working DB **0/0/0** завершені. Цей документ фіксує verified pre-commit acceptance; MAIN далі перевіряє staged diff/bytes і виконує normal commit/push лише пов’язаних M42 metadata/support/views/tests/diagnostics/report/.gitattributes. Без frozen sources, .env, runtime caches, DB snapshots/backups, screenshots, vendor/build або чужих hunks; без PR/main/production.

Branch: `codex/seo-m42-m11-m24-native-design`. Точний actual commit SHA та результат `remote SHA == HEAD` буде наведено у фінальному повідомленні **тільки після** normal push і read-only remote verification, не вгадано до створення самого commit. Main/production/deploy/PR залишаються поза scope.
