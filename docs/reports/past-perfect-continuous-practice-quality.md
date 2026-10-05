# Past Perfect Continuous — якість практики й тестів

## Межі та версія

Перевірено й виправлено Gramlyze локально, на `http://gramlyze.loc`. База accepted M35: `4644a1729d326b9a967d03c397998506f00d704b`; робоча гілка `codex/past-perfect-continuous-practice-quality`. Це не відновлення старої M26-гілки: M26–M35 залишаються в історії. Основний checkout мав сторонні незавершені зміни; їх не скидали. Versioned роботу виконано в окремому наявному worktree, до реального `.loc` перенесено тільки явно перелічені файли з перевіркою конфліктів і резервною копією.

Production `.com`/`.ub`, `main`, Apache/XAMPP/hosts, міграції та повне пересівання не використовувалися. Дизайн, маршрути, SEO й frozen author payload M26 не переписувалися.

## Inventory

Повний машинний inventory: [624 UUID та 24 статичні завдання](past-perfect-continuous-quality-inventory.json). Він містить route → page/block/bundle → saved test → seeder → definition → editorial/persistent UUID → type → level. Переклади та показ одного UUID у кількох режимах не рахуються новими авторськими питаннями.

Канонічні сторінки теорії:

- [Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms)
- [Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives)
- [Questions and Short Answers](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions)
- [Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions)

Публічні mixed-тести:

