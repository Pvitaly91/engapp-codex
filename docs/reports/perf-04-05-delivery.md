# PERF-DELIVERY-02 — checkpoint PERF-04 + PERF-05

## База та межі

11.11.2026. Репозиторій Pvitaly91/engapp-codex. База на GitHub: гілка codex/perf-theory-menu-html-cache, коміт f345eb405bc6fd3497db243af1bbb86b7d27df81. Історія PERF-01/02/03 включає d4c5df288998faaf18a751acde5f688cfa51c0cd і 9337430efec58f310dc19c0ec8eb2937eb528202. Їх збережено без переписування. Фактичний WT із перевіреним пакетом — codex/perf-mixed-question-preparation на тому самому HEAD; згадана в попередніх матеріалах codex/perf-theory-lesson-rendering лишилася локальною гілкою на базі.

Для доставки створено окремий чистий worktree і нову гілку codex/perf-04-05-checkpoint. Перед перенесенням цільового ref не було ні локально, ні на GitHub; віддалена база не змінилася. Served ROOT залишився на 41820a2bebdf69004fa7209a2a38457f93efabbd і мав сторонні локальні зміни, які не переносили. Приватна побайтова копія містить потрібні tracked та untracked файли, окремі WT/ROOT manifest і Git status, включно із записами про відсутність, і tracked binary diff. У початковому WT усі вісім змін залишено на місці, staging не змінювали. Початкова інсталяція не була джерелом додаткових файлів для checkpoint.

## Що входить у коміт

Усі вісім рядків нижче входять до кандидата. Позначка «збіг» у ROOT означає той самий SHA-256 і розмір, «немає» — файла там не було. Цей delivery report — дев'ятий, новостворений файл. Шість із восьми попередніх файлів були untracked.

| Етап | Файл | Git до перенесення | WT, B | SHA-256 у WT і staged blob | Served ROOT |
|---|---|---|---:|---|---|
| PERF-04 | app/Support/TheoryPracticeCompactProjection.php | tracked M | 9676 | e755708c45595e51a791d2fc4e6c804e42087121bc32b496285336b4d2d326a2 | Збіг |
| PERF-04 | tests/Unit/TheoryPracticeCompactOwnerGateTest.php | untracked | 11224 | a5ecdbfde3435b58b4263fdc563609da52bcf33af94b11efc58641223918488e | Немає |
| PERF-04 | docs/reports/perf-theory-lesson-rendering.md | untracked | 39169 | 77df5559d1c17f67e3191279e6e435c29743f321a7ef2dd1e6214a5433a42e29 | Немає |
| PERF-05 | app/Services/GrammarTestFilterService.php | tracked M | 32456 | 9a2ab8e9bad960eed09bc660c95cf5680476d3babfc7a71bab77ae00a08d0d67 | Збіг |
| PERF-05 | tests/Feature/GrammarTestQuestionPreparationTest.php | untracked | 19406 | 338fddb51a351ac595b7c28c3ad5fa47d14e42afa163624ad280c9e4cd3acc53 | Немає |
| PERF-05 | tests/Fixtures/perf-mixed-question-preparation-before.v1.json | untracked | 2988 | baef2da915f31bdff4c947dc0fe27d944b8ecb7834d91f4119513f7dc9629a9d | Немає |
| PERF-05 | docs/reports/perf-mixed-question-preparation.md | untracked | 72179 | d9bed5ab9392e620ac01b7f70457608d9e10ed4b3baddcaff6169b9211822c3a | Немає |
| Приймання PERF-05 | docs/reports/perf-05-category-timeout.md | untracked | 27780 | c3d1e4f8f05c96f6778010ad048247e640a73eab1e2376ca4c3bd76825134982 | Немає |

Успадковано без змін: PHP/Blade/JS HTML-кешу меню, desktop SSR, lazy mobile зі збереженою ревізією, локалізацію та fallback; request-scoped M42 memo із перевіркою файлів у новому запиті; finite catalog dispatch з integrity guards. PERF-04 зберігає перевірку закріпленого SHA перед відсівом чужого набору та повну валідацію власного. PERF-05 використовує спільний шлях добору, але готовий тест не готує невикористані довідники; prepare() для конструктора зберігає попередній контракт. Selection, randomization, variants, оцінювання й збереження прогресу не змінювали під час доставки.

Поза комітом: .env, ключі, дампи, сесії, прогрес, профілі браузера, raw receipts/HAR, копії відновлення, profiling routes/headers, згенеровані HTML меню й активні runtime manifests, vendor/node_modules, build-виходи, кеші/журнали та сторонні файли. Незалежний BEFORE JSON є синтетичним versioned fixture, а не дампом БД.

## Перевірка чистого checkout

Дерево восьми протестованих файлів поверх бази: e5e08962b767dc24ba12f4800afbf55d61912e10. Перед тестами звірено 64 454 звичайні файли з Git index. Managed checkout Windows спочатку матеріалізував 50 635 успадкованих файлів із CRLF; у новому checkout відновлено лише точні blob-байти після перевірки, що різниця тільки у закінченнях рядків. Базові blobs, Git-конфігурацію та інші worktrees не змінювали. Обидва lock-файли збігаються з базою; кожен із восьми staged blobs збігається з приватною побайтовою копією. У фінальне дерево додається тільки цей Markdown-звіт.

