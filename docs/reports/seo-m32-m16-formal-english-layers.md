# M32 — M16 Formal English: native basic і meaningful point-level details

## Межі й версія

Base: `326bf629e84cd9385ba2ac531a00fb30f82aab73` (accepted M31). Робоча гілка: `codex/seo-m32-m16-formal-english-layers`; base збережений в історії без merge/rebase/cherry-pick. Основний dirty checkout `D:/DEV/htdocs/gramlyze.loc` залишається на `41820a2bebdf69004fa7209a2a38457f93efabbd`. Його сторонні зміни та index не очищалися й не stage-илися.

Scope — тільки три accepted UK M16 уроки. Це технічна проєкція повного авторського матеріалу, не редагування навчального тексту. Нові правила, приклади, переклади, слова чи штучні пояснення не додавалися. Метадані, рівні, hero, категорії, теги, banks, saved tests і старі фрагменти захищені. Native практика реалізує вже погоджені авторські задачі; author prompts/keys/explanations збережені окремим literal self-check, зокрема без JavaScript.

Production не перевірявся й не змінювався. `.com`-canonical та sitemap URL порівнюються лише як рядки; HTTP-запитів на `.com`/`.ub` немає. Немає deploy, main/PR, dependency/build/vendor змін, migrations, seeders, cache/session clearing, startup/HTTP auto-apply або зміни Apache/XAMPP/hosts.

## Сторінки та accepted sources

