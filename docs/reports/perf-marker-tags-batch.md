# PERF-06 — пакетне читання marker tags

## Межі та точна база

Gramlyze, локально на gramlyze.loc. Опублікована база — Pvitaly91/engapp-codex, codex/perf-04-05-checkpoint, 8e48c44f34c117a637e20e1f1d93e91ebfdb1b89. Робоча гілка codex/perf-marker-tags-batch має той самий HEAD. Початковий worktree був чистим; інші worktrees не перемикали й не копіювали. Apache обслуговує D:/DEV/htdocs/gramlyze.loc/public з окремого ROOT (codex/production-ready-a14788dac, 41820a2bebdf69004fa7209a2a38457f93efabbd), а не з Git worktree. У scoped звірці 11/12 пар SHA збіглися, FinitePracticeQualityCatalog має успадковану лише EOL-відмінність. Перед змінами зафіксовано точні приватні preimages двох файлів обох копій, guards інших шляхів і незалежні payload/fixture відбитки. Web/CLI identity: 4/4 до та 4/4 після, приватний HMAC фізичного ROOT і конфігурації підключення; окремий read-only PDO контроль підтвердив gr2, MariaDB 12.0.2. PHP HTTP SAPI cgi-fcgi; PHP 8.5.10.

Немає змін навчальних даних, добору, варіантів, CEFR, aliases/hints, теорії, оцінювання чи прогресу. Без міграцій, зміни схем/індексів, нового persistent cache, commit, push, main або deploy. Звіт PERF-05, його статус C та історичні висновки не переписані.

## N+1 і зміна

Єдиний runtime caller getAllMarkerTags() був у TestJsV2Controller::buildQuestionDataset(), після selection і заміни variants, по одному виклику на кожен серіалізований Question. HTML card/step використовує новий batch лише за створення dataset або коли чинний guard вимагає content/technical refresh і повторної побудови. Саме повторне відкриття готового session payload (зокрема за звичайного HTTP reload) його не викликає. JSON /questions має окремий GrammarTestController і marker tags не перечитує. Виділено лише читання marker tags; legacy lookup TextBlock та окремі matcher/scorer methods не змінювались.

Сервіс складає sparse map question_id → marker → список {id, name, category}. Лише фінальні IDs builder, без запиту при [], одна Schema::hasTable на непорожній виклик, bound IN з dedupe IDs та максимум 500 на chunk (нижче за традиційний ліміт SQLite 999). INNER JOIN лишився: orphan не матеріалізується. Відсутній pivot → []; відсутність tags або помилка SQL не перехоплюється. Marker, тип id, Unicode, null/empty category, повтори рядків і per-question [] незмінні. Мапа існує лише в цьому виклику builder; для порожнього запису немає scalar fallback.

### Порядок та еквівалентність

Старий SQL не задає ORDER BY; новий теж його не додає. Порядок рядків у кожному marker не ігнорується: браузер відображає badge-масив у цьому порядку, а SSR/persistence зберігає масив. На MariaDB фактична схема має PK(id) і єдиний covering UNIQUE BTREE (question_id, tag_id, marker); versioned migration має інший порядок колонок (question_id, marker, tag_id), тому його не припускали. Scalar EXPLAIN на всіх 30 tagged questions: covering lookup pivot ref і tags PRIMARY eq_ref. Batch IN використовує той самий covering index (range або index) та eq_ref. Явне сортування за pivot id відтворено як неправильне для двох реальних питань. SQL не дає загальної гарантії порядку поза перевіреними схемами/планами; не вводимо нового сортування чи index hint, не нормалізуємо масиви для порівняння.

