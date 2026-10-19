# M43 — авторська модернізація трьох уроків про час і значення

Дата роботи: 2026-10-07. Єдина HTTP/DB-ціль: `http://gramlyze.loc`. Нові тексти створено Codex у межах дозволу на повну реалізацію; вони **не називаються особисто погодженими користувачем речення за реченням**. Редакторські cross-reviews виконали окремі агенти, а не незалежний зовнішній мовний редактор.

## База, робочі каталоги та межі

- Accepted base M42: `f1a552303cbc46713fa4a34e71fd6be01cc25a42`.
- Design reference M41: `053c6ddbe4b42b23f724ec336fdf472b6d219215`.
- Робоча гілка: `codex/seo-m43-authored-tense-usage`.
- Worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Фактично обслуговуваний ROOT: `D:/DEV/htdocs/gramlyze.loc`; document root: його `public`.
- ROOT починав на `codex/production-ready-a14788dac`, SHA `41820a2bebdf69004fa7209a2a38457f93efabbd`, зі сторонніми dirty changes (46 649 deleted, 120 modified). Вони не відновлювалися і не очищалися. Синхронізовано лише явно перевірені M43-файли та точні shared hunks.
- `main` (`c77b4326a92b2c1e92c80b07393d8e7000c0fe33`) не змінювався. Немає merge, PR, deploy, production HTTP, `.com/.ub` DB, dependency install, reseed, migration, зміни `.env`, hosts чи Apache/XAMPP.

Прочитано AGENTS.md, point-detail-quality, звіти M41 author/preserve-design і M42, чинні master/projection/native components та їхні guards. Старий M41 prompt із ZIP не виконувався. M43 має окремий finite opt-in; реєстри M41/M42 не розширювалися на нових owners.

## Три сторінки й фактичні обсяги

