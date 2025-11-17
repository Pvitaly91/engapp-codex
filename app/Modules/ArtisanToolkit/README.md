# Модуль "ArtisanToolkit"

Панель у адмінці для запуску типових команд `php artisan` (очистка кешів, скидання оптимізацій, перезапуск черг тощо) через кнопки. Працює без доступу до консолі та легко переноситься у інші проєкти Laravel.

## Як встановити в інший проєкт Laravel

1. **Скопіюйте модуль**  
   Перенесіть директорію `app/Modules/ArtisanToolkit` у свій застосунок.

2. **Зареєструйте сервіс-провайдер**  
   Додайте `App\Modules\ArtisanToolkit\ArtisanToolkitServiceProvider::class` до масиву `providers` у `config/app.php`.

3. **(Опційно) опублікуйте конфіг та шаблони**  
   ```bash
   php artisan vendor:publish --tag=artisan-toolkit-config
   php artisan vendor:publish --tag=artisan-toolkit-views
   ```
   Конфіг `config/artisan-toolkit.php` дозволяє змінити префікс маршруту (`route_prefix`), middleware та перелік доступних команд. Шаблони можна перевизначити у `resources/views/vendor/artisan-toolkit`.

4. **Додайте посилання у меню адмінки**  
   Базовий маршрут сторінки — `artisan-toolkit.index` (типово `/admin/artisan`). Додайте лінк у навігацію, щоб швидко відкривати інструмент.

5. **Налаштуйте команди під себе**  
   У конфігу `artisan-toolkit.php` список команд задається масивом з ключами `key`, `title`, `description`, `command`, `button_label` та, за потреби, `options`, `note`, `success_message` / `error_message`. Виконується лише whitelist із конфігу, тож можна безпечно додавати власні команди.

Після цих кроків можна запускати підготовлені Artisan-команди з веб-інтерфейсу.
