<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JsonTestDirectorySeeder extends Seeder
{
    protected function shouldRun(): bool
    {
        return true;
    }

    public function run(): void
    {
        foreach ($this->definitionPaths() as $path) {
            $runtimeSeeder = new JsonRuntimeSeeder($path);
            $definition = $runtimeSeeder->readDefinition();
            $seederClass = $this->resolveSeederClass($definition, $path);

            if (! $this->shouldRunDefinition($seederClass)) {
                continue;
            }

            $runtimeSeeder = new JsonRuntimeSeeder($path, $seederClass);
            $runtimeSeeder->seedFile();
            $this->logDefinitionRun($seederClass);
        }
    }

    protected function definitionDirectory(): string
    {
        return database_path('seeders/V3/definitions');
    }

    protected function definitionPaths(): array
    {
        $directory = $this->definitionDirectory();

        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::allFiles($directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'json')
            ->sortBy(fn ($file) => str_replace('\\', '/', $file->getPathname()))
            ->map(fn ($file) => $file->getPathname())
            ->values()
            ->all();
    }

    protected function resolveSeederClass(array $definition, string $path): string
    {
        $configured = trim((string) data_get($definition, 'seeder.class', ''));

        if ($configured !== '') {
            return $configured;
        }

        return $this->deriveSeederClassFromPath($path);
    }

    protected function deriveSeederClassFromPath(string $path): string
    {
        $directory = rtrim(str_replace('\\', '/', $this->definitionDirectory()), '/');
        $normalizedPath = str_replace('\\', '/', $path);
        $relativePath = Str::after($normalizedPath, $directory . '/');
        $segments = array_values(array_filter(explode('/', $relativePath), 'strlen'));
        $fileName = array_pop($segments) ?: 'generated';
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $className = Str::studly($baseName);

        if (! Str::endsWith($className, 'Seeder')) {
            $className .= 'Seeder';
        }

        $namespaceSegments = array_map(
            fn (string $segment) => Str::studly(pathinfo($segment, PATHINFO_FILENAME)),
            $segments
        );

        $namespaceSegments[] = $className;

        return 'Database\\Seeders\\V3\\Generated\\' . implode('\\', $namespaceSegments);
    }

    protected function shouldRunDefinition(string $className): bool
    {
        if (! Schema::hasTable('seed_runs')) {
            return true;
        }

        return ! DB::table('seed_runs')
            ->where('class_name', $className)
            ->exists();
    }

    protected function logDefinitionRun(string $className): void
    {
        if (! Schema::hasTable('seed_runs')) {
            return;
        }

        DB::table('seed_runs')->updateOrInsert(
            ['class_name' => $className],
            ['ran_at' => now()]
        );
    }
}
