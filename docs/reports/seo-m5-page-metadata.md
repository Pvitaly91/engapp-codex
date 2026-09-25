# M5 — точні SEO-заголовки та описи

## Межі та база

Gramlyze, 2026-09-09. Базовий HEAD і `origin/codex/seo-m4-1-sitemap-performance` після fetch: `4fdeb75b66440a5d46cdc65fbd32b4f7001d0932`. `origin/main`: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`. Створена гілка `codex/seo-m5-page-metadata`. Історія містить M1 (`d123a7380`), M2 (`b58c2769b`), M3 (`7c649117e`), M3.1 (`54d34083a`), M3.2 (`7938eebb3`), M4 (`9b8a16872`) та M4.1.

Прочитано AGENTS.md, звіти M4 і M4.1. Усі HTTP/браузерні запити цього етапу — лише `http://gramlyze.loc`; `.com` у sitemap/canonical — тільки SEO-origin. Production, SSH, серверна БД, Search Console та deployment API в M5 не використовувалися. Проблема сумісності sitemap із серверним MySQL, виявлена в попередньому окремому деплої, не виправляється й не перевіряється в M5.

Сторонні PPC-аудити, `.codex/`, dumps/backups та приватні артефакти збережені. Не змінені .env, schema, індекси, навчальні питання/відповіді, Page.title, test names, TextBlocks, visible H1, маршрути, canonical/robots, доступність курсів, autosave/progress або стилі.

## Що підтверджено до змін

`tools/diagnostics/seo-m5-metadata.py` отримав фактичний sitemap і виконав 554 прямі GET, максимум два одночасно, з Accept: text/html; без Cookie/Authorization/Referer/proxy/redirect/retry. Зберігаються лише метадані, timestamps, counts, bytes/hashes і status; повного HTML, CSRF, питань чи відповідей немає.

Перший baseline: 12:02:49–12:11:36 UTC, `storage/app/seo-m5-local/m5-before.json`: 552 успішні HTML-відповіді та два TimeoutError на `/theory/passive-voice` і `/theory/basic-grammar` після 45 секунд. Це не порожні title і не зниклі сторінки. Окремий ручний послідовний recheck до підключення нових метаданих: HTTP 200 за 2,500 і 2,281 с. Початкові помилки не переписані; supplement — `m5-before-recheck.json`. Сукупний baseline має метадані всіх 554 адрес.

| Показник | До |
| --- | ---: |
| Порожні title / description | 0 / 0 |
| Кілька title / description у head | 0 / 0 |
| Точні дублікати title / description основних URL | 0 / 0 |
| Title з механічним `…` | 12 |
| Description з механічним `…` | 182 |
| Сирі теги / entities / translation keys у метаданих | 0 |
| Розбіжності Open Graph / Twitter з title/description | 0 |

Дублів не було: M5 не заявляє «виправлення дублів», яких аудит не підтвердив. Курсова копія не входить до основного ordered sitemap і не порівнюється як окрема основна сторінка.

Джерела проблем: `theory/show` обрізав назву на 48 символах і description на 159; `theory/category` обчислював ліміт назви за загальною довжиною; tests використовували внутрішню назву з `(Mixed A1-C2)`. Категорійний шаблон давав, зокрема, «4 структурованих уроків». Усі курси отримували один шаблон опису, хоча grammar-theory і theory-driven мають різні навчальні сценарії.

## Реалізація

Чинна система — Blade sections у `layouts/catalog-public` та спадкування в `layouts/partials/social-meta`. Окремого SEO-CMS/override resolver не знайдено. Вона збережена; додано невеликий pure `App\Support\PageMetadata`, що отримує вже завантажені scalar/array дані, не викликає БД, registry, course provider або question bank.

