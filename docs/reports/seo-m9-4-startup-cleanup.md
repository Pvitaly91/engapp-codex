# M9.4 — запуск через apache_start.bat

13.09.2026. База: `452b100b0797089fa8d12e89277eeb8810ce2758`.
Гілка: `codex/seo-m9-4-xampp-curl-tls`. Робота лише в локальних Gramlyze/XAMPP.

**Статус: причину доведено; ремонт запуску НЕ застосовано і НЕ прийнято.**
Підготовлений кандидат замінює дві Apache OpenSSL DLL. Це межа окремого
погодження з пункту 2 завдання. Чинний Apache працює з несправним shared cURL;
успішні GET Gramlyze не доводять відсутності Windows-діалогу під час cold start.

## Фактичний запуск і причина

`C:/Program Files/xampp/apache_start.bat:2,8` виконує `cd /D %~dp0`, потім
`apache\bin\httpd.exe`, без аргументів або зміни environment. Чинний parent
`16388`, creation UTC `2026-09-13T11:15:16.9979120Z`, запущений `cmd.exe` PID
`26296` з `/C "C:\Program Files\xampp\apache_start.bat"`; child `37964` має
`-d "C:/Program Files/xampp/apache"`. Owner — звичайний локальний admin, не SYSTEM.
Ці PID — evidence, а не параметри майбутнього restart; elevation/error mode
початкової консолі не виміряні.

Читання поточних process parameters підтвердило cwd `C:/Program Files/xampp/`
і успадкований PATH із `C:/Program Files/xampp/php`. Process-level `PHPRC`,
`PHP_INI_SCAN_DIR`, `OPENSSL_CONF`, `OPENSSL_MODULES` відсутні. Це відрізняється
від Apache request-level `SetEnv` у `httpd-xampp.conf:5–12`:
`PHPRC=\xampp\php`, `OPENSSL_CONF=C:/Program Files/xampp/apache/bin/openssl.cnf`.
Приватний snapshot містить тільки дозволені PATH/PHP/OpenSSL/system fields;
інші environment values не виводилися.

`apache\bin\httpd.exe -V` підтвердив Apache 2.4.58 VS17 x64 і compiled
`SERVER_CONFIG_FILE=conf/httpd.conf`. Запуск із cwd bat без `-d/-f` із
`-t -D DUMP_INCLUDES` фактично прочитав
`C:/Program Files/xampp/apache/conf/httpd.conf`; ServerRoot у ньому той самий.
Отже, знайдений config збігається з M9.4, хоча спосіб запуску інший.
Всі 29 системних hashes baseline M9.4 збігаються.