Identity prefix у таблиці: `Database\Seeders\Page_V3\Tenses\`. Definitions: `database/seeders/Page_V3/Tenses/<Identity>/definition.json`.

| Сторінка | Identity (після prefix) | Рівень | Секції / points | Details | Tasks / required controls | Manual controls |
|---|---|---|---:|---:|---:|---:|
| [Past Perfect vs Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-vs-past-perfect-continuous) | `TensesPastPerfectVsPastPerfectContinuousTheorySeeder` | B1 | 6 / 22 | 3 | 6 / 11 | 4 |
| [Stative Verbs](http://gramlyze.loc/theory/tenses/stative-verbs) | `TensesStativeVerbsTheorySeeder` | A2–B1 | 7 / 13 | 1 | 6 / 13 | 2 |
| [Used to / Would](http://gramlyze.loc/theory/tenses/used-to-would) | `TensesUsedToWouldTheorySeeder` | B1 | 6 / 15 | 2 | 6 / 11 | 3 |
| Разом | Тільки 3 UK owners | — | 19 / 50 | **6** | **18 / 35** | **9** |

Усі 4 авторські таблиці збережено повністю. Це не квота disclosures і не механічний розподіл типів вправ. Кожна з 18 вправ оцінюється лише після всіх її required controls.

## Editorial BEFORE → AFTER

### Past Perfect vs Past Perfect Continuous

Збережено навчальну мету порівняння, обидві форми, протиставлення результату/процесу, часовий зв’язок, питання тривалості та роль станів. Це не копія окремого M26 Past Perfect Continuous або Narrative Tenses.

Виправлено поля перекладів: коментар про акцент більше не видається за український переклад речення. Пояснення винесено окремо від повних bilingual examples. Старе безумовне позначення Continuous із «п’ятьма листами» як помилки замінено контрастом готових результатів і кількості об’єктів роботи: інше значення не оголошується неграматичним.

Додано виразний минулий момент відліку; тривалі стани в Past Perfect; межу між акцентом на процесі та висновком про його фактичне припинення; ствердження, заперечення й питання обох форм; контексти for/since, before/after, by the time і how long. Часові слова не є автоматичними перемикачами. Три details поглиблюють конкретні контрасти, не приховують потрібний basic. Шість нових вправ перевіряють форми та заданий смисловий акцент.

### Stative Verbs

Збережено корисні групи станів і контрасти зі свідомою дією, але одиниця пояснення тепер — значення дієслова у конкретному вислові. `need` перенесено з володіння до потреби. Для think/have/see/taste/smell і be/being наведено контекстні пари з окремими перекладами.

Безумовну заборону на `She is loving jazz` замінено розрізненням нейтральної вподоби й актуального розмовного захоплення. Нечітку стару «помилку» з двох різних висловів замінено справжніми помилками побудови `does not owns` і `He having`. Відсутні правила «ніколи Continuous», «тимчасове — завжди Continuous» або «now усе вирішує».

Додано таблицю стверджень/заперечень/питань, perfect-приклади зі станами, for/since/how long, окрему межу вживання being і шість вправ. Єдине detail присвячено розмовному регістру loving; потрібне застереження видно у basic.

### Used to / Would

Збережено минулі звички, стани, часову рамку та форми. Уточнено, що Past Simple описує також повторення і тривалі стани, а не лише одиничний епізод. Старі безумовні заборони `would be / would have / would live` замінено поясненням саме цільового habitual значення; інші модальні значення не закреслюються.

Додано повний розділ `used to + infinitive` / `be used to` / `get used to + noun/-ing`, навчальну письмову модель did/didn’t use to, контекст розповіді, дві змістовні details та шість власних вправ. Там, де ситуація допускає три способи подати звичку, matcher явно приймає `used to walk`, `would walk`, `walked`. Однозначні завдання обмежені повною ситуацією, не лише every/yesterday.

Перед freeze виправлено feedback `used-q4`: у `is getting used to getting up` двічі трапляється слово **getting**, а не вся сполука getting up. Після freeze навчальні bytes master не підмінялися заради тестів.

Повний finite editorial mapping міститься в `lessons[*].editorial_changes` master. Корисні старі теми збережено або замінено явним точнішим поясненням, а не вилучено через незручність рендерингу.

## Frozen master, checksums і джерела

Версія `1.0.0`. Чотири bound source files — UTF-8 LF, два кінцеві LF bytes; `.gitattributes` містить лише 7 точних M43 `-text` paths для збереження byte hashes, без глобального правила. Для цих чотирьох frozen files окремо зазначено `whitespace=-blank-at-eof`: початковий diff-check позначив зафіксований другий LF як blank-at-EOF, але master не обрізався. Усі інші whitespace checks залишено; один зайвий кінцевий blank у не-frozen metadata diagnostic прибрано без зміни виконуваного коду, однаково у WT/ROOT.

| Файл | SHA-256 |
|---|---|
| `docs/content/m43-authored-tense-usage.v1.0.0.json` | `d9afc130ec223202ea83781147f9966855227d3194ccd7707c9b3c037b62e2f5` |
| `docs/content/m43-authored-tense-usage.v1.0.0.uk.md` | `03dfe19f6d4e54058ae6fe544c29fe41af92d35e955f563d746716d4be29ae39` |
| `docs/content/m43-editorial-notes.v1.0.0.md` | `96e3ed5f09567fed6d933345fcfd9080ccd290bd7ae95eaae331b123b8b638f8` |
| `docs/content/m43-native-mapping.v1.0.0.json` | `c66cd63305d39753b98c5aaa4c53a1814ab4a60006a9c923dbba2b1fbb02cd9f` |
| `docs/content/m43-checksums.v1.0.0.json` | `701c0172194df91918e64104f404e610f6338c20db9e9de73be5a0fa352a277c` |
| `database/content-patches/m43-authored-tense-usage-before.json` | `c737d07ebf0818028c5456f8639ed880f7c5de5d3d8960b14116d70f581f3f96` |
| `database/content-patches/m43-authored-tense-usage.v1.0.0.json` | `419ab6384bba5cde3a11b6030e0e5de136a63beda55b4fac80255d800a487f46` |

Редакторський прохід перевіряв граматику, ситуацію, переклади, рівень, ключі, aliases, not/числа, tokens та відповідність практики теорії. MAIN прочитав усі три drafts і 35 controls. Окремі агенти виконали cross-review не власних уроків. Це не зовнішня мовна сертифікація.

Фактично прочитані джерела, питання, дата та спосіб доступу перелічено у `master.sources` і notes. Основні редакційні матеріали:

- [British Council: Past perfect reference](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/past-perfect) і [B1–B2 explanation](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-perfect): минулий reference point, стани, попередні дії, before/for/since. Пряме читання редакційних частин успішне.
- [British Council: Stative verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/stative-verbs) та [Perfect simple/continuous](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/present-perfect-simple-continuous): значення стану/дії, be/being, тривалі стани. Пряме читання успішне.
- [British Council: Past habits](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2-grammar/past-habits-used-to-would-past-simple) та [Different uses of used to](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2-grammar/different-uses-of-used-to): habitual would, стани, Past Simple, accustomed constructions. Пряме читання успішне.
- Cambridge Grammar/Dictionary: Past Perfect pair/Continuous, state/action verbs, think, have, need, smell, used to і would. Прочитано доступний **індексований редакційний текст**. Частина прямих відкриттів дала 403, have — Internal Error; успішного прямого читання цих сторінок не заявляємо. Exact URLs/access notes залишено у master.

Учнівські коментарі не використано як нормативне джерело. Приклади й вправи видавців не переносилися.

## Native integration і fallback

`M43AuthoredTenseUsagePackage` перевіряє exact identities, UK, frozen hashes, повний stored AFTER, UUID, sort/config і body. Native opt-in передається саме theory caller, а не береться з довільного payload. Foreign owner/locale, невідомий type, відсутній або пошкоджений marker мають повний static fallback; чужий M41 author key не захоплюється M43.

Мапінг використовує чинні usage-panels, forms-grid, comparison-table, summary-list, mistakes-grid для справжніх wrong/right, point-disclosure та authored-practice-ui. Окремі M43 fragments додають escaped bilingual content; en/uk мають власні lang і typography. Таблиці прокручуються локально. Header/sidebar/background/global layout не редагувалися; global overflow-x:hidden не додавався.

Старі block slots, UUID, level, tags, anchors та зв’язки збережено. Нові 7 blocks додано в кінці із deterministic keys. Авторський порядок секцій відновлює лише finite M43 theory projection, не перенумерація DB. Шість details мають власні IDs, закриті початково й живуть у своїх points.

Оскільки курс повторно використовує ті самі theory rows, `preserveCourseBlocks` тільки для exact M43 AFTER клонує старі rows у пам’яті, повертає frozen BEFORE type/body та попередній порядок, виключає нові блоки. Це не запис у БД і не зміна курсу. Окремі tests і real course GET/DOM comparison підтвердили byte-equivalent навчальний HTML.

Практика використовує explicit aliases. Немає глобального розгортання 's, втрати not/чисел/внутрішньої пунктуації або довгих feedback keys у option labels. Невідоме природне перефразування поза finite matcher не проголошується доказом неграматичності. No-JS залишає текст, умови, токени та disclosures із ключами; автоматичне оцінювання без JS не заявляється.

## Existing banks — SELECT-only inventory

До apply перевірено фактичні links, seeder identities, типи й exact own IDs; їх не вгадували за slug. Primary linked standard банки трьох тем мають по 72 type-0 questions. Own Sentence Builder банки:

| Урок | Власний builder seeder | Own linked questions |
|---|---|---:|
| Past Perfect comparison | `PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder` | 72, type 4 |
| Stative Verbs | `PolyglotStativeVerbsAllLevelsLessonSeeder` | 72, type 4 |
| Used to / Would | `PolyglotUsedToWouldAllLevelsLessonSeeder` | 72, type 4 |

У Used to додатково існує legacy `PolyglotUsedToLessonSeeder`: 24 linked A2 type-4 questions. Він не підміняє власний 72-question bank. Усі сім виявлених banks і links збережено. Наявні тестові cards/widgets залишено; practice widget M43 обмежено власним банком, не загальним AllLevels pool. Нові 18 tasks містяться в three practice-set bodies, не в `questions`.

## Guarded local apply і незмінність даних

Приватні докази: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-local/`; у Git їх немає.

