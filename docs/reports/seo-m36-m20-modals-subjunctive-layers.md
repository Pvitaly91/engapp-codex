# M36 — M20 Modal Perfect / Subjunctive / Subtle Modal Meanings

Дата приймання: 2026-10-05. Scope: рівно три UK theory owners M20; без M21, production, main, PR або деплою.

## Версія та межі

Accepted base: `4644a1729d326b9a967d03c397998506f00d704b`, перевірений також як актуальний `origin/codex/seo-m35-m19-passive-reporting-layers` на початку роботи. Ancestry check — exit 0.

Робоча гілка: `codex/seo-m36-m20-modals-subjunctive-layers`.
Ізольований checkout: `D:/DEV/htdocs/gramlyze.loc/storage/app/m36`.
Реальна HTTP/application ціль: `D:/DEV/htdocs/gramlyze.loc`, `http://gramlyze.loc`, локальна MySQL `gr2`.

Початковий ROOT HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd` та сторонній dirty state не reset/stage/commit. Окремий попередній PPC checkout і його index не змінено. Через Windows long-path помилку app-managed worktree створено короткий локальний Git worktree; залежності не оновлювалися. До робочого сайту синхронізовано лише finite M36 джерела, із перевіркою та резервуванням початкових bytes. Shared views змінено лише additive M36 hooks; наявні PPC hunks у ROOT збережено.

## URL, identities та accepted blobs

Префікс усіх theory seeder identities: `Database\Seeders\Page_V3\`.

| Урок / локальна theory URL | Identity suffix | Category / level / page ID | Accepted definition Git blob | Point disclosures |
| --- | --- | --- | --- | ---: |
| [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | `ModalVerbs\ModalPerfectAndDeductionTheorySeeder` | modal-verbs / C1 / 303 | `06bc76a1db559c4e1c3fd49d9279c82b79227503` | 0 |
| [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | `FormalEnglish\SubjunctiveAndFormalStructuresTheorySeeder` | formal-english / C1 / 304 | `e38c3d1b80c339098b8404df66377f11a6367907` | 0 |
| [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | `ModalVerbs\SubtleModalMeaningsTheorySeeder` | modal-verbs / C2 / 320 | `b0ea70c81d37fc750f1a2725e6dc9f130ef65f1d` | 0 |

Відповідні перевірені тестові URL:

- [Modal Perfect and Deduction — test](http://gramlyze.loc/test/modal-verbs/modal-perfect-and-deduction).
- [Subjunctive and Formal Structures — test](http://gramlyze.loc/test/formal-english/subjunctive-and-formal-structures).
- [Subtle Modal Meanings — test](http://gramlyze.loc/test/modal-verbs/subtle-modal-meanings).

Accepted before manifest SHA-256: `cd9fb58a143e36a434a5960084fd2d513c917d11688e2be4add63899dac5e1e5`.
Finite projection SHA-256: `bcf663e413811f1fda15e02f6e89df2f6bcd091a87686a7cf623cd587767d970`.

## Native structure і fidelity

Кожна definition має незмінний hero та 8 native sections: 5 `usage-panels`, 1 `comparison-table`, 1 `practice-set`, 1 `summary-list`. Разом 9 definition blocks і 10 фактичних DB rows із subtitle. Колишній one-big box замінено першою native section зі збереженням його ID/UUID; сім наступних sections вставлено окремими детермінованими UUID.

Повний author basic видимий до будь-якого кліку: не конспект, не заміна тексту компактним резюме. Збережено порядок навчального тексту, пунктуацію, приклади, переклади, author links, висновки та контекстні обмеження. Три таблиці збережено повністю: 4 колонки × 4 body rows, min-width 1000px, явні 250px на колонку; горизонтальний scroll належить тільки таблиці.

У Subjunctive залишено активні/пасивні, заперечні та past-request моделі, різні значення suggest/insist, регіональну ремарку та lest. У Subtle збережено точний діалог Ada/Ben, функції well/as well/just, поради та розмежування необхідності й виконання. У Modal Perfect збережено спостереження проти висновку, can’t/may not/mustn’t, два could have та критика проти очікування. Нових граматичних узагальнень або навчальних фактів не додано.

Вісім старих section anchors на сторінку, self-check anchors і фактичні DB references збережено. Перевірка old anchor links, reload, scroll, menu/TOC та print не підмінена DOM fixture. Presentation працює тільки для точного owner/locale/UUID/order/type/body; невідповідний блок лишається повністю читабельним у fallback. Runtime word-count threshold не додано.

## Усі detail candidates

Усі 18 candidate fragments залишено `visible_basic`; meaningful retained details — **0 / 0 / 0**. Це семантичні рішення у finite projection. Дев’ять кандидатів мають <30 слів (4 / 3 / 2), що є лише діагностичним сигналом. Для всіх `example_pair_count=0`, `non_example_words=words`. Exact HTML fragments і рішення versioned у projection; таблиця нижче ідентифікує кожний candidate, не приховує жодного.

ID у таблиці = префікс + номер section + суфікс. Префікси: Modal `m36-modal-perfect-section-`, Subjunctive `m36-subjunctive-section-`, Subtle `m36-subtle-section-`. Суфікс скрізь `/source-final-paragraph`, крім останнього Subtle `/source-final-list`.

| Урок / section | Words / non-example words | Candidate та явне рішення basic |
| --- | ---: | --- |
| Modal 1 | 9 / 9 | «Усі ситуації нижче вигадані й призначені для мовної практики.» Короткий scope caveat, без окремого поглиблення. |
| Modal 2 | 43 / 43 | Couldn’t have attended; can’t enter як нездатність/заборона; mustn’t не замінює заперечний висновок. Основне контекстне розмежування, потрібне до кліку. |
| Modal 3 | 20 / 20 | Спочатку спостереження, потім сила висновку; не вимагати єдиного modal без заданої впевненості. Основний алгоритм поруч із повною таблицею. |
| Modal 4 | 23 / 23 | Явно невикористана можливість: доступна дія та її невиконання задані контекстом; не ототожнювати здатність/дозвіл/припущення. Центральний контраст could have. |
| Modal 5 | 31 / 31 | Needn’t have оцінює виконану непотрібну дію, не обов’язково докір; посилання на наступний урок. Core meaning плюс коротка cross-reference. |
| Modal 6 | 24 / 24 | Подія й час → факт/висновок → значення → have/V3/заперечення → переклад без вигаданих фактів. Короткий підсумковий алгоритм. |
| Subjunctive 1 | 25 / 25 | Sends повідомляє звичну дію, send виражає вимогу; приклади не юридичні/медичні приписи. Основний контраст плюс scope caveat. |
| Subjunctive 2 | 45 / 45 | Не будь-який оцінний прикметник/дієслово дає mandative-модель; request/require/recommend/demand, essential/necessary/advisable проти obvious. Межа базового правила. |
| Subjunctive 3 | 24 / 24 | Requested/recommended датують прохання; that-clause не має механічного backshift; дата поради не є виконанням. Core grammar поруч із таблицею. |
| Subjunctive 4 | 21 / 21 | Insist-вимога проти наполягання на істинності; has kept → keep змінює зміст. Основне семантичне розмежування. |
| Subjunctive 5 | 53 / 53 | Indicative mandatives іноді трапляються, особливо BrE; тут прямо задано форму без should; indicative факту — інше значення. Видима межа прийнятності, не optional depth. |
| Subjunctive 6 | 33 / 33 | Suffice it to say та be that as it may формальніші за in short/even so; вибір за функцією, не штучне ускладнення. Коротка register note. |
| Subtle 1 | 19 / 19 | Діалоги вигадані; C2 — редакційна позначка, не гарантія офіційного рівня кожного вислову. Scope/level caveat. |
| Subtle 2 | 31 / 31 | May/might as well — практична пропозиція, не додаткова ймовірність і не may well; виконання не випливає. Центральний функціональний контраст. |
| Subtle 3 | 43 / 43 | Wise + to-infinitive; застереження не завжди слабше за should; довжина не гарантує ввічливості; worth/wise не нові модальні дієслова. Основні моделі поради. |
| Subtle 4 | 29 / 29 | Непотрібна виконана дія не завжди критика; didn’t need to → needn’t have потребує факту виконання. Основна інформаційна межа. |
| Subtle 5 | 40 / 40 | У діалозі пояснення-кандидат, порада й непотрібна дія; не вигадувати встановлену причину, проведене порівняння чи успішне усунення плутанини. Пояснення безпосередньо до реплік. |
| Subtle 6 | 83 / 83 | Повний список worth/wise, well/as well, didn’t need to/needn’t have, wise/must, задана модель/граматичність. Основний checklist помилок форми й змісту. |

Жодну кнопку «Докладніше» не вигадано заради кількості. Нуль disclosures є результатом аудиту, а не пропуском реалізації.

## 18 оригінальних practice cases

На кожній сторінці — рівно 6 author cases, по 2 `selects`, `choices`, `inputs`. Кожний `source_index` має одного native owner. Усі original prompts, підпункти, constraints, keys та пояснення збережені в `author_self_check`; не замінені placeholder-питаннями. Feedback plain text має коректні межі абзаців, без злиття слів через HTML.

| Сторінка | Index / original heading | Native type | Збережений навчальний контекст |
| --- | --- | --- | --- |
| Modal Perfect | 1. Ознака чи висновок? | selects | Відкрита коробка, розірвана пломба; must have opened не доводить особу, час чи причину. |
| Modal Perfect | 2. Два задані висновки. | inputs | Nora must have unlocked the door; Eli can’t/cannot have been at the studio at nine; обидва підпункти. |
| Modal Perfect | 3. Два could have. | choices | Ravi: невідомий результат проти but he chose to walk. |
| Modal Perfect | 4. Критика чи очікування? | selects | Непідписані папки проти неперевіреного прибуття посилки. |
| Modal Perfect | 5. Виправ три граматичні помилки. | choices | Might of → might have; went → gone; don’t can → can’t/cannot; усі три переклади. |
| Modal Perfect | 6. Відредагуй повідомлення. | inputs | The studio was dark yesterday. The rehearsal may/might/could have been cancelled; не must або факт was cancelled. |
| Subjunctive | 1. Вимога чи факт? | selects | Requests that the entrance be kept clear проти reports that the entrance is clear; вимога не доводить виконання. |
| Subjunctive | 2. Задана базова форма. | inputs | The coordinator recommends that each reviewer read the brief by Friday; без should/reads. |
| Subjunctive | 3. Заперечна й пасивна вимога. | choices | Assistant not delete the comments; reviewers be informed by the assistant by noon; збережені виконавець і строк. |
| Subjunctive | 4. Минула рекомендація. | choices | On Monday, Leo recommended that Emma check the heading before publication; виконання невідоме. |
| Subjunctive | 5. Два значення suggest та insist. | selects | Ознака факту, вимога переміщення та наполягання на істинності; усі три підпункти. |
| Subjunctive | 6. Формальний сполучник. | inputs | She labelled both folders so that the reviewers would not/wouldn’t confuse them; правильна функція lest, без доданого успіху. |
| Subtle Modal | 1. Well чи as well? | selects | May well explain проти might as well rehearse; не стверджувати фактичної репетиції. |
| Subtle Modal | 2. Контекст just. | choices | Might just make the final selection: невелика реальна можливість, не «щойно» чи гарантія. |
| Subtle Modal | 3. Три задані моделі поради. | inputs | You might want to check; It may be worth checking; You would be wise to check … : the previous copy listed one name twice; усі три речення та підстава. |
| Subtle Modal | 4. Необхідність окремо від виконання. | selects | Didn’t need to print + so I didn’t / but I did / невідомо; відсутність необхідності не заборона. |
| Subtle Modal | 5. Чи можна перетворити? | choices | Omar needn’t/need not have booked another room лише після відомого виконання. |
| Subtle Modal | 6. Відредагуй мінінотатки. | inputs | Ira needn’t/need not have printed … yesterday; Missing clips could well explain today’s delay; It may be worth checking the cupboard. |

Client interactions: selected option і letter states, «Перевірити», wrong/correct feedback, first-attempt score, reset, Enter, manual input, shuffled finite groups по 1–3 слова, click/remove/re-availability токенів, типи апострофа та допустимі авторські альтернативи. API-сидери або спеціальні question-bank fixtures для вправ не підміняли реальні банки.

### Semantic negative fixtures

PHP перевіряє 33 мутації реальних author fragments: зміну факту/висновку, часу, підмета, джерела, моделі й сили висловлювання. Node/browser має 33 actual-input негативних випадки: may замість заданого must; may not/couldn’t замість заданого can’t; V3/have/of; nine/ten та yesterday/today; reads/should/read; Friday/Thursday; both/one; reviewers/writers; wise/worth/want; before/after printing; twice/once; didn’t print замість виконаної непотрібної дії; definitely/as well замість could well; today’s/yesterday’s; наказ замість поради. Це finite fixtures, не універсальний semantic scorer. Форма може бути природною, але не відповідати явно заданій моделі.

## Реальний linked-bank inventory

Джерело — SELECT-only actual rows/pivots, до й після apply однакові. Префікс classes: `Database\Seeders\V3\`. Для кожного AllLevels group — 12 питань на кожному A1/A2/B1/B2/C1/C2, 72 загалом. Own widgets обмежені exact primary class + type + level + фактичними ID; slug сам по собі не визначає банк.

| Page | Exact class suffix | Type / levels / count | Actual question IDs |
| --- | --- | --- | --- |
| 303 | `Polyglot\PolyglotModalPerfectAndDeductionC1LessonSeeder` | 4 / C1 / 48 own | 18129–18176 |
| 303 | `ModalVerbs\ModalPerfectAndDeductionAllLevelsV3Seeder` | 0 / A1–C2 / 72 | 25407–25478 |
| 303 | `Polyglot\PolyglotModalPerfectAndDeductionAllLevelsLessonSeeder` | 4 / A1–C2 / 72 | 25839–25910 |
| 304 | `Polyglot\PolyglotSubjunctiveAndFormalStructuresC1LessonSeeder` | 4 / C1 / 48 own | 18177–18224 |
| 304 | `FormalEnglish\SubjunctiveAndFormalStructuresAllLevelsV3Seeder` | 0 / A1–C2 / 72 | 50175–50246 |
| 304 | `Polyglot\PolyglotSubjunctiveAndFormalStructuresAllLevelsLessonSeeder` | 4 / A1–C2 / 72 | 50679–50750 |
| 304 | `Polyglot\PolyglotVerbToBePastAllLevelsLessonSeeder` | 4 / C1=3, C2=5 / 8 foreign | 6686, 6687, 6689, 6699, 6701, 6702, 6703, 6709 |
| 320 | `Polyglot\PolyglotSubtleModalMeaningsC2LessonSeeder` | 4 / C2 / 48 own | 18993–19040 |
| 320 | `ModalVerbs\SubtleModalMeaningsAllLevelsV3Seeder` | 0 / A1–C2 / 72 | 25479–25550 |
| 320 | `Polyglot\PolyglotSubtleModalMeaningsAllLevelsLessonSeeder` | 4 / A1–C2 / 72 | 25911–25982 |

Загалом linked IDs: 192 / 200 / 192. Власні primary banks — 48 / 48 / 48, не весь linked inventory. Чужі 8 VerbToBePast IDs виключені з Subjunctive own widget. Їхні питання й pivots не виправлялися як сторонній scope. Усі browser samples мають 5 IDs із фактичного відповідного primary inventory.

## Guarded local apply та незмінність даних

Фізичний proof: application/public identity ROOT, точний Windows Apache vhost, loopback HTTP, local MySQL host `localhost`, port 3306, server `DESKTOP-3C05HGF`, database `gr2`; CLI/web safe identity збігаються. APP environment `production`, але SiteMode для `.loc` — `development`; це не доказ звернення до production. `.com`/`.ub` не відкривалися.

Процедура: fresh proof → fresh preview → exact field/source review → exclusive backup → transaction → SELECT postconditions → repeated no-op → завершене live acceptance. Read-only HTTP capture виконувався до записів, after HTTP/браузерні перевірки — на реальних змінених сторінках.

Fresh reviewed preview `m36-preview-v2.json` SHA-256: `098f70832e67e6c6f1c2fb9238c6f669c3f62965eb546fa4c90c0e77aa1a2930`. Незалежний review підтвердив точні fields кожного update/insert, source hashes, old IDs/UUIDs, category/locale/order та source backup. Preview v1/v2 мали однаковий digest.

| Факт | Реальний результат |
| --- | --- |
| Scope | UK rows трьох exact owners 303 / 304 / 320 |
| Updates | 3: IDs 8798 / 8801 / 8847; лише type + body |
| Inserts | 21: по 7 native blocks, orders 3–9 |
| Deletes | 0 |
| Total text_blocks | 6468 → 6489 |
| Non-target text_blocks | 6465, SHA-256 `fd806926b549b118ca16aaacde88c55a4c61751db12780460975c0bc70655c98`, незмінні |
| Інші захищені таблиці | 19/19 fingerprints незмінні |
| M26–M35 DB owners | 32/32 snapshots незмінні |
| Pages/categories/subtitle/hero/metadata/tags/relations | Незмінні |
| Questions/answers/options/hints/pivots/progress | Незмінні |
| Repeated preview | state after, updates=0, inserts=0 |
| Repeated original-plan apply | status no-op, updates=0, inserts=0 |
| Repeated snapshot | Повністю тотожний after snapshot, крім часу фіксації |
| No-op backup | Не створено зайвого backup-файлу |

Exclusive DB backup збережено, не закомічено:
`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m36-local/m36-backup-v1.json`.
Файловий SHA-256: `f915ffa817d7157df6163d6b4d3e280af2e60a8f7c5ce0480f312a1c6c1e731b`.
Exclusive source backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m36-local/source-backup-f88e9d85a38653d6`.