В обох живих httpd завантажені `apache/bin/libssl-3-x64.dll` і
`libcrypto-3-x64.dll` **3.1.3**, `mod_ssl.so` 2.4.58, shared `php8ts.dll` і
`php8apache2_4.dll` **8.5.10**. `php_curl.dll` відсутня серед модулів.
PE-аудит доводить її імпорт `SSL_get0_group_name` саме з `libssl-3-x64.dll`:
Apache DLL не експортує символ, PHP DLL 3.5.7 експортує. Символ додано в
[OpenSSL 3.2](https://docs.openssl.org/3.5/man3/SSL_get0_group_name/).
[Windows loader](https://learn.microsoft.com/en-us/windows/win32/dlls/dynamic-link-library-search-order)
повторно використовує вже завантажену однойменну DLL; PATH або PHPIniDir не
додають до неї відсутній export.

Початковий діалог зафіксований як повідомлення користувача, не як новий screenshot.
`initial-error.json` зберігає його текст, offset/час log і відповідні рядки:
після bat-start parent `16388` та child `37964` записали `PHP Startup: Unable to
load dynamic library 'curl' ... specified procedure could not be found`;
parent продовжив роботу о 14:17:17, child — о 14:17:19 за локальним часом.
Ці події були до нашого втручання; новий cold start не виконувався.

## Активні handlers і споживачі

У `apache/conf/extra/httpd-xampp.conf:17–27,41,45` активні три LoadFile
(`php8ts.dll`, `libpq.dll`, `libsqlite3.dll`), `LoadModule php_module`,
`PHPINIDir C:/Program Files/xampp/php`, `.php → application/x-httpd-php`,
`.phps → application/x-httpd-php-source`, AddType. Shared ini має
`extension_dir="ext"`, `extension=curl`, `extension=openssl`.

| Споживач shared mod_php | Конфіг / фактичний стан |
| --- | --- |
| `lara.loc`, HTTP localhost/unmatched host | `httpd-vhosts.conf:44`; перший `*:80`, PHP entrypoint існує |
| `adminer.loc`, `diyxml.loc`, `xml-mapper.loc`, `e-shpop.loc` | vhosts `72,85,99,112`; PHP entrypoints існують |
| `vs.loc`, `/admin` | vhosts `158,165`; frontend/backend PHP існує |
| `/phpmyadmin`, `/webalizer` | xampp `91,98`; глобальні aliases, PHP файли існують, зокрема через HTTPS і host Gramlyze |
| default HTTPS `_default_:443` | ssl `121`; XAMPP htdocs існує і містить PHP |
| `vsemerch.loc`, `/admin` | vhosts `124,131`; обидва налаштовані корені відсутні |

`/php-cgi/` — окремий ScriptAlias із доступом тільки до `php-cgi.exe`;
`/cgi-bin/` задає `cgi-script` для PHP, але PHP файлів там немає. `/icons/`,
`/licenses` не містять PHP. Залежні `php_admin_flag` є в xampp `69–70,101–102`.
Handler/ini overrides у перевірених `.htaccess` consumer roots та ancestors
не знайдені. Shared PHP глобально вимикати не можна.

Активний include tree: mpm, autoindex, languages, userdir, info, vhosts, proxy,
default, xampp, ssl, ajp. Userdir-блок неактивний без модуля; `proxy-html.conf`
не включається без `proxy_html_module`. Gramlyze include активний двічі:
vhosts `70` і ssl `308`; `fcgid_module` — main config `149`.
Gramlyze public має власний NTS wrapper, PATH/PHPRC/scan-dir, recycling і
allowlist. Новий worker `34288` після локального GET завантажив cURL/OpenSSL
із `php-8.5.10-nts-gramlyze`, як у прийнятій M9.4 базі.

## Точний diff і обсяг окремого погодження

**Застосований config diff: порожній.** `apache_start.bat`, shared/NTS ini,
Apache configs і binaries не змінено. Ніякі придушення Windows-діалогів,
вимкнення extensions, зміни LoadFile або служб не застосовувалися.

Кандидат на наступний погоджений етап: після ізольованої runtime/TLS перевірки
замінити лише цю пару в `C:/Program Files/xampp/apache/bin/`:

| Файл | Поточний SHA-256 | Кандидат SHA-256 |
| --- | --- | --- |
| `libssl-3-x64.dll` | `cf0e4d008a748057a3dd638496a2c4c033a7d33ea4f4cd96f2f747a61f9d4748` | `dd76bebb8a13731a1bd047232c77299e7fa1fe9c8ef6d1563ca059473088630a` |
| `libcrypto-3-x64.dll` | `8bb143f97cb31089d50f59be1846a8e163fa8ac215792e5fa51dc92a7b5152e9` | `4978b06f18c1d092e4f7c8c864cc814db2ff4535baa2de54937348ffe14aae5e` |

Джерело — вже наявний PHP 8.5.10 TS VS17 x64 archive, SHA-256
`a6bc8b2f3d7bfb397ccb973db2f959e61e530e0986c9cea262dd4a317ec599d8`;
hash повторно збігається з [офіційним SBOM](https://downloads.php.net/~windows/releases/archives/php-8.5.10-Win32-vs17-x64.zip.cdx.json).
Обидва DLL із archive побайтово збігаються зі shared PHP. Нічого не встановлено.

Усі перевірені OpenSSL-імпорти curl, php_openssl, libpq, mod_ssl, apr_crypto_openssl
задовольняються новою парою; старій бракує curl-символу. Нова libssl потребує
21 crypto-символ, яких немає у старій libcrypto: одиночна заміна непридатна.
[Політика OpenSSL](https://openssl-library.org/policies/general/versioning-policy/)
підтримує API/ABI сумісність у напрямку старий споживач → новіша minor library;
це передумова, а не доказ Windows runtime acceptance. Обсяг впливу — весь
Apache TLS/shared PHP, включно з aliases і сторонніми сайтами.

Альтернатива — окремий maintained shared TS PHP 8.4 runtime/ini, але вона
змінює PHP для всіх shared consumers і потребує їх перевірки сумісності.
Сайти Laravel 12 вимагають PHP ≥8.2; повернення до старого XAMPP PHP 8.0/8.1
неприпустиме. Цю міграцію також не виконували.

## Backup, перевірки та залишкові умови

Новий приватний backup:
`storage/app/seo-m9-4-startup-local/backup-20260913-143341-225/`.
10 копій перевірено SHA-256: bat, main/xampp/vhosts/ssl/fcgi configs, shared/NTS
ini, дві старі Apache DLL. Старі backups M9.4 не перезаписані.
Поруч `rollback-openssl.ps1`: без параметрів перевіряє hashes; `-Restore`
повертає лише дві DLL після окремої адресної зупинки Apache, відмовляє за
живого instance/невідомого hash. Verification пройшов; restore не виконувався.
Перед майбутнім застосуванням повторити inventory і створити свіжий backup.

| Перевірка цього етапу | Результат |
| --- | --- |
| Фактичний config `httpd -t` із cwd bat | exit 0, Syntax OK, окремий AH00112 |
| CLI PHP / `tools/composer.cmd --version` | exit 0; 8.5.10 TS / Composer 2.10.3 |
| 7 GET: головна, теорія, Questions HTML/JSON, course, sitemap, 404 | PASS; порівняння з фінальною M9.4 базою без змін |
| Повний ordered sitemap | 554 loc; ordered equality, added/removed 0 |
| Новий PE-аудитор | 7 unit tests PASS; фактичні DLL прочитані без виконання |
| Збереження системи й сторонньої роботи | 69 protected files без змін; 81 809 сторонніх Git status entries збігаються |
| Cold start через нову адміністративну консоль/bat | НЕ ВИКОНАНО: зупинка перед погодженням заміни Apache DLL |
| Відсутність нового shared cURL warning після ремонту | НЕ ПРИЙНЯТО: ремонт не застосовано |
| Прямий cURL HTTPS через .loc, TLS negative test, save/reload, сторонні PHP HTTP/HTTPS після ремонту | НЕ ПОВТОРЮВАЛИСЯ; обов'язкові на етапі застосування |

Приватні evidence — у `storage/app/seo-m9-4-startup-local/`; HTTP comparison —
`storage/app/seo-m9-4-local/startup-initial-20260913-http.json`.
Тимчасових web endpoints/fixtures не створено. Apache і MariaDB не зупинялися.
Чужі початкові помилки сайтів не оголошені виправленими.

**DocumentRoot: НЕ ВИПРАВЛЕНО окремо.** `httpd-vhosts.conf:126` задає
`D:/DEV/htdocs/vsemerch.loc/frontend/web`; пов'язані paths — `131,133,144`.
Frontend/backend відсутні. Наявний `vs.loc` уже має власний vhost і hosts entry,
тому це не доведена заміна адреси. Vhost/Include збережені; потрібна фактична
адреса або підтвердження виведення сайту з експлуатації.

Початковий Git status збережено приватно: 46 646 видалень question JSON,
3 сторонні modified paths і 35 160 untracked entries при `-uall`
(включно з щойно створеним status evidence). Reset/stash/clean не виконувалися.
До commit входять лише ignore для нової приватної папки, PE-аудитор, його тести
і цей звіт; staged diff перевіряється явно. SHA commit/push та remote equality
повідомляються після публікації у цю робочу гілку. PR, main push, merge, SSH,
production requests і деплой не виконуються.
