# Опрацьовані сторінки: підключення до канонічного дизайну

Дата: 2026-10-10. Робоча гілка: `codex/theory-single-reference-template`.
Actual base: `afb54868f6a8d0292b157c0b57ed7cd1731a2716`.

**Файловий висновок:** охоплено 54/54 owners; presentation-виправлення стосуються 42, ще 12 уже використовують потрібний шаблон. Три runtime-адаптери перенесено точковими hunks до локального ROOT. Це presentation-only продовження, не новий контентний пакет. Автоматичне та візуальне приймання цього проходу не запускалося; історичні результати нижче використовуються лише як джерела identities і прийнятого контенту.

## Scope та джерела

Подальше зауваження користувача зі скриншотами показало, що описаний тут файловий прохід не завершив оформлення: безіменні абзаци лишалися сірими картками, а частина native-відповідей — великими uppercase-кнопками. Окреме виправлення цих розбіжностей зафіксоване у [theory-passed-pages-layout-correction.md](theory-passed-pages-layout-correction.md); попередні результати не видаються за приймання цього нового оформлення.

Рівно **54 унікальні UK owners**: 42 M11–M24 у чинних редакціях M27–M40/M42 + по три M41, M43, M44 і M45. M27–M40 не рахуються повторно. EN/PL URLs, locale fallback, category pages, курси й окремі `/test` routes не є додатковими цільовими owners. Чотири M26/PPC — окрема межа збереження. Підтвердженого наступного завершеного пакета понад цей scope не додано; M46 не створюється.

Вихідний перелік відновлено за [versioned theory registry](theory-single-reference-registry.json), [M42 identity/block registry](../../database/content-patches/m42-native-design-registry.v1.json), чинними definitions і content-patches. [M42](seo-m42-m11-m24-native-design.md), [M43.1](seo-m43-1-ppc-reference-design.md), [спрощена M44](m44-simplified-basic.md), [M45](seo-m45-compact-future-comparisons.md), [єдиний шаблон](theory-single-reference-template.md) та [практика](theory-practice-reference-template.md) прочитані як історичні звіти. Попередній приватний practice inventory використано лише для зіставлення збережених Page ID/owner/форматів, не як новий DB proof.

Незалежний файловий BEFORE цього проходу створено до редагування: `file-before-v1.json`, SHA-256 `4ce03441194b4cd601fd5bf223fd6a49de1fcfc1a569788b509eaf68b7aab1ce`; 276 presentation-файлів у приватному `source-before-v1/{root,worktree}`. Приватні artifacts не комітяться. Файлове читання чинної конфігурації вказує на served ROOT `D:/DEV/htdocs/gramlyze.loc/public`; новий HTTP/DB proof не виконувався.

### Джерела пакетів

