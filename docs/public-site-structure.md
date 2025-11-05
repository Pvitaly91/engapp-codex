# Публічна структура сайту Engram

Цей документ описує основні сторінки та шаблони, доступні кінцевим користувачам поза префіксом `/admin/...`.

## Базовий макет
- **Шаблон:** `resources/views/layouts/engram.blade.php`
- **Функції:** фіксована шапка з навігацією (тести, теорія), адаптивний пошук, підключення TailwindCSS через CDN, кольорові токени, футер із посиланнями.
- **Компоненти:** підтримка світлої/темної теми, форми пошуку (`route('site.search')`).

## Гостьові сторінки
| URL | Роутер | Контролер / Дія | View | Призначення |
| --- | --- | --- | --- | --- |
| `/` | `Route::get('/', ...)` | `HomeController@index` | `resources/views/home.blade.php` | Презентація можливостей платформи, статистики, останніх матеріалів. |
| `/login` | `Route::get('/login', ...)` | `AuthController@showLoginForm` | `resources/views/auth/login.blade.php` | Форма входу для адміністраторів. |
| `/search` | `Route::get('/search', ...)` | `SiteSearchController` (invokable) | `resources/views/search/index.blade.php` | Пошук по тестах, сторінках, питаннях. |
| `/words` | `Route::get('/words', ...)` | `WordSearchController@search` | `resources/views/words/search.blade.php` | Пошук слів, підказки, фільтрація за тегами. |

## Модуль тестів
> Більшість роутів вимагає авторизації (`auth.admin`), але вони публічні за шляхом (без `/admin`).

| URL | Контролер | Основні шаблони |
| --- | --- | --- |
| `/grammar-test` | `GrammarTestController@index` | `resources/views/grammar-test.blade.php` — конструктор тестів.
| `/grammar-test` (POST) | `GrammarTestController@generate` | Використовує той самий шаблон для відображення згенерованих результатів.
| `/tests` | `GrammarTestController@list` | `resources/views/saved-tests.blade.php` — перелік збережених тестів.
| `/test/{slug}` + варіації (`/random`, `/step`, `/js/...`) | `GrammarTestController@show*` | Шаблони у `resources/views/saved-test*.blade.php` — різні режими проходження тесту.
| `/ai-test/*` | `AiTestController` | Послідовність шаблонів у `resources/views/ai-test-*.blade.php` для AI-практики.

## Модуль рецензій запитань
| URL | Контролер | View |
| --- | --- | --- |
| `/question-review` | `QuestionReviewController@index` | `resources/views/question-review.blade.php` — форма відбору питань для перевірки.
| `/question-review/{question}` | `QuestionReviewController@edit` | `resources/views/question-review-complete.blade.php` — перегляд конкретного питання з варіантами відповіді.
| `/question-review-results` | `QuestionReviewResultController@index` | `resources/views/question-review-results.blade.php` — список уже оброблених рецензій.

## Теоретичні сторінки
| URL | Контролер | View |
| --- | --- | --- |
| `/pages` | `PageController@index` | `resources/views/engram/pages/index.blade.php` — каталог категорій і сторінок.
| `/pages/{category:slug}` | `PageController@category` | `resources/views/engram/pages/category.blade.php` — вибрана категорія.
| `/pages/{category:slug}/{pageSlug}` | `PageController@show` | `resources/views/engram/pages/show.blade.php` — контент статті.

## Додаткові функції
- **Підказки та пояснення:** POST-роути `/question-hint`, `/question-explain` працюють через `QuestionHelpController` і взаємодіють з компонентами в тестових шаблонах.
- **Пошук/експорт запитань:** Роут групи `QuestionController`, `QuestionAnswerController` та інших сервісних контролерів використовуються компонентами інтерфейсу тестів для збереження даних.

## Файлова організація
- `resources/views/components/` — Blade-компоненти, що повторно використовуються у тестах і формах.
- `resources/views/translate/`, `resources/views/words/`, `resources/views/test-tags/` — модулі з вузькою функціональністю (переклад, словникові тести, теги) доступні через власні контролери.
- `public/` — статичні ресурси (зображення, JS) для загальних сторінок.

Ця карта допомагає швидко орієнтуватися в кодовій базі при розробці нових публічних можливостей або редизайну інтерфейсу.
