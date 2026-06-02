# CHECKLIST.md — Project Pack Checklist

## Створені файли в /project_pack

| # | Файл | Опис | Статус |
|---|------|------|--------|
| 1 | `PROJECT_OVERVIEW.md` | Огляд системи, стек, інструкція запуску | ✅ |
| 2 | `ARCHITECTURE.md` | Архітектура, Mermaid діаграми, модулі | ✅ |
| 3 | `ROUTES_AND_ENDPOINTS.md` | Таблиці маршрутів web/admin/api | ✅ |
| 4 | `FLOWS.md` | 7 користувацьких flows з файлами | ✅ |
| 5 | `DATA_MODEL.md` | БД сутності, зв'язки, особливості | ✅ |
| 6 | `SERVICES_AND_INTEGRATIONS.md` | AI інтеграції, cache, queue, storage | ✅ |
| 7 | `DEPLOYMENT.md` | Production requirements, deploy process | ✅ |
| 8 | `SECURITY_AND_RISKS.md` | Auth, ризики, P0/P1/P2 рекомендації | ✅ |
| 9 | `BACKLOG.md` | Техборг, quick wins, prioritization | ✅ |
| 10 | `DECISIONS.md` | Decision log з альтернативами | ✅ |
| 11 | `LOCALIZATION.md` | i18n, lang files, LanguageManager | ✅ |
| 12 | `SEEDERS.md` | Архітектура сідерів тестів та сторінок теорії | ✅ |

### Не створено (не виявлено в проєкті)
- `HORIZON.md` — Laravel Horizon не підключено
- `API_CONTRACT.md` — Мінімальний API, без публічних ресурсів

---

## Найважливіші файли/папки проєкту

| # | Шлях | Призначення |
|---|------|-------------|
| 1 | `routes/web.php` | Публічні веб-маршрути — точка входу для розуміння URL структури |
| 2 | `routes/admin.php` | Адмін маршрути — всі backend операції |
| 3 | `app/Http/Controllers/TestJsV2Controller.php` | Основний контролер тестів — логіка відображення |
| 4 | `app/Services/ChatGPTService.php` | AI інтеграція — всі методи роботи з OpenAI |
| 5 | `app/Services/GeminiService.php` | AI інтеграція — Google Gemini |
| 6 | `app/Models/Question.php` | Модель питання — ядро даних тестів |
| 7 | `app/Models/SavedGrammarTest.php` | Модель тесту — структура збережених тестів |
| 8 | `app/Http/Controllers/WordsTestController.php` | Тренажер слів — session-based логіка |
| 9 | `app/Modules/LanguageManager/Services/LocaleService.php` | Локалізація — управління мовами |
| 10 | `config/services.php` | Конфігурація — API ключі та external services |
| 11 | `database/seeders/QuestionSeeder.php` | Базовий клас сідерів питань |
| 12 | `database/seeders/Pages/Concerns/GrammarPageSeeder.php` | Базовий клас сідерів сторінок теорії |
| 13 | `app/Http/Controllers/SeedRunController.php` | Адмін UI для управління сідерами |
| 14 | `app/Services/QuestionSeedingService.php` | Сервіс збереження питань з сідерів |

---

## Швидкий старт для нового розробника

### 1. Клонувати та встановити
```bash
git clone <repo>
cd engapp-codex
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. База даних
```bash
# Створити БД MySQL: gramlyze
php artisan migrate
php artisan db:seed  # опціонально
```

### 3. Запустити
```bash
npm run dev &
php artisan serve
# Відкрити http://localhost:8000
```

### 4. Налаштувати AI (опціонально)
```env
CHAT_GPT_API_KEY=sk-...
GEMINI_API_KEY=...
```

---

## Ключові концепції для розуміння

| Концепція | Файли | Опис |
|-----------|-------|------|
| Test Modes | `TestJsV2Controller`, `resources/views/tests/` | Step/Card/Input/Select/Manual modes |
| AI Hints | `QuestionHelpController`, `ChatGPTService` | Пояснення помилок, підказки |
| Question Variants | `QuestionVariantService` | Варіації одного питання |
| Site Tree | `SiteTreeController`, `SiteTreeItem` | Структура навігації |
| Seeders | `database/seeders/`, `SEEDERS.md` | Контент management через PHP |
| Locale | `LanguageManager` module | UK/EN/PL підтримка |

---

## Що читати далі

1. **Для backend роботи**: `ARCHITECTURE.md` → `ROUTES_AND_ENDPOINTS.md` → `DATA_MODEL.md`
2. **Для frontend роботи**: `PROJECT_OVERVIEW.md` (Tailwind) → `FLOWS.md` (UI потоки)
3. **Для AI фіч**: `SERVICES_AND_INTEGRATIONS.md` → `FLOWS.md` (AI-підказки)
4. **Для DevOps**: `DEPLOYMENT.md` → `SECURITY_AND_RISKS.md`
5. **Для планування**: `BACKLOG.md` → `DECISIONS.md`
6. **Для роботи з контентом**: `SEEDERS.md` → `DATA_MODEL.md`

---

## Перевірка актуальності

Цей Project Pack згенеровано на основі коду станом на **2025-01**. При значних змінах рекомендується оновити документацію:

- [ ] При додаванні нових моделей — оновити `DATA_MODEL.md`
- [ ] При нових маршрутах — оновити `ROUTES_AND_ENDPOINTS.md`
- [ ] При нових інтеграціях — оновити `SERVICES_AND_INTEGRATIONS.md`
- [ ] При архітектурних рішеннях — оновити `DECISIONS.md`
