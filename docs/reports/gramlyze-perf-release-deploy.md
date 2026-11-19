# Gramlyze PERF — ручне перенесення й відкат

Цей документ описує підготовлений пакет, а не виконаний деплой. Продакшн, його поточний Git SHA, схема й фактичний контент не перевірялися. Беріть **точну гілку та SHA з підсумку перевірки релізу**; не підміняйте SHA поточним `main` або пізнішою версією гілки.

## Що саме переносити

- Увесь перевірений Git checkout релізу, а не вибіркову копію PHP: потрібні `app`, `bootstrap`, `config`, `routes`, мовні модулі, `resources`, публічні джерела, `database` з версійованими визначеннями та сідерами, `docs/content` з frozen JSON, контрактами й реєстрами, а також обидва lock-файли та конфігурація frontend-збірки. `docs/content` є входом runtime, не тільки документацією. На іншій ОС переносити точний Git-вміст, не перетворювати закінчення рядків і не переписувати очікувані SHA.
- Активні оптимізації: PERF-01–06 і PERF-10, точний PERF-12 B, ранній вихід Passive Voice і прийняте обмежене відсікання в Pronouns & Demonstratives / Nouns, Articles & Quantity. PERF-07/08 і універсальний кеш декодування не входять до активного коду. Вихідний локальний checkpoint для накладення прийнятих змін — `9ab9ee1ea8dc17ac4cbd2bc8c80618a691c1f340`. Серверний `main` чи поточний live checkout не є доведено еквівалентними цій базі: підтримуваний спосіб перенесення — **цілий реліз поруч із попереднім**, не патч у невідомий серверний код.
- Не переносити локальні `.env`, APP_KEY, `vendor`, `node_modules`, Vite output зі старої збірки, `storage/app/theory-menu`, SQLite, дампи, копії, приватні журнали або профілі. Власні залежності, збірку і меню створити із цього checkout. Існуючі серверні `.env`/APP_KEY, БД, завантаження, session store і прогрес залишити без заміни.

## До перемикання трафіку

1. Зафіксуйте SHA і шлях **фактично обслуговуваного** попереднього релізу, document root, спосіб запуску PHP (FPM, FastCGI, Apache-модуль тощо), фактичну БД, її тип та місце сесій і публічних завантажень. Не публікуйте секрети. Збережіть попередній каталог і спосіб повернути трафік до його `public/`. Перевірте, що домен і віртуальний хост — той, який ви плануєте оновлювати.
2. Підготуйте окремий каталог складання. Після отримання release-гілки з авторизованого репозиторію зробіть checkout точного SHA з підсумку, із вимкненим перетворенням рядків. Значення `$RELEASE_SHA` нижче має бути точним SHA зі звіту; виконувати в **новому** checkout, не у live-каталозі:

   ```sh
   git clone --no-checkout --single-branch --branch codex/gramlyze-perf-release-2026-11-19 \
     https://github.com/Pvitaly91/engapp-codex.git gramlyze-perf-next
   cd gramlyze-perf-next
   git -c core.autocrlf=false switch --detach "$RELEASE_SHA"
   test "$(git rev-parse HEAD)" = "$RELEASE_SHA"
   git status --short --untracked-files=no
   ```

   Незареєстровані зміни у tracked-файлах або відмінність SHA — причина зупинитися. Виконайте `node tools/diagnostics/verify-perf-release-inputs.cjs`: усі 523 записи з `tests/Fixtures/perf-release-sha256.v1.json` мають збігтися побайтово. Помилку не усувати заміною очікуваного SHA чи підкладанням іншого віджета. На сервері не копіюйте абсолютний успадкований `public/storage` з checkout без перевірки: він може вказувати на інший каталог. Активний `public/storage` має вказувати на **фактичні збережені завантаження** цього сайту; перевірте ціль, не підмінюючи її вмісту. Якщо використовуєте `php artisan storage:link`, спочатку звірте налаштовані target-и та наявний link у новому release.
3. Під'єднайте до нового release наявний серверний `.env` без зміни значень, ключа чи доступів. Збережіть спільне сховище сесій і його cookie/домен/формат, а також uploads та інше потрібне постійне сховище цього сайту. Каталоги `bootstrap/cache`, `storage/framework/views`, службові cache/log каталоги й `storage/app/theory-menu` підготуйте з доступом необхідних build/deploy та PHP-користувачів; код і джерела мають бути читабельні PHP, `public/` — єдиний веб-корінь. За можливості тримайте `bootstrap/cache`, compiled views і меню окремо **для кожного релізу**, а file sessions і uploads — зі старими даними. `TheoryMenuStore` не приймає симлінк самого menu-root або його `mode/locale/theory`; якщо решта `storage` спільна, спочатку перевірте фактичну топологію, щоб побудова меню не активувала інший release завчасно. Не використовуйте `chmod 777`. Не давайте веб-доступ до `.env`, `docs`, `database` чи службового `storage`.
4. CLI і PHP, який реально обслуговує запити, повинні задовольняти `php ^8.5` (Composer). Lock: Laravel `13.31.0`, Livewire `4.4.4`, Sanctum `4.3.3`. Мінімум за production-lock: `ctype`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `session`, `tokenizer`; для наявної MySQL/MariaDB — також `PDO` / `pdo_mysql`. Якщо runtime використовує інший backend або драйвер кешу/черги/сесій, потрібне і його PHP-розширення. `sqlite3`/`pdo_sqlite` потрібні для ізольованих SQLite-тестів, не для заміни production-БД. Для npm використовуйте Node 20, як у CI, або сумісний підтримуваний новіший Node; lock Vite `5.4.19` і Tailwind CSS `4.1.16`. Не запускайте `composer update` або `npm install` для зміни lock.

   ```sh
   php -v
   node -v
   npm -v
   composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-plugins --no-scripts
   composer check-platform-reqs --no-dev
   php artisan package:discover --ansi
   npm ci --include=dev --ignore-scripts --no-audit --no-fund
   npm run build
   test -s public/build/manifest.json
   git diff --exit-code -- composer.lock package-lock.json
   ```

   Перевірка `composer check-platform-reqs` стосується поточного CLI, а не автоматично FPM/Apache. Не переносіть кеш конфігурації зі старого шляху, `public/hot` чи старий Vite manifest.
