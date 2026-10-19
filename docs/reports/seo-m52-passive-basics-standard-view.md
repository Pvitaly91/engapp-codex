# M52 — Passive Voice Basics: звіт локального впровадження

Дата: 2026-10-15 (Europe/Kiev). Лише `http://gramlyze.loc`, три чинні UK owners. Статус: контент застосовано, реальні сторінки й інтерактивність прийнято. Production, `.com`/`.ub`, SSH, main та PR не використовувалися.

## Виправлені сторінки

| Сторінка | Page / category | Display / technical | Continuing blocks | Власна практика | Вбудований конструктор | Point details |
|---|---|---|---|---|---|---|
| [Formation Rules — Правила утворення пасиву](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formation-rules) | 1 / 1 | A2 / A2 | 9 + UK subtitle | 2 selects / 2 choices / 2 inputs | 5/5, exact A2 bank | 1 |
| [Negatives & Questions in Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-negatives-questions) | 98 / 1 | B1 / B1 | 11 + UK subtitle | 2 / 2 / 2 | 5/5, exact B1 bank | 1 |
| [Passive with Modal Verbs — Пасив з модальними](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-modal-verbs) | 97 / 1 | B1 / B1 | 11 + UK subtitle | 2 / 2 / 2 | 5/5, exact B1 bank | 1 |

Кожен input підтримує ручну відповідь і перемішані токени. Це шість inputs із двома способами вводу, а не дванадцять різних завдань. Власні 18 завдань та 15 існуючих питань конструкторів обліковуються окремо.

## Навчальний зміст і чинний дизайн

Збережено корисні вихідні приклади, таблиці, усі continuing UUID/index/type/column/level, tags, Page identity й footer labels. Нову практику append виконано після source navigation; caller відображає її внизу навчальної частини перед footer. Навчальні data використовують наявні hero/forms/usage/table/mistake/summary/navigation/practice components. Views, CSS, JS, шрифти, header, sidebar, фон і answer engine не змінювалися.

- Formation: самодостатня базова модель be + V3, Active → Passive зі збереженням часу й учасників, узгодження, V3, omission за метою повідомлення, by як agent/cause проти with як instrument. `By someone` не оголошено універсальною помилкою; arrive без object і event/state межі пояснено в basic.
- Negatives/questions: not та інверсія з першим допоміжним, а не завжди literal be; short answers, wh, запитуваний учасник, компактні perfect/continuous приклади. Statement questions граматичні в належному комунікативному контексті; neutral inversion задано лише як критерій конкретної вправи.
- Modal passive: modal + be + V3, have to/need to/ought to, prohibition/no obligation/possible negative result, питання й short answers. Старий perfect шар збережено; контекст розрізняє deduction, known non-performance/criticism, unrealised possibility та expectation.

Англійські приклади мають точні українські переклади; corrections зберігають intended tense, negation, modal та учасників. У чотирьох English-stimulus meaning tasks точний переклад відкладено до Check, щоб не видати значення; у двох N/Q tasks потрібний український критерій питання видимий одразу. Options — кандидат, не розгорнутий авторський ключ.

Три змістовні detail owners визначено явно, без глобального runtime word threshold:

| Detail | Owner | Перевірений deep link |
|---|---|---|
| Event чи state | formation-forms `/items/1` | [block-54](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formation-rules#block-54) |
| Neutral vs statement question | negative-yes-no `/sections/0` | [block-3211](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-negatives-questions#block-3211) |
| Perfect із майбутньої точки оцінки | modal-perfect-meaning `/sections/3` | [block-3180](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-modal-verbs#block-3180) |

## Авторські джерела й freeze

Main незалежно прочитав усі три початкові definitions, усі basic/detail learning fields, 18 own tasks, 15 actual linked conditions/ordered answers/options/hints та source/live UK links перед freeze. Primary grammar review: [British Council Passives](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/passives), [Active and passive voice](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice), [Perfect aspect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/perfect-aspect).

Cambridge задані direct URLs повертали tool internal error; це не названо HTTP 403 чи повним прямим читанням. Для потрібних правил використано publisher-indexed canonical/localised тексти; фактичні URL/access записані в frozen master та editorial journal. Приклади й вправи не копіюють видавничі exercise banks. [People-first guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) використано для точності й користі; SEO gains не обіцяються.

Versioned артефакти:

- `docs/content/m52-passive-basics.v1.0.0.json`, SHA-256 `e1a020633c8e45869da213be82310885f717ab90b6e685f7c13d83a007dcf904`.
- Повна читабельна редакція: `docs/content/m52-passive-basics.v1.0.0.uk.md`.
- Finite component map і редакційний BEFORE → AFTER: `m52-passive-basics-components.v1.json`, `m52-passive-basics-editorial.v1.0.0.md`.
- Independent bank contract/audit: `docs/content/theory-sentence-builders/m52-passive-basics.v1.json`, `docs/content/m52-passive-basics-bank-audit.v1.json`.
- Source-only BEFORE і exact AFTER: `database/content-patches/m52-passive-basics-before.v1.json`, `database/content-patches/m52-passive-basics.v1.json`.
- `docs/content/m52-passive-basics-checksums.v1.json` зв’язує всі release sources і три active definitions.

Нові файли зафіксовано UTF-8/LF з одним EOF newline до hashes. Три існуючі active definitions зберігають CRLF і один EOF; old frozen sources не нормалізувалися. Blank-at-EOF exemptions для M52 не додавалися; явна cr-at-eol політика стосується лише цих існуючих CRLF definitions.

## Наявні банки: без пересівання

| Урок | Exact existing bank | Eligible UK/page-wide/type-4 pool | Selected IDs |
|---|---|---|---|
| Formation | PolyglotPassiveVoiceBasicsLessonSeeder | 24 A2 | 240–244 |
| N/Q | PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder | 12 B1 | 37311–37315 |
| Modal | PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder | 12 B1 | 37239–37243 |

Використано чинне supported `linked_practice.intro`: finite criteria щодо заданих аспектів, modals, початку й обставин речення та потрібної лексики. Це умови конкретної п’ятірки, не універсальні заборони інших природних англійських формулювань. Нових questions/options/ordered answers/links — **0**; старі banks/payloads/grader/matcher/localisation не змінено.

## Guarded local apply

Fresh independent BEFORE: 34 UK rows та 62 EN/PL rows; усі 46 SQL fingerprints незмінні після anonymous BEFORE GET. Безпечна ціль: document/application `D:/DEV/htdocs/gramlyze.loc`, MySQL `localhost:3306`, database `gr2`, SiteMode development при APP environment production. Physical Windows listener/vhost/runtime evidence звірено; environment та серверні налаштування не редагувалися.

Fresh web/CLI proof через випадковий local-only nonce GET → fresh preview → exact diff review → exclusive backup → unchanged inherited transaction/locking/postconditions → два repeated no-op → live acceptance → final read-only audit.

| SQL зміна | Кількість |
|---|---|
| Page.text | 3 |
| UK text_blocks.body, continuing | 34 |
| Appended native practice-set | 3 |
| Deleted / question-family / links / progress writes | 0 |

Reviewed plan digest: `b1283f9b37f33330e36ba1c017b580336d2e5e4f93ba8cbaebaafc44453e202f`. Exclusive private `backup-v1.json` SHA-256 `938746d7deccdbe771f7ef8a3060a400d39d869c6d13ff524c0e4f39b75f3fe0`. TEXT capacity 65535 bytes; найбільший новий body 11432 bytes.

Обидва repeated apply — `0 updated / 0 inserted / 0 deleted`; повторні backup не створювалися. Route видалено після no-op; окремий fixed-loopback GET повернув 404. `routes/api.php` byte-exact restored: SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`.

ROOT/WT shared transfer — лише literal owned hunks, із збереженням чужих bytes/EOL. Особливо TestJsV2Controller: M51-null exclusion залишається до M52 call. Source-sync-v2 зв’язує 19 exact paths; PageController не змінено. У ROOT не відновлювався чужий deleted `.gitattributes`.

## Реальне приймання

- Три HTTP 200; незмінні H1, title, canonical, breadcrumb та LearningResource name/headline. Descriptions відповідають новому hero/basic. Усі 28 старих `block-*` anchors збережено. Canonical `.com` — лише незмінний DOM string; production GET не виконувався. Local header `noindex, nofollow, noarchive` збережено.
- 18 real browser states: 1440×1000, 390×844, 320×844 × light/dark × три сторінки. Три exercise groups, два inputs, один widget, один спочатку закритий point detail на кожній. New content cards overflow = 0.
- Власні 18 tasks перевірено на desktop/mobile; шість inputs × manual/token × дві ширини = 24 cases. Empty/partial/wrong/repeated wrong/edit correct/recheck/reset, disabled used token, deletion-return/rebuild та scores 0/2 → 1/2 → 2/2 працюють.
- Три details: Enter відкриває лише свій content, Space закриває, deep link доступний, після reload знову закрито. Переклади й owner labels фактично видно.
- Три native widgets: кожен прийняв одну правильну відповідь і перейшов на П2/5. У N/Q перевірено «Вікна не розбили під час бурі» з двома незалежними instances The/the.
- Read-only matcher + fresh HTTP gate: M52 3/3; весь реально discovered registry — **79 targets, 0 failures, 0 writes**. Попередні 76 targets не переписано.
- Real browser spot-check: canonical Past Perfect Continuous Forms і попередній M51 Modals of Deduction зберігають practice/widget. Зроблено 18 viewport screenshots і підсумковий `m52-formation-accepted.jpg`; temporary viewport reset, light mode restored.

## Регресія й тести

Фінальний audit: усі 34 UK rows відповідають AFTER, 62 EN/PL rows і metadata точні BEFORE. Усі 46 protected table fingerprints, зокрема всі question families та progress, незмінні. 326 visual files і 2236 historical frozen files — byte-exact BEFORE.

30 old consumer URLs — HTTP 200; вісім saved tests, 480 original questions, per-question SSR/JSON/compose payload і help body, а також `.theory-design` DOM усіх трьох courses збігаються з незалежним BEFORE. Маскується лише верхній controller shuffle order через keyed identities, не поля питань чи learning DOM. Anonymous HTTP не змінив жодної таблиці.

| Перевірка | Результат |
|---|---|
| M52 package / patch / local-target guard | 22 tests, 2460 assertions, PASS |
| Applicable M50/M51 functional tests | 42 PASS; old assertions unchanged |
| Historical controller compatibility | 2 tests, 42 assertions, PASS |
| Canonical components / point detail / widget acceptance / practice completion | 51 tests, 5389 assertions, PASS |
| Frozen M52 unchanged native JS factory | 22 tests, PASS; 36 options, 18 IDs, 6 manual, 6 token, 9 accepted canonical/contraction answers |
| Previous M50/M51 / contractions / practice completion JS | 110 tests, PASS |

PHP runs використовували testing, private SQLite `:memory:`, array cache/session; working `.env` не завантажено. Кожен isolated wrapper підтвердив 46661 protected files / 0 changes. Є одна inherited PHP 8.5 deprecation, не прихована й не «виправлена» зміною старої конфігурації.

Початковий suite знайшов реальний null-chain дефект — виправлено тільки M52 shared hook. Окремо виправлено own fixture expectation: hero/navigation не належать native presenter registry. Поточний M50 literal test очікує історичний pre-M51 byte baseline; його не переписано й не послаблено. Оригінальний метод без змін виконано проти exact historical private M50 fixture. Compatibility chain звіряє current M52 → independently captured M51 BEFORE → accepted M50 Git blob → unchanged M50 projection.

## Межі підтвердження

- JS-off і print: `not_checked`; їх не оголошено PASS.
- На мобільному збережено inherited зовнішній shell overflow (16–32 px у цій матриці); усередині нових content cards overflow не виявлено. Новий дизайн/глобальний overflow fix не входить до M52.
- Низький контраст деяких dark CEFR pills/labels — inherited; не редизайнено.
- У widgets UI-graded 3 відповіді, не всі 15. Усі 15 перевірено source/live inventory, exact UK links, type/CEFR та matcher/HTTP payload.
- Для довільного unlisted wrong input збережено native generic feedback. Declared wrong probes і правильні/accepted відповіді мають повний перекладений author feedback.

## Evidence і Git

Private runtime receipts залишаються лише в ROOT `storage/app/seo-m52-local`; SQLite test receipts — у source WT `storage/app/seo-m2-local`. Cookies, credentials, dumps/backups, caches, screenshots, private diagnostics та сторонні незавершені файли не входять до commit.

Основні receipts: `before-v1.json` (`2ca4aa32…0bff`), `before-old-consumers-v1.json` (`d3d7abe5…f106`), `source-sync-v2.json` (`d7ba31ab…24db`), `final-readonly-v1.json` (`086ab0f0…0c39`), `after-old-consumers-v1.json` (`22ecc777…7c82`), `browser-acceptance-v1.json` (`85cf2c50…6ef`), `registry-sentence-builders-v1.json` (`a24fbae2…afe`). Повні hashes зберігаються у private evidence.

Base commit: `0e2ed40c438400e2f2a3992314220fdf206db9a7`. Робоча гілка: `codex/seo-m52-passive-basics-standard-view`. Цей звіт включено до scoped M52 commit; його повний SHA та підтвердження normal push наведено в завершальному повідомленні. Main, PR, merge, force push та deploy не виконуються.
