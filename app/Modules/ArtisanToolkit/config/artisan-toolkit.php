<?php

return [
    'route_prefix' => 'admin/artisan',

    'middleware' => ['web', 'auth.admin'],

    'commands' => [
        [
            'key' => 'cache-clear',
            'title' => 'Очистити кеш застосунку',
            'description' => 'Виконує php artisan cache:clear та повністю очищає кеш Laravel.',
            'command' => 'cache:clear',
            'button_label' => 'Очистити кеш',
            'success_message' => 'Кеш очищено.',
        ],
        [
            'key' => 'config-clear',
            'title' => 'Скинути кеш конфігурацій',
            'description' => 'Видаляє згенерований кеш конфігів (config:clear).',
            'command' => 'config:clear',
            'button_label' => 'Скинути кеш',
            'success_message' => 'Кеш конфігурацій скинуто.',
        ],
        [
            'key' => 'route-clear',
            'title' => 'Скинути кеш маршрутів',
            'description' => 'Очищає кеш з маршрутизацією (route:clear).',
            'command' => 'route:clear',
            'button_label' => 'Скинути маршрути',
            'success_message' => 'Кеш маршрутів очищено.',
        ],
        [
            'key' => 'view-clear',
            'title' => 'Очистити скомпільовані Blade',
            'description' => 'Видаляє усі скомпільовані шаблони (view:clear).',
            'command' => 'view:clear',
            'button_label' => 'Очистити шаблони',
            'success_message' => 'Скомпільовані Blade-файли видалено.',
        ],
        [
            'key' => 'optimize-clear',
            'title' => 'Скинути усі кеші оптимізації',
            'description' => 'Запускає optimize:clear і прибирає кеши маршрутів, подань, подій та сервісів.',
            'command' => 'optimize:clear',
            'button_label' => 'Скинути оптимізації',
            'success_message' => 'Усі оптимізації очищено.',
        ],
        [
            'key' => 'queue-restart',
            'title' => 'Перезапустити воркери черг',
            'description' => 'Надсилає сигнал queue:restart для плавного перезапуску воркерів.',
            'command' => 'queue:restart',
            'button_label' => 'Перезапустити черги',
            'success_message' => 'Черги отримали сигнал на перезапуск.',
        ],
    ],
];
