# ROUTES_AND_ENDPOINTS.md — Маршрути та API

## Огляд маршрутів

Проєкт використовує три основні файли маршрутів:
- `routes/web.php` — публічні веб-маршрути
- `routes/admin.php` — адміністративні маршрути (middleware `auth.admin`)
- `routes/api.php` — API маршрути (middleware `api`)

---

## Web Routes (`routes/web.php`)

### Аутентифікація
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/login` | `AuthController@showLoginForm` | — | Форма логіну |
| POST | `/login` | `AuthController@login` | — | Виконання логіну |
| POST | `/logout` | `AuthController@logout` | — | Вихід з системи |

### Публічні сторінки
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/` | `HomeController@index` | — | Головна сторінка |
| GET | `/set-locale` | Closure | — | Зміна мови інтерфейсу |

### Тренажер слів (Words)
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/words/test` | `WordsTestController@index` | — | Тренажер слів (easy) |
| GET | `/words/test/medium` | `WordsTestController@index` | — | Тренажер слів (medium) |
| GET | `/words/test/hard` | `WordsTestController@index` | — | Тренажер слів (hard) |
| GET | `/words/test/state` | `WordsTestController@state` | — | Стан тесту (JSON) |
| POST | `/words/test/check` | `WordsTestController@check` | — | Перевірка відповіді |
| POST | `/words/test/reset` | `WordsTestController@reset` | — | Скидання прогресу |
| POST | `/words/test/set-study-language` | `WordsTestController@setStudyLanguage` | — | Вибір мови навчання |

### Тренажер дієслів (Verbs)
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/verbs/test` | `IrregularVerbsTestController@index` | — | Сторінка тренажера |
| GET | `/verbs/test/data` | `IrregularVerbsTestController@data` | — | JSON дані дієслів |

### Сторінки (Pages)
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/pages` | `PageController@index` | — | Список категорій |
| GET | `/pages/{category}` | `PageController@category` | — | Сторінки категорії |
| GET | `/pages/{category}/{page}` | `PageController@show` | — | Окрема сторінка |

### Теорія (Theory)
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/theory` | `TheoryController@index` | — | Список теорії |
| GET | `/theory/{category}` | `TheoryController@category` | — | Категорія теорії |
| GET | `/theory/{category}/{page}` | `TheoryController@show` | — | Сторінка теорії |

### Каталог та пошук
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/catalog/tests-cards` | `GrammarTestController@catalogAggregated` | — | Каталог тестів |
| GET | `/search` | `SiteSearchController@__invoke` | — | Глобальний пошук |
| GET | `/words` | `WordSearchController@search` | — | Пошук слів |

### Тести (публічні)
| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/test/{slug}` | `TestJsV2Controller@showSavedTestJsV2` | — | Тест (card mode) |
| GET | `/test/{slug}/step` | `TestJsV2Controller@showSavedTestJsStepV2` | — | Тест (step mode) |
| GET | `/test/{slug}/step/input` | `TestJsV2Controller@showSavedTestJsStepInputV2` | — | Step + Input |
| GET | `/test/{slug}/step/manual` | `TestJsV2Controller@showSavedTestJsStepManualV2` | — | Step + Manual |
| GET | `/test/{slug}/step/select` | `TestJsV2Controller@showSavedTestJsStepSelectV2` | — | Step + Select |
| GET | `/test/{slug}/select` | `TestJsV2Controller@showSavedTestJsSelectV2` | — | Card + Select |
| GET | `/test/{slug}/input` | `TestJsV2Controller@showSavedTestJsInputV2` | — | Card + Input |
| GET | `/test/{slug}/manual` | `TestJsV2Controller@showSavedTestJsManualV2` | — | Card + Manual |

---

## Admin Routes (`routes/admin.php`)

**Middleware**: `auth.admin` — перевіряє `session('admin_authenticated')`

### Dashboard та Site Tree
| Method | Path | Controller@action | Purpose |
|--------|------|-------------------|---------|
| GET | `/admin/` | `DeploymentController@index` | Dashboard |
| GET | `/admin/site-tree` | `SiteTreeController@index` | Дерево сайту |
| POST | `/admin/site-tree` | `SiteTreeController@store` | Створити елемент |
| PUT | `/admin/site-tree/{item}` | `SiteTreeController@update` | Оновити елемент |
| DELETE | `/admin/site-tree/{item}` | `SiteTreeController@destroy` | Видалити елемент |

