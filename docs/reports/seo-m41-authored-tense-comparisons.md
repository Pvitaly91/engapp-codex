# M41 — авторські порівняння часів, редакція 1.0.1

Локальне приймання M41 завершено — PASS. Погоджений author master 1.0.1 реально застосовано до робочої `gramlyze.loc`; guarded apply, postconditions, дві повторні no-op операції, браузерна практика, no-JS, регресії та фінальний SELECT підтверджені. Source/fixture/offline PASS не підміняє actual local apply. Точний Git handoff SHA фіксується окремо після commit/push, не самопосилально всередині цього звіту.

Scope: лише три наявні UK сторінки `http://gramlyze.loc`, база M40 `ab61310a81f2389353c264fe23266025fe49ffd6`, гілка `codex/seo-m41-authored-tense-comparisons`, репозиторій `Pvitaly91/engapp-codex`. Production `.com/.ub`, main, PR, deploy, залежності, Apache/hosts, банківські дані та прогрес не входять до дозволених змін. Нові URL не створюються.

## Авторська редакція та exact-byte provenance

Усі три файли перенесено з наданого ZIP побайтово, UTF-8/LF. Авторський master не редагувався для реалізації або проходження тесту. SHA-256:

| Джерело | SHA-256 |
| --- | --- |
| `docs/content/m41-authored-tense-comparisons.v1.0.1.json` | `9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553` |
| `docs/content/m41-author-correction.v1.0.1.json` | `efe1d4ed3c691b8108e168d7dc182937fb9667ab96f192f2375d055c97137f89` |
| `docs/content/m41-codex-approval-and-correction.md` | `1c74999e7f50ea3b7f1b3d363a8bd42d96f092c334d486893b78273072119e40` |
| `database/content-patches/m41-authored-tense-comparisons-before.json` | `5966e2b5e330030fc399b77a5a80880a57462daac4b1869f94b4027cdbcae808` |
| `database/content-patches/m41-authored-tense-comparisons.v1.0.1.json` | `1324f0627dcd53b70b135b78c06204c3a77b6e02fe0a48056ac7750b650becfc` |

Master: 120078 bytes, без BOM/CR, один terminal LF. Reverse substitutions рівно трьох author-correction значень відтворюють точні bytes початкового v1 з SHA `a9569a61f37643951e7342de66dc7f3967951d4d7187c106a0f8703cf3653dcf`. Початковий v1 не переписаний. Structural diff v1 → 1.0.1 має лише:

1. `/version`: `1.0.0` → `1.0.1`.
2. `/status`: `author_preview_pending_approval` → `author_revision_ready_for_local_implementation`.
3. `/lessons/2/sections/2/points/1/paragraphs_uk/1`: єдина явно погоджена авторська текстова поправка.

Було: «Зразок нижче не означає, що форма написалася двічі: це одна подія, подана спочатку як новий результат, потім із часом.»

Стало: «У прикладі нижче форму надіслали лише один раз: спочатку повідомляємо про результат, а потім уточнюємо час надсилання.»

Приклад і переклад незмінні: `I have sent the form. I sent it at nine this morning.` / «Я надіслав форму. Я надіслав її сьогодні о дев’ятій.» Решта всіх правил, прикладів, перекладів, деталей, вправ, ключів, aliases, tokens, source refs та policy values v1 → 1.0.1 структурно незмінні. Read-only provenance audit PASS. Це technical transfer готового авторського payload, не самостійна мовна редактура та не незалежна повторна експертиза зовнішніх граматичних джерел.

Новий M41 learner text НАВМИСНО відрізняється від старої редакції цільових сторінок у accepted-before БД. Старий стан звіряється з accepted baseline definitions/before, а новий — з master 1.0.1. Правило «лише один абзац змінено» стосується авторського v1 → 1.0.1, не старої БД → нової редакції M41. Frozen M39/M40 та попередні авторські snapshots не переписуються.

## Цілі та збережена ідентичність

