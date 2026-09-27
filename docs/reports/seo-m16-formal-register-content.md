# M16 — Formal English: локальне завершення

Дата: 27 вересня 2026 року. **Зміни застосовано до робочого gramlyze.loc.**

## Межі та база

- Репозиторій: `Pvitaly91/engapp-codex`.
- Після fetch перевірено прийняту базу `origin/codex/seo-m15-conditionals-content`: `8a5ab0d698164ff005518d4876ffd8e892d66633`. Новішого продовження цієї remote-гілки не було.
- Гілка роботи: `codex/seo-m16-formal-register-content`, нащадок прийнятої M15.
- Використано вільний worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`. Основний checkout мав сторонні незавершені зміни; його не перемикали, не скидали й не включали до commit.
- Редагувалися рівно три навчальні definitions. Старі пакети M11–M15, їхні manifests/allowlists, TheoryRichContent, Blade, CSS, алгоритми тестів, банки питань і прогрес не редагувалися.
- HTTP/браузерне приймання — тільки `http://gramlyze.loc`. Production-профіль — ізольовані тести в пам’яті. Адреси .com у canonical/sitemap порівнювалися як рядки.
- Без production-доступу, PR, merge, workflow dispatch, деплою, force push або push у main.

## Ідентичності та перевірені адреси

