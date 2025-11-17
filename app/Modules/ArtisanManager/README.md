# Модуль ArtisanManager

## Опис

Модуль ArtisanManager додає в адмін-панель сторінку для виконання найпоширеніших команд Laravel Artisan через веб-інтерфейс. Це дозволяє швидко та зручно керувати кешами, оптимізацією та іншими функціями Laravel без доступу до командного рядка.

## Можливості

### Очищення кешів:
- `cache:clear` - Очистити кеш застосунку
- `config:clear` - Очистити кеш конфігурації
- `route:clear` - Очистити кеш маршрутів
- `view:clear` - Очистити скомпільовані шаблони
- `event:clear` - Очистити кеш подій
- `optimize:clear` - Очистити всі кеші одразу

### Оптимізація та кешування:
- `optimize` - Оптимізувати застосунок (config:cache + route:cache)
- `config:cache` - Кешувати конфігурацію
- `route:cache` - Кешувати маршрути
- `view:cache` - Прекомпілювати шаблони
- `event:cache` - Кешувати події

### Інші команди:
- `storage:link` - Створити symbolic link для public storage

## Встановлення

### 1. Копіювання модуля

Скопіюйте директорію `app/Modules/ArtisanManager` у ваш Laravel-проєкт за тим самим шляхом:

```
your-laravel-project/
└── app/
    └── Modules/
        └── ArtisanManager/
```

### 2. Реєстрація сервіс-провайдера

Відкрийте файл `config/app.php` та додайте сервіс-провайдер модуля до масиву `providers`:

```php
'providers' => [
    // ... інші провайдери
    App\Modules\ArtisanManager\ArtisanManagerServiceProvider::class,
],
```

### 3. Налаштування middleware (опціонально)

За замовчуванням модуль використовує middleware `['web', 'auth.admin']`. Якщо у вашому проєкті інший middleware для адмін-панелі, ви можете опублікувати конфігурацію:

```bash
php artisan vendor:publish --tag=artisan-manager-config
```

Це створить файл `config/artisan-manager.php`, де можна змінити:
- `route_prefix` - префікс маршруту (за замовчуванням `admin/artisan`)
- `middleware` - масив middleware (за замовчуванням `['web', 'auth.admin']`)

### 4. Публікація шаблонів (опціонально)

Якщо потрібно змінити зовнішній вигляд сторінки, опублікуйте шаблони:

```bash
php artisan vendor:publish --tag=artisan-manager-views
```

Після цього шаблони будуть доступні у `resources/views/vendor/artisan-manager/`.

### 5. Додавання навігації

Додайте пункт меню в адмін-панелі, який веде на маршрут `artisan.index`. Приклад:

```php
<a href="{{ route('artisan.index') }}">
    <i class="fa-solid fa-terminal"></i>
    Artisan команди
</a>
```

За замовчуванням URL буде: `/admin/artisan`

## Використання

1. Авторизуйтесь в адмін-панелі
2. Перейдіть за посиланням "Artisan команди" (або `/admin/artisan`)
3. Натисніть на відповідну кнопку для виконання команди
4. Результат виконання команди буде показано у верхній частині сторінки

## Безпека

⚠️ **ВАЖЛИВО**: Цей модуль дозволяє виконувати команди Artisan, які можуть впливати на роботу застосунку. 

Переконайтеся, що:
- Модуль доступний тільки авторизованим адміністраторам
- Middleware правильно налаштовано для захисту маршрутів
- Доступ до `/admin/artisan` обмежено відповідними правами

## Вимоги

- Laravel 10.x або новіше
- PHP 8.1 або новіше
- Налаштований middleware для адмін-панелі (за замовчуванням `auth.admin`)

## Структура модуля

```
ArtisanManager/
├── Http/
│   └── Controllers/
│       └── ArtisanController.php    # Контролер з методами для кожної команди
├── resources/
│   └── views/
│       └── index.blade.php          # Головна сторінка з кнопками
├── routes/
│   └── web.php                      # Визначення маршрутів
├── config/
│   └── artisan-manager.php          # Конфігурація модуля
├── ArtisanManagerServiceProvider.php # Сервіс-провайдер
└── README.md                         # Документація
```

## Розширення модуля

Щоб додати нові команди Artisan:

1. Додайте новий метод у `ArtisanController.php`:
```php
public function yourCommand(): RedirectResponse
{
    return $this->runCommand('your:command', 'Команда виконана успішно.');
}
```

2. Додайте маршрут у `routes/web.php`:
```php
Route::post('/your-command', [ArtisanController::class, 'yourCommand'])->name('your.command');
```

3. Додайте кнопку у `resources/views/index.blade.php`:
```html
<form method="POST" action="{{ route('artisan.your.command') }}">
    @csrf
    <button type="submit" class="...">
        <i class="fa-solid fa-icon mr-2"></i>
        Ваша команда
    </button>
</form>
```

## Видалення модуля

Щоб видалити модуль:

1. Видаліть сервіс-провайдер з `config/app.php`
2. Видаліть директорію `app/Modules/ArtisanManager`
3. (Опціонально) Видаліть опубліковані файли:
   - `config/artisan-manager.php`
   - `resources/views/vendor/artisan-manager/`

## Ліцензія

Цей модуль розповсюджується на тих самих умовах, що й Laravel framework (MIT License).

## Підтримка

Якщо виникають проблеми або питання, перевірте:
- Чи правильно зареєстровано сервіс-провайдер
- Чи налаштовано middleware
- Чи є права на виконання команд Artisan

Для додавання функціоналу просто розширте контролер та додайте відповідні маршрути й кнопки у шаблоні.
