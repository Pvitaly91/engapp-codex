# PERF‑28 — збірка й активація готового gzip лише для Gramlyze

Див. [вимірювання, межі та статус активації](../reports/perf-28-precompressed-static-assets.md). Приклади не змінюють Vite, PHP або спільну конфігурацію XAMPP. Не застосовувати на `.ub` чи production без окремого приймання.

**Не активувати виміряний кандидат PERF‑28.** Нижче — перевірена процедура для відтворення й майбутнього кандидата, який окремо пройде вимоги до швидкодії. Поточний include зберігає HTTP-контракт, але не пройшов цей поріг.

## Збірка і перевірка

Після звичайного Vite build оберіть **абсолютний** шлях до потрібного `public` та новий каталог покоління **поза будь-яким public root**. Користувач, від якого працює Apache, має читати пакунок, а застосунок, Vite, upload-процеси й сторонні користувачі не повинні мати можливості його змінювати. Не розміщуйте пакунок в URL alias чи `public/storage`.

```powershell
# Виконувати з репозиторію. Батьківський каталог має існувати.
$public = 'D:/DEV/htdocs/gramlyze.loc/public'
$release = 'D:/DEV/htdocs/gramlyze.loc/storage/app/perf28-static-01'
$include = "$release.conf"
node tools/perf/http-compression/precompress.cjs build --public $public --out $release
node tools/perf/http-compression/precompress.cjs verify --public $public --out $release
node tools/perf/http-compression/precompressed-contract.cjs render --public $public --generation $release --out $include
```

Кожна команда має завершитися з exit 0. `build` не перезаписує покоління; `render` не перезаписує include. Зафіксуйте SHA вхідного Vite manifest, inventory manifest і згенерованого include. Перевірка повторно читає поточний manifest, файли та всі sidecar, перевіряє SHA‑256 декодованого оригіналу, gzip bytes, відсутні/зайві файли й Last-Modified. Якщо артефакт зник, пошкодився або змінився manifest, **не публікуйте** це покоління.

Фільтр обмежений перевіреними CSS/JS із Vite та фіксованим списком JS/SVG. Для оновлення навіть одного незмінно названого файла створіть новий пакунок і include. Не дописуйте до активного пакунка й не замінюйте його файли на місці. Поки новий пакунок не готовий, новий хешований URL піде штатним identity-шляхом, а змінений оригінал за старим URL — identity через перевірку вихідного файла в Apache. Відсутній/укорочений `.gz` теж дає identity; перевірку довільного same-length бінарного пошкодження виконує `verify` до публікації. За підозри пошкодження вже активного пакунка від'єднайте весь include.

## Активація і відкат

1. Звірити процес/слухач, `httpd -M`, `httpd -S`, обидва Gramlyze vhost і всі включення. Потрібні `mod_rewrite`, `mod_headers`, `mod_mime` і `mod_authz_core`; `mod_deflate`, `mod_filter` чи новий PHP handler не потрібні. Не додавайте `LoadModule`, загальний `Alias` чи загальний rewrite.
2. Звірити, що `gramlyze-fastcgi.conf` підключено тільки з `gramlyze.loc`, і зафіксувати точний preimage, SHA‑256 та ACL обраного файла. У локальному XAMPP підключити один згенерований файл **усередині** Gramlyze vhost (наприклад, через його вже наявний приватний include). Не залишати паралельно PERF‑27 output filter. Не додавати правила до спільного `httpd.conf`, інших vhost чи root `.htaccess`.
3. Спочатку перевірити синтаксис у приватній копії ефективної конфігурації (`httpd.exe -d <підтверджений ServerRoot> -f <private shadow config> -t`); повторити `verify` і SHA перед публікацією. Увімкнути include лише перед погодженим штатним перечитуванням Apache. Для Apache як Windows-служби використовуйте підтверджений service restart; для консольного запуску — Ctrl+Break саме в консолі Apache, не Ctrl+C, kill або переінсталяцію. Див. [офіційні способи керування Windows Apache](https://httpd.apache.org/docs/2.4/platform/windows.html). За відсутності керованого перечитування **не змінювати живу конфігурацію**.
4. Перевірити gzip/identity, малі/відсутні ресурси, HEAD, 304 окремої репрезентації, Last-Modified та identity 206. На живому сайті повторити браузерний smoke й підтвердити identity для HTML/API; не відправляти справжній прогрес. Зіставити контрольні статичні відповіді інших локальних vhost з їхнім власним baseline.
5. Якщо перевірка не пройшла, відновити точні початкові байти **лише** файла Gramlyze й **лише** якщо поточний SHA збігається з кандидатом. Не перезаписувати паралельні зміни. Повернути попередню конфігурацію тим самим механізмом перечитування та повторити smoke; видаляти лише новий пакунок після того, як жоден активний include не посилається на нього. Точність відкоту перевірити за початковим SHA.

Автоматичні ізольовані перевірки: `node --test tools/perf/http-compression/precompress.test.cjs` і HTTP-контракт, описаний у [звіті PERF‑28](../reports/perf-28-precompressed-static-assets.md).