Спільний namespace: `Database\Seeders\Page_V3\FormalEnglish\`. Повні identities нижче відповідають точній allowlist пакета.

| Page.seeder | Рівень | Реальна сторінка теорії | Основний тест |
| --- | --- | --- | --- |
| `Database\Seeders\Page_V3\FormalEnglish\FormalRegisterAndNominalisationBasicsTheorySeeder` | B2 | [Formal Register and Nominalisation Basics](http://gramlyze.loc/theory/formal-english/formal-register-and-nominalisation-basics) | [Тест B2](http://gramlyze.loc/test/formal-english/formal-register-and-nominalisation-basics) |
| `Database\Seeders\Page_V3\FormalEnglish\NominalisationFormalRegisterTheorySeeder` | C1 | [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) | [Тест C1: номіналізація](http://gramlyze.loc/test/formal-english/nominalisation-formal-register) |
| `Database\Seeders\Page_V3\FormalEnglish\RegisterToneAndParaphraseTheorySeeder` | C1 | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) | [Тест C1: тон і перефразування](http://gramlyze.loc/test/formal-english/register-tone-and-paraphrase) |

URL отримано через чинні Page/test/course resolvers і перевірено реальними GET та переходами з теорії. Категорія — коренева `formal-english`, locale/language `uk`, type `theory`. Збережені slug, Page.title, фактичні H1, рівні B2/C1/C1, теги, власники, UUID/ID, порядок subtitle/hero/box та зв’язки.

Курсова копія: [B2 у курсі](http://gramlyze.loc/courses/english-grammar-theory/lesson/formal-english/formal-register-and-nominalisation-basics).

Read-only перевірені сусіди:

- [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases).
- [Ellipsis Substitution and Reference](http://gramlyze.loc/theory/sentence-structure/ellipsis-substitution-and-reference).
- [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices).
- [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) — доступний наявний короткий урок; M16 його не розширює.
- [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) — styled-контроль M15.

## Початкові недоліки та виконані уточнення

Усі три before-definition збігалися з відповідними робочими локальними записами. Невідомих ручних правок у цільових полях не знайдено. Перед редагуванням були короткі англомовні hero-опори й службові box про theory anchor, без повноцінних українських пояснень та самоперевірки. Rich-секцій було 0; після застосування — по 8.

| До | Проблема / межа правила | Після |
| --- | --- | --- |
| B2: «Use conduct, obtain, require, and indicate instead of more casual alternatives when needed.» | Застереження when needed правильне, але не пояснювало значення й керування; не варто трактувати його як універсальну заміну. | Конкретні природні сполучення, протиставлення obtain permission / get to a place, conduct a survey / do the dishes, require information / require somebody to do something, indicate that/which. |
| B2: «Turn actions into noun phrases for formal style.» | Надто коротко: не кожна іменникова група — номіналізація; формальніше не завжди ясніше. | Зв’язок decide → decision, approve → approval, analyse → analysis, accurate → accuracy; перебудова решти граматики та випадки переваги дієслова. |
| C1: «Use a noun phrase when formal style needs distance or abstraction.» | Відстань/абстрактність не є достатньою причиною ускладнювати текст або приховувати відповідального. | Фокус на процесі, зв’язність, явне збереження виконавця, часу, плану/процесу/результату; невідомого виконавця не вигадуємо. |
| C1 tone: «Keep meaning stable…» поруч із «Adjust certainty, politeness, and distance…» | Зміна впевненості може змінити саме твердження; її не можна автоматично називати рівнозначним перефразуванням. | Явно розділені А: збереження твердження та Б: свідома зміна сили звернення. May ≠ will; some ≠ all; рекомендація ≠ обов’язок. |

Початкові доречні ідеї про регістр, словникові сполучення, номіналізацію та стабільність змісту збережено й розгорнуто. Не вводилися загальні заборони на I/we, активний стан, короткі слова, phrasal verbs або contractions.

### Три різні навчальні цілі

- **B2:** вибрати стиль під адресата, виконати просте перетворення без втрати граматики; два власні повідомлення колезі й новому адресату мають однакові дію, строк і мету.
- **C1 nominalisation:** пояснити функцію іменникової конструкції в абзаці; покроково перебудувати думку, зберегти її статус і спростити зайві noun chains. Дві редакції власного абзацу порівнюють зв’язність і перевантаження.
- **C1 tone:** розрізняти регістр, тон і перефразування; перевіряти всі твердження, умови, строки й модальність; відрізняти точне перефразування від стислого переказу. Вигаданий навчальний сюжет явно позначено; наведено коротку вимогу атрибуції зовнішніх матеріалів.

Короткі приклади принципу before → after у новому матеріалі:

- `We decided on Monday to invite two speakers.` → `We made a decision on Monday to invite two speakers.`: рішення, а не вже надіслані запрошення.
- `The engineers are currently assessing the bridge.` → `The engineers’ assessment of the bridge is currently in progress.`: поточний процес, не завершена оцінка.
- Пряме прохання надіслати рахунок до полудня завтра → `Could you send me the invoice by noon tomorrow, please?`: дія й строк ті самі, сила звернення свідомо пом’якшується.

## Джерела: що саме вдалося перевірити

Використані для перевірки, не для копіювання вправ. Приклади й сюжети M16 — власні. Учнівські коментарі не використовувалися як правила.

| Першоджерело | Фактичний доступ | Застосування |
| --- | --- | --- |
| [Cambridge: Formal and informal language](https://dictionary.cambridge.org/grammar/british-grammar/formal-and-informal-language) | Пряме відкриття — HTTP 403; доступний індексований фрагмент сторінки видавця, не повний текст. | Контекст регістру, нейтральне мовлення; не універсальна заборона скорочень. |
| [Cambridge: Nouns — forming nouns from other words](https://dictionary.cambridge.org/grammar/british-grammar/nouns-forming-nouns-from-other-words) | Пряме відкриття — HTTP 403; перевірений індексований фрагмент видавця. | Словотвір різний, немає одного універсального суфікса. Конкретні форми додатково звірені зі словником. |
| [University of Melbourne: Nominalisation](https://students.unimelb.edu.au/academic-skills/language-development/vocabulary-and-grammar/nominalisation) | Прочитано основний текст. | Дієслово/ознака → іменник, зміна решти граматики, зв’язність, ризик перевантаження й зміни сенсу. Пасив і номіналізація в M16 окремо розмежовані. |
| [Monash: Descriptive writing language features](https://www.monash.edu/student-academic-success/improve-your-academic-english/the-language-of/describing-things,-actions-and-events/descriptive-writing-language-features) | Прочитано основний текст. | Ясність та жанрова доречність; рекомендації для академічного опису не перетворено на правила для всіх професійних жанрів. |
| [British Council: An email request](https://learnenglish.britishcouncil.org/free-resources/writing/c1/email-request) | Прочитано матеріал і редакційні tips, не коментарі. | Новий адресат, ввічливі прохання, доречний ступінь формальності. |
| [Purdue OWL: Paraphrase](https://owl.purdue.edu/owl/research_and_citation/using_research/quoting_paraphrasing_and_summarizing/paraphrasing.html) | Прочитано основний текст. | Власне формулювання зі збереженням суттєвого змісту та посиланням на зовнішнє джерело. |

Додатково перевірені індексовані редакційні матеріали [Purdue про tone/audience](https://owl.purdue.edu/owl/general_writing/writing_style/diction/tone_mood_audience.html), [paraphrase/summary](https://owl.purdue.edu/owl/research_and_citation/using_research/quoting_paraphrasing_and_summarizing/index.html), [Cambridge про contractions](https://dictionary.cambridge.org/grammar/british-grammar/contractions) і [multi-word verbs](https://dictionary.cambridge.org/us/grammar/british-grammar/phrasal-verbs-and-multi-word-verbs). Це частковий доступ, не твердження про прочитання всіх сторінок повністю.

Словникові значення й сполучення перевірено за індексованими статтями Cambridge Dictionary: [conduct](https://dictionary.cambridge.org/dictionary/english/conduct), [obtain](https://dictionary.cambridge.org/dictionary/english/obtain), [require](https://dictionary.cambridge.org/dictionary/english/require), [indicate](https://dictionary.cambridge.org/dictionary/english/indicate), [approval](https://dictionary.cambridge.org/dictionary/english/approval), [analysis](https://dictionary.cambridge.org/dictionary/english/analysis), [decision](https://dictionary.cambridge.org/dictionary/english/decision), [reduction](https://dictionary.cambridge.org/dictionary/english/reduction), [accurate](https://dictionary.cambridge.org/dictionary/english/accurate), [accuracy](https://dictionary.cambridge.org/dictionary/english/accuracy), [assessment](https://dictionary.cambridge.org/dictionary/english/assessment), [examination](https://dictionary.cambridge.org/dictionary/english/examination). Орієнтиром були словникові значення та моделі видавця, не випадкові корпусні приклади.

## Редакторська перевірка 18 ключів

Виконана окремо від структурних автоматичних тестів: граматика, природність, значення, учасники, час, модальність, український переклад і допустимі альтернативи. У відкритих відповідях використано «приклад відповіді» та критерії, а не штучно єдине формулювання.

| Урок / № | Завдання та ключ | Перевірка збереження змісту / альтернатив |
| --- | --- | --- |
| B2 / 1 | Обрати перший лист незнайомій працівниці музею: **B**, ввічливий запит доступних дат до п’ятниці. | Заданий адресат і строк; A не оголошено граматично неправильним у будь-якій ситуації. |
| B2 / 2 | Впізнати номіналізацію: `the director’s approval of the poster`. | Approval походить від approve; директор і афіша збережені. `the yellow poster` сам по собі не називає дію. |
| B2 / 3 | Перебудувати рішення запросити двох доповідачів: `We made a decision on Monday to invite two speakers.` | We, Monday, two та статус рішення збережено; запрошення ще не вважаються надісланими. |
| B2 / 4 | Виправити `obtained to the gallery`: `We arrived at the gallery at ten.` | Природне `We got to the gallery at ten.` також прийнятне. Obtain не має тут значення прибуття; arrive at. |
| B2 / 5 | Запросити план розсадки до полудня завтра для карток з іменами. | Приклад із Could you… та окремою метою I need it to prepare…; I would be grateful… можливе. Строк і мета обов’язкові. |
| B2 / 6 | Спростити рішення підтвердити бронювання: `Yesterday we decided to confirm the booking today.` | Yesterday стосується рішення, today — запланованого підтвердження; не перетворено на завершену дію. |
| C1 nominalisation / 1 | Перебудувати поточне дослідження розпису: `The conservators’ examination of the mural is currently in progress.` | Збережено conservators, mural, currently і незавершений процес. |
| C1 nominalisation / 2 | Зв’язати перевірку підписів редакторами з двома датами: `Their review revealed two incorrect dates.` | Their відсилає до редакторів; дія перевірки та знайдені two dates ті самі. Прийнятні інші точні зв’язні формулювання. |
| C1 nominalisation / 3 | Виявити підміну may repair на completed; приклад: `Repair of the gate by the volunteers next week is possible.` | Волонтери, ворота, наступний тиждень, лише можливість; will be completed недоречно гарантує результат. |
| C1 nominalisation / 4 | Виправити `did a analysis on`: `We conducted an analysis of the booking requests.` | An перед голосним звуком, conduct an analysis, of називає об’єкт. Carried out природне, але завдання явно задає conduct. On не заборонено в усіх контекстах. |
| C1 nominalisation / 5 | Спростити повідомлення про скасування майстер-класу: `It was announced yesterday that the workshop had been cancelled.` | Контекст прямо встановлює вже здійснене скасування й учорашнє оголошення. Невідомих виконавців не вигадано; workshop перекладено як майстер-клас. |
| C1 nominalisation / 6 | Відредагувати абзац про два дизайни: прямі дієслова + доречне `This evaluation`. | Комітет, two designs, Tuesday, missing label та план додати Friday збережені. Пояснено, де назва процесу підтримує зв’язність. |
| C1 tone / 1 | Обрати нейтральне повідомлення новому клієнту: **B**. | Враховано ситуацію; невимушений A не названо універсально неправильним. |
| C1 tone / 2 | Перефразувати повідомлення дизайнера: `The revised map will be sent to you by our designer by 4 p.m. on Wednesday.` | Збережені designer, revised map, адресат, will і граничний строк by 4 p.m.; at 4 p.m. не є точною заміною. |
| C1 tone / 3 | Знайти підміни not all → no, some → all, may → will. | Усі три змінюють твердження. Переклад відображає «не всі», «декому», «може знадобитися», а не повну заборону/обов’язковість. |
| C1 tone / 4 | Пом’якшити запит: `Could you send me the invoice by noon tomorrow, please?` | Предмет і строк збережені, пряме звернення стало ввічливим питанням-проханням; це свідома зміна сили, не повна функціональна тотожність. |
| C1 tone / 5 | Точне перефразування клубних заходів — **A**; B — стислий переказ. | A зберігає club, two, June, may та умову доступності парку; `The club may run outdoor sessions.` ці деталі опускає. |
| C1 tone / 6 | Виправити підміну умов проведення: `If the hall is unavailable, the organiser may move only the Saturday workshop online. Friday’s session will still take place in person.` | Збережені organiser, only Saturday, may та if; п’ятниця залишається очною. Немає all/will/because замість заданого обмеження, умови й невпевненості. |

18 формулювань завдань різні між собою та не збігаються з самоперевірками 15 прийнятих уроків. Автоматична відсутність точних дублів не подається як доказ редакційної точності.

## Фактичне локальне застосування

Мінімальні wrappers: `FormalRegisterContentPatch`, `M16LocalTargetGuard`, команда `content:patch-formal-register-m16`, Windows CLI-адаптер `run-m16-working-local.php`. Повторно використані контракти M11: перевірка фізичної цілі, stale/manual conflict, exclusive backup, транзакція, postcondition, адресний guarded restore. HTTP/startup/Composer/Git/scheduler apply не додано.

Versioned before-manifest: `database/content-patches/m16-formal-register-before.json`. Він містить рівно три старі публічні definitions із базового Git-коміту, **не** приватний знімок БД.

Після завершення редагування sources:

1. Guard підтвердив фізичний локальний Windows MySQL і відповідність справжньому vhost; APP_ENV=production не підмінювався. Початкова sandbox-спроба не мала доступу до process/listener inspection й не писала дані; повторено лише необхідну read-only перевірку з підвищеним доступом.
2. Свіжий proof: 19:26:20 UTC. Preview перевірено на точні три identities та дозволені поля; SHA плану `be2046a9fd3b71ea9687f553105ea88fc6e08472202979547e22c84a15a27344`.
3. Створено новий exclusive record-backup і виконано transactional apply: **12 записів**, postcondition успішний.
4. Повторний запуск того самого погодженого плану: **no-op, 0 змін**; зайвий backup не створився.
5. Після браузерних перевірок повторно звірено захищені дані та джерела попередніх уроків.

Фактичні записи:

| Page ID | Subtitle / hero / box text_block IDs |
| --- | --- |
| 291 | 8762 / 8765 / 8768 |
| 301 | 8790 / 8791 / 8792 |
| 307 | 8808 / 8809 / 8810 |

Оновлено тільки погоджені `pages.text` та `text_blocks.heading/body`; IDs/UUIDs, власники й порядок незмінні. Приватний backup збережено локально: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m16-local/records-before-final.json` (4 188 517 байтів). Preview, proof та діагностичні матеріали також залишилися приватними й не входять до Git.

