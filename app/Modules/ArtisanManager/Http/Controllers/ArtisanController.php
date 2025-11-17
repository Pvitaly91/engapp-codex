<?php

namespace App\Modules\ArtisanManager\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class ArtisanController extends Controller
{
    public function index(): View
    {
        $feedback = session('artisan');

        return view('artisan-manager::index', [
            'feedback' => $feedback,
        ]);
    }

    public function cacheClear(): RedirectResponse
    {
        return $this->runCommand('cache:clear', 'Кеш успішно очищено.');
    }

    public function configClear(): RedirectResponse
    {
        return $this->runCommand('config:clear', 'Кеш конфігурації очищено.');
    }

    public function routeClear(): RedirectResponse
    {
        return $this->runCommand('route:clear', 'Кеш маршрутів очищено.');
    }

    public function viewClear(): RedirectResponse
    {
        return $this->runCommand('view:clear', 'Скомпільовані шаблони очищено.');
    }

    public function optimizeClear(): RedirectResponse
    {
        return $this->runCommand('optimize:clear', 'Усі кеші очищено (config, route, view, compiled).');
    }

    public function optimize(): RedirectResponse
    {
        return $this->runCommand('optimize', 'Застосунок оптимізовано (config:cache, route:cache).');
    }

    public function configCache(): RedirectResponse
    {
        return $this->runCommand('config:cache', 'Конфігурація закешована.');
    }

    public function routeCache(): RedirectResponse
    {
        return $this->runCommand('route:cache', 'Маршрути закешовані.');
    }

    public function viewCache(): RedirectResponse
    {
        return $this->runCommand('view:cache', 'Шаблони прекомпільовані.');
    }

    public function eventClear(): RedirectResponse
    {
        return $this->runCommand('event:clear', 'Кеш подій очищено.');
    }

    public function eventCache(): RedirectResponse
    {
        return $this->runCommand('event:cache', 'Події закешовані.');
    }

    public function storageLinkCreate(): RedirectResponse
    {
        return $this->runCommand('storage:link', 'Символічне посилання на storage створено.');
    }

    private function runCommand(string $command, string $successMessage): RedirectResponse
    {
        try {
            $exitCode = Artisan::call($command);
            $output = trim(Artisan::output());

            $status = $exitCode === 0 ? 'success' : 'error';
            $message = $status === 'success' ? $successMessage : "Команда '{$command}' завершилася з помилкою.";

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
