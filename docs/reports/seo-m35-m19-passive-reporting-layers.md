# M35 — M19 Passive Reporting / Causative / Impersonal Style

Дата: 2026-10-04. Scope: тільки робочий `http://gramlyze.loc`.
Production не перевірявся й не змінювався. M20 не розпочато.

## База й ціль

Accepted base: `d2581e3bbcc8a5b89d0a93a454bb010d3c4606eb`,
`codex/seo-m34-m18-argumentation-cohesion-layers`.
Після `git fetch origin` локальна база й origin збігалися; ancestry підтверджено.
Нова робоча гілка: `codex/seo-m35-m19-passive-reporting-layers`.
Використано чистий tracked worktree `storage/app/m30`; unrelated dirty ROOT
на `41820a2bebdf69004fa7209a2a38457f93efabbd` не скидався і не комітився.
Без main, PR, force, reset, clean, rebase або деплою.

| Сторінка | Рівень / Page ID | Accepted definition blob | Disclosures |
| --- | --- | --- | --- |
| [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | C1 / 296 | `d6108186897211e6b8c705f805e60403e5a0b5b0` | 0 |
| [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | C1 / 305 | `efa7568192b82787e2091d19cb5983be55c68224` | 0 |
| [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | C2 / 319 | `52163bdf7870623bccba1ed1ad5687cfcd5cf006` | 0 |

Exact identities, усі з prefix `Database\Seeders\Page_V3\PassiveVoice\`:

- `PassiveReportingStructuresTheorySeeder`;
- `ComplexPassiveAndCausativeTheorySeeder`;
- `ComplexPassiveImpersonalStyleTheorySeeder`.

Before manifest SHA-256: `71610d3bbbd41b9d5fee14e633c4adc5dee0480910609dd9c4086bed982b68f6`.
Finite projection SHA-256: `000b06498bea93d9240b346db100b5578a9522b2ef082fc0b7f482d2367595dc`.

## Native structure і fidelity

Для кожного уроку: незмінний hero, 5 `usage-panels`, 1 `comparison-table`,
1 `practice-set`, 1 `summary-list`. У definition 9 блоків;
у фактичній БД разом із незмінним subtitle — 10 рядків.
Один великий box розкладено на ті самі 8 авторських секцій.

Повний ordered plain educational text, пунктуація, переклади, table cells
та внутрішні посилання співпадають з accepted source.
Зміст не скорочено, не перефразовано; нових grammar rules/examples немає.
Збережено reporting status, учасників, джерела, модальність, not scope,
одночасність/попередність, активне/пасивне відношення та результат/план/процес.

Усі три таблиці мають власний horizontal scroll і accepted мінімум 1000px.
Column minima: Reporting `[250,280,210,260]`, Causative `[250,370,300]`,
Impersonal `[250,380,300]`; усі по 4 рядки.
Старі `self-check-*` і `lesson-block-9167/8804/8849-section-1..8`
збережені; нові ключі скінченні й deterministic.
Немає hidden copy цілого уроку, порожніх disclosure wrappers або додаткових стрілок.
Unknown owner/locale/UUID/order/modified payload отримує повний basic fallback.

## Семантичний detail-quality audit

Рішення для всіх 18 кандидатів — `visible_basic`, явне в finite author projection.
0 details — редакційне рішення, а не runtime word-count filter.
Числа — діагностичні: `basic` означає решту тексту секції без кандидата;
кандидат фактично теж залишається basic.

| Урок / секція | Basic words | Candidate words / sentences | Чому лишено visible basic |
| --- | ---: | ---: | --- |
| Reporting 1 | 119 | 34 / 2 | Основний алгоритм перенесення джерела й підмета |
| Reporting 2 | 130 | 34 / 4 | Часові відношення й повна таблиця потрібні до кліку |
| Reporting 3 | 126 | 34 / 4 | Очікування, backshift і процес — core contrast |
| Reporting 4 | 87 | 27 / 3 | Збереження конкретного джерела — основна умова |
| Reporting 5 | 81 | 44 / 3 | That/to, узгодження й may — основне керування |
| Reporting 6 | 50 | 63 / 5 | Повний звіт, переклад і непідтвердженість — core |
| Causative 1 | 93 | 37 / 3 | Організатор/виконавець/об’єкт/домовленість — core |
| Causative 2 | 134 | 50 / 5 | Have person do / get person to do — основні форми |
| Causative 3 | 109 | 49 / 4 | Had had потрібне поруч із Past Perfect |
| Causative 4 | 134 | 43 / 5 | Питання, заперечення й скорочення завершують форми |
| Causative 5 | 113 | 29 / 3 | Коротка ремарка про get/register не потребує кліку |
| Causative 6 | 82 | 38 / 4 | План без вигаданого результату — головна межа |
| Impersonal 1 | 115 | 34 / 3 | That/raised subject продовжує основне розмежування |
| Impersonal 2 | 122 | 54 / 5 | Heavy passive і ясна that-альтернатива — core |
| Impersonal 3 | 142 | 45 / 4 | Been + adjective проти been + V3 — core |
| Impersonal 4 | 88 | 45 / 4 | Незнання й область not — центральне значення |
| Impersonal 5 | 121 | 38 / 2 | Два пасивні рівні й процес не можна приховувати |
| Impersonal 6 | 102 | 33 / 2 | Атрибуція й невигадана безпека — основний висновок |

Два кандидати <30 слів (Reporting §4, Causative §5) лишилися видимими.
Жодного meaningful hidden depth у цих accepted текстах не виділено.

## Усі 18 авторських practice cases

На кожній сторінці рівно 2 select, 2 choice, 2 token/manual tasks та own-bank widget.
Exact HTML prompts і повні literal author explanations збережено в `author_self_check`;
кожний source index має одного власника. Підказки не замінюють ключі.
Складні cases не зведено до одного yes/no: збережено всі їхні subdecisions.

### Reporting — [теорія](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures), [тест](http://gramlyze.loc/test/passive-voice/passive-reporting-structures)

| Case | Тип | Збережене завдання / ключ |
| --- | --- | --- |
| 1 | Choice | `The guide reports that the visitors are waiting outside.` Guide — джерело; visitors чекають, учасників не міняємо |
| 2 | Select | `People believe that the old lift is safe.` → `It is believed that the old lift is safe.` Не known/proved |
| 3 | Choice | Двоє художників малюють зараз → `The two artists are reported to be painting a mural now.` Не completed |
| 4 | Select | Усі три часові вибори й пояснення: (а) `to be empty`; (б) `to have left`; (в) `to be working` |
| 5 | Token/manual | Обидва виправлені каркаси: `The halls are believed to be empty. It is believed that the halls are empty.` Також finite variant без that |
| 6 | Token/manual | `On Friday, the courier was reported by the coordinator to have arrived at four on Thursday. This has not been independently confirmed.` Допустимий second-sentence variant із `This report` |

### Causative — [теорія](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative), [тест](http://gramlyze.loc/test/passive-voice/complex-passive-and-causative)

| Case | Тип | Збережене завдання / ключ |
| --- | --- | --- |
| 1 | Choice | `Olena had her coat shortened by a tailor yesterday.` Olena організує, tailor виконує, coat зазнає дії; yesterday збережено |
| 2 | Select | `Iryna had thirty invitations printed by the printing company on Wednesday.` Приймається й `30`, збережені друкарня й день |
| 3 | Token/manual | Повний авторський пояснювальний ключ до `Leo had his shelf painted on Monday.` / `Leo had painted his shelf himself before Monday.`: організація проти власного виконання, Past Simple/Past Perfect, object/V3 порядок та literal `on/before` |
| 4 | Select | Обидві форми: `Did you have the screen replaced yesterday? I did not have the screen replaced yesterday.` Також `didn't` |
| 5 | Choice | `The guest had her phone stolen during the performance.` Небажана подія, не замовлення крадіжки; переклад і критерій збережено |
| 6 | Token/manual | `I have booked my scanner in for repair on Thursday. It is expected to be ready on Friday.` Допустимий початок `I am going to have my scanner repaired on Thursday.` Немає вигаданого completed result |

### Impersonal — [теорія](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style), [тест](http://gramlyze.loc/test/passive-voice/complex-passive-impersonal-style)

| Case | Тип | Збережене завдання / ключ |
| --- | --- | --- |
| 1 | Choice | `On Wednesday, the map was reported by the curator to have been restored by a conservator on Monday.` Curator/source, conservator/actor, map/object і Wednesday/Monday — всі subdecisions |
| 2 | Select | Обидва рівні: `The technicians are reported to have calibrated the sensors...` та `The sensors are reported to have been calibrated by the technicians...`; час і voice не змішані |
| 3 | Choice | `On Friday, the parcel was reported by the archivist to have been sealed on Thursday.` Archivist — source; невідомого sealer не додано |
| 4 | Select | Повний пояснювальний ключ: `The file is not known to have been deleted` — немає підтверджених відомостей про видалення; `The file is known not to have been deleted` — відомо, що не видалили. Значення не тотожні |
| 5 | Token/manual | `Today’s update reports that two decorators are repainting the hall now.` Також `...the hall is being repainted by two decorators now.` Збережено джерело, кількість і процес |
| 6 | Token/manual | `On Monday, the organiser reported that the printing company had printed forty programmes on Sunday. The number has not been independently checked, and there is no information about delivery.` Також `40`; не known/delivered/not delivered |

Усі correct/approved variants, wrong distractors, scoring, contextual explanations,
reset і token-only/manual paths перевіряються. Кінцева пунктуація optional;
внутрішня пунктуація та semantic distinctions збережені.
Групи токенів 1–3 слова, перемішані; Backspace повертає токен у банк.
Токени не запускають suggestions/fetch і поля мають `autocomplete=off`.
Для M35 використано finite array `m35_token_groups`: literal slash у `on/before`
не розбиває авторський текст. Старий slash-delimited формат інших уроків незмінний.

## Фактичний linked-bank inventory

SELECT з робочої БД, не припущення за назвою seeder.
Prefix primary banks: `Database\Seeders\V3\Polyglot\`.

| Page | Primary own bank | Type / level | Actual count | Question IDs |
| --- | --- | --- | ---: | --- |
| 296 | `PolyglotPassiveReportingStructuresC1LessonSeeder` | 4 / C1 | 48 | 17697–17744 |
| 305 | `PolyglotComplexPassiveAndCausativeC1LessonSeeder` | 4 / C1 | 48 | 18273–18320 |
| 319 | `PolyglotComplexPassiveImpersonalStyleC2LessonSeeder` | 4 / C2 | 48 | 18897–18944 |

Окремо у кожної сторінки існують linked AllLevels V3 type 0 і AllLevels lesson
type 4 по 72 питання (по 12 на A1–C2). Це не primary widget pool M35.
Primary визначено тільки після перевірки type, exact level, one-level global scope
та єдиного primary owner. Питання/відповіді/options/hints/pivots/saved tests незмінні.
Browser sample звіряється з повним actual ID inventory; sample size не видається за 48.

## Guarded working-local apply

Physical/vhost/CLI-web proof: Windows, exact application/public ROOT,
єдиний active `.loc` vhost, loopback DNS, live Apache і MySQL PID/listeners;
MySQL `localhost:3306/gr2`, server `DESKTOP-3C05HGF`.
`APP_ENV=production`, але `.loc SiteMode=development`; CLI/web identity однакова.
Немає DB URL/socket/split/forwarding. `.env` не змінювався.

Fresh inventory → fresh proof → fresh preview → незалежний exact-field review
→ exclusive backup → transactional apply → postconditions → repeated no-op.
Preview SHA-256: `4b71b2846c0132b8a4dad0a2efbe901dc3ae28393c045a7f2c4b377df9851143`.

| Факт | Результат |
| --- | --- |
| Updates | 3, тільки `text_blocks.type/body`; IDs 9167, 8804, 8849 і UUID збережено |
| Inserts | 21, по 7 owned UK native blocks, без foreign owner |
| Deletes | 0 |
| Blocks загалом | 6439 → 6460 |
| Non-target text blocks | 6436, fingerprint незмінний |
| Інші protected tables | 19, усі fingerprints незмінні |
| M26–M34 snapshots | 29 owners, повні авторські payload незмінні |
| Повторний preview | `state=after`, 0 updates / 0 inserts |
| Повторний apply | `status=no-op`, 0 updates / 0 inserts; unused backup не створено |

Фінальний SELECT-only `m35-after-v2.json` після browser interactions також PASS:
exact actual DB bodies/IDs/anchors, 19 protected tables, 29 regression owners,
всі linked banks і 6436 non-target blocks незмінні. Це окрема postcondition,
не новий apply; додаткових записів у БД не було.

Exclusive DB backup: `storage/app/seo-m35-local/m35-backup-v1.json`,
SHA-256 `846705a65638cc8ab64889315bc28fc97e28176877cee9ae74c729bc48ad30b2`.
Перед finite source sync збережено окремий exclusive source backup
`storage/app/seo-m35-local/source-backup-c7f0061541d15a88` (16-file allowlist).
Обидва backups приватні, не в Git.

Тимчасовий nonce GET route: тільки `.loc`/plain HTTP/80/loopback,
без proxy headers/auth/cookies; SELECT-only safe identity/HMAC, no-store/noindex.
Після apply/no-op route видалено; GET nonce URL → **404**, remote **127.0.0.1**.
Raw `routes/api.php` SHA-256 повернувся byte-exact до
`2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`.
`.env` SHA-256 незмінний:
`b02694c5c2643f7ae217edbf2f6362638bcb0f8d5f8f74fb5388df25a07d514b`.

## Автоматичні перевірки

PHPUnit 12.5.35 / PHP 8.5.10: один finite run 40 classes,
**534 tests / 9486 assertions / 0 failures / 0 errors**, exit 0.
Є 1 PHPUnit deprecation; це не приховано як відсутність попереджень.
Isolation: testing, SQLite `:memory:`, array cache/session, окремі views/storage,
CLI OPcache off, робочий `.env` не завантажено.
46648 protected files перевірено before/after, змін 0.

M35 suites окремо (числа з JUnit цього самого run):

| Suite | Tests | Assertions |
| --- | ---: | ---: |
| M35PassiveReportingPackageTest | 6 | 767 |
| M35AuthorFidelityTest | 34 | 186 |
| M35ContentPatchTest | 22 | 59 |
| M35LocalTargetGuardTest | 5 | 10 |

Решта 36 M26–M34 classes у тому ж run: 467 tests / 8464 assertions.
Це не складання результатів різних прогонів.

| Regression stage | Tests | Assertions | Результат |
| --- | ---: | ---: | --- |
| M26, 5 classes | 44 | 2164 | PASS |
| M27, 3 classes | 29 | 279 | PASS |
| M28, 4 classes | 33 | 599 | PASS |
| M29, 4 classes | 41 | 836 | PASS |
| M30, 4 classes | 59 | 802 | PASS |
| M31, 4 classes | 63 | 648 | PASS |
| M32, 4 classes | 70 | 843 | PASS |
| M33, 4 classes | 56 | 1006 | PASS |
| M34, 4 classes | 72 | 1287 | PASS |

31 PHP semantic mutation fixtures реально змінюють існуючі accepted strings:
source/subject swap, belief/report→fact/proof, simultaneous→earlier,
process/expected/plan→completed, may loss, that/to/agreement,
organiser/performer swap, invented agreement, causative/Past Perfect,
object/V3/have/get order, theft/get-passive, actor/source loss, voice from tense,
been + adjective, future reference, not scope, claim confirmation,
heavy passive readability, invented safety і lost translation.
Окремо rejects neighbouring ownership, duplicate ID і lost practice case.

Node: final single run **505 tests PASS** у 20 related files.
M35 suites окремо: practice **60**, local/browser contracts **5**,
runtime tooling **7** — усі PASS. Раніші suites у тому ж run **433 PASS**.
23 semantic answer fixtures (8 Reporting / 7 Causative / 8 Impersonal)
змінюють actual accepted answers; усі відхиляються.
Vitest: **8 files / 61 tests PASS**; current theory/navigation/layout/assets/progress.
JSON parse, PHP lint і `git diff --check` пройшли.

Діагностичні невдалі спроби збережено приватно, не видано за PASS:

- Initial Node: 2/59 failures через slash parsing `on/before`; finite array виправлення,
  повторний і final run PASS, legacy branch збережено.
- Initial before HTTP: timeout 30s на M26 overview; окремий fresh guest GET дав 200,
  corrected complete before capture PASS без зміни timeout/серверної конфігурації.
- Другий HTTP diagnostic мав wrong copied M27 category path і 404; виправлено лише
  діагностичний fallback до accepted `clauses-and-linking-words`, не сторінку.
- Один помилково вибраний default M1 suite run (Windows path separator у filter)
  NONPASS через відсутній untracked Vite manifest у worktree; це не M35 acceptance.
  Результат збережено окремо; suite filter виправлено й потрібні 40 classes PASS.
  Залежності/build або сторонні тести заради цього не змінювалися.

## Live browser / no-JS / overflow

Real Chromium/Chrome, нові guest contexts, GET-only, не DOM fixture.
У `acceptance-v1-browser.json` **усі 12 станів M35 PASS**:
3 сторінки × desktop 1440×1000 / mobile 390×844 × light/dark.
Повний basic і таблиці, exact cases/keys, правильні/неправильні/approved alternatives,
scoring/reset, token-only/manual/Backspace reuse, exact own-bank IDs,
23 semantic negatives та contextual feedback (по 2 cases/сторінку) пройшли.
Anchors, reload, deep links і print/restoration також PASS.
На M35: 0 page errors, 0 local request/HTTP failures, 0 console errors,
0 font failures і 0 forbidden network attempts.
M35 details interaction N/A: нуль disclosures, весь basic visible.

Збережено **96 focused screenshots**, тільки приватно в `storage/app/seo-m35-local`.
Візуально перевірено desktop Reporting table / Causative practice,
mobile Impersonal negation / Causative dark practice.
Усі 12 widget samples (по 5 питань) належать точному primary inventory.
Приклад desktop/light IDs: Reporting `[17722,17715,17743,17724,17718]`,
Causative `[18283,18315,18287,18320,18310]`,
Impersonal `[18916,18924,18920,18926,18897]`.
5 — розмір browser sample, **48** — фактичний повний банк кожного уроку.

У цьому самому browser run **27/27 no-JS educational states PASS**:
M35 і M27–M34, повний author basic, readable practice prompts/keys та native details.
Це перевірка читабельності, не заява про no-JS scoring/token usability.
Обов’язкова M34 normal-JS rendering/practice regression: **3/3 PASS**.

Learning main і кожна visible content card: **0 px overflow у всіх 12 станах**.
Таблиці мають тільки внутрішній `overflow-x:auto`: scroll width 1000px,
client width 840px desktop / 322px mobile. Фон не змінювався.

| Mobile стан | Document overflow, px | Максимальна decorative shape boundary, px |
| --- | ---: | ---: |
| Reporting light | 2 | 7.03 |
| Causative light | 12 | 12.16 |
| Impersonal light | 0 | 10.18 |
| Reporting dark | 4 | 7.13 |
| Causative dark | 10 | 9.54 |
| Impersonal dark | 0 | 8.40 |

Desktop document/decorative overflow — 0. Strict whole-document mobile overflow
**не 0 у всіх станах**; decorative bounds виміряно окремо і не приховано
глобальним `overflow-x:hidden`. Це не learning/content overflow.

Розширена регресійна діагностика не видається за single-run full PASS:

- `acceptance-v1-browser.json` загалом `pass=false`: після 12 M35 і 27 no-JS PASS
  14/24 старих normal-JS regressions пройшли, 15-та (M30 C2) мала 30s timeout
  стандартного click `packed`, waiting visible/enabled/stable; останні 9 не виконані.
- Окремий свіжий `m30-click-probe-v4.json`: GET 200, усі три practice groups,
  variants/wrong/reset і обидва token/manual tasks PASS зі звичайним auto-scroll/click.
  Код/CSS/БД старого M30 не змінювалися; причину початкового timeout не встановлено.
- Під час діагностики Apache зупинився: owner DNS 127.0.0.1, MySQL працював,
  HTTP connection refused. Apache запустив користувач; fresh Causative GET → 200.
- Новий `acceptance-v2` зупинився до browser matrix у HTTP capture на
  `/theory/past-perfect-continuous`: timeout 30s, NONPASS.
  Окремий свіжий GET → 200, 127.0.0.1, 0.784301s.
  Збережено `acceptance-v2-failure.json`; конфігурацію/timeout не міняли заради PASS.
- Окремий fresh `regression-acceptance-v1.json`: **24/24 PASS одним прогоном**,
  нові guest contexts, desktop/light, усі M27–M34 owners (у driver M30 позначено M14).
  Перевірено exact basic/keys, meaningful details (mouse/Enter/Space/focus,
  independent/simultaneous/no fetch), усі practice groups/variants/wrong/reset,
  token-only/manual/Backspace, exact bank inventories, overflow,
  reload/deep links/print restoration, console/network/guard.
  Жодних forced clicks, CSS/DOM підмін, response fulfillment або зміни timeout.
  Це окремий PASS run, а не зміна `pass=false` початкового combined run.

Known historical no-JS limitation окремо: **11 M26–M29 pages / 22 hidden token banks**.
Це accepted NONPASS повної no-JS token usability, не нова проблема M35,
і не було автоматично «виправлено» або оголошено full PASS.
Legacy token branch збережено; регресійні owner/body і HTTP teaching controls незмінні.
M35 no-JS має exact readable prompts/keys; normal-JS practice повністю PASS.

## SEO, metadata, sitemap і регресії

Before/after 36 actual guest GETs: три target theory, 29 M26–M34 lessons,
Present Perfect Continuous control і три пов’язані tests. Усі 200.
Title/H1/description/OG/Twitter/canonical/meta robots/X-Robots/content-type незмінні.
Canonical з локальної HTML відповіді лишається production `.com` URL;
це текст metadata, не production HTTP перевірка.
Локальний X-Robots-Tag: `noindex, nofollow, noarchive`; meta robots absent.
Старі anchors, subtitle/hero, tag/category/page relations незмінні.
Sitemap: **554 ordered URLs**, SHA-256
`6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`, незмінний.

Accepted meaningful counts підтверджено джерелами, isolated rendering tests
і exact working-local snapshots:
M26=56, M27=13, M28=0, M29=0, M30=0, M31=2, M32=1, M33=2, M34=2.
Не повертали M27 до 17, M28 до 3 чи M29 до 15; short-details лишилися basic.

## Git / приватність

Commit allowlist: M35 canonical sources/package/command/guard/tests/diagnostics/report
та finite shared-view hooks. Без `.env`, proof route, screenshots, evidence,
DB dumps/backups, vendor, generated build або unrelated ROOT changes.
35-file allowlist перевірено; staged JSON parse (5 JSON), raw package hashes,
staged diff review, secret-pattern scan/private-path scan і `git diff --check` PASS.
Final Node 20-file run після експорту read-only diagnostic helpers: 505/505 PASS.
Повний commit SHA, commit link і результат `remote SHA == HEAD` надаються
у фінальній відповіді після normal branch-only push; SHA не записується у власний commit.
