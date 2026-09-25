# M9.4 — запуск через apache_start.bat

13.09.2026. База `452b100b0797089fa8d12e89277eeb8810ce2758`, гілка
`codex/seo-m9-4-xampp-curl-tls`. **Ремонт застосовано та прийнято:** повний cold
start через незмінений `apache_start.bat` пройшов без Entry Point Not Found.
Shared mod_php збережено; Gramlyze залишається на прийнятому NTS/FastCGI.

## Фактичний запуск і причина

`C:/Program Files/xampp/apache_start.bat:2,8`: `cd /D %~dp0`, потім
`apache\bin\httpd.exe`, без параметрів або environment overrides. Cwd — XAMPP;
фактичні ServerRoot/config — `C:/Program Files/xampp/apache` і `conf/httpd.conf`.
Це підтвердили process parameters, `-V`, `-t -D DUMP_INCLUDES` із cwd bat;
конфіг збігається з M9.4. Apache 2.4.58 VS17 x64.

Початкові PID перечитані: parent 16388, creation UTC
`2026-09-13T11:15:16.9979120Z`, cmd parent 26296 із запуском bat, child 37964
із `-d "C:/Program Files/xampp/apache"`. Обидва завантажили Apache OpenSSL 3.1.3
та shared PHP 8.5.10 TS, але не `php_curl.dll`. PATH містив shared PHP;
process-level PHPRC/PHP_INI_SCAN_DIR/OPENSSL_CONF/OPENSSL_MODULES були відсутні.
Request-level SetEnv у xampp config не дорівнює process environment.

PE-аудит і runtime isolation довели два конфлікти однойменних DLL:

- Apache libssl 3.1.3 не має `SSL_get0_group_name`, який імпортує PHP cURL;
  символ додано в [OpenSSL 3.2](https://docs.openssl.org/3.5/man3/SSL_get0_group_name/).
  [Windows loader](https://learn.microsoft.com/en-us/windows/win32/dlls/dynamic-link-library-search-order)
  повторно використовує вже завантажену однойменну DLL.
- Після заміни OpenSSL-пари в ізоляції виявлено Apache libssh2 1.10.0:
  відсутні `libssh2_crypto_engine`, `libssh2_session_callback_set2`,
  `libssh2_session_set_read_timeout`. Кандидат 1.11.1 задовольняє імпорти cURL
  і зберігає старі named exports. Серед перевірених Apache bin/modules та cURL
  прямий libssh2-споживач — php_curl.dll; nghttp2 змінювати не потрібно.

Початковий діалог збережений приватно як повідомлення користувача, без заяви
про screenshot. `initial-error.json` містить також відповідні log entries:
`PHP Startup: Unable to load dynamic library 'curl' ... specified procedure
could not be found` для початкових parent/child.

## Точний diff і споживачі

**Текстовий config diff порожній.** Bat, Apache directives/includes,
shared/NTS php.ini, PHP handlers, httpd.exe та mod_ssl.so не змінені.
Після окремого погодження бібліотек і додаткового погодження локальної
сертифікатної пари замінено рівно п'ять файлів під `C:/Program Files/xampp/`:

| Файл | Було → стало |
| --- | --- |
| `apache/bin/libssl-3-x64.dll` | OpenSSL 3.1.3 → 3.5.7 |
| `apache/bin/libcrypto-3-x64.dll` | OpenSSL 3.1.3 → 3.5.7 |
| `apache/bin/libssh2.dll` | 1.10.0 → 1.11.1 |
| `apache/conf/ssl.crt/server.crt` | Прострочений 2019 року RSA1024 без SAN → self-signed RSA3072/SHA256, до 13.09.2027 |
| `apache/conf/ssl.key/server.key` | Відповідний новий локальний RSA3072 ключ |

SAN: localhost, gramlyze.loc, www.gramlyze.loc, 127.0.0.1, ::1; сертифікат
використовують обидва наявні HTTPS vhosts. Trust store не змінено.
Три DLL — з уже наявного офіційного PHP 8.5.10 TS VS17 x64 ZIP, SHA-256
`a6bc8b2f3d7bfb397ccb973db2f959e61e530e0986c9cea262dd4a317ec599d8`,
звіреного з [офіційним SBOM](https://downloads.php.net/~windows/releases/archives/php-8.5.10-Win32-vs17-x64.zip.cdx.json).
Повні before/after hashes DLL закріплені в інструменті та приватному manifest;
сертифіката/ключа — лише в приватному manifest/evidence.

Shared LoadModule php_module, LoadFile php8ts/libpq/libsqlite3, PHPINIDir,
`.php → application/x-httpd-php`, `.phps → application/x-httpd-php-source`
активні у `httpd-xampp.conf:17–45`; curl/openssl extensions увімкнені.

| Фактичний shared споживач | Перевірка / стан |
| --- | --- |
| lara.loc, HTTP localhost/unmatched host | Перший *:80; PHP працює, app 200 |
| adminer.loc, diyxml.loc, xml-mapper.loc, e-shpop.loc | PHP працює; початкові app statuses збережені |
| vs.loc, /admin | Обидва PHP handlers працюють; наявні app 500/302 збережені |
| /phpmyadmin, /webalizer | Глобальні aliases, також на host Gramlyze та HTTPS; PHP збережено |
| Default HTTPS _default_:443 | XAMPP htdocs, PHP і перевірений HTTPS працюють |
| vsemerch.loc, /admin | Налаштовані корені відсутні; попередні 403, див. окремий статус |

`/php-cgi/` — explicit CGI ScriptAlias; `/cgi-bin/` не містить PHP;
`/icons/` і `/licenses` також без PHP. Залежні php_admin_flag збережені;
consumer .htaccess PHP overrides не знайдені. Shared mod_php вимикати не можна.
Активні includes: mpm, autoindex, languages, userdir, info, vhosts, proxy,
default, xampp, ssl, ajp; userdir-блок і proxy-html залежать від неактивних модулів.
Gramlyze FastCGI include лишився у HTTP vhost:70 та SSL config:308, із власними
NTS wrapper/PATH/PHPRC/scan-dir/CA/allowlist; повторної інсталяції не було.

## Cold start і приймання

Лише вихідний Apache адресно зупинено через його native WinNT shutdown event,
з перевіркою owner, exe, parent/child, held process handle і точного native
creation FILETIME; дочекалися завершення Apache/NTS workers та звільнення портів.
MariaDB PID 6964, creation UTC `2026-09-12T09:13:41.1571930Z`, залишився тим самим.

Перша повна спроба лише з трьома DLL завершилася `AH02562 / ee key too small`:
новий OpenSSL відхилив старий ключ. DLL негайно повернуто з backup. Повна
ізольована копія конфігурації відтворила цю помилку і пройшла з новою парою
сертифікат/ключ, shared PHP та NTS через HTTP/TLS 1.2/1.3.

Прийнятий запуск: **13:45:47 UTC (16:45:47 Київ)**, нова видима elevated
PowerShell 7 консоль PID 8252, звичайний admin, ErrorMode **0**. Нормальний
user environment від Windows без успадкування agent environment; stdio не
перенаправлено. Консоль виконала саме незмінений bat. Нові Apache PID
14076/33656 перейшли до normal operations за ~1,5 с; cURL та всі три потрібні
DLL завантажені в обох. Діалогу не виявлено; автоматичного натискання OK не було.
Нічого не пригнічувалося через SetErrorMode, приховування або stderr.

| Перевірка після bat cold start | Результат |
| --- | --- |
| Фактичний config syntax | Syntax OK; AH00112 окремо |
| Нові main/Gramlyze SSL/shared PHP logs від записаних offsets | Жодного cURL load / entry point / fatal TLS startup error |
| Gramlyze runtime | PHP 8.5.10 NTS cgi-fcgi, 15 config checks; живі workers завантажують власні NTS DLL |
| Прямий cURL через .loc | PHP.net/getcomposer 200, verify result 0; Laravel HTTP 200 із verification |
| TLS позитивний / негативний | Локальний 204/verify 0; wrong hostname відхилено, errno 60 |
| CLI / tools/composer.cmd --version | PHP 8.5.10 TS / Composer 2.10.3, exit 0 |
| Головна, теорія, Questions HTML/JSON, course, sitemap, 404 | 7/7 GET; page/metadata comparison без змін |
| Повний ordered sitemap | 554 → 554, точна ordered equality, added/removed 0 |
| Desktop/mobile theory/Questions | 4/4 browser scenarios; реальні POST 204, reload і окремий server restore |
| Shared PHP | 13/13 HTTP + 3/3 HTTPS probes; apache2handler 8.5.10, cURL завантажений, наявні extensions збережені |
| Shared cURL HTTPS | PHP.net 200, errno 0, verify result 0; peer=true, host=2 |
| Сторонні apps | Усі 10 app statuses дорівнюють BEFORE; початкові app errors не ремонтувалися |
| Інструменти | 20 Python unit tests + 8 filesystem transaction tests PASS, PowerShell parse PASS |
| Збереження роботи | 81 809 сторонніх Git entries однакові; 67/69 protected hashes незмінні, дві очікувані OpenSSL зміни; усі 5 replacements перевірені |

## Backup, rollback та обмеження

Остаточний приватний run/backup:
`storage/app/seo-m9-4-startup-local/apply-20260913-162158-183-cc97de63/`.
13 SHA-перевірених копій: 8 bat/config/ini + 5 змінюваних файлів. Усі попередні
M9.4 backups та backup невдалої спроби збережено. Транзакція перевіряє всі
джерела/цілі/backups до першого запису; помилка копіювання повертає весь набір.
Rollback при реальному блокуванні третього й п'ятого файлів перевірений тестами.

Адресний rollback із кореня repo в адміністративному PowerShell:

```powershell
$startupRun = 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-startup-local\apply-20260913-162158-183-cc97de63'
$startupTool = '.\tools\diagnostics\xampp-startup\maintain-runtime.ps1'
& $startupTool -Phase StopCurrent -RunDirectory $startupRun -ExpectedOwner 'DESKTOP-3C05HGF\admin'
& $startupTool -Phase Wait -RunDirectory $startupRun -ExpectedOwner 'DESKTOP-3C05HGF\admin'
# Виконати Restore тільки після успішного Wait.
& $startupTool -Phase Restore -RunDirectory $startupRun -ExpectedOwner 'DESKTOP-3C05HGF\admin'
& $startupTool -Phase Start -RunDirectory $startupRun -ExpectedOwner 'DESKTOP-3C05HGF\admin' -LaunchLabel rollback
```

Rollback відновлює початкові п'ять файлів, отже поверне і початковий cURL-діалог;
це аварійне повернення baseline. StopCurrent щоразу перечитує ідентичність
живого bat instance, не використовує PID зі звіту. Для завершеного застосування
rollback достатньо цього run з manifest/apply і backups; тимчасовий ізольований
стенд не є його залежністю.

Тимчасові PHP probes/route/nonce/fixtures прибрані; routes/api.php відновлений
byte-for-byte зі сторонніми змінами користувача, діагностичний endpoint — 404.
Приватні runtime/browser/log докази й backups не входять у Git. Browser мав
блокування Google Fonts; 8 ERR_ABORTED state requests зіставлено з POST 204 і
server restore; неочікуваних failures/page errors/HTTP ≥400 — 0.

**DocumentRoot: не виправлено, окремо.** `httpd-vhosts.conf:126` задає відсутній
`D:/DEV/htdocs/vsemerch.loc/frontend/web`; пов'язані paths:131,133,144. Правильний
шлях не доведений; vs.loc уже має власний vhost. Vhost/Include збережено,
потрібна фактична адреса або підтвердження, що vsemerch більше не потрібний.

Локальний сертифікат self-signed: OS/browser trust не встановлювався; перевірки
довіряли лише точному cert із hostname validation. Залишається AH01909 для
старого default ServerName www.example.com:443; його не змінювали. Перевірено
PHP виконання та незмінні app statuses споживачів, не всі їхні бізнес-сценарії.
Apache 2.4.58 лишився без загального оновлення XAMPP.

До публікації обираються явно лише очищені diagnostics/templates, їхні тести
та цей звіт; staged diff і git diff --check перевіряються перед commit.
Laravel/vendor/lock, навчальні дані, дизайн і стороння робота не входять до
commit. Push — лише в поточну M9.4-гілку з перевіркою remote SHA = HEAD; без
main/force push, PR, merge, production .com/.ub, SSH або деплою.
Підсумковий SHA наведено у відповіді.
