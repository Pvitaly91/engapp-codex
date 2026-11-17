# PERF-DELIVERY-03 — checkpoint прийнятих PERF-06 та PERF-10

## База і межі

17.11.2026, репозиторій `Pvitaly91/engapp-codex`. GitHub `refs/heads/codex/perf-04-05-checkpoint` підтверджено read-only запитом на `8e48c44f34c117a637e20e1f1d93e91ebfdb1b89`. Відомі предки PERF-01–05 `d4c5df288998faaf18a751acde5f688cfa51c0cd`, `9337430efec58f310dc19c0ec8eb2937eb528202` і `f345eb405bc6fd3497db243af1bbb86b7d27df81` справді є предками checkpoint. Ціль `codex/perf-06-10-checkpoint` не існувала у локальних refs та на origin перед створенням. `main` лише перевірено; merge/rebase/amend/force push/workflow dispatch/deploy не належать до цієї доставки.

| Копія | Гілка / початкова позиція | Роль |
|---|---|---|
| Source WT `perf-delivery-02/gramlyze.loc` | `codex/perf-conditionals-widget-membership` / `8e48c44f…b1b89` | Перевірені tracked **і** untracked зміни, джерело пакета; залишені на місці |
| Окремий served ROOT `gramlyze.loc` | `codex/production-ready-a14788dac` / `41820a2b…fbd` | Apache читає його `public`; використаний лише для read-only звірки, не як джерело всього дерева |
| Окремий чистий WT `perf-delivery-03/gramlyze.loc` | створений від `8e48c44f…b1b89`, далі `codex/perf-06-10-checkpoint` | Рівно allowlist; власні залежності і власні тести |

До перенесення створено приватні копії точних WT/ROOT bytes, SHA-256/size/absence manifests, окремі Git status і scoped binary tracked diffs. Вони охоплюють і всі untracked files пакета. Нових оптимізацій не додано, робочий сайт, БД, навчальний контент, особистий progress та сторонні зміни не переписувалися. Кандидат Git перед додаванням цього звіту: **`8119e919acaeedc6b56a9aba9e6de95a0944dbbc`**.

## Повний allowlist

Перші 13 рядків — 4 tracked modified та 9 untracked у source WT. SHA/розмір — точні витребувані bytes WT, перенесені й порівняні зі staged blobs. Для runtime/tests це також прийняті source/test pins, для історичних звітів — незмінні документи. Усі рядки включено. Чотирнадцятий файл — цей новий delivery report, без рекурсивного власного хеша.

| Відносний шлях | PERF / призначення | Source WT | B | SHA-256 WT / прийнятого артефакту | Served ROOT |
|---|---|---|---:|---|---|
| `app/Http/Controllers/TestJsV2Controller.php` | 06 — Final JS dataset / marker batches | tracked M | 87314 | `6c7f4cc417b4b153169df418baaa5dea99f9ee8698beb113e7a96d4c23341e3f` | збіг |
| `app/Services/MarkerTheoryMatcherService.php` | 06 — Marker-tag batch service | tracked M | 20016 | `8b6e3a67946b5871d3fe7e128b253f440128f3eddb0c8bcc2705c62983d350f8` | збіг |
| `tests/Feature/PublicFlows/SeededPublicFlowTestCase.php` | 06 — Test double support for bulk calls | tracked M | 16111 | `f0906d9abba1ab1e145e61f2b68458ba40c47c95d2b2189c6ec75b2589700236` | інша версія¹ |
| `tests/Feature/MarkerTagsDatasetBatchTest.php` | 06 — Final/resumed SSR and SQL regression | untracked | 12350 | `67d5446f97b9161c9abe02e12f5df912c4890d959984c3cc4594bb6d6291bfe0` | немає |
| `tests/Unit/MarkerTheoryMatcherBatchTest.php` | 06 — Batch parity, order and multiplicity | untracked | 10915 | `a44eb156c3ac988984701fef5e50af6403a395d46e98a78b1893d215425295b3` | немає |
| `docs/reports/perf-marker-tags-batch.md` | 06 — Accepted evidence | untracked | 25472 | `3c04f7bf9a98719515d3bd983abf0394f1902c487d9bdd2e3253f9d81f8d7b1b` | немає |
| `docs/reports/perf-mixed-selected-relations.md` | 07 — Rejected late hydration | untracked | 20948 | `e63703719af2cb4ed6501fe8ecae99ce1957598ca5b0d9a4ac07dad8943e6eea` | немає |
| `docs/reports/perf-theory-request-projections.md` | 08 — Rejected request projection memo | untracked | 30076 | `2293aac5f65e499b75fbf80794cd6b189042a09dfa42aec06c6a494e0560de29` | немає |
| `docs/reports/perf-runtime-baseline-stability.md` | 09 — Baseline diagnostics only | untracked | 39326 | `8cebbe443b0db94b1f1386e5caf9653244df54e0023b3870d601fd267845bd16` | немає |
| `app/Support/ConditionalsWidgetHints.php` | 10 — Exact negative membership and embedded registry | tracked M | 11671 | `273a0ba9ffbaf2e47ad57654e6a8ed49fadd822b16c829024864dd6be95ad128` | збіг |
| `tests/Feature/ConditionalsWidgetMembershipTest.php` | 10 — Exact roster, guards and owned/full path | untracked | 30527 | `cc1476e4bc38f05c659507a60e8c54ed729f4be2045b5d311b825fd0ade7826f` | немає |
| `docs/reports/perf-conditionals-widget-membership.md` | 10 — Original experiment including rollback | untracked | 43900 | `9409e9e9e0afa773eb354974380ad4edc2b15b20996a6cb6dfebd33d2830944d` | немає |
| `docs/reports/perf-10-acceptance-followup.md` | 10A — Local acceptance and timeout follow-up | untracked | 18916 | `e4e8f4c19bec83aa3730ea479098c70783807bb7ef6546038180b0a250c343f4` | немає |