Read-only транзакція на фактичній gr2: 111 pivot rows, 30 tagged questions, 40 marker-груп із понад одним тегом, 0 orphan. Незмінений scalar збігся з незалежним PDO scalar; точний новий PHP batch — зі scalar за strict PHP arrays і JSON bytes для одного питання та наборів 31, 54, 503 і 1200 унікальних ID (включно з невідомими, повторними input IDs, усіма tagged questions і оберненим порядком input). Для 54 — 1 schema + 1 data; 503 — 1+2; 1200 — 1+3. Для [] — 0 SQL. SQLite перевіряє також fixtures з дублями, orphan, Unicode/case/null/empty/numeric marker JSON, відсутнім pivot і несправним join. Незалежне повне SSR JSON зібрано на старому контролері в ізольованому SQLite перед зміною; guest fresh/reopened і admin payload (реальні technical data, variants, aliases, hints, theory refs, 54 питання/6 рівнів) порівнюються за точним SHA без сортування.

### Профіль SQL окремо від latency

Чотири окремі HTTP-сценарії для кожної руки, UK development, тільки власні гостьові сесії. Обидва mixed HTML мають 54 питання; нові запити починалися без cookies, resumed використав cookie саме свого seed. Заголовки, тіла, binds та cookie не входять у приватні записи; SQL shape відбитки keyed, вимірювали тільки completed queries. TTFB/total у цій таблиці **під профайлером**, не використовуються для A/B висновку. Перший профільований A мав повільнішу зовнішню тривалість; її не приписуємо 108 мс marker-етапу.

| Режим фактичного маршруту | Marker calls | Schema SQL | Tag-data SQL | Повний SQL count; час, мс A → B | Marker span, мс | Peak active MiB | HTTP/payload B | TTFB / total, мс A → B |
|---|---:|---:|---:|---|---:|---:|---:|---|
| Word Order new HTML | 54 → 1 | 54 → 1 | 54 → 1 | 149 → 43; 372,97 → 201,36 | 108,58 → 1,64 | 33,212 → 27,346 | 837199 / 401345 | 30815,82 / 30841,82 → 2045,13 / 2053,97 |
| Word Order resumed HTML | 0 → 0 | 0 → 0 | 0 → 0 | 44 → 44; 291,38 → 191,81 | 0 → 0 | 25,587 → 25,587 | 837199 / 401345 | 775,78 / 783,03 → 547,55 / 552,01 |
| Word Order /questions JSON | 0 → 0 | 0 → 0 | 0 → 0 | 37 → 37; 319,82 → 198,00 | 0 → 0 | 25,180 → 25,181 | 225473 / 140618 | 1128,85 / 1134,92 → 633,20 / 638,05 |
| Present Simple Passive new HTML | 54 → 1 | 54 → 1 | 54 → 1 | 146 → 40; 299,54 → 202,27 | 93,73 → 1,52 | 45,761 → 45,702 | 873702 / 437179 | 3969,89 / 3977,34 → 1862,31 / 1867,28 |

SQL усередині marker span (schema + data): Word Order 37,21 + 53,31 → 0,47 + 0,94 мс; Passive 33,60 + 42,99 → 0,46 + 0,84 мс. Peak — активні алокації PHP, не RSS. Усі профільовані запити повернули 200. Probe був лише для loopback GET на точних маршрутах із випадковим nonce, без нового маршруту, і повернений до точних preimages перед latency.

Ще до A/B збережено незалежні непропрофільовані відповіді A: Word Order новий 2271,59 мс, resumed 490,72 мс (окремий seed 2200,72), Passive новий 1878,22 мс. Наступний A-контроль категорії 16.11.2026 о 22:03:33.903 за Києвом встановив TCP за 0,88 мс, але клієнт зупинив очікування за 60 012,41 мс: статусу/TTFB немає, тіло 0 B. Access log рядок 119335 має відповідний 200/147432 (розмір обліку Apache, не доставлені байти); завершення транзакції підтверджено перед наступними HTTP. Це нове спостереження на A, не вимір PERF-06 та не встановлена причина історичного випадку. Решта двох уроків і JSON у цьому baseline не запускалися після таймауту; JSON має окремі профілі A/B.

## Парні HTTP-вимірювання без профайлера