Identity suffixes нижче мають prefix `Database\Seeders\Page_V3\Tenses\`.

| Сторінка / теорія | Identity suffix | Accepted baseline Git blob | Source blocks before → after | Display level |
| --- | --- | --- | --- | --- |
| [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous) | `TensesPastSimpleVsPastContinuousTheorySeeder` | `a75b745f9733301b900a4f8d82c5d500b6452074` | 8 → 9 | A2 |
| [Present Simple vs Present Continuous](http://gramlyze.loc/theory/tenses/present-simple-vs-present-continuous) | `TensesPresentSimpleVsPresentContinuousTheorySeeder` | `a6893cbdaa81e028e240eda2af49fe911a8cab50` | 8 → 9 | A1–A2 |
| [Present Perfect vs Past Simple](http://gramlyze.loc/theory/tenses/present-perfect-vs-past-simple) | `TensesPresentPerfectVsPastSimpleTheorySeeder` | `57e1e1336fbe597d40dd630a6cd9b4c8618e81a7` | 6 → 9 | A2–B1 |

Page.title, slug, owner identity, schema/type, locale, category ancestry, tags/base_tags, test relations та EN/PL localizations збережені. У кожного наявного native block незмінні position/order, UUID, column, heading, level, tags/inheritance та решта config. UUID не залежить від native type: збереження попередніх position підтримує exact legacy UUID.

У старих teaching slots змінюються тільки type/body: authored sections стають `usage-panels`, hero лишається `hero`, navigation лишається `navigation-chips`. Це погоджена presentation migration, не твердження про збереження всіх старих teaching-body/type values. Немає flattening або перенесення старих columns. Past/Present: hero slot0, sections slots1–6, navigation slot7, нова practice slot8. Perfect: hero0, sections1–4, navigation5; нові sections5/6 у slots6/7, practice8. П'ять нових source blocks мають окремі deterministic `m41-*` UUID keys.

Технічні levels не уніфіковано: Past block/body A2; Present block/body A1–A2; Perfect hero.block.level лишається A2, hero.body.level лишається A2–B1, наявні section levels A2. Нові slots використовують відповідний попередній block level.

Master.subtitle точно збережено в `page.subtitle_text` та `hero.intro`. Для `subtitle_html` потрібна технічна оболонка `<p><strong>{original Page.title}</strong> — {exact master.subtitle}</p>`. Реальний `PageController::extractLocalizedTitleFromSubtitle` інакше приймає фразу перед em dash за H1: plain draft Past subtitle помилково давав «Що сталося». Ізольований regression fixture порівняв реальний controller-derived title до/після для всіх трьох сторінок: погоджені English H1 збережені. Global controller не змінювався; це markup/context із вже погодженим title, не авторський rewrite. Ще не застосований draft projection був приватно заархівований перед цим виправленням; frozen accepted master/before не змінені.

Старі navigation title/config/items/order/current flags збережені як exact prefix. Після real local guest GET200 без redirect з правильним H1 додано лише відсутні `master.related` items: Past → Narrative Tenses; Present → Past Simple vs Past Continuous; Perfect → Present Perfect і Past Simple. Приватний route proof SHA `f6cc4618a037626e031aad7d5e0d78428d699e2fdc314effe6950d6fafa56d38` не комітиться.

Canonical definition SHA-256 після перенесення:

- Past: `f7a78deca8776a228a39a0c57f85cd3047e283eb0676a3ec52725c7244a1036b`.
- Present: `0e67fd34f16704fc15ec649d2cabfa78aabdb3a5c2220f669555a35a28b59785`.
- Perfect: `7ff677f343d3b06b307d4d55cd47a29ffcb5a5fb8a27a9c14687583280d1a945`.

## Повний basic та finite point-level details

18 sections, 35 basic points, 3 bilingual form tables, 14 явно авторських disclosures = 4/5/5. Усі короткі ремарки, застереження, пояснення форми, переклади та основні приклади лишаються visible basic. Не застосовується глобальний runtime word threshold і не вигадується detail заради кнопки. Нижче весь finite detail scope; кожний detail має два авторські пояснювальні абзаци й контекстні приклади.

| Owner | Point ID | Авторський detail title | Семантичне рішення |
| --- | --- | --- | --- |
| Past | `past-event` | Тривала дія теж може бути в Past Simple | Окреме поглиблення: duration не визначає aspect; цілий епізод проти процесу всередині нього. |
| Past | `past-process` | Що ця форма повідомляє, а чого не повідомляє | Процес у моменті не встановлює загального завершення; окреме речення вводить результат. |
| Past | `past-background` | Was making чи made після when? | Два граматично можливі речення з різним часовим змістом, не безумовна помилка. |
| Past | `past-parallel` | Після while не обов’язково два Continuous | Подія всередині фонового процесу; спільна рамка не означає однакову duration. |
| Present | `present-routine` | Звичайний не означає «назавжди» | Звичайне/тимчасове подання ситуації без вигаданого майбутнього. |
| Present | `present-around` | Книжка зараз закрита — це не суперечність | Поточна активність ширша за секунду мовлення, без гарантії результату. |
| Present | `present-think` | Перевіряй цілий вислів, а не список «заборонених» слів | Значення think/have визначає state/action; не механічний blacklist. |
| Present | `present-markers` | Повторюваність також може бути тимчасовою | Щоденність і тимчасовість сумісні; однозначній вправі потрібен заданий акцент. |
| Present | `present-always` | Повтор і ставлення, а не обов’язково скарга | Повторювані випадки й різні ставлення мовця, не безперервність/обов'язкове роздратування. |
| Perfect | `perfect-then` | Минула подія теж може мати результат зараз | Завершена минула дата й теперішній наслідок можуть співіснувати. |
| Perfect | `perfect-ever` | Ever може стосуватися завершеного минулого періоду | Life-to-now experience проти завершеного етапу; контекст не стирається через ever. |
| Perfect | `perfect-today` | Незавершений день містить завершені епізоди | Підсумок до зараз проти окремої події з часом усередині today. |
| Perfect | `perfect-duration` | For не належить лише одному часу | Duration завершеного періоду проти continuing state; since-start не перетворюється на duration. |
| Perfect | `perfect-recent` | Чому Did you call … yet? не треба автоматично позначати помилкою | Межі британської навчальної моделі та американського варіанта, не загальна взаємозамінність. |

Finite presentation прив'язана до master SHA, exact owner/UK locale, UUID, type/order та всіх data bytes. Лише code-owned view може бути обраний. Повний detail зберігається в native source note; успішна finite projection прибирає тільки цей note з basic і додає його до власного point disclosure. Невідома/змінена identity повертає повний native fallback з basic, details, таблицею, прикладами та перекладами, без guessed disclosure. Таблиці мають локальний horizontal scroll, focusable region і author section title як aria-label.

Ізольований SSR через справжній `theory.partials.content-block` dispatcher підтвердив усі 35 basic points, 14 closed-by-default details, усі таблиці й повний invalid-identity fallback. Це не заміна actual no-JS/browser приймання.

## Усі 18 завдань і 32 required controls

Тип interaction визначено авторським змістом, не квотою 2+2+2. 32 controls = 9 select + 8 choice + 15 manual. 14 із 18 cases складені; score кожного case не перевищує один і правильний лише після всіх required controls. В options лише відповіді; stimuli, full prompts, EN answer examples, UK translations і пояснення збережені окремо. Natural casing без ALL CAPS. Таблиця містить кожний task/control і правильний author key; усі неправильні candidates також exact у master/projection та independently asserted.

| Task / авторський title | Required controls: interaction → правильна відповідь |
| --- | --- |
| `past-q1` — Завершена дія й процес у заданий момент | `past-q1a` select → `repaired`; `past-q1b` select → `was sanding` |
| `past-q2` — Чи є година автоматичною підказкою? | `past-q2a` choice → `The bus arrived at eight.` |
| `past-q3` — Дві граматично можливі розповіді | `past-q3a` choice → «Готування вже було в процесі.»; `past-q3b` choice → «У цій розповіді вона приготувала чай після приходу.» |
| `past-q4` — Виправ тільки форму | `past-q4a` manual → `Did she take the key?`; `past-q4b` manual → `We were waiting outside.` |
| `past-q5` — When не доводить зупинки | `past-q5a` manual → `I was reading when the phone rang.`; `past-q5b` choice → «Ні, наступну дію не зазначено.» |
| `past-q6` — Коротка розповідь без нового факту | `past-q6a` manual → `At nine yesterday, I was waiting outside.`; `past-q6b` manual → `Then the door opened.` |
| `present-q1` — Звичка й поточна дія | `present-q1a` select → `cooks`; `present-q1b` select → `is washing` |
| `present-q2` — Правильне питання | `present-q2a` manual → `Does Kateryna work here?` |
| `present-q3` — Книжка зараз закрита | `present-q3a` choice → «Так, це поточна активність цього тижня.» |
| `present-q4` — Стан і дія | `present-q4a` select → `know`; `present-q4b` select → `am thinking` |
| `present-q5` — Звичний режим і сьогодні | `present-q5a` manual → `I usually work at the shop.`; `present-q5b` manual → `I am working from home today.` |
| `present-q6` — Після перевірки розкладу | `present-q6a` select → `leaves`; `present-q6b` manual → `I am meeting Marta at eight tomorrow.` |
| `perfect-q1` — До зараз і вчора | `perfect-q1a` select → `have checked`; `perfect-q1b` select → `checked` |
| `perfect-q2` — Досвід і конкретний випадок | `perfect-q2a` manual → `Have you ever travelled by ferry?`; `perfect-q2b` manual → `I travelled by ferry last summer.` |
| `perfect-q3` — Результат зараз не скасовує yesterday | `perfect-q3a` choice → «Past Simple датує втрату вчора; перепустка досі не знайдена.» |
| `perfect-q4` — V1, V2 або V3? | `perfect-q4a` manual → `She has written the note.`; `perfect-q4b` manual → `Did she write the note yesterday?` |
| `perfect-q5` — Жили й переїхали — чи досі живемо? | `perfect-q5a` choice → `We have lived here for three years.`; `perfect-q5b` choice → `We lived there for three years, and then we moved.` |
| `perfect-q6` — Новина та подробиця | `perfect-q6a` manual → `I have sent the form.`; `perfect-q6b` manual → `I sent it at nine this morning.` |

### Усі 20 явно заданих manual variants

Finite M41 wrapper приймає тільки явні author variants після typography/case/space/terminal-punctuation comparison. Він не виконує універсальну semantic перевірку й не розгортає нові contractions/перефразування автоматично. Legacy M39/M40 defaults shared mechanics не змінені. Повний список для 15 manual controls:

| Control | Explicit accepted answers |
| --- | --- |
| `past-q4a` | `Did she take the key?` |
| `past-q4b` | `We were waiting outside.` |
| `past-q5a` | `I was reading when the phone rang.` |
| `past-q6a` | `At nine yesterday, I was waiting outside.` |
| `past-q6b` | `Then the door opened.` |
| `present-q2a` | `Does Kateryna work here?` |
| `present-q5a` | `I usually work at the shop.` |
| `present-q5b` | `I am working from home today.`; `I'm working from home today.` |
| `present-q6b` | `I am meeting Marta at eight tomorrow.`; `I'm meeting Marta at eight tomorrow.` |
| `perfect-q2a` | `Have you ever travelled by ferry?`; `Have you ever traveled by ferry?` |
| `perfect-q2b` | `I travelled by ferry last summer.`; `I traveled by ferry last summer.` |
| `perfect-q4a` | `She has written the note.` |
| `perfect-q4b` | `Did she write the note yesterday?` |
| `perfect-q6a` | `I have sent the form.`; `I've sent the form.` |
| `perfect-q6b` | `I sent it at nine this morning.` |