¹ У served ROOT `SeededPublicFlowTestCase.php` інший: `8ebfe2212a4b94270fadb6db21af97f5cc3e878c6b00213e2d598089bac9c2ab`; перенесено **перевірений WT**, не ROOT. Єдина зміна додає `getAllMarkerTagsForQuestions` до чинного mock, який повертає порожній набір, поряд із `getAllMarkerTags`. Не змінює assertions, статуси або fixture table; відомий `question_hints` failure не виправлено заміною mock.

- **PERF-06 активний:** пакет тегів формується лише для кінцевих IDs нової видачі, порядок і multiplicity збережені, resumed path не перечитує теги.
- **PERF-10 активний:** реєстр **у класі**, не окремий файл; pinned frozen `docs/content/conditionals-widget-hints.v1.json` уже в базі, SHA `710e283af26f52d56eb1ceb04d22d5adc66785a607e0bf727d7525ac9290859c`, 13 UUID / 65 точних пар. Існування, symlink і SHA перевіряються перед відсівом; власні та невизначені пари проходять повні попередні guards. Зняття чотирьох документованих діагностичних вставок із історичних tagged A/B повертає точний чистий A/B. Виконання підтверджено 108/108 попередніх receipts; архівний B, фінальний локальний runtime й Git blob збігаються.
- **PERF-07/08 відхилені:** 54/54 кандидатів для late hydration, request projection memo відкочено. `TheoryNativeContentRelease.php` лишається з baseline SHA `23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11`. **PERF-09** — тільки профілювання baseline. Стадії 07–09 внесено як звіти, не runtime.

Виключено .env, credentials, DB dumps, sessions/progress, browser profiles/cookies, raw receipts/HAR, recovery, probes, generated menu HTML/pointers, vendor/node_modules, кеші, логи та сторонні файли. Незалежні frozen content/fixtures уже versioned у базі. Опціональний локальний oracle точного old class/живих пар не потрібен portable тесту й не публікується.

## Чисте відтворення

Composer із lock: 122 installs / 0 updates / 0 removals, offline, `--no-plugins --no-scripts`, exit 0. Npm `ci --offline --ignore-scripts --no-audit --no-fund`: 192 встановлених packages, exit 0. Обидва lock-файли незмінні. Composer попередив про 1415 успадкованих PSR-4 невідповідностей імен сідерів. 63 опційні JS пакети для інших платформ не встановлювалися; перевірено п’ять потрібних JS imports з цієї копії. Власний Composer autoload з delivery WT: 9939 classmap entries, 40 autoload includes, 108 PSR locations, 13 Reflection checks; три відсутні опційні lookup-каталоги Pint не заважають runtime/test. Жодних посилань на автозавантаження з source WT чи served ROOT.

### Windows checkout bytes