Fresh physical capture + read-only nonce GET підтвердили application/document identity, loopback, `http://gramlyze.loc`, mysql localhost:3306 / `gr2` та відповідність web/CLI. Фактичне APP environment було `production`, SiteMode — `development`; environment не змінювався заради guard. Proof не віддавав credentials, cookies, APP_KEY або tokens. Temporary route видалено; `routes/api.php` повернувся byte-exact до BEFORE SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; окремий GET nonce path повернув **404**.

Source sync proposal v1: 46 exact files, SHA `87a17013c67ff0a324d941ccfdd27f61425ab6e45298bd6b9a13969b845c2585`; 7 shared hunks перевірено проти accepted BASE і окремо захопленого ROOT. Сторонні ROOT bytes збережено.

1. Незалежний DB BEFORE: `m43-before-v1.json`, SHA `e4209b13af1301ed92d765d0bc535553b3f5717269519aa51de90926b6c13e53`.
2. Fresh proof: 2026-10-07T20:13:05Z; fresh physical v2 SHA `a329f470a9be291a350842af27c810e3ed650933e02ba8cb43919bb09df02dfa`.
3. Reviewed preview `m43-preview-v1.json`, plan SHA `2eee61069550d75a0a72d5020c6445e97a7bb5429d176412a0b9b40d90b21ef6`.
4. Exclusive private backup `m43-backup-v1.json`, 3 891 970 bytes; transaction, guarded postconditions.
5. Фактичний apply: **24 updated / 7 inserted / 0 deleted**. Updates = 3 `pages.text` + 21 UK `text_blocks.type/body`; inserts за уроками = 1 / 3 / 3. Ніяких інших полів існуючих rows не змінено.
6. Fresh AFTER preview plan SHA `9c077de34f174c649ac8f8b1d841bac8809bb6ea0e28df9bbd7e03fae0388dc6`; фактичний повторний `--apply`: **0 updated / 0 inserted / 0 deleted**.
7. Незалежний SELECT-only AFTER `m43-after-v1.json`, SHA `a2152c499655f13420357133700348ad15782e12ac724ce400c721413b0687b6`; exact definition=ROOT=DB для всіх трьох.

