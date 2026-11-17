# PERF-10 — ранній відсів чужих пар у ConditionalsWidgetHints

**Рішення: кандидат не залишено (inconclusive щодо повного приймання).** A/B на двох основних уроках пройшов наперед заданий поріг; фінальний HTTP smoke B зупинився на 60-секундному timeout категорії. Не встановлено, що таймаут спричинив кандидат. Щоб не прийняти незавершену перевірку, залишено точний A.

## База й межі

17.11.2026, локальний Gramlyze, `Pvitaly91/engapp-codex`. WT `perf-delivery-02/gramlyze.loc`, гілка `codex/perf-conditionals-widget-membership`, HEAD `8e48c44f34c117a637e20e1f1d93e91ebfdb1b89`. Окрема обслуговувана копія — `codex/production-ready-a14788dac`, HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd`; чинний Apache HTTP vhost `gramlyze.loc` має DocumentRoot у її `public`, не в WT. Приватні 63-path manifests **на кожну копію** зафіксували окремі SHA-256, розміри, відсутність, scoped Git diff та точні preimages до експерименту. Успадковані незакомічені PERF-06 та звіти PERF-07/08/09 збережено. Базовий `TheoryNativeContentRelease.php` має SHA `23b89050f061faddd80debce2fefc8dd356a0b49dffa44f79b2200cb5cbbef11` в обох копіях. Відхилені PERF-07/08 не активовано.

Тільки локальні запити та один експериментальний runtime-файл. Не змінювали дизайн, Blade, selection/grading, question IDs, options, hints, aliases, теми, контентну БД, схему сесій або server configuration. Працювали зі свіжими гостьовими сесіями, без змін особистого прогресу. Без commit, push, `main` або deploy. Докази [PERF-09](perf-runtime-baseline-stability.md) — контекст шуму, а не заявлений приріст.

## Незалежний BEFORE та належність пар

SHA-256 `docs/content/conditionals-widget-hints.v1.json` в обох копіях — `710e283af26f52d56eb1ceb04d22d5adc66785a607e0bf727d7525ac9290859c`. До редагування класу незалежно побудовано множини `(binding.uuid, selected_id)` із `targets` та `(block_uuid, id)` із `questions`. Збіг **в обох напрямках**, **13 різних UUID, 65 унікальних позитивних integer IDs, 65 строгих пар**. Підтверджено рівень, type `'4'`, банк кожного рядка й рівно п’ять належних питань кожної цілі. Незалежний від майбутнього реєстру pair-set SHA: `dd62b2f5793ce0ee07c4f0c12e5bb6b9a5bf71ec68d2bca6fbb17c89b094e6f7` (compact JSON від лексикографічно відсортованих exact pair keys).

Перший локальний GET отримав `ECONNREFUSED` без HTTP статусу; baseline точно відновлено. Після того як користувач запустив Apache, виконано окрему preflight спробу. Тимчасовий loopback/Host/nonce guard зібрав пари саме на вході старого `ConditionalsWidgetHints::project()`, а не з тексту чи результату. Web/CLI read-only відбиток фізичної копії й локального підключення з `SELECT 1` збігся **2/2**. Після запитів baseline SHA точно відновлено; службову директорію прибрано. Ідентичності питань, секрет і cookies у звіті не публікуються.

| Урок | HTTP | TTFB / total, мс (не benchmark) | Придатних викликів | Точні збіги | Доведено чужі |
|---|---:|---:|---:|---:|---:|
| [Basic Word Order](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | 200 | 23772,33 / 23776,50 | 5 | 0 | 5 |
| [Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 | 2186,89 / 2189,37 | 5 | 0 | 5 |

## Що робив кандидат B

`app/Support/ConditionalsWidgetHints.php` A SHA `32446b6d7947aeccdc09ff16cb13a995a1a3ea935e76c7c8725ab8e85fa21a2f`; експериментальний B SHA `273a0ba9ffbaf2e47ad57654e6a8ed49fadd822b16c829024864dd6be95ad128`. Початковий gate `project()` не змінено. Кожний придатний виклик і далі спочатку перевіряє наявність основного файла, `is_link` та точний SHA через `checkedSourcePath()`. Лише після цього B може повернути оригінальний payload для exact base `Question` із канонічним lowercase UUID і позитивним `int` ID, коли перевірений реєстр підтвердив **відсутність пари**. Не нормалізує ID/UUID, не вважає `null`, malformed, числовий рядок, невідомий реєстр або підклас зі своїми accessors доведеним промахом. `load()` як і раніше кидає помилки; `project()` має той самий fallback. Перенесено стару перевірку шляху у спільний helper, а старий parse/validate з його static lifetime — в один спільний `loadChecked()`. Реєстр не заповнює `$document` і не кешує HTML, відповіді, проєкції чи оцінки.

**Відтворення реєстру:** з точних SHA-pinned bytes побудувати та незалежно порівняти обидві множини, як описано вище; відхилити зміни типів/дублі/невідповідність binding. Сортувати `binding.uuid` лексикографічно, кожен список ID чисельно за зростанням; створити PHP-масив `['source_sha' => $sourceSha, 'pairs' => $pairs]` у цьому порядку. Відбиток `hash('sha256', json_encode($registry, JSON_PRESERVE_ZERO_FRACTION))` — `c17ea272692415dbb8232c2690b76ff230676893eb3ba3ce38cd7b04fd0bafa4`. Закріплено і SHA документа, і точні bytes компактного реєстру; кількість 13/65 не замінює перевірку. Відсутність, невалідна форма/тип, інша ревізія або відмінний digest повертають виклик до старого шляху. Нова версія frozen source вимагає незалежної регенерації й перевірки, а не просто заміни SHA у старому реєстрі.

Для власної чи невизначеної пари — `loadChecked()` і стара повна валідація: SHA-перевірки `ConditionalsPracticeSupplement`/`ConditionalsPracticeSource`, точні bindings, snapshots question/answers/options/hints/verb_hints, locale/type, достатня кратність токенів, labels, aliases, derived options і revision. Для чужої пари після outer check не потрібні decode документа й дорогий ланцюжок `validate()` → підготовка 13 цілей `ConditionalsPracticeSource::deriveTargets()`. Чи не перенеслася ця робота в інший caller, оцінюється за лічильниками **повного GET**, не лише за часом `project()`.

Обмеження старого коду, не нова поведінка реєстру: на холодному load JSON призначається static `$document` до `validate()`. Якщо залежна валідація кидає виняток, повторний loader в тому самому PHP execution може побачити вже присвоєний документ. PERF-10 не змінює цю послідовність або lifetime; ранній чужий return нічого у static не пише. Негативний контроль пошкодженої залежності перевіряє еквівалентність холодного шляху.

## HTTP-еквівалентність до замірів

Незалежний живий A знято на точному старому runtime, потім B — тимчасово лише одним перевіреним PHP-файлом, без diagnostic overlays. **18/18** GET в обох руках повернули 200; повний normalized HTML, embedded/test JSON bytes, identities/order, menu revision, H1, canonical, structured data, details/anchors однакові. 18 raw HTML SHA різні між незалежними гостьовими сесіями; єдина нормалізація — точні входження поточного CSRF token, без видалення контенту. Сирі SHA збережено приватно. На 13 тематичних практиках перевірено 65 належних позицій, відображені підказки, answers, aliases, labels і derived options; у граничних курсі й окремих тестах проєкція Conditionals не з’явилась. Усі 13 native URLs взято з `targets[].local_url`.

| URL | A → B | HTML bytes | Перевірено |
|---|---:|---:|---|
| [Basic Word Order](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | 200 → 200 | 606958 | 5 embedded |
| [Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 → 200 | 564389 | 5 embedded |
| [zero-conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | 200 → 200 | 620195 | 5 embedded (точний контракт) |
| [first-conditional](http://gramlyze.loc/theory/conditionals/first-conditional) | 200 → 200 | 631011 | 5 embedded (точний контракт) |
| [second-conditional](http://gramlyze.loc/theory/conditionals/second-conditional) | 200 → 200 | 638682 | 5 embedded (точний контракт) |
| [third-conditional](http://gramlyze.loc/theory/conditionals/third-conditional) | 200 → 200 | 668658 | 5 embedded (точний контракт) |
| [mixed-conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals) | 200 → 200 | 664906 | 5 embedded (точний контракт) |
| [unless-provided-as-long-as](http://gramlyze.loc/theory/conditionals/unless-provided-as-long-as) | 200 → 200 | 633683 | 5 embedded (точний контракт) |
| [in-case](http://gramlyze.loc/theory/conditionals/in-case) | 200 → 200 | 630897 | 5 embedded (точний контракт) |
| [wish-if-only](http://gramlyze.loc/theory/conditionals/wish-if-only) | 200 → 200 | 629352 | 5 embedded (точний контракт) |
| [would-rather](http://gramlyze.loc/theory/conditionals/would-rather) | 200 → 200 | 619880 | 5 embedded (точний контракт) |
| [had-better](http://gramlyze.loc/theory/conditionals/had-better) | 200 → 200 | 613151 | 5 embedded (точний контракт) |
| [conditionals-with-unless-provided-as-long-as](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | 200 → 200 | 589028 | 5 embedded (точний контракт) |
| [advanced-conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | 200 → 200 | 597155 | 5 embedded (точний контракт) |
| [conditional-alternatives-and-nuance](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | 200 → 200 | 603244 | 5 embedded (точний контракт) |
| [Zero Conditional — курс](http://gramlyze.loc/courses/english-grammar-theory/lesson/conditionals/zero-conditional) | 200 → 200 | 1910577 | курс, без Conditionals widget |
| [Word Order — standalone](http://gramlyze.loc/test/word-order/theory-basic-word-order) | 200 → 200 | 837199 | 54 standalone, без Conditionals widget |
| [In case — standalone](http://gramlyze.loc/test/conditionals/in-case) | 200 → 200 | 1784351 | 54 standalone, без Conditionals widget |

Це не latency A/B. Наприклад, на послідовному correctness проході Word Order 1953,21 → 1755,66 мс, PPC 1525,61 → 1392,94 мс, а курс ~33,58 с → ~0,24 с. Окремі власні сторінки були повільнішими у B проході. Відмінність режиму прогріву/часу спостереження не контролювалась цим проходом. Після equivalence точний served A відновлено.

## Заздалегідь фіксований A/B

Protocol SHA-256 `564941857dd1fd1a36da87409c2e293f451db5dcbeaafc67d3e62d1d9d1ff632`, зафіксовано до замірів. Primary statistic — парна різниця **total B−A** окремо для Word Order та PPC. По 20 пар на урок, 5 блоків × 4, рівно 2 AB та 2 BA в кожному блоці; порядок першого уроку теж збалансовано. 12 seed/warmup GET окремо, дві затверджені власні сторінки (Zero Conditional, In case) × 4 контрольні пари (16 GET). У кожного уроку власний гостьовий jar для обох рук, ті самі UK workload і HTML invariants. Серійно, 5250 мс від кінця попереднього body незалежно від swap/no swap, не менше 3250 мс після реальної заміни; відхилення інтервалу понад 350 мс — stop. Без паралельного тестування, нового профайлера, зміни конфігурації або очищення OPcache.

Приймати лише коли всі 20 пар валідні на **кожному** чужому уроці, paired median ≤ −100 мс, окремі AB/BA median ≤ −50 мс, ≥14/20 пар швидші, ≥4/5 блоків із від’ємною медіаною. Якщо сусідні медіани блоків без поділу на руки змістилися понад 200 мс, додатково всі п’ять paired block medians мають бути ≤ −50 мс і обидві order medians ≤ −100 мс. На власних уроках регресія означає ≥3/4 повільніші пари, обидві order medians >0 та paired median >100 мс або >5% медіани A. Перегляд проміжних чисел не змінює порядок, критерії чи пороги. Таймаут (60 с), статус/transport/content mismatch, відсутній чи хибний executed tag, неочікуваний worker/cache transition або source drift — записати та зупинити, не повторювати до PASS. Сплеск не вилучати з даних.

Доказ виконання: локальний захищений receipt із compile-time literal A/B tag **на першій операції в самому `project()`** для кожного вимірюваного GET, pin bytes, request alias та worker alias. Однакова коротка обгортка в обох руках, тільки кількість завершених SQL і їх wall time, кількість outer hashes / widget reads / validations / dependent reads / derivations та PHP active/peak bytes. Без SQL, bindings, IDs, cookies, HTML або response diagnostics у probe. `file_update_protection` та інші чинні параметри не змінюються; очікуваний miss при точній зміні файла не дорівнює restart. Медіана та nearest-rank p95 подаються з розміром вибірки; p95 за 20 спостережень описовий. PERF-09 дав 84–113 мс типового парного розходження й 148–166 мс руху блокових медіан: це контекст, не універсальний поріг.

### Результат A/B повної сторінки

17.11.2026 (Київ), 02:40:35–02:53:37 для фактичних GET. **108/108** валідні, 4 seed, 8 warmup, 80 primary GET і 16 owner-control GET; усі HTTP 200, без таймаутів, пропусків, повторів, вилучених observations або відхилених пар. У 108 receipts рівно 5 відміток правильного compile-time tag кожного запиту (**540**); одного й того самого 8.5.10 `cgi-fcgi` worker позначено W1. Немає непередбачених cache/worker transitions; `validate_timestamps/revalidate_freq/file_update_protection/revalidate_path = 1/2/2/0`, OPcache enabled, не переповнений, restart flags false, manual/OOM/hash restarts 0. Лічильники hit/miss 57595/1338 → 231624/1405 на межах серії (не атрибуція кожного miss до одного opcode). Фактичний post-body gap 5249,81–5266,38 мс; після реального swap 5058,52–5252,22 мс. Усі відповіді збіглися з independent A за normalized HTML, exact embedded payload і рештою контракту.

Total і TTFB — клієнтські wall мс. Δ = парна total B−A; % = медіана Δ / медіана A. p95 = nearest-rank окремо для кожної руки (n = кількість пар у рядку); особливо для n=4 це лише опис вибірки.

| Урок | Пари | Total A → B | TTFB A → B | Paired Δ (відносно A) | AB / BA Δ | Швидші / повільніші | Total p95 A → B |
|---|---:|---:|---:|---:|---:|---:|---:|
| Word Order | 20 | 2279,21 → 2096,51 | 2276,69 → 2093,81 | -144,45 (-6,34%) | -116,71 / -215,25 | 18 / 2 | 2451,81 → 2291,35 |
| PPC | 20 | 1821,87 → 1658,45 | 1817,91 → 1655,59 | -160,86 (-8,83%) | -141,81 / -188,60 | 17 / 3 | 4638,30 → 1915,17 |
| Zero Conditional | 4 | 1879,40 → 1865,23 | 1876,95 → 1862,68 | -44,75 (-2,38%) | 2,49 / -67,60 | 3 / 1 | 1971,03 → 1952,15 |
| In case | 4 | 1845,14 → 1834,78 | 1843,04 → 1832,37 | -20,85 (-1,13%) | -60,15 / 27,77 | 2 / 2 | 1958,62 → 1952,46 |

| Урок | Блок | Пари | Спільна медіана A+B | Медіана парної Δ | Мін / макс Δ |
|---|---:|---:|---:|---:|---:|
| Word Order | 1 | 4 | 2188,77 | -63,41 | -236,28 … 514,31 |
| Word Order | 2 | 4 | 2246,18 | -191,38 | -1534,46 … -79,88 |
| Word Order | 3 | 4 | 2242,27 | -197,03 | -259,28 … -115,68 |
| Word Order | 4 | 4 | 2135,76 | -145,81 | -214,05 … -78,83 |
| Word Order | 5 | 4 | 2043,91 | -176,88 | -344,51 … -117,74 |
| PPC | 1 | 4 | 1749,70 | -110,30 | -196,25 … 874,37 |
| PPC | 2 | 4 | 1772,22 | -137,41 | -2994,77 … -113,69 |
| PPC | 3 | 4 | 1777,65 | -189,18 | -364,68 … -154,51 |
| PPC | 4 | 4 | 1701,07 | -199,73 | -274,55 … -146,98 |
| PPC | 5 | 4 | 1729,26 | -64,52 | -215,25 … 27,81 |

Обидва чужі уроки пройшли наперед зафіксований критерій, усі **5/5** медіан блоків менші за нуль для кожного уроку. Найбільший сусідній рух спільної медіани: Word 106,51 мс, PPC 76,58 мс; поріг drift warning 200 мс не спрацював. Розміри AB/BA відрізнялися, але обидва порядки пройшли поріг і не змінили знак. Поодинокі повільні GET у різних руках залишено, тому з PPC p95 не робимо прогнозу. Дві owned серії не підтвердили повторюваного погіршення; їхні невеликі медіани **не** є доказом прискорення власних уроків.

Лічильники на **повний** GET (показано медіани; counts та bytes були сталими для кожної руки кожного уроку). Related reads — інструментовані source manifest/definitions цієї сім’ї, не весь диск. Active/peak PHP — в MiB, active знято на готовій відповіді. SQL наведено як кількість завершених query events / медіана їх сумарного часу, мс.

| Урок | Outer SHA | Widget read / validate | Dependent reads | Derive | SQL count / ms | Active / peak, MiB | HTML / JSON bytes (незмінні) |
|---|---:|---:|---:|---:|---:|---:|---:|
| Word Order | 5 → 5 | 1 → 0 / 1 → 0 | 14 → 0 | 1 → 0 | 30 → 30 / 99,57 → 105,95 | 54,40 → 52,15 / 55,73 → 53,48 | 606958 / 20923 |
| PPC | 5 → 5 | 1 → 0 / 1 → 0 | 14 → 0 | 1 → 0 | 30 → 30 / 95,03 → 102,69 | 54,67 → 52,42 / 55,93 → 53,68 | 564389 / 20811 |
| Zero Conditional | 5 → 5 | 1 → 1 / 1 → 1 | 14 → 14 | 1 → 1 | 29 → 29 / 95,43 → 90,29 | 54,69 → 54,69 / 56,07 → 56,07 | 620195 / 10442 |
| In case | 5 → 5 | 1 → 1 / 1 → 1 | 14 → 14 | 1 → 1 | 29 → 29 / 108,84 → 100,75 | 54,71 → 54,71 / 56,10 → 56,10 | 630897 / 11274 |

У двох чужих уроках виклики ділянки, яку мали уникнути, обнулилися **на повному GET**: не виявлено перенесення тієї самої Conditionals-підготовки в інший виклик або відкладений шлях. На обох власних уроках повний ланцюжок збережено. SQL count не змінився; ці заміри не доводять прискорення SQL. Медіана active PHP на чужих уроках зменшилася приблизно на 2,25 MiB, на власних практично не змінилася. Без узагальнення на інші теми чи production.

## Точкові автоматичні перевірки B

PHP 8.5.10, PHPUnit 12.5.35, ізольована SQLite і тимчасовий тестовий storage. Перша спроба старту обмеженим Windows-процесом зупинилася до виконання: PHP бачив bootstrap-файл, але `is_readable()` повертав false. Це окремий pre-execution blocker, не PHPUnit failure. Повтор в ізольованому runtime пройшов bootstrap, без зміни конфігурації або робочої БД.

- Новий `ConditionalsWidgetMembershipTest`: **14 виконань, 3216 assertions, 0 failures/errors, 1 skip** — Windows не дозволив створити symlink. Усі 65 власних payloads порівняно з незалежним frozen oracle **і** виконаним приватним old class із точного A. Перевірено 10 реальних чужих пар і synthetic cross-pairs, незмінні моделі, відсутність нових SQL, один outer guard на eligible call та відсутність dependent load для доведеного промаху. Для власних перевірено питання, eager answers/options/hints, кратність токенів, labels/aliases, revision і справжні позитивні проєкції. Окремо перевірено змінений/відсутній реєстр, інший SHA/тип, null/malformed, неповні relations, locale/type/caller, порядок foreign→owned/owned→foreign/repeats/дві власні, новий app/request context у процесі, missing/wrong SHA/malformed outer source, cold fallback пошкодженої залежності. Жоден frozen файл сайту для тестів не змінювали.
- `ConditionalsAuthorQualityTest`: **9 виконань, 81 804 assertions**, без failures/errors. Окремі два чинні seeder link controls на B дійшли до assertion `Failed asserting that null is not null.`; їх не зараховано як PASS. Порівняння з точним A та актуальні PERF регресії наведено окремо нижче. Одна відома PDO deprecation у PHP 8.5.

## Сирі A/B часи

17.11.2026, час Київ. Усі GET у первісному порядку, включно з 12 позастатистичними seed/warmup. `word` — Basic Word Order, `ppc` — Past Perfect Continuous, `owned-1` — Zero Conditional, `owned-7` — In case. Усі 108 відповідей пройшли перевірки HTTP, контенту та виконаного тега, без timeout, повторів або вилучених спостережень.

| № | Старт | Маршрут | Рука | Етап / блок.пара | HTTP | TTFB, мс | Total, мс | HTML bytes |
|---:|---|---|:---:|---|---:|---:|---:|---:|
| 1 | 02:40:35.297 | word | A | seed | 200 | 2081.62 | 2085.82 | 606958 |
| 2 | 02:40:42.646 | ppc | A | seed | 200 | 1517.90 | 1523.04 | 564389 |
| 3 | 02:40:49.430 | owned-1 | A | seed | 200 | 3762.47 | 3766.57 | 620195 |
| 4 | 02:40:58.455 | owned-7 | A | seed | 200 | 3820.22 | 3824.20 | 630897 |
| 5 | 02:41:07.534 | owned-7 | B | warmup | 200 | 1571.89 | 1574.10 | 630897 |
| 6 | 02:41:14.371 | owned-1 | B | warmup | 200 | 1560.88 | 1563.13 | 620195 |
| 7 | 02:41:21.190 | ppc | B | warmup | 200 | 1323.52 | 1325.59 | 564389 |
| 8 | 02:41:27.775 | word | B | warmup | 200 | 1689.93 | 1692.92 | 606958 |
| 9 | 02:41:34.729 | word | A | warmup | 200 | 1869.57 | 1872.57 | 606958 |
| 10 | 02:41:41.852 | ppc | A | warmup | 200 | 1621.08 | 1634.74 | 564389 |
| 11 | 02:41:48.739 | owned-1 | A | warmup | 200 | 1547.91 | 1550.27 | 620195 |
| 12 | 02:41:55.552 | owned-7 | A | warmup | 200 | 1590.01 | 1594.68 | 630897 |
| 13 | 02:42:02.404 | word | A | 1.1 AB | 200 | 1848.79 | 1851.41 | 606958 |
| 14 | 02:42:09.510 | word | B | 1.1 AB | 200 | 1868.36 | 1871.51 | 606958 |
| 15 | 02:42:16.635 | ppc | B | 1.1 BA | 200 | 1350.16 | 1352.60 | 564389 |
| 16 | 02:42:23.243 | ppc | A | 1.1 BA | 200 | 1531.13 | 1533.54 | 564389 |
| 17 | 02:42:30.042 | ppc | B | 1.2 BA | 200 | 1385.66 | 1387.99 | 564389 |
| 18 | 02:42:36.692 | ppc | A | 1.2 BA | 200 | 1582.13 | 1584.23 | 564389 |
| 19 | 02:42:43.532 | word | A | 1.2 AB | 200 | 1864.27 | 1867.66 | 606958 |
| 20 | 02:42:50.652 | word | B | 1.2 AB | 200 | 2379.21 | 2381.97 | 606958 |
| 21 | 02:42:58.289 | word | B | 1.3 BA | 200 | 2212.64 | 2215.53 | 606958 |
| 22 | 02:43:05.771 | word | A | 1.3 BA | 200 | 2448.97 | 2451.81 | 606958 |
| 23 | 02:43:13.483 | ppc | A | 1.3 AB | 200 | 4691.05 | 4695.03 | 564389 |
| 24 | 02:43:23.441 | ppc | B | 1.3 AB | 200 | 5564.26 | 5569.40 | 564389 |
| 25 | 02:43:34.261 | ppc | A | 1.4 AB | 200 | 1951.68 | 1954.83 | 564389 |
| 26 | 02:43:41.468 | ppc | B | 1.4 AB | 200 | 1909.58 | 1915.17 | 564389 |
| 27 | 02:43:48.636 | word | B | 1.4 BA | 200 | 2159.13 | 2162.00 | 606958 |
| 28 | 02:43:56.053 | word | A | 1.4 BA | 200 | 2305.98 | 2308.93 | 606958 |
| 29 | 02:44:03.623 | ppc | A | 2.5 AB | 200 | 1784.60 | 1787.05 | 564389 |
| 30 | 02:44:10.675 | ppc | B | 2.5 AB | 200 | 1670.09 | 1673.36 | 564389 |
| 31 | 02:44:17.614 | word | B | 2.5 BA | 200 | 2059.44 | 2061.91 | 606958 |
| 32 | 02:44:24.935 | word | A | 2.5 BA | 200 | 2314.52 | 2317.40 | 606958 |
| 33 | 02:44:32.508 | word | B | 2.6 BA | 200 | 2288.70 | 2291.35 | 606958 |
| 34 | 02:44:40.061 | word | A | 2.6 BA | 200 | 2416.03 | 2418.62 | 606958 |
| 35 | 02:44:47.733 | ppc | A | 2.6 AB | 200 | 1883.12 | 1886.48 | 564389 |
| 36 | 02:44:54.880 | ppc | B | 2.6 AB | 200 | 1736.69 | 1739.31 | 564389 |
| 37 | 02:45:01.872 | ppc | B | 2.7 BA | 200 | 1641.09 | 1643.53 | 564389 |
| 38 | 02:45:08.771 | ppc | A | 2.7 BA | 200 | 4632.39 | 4638.30 | 564389 |
| 39 | 02:45:18.665 | word | A | 2.7 AB | 200 | 3682.37 | 3687.28 | 606958 |
| 40 | 02:45:27.612 | word | B | 2.7 AB | 200 | 2150.14 | 2152.81 | 606958 |
| 41 | 02:45:35.016 | word | A | 2.8 AB | 200 | 2197.61 | 2201.01 | 606958 |
| 42 | 02:45:42.474 | word | B | 2.8 AB | 200 | 2118.40 | 2121.13 | 606958 |
| 43 | 02:45:49.851 | ppc | B | 2.8 BA | 200 | 1754.36 | 1757.38 | 564389 |
| 44 | 02:45:56.872 | ppc | A | 2.8 BA | 200 | 1881.97 | 1885.03 | 564389 |
| 45 | 02:46:04.009 | word | A | 3.9 AB | 200 | 2405.34 | 2410.85 | 606958 |
| 46 | 02:46:11.675 | word | B | 3.9 AB | 200 | 2155.84 | 2158.78 | 606958 |
| 47 | 02:46:19.083 | ppc | B | 3.9 BA | 200 | 1685.06 | 1689.59 | 564389 |
| 48 | 02:46:26.032 | ppc | A | 3.9 BA | 200 | 1840.70 | 1844.10 | 564389 |
| 49 | 02:46:33.134 | ppc | B | 3.10 BA | 200 | 1699.48 | 1702.31 | 564389 |
| 50 | 02:46:40.101 | ppc | A | 3.10 BA | 200 | 1906.76 | 1909.50 | 564389 |
| 51 | 02:46:47.263 | word | A | 3.10 AB | 200 | 2393.26 | 2399.13 | 606958 |
| 52 | 02:46:54.913 | word | B | 3.10 AB | 200 | 2280.39 | 2283.45 | 606958 |
| 53 | 02:47:02.457 | word | B | 3.11 BA | 200 | 2187.21 | 2190.82 | 606958 |
| 54 | 02:47:09.907 | word | A | 3.11 BA | 200 | 2447.10 | 2450.09 | 606958 |
| 55 | 02:47:17.607 | ppc | A | 3.11 AB | 200 | 1879.17 | 1882.36 | 564389 |
| 56 | 02:47:24.752 | ppc | B | 3.11 AB | 200 | 1708.96 | 1711.19 | 564389 |
| 57 | 02:47:31.724 | ppc | A | 3.12 AB | 200 | 1984.05 | 1986.56 | 564389 |
| 58 | 02:47:38.967 | ppc | B | 3.12 AB | 200 | 1619.19 | 1621.88 | 564389 |
| 59 | 02:47:45.853 | word | B | 3.12 BA | 200 | 2056.42 | 2059.10 | 606958 |
| 60 | 02:47:53.170 | word | A | 3.12 BA | 200 | 2198.54 | 2201.09 | 606958 |
| 61 | 02:48:00.621 | ppc | A | 4.13 AB | 200 | 1797.81 | 1800.33 | 564389 |
| 62 | 02:48:07.675 | ppc | B | 4.13 AB | 200 | 1629.39 | 1633.12 | 564389 |
| 63 | 02:48:14.571 | word | B | 4.13 BA | 200 | 2087.75 | 2090.54 | 606958 |
| 64 | 02:48:21.913 | word | A | 4.13 BA | 200 | 2293.59 | 2296.46 | 606958 |
| 65 | 02:48:29.468 | word | B | 4.14 BA | 200 | 2087.36 | 2090.20 | 606958 |
| 66 | 02:48:36.808 | word | A | 4.14 BA | 200 | 2166.34 | 2169.03 | 606958 |
| 67 | 02:48:44.242 | ppc | A | 4.14 AB | 200 | 1797.29 | 1803.18 | 564389 |
| 68 | 02:48:51.296 | ppc | B | 4.14 AB | 200 | 1568.83 | 1570.94 | 564389 |
| 69 | 02:48:58.134 | ppc | B | 4.15 BA | 200 | 1562.94 | 1566.00 | 564389 |
| 70 | 02:49:04.954 | ppc | A | 4.15 BA | 200 | 1838.01 | 1840.56 | 564389 |
| 71 | 02:49:12.060 | word | A | 4.15 AB | 200 | 2265.03 | 2267.71 | 606958 |
| 72 | 02:49:19.588 | word | B | 4.15 AB | 200 | 2050.87 | 2053.66 | 606958 |
| 73 | 02:49:26.902 | word | A | 4.16 AB | 200 | 2185.35 | 2188.17 | 606958 |
| 74 | 02:49:34.352 | word | B | 4.16 AB | 200 | 2099.87 | 2102.48 | 606958 |
| 75 | 02:49:41.718 | ppc | B | 4.16 BA | 200 | 1619.63 | 1622.03 | 564389 |
| 76 | 02:49:48.605 | ppc | A | 4.16 BA | 200 | 1751.25 | 1769.01 | 564389 |
| 77 | 02:49:55.640 | word | A | 5.17 AB | 200 | 2138.64 | 2141.53 | 606958 |
| 78 | 02:50:03.035 | word | B | 5.17 AB | 200 | 2009.76 | 2012.34 | 606958 |
| 79 | 02:50:10.306 | ppc | B | 5.17 BA | 200 | 1573.79 | 1576.68 | 564389 |
| 80 | 02:50:17.141 | ppc | A | 5.17 BA | 200 | 1789.37 | 1791.92 | 564389 |
| 81 | 02:50:24.194 | ppc | B | 5.18 BA | 200 | 1817.26 | 1822.21 | 564389 |
| 82 | 02:50:31.278 | ppc | A | 5.18 BA | 200 | 1791.55 | 1794.41 | 564389 |
| 83 | 02:50:38.329 | word | A | 5.18 AB | 200 | 2072.40 | 2075.48 | 606958 |
| 84 | 02:50:45.669 | word | B | 5.18 AB | 200 | 1937.36 | 1957.74 | 606958 |
| 85 | 02:50:52.881 | word | B | 5.19 BA | 200 | 1943.67 | 1946.19 | 606958 |
| 86 | 02:51:00.086 | word | A | 5.19 BA | 200 | 2288.34 | 2290.70 | 606958 |
| 87 | 02:51:07.638 | ppc | A | 5.19 AB | 200 | 1691.43 | 1693.99 | 564389 |
| 88 | 02:51:14.593 | ppc | B | 5.19 AB | 200 | 1555.05 | 1557.53 | 564389 |
| 89 | 02:51:21.414 | ppc | A | 5.20 AB | 200 | 1723.01 | 1725.56 | 564389 |
| 90 | 02:51:28.403 | ppc | B | 5.20 AB | 200 | 1729.61 | 1732.97 | 564389 |
| 91 | 02:51:35.391 | word | B | 5.20 BA | 200 | 1974.38 | 1977.60 | 606958 |
| 92 | 02:51:42.623 | word | A | 5.20 BA | 200 | 2199.53 | 2202.18 | 606958 |
| 93 | 02:51:50.082 | owned-1 | A | control 1 AB | 200 | 1968.91 | 1971.03 | 620195 |
| 94 | 02:51:57.310 | owned-1 | B | control 1 AB | 200 | 1886.33 | 1888.93 | 620195 |
| 95 | 02:52:04.452 | owned-7 | B | control 1 BA | 200 | 1781.71 | 1784.93 | 630897 |
| 96 | 02:52:11.500 | owned-7 | A | control 1 BA | 200 | 1856.94 | 1859.06 | 630897 |
| 97 | 02:52:18.623 | owned-7 | B | control 2 BA | 200 | 1949.84 | 1952.46 | 630897 |
| 98 | 02:52:25.835 | owned-7 | A | control 2 BA | 200 | 1820.53 | 1822.78 | 630897 |
| 99 | 02:52:32.924 | owned-1 | A | control 2 AB | 200 | 1862.93 | 1865.08 | 620195 |
| 100 | 02:52:40.051 | owned-1 | B | control 2 AB | 200 | 1949.09 | 1952.15 | 620195 |
| 101 | 02:52:47.261 | owned-1 | B | control 3 BA | 200 | 1839.03 | 1841.53 | 620195 |
| 102 | 02:52:54.357 | owned-1 | A | control 3 BA | 200 | 1846.44 | 1848.94 | 620195 |
| 103 | 02:53:01.469 | owned-7 | A | control 3 AB | 200 | 1829.14 | 1831.22 | 630897 |
| 104 | 02:53:08.566 | owned-7 | B | control 3 AB | 200 | 1861.31 | 1863.66 | 630897 |
| 105 | 02:53:15.696 | owned-7 | A | control 4 AB | 200 | 1956.34 | 1958.62 | 630897 |
| 106 | 02:53:22.919 | owned-7 | B | control 4 AB | 200 | 1803.43 | 1805.89 | 630897 |
| 107 | 02:53:29.986 | owned-1 | B | control 4 BA | 200 | 1763.38 | 1765.92 | 620195 |
| 108 | 02:53:37.008 | owned-1 | A | control 4 BA | 200 | 1890.97 | 1893.73 | 620195 |

## Актуальні регресії і звірка відомих збоїв

На точному B, без production DB, з окремими fixtures/test runtimes. Виконання включають data providers, групи можуть перетинатися. Усі PHPUnit JUnit збережено приватно. Windows-тестові exports/views після завершення переміщено тільки з точних створених test-runtime шляхів у приватний архів; сторонніх каталогів не чіпали.

| Набір | Виконання / assertions | Результат |
|---|---:|---|
| PERF-01–04: menu/M42/catalog/theory guards | 32 / 21966 | 0 failures/errors |
| PERF-05: selection і незалежний BEFORE | 10 / 608 | 0 failures/errors |
| PERF-06: marker lookup і SSR | 9 / 134 | 0 failures/errors |
| Ширші PHP регресії | 53 / 43730 | 5 failures, 1 error; ті самі, що в PERF-08 |
| Seeded PublicFlows | 11 / 195 | 1 failure; той самий, що в PERF-08 |
| Node menu snapshot і search ranking | 12 | 12 PASS |
| Vitest sidebar layout | 7 | 7 PASS |

Для broad/PublicFlows збіглися **всі cases, method, kind, exception type, message та stats** із незалежним JUnit [PERF-08](perf-theory-request-projections.md); їх не перетворено на PASS. Ширший набір: три `MixedTheoryPageTestRenderTest` очікували 302 і отримали 404 (ліміт/порядок/all levels і standard/compose); `QuestionsNegationsQualityRuntimeTest` — size 5 замість 4; два `SavedTestJsStateTest` fetch/retry — SQLite fixture `no such table: question_hints` (failure 500 та QueryException). PublicFlows — той самий представницький маршрут 500 замість 200 зі згаданою fixture table. Не змінювали fixtures, щоб отримати зелений total. Відомі PHP 8.5 deprecations відділено від помилок.

Два старі Conditionals seeder link tests (`ConditionalsTheoryPageTestsSeedersTest::test_conditionals_pages_have_tests_and_theory_links` та `ConditionalsExtraTheoryPageTestsSeedersTest::test_conditionals_extra_pages_have_tests_and_theory_links`) перевірено окремим ізольованим запуском точного A, завантаженого з приватного SHA-pinned preimage. A та B збіглися **2/2 cases, повідомлення й statistics (32 assertions)**; для `zero-conditional` та `unless-provided-as-long-as` відповідно та сама перевірка «Mixed A1-C2 test exists» закінчилась `Failed asserting that null is not null.`. Це не нова регресія B; live HTTP 18/18 і окремі тести не стирають ці failures.

## Фінальний HTTP smoke і точний відкат

Тимчасові три PHP overlays A/B та runtime directory повністю прибрано, звірено їхні точні preimages. Потім перевірявся **чистий B** (тільки один кандидатний файл, без probes) із автоматичним exact restore, якщо smoke не завершиться. Час Київ, 17.11.2026. Це функціональний smoke, не друга latency-серія.

| Старт | Маршрут | Клієнтський HTTP | Total, мс | Результат |
|---|---|---:|---:|---|
| 02:58:58.589 | [Basic Word Order](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | 200 | 2101,35 | content match |
| 02:59:01.713 | [Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 | 1576,71 | content match |
| 02:59:04.300 | [Whose and Which](http://gramlyze.loc/theory/clauses-and-linking-words/relative-clauses-with-whose-and-which) | 200 | 1715,15 | embedded smoke |
| 02:59:07.032 | [Zero Conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | 200 | 1910,69 | content match |
| 02:59:09.963 | [Word Order mixed](http://gramlyze.loc/test/word-order/theory-basic-word-order) | 200 | 1764,31 | payload match |
| 02:59:12.750 | [Present Simple Passive mixed](http://gramlyze.loc/test/passive-voice/theory-passive-voice-present-simple) | 200 | 2359,33 | standalone smoke |
| 02:59:16.132 | [Word Order category](http://gramlyze.loc/theory/word-order) | — | 60006,90 | timeout; без заголовків/TTFB |

**6/7**, не PASS. Запит категорії не повторювали на B. Перед новим GET перевірили vhost access log: запис завершеного localhost GET із тією ж секундою старту містив 200 та 147432 B. Це не перетворює відсутню клієнтську відповідь на 200, не підтверджує повний body і не визначає причину затримки. PERF-09 зафіксував `not reproduced / cause unresolved` для історичного випадку; тут відбувся **новий timeout**, його причина не встановлена. Єдиного спостереження недостатньо, щоб встановити регресію цього PHP-класу; жодної вимірюваної пари уроків воно не скасовує. Без нового профілю, змін конфігурації або повтору до бажаного результату.

Guard повернув точний A на served ROOT. Випробувані B source/test побайтово збережено **лише приватно**; власний PHP-файл WT повернуто до точного A (`32446b6d…a2f`), новий B-залежний тест прибрано з WT до початкової відсутності. Початкові 63-path manifests обох копій знову збігаються для всіх успадкованих і змінюваних файлів. Diagnostic routes не додавалися, `routes/web.php` та `public/index.php` звірені, тимчасові серверні каталоги відсутні. Успішні smoke responses перевірено на відсутність діагностичних headers.

**Перевірка відновленого A**, один новий GET на кожен маршрут після точного відкату, окремий receipt. 7/7 HTTP 200, без errors; незалежні contracts для Word/PPC/Zero/Word mixed збіглися. Це підтвердження робочого локального стану, не повтор B і не новий A/B.

| Старт, Київ | Маршрут (як вище) | HTTP | TTFB / total, мс | HTML bytes |
|---|---|---:|---:|---:|
| 03:02:14.151 | Basic Word Order | 200 | 2960,35 / 2964,70 | 606958 |
| 03:02:18.137 | Past Perfect Continuous | 200 | 1860,75 / 1862,92 | 564389 |
| 03:02:21.014 | Whose and Which | 200 | 2213,09 / 2215,39 | 602837 |
| 03:02:24.241 | Zero Conditional | 200 | 1873,04 / 1875,53 | 620195 |
| 03:02:27.129 | Word Order mixed | 200 | 1904,13 / 1906,39 | 837199 |
| 03:02:30.061 | Present Simple Passive mixed | 200 | 1755,67 / 1757,93 | 873702 |
| 03:02:32.844 | Word Order category | 200 | 450,64 / 452,50 | 394044 |

Browser interaction **BLOCKED**: нового безпечного ізольованого браузерного контексту не підтверджено; особисту спробу не змінювали. Відображення підказок підтверджене лише HTTP/автотестами; manual/token wrong→edit→correct, Next, browser reload/resume та reset не зараховано. Статуси PERF-05/06 не перекласифіковано.

Звірений повний локальний пакет, без staging:

- PERF-06, modified: `app/Http/Controllers/TestJsV2Controller.php`, `app/Services/MarkerTheoryMatcherService.php`, `tests/Feature/PublicFlows/SeededPublicFlowTestCase.php`.
- PERF-06, untracked: `tests/Feature/MarkerTagsDatasetBatchTest.php`, `tests/Unit/MarkerTheoryMatcherBatchTest.php`, `docs/reports/perf-marker-tags-batch.md`.
- Незмінні untracked звіти PERF-07, PERF-08, PERF-09: `docs/reports/perf-mixed-selected-relations.md`, `docs/reports/perf-theory-request-projections.md`, `docs/reports/perf-runtime-baseline-stability.md`.
- PERF-10: тільки цей звіт `docs/reports/perf-conditionals-widget-membership.md` лишився як новий versionable файл. `app/Support/ConditionalsWidgetHints.php` — точний A; `tests/Feature/ConditionalsWidgetMembershipTest.php` — початкова відсутність. Приватні candidate/preimage/receipt/probe файли, cookie/storage та runtime menu HTML не входять у майбутній checkpoint.

Результат: підтверджений виграш повного lesson request та повна еквівалентність, але локальний B не прийнято через незавершений фінальний smoke. PERF-01–06 збережено, PERF-07/08 не активовано, звіт PERF-09 не переписано. Немає commit, push, змін `main` або deploy.
