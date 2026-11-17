# PERF-10-ACCEPTANCE — перевірка таймауту та локальне приймання

**Рішення B: точний кандидат прийнято тільки локально.** Нової регресії, специфічної для B, не встановлено. Є окремі докази швидкодії, коректності та обмеженої стабільності. Причина історичного таймауту лишається невстановленою; browser acceptance BLOCKED, production readiness не підтверджено.

Окреме приймання; [початковий звіт PERF-10](perf-conditionals-widget-membership.md) і тодішнє рішення про відкат не переписувалися. Не PERF-11, без нового алгоритму, повного повтору 108 A/B GET, зміни контенту, БД, дизайну, конфігурації чи прогресу. Усі нові HTTP запити — тільки `gramlyze.loc`, з власних гостьових сесій. `Main` і production поза межами.

## База й порівнювані байти

Репозиторій `Pvitaly91/engapp-codex`; WT — гілка `codex/perf-conditionals-widget-membership`, HEAD `8e48c44f34c117a637e20e1f1d93e91ebfdb1b89`. Окремий served ROOT — `codex/production-ready-a14788dac`, HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd`; чинний vhost має DocumentRoot у його `public`, а не WT. Успадковані незакомічені PERF-06 source/tests і звіти PERF-07/08/09 звірено за приватними 63-path manifests. A — фактичний локальний PERF-06, не чистий Git checkpoint; PERF-07/08 не активовано.

| Артефакт | SHA-256 |
|---|---|
| `ConditionalsWidgetHints.php`, A | `32446b6d7947aeccdc09ff16cb13a995a1a3ea935e76c7c8725ab8e85fa21a2f` |
| `ConditionalsWidgetHints.php`, чистий B | `273a0ba9ffbaf2e47ad57654e6a8ed49fadd822b16c829024864dd6be95ad128` |
| `ConditionalsWidgetMembershipTest.php`, B | `cc1476e4bc38f05c659507a60e8c54ed729f4be2045b5d311b825fd0ade7826f` |
| Frozen Conditionals source | `710e283af26f52d56eb1ceb04d22d5adc66785a607e0bf727d7525ac9290859c` |
| Відбиток реєстру 13 UUID / 65 точних пар | `c17ea272692415dbb8232c2690b76ff230676893eb3ba3ce38cd7b04fd0bafa4` |
| Історичний tagged B | `280b7dffb9143970ed0b9c0e3bb9201688bd60f470fe107ca28788fd78a87e62` |

Незалежно видалено з історичних tagged A/B лише чотири документовані діагностичні гачки (мітка входу, лічильник validation, загортання hash/read): відновлені bytes точно дорівнюють A і B. Історичні overlay index/dependency/probe збігаються з приватним manifest; відмінності — guarded receipt, еквівалентне розділення handle/send і лічильники джерел. Усі 108 вихідних запитів мають очікувану мітку виконаного методу, п’ять входів, HTTP 200 та незмінний контракт. Чистий B і тест взято з точного приватного архіву, а не відтворено з опису; 16 успадкованих hashes і незалежний preflight fixture залишилися як у попередньому запуску. Source SHA, strict типи, caller/locale/type guards, частота outer path/symlink/SHA check не змінені. Для доведено чужої пари не виконується дорога підготовка Conditionals; для власної/сумнівної — повний попередній шлях.

## Історичний timeout: що саме зафіксовано

17.11.2026 о **02:59:16.132 (Київ)**, `GET /theory/word-order`, localhost HTTP/1.1, UK, development, свіжий гостьовий jar і жодного надісланого cookie. Це був сьомий запит чистого smoke B після шести HTTP 200; попередньо tagged A/B закінчився о 02:53:38.914, overlays прибрано, чистий B перевірено на диску перед запуском (із паузою 5250 мс). Точний момент очищення і live worker/opcode для цього **нетегованого** запиту не вимірювалися.

- Client: `timeout` через **60006,90 мс**, `status/TTFB/raw/contract=null`, 0 отриманих байтів, без заголовків. Старий клієнт не мав TCP-connect таймера. Node завершив suite із 2, зовнішній PowerShell завершився з 1 після точного rollback. Це не HTTP 200 з погляду клієнта.
- Apache vhost access log: окремий точний localhost GET у секунду `02:59:16` — `200 147432`. У записі `%t` означає час **початку**; відсутні тривалість, timestamp кінця, worker, фаза PHP й факт отримання байтів клієнтом. Запис підтверджує, що запит дістався до Apache і був зафіксований як завершений на рівні access log до наступного A GET; чи сталося це саме **після** клієнтського deadline та чи завершився PHP, не встановлено. Точний внутрішній етап невідомий.
- У відповідному вікні у доступних Apache vhost/master error logs, PHP і Laravel немає пов’язаних записів. Відсутність записів не доводить успішне виконання чи причину.

Окремий історичний timeout на A з PERF-08 у `00:11:20` теж мав `200 147432`, але це інший запит. Окремий rollback GET на A у `03:02:32` отримав 200 і повні `394044` B (~452,50 мс). Ні збіг чисел із PERF-08, ні успішний A не атрибутують причину B. [PERF-09](perf-runtime-baseline-stability.md) і [PERF-08](perf-theory-request-projections.md) не змінювалися. **Причина історичного timeout залишається unresolved.**

## Шлях категорії та межі diff

Фактичні файли route/controller/shared views у served ROOT звірено з WT (12 файлів). `routes/web.php:116` → `TheoryController::category`, успадкований від `PageController.php:123–199` → `theory.category`. Категорія вибирає блоки `whereNull(page_id)` і через `categoryDescriptionData` передає опис без колекції питань. `theory/category.blade.php` → `components/theory/category-description.blade.php` → спільний `content-block`/native practice-set передає `practiceQuestions ?? collect()`. Єдиний production-виклик `ConditionalsWidgetHints::project()` — у `components/text-block-practice-questions.blade.php:175`, всередині `map` по колекції. `buildForCategory()` готує картки пов’язаних тестів, а не виконує урок чи його віджет.

Diff A→B — лише константи реєстру та методи всередині того самого класу. Немає eager registration або нових autoload/service provider hooks, top-level роботи, locks, middleware, змін шаблонів, нових записів чи персистентного стану. Чинний локальний `static $document` тільки переміщено до приватного методу з тим самим lifecycle. Нові digest/lookup запускаються лише після входу в eligible `project()`; до них — чинна перевірка джерела. Статичний висновок перевіряється позитивно завершеним HTTP receipt, не відсутністю log-файла. Доведена відсутність прямого виклику не дає абсолютної гарантії щодо середовища.

## Повторні цільові тести

Тільки ізольований WT, SQLite `:memory:`, array session/cache, без served DB. Exact B і fixture pins перевірено до/після. Вузький запуск: **23 виконання, 3350 assertions, 0 failures/errors, 1 SKIP**. З них новий Conditionals suite — 14 виконань (10 методів), 3216 assertions, Windows symlink залишився SKIP. Є тести точного source, реєстру, усіх 65 власних пар і 10 фактичних чужих, повної валідації власної пари, пошкодженого source, повторів і нового request. Два PERF-06 класи — 9 виконань / 134 assertions, без failure/error/skip. Повторно всі broad/PublicFlows не запускали: попередні failures залишаються відомими й не зараховані як PASS.

## Наперед визначений контроль і фінальний smoke

Чотири пари категорії в порядку **AB / BA / BA / AB**. Перед категорією в кожній руці та сама послідовність, що була перед історичним timeout: Word Order, Past Perfect Continuous, Whose and Which, Zero Conditional, Word Order mixed, Present Simple Passive mixed. Це одночасно симетричний прогрів та контекст, не окремий benchmark уроків. Одна свіжозгенерована guest session на GET, лише serial; 1000 мс після body між GET у руці, 5250 мс між руками незалежно від фактичної заміни. Один окремий untimed ідентифікаційний GET A перед парами; не більше 56 контрольних та 7 фінальних GET. Зупинка на першому HTTP/content/receipt/hash mismatch, timeout 60 с, зміні worker/OPcache epoch або категорії понад 10 с; repeatable slowdown — B > A на 500 мс і >2× у щонайменше 3 з 4 пар. Таймаут не вилучається, за невідомого in-flight стану нового GET немає.

Тимчасова loopback/Host/nonce діагностика лише у `public/index.php` і `ConditionalsWidgetHints.php`. Позначка із SHA справді виконується на вході в метод, якщо цей метод викликано; для категорії очікується завершений ready/done receipt, `theory.category`/потрібний controller, нуль викликів і class not loaded (перевірка без autoload). Не викликали штучно. Для фінального smoke — один той самий фіксований порядок семи GET, без probes, чистий B; точна normalisation тільки поточного CSRF token. Для категорії власний незалежний A HTML/menu contract, не контракт уроку. Фінальний failure відновлює A без повтору до PASS.

### Результат контролю

Окремий read-only web/CLI proof (`SELECT 1`, HMAC фізичного ROOT і параметрів локальної БД) **збігся 1/1**; не включений у пари. PHP 8.5.10 `cgi-fcgi`, один worker і незмінний OPcache epoch у серії. **56/56** контрольних GET дали HTTP 200; повний HTML після нормалізації лише CSRF і дані вправ збіглися з незалежним A. Усі 56 мають ready/done receipts, timeout/error немає; повторів не було. На Word/PPC/M94/Zero — по п’ять входів із справжньою міткою A/B; на двох окремих mixed — нуль. У кожному з восьми завершених category GET: route `theory.category`, action `TheoryController@category`, один category view, **0 входів**, клас не завантажено ні на ready, ні після terminate. Отже lookup/реєстр не виконувалися. Усі вісім відповідей категорії — точні за normalized HTML/menu contract і 394044 B.

| Пара | Порядок | A total, мс | B total, мс | HTTP A/B | Входи в project() A/B |
|---|---|---:|---:|---|---|
| 1 | AB | 4137,29 | 430,22 | 200 / 200 | 0 / 0 |
| 2 | BA | 457,21 | 433,80 | 200 / 200 | 0 / 0 |
| 3 | BA | 447,16 | 407,33 | 200 / 200 | 0 / 0 |
| 4 | AB | 443,18 | 416,40 | 200 / 200 | 0 / 0 |

Повільніший перший A збережено, він не перевищив stop bound. Повторюваного сповільнення чи нової B-specific регресії в контролі не встановлено. Ці часи категорії — лише перевірка стабільності; оскільки клас не завантажувався, вони **не** є новим виграшем від алгоритму B і не доводять нульову ймовірність timeout. Після серії повернено точний A та entry preimage, тимчасові probes прибрано до єдиного чистого smoke.

### Фінальний smoke чистого B

Єдина наперед визначена послідовність після прибирання probes: **7/7 HTTP 200**, без timeout/error або повторів; усі незалежні normalized HTML/data contracts збіглися, зокрема власний контракт категорії. Зміна файлу на диску не вважається runtime-міткою: доказ фактичного виконання B отримано в окремих контрольних уроках і попередніх 108 GET.

| Маршрут | HTTP | Total, мс | HTML bytes |
|---|---:|---:|---:|
| [Basic Word Order](http://gramlyze.loc/theory/basic-grammar/word-order/theory-basic-word-order) | 200 | 2337,79 | 606958 |
| [Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 | 1785,95 | 564389 |
| [Whose and Which](http://gramlyze.loc/theory/clauses-and-linking-words/relative-clauses-with-whose-and-which) | 200 | 2005,92 | 602837 |
| [Zero Conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | 200 | 2096,14 | 620195 |
| [Word Order mixed](http://gramlyze.loc/test/word-order/theory-basic-word-order) | 200 | 1988,08 | 837199 |
| [Present Simple Passive mixed](http://gramlyze.loc/test/passive-voice/theory-passive-voice-present-simple) | 200 | 1924,91 | 873702 |
| [Word Order category](http://gramlyze.loc/theory/word-order) | 200 | 450,40 | 394044 |

Точний чистий B однаковий побайтово у WT і локальному served ROOT. `public/index.php` повернено до SHA `ee997f791c96f1455dd3ee579c907069c2a274bd71a5bd6a8d92ed23bb17e7ea`, очікувані відмінності в обох 63-path manifests — лише candidate PHP/test та попередній звіт WT. Нових diagnostic routes не додано; route bytes збігаються, тимчасові runtime directories відсутні; фінальні HTTP не мають diagnostic headers.

## Окремі статуси

| Перевірка | Результат |
|---|---|
| Швидкодія уроків | Підтверджена попереднім контрольованим A/B, не вимірювалася наново. Медіанна парна економія: Basic Word Order 144,45 мс (6,34%), PPC 160,86 мс (8,83%); обидва порядки й всі блоки мали той самий напрям. На Zero Conditional та In case не встановлено відтворюваного сповільнення. |
| Коректність | Незмінний exact B; історична еквівалентність 18/18, нові 56/56 і 7/7; ізольовані короткі тести 23 виконання / 3350 assertions без failures/errors, 1 symlink SKIP. Історичні broad/PublicFlows failures залишаються видимими в попередньому звіті. |
| Стабільність | Обмежений контроль і фінальний smoke пройшли, нової B-specific регресії не встановлено. Історичний timeout: cause unresolved, не «виправлено». |
| Browser acceptance | **BLOCKED**: нового дозволеного ізольованого контексту не підтверджено; guard не обходили, особисту спробу не змінювали. HTTP і тести не зараховано як UI-приймання. |
| Production readiness | **Не підтверджено**; локальне приймання не є дозволом на deploy. |

## Локальний пакет без staging

- PERF-06: `app/Http/Controllers/TestJsV2Controller.php`, `app/Services/MarkerTheoryMatcherService.php`, `tests/Feature/PublicFlows/SeededPublicFlowTestCase.php`, `tests/Feature/MarkerTagsDatasetBatchTest.php`, `tests/Unit/MarkerTheoryMatcherBatchTest.php`, `docs/reports/perf-marker-tags-batch.md`.
- Незмінені звіти PERF-07/08/09: `docs/reports/perf-mixed-selected-relations.md`, `docs/reports/perf-theory-request-projections.md`, `docs/reports/perf-runtime-baseline-stability.md`.
- Прийнятий локальний PERF-10: `app/Support/ConditionalsWidgetHints.php` (реєстр усередині), `tests/Feature/ConditionalsWidgetMembershipTest.php`, незмінений `docs/reports/perf-conditionals-widget-membership.md` і цей `docs/reports/perf-10-acceptance-followup.md`.

Приватні receipts, архіви A/B, probes, cookies, generated menu HTML та ізольовані test runtimes не належать до пакета. Без commit, push, змін `main` або deploy.
