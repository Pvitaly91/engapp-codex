# Модуль Git Deployment

Цей модуль інкапсулює усю логіку керування Git-оновленнями та резервними гілками, що раніше знаходилась у проєкті. Його можна легко перенести в інший Laravel-додаток.

## Структура
- `src/Http/Controllers` — контролери для SSH та API режимів оновлення.
- `src/Services` — робота з GitHub API та файловою системою без shell-команд.
- `src/Models` — модель `BackupBranch` для обліку резервних гілок.
- `routes/web.php` — маршрути адмінської панелі для розгортання.
- `resources/views` — Blade-шаблони інтерфейсу.
- `config/git-deployment.php` — налаштування GitHub та список шляхів, що зберігаються при оновленні.
- `database/migrations` — міграції для таблиці резервних гілок.

## Підключення в іншому проєкті
1. Скопіюйте каталог `modules/GitDeployment` у свій репозиторій.
2. Додайте простір імен у `composer.json`:
   ```json
   "autoload": {
       "psr-4": {
           "Modules\\\\GitDeployment\\\\": "modules/GitDeployment/src/"
       }
   }
   ```
   Після цього виконайте `composer dump-autoload`.
3. Зареєструйте сервіс-провайдер у `config/app.php` (розділ `providers`):
   ```php
   Modules\GitDeployment\GitDeploymentServiceProvider::class,
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