Порівняння всіх **46** protected tables: exact counts + SHA unchanged. Raw differences тільки `pages` (254→254) і `text_blocks` (6563→6570), рівно дозволений plan. Зокрема questions 44 460, answers 197 564, theory links 134 184, attempts 118, progress 20, saved tests 750 — unchanged. Усі EN/PL rows, metadata fields, categories/tags/relations і сім banks unchanged. Це повна table verification, не sampling.

Після apply окремо посилено ambiguity check source-sync helper для змішаних LF/CRLF: він відхиляє другий equivalent hunk навіть поряд із exact match. Це не змінює content або DB і перевірено 7 isolated cases. Browser harness окремо уточнив селектор єдиного `m43PracticeUi` та очікування завершення Alpine render після reset/edit. Master і learner rendering від цих diagnostic змін не мінялися; початкові preview/backup/sync manifests збережено як історичні докази, не переписано заднім числом.

Фінальний SELECT-only capture `m43-final-v1.json`: SHA `c7969c0cfbaa0cc543af233561a18f1fe76b9896459f442933db0ee079a6f7c0`. Окремий `db-final-comparison-v1.json` підтвердив: усі 46 raw tables exact AFTER=FINAL, усі 46 protected tables exact BEFORE=FINAL, повні 3 target records/banks/source identity AFTER=FINAL. Отже браузерні й автоматичні тести не записали прогрес або інші робочі дані.

