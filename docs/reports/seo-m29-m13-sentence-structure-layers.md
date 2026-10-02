# M29 — M13 Sentence Structure: локальне приймання

Дата: 2026-10-03. Зміни застосовано до робочого `http://gramlyze.loc`; production не перевірявся й не змінювався.

## База та межі

Accepted M28 та fetch-verified origin/M28: `9edca5db593585d09411ec8107272dd24e8151ab`.
Нова робоча гілка: `codex/seo-m29-m13-sentence-structure-layers`.
Tracked worktree перед роботою чистий: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`.
Основний checkout `D:/DEV/htdocs/gramlyze.loc` мав сторонні незавершені зміни; їх не скидали, не stash-или й не комітили.
Його HEAD до роботи: `41820a2bebdf69004fa7209a2a38457f93efabbd`.

Тільки технічна проєкція accepted M13, власні point-level details і явно погоджена адаптація static self-check → interactive practice.
Нові правила/explanations не генерувалися; навчальні тексти не скорочені й не перефразовані.
Перед синхронізацією перевірено відсутність накладання на dirty changes у трьох definitions і п'яти shared templates; попередні runtime sources збережено приватно в `storage/app/seo-m29-local/preexisting-source`.
Не змінювали Apache/XAMPP/hosts, `.env`, залежності, production, банк питань або saved tests. Не запускали seeds, migrations, cache/session clear чи full DB restore.

| Урок | Identity | Accepted Git blob |
| --- | --- | --- |
| [Cleft Sentences and Emphasis](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis) | `Database\Seeders\Page_V3\SentenceStructure\CleftSentencesEmphasisTheorySeeder` | `088f8a1ac2c99471c85b2ad0e4b6a0895ac0ad6e` |
| [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) | `Database\Seeders\Page_V3\SentenceStructure\ComplexNounPhrasesTheorySeeder` | `9fdd6453b8c5dbdd1b057102cef55f14d6f5316b` |
| [Ellipsis, Substitution and Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference) | `Database\Seeders\Page_V3\SentenceStructure\EllipsisSubstitutionAndReferenceTheorySeeder` | `1358e3d04590cb39e9729c4d6cb9009b0442e969` |

Locale uk, type theory, ancestry рівно `sentence-structure` без parent. Slug, title, level, subtitle, hero, tags та test relations незмінні.
Before manifest SHA-256: `cbc80910b642203243672d8c143d4998437c21f0c3d775d82cba6e50ac078977`.
Фінальний finite package SHA-256: `e28b75ee73f02fb7ed12bff25ef35423ee724a384927156eac877a3f79429d06`.
Обидва versioned JSON зафіксовані `-text`, щоб Git не змінював hash-bound байти.

## Структура та власність detail

Було: subtitle, hero, один великий авторський box зі статичною самоперевіркою.
Стало: ті самі subtitle/hero + 9 / 9 / 10 native sections. Перший старий box зберігає UUID/order/id; змінені лише type/body.
Basic — повноцінний урок: основні пояснення, таблиці, переклади, попередження, розбори та повні cohesion paragraphs доступні одразу.
Detail — тільки наявний додатковий авторський фрагмент конкретного пункту. Немає штучних, порожніх або загальних кнопок для всієї секції.

У таблиці «Пункти з detail» — точні 1-based індекси. Для кожного такого пункту є рівно один власний disclosure; він не відкриває сусідів.
IDs: `m29-<cleft|noun|ellipsis>-section-S-point-P` та `block-m29-<...>-section-S-point-P-detail`.
Старі `block-ID`, section і self-check anchors збережені.

| Урок | Секція | Native type | Basic points | Пункти з detail |
| --- | --- | --- | ---: | --- |
| Cleft | 1. Не ще одна формула, а відповідь на конкретне запитання | comparison-table | 0 + таблиця | — |
| Cleft | 2. Контраст: виправляємо припущення, а не граматику | usage-panels | 4 | 1, 2, 3 |
| Cleft | 3. Заперечний фокус і уточнювальне запитання | usage-panels | 3 | 1, 2, 3 |
| Cleft | 4. What-cleft: дія, потреба та результат | usage-panels | 4 | 1, 2, 3 |
| Cleft | 5. Узгодження без надмірних заборон | usage-panels | 3 | 1, 2, 3 |
| Cleft | 6. Акцент у невеликому тексті | usage-panels | 4 + повний абзац | — |
| Cleft | 7. Типові помилки та невлучний вибір | usage-panels | 3 | — |
| Cleft | 8. Практика | practice-set | 6 завдань + widget | — |
| Cleft | 9. Пов’язані способи керувати увагою | usage-panels | 1 | — |
| Noun | 1. Головне слово тримає групу разом | usage-panels | 1 | — |
| Noun | 2. Карта компонентів перед головним словом і після нього | comparison-table | 0 + таблиця | — |
| Noun | 3. Три покрокові розбори | usage-panels | 3 | 1, 2, 3 |
| Noun | 4. Доповнення змісту чи додатковий опис? | usage-panels | 4 | 1, 2, 3 |
| Noun | 5. Узгодження: не орієнтуйся на найближчий іменник | usage-panels | 2 | 1 |
| Noun | 6. Прикладка й неоднозначне приєднання | usage-panels | 5 | 1, 2, 4, 5 |
| Noun | 7. Корисне ущільнення та помилки перевантаження | usage-panels | 2 | 1, 2 |
| Noun | 8. Практика | practice-set | 6 завдань + widget | — |
| Noun | 9. Поглибити окремі зв’язки | usage-panels | 1 | — |
| Ellipsis | 1. Пропустити, замінити чи вказати? | comparison-table | 1 + таблиця | 1 |
| Ellipsis | 2. Ellipsis: що саме не повторюємо | usage-panels | 4 | 1, 2, 3, 4 |
| Ellipsis | 3. So/not: значення та дієслівні моделі | usage-panels | 6 | 4, 5, 6 |
| Ellipsis | 4. Do і do so: повторювана дія та межі заміни | usage-panels | 4 | 1, 2, 4 |
| Ellipsis | 5. One/ones: коротке нагадування | usage-panels | 4 | — |
| Ellipsis | 6. This/that, such та дві явно названі альтернативи | usage-panels | 3 | 1, 2, 3 |
| Ellipsis | 7. Абзац: простеж усі зв’язки | usage-panels | 6 + повний абзац | 5 |
| Ellipsis | 8. Типові помилки: неоднозначність не лікується стисненням | usage-panels | 1 | — |
| Ellipsis | 9. Практика | practice-set | 6 завдань + widget | — |
| Ellipsis | 10. Від структури до зв’язного тексту | usage-panels | 1 | — |

Фактичні disclosure counts: **12 / 13 / 15**. Незалежність перевірена для кожного disclosure на кожному viewport/theme та без JS.
У Noun обидва прочитання attachment мають окремі basic points; коми supplementary apposition не втрачено.
У Ellipsis основні обмеження so/not, do/do so, one/ones, former/latter і всі виправлення неоднозначного `her` залишені в потрібному контексті.
Повні англійські cohesion paragraphs, переклади та розбори Cleft/Ellipsis не замінено конспектом.
Невідомі/stale metadata, UUID, order, locale або body повертають повний stored basic без приховування й без detail-кнопок.

## Інтерактивна практика

Кожна сторінка: 2 selects, 2 choices, 2 token/manual cases і linked widget. Усі групи мають «Перевірити», correct/wrong feedback та reset.
Token groups перемішуються, manual input працює; Backspace повертає token, autocomplete вимкнено, suggestions не відкриваються.

| Урок / група | Завдання → правильна відповідь / допустимі альтернативи |
| --- | --- |
| Cleft selects | `It wasn't the ___ ...; it was the lack of evidence.` → `cost`; `What the panel did was ___ an independent review.` → `request` / `to request` |
| Cleft choices | Editor vs removed material → B; A граматичне, але має інший фокус, і це прямо пояснено. `It is the local volunteers who help visitors.` → A |
| Cleft token/manual | `When was it that the supplier changed the delivery date?`; `What the team needed was a clearer brief.` (контекст достатнього обладнання показано) |
| Noun selects | Head у `those three carefully edited policy reports` → `reports`; `description/descriptions ... incomplete` → `is / are` |
| Noun choices | Content clause claim vs relative device → A; drawing/technician/workshop — пара двох явно різних прочитань → A |
| Noun token/manual | `Leila, the project coordinator, approved the change.` — внутрішні коми обов’язкові; `We need a new-staff training plan. The training will start in May.` / явне `a plan for training new staff` із тим самим часом |
| Ellipsis selects | `translate the abstract`; `any` |
| Ellipsis choices | `I hope not / I don't think so` → A; curator `did so` + display `is too` → A, неправильні auxiliary models відхиляються |
| Ellipsis token/manual | Handbook/revision із явно названими former=workshop, latter=handbook; `Olena told Marta that Olena's notes were missing.` / `Olena told Marta, “My notes are missing.”` (straight quotes також приймаються); неоднозначне `her` відхиляється |

