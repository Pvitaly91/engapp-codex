# M17 — Academic English: hedging, stance та evaluation

Дата: 2026-09-27. Пакет реалізовано локально для Gramlyze; production-перевірки та деплой не входять до завдання.

## База та межі

Прочитані AGENTS.md, звіти M16 і M12 styling. Після fetch підтверджено прийняту базу M16 `aec0f244e44936683987ba159bc9f59438efd145` і відповідність origin/codex/seo-m16-formal-register-content. M17 продовжує цю базу у гілці `codex/seo-m17-hedging-stance-content`.

Повторно використано вільний worktree `storage/app/seo-m11-worktree`. Основний checkout на `41820a2bebdf69004fa7209a2a38457f93efabbd` / `codex/production-ready-a14788dac` мав сторонні незавершені зміни; його не перемикали й не скидали. Старі 18 уроків M11–M16, manifests/allowlists, renderer, CSS та алгоритми тестів збережено.

Before робочої БД збігався з трьома Git-definitions за Page.seeder, slug, ancestry, locale, subtitle/hero/box, IDs/UUIDs, порядком і зв’язками. Ручних конфліктів цільового контенту не знайдено. APP_ENV=production, .env, ключ і підключення не змінювалися.

## Три цілі й реальна навігація

Повний префікс identities: `Database\Seeders\Page_V3\AcademicEnglish\`. Definition кожного уроку лежить у `database/seeders/Page_V3/AcademicEnglish/<Seeder>/definition.json`. Назви Page.title та фактичні controller-derived H1 збережено точно.

| Рівень / Seeder | Теорія | Основний тест |
| --- | --- | --- |
| B2 — HedgingAndCautiousLanguageBasicsTheorySeeder | [Hedging and Cautious Language Basics](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language-basics) | [Тест B2](http://gramlyze.loc/test/academic-english/hedging-and-cautious-language-basics) |
| C1 — HedgingAndCautiousLanguageTheorySeeder | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | [Тест C1](http://gramlyze.loc/test/academic-english/hedging-and-cautious-language) |
| C2 — StanceRegisterAndEvaluationTheorySeeder | [Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) | [Тест C2](http://gramlyze.loc/test/academic-english/stance-register-and-evaluation) |

Рівні в першій колонці — рівні теорії; назви наявних mixed-тестів із позначенням A1–C2 не змінювалися. Тести перевірялися як переходи, не як нові question banks.

Курсова адреса, підтверджена resolver: [копія Basics у курсі](http://gramlyze.loc/courses/english-grammar-theory/lesson/academic-english/hedging-and-cautious-language-basics).

Read-only сусіди та доречні посилання:

- [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase) — точність змісту при зміні регістру.
- [Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) — фокус через іменник без втрати виконавця/статусу.
- [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) — логічні переходи.
- [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) — лише короткі наявні опорні тези. Це не четверта ціль і не обіцяний повний курс аргументації.

## Початкові недоліки та зміни

До M17 кожна ціль мала короткий англомовний hero із трьома anchors і службовий box замість розгорнутого уроку. Самоперевірок не було. Правильні вихідні теми збережено, але broad rules уточнено окремо від нового матеріалу:

- B2: старий список probably/possibly/generally/relatively не пояснював різних функцій. Тепер можливість, типовість, тенденція й відносний ступінь розведені; немає вигаданих відсотків. Seem/appear/tend to мають моделі керування; suggest «вказувати» відрізняється від «пропонувати».
- C1: seem/suggest/indicate не оголошуються універсальними пом’якшувачами. Indicate може прямо описувати те, що показують записи; prove заявляє сильніший висновок. Would appear розібрано як поєднання modal + verb, не окреме просте модальне дієслово.
- C2: оцінка потребує критерію й меж, а не лише слова significant або формального тону. Статистичну значущість без даних не стверджуємо; reporting verbs не підміняють силу позиції джерела.

Новий авторський матеріал:

| Урок | Навчальна мета й нові опори |
| --- | --- |
| B2 | Відрізняти факт, можливе пояснення, тенденцію та порівняння. Таблиця чотирьох колонок; модальні форми, seem/appear, tend to, suggest; прохання проти припущення; шість практичних рішень. |
| C1 | Обмежувати висновок групою/методом/умовами. Вигаданий каталог: 8 добровольців, незмінний порядок, 7 швидших результатів. Відомий результат не хеджується; причина лишається непевною. Not necessarily, may not, відсутність доказів, result vs recommendation; редагування абзацу. |
| C2 | Відділяти опис, критерій оцінки, припущення й рекомендацію. Контрольний список без позначок версії; точні promising/limited/preliminary/inconclusive/convincing; умовна нотатка А; авторський мініогляд і конструктивна критика методу. |

Короткі before/after за змістом:

- «Use cautious adverbs when the claim is not absolute» → generally описує типовість, relatively потребує зрозумілого орієнтира, possibly — можливість.
- Невизначене «may be related to the wider context» → «Seven of the eight volunteers completed the second search faster» + окреме можливе пояснення + конкретний confound повторного проходження.
- Непояснене «significant but limited improvement» → для одного пошуку 2 екрани замість 4; перевага за цим критерієм не доводить швидкості, точності чи загальної ефективності.

Усі приклади й вправи написано для цього пакета, не скопійовано з зовнішніх статей. Вигадані ситуації явно позначені; немає вигаданих реальних досліджень, експертного консенсусу чи гарантій SEO. Це 18 самоперевірок у сторінках, не 18 нових питань mixed-банку.

## Зовнішня перевірка та межі доступу

Повний доступний основний текст прочитано:

- [Manchester — Being cautious](https://www.phrasebank.manchester.ac.uk/using-cautious-language/): співвіднесення сили твердження з доказами, межі узагальнень.
- [Manchester — Being critical](https://www.phrasebank.manchester.ac.uk/being-critical/): предметна критика доказів і методу, конструктивні зауваження.
- [Monash — Express uncertainty in writing](https://www.monash.edu/student-academic-success/improve-your-academic-english/strategies-for-writing-academic-english/express-uncertainty-in-writing): обережність відповідно до контексту, уникнення надлишкових hedges.
- [Monash — Evaluative language](https://www.monash.edu/student-academic-success/improve-your-academic-english/the-language-of/adopting-a-critical-position/evaluative-language): оцінка як обґрунтоване судження, а не загальна похвала/осуд.

Редакційні рішення не є механічним копіюванням цих списків. Частота, відносний ступінь та ймовірність не переносяться в універсальну low/medium/high шкалу. Не успадковано неточні категоріальні позначки на сторінці Monash: appear to не названо простим modal verb, possible/likely після is — прикметникові предикативи, не прислівники. Стильова рекомендація не оголошена правилом для кожного жанру.

[Cambridge — Hedges](https://dictionary.cambridge.org/us/grammar/british-grammar/hedges-just): пряме відкриття завершилося помилкою інструмента; використано доступний індексований фрагмент сторінки видавця, **не повний текст**. Він допоміг звірити різницю між ввічливістю й обережним твердженням.

Конкретні значення й моделі додатково звірено за індексованими фрагментами Cambridge (не видаємо їх за повністю відкриті статті):

- [seem](https://dictionary.cambridge.org/grammar/british-grammar/seem), [appear](https://dictionary.cambridge.org/dictionary/english/appear), [tend](https://dictionary.cambridge.org/dictionary/learner-english/tend), [suggest](https://dictionary.cambridge.org/us/dictionary/english/suggest), [indicate](https://dictionary.cambridge.org/dictionary/english/indicate);
- [possibly](https://dictionary.cambridge.org/us/dictionary/english/possibly), [probably](https://dictionary.cambridge.org/us/dictionary/english/probably), [generally](https://dictionary.cambridge.org/dictionary/english/generally), [relatively](https://dictionary.cambridge.org/us/dictionary/english/relatively);
- [may](https://dictionary.cambridge.org/grammar/british-grammar/may), [necessarily](https://dictionary.cambridge.org/us/dictionary/english/necessarily), [інші дієслова з модальним значенням](https://dictionary.cambridge.org/grammar/british-grammar/modality-other-verbs);
- [promising](https://dictionary.cambridge.org/us/dictionary/english/promising), [limited](https://dictionary.cambridge.org/dictionary/english/limited), [preliminary](https://dictionary.cambridge.org/dictionary/english/preliminary), [inconclusive](https://dictionary.cambridge.org/dictionary/english/inconclusive), [convincing](https://dictionary.cambridge.org/us/dictionary/english/convincing);
- [report](https://dictionary.cambridge.org/dictionary/english/report), [admit](https://dictionary.cambridge.org/dictionary/english/admit), [prove](https://dictionary.cambridge.org/dictionary/learner-english/prove).

Прямі спроби відкрити probably/tend/suggest/indicate отримали HTTP 403. Захист не обходився. Учнівські коментарі, сторонні навчальні перекази й корпусні приклади не використовувалися як правила редакції; зовнішні вправи не переносилися.

## Окрема редакторська перевірка 18 ключів

Це змістовний перегляд, окремий від assertions кількості/унікальності. Перевірені граматика, природність, переклад, відомі факти, модальність, заперечення й альтернативи.

| № | Завдання | Що перевірено в ключі |
| --- | --- | --- |
| B2.1 | Мокрий килим | Стан видимий; дощ — лише причина-кандидат. Прибирання назване альтернативою, не новим фактом. |
| B2.2 | Принтер: may/seem | May need, seems to need; узгодження однини. Іншу природну форму відрізнено від невиконання заданої моделі. |
| B2.3 | Could / generally про ліфт | Можливий стан зараз проти типової роботи. Generally не гарантує теперішню справність. |
| B2.4 | Relatively і два маршрути | Явний орієнтир 20 проти 45 хвилин, межа однієї поїздки, без always. Альтернативні природні редакції дозволені. |
| B2.5 | Might needs repair | Прибрано -s після modal; це граматична помилка, не варіант стилю. |
| B2.6 | Лампа після заміни батарейки | Послідовність відома; cause не доведено. Пояснено may have caused і простішу альтернативу could explain. |
| C1.1 | Час одного добровольця / допомога підписів | Пряме спостереження відділене від можливої причини. |
| C1.2 | Everyone → задана група | Збережено 7 із 8, другий пошук і confound порядку; не підмінено causation. |
| C1.3 | Not necessarily і зрозумілість | Заперечено неминучість висновку, не будь-яку можливість покращення. |
| C1.4 | May not → do not | Обидві форми граматичні, сила різна; початковий прогноз не є забороною. |
| C1.5 | Вказівники та черги | Збережено нові знаки й менше відвідувачів; жодну причину не проголошено доведеною. |
| C1.6 | Надмірно хеджований абзац | Прибрано непевність відомого результату; збережено можливі причини й конкретний порядок. |
| C2.1 | Посібник без покажчика | Опис / оцінка за критерієм швидкого пошуку / рекомендація. Не оголошено повної непридатності. |
| C2.2 | Inconclusive / false | Неможливість обрати швидшу версію не дорівнює хибності даних або доведеній рівності всюди. |
| C2.3 | Оцінка глосарія | Критерій — доступ до прикладів, не вигаданий навчальний ефект. |
| C2.4 | Умовне джерело Б | Збережено may та not tested; suggests/states/writes допустимі за точного змісту. Proves/admit додали б невідоме. |
| C2.5 | Відсутня дата та вибір користувачів | Факт описано прямо, наслідок — як можливий. Кожному користувачу не приписано гарантованої помилки. |
| C2.6 | Два аудіогіди | Перевага навігації спирається на функцію, межа — один пристрій. Інші сумісність/якість/успішність не вигадуються. |

Відкриті завдання мають приклад і критерії, не одну обов’язкову фразу. Завдання різні між рівнями; вони не є перестановкою імен у тій самій вправі.

## Фактичне локальне застосування

Використано попередній LinkingWordsContentPatch/M11LocalTargetGuard без зміни їхніх форматів і політик. Додані тільки вузькі M17 wrappers, before-manifest трьох повних identities, CLI-адаптер worktree sources → working app і read-only acceptance profile.

Guard підтвердив Windows MySQL, loopback DNS, Apache/vhost, PID/listener та nonce-bound відповідність web/CLI. Початкова sandbox-спроба не змогла прочитати Windows process evidence: guard зупинив роботу до запису. Повтор дозволено лише для потрібної перевірки; policy не послаблювалася.

Послідовність: fresh proof → final preview → перевірка scope → exclusive record-backup → transactional apply/postcondition → повторний no-op.

- Фінальний preview: `m17-hedging-stance-v1`, 12 змін; digest `ffffa3a9a1307efbb6f751edba62bda50f803131dbe9775f568d6c838ba2454b`.
- Apply: `status=applied, updated=12`.
- Повтор: `status=no-op, updated=0`; зайвий backup не створено.
- Фінальний приватний backup збережено: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m17-local/records-before-table-final.json` (3 975 544 байти). Це record-backup пакета, не новий повний дамп БД; до Git він не входить.

