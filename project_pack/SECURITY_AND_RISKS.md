# SECURITY_AND_RISKS.md — Безпека та ризики

## Аутентифікація та авторизація

### Authentication
| Механізм | Опис |
|----------|------|
| **Session-based** | Адмін аутентифікація через `session('admin_authenticated')` |
| **Laravel Sanctum** | API токени для SPA/mobile (налаштовано, обмежено використовується) |
| **Login Form** | `AuthController@login` — стандартна форма |

### Guards
```php
// config/auth.php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'sanctum' => ['driver' => 'sanctum', 'provider' => 'users'],
],
```

### Admin Middleware
```php
// app/Http/Middleware/AdminAuthenticate.php
if (!$request->session()->get('admin_authenticated', false)) {
    return redirect()->route('login.show');
}
```

### Policies & Gates
**Статус**: Не виявлено кастомних Policies або Gates.

### Roles
**Статус**: Не виявлено повноцінної системи ролей. Використовується простий флаг `admin_authenticated` в сесії.

---

## Типові ризики та їх статус

### 1. Mass Assignment Protection

**Ризик**: Неконтрольоване заповнення полів моделі

**Статус**: ✅ Захищено через `$fillable`

```php
// Приклад з Question.php
protected $fillable = [
    'uuid', 'question', 'difficulty', 'level',
    'category_id', 'source_id', 'flag', 'seeder', 'type',
];
```

**Рекомендація**: Переконатися, що всі моделі мають явний `$fillable`.

---

### 2. File Uploads

**Статус**: ⚠️ Частково

- Немає публічного upload endpoint для користувачів
- Сідери та імпорт файлів доступні тільки адмінам
- `WordsExportController@import` — імпорт JSON/CSV

**Рекомендації**:
- Валідувати MIME-типи при імпорті
- Обмежувати розмір файлів
- Зберігати uploads поза public директорією

---

### 3. XSS (Cross-Site Scripting)

**Статус**: ✅ Захищено через Blade

Blade за замовчуванням escape-ить вивід:
```blade
{{ $variable }}  <!-- Escaped -->
{!! $html !!}    <!-- Raw HTML - небезпечно -->
```

**Ризикові місця**:
- AI-генерований HTML в описах тестів (`{!! $test->description !!}`)
- TextBlock body може містити HTML

**Рекомендації**:
- Аудит всіх `{!! !!}` використань
- Санітизація AI-відповідей перед збереженням

---

### 4. CSRF Protection

**Статус**: ✅ Захищено

```php
// app/Http/Middleware/VerifyCsrfToken.php
// Стандартний Laravel CSRF middleware активний
```

Blade автоматично додає CSRF токен в форми:
```blade
<form method="POST">
    @csrf
</form>
```

---

### 5. SQL Injection

**Статус**: ✅ Захищено через Eloquent

```php
// Eloquent автоматично параметризує запити
Question::where('uuid', $uuid)->first();

// Використання raw queries - потенційний ризик
DB::raw(...) // Аудитувати
whereRaw(...) // Аудитувати
```

**Знайдені raw queries** (потрібен аудит):
- `QuestionHelpController.php` — `whereRaw('LOWER(TRIM(...))')`

---

### 6. Rate Limiting

**Статус**: ⚠️ Недостатньо

- Стандартний Laravel rate limiting для API: `throttle:api`
- **AI endpoints не мають rate limiting**

**Ризик**: DDoS на AI endpoints може спричинити великі рахунки за API.

**Рекомендації**:
```php
// routes/admin.php
Route::post('/question-hint', [QuestionHelpController::class, 'hint'])
    ->middleware('throttle:10,1'); // 10 requests per minute
```

---

### 7. SSRF (Server-Side Request Forgery)

**Статус**: ⚠️ Потенційний ризик в AI інтеграціях

```php
// GeminiService.php
$url = "{$this->endpoint}/models/{$model}:generateContent?key={$key}";
$response = Http::post($url, $payload);
```

**Ризик**: Якщо `$model` контролюється користувачем — можливий SSRF.

**Поточний статус**: Модель читається з конфігу, не від користувача. ✅ Безпечно.

---

### 8. Information Disclosure

**Статус**: ⚠️ Потрібна увага

| Проблема | Статус |
|----------|--------|
| `.env.example` не містить секретів | ✅ OK |
| `APP_DEBUG=false` в production | ⚙️ Перевірити |
| Stack traces в production | Залежить від APP_DEBUG |
| Debugbar для гостей | ✅ Вимкнено (middleware) |

---

### 9. Session Security

**Статус**: ⚙️ Налаштовується

```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', false),
'http_only' => true,
'same_site' => 'lax',
```

**Рекомендації для production**:
```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

---

### 10. Open Redirect

**Статус**: ✅ Захищено

```php
// routes/web.php - set-locale
$referer = $request->headers->get('referer');
$host = $request->getHost();

// Валідація referer від того ж хоста
if ($referer && parse_url($referer, PHP_URL_HOST) === $host) {
    return redirect()->back()->withCookie($cookie);
}
return redirect()->route('home')->withCookie($cookie);
```

---

## P0/P1/P2 Список проблем та рекомендацій

### P0 (Критичні — виправити негайно)
| # | Проблема | Рекомендація |
|---|----------|--------------|
| — | Не виявлено критичних проблем | — |

### P1 (Високий пріоритет)
| # | Проблема | Рекомендація |
|---|----------|--------------|
| 1 | Відсутній rate limiting для AI endpoints | Додати throttle middleware |
| 2 | APP_DEBUG може бути true в production | Перевірити .env на сервері |
| 3 | Немає системи ролей | Впровадити Spatie Permissions або власну систему |

### P2 (Середній пріоритет)
| # | Проблема | Рекомендація |
|---|----------|--------------|
| 4 | Raw HTML в {!! description !!} | Санітизувати AI-відповіді |
| 5 | Немає audit log для адмін дій | Впровадити activity logging |
| 6 | API ключі в .env без ротації | Впровадити secrets management |
| 7 | Немає 2FA для адмінів | Розглянути впровадження |
| 8 | Session-based admin auth | Розглянути token-based для API |

---

## Security Headers (рекомендовано)

```php
// Middleware або Nginx config
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'
```

---

## Checklist перед production

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Унікальний `APP_KEY`
- [ ] Secure cookies enabled
- [ ] HTTPS only
- [ ] Rate limiting на AI endpoints
- [ ] Database backups налаштовано
- [ ] Logs не містять sensitive data
- [ ] `.env` не в Git
- [ ] Debugbar вимкнено
