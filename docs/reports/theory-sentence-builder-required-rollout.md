# Конструктор речень для 70 сторінок і наступних пакетів

## Результат і межі

Локальний сайт: `http://gramlyze.loc`; served ROOT: `D:/DEV/htdocs/gramlyze.loc`. Робоча гілка: `codex/m46-m49-practice-completion`.

Перевірено серверні дані всіх **70 унікальних сторінок**: 42 M11–M24, 12 M41/M43/M44/M45, 4 Past Perfect Continuous та 12 M46–M49. На кожній конструктор уже був: один native widget із п’ятьма питаннями `Question::TYPE_COMPOSE_TOKENS` (type 4). Відсутніх у серверному HTML — **0**, додано — **0**, відновлено — **0**; після серверної та браузерної перевірки без блока залишилося **0**. Існуючі підключення залишено без змін. Нових завершених M50+ цілей не виявлено.

Фактична браузерна видимість підтверджена на **70/70 унікальних URL**: конструктор знаходиться у viewport, є українська умова, лічильник `П1/5`, банк із 8–26 кнопками та область складання речення. Console errors і debug labels не виявлено. Це перевірка наявності й видимості, не проходження відповідей.

Підключення, питання, відповіді, прогрес, навчальні тексти, авторські inputs, «Докладніше», metadata, URL, anchors, курси та окремі тести не змінювалися. Записів у БД — **0**; content apply і backup для цього завдання без змін підключень не були потрібні. Blade/CSS/JS, шрифти, grader та дизайн не редагувалися. Main, production, налаштування сервера й кеші не змінювалися.

## Що додано

- В `AGENTS.md` закріплено обов’язковість одного конструктора внизу «Практики» кожної нової або SEO-модернізованої сторінки. Він не замінює власні inputs і не замінюється посиланням на тест.
- [Окреме правило контентного процесу](../content/theory-sentence-builder-required.md) визначає тематичний банк, п’ять різних питань, збереження guards і перевірку реальної сторінки.
- `TheorySentenceBuilderAcceptance`, команда `theory:audit-sentence-builders` і незалежні owner → bank contracts у `docs/content/theory-sentence-builders/*.json` перевіряють модель, штатний matcher та фактичний HTTP payload.
- Discovery прийнятих AFTER-цілей із versioned content patches доповнює початковий перелік. Новий owner без контракту або підключення дає помилку; перевірка не обмежена назавжди цими 70 сторінками. Вона не виконує автоматичних записів чи apply.
- Додано захист вихідних даних validator: для майбутньої цілі без перевіреного контракту `url=null`. Негативна fixture охоплює credentials і вкладені значення; чинні 70 bindings та підключення не змінені.

Історичний `docs/content/theory-template.md` збережено byte-for-byte, SHA-256 `4dd38be95f1a89f9b5f13a13f5cc504d805066e8686ccbc81c803fface871bd1`. Frozen releases, наявні manifests, банківські джерела та їхні pins не переписувалися. Попередній звіт [Practice Completion M46–M49](m46-m49-practice-completion.md) не редагувався.

## Підтверджені перевірки

Фінальний ізольований QA v2 нового правила та суміжних механізмів завершено: **38 унікальних tests / 4,098 assertions, PASS**, failures та errors — 0, child і runner — exit 0; із 46,661 захищеного файла змін — 0. Окремо зафіксовано одну наявну PDO deprecation; це не failure, конфігурацію заради неї не змінювали. Цей результат замінює попередній; повторні запуски не підсумовуються як нові унікальні тести.

Приватний QA receipt у WT: `storage/app/seo-m2-local/sentence-builder-required-v2-cea4f94b50324b8b963b045827a997a8-result.json`, SHA-256 `b6899ef928d133d5844ddb3a17181a8da1d0d77ee03ee94f8bb357c87d71013a`.

Фінальний live CLI gate v2 після захисту URL: **70/70, 0 failures, 0 writes**, exit 0, stderr порожній; тривалість — 168 секунд. Для кожного URL підтверджені HTTP 200, точний кінцевий URL, один widget, п’ять переданих питань type 4 та відповідність незалежному тематичному контракту через чинний matcher. Зв’язки можуть бути page-wide: пряме посилання саме на practice UUID не вимагалося. Фільтр рівня не знімався; exact CEFR та display ranges обробляються за чинними правилами matcher.

Приватний серверний receipt:
`storage/app/widget-audit-local/required70-after-urlguard-v2-2026-10-13T17-34-38-169Z-bab9f4fd-a643-48d3-931f-aea12f3cbd33.json`

SHA-256: `e49fa667cf40a44aa2273925788295beda46a41a91c8a9428f727df8d294c97b`.