Source AFTER охопив **52 518 paths** у кожному ROOT/WT inventory (`app`, `resources`, `routes`, `database`, `docs/content`, `public/js`, `public/css`). Явний allowlist — 29 M43 paths на каталог; порівняння: 58 дозволених змін, **0 поза allowlist**, SHA comparison `e9dca311e354733839938c66b148fa26992f0386bd3fe698b52bc755d2f54cf8`. Tools/tests/report/.gitattributes додатково перевірено Git diff, а не приписано цьому inventory. Приватний `post-apply-source-followup-v1.json` окремо фіксує два source-only уточнення після apply; historical sync manifest v1 незмінний і не є новим дозволом повторно використати старий preview.

## HTTP і metadata

BEFORE v2: 65 GET rows + robots/sitemap, SHA `1a6b6a3a70ad71180959eb49ffea0ab43781528f5317a861f511b6eeea1fd210`.
AFTER v1: **65/65 HTTP 200**, SHA `782a643e51881ce2018666a18bebb246a6c1d061a06e0a223843ae9ce46c3627`.

Окремо перевірено exact BEFORE→AFTER: 3 targets, 62 controls (42 M42, 3 M41, 4 M26, 6 EN/PL, home/theory/course, 3 відповідні course lessons, test). Robots/sitemap responses і ordered URL set unchanged. Title, H1, canonical, robots/X-Robots та старі DB anchors збережено. `.com` у canonical/JSON-LD — раніше наявні metadata values, не запити до production.

Новий subtitle очікувано змінює description, OG description, Twitter description та LearningResource.description. Незалежні очікування отримано **до apply** з frozen master через metadata service (`metadata-expectations-before-v1.json`, SHA `d994b5e18328153ce92746711e63caa0f87064cb97adaac078d7ed45da308e01`), не з generated AFTER. Усі HTML/JSON-LD порівняння пройшли; це не «metadata повністю unchanged» для трьох targets.

## Browser acceptance

Використано справжній Chrome/Playwright на `.loc`, fresh isolated contexts, GET-only same-origin policy. Production не відкривалася. Зовнішній Google Fonts навмисно заблоковано; його classified console failure не приховано і не видано за application JS error. Локальні assets/Alpine перевіряються окремо. Видимий fallback font може відрізнятися від звичайного браузера з доступним Google Fonts.

BEFORE: 10/10 states, 86 PNG; окремо course BEFORE v2 6/6 states, 33 PNG. AFTER course v1 **6/6 PASS**, exact learner DOM/metadata/HTTP проти BEFORE v2; manifest SHA `887673c3137cf783efcbc3e84f6e347b881222af12a51a7f0e82ae0902d24ebb`. Guest/no-JS режими не обходили штатну course access policy.

Supplemental v2 **PASS**: 3 no-JS + 6 responsive (320 CSS px / DPR2) + 4 M41/M26 reference states. Manifest `browser-supplemental-v2/supplemental-v2-supplemental.json`, SHA `d6dbeceb642fafb1da95643756b43baf1e56fdc9a855adb269db7353cbc572ce`. No-JS: усі theory text/conditions/answer keys/tokens доступні. Local table scroll перевірено окремо від decorative overflow.

Основний **after-v5: 12/12 PASS, exit 0**, UTC 20:23:53–20:34:27. Manifest `browser-after-v1/after-v5-pages.json`, SHA `5a8e3b4c512de2b5dae3a2487ae712ce2cfb9a998d60761a46cc05d9b265a27b`. Це 3 уроки × desktop 1440×1000 / mobile 390×844 × light/dark. 424 PNG, 72 task-runs, 140 control-runs, 176 explicit alias/typography/terminal-punctuation checks, 36 manual token runs, 24 detail-runs. Перевірено всі 18 unique tasks і 35 required controls: initial/empty/partial/wrong/correct/reset, повторне редагування, токени/повернення, keyboard/focus, score; усі 6 independent details — mouse/keyboard/deep-link/print/reload. У всіх rows `pageErrors`, `localHttpErrors`, `localFailures`, `blockedNonGET` = 0.