Усі 55 logical tokens exact; join у canonical order дорівнює canonical_answer для кожного manual control. Перемішування, ручний ввід, token history/reset, keyboard та aliases мають бути перевірені також у real browser, не лише helper fixtures.

## Точний own-bank scope

Actual read-only inventory підтвердив primary mappings, а не вигадану кількість 48. Class prefix: `Database\Seeders\V3\Polyglot\`.

| Owner | Primary seeder class suffix | question_type / levels / actual question_count |
| --- | --- | --- |
| Past | `PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder` | 4 / A1–C2 / 72 |
| Present | `PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder` | 4 / A1–C2 / 72 |
| Perfect | `PolyglotPresentPerfectVsPastSimpleLessonSeeder` | 4 / A2 / 24 |

Пов'язані test URLs (наявні, не нові): [Past Simple vs Past Continuous](http://gramlyze.loc/test/tenses/past-simple-vs-past-continuous), [Present Simple vs Present Continuous](http://gramlyze.loc/test/tenses/present-simple-vs-present-continuous), [Present Perfect vs Past Simple](http://gramlyze.loc/test/tenses/present-perfect-vs-past-simple). Загальна linked inventory включно з іншими наявними seeder modes — 144/144/168; finite widget бере 5 питань лише зі свого primary type4 bank 72/72/24.

Приватний inventory SHA `f2bc9cafb20c24da5702e1c6b583b61b3f3d034bc91434149d5cc3a8174d3600`. Versioned projection містить лише class/type/level/levels/count і opaque question_ids SHA, без raw local IDs/test_url/private DB inventory. Банківські questions/answers/options/verb_hint/pivots не редагуються. Нові 18 author self-checks не є переписуванням Mixed bank. Progress та чужі owners захищає окремий guarded pipeline.

## Перевірки, що вже виконані

| Перевірка | Підтверджений результат |
| --- | --- |
| ZIP/current master/correction/approval exact SHA та reverse-v1 bytes | PASS; лише три дозволені structural differences, одна learner-facing поправка |
| Read-only `project-m41-sources.php --audit-master` | PASS: 3 lessons / 18 tasks / 32 controls |
| Deterministic `--check` із actual bank/related evidence | PASS: after/after/after, frozen projection1324 та definition SHA відтворюються |
| `M41AuthorFidelityTest` + `M41AuthoredTenseComparisonsPackageTest` | Final 33 tests / 2122 assertions, zero failures/errors; negative practice-identity fallback fixture включено |
| Runtime identity / mutation rejection | Exact owner/locale/UUID/type/order/data checks; 24 mutations actual author fragments відхилено |
| Controller title-extraction / H1 regression fixture | English titles збережені для трьох targets; unsafe plain draft Past extraction відтворено |
| Dispatcher SSR/fallback | 14 default-closed point details, повний basic/таблиці/EN–UK translations; practice fallback exact32labels/9stimuli/35optionlabels/55tokens/18prompts/18keys без interactive grader |
| Related routes real guest GET | 4/4 HTTP200, no redirect, expected H1; append-only author links |
| П'ять відповідних Vitest files, один worker | 43/43 tests, 5/5 files PASS; actual child exit0/signal null, stderr0 bytes, Vitest duration119.41s |
| `M41ContentPatchTest` + `M41LocalTargetGuardTest` | 68 tests / 179 assertions PASS; protected files unchanged |
| Обов'язкові попередні PHP M39/M40 guards | 158 tests / 403 assertions PASS; protected files unchanged |
| M41 practice/local + попередні M39/M40 Node UI tests | 365/365 PASS, включно з rejection неавторського `She's written the note.` тільки для M41 |