Managed Windows checkout з `core.autocrlf=true` матеріалізував LF як CRLF для частини raw-SHA inputs. Серед 215 **цільових** path checks зберегли exact index bytes для 120 файлів (54 + 53 + 13), зокрема frozen manifests, raw definition inputs, транзитивні M46–49/linked widget artifacts, eager M77–94 bindings і точні author sources трьох задіяних каталогів. Кожний запис перед заміною перевірено на **лише** LF/CRLF, після — на SHA exact index blob. Не змінено Git config, stage, tracked blobs, frozen SHA чи решту дерева; `git add --renormalize` не застосовувався. Файли були в Git, тому source WT не є прихованою залежністю. На Windows для fresh materialization цих байтів не слід покладатися на checkout, який перетворює закінчення рядків точних артефактів.

Перший PHPUnit-прохід із ще неповним EOL набором залишено у приватних доказах: Conditionals падав на відхиленому успадкованому presenter, route smoke — на M77 binding, а Word Order/QN — на generated banks, що містять raw авторський SHA. Два Conditionals link controls тимчасово проходили через fallback замість відповідності відомому baseline; цей результат не зараховано. Після закриття саме цих транзитивних залежностей виконано повтор зачеплених груп на **тому самому** candidate tree, без runtime/test diff. PERF-05 та JS пройшли в першому проході й не повторювалися, оскільки їхні вхідні файли не змінилися.

Повний `git diff --cached --check` виявляв CR на кінці вже перевірених рядків двох PERF-06 PHP файлів. Ті CRLF bytes навмисно збережено як прийняті й перевірені; повтор `git -c core.whitespace=cr-at-eol diff --cached --check` трактує CRLF як закінчення рядка, залишаючи решту whitespace checks, і пройшов. Звірено повний staged allowlist і відсутність секретів/діагностики; індексні blob SHA-256 збігаються з WT. Фінальне дерево має відрізнятися від перевіреного кандидата лише цим звітом.

PHP 8.5.10 NTS, PHPUnit 12.5.35, PHP lint усіх семи PHP-файлів пакета. `php vendor/bin/phpunit --do-not-cache-result --colors=never --display-all-issues --log-junit …` запускав тільки групи з попередніх прийнятих звітів, із вимкненим PHP prepend/append, SQLite `:memory:` або спеціальним SQLite fixture, ізольованим storage, array cache/session і без `.env` робочого сайту. Тимчасові тестові каталоги прибрано з checkout у приватну область кожної групи. Окремо запущено `node --test tests/Browser/theory-menu-snapshot.test.cjs tests/Browser/theory-search-ranking.test.cjs` і `node node_modules/vitest/vitest.mjs run tests/js/theorySidebarLayout.test.js` у цьому самому дереві. Відсутність `GRAMLYZE_PERF10_CASE_RECEIPT`/`GRAMLYZE_PERF10_BEFORE` у portable run навмисна: core contract перевірено з versioned frozen oracle, без доступу до приватних даних робочого сайту.

### Результати точного delivery tree

**131 PHP виконання, 105 унікальних PHP-методів, 69 701 assertions** (групи частково перетинаються; executions включають providers). Нових відхилень проти точного accepted baseline не знайдено. Наводимо окремо коди завершення child runner, а не маскуємо відомі failures загальним PASS.

| Група | Методи / executions | Assertions | Failures / errors / skips | Warnings / deprecations | Child exit |
|---|---:|---:|---:|---:|---:|
| PERF-10 membership/integrity | 10 / 14 | 3036 | 0 / 0 / 1 | 0 / 0 | 0 |
| PERF-06 marker batch і SSR | 9 / 9 | 134 | 0 / 0 / 0 | 0 / 1 | 0 |
| PERF-05 filter / exact BEFORE | 10 / 10 | 608 | 0 / 0 / 0 | 0 / 1 | 0 |
| PERF-01–04 menu/M42/catalog/core | 30 / 32 | 21966 | 0 / 0 / 0 | 0 / 1 | 0 |
| Ширша PHP регресія | 41 / 53 | 43730 | 5 / 1 / 0 | 0 / 4 | 2 |
| Seeded PublicFlows | 11 / 11 | 195 | 1 / 0 / 0 | 0 / 1 | 1 |
| Старі Conditionals link controls | 2 / 2 | 32 | 2 / 0 / 0 | 0 / 1 | 1 |

