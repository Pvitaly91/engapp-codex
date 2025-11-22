# Розширення функціоналу тегів для сторінок теорії

## Опис

Цей функціонал розширює систему тегів тестів (`/admin/test-tags`), дозволяючи використовувати теги для категорій та сторінок теорії. Тепер теги можуть бути пов'язані не тільки з питаннями та словами, але й зі сторінками теорії (Pages) та категоріями сторінок теорії (PageCategories).

## Що було реалізовано

### 1. База даних

Створено дві нові таблиці для зв'язків many-to-many:

- **`page_tag`** - зв'язок між тегами та сторінками теорії
  - `id` - первинний ключ
  - `page_id` - зовнішній ключ на таблицю `pages`
  - `tag_id` - зовнішній ключ на таблицю `tags`
  - `timestamps` - часові мітки створення/оновлення
  - Унікальний індекс на пару `(page_id, tag_id)`

- **`page_category_tag`** - зв'язок між тегами та категоріями сторінок теорії
  - `id` - первинний ключ
  - `page_category_id` - зовнішній ключ на таблицю `page_categories`
  - `tag_id` - зовнішній ключ на таблицю `tags`
  - `timestamps` - часові мітки створення/оновлення
  - Унікальний індекс на пару `(page_category_id, tag_id)`

**Міграції:**
- `database/migrations/2025_11_21_000001_create_page_tag_table.php`
- `database/migrations/2025_11_21_000002_create_page_category_tag_table.php`

### 2. Моделі

Оновлено моделі для підтримки нових відношень:

**Tag.php:**
```php
public function pages()
{
    return $this->belongsToMany(Page::class);
}

public function pageCategories()
{
    return $this->belongsToMany(PageCategory::class);
}
```

**Page.php:**
```php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

**PageCategory.php:**
```php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

### 3. Контролер (TestTagController)

Додано нові методи:

#### Перегляд пов'язаних сторінок та категорій:

- **`pages(Tag $tag): JsonResponse`** - Повертає список сторінок теорії, пов'язаних з тегом
- **`pageCategories(Tag $tag): JsonResponse`** - Повертає список категорій сторінок теорії, пов'язаних з тегом

#### Управління зв'язками:

- **`attachPage(Request $request, Tag $tag): JsonResponse`** - Прив'язує тег до сторінки
- **`detachPage(Request $request, Tag $tag): JsonResponse`** - Від'єднує тег від сторінки
- **`attachPageCategory(Request $request, Tag $tag): JsonResponse`** - Прив'язує тег до категорії
- **`detachPageCategory(Request $request, Tag $tag): JsonResponse`** - Від'єднує тег від категорії

#### Оновлені методи:

- **`destroy()`** - Тепер також від'єднує теги від сторінок і категорій перед видаленням
- **`destroyCategory()`** - Оновлено для роботи з новими зв'язками

### 4. Маршрути

Додано нові маршрути в `routes/web.php`:

```php
Route::get('/{tag}/pages', [TestTagController::class, 'pages'])->name('pages');
Route::post('/{tag}/pages', [TestTagController::class, 'attachPage'])->name('pages.attach');
Route::delete('/{tag}/pages', [TestTagController::class, 'detachPage'])->name('pages.detach');

Route::get('/{tag}/page-categories', [TestTagController::class, 'pageCategories'])->name('page-categories');
Route::post('/{tag}/page-categories', [TestTagController::class, 'attachPageCategory'])->name('page-categories.attach');
Route::delete('/{tag}/page-categories', [TestTagController::class, 'detachPageCategory'])->name('page-categories.detach');
```

### 5. Користувацький інтерфейс

#### Сторінка списку тегів (`/admin/test-tags`)

На сторінці індексу тегів додано дві нові кнопки для кожного тегу:

- **"Сторінки"** - Показує всі сторінки теорії, пов'язані з тегом
- **"Категорії"** - Показує всі категорії сторінок теорії, пов'язані з тегом

Обидві кнопки розташовані поруч з назвою тегу і завантажують дані через AJAX.

