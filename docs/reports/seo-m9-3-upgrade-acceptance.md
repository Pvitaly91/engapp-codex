# M9.3 — завершення приймання після оновлення PHP/Laravel

Дата перевірки: 12.09.2026. Усі реальні HTTP і браузерні перевірки виконано
лише на `http://gramlyze.loc`. Production `.com` і `.ub`, SSH, деплой,
міграції, сідери, робоча БД та навчальні дані не використовувалися.

## Результат

M9.3 закриває залишки M9.2: generated Laravel manifests більше не
відстежуються Git, для проєкту є перевірений Composer 2.10.3 launcher, два
незалежні чисті встановлення пройшли з фінального versioned набору, а всі
вісім браузерних сценаріїв пройшли на новому локальному стеку.

Нових змін до PHP/Laravel dependency graph, `composer.json`,
`composer.lock`, `.env`, схеми, питань, відповідей, UUID, рівнів, `verb_hint`,
metadata, definitions, redirects або навчальних сторінок не внесено.

## База, Git і межі

| Параметр | Значення |
| --- | --- |
| Базовий HEAD M9.2 | `d55b8cf76153f09dc36cbd9f6ef0fd8aca72d138` |
| Робоча гілка M9.3 | `codex/seo-m9-3-upgrade-acceptance` |
| `origin/codex/seo-m9-2-latest-php-laravel` після `git fetch --prune origin` | `d55b8cf76153f09dc36cbd9f6ef0fd8aca72d138` |
| `origin/main` після fetch | `c77b4326a92b2c1e92c80b07393d8e7000c0fe33` |
| PHP | `8.5.10` TS, VS2022 x64 (`C:/Program Files/xampp/php/php.exe`) |
| Laravel | `13.31.0` |
| Livewire | `4.4.4` |
| PHPUnit | `12.5.35` |

На старті в worktree вже були сторонні незакомічені зміни: 46 646 видалених
question JSON-файлів, два змінені runtime audit-файли та 18 untracked позицій.
Вони не належать M9.3, не відновлювалися, не ховалися, не потрапляють до
commit і не використовувалися як результат цієї перевірки.

Локальні workflow-файли перевірено read-only: їхні `push`-тригери обмежені
`main`, а PR-тригери — `main`. M9.3 не робить PR, merge, workflow dispatch або
push у `main`.

## Generated bootstrap cache

До M9.3 у Git відстежувалися лише три файли каталогу `bootstrap/cache`:

```text
bootstrap/cache/.gitignore
bootstrap/cache/packages.php
bootstrap/cache/services.php
```

З індексу знято тільки generated manifests:

```text
bootstrap/cache/packages.php
bootstrap/cache/services.php
```

Локальні runtime-копії не видалялися. `bootstrap/cache/.gitignore` збережено;
його правило `*` із винятком для `.gitignore` продовжує ігнорувати нові
`packages.php`, `services.php`, `livewire-components.php` та тимчасові
manifests. Після зміни `git ls-files bootstrap/cache` містить тільки
`bootstrap/cache/.gitignore`.

Відсутність залежності від закомічених manifests доведена двома чистими
інсталяціями нижче: кожна самостійно згенерувала власні `packages.php` і
`services.php`, а локальний сайт паралельно пройшов HTTP/browser приймання.
Робочі caches і learner sessions масово не очищувалися.

## Підтримуваний Composer launcher

Глобальний launcher лишився:

```text
C:/ProgramData/ComposerSetup/bin/composer.bat
Composer 2.8.8
```

Він не оновлювався: попередня спроба M9.2 отримала `Permission denied` у
захищеному `C:/ProgramData/ComposerSetup`, а M9.3 не обходить ACL/UAC і не
повторює ту саму операцію без зміни умов доступу.

Натомість створено versioned wrapper:

```text
tools/composer.cmd
tools/composer.ps1
```

Рекомендована команда в корені проєкту:

```powershell
.\tools\composer.cmd install
```

