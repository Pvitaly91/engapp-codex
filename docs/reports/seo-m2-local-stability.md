# M2 — локальна стабільність і збереження стану

Дата: 8 вересня 2026, Europe/Kyiv. Усі HTTP/браузерні перевірки — тільки
`http://gramlyze.loc`; production не перевірявся й не оновлювався.

## База та межі

- Початкова гілка `main`, HEAD і `origin/main` після `git fetch origin`:
  `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`.
- Remote: `https://github.com/Pvitaly91/engapp-codex.git`.
- Нова робоча гілка: `codex/seo-m2-local-stability`, створена від фактичного HEAD
  без reset/stash і без перезапису сторонніх змін.
- M1 вже виділено в [окремий коміт d123a73800ded20ceca3ef6476597787fb153bd0](https://github.com/Pvitaly91/engapp-codex/commit/d123a73800ded20ceca3ef6476597787fb153bd0).
  Його чотири виправлення та історичні/повторні результати — у [звіті M1](seo-m1-local-fixes.md).
- Прочитані README, release/post-merge/branch-protection правила і чотири
  `.github/workflows` файли. Push-trigger наявних smoke workflows обмежений
  `main`; release gate — PR/main або ручний dispatch. Робоча гілка не є
  автодеплойною. Workflow dispatch, merge/main push і деплой не виконуються.

## 1. MissingAppKeyException: доказ, виправлення і межа висновку

**Історичний HTTP 500 наживо в M2 не відтворився. Його точна причинність для
старих запитів остаточно не встановлена.** Водночас встановлено реальний
небезпечний механізм спільного process environment у локальному threaded Apache;
він відтворює той самий виняток в ізольованому регресійному тесті й усунутий
адресною зміною bootstrap. Нуль 500 у повторних GET сам собою не є доказом
остаточного усунення всіх можливих причин.

### Спостереження

- Старі записи Laravel log: `2026-09-08 00:43:52`, `00:44:16`, `00:51:37`
  (час локального журналу), `MissingAppKeyException` у
  `EncryptionServiceProvider` під час створення encrypter/middleware.
  У старому записі немає PID для точної кореляції з поточним worker.
- XAMPP Apache 2.4.58 Win64, MPM WinNT **threaded**, web PHP 8.2.12,
  `apache2handler`, `PHP_ZTS=1`, worker PID `20568`. CLI PHP 8.2.12 — `cli`.
  Обидва використовують XAMPP PHP ini; Apache завантажує `php8apache2_4.dll`.
  Application base path і vhost DocumentRoot відповідають цьому репозиторію.
- `variables_order=GPCS`; OPcache увімкнений, перевірка timestamps увімкнена,
  `revalidate_freq=2`. Доказів неправильного OPcache немає; його не очищали.
- Робочий config cache відсутній. `packages.php`/`services.php` у робочому
  bootstrap/cache залишили попередні timestamps 27 серпня UTC. Джерело
  `config('app.key')` — стандартний `env('APP_KEY')` у `config/app.php`.
  Не знайдено custom provider, який переписує ключ, або активного іншого
  environment file. Робочий `.env` byte-for-byte незмінний.

Laravel за замовчуванням додає PutenvAdapter. На threaded mod_php process
environment спільний між запитами. Якщо інший запит уже завантажив ключ у
`getenv`, immutable dotenv може прийняти його за зовнішнє значення і не
записати власну копію в `$_SERVER`/`$_ENV`. Після завершення сусіднього
запиту process-значення може зникнути раніше, ніж поточний запит прочитає
конфігурацію. Документація phpdotenv прямо застерігає від `getenv`/`putenv`
через thread safety: [офіційний контракт](https://github.com/vlucas/phpdotenv#putenv-and-getenv).

У 390 baseline GET observer зафіксував **238 запитів без власної копії ключа**
після dotenv, із наявним process-значенням; у 7 записах до shutdown вже були
відсутні всі три environment-копії. На момент LoadConfiguration усі 390 ще
мали правильний ключ і повернули 200. Це доказ небезпечного стану та вікна
гонки, але не перехоплений момент старого HTTP 500. Спостереження різних
environment-адаптерів не є атомарним snapshot процесу.

### Зміна

[ThreadSafeEnvironment](../../app/Support/ThreadSafeEnvironment.php), викликаний
на початку [bootstrap/app.php](../../bootstrap/app.php), вимикає тільки
PutenvAdapter через штатний `Env::disablePutenv()` **лише для threaded
`apache2handler`**. Dotenv зберігає request-local значення, не спираючись на
тимчасове значення сусіднього запиту. CLI і non-threaded PHP-FPM не змінені;
явний `$_SERVER` override збережений.

[Чотири регресійні тести](../../tests/Unit/ThreadSafeEnvironmentTest.php)
відтворюють unsafe interleaving і справжній Laravel MissingAppKeyException,
перевіряють виправлений encrypt/decrypt, зовнішній override та незмінність
CLI/FPM. Вони працюють із випадковим тимчасовим ключем **тільки в пам’яті**.
Робочий ключ не читався у вивід, не змінювався і не ротувався.
Стандартні правила ключа/конфігурації залишаються чинними:
[Laravel encryption](https://laravel.com/framework/docs/10.x/encryption),
[Laravel configuration](https://laravel.com/framework/docs/10.x/configuration).

Це виправлення поведінки env-loader для фактичного SAPI, не fallback-ключ,
не повторне ручне читання `.env`, не вимкнення encryption і не приховування 500.

### GET до й після ізольованих тестів

Кожна серія чергує ці три paths; кожний запит — новий GET без cookies,
auth/Referer, без автоматичного retry або слідування редиректам:

1. `/theory/common-mistakes/countable-vs-uncountable-nouns-common-mistakes`
2. `/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions`
3. `/test/future-perfect/questions`

| Серія | Workers | GET на кожний path | Усього | Не-200 / мережеві збої |
|---|---:|---:|---:|---:|
| Baseline, 01:51–01:52 | 1 | 10 | 30 | 0 |
| Baseline, 01:52 | 3 | 20 | 60 | 0 |
| Baseline extended, 01:53–01:55 | 3 | 100 | 300 | 0 |
| Після зміни, до тестів, 02:01–02:02 | 1 | 30 | 90 | 0 |
| Після зміни, до тестів, 02:04–02:05 | 3 | 30 | 90 | 0 |
| Після тестів, 02:18–02:20 | 1 | 30 | 90 | 0 |
| Після тестів, 02:20–02:21 | 3 | 30 | 90 | 0 |

Разом **750/750 HTTP 200**, з них **360 після зміни**. У всіх 360 observer
підтвердив власні `$_ENV`/`$_SERVER` копії, відсутній process key та валідний
`config('app.key')`, який відповідає очікуваному. Нових MissingAppKeyException
в інтервалі M2 немає. Тимчасовий observer hook і приватний control-файл видалено
перед фінальною браузерною перевіркою. Інструмент поза public лишено тільки
для явного локального opt-in; debug endpoint не створювався.

## 2. Збереження стану і ERR_ABORTED

**Підтверджено помилковість висновку «ERR_ABORTED означає втрату прогресу»
для перевірених запитів. Дефект збереження застосунку не підтвердився.**
Точна внутрішня причина, чому Chromium позначає цей завершений 204 як aborted,
на рівні реалізації браузера не встановлена. Доказів скасування актуального
save застосунком немає.

Прочитані [persistence config](../../resources/views/components/saved-test-js-persistence.blade.php),
[JS helpers](../../resources/views/components/saved-test-js-helpers.blade.php),
[controller](../../app/Http/Controllers/GrammarTestController.php) і
[server state service](../../app/Support/SavedTestJsState.php).
Використовується `fetch`, не form/sendBeacon. Debounce — 250 ms,
`JS_TEST_SAVE_QUEUE` послідовна, payload — відокремлена копія. Local snapshot
пишеться відразу; pagehide оновлює локальну копію. Save не парсить JSON із 204,
не викликає навігацію та не має AbortController. HTTP 204 збережений:
[RFC 9110 §15.3.5](https://www.rfc-editor.org/rfc/rfc9110.html#section-15.3.5).

Контрольний network probe повторив послідовність і з перехопленням усіх
запитів, і **без перехоплення локального state endpoint**. У другому запуску,
відносно request: fetch resolved `204/ok=true` на +2373 ms, response і
`requestfailed net::ERR_ABORTED` на +2375 ms, context закрито тільки на
+3300 ms. Між request та abort не було pagehide/beforeunload.
Різниця кількох ms між JS/browser events — доставка подій інструмента.

Фінальна UI-траса додатково містить дії користувача, snapshot-enqueue,
snapshot-send, request/response/failed і події навігації. Після двох відповідей
фактично відновилися `answered=2`, `correct=2`, `activeCardIdx=4` і той самий
порядок усіх 84 питань. Після швидких правок зберігся останній непорожній
ручний ввід; жодна стара відповідь не перезаписала новішу.

### Обов’язкові сценарії: desktop 1440×1000 / mobile 390×844

| Сценарій | Desktop | Mobile |
|---|---|---|
| А: новий гість, 2 правильні відповіді, save, reload; відповіді/позиція/лічильники/порядок | PASS | PASS |
| Б: швидкі зміни ручного поля, найновіший snapshot після reload | PASS | PASS |
| В: внутрішнє посилання «Теорія», back; окремо reload біля debounce | PASS | PASS |
| Г: один відхилений POST; один затриманий на 1 s; черга працює, найновіший стан відновлюється | PASS | PASS |
| Д: після виходу зі сторінки прибрані тільки її storageKeys; cookies залишені в пам’яті; серверне відновлення | PASS | PASS |
| Е: новий гість, інший тест Forms, інший режим Step не отримують відповіді | PASS | PASS |

Фінальний запуск: **28/28 assertions**, 0 pageerror, 0 спроб доступу до
production. До нього вже пройшов окремий повний запуск 28/28. Фінальний desktop
зберіг 16 raw `ERR_ABORTED`/204, mobile — 15; додатково по одному навмисному
`ERR_FAILED`/fetch rejection. Ці помилки не відфільтровані.

У серверному сценарії очікуваний і відновлений SHA-256 видимого прогресу
однаковий на обох viewport:
`5c5684c0ba817b80ffc15f89959e285396bcba91bab7e86fa5e034ac8fcfd00e`.
Перед повторним GET локальних snapshot немає; новий HTML отримує стан із
чинної session. Cookies, CSRF і повні session payload не записані у Git.
Скриншоти переглянуті локально; питання й ручне поле відображаються.

Змінено тільки діагностичну [класифікацію подій](../../tools/diagnostics/state-request-classification.cjs)
і додано поведінкові тести. Вона не прирівнює навіть успішний fetch до
серверного запису без окремого підтвердження restoration. Rejected fetch,
HTTP 419/422/500, інші network errors і navigation abort не маскуються.
Код autosave, endpoint 204, CSRF та cookies/session контракт **не змінювалися**.

### Невдалі ранні спроби не приховані

- Перший browser probe двічі зупинився через неправильний селектор типу
  reorder; виправлено вибір реального options-контролу в DOM.
- Два наступні прогони на обох viewport помилково порівнювали `''` із `null`
  в порожніх manual slots. Це штатний `ConvertEmptyStringsToNull` на POST,
  не втрата відповіді. Probe тепер нормалізує тільки порожні manual values,
  зберігаючи індекси та всі непорожні значення. Лічильники/порядок/відповіді
  збігалися ще до нормалізації.
- Перший додатковий SEO smoke помилково очікував 200 від `source=theory`;
  код і наявний feature-test підтверджують навмисне очищення query через
  302 на чистий локальний URL. Виправлено очікування probe, не застосунок.
  Початковий невдалий результат збережено окремо, фінальний — 9/9.

## 3. Автоматичні регресії та M1 smoke

| Перевірка | Фактичний результат |
|---|---|
| Новий повтор M1 перед commit 1 | 75 tests / 688 assertions, 27.666 s |
| Окремо thread environment | 4 tests / 8 assertions |
| Окремо server state + synonyms | 28 tests / 101 assertions |
| Сукупний фінальний PHP M1+M2 | **107 tests / 797 assertions**, 49.876 s, exit 0 |
| Node behavioral persistence + classifier | **12/12 PASS**, 114.4 ms |
| Локальний SEO smoke після видалення observer hook | **9/9 PASS** |
| Passive Voice `--dry-run` | `status=clean`, 0 blocks/pivots/metadata changes/conflicts |

PHP suite запущено 02:16:59–02:17:49 за Києвом. Runner перевірив testing,
SQLite `:memory:` (включно з PRAGMA), array cache/session до schema-змін.
Кожен запуск отримав окремі config/routes/packages/services caches, compiled
views і повний Laravel storage root; тестовий ключ існує тільки в процесі.
[State fixture](../../tests/Feature/SavedTestJsStateTest.php) додатково відмовляється
працювати поза ізольованою конфігурацією і не запускає question snapshot observer.

M1 production-профіль перевірений **локальним Laravel kernel**, без зовнішнього
HTTP: правильний canonical/robots для `/questions`, технічний noindex JSON,
курсові canonical, safe inline HTML/XSS, scoped Passive Voice repair.
Додаткові живі `.loc` GET підтвердили:

- HTML `/test/future-perfect/questions`: 200, canonical з кінцевим `/questions`;
  навмисний development `X-Robots-Tag: noindex, nofollow, noarchive` збережений.
- `?source=theory`: 302 на чистий `.loc` path. Той самий чистий path із
  `Accept: application/json`: 404; справжній JSON endpoint із подвійним
  `/questions/questions?mode=saved-test-js-v2`: 200 та технічний noindex.
- Parts of Speech у theory/course: 200, один спільний canonical на теорію.
- Present Perfect Forms і Collective Nouns: немає буквальних strong/span.
- Passive Voice Formation Rules: 200, немає службових fixture-вставок.

Банк незмінний: **84 питання**, SHA-256 нормалізованої структури
`47f7cf6c04b9ddad174749eacab6aa5aa6863d1f80438e49c2f6811e0e2cf101`,
як до M1. Навчальні UUID, тексти, відповіді, рівні й snapshots не редагувалися.

## 4. Відтворення

Виконувані файли беруться з PATH або параметрів середовища; конкретні локальні
шляхи runtime не зашиті в репозиторій. Докладні обмеження і opt-in observer:
[tools/diagnostics/README.md](../../tools/diagnostics/README.md).

```sh
python tools/diagnostics/local-get-series.py --phase thread-safe-before-tests --count 30 --workers 1
python tools/diagnostics/local-get-series.py --phase thread-safe-before-tests --count 30 --workers 3
python tools/diagnostics/run-isolated-tests.py --php php --include-m2 --label m1-m2-final
node --test tests/Browser/saved-test-persistence.test.cjs tests/Browser/state-request-classification.test.cjs
python tools/diagnostics/local-get-series.py --phase thread-safe-after-tests --count 30 --workers 1
python tools/diagnostics/local-get-series.py --phase thread-safe-after-tests --count 30 --workers 3
node tools/diagnostics/probe-state-network.cjs
node tools/diagnostics/local-state-browser.cjs final
python tools/diagnostics/local-seo-smoke.py --label final
php artisan seo:repair-passive-voice-debug --dry-run
```

У фактичних командах `--php` вказував на наявний XAMPP PHP; Playwright/Chromium
обрані через `PLAYWRIGHT_MODULE`/`CHROMIUM_EXECUTABLE`. Усі результати й невдалі
спроби залишені локально в `storage/app/seo-m2-local/`, не в комітах. Для нового
запуску використовуйте нові phase/label, щоб не перезаписувати свої докази.

## 5. Конфігурація, публікація й залишковий ризик

- **Локальних конфігураційних змін поза Git немає:** `.env`, робочий APP_KEY,
  APP_ENV, php.ini, Apache vhost, hosts, OPcache та робочий config cache не
  змінені. APP_ENV залишено production, SiteMode development визначається host.
  Не потрібен секретний конфіг для застосування M2.
- Додано короткий workflow [AGENTS.md](../../AGENTS.md): самері, локальні
  перевірки, commit/push у робочу гілку, окремий дозвіл для деплою, без секретів
  та сторонніх незавершених змін.
- M2 не змінює навчальну БД. Тільки звичайні гостьові session-записи браузерних
  сценаріїв; повторний Passive Voice apply, міграції/сідери робочої БД не запускались.
- PPC audit-файли, `.codex/`, попередні raw evidence, archives/dumps/backups
  залишено на місці поза комітами. Наявність стороннього dirty worktree навмисна.
- У Git — код, тести, інструменти та два очищені звіти. Локальні DB-операції
  M1 не є «запушеною базою». Немає HTTP/SSH/DB/deployment API/SC звернень до
  production; GitHub fetch/push не є деплоєм.
- Залишкове непідтверджене: точний interleaving історичних 500 і внутрішня
  Chromium-причина позначення успішного 204 як aborted. Для старого 500
  публікується перевірене усунення відтворюваного threaded-env механізму,
  а не гарантія відсутності будь-яких майбутніх 500. Для save підтверджено
  справність описаних сценаріїв, не контракт збереження після завершення
  повністю нової гостьової сесії.