MAIN переглянув representative readable sections/practice/no-JS/320px та viewport PNG; acceptance-agent особисто переглянув усі 12 full-page PNG, mobile details, складні compound/manual feedback/reset states та 6 course full PNG. Навчальний/main overflow = 0. Невеликий document/decorative overflow mobile виміряно окремо: light 5/3/10px, dark 3/2/3px; не приховувався глобальним CSS.

У наддовгому A mobile full-page PNG виявлено повтор нижньої частини зображення; DOM/fidelity/окремий footer були правильні. Для незалежного підтвердження додатково зроблено **68 реальних viewport screenshots** (34 light + 34 dark) з overlap до footer, без DOM/style injection або синтетичного stitching. Усі 68 особисто переглянув агент; повтору при реальному scrolling немає. MAIN додатково переглянув light-09/light-25/dark-13/dark-34. `mobile-scroll-v1/manifest.json` SHA `147282fc9dfdbc6122b1d52145bc904c8699fa16fd6888e06da46e2bea55490b`; learner DOM hash до/після однаковий. Це supplemental evidence, не заміна невдалого PNG заднім числом.

Ранні attempts збережено: v1 sandbox DNS resolution failure — не висновок про недоступність сайта; v2/v3 strict locator знаходив також nested own-bank widget; v4 перевіряв x-show одразу до Alpine microtask. Остаточний harness вимагає exact single authored component, чекає фактичного hidden/visible state і не послаблює scoring/content assertions. Supplemental v1 мав 3 no-JS/4 references PASS і 6 responsive failures того самого selector; v2 — окремий повний повтор, не змішаний total.

Visual evidence caveat: locator screenshots високих секцій можуть містити sticky header поверх crop через автоматичне прокручування. Це не доказ втрати DOM; аномалію наддовгого A full-page PNG окремо перевірено viewport-серією вище. MAIN та агент переглянули full-page і readable section/practice/detail images. Успадкований світлий текст native yellow level badge у dark потребує окремого contrast audit; global native CSS у M43 не змінюється.

У незміненому random own-bank widget також помічено старий український prompt «Вони їв…». Він не входить до 18 нових author tasks; питання банку не виправлялося, оскільки M43 прямо забороняє їх редагувати. Окремі summary points використовують штатний local list marker 1; він не є наскрізним номером секції/TOC і не змінює порядок master.

**Не перевірено справжній Ctrl+zoom**. 320px/DPR2 — viewport/density coverage, не browser zoom. Lighthouse/польові CWV не запускалися й не вигадувалися.

## Ізольовані тести й точні команди

PHP 8.5.10: `C:/Program Files/xampp/php/php.exe`; Node 22.15: `C:/Program Files/nodejs/node.exe`. CWD команд — WT. PHPUnit запускається існуючим `tools/diagnostics/run-isolated-tests.py`: SQLite memory, isolated storage/cache/session, working MySQL/.env/progress не використовуються; protected file fingerprints до/після перевірено.

```powershell
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m43-fidelity-course-v4 tests/Feature/M43AuthorFidelityTest.php tests/Feature/M43CoursePreservationTest.php
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m43-content-patch tests/Feature/M43ContentPatchTest.php tests/Feature/M43LocalTargetGuardTest.php
node --test tests/Browser/seo-m43-local.test.cjs tests/Browser/m43-practice-ui.test.cjs
node node_modules/vitest/vitest.mjs run --reporter=json --outputFile=storage/app/seo-m43-local/vitest-final-v1.json --maxWorkers=1 --minWorkers=1
```