### Test Tags Management
| Method | Path | Controller@action | Purpose |
|--------|------|-------------------|---------|
| GET | `/admin/test-tags/` | `TestTagController@index` | Список тегів |
| POST | `/admin/test-tags/` | `TestTagController@store` | Створити тег |
| PUT | `/admin/test-tags/{tag}` | `TestTagController@update` | Оновити тег |
| DELETE | `/admin/test-tags/{tag}` | `TestTagController@destroy` | Видалити тег |
| GET | `/admin/test-tags/aggregations` | `TestTagController@aggregations` | Агрегації тегів |
| POST | `/admin/test-tags/aggregations/auto-chatgpt` | `TestTagController@autoAggregationsChatGPT` | Auto via ChatGPT |

### Grammar Tests
| Method | Path | Controller@action | Purpose |
|--------|------|-------------------|---------|
| GET | `/admin/grammar-test` | `GrammarTestController@index` | Генератор тестів |
| POST | `/admin/grammar-test` | `GrammarTestController@generate` | Згенерувати питання |
| POST | `/admin/grammar-test-save-v2` | `GrammarTestController@saveV2` | Зберегти тест |
| GET | `/admin/tests` | `GrammarTestController@list` | Список тестів |
| PUT | `/admin/tests/{slug}` | `GrammarTestController@update` | Оновити тест |
| DELETE | `/admin/tests/{slug}` | `GrammarTestController@destroy` | Видалити тест |

### Seed Runs
| Method | Path | Controller@action | Purpose |
|--------|------|-------------------|---------|
| GET | `/admin/seed-runs` | `SeedRunController@index` | Список сідерів |
| POST | `/admin/seed-runs/run` | `SeedRunController@run` | Виконати сідер |
| POST | `/admin/seed-runs/run-missing` | `SeedRunController@runMissing` | Виконати невиконані |
| DELETE | `/admin/seed-runs/{seedRun}` | `SeedRunController@destroy` | Видалити запис |

### Question Management
| Method | Path | Controller@action | Purpose |
|--------|------|-------------------|---------|
| PUT | `/admin/questions/{question}` | `QuestionController@update` | Оновити питання |
| POST | `/admin/question-answers` | `QuestionAnswerController@store` | Додати відповідь |
| DELETE | `/admin/question-answers/{answer}` | `QuestionAnswerController@destroy` | Видалити відповідь |
| POST | `/admin/question-hints` | `QuestionHintController@store` | Додати підказку |
| POST | `/admin/question-hint` | `QuestionHelpController@hint` | AI підказка |
| POST | `/admin/question-explain` | `QuestionHelpController@explain` | AI пояснення |

### Words Export
| Method | Path | Controller@action | Purpose |
|--------|------|-------------------|---------|
| GET | `/admin/words/export` | `WordsExportController@index` | Форма експорту |
| POST | `/admin/words/export` | `WordsExportController@export` | Експорт JSON |
| POST | `/admin/words/export/import` | `WordsExportController@import` | Імпорт JSON |
| POST | `/admin/words/export/csv` | `WordsExportController@exportCsv` | Експорт CSV |

---

## API Routes (`routes/api.php`)

| Method | Path | Controller@action | Middleware | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/api/user` | Closure | `auth:sanctum` | Поточний користувач |
| GET | `/api/search` | Closure | — | Autocomplete слів |

---

## Спеціальні маршрути з параметрами

### Динамічні сторінки (Admin)
```php
// Handles any page type dynamically
Route::get('/{pageType}', [DynamicPageController::class, 'indexForType'])
    ->where('pageType', '^(?!pages|login|logout|admin|test|tests|...)$');
```

### Route Model Binding
- `{category:slug}` — категорії за slug
- `{slug}` — тести за slug

### Named Routes (приклади)
- `test.show` → `/test/{slug}`
- `theory.index` → `/theory`
- `words.test` → `/words/test`
- `site.search` → `/search`
- `admin.dashboard` → `/admin/`
