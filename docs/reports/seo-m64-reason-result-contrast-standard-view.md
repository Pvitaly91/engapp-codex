# M64 — Reason, Result і Contrast: стандартна в’юха

Дата: 2026-10-17. Завершено локальну модернізацію рівно трьох UK-уроків; production не перевірявся й не змінювався.

База: `b5f521d9886a1a9b1c5298f84f581bf7b54e2713` (accepted M63).
Робоча гілка: `codex/seo-m64-reason-result-contrast-standard-view`.
Served application: `D:/DEV/htdocs/gramlyze.loc`, document root — `public`. Код гілки перенесено тільки власними exact hunks; Git HEAD served ROOT не змінювався.

## Сторінки й результат

| Сторінка | Page / category | Власна практика | Нижній конструктор |
| --- | --- | --- | --- |
| [Reason: because / since / as](http://gramlyze.loc/theory/clauses-and-linking-words/reason-because-since-as) | 36 / 34 | 2 форма + 2 зміст + 2 побудова | 5/5 у браузері |
| [Result: so / therefore / as a result](http://gramlyze.loc/theory/clauses-and-linking-words/result-so-therefore-as-a-result) | 37 / 34 | 2 + 2 + 2 | 5/5 у браузері |
| [Contrast: although / though / however](http://gramlyze.loc/theory/clauses-and-linking-words/contrast-although-though-however) | 30 / 34 | 2 + 2 + 2 | 5/5 у браузері |

Exact owners: `Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWords{Reason,Result,Contrast}TheorySeeder`.
Усі три відповіді — HTTP 200 на точних local URLs. Locale uk; ancestry `clauses-and-linking-words`; technical hero/practice B1, display B1-B2. Category не перейменована. Purpose і M63 overview не є цілями.

## Редактура

- Reason: because/since/as пояснюються через фокус і подання фону, не «силу» чи правдивість причини. Basic містить дві позиції частини, Why-dialogue, because of, часові since/as, прозоре питання та заперечення з контекстом. Стилістична перевага because не перетворена на граматичну заборону as.
- Result: збережено напрямок причина → наслідок/рішення/висновок. Є so, therefore, and therefore, as a result / as a result of; межі самостійних частин; відмінність мети so that від здійсненого результату. Therefore не оголошено автоматично логічнішим, so — непридатним для письма.
- Contrast: although/though + clause, дві позиції частини, linking however та внутрішнє however, кінцеве though, however + degree і логічний підмет скорочення. Обидва факти й учасники збережені.

Нові власні приклади та завдання не видаються за старий source. Повні EN-приклади мають UK-переклади. Meaning-choice переклади відкриваються після Check; контекст і критерій видно відразу. Джерельні спрощення уточнено предметно, не оголошено всі старі формулювання помилками.

[Повна UK-редакція](../content/m64-reason-result-contrast.v1.0.0.uk.md), [editorial BEFORE/AFTER](../content/m64-reason-result-contrast.editorial.v1.0.0.md), [frozen master](../content/m64-reason-result-contrast.v1.0.0.json).

Мовна перевірка: Cambridge Grammar Today/dictionary, [Purdue](https://owl.purdue.edu/owl/general_writing/punctuation/independent_and_dependent_clauses/index.html), [Google Search Central](https://developers.google.com/search/docs/fundamentals/creating-helpful-content). Actual URLs, теми перевірки та способи доступу зафіксовані в master.sources. Cambridge publisher-indexed body і direct 403 розрізнено; успішного direct body/redirect не вигадано. Видавничі exercise banks та учнівські коментарі не використано.

## OLD → стандартні компоненти

Однакова скінченна карта для кожного owner:

| Source slot | BEFORE | AFTER | Metadata |
| --- | --- | --- | --- |
| 0 | hero | hero | header / B1 збережено |
| 1 | box | usage-panels | колишній heading → null; left / null level збережено |
| 2 | box | forms-grid | heading → null; left / null збережено |
| 3 | box | comparison-table | heading → null; right / null збережено |
| 4 | box | mistakes-grid | heading → null; right / null збережено |
| 5 | box | summary-list | heading → null; right / null збережено |
| 6 | navigation-chips | navigation-chips | footer / null; labels/order/current flags збережено |
| 7, append | відсутній | practice-set | left / B1; sort_order 8 |

Це **15 явних type/heading transitions**, не body-only update. Усі 21 continuing source slots, їхні UUID/anchors, порядок, columns і levels збережені; ще три continuing UK rows — subtitles. Стара subtitle strong-title форма з повною назвою збережена.

Allowlist старих headings (префікси 1–5 включені в source):
Reason — «Основне значення», «Типовий pattern», «Приклади і відтінки», «Типові помилки», «Коротке правило».
Result — «Як працює so», «Як працюють therefore і as a result», «Пунктуація і pattern», «Типові помилки», «Коротке правило».
Contrast — «Як працюють although і though», «Як працює however», «Порівняй pattern», «Типові помилки», «Коротке правило».

Точні body hashes, UUID, native view та detail ownership — [component map](../content/m64-reason-result-contrast.components.v1.json).

### Meaningful details

По одному змістовному поглибленню, без квоти слів і без глобального runtime threshold:

| Owner block / point | Detail |
| --- | --- |
| reason-focus / sections/2 | reason-negation-focus — заперечення дії проти її причини |
| result-meaning / sections/1 | result-purpose-outcome — намір, рішення та відомий наслідок |
| contrast-meaning / sections/3 | contrast-reduced-owner — власник ознаки у скороченні |

Необхідні правила, переклади та застереження залишилися в basic. Повний basic/detail присутній у server HTML. У браузері всі три initial closed, Enter відкриває власний detail, Space закриває; deep link і reload відкривають правильний фрагмент без обрізання.

## Власна практика та sentence builders

18 власних tasks: 6 selects, 6 meaning choices, 6 inputs. Усі inputs працюють вручну й токенами. Сім finite accepted variants — шість canonical плюс явний Result alias `and we therefore stayed home`. Plain-text feedback містить відповідь, точний UK-переклад і пояснення; generic fallback не переписано.

Незмінний normalize ігнорує пунктуацію. Punctuation A/B мають різні українські semantic values; кожний distractor відхиляється. Прийняття ручного/token речення не видається за перевірку ком. Граматична альтернатива, що не виконує явної інструкції, не названа неграматичною.

Окремі нижні widgets — 15 selected type-4 B1 питань, не частина total 18:
7 reused + 8 new (Reason 3, Result 2, Contrast 3). Прочитано всі 36 old B1 кандидати; 29 не відібрано через неоднозначність, дублювання або недостатній контекст. Їхні keys не змінювалися.
Exact old/new banks, selected/pool IDs та reviewed умови — [навчальний bank-contract](../content/m64-reason-result-contrast-bank-audit.v1.json); [незалежний owner/bank contract](../content/theory-sentence-builders/m64-reason-result-contrast.v1.json).

Нові practice rows:

| Page | Block ID | UUID |
| --- | --- | --- |
| 36 | 12738 | b2602cab-1d75-508a-a456-4e925d861fdf |
| 37 | 12739 | 64448990-5705-5ef3-bcd1-ed7133688c91 |
| 30 | 12740 | 4a1492b4-5e87-5977-a2e6-e2c662e7d2e7 |

Нові question IDs — 59709–59716; 58 physical option rows reused, без записів у question_options. Existing page-wide links семи reused питань залишилися чинними. Вісім new links ведуть лише на власні нові practice UUID.

## Guarded apply та збереження

Незалежні source/DB/HTTP BEFORE, bank/consumer inventory, fresh physical/web/CLI proof → fresh preview → exact review → exclusive private backup → M26 transaction → postconditions → repeated no-op. Тимчасові nonce routes були read-only GET, тільки http/loopback gramlyze.loc, без секретів. Обидва видалено; кожний окремий GET після видалення дав 404. Routes file відновлений байт у байт, включно зі сторонніми змінами.

Фактичний DB diff:

| Сімейство | Оновлено | Додано | Видалено |
| --- | ---: | ---: | ---: |
| pages (тільки text) | 3 | 0 | 0 |
| continuing UK text_blocks | 24 | 0 | 0 |
| нові practice text_blocks | 0 | 3 | 0 |
| questions | 0 | 8 | 0 |
| question_options | 0 | 0 | 0 |
| question_answers | 0 | 85 | 0 |
| question_option_question | 0 | 103 | 0 |
| question_theory_text_blocks | 0 | 8 | 0 |

Повторні apply початкового та AFTER-планів дали no-op; фінальний AFTER/no-op після technical correction також має нулі у всіх сімействах. Зайві backup-файли no-op не створилися.

Остаточний післябраузерний SELECT-only аудит усіх **46 таблиць** відновив точний незалежний BEFORE у пам’яті, віднявши лише перевірені нові рядки й погоджені поля. Усі old rows/timestamps, 48 EN/PL target rows, category descriptions, прогрес і нецільові question payloads збережені. 56 old HTTP consumers, 6 saved tests і 432 old questions — unchanged; old SQL fingerprints до/після HTTP однакові. Доступ не обходився.

## Невізуальна інтеграція та final bytes

Вісім існуючих seams розширено лише exact-bound реєстрацією, normalization/correction delegation, clone-only presentation/exclusions і course/standalone preservation. Нової в’юхи, stylesheet, frontend JS, grader або auto-apply mechanism немає.

SSR acceptance виявив fail-closed guard на трьох службових атрибутах штатного matcher. Виправлення дозволяє лише повний відомий decoration-набір, не послаблюючи 14-column DB snapshot, ordered answers/options/pivots/hints hashes або all-or-nothing fallback. Unknown/partial decorations відхиляються. Live перевірка 15 clones підтвердила: original models/relations unchanged, 0 додаткових SQL queries, 0 DB writes.

Точний technical follow-up і hashes — [runtime lineage](../content/m64-reason-result-contrast.runtime-lineage.v1.json). M64 source-sync-v1 залишений історичним; final source-sync-v2 підтверджує 43 paths. M64 postconditions явно зв’язують лише два reviewed old/new technical hashes; старі receipts не переписані. Контент 1.0.0 і всі 15 frozen artifact hashes незмінні.

326 visual source files і 2416 historical frozen files — unchanged. Reverse-hunk перевірка всіх 16 ROOT/WT shared profiles відновила exact preimage разом із foreign changes та EOL; ще 44 non-owned technical raw hashes збережено. M63 source-sync-v2 та final lineage збережені з accepted hashes. Чужий ROOT .gitattributes deletion не відновлювався; лише WT отримав finite -text правила для M64 frozen bytes.

## Фактичні перевірки

- Frozen projection, provenance, body sizes: PASS; максимальний UTF-8 body 13 573 bytes при SQL TEXT capacity 65 535, без DDL.
- Фінальний ізольований профіль: **63/63 tests, 7782 assertions**, SQLite :memory:, array cache/session, без робочої .env/MySQL; 0 protected-file changes.
- Фінальний shared/M63/H1/token/alias/retry набір: **111/115 PASS**, 27 332 assertions; чотири відомі literal-UI failures нижче. 0 protected-file changes. Обидва PHP-набори повідомили 1 deprecation; це не zero-issues run.
- Незмінна extracted Alpine factory: **24/24 unique offline scenarios**, окремо перевірена на served-local і WT bytes. Це не 48 унікальних tests і не browser acceptance.
- Local HTTP/SSR: 3×200, **388 rendered learning fields**, metadata/anchors, full details, дев’ять navigation destinations та exact widget payloads.
- Dynamic read-only registry: **115/115 targets**, 112 попередніх + 3 нові, 0 writes; discovery не має постійного hardcoded cap.
- Browser: **18 states** (1440×1000 / 390×844 / 320×844, light/dark, три сторінки), до footer. Font loading, H1, три власні групи й один widget підтверджені.
- Browser interaction: усі **28 option cases**, **24 input task/mode/size cases**, 12 error/retry sequences, extra Result alias, reset/recheck/independent scores; actual plain bilingual feedback.
- Widgets: **5/5 × 3**, client shuffle враховано; BANK-only locators, strict case/multiplicity, правильні keys і Next. Особистий localStorage/cookies/progress не очищалися.
- Available browser error log: 0 errors. Viewport і light theme відновлені.

Чотири historical failures у незміненому TheoryPracticeSetTokenBankUiTest:
`test_manual_practice_inputs_suppress_native_browser_suggestions`;
`test_token_bank_input_keeps_manual_sync_without_registering_word_suggestion_handlers`;
`test_token_bank_source_is_rendered_as_buttons_without_the_plain_slash_text`;
`test_separate_choice_exercise_has_its_own_answers_checking_and_reset_state`.
Вони очікують старі literal Blade strings після ранішнього component refactor. Їх не вилучали й не переписували заради PASS; visual/engine sources залишилися exact BEFORE. Повторні runs не додаються до унікальних totals і не видаються за один загальний запуск.

Ширина learning main: 877/877, 358/358, 288/288 client/scroll. На mobile таблиці мають штатний auto-scroll (576px content усередині 322/252px region). Shell document overflow варіює між BEFORE/AFTER; у діагностичному 320px sample поза learning main виходили два SPAN. Не весь AFTER overflow названо успадкованим. CSS не змінювався для приховування дефектів.
Print, no-JS і формальний WCAG audit — **not_checked**, не PASS і не твердження про відсутність підтримки.

## Frozen artifacts та приватні докази

[Checksums 15 artifact files](../content/m64-reason-result-contrast-checksums.v1.json).
Master SHA-256: `889ea0b30f3b573cbdfd7a09e80178a0131c42eba01da3945d57b79c2d91e318`.
Author/native patch і independent builder contract — version 1.0.0 / v1.

Публічний bank-contract містить лише окремо дозволені teaching fields, Page/question IDs, назви банків та opaque snapshot hashes. Сирі DB rows/timestamps, runtime identity, backups, screenshots, proofs, caches і приватні діагностичні receipts не комітяться.

Приватні фінальні receipts: source-sync-v2, final-readonly-v3, live-pages-v1, after-old-consumers-v1, registry-sentence-builders-v1, browser-after-v1. Final-readonly-v3 SHA-256: `f8d9eda7eed7dca67b58fc91dc1dfa30f6757344feb8f0b24067b711b8e088e3`. Screenshots збережені локально, не в Git.

## Доставка

Scoped commit і normal push виконуються після staged diff, diff --check та exact staged frozen-byte validation. Фінальний commit SHA і незалежна перевірка remote SHA == HEAD повідомляються в delivery response. Main, production, PR і deployment не зачіпаються.
