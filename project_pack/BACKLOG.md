# BACKLOG.md — Техборг та покращення

## Backlog таблиця

| Title | Priority | Effort | Area | Notes |
|-------|----------|--------|------|-------|
| Rate limiting для AI endpoints | P1 | S | Security | Захист від зловживань API |
| Система ролей/permissions | P1 | M | Auth | Spatie Permissions або кастомна |
| Full-text search (Meilisearch) | P2 | M | Search | Замість LIKE запитів |
| Queue для AI запитів | P2 | M | Performance | Async processing |
| Audit logging | P2 | S | Security | Логування адмін дій |
| Tests coverage | P2 | L | Quality | PHPUnit тести для сервісів |
| API documentation | P2 | M | DX | OpenAPI/Swagger spec |
| CI/CD pipeline | P2 | M | DevOps | GitHub Actions |
| Redis cache | P2 | S | Performance | Замість file cache |
| Horizon dashboard | P2 | S | Monitoring | Для queue management |
| 2FA для адмінів | P2 | M | Security | TOTP authentication |
| Sanitize AI responses | P1 | S | Security | XSS prevention |
| Error monitoring | P2 | S | Monitoring | Sentry/Bugsnag integration |
| Database indexing review | P2 | S | Performance | Optimize slow queries |
| Soft deletes для Questions | P2 | S | Data | Відновлення видалених |
| API versioning | P2 | M | Architecture | /api/v1/, /api/v2/ |
| Feature flags | P2 | M | Architecture | LaunchDarkly або env-based |
| CDN для static assets | P2 | S | Performance | CloudFlare/AWS CloudFront |
| Image optimization | P2 | S | Performance | WebP conversion |
| Localization completeness | P2 | M | i18n | Повний переклад UK/EN/PL |
| Dark mode persistence | P1 | S | UX | Зберігання в cookie/localStorage |
| Mobile responsiveness audit | P2 | M | UX | Tailwind responsive review |
| Accessibility (a11y) | P2 | M | UX | WCAG compliance |
| SEO meta tags | P2 | S | SEO | Dynamic og:tags |
| Sitemap generation | P2 | S | SEO | Artisan command |
| Backup automation | P1 | S | DevOps | Scheduled DB backups |
| Health check endpoint | P2 | S | Monitoring | /health route |
| Admin dashboard metrics | P2 | M | Admin | Stats, charts |
| Question import validation | P2 | S | Admin | Schema validation |
| Duplicate question detection | P2 | M | Quality | AI-based similarity |

---

## Quick Wins (зробити швидко, високий impact)

### 1. Rate Limiting для AI
**Effort**: 1-2 години  
**Impact**: Захист від фінансових втрат

```php
// routes/admin.php
Route::post('/question-hint', ...)->middleware('throttle:10,1');
Route::post('/question-explain', ...)->middleware('throttle:10,1');
```

### 2. Health Check Endpoint
**Effort**: 30 хвилин  
**Impact**: Моніторинг production

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'db' => DB::connection()->getPdo() ? 'connected' : 'error',
    ]);
});
```

### 3. Dark Mode Cookie
**Effort**: 1 година  
**Impact**: UX покращення

```javascript
// Зберігати тему в localStorage замість session
localStorage.setItem('theme', 'dark');
```

### 4. Sitemap
**Effort**: 2 години  
**Impact**: SEO

```php
// artisan make:command GenerateSitemap
// Генерувати XML з pages, tests
```

### 5. Error Logging до Sentry
**Effort**: 1 година  
**Impact**: Production monitoring

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish
```

---

## Техборг (Technical Debt)

### Code Quality

| Issue | Location | Severity |
|-------|----------|----------|
| Hardcoded strings замість lang files | Views, Controllers | Low |
| Magic strings для типів | `Question::TYPE_*` | Low |
| Великі контролери | `GrammarTestController` | Medium |
| Дублювання логіки | ChatGPT/Gemini services | Low |
| Відсутні DocBlocks | Деякі методи | Low |

### Architecture

| Issue | Description | Recommendation |
|-------|-------------|----------------|
| Fat controllers | `GrammarTestController` має багато методів | Extract to services |
| Tight coupling to AI providers | Прямі виклики в controllers | Strategy pattern |
| Session-based test state | WordsTestController | Redis або DB storage |
| No repository pattern | Прямі Eloquent calls | Optional: repositories |

### Database

| Issue | Description | Recommendation |
|-------|-------------|----------------|
| No indexes on frequently queried columns | `questions.uuid`, `pages.slug` | Add indexes |
| N+1 queries potential | Перевірити eager loading | Debug bar analysis |
| Spelling: `quastion_*` tables | Typo в назвах | Migration rename (optional) |

### Testing

| Issue | Current State | Target |
|-------|---------------|--------|
| Unit tests | Базовий phpunit.xml | Cover services |
| Feature tests | Не виявлено | Add for API endpoints |
| Integration tests | Не виявлено | Add for AI services (mocked) |

---

## Prioritization Matrix

```
High Impact
    │
    │  P1: Rate Limiting    P1: Sanitize AI
    │  P1: Backup           P1: Dark Mode
    │
    │  P2: Meilisearch      P2: Tests
    │  P2: Queue            P2: CI/CD
    │
Low ─┼─────────────────────────────────────── High Effort
    │
    │  P2: Health Check     P2: Roles System
    │  P2: Sitemap          P2: API Docs
    │
    │
Low Impact
```

---

## Версіонування

| Version | Focus | Key Items |
|---------|-------|-----------|
| v1.1 | Security | Rate limiting, sanitization, backups |
| v1.2 | Performance | Redis cache, queue, search |
| v1.3 | Quality | Tests, CI/CD, monitoring |
| v2.0 | Features | Roles, API v2, mobile app |