Кожний рядок основного реєстру містить exact identity suffix і посилання на його definition. Повний identity має префікс `Database\Seeders\Page_V3\`. Відповідний content source визначений наведеною нижче таблицею, а не припущенням за URL.

| Поточний пакет | Versioned source |
|---|---|
| M27 | [m27-m11-linking-words.v2.json](../../database/content-patches/m27-m11-linking-words.v2.json) |
| M28 | [m28-m12-emphasis-inversion.v2.json](../../database/content-patches/m28-m12-emphasis-inversion.v2.json) |
| M29 | [m29-m13-sentence-structure.v2.json](../../database/content-patches/m29-m13-sentence-structure.v2.json) |
| M30 | [m30-m14-participle-clauses.v1.json](../../database/content-patches/m30-m14-participle-clauses.v1.json) |
| M31 | [m31-m15-conditionals.v1.json](../../database/content-patches/m31-m15-conditionals.v1.json) |
| M32 | [m32-m16-formal-english.v1.json](../../database/content-patches/m32-m16-formal-english.v1.json) |
| M33 | [m33-m17-academic-english.v1.json](../../database/content-patches/m33-m17-academic-english.v1.json) |
| M34 | [m34-m18-argumentation-cohesion.v1.json](../../database/content-patches/m34-m18-argumentation-cohesion.v1.json) |
| M35 | [m35-m19-passive-reporting.v1.json](../../database/content-patches/m35-m19-passive-reporting.v1.json) |
| M36 | [m36-m20-modals-subjunctive.v1.json](../../database/content-patches/m36-m20-modals-subjunctive.v1.json) |
| M37 | [m37-m21-grammar-structures.v1.json](../../database/content-patches/m37-m21-grammar-structures.v1.json) |
| M38 | [m38-m22-articles-collocations.v1.json](../../database/content-patches/m38-m22-articles-collocations.v1.json) |
| M39 | [m39-m23-authored-revision.v1.json](../../database/content-patches/m39-m23-authored-revision.v1.json) + [m39-practice-ui.v1.json](../../database/content-patches/m39-practice-ui.v1.json) |
| M40 | [m40-m24-tenses-b1.v1.json](../../database/content-patches/m40-m24-tenses-b1.v1.json) |
| M41 | [m41-authored-tense-comparisons.v1.0.1.json](../../database/content-patches/m41-authored-tense-comparisons.v1.0.1.json) |
| M43 | [m43-authored-tense-usage.v1.0.0.json](../../database/content-patches/m43-authored-tense-usage.v1.0.0.json) |
| M44 | [m44-authored-future-forms.v1.0.0.json](../../database/content-patches/m44-authored-future-forms.v1.0.0.json) |
| M45 | [m45-authored-future-comparisons.v1.0.0.json](../../database/content-patches/m45-authored-future-comparisons.v1.0.0.json) |

Додаткові незмінні presentation mappings: `m41-existing-native-design.v1.json`, `m42-native-design.v1.json`; для M44 — `docs/content/m44-simplified-presentation.v1.json`, `m44-forms-presentation.v2.json`, `m44-cont-forms-presentation.v3.json`. Вони визначають уже погоджене групування, а не новий дизайн цього проходу. M41/M43/M44/M45 author masters у `docs/content/` не редагуються.

## Фактичні шляхи відображення

Спільний entry: `resources/views/theory/show.blade.php` явно передає `theoryCanonical=true` у `theory/partials/content-block.blade.php`. Dispatcher виконує чинні package guards; `M42NativeDesignPackage::decorate` зберігає дані й додає перевірені presentation annotations. Лише перевірений code-owned `native_view` може змінити вибір view. Hero/navigation лишаються в чинному `theory.show`; subtitle зберігає anchor і не дублює intro.

У theory canonical context старі package styles не підключаються: `courses.compatibility.theory.package-styles` і M42 wrapper розміщені в `!$theoryCanonical` гілці. Foreign/mutated/invalid data зберігають повний безпечний fallback. Наявність fallback markup сама по собі не є окремим активним дизайном погодженого уроку.

### Профілі теорії

| Код | Guarded шлях до спільних компонентів |
|---|---|
| T-native | M27–M40 `presentation()` → перевірені native дані/point fragments → `engram.theory.blocks-v3.{usage-panels,forms-grid,comparison-table,mistakes-grid,summary-list}` → `TheoryLegacyAdapter::section` → `theory.components.node`. Point fragments: `theory.partials.point-detail-fragment` → `TheoryPointDetailAdapter`. Конкретний набір визначають типи рядка. |
| T-41 | `M41AuthoredTenseComparisonsPackage::presentation` + `M41ExistingDesignPackage::decorate` → `m41-existing-design-section` (guarded fallback wrapper: `m41-author-section`) → `theory.partials.authored-section` → `TheoryAuthoredAdapter` → `theory.components.node`. |
| T-43 | `M43AuthoredTenseUsagePackage` → `m43-native-section` → `authored-section` → `TheoryAuthoredAdapter` → `theory.components.node`. |
| T-44 | `M44AuthoredFutureFormsPackage` → `m44-native-section` → чинний `M44SimplifiedPresentation` → `authored-section` → `TheoryAuthoredAdapter` → `theory.components.node`. Узгоджені компактні групи й anchors зберігаються. |
| T-45 | `M45FutureComparisonsPackage` → `m45-section` → `authored-section` → `TheoryAuthoredAdapter` → `theory.components.node`. Зберігаються дві картки форм із трьома рядками, 13 власних details на три уроки. |

Кінцеві компоненти — `resources/views/theory/components/`: section, usage, form/form-heading, example, note, table, mistake/correction, summary, disclosure, fragment/group/paragraph. Section header — `components.theory-native-header`; point-level «Докладніше» — наявні `theory.partials.point-disclosure`/`section-disclosure`. `TheoryHtmlAdapter` обробляє підтримані HTML fragments; нерозпізнане зберігається повністю. Wrapper не обирає власну M-палітру.

### Профілі практики

| Код | Фактичний шлях |
|---|---|
| P-native | Dispatcher → `engram.theory.blocks-v3.practice-set` → native групи → `components.theory-practice-*`; на 33 сторінках select2/choice2/input2, на Page291/307 choice3/input3, на Page301 input6. |
| P-39 | `practice-set` → чинний `M39PracticeUiPackage` guard → `m39-practice-ui` → `authored-practice-ui`. |
| P-40 | `practice-set` → чинний `M40TensesB1Package` guard → `m40-practice-ui` → `authored-practice-ui`. |
| P-41 | `practice-set` → чинний `M41AuthoredTenseComparisonsPackage` guard → `m41-practice-ui` → `authored-practice-ui`. |
| P-43 / P-44 / P-45 | Package guard повертає `m43-practice-ui` / `m44-practice-ui` / `m45-practice-ui` → `authored-practice-ui`. |

Усі authored шляхи використовують `TheoryPracticePresentation` та спільні exercise/header/heading, control/marker, option, token-bank/token/input, actions/action, feedback/explanation. У складному authored Blade збережені явні PHP-блоки з останнього 500-fix. Noncanonical caller має явну compatibility-гілку; theory не переходить до неї.

Класифікація scope: **36 native + 18 authored = 54**, інша механіка 0, лише widget 0, без практики 0. На кожному owner є один практичний блок і окремий нижній `x-text-block-practice-questions` з `theory_links`; банк не дублюється всередині завдань. Наявні M38 semantic checks також використовують `x-theory-practice-option`. Повні static guard-fallback, autocomplete dropdown і нижній widget не замінюються новою механікою. Історичні 108 authored tasks / 174 controls наведені для визначення scope, не як новий результат проходження.

## Повний поурочний реєстр

Коди збережених типів: S=`subtitle`, H=`hero`, U=`usage-panels`, T=`comparison-table`, F=`forms-grid`, M=`mistakes-grid`, R=`summary-list`, P=`practice-set`, N=`navigation-chips`. Це типи джерел/збереженого каталогу; вкладені authored points нормалізуються профілем, а не перейменовуються в БД.

Усі рядки — UK. Колонка «Шляхи» посилається на точні profiles вище; identity одночасно є посиланням на definition. A/B статус нижче означає лише висновок за файлами, не браузерний PASS. E — конкретний nonpractice pointer у незміненому finite M42 metadata з English-em annotation; slot — zero-based source_index, не номер видимої секції. Перенесення ROOT і відсутність нового acceptance фіксуються для всього переліку окремо.

| Page ID | Наявна сторінка | Identity suffix / definition | Пакет | Типи | Шляхи theory / practice | Статус |
|---:|---|---|---|---|---|---|
| 290 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | [ClausesAndLinkingWords\LinkingWordsReasonResultContrastTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/LinkingWordsReasonResultContrastTheorySeeder/definition.json) | M11 → M27 + M42 | S, H, U, T, R, P | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 302 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | [ClausesAndLinkingWords\AdvancedLinkingDevicesTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/AdvancedLinkingDevicesTheorySeeder/definition.json) | M11 → M27 + M42 | S, H, U, R, P | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 315 | [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | [ClausesAndLinkingWords\ConcessiveAndContrastiveStructuresTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/ConcessiveAndContrastiveStructuresTheorySeeder/definition.json) | M11 → M27 + M42 | S, H, U, R, P | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 288 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | [BasicGrammar\WordOrder\InversionBasicsTheorySeeder](../../database/seeders/Page_V3/BasicGrammar/WordOrder/InversionBasicsTheorySeeder/definition.json) | M12 → M28 + M42 | S, H, U, T, P | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 289 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | [SentenceStructure\CleftSentencesBasicsTheorySeeder](../../database/seeders/Page_V3/SentenceStructure/CleftSentencesBasicsTheorySeeder/definition.json) | M12 → M28 + M42 | S, H, T, U, P | T-native / P-native | A · E: slot 1, `/rows/0/cells/1` |
| 313 | [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | [BasicGrammar\WordOrder\AdvancedFrontingAndEmphasisTheorySeeder](../../database/seeders/Page_V3/BasicGrammar/WordOrder/AdvancedFrontingAndEmphasisTheorySeeder/definition.json) | M12 → M28 + M42 | S, H, T, U, P | T-native / P-native | A · E: slot 1, `/rows/0/cells/1` |
| 297 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | [SentenceStructure\CleftSentencesEmphasisTheorySeeder](../../database/seeders/Page_V3/SentenceStructure/CleftSentencesEmphasisTheorySeeder/definition.json) | M13 → M29 + M42 | S, H, T, U, P | T-native / P-native | A · E: slot 1, `/rows/0/cells/1` |
| 311 | [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | [SentenceStructure\EllipsisSubstitutionAndReferenceTheorySeeder](../../database/seeders/Page_V3/SentenceStructure/EllipsisSubstitutionAndReferenceTheorySeeder/definition.json) | M13 → M29 + M42 | S, H, T, U, P | T-native / P-native | A · E: slot 1, `/rows/0/cells/1` |
| 312 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | [SentenceStructure\ComplexNounPhrasesTheorySeeder](../../database/seeders/Page_V3/SentenceStructure/ComplexNounPhrasesTheorySeeder/definition.json) | M13 → M29 + M42 | S, H, U, T, P | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 285 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | [ClausesAndLinkingWords\ParticipleClausesBasicsTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/ParticipleClausesBasicsTheorySeeder/definition.json) | M14 → M30 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 299 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | [ClausesAndLinkingWords\ParticipleClausesTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/ParticipleClausesTheorySeeder/definition.json) | M14 → M30 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 318 | [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | [ClausesAndLinkingWords\AdvancedParticipleAndAbsoluteClausesTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/AdvancedParticipleAndAbsoluteClausesTheorySeeder/definition.json) | M14 → M30 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 287 | [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | [Conditionals\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder](../../database/seeders/Page_V3/Conditionals/ConditionalsWithUnlessProvidedAsLongAsTheorySeeder/definition.json) | M15 → M31 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 294 | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | [Conditionals\AdvancedConditionalsTheorySeeder](../../database/seeders/Page_V3/Conditionals/AdvancedConditionalsTheorySeeder/definition.json) | M15 → M31 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 309 | [Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | [Conditionals\ConditionalAlternativesAndNuanceTheorySeeder](../../database/seeders/Page_V3/Conditionals/ConditionalAlternativesAndNuanceTheorySeeder/definition.json) | M15 → M31 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 291 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | [FormalEnglish\FormalRegisterAndNominalisationBasicsTheorySeeder](../../database/seeders/Page_V3/FormalEnglish/FormalRegisterAndNominalisationBasicsTheorySeeder/definition.json) | M16 → M32 + M42 | S, H, T, U, P, R | T-native / P-native | A · E: slot 2, `/sections/1/description` |
| 301 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | [FormalEnglish\NominalisationFormalRegisterTheorySeeder](../../database/seeders/Page_V3/FormalEnglish/NominalisationFormalRegisterTheorySeeder/definition.json) | M16 → M32 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 307 | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | [FormalEnglish\RegisterToneAndParaphraseTheorySeeder](../../database/seeders/Page_V3/FormalEnglish/RegisterToneAndParaphraseTheorySeeder/definition.json) | M16 → M32 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/2/description` |
| 293 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | [AcademicEnglish\HedgingAndCautiousLanguageBasicsTheorySeeder](../../database/seeders/Page_V3/AcademicEnglish/HedgingAndCautiousLanguageBasicsTheorySeeder/definition.json) | M17 → M33 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 298 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | [AcademicEnglish\HedgingAndCautiousLanguageTheorySeeder](../../database/seeders/Page_V3/AcademicEnglish/HedgingAndCautiousLanguageTheorySeeder/definition.json) | M17 → M33 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 314 | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | [AcademicEnglish\StanceRegisterAndEvaluationTheorySeeder](../../database/seeders/Page_V3/AcademicEnglish/StanceRegisterAndEvaluationTheorySeeder/definition.json) | M17 → M33 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 310 | [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | [ClausesAndLinkingWords\DiscourseMarkersAndCohesionTheorySeeder](../../database/seeders/Page_V3/ClausesAndLinkingWords/DiscourseMarkersAndCohesionTheorySeeder/definition.json) | M18 → M34 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 322 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | [FormalEnglish\ParaphraseAndReformulationTheorySeeder](../../database/seeders/Page_V3/FormalEnglish/ParaphraseAndReformulationTheorySeeder/definition.json) | M18 → M34 + M42 | S, H, T, U, P, R | T-native / P-native | A · E: slot 1, `/intro` |
| 323 | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | [AcademicEnglish\ArgumentationAndAcademicToneTheorySeeder](../../database/seeders/Page_V3/AcademicEnglish/ArgumentationAndAcademicToneTheorySeeder/definition.json) | M18 → M34 + M42 | S, H, T, U, P, R | T-native / P-native | A · E: slot 2, `/sections/0/description` |
| 296 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | [PassiveVoice\PassiveReportingStructuresTheorySeeder](../../database/seeders/Page_V3/PassiveVoice/PassiveReportingStructuresTheorySeeder/definition.json) | M19 → M35 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/0/description` |
| 305 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | [PassiveVoice\ComplexPassiveAndCausativeTheorySeeder](../../database/seeders/Page_V3/PassiveVoice/ComplexPassiveAndCausativeTheorySeeder/definition.json) | M19 → M35 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 319 | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | [PassiveVoice\ComplexPassiveImpersonalStyleTheorySeeder](../../database/seeders/Page_V3/PassiveVoice/ComplexPassiveImpersonalStyleTheorySeeder/definition.json) | M19 → M35 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 303 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | [ModalVerbs\ModalPerfectAndDeductionTheorySeeder](../../database/seeders/Page_V3/ModalVerbs/ModalPerfectAndDeductionTheorySeeder/definition.json) | M20 → M36 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/2/description` |
| 304 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | [FormalEnglish\SubjunctiveAndFormalStructuresTheorySeeder](../../database/seeders/Page_V3/FormalEnglish/SubjunctiveAndFormalStructuresTheorySeeder/definition.json) | M20 → M36 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 320 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | [ModalVerbs\SubtleModalMeaningsTheorySeeder](../../database/seeders/Page_V3/ModalVerbs/SubtleModalMeaningsTheorySeeder/definition.json) | M20 → M36 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 2, `/sections/0/description` |
| 286 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | [VerbPatterns\AdvancedGerundInfinitivePatternsTheorySeeder](../../database/seeders/Page_V3/VerbPatterns/AdvancedGerundInfinitivePatternsTheorySeeder/definition.json) | M21 → M37 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 295 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | [BasicGrammar\WordOrder\InversionAfterNegativeAdverbialsTheorySeeder](../../database/seeders/Page_V3/BasicGrammar/WordOrder/InversionAfterNegativeAdverbialsTheorySeeder/definition.json) | M21 → M37 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 300 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | [RelativeClauses\ComplexRelativeClausesTheorySeeder](../../database/seeders/Page_V3/RelativeClauses/ComplexRelativeClausesTheorySeeder/definition.json) | M21 → M37 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 306 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | [ArticlesAndQuantifiers\AdvancedArticleAndQuantifierNuanceTheorySeeder](../../database/seeders/Page_V3/ArticlesAndQuantifiers/AdvancedArticleAndQuantifierNuanceTheorySeeder/definition.json) | M22 → M38 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/2/description` |
| 316 | [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | [VocabularyAndCollocations\AdvancedCollocationAndLexicalChoiceTheorySeeder](../../database/seeders/Page_V3/VocabularyAndCollocations/AdvancedCollocationAndLexicalChoiceTheorySeeder/definition.json) | M22 → M38 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 2, `/rows/0/cells/2` |
| 317 | [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | [ArticlesAndQuantifiers\PrecisionWithArticlesAndDeterminersTheorySeeder](../../database/seeders/Page_V3/ArticlesAndQuantifiers/PrecisionWithArticlesAndDeterminersTheorySeeder/definition.json) | M22 → M38 + M42 | S, H, U, T, P, R | T-native / P-native | A · E: slot 1, `/sections/1/description` |
| 321 | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | [FormalEnglish\NominalStyleAndInformationDensityTheorySeeder](../../database/seeders/Page_V3/FormalEnglish/NominalStyleAndInformationDensityTheorySeeder/definition.json) | M23 → M39 + M42 | S, H, U, T, P, R | T-native / P-39 | A · E: slot 1, `/sections/1/description` |
| 329 | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | [BasicGrammar\C1MixedRevisionTheorySeeder](../../database/seeders/Page_V3/BasicGrammar/C1MixedRevisionTheorySeeder/definition.json) | M23 → M39 + M42 | S, H, T, U, P, R | T-native / P-39 | A · E: slot 1, `/rows/0/cells/2` |
| 330 | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | [BasicGrammar\C2MixedRevisionTheorySeeder](../../database/seeders/Page_V3/BasicGrammar/C2MixedRevisionTheorySeeder/definition.json) | M23 → M39 + M42 | S, H, U, P, R | T-native / P-39 | A · E: slot 1, `/sections/2/description` |
| 189 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | [Tenses\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder/definition.json) | M24 → M40 + M42 | S, H, T, F, U, M, R, N, P | T-native / P-40 | A · E: slot 8, `/intro` |
| 280 | [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | [Tenses\TensesNarrativeTensesTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesNarrativeTensesTheorySeeder/definition.json) | M24 → M40 + M42 | S, H, T, F, R, M, N, U, P | T-native / P-40 | A · E: slot 6, `/intro` |
| 327 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | [BasicGrammar\BasicGrammarB1MixedRevisionTheorySeeder](../../database/seeders/Page_V3/BasicGrammar/BasicGrammarB1MixedRevisionTheorySeeder/definition.json) | M24 → M40 + M42 | S, H, R, F, N, U, P | T-native / P-40 | A · E: slot 4, `/intro` |
| 187 | [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous) | [Tenses\TensesPastSimpleVsPastContinuousTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesPastSimpleVsPastContinuousTheorySeeder/definition.json) | M41 | S, H, U, N, P | T-41 / P-41 | B · правки не потрібні |
| 188 | [Present Perfect vs Past Simple](http://gramlyze.loc/theory/tenses/present-perfect-vs-past-simple) | [Tenses\TensesPresentPerfectVsPastSimpleTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesPresentPerfectVsPastSimpleTheorySeeder/definition.json) | M41 | S, H, U, N, P | T-41 / P-41 | B · правки не потрібні |
| 190 | [Present Simple vs Present Continuous](http://gramlyze.loc/theory/tenses/present-simple-vs-present-continuous) | [Tenses\TensesPresentSimpleVsPresentContinuousTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesPresentSimpleVsPresentContinuousTheorySeeder/definition.json) | M41 | S, H, U, N, P | T-41 / P-41 | B · правки не потрібні |
| 186 | [Past Perfect vs Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-vs-past-perfect-continuous) | [Tenses\TensesPastPerfectVsPastPerfectContinuousTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesPastPerfectVsPastPerfectContinuousTheorySeeder/definition.json) | M43 | S, H, T, F, U, M, R, P, N | T-43 / P-43 | B · правки не потрібні |
| 191 | [Stative Verbs](http://gramlyze.loc/theory/tenses/stative-verbs) | [Tenses\TensesStativeVerbsTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesStativeVerbsTheorySeeder/definition.json) | M43 | S, H, U, T, M, R, P, N | T-43 / P-43 | B · правки не потрібні |
| 192 | [Used to / Would](http://gramlyze.loc/theory/tenses/used-to-would) | [Tenses\TensesUsedToWouldTheorySeeder](../../database/seeders/Page_V3/Tenses/TensesUsedToWouldTheorySeeder/definition.json) | M43 | S, H, F, U, T, M, R, P, N | T-43 / P-43 | B · правки не потрібні |
| 58 | [Future Forms: Choosing the Right Form](http://gramlyze.loc/theory/maibutni-formy/choosing-the-right-future-form) | [FutureForms\FutureFormsChoosingTheRightFutureFormTheorySeeder](../../database/seeders/Page_V3/FutureForms/FutureFormsChoosingTheRightFutureFormTheorySeeder/definition.json) | M44 | S, H, U, T, F, M, R, P, N | T-44 / P-44 | B · правки не потрібні |
| 62 | [Present Continuous for Future](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future) | [FutureForms\FutureFormsPresentContinuousForFutureTheorySeeder](../../database/seeders/Page_V3/FutureForms/FutureFormsPresentContinuousForFutureTheorySeeder/definition.json) | M44 | S, H, F, U, T, M, R, P, N | T-44 / P-44 | B · правки не потрібні |
| 63 | [Will vs Be Going To — Вибір форми](http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to) | [FutureForms\FutureFormsWillVsBeGoingToTheorySeeder](../../database/seeders/Page_V3/FutureForms/FutureFormsWillVsBeGoingToTheorySeeder/definition.json) | M44 | S, H, T, F, U, M, R, P, N | T-44 / P-44 | B · правки не потрібні |
| 59 | [Future Continuous vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous) | [FutureForms\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder](../../database/seeders/Page_V3/FutureForms/FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder/definition.json) | M45 | S, H, T, F, U, M, R, N, P | T-45 / P-45 | B · правки не потрібні |
| 60 | [Future Perfect vs Future Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-continuous) | [FutureForms\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder](../../database/seeders/Page_V3/FutureForms/FutureFormsFuturePerfectVsFutureContinuousTheorySeeder/definition.json) | M45 | S, H, T, F, U, M, R, N, P | T-45 / P-45 | B · правки не потрібні |
| 61 | [Future Perfect vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous) | [FutureForms\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder](../../database/seeders/Page_V3/FutureForms/FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder/definition.json) | M45 | S, H, T, F, U, M, R, N, P | T-45 / P-45 | B · правки не потрібні |

## A. Presentation-виправлення

**42 сторінки M11–M24 / M27–M40 + M42**, позначені A в реєстрі. Для кожного owner у колонці E наведено конкретний source pointer із перевіреною English-em роллю в чинному metadata. Це не висновок лише з наявності helper або загального M42 marker: усі 42 мають принаймні один застосовний nonpractice фрагмент. Останні три M40 теж охоплені: Page189 slot8 `/intro`, Page280 slot6 `/intro`, Page327 slot4 `/intro`.

Знайдені розходження і render-only виправлення:

1. **Усі 42:** metadata позначає точні EN `em`-фрагменти, але shared HTML rendering залишав курсивний HTML tag. `TheoryHtmlAdapter` переводить лише вже підтверджений `data-m42-em-language="en"` у звичайний inline span зі збереженими атрибутами й усіма дочірніми словами. UK-template exceptions не перепризначаються англійськими; формули та inline слова не перетворюються довільно на окремі example cards.
2. **Page189 і Page280:** чинні `rich_mixed_fields` містять 9 forms subtitles і 7 right-correction fields з окремими English/UK ролями. `TheoryLegacyAdapter` споживає ці існуючі finite ranges і передає example/translation окремим semantic полям спільних form/example/correction components. Formula context, glyphs, separator і порядок слів зберігаються; source strings не переписуються.
3. **Page302, Page297, Page311, Page294, Page309:** source-explicit `blockquote` + «Переклад:» пари проходять через чинний shared example. Це вузька обробка поля з exact hash і підтвердженим повним M42 plan, не мовна евристика. Label, translation, anchors, наступна проза й повний unsupported fallback залишаються. Detail fragments підключені через той самий `nativeFragment`, без нового package-detail markup. Native usage wrapper визначає block/inline за вже нормалізованим HTML, щоб shared example DIV не опинився всередині P.

Ці підмножини перетинаються: **42 + 2 + 5 не є 49 зміненими сторінками**. Runtime scope — три render-only adapters; components, package palettes, shared views, learning sources та практика не потребують повторного редизайну. Підключення реалізовано за вихідним кодом; runtime execution у цьому проході не перевірявся.

## B. Уже підключений шаблон

**12 цілих сторінок**: M41 [187, 188, 190], M43 [186, 191, 192], M44 [58, 62, 63], M45 [59, 60, 61]. Для кожної статус: **«канонічний шлях уже підключений; правки не потрібні»**. Їхні guarded authored sections уже нормалізуються через `TheoryAuthoredAdapter`, а практика — через останній `authored-practice-ui`; пакетна compatibility-верстка не є активним theory path.

Окремо практика **всіх 54 owners** уже йде через потрібні спільні компоненти; **0 сторінок потребували нових practice правок**. Це 54/54 source-path класифікація, не новий тестовий результат. Для 42 A-сторінок незмінність practice path не означає відсутність theory-виправлення.

## C. Межі й невиконане приймання

Невирішених перешкод для файлового підключення за оглядом коду не виявлено. Неоднозначні inline згадки та формули залишаються повністю доступними всередині спільного fragment; вони не перетворюються на окрему картку лише за тегом `em` або тире. Block/inline обгортки usage і summary обираються за вже нормалізованим HTML, щоб новий shared example не вкладався в paragraph/span.

- Файлова реалізація й огляд diff не є перевіркою виконаного Laravel rendering.
- Browser/HTTP/zoom/Playwright/CDP, PHPUnit/Node/Vitest та новий DB BEFORE/AFTER audit за прямою вказівкою користувача не запускалися.
- Операції запису до БД не виконувалися; нового виміряного DB diff 0/0/0 не заявлено.
- Історичні snapshots і reports не перейменовувалися в актуальний BEFORE/AFTER.
- Main, production, серверні налаштування, курси й standalone tests не змінюються; deploy не виконується.

## Еталон M26: окрема межа збереження

| Page ID | Сторінка | Identity suffix |
|---:|---|---|
| 162 | [Past Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | `Tenses\PastPerfectContinuous\PastPerfectContinuousFormsTheorySeeder` |
| 163 | [Past Perfect Continuous: Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives) | `Tenses\PastPerfectContinuous\PastPerfectContinuousNegativesTheorySeeder` |
| 164 | [Past Perfect Continuous: Questions and Short Answers](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions) | `Tenses\PastPerfectContinuous\PastPerfectContinuousQuestionsTheorySeeder` |
| 165 | [Past Perfect Continuous: Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) | `Tenses\PastPerfectContinuous\PastPerfectContinuousTimeExpressionsTheorySeeder` |

M26 має native theory + `M26PointDetails`/`TheoryPointDetailAdapter`, native practice та окремий bank widget. Не входить у 54, не перепроєктовується й не отримує нового контенту. Наявні сторонні PPC hooks у served ROOT мають бути збережені при перенесенні власних hunks. Нового M26 live regression acceptance у цьому проході немає.

## Локальне перенесення, файли й Git

Статус локального перенесення для кожного рядка: **A — спільні adapter hunks застосовано до `D:/DEV/htdocs/gramlyze.loc`; B — чинне канонічне підключення збережено, додаткових файлів для перенесення не потрібно**. Для всіх 54 статус нового browser/test acceptance — **не запускалося**. Не синхронізовано весь checkout; сторонні dirty/deleted файли та PPC hooks у ROOT не редагувалися.

Власний файловий scope:

- `app/Support/TheoryLegacyAdapter.php`;
- `app/Support/TheoryHtmlAdapter.php`;
- `app/Support/TheoryPointDetailAdapter.php`;
- `docs/reports/theory-passed-pages-design-rollout.md`.

Локальна збірка **не виконувалася і не потрібна**: CSS/JS і їхні класи не редагувалися; адаптери використовують уже наявні компоненти. За оглядом scoped diff змінено лише три render-only PHP-адаптери та цей новий звіт. Навчальні masters/definitions/content-patches і прийняті projections не редагувалися; view/JS тестів, practice scoring/retry/reset, питання, aliases, tokens, metadata та basic/detail mappings до diff не входять. Власний перелік файлів збережено приватно в `storage/app/theory-passed-pages-local/files-changed-v1.json`; цей перелік і BEFORE не комітяться.

Фінальний commit і normal push виконує sole publisher у `codex/theory-single-reference-template`; full SHA та звірення remote SHA з HEAD повідомляються після push, не вписуються як self-referential hash.