Terminal punctuation не обов’язкова; смислово потрібні внутрішні коми Leila обов’язкові. Generic contraction handling не змінювали.

### Exact linked-bank inventory (read-only)

| Тест | Primary seeder (prefix `Database\Seeders\V3\Polyglot\`) | Level/type | Наявний банк | IDs |
| --- | --- | --- | ---: | --- |
| [Cleft](http://gramlyze.loc/test/sentence-structure/cleft-sentences-emphasis) | `PolyglotCleftSentencesEmphasisC1LessonSeeder` | C1 / 4 | 48 | 17841–17888 |
| [Noun](http://gramlyze.loc/test/sentence-structure/complex-noun-phrases) | `PolyglotComplexNounPhrasesC2LessonSeeder` | C2 / 4 | 48 | 18609–18656 |
| [Ellipsis](http://gramlyze.loc/test/sentence-structure/ellipsis-substitution-and-reference) | `PolyglotEllipsisSubstitutionAndReferenceC2LessonSeeder` | C2 / 4 | 48 | 18561–18608 |

Widget показує 5 питань тільки свого primary bank/type/level. В усіх 12 браузерних станах IDs звірено з read-only inventory.
AllLevels та type 0 не підмішуються; questions, options, answers, hints, pivots і saved bank не змінювалися.

## Реальне застосування до БД

Fresh physical/vhost/CLI/web proof: Windows `DESKTOP-3C05HGF`, ROOT application/document identity, MySQL loopback `localhost:3306`, database `gr2`.
PHP 8.5.10; APP environment production, але SiteMode саме `development` для gramlyze.loc. Середовище не переналаштовували.
DNS тільки 127.0.0.1; активний vhost, Apache PID/PID-file та фізичний mysqld підтверджені guard.

Final preview digest: `05adc3baf48864f19dcb755152dd0657f1ee53049017a34c80fd576ad69f9010`.
Private exclusive backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m29-local/m29-before-v2.json`.
SHA-256: `e027f4e8d533daa67c335a0a9df75716083fe14d35d81163d67d744e76b247d1`.
Backup збережено; він не входить до Git.

| Page ID | Старий box ID | Final update | Final inserted IDs | Практика ID/order |
| ---: | ---: | --- | --- | --- |
| 297 | 8780 | type/body | 12379–12386 (8) | 12385 / 9 |
| 312 | 8825 | type/body | 12387–12394 (8) | 12393 / 9 |
| 311 | 8822 | type/body | 12395–12403 (9) | 12402 / 10 |

Фінальна транзакція: **updated 3, inserted 25, deleted 0**. Після неї 11 / 11 / 12 UK rows включно із subtitle/hero.
Postconditions: exact canonical bodies/owners/locale/order/UUID, незмінні protected fields та count/hash 20 protected tables. Counts отримані з diff, не підставлені константами.
Repeated apply: **no-op, updated 0, inserted 0**; `m29-unused-noop-v2.json` не створено.

Перший apply-v1 виявив зайве екранування namespace у трьох linked-practice seeder strings; widget був порожній.
Зроблено guarded scoped rollback саме власного M29: updated 3 / deleted 25, без full DB restore. Старий exclusive backup-v1 збережено.
Виправлено generator і додано exact-name assertion для трьох banks. Fresh preview-v2 review підтвердив: змінився тільки цей config у трьох practice payloads; решта авторських даних і update diff ідентичні.
Потім виконано наведений final apply-v2 та no-op. Відкочування і повторне застосування пояснюють gaps у auto-increment IDs.

Temporary nonce GET proof routes працювали лише exact HTTP gramlyze.loc/raw loopback/Windows, відхиляли Forwarded headers, повертали allowlisted safe runtime fields і nonce-bound digest, виконували SELECT-only; без session/cookies/secrets.
Обидва routes видалено після apply/no-op; GET кожного nonce → 404. `routes/api.php` відновлено byte-for-byte до початкового SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689` зі збереженням попередніх сторонніх змін. Route не комітиться.

## Fidelity та автоматичні перевірки

Source → canonical finite package → actual DB → initial HTTP HTML → DOM after reload.
Exact basic/detail/table-cell comparison включає punctuation, author wording, приклади й переклади; word-multiplicity comparison виключає лише погоджену заміну static practice.
19 semantic negative fixtures відхиляють втрату `not`, неправильний cleft focus/order, request/need substitution, agreement/head erosion, content-vs-relative flip, внутрішні коми, merged attachment readings, некоректні one/ones, hope/not, do so, former/latter, `her`, зниклий UA translation, detail чужого пункту, duplicate anchor та missing practice answer.
Package hash захищає точні дані; stale/manual DB conflict, wrong owner/category/parent/locale/order, partial state, backup collision, rollback і repeated no-op перевірені ізольованими fixtures.

- PHP core-v3: **85 tests / 1961 assertions**, exit 0. M29 package/patch/guard, M28 package/patch, M27 package, M26 point details. Один відомий PDO deprecation; див. limitations про VirtualAlloc/fingerprint evidence.
- Окремий PHP regressions-v1: **76 tests / 1505 assertions**, exit 0, stderr порожній. M26 content package/patch та interactive practice package/patch; M27 patch/guard, M28 guard, M11 physical/runtime guard. Та сама isolated SQLite/array/private-storage preflight, один відомий PDO deprecation. Цей запуск не підсумовувався з core у вигаданий загальний total.
- Node: **45 tests**, PASS, окремий запуск M29/M28/M27 practice + generic contractions.
- Vitest: **3 files / 32 tests**, PASS — sections, navigation, sidebar.
- 10 нових PHP-файлів lint PASS; 5 canonical/package JSON parse PASS; CJS syntax checks і `git diff --check` PASS.

Shared rendering changes мінімальні: M29 finite presentation opt-in, author HTML fragment, native independent points/table columns/outro, contextual feedback тільки за наявності per-item config. Старий generic шлях не замінено.
Actual ROOT Vite build: exit 0, 57 modules; manifest і 4 referenced assets існують. Manifest SHA-256 `3a02fd268ba16acb8e988de9bb0e566a3d9f050a6b60eb520b8075ea36532fe8`.
Generated assets не комітяться; dependencies/config не оновлювалися.

## Реальні HTTP/браузерні перевірки

Private final evidence: `storage/app/seo-m29-local/acceptance-v5-http.json`, `acceptance-v5-browser.json`, `elements-v1-elements.json`.
GET-only guest .loc, без authorization/Referer; separate browser contexts. Browser policy не дозволяє production requests.
Chromium headless shell 1217 / Playwright; viewport 1440×1000 та 390×844, light/dark.

- **12 M29 states PASS**: усі 40 власних details на кожному стані; mouse/Enter/Space/focus-visible, незалежність і два одночасно відкриті пункти; без fetch при disclosure; closed reload; deep fragment; print opens all і screen відновлює попередній state.
- **9 no-JS pages PASS**: M29×3 + M27×3 + M28×3; native disclosure usable без JS/запитів.
- **10 control URLs PASS**: M26 Forms, Present Perfect Continuous Forms, ordinary Present Perfect Forms, M14 Participle Clauses Basics, три M27 та три M28. У них немає M29 sections; metadata, content hashes та detail counts незмінні.
- M27 **22 details** і M28 **20 details**: own toggle, deep fragment, print/reload і practice correct/wrong/reset/alternatives PASS.
- Elements supplement: **5 exact DOM-after-reload fidelity checks**, **4 M26 practice pages**, **10 focused screenshots**, PASS. Missing Leila commas і ambiguous her реально відхиляються; потрібні коми й пряма цитата реально приймаються.
- Повна M26 regression-v3: **5 actual GET pages / 56 unique points / 20 browser states / 5 no-JS pages**, PASS. Навчальний overflow PASS; strict document overflow не PASS через random background. У діагностиці додано лише opt-in `GRAMLYZE_M26_VIEWPORT_SCREENSHOTS=1`: bounded viewport screenshots замість дуже довгих full-page captures; усі функціональні/DOM/keyboard/print/fidelity assertions незмінні, default попереднього runner залишено full-page. Evidence у приватному `storage/app/seo-m26-local/m29-regression-v3-browser.json`.

Візуально перевірено focused screenshots Cleft/table, Noun/apposition/two attachments і практику/мобільну таблицю; файли залишені тільки в приватній evidence directory, не в Git.

Learning-content overflow: **0 px** у main і видимих section cards усіх 12 M29 states. Таблиці мають overflow-x auto/scroll; Noun на mobile: client 322 px, scroll 576 px, фактичне прокручування праворуч PASS.
Decorative overflow окремо: document 0–9 px у поточному M29 run, background shape excursions приблизно 0.20–13.65 px. Це random shell background, не навчальний контент; global overflow-x hidden не додавали, фон не «лікували» і document-wide zero не заявляємо.
У фінальному окремому M26 run document range 0–12 px; learning content PASS, local failed requests / HTTP errors / console page errors = 0.

## SEO та controls

13 initial GET rows до/після: HTTP 200; title, H1, description, canonical, robots, X-Robots-Tag, OG/Twitter незмінні.
10 control main-content hashes незмінні. Для 3 M29 перевірено exact author fidelity та збереження старих anchors.
Ordered sitemap: **554 URLs**, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`, до/після ідентичний.
Canonical production strings у HTML не означають звернення до production: .com/.ub не перевірялися.

## Обмеження та проміжні невдалі спроби

- Це targeted acceptance, не повний repository suite, SEO production audit або Lighthouse/CWV.
- Відомий PDO::MYSQL_ATTR_SSL_CA deprecation та застарілий Browserslist dataset не виправлялися поза scope.
- PHP core-v3 фактично PASS, але child stderr мав `VirtualAlloc ... paging file is too small`; wrapper fingerprints для цього запуску були порожні. Це не доказ захисту робочих файлів. Preflight підтвердив isolated SQLite :memory:, array cache/session, private storage/views, no working .env, CLI OPcache off. Protection робочої БД окремо підтверджений guarded apply/no-op, не цими порожніми fingerprints.
- Один initial HTTP run отримав transient MySQL out-of-memory 500 під час паралельної перевірки; пізніше був transient Alpine wait timeout. В остаточному послідовному acceptance-v5 всі local requests/console/error checks PASS. Налаштування пам’яті/сервер не змінювали.
- M26 regression-v1 зупинився на GET timeout; v2 після 5 HTTP/56 points і 17 states зупинився на неможливості Chromium зберегти full-page screenshot. Остаточний bounded-screenshot v3 пройшов усі 20 states та 5 no-JS; функціональні assertions не послаблювалися.
- Google Fonts stylesheet/font requests у sandbox можуть мати ERR_NETWORK_ACCESS_DENIED; у final M29 states зафіксовано 24 таких зовнішніх font failures, окремо від local failed requests. UI перевірявся з доступними fallback fonts.
- Перший browser fidelity diagnostic неправильно склеював `<br>` через textContent; виправлено діагностичне читання HTML, не авторський текст.
- Під час другого temporary proof placement тип Request спочатку резолвився як facade: endpoint 500, guard відмовив без DB writes; виправлено FQCN, після чого proof/transaction пройшли. Endpoint видалено, routes exact restored.

Production не перевірявся й не змінювався. Наступні уроки автоматично не починалися.