- Теорія: повна збережена `Page` назва через `getRawOriginal`, а не скорочений subtitle display title; видимий H1 не змінюється. Повна точна тема, українські пояснення повторюваних підрозділів (форми, заперечення, питання, часові маркери), маркер «правила». Зайвий повтор слова «правила» і бренду усувається без обрізання назви.
- Тести: ідентичність із уже визначених controller breadcrumbs теорії; fallback — чинне ім’я тесту без технічного all-level suffix. Дані питань, query, session і порядок банку не є джерелом SEO.
- Intro береться тільки з hero/hero-v2, обирається ціле перше речення. За відсутності українського intro — точний тематичний fallback; англійські технічні intro не видаються за український опис. Формули, слова та переліки не обрізаються за числом символів.
- Категорії: hero intro або фактично наявні впорядковані назви уроків, без змінних лічильників. Це не випадковий related-pages widget уроку.
- Два course descriptions прив’язані до стабільних course slugs і чинних manifest/controller contracts: theory course зберігає прогрес на пристрої; theory-driven використовує змішані тести та послідовне відкриття уроків. Назви курсів не змінені.
- Українські index/home/catalog metadata уточнені без зміни видимих H1. EN/PL descriptions і мова залишаються чинними; спільне видалення обрізання назв не додає українських рядків до інших локалей.
- Plain-text helper прибирає розмітку й небезпечні елементи, нормалізує один legacy double-encoding layer і виконує один entity decode. Результат залишається рядком, Blade escaping не вимикається. Social partial обирає section через `hasSection`, без вкладеного `yieldContent`, який повторно екранував fallback. Декодується рівно один рівень escaping Blade section; свідомі social overrides збережені, URL/image/type не змінені.

Немає вручну переписаної таблиці 554 URL, нових DB-полів чи зовнішніх AI-викликів. Два course-slug описи — єдині редакційні винятки за ідентичністю; повторювані назви підрозділів перекладаються спільним словником. Опис не гарантує конкретного способу відповіді, рівня, кількості питань, безкоштовності чи пояснення помилок там, де це не доведено.

## Змінені файли

| Файли | Призначення |
| --- | --- |
| `app/Support/PageMetadata.php` | Детерміновані plain text, повні title, topic-aware descriptions, два course descriptions |
| `resources/views/theory/show.blade.php`, `theory/category.blade.php` | UK metadata з уже завантажених Page/category/hero даних |
| `resources/views/test-show.blade.php` | Повна серверно визначена ідентичність основного тесту |
| `resources/views/courses/show.blade.php`, `courses/theory-course.blade.php` | Метадані фактичного типу курсу |
| `resources/views/layouts/partials/social-meta.blade.php` | Один fallback section, одноразове декодування escaping |
| `resources/lang/uk/public.php` | Головна, каталог теорії, каталог курсів; не H1 |
| `tests/Unit/PageMetadataTest.php`, `tests/Feature/ResolvedLearningPageSeoTest.php` | Тематична точність, безпечний head, локалі, інтеграційні інваріанти |
| `tools/diagnostics/seo-m5-metadata.py`, `seo-m5-compare.py`, `seo-m5-context.php`, `seo-m5-browser.cjs` | Local-only inventory, порівняння, read-only редакційний контекст, браузерне приймання |
| `tests/diagnostics/test_seo_m5_metadata.py`, `tests/Browser/seo-m5-browser.test.cjs` | Guards і коректність діагностики |
| `.gitignore`, цей звіт | Приватні M5-артефакти виключені, один публічний звіт |

## Приймання

Заключний повний обхід `m5-accepted.json`: **554/554 HTTP 200**, 12:42:51–12:47:12 UTC. Усі 554 основні URL мають зміну title та/або description. Порівняно весь ordered loc-набір, не лише лічильник: **ідентичний**. H1, canonical, robots та X-Robots-Tag не змінилися на жодній адресі. Чинний development noindex збережений.

| Показник | До | Після |
| --- | ---: | ---: |
| Порожні title / description | 0 / 0 | 0 / 0 |
| Кілька title / description | 0 / 0 | 0 / 0 |
| Точні дублікати title / description | 0 / 0 | 0 / 0 |
| Механічно обірвані title | 12 | 0 |
| Механічно обірвані description | 182 | 0 |
| Сирі теги / entities / translation keys | 0 | 0 |
| Розбіжності OG / Twitter | 0 | 0 |
| Максимальна довжина title / description | 77 / 182 | 84 / 261 |

Після повного обходу зроблено останнє невелике уточнення: українське слово «курс» не додається вдруге до назви, яка вже його містить. Повторено захищену PHP-suite, а потім окремо обидва основні course GET о 12:51:35–12:51:36 UTC. `m5-courses-final.json` доповнює, а не перезаписує повний inventory; фактично змінився лише title theory-driven. Кінцеві підрахунки й приклади враховують цей supplement; H1/canonical/robots та social узгодженість знову перевірено.

Єдиний `…` у нових descriptions — змістовна граматична конструкція `as…as / not as…as`, не обрізання. Звіт діагностики розрізняє наявність символу й обірване закінчення.

