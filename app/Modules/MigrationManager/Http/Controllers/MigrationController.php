<?php

namespace App\Modules\MigrationManager\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MigrationController extends Controller
{
    public function index(Request $request): View
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

        $search = trim((string) $request->query('search', ''));
        $sortField = $request->query('sort', 'batch');
        $direction = $request->query('direction', 'desc');

        $allowedSorts = ['migration', 'batch'];
        if (! in_array($sortField, $allowedSorts, true)) {
            $sortField = 'batch';
        }

        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $executedQuery = DB::table('migrations');

        if ($search !== '') {
            $executedQuery->where('migration', 'like', '%'.$search.'%');
        }

        $executedMigrations = $executedQuery
            ->orderBy($sortField, $direction)
            ->orderBy($sortField === 'migration' ? 'batch' : 'migration')
            ->get();

        $fileManagerAvailable = Route::has('file-manager.index');

        $executedMigrationPaths = collect($migrationFiles)
            ->mapWithKeys(fn ($path, $migration) => [$migration => [
                'file' => $this->toRelativePath($path),
                'directory' => $this->toRelativeDirectory($path),
            ]])
            ->all();

        $feedback = session('migrations');

        return view('migration-manager::index', [
            'pendingMigrations' => $pendingMigrations,
            'lastBatch' => $lastBatchMigrations,
            'feedback' => $feedback,
            'executedMigrations' => $executedMigrations,
            'search' => $search,
            'sortField' => $sortField,
            'sortDirection' => $direction,
            'fileManagerAvailable' => $fileManagerAvailable,
            'executedMigrationPaths' => $executedMigrationPaths,
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

    public function destroy(string $migration): RedirectResponse
    {
        try {
            $deleted = DB::table('migrations')
                ->where('migration', $migration)
                ->delete();

            if ($deleted === 0) {
                return $this->redirectWithFeedback('error', 'Запис міграції не знайдено.', '');
            }

            return $this->redirectWithFeedback('success', 'Запис про міграцію видалено.', '');
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

    private function toRelativePath(string $absolutePath): string
    {
        $normalized = str_replace('\\', '/', $absolutePath);
        $base = str_replace('\\', '/', base_path()).'/';

        if (! Str::startsWith($normalized, $base)) {
            return ltrim($normalized, '/');
        }

        return ltrim(Str::after($normalized, $base), '/');
    }

    private function toRelativeDirectory(string $absolutePath): string
    {
        $relative = $this->toRelativePath($absolutePath);
        if ($relative === '') {
            return '';
        }
        $directory = str_replace('\\', '/', dirname($relative));

        if ($directory === '.' || $directory === DIRECTORY_SEPARATOR) {
            return '';
        }

        return ltrim($directory, '/');
    }
}