Перший прохід автоматичних/browser перевірок пройшов, але перегляд screenshots виявив поділ слів у надто вузькій першій колонці B2. Через штатний guarded record-restore повернуто тільки 12 записів M17; після уточнення мінімальної ширини перших колонок B2/C1 виконано свіжий proof/preview, новий exclusive backup, apply 12 і no-op 0. Попередні plan/backup/screenshots збережено приватно. Фінальна різниця з початковою БД — ті самі 12 записів, не 24. Renderer/CSS не змінювалися.

| Page ID | Subtitle / hero / box IDs |
| --- | --- |
| 293 | 8760 / 8763 / 8766 |
| 298 | 8781 / 8782 / 8783 |
| 314 | 8829 / 8830 / 8831 |

Змінено тільки pages.text та text_blocks.heading/body. IDs/UUIDs/owners/order, tags/pivots, переклади й test relations лишаються захищеними. Застосування не змінює APP_ENV і не запускається через HTTP/startup/hooks. Робочих сідерів, міграцій, truncate, масових UPDATE, full restore або cache/session clearing не було.

## Автоматичні перевірки

- HedgingStanceContentPackageTest, HedgingStanceContentPatchTest, M11LocalTargetGuardTest: фінально **41 тест, 692 assertions, exit 0** (початковий прохід: 672 assertions). Додано захист ширини перших колонок і порівняння вправ з усіма 18 попередніми уроками, включно зі старими sections без data-атрибутів.
- Ізоляція: SQLite in-memory, окремі runtime paths; production-профіль SEO — лише in-memory requests, не production HTTP.
- Node-контракти seo-m17-local.test.cjs: **8/8**.
- Pint: шість нових PHP-файлів — passed.
- Перевірено JSON/hero JSON, rich opt-in, 18 завдань/ключів, controller-derived H1/title/meta/OG/Twitter, real resolvers, immutable identities, manual/stale conflicts, exclusive backup, transaction/restore rollback, later-edit safety і відмову непідтвердженій цілі.
- PHPUnit повідомляє одну deprecation середовища PHP 8.5; це не падіння тестів. Конфігурацію/залежності поза scope не змінювали.

