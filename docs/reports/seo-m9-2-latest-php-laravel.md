# M9.2 — локальне оновлення XAMPP PHP і Laravel

Дата перевірки: 12.09.2026. Робочий сайт: `http://gramlyze.loc`.
Production `.com` і `.ub`, SSH, деплой, міграції, сідери та робоча MariaDB не використовувалися.

## Результат

Локальний Gramlyze вже працює на PHP 8.5.10 TS x64 та Laravel 13.31.0. Новий
Composer lock versioned, а локальний Apache відповідає саме новим PHP runtime.
Оновлено сумісні прямі залежності, PHPUnit-тести, generated package discovery і
PHP 8.5 у чотирьох CI workflows. Навчальні дані не змінювалися.

Окреме системне обмеження: активний глобальний Composer launcher залишився на
2.8.8. Штатний `composer self-update 2.10.3` отримав `Permission denied` на
`C:/ProgramData/ComposerSetup/bin/composer.phar`; ACL не обходилися. Прийнятий
lock і clean candidate installs були зібрані Composer 2.10.3 у приватній
локальній області. Це не впливає на робочий PHP/Laravel runtime, але системний
launcher слід оновити вручну від адміністратора перед наступною dependency-операцією.

## База й джерела

- Відправна гілка/commit: `codex/seo-m9-1-composer-reproducibility`,
  `473296261a657eb3fd51658cc70c54689e7f529e`; історія M1–M9.1 є предком цього
  етапу. Робоча гілка: `codex/seo-m9-2-latest-php-laravel`.
