# LOCALIZATION.md — Локалізація (i18n)

## Огляд

**Підтримувані мови**: UK (українська), EN (англійська), PL (польська)  
**Мова за замовчуванням**: UK  
**Механізм**: Laravel lang files + LanguageManager module

---

## Структура файлів локалізації

```
resources/lang/
├── en/
│   ├── messages.php     # Системні повідомлення
│   ├── public.php       # Публічні тексти UI
│   ├── verbs.php        # Тренажер дієслів
│   └── words_test.php   # Тренажер слів
├── uk/
│   ├── public.php       # Основні UI тексти
│   ├── verbs.php
│   └── words_test.php
└── pl/
    └── ...
```

---

## Ключові файли локалізації

### public.php — Публічний UI
```php
return [
    'meta' => [
        'title' => 'Gramlyze — Платформа для викладачів англійської',
        'description' => '...',
    ],
    'nav' => [
        'catalog' => 'Каталог',
        'theory' => 'Теорія',
        'words_test' => 'Слова',
        'verbs_test' => 'Дієслова',
    ],
    'search' => [...],
    'footer' => [...],
    'home' => [...],
    'theory' => [...],
    'language' => [...],
    'theme' => [...],
];
```

### words_test.php — Тренажер слів
Переклади для UI тренажера слів.

### verbs.php — Тренажер дієслів
Переклади для UI тренажера дієслів.

---

## LanguageManager Module

**Шлях**: `app/Modules/LanguageManager/`

**Структура**:
```
LanguageManager/
├── Http/
├── Models/
│   └── Language.php
├── Services/
│   └── LocaleService.php
├── database/
│   └── migrations/
├── config/
└── resources/
```

### LocaleService

Основний сервіс для роботи з мовами:

```php
use App\Modules\LanguageManager\Services\LocaleService;

// Отримати активні мови
$languages = LocaleService::getActiveLanguages();

// Мова за замовчуванням
$default = LocaleService::getDefaultLanguage();

// Поточна мова
$current = LocaleService::getCurrentLocale();

// URL для перемикання мови
$url = LocaleService::switchLocaleUrl('en');

// Локалізований маршрут
$url = LocaleService::localizedRoute('theory.show', ['category' => 'tenses', 'page' => 'present-simple']);
```

---

## Fallback логіка

```php
// config/language-manager.php або код
$fallbackLocale = LocaleService::getDefaultLanguage()?->code
    ?? config('language-manager.fallback_locale', 'uk');
```

**Порядок fallback**:
1. Мова з сесії/cookie
2. Мова з БД (languages table)
3. Конфіг `app.locale`
4. Хардкодований 'uk'

---

## Перемикання мови

### Route
```php
// routes/web.php
Route::get('/set-locale', function (Request $request) {
    $lang = $request->input('lang', 'uk');
    
    // Валідація мови
    $supportedLocales = LocaleService::getActiveLanguages()->pluck('code')->toArray();
    if (!in_array($lang, $supportedLocales)) {
        $lang = LocaleService::getDefaultLanguage()?->code ?? 'uk';
    }
    
    session(['locale' => $lang]);
    app()->setLocale($lang);
    
    // Cookie на 1 рік
    $cookie = cookie('locale', $lang, 60 * 24 * 365);
    
    return redirect()->back()->withCookie($cookie);
})->name('locale.set');
```

### Middleware
```php
// app/Http/Middleware/SetLocale.php
public function handle($request, $next)
{
    $locale = $request->cookie('locale') 
        ?? session('locale') 
        ?? app()->getLocale();
    
    app()->setLocale($locale);
    
    return $next($request);
}
```

---

## SEO-friendly URLs

### Локалізовані маршрути
```php
// Без локалі (default UK): /theory/tenses/present-simple
// З локаллю EN: /en/theory/tenses/present-simple
// З локаллю PL: /pl/theory/tenses/present-simple
```

### Генерація URL
```php
// LocaleService::localizedRoute() обробляє префікси автоматично
$url = LocaleService::localizedRoute('theory.show', [
    'category' => 'tenses',
    'page' => 'present-simple'
], true, 'en');
// Результат: https://gramlyze.com/en/theory/tenses/present-simple
```

---

## Контент локалізація

### TextBlocks
```php
// Блоки тексту мають поле locale
TextBlock::where('page_id', $pageId)
    ->where('locale', app()->getLocale())
    ->orderBy('sort_order')
    ->get();
```

### Fallback для контенту
```php
// PageController.php
protected function filterBlocksByChosenLocale(Collection $blocks, string $preferred, string $fallback): array
{
    $usedLocale = $this->chooseLocaleForBlocks($blocks, $preferred, $fallback);
    return [$blocks->where('locale', $usedLocale)->values(), $usedLocale];
}
```

---

## Використання в Blade

### Переклади
```blade
{{ __('public.nav.catalog') }}
{{ __('public.home.title') }}

@lang('public.search.placeholder')
```

### Перемикач мов
```blade
@php
$languages = \App\Modules\LanguageManager\Services\LocaleService::getLanguageSwitcherData();
@endphp

<select onchange="window.location.href=this.value">
    @foreach($languages as $lang)
        <option value="{{ $lang['url'] }}" @selected($lang['is_current'])>
            {{ $lang['native_name'] }}
        </option>
    @endforeach
</select>
```

---

## База даних: Language model

```php
// app/Modules/LanguageManager/Models/Language.php
// Таблиця: languages

Schema::create('languages', function (Blueprint $table) {
    $table->id();
    $table->string('code', 5)->unique();  // 'uk', 'en', 'pl'
    $table->string('name');               // 'Ukrainian'
    $table->string('native_name');        // 'Українська'
    $table->boolean('is_active')->default(true);
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});
```

---

## Checklist локалізації

### Повнота перекладів
| Файл | UK | EN | PL |
|------|----|----|-----|
| public.php | ✅ | ✅ | ⚠️ Частково |
| words_test.php | ✅ | ✅ | ⚠️ Частково |
| verbs.php | ✅ | ✅ | ⚠️ Частково |
| messages.php | ⚠️ | ✅ | ⚠️ |

### Контент
| Тип | UK | EN | PL |
|-----|----|----|-----|
| TextBlocks | ✅ | ⚠️ Частково | ❌ |
| PageCategories | ✅ | ⚠️ | ❌ |
| Word translations | ✅ | — | ✅ |

---

## Рекомендації

1. **Завершити переклади PL** — файли lang та контент
2. **Додати missing keys** — перевірити console на missing translations
3. **SEO hreflang** — додати `<link rel="alternate" hreflang="...">` в head
4. **Content audit** — перевірити TextBlocks для EN/PL версій
5. **RTL support** — якщо планується Arabic/Hebrew (наразі не потрібно)