Composer: 122 пакети з lock, офлайн, без plugins/scripts, exit 0. npm ci: 192 пакети з lock, офлайн, без scripts, exit 0. Без оновлення залежностей. Composer повідомив про 1415 наявних PSR-4 невідповідностей сідерів; npm — deprecated whatwg-encoding. PHP 8.5.10: syntax 4/4. Окремо звірено 9 936 записів classmap, 40 autoload includes, 108 PSR locations і Reflection для 13 ключових класів. Три відсутні опційні lookup-каталоги належать пакету Pint; фактичні PHP-файли, потрібні застосунку й тестам, розв'язуються всередині нового checkout. Немає випадкового autoload зі старого WT або served ROOT.

Тести запускали без робочої .env, із SQLite :memory:, ізольованим storage/cache і array sessions. Власні тестові артефакти залишили приватними. Production Vite build виконано на джерелах кандидата, у приватний каталог, щоб не перезаписувати успадковані Git-артефакти public/build.

Свіжий запуск: PHP 8.5.10, PHPUnit 12.5.35. Кількість виконань враховує data providers; набори частково перетинаються.

| Набір | Методи / виконання | Assertions | Failures / errors / skips | Warnings / deprecations | Exit |
|---|---:|---:|---:|---:|---:|
| PERF-05: filter service і незалежний BEFORE | 10 / 10 | 608 | 0 / 0 / 0 | 0 / 1 | 0 |
| PERF-04 + menu/M42/catalog | 30 / 32 | 21 966 | 0 / 0 / 0 | 0 / 1 | 0 |
| Ширша PHP-регресія | 41 / 53 | 43 730 | 5 / 1 / 0 | 0 / 4 | 2 |
| Node: menu snapshot і search ranking | 12 | — | 0 / 0 / 0 | 0 / 0 | 0 |
| Vitest: theory sidebar layout | 7 | — | 0 / 0 / 0 | 0 / 0 | 0 |

У 10 тестах PERF-05 перевірено 21 сценарій зі збереженим незалежним BEFORE; це не 31 тест. Core підтвердив генератор і reader меню, desktop/mobile стан, M42 request-scoped integrity, finite dispatch із guards та повну валідацію власного набору PERF-04. Результат Composer і PHP lint не підміняють ці перевірки. 23 власні ізольовані test runtimes винесено з checkout у приватний каталог. Vite 5.4.19 production build: 57 модулів, 4 assets і manifest, exit 0; попередження про застарілі Browserslist data та зовнішній приватний outDir. Build-виходи не додано у Git.

Усі 53 broad cases і всі шість відхилень збіглися з попереднім прийманням за методом, failure/error kind, типом винятку й основними повідомленнями. Нових відхилень немає. Тип перших п'яти — PHPUnit\Framework\ExpectationFailedException:

| Метод | Підтверджене історичне відхилення |
|---|---|
| MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level | Expected 302, received 404 |
| MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2 | Expected 302, received 404 |
| MixedTheoryPageTestRenderTest::test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes | Expected 302, received 404 |
| QuestionsNegationsQualityRuntimeTest::test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices | Actual size 5, expected 4 |
| SavedTestJsStateTest::test_it_fetches_questions_for_filter_based_saved_test | Expected 200, received 500; SQLite fixture: no such table: question_hints |
| SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test | Illuminate\Database\QueryException: SQLite :memory:, no such table: question_hints |

До коміту staged allowlist, точні blob-байти, SHA-256 джерел, whitespace та відсутність credential patterns звірено; final staged tree відрізняється від протестованого лише цим звітом. Готовий checkpoint не залежить від незакомічених файлів початкового WT.

## Попередні локальні вимірювання та приймання

Ці результати не є вимірюваннями з доставки. Нову HTTP/benchmark-серію не запускали; цифри не прогнозують продуктивність production.

| Етап / сценарій | Медіана до → після | Швидші пари |
|---|---:|---:|
| PERF-04, урок Word Order | 3,25 → 2,34 с | 20/20 |
| PERF-04, урок Past Perfect Continuous | 2,68 → 1,91 с | 20/20 |
| PERF-05, новий Word Order dataset | 2,64 → 1,95 с | 20/20 |
| PERF-05, повторне відкриття Word Order | 1,23 → 0,53 с | 4/4 |

HTML і дані вправ PERF-04 незмінні. Попередні профілі PERF-05: 173 → 149 завершених SQL для нового набору, 68 → 44 для повторного. Незалежний BEFORE підтвердив еквівалентність добору та payload; Present Simple Passive перевірено окремо. Початкову нестабільну PERF-05 серію збережено, причина стрибка невстановлена. Серверне відновлення підтверджено окремо на обох тестах. Останній локальний HTTP smoke — 5/5.

**Браузерне приймання PERF-05: C.** Автоматизація Opera заблокована перевіркою URL; усі сім ручних сценаріїв позначено «не перевірено вручну». HTTP, серверне відновлення та PHP/JS не підміняють браузерну взаємодію. Історичний таймаут Word Order: **NOT REPRODUCED / CAUSE UNRESOLVED**. Деталі в [PERF-04](perf-theory-lesson-rendering.md), [PERF-05](perf-mixed-question-preparation.md) та [звіті про таймаут і приймання](perf-05-category-timeout.md).

**Production readiness: NOT APPROVED.** main, сервери, контентна БД й особистий прогрес не є цілями доставки й не змінювалися. Деплою та PERF-06 немає. Ціль нормального push — лише codex/perf-04-05-checkpoint.

## Відновлення меню

Команда, генератор, reader, CSS/JS і шаблони desktop/mobile присутні в Git. Для потрібного середовища з коректним APP_URL див. [документацію](../theory-menu-cache.md):

    php artisan theory:menu-build
    php artisan theory:menu-build --check
