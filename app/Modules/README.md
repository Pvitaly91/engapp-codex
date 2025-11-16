# Інструкція з встановлення модулів `app/Modules`

## Модуль "DatabaseStructure"
Цей модуль додає в адмін-панель сторінку для перегляду структури бази даних (таблиці, стовпці, типи та інші метадані). Нижче наведено кроки для підключення модуля до будь-якого Laravel-проєкту.

### 1. Копіюйте модуль у проєкт
- Скопіюйте директорію `app/Modules/DatabaseStructure` у свій Laravel-проєкт (наприклад, у той самий шлях `app/Modules/DatabaseStructure`).

### 2. Зареєструйте сервіс-провайдер
- Відкрийте `config/app.php` та додайте до масиву `providers` клас `App\Modules\DatabaseStructure\DatabaseStructureServiceProvider::class`.
- Переконайтеся, що у вашій адмін-панелі існує middleware `auth.admin` (або замініть його на власний у файлі `app/Modules/DatabaseStructure/routes/web.php`).

### 3. Опублікуйте конфігурацію та (за потреби) шаблони
- Запустіть команду:
  ```bash
  php artisan vendor:publish --tag=database-structure-config
  ```
  Це створить файл `config/database-structure.php`, де можна змінити префікс маршруту (`route_prefix`) та вказати окреме підключення до бази (`connection`).
  Тут же доступні налаштування для контент-менеджменту: ви можете додавати псевдоніми колонок (`aliases`), приховувати поля (`hidden`) і задавати відображувані значення для зовнішніх ключів через секцію `relations`, наприклад ` 'relations' => ['user_id' => 'users.name']`.
- Щоб перевизначити Blade-шаблони, виконайте (необов'язково):
  ```bash
  php artisan vendor:publish --tag=database-structure-views
  ```
  Після цього шаблони будуть доступні у `resources/views/vendor/database-structure`.

### 4. Оновіть навігацію адмін-панелі
- Додайте пункт меню, який веде на маршрут `database-structure.index` (у стандартній конфігурації – `/admin/database-structure`).
- У прикладі з цього репозиторію відповідні посилання знаходяться у `resources/views/layouts/app.blade.php`.

### 5. Перевірте доступність сторінки
- Після авторизації в адмінці відкрийте URL з пункту меню (наприклад, `/admin/database-structure`).
- Ви повинні побачити перелік таблиць з можливістю переглянути стовпці та фільтрувати їх за назвою.

Після виконання цих кроків модуль буде готовий до роботи в іншому Laravel-проєкті.

## Модуль "PageManager"

Модуль виносить інтерфейс керування сторінками `/admin/pages/manage` в окрему директорію `app/Modules/PageManager`.

### Як встановити
1. Скопіюйте папку `app/Modules/PageManager` у ваш застосунок.
2. Додайте `App\Modules\PageManager\PageManagerServiceProvider::class` у масив `providers` файлу `config/app.php`.
3. Запустіть `php artisan migrate`, щоб застосувати вбудовані міграції (`pages`, `page_categories`, `text_blocks`). За потреби опублікуйте їх командою `php artisan vendor:publish --tag=page-manager-migrations`.
4. За потреби опублікуйте конфіг/шаблони:
   ```bash
   php artisan vendor:publish --tag=page-manager-config
   php artisan vendor:publish --tag=page-manager-views
   ```
5. Налаштуйте доступи (middleware `auth.admin`) і навігацію адмінки на маршрут `pages.manage.index`.

Детальніша інструкція розміщена у `app/Modules/PageManager/README.md`.
