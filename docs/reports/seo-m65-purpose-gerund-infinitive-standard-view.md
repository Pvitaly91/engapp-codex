# M65 — Purpose, Gerund, To-Infinitive

## Результат і межі

Локально застосовано frozen release **1.0.0** до рівно трьох UK-уроків. Це не fixture-only preview: реальні сторінки та інтерактивна практика перевірені на `http://gramlyze.loc`.

| Сторінка | HTTP | Власна практика | Вбудований TYPE 4 | Meaningful detail |
| --- | --- | --- | --- | --- |
| [Purpose: to / in order to / so that](http://gramlyze.loc/theory/clauses-and-linking-words/purpose-to-in-order-to-so-that) | 200 | 2 selects + 2 choices + 2 inputs, B1 | 5/5 | 1 |
| [Gerund](http://gramlyze.loc/theory/verb-patterns/gerund) | 200 | 2 selects + 2 choices + 2 inputs, A2 | 5/5 | 1 |
| [To-Infinitive](http://gramlyze.loc/theory/verb-patterns/to-infinitive) | 200 | 2 selects + 2 choices + 2 inputs, A2 | 5/5 | 1 |

Робоча гілка: `codex/seo-m65-purpose-gerund-infinitive-standard-view`.
Accepted base: `8371a96ad8b0254d5e88d2265dff57d91cb87190` (M64).
Main, PR, merge/rebase, production .com/.ub та деплой не використовувалися.
Served ROOT і commit-worktree різні: перенесено лише exact M65 hunks, сторонній dirty ROOT не включено.

## Навчальний зміст і канонічний дизайн

- Purpose: базові форми й доповнення; той самий/інший виконавець; so that з модальністю та без обов’язкового modal; негативна мета проти заперечення головної дії; намір проти досягнутого результату; for + noun / for + -ing проти особистої мети.
- Gerund: предмет/доповнення -ing групи; герундій проти Continuous і прикметника; обмежені правила написання; моделі дієслів і прийменників, включно з prepositional to; not + -ing і час; зміст відповідей з mind.
- To-Infinitive: базова й негативна форми, питання через головну конструкцію; want someone to й ролі; мета проти двох виконаних дій; прикметники, too/enough, noun/something; bare infinitive і prepositional to; різні області заперечення. Немає загальної заборони split infinitive.

Жодної нової верстки, Blade/CSS/frontend JS чи глобального нормалізатора. Збережені native кольори, шрифти, відступи, приклади, sidebar, navigation та caller-owned theory boundary.
Purpose мав HTML boxes: рівно п’ять дозволених metadata transitions у forms-grid / usage-panels / comparison-table / mistakes-grid / summary-list. UUID, sort order, columns і levels не змінені. Gerund/To-Infinitive зберегли native metadata.
Технічні рівні власної практики B1/A2/A2; чинні display badges B1–B2/A2–B1/A2–B1 не перейменовані.

Окремі змістовні деталі:

| Owner point | Detail |
| --- | --- |
| purpose-intention-outcome | Намір не гарантує результату |
| gerund-time-function | Час не міститься в закінченні -ing |
| infinitive-adjective-roles | Хто діє, а що зазнає дії? |

Короткі ремарки, приклади, переклади та застереження залишені у visible basic.
Джерела — British Council, Cambridge English Grammar Today, Google Search Central; точні URLs і спосіб доступу зафіксовані в master та editorial companion. Для частини Cambridge використовувався publisher-indexed body після direct error/403; повний прямий доступ до цих сторінок не заявляється.
Перевірка редактури агентська; зовнішній human reviewer, сертифікація чи SEO/ranking-ефект не заявляються. Поле `editorial_review.status=draft-not-reviewed` не видається за людське погодження.

## Практика і тематичні банки

Власні **18 tasks** не змішані з **15 selected widget questions**.
У кожному уроці 6 власних завдань; кожний із 6 inputs має manual і token спосіб відповіді.
У змістових choices переклад і пояснення відкриваються після Check; довгі пояснення не винесені у тексти кнопок.
Відповіді зберігають виконавця, час, заперечення та предмет. Визнані aliases задані явно, а не глобальним послабленням перевірки.

Purpose: із 12 старих B1-кандидатів збережено два різні тематичні питання (30615, 30620), додано лише три нові контексти (59717–59719). Gerund: 39531, 39533, 39534, 39537, 39541. To-Infinitive: 39459, 39461, 39463–39465. Отже **12 reused + 3 new**. Усі 36 старих кандидатів трьох цільових банків мають finite review.
31 потрібне слово знайдене однозначно за SQL collation: 0 missing, 0 ambiguous. Нових глобальних options не створено.
Старі question/answer/options/hints/verb_hints та їх timestamps не переписані. Case і multiplicity перевірені на ordered answer instances; чинний native ComposeTokenCase handles mid-sentence You → you.

Додані finite policy/selector/supplement/guard класи делегують існуючим native release/planner/writer primitives.
Selector працює clone-only, без запитів і записів; exact snapshots/IDs/loaded relations/відомі decorations перевіряються fail-closed.
Вісім shared integration seams містять лише M65 calls/guards. Курси та standalone tests зберігають попередню поведінку.

## Guarded local apply

1. Незалежний BEFORE: 46 SQL tables, 326 visual hashes, 2437 frozen hashes; 6 HTTP 200. M63/M64 private technical evidence окремо збережено.
2. Окремий BEFORE старих consumers: 36 фактичних HTTP-маршрутів, 6 старих банків, 432 старих питання.
3. Frozen sources та 37 exact sync paths звірені між worktree і served ROOT.
4. Fresh loopback-only read-only nonce GET підтвердив один і той самий local web/CLI target без секретів.
5. Fresh preview: всі 25 updates та 3 inserts вручну звірені з незалежним BEFORE і finite author projection; five Purpose metadata transitions перевірені окремо.
6. Exclusive backup `storage/app/seo-m65-local/backup-v1.json`; транзакційний apply.
7. Результат: 25 updates (3 Page.text + 22 continuing UK rows), 3 native practice blocks; 3 questions, 0 options, 38 answer instances, 44 pivots, 3 exact practice links; 0 deletes.
8. Original preview повторно застосований як no-op; fresh AFTER preview також no-op: усі write counts 0. Зайві backup-файли не створені.
9. Тимчасовий route прибрано; foreign-inclusive routes file exact restored, окремий GET nonce URL = 404. Конфігурація/hosts/Apache/.env не змінені.
10. Read-only postconditions реконструювали кожний BEFORE table fingerprint, відкинувши лише точні new rows і повернувши лише дозволені content fields. Усі старі rows/timestamps exact; EN/PL 44 rows unchanged.

Приватні receipts, raw captures, backup, screenshots і runtime identity не входять у commit.

### Ключові докази (SHA-256)

| Evidence | SHA-256 |
| --- | --- |
| before-v1.json | ae650493911b7496ddc79300f47d2bffc6cfd1ec1b9f97a3e707046ed8eb07ce |
| before-old-consumers-v1.json | 95318c79297facaf3b0ba82362335efb8766eb5dc3dd389ac43f9139c7bedeeb |
| source-sync-v1.json | f2f6c58bf98416c5782867ee18ed9e13267c04776cf4b916c6ba9fbb26311141 |
| final source-sync-v2.json | a5a55455dc2cce1a20c36efa502e9d60c17f936260640f7b2f5669898e57edef |
| reviewed preview digest | f70a5cdaaf64e177af7e13bcfddba54723d1f87374db6ab2d3951c9c32915c45 |
| AFTER/no-op preview digest | 419a270404aed5212f608baafe703b6e5f591a89d556af613bd5d11b3668ffbd |
| final technical AFTER preview digest | a64e952a6bf1b9124591ddc6ae187a324b3d4e36a30fbd5d09c42a97ff6b03f3 |
| final-readonly-v2.json, after main browser run | 190827548926e1d51f54fb3da77fa3734897d37359ce763b3b3f0a85f06e3612 |
| final-readonly-v3.json, after all UI actions | a85a3db7279a8e5013fc0d9b69cd8b818f0e887053b620ba51ff8359f4601808 |
| final-readonly-v4.json, technical EOF correction | dec414c5fdffdfd532ed73b5176d70b0dab56ad9cab5861d20bc892e04e50ad1 |
| final-readonly-v5.json, final no-ops/cleanup | fddd02cae84228d6b75d3f917e1e1f2bdb6d31a9a5de07ed9389732ceef6227f |
| final-readonly-v6.json, final feedback/detail check | 5cc4a85ce90d1242c10634dcf8775d1745455089608833fbe40ef957968034aa |
| after-old-consumers-v1.json | ca2d361da772cd723b7f4a245a8eb5bfea5c196e2ddf808f140cc3d310c4df72 |
| live-pages-v1.json | b10088fa874cc7e01e6e6bdec4b56ff44d3f2a525ad4421c5b1f865f740b5cab |
| M65 registry audit | e1cebf74de4113331eb0f95d20610ddc1b2d4d6bf414d7aeb764f5565ad63bf3 |
| full dynamic registry audit | 780a56626c20734be18fa8b37f640861a07d282bc1fd7848b0d7d8e287ea01ee |
| route cleanup/independent404 | 4a248891f5d317aa57b55f5d715fcf177a35aea2ca0f6e276286b108602e9322 |
| technical re-proof cleanup/independent404 | 36e542e379cc34974e26879bb035f05f3ddbd692a6249417af418f5d465891bb |

Full registry: **118 targets, 0 failed, 0 writes** (попередні 115 + M65 3). Це dynamic discovery, не постійний ліміт 118.
Exploratory `registry-before` receipt також містив 118, а не 115; його не використано як окремий BEFORE audit. Неоднозначний alias видалено з нового diagnostic wrapper.

## Реальні HTTP/DOM та браузер

SSR: 3 HTTP 200, **378 learning fields** звірені з finite author projection, включно з SEO, single H1, LearningResource, перекладами, tables/outros/mistakes, basic/detail ownership, navigation, власною практикою і exact selected widget payload.

Браузер: actual DOM viewport, 3 × (1440×1000, 390×844, 320×844) × light/dark = **18 states**. Для кожного: H1 один, 3 native own groups, 1 widget, footer reachable. Усі 18 viewport screenshots переглянуті окремо.
Native fonts порівняні з канонічним Past Perfect Continuous: основний текст 14px/22.75px, приклади 14.4px/25.2px, без зменшення для компактності.

Фактично виконано:

- 30 visible options, включно з кожним correct/distractor; 18 tasks приймають reviewed answers.
- 24 input task/mode/size cases: 6 inputs × manual/tokens × desktop/mobile; wrong → corrected answer, aliases, token case/multiplicity, mixed delete/rebuild, repeated Check, reset та independent scores.
- Окремо 6 порожніх inputs: `Обери або введи відповідь.`, 0/2; reset повертає порожні поля.
- У кожному widget п’ять різних тематичних умов та видимий результат **П5/5 ✓5**. Purpose після reload відновив власну завершену спробу; потім його штатно reset і свіжо пройдено 5/5. Особисті cached attempts не очищені.
- Кожна деталь initially closed без fragment; Enter відкриває, Space закриває; лише правильний owner; direct summary fragment + reload відкриває потрібний detail. На 320px body мав позитивну повну висоту й не був обрізаний.

Браузерні обмеження: у спільного фонового декору є невеликий mobile document overflow (до 17px за viewport у фінальній матриці). У learning content unclipped overflow не знайдено; таблиці мають штатний горизонтальний scroll. Канонічна reference-сторінка також показала decorative overflow. Dark A2 badge має чинний inherited contrast. Це не приховано як «ідеальний загальний layout» і не змінено поза content scope.
Початкова stale viewport-прив’язка, перерваний browser call та ambiguous repeated-token locator були виправлені у перевірочних сценаріях; їхні невдалі спроби не зараховані як PASS.
Temporary viewport override reset, світла тема повернена. No-JS/print/browser-authenticated personalization не перевірялися й не заявляються.

Приватні screenshots: `storage/app/seo-m65-local/browser/{purpose,gerund,infinitive}-{1440,390,320}-{light,dark}.jpg` та `*-widget-5of5.jpg`. Підсумок UI: `browser-acceptance-v2.json` (v1 збережений). Додаткова фінальна перевірка: шість змістових перекладів приховані до Check і видимі після нього; відкриті detail bodies мають overflow-y: visible та scrollHeight == clientHeight, без clipping.

## Ізольовані тести і чесний baseline

| Запуск | Tests | Assertions | Failures | Deprecations |
| --- | --- | --- | --- | --- |
| Fresh M64 BEFORE baseline | 178 | 35108 | 4 known | 1 |
| Профільні M65 | 52 | 7371 | 0 | 1 |
| M65 повторно після technical EOF revision | 52 | 7371 | 0 | 1 |
| Фінальна регресія actual current source | 228 | 42136 | ті самі 4 | 1 |
| Frozen unchanged native JS, accepted-worktree | 24 | native state suite | 0 | 0 |
| Frozen unchanged native JS, served-local | 24 | native state suite | 0 | 0 |

4 старі literal-source failures у `TheoryPracticeSetTokenBankUiTest`:

- `test_manual_practice_inputs_suppress_native_browser_suggestions`
- `test_token_bank_input_keeps_manual_sync_without_registering_word_suggestion_handlers`
- `test_token_bank_source_is_rendered_as_buttons_without_the_plain_slash_text`
- `test_separate_choice_exercise_has_its_own_answers_checking_and_reset_state`

Вони очікують колишні Blade literals після вже чинного component refactor. Assertions не послаблені/не переписані; весь regression suite не називається green.
У захищених 46661 paths немає змін від test harness. SQLite :memory:, cache/session array; робочий .env не завантажувався isolated runner.
Повторний post-EOF runner: `m65-final-eof-7c939de970d8461fa58c5ec0d2ca910c-result.json`, exit 0; після фінального fingerprint також 46661 protected files, changes 0.
Новий M65 test, що помилково називав registered Gerund foreign owner, виправлено на справді unregistered owner; жодний accepted old test не змінено.

M65 suite перевіряє native metadata transitions; backup/apply/transaction rollback/no-op; exact question insert scope; source/SQL guards; zero-query clone-only selector; opaque snapshot failures; registry; local guard; plain feedback; lineage й current runtime compatibility.
Current-runtime tests запускаються на фактичному final controller (30 accepted owners M56–M65 + попередні), не лише historical fixture.

M64 historical lineage перевіряється окремо незміненим M64 chain test на відновленому exact historical controller. Це не підміняє actual current-code acceptance.
Final controller hashes:
- worktree: `b0a5a89a8d9ece5c84ca49f8686fb4725bc71faaff4980b72c95ce9c54cf2f9c`;
- foreign-inclusive ROOT: `7e301caeae7c74f50b3f6dfdab3aace029e7e8867b8bb15e1d7f2ab276820e19`;
- removing only M65 calls/two guards reconstructs accepted M64 worktree: `6769b8beb4bab7c25055fb32d5af5a6cc1f38c0b0e8d547e2fd755f90a1cf9de`.

Native JS factory hash unchanged: `43b4bd3e266594ce7b6ffe697e979ba64c3a824cfb816af06fd8beef31847f8b`. Offline suite covers 18 tasks, 30 options, 8 accepted input variants, 6 deferred translations, 12 grammatical-but-instruction rejections, 0 network calls. Offline checks не названі live browser acceptance.

## Source і доставка

Перед commit whitespace gate виявив один порожній EOF-рядок у новому M65 package class. Його видалено, без зміни teaching master 1.0.0. Явна technical revision 1.0.1 з точними predecessor/successor hashes і diff: `docs/content/m65-technical-eof-correction.v1.0.1.json`. Guard використовує нову exclusive source-sync-v2 квитанцію; v1 не перезаписана. SELECT-only postconditions приймає лише ці два exact технічні переходи, не будь-які невідомі зміни. Shared guard/diagnostic primitives та M63/M64 history не редагувалися.

У worktree додано лише finite M65 .gitattributes правила для збереження frozen bytes, без whitespace exemptions. Сторонню наявну відсутність .gitattributes у served ROOT не відновлювали.

Після technical revision повторено fresh proof → fresh AFTER preview (0 updates / 0 inserts) → два guarded no-op (0 у всіх content/question write counts) → exact route restoration та окремий GET404 → final-readonly-v5. Обидва unused backup paths відсутні. Додаткових контентних записів не було.

Frozen SHA-256:
- master: `c42c6a24735731b1a958a5aaa173b92a32e2b9f7a883274e8d4110d165706c98`;
- source BEFORE: `87e158a8c9a850db1160686603e11f902b3673c45ebb51d5bbf4138332980b97`;
- native patch: `1bd5439ce9729c0d4e1003002d3484b8b3c9aae31a6ce11ef2d9a3ffa6b274d7`;
- builder contract: `252aa15bfe5d405f3b2f0d0b7464722ae20f8c193edc52b328c9bed3f9bcbab5`;
- teaching-only bank audit: `5b21b66a90a699b2e8dab6d1f8c357f3e6feb26c55590be361734d9ace8aa4f0`.

Versioned diagnostics — лише M65 wrappers поверх hash-bound existing read-only diagnostics. Немає auto-apply/startup/hooks/scheduler або залишеного proof route.
Перед delivery перевіряється explicit staged allowlist, frozen checksums, PHP syntax і `git diff --check`. Після normal push повний remote branch SHA звіряється з HEAD; фактичний SHA повідомляється у фінальній відповіді, не self-referential у цьому файлі.