Перший післязмінний `m5-after.json` також мав 554 HTTP 200, але частина ранніх відповідей ще містила старий rendered metadata; цей результат збережений і не підмінений. Наступні точкові GET без втручання в сервер уже віддавали новий head. Причину тимчасової різниці не оголошуємо доведеною; конфігурацію та caches не змінювали. Заключний повний обхід усунув цю невизначеність для фактично перевірених відповідей.

Контроль вартості на чотирьох representative GET (до → після, мс): PPC Questions lesson **625 → 250**, Future Perfect Questions test **2984 → 2047**, Future Perfect category **2734 → 296**, grammar theory course **27766 → 156**. Це окремі звичайні запити з різною прогрітістю наявних кешів, не A/B і не доказ прискорення завдяки M5. Без додаткового performance-циклу; незалежний formatter-тест доводить 0 нових SQL.

Фінальний `npm run build` завершився exit 0 (Vite 5.4.19, 55 modules, 1m 9s). Усі чотири asset filenames/hashes залишились як у M4.1: зміни лише текстових PHP/Blade метаданих, CSS/JS не змінені. Попередження про застарілу Browserslist database не усувалося оновленням залежностей поза scope. HMR не використовувався; ignored build не додається до Git.

Перший protected PHP run `m5-initial-64e568b91b6b4d278e75848577b425fb-result.json` завершився exit 2 до виконання tests: помилково вказано неіснуючий `tests/Feature/LocaleSwitchTest.php`. Виправлений список використовує наявний `PageLocaleContentTest.php`; ізоляцію не послаблено. Початковий guard: фактично 46 737 paths, 0 змін. Невдала спроба збережена приватно й не названа PASS.

Наступний `m5-regression-f9c3c74232854588b57fe7daa1dfb0df-result.json`: 56 tests, 688 assertions, 2 failures, exit 1; 46 737 protected files, 0 changes. Нові перевірки лапок/амперсандів виявили подвійне escaping social fallback через вкладені `yieldContent`. Виправлено вибір section, а не послаблено assertions чи Blade escaping. Доказ старого механізму — `Illuminate/View/Concerns/ManagesLayouts.php::yieldContent`, який виконує `e($default)`.

Третя спроба `m5-final-a86dc02265c04a44948124473b2fbcf2-result.json`: 56 tests, 750 assertions, 1 failure; guard 46 737 / 0 змін. Причина — надмірно широкий новий assertion перевіряв увесь документ на текст штучного `<script>attack()`. Він містився як неактивний рядок у наявному `__QUESTION_REPORT_TEST_NAME__` JSON зі slash-escaping, не в SEO head. Assertion уточнено до DOM head — сфери M5; перевірки очищення title/description та social equality збережені. Робочий body/JSON не змінювався.

Фінальний захищений run `m5-release-74c4bb4b2e4a460686268395c22263b4-result.json`: **57 tests, 766 assertions, exit 0**, 38,775 с, 76 MiB. Фактично захищено **46 737 файлів, 0 змін**. SQLite in-memory, array cache/session, окремі compiled views/runtime, CLI OPcache вимкнено тільки для дерева тестового процесу. HTTP/browser не запускалися між before/after fingerprint. Додаткова інтеграційна перевірка доводить, що повна збережена назва зберігається у metadata за коротшого subtitle H1; EN/PL перевірені для test, theory lesson і category.

Окремо: **5 Python diagnostic tests + 1 Node guard test — PASS**. 2 000 викликів formatter у захищеному тесті: **19,2007 мс, 0 SQL**, session без змін. Це локальний мікротест formatter, не оцінка швидкості сайту чи польових показників.

Функціональний browser run `m5-accepted-browser.json`: **8/8 PASS**, Chromium 147.0.7727.15, desktop 1440×1000 / mobile 390×844, по новому guest context для кожної сторінки. Перевірено урок PPC Forms, основний Future Perfect Questions, категорію Future Perfect та курс English Grammar Theory. Меню відкривається/закривається, H1 один, social metadata збігаються, canonical незмінні, overflow 0. Questions: initial/rendered **84/84**, answered **0**, 704 input/button елементи; відповіді не надсилалися. Курс і навчальний блок присутні. Немає pageerror/console error, невдалих ресурсів або document redirects; production guard не спрацював, бо заборонених звернень не було. Збережено 8 приватних viewport screenshots; вибірково візуально переглянуто desktop Questions та mobile lesson/course. Без LCP/CLS/Lighthouse циклів.

