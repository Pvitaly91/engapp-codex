# PERF-30 — Apache privilege isolation

**Рішення: PARTIAL_PASS лише для файлової ізоляції та перевірок еталона; запуск нового HTTP worker і активація — BLOCKED.** Чинний `gramlyze.loc` не перемикався. База — `05573a4734361f6946bf6514422cd062fe8d5d43` (`codex/perf-29-immutable-static-delivery`), коміт реалізації — `489d53567e9c4a5c3a93a3591e547af726d47d33`, робоча гілка — [codex/perf-30-apache-privilege-isolation](https://github.com/Pvitaly91/engapp-codex/tree/codex/perf-30-apache-privilege-isolation).

## Чинний Windows/XAMPP

Поточне зіставлення TCP listener і process tree виконано заново. `0.0.0.0:80` і `:443` належали PID 26080, батько — cmd.exe PID 34232, дочірній Apache — PID 11804. Це консольний запуск (імені SCM-служби для цих процесів немає). Образ в обох — `C:/Program Files/xampp/apache/bin/httpd.exe`, XAMPP Apache 2.4.58. У головного немає додаткових аргументів, дочірній має `-d "C:/Program Files/xampp/apache"`.

Обидва токени — того самого локального користувача, High (`S-1-16-12288`), elevated, Administrators enabled. Увімкнені `SeChangeNotify`, `SeImpersonate`, `SeCreateGlobal`; TakeOwnership, Backup, Restore, Debug та інші присутні, хоч вимкнені на момент знімка. Вимкнений привілей, який токен може ввімкнути, не є ізоляцією. Read-only AccessCheck фактичних токенів показав можливість запису, append, зміни timestamp, видалення, зміни DACL та власника на Apache config, PHP FastCGI, DocumentRoot, чинному тестовому immutable vault і файлах у ньому; на відповідних каталогах також `DELETE_CHILD`. Жодної зміни існуючих файлів для цієї перевірки не виконано. SID користувача, повні набори привілеїв і маски/шляхи збережені тільки в приватному `storage/logs/perf-30/live-audit-01.json`.

`httpd -t -D DUMP_VHOSTS` пройшов як перевірка конфігурації, не перезапуск. HTTP: `lara.loc` (default), `gramlyze.loc`, `adminer.loc`, `diyxml.loc`, `xml-mapper.loc`, `e-shpop.loc`, `vsemerch.loc` (наявне попередження відсутнього DocumentRoot), `vs.loc`, `dasha.loc`. HTTPS: default-шаблон `www.example.com`, `gramlyze.loc` і його `www.gramlyze.loc`. Обидва vhost Gramlyze включають `extra/gramlyze-fastcgi.conf` із PHP 8.5.10 NTS FastCGI. Приватний preimage не містить ключів, cert bytes, логів, БД, `.env` або даних сесій.

## Publisher і Worker

- Trusted Publisher лишається PERF-29: `immutable-release.cjs` створює покоління, перевіряє manifest, identity/gzip SHA-256 і рівність декодованих даних. `seal-release.ps1` додає звичайному користувачу лише read для готових поколінь, а окремому restricting SID — RX тільки до дерева доставки, **без доступу до маніфестів**. Розширена перевірка читає реальний Apache token і відхиляє роль, якщо вона також є звичайною групою цього токена. Старі покоління не перезаписуються, існуючий fingerprint з іншими bytes відхиляється. Два підготовлені покоління мають ті самі контрольні SHA, що PERF-29: шість тестових файлів `49c7cc07a9a2e1a765d2d10878c156991ea4ac81f681e9c10bf04114dbc39046`; чотири Vite файли `5f5c7f441b51d1cdcb45905e2baaeb96f6919f15037b13fd3f0f4e9cb1889fdb`. Звірено 39 об’єктів NTFS. Renderer дає байт-в-байт ті самі Apache правила після підстановки шляху до vault; повторне SHA-хешування при GET не додано.
- Private Worker — точний Apache 2.4.58 SHA-256 `7bd994ae100f079c7c2e36414ec40c059b38d440f0c48e89c0946896c8b0aeea`, окремі `server`, `www`, `run`, `control`; точні копії необхідних Apache DLL/модулів і конфігурації, без змін XAMPP. Публікатор може керувати приватним стендом, Apache може читати його конфігурацію та файли доставки, писати лише у власний `run`; контрольні receipts і синтетичний private fixture йому недоступні. Немає успадкованого оточення застосунку або успадкованих handles. Спроба створення процесу використовує звичайний Windows [restricted token](https://learn.microsoft.com/en-us/windows/win32/secauthz/restricted-tokens), не `WRITE_RESTRICTED`: другий прохід ACL стосується і читання. Це **не новий обліковий запис**: User SID лишається тим самим, фактичний токен нового PID — Medium, Administrators deny-only, без elevation, в `Present` та `Enabled` лише `SeChangeNotifyPrivilege`. TakeOwnership/Backup/Restore/Debug/Impersonate відсутні, а не просто вимкнені. Обмежений процес запускається в Job Object з одним власним дочірнім процесом та без Win32k; спільний сервіс не використовується.
- **19/19 операцій** виконано після створення точного Apache PID, до resume, через impersonation його реального токена. Дозволено 3: читання identity, читання gzip, створення runtime-файла. Заборонено 16: читання private canary, запис конфігурації, write/append, заміна sidecar, delete/rename, timestamp, DACL/owner, hardlink/reparse та зміни батьківського каталогу; всі 16 повернули Win32 5. SHA і timestamps fixtures до/після збіглися. Незалежний gate на фактичному PID підтвердив також роль і права на реальний sealed vault. Це доказ файлових прав до resume, **не HTTP-приймання**. Мережеву ізоляцію від інших локальних сайтів не зараховано: звичайний ACL сам по собі не обмежує вихідні TCP-з’єднання.
- **22/22** Node-контрактів: пошкодження gzip навіть зі зміненою metadata, відсутній/пошкоджений manifest, stale bytes, hardlink/reparse, конфлікт fingerprint, спроба перепублікувати наявне покоління та збереження попереднього. Звичайний і role-sealed PERF-29 режими пройшли окремі ACL-перевірки. Артефакти: `storage/logs/perf-30/{probe01,role-vaults.json}` і приватні receipts усередині runtime/publisher control.

## Блокер запуску та HTTP

Приватний `httpd -t` на **тому самому образі та конфігурації** у звичайному filtered контексті — exit 0. Створений Windows restricted worker з точною роллю і доведеним файловим gate після resume — exit `0xC0000022` (3221225506, [STATUS_ACCESS_DENIED](https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-erref/596a1078-e883-4972-9bbc-49e60bebca55)), до Apache startup log і до відкриття HTTP listener. На фінальному launcher SHA `e3551fa8b9bf85b61ba9821f426759377f5a333be009965e846b5739f82d13ac` (`probe02`) знову отримано всі 19 очікуваних файлових результатів, PASS vault і той самий exit. `Start` вимагає попереднього успішного `Validate` з тими ж pins, фактичних файлових перевірок та пов’язаної vault-перевірки для immutable include; його не можна зарахувати за результатом лише pre-resume. Контроль `-v` без конфігурації й listener у цій ролі теж повернув `0xC0000022`, тоді як ordinary `-v` — 0. Native read-only probe того ж обмеженого токена зафіксував відмову в доступі до дев’яти перевірених файлів System32 та восьми існуючих об’єктів `\\KnownDlls` з `MAP_READ|MAP_EXECUTE`; звичайний filtered токен їх відкривав. Це доказ несумісності доступу до системних залежностей в цьому контексті, **не трасування першого об’єкта, на якому завершився loader**.

Додаткова перевірка тільки прав із well-known [Restricted Code SID](https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/manage/understand-security-identifiers) показала, що той SID відкрив би перевірені системні секції, але не дев’ять файлів System32, зокрема необхідну залежність `vcruntime140.dll`. Publisher-only fixture ще був недоступний, але додатковий SID розширює права на інші об’єкти з відповідним ACE, і виконання повної межі доступу до сторонніх файлів не доведено. Такий токен не запускав Apache і не увійшов у код пакета. Системні ACL, групи, правила безпеки та власники не змінено. Нових профілів, служби чи облікового запису Windows не створено.

- **B, еталон PERF-29:** 45/45 HTTP-перевірок виконано повторно поточним renderer на тому самому Apache та приватному порті. Збережено negotiation, MIME/довжину, Vary/ETag/Last-Modified, HEAD, 304, Range/206, 404/406, попередні fingerprinted assets та identity fallback. Receipt: `storage/logs/perf-29/immutable-http-t47nZ3/http-contract-912806ac-0e40-4dd8-95bf-9ea49fcb9333.json`.
- **C, новий worker:** HTTP-контракт, навантаження та Chromium UK/EN/PL **NOT RUN**; жодного HTTP-запиту до нього. Браузерний стенд підготовлено окремо (`storage/logs/perf-30/restricted-chromium.cjs`): тільки приватний localhost, allowlist локальних ресурсів, PHP Laravel з read-only БД і тимчасовою сесією після підтвердженого HTTP від C. Він не стартував; не зараховуємо йому 48 Chromium-навігацій з PERF-29. Діагностика: `runtime-minimum.finish.json`, `runtime-loader-objects-01.json`, `runtime-loader-rc-01.json` у приватному каталозі PERF-30.

## Benchmark — поточні еталони A/B, C NOT RUN

A = identity, B = перевірений PERF-29 immutable. Два приватні crossover `perf30-ab01`, `perf30-ab02`, зворотний порядок запуску, ABBA/BAAB, 24 хвилі на руку/рівень у кожній серії. Однаковий образ Apache/модулі та шість SHA-пінованих файлів. По 1 872 завершені виміряні відповіді у кожній серії; звірено encoded SHA для B та decoded SHA у кожній відповіді обох рук. C не вимірювався.

| Парал. | Рука | req/s мед. | TTFB мед./p95/p99, мс | Total мед./p95/p99, мс | Тіло / wire, Б |
|---:|:---|---:|---:|---:|---:|
| 1 | A | 437 | 2,07 / 3,75 / 10,68 | 2,23 / 3,98 / 11,34 | 2 644 768 / 2 657 296 |
| 1 | B | 444 | 2,05 / 2,99 / 12,72 | 2,20 / 3,23 / 12,89 | 504 064 / 519 176 |
| 3 | A | 632 | 3,73 / 6,94 / 16,27 | 3,85 / 7,15 / 16,46 | 7 934 304 / 7 971 888 |
| 3 | B | 677 | 3,85 / 6,18 / 10,91 | 3,93 / 6,25 / 11,43 | 1 512 192 / 1 557 528 |
| 5 | A | 986 | 4,21 / 5,26 / 6,01 | 4,29 / 5,44 / 6,08 | 13 223 840 / 13 286 480 |
| 5 | B | 984 | 4,48 / 5,85 / 6,94 | 4,52 / 5,87 / 6,98 | 2 520 320 / 2 595 880 |
| 10 | A | 896 | 9,21 / 13,71 / 18,72 | 9,33 / 13,87 / 18,84 | 26 447 680 / 26 572 960 |
| 10 | B | 933 | 8,88 / 12,24 / 15,00 | 8,92 / 12,28 / 15,04 | 5 040 640 / 5 191 760 |
| 20 | A | 1 242 | 13,47 / 25,59 / 59,59 | 13,57 / 25,68 / 59,63 | 52 895 360 / 53 145 920 |
| 20 | B | 1 223 | 13,50 / 19,20 / 25,11 | 13,54 / 19,27 / 25,14 | 10 081 280 / 10 383 520 |

| Парал. | B/A серія 1 | B/A серія 2 | B/A pooled |
|---:|---:|---:|---:|
| 1 | 99,0% | 104,4% | 102,8% |
| 3 | 103,2% | 97,6% | 100,8% |
| 5 | 96,7% | 102,1% | 100,1% |
| 10 | 115,4% | 109,0% | 111,1% |
| 20 | 110,1% | 106,8% | 107,9% |

| Серія | Рука | CPU хвиль, мс | Peak RSS, MiB | Windows read ops / bytes |
|:---|:---|---:|---:|---:|
| perf30-ab01 | A | 1 203,1 | 15,6 | 0 / 0 |
| perf30-ab01 | B | 718,8 | 14,9 | 0 / 0 |
| perf30-ab02 | A | 1 234,4 | 15,3 | 0 / 0 |
| perf30-ab02 | B | 1 000,0 | 14,9 | 0 / 0 |

За дві серії encoded body A 103 145 952 Б, B 19 658 496 Б (−80,94%); wire A 103 634 544 Б, B 20 247 864 Б (−80,46%, з HTTP заголовками, без TCP/IP). Парна медіана B/A обчислена по хвилях і не є часткою двох pooled медіан req/s; наприклад, на 20 клієнтах pooled медіани 1 242 / 1 223 не означають, що B виграв у кожному прогоні. Абсолютні числа з PERF-29 не застосовано як контроль до C. CPU — дельта процесу, peak RSS — за його життя, Windows read counters не обліковують усі файлові/mmap-операції. Сирі `requests.jsonl`, `waves.jsonl`, `summary.json`, CPU/RSS/I/O, контроль закриття приватних процесів — `storage/logs/perf-30/bench/runs/{perf30-ab01,perf30-ab02}`. SHA приватного harness `9c849d76791a7906287a5bda2c0080cc8661ec73468c69a7860f4bee2f235384`.

## Архітектури майбутньої активації

| Модель | Контур і вплив | Рішення |
|:--|:--|:--|
| A — лише виділений static worker | Відокремлені Apache та vault; direct loopback добре ізолює тест. Якщо чинний elevated Apache проксіює той самий URL і може записати vault, межа не виконується. Без нього потрібен окремий listener/адреса або зміна origin. | Придатне для ізольованої перевірки, не для активації за чинним front. |
| B — непривілейований front для чинних URL | Пряме обслуговування `gramlyze.loc`: поточні Vite/Blade посилання, origin, cookies та Livewire зберігаються; PHP має свій ізольований backend, без доступу до vault і без облікових даних у front. У спільному XAMPP зміна власника listener впливає на всі перелічені vhost і HTTPS. | Єдиний розглянутий шлях зберегти origin і прибрати elevated Apache з довіреного HTTP-шляху. Не погоджено і не виконано. |
| C — окремий static origin | Потрібні зміни або конфігурація Vite/URL, перевірка Blade та import/worker/CSS залежностей, CSP, CORS, cache/service workers, domain cookies, HTTPS і браузерних 304/206. Інший hostname не заважає elevated Apache на тому самому Windows змінити vault. | Не активовано і не прийнято. |

## План переходу і відкату (не виконано)

1. Окремо погодити адміністративний етап для **виділеного облікового запису Windows без прав адміністратора** або іншої межі ізоляції з необхідними системними бібліотеками. Не змінювати чинний XAMPP на цьому етапі. Для окремого облікового запису потрібне перевірене розширення моделі seal (RX фактичному SID, не підставляти його замість restricting-only SID поточного launcher). Надати лише RX до потрібного скопійованого executable, module/config та sealed files; write до його logs/temp; без власності над vault, `DELETE_CHILD`, DACL/owner, доступу до конфігів та файлів сторонніх сайтів, `.env` або особистих даних. Окремо довести допустимі мережеві з’єднання (лише локальний PHP/backend за необхідності) та відсутність небажаних привілеїв. Не змінювати ACL системних DLL.
2. Rehearsal на приватному loopback: справжній worker `httpd -v` та `-t` exit 0; звірити executable SHA, актуальний PID/токен, `seal-release.ps1 -Mode Verify … -ApachePid …` для відповідної перевіреної моделі, повторити фактичні негативні операції, 45 HTTP-перевірок, Chromium UK/EN/PL і повний A/B/C вже з працюючим C. Стан SQL read-only, лише тимчасові сесії. За будь-якої невідповідності не переходити далі.
3. Окремий явний дозвіл на 80/443 та, за потреби, shared config. Перед змінами зафіксувати фактичні процеси, запуск консольного екземпляра, SDDL, exact SHA й перевірені копії preimage/postimage. Прогнати `httpd -t -D DUMP_VHOSTS`, перевірити зміни для кожного vhost, модулів, FastCGI, HTTPS/SNI, сесій/Livewire й незмінність поточних URL. Публікувати один перевірений include атомарною заміною на тому самому томі; не переписувати старі vault. Штатно перемкнути listener лише в погоджене вікно. Після запуску повторити токен/ACL, тестові URL та health checks.
4. Rollback: зупинити лише новий listener, звірити очікуваний postimage перед будь-яким записом; відновити тільки зачеплені exact preimage та права в погоджених шляхах атомарною заміною, не видаляти запечатані покоління; повторити `httpd -t -D DUMP_VHOSTS`. З окремого дозволу повернути зафіксований старий консольний запуск і перевірити 80/443, усі задіяні vhost, FastCGI та Livewire. Якщо SHA або фактичний процес не ті — зупинитися, не перезаписувати.

Звірені preimage SHA-256; приватні byte-exact копії з before/copy/after, timestamps та SDDL: `storage/logs/perf-30/live-preimage-3d108bb36de04d5087a66273490f95b6/receipt.json` (SHA `d01fb721ffb875daf90143cee62b9f38fdd4fcb7cc14399d279d5d8affaa206f`). Чинний `apache_start.bat` (SHA `b639cd55ae93a75d577abdd9705aace4be2cba893b6e9d5c7e4fdd1cf30d362b`) переходить у свій каталог і викликає `apache\bin\httpd.exe` без аргументів, що узгоджується з аудитом. Його не запускали.

| Файл | SHA-256 |
|:--|:--|
| `httpd.conf` | `c3e7f589c8aa75d04449c67152ccc1a523914b93b3e913118782f08957438d38` |
| `extra/httpd-vhosts.conf` | `cd6f2dc0ab103744ce02bf0e6098dd94a4b853f8acabacab9d82b0f373de2b1b` |
| `extra/httpd-ssl.conf` | `9df638e406d051560c8eef2a7abc52561fa030b4ea833e0edbdb77fd9f7ecbcd` |
| `extra/gramlyze-fastcgi.conf` | `1e0bc2b3a3ee79d3a3d4c15dbb844f67dec95cdc2b9cefcc653c5bd734a52b81` |
| `public/.htaccess` | `24c0872d9e22a0afdf3d7e336c2506b18782f5bfa09a9384d60465aef5ff05ee` |

Після діагностики всі п’ять SHA повторно збіглися. Один HEAD чинного `http://gramlyze.loc/build/assets/app-C0PgywvM.css` із `Accept-Encoding: gzip` повернув 200, identity, Content-Length 155 378. Активації та rollback на чинному сайті не було. `.ub`, production, `main`, робочу БД, навчальний контент і персональні сесії не змінено.