Тимчасовий read-only proof route прибрано: GET повернув **404**. `routes/api.php` після вилучення точно збігається з початковими байтами; сторонні зміни збережені. Повного дампа, reseed, міграцій, truncate, масових UPDATE, очищення кешів/сесій не виконували.

## Приймання: renderer, HTTP, браузер

### Автоматичні перевірки

- Ізольовані PHP-тести: `FormalRegisterContentPackageTest`, `FormalRegisterContentPatchTest`, `M11LocalTargetGuardTest` — **41 тест, 645 assertions**, exit 0.
- Є одна наявна PHP 8.5 deprecation для `PDO::MYSQL_ATTR_SSL_CA` у конфігурації БД. Вона не спричинила падіння; залежності/конфігурацію поза scope не змінювали.
- Node-контракти `tests/Browser/seo-m16-local.test.cjs` — **8/8**.
- Pint для шести нових PHP-файлів — passed.
- Перевірено JSON/hero, 18 завдань/ключів, rich opt-in, controller-derived H1, URLs, metadata, immutable identities, old→new/no-op, manual/stale conflict, exclusive backup, rollback, restore після сторонніх правок та відмову непідтвердженій цілі.
- Production-профіль SEO перевірений тільки ізольованими in-memory requests; це не production HTTP-приймання.