16.11.2026, один локальний Apache/PHP/MySQL runtime; кеші застосунку не очищали й не перебудовували, сервер/DB не перезапускали між A/B. Профілі обох рук підтвердили OPcache enabled і app debug off; OPcache не скидали. Після точної заміни двох файлів до кожного запиту чекали 2200 мс для web revalidation. A і B прогріті окремими запитами перед кожною серією. Це не cold-server вимірювання. Для основного сценарію наперед визначили п’ять блоків по чотири пари (по два AB і BA в кожному). Кожна сторона new HTML мала новий порожній jar і створила готовий session dataset; resumed A і B читали один A seed. Peak і SQL виміряно в окремих профільованих запитах вище.

A: controller e5d77869d1611cfe84b97e9610d88b2d4ca2e77c96cb37a7b26f345dcfbaa2f2; service 8a1ba48559e5e49f61729a84298a0f4ca50d6c45f346bd06381ef3f11a47dd62. B SHA наведено нижче. Перед кожною рукою й після серії звірено точні байти та сторонні runtime guards; під час A/B інструментація відсутня. Усі 63 запити (56 вимірюваних, 6 warmup, 1 resumed seed) — HTTP 200, без пропущених/відкинутих пар. В усіх відповідях цих трьох сценаріїв — 54 питання, однакові порядок ID і повний payload SHA відносно відповідного BEFORE; HTML SHA теж збіглися після виключення лише випадкового CSRF токена. HTML/payload bytes: Word Order new/resumed 837199/401345, Passive 873702/437179.

Усі часи в мс; p95 — nearest rank; Δ — медіана парних B−A, а не різниця медіан.

| Сценарій | Пар | Total медіана A → B | Total Δ | Total p95 A → B | B швидше / повільніше / рівно (total) | TTFB медіана A → B | TTFB Δ | TTFB p95 A → B |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Word Order: новий HTML | 20 | 2154.56 → 2069.79 | -103.92 | 2431.06 → 2210.67 | 16 / 4 / 0 | 2152.58 → 2066.94 | -104.65 | 2429.18 → 2208.24 |
| Word Order: resumed HTML | 4 | 641.13 → 611.21 | -5.63 | 655.20 → 661.60 | 2 / 2 / 0 | 639.05 → 609.49 | -5.40 | 653.06 → 659.82 |
| Present Simple Passive: новий HTML | 4 | 2128.32 → 2052.51 | -90.22 | 2241.01 → 2176.44 | 3 / 1 / 0 | 2126.34 → 2050.54 | -90.37 | 2239.43 → 2174.74 |

Медіана нового Word Order зменшилась на 84,77 мс (3,93%); медіана парного виграшу 103,92 мс. Розкид відчутний: медіани total Δ послідовних блоків −148,70, −228,14, −40,47, −115,43, +17,66 мс. Отже, це помірний локальний виграш, не гарантія для кожного запиту чи production. У resumed 2 швидші і 2 повільніші пари та 0 marker SQL обома шляхами; контрольний Passive має лише чотири пари.

### Повні вимірювані спостереження

Дата 16.11.2026 UTC, усі рядки — HTTP 200 і відповідні розміри/кількість вище.

#### Word Order — новий dataset