| Окремий запуск | Результат |
|---|---|
| M43 fidelity + course v4 | **7 tests / 2 412 assertions, exit 0**, protected changes 0, 1 deprecation |
| M43 patch + guard | **130 tests / 326 assertions, exit 0**, protected changes 0, 1 deprecation |
| Final mixed-EOL shared-hunk cases | **7 tests / 8 assertions, exit 0**, protected changes 0, 1 deprecation |
| M42 regression batch v1 | **324 tests / 24 163 assertions, exit 0**, failures/errors/skips 0, protected changes 0, 1 deprecation |
| M43 Node contracts після harness fixes | **112/112, exit 0**; synthetic contracts не підміняють live acceptance |
| Shared M26/M41/category/course/test v1 | **263 tests / 6 817 assertions, exit 1**, 3 failures, 1 deprecation, protected changes 0; пояснення нижче |
| Inline + M43 fidelity/course v2 | **26 tests / 2 641 assertions, exit 1**, тільки 1 baseline failure, 1 deprecation; UsedToWould migration і всі M43 cases PASS |
| M41/M42/M43 Node regression v2 | **659/659, exit 0**, 0 skipped; окремий run, не додається до 112 |
| Vitest final v1 | **61/61 у 8 files, exit 0**, 0 failed/pending; report SHA `69044339af2ec261ff3052ba46f031c2f5e470d41c75a0245a844b0d12f32379` |

Не підсумовуємо повторні overlapping runs в один total. Ранні fidelity runs знаходили Blade inline/block parser issue та помилку synthetic multiplicity assertion, що рахувала summary/noscript UI двічі. Остаточна перевірка рахує реальний learner text, виключаючи лише UI/noscript duplications, і зберігає assertions кожного авторського поля. Snapshots не оновлювалися автоматично, тести не видалялися.

### Невдалі regression assertions — окремо, не прихований PASS

1. **Baseline** `TheorySectionRenderingTest::test_invalid_pairs_fallback_to_original_full_content_with_reason`, dataset changed/detail-reference-changed. Тест замінює `Past Simple` у fixture, але актуальний accepted M41 hero-derived detail уже не містить цього тексту: mutation є no-op, reason залишається null. Test, TheorySection і target definition byte-unchanged від base; передумову прямо перевірено в `git show f1a552303...`.
2. **Baseline** `TheoryInlineHtmlRenderingTest`, provider Present Perfect vs Past Simple. Accepted M41 definition не має жодного old-style richFields HTML block; count=0. Test/definition byte-unchanged від base, zero count підтверджено з git blob. Старий тест не переписували заради M43.
3. **Виправлено test migration M43**: Used to / Would тепер має structured author fields замість legacy hero rules.example HTML. Його єдиний case явно перевіряє current definition == hash-bound package AFTER та exact owner/path; старі richFields/renderBoth/XSS assertions без змін запускаються на frozen BEFORE, який курс зберігає. AFTER окремо перевіряють M43 fidelity/course suites. Provider не видалений, affected>0 не послаблений, master не забруднений штучним `<strong>`.

Node v1 мав один assertion, прив’язаний до старого literal `$practiceScope === 'm41'`. Після дозволеного opt-in M43 тест тепер захищає **точний strict allowlist `['m41', 'm43']`**, єдиний noscript token bank і повний token loop, не довільний scope. Повтор v2 659/659; evidence SHA `b4772f0e0a347fcd76b30350366589b8f9e6f859434b3e126dacfcb58b304a53`. Legacy owners не отримують нової розмітки.

Shared v1 і targeted v2 завершили не лише PHPUnit child, а й wrapper AFTER fingerprints: **46 661 protected files / 0 changes**, before/after fingerprint `e1ff9fe48ab06d5f3ca47e8478bfe95ff3778dd459be19248b59beacf6383933`. Result basenames: `m43-shared-regressions-v1-f0222d2c54ce40a4a752d2baeb444069-result.json` та `m43-inline-native-regressions-v2-674a931915114611b35ed00eb1e87270-result.json` у WT `storage/app/seo-m2-local`. У targeted v2 M43 fidelity окремо 5 tests/2331 assertions PASS, course 2/81 PASS, Inline 19/229 має єдиний baseline failure.