### Живі GET

Before: 19:14:03 UTC; after: 19:27:53 UTC. Фіксований набір із **13/13 HTTP 200**: три теорії, три тести, курсова копія, два styled-контролі, три додаткові пов’язані теорії та sitemap.

Новий матеріал присутній у початковому серверному HTML, не підставлений у DOM. Кожна теорія має 6 завдань, 6 пояснених ключів, 8 rich-карток/секцій. Немає службового anchor box, сирих HTML-тегів або технічних маркерів у навчальному тексті.

### Реальний браузер

Chromium 147.0.7727.15, read-only runner без page.setContent, route.fulfill, innerHTML чи fixture-підміни live HTML.

- **6/6 основних сценаріїв:** 3 уроки × desktop/mobile, кожний у світлій і темній темі.
- Перевірені початковий HTML, матеріал нижче першого екрана, reload і новий context, нумеровані картки, англійські приклади/переклади та відкриті ключі.
- Details працює мишею, Enter і Space на всіх шести сценаріях.
- Усі три штатні переходи до основних тестів успішні; відповіді на тести не надсилалися.
- Контролі Advanced Conditionals і Complex Noun Phrases: **4/4 desktop/mobile сценарії**, обидві теми.
- Overflow документа відсутній. Ширина мобільного контейнера таблиць — 322 px; фактичний горизонтальний scroll: B2 478 px при contentWidth 800, C1 nominalisation 458 при 780, C1 tone 358 при 680.
- Скриншоти збережено приватно. Візуально переглянуті три таблиці зліва/справа на mobile, три вступні desktop-екрани, довгі приклади й ключі mobile у світлій/темній темі, обидва контролі та курсова копія. Приклади переносяться всередині карток; широкі колонки прокручуються локально, а не стискаються до вузьких слів.

