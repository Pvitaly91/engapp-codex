# Модуль Git Deployment

Цей модуль інкапсулює усю логіку керування Git-оновленнями та резервними гілками, що раніше знаходилась у проєкті. Його можна легко перенести в інший Laravel-додаток.

## Структура
- `Http/Controllers` — контролери для SSH та API режимів оновлення.
- `Services` — робота з GitHub API та файловою системою без shell-команд.
- `Models` — модель `BackupBranch` для обліку резервних гілок.
- `routes/web.php` — маршрути адмінської панелі для розгортання.
- `resources/views` — Blade-шаблони інтерфейсу.
- `config/git-deployment.php` — налаштування GitHub та список шляхів, що зберігаються при оновленні.
- `database/migrations` — міграції для таблиці резервних гілок.

## Content-aware preview and gate

Модуль інтегрує unified changed-content planner у deployment preview path:

- перед full deploy або rollback можна подивитися read-only content diff preview
- preview reuse-ить `ChangedContentPlanService` як canonical source of truth
- preview показує merged V3 + Page_V3 impact, grouped phases, blockers, warnings, і release-check readiness summary
- якщо preview повертає blocked states або strict warnings, старт deploy/rollback блокується до початку code update

Маршрути preview:

- `GET /admin/deployment/content-preview`
- `GET /admin/deployment/native/content-preview`

Параметри:

- `source_kind=deploy&branch=<branch>`
- `source_kind=backup_restore&commit=<commit>`
- optional `json=1` для machine-readable payload

Preview path залишається повністю read-only:

- не запускає `content:apply-changed`
- не мутує content DB rows
- не мутує `seed_runs`
- не видаляє файли
- не виконує `git checkout/reset/clean`

## Deployment-owned content apply

Після успішного full deploy або rollback модуль тепер може явно запускати unified `content:apply-changed` як deployment-owned крок:

- code update / restore лишається в existing GitDeployment flow
- changed-content apply запускається тільки після успішного code update / restore
- reuse-иться той самий preview payload і ті самі `base_ref` / `head_ref`
- якщо content apply падає, deploy/restore позначається як failed і це видно в UI
- глобального rollback orchestration для content apply немає

UI інтеграція:

- у формах full deploy і rollback є explicit toggle `Apply changed content after deploy`
- є read-only `Dry-run content apply` action для shell та native screens
- результат з preflight/execution summary і report path показується прямо в deployment UI

Маршрути dry-run preview:

- `GET /admin/deployment/content-apply-preview`
- `GET /admin/deployment/native/content-apply-preview`

Параметри такі самі, як у preview:

- `source_kind=deploy&branch=<branch>`
- `source_kind=backup_restore&commit=<commit>`
- optional `json=1`

Post-deploy content apply path reuse-ить canonical mutation layers:

- deleted cleanup через existing deleted cleanup services
- current-package seed/refresh через existing package services
- `seed_runs` мутуються тільки всередині цих canonical services

## Підключення в іншому проєкті
1. Скопіюйте каталог `app/Modules/GitDeployment` у свій репозиторій.
2. Переконайтеся, що стандартний PSR-4-маппінг `"App\\": "app/"` увімкнений (цей крок типовий для Laravel).
3. Зареєструйте сервіс-провайдер у `config/app.php` (розділ `providers`):
   ```php
   App\Modules\GitDeployment\GitDeploymentServiceProvider::class,
   ```
4. Налаштуйте змінні середовища для GitHub у `.env`:
   ```env
   DEPLOYMENT_GITHUB_OWNER=your-org
   DEPLOYMENT_GITHUB_REPO=your-repo
   DEPLOYMENT_GITHUB_TOKEN=github_token_with_repo_scope
   DEPLOYMENT_GITHUB_USER_AGENT="ProjectDeploymentBot/1.0"
   ```
5. Запустіть міграції: `php artisan migrate`.
6. За потреби опублікуйте конфіг та шаблони:
   ```bash
   php artisan vendor:publish --tag=git-deployment-config
   php artisan vendor:publish --tag=git-deployment-views
   ```

Після цього маршрути `/admin/deployment` та `/admin/deployment/native` будуть доступні автоматично, а модель `BackupBranch` використовуватиметься для запису резервних гілок.