## Живе приймання та збереження даних

### HTTP та renderer

Before: 19:57:03 UTC; final: 20:24:09 UTC. Фіксований набір із **12/12 HTTP 200**: три теорії, три основні тести, курсова копія, два контролі, два додаткові сусіди та sitemap. GET без fixture-підміни.

Новий матеріал є в початковому HTML справжнього gramlyze.loc. У кожному уроці 6 завдань, 6 пояснених ключів і 8 rich-секцій. Rich opt-in підтверджений перед записом і в live DOM; plain fallback немає. Англійські приклади й переклади оформлено чинними картками; службовий anchor box прибрано. H1 перевірено через справжній controller, не лише bare Blade.

### Браузер

Chromium 147.0.7727.15. **6/6 основних сценаріїв**: 3 уроки × desktop 1440×1000/mobile 390×844, кожен у світлій і темній темі. Кожний сценарій має новий browser context і reload. Перевірено контент нижче першого екрана, збіг server/reload/DOM content hashes, нумерацію, keys, details мишею й Enter/Space, відсутність document overflow та три штатні переходи до тестів.

Контролі Register Tone and Paraphrase (M16) та Advanced Linking Devices — **4/4 desktop/mobile**, світла/темна тема. Контент і метадані контрольних сторінок збережено.