```powershell
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m43-inline-native-regressions-v2 tests/Feature/TheoryInlineHtmlRenderingTest.php tests/Feature/M43AuthorFidelityTest.php tests/Feature/M43CoursePreservationTest.php
node --test tests/Browser/m41-practice-ui.test.cjs tests/Browser/m41-design-http.test.cjs tests/Browser/m42-native-style-policy.test.cjs tests/Browser/m42-design-http.test.cjs tests/Browser/seo-m43-local.test.cjs tests/Browser/m43-practice-ui.test.cjs
node tools/diagnostics/seo-m43-local.cjs --pages D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-local/browser-after-v1 after-v5
node tools/diagnostics/seo-m43-local.cjs --supplemental D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-local/browser-supplemental-v2 supplemental-v2
node tools/diagnostics/capture-m43-mobile-scroll.cjs mobile-scroll-v1
php -d opcache.enable_cli=0 tools/diagnostics/inspect-m43-working-local.php m43-final-v1.json
```

Додаткові точні PHP wrapper commands (у фактичному запуску `python` — `C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe`):

```powershell
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m43-shared-hunks-final tests/Feature/M43ContentPatchTest.php --filter shared_hunk
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m43-m42-regressions-v1 tests/Feature/M42NativeDesignPackageTest.php tests/Feature/M42DesignEvidenceTest.php
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m43-shared-regressions-v1 tests/Feature/M26ContentPackageTest.php tests/Feature/M26InteractivePracticePackageTest.php tests/Feature/M26PointDetailsTest.php tests/Feature/M26ContentPatchTest.php tests/Feature/M26InteractivePracticePatchTest.php tests/Feature/M41AuthorFidelityTest.php tests/Feature/M41AuthoredTenseComparisonsPackageTest.php tests/Feature/M41ExistingDesignPackageTest.php tests/Feature/M41ContentPatchTest.php tests/Feature/M41LocalTargetGuardTest.php tests/Unit/M41DesignEvidenceTest.php tests/Feature/UnifiedTheoryNativePresentationTest.php tests/Feature/TheorySectionRenderingTest.php tests/Feature/TheoryInlineHtmlRenderingTest.php tests/Feature/TheorySidebarPresentationTest.php tests/Feature/TheoryPagePromptLinkedTestsTest.php tests/Feature/PolyglotCourseBlueprintTest.php tests/Feature/PolyglotCourseLandingPageTest.php
```

Wrapper додає `--do-not-cache-result --colors=never --log-junit <isolated-runtime>/phpunit.junit.xml`. Node browser commands використовували `NODE_PATH=C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules`. Назви evidence exclusive: повторний запуск потребує нового label, не перезаписування старого доказу.

## Фінальний handoff

Фінальні source/DB captures та staged review завершено: **57 явно обраних файлів**, `git diff --cached --check` exit 0 із лише документованими per-file frozen-EOF attributes; усі 7 frozen staged blobs мають початкові SHA. Shared hunks прочитано окремо; ROOT-only зміни не входять до index. `post-apply-diagnostic-followup-v2.json` приватно фіксує новий viewport capture tool і видалення одного EOF blank у metadata diagnostic; application/runtime/DB від цього не змінювалися.

Приватні screenshots, DB backup/proof/diagnostics, `.env`, runtime caches, vendor/build і сторонні ROOT changes у commit не включені. Звіт входить до implementation commit робочої гілки; його SHA можна отримати через `git log -1 --format=%H -- docs/reports/seo-m43-authored-tense-usage.md`. Після normal push full SHA та рівність remote HEAD повідомляються у фінальному handoff. Немає force push, PR, main update або production deploy.