| Урок | Theory URL | Рівень | Accepted Git blob | Page ID / старий box ID |
| --- | --- | --- | --- | --- |
| Formal Register and Nominalisation Basics | [Відкрити](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | B2 | `2f36b86d9e19af0739387d9117f0074cb2ba92c7` | 291 / 8768 |
| Nominalisation and Formal Register | [Відкрити](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | C1 | `0679c7b3c84e3317a184280ad4a5ff1054863515` | 301 / 8792 |
| Register Tone and Paraphrase | [Відкрити](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | C1 | `79966df02d62d62f3481ddd4bb06a16955ad260c` | 307 / 8810 |

Canonical definitions: `database/seeders/Page_V3/FormalEnglish/{FormalRegisterAndNominalisationBasicsTheorySeeder,NominalisationFormalRegisterTheorySeeder,RegisterToneAndParaphraseTheorySeeder}/definition.json`.

Immutable before manifest SHA-256: `f5be9aaa8ba03ade84ffb1eeeb6e3929f4360f7c4d7940bdf806e69416d46677`.
Immutable projection SHA-256: `e904174d5a530673e1f1a1732f10b09a5b9530d6a8f7ae8f6eb090bd817294d8`.
Before manifest — versioned accepted source, не backup робочої БД. Обидва hash-bound JSON мають `-text` у `.gitattributes`.

Раніше кожен урок мав subtitle, hero та один legacy HTML box із шістьма teaching sections, self-check і continuation — 3 DB rows. Тепер: незмінні subtitle/hero, шість native teaching blocks, native `practice-set` (section 7) і continuation (section 8), тобто 8 native content blocks/page плюс hero/subtitle — **10 DB rows/page**. Збережені first-box ID/UUID/order/heading/column; його змінені поля — тільки `type`, `body`. Basic points: 17 / 18 / 20. Full accepted tables, абзаци, translations і смислові межі залишені у відповідних пунктах.

## Semantic audit кандидатів

Оцінка змістова, не runtime word-count threshold. B — preceding basic words; D — candidate words; S — sentences. У таблиці підрахунок потрібен для аудиту, не для створення disclosure. Матеріал, необхідний для первинного розуміння, залишається basic незалежно від довжини.

| Урок / section | B / D / S | Рішення |
| --- | --- | --- |
| B2 §1 | 133 / 65 / 5 | basic: центральні межі регістру, сила звернення, I/we, скорочення й фразові дієслова |
| B2 §2 | 149 / 32 / 2 | basic: необхідне розмежування іменникової групи й номіналізації |
| B2 §3 | 85 / 30 / 3 | basic: approval/of та зміна керування — основна граматика |
| B2 §4 | 0 / 165 / 17 | basic: вся фінальна direct `ul` з чотирма lexical groups; не лише останній `li` |
| B2 §5 | 129 / 18 / 2 | basic: короткий висновок про просте I need завершує порівняння повідомлень |
| B2 §6 | 46 / 20 / 2 | basic: фінальний checklist потрібен без кліку |
| C1 nominalisation §1 | 81 / 23 / 2 | basic: відомого виконавця не приховуємо, невідомого не вигадуємо |
| C1 nominalisation §2 | 99 / 19 / 2 | basic: необов’язковість номіналізації завершує метод |
| C1 nominalisation §3 | 142 / 52 / 7 | basic: fundamental passive-vs-nominalisation distinction |
| C1 nominalisation §4 | 140 / 15 / 2 | basic: артикль і керування не визначаються формальністю |
| C1 nominalisation §5 | 118 / 55 / 4 | detail: цілісний аналіз після повного paragraph comparison |
| C1 nominalisation §6 | 83 / 29 / 3 | basic: checklist виконавця, часу, статусу й модальності |
| C1 tone §1 | 62 / 48 / 6 | basic: основний active/passive приклад зберігає виконавця |
| C1 tone §2 | 106 / 30 / 3 | basic: два режими редагування й точна інструкція — базова рамка |
| C1 tone §3 | 135 / 27 / 2 | basic: obtain/get пояснює семантичну межу синонімів |
| C1 tone §4 | 100 / 32 / 3 | basic: прохання vs пряма інструкція — основна частина порівняння тону |
| C1 tone §5 | 185 / 22 / 2 | basic: коротка вимога атрибуції джерела |
| C1 tone §6 | 42 / 30 / 2 | basic: перевірка змісту й допустимість природних альтернатив |

17 кандидатів залишено basic, 1 — meaningful detail. Усі 8 кандидатів коротші за 30 слів залишено basic: B2 §5/§6; nominalisation §1/§2/§4/§6; tone §3/§5. Їх не роздували новими прикладами й не приховували для штучного count.

Єдине «Докладніше»: C1 Nominalisation §5, point 1 (`m32-nominalisation-section-5-point-1`). Повний вступ, обидва повні порівнювані абзаци й переклади — у basic. Точний авторський 55-word/4-sentence аналіз — у цьому ж пункті, без об’єднання з чужими деталями. У B2 та C1 Tone кнопок немає. Source count: **0 / 1 / 0**. Runtime unknown/mismatched payload fail-closed до повного native basic, а не до обрізаного тексту.

## Інтерактивна практика й linked banks

Практика зберігає всі **18 авторських задач і ключів (6/page)**. Не примушуємо неоднакові задачі до схеми 2/2/2: B2 — 3 choices + 3 inputs; nominalisation — 6 inputs; tone — 3 choices + 3 inputs. Відповіді вводяться вручну або авторськими token groups (1–3 слова); видалені токени доступні знову, autocomplete вимкнено. Correct/wrong/reset, approved alternatives, finite contextual feedback й sentence boundaries перевіряються окремо. Terminal punctuation optional; внутрішні межі багатореченнєвих відповідей потрібні.

B2 case 1 і tone case 1 оцінюють доречність у заданому контексті, а не оголошують неформальні речення універсально неграматичними. У B2 arriving case обидва `arrived` і `got` accepted; `obtained` — ні. C1 nominalisation зберігає ongoing actor/action/object, cause/two dates, possibility/next week/agent, conduct instruction, unknown cancellation agent/chronology і повний триреченнєвий paragraph. C1 tone зберігає designer/recipient/deadline, свідому ввічливу зміну сили invoice request і full Saturday-only conditional/may + definite Friday contrast. Author-approved aliases скінченні; це не універсальний semantic scorer для довільних природних перефразувань.

Fresh SELECT-only inventory перед генерацією: root `D:/DEV/htdocs/gramlyze.loc`, MySQL `localhost:3306`, database `gr2`. Виявлено власний single-level primary bank, реально linked через UUID; all-level 72-question banks не підставлялися.

| Test URL | Exact type-4 primary seeder (`Database\\Seeders\\V3\\Polyglot\\`) | Level / count | IDs |
| --- | --- | --- | --- |
| [B2 test](http://gramlyze.loc/test/formal-english/formal-register-and-nominalisation-basics) | `PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder` | B2 / 48 | 17505–17552 |
| [C1 nominalisation test](http://gramlyze.loc/test/formal-english/nominalisation-formal-register) | `PolyglotNominalisationFormalRegisterC1LessonSeeder` | C1 / 48 | 18033–18080 |
| [C1 tone test](http://gramlyze.loc/test/formal-english/register-tone-and-paraphrase) | `PolyglotRegisterToneAndParaphraseC1LessonSeeder` | C1 / 48 | 18369–18416 |

Private initial inventory: `storage/app/seo-m32-local/m32-bank-inventory-v1.json`. Question/answer/option/hint/variant/pivot/saved-test дані не входять у write scope.

## Перевірки до apply

- Node/JSDOM M27–M31 practice + M32 diagnostic/tooling (9 files), окремий запуск: **143/143 PASS**, 0 failures/skips.
- Окремий M32 practice + updated tooling запуск: **77/77 PASS** (70 practice із 46 negative cases; 7 tooling).
- Vitest, окремий запуск: **8 files / 61 tests PASS**, 0 failures; включає theorySections/navigation/sidebar, public assets і progress state. Тимчасові build fixtures не є deployment artifacts.
- Фінальний спільний Node/JSDOM + contract run (10 files), окремий запуск: **215/215 PASS**, 0 failures/skips. Попередні запуски до цього числа не додавалися.
- Ізольований targeted PHP M26–M32: **28 suites / 339 tests / 6171 assertions PASS**, child/runner exit 0; PHP 8.5.10, PHPUnit 12.5.35, 04:55.683, 98 MB. 46 648 protected files / changes 0; before/after digest `2a2b567939462ff6a2dbcf6354a1c097665204f4cb44f1d71fd6c80f90474028`. SQLite `:memory:`, testing, array cache/session, CLI OPcache off, working `.env` not loaded, stderr empty. Одне наявне deprecation `PDO::MYSQL_ATTR_SSL_CA` не приховувалося. M32 suites: Package 25/390, Patch 15/40, Guard 5/10, Fidelity 25/403; усі failures/errors 0. Private evidence: `storage/app/m30/storage/app/seo-m2-local/m32-integrated-v1-2dc8b8e8b2f34fc19fbb070def792cf1-result.json`.
- Finite sync `--check`: exact 16 files, no writes. Перша sandbox-only спроба була відхилена Git ownership check до будь-якого запису; перевірка від імені власника repo — PASS. Глобальний `safe.directory` не змінювався.

## Apply та live acceptance

Finite working source sync після тестів: **16 files**, exclusive source backup `storage/app/seo-m32-local/source-backup-77553a79ef2bbc42`; exact old bytes або прийнятий Git blob перевірені до запису, сторонні зміни не перезаписані.

Fresh physical/vhost/CLI-web proof — PASS: Windows MySQL listener/PID, Apache active vhost/document root і runtime match. `APP_ENV=production`, але це перевірений локальний `.loc`, `SiteMode=development`; `.env` не змінювали й профіль не перемикали. CLI/web однаково підтвердили root `d:/dev/htdocs/gramlyze.loc`, document root `/public`, mysql/localhost/3306/gr2. Тимчасовий nonce route був GET-only/http/exact-host/raw-loopback, відхиляв proxy headers (HEAD404 і forwarded GET404), повертав тільки safe identity та digest, без ключів/password/cookies/tokens і DB writes.

Fresh preview `m32-preview-v2.json`: `before`, **3 updates / 21 inserts / 0 deletes**; digest `6461e731973f7d5cb40a20f138d5bf34dbe72dcdd2c06eed223dd19a74298a2c`. Root exact-review tool і незалежний reviewer звірили всі fields/UUID/source bodies, accepted blobs, 21 source fingerprints, 16-file source backup і 19 protected-table fingerprints. Before: 6376 text blocks; 6373 non-target rows мають SHA-256 `c048391153af06df6dca5bfeb25bacbb3d883e6d9f1bd39e69a824ba70820b26`.

Transactional apply до `gr2` — **applied 3/21**; exclusive DB backup `storage/app/seo-m32-local/m32-before-v2.json`, byte SHA-256 `7bee8278b9d3d9721e1764355d23c30f0e537c99cbb6df4eedfa9917617c1079`, точна byte-copy reviewed preview. First-box IDs 8768/8792/8810 та UUID незмінні; тільки `type`/`body`. Subtitle/hero/metadata/order/locale/owner/relations/timestamps старих rows незмінні. No questions/options/answers/hints/variants/pivots/saved-tests/seed-runs writes.

Повторний guarded apply із тим самим reviewed plan — **no-op, updated 0 / inserted 0**. `m32-unused-no-op.json` не створений. Після цього proof-route видалено через apply_patch; routes file byte SHA-256 збігся з raw before `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689` без додаткового formatting/restore. Fresh GET старого nonce → **404**. `.env` byte hash також unchanged. Немає routes/cache clearing.

Independent SELECT-only after evidence `m32-after-apply-v1.json` + `verify-m32-evidence.php` — PASS: 6376→6397 text blocks, exact bodies/types/locale/orders і 10 rows/target; 19 protected tables, 6373 non-target rows, actual linked48×3 IDs та **20 M26–M31 snapshots unchanged**. Fresh final after-browser snapshot `m32-after-browser-v1.json` (2026-10-03T23:50:06Z) і незалежний verifier також **PASS**: 3 updates / 21 inserts / 0 deletes, exact actual DB bodies, ті самі 19 tables / 20 regression owners / 6373 non-target blocks. Browser acceptance не змінила навчальні дані чи banks.

Первинний HTTP baseline: **27 guest GET200**, ordered sitemap 554 URLs, SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.
Дві попередні незараховані спроби timeout 30 s: перша на B2 target, друга на M26 overview після трьох target GET. Окремі curl GET200 (0.726/0.636 s), Node GET200 (1.284 s) та два four-GET probes (звичайний і з JSDOM) — PASS. Третій fresh full capture тим самим 30-second client — PASS. Причину початкових timeout не встановлено; автоматичних retries, server/config/cache змін чи висновку про production не було.

Перший live `acceptance-v1` не зарахований як browser PASS: усі 27 HTTP GET і metadata/sitemap comparison пройшли, але diagnostic score regex вимагав `3 з 3`, а реальний correct scorer показував `Результат: 3 з 3`. Незалежний reviewer підтвердив assertion-only mismatch. Runtime/data не змінювалися; diagnostic тепер перевіряє exact localized label і correct/total числа (новий negative contract test, 7/7 PASS). Fresh acceptance-v2 — окремий запуск; старе failure evidence збережено.

Fresh `acceptance-v2-http.json`: **27/27 guest GET200**, metadata title/H1/description/OG/Twitter/canonical/robots/X-Robots-Tag, old fragments, контрольний навчальний текст і ordered sitemap before == after. Sitemap 554 URLs, SHA-256 незмінний. Готові test URLs перевірені напряму, без авторизації/cookie/Referer або попереднього переходу з теорії.

`acceptance-v2-browser.json`: **PASS, 12 M32 states + 18 no-JS + 15 JS regressions M27–M31**, fresh guest contexts, GET-only, 1440×1000/390×844, light/dark. Real initial HTML і reload DOM містять повний ordered author core; exact six prompts/keys/page preserved. Count M32 **0/1/0** фактично підтверджено. Mouse/Enter/Space, focus-visible, own-point detail, independence, reload closed, own detail deep link або old visible anchor, print state/restore та no detail fetch — PASS. Один retained detail не створює вимоги simultaneous >1; regression pages із кількома details перевірені окремо.

M32 practice: correct/wrong/reset, approved alternatives, token-only/manual, Backspace token reuse, autocomplete-off, terminal punctuation optional і internal sentence boundaries — PASS. **76 live semantic negative checks (19 fixtures × 4 states)** відхилені; B2/Tone contextual feedback exact, не універсальна заборона граматично можливих informal forms. Widget вибрав 5 actual questions/page із відповідного власного48-question type4 bank; IDs належать fresh SELECT inventory, all-level bank не використано.

No-JS усіх18 уроків: full basic readable. M32/M30/M31 (9 pages) — literal complete6prompts + visible author keys. M27–M29 цей main runner підтверджує видимий practice markup; stricter old-token test окремо нижче. No-JS disabled-script CSP events18 expected, не приховані application failures. Page/console/local request/HTTP/policy errors **0**; font failures **0** у цьому запуску.

Збережено **76 bounded viewport PNG** у private `seo-m32-local`; main agent переглянув representative light/dark desktop/mobile table/basic/practice/detail, independent reviewer — 16 representative screenshots усіх3pages. No M32 content/layout blockers. Незмінний dark styling маленького жовтого level badge має слабкий контраст (pale text на pale background): inherited `resources/views/engram/theory/partials/category-description-v3.blade.php:33` + `resources/css/theory-unified-design.css:123`. Header/CSS unchanged by M32; це окрема nonblocking косметична знахідка, не виправлення поза scope і не заява про ідеальний contrast.

Learning overflow M32: main/cards **0 px** у всіх12states. Mobile tables мають власний focusable horizontal scroll (client322/scroll576px), не розширюють content/document. Desktop document0px; mobile light B2/Nom/Tone **7/5/4px**, dark **9/17/0px**. Animated decorative rectangles окремо до17.272px; це не навчальні cards. Strict whole-document-zero не заявляється; background/global overflow CSS не змінено.

Після diagnostic-only correction новий фінальний 10-file Node run: **216/216 PASS**, 0 failures/skips; попередні 215 не сумуються.

## M26–M31 regression acceptance

Окремий accepted `seo-m26-point-details-local.cjs`, private evidence `storage/app/seo-m26-local/m32-regression-v1-browser.json`: **PASS, 5 real GET200 / 20 browser states / 5 no-JS pages / 56 unique point details**. Desktop/mobile, light/dark; mouse/keyboard, print/deep links, full accepted author fidelity пройдено. Baseline SHA-256 `98cf3ea7185151d99d8dbb20be23226c1847bef0bbc28ad56bfb90eb2b03c484`, той самий accepted baseline, що в M31. Збережено 20 bounded screenshots. Page/network/font/policy failures 0; цей окремий runner не збирає console stream, тому нуль console errors йому не приписується. П'ять expected no-JS script CSP events відокремлені.

| Package | Реальні сторінки | Actual point-details по сторінках | Total |
| --- | ---: | --- | ---: |
| M26 | 5 | 8 / 12 / 12 / 12 / 12 | 56 |
| M27 | 3 | 3 / 7 / 3 | 13 |
| M28 | 3 | 0 / 0 / 0 | 0 |
| M29 | 3 | 0 / 0 / 0 | 0 |
| M30 / M14 | 3 | 0 / 0 / 0 | 0 |
| M31 | 3 | 0 / 1 / 1 | 2 |
| M32 | 3 | 0 / 1 / 0 | 1 |

M27–M31 JS practice, reset, token reuse, manual acceptance, exact own linked banks, anchors і print/reload — PASS у 15 regression states main runner. Повні accepted DB bodies/metadata всіх20 M26–M31 owners незмінні за фінальним SELECT snapshot, не тільки disclosure counts.

M26 learning overflow PASS: main/cards 0, desktop document0px. Mobile document (overview/forms/negatives/questions/time expressions): light **0/8/14/0/9px**, dark **7/3/0/10/6px**. `learningOverflowPass=true`, але `strictDocumentOverflowPass=false`: animated decoration рахується окремо й не прихована через global overflow CSS. Це не зміна навчальної верстки M32.

## Старе no-JS обмеження — окремо від M32

Користувач явно залишив поза M32 scope стару проблему token inputs на 11 сторінках M26–M29. Окремий accepted `seo-m31-m26-practice.cjs` її відтворив: private evidence `storage/app/seo-m31-local/m32-legacy-nojs-v1-browser.json`, **exit1 / strict overall non-PASS**, не маскується як успіх. M26 JS practice **4/4 PASS**; M26 full-token no-JS **0/4**, M27–M29 **2/9**. Загалом на11 сторінках не показані22 token banks (по2/page). Основний basic, labels та intros видимі; це неповний контекст manual/token завдань, а не порожня теорія.

| Старі no-JS сторінки з обмеженням | Count / hidden token banks |
| --- | --- |
| M26: `/theory/tenses/past-perfect-continuous/past-perfect-continuous-{forms,negatives,questions,time-expressions}` | 4 / 8 |
| M27: `/theory/clauses-and-linking-words/linking-words-reason-result-contrast` | 1 / 2 |
| M28: `/theory/sentence-structure/cleft-sentences-basics`, `/theory/basic-grammar/word-order/{inversion-basics,advanced-fronting-and-emphasis}` | 3 / 6 |
| M29: `/theory/sentence-structure/{cleft-sentences-emphasis,complex-noun-phrases,ellipsis-substitution-and-reference}` | 3 / 6 |

M27 Advanced Linking Devices і Concessive and Contrastive Structures — complete manual contexts PASS; їх до11 проблемних не включено. Accepted old token branch byte-identical SHA-256 `1cd9f920295ad119a31242fb308d0201d5f6c5daaf339b4f4088e39c4b000d77` (accepted commit `ceb2162c862978ca461607be3913a9f717f380b2`), M27–M29 source hashes unchanged. Network/page/console/font/policy errors 0, expected disabled-script CSP окремо. Обмеження не виправляли й не погіршили. **M32 всі18 prompts/keys мають повний literal no-JS fallback**; видимість старого practice markup не видається за повноту старих token contexts.

## Git завершення

Versionable scope — 33 пов'язані M32 файли в окремій робочій гілці; тільки normal push. Shared views використовують finite `m32_v1` opt-in, M26–M31 гілки збережено. Before/projection raw hashes, canonical3 exact-after і accepted3 Git blobs повторно перевірено незалежним reviewer. JSON parse, PHP lint, targeted PHP/Node/Vitest, diff check та real local acceptance завершені до commit. Secrets, `.env`, routes proof, runtime evidence, backups/dumps/screenshots, vendor/public/build та foreign unfinished changes виключені.

Точний commit SHA, commit link і read-only результат `remote SHA == local HEAD` наведені у фінальному повідомленні після normal push; власний hash не вбудовується в цей versioned report. Основний dirty checkout/index не використовується для Git завершення. Немає main/PR/force/deploy.