Фінальна звірка v2 підтвердила незмінність 18 frozen pins, 326 visual entries, семи попередніх runtime-файлів у ROOT і WT та 70 bank definitions. URL, Page ID, practice UUID, банки й усі 350 question IDs/types збігаються з браузерним проходом і попереднім CLI receipt: розбіжностей — 0. Приватний receipt у ROOT: `storage/app/widget-audit-local/required70-preservation-urlguard-v2-20261013T173927Z.json`, SHA-256 `0fd16a6d3ad988df65d03f52bb82bd423bc44bd72964ffe5e2ded4373393aa4b`.

Команда відтворення read-only перевірки з підтвердженого served ROOT:

```sh
php artisan theory:audit-sentence-builders --target=gramlyze.loc
```

Ця команда не виконує браузерних дій: її `browser_visibility` та `interaction` залишаються `not_checked`. Видимість підтверджує окремий фактичний браузерний прохід усіх 70 URL, а не CLI-результат.

Приватний browser receipt у ROOT: `storage/app/sentence-builder-required-local/a1a45056-0642-426c-aa1e-7f14d3433399/browser-complete.json`, SHA-256 `c40ca39eac351eed2f0e0c22becce152eda625779b7a16fb0d882596ac30a5c6`. На кожній сторінці всі п’ять UI question IDs, їхні типи `4` та practice UUID точно збігаються зі свіжим CLI receipt: розбіжностей — 0.

Answer/check/next/reset у цьому проході не виконувалися: **interaction not checked** на всіх 70 сторінках. Підключення не змінювалися, тому повне проходження 350 відповідей не було умовою завдання й не заявляється. Особистий прогрес користувача не очищувався.

Два тимчасові збої браузерного інструмента відокремлено від результату сайту: початковий navigation timeout на PPC-еталоні відновлено читанням тієї самої вкладки; DOM timeout на Wish — свіжим читанням і повторною перевіркою. Обидві сторінки підтверджені у фінальному проході. Retry не збільшує кількість унікальних URL і не зарахований як окремий website failure.

## Поурочний перелік

«Був» означає серверну наявність native compose widget із п’ятьма питаннями, а не авторські inputs чи картку-посилання на тест. Колонка «Зміна» стосується підключення конструктора, не нового правила або тестів.

| Група | Точний URL | Був до змін правила | Зміна підключення | Після: браузер |
|---|---|---|---|---|
| M11–M24 | [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Cleft Sentences Basics](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Fronting And Emphasis](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Ellipsis Substitution And Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Conditionals with Unless, Provided That, and As Long As](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Conditional Alternatives And Nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Discourse Markers And Cohesion](http://gramlyze.loc/theory/clauses-and-linking-words/discourse-markers-and-cohesion) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M11–M24 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Present Perfect vs Past Simple](http://gramlyze.loc/theory/tenses/present-perfect-vs-past-simple) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Present Simple vs Present Continuous](http://gramlyze.loc/theory/tenses/present-simple-vs-present-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Past Perfect vs Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-vs-past-perfect-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Stative Verbs](http://gramlyze.loc/theory/tenses/stative-verbs) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Used to / Would](http://gramlyze.loc/theory/tenses/used-to-would) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Future Forms: Choosing the Right Form](http://gramlyze.loc/theory/maibutni-formy/choosing-the-right-future-form) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Present Continuous for Future](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Will vs Be Going To — Вибір форми](http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Future Continuous vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Future Perfect vs Future Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M41/M43/M44/M45 | [Future Perfect vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M26 PPC | [Past Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M26 PPC | [Past Perfect Continuous: Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M26 PPC | [Past Perfect Continuous: Questions and Short Answers](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M26 PPC | [Past Perfect Continuous: Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/zero-conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/first-conditional](http://gramlyze.loc/theory/conditionals/first-conditional) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/second-conditional](http://gramlyze.loc/theory/conditionals/second-conditional) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/third-conditional](http://gramlyze.loc/theory/conditionals/third-conditional) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/mixed-conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/unless-provided-as-long-as](http://gramlyze.loc/theory/conditionals/unless-provided-as-long-as) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/in-case](http://gramlyze.loc/theory/conditionals/in-case) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/wish-if-only](http://gramlyze.loc/theory/conditionals/wish-if-only) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/would-rather](http://gramlyze.loc/theory/conditionals/would-rather) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/conditionals/had-better](http://gramlyze.loc/theory/conditionals/had-better) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/modal-verbs/can-could](http://gramlyze.loc/theory/modal-verbs/can-could) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |
| M46–M49 | [/theory/modal-verbs/may-might](http://gramlyze.loc/theory/modal-verbs/may-might) | Так: 1 блок / 5 type 4 | Немає | Видно: П1/5 |

## Завершення

Серверна наявність і фактична браузерна видимість перевірені для всіх 70 цілей; без конструктора залишилося 0. Обов’язкове правило для майбутніх сторінок та динамічний регресійний захист закріплено. Взаємодія з відповідями не перевірялася й не оголошується PASS.

Звіт фіналізовано до Git-доставки. Далі виконується звичайний scoped commit/push у робочу гілку; факт доставки й повний SHA наводяться в підсумковому повідомленні після виконання. Цей файл не заявляє наперед виконаний push і не містить самопосилального commit SHA.