Node **12/12**, Vitest **7/7**, обидва exit 0. PHP lint **7/7**, exit 0. Старі PHP 8.5 deprecations відокремлено; PHPUnit warnings/deprecations 0. Symlink — рівно один **SKIP**, Windows не дозволила створити посилання; обмеження не обходили. У PERF-10 виконання тих самих 14 cases без необов’язкового приватного запису живих пар і старого класу дало 3036 assertions, не попередні 3216: чистий portable package не залежить від цих приватних даних. PERF-06 перевірив batch parity, порядок/multiplicity, свіжий SSR payload і відсутність повторного читання на resumed; PERF-10 — exact roster, SHA/path/strict gates, повні owned snapshots, stale/malformed fallback і новий request.

Broad та PublicFlows звірено з актуальним прийнятим PERF-10 B JUnit; старі Conditionals links — із точним попереднім контролем, який дав однаковий результат на A і B. Збіглися cases, statistics і **кожен method/kind/exception/message**. Відомі відхилення не є PASS і не приховані:

| Метод | Вид / тип | Основне повідомлення |
|---|---|---|
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level` | failure; `ExpectationFailedException` | Expected response status code [302] but received 404. Failed asserting that 404 is identical to 302. |
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2` | failure; `ExpectationFailedException` | Expected response status code [302] but received 404. Failed asserting that 404 is identical to 302. |
| `MixedTheoryPageTestRenderTest::test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes` | failure; `ExpectationFailedException` | Expected response status code [302] but received 404. Failed asserting that 404 is identical to 302. |
| `QuestionsNegationsQualityRuntimeTest::test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices` | failure; `ExpectationFailedException` | Failed asserting that actual size 5 matches expected size 4. |
| `SavedTestJsStateTest::test_it_fetches_questions_for_filter_based_saved_test` | failure; `ExpectationFailedException` | Expected response status code [200] but received 500. Failed asserting that 500 is identical to 200. PDOException: SQLSTATE[HY000]: General error: 1 no such table: question_hints |
| `SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` | error; `QueryException` | Illuminate\Database\QueryException: SQLSTATE[HY000]: General error: 1 no such table: question_hints |
| `PublicFlows.PublicTestRoutesSmokeTest::test_representative_public_test_routes_render_deterministically` | failure; `ExpectationFailedException` | Expected response status code [200] but received 500. Failed asserting that 500 is identical to 200. PDOException: SQLSTATE[HY000]: General error: 1 no such table: question_hints |
| `ConditionalsExtraTheoryPageTestsSeedersTest::test_conditionals_extra_pages_have_tests_and_theory_links` | failure; `ExpectationFailedException` | Failed asserting that null is not null. |
| `ConditionalsTheoryPageTestsSeedersTest::test_conditionals_pages_have_tests_and_theory_links` | failure; `ExpectationFailedException` | Failed asserting that null is not null. |

Після проходу кандидат мав те саме tree `8119e919acaeedc6b56a9aba9e6de95a0944dbbc`, усі 13 transferred files побайтово збігалися з approved SHA, жодних unstaged runtime/test edits. Фінальний pre-commit контроль додає лише цей звіт. Після створення checkpoint перевіряються exact parent, count одного нового коміту, цільовий remote ref і незалежний GitHub commit/file proof; фінальний SHA звіт не записує до себе.

## Історичні локальні докази, не заміри доставки

- PERF-06: новий Word Order dataset **149 → 43 SQL**, теги **54 → 1**, schema **54 → 1**, тодішня медіана **2,155 → 2,070 с**; resumed path без нового читання тегів.
- PERF-10: медіанна парна економія **144,45 мс** (Basic Word Order) і **160,86 мс** (Past Perfect Continuous) у відповідній попередній серії. Показники з різних кампаній не додаються і не переносяться на production.
- **[PERF-10-ACCEPTANCE](perf-10-acceptance-followup.md):** 56 контрольних + 7 фінальних HTTP 200; у восьми запитах категорії змінений клас не завантажувався. Історичний timeout **cause unresolved / not reproduced in follow-up**. Оригінальний [звіт із відкатом](perf-conditionals-widget-membership.md) не змінено.
- **Browser acceptance BLOCKED**: попередню браузерну ізоляцію не підтверджено. Автотести, HTTP і серверне відновлення не є ручними взаємодіями. **Production readiness NOT APPROVED.** Нових A/B, браузерних спроб чи серверних змін під час доставки не запускали.

Відновлення меню в належно налаштованому середовищі: `php artisan theory:menu-build` (опційно `php artisan theory:menu-build --check`). У checkpoint потрібні versioned generator/reader, views, assets sources і тести, не згенероване меню конкретного сайту.