Тимчасовий nonce route мав GET-only/loopback/exact-host/exact-document policy, відхиляв HEAD/forwarded/auth/cookie та не писав у БД. Видавав тільки allowlisted safe identity/fingerprint. Після apply/no-op видалений; actual fresh guest GET повернув **404**, IP 127.0.0.1. ROOT `routes/api.php` byte-identical до початкового стану: SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Нормалізовано тільки trailing whitespace до початкових mixed newline bytes. Temporary route не входить у commit.

### Важливий наявний M26/PPC baseline

Чотири M26 UK practice rows 12307–12310 уже відрізнялися від frozen M26 practice payload до M36 через попередню accepted PPC роботу. Actual bodies відповідали поточним ROOT canonical definitions; Forms також звірено з PPC commit `cfbbfa57929b779a9ef315cb178f7d08ba46c9e4`.
Цей стан не reset/overwrite і не видано за exact frozen M26 payload. Inspector допускає лише чотири exact row identities/order/type/body hashes + поточний canonical match; усі інші M26 авторські блоки перевіряються frozen-exact. Усі чотири rows і non-target fingerprint незмінні після M36, M26 disclosures=56. Жодних PPC files/rows не включено у M36 DB diff чи commit.

## Автоматичні перевірки

PHP 8.5.10 / PHPUnit 12.5.35, один final isolated прогін 44 Feature classes M26–M36: **620 tests / 10886 assertions / 0 failures / 0 errors**, exit 0, 11:40.425. З них M36 subset — **86 / 1400**, M26–M35 — **534 / 9486**; це subsets, не додаткові прогони для штучного загального підсумку. Окремий ранній M36 targeted run також мав 86 / 1400 PASS.