5. Звірте на обраній серверній БД (а не на локальному дампі) застосовані міграції та необхідний контент. Цей PERF-набір не додає міграцій. Для вже сумісного baseline перевірте стан командою `php artisan migrate:status --no-interaction`; не запускайте `migrate`, `db:seed`, `content apply`, імпорт дампу чи автоматичний запис контенту. Якщо невідомий старий реліз відстає, **зупиніться** до окремого плану сумісності. Теорії потрібні актуальні `pages`, `page_categories`, блоки теорії та зв'язки; mixed потребує збережених тестів/зв'язків, банків питань, відповідей, варіантів, marker tags і підказок. Точні ранні виходи залежать не лише від файлових SHA: DB-ідентичність `block UUID`/`question ID`, а для лексичних пакетів також `question UUID` та `seeder`, має відповідати frozen джерелу. За дрейфу не покладайтеся на ранній вихід: спрацює попередній шлях або охоронна перевірка відхилить непідтверджене джерело. Однаковий HTML чи HTTP 200 не доводять активації потрібного шляху. До перемикання порівняйте типові власні й чужі віджети, дані відповідей/підказок і порядок mixed з перевіреним пакетом або доступним read-only знімком того самого контенту; не вимагайте збігу локальної кількості локалей/уроків без перевірки серверних даних.

## Меню, кеші та перевірка після активації

У **новому** checkout переконайтеся, що чинний `APP_URL` відповідає обслуговуваному домену: команда вибирає site mode й доступні локалі за цим URL і налаштуваннями локалей. Для файлового кешу/compiled views спочатку перевірте, що ці каталоги release-local. Перебудуйте тільки кеші нового релізу. Якщо чинний сервер не використовує певний Laravel cache, необов'язкову cache-команду нижче пропустіть; Laravel відновить відповідний стан на вимогу. За `VIEW_COMPILED_PATH` у спільному каталозі не запускайте `view:cache`/`view:clear` без окремого плану спільного кешу. Не виконувати `cache:clear` чи `optimize:clear` без оцінки shared cache і session store.

```sh
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan theory:menu-build
php artisan theory:menu-build --check
```

Останні дві команди охоплюють усі ввімкнені локалі, `--locale=uk` застосовуйте лише для конкретної ввімкненої локалі. Звірте mode, локалі, ревізію, кількість та розмір desktop/mobile; `--check` перевіряє обидва активні файли й не підміняє відсутнє меню fallback-версією. Команда працює з фактичною БД і генерує snapshot в `storage/app/theory-menu` за замовчуванням; тепле локальне меню не переноситься. Ненульовий exit — не перемикати трафік.

Перемкніть чинний механізм релізів на новий `public/`. Оновіть PHP-процеси та їх OPcache **штатним способом саме для фактичного handler**: FPM — graceful reload відповідного PHP-FPM service/pool; FastCGI — керований recycle відповідних worker-ів; Apache `mod_php` — graceful reload відповідного Apache. Nginx reload сам собою не скидає PHP-FPM OPcache, `opcache_reset()` з CLI не скидає вебкеш. Якщо є довгоживучі queue/Octane-процеси, застосуйте штатний перезапуск тільки відповідних worker-ів згідно зі схемою розгортання; не вимикайте/очищайте спільні бекенди без потреби.

Перевірте в окремому гостьовому вікні: `/theory/word-order`, `/theory/basic-grammar/word-order/theory-basic-word-order`, `/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms`, `/test/word-order/theory-basic-word-order` та `/test/passive-voice-tenses/theory-passive-voice-present-simple`. Виберіть власний урок із наявної Passive Voice, Conditionals, Reported Speech/Clauses/Common Mistakes практики, а за ввімкненого контенту — Pronouns та Partitives. Перевірте заголовок і реальний вміст, не тільки HTTP 200. У меню перевірте пошук, активний пункт, desktop/mobile і відповідність `X-Theory-Menu-Revision` завантаженого фрагмента активній ревізії за наявності заголовка. У гостьовій mixed спробі перевірте ручний ввід, токени, неправильна → редагування → правильна, перехід, перезавантаження й відновлення, скидання лише цієї спроби. Не відкривайте/не скидайте особистий прогрес для smoke. Повторіть `php artisan theory:menu-build --check` з активного кореня. Перевірте відсутність нових помилок вебпроцесу/збірки або непередбачених fallback під час smoke.

## Відкат

Якщо не пройшли preflight, активне меню, assets, реальні сторінки чи mixed взаємодія, поверніть трафік до **зафіксованого попереднього `public/` і SHA**, зі старими `vendor`, assets та release-local кешами/меню; повторіть штатне оновлення PHP/OPcache й коротку гостьову перевірку. Не відновлюйте БД/сесії/завантаження з локальної копії: цей реліз їх не замінює й не вимагає schema rollback. Якщо меню єдине несправне, а реліз підтримує коректну попередню ревізію в тому самому menu store, окремо доступне `php artisan theory:menu-build --rollback --locale=uk` з наступним `php artisan theory:menu-build --check --locale=uk` (адаптуйте тільки до фактичної ввімкненої локалі). Це **відкат меню, не застосунку**; якщо rollback не знайшов перевіреної ревізії або несправний код — поверніть попередній реліз.