- [Forms](http://gramlyze.loc/test/past-perfect-continuous/forms)
- [Negatives](http://gramlyze.loc/test/past-perfect-continuous/negatives)
- [Questions](http://gramlyze.loc/test/past-perfect-continuous/questions)
- [Time Expressions](http://gramlyze.loc/test/past-perfect-continuous/time-expressions)

Фактично підключені авторські банки:

| Підтема | Mixed, type 0 | Sentence Builder, type 4 | Рівні | Змінені умови |
| --- | ---: | ---: | --- | ---: |
| Forms | [72](http://gramlyze.loc/test/past-perfect-continuous-forms-all-levels-v3) | [72](http://gramlyze.loc/test/polyglot-past-perfect-continuous-forms-all-levels) | По 12 кожного типу на A1–C2 | 144 |
| Negatives | [72](http://gramlyze.loc/test/past-perfect-continuous-negatives-all-levels-v3) | [72](http://gramlyze.loc/test/polyglot-past-perfect-continuous-negatives-all-levels) | По 12 кожного типу на A1–C2 | 144 |
| Questions | [72](http://gramlyze.loc/test/past-perfect-continuous-questions-all-levels-v3) | [72](http://gramlyze.loc/test/polyglot-past-perfect-continuous-questions-all-levels) | По 12 кожного типу на A1–C2 | 144 |
| Time Expressions | [72](http://gramlyze.loc/test/past-perfect-continuous-time-expressions-all-levels-v3) | [72](http://gramlyze.loc/test/polyglot-past-perfect-continuous-time-expressions-all-levels) | По 12 кожного типу на A1–C2 | 144 |
| Basics legacy, підключений до Forms | — | [48](http://gramlyze.loc/test/polyglot-past-perfect-continuous-basics-b2) | B2 | 48 |
| Разом | 288 | 336 | Усі наявні рівні збережені | 624 |

Окремі comparison-банки Past Perfect / Past Perfect Continuous існують, але не підключені до цих чотирьох сторінок, тому їх не змінено. Пов'язаний курс використовує ті самі UUID, а не додатковий авторський банк.

Для `course-td-past-perfect-continuous-forms` inventory має `additional_saved_test_projections`: 15 exact Basics B2 UUID, повні canonical joins та 9 route/mode/locale URLs. [Sanitized projection source](past-perfect-continuous-quality-course-projection.json) фіксує реально спостережену legacy membership, не обіцяє відтворення цієї історичної вибірки поточним course selector. Generator перевіряє slug/page/category за versioned blueprint та кожний UUID офлайн; ці 15 не збільшують авторський count 624. Question records/banks/counts після додавання проєкції семантично незмінні.

Статична практика: 24 завдання, по 6 на сторінку (2 заповнення, 2 вибір твердження, 2 побудова з токенів). Переглянуто всі 24; уточнено 8 українських умов побудови, 16 коректних select/choice збережено. Додано 48 локалізованих EN/PL версій цих завдань. Англійські відповіді й токени не перекладалися.

| Рівень | Mixed, type 0 | Builder, type 4 | Разом змінених UUID |
| --- | ---: | ---: | ---: |
| A1 | 48 | 48 | 96 |
| A2 | 48 | 48 | 96 |
| B1 | 48 | 48 | 96 |
| B2 | 48 | 96, включно з 48 Basics | 144 |
| C1 | 48 | 48 | 96 |
| C2 | 48 | 48 | 96 |

## Що виправлено

- Умови Builder природно локалізовано UK/EN/PL, із явними ролями, часовими точками, запереченням і навчальними обмеженнями порядку слів. Зарезервований provider `compose_prompt` зберігає умову окремо від граматичного English stem, без міграцій; legacy-питання не отримують нового контракту автоматично.
- Підказки задають лексичну основу та операцію для всіх 295 справжніх маркерів пропусків (885 marker-locale записів). Для часових виразів вони пояснюють тривалість/початок/кінцевий момент; для коротких відповідей — полярність, особу й логічний висновок. У 336 Builder-завданнях маркери є позиціями токенів, а не окремими дієслівними пропусками: для цілого речення показано локалізовану лексичну підказку до першої спроби, без відкриття options. Це перевірено в тестових картках, native composer та linked практиці теорії; для останньої додано finite `compose_preanswer_hint`, а не показ готової відповіді.
- Синхронізовано answers, правильні токени, повторювані токени, options/distractors, локалізації й експорти. `variants` не трактуються як accepted answers. Повні/скорочені форми використовують чинний strict matcher, без глобального fuzzy-оцінювання чи ігнорування `not`/`been`.
- Прив'язки всіх 624 питань стали явними UUID-map відповідної підтеми замість широкого текстового/tag fallback. Повторне пересівання не має повернути старі умови чи нецільові блоки.
- Експорт/імпорт дев'яти opt-in банків переносить повний упорядкований `theory_links` разом із primary UUID, типом, seeder, marker-options та locale підказок. Перед імпортом перевіряються schema, наявність усіх блоків, непорожній порядок без дублів і відповідність першого link primary UUID. Invalid payload не пише ні дані, ні export; final export виконується після commit. Legacy payload без нового поля зберігає чинну поведінку.
- Renderer показує локалізовану умову над банком токенів. Для opt-in питання кінцева англійська пунктуація береться з English stem, а не з локалізованої інструкції; природні `Yes,`/`No,` збережені.
- У native composer для нової умови використано нейтральний підпис «Умова / Task / Polecenie»; початковий `source_sentence` підпис збережено для інших наборів.
- Усунуто втрату `@once` matcher bootstrap під час відкинутого pre-render native M26-блоків: потрібний asset підключається до рендеру сторінки. Навчальні basic/detail тексти та point-level структура не змінені.
- Кешований dataset має locale-independent fingerprint канонічного завдання тільки для дев'яти opt-in PPC банків. Застарілі умови/відповіді, видимий `reorder_source_question` та вилучені variants освіжаються без скидання chosen/attempts/history/navigation; поля нового контракту копіюються лише для opt-in. Це не переоцінює історичні відповіді та не видаляє попередні завершення.
- Mixed-сторінки явно підключено до чинного level-aware чергування: на кожному A1–C2 по 7 побудов, 4 справжні пропуски та 3 перестановки. Умови довших C1/C2 не гублять перестановку через старе обмеження сирої кількості слів. Saved-test filters і сторонні теми не переписано.
- Виправлено реальну відсутність полів у `/manual` та `/step/manual` для локалізованої Builder-умови без `{aN}`. Finite preview створює поля з фактичних prepared-маркерів, включно з об'єднаними скороченнями, та видиму підказку. Existing listeners/checker і legacy-renderSentence залишено незмінними.
- Для порожніх finite manual fields збережено початковий affordance `min-width:8rem` і `max-width:100%`: чинний autoResize більше не стискає їх у вузькі нерозбірливі pill-елементи. Для введених фраз у finite card mode autoResize враховує фактичні padding/border та місце курсора, щоб початок відповіді не обрізався; legacy надбавку `+8px` збережено. Це не глобальна зміна стилю legacy inputs.
- Точний не-початковий `When` у відповідях PPC відображається як `when`, без переписування спільних рядків options у БД; початковий `When`, власні назви та інші теми збережено.

## Приклади питань

### [Forms — статична практика](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms)

Видима умова: «До 2020 року Анна знала Лева вже п'ять років. Почни речення з By 2020.» Правильна відповідь: `By 2020, Anna had known Lev for five years.` Збережено stative `had known`; `Lev had known Anna` після уточнення ролей не приймається.

### [Time Expressions — Mixed](http://gramlyze.loc/test/past-perfect-continuous/time-expressions)

`pastpc-time-expressions-v3-a1-01`: `They had been waiting ____ two hours when the doors opened.` Правильна відповідь `for`; варіанти `for / since / until / at / by`. Тривалість `two hours` і локалізована підказка усувають попередню неоднозначність `for two hours / since morning / all morning`.

### [Forms — C2](http://gramlyze.loc/test/past-perfect-continuous/forms)

Колишній косметичний дубль A1 `I ____ for two hours before the proceedings opened` замінено на `Before the correction, successive briefings ____ an estimate as established fact, not merely citing it once.` Завдання розрізняє повторюваний попередній процес і одноразову згадку, а не лише складнішу лексику.

## Дублі та складність

Детальні рішення: [duplicate review](past-perfect-continuous-quality-duplicate-review.json).

| Критерій, 624 авторські питання | До: групи / зайві повтори | Після |
| --- | --- | --- |
| Точні умови | 60 / 192 | 0 / 0 |
| Нормалізовані умови | 61 / 193 | 0 / 0 |
| Точні відновлені відповіді | 181 / 323 | 0 / 0 |
| Нормалізовані відновлені відповіді | 202 / 362 | 0 / 0 |

Переглянуто 23 післяредакційні near-duplicate пари; для кожної є явне рішення та обґрунтування. 20 стосуються коротких відповідей Questions, 3 — міжбанкових контрастів твердження/питання/заперечення. Однакова допоміжна форма сама по собі не є дублем. Невирішених кандидатів за задекларованими критеріями немає.

Рубрика: A1/A2 — коротка керована одноетапна операція; B1/B2 — самостійне відновлення з часовим зв'язком, причиною та правдоподібними дистракторами; C1/C2 — точний дискурсивний висновок, заперечення scope або коротка відповідь з фактичної умови. Це робоча рубрика складності вправ на вже задану тему, а не твердження, що Past Perfect Continuous належить до навчального curriculum A1. Довжина чи рідкісне слово не використані як достатній критерій CEFR.

Автоматичний аудит виконує 194 376 попарних порівнянь на фазу з нормалізацією та template/3-gram кандидатами; він доповнює редакційний перегляд, але не доводить відсутності будь-якого можливого семантичного перефразування.

## Локальне застосування та приймання

Фізичну ціль підтверджено fresh loopback proof: Apache application/document identity відповідає робочому `.loc`, MySQL connection та фізична локальна БД збігаються. `APP_ENV=production` не підмінявся; локальний SiteMode — development. Proof не повертав паролів, APP_KEY, cookie чи token. Ідентифікатори фізичного runtime та connection evidence залишено приватно.

Перед записом сформовано fresh preview з точними old/new умовами, answers/options, локалізаціями та UUID-прив'язками. Перевірено 1119 відмінностей: 624 питання, 12 practice rows, 483 змінені набори links. Exclusive backup (6 088 448 bytes) залишено приватно в `storage/app/ppc-quality-local`.

Transactional apply: 624 існуючі питання та 12 practice rows (4 сторінки × UK/EN/PL), точні theory links. UUID/ID, кількість питань, користувацькі записи, saved-test membership та сторонні дані перевірено всередині транзакції. Після commit експортовано тільки 624 цільові питання в task worktree. ROOT-exports зі сторонніми видаленнями не відновлювалися.

Postconditions: `verified`, mismatches `[]`. Повторний apply: `repeated-no-op`, без повторних seed/export/write. Тимчасовий nonce route видалено; файл routes повернуто byte-exact до початкового стану, повторний GET повернув 404.

Після перевірки round-trip контракту окремо освіжено 624 WT exports під MySQL READ ONLY transaction та SQL whitelist (SELECT/SHOW). Перед цим зроблено exclusive копію всіх 624 попередніх файлів. Перевірено 1872 `compose_prompt` locale rows, 885 marker hints і 2037 ordered theory links; усі canonical IDs та digests п'яти захищених progress/state/membership таблиць незмінні. Незалежний post-refresh audit підтвердив, що попередні question/options/answers/tags/variants/explanations та IDs/dates збережені: додано тільки `theory_links`; hint array отримав PK-порядок, не нові/змінені рядки. ROOT-exports не зачіпалися.

Read-only перевірка під час браузерного приймання підтвердила незмінні hashes і counts користувачів, answer attempts, lesson progress та content sync state. Їхні записи й приватні метрики не публікуються. Runtime sources переносилися finite source-only командою з exclusive backup та перевіркою, що проміжних сторонніх правок немає; фінальна синхронізація охопила 57 файлів, backup залишено приватно.

Точні scoped-команди з task worktree (змінні посилаються на приватні fresh evidence, nonce у Git не зберігається):

```powershell
php -d opcache.enable_cli=0 tools/diagnostics/sync-ppc-quality-sources.php check
php -d opcache.enable_cli=0 tools/diagnostics/sync-ppc-quality-sources.php apply
php -d opcache.enable_cli=0 tools/diagnostics/ppc-quality-working-local.php preview $proof
php -d opcache.enable_cli=0 tools/diagnostics/ppc-quality-working-local.php apply $proof $preview
php -d opcache.enable_cli=0 tools/diagnostics/ppc-quality-working-local.php verify $proof
php -d opcache.enable_cli=0 tools/diagnostics/ppc-quality-working-local.php apply $proof
php -d opcache.enable_cli=0 tools/diagnostics/refresh-ppc-quality-exports.php --source-check
php -d opcache.enable_cli=0 tools/diagnostics/refresh-ppc-quality-exports.php --refresh
```

`$preview` — basename exact reviewed fresh preview, `$proof` — basename перевіреного fresh proof. Apply-команда сама викликає лише 9 цільових JSON-bank seeders і 4 TheoryLinks seeders; observers вимкнено на час транзакції, exports — тільки після commit. Всі таблиці мутацій перевірено на InnoDB. Це не загальний `db:seed`.

### Автоматичні перевірки

- Повний цільовий isolated PHPUnit: 149 tests / 54 830 assertions, exit 0; guarded fingerprint 46 648 protected files, змін 0.
- Після останніх finite presentation змін: 72 PHP tests / 1051 assertions (payload 56, balancing 2, pre-answer hint 7, token presentation 7), exit 0.
- Shared isolated PHPUnit: 109 tests / 1007 assertions, exit 0; protected fingerprint 46 648 files, змін 0. Перевірено export, V3 JSON seeder, compose modes, mixed render, theory-page test seeders, saved state, synonyms, reorder factory та accepted variants.
- Фінальний ordered import/export + localized round-trip + legacy export: 31 PHP tests / 199 assertions, exit 0. Guarded saved-state/synonyms: 21 tests / 52 assertions, exit 0; protected 46 648 files, змін 0. Після додавання `reorder_source_question` повторено ці Unit-тести в private runtime: 21 tests / 54 assertions, exit 0, без DB/export writes.
- Safety tests SELECT-only WT export tool: 11 tests / 13 assertions, exit 0; source-only `--source-check` не bootstrap-ить Laravel і нічого не пише.
- Після додавання linked pre-answer hints: isolated LocalizedCompose Feature 8 tests / 109 assertions, exit 0; protected 46 648 files, змін 0. Невідомий/non-finite owner не отримує нового payload або порожньої markup-обгортки.
- Node: усі 9 цільових файлів, 123/123 tests passed. Перевіряються фактичні renderer/matcher functions, статична й linked практика, progress merge, manual fields, pre-answer hints і локалізований native підпис; actual-autoResize regression окремо підтверджує finite padding/border allowance та незмінний legacy `+8px`. Перекриття між PHP-запусками не підсумовується як кількість унікальних тестів.
- Одна відома PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` у чинному `config/database.php`; конфігурацію цього завдання не переписано.
- Source generators, inventory та finite duplicate review перевірено після останніх правок. Попередні діагностичні невдалі спроби не рахуються успішними.

### Реальне браузерне приймання

Теорія, повторне приймання на final linked-hint code: 12/12 canonical сторінок UK/EN/PL, 72/72 static task executions, 24 clicked input-bank targets, 48 accepted static alternatives, 33 semantic rejection cases. Linked практика: 60 цілей з фактичних 12 pools по 5 UUID, усі 60 lexical hints видимі до відповіді; removal/reuse і omitted-been rejection на всіх 12; omitted-not rejection та clickable contraction acceptance — на 3 локалізованих Negatives. Перевірено 48 UK M26 disclosures, keyboard/print/basic fidelity і єдиний matcher bootstrap на 12 сторінках. Page/console/HTTP/violations — 0. Усі 24 final theory screenshots переглянуто візуально. UI-feedback перевіряється після фактичного Alpine DOM flush, а не через race з одразу прочитаним state.

Справжнє перемикання мови через header dropdown: UK → EN → PL на Forms theory та Forms Builder `/manual`, 6 станів / 4 clicked language links. Усі GET — HTTP 200, фактичні localized conditions/pre-answer hints/manual fields підтверджено, page/console errors — 0; усі 6 screenshots переглянуто візуально. Для цього read-only flow заблоковано 3 routine state POST та 6 Google-font requests; жодний POST не надсилався. Перевірку локалей через різні URLs не видано за ці кліки.

Окреме final manual-layout приймання після autoResize fix: Forms `/manual` та `/step/manual` × UK/EN/PL × desktop/mobile — 12/12 HTTP 200. Усі 84 порожні поля мають ширину 128px і вкладаються у viewport. Перша введена prepared-фраза `I had been` повністю видима в усіх 12 станах: після завершення CSS width transition `scrollWidth=clientWidth`, `scrollLeft=0`, виміряний текст вкладається у content width; clipping — 0. Виконано 12 incomplete rejection та 12 full-target acceptance checks. Усі 24 empty/typed screenshots переглянуто візуально; page/console/HTTP errors та надіслані non-GET — 0. У цьому read-only flow заблоковано 24 routine POST і 12 font GET. Ранні timeout/geometry-race спроби не включено у ці успішні лічильники.

Mixed virtual routes: 12/12 сторінок (4 підтеми × 3 локалі), по 84 питання; для кожного рівня A1–C2 підтверджено 7 builder / 4 gap / 3 reorder. 216/216 реальних завершень: по одному завданню кожного типу на кожному рівні й мові. На всіх 12 сторінках reload відновив 18/18 правильних відповідей, restart очистив поточну спробу. Page errors — 0, learning controls mobile overflow — 0; окремо збережено 6 попереджень про нецільові shell background shapes. Є 24 desktop/mobile screenshots; репрезентативні перевірено візуально.

Прямі saved-test routes: 108/108 сценаріїв (96 supported HTTP 200 та 12 очікуваних HTTP 404 для `/step/compose` чотирьох pure type-0 банків, який навігація не пропонує). Data coverage — всі 624 UUID, 2655 фактично відрендерених локалізованих gap-marker hints, 4032 native lexical-hint payload checks, 6624 source/theory rows. 37/37 реальних UI actions у choose/manual/step-manual/native token та manual compose; violations — 0, server progress POST — 0.

Додатковий курс [course-td-past-perfect-continuous-forms](http://gramlyze.loc/test/course-td-past-perfect-continuous-forms): 9/9 supported choose/manual/step-manual сторінок UK/EN/PL, 3/3 реальні дії. Його фактичний pool — 15 B2 UUID, однаковий у всіх 9 сценаріях і повністю всередині вже врахованих 624; перевірено 135 locale/source/hint/theory rows, violations та progress POST — 0.

Після останньої зміни native підпису виконано 15/15 fresh compose сценаріїв (5 Builder банків × 3 мови): точні DOM-підписи «Умова / Task / Polecenie», 1008 source/hint row checks, violations і progress POST — 0. Скріншотний uppercase не підміняв перевірку фактичного DOM-тексту.

Regression smoke: [Present Perfect Forms](http://gramlyze.loc/test/present-perfect/forms), [Future Perfect Forms](http://gramlyze.loc/test/future-perfect/forms) та [Present Perfect Continuous Forms theory](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms) — HTTP 200, тестові datasets по 84 питання, практика теорії присутня, page errors — 0. M26 renderer/basic fidelity перевірено окремо на всіх чотирьох цільових сторінках.

Це не твердження про інтерактивне проходження всіх 624 речень: повний банк перевіряється джерелами, data contracts і semantic review; живі дії та data coverage мають окремі лічильники.

### Межі та виявлені нецільові обмеження

- Фактична category [Past Perfect Continuous](http://gramlyze.loc/theory/past-perfect-continuous) та її EN/PL версії повертають HTTP 200, посилання ведуть на 4 canonical leaf сторінки вище. Code-grounded sweep 42 URL UK/EN/PL: category 3 та leaf 12 — HTTP 200; nested category `/theory/tenses/past-perfect-continuous`, old full leaf без `/tenses/` і shorthand `/theory/past-perfect-continuous/forms` тощо — 27 HTTP 404, без 3xx/Location. Додатково перевірені раніше 12 однорівневих page-slug адрес повертають 404. Три початкові load timeouts у sweep зникли після bounded sequential retry; це не було підтвердженням недоступності сторінки. Чинних shorthand aliases не знайдено; маршрути не змінено.
- Тематичний `/step/compose` зберігає чинний course-progress gate. Чистого гостя перевірено окремо; composer після prerequisites перевіряється browser-only guest fixture, без серверних user/progress записів. Через неповний `previous_lesson_slug` у чинному маніфесті автоматичне послідовне розблокування не заявляється як перевірене виправлення цього завдання.
- У shell random background shapes при desktop→mobile resize виявлено document overflow. Навчальні controls вимірюються окремо; спільний дизайн/декорації тут не перероблялися.
- Перевірені повні форми, `hadn't` та доречні positive `'d` у чинному matcher. Рідкісне `I'd not been …` залишається обмеженням наявного контекстного matcher; глобальне оцінювання не послаблювали. Не заявляється підтримка всіх можливих розмовних скорочень.
- Збережені історичні завершення не переоцінюються за переписаною умовою; відповідь/attempts не видалено. Нова чиста спроба використовує новий canonical dataset.
- Production не перевірявся й не оновлювався.

## Відтворювані перевірки джерел

З task worktree:

```powershell
php -d opcache.enable_cli=0 scripts/upgrade_ppc_quality_builder.php --check
php -d opcache.enable_cli=0 scripts/upgrade_ppc_quality_mixed.php --check
php -d opcache.enable_cli=0 scripts/upgrade_ppc_quality_links.php --check
php -d opcache.enable_cli=0 tools/diagnostics/audit-ppc-quality-content.php verify-review
php -d opcache.enable_cli=0 tools/diagnostics/generate-ppc-quality-inventory.php --check
git diff --check
```

Генератори працюють preview-first; `--write` — явна механічна регенерація лише відповідних versioned джерел. Немає HTTP/startup/scheduler apply-hook. Фінальна SELECT-only перевірка підтвердила незмінні захищені user/progress/state digests після браузерних перевірок; 57 явно перелічених файлів реального `.loc` збігаються з task worktree.

Для commit явно обрано 736 файлів цього завдання, включно з рівно 624 UUID exports. Незалежно перевірено відповідність exports inventory, ordered links і локалізаціям, ancestry accepted M35 та staged diff. Сторонні незавершені зміни ROOT не включено. Runtime evidence, резервні копії, `.env`, vendor/build, cookie/token та приватні діагностичні файли не входять у commit.
