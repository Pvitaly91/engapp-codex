# DECISIONS.md — Decision Log

## Архітектурні рішення

### D001: Laravel як основний фреймворк
**Дата**: Початок проєкту  
**Що**: Вибір Laravel 10 як backend framework  
**Чому**:
- Швидка розробка з Eloquent ORM
- Вбудована система аутентифікації
- Blade templating для серверного рендерингу
- Велика екосистема пакетів
- Українськомовне ком'юніті

**Альтернативи**:
- Symfony — надто verbose для MVP
- Node.js + Express — менш зручний ORM
- Django — Python не в стеку команди

---

### D002: Livewire для інтерактивності
**Дата**: ~2024  
**Що**: Livewire 3.0 замість SPA framework  
**Чому**:
- Інтеграція з Blade без окремого API
- Менше JavaScript коду
- Server-side rendering для SEO
- Простіший deployment

**Альтернативи**:
- Vue.js + Inertia — додаткова складність
- React SPA — потребує окремий API
- Alpine.js only — недостатньо для складних компонентів

**Компроміси**: Обмежена офлайн-функціональність

---

### D003: Tailwind CSS для стилізації
**Дата**: ~2024  
**Що**: Tailwind CSS замість Bootstrap/custom CSS  
**Чому**:
- Utility-first підхід для швидкої розробки
- Консистентний дизайн через дизайн-токени
- Оптимізація bundle size через purging
- Dark mode з мінімальними зусиллями

**Альтернативи**:
- Bootstrap — більш opinionated
- Material UI — React-орієнтований
- Custom CSS — повільніше, менш консистентно

---

### D004: MySQL як база даних
**Дата**: Початок проєкту  
**Що**: MySQL замість PostgreSQL  
**Чому**:
- Широка підтримка хостингів
- Простіший setup
- Достатній функціонал для потреб проєкту

**Альтернативи**:
- PostgreSQL — більш потужний, але складніший
- SQLite — для dev only
- MongoDB — не підходить для relational data

---

### D005: OpenAI + Gemini для AI підказок
**Дата**: ~2024  
**Що**: Дуальна інтеграція AI провайдерів  
**Чому**:
- Резервування на випадок downtime
- Порівняння якості відповідей
- Гнучкість у виборі провайдера
- Різна вартість/якість для різних задач

**Альтернативи**:
- Тільки OpenAI — single point of failure
- Anthropic Claude — на момент вибору менш доступний
- Self-hosted LLM — занадто дорого для старту

**Компроміси**: Подвійна підтримка коду

---

### D006: LIKE-based пошук замість Full-text
**Дата**: ~2024  
**Що**: MySQL LIKE замість Meilisearch/Algolia  
**Чому**:
- Простота імплементації
- Невелика кількість контенту
- Немає потреби в fuzzy search
- Менше інфраструктури

**Альтернативи**:
- Meilisearch — потребує окремий сервіс
- Algolia — платний, vendor lock-in
- Elasticsearch — занадто потужний для потреб

**Техборг**: Переглянути при зростанні контенту

---

### D007: Session-based стан для тренажерів
**Дата**: ~2024  
**Що**: Збереження прогресу в session замість DB  
**Чому**:
- Простота для anonymous users
- Немає потреби в аккаунтах
- Швидкий старт без реєстрації

**Альтернативи**:
- User accounts + DB storage — складніше, але persistent
- localStorage — client-only, не cross-device

**Компроміси**: Прогрес втрачається при logout/session expire

---

### D008: Модульна архітектура (Modules)
**Дата**: ~2024  
**Що**: Використання `app/Modules/` для ізольованих фіч  
**Чому**:
- Кращий code organization
- Легше тестувати окремо
- Можливість вимкнути модуль
- Підготовка до потенційного розділення

**Структура**:
```
app/Modules/
├── LanguageManager/     # i18n управління
├── PageManager/         # CMS функціонал
├── GitDeployment/       # Deployment integration
└── ...
```

**Альтернативи**:
- Monolithic app/ structure — простіше для малих проєктів
- Laravel Modules package — додаткова залежність

---

### D009: Кешування AI відповідей в БД
**Дата**: ~2024  
**Що**: Окремі таблиці для кешу AI замість Cache facade  
**Чому**:
- Персистентність при cache:clear
- Можливість аналізу/review
- Редагування кешованих відповідей
- Пошук по кешу

**Таблиці**:
- `chatgpt_explanations`
- `chatgpt_translation_checks`
- `question_hints`

**Альтернативи**:
- Redis cache — втрата при перезавантаженні
- File cache — складніший пошук
- In-memory only — занадто дорого

---

### D010: Multiple test modes (step/card/input/select)
**Дата**: ~2024  
**Що**: Різні UI modes для тестів  
**Чому**:
- Різні стилі навчання
- A/B тестування ефективності
- Адаптація до типу контенту

**Modes**:
- Card mode — всі питання одразу
- Step mode — одне питання за раз
- Input — ручне введення
- Select — вибір з варіантів
- Manual — самоперевірка

**Альтернативи**:
- Один універсальний mode — менш гнучко
- Повністю configurable — занадто складно

---

### D011: UUID для питань
**Дата**: ~2024  
**Що**: UUID як ідентифікатор питань замість auto-increment ID  
**Чому**:
- Унікальність при імпорті/експорті
- Можливість merge даних з різних джерел
- Безпека — не можна вгадати наступний ID
- Стабільні посилання

**Альтернативи**:
- Auto-increment only — проблеми при sync
- ULID — менш поширений

---

### D012: Seeder-based контент management
**Дата**: ~2024  
**Що**: PHP seeders для управління контентом замість CMS  
**Чому**:
- Версіонування контенту в Git
- Code review для змін
- Легкий rollback
- Batch import

**Альтернативи**:
- Full CMS (Nova, Filament) — overhead
- JSON files — менш flexible
- External CMS — додаткова інфраструктура

**Компроміси**: Технічний бар'єр для non-devs

---

## Відкриті питання

| Question | Context | Status |
|----------|---------|--------|
| Чи потрібна user registration? | Персоналізація, збереження прогресу | Pending |
| Чи розглядати mobile app? | React Native / Flutter | Pending |
| Чи потрібен premium tier? | Монетизація | Pending |
| Чи масштабувати на інші мови (DE, FR)? | i18n scope | Pending |
