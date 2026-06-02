# DEPLOYMENT.md — Деплоймент та Production

## Production Requirements

### Системні вимоги
| Компонент | Мінімум | Рекомендовано |
|-----------|---------|---------------|
| PHP | 8.1 | 8.2+ |
| MySQL | 8.0 | 8.0+ |
| Node.js | 18 | 20+ (для build) |
| RAM | 1 GB | 2+ GB |
| Disk | 5 GB | 10+ GB |

### PHP Extensions
```
bcmath, ctype, curl, dom, fileinfo, gd, json,
mbstring, openssl, pdo, pdo_mysql, tokenizer, xml
```

### Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

---

## Environment Configuration

### Обов'язкові змінні
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gramlyze.com
APP_KEY=base64:... # php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=gramlyze
DB_USERNAME=your-user
DB_PASSWORD=your-password

# AI Services (опціонально)
CHAT_GPT_API_KEY=sk-...
GEMINI_API_KEY=...
```

### Рекомендовані production налаштування
```env
# Performance
CACHE_DRIVER=file  # або redis
SESSION_DRIVER=file  # або redis
QUEUE_CONNECTION=sync  # або database/redis

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

---

## Queue Worker

### Якщо використовується async queue

```bash
# Systemd service для queue worker
# /etc/systemd/system/gramlyze-queue.service

[Unit]
Description=Gramlyze Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/gramlyze
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable gramlyze-queue
sudo systemctl start gramlyze-queue
```

**Примітка**: В поточній конфігурації використовується `sync` queue — worker не потрібен.

---

## Scheduler (Cron)

### Laravel Scheduler
```bash
# Crontab entry
* * * * * cd /var/www/gramlyze && php artisan schedule:run >> /dev/null 2>&1
```

**Примітка**: В поточній версії немає scheduled tasks в `routes/console.php`.

---

## Cache Configuration

### Кешування конфігів (Production)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Очищення кешу
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Redis (якщо потрібно)
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

---

## Storage Configuration

### Symlink для public storage
```bash
php artisan storage:link
# Створює symlink: public/storage -> storage/app/public
```

### Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Horizon

**Статус**: Не підключено в проєкті.

Якщо потрібно додати:
```bash
composer require laravel/horizon
php artisan horizon:install
```

---

## Deployment Process

### Manual Deployment
```bash
# 1. Pull changes
cd /var/www/gramlyze
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Build frontend assets
npm ci
npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue (if using)
sudo systemctl restart gramlyze-queue

# 7. Reload PHP-FPM (zero-downtime)
sudo systemctl reload php8.2-fpm
```

### Zero-Downtime Considerations

1. **Migrations**: Уникайте destructive migrations в production
   - Не використовуйте `dropColumn`, `dropTable` без попередження
   - Робіть additive migrations

2. **Config Cache**: Оновлюйте після зміни .env
   ```bash
   php artisan config:cache
   ```

3. **Opcache Reset**: Якщо використовується opcache
   ```bash
   sudo service php8.2-fpm reload
   ```

4. **Assets**: Vite build з версіонуванням
   ```bash
   npm run build
   # Генерує manifest.json з hashed filenames
   ```

---

## Healthchecks

### Endpoint для monitoring
Можна додати в `routes/web.php`:
```php
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ok']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error'], 500);
    }
});
```

### Перевірка компонентів
```bash
# Database
php artisan db:show

# Cache
php artisan cache:clear && echo "Cache OK"

# Queue
php artisan queue:monitor default

# Storage
ls -la storage/app/public
```

---

## Логування

### Шлях до логів
```
storage/logs/laravel.log
storage/logs/laravel-2024-01-01.log  # daily driver
```

### Конфігурація (`config/logging.php`)
```php
'default' => env('LOG_CHANNEL', 'stack'),

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'days' => 14,
    ],
],
```

### Рекомендація для production
```env
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

### Перегляд логів
```bash
# Останні записи
tail -f storage/logs/laravel.log

# Пошук помилок
grep -i "error\|exception" storage/logs/laravel.log
```

---

## Nginx Configuration (приклад)

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name gramlyze.com www.gramlyze.com;

    root /var/www/gramlyze/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/gramlyze.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/gramlyze.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets caching
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## Backup Strategy

### Database
```bash
# Daily backup
mysqldump -u user -p gramlyze > backup-$(date +%Y%m%d).sql
```

### Files
```bash
# Storage backup
tar -czf storage-backup-$(date +%Y%m%d).tar.gz storage/app
```
