# M9.1 — Composer: перевірений блокер у межах Laravel 10

Дата звіту: 2026-09-11, Europe/Kyiv. Перевірки залежностей виконані 2026-09-10 UTC. Проєкт: Gramlyze.

## Результат для власника

**Composer-блокер M9 не закритий.** Безпечний сумісний lockfile у погоджених межах **PHP 8.2.12 / Laravel 10 / Livewire 3** не отримано. Це вже не лише проблема випадкового `symfony/css-selector` 8.1.0: актуальний Composer resolver із security blocking відхиляє доступні Laravel **10.10.0–10.50.3** через security advisories. Потрібне окреме рішення щодо major-оновлення framework; у M9.1 воно заборонене й не виконувалося.

Доведено окремо:

- фактичний installed inventory — 117 пакетів, 38 розбіжностей із старим lock;
- файли всіх 117 установлених пакетів звірено з офіційними pinned archives: **8 993 файли, 0 changed/missing/extra**;
- **62 355 захищених робочих файлів незмінні**, включно з `.env`, vendor, bootstrap/cache, compiled views, навчальними sources/snapshots і сторонніми файлами;
- **9 нових self-tests діагностичного інструмента — PASS**;
- робоча головна після guard — HTTP 200; це контроль **старого** vendor, не приймання нового.

**Dev/no-dev clean install, новий 21-suite PHP-набір, no-dev kernel та loopback browser не виконувалися:** немає дозволеного безпечного resolved lock для їх запуску. Історичні M9 249/4112 не видаються за новий PASS. Непрацездатний lock не доданий у Git. Робочі `composer.json`, `composer.lock`, vendor та CI залишені без змін. Збережені звіт, діагностичний інструмент, його тести та ignore приватних доказів.

## 1. База і межі

Прочитано `AGENTS.md`, `docs/reports/seo-m9-local-release-readiness.md`, composer.json/lock, обидва installed metadata, `.gitignore`, усі чотири чинні workflows, protected runner, `tests/bootstrap.php` та `bootstrap/app.php`.

Після `git fetch origin`:

| Ref | Фактичний SHA |
| --- | --- |
| Початковий HEAD / origin M9 | `29d33689db43a40d7a1e9bb2b9577423a2bdf7af` |
| origin/main | `c77b4326a92b2c1e92c80b07393d8e7000c0fe33` |

Гілка `codex/seo-m9-1-composer-reproducibility` створена від фактичного M9 HEAD. Попередньої local/remote M9.1 не було. Не було reset/merge/cherry-pick/stash/clean/force. Сторонні PPC audit-файли, `.codex/`, архіви та приватні матеріали збережені й не включаються в commit. Історичний звіт M9 не переписано.

Production `.com/.ub`, SSH, серверна БД, deployment API і Search Console не використовувалися. Дозволені мережеві джерела: GitHub, Composer/Packagist та їхня документація; один кінцевий контрольний GET `.loc`. Установлення/зміни системного PHP, Apache, hosts, `.env`/APP_KEY, робочих dependencies, caches або навчальних даних не виконувалися.

## 2. Три джерела залежностей та платформа

### Фактична платформа