Wrapper знаходить repository root через `$PSScriptRoot`, запускає активний
XAMPP PHP (або явно заданий `GRAMLYZE_PHP`), перевіряє PHP 8.5.x, checksum PHAR
та фактичну версію Composer перед передаванням аргументів. Він не завантажує
Composer, не виконує неявний `install`/`update` і повертає реальний exit code.

PHAR лежить у постійному приватному, непублічному та ignored каталозі
`storage/app/gramlyze-composer/composer-2.10.3.phar`:

| Перевірка | Значення |
| --- | --- |
| Composer | `2.10.3` |
| SHA-256 | `7a2d379d5b8ffdaa028580ef26494c36d2feef4b178d3dd1473a4dbc5e17c8d6` |
| PHP для запуску | `C:/Program Files/xampp/php/php.exe`, `8.5.10` |

Перевірки з нового shell:

- `tools\composer.cmd --version` — exit `0`, Composer 2.10.3 / PHP 8.5.10;
- `tools\composer.cmd validate --no-check-publish` — exit `0`;
- `--working-dir "C:\Program Files\xampp\php" --version` — exit `0`, що
  перевіряє шлях із пробілами;
- безпечна неіснуюча Composer-команда повернула exit `1`, тобто wrapper не
  маскує код завершення.

`storage/app/gramlyze-composer/` і `storage/app/seo-m9-3-local/` додані до
`.gitignore`; PHAR, credentials, cache, logs та clean-install evidence не
додаються в Git.

## Чисті dev і no-dev встановлення з фінального набору

Прийняті докази збережені лише локально в:

```text
storage/app/seo-m9-3-local/composer-clean-20260912-4/dev
storage/app/seo-m9-3-local/composer-clean-20260912-4/no-dev
```

Джерело — `git archive` від `d55b8cf…`, SHA-256 архіву
`c1e184de6a4092cf2f5c6b733ed16dae2c343ae6af2ab0cab4530fbc62032b4f`.
Єдиний технічний виняток — versioned Windows-incompatible symlink
`public/storage`, який Windows `tar` не може матеріалізувати; він не є
Composer input. Кожна копія на старті не мала `vendor`, `.env`,
`bootstrap/cache/packages.php` або `bootstrap/cache/services.php`.

`composer.lock` у робочому корені має CRLF SHA-256
`6897cff07cbf36201f8eb7dc9f987905f698f770c1cbcc26a3f22da89cf59595`; той самий
Git blob в archive має LF SHA-256
`51521a312e6bf7faca6c869b53cc16bdbf3f015e382bf4a89d02e4ab28e03f7a`.
Це різниця лише в line endings: обидва parse до однакового Composer
`content-hash` `83c68a915037e0e8fb7f510379e0e151`.

Для кожної незалежної копії пройшли з exit `0`:

```text
composer validate --strict --no-check-publish
composer install [--no-dev --optimize-autoloader для production-копії]
composer check-platform-reqs --lock
composer audit --locked --format=json
composer install повторно
```

Обидва online audit відповіли `advisories: []`, `abandoned: []`; lock hash не
змінився. Повторні installs коротко зафіксували Packagist `curl error 7`, після
чого використали свіжий локальний cache і завершилися з exit `0`; це
зафіксоване мережеве обмеження, не прихований успіх audit.

| Копія | Результат |
| --- | --- |
| dev | 122 packages; discovery згенерував 10 manifest entries, Debugbar присутній як очікується; `artisan --version`, `artisan about` і прямий Laravel bootstrap — exit `0`. |
| no-dev | 82 packages; discovery згенерував 6 production-only manifest entries; `artisan --version`, `artisan about` і bootstrap — exit `0`; `vendor/phpunit/phpunit`, Debugbar, `Tests\\` PSR-4 та класи PHPUnit/Debugbar/`Tests\\TestCase` відсутні. |

У no-dev приватний loopback `GET /login` дав `200` без Debugbar/provider error.
`GET /` дав `500` лише через навмисно порожню SQLite copy (query `HomeController`
до відсутньої БД), а не через відсутній dev provider. Це обмеження ізольованого
стенду, не результат для робочого `gramlyze.loc`.