Ізольовані PHP tests використовують sqlite:memory та окремий private runtime, не working `.env`/БД. Є одна попередня PHP8.5 deprecation у `config/database.php:62`: `PDO::MYSQL_ATTR_SSL_CA`; це не M41 failure і не привід для сторонньої зміни конфігурації. Ранній SSR test-harness error через відсутній DOM fragment body виправлено явною html/head/body оболонкою; після цього повний final run PASS. Попередні/повторні результати не складаються у вигаданий aggregate.

Actual Vitest child command (Node22.15.0, existing dependencies; no application build/install):

```text
node node_modules/vitest/vitest.mjs run tests/js/publicAssets.test.js tests/js/unifiedTheoryDesign.test.js tests/js/theorySidebarLayout.test.js tests/js/theorySections.test.js tests/js/theoryNavigation.test.js --maxWorkers=1 --minWorkers=1 --no-file-parallelism --no-color
```

Private UTF-8 capture: `storage/app/seo-m41-local/m41-vitest-related-v1-383b99e3934748d4a96214b4f884bd68-result.json`, відповідні `-stdout.log` і `-stderr.log`. Actual child exit code та збережений Vitest summary незалежно reread: 43/43, 5/5, stdout658 bytes, stderr0. Result SHA `fb3e4f7d0c54a88ba4c7ff6cd4531eaade54e53bb3c7c10d998f735b2ad95d28`; stdout SHA `c4c84411840bbb1a678bc84cfe47ba36a5ed762c76e0da0884787ada602218db`. Wrapper не передруковує Unicode checkmarks через Windows cp1251 console й повертає actual child status. `publicAssets` виконує власну ізольовану CSS compilation fixture; application assets не збиралися. Captures/wrapper залишаються приватними, не Git artifacts.

