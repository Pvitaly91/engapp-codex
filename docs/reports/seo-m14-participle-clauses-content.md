# M14 — Participle Clauses B2–C2

Дата: 2026-09-27. **Зміни застосовано до робочого gramlyze.loc.**

## 1. База, межі та результат

- Після fetch локальний і remote M13 вказували на `52bf375d1e7d7391878a728cdc4f9e2773932cbe`, гілка `codex/seo-m13-sentence-structure-content`. Це фактичний parent пакета M14, не старий main.
- Робоча гілка: `codex/seo-m14-participle-clauses-content`.
- Повторно використаний окремий checkout: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`. Історична назва каталогу не означає стару базу. Основний dirty checkout не скидався й не перемикався.
- Прочитані AGENTS.md, актуальний звіт M13 і `seo-m12-theory-content-styling.md`; збережені накопичені M11–M13, renderer, CSS і попередні manifests.
- Змінено лише три UK definitions та додано вузькі M14 manifest/wrappers/тести/цей звіт. Банки Mixed, questions/answers/options, verb_hint, перевірка відповідей, маршрути та SEO-політика не змінювалися.
- На кожній сторінці є окреме пояснення відповідного рівня, авторські приклади з перекладом, одна самоперевірка з 6 завданнями й 6 поясненими ключами. Разом — 18 нових завдань.
- Рівні B2/C1/C2 збережені як редакційні позначки Gramlyze, без заяв про офіційну CEFR-сертифікацію.
- Production не перевірявся й не змінювався. Немає .com/.ub HTTP, SSH, деплою, PR, merge, workflow dispatch, push у main або force push.

## 2. Ідентичності та перевірені адреси

Повні Page.seeder:

1. `Database\Seeders\Page_V3\ClausesAndLinkingWords\ParticipleClausesBasicsTheorySeeder`
2. `Database\Seeders\Page_V3\ClausesAndLinkingWords\ParticipleClausesTheorySeeder`
3. `Database\Seeders\Page_V3\ClausesAndLinkingWords\AdvancedParticipleAndAbsoluteClausesTheorySeeder`

Відповідні джерела: `database/seeders/Page_V3/ClausesAndLinkingWords/<назва-сідера>/definition.json`.

| Рівень / Page ID | Теорія | Основний тест |
| --- | --- | --- |
| B2 / 285 | [Participle Clauses Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics) | [Тест B2](http://gramlyze.loc/test/clauses-and-linking-words/participle-clauses-basics) |
| C1 / 299 | [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses) | [Тест C1](http://gramlyze.loc/test/clauses-and-linking-words/participle-clauses) |
| C2 / 318 | [Advanced Participle And Absolute Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses) | [Тест C2](http://gramlyze.loc/test/clauses-and-linking-words/advanced-participle-and-absolute-clauses) |

Адреси підтверджені чинними theory/test/course resolvers і навігацією. Це не URL, виведені лише з назв PHP-папок. Усі шість GET — HTTP 200; три test href також фактично відкриті натисканням у браузері.

Курс: [копія уроку B2](http://gramlyze.loc/courses/english-grammar-theory/lesson/clauses-and-linking-words/participle-clauses-basics). GET 200; нові 6 завдань/6 ключів присутні в серверному HTML. Для нового гостя чинний gate приховує навчальний блок і показує «Урок заблоковано». Gate не обходився; повне візуальне приймання відкритого уроку курсу не заявляється.

Read-only сусіди:

- [Defining Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/defining-relative-clauses): необхідний опис іменника, відсутність відокремлення комами.
- [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases): головне слово, pre/postmodification; styled-контроль M13.
- [Concessive And Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures): явні змістові зв’язки; суміжний styled-контроль.

Усі ці адреси GET 200, матеріал переглянуто. Їхній основний текст і metadata before/after незмінні. Для Defining Relative Clauses наявні 8 UK-блоків, а не трьохблокова структура M14; це не конфлікт цільового scope.

### Збережена структура

Категорія — `clauses-and-linking-words`, коренева, language=uk, type=theory. У Basics поле `page.category.type` навмисно не додано: `JsonPageSeeder` використовує root `type=theory` за відсутності поля категорії. Фактична БД і ізольований імпорт це підтвердили.

| Урок | subtitle / hero / box ID | Незмінні UUID у тому ж порядку |
| --- | --- | --- |
| B2 | 8742 / 8743 / 8744 | `64c497c4-86c8-5959-b2e0-492d8895b093`; `6c3204e1-ccff-5d0d-bca9-c95790d58d48`; `149a8908-2729-5b07-9ba0-739773a985e4` |
| C1 | 8784 / 8785 / 8786 | `3ffd1434-e53f-53f5-9f41-967651d6d98c`; `76898240-cda1-509d-8439-b2631e6f4989`; `03aaa3bc-9c75-5af1-a6f0-6f73f57398c0` |
| C2 | 8841 / 8845 / 8848 | `f2349652-d5ae-5bc5-9448-d863342e2abd`; `f938b91a-3025-5a97-93f3-17fa16e82b6b`; `fee52aaa-0298-5083-b03f-b8f91c70fff0` |

Порядок 0/1/2, власники, зв’язки, tags/pivots, slug, Page.title, locale, рівні й локалізації збережені. Старі значення всіх трьох definitions зафіксовані у `database/content-patches/m14-participle-clauses-before.json`.

## 3. Що було й що допрацьовано

| Урок | До | Після / навчальна ціль |
| --- | --- | --- |
| B2 | Змішаний вступ, три короткі англійські опори, службовий box | Розпізнавання -ing/V3 без ототожнення з часом; активне/пасивне відношення; порівняльна таблиця повного й скороченого варіантів; опис додатка; пошук dangling modifier без вигадки фактів |
| C1 | Короткі anchors форм без достатнього контексту | Одночасність, причина, супровід і наслідок; узгоджене порівняння активної/пасивної/попередньої дії; лексичне having проти having + V3; not; before/after/while/instead of; модальність і різні учасники; власний абзац |
| C2 | Компактні загальні опори, абсолютна конструкція без системного розбору | Власний підмет абсолютного звороту; п’ять моделей із чотирикроковим розбором; межа з dangling і comma splice; with без універсального правила видалення; пунктуація, регістр і читабельність; власний абзац із повною альтернативою |

Короткі приклади нової точності:

- Замість некваліфікованого «same subject» у B2: `I checked the forms signed yesterday.` Описаний іменник — forms (додаток); підмет усього речення — I. Українське пояснення не плутає це з початковим обставинним зворотом.
- Замість автоматичної класифікації за першим словом у C1: `Having a spare key...` — лексичне «мати»; `Having found the spare key...` — perfect із V3. Окремо показано природне `After checking...` без обов’язкового perfect.
- Замість нечіткого «independent circumstance» у C2: `The courier having left the parcel, the resident opened the door.` Кур’єр — виконавець першої дії, мешканець — головної. Видалення the courier дає інший зміст, а не рівнозначне скорочення.
- Для помилкового `Crossing the square, the clock struck noon.` відновлено задане `While Oksana was crossing the square...`; не вигадано, що Оксана почула годинник.

Немає нової палітри, JS, CDN або копії CSS. Використано чинні subtitle/hero/box, верхньорівневі нумеровані h4, self-check section, ol і native details/summary. У БД немає згенерованих `theory-rich-*` обгорток: вони додаються renderer один раз. Штатну кнопку «Пройти тест» не дубльовано; її початкові href збережені.

## 4. Джерела та межі правил

Перевірка 2026-09-27, приклади й вправи написані для Gramlyze, не скопійовані зі статей.

- [British Council — Participle clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/participle-clauses): основний навчальний текст доступний і прочитаний. Зіставлено значення форм, perfect active/passive і конструкції після сполучних елементів. Правило спільного підмета обмежено звичайними обставинними зворотами, а не перенесено на означальні й абсолютні.
- На тій самій сторінці окремо враховані підписані відповіді **Peter M., The LearnEnglish Team**, 21 і 27 липня 2026. Перша відповідь про різні підмети спрощена; друга визнає конкретні bare/with абсолютні приклади. Запитання Sami та відповіді інших читачів не використано як редакційні правила. Дозвіл with/без with у цих прикладах не перетворено на універсальне правило.
- [Cambridge — Clauses: finite and non-finite](https://dictionary.cambridge.org/grammar/british-grammar/clauses-finite-and-non-finite): прямий доступ повернув **403**. Повну сторінку не вважаю прочитаною. Доступний індексований текст видавця використано лише для обмеженої звірки неособової форми й relative reduction, включно з означенням іменника-додатка. Спрощений виклад про subject звірено з конкретною функцією прикладів, а не узагальнено на всі конструкції.
- [Cambridge — Negation in non-finite clauses](https://dictionary.cambridge.org/grammar/british-grammar/negation-in-non-finite-clauses): додатково доступний індексований фрагмент про not перед неособовою конструкцією; це не повне читання сторінки й не підстава заперечувати власний підмет абсолютних моделей.
- [Purdue OWL — Dangling modifiers](https://owl.purdue.edu/owl/general_writing/mechanics/dangling_modifiers_and_how_to_correct_them.html): сторінка доступна й прочитана; зіставлено логічного виконавця та способи виправлення через названого учасника або повну підрядну частину.
- [Purdue OWL — Commas with nonessential elements](https://owl.purdue.edu/owl/general_writing/punctuation/commas/commas_with_nonessential_elements.html): сторінка доступна й прочитана; відокремлення додаткового опису відрізнено від необхідного означення. Не введено правила «кома перед кожним -ing».
- [Phoenix College — Commas and Absolute Phrases](https://www.pc.maricopa.edu/englishhumanities/esl/owl/rule18.html): сторінка доступна й прочитана; звірено власний підмет, місце absolute phrase, межі ком і відмінність від comma splice.

Терміни phrase/clause у джерелах відрізняються. Урок пояснює функцію й наявність/відсутність особової форми. Наявність власного підмета не подається як доказ самодостатнього речення. Український переклад обирається за змістом, не зводиться завжди до одного типу звороту.

## 5. Редакторська перевірка всіх 18 ключів

Перевірено граматику, учасників, часові відношення, український переклад і відповідність інструкції. Структурні тести не підміняють цей перегляд.

### B2 — форми й приєднання

| № | Завдання / опорний фрагмент | Перевірений ключ |
| --- | --- | --- |
| 1 | The woman carrying a red folder is our guide. | Carrying — -ing; описує woman; назва форми не визначає час будь-якого речення |
| 2 | The tickets printing / printed this morning... | Printed: квитки є об’єктом друку, не виконавцем |
| 3 | The people who are repairing the roof need a ladder. | Приклад: The people repairing the roof need a ladder. Збережено активного виконавця |
| 4 | We photographed the bus parked beside the school. | Parked описує bus; головний підмет we. Означення додатка коректне |
| 5 | Opening the cupboard, a plate fell onto the floor. | Dangling: тарілці приписано відкривання. Виконавець невідомий, ім’я не вигадується |
| 6 | Reading the message, the lights went out. За фактом читав Taras | Приклад: While Taras was reading the message, the lights went out. Відновлено відомого читача без нової дії |

### C1 — значення та вибір форми

| № | Завдання / опорний фрагмент | Перевірений ключ |
| --- | --- | --- |
| 1 | Sorting / Having sorted the files, Sofia made a backup. | Having sorted прямо підкреслює завершення. Sorting не оголошено неграматичним у всіх контекстах |
| 2 | Having inspected / Having been inspected by a specialist, the lift... | Having been inspected: огляд спрямований на ліфт, виконавець — specialist |
| 3 | Because Roman had not seen the revised map... | Приклад: Not having seen the revised map, Roman took the old route. Збережено not і попередність |
| 4 | Having a valid pass / Having obtained a valid pass... | Перше — лексичне have; друге — perfect. After obtaining також зберігає порядок |
| 5 | Seeing the warning light, Pavlo stopped the machine. Явна причина | Приклад: Because Pavlo saw the warning light, he stopped the machine. Початкове речення граматичне, але зв’язок менш явний |
| 6 | Because the filter might be damaged... → Damaged, the operator... | Нееквівалентно: губиться might, опис приєднується до operator. Зберегти повну вихідну конструкцію |

### C2 — власні підмети й редагування

| № | Завдання / опорний фрагмент | Перевірений ключ |
| --- | --- | --- |
| 1 | The audience watching in silence, the pianist... | Audience → watching → pianist → одночасне тло. Явний власний підмет не dangling |
| 2 | The guide reading the map, we waited... / Reading the map, the gate... | Перше absolute; друге приписує читання gate. Невідомий читач не вигадується |
| 3 | The equipment packing / packed, the climbers... | Packed: спорядження зазнає дії; стан перед виходом |
| 4 | The last group arriving / having arrived, the coordinator... | Having arrived явно виражає завершення; arriving можливе з іншим ракурсом, але не виконує умову |
| 5 | The lights, having been switched off the guard... | Кома після всього звороту, не після lights. Варіант із had been — дві особові частини; наведено повний After-варіант |
| 6 | Ланцюжок rehearsal having ended / instruments having been packed / players being tired | Приклад із трьома реченнями: після репетиції інструменти спакували; музиканти втомлені; диригент подякував. Порядок і пасив збережені, пакувальника не вигадано |

18 формулювань різні між собою та не дублюють вправ дев’яти прийнятих уроків M11–M13. Відкриті перетворення позначені як приклади відповідей або мають перелік доречних альтернатив. Неграматичність, зміна змісту і невиконання інструкції явно розрізняються.

## 6. Реальне локальне застосування

Робочий `APP_ENV=production` збережено; .env, APP_KEY, credentials і connection не підмінялися. Чинний фізичний guard перевірив:

- gramlyze.loc резолвиться лише в loopback;
- активний Apache/vhost веде до `D:/DEV/htdocs/gramlyze.loc/public`;
- PID і TCP-listener належать локальному MySQL, немає forwarding/split/remote connection;
- nonce-bound read-only HTTP proof фактичного вебruntime збігається з CLI runtime/БД.

Поточні локальні source/DB відповідали старим definitions до змін. Адресна команда: `content:patch-participle-clauses-m14`; adapter завантажує незмінений робочий app і тільки M14 sources з ізольованого checkout. Власні full-identity allowlist, guard namespace й before-manifest; shared updater і попередні пакети не змінено.

### Завершений цикл і повторна візуальна перевірка

1. Початкові редактура/форматування та staged diff завершені до preview; створено exclusive record-backup, транзакційно застосовано 12 записів; postcondition і no-op успішні.
2. Перегляд мобільних screenshots виявив стискання чотирьох колонок. Початковий тест, який дозволяв таблиці вміститися без scroll, цього не виявив. Його PASS не використано як остаточне візуальне приймання.
3. **Тільки 12 записів M14** повернено guarded record-restore; не відновлювалася вся БД. У definitions застосовано прийняту M13 геометрію `width:100%;min-width:720px` усередині overflow-wrapper. Додано окрему перевірку реального mobile scroll.
4. Фінальні sources знову відформатовані й staged diff перевірений. Свіжий proof → **новий** `preview-mobile-final.json` → перевірка scope → **новий** `record-backup-mobile-final.json` → transactional apply → postcondition.
5. Фактично оновлено **12 унікальних записів: 3 pages.text + 9 text_blocks.heading/body**. Кількість узято з preview/result, а не примусово задано updater.
6. Повтор фінального plan: **no-op, updated=0**; `unused-mobile-noop.json` не створений.
7. Уся фінальна browser-матриця запущена повторно й screenshots переглянуті.

Приватний каталог доказів і backup:
`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m14-local/`.

Фінальні артефакти: `inventory-before.json`, `preview-mobile-final.json`, `record-backup-mobile-final.json`, `before-http.json`, `final-http.json`, `final-comparison.json`, `final-browser.json`, `controls-browser.json`, `protected-before.json`, `protected-accepted.json`, screenshots. Попередній backup також збережений. Ці файли не входять у Git.

Тимчасовий require у робочому routes/api.php від’єднаний. Після перевірки відсутності сторонніх нових правок відновлено точні початкові байти файлу разом із попередніми користувацькими змінами. Діагностичний endpoint повертає **404**. Apply не додано в HTTP/startup/Composer/Git hooks/scheduler.

## 7. Автоматичне та живе приймання

### Ізольовані тести

Фінальний запуск через `tools/diagnostics/run-isolated-tests.py`, PHP 8.5.10, окрема SQLite fixture:

- `ParticipleClausesContentPackageTest`;
- `ParticipleClausesContentPatchTest`;
- `M11LocalTargetGuardTest`;
- `TheoryRichContentTest`;
- `SentenceStructureContentPackageTest`.

**64 тести, 2186 assertions, 0 failures/errors.** Окремо Node: **8/8** `tests/Browser/seo-m14-local.test.cjs`.

Перевірено JSON/hero, незмінність identities, нормалізацію відсутнього category.type, 6+6, renderer opt-in, відсутність службового HTML, фактичний `TheoryController::showByCategoryPath` і controller-derived H1, metadata, resolvers, курс, old→new/no-op/restore, ручні/stale/ancestry/UUID/locale/pivot конфлікти, exclusive backup, transaction rollback і відмову непідтвердженому local-target. Production SEO-профіль перевірявся тільки in-memory, без звернення до production.

Є **1 наявне PHP deprecation**: `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` застаріло з PHP 8.5. Окремий ізольований запуск з `--display-deprecations` підтвердив причину (1 тест / 2 assertions). PHP/config/dependencies у M14 не змінювалися.

### HTTP і браузер

11 фіксованих адрес у before/final capture: 3 theory + 3 tests + course + 2 controls + Defining Relative + sitemap. Усі GET 200; не використовувалися пошуковий кеш, page.setContent, route.fulfill, innerHTML чи fixture-підстановка.

| Перевірка | Фінальний результат |
| --- | --- |
| 3 уроки × desktop 1440×1000 / mobile 390×844 | 6/6 сценаріїв |
| Світла і темна теми в кожному | 12/12 станів; текст, таблиці, ключі й матеріал нижче першого екрана читабельні |
| Перший server response / DOM / звичайний reload | Новий текст збігається; у кожному 6 завдань і 6 ключів |
| Нові browser contexts | Окремий гостьовий context для кожного сценарію, без успадкованого стану |
| Renderer | 8 rich sections на урок; приклади B2/C1/C2: 24 / 33 / 28; немає plain fallback чи сирого HTML |
| Native details | Миша відкриває/закриває; Enter відкриває; Space закриває; Enter знову відкриває |
| Мобільні таблиці | Wrapper 322 px, content 720 px, перевірений scrollLeft=398; document overflow=false |
| Основні тести | Усі три штатні переходи виконані; URL і test H1 отримані, відповіді не надсилалися |
| Контроль M13 + сусід | Complex Noun Phrases і Concessive And Contrastive Structures: 4/4 desktop/mobile сценаріїв, обидві теми |
| JavaScript pageerror | 0 у цільових сценаріях |

Збережено 58 screenshots фінального цільового проходу (включно з курсом), а також контрольні. Переглянуто основні intro/keys/table знімки всіх трьох уроків, обидві теми, мобільні таблиці після прокрутки, styled-контролі та guest course gate.

**Мережеві обмеження класифіковано окремо:**

- 15 невдалих запитів Google Fonts у фінальному цільовому прогоні: `net::ERR_NETWORK_ACCESS_DENIED`. Перевірене компонування з доступними fallback-шрифтами; PASS завантаження оригінальних Google Fonts не заявляється.
- 3 автоматичні state POST тестів заблоковані read-only runner навмисно, `stateful-request` / `net::ERR_FAILED`. Це не регресії застосунку й не відправлені відповіді.
- Публічні меню/матеріал працюють; обхід course gate не виконувався. Lighthouse, повний crawl і весь repository suite не запускалися.

## 8. Метадані, sitemap і сторонні дані

- До live apply перевірено реальний controller, не тільки окремий Blade-фрагмент. Page.title та фактичні H1 лишилися: Participle Clauses Basics; Participle Clauses; Advanced Participle And Absolute Clauses.
- Title для кожного лишився `<назва> — правила | Gramlyze`.
- Canonical збережено, у локальній відповіді він як і раніше посилається на відповідну .com-адресу. Ці URL лише прочитані з metadata, мережевих запитів на .com не було.
- Локальний X-Robots-Tag лишився `noindex, nofollow, noarchive`, meta robots незмінні.
- Лише три цільові descriptions змінилися через новий український hero intro. Meta/OG/Twitter узгоджені, назва не повторюється двічі в одному description.
- Metadata курсу, тестів і сусідів не змінені. Основні test href before/final однакові.
- Ordered sitemap: **554 URL фактично і до, і після**; порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334` однакові. Число не захардкоджене в перевірці.
- Before/accepted порівняння **46 таблиць**: кількість записів і захищені хеші незмінні за винятком дозволених content-полів 12 адресних записів.
- Усі дев’ять прийнятих source/DB уроків M11–M13 збігаються. Питання, відповіді, опції, підказки, зв’язки, локалізації й банки тестів збережені. Хеш .env незмінний.
- Сідери запускалися лише в ізольованих fixtures. На робочій БД не було db:seed, migrations, truncate, масового update, cache/session clearing або full restore.
- Build inputs і shared renderer/CSS не змінені, тому build не запускався. PHP/XAMPP/Composer не оновлювалися.

## 9. Git і обмеження перенесення

До коміту явно обрано лише 13 файлів M14: 3 definitions, before-manifest, 3 command/service/guard wrappers, CLI adapter, локальний діагностичний profile, 3 test-файли й цей звіт. Приватні докази, backup, .env, vendor, runtime caches і сторонні зміни виключені. Staged diff перевірено на whitespace, scope і секрети; збігів секретів і whitespace-помилок немає. Фінальні source/hash збігаються із застосованим preview.

Гілка призначена для звичайного push у `origin/codex/seo-m14-participle-clauses-content`; фінальний SHA і результат звірки remote вказуються в повідомленні завершення. Production-перенесення цього пакета зараз не реалізовувалося й не запускалося. Звіт не є дозволом на майбутній деплой.
