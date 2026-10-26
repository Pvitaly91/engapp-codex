# M94 — Relative Clauses with Whose and Which

Локально застосовано навчальний пакет однієї української сторінки та вузьке виправлення її standalone caller. Завершено цільові тести, actual browser-практику, суворе порівняння 24 історичних профілів, повторні consumer перевірки, registry, canonical census і незалежний all46 SQL audit після runtime repair. Загального all-PASS немає: історичні FAIL/ERROR, перший consumer regression, strict raw/full-entry FAIL та три Passive source differences збережено. Це pre-commit acceptance record; завершальні source/index/delivery gates нижче не підмінено заявою про вже отриманий PASS.

Робоча гілка: `codex/seo-m94-relative-clauses-whose-which-standard-view`; база — прийнятий M93 `8fbc16ab6dd6c6636b93f4f09406addaf77d3cb2`. Served ROOT зі сторонніми незавершеними змінами не є delivery checkout. Main, production, `.com`/`.ub`, PR/deploy цим пакетом не змінювалися. Доставка дозволена лише scoped commit і normal push цієї робочої гілки після завершальних gates; full commit SHA й незалежна remote verification наводяться у фінальному handoff після Git, а не самопосилально в цьому звіті.

## 1. Єдина ціль і OLD → AFTER

