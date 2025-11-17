# Модуль Artisan Commands

Цей модуль додає веб-інтерфейс для виконання Laravel Artisan команд через адмін-панель. Він надає зручний спосіб керувати кешем, оптимізацією та обслуговуванням додатку без доступу до командного рядка.

## Функціональність

Модуль включає кнопки для виконання наступних команд:

### Очистка кешу
- `cache:clear` - Очистити кеш додатку
- `config:clear` - Очистити кеш конфігурації
- `route:clear` - Очистити кеш маршрутів
- `view:clear` - Очистити кеш шаблонів
- `optimize:clear` - Очистити весь кеш

### Оптимізація
- `config:cache` - Кешувати конфігурацію
- `route:cache` - Кешувати маршрути
- `view:cache` - Кешувати шаблони
- `optimize` - Оптимізувати додаток

### Обслуговування
- `storage:link` - Створити symbolic link для storage
- `queue:restart` - Перезапустити черги

## Структура модуля

```
app/Modules/ArtisanCommands/
├── ArtisanCommandsServiceProvider.php  # Service Provider модуля
├── Http/
│   └── Controllers/
│       └── ArtisanCommandsController.php  # Контролер для виконання команд
├── config/
│   └── artisan-commands.php  # Конфігурація доступних команд
├── resources/
│   └── views/
│       └── index.blade.php  # Головна сторінка інтерфейсу
├── routes/
│   └── web.php  # Маршрути модуля
└── README.md  # Ця інструкція
```

## Встановлення в новий Laravel проєкт

### 1. Копіювання модуля

Скопіюйте директорію `app/Modules/ArtisanCommands` у ваш Laravel проєкт у той самий шлях:

```bash
cp -r app/Modules/ArtisanCommands /path/to/your/project/app/Modules/
```

### 2. Реєстрація Service Provider

Відкрийте файл `config/app.php` та додайте Service Provider до масиву `providers`:

```php
'providers' => [
    // ... інші провайдери
    App\Modules\ArtisanCommands\ArtisanCommandsServiceProvider::class,
],
```

**Важливо:** Переконайтеся, що у вашому проєкті існує middleware `auth.admin` або змініть його в конфігурації модуля на ваш власний middleware для адміністраторів.

### 3. Публікація конфігурації (опціонально)

Якщо ви хочете налаштувати доступні команди, опублікуйте конфігурацію:

```bash
php artisan vendor:publish --tag=artisan-commands-config
```

Це створить файл `config/artisan-commands.php`, де ви можете:
- Змінити префікс маршруту (`route_prefix`)
- Налаштувати middleware (`middleware`)
- Додати, видалити або змінити доступні команди (`commands`)

### 4. Публікація шаблонів (опціонально)

Якщо ви хочете налаштувати зовнішній вигляд, опублікуйте шаблони:

```bash
php artisan vendor:publish --tag=artisan-commands-views
```

Шаблони будуть доступні у `resources/views/vendor/artisan-commands`.

### 5. Додавання до навігації

Додайте посилання на модуль у вашій навігаційній панелі. Наприклад, у `resources/views/layouts/app.blade.php`:

```php
<a href="{{ route('artisan-commands.index') }}" class="hover:text-blue-500 transition">
    Artisan команди
</a>
```

Або додайте до існуючого dropdown меню "Деплой" або "Обслуговування":

```php
<a href="{{ route('artisan-commands.index') }}" class="block px-4 py-2 hover:bg-blue-50">
    Artisan команди
</a>
```

### 6. Перевірка роботи

Після виконання всіх кроків, перейдіть за адресою:

```
/admin/artisan-commands
```

Ви побачите інтерфейс з кнопками для виконання команд.

## Налаштування команд

Ви можете легко додати власні команди у файлі `config/artisan-commands.php`:

```php
'commands' => [
    'custom_category' => [
        [
            'key' => 'my_command',
            'title' => 'Назва команди',
            'description' => 'Опис команди',
            'command' => 'my:command',
            'icon' => 'fa-star',  // Font Awesome icon
            'color' => 'purple',  // Tailwind color
            'confirmation_required' => false,
        ],
    ],
],
```

### Параметри команди

- `key` (string) - Унікальний ключ команди
- `title` (string) - Назва, що відображається на кнопці
- `description` (string) - Опис команди
- `command` (string) - Artisan команда для виконання
- `icon` (string) - Font Awesome клас іконки (опціонально)
- `color` (string) - Колір Tailwind для кнопки (опціонально)
- `confirmation_required` (boolean) - Чи потрібне підтвердження перед виконанням

## Безпека

- Модуль захищений middleware `auth.admin` (за замовчуванням)
- Всі POST запити захищені CSRF токенами
- Команди виконуються на сервері через `Artisan::call()`
- Не передає користувацький ввід безпосередньо в команди

## Вимоги

- PHP 8.1+
- Laravel 10+
- Font Awesome 6+ (для іконок)
- Alpine.js 3+ (вже включено в шаблон)
- Tailwind CSS (вже включено в шаблон)

## Підтримка

Модуль розроблено для легкого портування між Laravel проєктами. Всі залежності самодостатні і не потребують додаткових пакетів Composer.

## Ліцензія

MIT