Курсова копія повернула HTTP 200 з правильним H1. Для нового гостя урок **заблокований штатним gate**: відкритий навчальний блок live не приймався й gate не обходився. Серверний HTML містить 6 завдань/ключів; відповідність курсової моделі тим самим blocks і рендер навчального матеріалу додатково перевірені ізольовано. Це не твердження про доступність закритого контенту гостю.

Application pageerror не зафіксовано. Окремі очікувані мережеві обмеження:

- Google Fonts: 15 невдалих запитів у головному проході та 10 у контрольному, `ERR_NETWORK_ACCESS_DENIED`. Візуальне приймання відбулося з fallback-шрифтами.
- Автоматичні test state POST: 3 + 2 навмисно заблоковані runner як `stateful-request`; відповідні `ERR_FAILED` не є збоями застосунку. Прогрес не записувався.

## Метадані, sitemap та збереження даних

Page.title, фактичний H1, HTML title, canonical, robots та основні test href збережено. Локальний X-Robots-Tag лишився `noindex, nofollow, noarchive`; meta robots відсутній як і before.

Лише описи трьох теорій змінилися через чинний PageMetadata; meta/OG/Twitter однакові між собою, назва не дублюється:

1. «Formal Register and Nominalisation Basics. Стиль залежить від адресата й мети повідомлення.»
2. «Nominalisation and Formal Register. Іменникова конструкція може підхопити попередню думку й змінити фокус тексту.»
3. «Register Tone and Paraphrase. Змінюй формулювання під адресата, але звіряй факти, строки й ступінь упевненості.»

Один ordered sitemap before/after: фактично **554 адреси**, незмінний порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Число спостережене, не hardcode умови успіху.

Read-only fingerprints усіх **46 таблиць**, виключаючи тільки дозволені поля 12 записів, збігаються before/after/commit-ready. Questions/answers/options, verb_hint, зв’язки, прогрес та інші дані незмінні. Усі **15 прийнятих уроків M11–M15** досі збігаються зі своїми джерелами; texts/metadata контрольних і пов’язаних сторінок незмінні. .env зберіг початковий hash; APP_KEY, APP_ENV=production і підключення не змінювали.

## Межі завершення

Не запускали повний suite, crawl, Lighthouse, M10 або LCP/CLS-кампанію. Build inputs не змінювалися, тому rebuild і оновлення залежностей не потрібні. Нові Mixed-банки не створювалися й не перевірялися на відповіді — тільки штатні переходи до існуючих тестів.

Версійний пакет містить тільки definitions, вузький before-manifest, мінімальні wrappers/адаптер/readonly-профіль, цільові тести та цей звіт. Приватні proofs/backups/screenshots, .env, vendor, build і сторонні незавершені зміни виключені. Звичайний push призначено тільки для `codex/seo-m16-formal-register-content`; commit SHA та результат звірки remote наводяться у фінальній відповіді після push.

**Production не перевірявся й не змінювався.**