Мобільні таблиці мають контейнер 322 px і реальний local scroll:

| Урок | Content width | Досягнутий scrollLeft | Захист читабельності |
| --- | --- | --- | --- |
| B2 | 880 px | 558 px | Перша колонка не менше 130 px; possibly/relatively не розбиті на фрагменти. |
| C1 | 800 px | 478 px | Перша колонка не менше 170 px; назви шарів тексту читаються цілими. |
| C2 | 780 px | 458 px | Довші значення не стискаються до вузьких колонок. |

Збережено й візуально переглянуто приватні screenshots: мобільні таблиці зліва/справа, вступні desktop-екрани, довгі англійські приклади, відкриті ключі у світлій/темній темах, контролі та курсова копія. Початкові скриншоти з недоліком збережено окремо від accepted-final.

Курсова копія: HTTP 200, правильний H1, **штатний gate «Урок заблоковано»** для нового гостя. Gate не обходили. Серверний HTML містить 6 завдань/ключів; спільний course resolver, blocks та рендер додатково перевірені ізольовано. Відкритий навчальний контент курсу для гостя live не приймався.

Application pageerror — 0. Окремо від збоїв застосунку:

- Google Fonts: 15 невдалих запитів у фінальному основному проході та 10 у контрольному, ERR_NETWORK_ACCESS_DENIED; візуальне приймання виконано з fallback-шрифтами.
- Автоматичні test state POST: 3 + 2 навмисно заблоковані read-only runner як stateful-request. Їхні ERR_FAILED не означають збій застосунку; відповіді/прогрес не записувалися.