Isolation: SQLite `:memory:`, array cache/session, окремий runtime/storage/views, CLI OPcache off, working `.env` не завантажено. Protected fingerprint до/після: 46648 files, changes=0, SHA-256 `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`.

| M36 suite | Tests | Assertions |
| --- | ---: | ---: |
| AuthorFidelity | 36 | 356 |
| ContentPatch | 25 | 68 |
| LocalTargetGuard | 19 | 27 |
| ModalsSubjunctivePackage | 6 | 949 |

Є **1 deprecation**, не приховано: існуючий `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` під PHP 8.5. Виправлення конфігурації не входить до M36. Runtime fallback tests виконані для foreign owner, wrong locale, UUID/order/type та edited body; окремі empty-locale/tampered-source-file runtime мутації не запускалися, хоча strict locale та exact hash guards наявні й перевірені аналізом коду.

Node: **591/591 PASS**, 23 related files, exit 0, 6.80s. M36 subset — 86 tests (74 practice, 5 local, 7 tooling), older subset — 505; не додано PHP/Vitest до штучного total.

Vitest: **8/8 files, 61/61 tests PASS**, exit 0, 89.38s; поточні theory/navigation/layout/public-asset suites.

Final syntax/format: 18 M36 PHP files lint PASS; 6 M36 JS files node --check PASS; 5 package/canonical JSON parse PASS; `git diff --check` PASS.

