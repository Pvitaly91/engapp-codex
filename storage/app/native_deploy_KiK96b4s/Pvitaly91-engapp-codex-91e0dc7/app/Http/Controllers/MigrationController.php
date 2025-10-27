<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrationController extends Controller
{
    public function index(): View
    {
        $migrator = app('migrator');

        if (! $migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }

        $paths = $migrator->paths();
        $paths[] = database_path('migrations');
        $paths = array_unique($paths);

        $migrationFiles = $migrator->getMigrationFiles($paths);
        $ran = $migrator->getRepository()->getRan();

        $pendingMigrations = collect(array_diff(array_keys($migrationFiles), $ran))
            ->map(fn ($migration) => [
                'name' => $migration,
                'path' => $migrationFiles[$migration],
            ])
            ->values();

        $lastBatchNumber = DB::table('migrations')->max('batch');
        $lastBatchMigrations = collect();

        if ($lastBatchNumber) {
            $lastBatchMigrations = DB::table('migrations')
                ->where('batch', $lastBatchNumber)
                ->orderBy('migration')
                ->get();
        }

        $feedback = session('migrations');

        return view('migrations.index', [
            'pendingMigrations' => $pendingMigrations,
            'lastBatch' => $lastBatchMigrations,
            'feedback' => $feedback,
        ]);
    }

    public function run(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            $status = $exitCode === 0 ? 'success' : 'error';
            $message = $status === 'success'
                ? 'Усі доступні міграції виконано.'
                : 'Команда migrate завершилася з помилкою.';

            return $this->redirectWithFeedback($status, $message, $output);
        } catch (\Throwable $exception) {
            return $this->redirectWithFeedback('error', $exception->getMessage(), '');
        }
    }

    public function rollback(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]);
            $output = trim(Artisan::output());

            $status = $exitCode === 0 ? 'success' : 'error';
            $message = $status === 'success'
                ? 'Останню партію міграцій скасовано.'
                : 'Команда rollback завершилася з помилкою.';

            return $this->redirectWithFeedback($status, $message, $output);
        } catch (\Throwable $exception) {
            return $this->redirectWithFeedback('error', $exception->getMessage(), '');
        }
    }

    private function redirectWithFeedback(string $status, string $message, string $output): RedirectResponse
    {
        return redirect()
            ->route('migrations.index')
            ->with('migrations', [
                'status' => $status,
                'message' => $message,
                'output' => $output,
            ]);
    }
}
