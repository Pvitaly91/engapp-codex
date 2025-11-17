<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for all artisan commands module routes.
    |
    */
    'route_prefix' => 'admin/artisan-commands',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware to apply to all routes in this module.
    |
    */
    'middleware' => ['web', 'auth.admin'],

    /*
    |--------------------------------------------------------------------------
    | Available Commands
    |--------------------------------------------------------------------------
    |
    | List of available artisan commands with their configurations.
    | Each command can have: title, description, command, confirmation_required
    |
    */
    'commands' => [
        'cache' => [
            [
                'key' => 'cache_clear',
                'title' => 'Очистити кеш додатку',
                'description' => 'Видаляє всі кешовані дані додатку',
                'command' => 'cache:clear',
                'icon' => 'fa-trash-can',
                'color' => 'red',
                'confirmation_required' => false,
            ],
            [
                'key' => 'config_clear',
                'title' => 'Очистити кеш конфігурації',
                'description' => 'Видаляє кешовану конфігурацію',
                'command' => 'config:clear',
                'icon' => 'fa-file-circle-xmark',
                'color' => 'red',
                'confirmation_required' => false,
            ],
            [
                'key' => 'route_clear',
                'title' => 'Очистити кеш маршрутів',
                'description' => 'Видаляє кешовані маршрути',
                'command' => 'route:clear',
                'icon' => 'fa-road',
                'color' => 'red',
                'confirmation_required' => false,
            ],
            [
                'key' => 'view_clear',
                'title' => 'Очистити кеш шаблонів',
                'description' => 'Видаляє скомпільовані Blade-шаблони',
                'command' => 'view:clear',
                'icon' => 'fa-eye-slash',
                'color' => 'red',
                'confirmation_required' => false,
            ],
            [
                'key' => 'clear_all',
                'title' => 'Очистити весь кеш',
                'description' => 'Виконує всі команди очистки кешу',
                'command' => 'optimize:clear',
                'icon' => 'fa-broom',
                'color' => 'red',
                'confirmation_required' => true,
            ],
        ],
        'optimization' => [
            [
                'key' => 'config_cache',
                'title' => 'Кешувати конфігурацію',
                'description' => 'Створює кеш конфігурації для швидкої роботи',
                'command' => 'config:cache',
                'icon' => 'fa-file-circle-check',
                'color' => 'green',
                'confirmation_required' => false,
            ],
            [
                'key' => 'route_cache',
                'title' => 'Кешувати маршрути',
                'description' => 'Створює кеш маршрутів для швидкої роботи',
                'command' => 'route:cache',
                'icon' => 'fa-road',
                'color' => 'green',
                'confirmation_required' => false,
            ],
            [
                'key' => 'view_cache',
                'title' => 'Кешувати шаблони',
                'description' => 'Компілює Blade-шаблони',
                'command' => 'view:cache',
                'icon' => 'fa-eye',
                'color' => 'green',
                'confirmation_required' => false,
            ],
            [
                'key' => 'optimize',
                'title' => 'Оптимізувати додаток',
                'description' => 'Виконує всі оптимізаційні команди',
                'command' => 'optimize',
                'icon' => 'fa-gauge-high',
                'color' => 'green',
                'confirmation_required' => false,
            ],
        ],
        'maintenance' => [
            [
                'key' => 'storage_link',
                'title' => 'Створити symbolic link для storage',
                'description' => 'Створює symbolic link з public/storage на storage/app/public',
                'command' => 'storage:link',
                'icon' => 'fa-link',
                'color' => 'blue',
                'confirmation_required' => false,
            ],
            [
                'key' => 'queue_restart',
                'title' => 'Перезапустити черги',
                'description' => 'Перезапускає worker\'и черг після зміни коду',
                'command' => 'queue:restart',
                'icon' => 'fa-rotate',
                'color' => 'yellow',
                'confirmation_required' => false,
            ],
        ],
    ],
];