Private evidence збережено поза commit: ROOT `storage/app/seo-m36-local` для proof/preview/review/backup/inventories/HTTP/browser та WT `storage/app/seo-m2-local` / `storage/app/seo-m36-local` для isolated test result/JUnit і Node/Vitest stdout. Секрети, cookies та response bodies не збережено як report artifacts.

## Live browser / no-JS / overflow

Chromium/Chrome, реальні fresh guest contexts, GET-only. M36 matrix: 1440×1000 і 390×844, light/dark, три уроки — **12/12 PASS**. Перевірено full initial basic, кожний table cell, source-case ownership, wrong/correct score/reset, авторські альтернативи, Enter/manual/tokens/remove, усі semantic-negative input cases, primary bank isolation, anchors/reload/print. Screenshots збережено приватно; desktop table і mobile practice переглянуто візуально.

Єдиний завершений live run `acceptance-v1-browser.json`: **pass=true**, **30/30 no-JS educational states PASS** (M27–M36), **3/3 normal-JS M35 rendering/practice regressions PASS**, network-policy violations=0. Нових console/page errors, failed requests або HTTP errors у прийнятих станах немає. No-JS M36 має точні читабельні original prompts і keys; native practice повністю працює у normal JS. Збережено 92 M36 screenshots, без commit.