## Фізичний proof, fresh preview та actual local apply

Physical/vhost/web–CLI proof PASS: local application `D:/DEV/htdocs/gramlyze.loc`, document root `public`, MySQL localhost:3306 `gr2`; runtime environment production, effective SiteMode development для `.loc`. Це локальний proof, не production request або deploy.

Accepted-before source → actual DB gate PASS для всіх трьох exact owners до source transfer. Fresh preview `m41-authored-tense-comparisons-v1-0-1` має state `before`, digest `beb414142f415f665590fbbc37f99f2f6bc86725c9aaaebc756ba73c379b0f80`: 28 updates = 3 Page.text + 25 UK text_blocks type/body; 5 inserts; 0 deletes. Page title/slug/type/owner/category та original block identity/order/column/heading/levels/pivots, EN/PL і чужі дані не входять у permitted diff.

Приватні source backup manifests існують: `bootstrap-source-backup-d81656eb2da85dbe` (3 bootstrap records) і `source-backup-af0c74a0ed14c668` (24 source records). Вони, SQL preview, physical proofs, logs, screenshots, private draft archive і runtime caches не комітяться.

Після повторного fresh physical proof (2026-10-06 18:54:26 UTC) створено exclusive DB backup `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/m41-backup-v1.json`, SHA-256 `0561c52770427f8224ba15f1e8980849f8e7448db765833feffaaac7b12bbaf9`. Це exact reviewed-before plan для scoped rollback, не повний дамп всієї БД; backup побайтово збігається з reviewed preview.

