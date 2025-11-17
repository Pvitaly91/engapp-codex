<?php

namespace App\Modules\ArtisanToolkit\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Throwable;

class ArtisanCommandController extends Controller
{
    public function index(Request $request): Renderable
    {
        return view('artisan-toolkit::index', [
            'commands' => $this->commands(),
            'feedback' => $request->session()->get('artisan-toolkit.feedback'),
        ]);
    }

    public function run(Request $request, string $commandKey): RedirectResponse
    {
        $command = $this->commands()->firstWhere('key', $commandKey);

        if (! $command) {
            return redirect()
                ->route('artisan-toolkit.index')
                ->with('artisan-toolkit.feedback', [
                    'status' => 'error',
                    'message' => 'Невідома команда або вона вимкнена.',
                ]);
        }

        $feedback = $this->executeCommand($command);

        return redirect()
            ->route('artisan-toolkit.index')
            ->with('artisan-toolkit.feedback', $feedback);
    }

    protected function commands(): Collection
    {
        return collect(config('artisan-toolkit.commands', []))
            ->filter(fn (array $command) => ! ($command['hidden'] ?? false))
            ->map(function (array $command) {
                $command['key'] ??= Str::slug($command['command'], '-');
                $command['button_label'] ??= 'Запустити';

                return $command;
            });
    }

    protected function executeCommand(array $command): array
    {
        try {
            $exitCode = Artisan::call($command['command'], $command['options'] ?? []);
            $output = trim(Artisan::output());
            $status = $exitCode === 0 ? 'success' : 'error';

            return [
                'status' => $status,
                'message' => $command[$status === 'success' ? 'success_message' : 'error_message']
                    ?? ($status === 'success' ? 'Команду виконано успішно.' : 'Команда завершилась з помилкою.'),
                'output' => $output,
                'exitCode' => $exitCode,
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'error',
                'message' => $command['error_message'] ?? 'Сталася помилка під час виконання команди.',
                'output' => $exception->getMessage(),
                'exitCode' => null,
            ];
        }
    }
}