- PHP binary: `C:/Program Files/xampp/php/php.exe`; **8.2.12 ZTS**, Windows x64; завантажений `C:/Program Files/xampp/php/php.ini`, додаткових ini немає.
- Системний Composer: **2.8.8** (`C:/ProgramData/ComposerSetup/bin/composer.phar`), залишився незмінним.
- Приватний Composer: **2.10.3**, отриманий з [офіційного versioned download](https://getcomposer.org/download/2.10.3/composer.phar), 3 642 137 bytes; SHA-256 звірений із `.sha256sum`: `7a2d379d5b8ffdaa028580ef26494c36d2feef4b178d3dd1473a4dbc5e17c8d6`. Це приватний інструмент із чинною blocking policy, не автоматичне оновлення application stack або системного Composer.
- Глобального Composer `config.json` немає. Глобальний проект містить `laravel/installer ^5.16`; його vendor не підключався. У початковому process environment не було `COMPOSER*` overrides. Auth-файли/секрети не виводились.
- Приватний процес має окремі HOME/COMPOSER_HOME/cache/TEMP та явний OS environment allowlist. Не успадковує APP/DB credentials, Composer auth, proxy, no-audit/no-blocking/ignore-platform flags або global Git config. TLS verification не вимикалась.

Фактично завантажені PHP extensions: bcmath, bz2, calendar, Core, ctype, curl, date, dom, exif, fileinfo, filter, ftp, gd, gettext, hash, iconv, json, libxml, mbstring, mysqli, mysqlnd, openssl, pcre, PDO, pdo_mysql, pdo_sqlite, Phar, random, readline, Reflection, session, SimpleXML, SPL, standard, tokenizer, xml, xmlreader, xmlwriter, Zend OPcache, zip, zlib. Для діагностики OPcache CLI вимикався лише аргументом приватного процесу.

Старий lock вимагає composer-plugin-api/runtime-api та ctype, curl, dom, fileinfo, filter, hash, iconv, json, libxml, mbstring, openssl, pcre, phar, session, tokenizer, xml, xmlwriter і lib-pcre. Вони пройшли actual platform-check; **PHP requirement не пройшов**. pdo_sqlite потрібний для майбутніх ізольованих fixtures, pdo_mysql — для чинного локального сайту. Остаточний набір requirements нового release ще не встановлений. Значення `^8.1` у старому composer.json не є заявою про перевірену підтримку PHP 8.1.

### Composer.json проти lock та installed

Root constraints: `php ^8.1`, `laravel/framework ^10.10`, `livewire/livewire ^3.0`, `guzzlehttp/guzzle ^7.2`, `laravel/sanctum ^3.3`, `laravel/tinker ^2.8`, `openai-php/laravel ^0.11.0`; PHPUnit `^10.1` у require-dev. Direct/transitive dependencies не перетворювалися на штучні direct pins.

Старий lock: **77 production + 40 dev = 117**. Installed: **117**, з них 40 dev; `installed.php` і `installed.json` погоджуються за version/reference для всіх пакетів. Laravel 10.50.2, PHPUnit 10.5.63, CommonMark 2.8.2, php-http/discovery 1.20.0 — однакові у lock/installed. Installed metadata використано лише як inventory/тимчасові resolver constraints, не перейменовано чи перетворено на lockfile.

Повні 38 розбіжностей до M9.1:

| Пакет | Робочий installed | Старий ignored lock |
| --- | --- | --- |
| `barryvdh/laravel-debugbar` | `v3.16.1` | `v3.16.5` |
| `guzzlehttp/guzzle` | `7.10.0` | `7.10.6` |
| `guzzlehttp/promises` | `2.3.0` | `2.4.1` |
| `guzzlehttp/psr7` | `2.9.0` | `2.10.4` |
| `guzzlehttp/uri-template` | `v1.0.5` | `v1.0.6` |
| `laravel/pint` | `v1.29.0` | `v1.29.1` |
| `laravel/sail` | `v1.56.0` | `v1.61.0` |
| `league/flysystem` | `3.33.0` | `3.34.0` |
| `livewire/livewire` | `v3.7.15` | `v3.8.0` |
| `nette/utils` | `v4.1.3` | `v4.1.4` |
| `php-debugbar/php-debugbar` | `v2.2.4` | `v2.2.6` |
| `psy/psysh` | `v0.12.22` | `v0.12.23` |
| `spatie/flare-client-php` | `1.11.0` | `1.11.1` |
| `symfony/console` | `v6.4.36` | `v6.4.41` |
| `symfony/css-selector` | `v7.4.8` | `v8.1.0` |
| `symfony/deprecation-contracts` | `v3.6.0` | `v3.7.0` |
| `symfony/event-dispatcher` | `v7.4.8` | `v7.4.9` |
| `symfony/event-dispatcher-contracts` | `v3.6.0` | `v3.7.0` |
| `symfony/http-foundation` | `v6.4.35` | `v6.4.41` |
| `symfony/http-kernel` | `v6.4.36` | `v6.4.41` |
| `symfony/mailer` | `v6.4.34` | `v6.4.40` |
| `symfony/mime` | `v6.4.36` | `v6.4.41` |
| `symfony/polyfill-ctype` | `v1.36.0` | `v1.37.0` |
| `symfony/polyfill-intl-grapheme` | `v1.36.0` | `v1.38.1` |
| `symfony/polyfill-intl-idn` | `v1.36.0` | `v1.38.1` |
| `symfony/polyfill-intl-normalizer` | `v1.36.0` | `v1.38.0` |
| `symfony/polyfill-mbstring` | `v1.36.0` | `v1.38.1` |
| `symfony/polyfill-php80` | `v1.36.0` | `v1.37.0` |
| `symfony/polyfill-php83` | `v1.36.0` | `v1.38.1` |
| `symfony/polyfill-uuid` | `v1.36.0` | `v1.37.0` |
| `symfony/process` | `v6.4.33` | `v6.4.41` |
| `symfony/routing` | `v6.4.34` | `v6.4.41` |
| `symfony/service-contracts` | `v3.6.1` | `v3.7.0` |
| `symfony/string` | `v7.4.8` | `v7.4.13` |
| `symfony/translation` | `v6.4.34` | `v6.4.38` |
| `symfony/translation-contracts` | `v3.6.1` | `v3.7.0` |
| `symfony/yaml` | `v7.4.8` | `v7.4.13` |
| `voku/portable-ascii` | `2.0.3` | `2.1.1` |

Таблиця **реально змінених** пакетів порожня: змінено **0**, новий accepted lock відсутній. Наведена вище таблиця — не перелік виконаних оновлень.

## 3. Resolver і точний блокер

Приватний каталог: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m9-1-local/candidate`. Він не має vendor, `.env`, symlink/junction до робочих даних. Це metadata-resolver copy, **не** готова application/dev/release копія. Для коректного `why` скопійовані лише шість реальних root classmap source-файлів; вони не bootstrap-ились. Початкові classmap warnings у першому `why` збережені, потім усунені в інструменті без зміни вимог пакетів.

Старі composer.json/lock та обидва installed metadata збережено приватно до будь-якої заміни. SHA-256 старого lock:
`c06f976273101f3a0cab86604a56843720655591b4176905bde56941f545dc90`.
Та сама сума у working lock і candidate lock після обох невдалих resolver-запусків: нового lock не створено. Package requirements/content-hash вручну не редагувалися.

Лише в candidate JSON спробовано `require.php ^8.2.12`, `config.platform.php 8.2.12`, allowlist тільки `php-http/discovery: true` замість також дозволеного, але не встановленого pest-plugin. Extensions не імітувалися. Laravel 10 / Livewire 3 / PHPUnit 10 та інші root ranges збережено. Ці candidate-правки **не перенесено в робочий composer.json**, бо кандидат не пройшов resolver.

Основні виконані команди всередині candidate (кожна через приватний Composer з `--no-plugins --no-scripts --no-interaction`, PHP `-d opcache.enable_cli=0`):

```text
why symfony/css-selector --locked --tree
check-platform-reqs --lock
audit --locked --format=json
update --no-install --prefer-dist --minimal-changes --audit-format=json --with=<name:installed-version> ...
config --list --source
update laravel/framework symfony/css-selector guzzlehttp/guzzle livewire/livewire league/commonmark --with-all-dependencies --minimal-changes --no-install --prefer-dist --audit-format=json
validate --strict --no-check-publish
```

Перша update-команда має **117 тимчасових `--with` constraints**, згенерованих з inventory; повний argv збережений у приватному evidence. Це справжній Composer resolver, а не конструювання lock із installed.json. Другий targeted update з `-W` знімає ці тимчасові pins, залишаючи погоджені root major-межі, та дозволяє потрібні транзитивні зміни. Не було необмеженого update у робочому корені, ні install, ні vendor:publish/create-project/key:generate, ні scripts/discovery.

| Перевірка | Exit | Фактичний результат |
| --- | ---: | --- |
| `why ... --locked --tree` | 0 | Root → Laravel 10.50.2 (`^10.10`) → CssToInlineStyles 2.4.0 (`^2.2.5`) → css-selector 8.1.0 (`^5.4 || ^6 || ^7 || ^8`) |
| Actual `check-platform-reqs --lock` | 1 | PHP 8.2.12 не задовольняє css-selector `>=8.4.1`; інші показані platform requirements успішні |
| Audit старого lock | 1 | 25 advisory records у 5 пакетах; abandoned `[]`; feed доступний |
| Installed-constrained update | 2 | Відомо вразливі встановлені версії та частина dependency constraints заблоковані |
| Targeted update без installed pins | 2 | Root `laravel/framework ^10.10`: знайдені 10.10.0–10.50.3, але вони заблоковані advisories |
| Candidate strict validate | 2 | JSON валідний, lock застарілий щодо приватних змін PHP/platform; не accepted lock |

У targeted update Problem 1 прямо називає `PKSA-m5cs-t1y6-qpcs`, `PKSA-3r5d-mb8f-1qw9`, `PKSA-mdq4-51ck-6kdq`, `PKSA-8qx3-n5y5-vvnd`, `PKSA-w7xr-vk7n-rstm`. Додатковий Problem 2 проходить через `openai-php/laravel 0.11.0 → laravel/framework ^9.46.0|^10.34.2|^11.29.0|^12.0`. Тобто відмова не спричинена лише pin на 10.50.2 або старим css-selector.

Офіційне advisory Laravel для [CRLF injection / CVE-2026-48019](https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq) вказує виправлення у 12.60.0 / 13.10.0; [Temporary Signed URL Path Confusion / CVE-2026-48041](https://github.com/laravel/framework/security/advisories/GHSA-crmm-hgp2-wgrp) — у 12.61.1 / 13.12.0. Laravel 10 залишається в affected ranges. Це достатня причина зупинити формування release у заданих межах; наведені версії виправлень **не є новим погодженим dependency baseline** і не замінюють майбутній повний audit. Реальну експлуатованість у конкретних маршрутах Gramlyze тут не досліджували.

### Audit: не прихований «0 vulnerabilities»

| Пакет у старому lock | Версія | Advisory records |
| --- | --- | ---: |
| guzzlehttp/guzzle | 7.10.6 | 9 |
| guzzlehttp/psr7 | 2.10.4 | 2 |
| laravel/framework | 10.50.2 | 3 |
| league/commonmark | 2.8.2 | 10 |
| livewire/livewire | 3.8.0 | 1 |

Це **25 записів, не 25 гарантовано унікальних вразливостей**: Laravel email advisory присутній під двома PKSA IDs з різних джерел. Повний JSON містить IDs, ranges, dates, source links і severity. Audit виконано для старого lock, **не для нового resolved набору або всього installed inventory**; у робочому vendor частина версій ще старіша, тому не слід вважати цю таблицю вичерпним audit робочого сайту. Abandoned = 0 саме в цьому audit, не гарантія підтримки стеку. Навіть майбутній нуль advisories не означатиме повної безпеки.

Policy доказ: приватний config показує `[policy] true (default)`, `[audit.abandoned] fail`, `[secure-http] true`, `[disable-tls] false`, `[platform.php] 8.2.12`, `[platform-check] php-only`, allow-plugins лише php-http/discovery. На додачу виконаний повний actual platform-check. Security blocking/audit/платформні вимоги не відключалися; advisory ignores, forks/path repositories, підміна package versions або правки vendor platform_check не додавалися. Текст Composer із пропозиціями вимкнути захист залишений у приватному stderr як помилка, **не як рекомендована дія**.

Перша strict validate також зафіксувала Git parent-discovery/ownership warning від sandbox-user. Інструмент обмежив `GIT_CEILING_DIRECTORIES` приватною областю та ізолював Git config. Цільова повторна validate прибрала ownership warning, але закономірно лишила exit 2 через незгенерований lock. Global `safe.directory` не змінювали; обидва результати збережені.

## 4. Робочий vendor: джерела, ручні правки, незмінність

Усі 117 пакетів мають public GitHub dist. Installed version/source/dist refs звірені з офіційною Packagist metadata, завантаженою Composer у чистий cache. Metadata minification розгортається лише для read-only порівняння inventory, без зміни requirements або створення lock. Архіви отримані по HTTPS з GitHub codeload за повними 40-character commit refs, SHA-256 кожного архіву збережений. Вміст ZIP **не розпаковувався у vendor і не виконувався**: лише побайтове порівняння.

Перший запуск: 116 пакетів перевірені, один консервативно зупинений через URL drift. `barryvdh/laravel-debugbar` 3.16.1 тепер має офіційний source `fruitcake/laravel-debugbar`, той самий reference `21b2c6fce05453efd4bceb34f9fddaa1cdb44090`. Окремий follow-up тільки цього пакета звірив офіційний архів: усі 57 файлів однакові. Загалом **117 пакетів / 8 993 файли**, changed/missing/extra = 0. Перший failure не перезаписаний. URL relocation не видано за ручну правку чи необхідність fork.

Composer-generated `vendor/composer/*`/bin/autoload файли не є файлами upstream package archives; вони входять у загальний before/after guard, а обидва installed inventories окремо погоджуються за version/reference. Не стверджується незалежна перевірка походження кожного історичного generated autoload-файлу; їх M9.1 не змінював і не запозичував у новий install.

Окремий working guard, не guard приватного runner: **62 355 → 62 355**, changed = `[]`. Inventory fingerprint до/після:
`fca2e891f26e99a7dfba4d39f49d7ff8ffd4694c07c9284c140d652c0782b756`.

Охоплено робочі `.env`, увесь vendor, bootstrap/cache, database/seeders (включно з learning snapshots/definitions), content-patch manifests, storage/framework (views/sessions/audits), `.codex/` і gramlyze-архіви. Навмисні source-зміни helper/test/report та ignore не включені у protected inventory. Junction/symlink обходи перевіряються й відхиляються; файли після перевірок не відновлювалися. Інструменти не підключалися до робочої БД й не містять SQL/repair/deploy дій; повний DB-hash audit не виконувався.

Після завершення guard, **20:59:46 UTC**, один fresh GET `http://gramlyze.loc/` без cookie/auth/Referer/proxy та без redirects: **200**, `text/html; charset=UTF-8`, title «Англійська граматика: теорія, тести й курси | Gramlyze», X-Robots-Tag `noindex, nofollow, noarchive`. Це тільки контроль незмінного старого сайту. Штатні runtime cache/session записи цього GET не підміняють попередній guard і не свідчать про приймання candidate vendor.

## 5. Що перевірено, а що зупинено

| Контракт | M9.1 статус |
| --- | --- |
| Inventory, actual PHP/platform, official sources та package byte comparison | Виконано; platform failure записаний, 117 packages byte check PASS |
| Resolver при встановлених versions і в дозволених major ranges | Обидва завершені exit 2 через advisories; без install |
| Dev install з require-dev, autoload/discovery/plugins | **Не виконано** — безпечного lock немає |
| Release `--no-dev --optimize-autoloader`, no-dev kernel | **Не виконано**; приватних dev/release vendor paths не створено |
| InstalledVersions / Reflection Laravel / PHPUnit саме candidate vendor | **Не виконано**, candidate vendor не існує; старий не використано замість нього |
| Новий M9 batch із 21 suite, no-dev fixtures та SEO kernel | **Не виконано**, історичні 249/4112 не повторно зараховані |
| Loopback browser desktop/mobile, Livewire/Alpine/save/reload/server restore | **Не виконано**; браузер/stand не запускався, production guard не видається за перевірений тут |
| Clean/repeated install і lock-hash invariance під install | **Не виконано**; доведено лише незмінність старого lock після failed resolver |
| Platform checks installed/no-dev та audit нового candidate | **Не виконано**, нового installed набору немає |
| Діагностичні self-tests | **9 PASS**: ізоляція env/security overrides, download host/TLS guard, inventory boundaries, exclusive evidence, заборона робочого vendor, junction refusal, archive diff/traversal, metadata expansion |
| Frontend build/performance/crawl | Не потрібні в заблокованому dependency-кандидаті; inputs/assets не змінено |

Google Fonts, no-dev відсутність Tests/PHPUnit/Debugbar, package discovery та browser runtime нового Livewire не перевірялися, а не «пройшли за замовчуванням». PHP/СУБД production невідомі. Коректної завершеної інструкції dev/no-dev встановлення нового набору поки немає; запуск `composer install` із нинішнім untracked lock не рекомендується як спосіб відтворити M9.

## 6. Відтворення діагностики й приватні докази

Інструмент вимагає Python 3.12+ (Windows junction checks) та явно вибраний локальний PHP. Повторний запуск потребує окремої приватної області/нових labels; baseline та evidence не перезаписуються. Інструмент **не є clean-install або deployment manager** і не може непомітно переключити робочий vendor.

Фактичні команди з робочого repo:

```powershell
$python = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
& $python -B tools/diagnostics/composer-reproducibility.py inventory
& $python -B tools/diagnostics/composer-reproducibility.py download-composer
& $python -B tools/diagnostics/composer-reproducibility.py inspect --label initial
& $python -B tools/diagnostics/composer-reproducibility.py resolve --label installed-constrained-resolve
& $python -B tools/diagnostics/composer-reproducibility.py boundary --label allowed-boundary-resolve
& $python -B tools/diagnostics/composer-reproducibility.py vendor-integrity --label installed-source-check
& $python -B tools/diagnostics/composer-reproducibility.py vendor-integrity --label debugbar-source-followup --package barryvdh/laravel-debugbar
& $python -B tools/diagnostics/composer-reproducibility.py validate --label candidate-validate
& $python -B tools/diagnostics/composer-reproducibility.py validate --label candidate-validate-ceiling
& $python -B -m unittest tests/diagnostics/test_composer_reproducibility.py
& $python -B tools/diagnostics/composer-reproducibility.py guard --label final
```

`boundary` helper у фінальній версії також зберігає strict validate перед resolver; у фактичній послідовності цього приймання validate була окремими пізнішими командами, а не підставленим результатом старого запуску. Команди з очікуваними failures не називаються PASS лише тому, що пізніший self-test повернув 0.

Private root: `storage/app/seo-m9-1-local/`. Не включені в Git: baseline/final guard JSON; old composer/installed копії; Composer phar/checksum; candidate JSON/старий lock; process HOME/cache; повні argv/stdout/stderr `initial-*`, `installed-constrained-resolve`, `allowed-boundary-resolve*`, `candidate-validate*`; всі package archives і JSON integrity reports. Матеріали не містять auth.json/cookies/CSRF і не є release artifacts. Повний package version/source/dist inventory лишився приватним доказом.

## 7. Git, CI та наступне рішення

Після невдалого resolver `composer.json` byte-identical з початковим; working/candidate/backup lock hashes однакові. Root composer.lock **не tracked** і досі ignored: прибрати правило та додати саме цей lock означало б видати невирішений кандидат за виправлення. Застосовано виняток із завдання «не публікуй непрацездатний lock»; у `.gitignore` додана тільки приватна M9.1 область.

Чинні workflows використовують `composer install`, не явний update, але без tracked lock чистий clone і надалі не забезпечує pinned versions. Це **не виправлено** й не замасковано зеленим CI. Нових CI-команд, triggers, permissions, workflow dispatch або deployment не додано. Вузький CI lock/validate/hash gate має сенс після отримання прийнятого lock, а не замість нього.

Перед push повторно перевірено workflows: main push / PR main / manual, немає автодеплою від звичайного push цієї гілки. Активних Git hooks/custom hooksPath не виявлено. GitHub CLI не авторизований, тому read-only branch protection/rulesets API недоступний; це обмеження не обходилося історичними токенами. PR-only main дотримано; приховані серверні integrations не перевірені.

До commit призначені тільки цей звіт, `tools/diagnostics/composer-reproducibility.py`, `tests/diagnostics/test_composer_reproducibility.py` і одна private-ignore правка. Staged diff/secret-path scan/`git diff --check` перевіряються перед commit. Повний кінцевий SHA і звірка з remote наводяться у фінальній відповіді, не самопосилально тут.

**Потрібне окреме рішення:** дозволити окремо сплановане major-оновлення Laravel до підтримуваної сумісної гілки з повторним актуальним audit, узгодженням інших direct/transitive constraints, dev/no-dev clean installs і всім заявленим M9.1 прийманням. Не вмикати advisory ignore/не вимикати security blocking. Підтримуваний спосіб серверного ремонту даних залишається іншою незалежною передумовою й не реалізований у dependency-пакеті.

Офіційні правила, використані для діагностики: [lock/install](https://getcomposer.org/doc/01-basic-usage.md#installing-from-composer-lock), [temporary constraints і targeted update](https://getcomposer.org/doc/03-cli.md#update-u-upgrade), [Composer policy/config](https://getcomposer.org/doc/06-config.md), [install/update script events](https://getcomposer.org/doc/articles/scripts.md). Вони не є дозволом виконати серверне встановлення.

Фактичний статус: resolver та джерела перевірені у приватній локальній області; **новий Composer-набір не отриманий і не прийнятий**. Робочий vendor gramlyze.loc не перевстановлювався. Production `.com/.ub` не перевірялися й не оновлювалися; PR, merge та деплой не виконувалися.