- Обрані стабільні версії на 12.09.2026: PHP 8.5.10, Laravel 13.31.0 і Composer
  2.10.3. Джерела: [PHP Windows downloads](https://windows.php.net/downloads/releases/),
  [PHP Apache installation](https://www.php.net/manual/en/install.windows.apache2.php),
  [Laravel releases](https://github.com/laravel/framework/releases),
  [Laravel 11](https://laravel.com/docs/11.x/upgrade),
  [12](https://laravel.com/docs/12.x/upgrade), [13](https://laravel.com/docs/13.x/upgrade)
  upgrade guides і [Composer CLI](https://getcomposer.org/doc/03-cli.md).
- Офіційний PHP archive:
  `https://windows.php.net/downloads/releases/php-8.5.10-Win32-vs17-x64.zip`;
  SHA-256 `a6bc8b2f3d7bfb397ccb973db2f959e61e530e0986c9cea262dd4a317ec599d8`.

## PHP/XAMPP: до → після

| Перевірка | До | Після |
| --- | --- | --- |
| XAMPP PHP | 8.2.12 | 8.5.10 |
| CLI | PHP 8.2.12 | `C:/Program Files/xampp/php/php.exe`, PHP 8.5.10 |
| Apache | старий `apache2handler` runtime | PHP 8.5.10, `apache2handler` |
| Laravel CLI / HTTP probe | 10.50.2 | 13.31.0 |
| Архітектура | x64 | x64, VS2022, Thread Safe (ZTS) |

Новий PHP спочатку перевірено в окремій приватній директорії, після чого активний
`C:/Program Files/xampp/php` переключено на TS build. Старий runtime збережено
як `C:/Program Files/xampp/php-8.2.12-m9-2-previous`. Apache handler в
`httpd-xampp.conf` уже посилається на динамічний `C:/Program Files/xampp/php/...`,
тому файл Apache не змінювався; XAMPP Shell і Machine PATH також уже посилалися
на цей шлях. Apache перезапущено точково як foreground XAMPP process.

Активний `php.ini`: `C:/Program Files/xampp/php/php.ini`; додаткових ini немає.
Перевірені extensions: `bcmath`, `curl`, `dom/xml`, `fileinfo`, `gd`, `intl`,
`mbstring`, `mysqli`, `openssl`, `pdo_mysql`, `pdo_sqlite/sqlite3`, `zip`.
`httpd -t` завершився `Syntax OK`; наявне стороннє попередження про відсутній
DocumentRoot `D:/DEV/htdocs/vsemerch.loc/frontend/web` не належить Gramlyze.

## Laravel і залежності

`composer.json` тепер вимагає PHP `^8.5`, `laravel/framework ^13.0`, Sanctum
`^4.0`, Tinker `^3.0`, Livewire `^4.0`, `openai-php/laravel ^0.20`; dev-набір
оновлено до Debugbar `^4.0`, Collision `^8.0`, PHPUnit `^12.0` і Ignition
`^2.12`. У lock зафіксовано прийнятий dependency graph (SHA-256
`6897cff07cbf36201f8eb7dc9f987905f698f770c1cbcc26a3f22da89cf59595`).

Laravel 13 змінив API query grammar constructor і PHPUnit 12 змінив data-provider
attributes; ці сумісні правки покриті тестами. Session serialization явно лишено
`php`, щоб не змінювати формат наявних local sessions. Debugbar namespace
зроблено сумісним із v4 package discovery без доступу до dev package у no-dev.
CI workflows `admin-flows-smoke`, `contentops-release-gate`,
`public-flows-smoke`, `theory-smoke` використовують PHP 8.5. Deployment
triggers, permissions і workflow dispatch не змінювалися.

Дві незалежні приватні копії без початкового vendor пройшли чистий install:
require-dev і `--no-dev --optimize-autoloader`; no-dev kernel не має PHPUnit,
Tests namespace або Debugbar. У робочому root встановлено прийнятий dev vendor;
`vendor` не додається в Git.

## Перевірки

| Перевірка | Результат |
| --- | --- |
| Candidate M9 PHP suite | PASS: 249 tests, 4 116 assertions; PHPUnit 12.5.35, PHP 8.5.10; 1 framework/package deprecation |
| Real disposable MySQL 9.4 | PASS: 36 tests, 394 assertions; PHP 8.5.10, Laravel 13.31.0; builder і sitemap HTTP kernel PASS |
| MySQL ownership guard | PASS: 46 739 protected files, changed 0; owned server stopped normally |
| Composer candidate validate / platform / audit | PASS; platform requirements satisfied, audit `advisories: []`, `abandoned: []` |
| Root validate / platform | PASS під активним PHP 8.5.10; Composer launcher 2.8.8 показав лише власні deprecation notices |
| Pint | PASS для змінених PHP файлів |
| Browser | Локальна сторінка Questions завантажена: 84 завдання, A1–C2, HTML ручного вводу, переклад і перемішані token groups доступні |

Вторинна повна suite була зупинена на 61 з 1 397 тестів, бо це значно ширший
набір, ніж погоджений M9 regression batch; її результат не зараховується.

Реальні GET без авторизації на `http://gramlyze.loc` після switch:

| URL | HTTP | Час |
| --- | ---: | ---: |
| `/` | 200 | 361 ms |
| `/theory` | 200 | 237 ms |
| `/theory/future-perfect` | 200 | 451 ms |
| `/theory/basic-grammar/sentence-types` | 200 | 902 ms |
| `/test/future-perfect/forms` | 200 | 2 272 ms |
| `/test/future-perfect/questions` | 200 HTML | 3 171 ms |
| `/test/future-perfect/questions/questions` | 200 JSON | 2 161 ms |
| `/test/future-perfect/time-expressions` | 200 | 1 962 ms |
| `/courses/english-grammar-theory` | 200 | 222 ms |
| One/Ones і Reciprocal lessons | 200 | 502 / 258 ms |
| `/theory/passive-voice` | 200 | 1 988 ms |
| `/robots.txt`, `/sitemap.xml` | 200 | 4 / 2 594 ms |
| legacy `/tests/cards` | 302 | 108 ms |
| unknown course/theory path | 404 | 1 191 / 51 ms |

Temporary loopback-only version endpoint підтвердив `php=8.5.10`,
`sapi=apache2handler`, `laravel=13.31.0`; він видалений, а повторний GET повертає
404. Порядок sitemap та SEO contracts перевірені M9 suite; локальний sitemap
має 554 URL. Публічні frontend inputs не змінювалися, а наявний manifest/assets
лишається узгодженим; окремий rebuild не був потрібний.

## Дані, backups і відкат

До switch створено приватний backup `storage/app/seo-m9-2-local/backups/20260912-144925`:
старі PHP/config, робочий vendor, composer files, manifest/assets і локальний DB
dump. Dump SHA-256:
`ab342d01582eb513c64ce203aa2ea60a7b8a5f3b5a75f587d220cef042b5ee8e`
(120 573 927 bytes). Таблиці й triggers збережено; events/routines не включені,
бо чинна local MariaDB має стару помилку `mysql.proc`. Ні schema, ні навчальні
дані, ні сідери/міграції не змінювалися.

Повернення: (1) зупинити лише Apache process з його pid; (2) повернути
`php-8.2.12-m9-2-previous` як активний `php`; (3) повернути private old vendor,
composer files і tracked source revision; (4) відновити saved package caches за
потреби; (5) стартувати Apache та перевірити `php -v`, `artisan --version` і GET
`/`. Не відновлювати DB dump для самого runtime rollback, бо дані не змінювалися.

## Інші hosts та залишкові обмеження

XAMPP PHP є shared runtime: синтаксис Apache пройшов, але сторонні vhosts і
phpMyAdmin функціонально не тестувалися, щоб не розширювати scope. Під час
наступного локального сеансу їх слід smoke-перевірити окремо. Старі guest
cookies, створені до switch, неможливо ретроспективно перевірити після оновлення;
конфігурація зберігає старий PHP session serialization і cookies/sessions не
очищувалися. Production, PR, merge, deployment та GitHub Actions не запускалися.