| № | Черга | A UTC | A TTFB / total | B UTC | B TTFB / total |
|---:|:---:|---|---:|---|---:|
| 1 | AB | 20:05:34.627 | 2150.55 / 2152.79 | 20:05:39.007 | 2053.37 / 2055.61 |
| 2 | BA | 20:05:47.586 | 2230.40 / 2231.99 | 20:05:43.282 | 2066.70 / 2069.41 |
| 3 | BA | 20:05:56.543 | 2618.60 / 2621.43 | 20:05:52.051 | 2262.84 / 2265.27 |
| 4 | AB | 20:06:01.394 | 2124.13 / 2126.35 | 20:06:05.755 | 1988.37 / 1991.53 |
| 5 | BA | 20:06:14.179 | 2140.80 / 2142.99 | 20:06:09.979 | 1967.31 / 1970.70 |
| 6 | AB | 20:06:18.548 | 1989.57 / 1991.55 | 20:06:22.769 | 2067.18 / 2070.17 |
| 7 | AB | 20:06:27.079 | 2429.18 / 2431.06 | 20:06:31.742 | 2144.80 / 2147.08 |
| 8 | BA | 20:06:40.254 | 2234.17 / 2236.27 | 20:06:36.118 | 1909.27 / 1911.59 |
| 9 | AB | 20:06:44.713 | 2027.53 / 2029.24 | 20:06:48.974 | 2020.45 / 2022.84 |
| 10 | BA | 20:06:57.506 | 2118.46 / 2121.59 | 20:06:53.217 | 2063.39 / 2065.88 |
| 11 | BA | 20:07:06.256 | 2194.55 / 2196.93 | 20:07:01.855 | 2168.34 / 2171.69 |
| 12 | AB | 20:07:10.677 | 2122.98 / 2124.97 | 20:07:15.029 | 2053.80 / 2055.89 |
| 13 | BA | 20:07:23.618 | 2204.64 / 2207.05 | 20:07:19.307 | 2084.18 / 2086.84 |
| 14 | AB | 20:07:28.057 | 2412.90 / 2416.39 | 20:07:32.727 | 2033.16 / 2035.29 |
| 15 | AB | 20:07:36.984 | 2110.37 / 2112.05 | 20:07:41.329 | 2125.65 / 2163.28 |
| 16 | BA | 20:07:49.919 | 2082.63 / 2084.27 | 20:07:45.723 | 1970.50 / 1973.61 |
| 17 | AB | 20:07:54.229 | 2154.60 / 2156.33 | 20:07:58.624 | 2208.24 / 2210.67 |
| 18 | BA | 20:08:07.399 | 2296.00 / 2298.76 | 20:08:03.063 | 2102.16 / 2105.99 |
| 19 | BA | 20:08:16.429 | 2181.86 / 2184.45 | 20:08:11.922 | 2172.38 / 2174.66 |
| 20 | AB | 20:08:20.840 | 2035.29 / 2037.59 | 20:08:25.109 | 2079.94 / 2082.71 |

#### Word Order — resumed

| № | Черга | A UTC | A TTFB / total | B UTC | B TTFB / total |
|---:|:---:|---|---:|---|---:|
| 1 | AB | 20:08:39.449 | 649.90 / 651.63 | 20:08:42.330 | 607.48 / 609.42 |
| 2 | BA | 20:08:47.898 | 653.06 / 655.20 | 20:08:45.160 | 505.39 / 507.41 |
| 3 | BA | 20:08:53.694 | 628.20 / 630.64 | 20:08:50.801 | 659.82 / 661.60 |
| 4 | AB | 20:08:56.556 | 561.14 / 563.54 | 20:08:59.353 | 611.50 / 613.00 |

#### Present Simple Passive — новий dataset

| № | Черга | A UTC | A TTFB / total | B UTC | B TTFB / total |
|---:|:---:|---|---:|---|---:|
| 1 | AB | 20:09:10.610 | 2202.68 / 2204.61 | 20:09:15.042 | 2086.62 / 2088.75 |
| 2 | BA | 20:09:23.445 | 2050.00 / 2052.04 | 20:09:19.363 | 1848.78 / 1850.55 |
| 3 | BA | 20:09:32.129 | 2239.43 / 2241.01 | 20:09:27.726 | 2174.74 / 2176.44 |
| 4 | AB | 20:09:36.591 | 1968.54 / 1970.49 | 20:09:40.795 | 2014.46 / 2016.27 |

Прогрів і seed (UTC, TTFB / total, усі 200, у медіани не входять):

| Сценарій | Рука / режим | UTC | TTFB / total, мс |
|---|---|---|---:|
| Word Order | A warmup | 20:05:25.803 | 2236.94 / 2241.01 |
| Word Order | B warmup | 20:05:30.272 | 2128.06 / 2130.87 |
| Word Order resumed | A seed | 20:08:29.417 | 2147.64 / 2150.15 |
| Word Order resumed | A warmup | 20:08:33.803 | 544.96 / 546.65 |
| Word Order resumed | B warmup | 20:08:36.577 | 650.92 / 652.69 |
| Present Simple Passive | A warmup | 20:09:02.201 | 2069.86 / 2072.62 |
| Present Simple Passive | B warmup | 20:09:06.506 | 1880.44 / 1882.35 |

