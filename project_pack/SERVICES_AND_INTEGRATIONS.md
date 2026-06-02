# SERVICES_AND_INTEGRATIONS.md — Сервіси та інтеграції

## AI Інтеграції

### 1. OpenAI (ChatGPT)

**Провайдер**: OpenAI API через `openai-php/laravel` SDK

**Конфігурація** (`config/services.php`):
```php
'openai' => [
    'key' => env('OPENAI_API_KEY', env('CHAT_GPT_API_KEY')),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'timeout' => env('OPENAI_TIMEOUT', 60),
    'max_retries' => env('OPENAI_MAX_RETRIES', 3),
],
'chatgpt' => [
    'key' => env('CHAT_GPT_API_KEY'),
    'language' => env('CHAT_GPT_LANGUAGE', 'uk'),
],
```

**Сервіс**: `app/Services/ChatGPTService.php`

**Функціонал**:
| Метод | Призначення |
|-------|-------------|
| `explainWrongAnswer()` | Пояснення чому відповідь неправильна |
| `hintSentenceStructure()` | Підказка структури речення |
| `determineTenseTags()` | Визначення часів у питанні |
| `determineDifficulty()` | Визначення рівня CEFR |
| `generateTestDescription()` | Генерація опису тесту |
| `generateGrammarQuestions()` | Генерація граматичних питань |
| `suggestTagAggregations()` | Автоагрегація тегів |
| `checkTranslation()` | Перевірка перекладу |

**Моделі для кешування**:
- `ChatGPTExplanation` — кешовані пояснення помилок
- `ChatGPTTranslationCheck` — кешовані перевірки перекладів
- `QuestionHint` — кешовані підказки (provider='chatgpt')

**Як тестувати локально**:
1. Отримайте API ключ на https://platform.openai.com
2. Додайте в `.env`: `CHAT_GPT_API_KEY=sk-...`
3. Виклик через Tinker:
```php
$gpt = app(\App\Services\ChatGPTService::class);
$gpt->hintSentenceStructure("He ___ to school yesterday.");
```

---

### 2. Google Gemini

**Провайдер**: Google Gemini API через HTTP (Guzzle)

**Конфігурація** (`config/services.php`):
```php
'gemini' => [
    'key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
    'timeout' => env('GEMINI_TIMEOUT', 60),
    'max_retries' => env('GEMINI_MAX_RETRIES', 3),
],
```

**Сервіс**: `app/Services/GeminiService.php`

**Функціонал**:
| Метод | Призначення |
|-------|-------------|
| `explainWrongAnswer()` | Пояснення помилки |
| `hintSentenceStructure()` | Підказка структури |
| `determineDifficulty()` | Визначення рівня |
| `determineTenseTags()` | Визначення часів |
| `generateGrammarQuestions()` | Генерація питань |
| `generateTestDescription()` | Опис тесту |
| `suggestTagAggregations()` | Автоагрегація |

**Endpoint**:
```php
$endpoint = 'https://generativelanguage.googleapis.com/v1beta';
$url = "{$endpoint}/models/{$model}:generateContent?key={$key}";
```

**Як тестувати локально**:
1. Отримайте API ключ на https://makersuite.google.com/app/apikey
2. Додайте в `.env`: `GEMINI_API_KEY=...`

---

## Пошук

### Реалізація
**Тип**: Вбудований MySQL LIKE пошук

**Не використовується**: Meilisearch, Algolia, Elasticsearch

**Сервіс пошуку**: `app/Http/Controllers/SiteSearchController.php`

```php
$pages = Page::query()
    ->where('title', 'like', "%{$query}%")
    ->orWhere('slug', 'like', "%{$query}%")
    ->limit(10)->get();

$tests = Test::query()
    ->where('name', 'like', "%{$query}%")
    ->orWhere('slug', 'like', "%{$query}%")
    ->limit(10)->get();
```

**Autocomplete слів**: `routes/api.php`
```php
Route::get('/search', function (Request $request) {
    return Word::with('translates')
        ->where('word', 'like', $request->q.'%')
        ->limit(8)->get();
});
```

---

## Черги (Queue)

**Конфігурація** (`config/queue.php`):
```php
'default' => env('QUEUE_CONNECTION', 'sync'),

'connections' => [
    'sync' => ['driver' => 'sync'],
    'database' => ['driver' => 'database', 'table' => 'jobs'],
    'redis' => ['driver' => 'redis'],
],
```

