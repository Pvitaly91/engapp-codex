# M9.4 — cURL/HTTPS у локальному XAMPP

Дата роботи: 12–13.09.2026. Область: `D:/DEV/htdocs/gramlyze.loc` і `C:/Program Files/xampp`; HTTP/browser — тільки `http://gramlyze.loc`.

## Статус приймання

**Локальний ремонт і приймання — PASS.** `http://gramlyze.loc` обслуговує PHP 8.5.10 NTS x64 через
`cgi-fcgi`. Прямі cURL GET до PHP.net і getcomposer.org, Laravel HTTP та локальний позитивний/негативний TLS пройшли після керованого
restart робочого Apache. Сім функціональних GET і повний ordered sitemap збігаються з baseline. Усі шість desktop/mobile сценаріїв
та додатковий restart/recycling пройшли. Probe/fixtures прибрані, навчальні дані та прийняті залежності незмінні.
Фактичні commit SHA, результат push і звірка remote ref наводяться у фінальному повідомленні після Git-операцій.

## База та Git

| Параметр | Зафіксоване значення |
| --- | --- |
| Прийнята база M9.3 / HEAD на початку M9.4 | `87ae50687a31a62a6adea765bc5c0abfa016a170` |
| Поточна робоча гілка | `codex/seo-m9-4-xampp-curl-tls` |
| `origin/codex/seo-m9-3-upgrade-acceptance` | `87ae50687a31a62a6adea765bc5c0abfa016a170` |
| `origin/main` | `c77b4326a92b2c1e92c80b07393d8e7000c0fe33` |
| Remote ref M9.4 на початку роботи | відсутній; фінальна перевірка після push — у повідомленні про завершення |

Продовжено вже наявну локальну гілку та незакомічені M9.4 інструменти. Сторонні видалення question JSON, PPC-аудити, `.codex/`, архіви та
backups збережено. Reset, clean, stash, force push і повернення до старого main не виконувалися. Прочитано чотири GitHub workflows: smoke
push triggers обмежені `main`, ContentOps не має push trigger; ця M9.4 гілка їх не запускає. Активних Git hooks/core.hooksPath немає.
Локальний документ branch protection є описом політики, а не доказом її GitHub-налаштування. Приховані remote integrations не перевірялися.
PR, merge, workflow dispatch і деплой не виконувалися.

## Перевірений installer і джерела

Початковий приватний installer повністю прочитано; його збережена копія:
`storage/app/seo-m9-4-local/staging/install-fastcgi.before-review-20260912-233759-719.ps1`, SHA-256
`84c6b15d6c4a13d75492b5cbb4ea0534894a9ac35eaad41f02f3e2299d84014d`.

Очищений інструмент — `tools/diagnostics/xampp-fastcgi/install-fastcgi.ps1`. Його поточна копія у приватному `staging/install-fastcgi.ps1`
збігається: SHA-256 `215e2a3e3c88b244bde3f916c157e9506dd58e5488cb225ced8b4315d643d7e0`. Installer має `-AuditOnly`; не завантажує latest, не
запускає Composer, Laravel, DB-команди, restart, service installation або ACL-зміни.

| Артефакт | Версія / SHA-256 |
| --- | --- |
| PHP NTS x64 VS17 archive | `8.5.10`; `22ec430195984d233eb9e62c637a945bbcda06efca2f392d9d96d62c6acd34f8` |
| mod_fcgid archive | `2.3.10-win64-VS17`; `cf3ded8953863c68fc522ee48f516565e67e18b180b9e5b147668c04fa5dc46d` |
| `mod_fcgid.so` | `47d2a1bccff5f2560a5b1534af4fa3c9534fd471e2e4d8b160c4a93c58e1af4d` |
| CA bundle | `f66dff1bdf8f96060b8177976f8b7d9254bc89bc4db933d769f7384d28480bc9` |

