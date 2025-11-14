# Модуль "MigrationManager"

Модуль додає в адмінку сторінку `/admin/migrations` з можливістю запускати усі доступні міграції та робити rollback останньої партії без доступу до консолі.

## Встановлення у будь-який Laravel-проєкт

1. **Скопіюйте модуль**  
   Скопіюйте директорію `app/Modules/MigrationManager` у свій проєкт.

2. **Зареєструйте сервіс-провайдер**  
   Додайте `App\Modules\MigrationManager\MigrationManagerServiceProvider::class` до масиву `providers` у `config/app.php`.

3. **(Опційно) опублікуйте конфіг та шаблони**  
   ```bash
   php artisan vendor:publish --tag=migration-manager-config
   php artisan vendor:publish --tag=migration-manager-views
   ```
   Конфіг створить файл `config/migration-manager.php`, де можна змінити префікс маршруту (`route_prefix`) та middleware (`middleware`). Опубліковані Blade-шаблони з'являться у `resources/views/vendor/migration-manager`.

4. **Додайте посилання в адмін-навігацію**  
   Стандартний маршрут індексної сторінки має ім'я `migrations.index` та шлях `/admin/migrations`. Додайте посилання на нього у меню адмін-панелі.

Після цього модуль готовий до використання і його можна перенести в інші Laravel-застосунки разом з цими інструкціями.
