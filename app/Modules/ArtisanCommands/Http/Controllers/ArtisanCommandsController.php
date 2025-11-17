<?php

namespace App\Modules\ArtisanCommands\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ArtisanCommandsController extends Controller
{
    /**
     * Display the artisan commands interface.
     */
    public function index(): View
    {
        $commands = config('artisan-commands.commands', []);
        
        return view('artisan-commands::index', compact('commands'));
    }

    /**
     * Execute an artisan command.
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $commandKey = $request->input('command');
        $command = $this->findCommand($commandKey);

        if (!$command) {
            return response()->json([
                'success' => false,
                'message' => 'Команду не знайдено',
            ], 404);
        }

        try {
            // Capture output
            $exitCode = Artisan::call($command['command']);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => $exitCode === 0 
                    ? 'Команду успішно виконано' 
                    : 'Помилка виконання команди',
                'output' => $output,
                'exit_code' => $exitCode,
                'command' => $command['command'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка виконання: ' . $e->getMessage(),
                'output' => '',
                'exit_code' => 1,
            ], 500);
        }
    }

    /**
     * Find a command by its key.
     */
    private function findCommand(string $key): ?array
    {
        $allCommands = config('artisan-commands.commands', []);
        
        foreach ($allCommands as $category => $commands) {
            foreach ($commands as $command) {
                if ($command['key'] === $key) {
                    return $command;
                }
            }
        }
        
        return null;
    }
}