Guarded transaction застосувала 28 updates (3 Page.text + 25 UK text_blocks type/body), 5 inserts, 0 deletes. Actual SELECT `m41-after-v1.json` (18:56:49 UTC), SHA `4465cddb038e3391ef2eebc883bdb3e1968332189c9b529cdc913fa9ffa25a4b`, та незалежний verifier дали PASS: 6563 text_blocks загалом, усі наявні IDs/UUID/order/metadata/timestamps збережені, нові rows exact-source-derived. Усі 24 protected fingerprints стабільні: 22 інші таблиці незмінні повністю, pages fingerprint виключає тільки 3 дозволені text values, а 6533 non-target text_blocks лишилися exact з SHA `b941770db74c6511345575f2466bbaf5a32764ba4d1c04d6c483c91882232a40`. Усі 47 попередніх owners, EN/PL, banks, pivots та progress tables незмінні.

Два повторні `--apply` завершилися exit0 зі `status=no-op`, `updated=0`, `inserted=0`; unused no-op backup files не створені. Тимчасовий GET/loopback-only nonce route видалено: `routes/api.php` відновлено побайтово до SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, реальний guest GET до видаленого URL повернув 404. Маршрут і private proof не входять у commit.

## Реальні сторінки та практика — підтверджені завершені фази

Усі запити й browser contexts спрямовані тільки на `http://gramlyze.loc`, без admin/auth session. Chrome headless через Playwright, desktop1440×1000 та mobile390×844; окремі contexts і світла/темна теми, без підміни working `.env` та production URL.

- `m41-pre-v1-visual.json`: 36/36 PRE states (18 desktop + 18 mobile), keys приховані, full prompts/context/stimuli/32 controls exact-source, candidates answer-only. MAIN перед будь-яким functional scoring особисто оглянув усі 36 PNG; gate `m41-main-pre-review-v1.json` bound до manifest SHA `662fe7fba6fff6e46250ce1d6d8e5f14c76a6eef4145d929b9d3359825dab2ff`.
- `m41-pages-v1-pages.json`: 12/12 page states PASS, усі 35 basic points, 3 EN/UK form tables, 56 own-point disclosure interactions (14 × 4 viewport/theme combinations), keyboard, deep links, print/reset і всі 19 legacy native anchors. Widget відобразив 5 питань на кожній сторінці, exact primary banks 72/72/24, type4. 62 PNG включають 18 closed basic sections та 14 opened own-point details. MAIN оглянув усі 12 page tops, 18 sections, 14 details і 3 mobile right-table views; приватний content review `m41-main-content-review-v1.json`.
- `m41-post-v1-acceptance.json`: 36/36 functional cases PASS, 72 POST PNG (correct + wrong) разом із 36 PRE = 108 required visual states. Усі 20 explicit aliases, typography/terminal-punctuation variants, manual/token assembly, visible keyboard focus, reset і score6/6 → 0/6 перевірені реально. Неавторське `She's written the note.` відхилено LIVE (straight/smart/no-terminal), а explicit master answers прийняті. MAIN додатково оглянув 12 representative POST PNG для select/choice/manual/compound у двох viewport; full 72 captures і exact DOM assertions збережені, не видаються за 72 ручні огляди.
- `m41-table-v1-tables.json`: 6/6 actual mobile native-table states PASS. Region справді фокусується, ArrowRight дає ненульовий scroll delta і дозволяє дістатися right edge (254px; actual client322px / scroll576px). Це browser measurement, не припущення за CSS min-width; 6 додаткових right-edge PNG збережено.