#### Сторінка редагування тегу (`/admin/test-tags/{tag}/edit`)

Додано два нові розділи в кінці сторінки:

1. **"Пов'язані сторінки теорії"** - Секція для перегляду та управління сторінками, пов'язаними з тегом
2. **"Пов'язані категорії теорії"** - Секція для перегляду та управління категоріями, пов'язаними з тегом

Кожна секція має кнопку для завантаження відповідних даних через AJAX.

#### View шаблони

Створено нові часткові шаблони (partials):

- `resources/views/test-tags/partials/pages-list.blade.php` - Відображає список сторінок
- `resources/views/test-tags/partials/page-categories-list.blade.php` - Відображає список категорій

Обидва шаблони показують:
- Назву сторінки/категорії
- Відповідну категорію (для сторінок)
- Кількість сторінок (для категорій)
- Посилання для перегляду на публічній частині сайту

## Як використовувати

### 1. Запуск міграцій

Перед використанням функціоналу потрібно виконати міграції:

```bash
php artisan migrate
```

### 2. Перегляд пов'язаних сторінок та категорій

1. Перейдіть до `/admin/test-tags`
2. Розгорніть будь-яку категорію тегів
3. Натисніть на кнопку **"Сторінки"** або **"Категорії"** біля потрібного тегу
4. Відобразиться список пов'язаних сторінок або категорій

### 3. Управління зв'язками через API

Для програмного прив'язування тегів до сторінок або категорій можна використовувати API endpoints:

#### Прив'язати тег до сторінки:
```javascript
POST /admin/test-tags/{tag}/pages
Content-Type: application/json

{
  "page_id": 123
}
```

#### Від'єднати тег від сторінки:
```javascript
DELETE /admin/test-tags/{tag}/pages
Content-Type: application/json

{
  "page_id": 123
}
```

#### Прив'язати тег до категорії:
```javascript
POST /admin/test-tags/{tag}/page-categories
Content-Type: application/json

{
  "page_category_id": 45
}
```

#### Від'єднати тег від категорії:
```javascript
DELETE /admin/test-tags/{tag}/page-categories
Content-Type: application/json

{
  "page_category_id": 45
}
```

### 4. Використання в коді

Приклади використання в PHP коді:

```php
// Отримати всі теги для сторінки
$page = Page::find(1);
$tags = $page->tags;

// Отримати всі сторінки для тегу
$tag = Tag::find(1);
$pages = $tag->pages;

// Прив'язати тег до сторінки
$page->tags()->attach($tagId);

// Від'єднати тег від сторінки
$page->tags()->detach($tagId);

// Отримати всі категорії для тегу
$categories = $tag->pageCategories;

// Прив'язати тег до категорії
$category->tags()->attach($tagId);
```

## Переваги

1. **Організація контенту** - Тепер можна організувати теоретичні матеріали за допомогою тегів, так само як питання
2. **Зручний пошук** - Користувачі можуть знайти всі матеріали (питання, слова, сторінки теорії) за певним тегом
3. **Інтеграція** - Теги для сторінок теорії використовують ту ж систему, що й теги для питань та слів
4. **Гнучкість** - Одна сторінка може мати кілька тегів, один тег може бути пов'язаний з кількома сторінками

## Технічні деталі

- Усі зв'язки реалізовані через таблиці many-to-many (pivot tables)
- Використано каскадне видалення (cascade on delete) для збереження цілісності даних
- AJAX-завантаження для швидкого перегляду без перезавантаження сторінки
- Валідація даних на рівні контролера
- Використано JSON API для комунікації між фронтендом та бекендом

## Майбутні можливості

Можливі напрямки розвитку функціоналу:

1. Додати можливість масового прив'язування тегів до сторінок
2. Створити фільтри для пошуку сторінок за тегами на публічній частині сайту
3. Додати автоматичні рекомендації тегів для нових сторінок на основі контенту
4. Реалізувати статистику використання тегів для сторінок теорії
5. Інтегрувати теги сторінок з системою агрегації тегів

## Автори

- Реалізовано як частина розширення функціоналу системи тегів для Engapp Codex