Learning main і всі content cards: **0px overflow у всіх 12 M36 станах**. Таблиці мають власний `overflow-x:auto`: client 840px desktop / 322px mobile, scroll 1000px. Desktop document/decorative overflow=0. Mobile random background виміряно окремо, без його зміни та без global overflow-x:hidden:

| Mobile стан | Document overflow, px | Максимальна decorative boundary, px |
| --- | ---: | ---: |
| Modal Perfect light | 4 | 4.24 |
| Subjunctive light | 4 | 3.88 |
| Subtle Modal light | 8 | 8.11 |
| Modal Perfect dark | 6 | 9.02 |
| Subjunctive dark | 5 | 8.67 |
| Subtle Modal dark | 10 | 9.94 |

Це не заява про нульовий загальний document overflow: навчальні блоки мають 0px, наявні декоративні shapes іноді виходять за viewport. Фон не входить до M36 scope.

Known historical no-JS limitation окремо: **11 M26–M29 pages / 22 hidden token banks** з попереднього accepted report. Це NONPASS повної no-JS token usability, не нова M36 проблема. У цьому run no-JS матриця охоплює M27–M36; M26 збережено через exact baseline snapshots та HTTP controls. Стару token branch не переписано. Читабельність навчального basic/keys без JS не означає no-JS scoring.

