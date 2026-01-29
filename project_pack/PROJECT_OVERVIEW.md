# PROJECT_OVERVIEW.md — Gramlyze (Engapp)

## Що це за система

**Gramlyze** — платформа для практики англійської мови. Основні функції:
- Каталог граматичних тестів із різними режимами (вибір варіанту, введення, покроковий)
- Сторінки теорії з граматичними правилами та прикладами
- Тренажер слів (Words Trainer) — практика перекладу слів
- Тренажер неправильних дієслів (Verbs Trainer) — форми дієслів (base, past, participle)
- Глобальний пошук — пошук по теорії та тестах
- AI-підказки (ChatGPT/Gemini) — пояснення помилок, підказки щодо граматичних конструкцій
- Світла/темна тема
- Локалізація (UK/EN/PL)

**Production URL**: https://gramlyze.com

---

## Стек технологій

| Категорія | Технологія | Версія |
|-----------|------------|--------|
| Backend Framework | Laravel | ^10.10 |
| PHP | PHP | ^8.1 |
| Frontend Views | Blade + Livewire | 3.0 |
| CSS Framework | Tailwind CSS | ^4.1.11 |
| Build Tool | Vite | ^5.0.0 |
| AI Integration | OpenAI PHP SDK | ^0.11.0 |
| AI (Gemini) | HTTP client (Guzzle) | ^7.2 |
| Database | MySQL | (via env) |
| Auth | Laravel Sanctum | ^3.3 |
| Queue | Sync/Database/Redis | Laravel default |
| Cache | File/Redis | Laravel default |

---

## Як запустити локально

### Передумови
- PHP 8.1+
- Composer 2.x
- Node.js 18+ та npm
- MySQL 8.0+

### Крок за кроком

```bash
# 1. Клонування репозиторію
git clone <repo-url>
cd engapp-codex

# 2. Встановлення PHP залежностей
composer install

# 3. Копіювання та налаштування оточення
cp .env.example .env
# Редагуйте .env — задайте DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Додайте API ключі: CHAT_GPT_API_KEY, GEMINI_API_KEY (опціонально)

# 4. Генерація ключа додатка
php artisan key:generate

# 5. Створення БД та міграції
php artisan migrate

# 6. Запуск сідерів (опціонально)
php artisan db:seed

# 7. Лінкування storage
php artisan storage:link

# 8. Встановлення npm залежностей
npm install

# 9. Збірка фронтенду (development)
npm run dev

# 10. Запуск локального сервера
php artisan serve
# Відкрийте http://localhost:8000
```

---

## Основні artisan-команди

| Команда | Призначення |
|---------|-------------|
| `php artisan migrate` | Застосувати міграції БД |
| `php artisan migrate:fresh` | Скинути та перестворити БД |
| `php artisan db:seed` | Запустити всі сідери |
| `php artisan db:seed --class=ClassName` | Запустити конкретний сідер |
| `php artisan tinker` | Інтерактивна консоль |
| `php artisan test` | Запустити PHPUnit тести |
| `php artisan route:list` | Показати всі маршрути |
| `php artisan cache:clear` | Очистити кеш |
| `php artisan config:cache` | Кешувати конфіг (production) |
| `php artisan queue:work` | Запустити обробник черги |

---

## Структура проєкту — ключові файли та папки

```
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Web контролери
│   │   ├── Middleware/            # Middleware (auth, locale)
│   │   ├── Livewire/              # Livewire компоненти
│   │   └── Resources/             # API Resources
│   ├── Models/                    # Eloquent моделі
│   ├── Modules/                   # Модульна архітектура
│   │   ├── LanguageManager/       # Управління мовами
│   │   ├── PageManager/           # Управління сторінками
│   │   ├── GitDeployment/         # GitHub деплоймент
│   │   └── ...
│   ├── Services/                  # Бізнес-логіка
│   │   ├── ChatGPTService.php     # OpenAI інтеграція
│   │   ├── GeminiService.php      # Google Gemini інтеграція
│   │   └── ...
│   └── Providers/                 # Service Providers
├── config/
│   ├── services.php               # Конфіг зовнішніх сервісів
│   ├── database.php               # Конфіг БД
│   └── ...
├── database/
│   ├── migrations/                # Міграції БД
│   └── seeders/                   # Сідери даних
├── resources/
│   ├── views/                     # Blade шаблони
│   ├── lang/                      # Локалізація (uk, en, pl)
│   ├── css/                       # Стилі
│   └── js/                        # JavaScript
├── routes/
│   ├── web.php                    # Публічні web маршрути
│   ├── admin.php                  # Адмін маршрути
│   ├── api.php                    # API маршрути
│   └── console.php                # Artisan команди
├── composer.json                  # PHP залежності
├── package.json                   # npm залежності
├── tailwind.config.js             # Tailwind CSS конфіг
└── vite.config.js                 # Vite конфіг
```

---

## Посилання на ключові файли

- **Залежності**: `composer.json`, `package.json`
- **Маршрути**: `routes/web.php`, `routes/admin.php`, `routes/api.php`
- **Моделі**: `app/Models/`
- **Сервіси**: `app/Services/`
- **Контролери**: `app/Http/Controllers/`
- **Конфіги**: `config/services.php`, `config/database.php`, `config/cache.php`
- **Міграції**: `database/migrations/`
- **Локалізація**: `resources/lang/uk/`, `resources/lang/en/`, `resources/lang/pl/`
