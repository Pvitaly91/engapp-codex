<?php

namespace App\Modules\ArtisanManager\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class ArtisanManagerController extends Controller
{
    private array $commands = [
        'cache_clear' => [
            'title' => 'Очистити кеш додатку',
            'description' => 'Виконує команду <code>php artisan cache:clear</code>. Очищає весь кеш додатку.',
            'artisan_command' => 'cache:clear',
            'icon' => 'fa-broom',
            'button_class' => 'bg-primary',
        ],
        'config_clear' => [
            'title' => 'Очистити кеш конфігурації',
            'description' => 'Виконує команду <code>php artisan config:clear</code>. Видаляє закешовані файли конфігурації.',
            'artisan_command' => 'config:clear',
            'icon' => 'fa-file-lines',
            'button_class' => 'bg-info',
        ],
        'route_clear' => [
            'title' => 'Очистити кеш маршрутів',
            'description' => 'Виконує команду <code>php artisan route:clear</code>. Видаляє закешовані маршруті.',
            'artisan_command' => 'route:clear',
            'icon' => 'fa-map-signs',
            'button_class' => 'bg-info',
        ],
        'view_clear' => [
            'title' => 'Очистити кеш шаблонів',
            'description' => 'Виконує команду <code>php artisan view:clear</code>. Видаляє скомпільовані Blade шаблони.',
            'artisan_command' => 'view:clear',
            'icon' => 'fa-eye',
            'button_class' => 'bg-info',
        ],
        'clear_compiled' => [
            'title' => 'Видалити скомпільовані класи',
            'description' => 'Виконує команду <code>php artisan clear-compiled</code>. Видаляє файл скомпільованих класів.',
            'artisan_command' => 'clear-compiled',
            'icon' => 'fa-file-code',
            'button_class' => 'bg-warning',
        ],
        'optimize' => [
            'title' => 'Оптимізувати додаток',
            'description' => 'Виконує команду <code>php artisan optimize</code>. Кешує конфігурацію, маршрути та завантаження класів для покращення продуктивності.',
            'artisan_command' => 'optimize',
            'icon' => 'fa-rocket',
            'button_class' => 'bg-success',
        ],
        'optimize_clear' => [
            'title' => 'Скасувати оптимізацію',
            'description' => 'Виконує команду <code>php artisan optimize:clear</code>. Видаляє всі закешовані файли оптимізації.',
            'artisan_command' => 'optimize:clear',
            'icon' => 'fa-rotate-left',
            'button_class' => 'bg-warning',
        ],
        'storage_link' => [
            'title' => 'Створити символічне посилання',
            'description' => 'Виконує команду <code>php artisan storage:link</code>. Створює символічне посилання з public/storage на storage/app/public.',
            'artisan_command' => 'storage:link',
            'icon' => 'fa-link',
            'button_class' => 'bg-secondary',
        ],
        'queue_restart' => [
            'title' => 'Перезапустити черги',
            'description' => 'Виконує команду <code>php artisan queue:restart</code>. Перезапускає всіх воркерів черг після завершення поточного завдання.',
            'artisan_command' => 'queue:restart',
            'icon' => 'fa-list',
            'button_class' => 'bg-secondary',
        ],
    ];

    public function index(): View
    {
        $enabledCommands = config('artisan-manager.enabled_commands', array_keys($this->commands));
        $availableCommands = array_filter(
            $this->commands,
            fn ($key) => in_array($key, $enabledCommands, true),
            ARRAY_FILTER_USE_KEY
        );

        $feedback = session('artisan');

        return view('artisan-manager::index', [
            'commands' => $availableCommands,
            'feedback' => $feedback,
        ]);
    }

    public function execute(string $command): RedirectResponse
    {
        $enabledCommands = config('artisan-manager.enabled_commands', array_keys($this->commands));

        if (! in_array($command, $enabledCommands, true) || ! isset($this->commands[$command])) {
            return $this->redirectWithFeedback('error', 'Команда не дозволена або не знайдена.', '');
        }

        $commandData = $this->commands[$command];

        try {
            $exitCode = Artisan::call($commandData['artisan_command']);
            $output = trim(Artisan::output());

            $status = $exitCode === 0 ? 'success' : 'error';
            $message = $status === 'success'
                ? "Команда '{$commandData['artisan_command']}' успішно виконана."
                : "Команда '{$commandData['artisan_command']}' завершилася з помилкою.";

            return $this->redirectWithFeedback($status, $message, $output);
        } catch (\Throwable $exception) {
            return $this->redirectWithFeedback('error', $exception->getMessage(), '');
        }
    }

    private function redirectWithFeedback(string $status, string $message, string $output): RedirectResponse
    {
        return redirect()
            ->route('artisan.index')
            ->with('artisan', [
                'status' => $status,
                'message' => $message,
                'output' => $output,
            ]);
    }
}