## Фінальне приймання локально

Залишено B у served ROOT і в worktree. Обидві копії двох змінених runtime-файлів мають ті самі SHA-256:

- `app/Http/Controllers/TestJsV2Controller.php` — `6c7f4cc417b4b153169df418baaa5dea99f9ee8698beb113e7a96d4c23341e3f`.
- `app/Services/MarkerTheoryMatcherService.php` — `8b6e3a67946b5871d3fe7e128b253f440128f3eddb0c8bcc2705c62983d350f8`.

Тимчасові вставки в entrypoint та контролери видалено: entrypoint, JSON controller, routes і решта guard-файлів звірені з preimages, активних probe/control-файлів немає. Приватні агреговані receipts/preimages залишаються лише в ігнорованому локальному каталозі, не є залежністю змін.

| Фінальний HTTP, окремо від A/B | Status / тип | TTFB / total, мс | Тіло, B | Що перевірено |
|---|---|---:|---:|---|
| [Basic Word Order](http://gramlyze.loc/test/word-order/theory-basic-word-order), новий | 200 HTML | 2033.31 / 2037.26 | 837199 | 54; повний збіг із BEFORE |
| Basic Word Order, resumed (після нового B seed) | 200 HTML | 520.27 / 523.26 | 837199 | 54; повний збіг із BEFORE |
| [Present Simple Passive](http://gramlyze.loc/test/passive-voice/theory-passive-voice-present-simple), новий | 200 HTML | 2082.96 / 2084.76 | 873702 | 54; повний збіг із BEFORE |
| [Basic Word Order — урок](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | 200 HTML | 20585.37 / 20587.95 | 606958 | Theory menu/revision та 5 вбудованих питань; одинична повільна відповідь, не benchmark і не вимір causal effect |
| [Past Perfect Continuous — урок](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 HTML | 2654.89 / 2658.93 | 564389 | Theory menu/revision та 5 вбудованих питань |
| [Word Order — /questions](http://gramlyze.loc/test/word-order/theory-basic-word-order/questions) | 200 JSON | 700.69 / 702.06 | 225473 | Окремий endpoint, 54 питання і належний JSON контракт |
| [Word Order — категорія](http://gramlyze.loc/theory/word-order) | 200 HTML | 478.60 / 479.64 | 394044 | Theory menu/revision; швидкий фінальний контроль не пояснює timeout на A |

Початковий B seed для resumed: 1883,60 мс TTFB / 1887,09 мс total, 200 HTML, 837199 B і той самий payload. Історичний запис PERF-05 лишається `not reproduced / cause unresolved` як статус того етапу; новий timeout PERF-06 на A задокументовано окремо, причина теж не встановлена.

**Серверне збереження — PASS (HTTP, не браузер):** для обох банків окремі нові анонімні test-only сесії мали 54 питання; card/step повертали HTTP 200, запис — 204, reload — 200, reset власного step — 204, наступне відкриття — 200. Збіглися кількість/позиція, записи відповідей, нормалізовані manual inputs, snapshot і dataset. Збереження та скидання step не змінювали card; чужих спроб не чіпали.

**Браузер — частковий PASS; взаємодії BLOCKED:** у вбудованому браузері підтверджено точний URL Basic Word Order (UK), 54 картки, пропуски, English Sentence Builder і порядок слів. На першій картці відкрився розділ «Структура S–V–O»; панель повернуто у вихідний стан. Окремі marker badges і admin/інші locale не перевірялися браузером. Вкладка відновила вже наявний прогрес, тому не запускали неправильна → редагування → правильна, введення/кліки для оцінювання, Next, reload/resume власної нової спроби чи reset через UI: не підтверджено ізоляцію окремої браузерної спроби. Немає перенесення PASS із серверного тесту на браузер, немає обходу Opera guard, PERF-05 статус C не змінено.

### Автоматичні регресії

PHP 8.5.10, ізольовані SQLite-тести: нові PERF-06 використовують `:memory:`, а PublicFlows — власний тимчасовий SQLite fixture-файл. Змінені PHP-файли проходять lint. Тестові копії не використовують робочу БД сайту.

| Серія | Тести | Твердження | Результат |
|---|---:|---:|---|
| PERF-06 unit + HTTP SSR | 9 | 134 | 0 failures / 0 errors |
| PERF-05 selection/prepare | 10 | 608 | 0 failures / 0 errors |
| PERF-01–04, меню та M42 | 32 | 21966 | 0 failures / 0 errors |
| Ширша PHP-регресія | 53 | 43730 | 5 наявних failures / 1 наявний error, exit 2 |
| JS (браузерні helper-и та sidebar) | 19 (12 + 7) | — | Обидва набори PASS, exit 0 |
| PublicFlows, додатковий контроль mock | 11 | 195 | 1 відтворений baseline failure, 0 errors, exit 1 |

Core покриває локальну команду `theory:menu-build`, SSR/sidebar selection, кеш production/development, request-scoped M42 artifact validation, finite dispatcher і caller-owned projection. Історичний PHP 8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` не є failure.

У broad всі 53 cases та шість сигнатур збіглися з незалежним PERF-05 acceptance JUnit за методом, видом, класом винятку й основним повідомленням. Контрольний перелік зі [звіту доставки](perf-04-05-delivery.md):

| Метод | Точна ознака відмови |
|---|---|
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_limits_random_selection_to_fourteen_questions_per_level` | Очікував 302, одержав 404 |
| `MixedTheoryPageTestRenderTest::test_mixed_all_levels_virtual_test_orders_questions_from_a1_to_c2` | Очікував 302, одержав 404 |
| `MixedTheoryPageTestRenderTest::test_mixed_theory_virtual_test_exposes_standard_and_compose_questions_in_safe_modes` | Очікував 302, одержав 404 |
| `QuestionsNegationsQualityRuntimeTest::test_twenty_four_historical_page_urls_resolve_only_the_new_owned_bank_and_short_choices` | Фактичний розмір 5, очікував 4 |
| `SavedTestJsStateTest::test_it_fetches_questions_for_filter_based_saved_test` | Очікував 200, одержав 500; SQLite `no such table: question_hints` |
| `SavedTestJsStateTest::test_it_rotates_variants_when_retrying_js_test` | `Illuminate\Database\QueryException`: SQLite `no such table: question_hints` |

PublicFlows BEFORE окремо запущено в ізольованому процесі з оригінальними Git blobs контролера, сервісу і mock з `8e48c44f3`, без заміни файлів кандидата. BEFORE/AFTER збігаються за всіма 11 cases і 195 assertions: `PublicTestRoutesSmokeTest::test_representative_public_test_routes_render_deterministically`, `ExpectationFailedException` (очікував 200, одержав 500), причина fixture — `PDOException … no such table: question_hints`. Сторонні fixtures не виправляли.

### Файли checkpoint

1. `app/Http/Controllers/TestJsV2Controller.php` — підключення batch на HTML dataset builder.
2. `app/Services/MarkerTheoryMatcherService.php` — пакетний метод; scalar API незмінний.
3. `tests/Feature/PublicFlows/SeededPublicFlowTestCase.php` — mock підтримує обидва методи, контракти тестів незмінні.
4. `tests/Feature/MarkerTagsDatasetBatchTest.php` — новий інтеграційний тест (untracked).
5. `tests/Unit/MarkerTheoryMatcherBatchTest.php` — новий незалежний unit oracle (untracked).
6. `docs/reports/perf-marker-tags-batch.md` — цей звіт (untracked).

Не було staging/commit/push, змін `main` чи production. Поза scope залишились selection/hydration, legacy `TextBlock` lookups, matching/scoring, JSON endpoint, frontend/session format, історична причина таймауту і будь-яка production-перевірка.