Learning main/cards overflow = 0 в усіх 12 states. Старий randomized декоративний background окремо має document overflow 0–15px (mobile rows 8/0/9/15/6/5); він не є обрізанням навчального контенту і не маскувався global overflow rule. У завершених browser фазах 0 JS/HTTP/console errors і 0 scope violations; author master/BEFORE/projection/definitions/JS SHA незмінні. SVG/decorative background, sidebar та хедер не змінювалися заради цих результатів.

## Supplemental acceptance, HTTP і відомі обмеження

Supplemental browser assertions завершені: 28/28 omission checks (кожен із 14 compound tasks перевірено без першої і без другої required part), 64/64 semantic negatives відхилено, усі 36 M39/M40 cases перевірено із власними первинними acceptance helpers. Без JavaScript перевірено 9 сторінок/54 native answer keys; M41 окремо має 15 читабельних literal token banks/55 groups та 14 native point details. Усі завершені browser rows PASS, 0 console/page/scope violations. Службово заблокований script при `javaScriptEnabled=false` записано як expected disabled-script event, не приховано як unexpected error.

Перший supplemental контейнер навмисно залишено `pass:false`: після успішних browser sections фінальна HTTP phase двічі timed out на старому `/theory/past-perfect-continuous` (19:15:28.262–19:15:58.267 UTC та 19:15:58.268–19:16:28.277 UTC), `TimeoutError: The operation was aborted due to timeout`. Так само цей URL timed out до M41 у `before-v1`; це не доказ причини M41 або блокування Googlebot. Файл `m41-supplemental-v1-supplemental.json` не переписувався і не позначений PASS.

Незалежний fresh repeat `m41-after-http-v1-http.json` (19:19:20.988–19:20:02.429 UTC) завершив весь набір: 55/55 real guest GET200, без retries/errors. Нові title/description перевірено exact локальним `PageMetadata` щодо master; target H1/canonical/robots/X-Robots/title/OG/Twitter title збережені, description/OG/Twitter description погоджено оновлені. Усі 48 control pages мають незмінні main-text SHA, metadata, details та anchors. Ordered sitemap URLs = 554, SHA `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334` незмінний; це SHA ordered URL list, не всіх XML bytes.

Course URL `/courses/english-grammar-theory/lesson/tenses/past-simple-vs-past-continuous`: GET200, H1 `Past Simple vs Past Continuous`, source6 prompts/6 keys, course content present. Лише server-properties check, без обходу browser/course gate та без заяви про visual course acceptance. Результати завершених browser assertions і незалежного fresh HTTP узгоджено в новому exclusive `m41-final-reconciliation-v1.json`, SHA `5ce112f1fef52c1ae81481cb1a803afaf0af6fdad408984ce39561c1666a312b`; він bind-ить original fail, fresh PASS та всі source/artifact SHA. Попередні невдачі залишено видимими.

### Крайовий fallback, знайдений на фінальному code audit

Valid finite path та його no-JS UI пройшли приймання. Додатковий code audit знайшов інший крайовий випадок: при invalid practice identity резервна гілка показувала prompts/keys, але пропускала окремі `case.controls` labels/stimuli/options/tokens. Зроблено вузьке presentation-only виправлення лише цієї M41 fallback гілки: escaped static text для всіх 32 controls/9 stimuli/35 option labels/55 tokens, із 18 native answer keys; без Alpine factory, input/check/reset або guessed grading. Авторські джерела, projections, definitions, JS, guard services і БД не змінювалися.

