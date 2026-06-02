# Модуль ArtisanManager

Модуль для керування Artisan командами через веб-інтерфейс. Надає зручний спосіб виконання поширених команд Laravel (очищення кешів, оптимізація тощо) безпосередньо з адмін-панелі.

## Можливості

Модуль підтримує виконання таких команд:

- **Очистити кеш додатку** (`php artisan cache:clear`)
- **Очистити кеш конфігурації** (`php artisan config:clear`)
- **Очистити кеш маршрутів** (`php artisan route:clear`)
- **Очистити кеш шаблонів** (`php artisan view:clear`)
- **Видалити скомпільовані класи** (`php artisan clear-compiled`)
- **Оптимізувати додаток** (`php artisan optimize`)
- **Скасувати оптимізацію** (`php artisan optimize:clear`)
- **Створити символічне посилання** (`php artisan storage:link`)
- **Перезапустити черги** (`php artisan queue:restart`)

## Встановлення

### 1. Скопіюйте модуль у ваш проєкт

Скопіюйте директорію `app/Modules/ArtisanManager` у ваш Laravel-проєкт за тим самим шляхом.

### 2. Зареєструйте сервіс-провайдер

Відкрийте файл `config/app.php` та додайте до масиву `providers`:

```php
'providers' => ServiceProvider::defaultProviders()->merge([
    // ... інші провайдери
    App\Modules\ArtisanManager\ArtisanManagerServiceProvider::class,
])->toArray(),
```

### 3. (Опціонально) Опублікуйте конфігурацію та шаблони

Для налаштування модуля опублікуйте конфігурацію:

```bash
php artisan vendor:publish --tag=artisan-manager-config
```

Це створить файл `config/artisan-manager.php`, де ви можете змінити:

- `route_prefix` - префікс URL для маршрутів модуля (за замовчуванням: `admin/artisan`)
- `middleware` - middleware для захисту маршрутів (за замовчуванням: `['web', 'auth.admin']`)
- `enabled_commands` - список доступних команд (можна вимкнути непотрібні)

Для зміни шаблонів опублікуйте views:

```bash
php artisan vendor:publish --tag=artisan-manager-views
```

Шаблони будуть доступні у `resources/views/vendor/artisan-manager`.

### 4. Налаштуйте middleware

Переконайтеся, що у вашому проєкті існує middleware `auth.admin` або змініть його у файлі конфігурації на власний middleware для авторизації адміністраторів.

Якщо у вас немає middleware `auth.admin`, ви можете:

- Створити власний middleware для перевірки адміністраторів
- Використовувати стандартний `auth` middleware
- Змінити конфігурацію у `config/artisan-manager.php`:

```php
'middleware' => ['web', 'auth'], // або ваш власний middleware
```

### 5. Додайте посилання у меню адмін-панелі

Додайте посилання на маршрут `artisan.index` у навігацію вашої адмін-панелі. За замовчуванням URL буде `/admin/artisan`.

Приклад для `resources/views/layouts/app.blade.php`:

```blade
<a href="{{ route('artisan.index') }}" class="nav-link">
    <i class="fa-solid fa-terminal"></i>
    Artisan команди
</a>
```

### 6. Перевірте доступність

Після авторизації в адмінці відкрийте URL `/admin/artisan` (або інший, якщо змінили `route_prefix`).

Ви побачите список доступних команд з кнопками для виконання.

## Налаштування

### Вимкнення окремих команд

У файлі `config/artisan-manager.php` ви можете вимкнути небажані команди, видаливши їх з масиву `enabled_commands`:

```php
'enabled_commands' => [
    'cache_clear',
    'config_clear',
    'route_clear',
    'view_clear',
    // 'clear_compiled',  // вимкнено
    'optimize',
    'optimize_clear',
    // 'storage_link',    // вимкнено
    // 'queue_restart',   // вимкнено
],
```

### Зміна URL

Змініть `route_prefix` у конфігурації:

```php
'route_prefix' => 'dashboard/commands', // тепер доступно за адресою /dashboard/commands
```

### Власний middleware

Змініть `middleware` у конфігурації:

```php
'middleware' => ['web', 'auth', 'can:manage-artisan'], // власний middleware
```

## Безпека

**ВАЖЛИВО!** Цей модуль надає доступ до потужних команд Laravel, які можуть впливати на роботу всього додатку.

Рекомендації:

- Завжди використовуйте надійний middleware для авторизації
- Обмежте доступ лише для довірених адміністраторів
- У продакшені використовуйте модуль обережно
- Розгляньте можливість логування виконаних команд

## Переносимість

Модуль розроблений з урахуванням максимальної переносимості між Laravel-проєктами:

- Усі залежності стандартні для Laravel
- Немає зовнішніх пакетів
- Проста структура файлів
- Гнучкі налаштування через конфігурацію
- Можливість публікації та зміни шаблонів
- Підтримка Laravel 10+

## Структура модуля

```
app/Modules/ArtisanManager/
├── Http/
│   └── Controllers/
│       └── ArtisanManagerController.php
├── resources/
│   └── views/
│       └── index.blade.php
├── routes/
│   └── web.php
├── config/
│   └── artisan-manager.php
├── ArtisanManagerServiceProvider.php
└── README.md
```

## Підтримка

Модуль підтримує Laravel 10+ та PHP 8.1+.

## Ліцензія

MIT