PHP checksum звірено з офіційними release JSON; приватний доказ: `php-official-release-evidence.json`. Повторне отримання того самого
[архіву Apache Lounge](https://www.apachelounge.com/download/VS17/modules/mod_fcgid-2.3.10-win64-VS17.zip) дало HTTP 200 і точний збіг
staging hash (`fcgid-source-evidence.json`). [CA bundle curl.se](https://curl.se/ca/cacert.pem) звірено з опублікованим checksum; доказ —
`ca-source-evidence.json`. Ці evidence-файли лежать у `storage/app/seo-m9-4-local/` і не комітяться.

Архів Apache Lounge має назву 2.3.10; сам модуль у startup log ідентифікується як `mod_fcgid/2.3.10-dev`.
Локальний `.gitattributes` у каталозі FastCGI інструментів зберігає точні bytes `.ps1`/`.conf`, щоб Windows checkout не змінював SHA-256 pins.

## Адміністративні права та точні системні зміни

Installer перевіряє реальний Windows principal; успішний manifest фіксує `elevated=true`. Системні команди виконуються через штатне адресне
погодження CLI. Захист UAC/Defender/sandbox, власник Program Files, глобальний execution policy і Windows trust store не змінювалися.

Звичайний sandbox-процес мав non-admin token `CodexSandboxOffline`; адресно погоджені системні команди виконувалися як
`DESKTOP-3C05HGF\admin` з elevated High token. Запис у потрібні XAMPP directories перевірено фактично; успішне встановлення
та нові backups підтверджують доступ до runtime, Apache modules і конфігурації. Мережеве обмеження sandbox для GitHub refs
обійдено лише штатним погодженням конкретної команди, без зміни sandbox policy.

Установлено лише:

- `C:/Program Files/xampp/php-8.5.10-nts-gramlyze/` із власними DLL, `php.ini`,
  `conf.d`, log directory та `extras/ssl/cacert.pem`;
- `apache/modules/mod_fcgid.so`;
- `apache/conf/extra/gramlyze-fastcgi.conf`.

У `apache/conf/httpd.conf` додано тільки `LoadModule fcgid_module`. В існуючі **лише Gramlyze** vhosts `*:80` і `*:443` додано
Include; інші директиви цих vhosts і чужі vhosts зберігаються. Глобальні mod_php, mod_ssl, Apache/OpenSSL DLL не замінюються. PHP CLI та
project Composer launcher залишаються на прийнятих версіях.

Власний NTS `php.ini` задає абсолютні extension/CA/error-log paths; сесійні параметри взято з чинної конфігурації. Installer перевірив
`curl`, `openssl`, `pdo_mysql`, `mbstring`, `intl`, XML та інші потрібні extensions. CLI preflight NTS показав cURL TLS backend
`OpenSSL/3.5.7`. Окремий реальний WEB probe нижче підтвердив цей runtime та зовнішній HTTPS.

mod_fcgid володіє workers без TCP listener. `PHP_FCGI_CHILDREN=0`, PHP recycle limit `1000`, Apache limit `500`; Apache має першим
замінювати worker. Шлях wrapper із пробілами має внутрішні лапки для parser mod_fcgid. WEB probe підтвердив 15 безпечних boolean checks:
власні binary/ini/extensions, обидва CA paths, відсутність сторонніх ini, front controller/document root, runtime write access, session
settings, `cgi.fix_pathinfo=0` і recycling env. Worker recycling і browser request persistence підтверджені нижче.

## Backup, невдалі спроби та виправлення

Старий backup `backups/20260912-211115` не перезаписувався. Кожна спроба створює окремий manifest із hashes **фактичних** трьох конфігів,
перевіряє backup і фіксує створені файли для адресного rollback.

1. `backups/install-20260913-002808-311-ba235115`: preflight відхилив
   помилкове припущення про Schannel. Прийнятий PHP cURL використовує OpenSSL;
   установчі файли цієї спроби прибрано до зміни активної конфігурації.
   Додано перевірений окремий CA bundle без послаблення TLS verification.
2. `backups/install-20260913-003246-939-7cdee6b7`: installation і точний
   Apache syntax check дали exit 0. Після activation `/` відповів 200,
   але переписані Laravel URLs, включно з `/sitemap.xml`, дали 403,
   `AH01630 ... public/index.php`. Це критичний збій, а не HTTP PASS.
   Три передремонтні конфігурації повернено з цього backup, syntax перевірено,
   надіслано native restart саме початковому Apache.

Причина — `<DirectoryMatch ".../public/.+">` застосовував заборону PHP також до самого `public/index.php`: Apache порівнює regex із повним
`r->filename`. Це підтверджує [код Apache](https://raw.githubusercontent.com/apache/httpd/2.4.x/server/request.c). Шаблон адресно виправлено
на звичайний `<Directory ".../public/*">`: кореневий front controller зберігає handler, вкладені PHP filenames заборонені. Звичайний
Directory також потрібний для дієвого [`AllowOverride None`](https://httpd.apache.org/docs/2.4/mod/core.html#allowoverride). Підготовлений
template SHA-256: `1e0bc2b3a3ee79d3a3d4c15dbb844f67dec95cdc2b9cefcc653c5bd734a52b81`. Адресний `apply-prepared-handler.ps1` (SHA-256
`5925c453c84175c5a296a592adf940d62f4094049bfeca2dd846d0d4aea9258b`) повторно використав уже перевірені встановлені файли; runtime не
розпаковувався повторно. Безпосередньо перед зміною створено новий перевірений backup
`storage/app/seo-m9-4-local/backups/apply-20260913-013528-271-3c0bd465/`: `httpd.conf`, `httpd-vhosts.conf`, `httpd-ssl.conf`, попередній
inactive `gramlyze-fastcgi.conf`, manifest і rollback script. Syntax дав exit 0. Тільки після цього виконано адресний restart; Laravel
routing відновлено.

## Точний Apache та керований restart

Початковий parent PID `22840`, executable `C:/Program Files/xampp/apache/bin/httpd.exe`, ServerRoot `C:/Program Files/xampp/apache`, config
`apache/conf/httpd.conf`. Перевіряються PID, creation time, child command line та штатний owner. Нову службу, scheduled task або
запуск під SYSTEM не створювали; owner console Apache збережено, початковий elevation невідомий.

`restart-apache.ps1` перевіряє саме цей config через `-d ... -f ... -t`, після Syntax OK одноразово сигналізує наявну WinNT MPM event
`ap22840_restart`. Parent і його обліковий запис зберігаються; MySQL не чіпається. Масового taskkill або довільного `httpd -k restart`
немає.

Початковий observer натрапив на race: child зник між enumeration і GetOwner. Окрема спроба спостереження також мала 30-second timeout. Вони
збережені як невдалі observer evidence, не як успішне приймання. Фактична заміна child спільного Apache тривала приблизно 131 s. Observer
виправлено: дозволено лише підтверджене зникнення child/worker, додано `-ObserveOnly` для продовження спостереження без повторного restart
signal.

Після rollback-сигналу `2026-09-12T21:37:59Z` parent `22840` залишився живим, old child `36836` завершився; mod_fcgid прибрав свого worker
`29296` (у log є штатне forceful termination після очікування). Windows показав `httpd.exe - Entry Point Not Found`: `SSL_get0_group_name`
відсутній у `C:\Program Files\xampp\php\ext\php_curl.dll`. Це старий shared TS runtime, не новий NTS runtime Gramlyze. Саме modal dialog, а
не тривалий робочий startup, блокує продовження parent. Computer Use прочитав і показав діалог, але click, activation і доступна Raise
action повернули `failed to activate captured window` (перша спроба без screenshot також мала `coordinate input geometry is unavailable`).
Користувач закрив діалог. Після цього Apache був зупинений, ports 80/443 вільні; початкові конфігурації залишалися byte-for-byte
відновленими. `start-stopped-apache.ps1` перевірив відсутність instance і точний config, запустив console Apache під тим самим
`DESKTOP-3C05HGF\admin`, без service/task. Новий parent `31316`, creation UTC `2026-09-12T22:34:05.0392090Z`; child `20488`. Launcher мав
elevated token; elevation початкового Apache не виміряно, тому рівність owner не доводить рівність token. Evidence фіксує це обмеження.
ErrorMode launcher до запуску `3`, успадкований child `3`: додаткових bits фактично не внесено, launcher mode відновлено. Shared startup
warnings залишаються в логах; Windows-wide settings, trust store та DLL не змінено. `m94-recovered-baseline-http.json` підтвердив сім GET і
baseline equality. Після corrected apply native event parent `31316` замінив child `20488 → 15980`; реальні WEB HTTPS і HTTP acceptance
після restart — PASS. Додатковий native restart зберіг parent `31316`, замінив child `15980 → 35224`; mod_fcgid прибрав старий worker
`21668` після 8 s очікування і створив worker `44752` під child `35224`. `m94-after-restart-curl-tls.json` та
`m94-after-restart-http.json` повторно дали PASS. Worker не залежить від живого terminal процесу launcher.

`m94-final-system.json`: три точні web processes під тим самим owner; п'ять DLL (`php8`, cURL, OpenSSL, ssl/crypto) завантажені з
власного NTS каталогу. Apache слухає тільки штатні 80/443; PHP TCP listener відсутній. Усі **29** baseline system hashes незмінні,
включно з CLI PHP/ini, shared XAMPP/MPM configs та Apache DLL. Нові log events зіставлено за часом/PID; старі записи не видалялися.
Private terminal formatter помилився вже після запису валідного PASS JSON; адресний formatter fix не змінює збережену перевірку.
Власний NTS log directory порожній: startup warnings цього runtime не виявлено.

SHA-256 `start-stopped-apache.ps1`: `2d7566297e8b4c5b07f3f203efa8fa94a766856ade983a575872d8723ca24a42`;
`restart-apache.ps1`: `7b5f9ffb854762063f3076b2461f083a488d993e7ba221c6472b338c5a199824`.

## Дані та baseline

`m94-before-install-http.json`: сім запланованих локальних GET пройшли; фактичний повний ordered sitemap містить **554 loc**. Runner не
hardcode-ить цей count і порівнює весь набір із новим baseline.

`m94-before-install-files.json`: SHA-256 для **15 262 файлів** — vendor 10 208, seeders/course blueprints 4 824, views 179, translations 20,
config 24, три root protected files та чотири course services. `composer.lock` SHA-256:
`6897cff07cbf36201f8eb7dc9f987905f698f770c1cbcc26a3f22da89cf59595`.

`m94-before-install-db.json`: **14 teaching tables, 910 341 rows**, потоковий SELECT усіх колонок у PK order всередині READ ONLY REPEATABLE
READ consistent snapshot. Локальний DSN, database identity, COMPUTERNAME, port і InnoDB перевірено; credentials, DB name і row contents не
збережені. Покрито questions, variants, answers/options/links, verb hints, pages, text blocks/categories, saved tests/links і course
tree/variants. Runtime/session tables виключено. Проміжне і фінальне порівняння після browser acceptance дали PASS:
`m94-after-acceptance-files.json` — **15 262 файли, змін 0**; `m94-after-acceptance-db.json` — **14 таблиць / 910 341 рядок, змін 0**.
`.env`, APP_KEY, composer.json/lock, vendor і навчальний контент збережені. Штатні записи власних гостьових сесій/progress відокремлені
від teaching tables; чужі sessions/progress не очищувалися.

## Перевірки та остаточне приймання

Protected runner: **62 tests, 304 assertions, 1 deprecation**, exit 0; **96 protected files, 0 changes**. SQLite/runtime/cache paths
ізольовані, робочі sessions/caches не очищувалися. Python diagnostics: **19/19 PASS**; Node persistence/browser-policy batch: **17/17
PASS**. Повний набір тестів, новий SEO crawl і performance audit не запускалися.

Окремий diagnostics follow-up: **7 tests, 37 assertions, 1 deprecation**, 96 protected files / 0 changes
(`m94-probe-final-b318c38e27c5400c8655ca6a5c9508b1-result.json`). Перший WEB probe після switch повернув generic 503 через арність builtin
callback у `array_all`: PHP передає value і key. Додано адресні typed closure для `extension_loaded`/`is_writable`; конфігурація робочого handler не
змінювався. Нова regression-перевірка викликає справжній `runtime()` без мережі, перевіряє schema й усі 15 boolean fields.
Фінальний protected diagnostics запуск — **8 tests, 64 assertions, 1 deprecation**, exit 0, **96 protected files / 0 changes**:
`storage/app/seo-m2-local/m94-runtime-regression-ced28fa8e8e84ac6b5e0923cafd5c75f-result.json`. Фінальні Node 17/17 і Pint для двох PHP файлів — PASS.

| Перевірка | До ремонту | Остаточний результат |
| --- | --- | --- |
| PHP CLI / Composer launcher | PHP 8.5.10 TS, Composer 2.10.3 | `tools/composer.cmd --version`: exit 0, 2.10.3 / CLI 8.5.10, прийнятий PHP path |
| WEB PHP / handler | 8.5.10 TS, `apache2handler`, cURL відсутній | 8.5.10 NTS x64, `cgi-fcgi`, cURL 8.21.0 / OpenSSL 3.5.7; PASS |
| Прямі cURL GET PHP.net + getcomposer.org | недоступні у WEB | обидва HTTP 200, errno 0, ssl_verify_result 0; PASS |
| Laravel HTTP із Guzzle CurlHandler | не прийнято | HTTP 200, verify=true; PASS |
| Private TLS positive / hostname-negative errno 60 | не прийнято | trusted localhost 204; wrong hostname status 0 / errno 60 / verify_result 1; PASS |
| GET /, theory, Questions HTML/JSON, course, sitemap, 404 | baseline PASS | `m94-after-restart-http.json`: усі сім PASS, metadata/content та ordered sitemap однакові |
| Desktop/mobile theory, Questions save/restore, course/reload | M9.3 історичний PASS | `m94-acceptance-browser.json`: 6/6 PASS |
| Керований restart/recycling | observer issues описані вище | parent 31316 збережено, child/worker замінено; наступні WEB TLS і GET PASS |
| Probe і власні TLS/upload fixtures прибрані | тимчасові лише для локального acceptance | route/import diff 0; endpoint 404, control/fixtures відсутні; PASS |

Probe має серверний loopback gate, raw REMOTE_ADDR + private nonce, фіксовані HTTPS targets; `CURLOPT_SSL_VERIFYPEER=true`,
`CURLOPT_SSL_VERIFYHOST=2`. Реальний доказ `m94-web-curl-tls.json`: endpoint 200, aggregate PASS, усі 15 WEB configuration checks true,
fixture/control cleanup true. Позитивна локальна fixture не підміняє зовнішній HTTPS PASS; timeout не зараховується як TLS rejection.
Браузерний guard блокує production до navigation; save виконує справжній POST, видаляється лише власний snapshot зі збереженням cookies.
Randomized question variants відокремлені від сталих полів/даних; raw JSON digest збережено.

Browser desktop/mobile: theory menu має 295 links, тема відновлюється після reload. Questions має 84 items; власна відповідь
збереглася справжнім POST 204, reload і серверне відновлення після видалення рівно одного власного snapshot дали однаковий state hash.
Cookies та інше storage збережені. Зафіксовані navigation-time `ERR_ABORTED` зіставлено з успішними save promises і серверним state,
фіктивних 204 немає. Course native click і reload пройшли на обох viewport; production requests не виконувалися.
Google Fonts отримали `ERR_NETWORK_ACCESS_DENIED` від браузерного середовища; це окреме обмеження, зовнішній PHP HTTPS — PASS.
Фокусоване повторення `m94-reload-visual-browser.json` — **4/4 PASS** після явного очікування меню на reload і screenshot фактично
відновленої відповіді. Переглянуті desktop/mobile screenshots показують завантажене меню та Questions 1/84, correct 100%.

`handler-security-result.json`: у власному upload fixture вкладений `index.php`, `.PHP`, `.php8`, `.phtml`, `.phar`, `.php.txt`
отримали 403 без виконання або видачі source; static text дав 200 попри власний `.htaccess`, що підтверджує його ігнорування.
Усі сім перевірок PASS, власний fixture прибрано. `m94-final-loopback-gate.json`: власний nonloopback interface зі spoofed X-Forwarded-For
отримав серверний 403; інший локальний host і loopback без nonce — 404. Усі три PASS, довільні LAN hosts не запитувалися.
`m94-cleanup.json`: після вилучення тимчасового route/import endpoint дає 404, control відсутній. Перевірено відсутність власних
`m94-curl-tls-*` directories; залишилися тільки потрібні backups/restart/staging/start. Робочі PHP workers збережені.

Shared-host baseline: lara 200, adminer 200, xml-mapper 404, vsemerch 403, vs 500. Короткі післяремонтні GET: adminer 200, vsemerch 403;
lara/xml-mapper/vs мали 6 s timeout; адресне read-only повторення з budget 30 s дало **200/404/500**, тобто той самий baseline
(`after-shared-hosts-retry.json`). Усі п'ять hosts зберегли початкові status. Старі shared mod_php cURL warning і чужий
відсутній DocumentRoot збережені; весь XAMPP не оголошується виправленим.

## Відтворення діагностики

Усі commands — з кореня цього repo. Python — уже наявний runtime, Node/Playwright — наявне browser-середовище; залежності не встановлюються.
Для нового ремонту спочатку capture baseline, після зміни — compare; унікальний label не перезаписує evidence.

```powershell
$m94Python = 'C:\Users\admin\.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe'
& $m94Python tools/diagnostics/seo-m9-4-http.py --label m94-recheck-before --capture-baseline
& $m94Python tools/diagnostics/seo-m9-4-http.py --label m94-recheck-after --baseline storage/app/seo-m9-4-local/m94-recheck-before-http.json
node tools/diagnostics/seo-m9-browser.cjs m94-recheck storage/app/seo-m9-4-local/m94-recheck-after-http.json --handler-acceptance
& $m94Python tools/diagnostics/m9-4-curl-tls.py --label m94-recheck --openssl 'C:\Program Files\xampp\apache\bin\openssl.exe'
```

Лише для локального TLS повторення, після перевірки активного Apache `Require ip 127.0.0.1 ::1` на точний probe path, тимчасово додати
в `routes/api.php` цей route. Driver створює власний nonce і fixture та прибирає їх; після запуску вилучити route і перевірити HTTP 404.
У прийнятому Git-стані цей route відсутній.

```php
Route::middleware(['diagnostic.loopback', 'site.dev', 'throttle:2,1'])
    ->get('/_local/m9-4-curl-tls', \App\Http\Controllers\LocalCurlTlsProbeController::class)
    ->name('diagnostic.local-curl-tls');
```

## Адресний rollback і обмеження

Перший command повертає три конфіги та перевіряє syntax; другий адресує саме зафіксований instance. Перед виконанням повторно звірити
PID, creation time, owner і точний child command line; за зміни identity ці аргументи не застосовувати навмання.

```powershell
& .\tools\diagnostics\xampp-fastcgi\rollback-fastcgi.ps1 -BackupDirectory 'D:\DEV\htdocs\gramlyze.loc\storage\app\seo-m9-4-local\backups\apply-20260913-013528-271-3c0bd465' -Phase RestoreConfig
& .\tools\diagnostics\xampp-fastcgi\restart-apache.ps1 -ExpectedParentPid 31316 -ExpectedParentCreationUtc '2026-09-12T22:34:05.0392090Z' -ExpectedChildPid 35224 -ExpectedChildCommandLine '"C:\Program Files\xampp\apache\bin\httpd.exe" -d "C:/Program Files/xampp/apache" -d "C:\Program Files\xampp\apache" -f "C:\Program Files\xampp\apache\conf\httpd.conf"' -ExpectedOwner 'DESKTOP-3C05HGF\admin' -Label m94-rollback
```

Після HTTP recovery `-Phase Cleanup` із тим самим backup видаляє тільки додані runtime/module/Include. Native restart може залишити
module завантаженим у parent: Cleanup тоді відмовить до адресного stop/start цього instance. Не видаляти DLL або живих workers вручну.
Hashes/containment захищають чужі пізніші зміни; backup залишається. Laravel rollback або restore БД не потрібні.

Обмеження: браузерні Google Fonts заблоковані середовищем; shared TS mod_php warning не виправлявся; elevation початкового Apache
не виміряно. Recycling доведено керованою заміною child/worker, без навантажувального проходу 500 requests. Backend failure не перевірявся
примусовим kill: `fcgid-script` залишається явним handler без fallback на source. Production `.com`/`.ub`, серверні БД, SSH, PR, merge
і deployment не використовувалися. Фінальний commit/push та remote SHA підтверджуються окремо після збереження цього звіту.
