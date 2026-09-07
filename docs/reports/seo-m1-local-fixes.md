# M1 — локальні SEO-виправлення Gramlyze

База: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`. Реалізовано 8 вересня 2026 локально на `http://gramlyze.loc`. Production не оновлювався. Цей очищений звіт публікує кодові результати M1; виконані операції локальної БД не є частиною Git.

## Чотири виправлення

### 1. Тематичний HTML `/questions`

Router обирає `test.js.questions` із коротшим slug `future-perfect`, але контролер повертає навчальний fallback `future-perfect/questions`. Раніше middleware застосовували до цього HTML технічний noindex і canonical без кінцевого `/questions`.

Після успішного визначення навчального HTML [контролер](../../app/Http/Controllers/GrammarTestController.php) записує типізований серверний [ResolvedHtmlTest](../../app/Support/ResolvedHtmlTest.php). [Canonical middleware](../../app/Http/Middleware/AddCanonicalUrl.php) і [robots middleware](../../app/Http/Middleware/ApplySeoRobots.php) враховують цей контекст тільки для відповідного route, GET/HEAD, HTTP 200 та HTML. Query не може створити цей контекст.

- `/test/future-perfect/questions`: HTML 200, один canonical з повним path.
- У production-профілі локального Laravel kernel немає випадкового noindex в meta/header.
- На `.loc` навмисний development noindex збережений.
- Accept `application/json` для неіснуючого батьківського тесту залишається 404, не JSON-заглушкою.
- Реальний `/test/future-perfect/questions/questions?mode=saved-test-js-v2` — JSON 200 із технічним noindex.
- [ApplySiteMode](../../app/Http/Middleware/ApplySiteMode.php) зберігає `Vary: Host` і додає `Accept` для negotiated route.
- Перевірені source query, інша тема, звичайний тест, step, невідомий slug, spoof query та перемикання Accept.

Локальний банк залишився незмінним: 84 питання; SHA-256 нормалізованої структури до/після `47f7cf6c04b9ddad174749eacab6aa5aa6863d1f80438e49c2f6811e0e2cf101`. Питання, відповіді й рівні не редагувалися.

### 2. Службові вставки Passive Voice

На `/theory/passive-voice/theory-passive-voice-formation-rules` знайдено три конкретні fixture-блоки, які перекривали заголовок і додавали службовий текст до справжнього уроку.

[Адресний сервіс](../../app/Services/PassiveVoiceDebugContentRepair.php) та [artisan-команда](../../app/Console/Commands/RepairPassiveVoiceDebugContent.php) перевіряють стабільні UUID, власника, старі значення, канонічне джерело, зв’язки та локальне підключення. Є default dry-run, backup-before-write, транзакція, no-op повтор і guarded restore. Ручні відмінності не перезаписуються.

У M1 **тільки локально** видалені 3 debug-блоки й 7 їхніх tag-pivots; усі 28 навчальних блоків і Page metadata збережені. Backup залишений поза Git. Повторне apply не змінювало дані; restore перевірено без застосування. У M2 повторне apply заборонене і не потрібне.

Видимий заголовок тепер «Пасивний стан (Passive Voice)» з канонічного subtitle; збережений Page.title «Formation Rules — Правила утворення пасиву» не переписувався. Урок залишається публічним і в sitemap. Канонічний definition уже був чистим. [Fixture-тест](../../tests/Feature/PageV3UnseedFolderCommandTest.php) тепер відмовляється змінювати schema поза ізольованим testing/SQLite memory і не зачіпає спільні файлові звіти чи question snapshots. Історичний шлях потрапляння fixture-даних на production не доведено.

### 3. Буквальні HTML-теги