Ранні приватні каталоги `composer-clean-…-2` і `…-3` не є evidence: вони
відкинуті через помилку test runner у Windows absolute cache path, а не через
помилку versioned application code.

## HTTP і browser-приймання

Перед першою браузерною навігацією чинний M9 runner встановив route/CDP guard:
`.com`, `.ub`, external navigation, unexpected redirects і непланові writes
заблоковані. У всіх восьми сценаріях `blocked` був порожній; production не
відкривався.

Fresh HTTP matrix:

```text
storage/app/seo-m9-local/m9-3-browser-acceptance-http.json
```

має `pass: true`: 14 локальних GET-перевірок, representative HTML/JSON,
canonical/robots та повний ordered sitemap. Sitemap має **554 URL** і збігається
з baseline. Evidence ignored і не входить до commit.

| Сценарій | Desktop 1440×1000 | Mobile 390×844 |
| --- | --- | --- |
| Теорія | PASS: 295 sidebar links, current marker, collapse, theme + reload | PASS: mobile menu, sidebar state, theme + reload |
| Future Perfect Questions | PASS: 84 JSON questions, відповідь `0 → 1`, реальний POST save `204`, reload і server-only restore збережених order/position/counters | PASS: ті самі інваріанти |
| Курс | PASS: тільки тимчасовий власний client progress `locked → current`, штатний перехід на One/Ones lesson і reload | PASS: ті самі інваріанти |
| One/Ones | PASS: source-equivalent blocks 2, 5, 7, 8, 9, включно з нижніми правилами та вправами | PASS: ті самі інваріанти |

Файл `storage/app/seo-m9-local/m9-3-browser-acceptance-browser.json` має
`pass: true`, 8/8 rows і 16 локальних screenshots. Console не містить
application page errors, немає `>=400` responses або unexpected failed
requests. Google Fonts були заблоковані середовищем (`ERR_NETWORK_ACCESS_DENIED`)
та враховані окремо, без зміни шрифтів чи дизайну. У Questions збережено raw
`ERR_ABORTED` state POST після підтверджених `204`; вони не приховані, а
класифіковані через незалежне server restoration.

### Livewire та Alpine

- Кожна публічна сторінка завантажила локальний `GET 200`
  `/livewire-c9acd0d9/livewire.min.js`; установлене versioned джерело —
  Livewire `4.4.4`.
- `window.Livewire` існує, Alpine `3.17.2` реально ініціалізувався рівно один
  раз; Vite `catalog-public` CSS/JS відповіли `200`.
- На перевірених публічних сторінках немає mounted `wire:id` /
  `<livewire:…>` компонента, отже відсутній штатний публічний update endpoint,
  який можна чесно викликати.
- Замість додавання функції для тесту виконано private no-write fixture:
  `Livewire::test(App\Http\Livewire\WordsTest::class)->call('closeFailureModal')`
  підтвердив `showFailureModal=false`, `SESSION_DRIVER=array` і `0` DB write
  queries. Це in-process proof, не HTTP update proof.

Окремий Node regression batch пройшов: **15/15**, exit `0`
(`seo-m9-browser`, `state-request-classification`, `saved-test-persistence`).

## Цільові PHP регресії

Protected isolated runner виконав лише механізми цього етапу, не повний набір
із 1 397 тестів:

```text
SmokeIsolationTest
SavedTestJsStateTest
SavedTestStepActionsTest
SavedTestStepNavigationTest
SavedTestStepOrderTest
SavedTestStepQuestionNumberTest
SavedTestStepUniqueCountTest
CanonicalUrlTest
SeoRobotsTest
SitemapTest
CourseSitemapMetadataTest
MainTheoryTestSitemapReadinessTest
OneOnesEditorialPatchTest
TheoryInlineHtmlRenderingTest
```

Результат JUnit isolated run: **104 tests, 1 137 assertions, 0 errors,
0 failures**. Runner працював з окремими SQLite/runtime/cache paths; робочі
`.env`, DB, sessions і bootstrap caches не очищувалися.

## XAMPP, phpMyAdmin та інші loopback hosts

Перевірки були тільки GET на loopback без входу й без адмінських дій:

| Host | Фактичний результат | Класифікація |
| --- | --- | --- |
| `gramlyze.loc` | `200` | Gramlyze на PHP 8.5.10 |
| `localhost/phpmyadmin/`, `127.0.0.1/phpmyadmin/` | `200`, але body містить `Access denied!` / MySQL 1045 | локальна DB-auth/config, не успішний login |
| `lara.loc` | `200`, Laravel welcome | сторонній сайт працює |
| `adminer.loc` | `200`, Login - Adminer | сторонній сайт працює |
| `xml-mapper.loc` | `404` власної app | routing/app state |
| `e-shpop.loc` | timeout 9 s, без bytes | невизначена app/runtime nonresponse |
| `vsemerch.loc` | `403` | відсутній configured DocumentRoot |
| `vs.loc` | `500`, `Undefined variable $banner` у власному Yii view | дефект стороннього застосунку, не доведена PHP 8.5 несумісність |

### Важливе обмеження shared XAMPP Apache

Після switch на PHP 8.5.10 Apache error log на кожному старті фіксує неможливість
завантажити `curl` (`specified procedure could not be found`). Це Apache-only
конфлікт shared DLL dependencies: `mod_ssl` завантажує Apache OpenSSL 3.1.3,
тоді як PHP 8.5 cURL має залежності від однойменних PHP OpenSSL 3.5.7 DLL.
Офіційний PHP 8.5.10 archive (SHA-256
`a6bc8b2f3d7bfb397ccb973db2f959e61e530e0986c9cea262dd4a317ec599d8`) не містить
окремого `libcurl.dll` для копіювання.

Тому M9.3 навмисно **не** вимикає cURL, не копіює вигаданий DLL, не міняє
порядок `LoadFile` і не замінює OpenSSL у спільному XAMPP: це могло б зламати
TLS та всі local hosts. Безпечне виправлення потребує окремої ізольованої
перевірки сумісного Apache/mod_ssl/PHP bundle або process isolation. Усі
Gramlyze browser-сценарії при цьому пройшли; обмеження залишається задокументованою
передумовою окремого XAMPP maintenance завдання.

## Дані, sitemap, backup і старі сесії

- Міграції, сідери, data/content commands, restore, MariaDB repair і робочі DB
  writes не запускалися. Єдині допустимі записи — власний тимчасовий guest test
  state у браузерному сценарії, не навчальні дані.
- Fresh HTTP sitemap comparison підтвердив ті самі 554 впорядковані URL.
  M9.3 не додає і не stage-ить question/definition/page files; наявні сторонні
  worktree changes збережені окремо.
- M9.2 local backup лишається в
  `storage/app/seo-m9-2-local/backups/20260912-144925`; DB dump SHA-256
  `ab342d01582eb513c64ce203aa2ea60a7b8a5f3b5a75f587d220cef042b5ee8e`.
  Це не повна перевірена копія всіх DB objects: events/routines відсутні через
  наявну помилку `mysql.proc`. M9.3 не запускає `mariadb-upgrade`, repair або
  restore.
- Реальні cookies, створені до PHP switch, ретроспективно не перевірялися й не
  очищувалися. Нова browser acceptance використовувала лише fresh guest
  contexts; session serialization не змінювалася. Це не доказ сумісності всіх
  історичних сесій.

## Зміни, що мають увійти до M9.3 commit

```text
.gitignore
bootstrap/cache/packages.php              (вилучення з Git)
bootstrap/cache/services.php              (вилучення з Git)
tools/composer.cmd
tools/composer.ps1
docs/reports/seo-m9-3-upgrade-acceptance.md
```

Не додаються PHP/Composer binaries, `vendor`, `.env`, auth data, backups/dumps,
runtime cache/logs, private screenshots/cookies/evidence або сторонні незавершені
зміни.

## Фінальний статус

Приймання виконано на оновленому gramlyze.loc та в ізольованих локальних
копіях. Навчальні дані не змінювалися. Commit і push виконані. Production
.com/.ub не перевірялися й не оновлювалися; PR, merge та деплой не виконувалися.