**Статус**: За замовчуванням `sync` — синхронне виконання.

**Jobs**: Не виявлено кастомних Job класів у репозиторії.

**Як використовувати database queue**:
1. Створіть таблицю: `php artisan queue:table && php artisan migrate`
2. Змініть `.env`: `QUEUE_CONNECTION=database`
3. Запустіть worker: `php artisan queue:work`

---

## Кеш (Cache)

**Конфігурація** (`config/cache.php`):
```php
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'file' => ['driver' => 'file', 'path' => storage_path('framework/cache/data')],
    'redis' => ['driver' => 'redis', 'connection' => 'cache'],
    'database' => ['driver' => 'database', 'table' => 'cache'],
],
```

**Статус**: За замовчуванням `file` cache.

**Використання в коді**: Основне кешування через БД (ChatGPTExplanation, QuestionHint), не через Cache facade.

---

## Пошта (Mail)

**Конфігурація** (`config/mail.php`, `config/services.php`):
```php
'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
],
'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
],
'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
],
```

**Статус**: Налаштовано конфіги, але **не виявлено** активного використання пошти в коді.

---

## Файлове сховище (Storage)

**Конфігурація** (`config/filesystems.php`):
```php
'default' => env('FILESYSTEM_DISK', 'local'),

'disks' => [
    'local' => ['driver' => 'local', 'root' => storage_path('app')],
    'public' => ['driver' => 'local', 'root' => storage_path('app/public')],
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],
```

**Статус**: Підтримка S3 налаштована, але за замовчуванням `local`.

---

## Аналітика та платежі

**Аналітика**: Не виявлено інтеграцій (Google Analytics, Mixpanel, etc.)

**Платежі**: Не виявлено інтеграцій (Stripe, PayPal, LiqPay, etc.)

---

## GitHub Deployment

**Модуль**: `app/Modules/GitDeployment/`

**Конфігурація** (`.env.example`):
```
DEPLOYMENT_GITHUB_OWNER=
DEPLOYMENT_GITHUB_REPO=
DEPLOYMENT_GITHUB_TOKEN=
DEPLOYMENT_GITHUB_USER_AGENT=EngappDeploymentBot/1.0
```

**Функціонал**: Інтеграція з GitHub для деплойменту (restore branch, etc.)

---

## Feature Flags / Env-driven Behavior

### AI Provider Selection
```php
// QuestionHelpController.php
// Використовує обидва провайдери паралельно
$chatgptHint = $gpt->hintSentenceStructure($question, $lang);
$geminiHint = $gemini->hintSentenceStructure($question, $lang);
```

### Language Manager
```php
// Динамічне визначення мов з БД
$supportedLocales = LocaleService::getActiveLanguages()->pluck('code')->toArray();
$defaultLocale = LocaleService::getDefaultLanguage()?->code;
```

### Debug Mode
```php
// Middleware DisableDebugbarForGuests
// Вимикає debugbar для неавторизованих
if (!session('admin_authenticated')) {
    Debugbar::disable();
}
```

---

## Резюме інтеграцій

| Сервіс | Статус | Конфіг |
|--------|--------|--------|
| OpenAI (ChatGPT) | ✅ Активно | `CHAT_GPT_API_KEY` |
| Google Gemini | ✅ Активно | `GEMINI_API_KEY` |
| MySQL | ✅ Активно | `DB_*` |
| File Cache | ✅ За замовчуванням | `CACHE_DRIVER=file` |
| Redis Cache | ⚙️ Налаштовано | `CACHE_DRIVER=redis` |
| Sync Queue | ✅ За замовчуванням | `QUEUE_CONNECTION=sync` |
| Database Queue | ⚙️ Налаштовано | `QUEUE_CONNECTION=database` |
| S3 Storage | ⚙️ Налаштовано | `AWS_*` |
| Mail (Mailgun/SES) | ⚙️ Налаштовано | Не використовується |
| GitHub API | ✅ Активно | `DEPLOYMENT_GITHUB_*` |
| Meilisearch/Algolia | ❌ Не підключено | — |
| Horizon | ❌ Не підключено | — |
| Analytics | ❌ Не підключено | — |
| Payments | ❌ Не підключено | — |