[Relative Clauses with Whose and Which](http://gramlyze.loc/theory/clauses-and-linking-words/relative-clauses-with-whose-and-which): Page 284, category 34, owner `Database\Seeders\Page_V3\ClausesAndLinkingWords\RelativeClausesWithWhoseAndWhichTheorySeeder`.

| Позиція | Збережена identity/metadata | OLD → AFTER |
| --- | --- | --- |
| Subtitle, sort 0 | ID 8739; UUID `b0104b91-d163-5c7f-a213-07ce501f7b00`; header; SQL NULL | Оновлено body; Page.text отримав погоджений український teaser |
| Hero, source 0 / sort 1 | ID 8740; UUID `9cf0dba9-355d-5b1b-a56b-36deeb988f23`; header; B2 | Hero залишено hero; три старі EN приклади збережено й повністю перекладено |
| HTML box, source 1 / sort 2 | ID 8741; UUID `2d66a1e4-cb61-5b4d-bfd8-fdb58b9d2887`; left; SQL NULL; source level відсутній | Службовий filler замінено поясненням; heading `B2 focus` → `1. Належність чи уточнення?` |
| Appended source 2–7 / sort 3–8 | Шість нових native blocks | forms-grid, usage-panels, mistakes-grid, summary-list, navigation-chips, practice-set |

Збережено raw title/H1, owner, category, slug, старі UUID, type, sort і column. HTML box не мігрував у JSON/native stored type: body лишився підтримуваним буквальним HTML із p, а heading передається окремо. Відсутній source level не замінено вигаданим значенням. Новий practice-set має B2/left/source 7/sort 8 та UUID `0bcaa980-15f2-57e6-8266-cb12fe79a870`.

Не додано нових views, стилів, компонентних типів, formatter або grader. У восьми shared-файлах є лише 12 точних M94 registrations; їх вилучення відновлює повні незалежно зафіксовані raw bytes кожного ROOT/WT контексту. Це не порівняння двох контекстів як нібито однакових.

## 2. Мовні й навчальні уточнення

- `Whose + noun` означає належність/зв’язок, не лише юридичне володіння й не лише людину. Відмежовано whose від who’s; пояснено узгодження з потрібною іменниковою групою та відсутність зайвих his/her/its.
- Для which розділено роль підмета й додатка, зайве it, можливий пропуск у відповідній defining-моделі та non-defining обмеження. Defining which не оголошено загалом неправильним замість that.
- Defining/non-defining meaning задається видимими фактами про конкретну групу предметів. Конструкція сама не доводить фактів про всі інші предмети.
- Whole-situation which пояснено через цілу попередню подію, не механічно через найближчий іменник.
- Чотири mistakes мають wrong/right EN і UA та видимі note. Переклади, короткі застереження й основні приклади залишені basic.
- Є одне змістовне «Докладніше» про перебудову всієї групи whose/of which із двома повними моделями; це не сховане продовження основного речення.

У hero буквально залишено: `The designer whose idea won was promoted.`, `The plan, which was risky, worked well.`, `My laptop, which I bought last year, is slow.` Для кожного додано український переклад.

## 3. Шість власних завдань і aliases

Старої власної практики на цій сторінці не було; task map не вигадує OLD task IDs. Додано 2 form + 2 meaning + 2 full-input tasks, 12 показаних options і 7 оголошених правильних ключів включно з canonical.

| Task | Оцінювана функція / canonical |
| --- | --- |
| `m94-form-whose-gallery` | whose для зв’язку неістоти з колекцією, на противагу which/who’s |
| `m94-form-which-subject` | which як підмет у явно додатковій частині без дублювання it |
| `m94-meaning-defining-context` | Дві пошкоджені коробки із шести, не додаткове твердження про всі шість |
| `m94-meaning-whole-situation` | Посилання на дострокове закриття складу, не на будівлю чи кур’єрів |
| `m94-full-whose-studio` | `The studio whose owner we interviewed will open next month.` |
| `m94-full-which-bus` | `We missed the last bus, which delayed our arrival.` |

У full inputs видимі факти, лексичні/порядкові критерії та перемішані групи по 1–3 слова. Обидва завдання доступні вручну й токенами. Додаткова правильна відповідь `We missed the last bus, which made us arrive late.` чесно manual-only: canonical bank не містить усіх її слів. Пояснення feedback — plain text.

Незмінений lexical grader нормалізує регістр і пунктуацію. Full-input score не є незалежним доказом оцінювання ком; контекстний contrast оцінюється окремими семантично відмінними meaning options.

## 4. Окремий тематичний type 4 widget

Підключено існуючий `PolyglotRelativeClausesWithWhoseAndWhichB2LessonSeeder`: actual B2/type 4 pool 48, selected 5, excluded 43 без видалення чи переписування. Frozen matcher selection має IDs `[17164, 17124, 17140, 17123, 17138]`; actual browser показав ті самі п’ять у порядку `[17164, 17140, 17124, 17138, 17123]`. Це різні зафіксовані порядки, не зміна membership.

| Існуючий ID | Settled English |
| --- | --- |
| 17164 | We discussed the conditions under which the contract could be signed. |
| 17124 | We met the writer whose books have been translated into many languages. |
| 17140 | I lost the key which you gave me. |
| 17123 | She works for a company whose office is in the city centre. |
| 17138 | We stayed at a hotel which opened last year. |

Видимі UA conditions і пояснення змінюються лише на snapshot-proven copies. Original questions, answers, options, hints, pivots і saved pools не переписані; UA hint-only options не потрапляють до англійського token bank. Повний exact-bound helper завантажених hints не послаблено.

П’ять моделей не означають повного оцінювання всіх підтем. Whole-situation/defining meaning перевірено own choices; of which лишається explanatory detail. Окреме продуктивне оцінювання кожного пропуску, узгодження й кожної природної перефрази не заявляється.

## 5. BEFORE, actual apply і runtime-only repair

Fresh BEFORE: `efad9a30f6f24c139a57e09f39916bdb815266dc938927d176d53a6e5a3c4cfd`; source capture: `5fd7c5c02445d3cdf9488aefa7136c203b2c77b14767c5783484b01f42a0322c`.

Зафіксовано 8644 paired source paths, 46 таблиць і їхні getColumns schemas, 326 visual paths, 3094 ROOT historical frozen paths, 25 foreign WT raw profiles. Перевірка column schemas не оголошується незалежним інвентарем indexes/foreign keys/triggers. Theory/course початково HTTP 200. Bank supplement охопив три родини/192 різні питання, їхні повні relations і чотири saved pools; 207 memberships не дорівнюють 207 різним питанням.

| Actual content apply | Результат |
| --- | --- |
| Existing updates | 4 rows: Page.text; subtitle body; hero body; box body + єдиний heading |
| Native inserts | 6 rows, включно з одним practice-set |
| Різні змінені rows | 10 |
| Question-family writes / deletes / DDL | 0 / 0 / 0 |
| Початкові повторні apply | Два actual no-op, кожне 0 updates / 0 inserts |

Exclusive backup raw-byte збігається з reviewed preview. Після початкового apply nonce route вилучено, попередні bytes відновлено точно, actual removed endpoint відповів 404. Початковий final-readonly PASS: `56dc45047b981574a74649f02dbe21c3f05b193fbd16ffc97591094135ab77fe`. Усі 46 normalized-before fingerprints, protected rows, старі timestamps, 192 questions і чотири saved pools підтверджені; target має 3 old UK + 6 new rows, без старих EN/PL.

### Перший реальний consumer FAIL не приховано

Перший AFTER14 (`016731c6a91e91919f62663c96b833c0e8e790eb1a7dca202603273ef25091dc`) збережено: 0/14 literal complete entries exact, 5/14 raw bodies exact, 6/14 keyed question payloads exact; course learning DOM exact і status/route/kind flags 14/14. У восьми SSR embedded theory payloads був реальний drift. Це не названо випадковим shuffle чи CSRF.

Причину встановлено в actual native `BelongsToMany` hydration: `migratePivotAttributes` вилучає три `pivot_*` лише з поточних attributes, а rawOriginal їх зберігає. Надто суворе порівняння повних масивів у M94 standalone preservation відхиляло ці справжні framework models.

Виправлено тільки M94 Package standalone seam: перевіряються exact три aliases `pivot_question_uuid`, `pivot_text_block_uuid`, `pivot_position`, правильна завантажена native Pivot relation, таблиця/ключі/значення й відсутність інших dirty/extra attributes. Лише unsaved clone синхронізує доведений поточний масив перед чинними строгими guards. Original model/Pivot не змінюється; неправильні callers/owners/locales/pivot state не отримують ширшого допуску. Немає нового запиту, DB write чи зміни shared exact-attribute helper.

Approved runtime delta — тільки Package `2ad03e868201c5f7c85e726f1e6bf66f841b8f90264f8da1486a9d8a3dca90d0` і writer `2b1533ac837e0f3e17427afbc8a3a188ded1516590923e0bf6804e20c5971c68`; writer змінив лише `SYNC_RECEIPT` v1 → v2. Builder presenter і всі frozen learning artifacts незмінні. Original preview/backup/source-sync-v1/final-v1 збережені. Runtime manifest — `b097ffab44a6545f6c1294054453d7dccafe2d1f312a04293fe25a3d5ccfa28b`; actual source-sync-v2 — `14002f0b20d84324b27fa092cb00b4e38f919aa8d311b7d62f563e80463ed9ab`.

Другий runtime proof/preview-v2 підтвердив after-state 0/0; actual no-op також дав 0/0 без нового content write. Route bytes відновлено до SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, removed endpoint реально повернув 404; cleanup receipt SHA `dd0bf9f8fcdd018562a58ab4f6e74ae3d5c839a3c565cfa40647659086fddcbb`. Незалежний final-readonly-v2 SHA `58564e91ef8828a4f6347c7674ea3ad405412b19b04694c9551dc041d396db40` зберіг original preview/backup authority, точний runtime revision і literal equality усіх 46 actual AFTER fingerprints із початковим final-v1. Нової content/DDL/question-family зміни немає. Окремий пізніший repeat final-readonly-v3 SHA `d202f47367ae927ad2c1a6ec6cf430d829183de796973d7941cdef32e2f2c84b` також підтвердив exact усі 46 AFTER fingerprints без змін.

## 6. Лише погоджене форматування delivery

Початковий source-sync-v2 передував атрибутному доповненню й не є доказом фінальних attrs. Перший запит на suffix було відхилено до запису; після окремого людського дозволу «лише ці 30 правил M94» main додав тільки exact LF rules у WT `.gitattributes`.

ROOT attributes залишається відсутнім. Повний старий prefix 81742 bytes/SHA `642e28142a57290510f29b95835dc6fc2154b995f43818a246cacb4e251e1c0b` збережено. Exact approved suffix receipt: `2e0ee3ffcedc43374e20c2188e125a0b10aa8a85ec702bcdfb0761f1c571976f`; WT full after SHA `4194c9f3d888335c295129b899ce23f7e58604a86251cbacda0152618322aa2e`. Це не generic append exemption і не renormalization старих джерел.

30 rules стосуються тільки нових LF artifacts/runtime/tests/report. Існуючий canonical definition лишається raw CRLF/SHA `aea1e1a48d2cab59f9dea2ec09eb4a2e9f73658fb2f221148438dab7452d9b79`; його вже staged окремою command-local `core.autocrlf=false` операцією без зміни глобальної config, історичних правил чи working bytes. Для решти файлів застосовано звичайний scoped staging. Остаточна перевірка всіх 40 clean index blobs є окремим delivery gate; checkout-roundtrip тут не оголошується вже пройденим.

Початковий unstaged `diff --check` повідомив чотири CR-at-EOL на controller lines. На actual 39 staged paths без report звичайна staged перевірка завершилася exit2 з рівно 86 CR-at-EOL diagnostics: 82 canonical-definition і 4 controller; інших trailing diagnostics не виявлено. Окрема command-local `core.whitespace=blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol` перевірка дала exit0. Початковий strict nonzero не стирається; це EOL-aware check, не renormalization чи загальний whitespace waiver.

Фактичні EOL profiles у цьому 39-path index: `.gitattributes` — i/lf, w/mixed; controller — i/crlf, w/crlf; matcher і course manifest — i/lf, w/crlf; canonical definition — i/crlf, w/crlf. Raw working-source audit й clean index audit перевіряють різні представлення; історична index-normalization не дозволяє переписувати старі working bytes.

Delivery allowlist скінченний: 40 paths, включно з report/attributes та новим one-method attributes boundary test. До фіналізації цього тексту staged тільки 39 paths без report. Після його додавання потрібні незалежний filesystem audit з 8644 paired profiles/foreign25/exact12hunks/attrs prefix-suffix/raw report hash та exact40 staged-index/privacy/EOL перевірки. Цей pre-commit звіт не оголошує ці ще не виконані gates успішними.

## 7. Автоматичні тести — фактичні, не загально зелені

| Final M94 targeted context | Unique methods у цьому run | Assertions | F / E | Deprecations | Child exit |
| --- | ---: | ---: | --- | ---: | ---: |
| ROOT application + ROOT test/factory sources | 51 | 5034 | 0 / 0 | 1 | 0 |
| ROOT application + WT test/factory sources | 51 | 5034 | 0 / 0 | 1 | 0 |

Це 51 unique methods і 102 executions, не дві незалежні WT/ROOT application installations. Тести працювали в SQLite `:memory:`/testing, array cache/session, CLI opcache off, без робочого `.env`/MySQL. У кожному run 675 protected files byte-exact unchanged. Final target receipts: ROOT `db65aea456dcb92511b8b405a294ae54cb543497e575f5b567fdab0495c24036`; WT-source `00bec2b2378de410fd0333d1162d7563bb8181e2db8fa91e21e86ef8f325c0a8`.

Старі власні failed attempts і попередні 46/50-method passes не видалені. Чотири нові pivot-boundary tests використовують actual framework hydration, правильні/хибні loaded states і clone/no-query/no-mutation boundaries. Один attributes test звіряє весь prefix і exact30 literal rules.

Offline actual native factory окремо в ROOT і WT перевірила 6 own controls, 7 declared keys, 12 options і 2 full inputs; кожен контекст мав 302 assertions/exit0. Незмінений widget replay підтвердив 5 settled моделей у двох source-контекстах, зокрема два окремі the в 17164. Offline replay не замінює browser UI evidence нижче.

### Остаточні 24 історичні профілі, AFTER v3

| Метрика | Fresh BEFORE | Final AFTER v3 |
| --- | ---: | ---: |
| Profiles | 24 | 24 |
| Method executions / unique logical methods | 1301 / 1208 | 1301 / 1208 |
| Assertions | 308776 | 308248 |
| Failures / Errors / Skips | 82 / 1 / 0 | 86 / 1 / 0 |
| Reported deprecations / PHPUnit warnings | 24 / 1 | 24 / 1 |
| Child exit distribution 0 / 1 / 2 | 3 / 20 / 1 | 2 / 21 / 1 |

93 executions — перекриття тих самих logical methods. Усі old test bytes та 675 protected files у кожному профілі збережено. Final index SHA `67cb3c81f9f6549c6e370c461cb96931d138346716a89f8b5706da17bf151668`; pure comparison summary SHA `78e19eb29cd9461b6d98956c0f8b7f2dd79e5f285490c62773814f1f82f87338`, comparator exit1. Це strict FAIL, не approved all-green.

Чотири нові adverse methods у `M93SourceBoundaryTest`:

1. `test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile`;
2. `test_native_validator_formatter_and_grading_engine_keep_their_independent_raw_bytes`;
3. `test_m89_complete_hint_snapshot_helper_is_reused_byte_exact_without_new_delta_authority`;
4. `test_attributes_keep_root_absent_and_worktree_exact_prefix_or_explicit_reviewed_suffix`.

Кожен зупинився на спільному ранньому M93 controller whole-file AFTER guard: actual profile більше не входить до старих зафіксованих M93 raw profiles після чотирьох M94 controller additions. Точний twelve-hunk reversal діагностує цю source transition, але не перетворює відмову на PASS і не підтверджує пізні assertions, до яких виконання не дійшло.

Чотири повідомлення вже failing M74/M73/M77 lineage methods змінилися рівно через доведені нові source SHA; усі залишилися failures, unattributed parsed method-message changes 0, resolved adverse outcomes 0. Assertion delta −528 = −536 у чотирьох ранніх M93 відмовах +8 у двох досі passing dynamic catalogue methods. Нічого не виправлялося в old tests, harness чи parser.

Warning/deprecation sections за parsed summary незмінні, але raw stderr профілю `interaction` в AFTER v3 не тотожний BEFORE. Обидва stderr мають по 31357 bytes; після 21-символьного timestamp решта bytes, включно з сутнісною SQLite `question_hints` error і stack, тотожні. Це не усунутий error і не нормалізований PASS: strict stderr equality false. Повний comparator output SHA `deda370627c227bff2c980998f5675601f670267a47b682b5b39cb0fd2dea899`, receipt SHA `d8dbf9387d76999443007a9c68d82c1ae4a0ff78760acd1c63d9dab5ada9e493`.

## 8. Actual browser і SSR

Після runtime fix повторно перевірено 6 layout states: 1440×1000, 390×844, 320×844 у light/dark. Frozen widget payload до/після standalone repair byte-exact. Browser layout receipt SHA `94de359db408c8abb8c91c1d74b291a515159aca57c790308fa2024006620adc`.

Learning cards вписуються у свої колонки: зафіксовані праві межі 1368/374/304 для відповідних ширин. Fonts Manrope loaded, broken images 0, technical visible leaks false, один native widget і footer доступні. Водночас document widths у фінальному спостереженні — 1425/400/326: на вузьких екранах є document overflow. Проміжні earlier-AFTER narrow observations 394/327 теж збережені; причину різниці чи «покращення дизайну» не приписано M94. Нуль visual source changes не означає нуль global overflow.

| CSS viewport, light/dark | BEFORE → final main width/scrollWidth | Learning right bound | BEFORE → final document width | Raster BEFORE / AFTER |
| --- | --- | ---: | --- | --- |
| 1440×1000 | 877/877 → 877/877 | 1368 | 1425 → 1425 | 1390×990 / 1390×990 |
| 390×844 | 358/358 → 358/358 | 374 | 401 → 400 | 375×812 / 375×812 |
| 320×844 | 288/288 → 288/288 | 304 | 326 → 326 | 305×804 / 305×804 |

CSS viewport, DOM bounds і JPEG raster мають різні зафіксовані розміри; screenshot не видається за 1:1 CSS-pixel доказ. Новий контент змінює зображення й висоту сторінки, але pixel-identical screenshot comparison не виконувався. Знімки збережено приватно; bounds не встановлюють причини transport/raster різниці чи redesign.

Actual own practice:

- 8 nominal cases = 2 full inputs × manual/token × desktop/mobile; після Package fix усі 8 повторено, receipt SHA `a6b2fba2bd390cf308fcb24a9894ecd955823a08df1c7927695ed9422083ac48`;
- усі 12 показаних options і 7 declared keys охоплено в збереженому own-practice receipt `8015fbbe2a72ea9130d622f69b42ec9de628fc524e76c146d54e22e751d42f66`;
- збережено перевірки empty/whitespace/wrong/edit/recheck/reset, mixed manual/token і delete-return; shared grader не змінено;
- один semantic detail перевірено keyboard open/close, deep link і reload; початковий locator/harness failure лишився в доказах, не замінений заявою про browser defect.

Actual widget пройдено 5/5: усі IDs і settled sentences відповідають frozen membership/keys, два the мають окремі token instances, removal/return перевірено. Було чотири Next transitions; final restart button видимий, але не натискався, personal progress не очищався. Receipt `3a831eb1b3515d88de2a6c6366ed1c9c7291cda4472b2b1e759afb13961c90e3`.

SSR/browser metadata: один H1 `Relative Clauses with Whose and Which`, незмінний title, UK locale/LearningResource, description із hero intro. Canonical досі має `.com` origin як у BEFORE; це не було виправлено і `.com` не запитувався. Robots meta у DOM має null; HTTP robots headers не зафіксовані, їхній статус — not_checked, без додаткового GET чи вигаданого PASS.

Actual старі anchors — 8739 і 8741 — збережені. Hero8740 не був DOM anchor і до M94; перше припущення про його відсутність не є regression. Чотири нижні navigation links знайдено з точними локальними canonical paths: огляд Relative Clauses, Defining, Non-defining, Omitting. Початковий overrestrictive selector, що повернув порожній список, не є доказом відсутньої навігації.

Print, no-JS і повний WCAG audit не виконувалися. Zoom не змінювався; browser UI evidence не є загальною accessibility certification.

## 9. Старі споживачі, registry і census: окремі осі приймання

Fresh BEFORE14 охопив course та standalone SSR/JSON saved-пули. Перший реальний AFTER regression вище збережено. Окремий actual corrected AFTER14 SHA `76c29cdf177661869a33b5851ae7a4b62d3d378dfa7f8d60c7f501da03c5385b` отримав 14 HTTP200, keyed payload equality 14/14, HTML facts equality 14/14 і course learning DOM 1/1. Raw bodies exact лише 5/14, literal complete entries exact 0/14: strict FAIL не стираються. Відновлення keyed caller boundary підтверджено свіжими даними після вузького pivot repair; інших причин raw/full-entry відмінностей не приписано.

Перший extra-controls AFTER16 (`6ad22af26909f4ed06ff3a4ea0d22686920fe6f4254f2936242c6dbf92c3dc90`) зберіг 0/16 complete/raw exact, 15/16 literal main DOM і один exact approved Page284 teaser projection; keyed inline payloads 15/15. Окреме literal спостереження CSRF value differences поза цим teaser не стирає raw FAIL і не пояснює будь-який інший drift. Самі CSRF значення не публікуються. Окремий actual AFTER16-v2 SHA `d896e20fb61d20722fe741985b6cb5aef672a2eaf3bf4a022c9a88e4356934d3` має 16 HTTP200, 15 literal main DOM matches і один exact approved target teaser projection. Усі 15 actual inline question arrays keyed-exact; порожня category array не рахується шістнадцятим question payload. Raw bodies exact 0/16, complete entries exact 0/16: strict raw/full-entry FAIL збережено.

Додатковий pure literal аналіз existing AFTER2 captures встановив конкретні raw dimensions без waiver: extra16 — 15 з відмінними тільки спостереженими CSRF values та одна category з CSRF + exact approved teaser. Old14 — 5 raw JSON exact, 6 CSRF-only, 2 CSRF + question-item order і 1 AllLevels compose з CSRF + question-item order + переставленим тим самим multiset із шести native CEFR-badge spans. Після врахування лише цих конкретних спостережень іншого raw remainder не залишилося; це не висновок про причину session/shuffle і не нормалізація strict comparator. Evidence SHA `aa439e07b7199cd6b51326605771f765d267a9a3fdcf5b5520b3e96c5e9c3571` та `d29bd68e12b778a23fa18956be5fd1ffb3ced6d2d11cc2d41b8b781e892c91e4`. Literal raw/full-entry FAIL counts незмінні; самі CSRF values не публікуються.

Початкові targeted1 + whole254 registry commands до pivot repair збережено окремо. Revised targeted1 і whole254 також фактично пройшли: failed0, old253 semantic rows exact за винятком лише whole HTTP SHA, new-owner field differences0, all46 SQL fingerprints exact, child exit0. Whole raw stdout SHA `4793cbe7857f5666a14847be0c0801eb3153a7cdcfea1a08f07dd15a22a3f28a`, summary SHA `91cc7517581c77a0c6d621c1c6d2d2e949163395c4c4a263c08e19b61c59c462`. Це не загальна raw-HTML equality і не native browser grading acceptance.

Fresh canonical census BEFORE знайшов 254 unique UK Page_V3 owners, 253 active accepted builder bindings і одного remaining M94 owner. Actual AFTER census має ROOT/WT по254 unique UK owners і по254 active contracts, actual registry before253/after254, new1, remaining0, binding uncertainties0. Кількість отримана з canonical UK definitions та accepted AFTER bindings, а не permanent254 cap. Receipt SHA `01fa5e51bc61e6ada5b1862ab9dbd5a51b684dea5f04f9f358529896585113ff`; process exit1 і кваліфікований статус `DIFFERENCES_RETAINED_NOT_PASS` збережені через три decoded Passive context differences. Нуль remaining не означає ROOT/WT source parity або загальне all-SEO PASS.

## 10. Окремий read-only Passive audit

| Сторінка / Page | Exact decoded body scope і practice UUID | Прийняте WT походження | ROOT-only quality source / вплив |
| --- | --- | --- | --- |
| [Present Simple Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-present-simple), 113 | `blocks[9].body`; `d70401dc-cefd-5b86-af69-084629f63663`; 13 leaf differences | M56 v1.0.1; commit `b64e338e7dd0e515d4fcb72794c38a8c4f721936` | `docs/content/present-simple-passive-practice-quality.v1.json`; WT 2 selects / 2 choices / 2 inputs, ROOT і captured SQL 2 / 2 / 4 |
| [Past Simple Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-past-simple), 110 | `blocks[10].body`; `14bdb806-7325-5ec6-bab4-8f857a1f1769`; 13 leaf differences | M56 v1.0.1; commit `b64e338e7dd0e515d4fcb72794c38a8c4f721936` | `docs/content/past-simple-passive-practice-quality.v1.json`; WT 2 / 2 / 2, ROOT і captured SQL 2 / 2 / 4 |
| [Future Simple Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple), 107 | `blocks[11].body`; `4990c3a9-12f3-5228-b52f-145c2b747389`; 15 leaf differences | M58 v1; commit `5c314ccfedaba52934cdfd7cae3aef3ce7f1aa89` | `docs/content/future-simple-passive-practice-quality.v1.json`; WT 2 / 2 / 2, ROOT і captured SQL 2 / 2 / 4 |

Це реальні decoded зміни лише в названих practice bodies, не тільки EOL; усі інші decoded fields незмінні. WT raw відповідає прийнятій M93 базі й наведеному M56/M58 походженню. ROOT відповідає окремим ROOT-only quality-after джерелам і captured SQL practices. Це не доводить ROOT/WT parity чи окремого browser/grader/course acceptance. M94 не редагує, не переносить і не синхронізує ці definitions/hooks/архіви. Історичний Present compose-bank source hash не пояснений EOL; поточну bank-to-SQL parity не аудовано.

Окремий follow-up має явно володіти лише трьома exact practice-body revisions та аудитом шести тематичних banks/quality hooks. Потрібно зберегти UUID/IDs, metadata і caller isolation, перевірити aliases, token composability, mixed/видимі widget сценарії та цільові regression tests, а локальний apply виконати лише після finite preview/backup і guarded перевірок. Не переписувати історію й не копіювати весь foreign ROOT у WT; вирішити відмінності як окрему погоджену content revision, а не приховати їх під parity.

## 11. Джерела, артефакти й межі доставки

Primary source catalogue містить пряме читання [British Council: defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-defining-relative-clauses), [British Council: non-defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-non-defining-relative-clauses), [Purdue OWL: relative pronouns](https://owl.purdue.edu/owl/general_writing/grammar/relative_pronouns/index.html) і [Collins: whose](https://www.collinsdictionary.com/dictionary/english/whose). [British Council reference](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/relative-pronouns-relative-clauses?page=2) прочитано через офіційний pagination URL після timeout базового URL: це одна стаття, не два незалежні джерела. Редакційну відповідь LearnEnglish Team не ототожнено з learner comment. Cambridge endpoints із 403 та недоступні Merriam-Webster endpoints не оголошені прочитаними змістовими доказами; publisher exercises, автоматичні corpus examples і learner comments не копіювалися.

Публічні навчальні джерела: [master](../content/m94-relative-clauses-whose-which.v1.json), [UK text](../content/m94-relative-clauses-whose-which.uk.md), [components](../content/m94-relative-clauses-whose-which.components.v1.json), [tasks](../content/m94-relative-clauses-whose-which.tasks.v1.json), [editorial](../content/m94-relative-clauses-whose-which.editorial.v1.json), [coverage](../content/m94-relative-clauses-whose-which.coverage.v1.json), [bindings](../content/m94-relative-clauses-whose-which.bindings.v1.json), [navigation](../content/m94-relative-clauses-whose-which.navigation.v1.json), [builder contract](../content/theory-sentence-builders/m94-relative-clauses-whose-which.v1.json), [bank audit](../content/m94-sentence-builder-bank-audit.v1.json), [checksums](../content/m94-relative-clauses-whose-which.checksums.v1.json).

Canonical definition: `database/seeders/Page_V3/ClausesAndLinkingWords/RelativeClausesWithWhoseAndWhichTheorySeeder/definition.json`; finite patch: `database/content-patches/m94-relative-clauses-whose-which.v1.json`.

| Зафіксоване приймання | Фактичний результат |
| --- | --- |
| Second-proof no-op, exact route cleanup404, independent all46 | 0/0; final-v2 `58564e91…`, repeat-v3 `d202f473…` |
| Corrected AFTER14 і AFTER16 controls | Keyed/learning boundaries підтверджені; raw/full-entry FAIL retained |
| Revised targeted1 + whole registry | 1 + 254, old253 semantic exact, F0 / exit0 / all46 exact |
| Canonical AFTER census | UK254, active254, new1, remaining0, uncertainty0; exit1 через3 source differences |
| 39-path staged EOL check без report | Plain exit2/86 CR-only diagnostics; окремий EOL-aware exit0 |

Завершальні delivery gates після остаточного report hash:

- Незалежний paired filesystem audit: exact40 allowlist, frozen/runtime bytes, 8644 captured paths, 25 foreign raw profiles, усі shared reversal/attrs bounds і відсутність private additions.
- Після додавання report — exact40 staged clean blobs, staged privacy/source/EOL diff та збереження foreign/index boundaries. Успішність цих перевірок установлюється фактичними receipts, не цим pre-commit текстом.
- Лише після успішних gates — scoped commit і normal push робочої M94 гілки, перевірка remote SHA. Main/production/деплой не входять до дозволу. Повний commit SHA буде у фінальній відповіді після Git; report не намагається self-pin власний commit.

Raw/full-entry FAIL лишаються FAIL; collector exit0 не дорівнює strict acceptance. У delivery не включаються runtime caches, backups/dumps, proofs, cookies/CSRF values, screenshots, private SQL rows, `.env` чи сторонні незавершені зміни.