Локально підтвердилися всі 12 випадків: Verb Patterns; Verb to Be Future; Choosing the Right Future Form; Future Perfect Continuous Forms; Collective Nouns; One/Ones; Reciprocal Pronouns; Past Perfect Forms; Past Perfect Continuous Forms; Present Perfect Forms; Present Perfect vs Past Simple; Used to/Would. Історично браузером були підтверджені Present Perfect Forms і Collective Nouns, решта 10 — HTTP-кандидати.

Rich-text поля `subtitle`, `wrong`, `rows[].en/ua`, `examples[].en/ua`, `hero.rules[].example` помилково друкувалися як plain text. [TheoryInlineHtml](../../app/Support/TheoryInlineHtml.php) будує новий DOM із allowlist inline-тегів: script/foreign content, events, styles та URL-атрибути не переносяться. Звичайні назви/labels залишилися escaped. Навмисні encoded приклади коду не активуються.

Обробка застосована в shared [blocks-v3](../../resources/views/engram/theory/blocks-v3/), [lesson-rule-cards](../../resources/views/engram/theory/widgets/lesson-rule-cards.blade.php), [theory show](../../resources/views/theory/show.blade.php), [category description](../../resources/views/theory/partials/category-description.blade.php) і [course content](../../resources/views/courses/partials/theory-page-content.blade.php). Немає глобального raw Blade, regex-санітайзера, `strip_tags` як санітайзера або JS-маскування.

У M1 перевірені всі 12 theory pages на desktop та п’ять сімейств renderer на mobile і в курсі desktop/mobile. Після — нуль буквальних strong/span у перевірених блоках. Unit/rendering тести перевіряють XSS, лапки, амперсанди, `<`/`>`, український текст і форматування. Контент БД для цієї правки не змінювався.

### 4. Canonical курсових копій

Тільки route `courses.theory.lesson` отримує canonical відповідної теорії через вже вирішений Page/lesson.theory_url із перевіркою page_id. Вкладені категорії та налаштований SEO-origin збережені. Другого canonical, редиректу чи нового noindex немає.

Дві реальні пари Parts of Speech і Sentence Types: theory/course 200, спільний canonical на theory path. Каталог курсів, головна курсу, lesson test і progress endpoint зберігають свої політики. Курсова навігація та browser-local прогрес перевірені. Теорія залишається в sitemap, курсові копії не додані.

## Перевірки: історичні та повторні

| Запуск | Результат |
|---|---|
| Історичний M1 | 75 tests / 688 assertions, 35.257 s; PHP 8.2.12 / PHPUnit 10.5.63 |
| Новий повтор перед M1 commit у M2 | **75 tests / 688 assertions, 27.666 s**, exit 0, SQLite `:memory:`, array cache/session, окремі bootstrap/view paths, тимчасовий тестовий ключ |

Повтор охопив `TheoryInlineHtmlTest`, `TheoryInlineHtmlRenderingTest`, `PassiveVoiceDebugContentRepairTest`, `PageV3UnseedFolderCommandTest`, `ResolvedLearningPageSeoTest`, `SeoRobotsTest`, `CanonicalUrlTest`, `SiteModeTest`, `TheoryCanonicalLessonUrlTest`. Новий відтворюваний runner і точні M2 команди наведені в [звіті M2](seo-m2-local-stability.md).

Історичні локальні HTTP 500 / MissingAppKeyException та `ERR_ABORTED` на save не вважаються усуненими цими SEO-правками; їхній окремий аналіз — M2. Сам факт `.com` у canonical/sitemap/JSON-LD є SEO-метаданими, не зовнішнім запитом. Production SHA та індексація Google не перевірялися.

## Межі публікації

У Git — 12 змінених source/test файлів, 8 нових source/test файлів і цей очищений звіт. Runtime caches, `.env`, SQL, archives, raw logs, cookies/CSRF, snapshots сесій, локальний backup і сторонні PPC-аудити не включаються.

Для майбутнього production потрібен окремий дозвіл на деплой коду та окрема перевірка/backup фактичних production даних перед адресним очищенням Passive Voice. Локальні DB-операції не «запушені».