Новий негативний SSR fixture підтвердив ці exact arrays; final author/package suite 33/2122 PASS. Старий ROOT suffix після M41 branch побайтово збігається з оригінальною shared backup (збережено сторонні PPC hunks); новий M41 prefix ROOT/WT exact. Final practice-set raw SHA: WT `292b37078502408028663d5142b1ea8dd352d4fb40fad8ae82fb51b3f7a9676a`, ROOT `45aa95f8a7c4fb5a1a44fda0da9a2f0d6ca7d5631b6d75de577c3a78ea6ac0ed`. Private `m41-presentation-fallback-review-v1.json` пояснює intentional difference та chronology. Initial plan/backup/source-review/proof залишено історичними й незмінними; після presentation-only code change initial plan не слід повторно використовувати — guard вимагає fresh review/preview для будь-якого майбутнього DB apply.

Final SELECT до цього fallback-only виправлення вже мав PASS: `m41-after-noop-v1.json`, SHA `1b3ab635cc2abf4d3cb7c6a2fb44b1655bcf8238fed90d16f0efbe30cb5f63a7`; він дорівнює post-apply SELECT, крім `at`, і не переписувався.

Після остаточної fallback-правки виконано bounded current live recheck: 6 fresh contexts, 36 exact initial cases, 36 functional cases з усіма aliases/token/manual/keyboard/reset і 72 нові POST PNG; 3 M41 no-JS сторінки/18 keys/15 banks/55 groups/14 details; 3 fresh target GET з metadata/main-text exact до попереднього accepted after HTTP, без fallback attrs на valid finite path. `m41-post-fallback-v1.json`, SHA `29708c3757db3ede10562dde285b04c9fc799a002fa6ab9d2b20af8ea258b66b`, bind-ить current ROOT/WT view SHA та незмінні 6 valid-UI source hashes. Старі artifacts незмінні. Сукупно збережено 320 screenshots: первинні 248 та 72 post-fallback; required matrix має 108 states, повтори не видаються за нові унікальні tasks.

Після всіх цих browser/HTTP requests опубліковано окремий `m41-after-noop-v2.json` (19:34:15 UTC), SHA `f3c26e6a82b21f7184a0b839a1fcab7adacbfec535d20505d339c26232fe92b1`. Незалежний verifier з historical applied preview/backup — PASS. v2 дорівнює final-v1/post-apply SELECT, крім `at`: 24 protected fingerprints, 22 raw tables, 47 prior owners, exact primary/all-linked banks та progress незмінні; лише погоджені 3 Page.text/25 UK updates/5 inserts. Усі IDs/UUID/order/full metadata/timestamps збережені; 6533 non-target rows exact. Backup і original preview SHA `0561c527…` незмінні; unused no-op backup files відсутні, routes file byte-exact. Це SELECT-only фінальне підтвердження, не новий apply.

## Final source/staged review та Git handoff

Final `--audit-master`, deterministic `--check` і saved-evidence verification PASS; original v1/master 1.0.1/BEFORE/projection/3 definition SHA unchanged. PHP lint — 21 files; Node syntax — 7 files; whitespace diff check PASS. Фінальна перевірка privacy/scope не знайшла додаткових blockers або secret-pattern hits. Допоміжні incorrect semantic fixtures — лише test inputs, не нові learner candidates або aliases.

Явно обрано 46 M41 source/test/report files. Staged paths звіряються exact allowlist; shared diff обмежено finite M41 dispatcher/fallback, stimulus/no-JS support і M26 opt-in hook із default page-write denial. Жодних `.env`, vendor/build, private `storage`, dumps/backups, nonce routes, cookies/tokens/logs/screenshots чи сторонніх незавершених змін у stage. Dirty ROOT збережено, HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd`; робота ведеться в окремому існуючому worktree від accepted M40 `ab61310a81f2389353c264fe23266025fe49ffd6`.

Гілка handoff: `codex/seo-m41-authored-tense-comparisons`. Normal commit/push — лише ця гілка, без force/main/PR/deploy. Exact full commit SHA та результат `ls-remote == HEAD` записуються після Git операцій у private `m41-git-handoff-v1.json` і фінальному повідомленні; вони не вбудовуються в сам commit як self-referential SHA. Production `.com/.ub` не перевірявся й не змінювався. Автоматичний перехід до іншого пакета не виконується.

Усі локальні навчальні, data-safety та acceptance кроки завершено. Окремі відомі обмеження: historical PPC HTTP timeout із успішним повним fresh repeat, попередня PHP8.5 PDO deprecation, decorative background overflow до15px; вони не приховані й не виправлялися сторонніми server/config змінами.