## HTTP, metadata, sitemap та регресії

39 fixed локальних URL до/після: theory targets, M26–M35 controls, PPC control і три test URLs; усі завершені capture responses — **200**, redirect=manual, без authorization/cookie/Referer. Title, controller-derived H1, description, canonical, robots, X-Robots-Tag, OG/Twitter metadata і Content-Type точні before==after. Canonical URL у HTML міг мати production origin за поточною політикою; production URL не запитували.

Ordered sitemap before==after: observed **554 URLs**, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Count не hardcoded acceptance condition.

| Пакет | Actual disclosures, unchanged |
| --- | ---: |
| M26 | 56 = 8 + 12 + 12 + 12 + 12 |
| M27 | 13 = 3 + 7 + 3 |
| M28 | 0 |
| M29 | 0 |
| M30 | 0 |
| M31 | 2 |
| M32 | 1 |
| M33 | 2 |
| M34 | 2 |
| M35 | 0 |
| M36 | 0 / 0 / 0, семантичний результат |

Початкові локальні проблеми доступу не приховано: перший GET мав curl timeout HTTP000, потім 200; три before-capture спроби мали 30s timeout. Окремий M26 GET повернув 500 за 27.81s; локальний журнал показав Windows rename Access denied під час Blade compilation. Наступні GET та повний before capture успішні без Apache/permissions/cache/session changes. Це локальна environment подія, не висновок про production або Googlebot; невдалі спроби не підмінено fixtures.

## Git / handoff

Final staged review — PASS: рівно 36 M36 files, forbidden/private artifact paths=0, high-confidence secret hits=0, binary files=0, unstaged tracked files=0. Незалежно звірено staged author/package bytes, finite shared hooks, фактичні DB/browser докази та цей report. Обидва frozen JSON Git objects точно відповідають raw working bytes; `.gitattributes -text` запобігає їх нормалізації. `git diff --cached --check` — PASS.

До commit включено лише M36 sources, три canonical definitions, finite shared-view hooks, tests/tooling, `.gitattributes` і цей report. `.env`, backups/dumps, screenshots/proof evidence, vendor/build, temporary route та сторонній dirty state виключені. Push — звичайний у зазначену робочу гілку; full commit SHA і remote SHA==HEAD перевіряються у фінальному handoff після push, без самопосилання commit у власному report. Main/PR/deploy не виконуються. Production не перевірявся й не змінювався.
