# M50 Обов’язок, порада та необхідність

## Результат і межі

Редакцію 1.0.0 застосовано до трьох чинних UK theory pages на `gramlyze.loc`. Scoped apply, повторні no-op, ізольовані тести, серверні перевірки конструкторів, повторна UI-матриця після H1 fix і read-only postconditions завершені. Локальне приймання завершено з явно зазначеними успадкованими візуальними та capability-обмеженнями. Commit/push підтверджуються окремим delivery handoff.

| Урок | Local URL | Page ID | Exact identity |
| --- | --- | ---: | --- |
| Must / Have to | [Must / Have to](http://gramlyze.loc/theory/modal-verbs/must-have-to) | 75 | `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMustHaveToTheorySeeder` |
| Should / Ought to | [Should / Ought to](http://gramlyze.loc/theory/modal-verbs/should-ought-to) | 79 | `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsShouldOughtToTheorySeeder` |
| Need / Needn't / Don't have to | [Need / Needn't / Don't have to](http://gramlyze.loc/theory/modal-verbs/need-neednt-dont-have-to) | 76 | `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsNeedNeedntDontHaveToTheorySeeder` |

BEFORE підтвердив category ID 15, locale UK, display A2-B1 і technical A2. Slug, category, canonical, Page owners та IDs/UUID continuing blocks збережено. Raw DB titles не змінювалися; виявлений rendered H1 drift виправлено вузьким guarded code change без зміни frozen навчальних джерел. H1, Breadcrumb і LearningResource name/headline знову відповідають оригінальному BEFORE; нові descriptions — незалежній очікуваній проєкції, отриманій до apply. Served ROOT — `D:/DEV/htdocs/gramlyze.loc`; fresh local proof підтвердив дозволену фізичну ціль.

Завдання не охоплює main, production, deploy, сусідні уроки, EN/PL, зміну курсового контенту, dependencies, серверну конфігурацію або особистий прогрес.

## Навчальні зміни

- **Must / have to:** контекстний перетин вимог, заборона проти необов’язковості, питання й короткі відповіді, had to / didn't have to та will / won't have to. Must може стосуватися майбутнього; помилка — поєднання will must.
- **Should / ought to:** порада й очікуваний результат, потрібне to, заперечення та питання, регістр без автоматичного осуду. Рівнозначні поради приймаються обидва. Should have не оголошено завжди невиконаною дією.
- **Need:** звичайне need to / need + іменник і модальне needn't, заперечення й питання, необов’язковість проти заборони. Didn't need to саме по собі не визначає факту виконання; переклад needn't have явно зберігає виконану, але непотрібну дію.

Редакційна карта охоплює 54 старі sentence fields: 46 EN-exact retained/moved, 8 замінено або вилучено з конкретними причинами. Окремо описано 24 English-bearing поля forms. Основний контент та всі 18 власних завдань пройшли main review й незалежний semantic/token review до freeze.

## Канонічні компоненти та basic/detail

Збережено шість continuing slots кожного уроку: hero, forms-grid, usage-panels, mistakes-grid, summary-list, navigation-chips. Додано один native practice-set. Пояснення використовують іменовані семантичні пункти, а не безіменні картки окремих абзаців.

| Урок | Visible basic | Meaningful details |
| --- | --- | --- |
| Must / Have to | Повні форми, значення, час і необхідні застереження | 0 |
| Should / Ought to | Форми, порада, очікування та регістр | 1 у власному usage-пункті; 2 пояснені підпанелі |
| Need / Needn't / Don't have to | Форми, необов’язковість, заперечення й коротке застереження про факт минулої дії | 1 у власному usage-пункті; 1 контрастна панель |

Карта: [m50-obligation-advice-components.v1.json](../content/m50-obligation-advice-components.v1.json). Теорія відображається через чинний universal content path; практика — через незмінний practice-set і наявний answer engine. Blade, CSS, frontend JS, шрифти, header/sidebar/background не редагувалися. Read-only аудит після H1-виправлення підтвердив 326 visual entries без змін.

У UI v1 Should і Need мали detail лише у власному пункті з індексом 1; Must — жодного detail. Перевірено нейтральні content panels без кольорової рамки, приклади із синьою лівою лінією та переклади без курсиву. Це не означає відсутності всіх успадкованих візуальних обмежень — вони наведені нижче.

## Власна практика

| Урок | Selects | Choices | Inputs | Власні завдання |
| --- | ---: | ---: | ---: | ---: |
| Must / Have to | 2 | 2 | 2 | 6 |
| Should / Ought to | 2 | 2 | 2 | 6 |
| Need / Needn't / Don't have to | 2 | 2 | 2 | 6 |
| Разом | 6 | 6 | 6 | 18 |

Кожний input — одне завдання з manual і token-способом, а не два завдання для статистики. Повна UK умова міститься в підтриманому `after`, перемішаний slash-bank — у `before`. Є comma та question-token cases, перевірені контекстом aliases, компактні feedback maps і відомі wrong probes. Варіанти відповіді не містять готового розбору.

У meaning tasks Need №3–4 точний UK переклад English stimulus доступний після Check: його попередній показ розкрив би перевірюваний зміст. Повні українські умови й контекст видимі одразу. Must question stimulus і Should expectation stimulus мають видимі переклади, що не замінюють вибір відповіді.

Відоме обмеження чинного engine: feedback lookup використовує trim + lowercase. Для оголошених aliases, потрібних апострофів, пунктуаційних форм і known wrong probes задано перекладений feedback. Довільний невідомий manual wrong використовує чинний generic fallback; новий grader не створювався.

У фактичному UI v1 всі 18 власних завдань пройдено на desktop за послідовністю empty → wrong → repeated wrong → edit correct → repeated check → reset. Усі 6 inputs перевірено вручну й токенами на desktop та mobile: **24 різні task × mode × size cases**, не 24 нові завдання. Виконано delete / return / rebuild, перевірено кому й знак питання. Score inputs проходив 0 → 2; повторний Check не додавав балів удруге. Це функціональне browser evidence збережено після H1 fix: practice bodies та JS не змінилися. UI v2 окремо повторив усю матрицю відображення й H1; повторні states не додаються до цих 24 cases.

## Окремий конструктор із п’ятьма питаннями

18 власних завдань і 15 повторно використаних питань банків рахуються окремо. Це не 33 нові авторські завдання.

| Owner | Погоджений банк або скінченний добір | Придатний пул type 4 / A2 | Обрані local IDs | Нові pivots |
| --- | --- | ---: | --- | ---: |
| Must / Have to | `Database\Seeders\V3\Polyglot\PolyglotMustHaveToLessonSeeder` | 24 | 121–125 | 0 |
| Should / Ought to | `Database\Seeders\V3\Polyglot\PolyglotShouldOughtToLessonSeeder` | 24 | 97–101 | 0 |
| Need / Needn't / Don't have to | `PolyglotGerundVsInfinitiveLessonSeeder` + `PolyglotMustHaveToLessonSeeder`, лише погоджені need-to / no-obligation items | 5 | 158, 164, 166, 133, 135 | 5 |

Початковий AllLevels-добір не прийнято: зокрема неправильне «She have to…» та generic UK labels не відповідали якості потрібного конструктора. Старі AllLevels questions і answers не виправлялися й не видалялися. П’ятірку Need погоджено за фактичними українськими умовами, тематичним змістом, відповідями та type 4 / A2, а не за випадковим результатом matcher чи як довільний fallback ширшого корпусу.

Нових questions — **0**. Записано лише п’ять погоджених зв’язків існуючих питань із новим Need practice block. Must / Should використовують наявні page-wide theory links. Bank/owner/locale/level filters не послаблено.

Незалежний контракт: [m50-obligation-advice.v1.json](../content/theory-sentence-builders/m50-obligation-advice.v1.json). Один native widget на сторінку, п’ять унікальних питань, type 4, exact A2:

- scoped read-only gate — **3/3**, HTTP 200;
- повний dynamic gate — **73/73**, failures 0, writes 0; попередні 70 targets збережено, три нові owners включено до discovery;
- у UI v1 фактично складено правильні відповіді для всіх **15 повторно використаних widget questions**, по п’ять на кожній сторінці; наявність і взаємодія не виведені лише з CLI.

Після перевірки скинуто лише нові власні M50 widget states до свіжого 1/5. Старий прогрес не очищався. Read-only postconditions підтвердили повну незмінність 13 question/test/progress tables. UI v2 показав один widget зі свіжим 1/5 у кожному з 18 states.

## Versioned джерела

Редакція 1.0.0:

- [Master](../content/m50-obligation-advice.v1.0.0.json), SHA-256 `5f169d760aff9ecadd283186311cdddd1f88e997c705bb65c32cd50751d73e01`.
- [Читабельна українська редакція](../content/m50-obligation-advice.v1.0.0.uk.md).
- [Editorial BEFORE/AFTER](../content/m50-obligation-advice-editorial.v1.0.0.md).
- [Component/basic-detail map](../content/m50-obligation-advice-components.v1.json).
- Незалежний BEFORE: `database/content-patches/m50-obligation-advice-before.v1.json`.
- Exact content patch: `database/content-patches/m50-obligation-advice.v1.json`.
- Exact Need link patch: `database/content-patches/m50-need-practice-links.v1.json`.
- [Checksum manifest](../content/m50-obligation-advice-checksums.v1.json): 11 frozen files, SHA-256 `b30007fb58cea1cc7ba7980f0f6e70bc7ab8c3bd1f756f19a0875809ef0d654b`.

Фінальний source-sync v3 підтвердив 18 reviewed paths, включно з вузькими H1 та course subtitle guard fixes; SHA-256 `f89f025b6e587efedd5ef20ae0166d2689432b3890e4b3a1a8c58feb8ca00cff`. Source-sync v1 і v2 є історичними; v2 мав SHA-256 `33fc4eca6967216a12906a46fb26134641de0852c485957be653041e529f01c2`. Усі 11 release pins, 326 visual entries, 2203 frozen files та 6 bank source definitions незмінні. Старі M46–M49 / Practice Completion releases та maps не переписувалися.

### Першоджерела й межі доступу

Cambridge direct URLs повертали 403; перевірено індексований редакційний текст, що не названо повним прямим читанням. British Council та Google Search Central відкрито прямо. Точні URLs, питання перевірки та access mode записано в master/editorial. Коментарі учнів не використовувалися, видавничі вправи не копіювалися.

Зміст відповідає на питання про значення, форми, контрасти, приклади, переклади та практику. Немає вигаданого рецензента, авторитетної сертифікації, search volumes, CTR або claims про покращення позицій Google.

## Local apply і повторні no-op

Перед записом отримано independent BEFORE, очікувану metadata-проєкцію через чинний service, fresh physical-local proof і reviewed exact preview. Apply використовував exclusive private backup та транзакцію. Незалежний `metadata-expected-v1.json`, сформований до apply: SHA-256 `7b98bfb698e1e95f2b8d802f7d9781a6d1fc49c6b8c5707ae228c0e6906ffa3e`.

| Вид зміни | Фактичний перший apply |
| --- | ---: |
| UK body updates, включно з subtitle rows | 21 |
| Page.text updates | 3 |
| Updates разом | 24 |
| Нові practice text blocks | 3 |
| Deleted rows | 0 |
| Нові Need question–practice pivots | 5 |
| Нові або переписані questions / answers | 0 |

Фактичні practice body sizes: **14 552 / 11 410 / 13 028 UTF-8 bytes**; чинна SQL TEXT capacity — **65 535 bytes**. DDL і migrations не виконувалися; повторне роздування feedback maps не застосовувалося.

Повторне застосування початкового плану: **0/0/0**, links 0. AFTER preview визначив `state=after`; повторне застосування AFTER-плану також **0/0/0**, links 0. Після H1 fix source-after preview v2 і no-op знову підтвердили **0/0/0**, links 0. Після фінального course subtitle guard fix preview v3 визначив `state=after`, а apply поточного коду підтвердив **0/0/0**, links 0. No-op не створювали додаткових backups. На етапі H1-перевірки одна спроба зупинилася до записів через 20-секундний Windows physical-inspector timeout; перед третім proof lifecycle сталася ще одна така відмова до створення route і записів. Обидва точні повтори пройшли без послаблення guard.

Усі три тимчасові proof-route lifecycles закрито: route file відновлено byte-exact, усі три nonce GET повернули 404. Public report не містить значень nonce, credentials чи приватних runtime-host даних.

Перший аудит після UI справді виявив H1 drift: евристика PageController перетворювала plain new subtitle на title у пам’яті моделі, хоча raw DB titles залишилися правильними. Це не tool timeout і не fixture-only failure. Виправлення активується лише для exact M50 UK theory owner, Page/category IDs, raw fields і вже завантаженого exact subtitle. Глобальний extractor не змінено. Дві нові регресії actual PageController покривають цю межу; frozen навчальний payload не переписано.

Фінальний static review додатково виявив вузьку прогалину course subtitle fallback guard. Перед застосуванням fallback тепер обов’язково звіряються exact subtitle identity, metadata та current Page; невідповідність закриває цей шлях, а не дозволяє підмінити чужі дані. Це невізуальний scope fix: навчальний payload, 11 frozen release files та відображення не змінювалися. Чинний course test посилено на 180 assertions без додавання нового тесту до лічильника.

Повторний read-only `after-v2.json` пройшов: SHA-256 `27e402cb6a26ca1af5e70ebe809e084acf50da8a5673d799f761271d497a0764`. Підтверджено 15 GET зі статусом 200 — 3 theory, 3 courses та 9 standalone test pages; 46 protected SQL tables; повну незмінність 13 question/test/progress tables; exact п’ять нових Need pivots. Повні перевірені DOM bytes трьох курсів дорівнюють BEFORE. На дев’яти standalone consumers повні per-question payloads дорівнюють BEFORE при зіставленні за question ID; лише чинний верхньорівневий порядок питань не використовується як відмінність. Усі 6 bank source definitions, 326 visual entries та 2203 frozen files незмінні.

Read-only аудит після завершеної UI v2 — `after-v3.json` — також PASS, SHA-256 `37f20c706dad9479f8e290edc432e14bb5890f551cb05c14854e5dc9d69cb170`; це історичне підтвердження перед фінальним course guard fix.

Фінальний postconditions receipt — `after-v4.json`, PASS, SHA-256 `50c0c32fdf78a2a14889578661d8ec933b3f9a57b075a8976a8ddfda37c4aabd`. Знову підтверджено 15/15 HTTP 200, 46 protected tables, незмінність 326 visual entries, 2203 frozen files та 6 bank source definitions. На кожній сторінці — 8 UK / 22 усіх rows; practice IDs 12681 / 12682 / 12683 та добір питань збережено. Повторні audits не додаються до кількості унікальних сторінок чи UI states.

## Тести та поточний стан приймання

| Перевірка | Фактичний результат |
| --- | --- |
| Фінальний mixed run після H1 і course guard fixes | 59 tests / 5139 assertions, PASS; включає всі 20 поточних M50 tests та M45 / SiteMode / локальні SEO production-profile регресії |
| Поточний M50 profile всередині цього run | 20 tests: Package 8, Patch 9, Guard 3; не додавати окремо до 59 |
| Потрібні ізольовані регресії | 80 tests / 31 800 assertions, PASS |
| Offline JS / native answer factory | 19 tests, PASS |
| Protected filesystem windows | 46 661 files, changes 0 в усіх завершених runs |
| Scoped sentence-builder gate | 3/3, HTTP 200 |
| Повний dynamic gate | 73/73, failures 0, writes 0 |
| Основна UI матриця 3 × desktop 1440 / mobile 390 × light/dark | 12 фінальних UI v2 states після H1 fix; H1 звірено в кожному |
| Додаткові 320 px | 6 фінальних UI v2 states: 3 сторінки × 2 themes; разом 18, не 18 + попередні 18 |
| PPC reference | Перевірено окремо; не додається до 18 M50 states |
| Усі 18 власних завдань | Desktop retry / correct / repeat / reset завершено |
| 6 inputs у двох способах на двох розмірах | 24 різні mode/size cases завершено |
| 3 native linked widgets | Усі 15 питань фактично складено правильно; нові widget states повернуто до 1/5 |
| JS-off / print | not_checked: відповідна capability не була доступна; функціональну відсутність не заявлено |
| Фінальний read-only preservation audit after-v4 | PASS: 15 HTTP 200; title/metadata, courses, question payloads і protected data звірено |

Два попередні profile attempts мали fixture-only невідповідності: очікування DOM wrapper / hero-navigation caller і збереження `lang` після чинної нормалізації. Їх виправлено в тестових fixtures, не зміною погодженого навчального payload або runtime заради PASS. Ці failed attempts не додаються до успішних унікальних tests.

Зафіксовано одну чинну PDO constant deprecation у наявному config; вона не є новим failure M50, і config через неї не редагувався. Environment: testing, SQLite `:memory:`, array cache/session, `working_env_loaded=false`, CLI opcache false. Робоча MySQL та робоча `.env` ізольованими тестами не використовувалися; production profiles перевірено локально, без production HTTP/DB.

Фінальні receipts у приватному WT `storage/app/seo-m2-local`:

- Поточний mixed run: `m50-course-guard-final-027c2f2e2d8e4f53a569806daad32540-result.json`, 59 / 5139, SHA-256 `96c6cf82a6424e10ee0d2bc6a7e10ac19b8416e9b9201f86a188e0a8a9d1845c`; child exit 0, runner exit 0, protected files 46 661, changes 0.
- Історичний mixed run перед course guard fix: `m50-title-final-465b84f49e594418bba4030de0041826-result.json`, 59 / 4959, SHA-256 `8afb701e80c2b909e6d8230469d1b97f82ab49770650729a9115a66d009eceaf`; замінений фінальним 59 / 5139 і не додається до нього.
- `m50-regressions-93c64eaee6a34bfc96c5bb29602d91f4-result.json`, 80 / 31 800, SHA-256 `7e06cb0b6d32d725c57547f6feec40aa08744961dfe3caba267d7e4fded76942`.
- Історичний pre-fix profile `m50-profile-verified-ef97d3a45ed7407ea1503dc416f7509d-result.json`, 18 / 1405, SHA-256 `4227cc5056a141024afe88b4980369b585c73ad2f96e9aad4766f2cd9f0441a3`; не додається до актуального M50 profile.

Кількості наведено за завершеними runs і вкладеним scope; їх не підсумовано як нібито єдину дедупліковану suite. Offline JS повторено на фінальному коді: **19 унікальних tests**, не 38 за два запуски. Node variants перевіряють чинну механіку, не всі можливі граматичні aliases чи всі старі банки.

### Внутрішні посилання та transient failures

У початковій read-only перевірці дев’яти destinations вісім GET повернули 200; Must мав diagnostic 000 через timeout. Пізніше scoped gate і фактичне відкриття Must у браузері повернули 200. Початковий timeout збережено як transient failure, а не приховано й не видано за завершений PASS усіх дев’яти з першої спроби.

Під час UI були Page.navigate / focus CDP timeouts; також відновлено браузерний контекст після відкриття користувачем чату. Керування відновлено переприв’язкою того самого браузера й tab; Windows або інший нижчорівневий обхід browser safety не застосовувався. Це окремо від справжнього H1 дефекту, який виправлено й повторно перевірено.

### Фактична візуальна перевірка й успадковані обмеження

У кожному з 18 M50 states переглянуто всі шість H2 секцій, зокрема тематичні тести й footer. Після H1 fix незалежний UI v2 повторив усі 18 states і підтвердив кожний H1, завантажені шрифти, відсутність broken images та один widget зі свіжим 1/5. Learning content overflow — **0 в усіх 18 фінальних states**. Попередній UI v1 збережено для історії знайденого дефекту, не додано до фінальних лічильників. Наприкінці відновлено світлу тему й скинуто тестовий viewport; browser zoom не змінювався.

Загальний document overflow не дорівнює нулю: на mobile є успадковані shell / decorative / 100vw-scrollbar виходи приблизно на 15–26 px поза навчальним контентом. На окремому PPC reference 390 px clientWidth був 375, scrollWidth 395, декоративний right edge — 395. Global overflow:hidden не додавали; це не підстава заявляти відсутність overall overflow.

У dark theme CEFR badge та form label «Приклад» мають низький контраст у чинному canonical CSS. Це успадковане обмеження, а не виправлена в M50 риса. CSS не змінювався, бездоганний візуальний результат не заявляється.

JS-off і print не перевірено через недоступність відповідної browser capability. Серіалізовані відповіді в даних сторінки не видаються за доступний користувачеві no-JS ключ.

## Доставка

Робоча гілка — `codex/seo-m50-obligation-advice-standard-view`; база `4d85f890b8fe4fa0887d2f989581c18eb8cf4534`.

Перевірені локальні implementation та acceptance завершено; звіт фінальний. Scoped staged diff / whitespace / hashes review, normal commit і push та перевірка remote SHA == HEAD — окремий крок доставки головного агента. Фактичний full SHA буде надано в delivery handoff; самопосилальний hash у звіт не додається, committed/pushed до фактичного виконання не заявляється.

Main і production не змінювалися; deploy не виконувався. Секрети, .env, backups/dumps, приватні proofs/screenshots, caches, generated assets і сторонні dirty changes не є матеріалом commit.