### Метадані та захищені дані

Page.title, controller-derived H1, HTML title, canonical, meta robots, X-Robots-Tag і основні test href незмінні. Локальний заголовок лишився noindex, nofollow, noarchive; meta robots відсутній як і before. .com у canonical/sitemap аналізувався лише як рядок.

Лише три цільові descriptions змінилися через український intro й чинний PageMetadata; meta/OG/Twitter збігаються, назва не повторюється. Описи тестів, курсу та чотирьох сусідів незмінні.

Ordered sitemap: фактично **554 URL**, порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334` незмінні. Число спостережене, не hardcode критерію.

Read-only fingerprints усіх **46 таблиць**, за винятком лише дозволених полів 12 записів, збігаються before/after/commit-ready. Усі **18 прийнятих уроків M11–M16** досі збігаються зі своїми sources. Questions/answers/options, verb_hint, relations, progress та інші дані не змінені. .env зберіг початковий hash.

Тимчасовий read-only proof route прибрано: GET **404**. `routes/api.php` відновлено побайтно до початкового стану (SHA-256 `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522`), сторонні зміни збережено. Приватні докази лишилися поза Git.

## Межі завершення

Не запускали весь repository suite, crawl, M10, Lighthouse чи performance-кампанію. Build inputs не змінювалися — rebuild/оновлення залежностей не потрібні. Mixed-банки, перевірка відповідей і прогрес поза scope.

До Git включено лише три definitions, before-manifest, мінімальні M17 wrappers/адаптер/профіль, цільові тести й цей звіт. .env, vendor/build, runtime caches, приватні proofs/backup/screenshots та сторонні зміни виключені. Звичайний push — тільки codex/seo-m17-hedging-stance-content; SHA і результат remote-звірки наведено у фінальній відповіді. PR, merge, push main, workflow dispatch і деплой не виконувалися.

**Production не перевірявся й не змінювався.**