## 19 змістовно перевірених прикладів «до → після»

Нижче — фактичні анонімні GET метадані, а не очікувані рядки з unit-тестів. Незмінені значення (наприклад, добрий description головної) наведено чесно поруч зі зміненими.

### 1. Головна

[/](http://gramlyze.loc/)

| Поле | До | Після |
| --- | --- | --- |
| Title | Gramlyze — платформа англійської практики | Англійська граматика: теорія, тести й курси \| Gramlyze |
| Description | Вивчайте англійську граматику на Gramlyze: структурована теорія, інтерактивні тести, курси, практика слів і неправильних дієслів. | Вивчайте англійську граматику на Gramlyze: структурована теорія, інтерактивні тести, курси, практика слів і неправильних дієслів. |

Перевірка змісту: Головна пропонує теорію, практику й курси; зміст перевірено в home.blade.php і чинному каталозі.

### 2. Каталог теорії

[/theory](http://gramlyze.loc/theory)

| Поле | До | Після |
| --- | --- | --- |
| Title | Теорія англійської граматики за темами \| Gramlyze | Теорія англійської граматики за темами \| Gramlyze |
| Description | Вивчайте англійську граматику за структурованими темами: правила, зрозумілі пояснення, приклади та практичні вправи для рівнів A1–C2. | Оберіть тему англійської граматики: будова речення, часи, пасивний стан, умовні речення та інші розділи з поясненнями й прикладами. |

Перевірка змісту: Категорії у фактичній навігації: будова речення, часи, пасив, умовні речення; не обіцяємо всі рівні кожній сторінці.

### 3. Категорія Future Perfect

[/theory/future-perfect](http://gramlyze.loc/theory/future-perfect)

| Поле | До | Після |
| --- | --- | --- |
| Title | Тема: Future Perfect \| Gramlyze | Future Perfect — теми й правила \| Gramlyze |
| Description | Вивчайте тему «Future Perfect»: 4 структурованих уроків із правилами, поясненнями, прикладами та практикою англійської граматики. | Future Perfect показує, що дія вже буде завершена до певного моменту, дедлайну або іншої події в майбутньому. |

Перевірка змісту: Hero категорії пояснює завершення до майбутнього моменту; підрозділи: форми, заперечення, питання, часові маркери.

### 4. Категорія Present Perfect Continuous

[/theory/present-perfect-continuous](http://gramlyze.loc/theory/present-perfect-continuous)

| Поле | До | Після |
| --- | --- | --- |
| Title | Тема: Present Perfect Continuous \| Gramlyze | Present Perfect Continuous — теми й правила \| Gramlyze |
| Description | Вивчайте тему «Present Perfect Continuous»: 4 структурованих уроків із правилами, поясненнями, прикладами та практикою англійської граматики. | Present Perfect Continuous показує процес або тривалість: дія почалася в минулому, тривала якийсь час і ще триває зараз або щойно закінчилася з видимим результатом. |

Перевірка змісту: Чотири реальні підрозділи PPC; hero описує тривалість дії до теперішнього моменту.

### 5. Форми Future Perfect

[/theory/maibutni-formy/future-perfect/future-perfect-forms](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-forms)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Forms and Use \| Gramlyze | Future Perfect: форми та вживання — правила \| Gramlyze |
| Description | Future Perfect: Forms and Use — Future Perfect. У Future Perfect ми беремо will have + V3, щоб показати дію, яка буде завершена до певного моменту або події в… | Future Perfect: форми та вживання. У Future Perfect ми беремо will have + V3, щоб показати дію, яка буде завершена до певного моменту або події в майбутньому. |

Перевірка змісту: UK hero: will have + V3, завершення до майбутнього моменту; форми й уживання присутні у блоках уроку.

### 6. Заперечення Future Perfect

[/theory/maibutni-formy/future-perfect/future-perfect-negatives](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-negatives)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Negatives \| Gramlyze | Future Perfect: заперечення — правила \| Gramlyze |
| Description | Future Perfect: Negatives — Future Perfect. Для заперечення в Future Perfect ми беремо will not have + V3 або коротку форму won't have + V3. | Future Perfect: заперечення. Для заперечення в Future Perfect ми беремо will not have + V3 або коротку форму won't have + V3. |

Перевірка змісту: UK hero: will not have + V3 / won't have + V3; опис зберігає повну формулу.

### 7. Питання Future Perfect

[/theory/maibutni-formy/future-perfect/future-perfect-questions](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-questions)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Questions and Short Answers \| Gramlyze | Future Perfect: питання та короткі відповіді — правила \| Gramlyze |
| Description | Future Perfect: Questions and Short Answers — Future Perfect. У питаннях з Future Perfect ми ставимо will перед підметом: Will + subject + have + V3? | Future Perfect: питання та короткі відповіді. У питаннях з Future Perfect ми ставимо will перед підметом: Will + subject + have + V3? |

Перевірка змісту: UK hero: Will + subject + have + V3; підрозділ містить питання й короткі відповіді.

### 8. Часові маркери Future Perfect

[/theory/maibutni-formy/future-perfect/future-perfect-time-expressions](http://gramlyze.loc/theory/maibutni-formy/future-perfect/future-perfect-time-expressions)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Time Expressions \| Gramlyze | Future Perfect: часові маркери — правила \| Gramlyze |
| Description | Future Perfect: Time Expressions — Future Perfect. Вирази by, by the time, before, by tomorrow evening, by next week / month / year, by then, already часто поє… | Future Perfect: часові маркери. Вирази by, by the time, before, by tomorrow evening, by next week / month / year, by then, already часто поєднуються з Future Perfect. |

Перевірка змісту: UK hero перелічує by, by the time, before та майбутні дедлайни; перелік не обрізано.

### 9. Довга назва PPC Questions

[/theory/tenses/present-perfect-continuous/present-perfect-continuous-questions](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-questions)

| Поле | До | Після |
| --- | --- | --- |
| Title | Present Perfect Continuous: Questions and Short… \| Gramlyze | Present Perfect Continuous: питання та короткі відповіді — правила \| Gramlyze |
| Description | Present Perfect Continuous: Questions and Short Answers — Present Perfect Continuous. У питаннях Present Perfect Continuous ми ставимо have / has перед підмето… | Present Perfect Continuous: питання та короткі відповіді. У питаннях Present Perfect Continuous ми ставимо have / has перед підметом, а далі беремо been + V-ing. |

Перевірка змісту: UK hero: have / has перед підметом, been + V-ing; повна назва зберігає короткі відповіді.

### 10. Narrative Tenses

[/theory/tenses/narrative-tenses](http://gramlyze.loc/theory/tenses/narrative-tenses)

| Поле | До | Після |
| --- | --- | --- |
| Title | Narrative tenses \| Gramlyze | Narrative Tenses: Past Simple, Past Continuous and Past Perfect — правила \| Gramlyze |
| Description | Narrative tenses — Часи. Use narrative tenses to make the order of past events clear in a story. | Пояснення теми «Narrative Tenses: Past Simple, Past Continuous and Past Perfect» в англійській граматиці. |

Перевірка змісту: Повна збережена назва перелічує Past Simple, Past Continuous, Past Perfect; English hero пояснює порядок минулих подій. Українського intro немає: чесний тематичний fallback, без вигаданого перекладу матеріалу.

### 11. Порівняння часів

[/theory/tenses/present-perfect-vs-present-perfect-continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous)

| Поле | До | Після |
| --- | --- | --- |
| Title | Present Perfect vs Present Perfect Continuous \| Gramlyze | Present Perfect vs Present Perfect Continuous — правила \| Gramlyze |
| Description | Present Perfect vs Present Perfect Continuous — Часи. Present Perfect usually answers what has happened or what is true now because of the action. Present Perf… | Пояснення теми «Present Perfect vs Present Perfect Continuous» в англійській граматиці. |

Перевірка змісту: Матеріал порівнює результат і процес; блоки «Швидке порівняння», «Форми обох часів», «Де акцент змінює вибір часу». Hero англійський, тому збережено явний fallback.

### 12. One / Ones

[/theory/zaimennyky-ta-vkazivni-slova/one-ones](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/one-ones)

| Поле | До | Після |
| --- | --- | --- |
| Title | One \| Gramlyze | One / Ones — Заміна іменника — правила \| Gramlyze |
| Description | Вивчайте «One» у розділі «Займенники та вказівні слова»: правила, пояснення, приклади та вправи з англійської граматики. | Пояснення теми «One / Ones — Заміна іменника» в англійській граматиці. |

Перевірка змісту: Page.title: «One / Ones — Заміна іменника»; є пояснювальні box і comparison-table, hero відсутній. Відновлено повну ідентичність SEO замість display «One».

### 13. Тест форм

[/test/future-perfect/forms](http://gramlyze.loc/test/future-perfect/forms)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Forms and Use (Mixed A1-C2) | Future Perfect: форми та вживання — тест \| Gramlyze |
| Description | Future Perfect: Forms and Use (Mixed A1-C2) — інтерактивний тест англійської на Gramlyze. Перевірте знання, виконайте вправи та отримайте результат. | Тест «Future Perfect: форми та вживання». Тренуйте граматичні форми та їх уживання в реченнях. |

Перевірка змісту: Сервером пов’язаний із уроком форм Future Perfect через Page breadcrumbs; змішані граматичні завдання, перевірка відповіді. Конкретні питання не використовувалися як рекламний опис.

### 14. Основний Questions-тест

[/test/future-perfect/questions](http://gramlyze.loc/test/future-perfect/questions)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Questions and Short Answers (Mixed A1-C2) | Future Perfect: питання та короткі відповіді — тест \| Gramlyze |
| Description | Future Perfect: Questions and Short Answers (Mixed A1-C2) — інтерактивний тест англійської на Gramlyze. Перевірте знання, виконайте вправи та отримайте результат. | Тест «Future Perfect: питання та короткі відповіді». Тренуйте побудову питальних речень і перевіряйте свої відповіді. |

Перевірка змісту: Повна M1 HTML-ідентичність; браузер завантажив 84/84 питання. Не переплутано з /questions/questions JSON endpoint; SEO описує побудову питань.

### 15. Тест часових маркерів

[/test/future-perfect/time-expressions](http://gramlyze.loc/test/future-perfect/time-expressions)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Time Expressions (Mixed A1-C2) | Future Perfect: часові маркери — тест \| Gramlyze |
| Description | Future Perfect: Time Expressions (Mixed A1-C2) — інтерактивний тест англійської на Gramlyze. Перевірте знання, виконайте вправи та отримайте результат. | Тест «Future Perfect: часові маркери». Тренуйте вживання часових виразів у реченнях і перевіряйте свої відповіді. |

Перевірка змісту: Зв’язок із Time Expressions через Page; тренуються часові вирази, а не випадкова внутрішня назва SavedGrammarTest.

### 16. Тест заперечень

[/test/future-perfect/negatives](http://gramlyze.loc/test/future-perfect/negatives)

| Поле | До | Після |
| --- | --- | --- |
| Title | Future Perfect: Negatives (Mixed A1-C2) | Future Perfect: заперечення — тест \| Gramlyze |
| Description | Future Perfect: Negatives (Mixed A1-C2) — інтерактивний тест англійської на Gramlyze. Перевірте знання, виконайте вправи та отримайте результат. | Тест «Future Perfect: заперечення». Тренуйте утворення заперечних речень і перевіряйте свої відповіді. |

Перевірка змісту: Зв’язок із Negatives; опис конкретно про утворення заперечних речень.

### 17. Каталог курсів

[/courses](http://gramlyze.loc/courses)

| Поле | До | Після |
| --- | --- | --- |
| Title | Курси — Gramlyze | Курси англійської граматики та побудови речень \| Gramlyze |
| Description | Оберіть курс англійської на Gramlyze: Sentence Builder за рівнями A1–C2 або системний курс граматичної теорії. | Напрями навчання на Gramlyze: English Sentence Builder, курс граматичної теорії та курс за темами теорії зі змішаними тестами. |

Перевірка змісту: CourseCatalogService::courses(): три реальні напрями — Sentence Builder, Grammar Theory і theory-driven. Каталог не оголошує всі окремі курси готовими.

### 18. Курс граматичної теорії

[/courses/english-grammar-theory](http://gramlyze.loc/courses/english-grammar-theory)

| Поле | До | Після |
| --- | --- | --- |
| Title | English Grammar Theory Course | English Grammar Theory Course — курс \| Gramlyze |
| Description | English Grammar Theory Course — структурований курс англійської на Gramlyze з уроками, практичними вправами та відстеженням прогресу. | English Grammar Theory Course — послідовне вивчення граматики за сторінками теорії: уроки за темами, пов’язані тести та прогрес на цьому пристрої. |

Перевірка змісту: TheoryCourseManifestService + theory-course.blade.php + theory-course-progress.js: уроки за структурою теорії, пов’язані тести, прогрес пристрою; browser підтвердив сторінку курсу.

### 19. Курс зі змішаними тестами

[/courses/theory-driven](http://gramlyze.loc/courses/theory-driven)

| Поле | До | Після |
| --- | --- | --- |
| Title | Повний курс по теорії | Повний курс по теорії \| Gramlyze |
| Description | Повний курс по теорії — структурований курс англійської на Gramlyze з уроками, практичними вправами та відстеженням прогресу. | Повний курс по теорії — навчання за темами теорії зі змішаними тестами та покроковим відкриттям уроків. |

Перевірка змісту: TheoryDrivenCourseBuilder::COURSE_DESCRIPTION і наявний course progress/unlock: програма за сторінками теорії, mixed тести, послідовне відкриття уроків.

## Відтворення

PowerShell, cwd `D:/DEV/htdocs/gramlyze.loc`:

```powershell
$P = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$PHP = 'C:/Program Files/xampp/php/php.exe'
git fetch origin
git switch -c codex/seo-m5-page-metadata # тільки початок M5; не повторювати для продовження
& $P -B tools/diagnostics/seo-m5-metadata.py --label m5-before --workers 2
& $PHP tools/diagnostics/seo-m5-context.php --local-read-only
# До змін: додаткові capture('/theory/passive-voice') і capture('/theory/basic-grammar')
# з тим самим request_local; результат m5-before-recheck.json, перші спроби залишаються.
npm run build
$suite = @(
  'tests/Unit/PageMetadataTest.php',
  'tests/Feature/ResolvedLearningPageSeoTest.php',
  'tests/Feature/SeoRobotsTest.php',
  'tests/Feature/CanonicalUrlTest.php',
  'tests/Feature/TheoryCanonicalLessonUrlTest.php',
  'tests/Feature/TheoryInlineHtmlRenderingTest.php',
  'tests/Feature/PageLocaleContentTest.php',
  'tests/Feature/SitemapTest.php',
  'tests/Feature/SentenceBuilderPublicBrandingTest.php'
)
& $P -B tools/diagnostics/run-isolated-tests.py --php $PHP --label m5-release @suite
& $P -B -m unittest discover -s tests/diagnostics -p test_seo_m5_metadata.py -v
node --test tests/Browser/seo-m5-browser.test.cjs
# Після закриття protected-file guard:
& $P -B tools/diagnostics/seo-m5-metadata.py --label m5-accepted --workers 2
& $P -B tools/diagnostics/seo-m5-compare.py --after-label m5-accepted --after-supplement m5-courses-final
$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m5-browser.cjs m5-accepted
# Додатковий course recheck через той самий capture:
# capture("/courses/english-grammar-theory"), capture("/courses/theory-driven")
# Збережено тільки очищений JSON-масив як m5-courses-final.json.
```

Наявні labels не перезаписувати; для повторення використовувати нові. Приватні матеріали — `storage/app/seo-m5-local/` і protected runner evidence в `storage/app/seo-m2-local/`.

## Обмеження та публікація

На 43 сторінках немає українського hero intro (частина intro англійською, частина відсутня). Там збережено точний тематичний fallback із повної назви; він безпечний і відповідає темі, але менш змістовний за редакційний summary. Наприклад: Narrative Tenses, Present Perfect vs Present Perfect Continuous, One / Ones. Масовий переклад/переписування навчального контенту не виконувалися; поліпшення таких коротких описів окремими редакційними summaries залишається можливим наступним кроком. EN/PL descriptions не перероблялися масово.

Довжина title/description — діагностичний показник, не єдиний критерій якості: точний повний заголовок залишається довгим, якщо немає чесного короткого еквівалента. За [рекомендаціями Google щодо title](https://developers.google.com/search/docs/appearance/title-link) потрібно передавати зміст сторінки й уникати повторів; [meta description](https://developers.google.com/search/docs/appearance/snippet) не гарантує дослівного використання в пошуковому сніпеті. У M5 не вимірюються CTR, позиції, індексація чи польові Core Web Vitals.

GitHub CI не запускався й не видається за локальний PASS. Перевірено всі чотири чинні workflow: три smoke workflow мають push тільки для `main`, ContentOps — pull_request/main та manual dispatch. Звичайний push `codex/seo-m5-page-metadata` не запускає production deploy. Публікація тільки в робочу гілку; без PR, merge, workflow dispatch, main push та деплою.
