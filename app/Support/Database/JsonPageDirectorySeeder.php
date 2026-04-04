<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JsonPageDirectorySeeder extends Seeder
{
    protected function shouldRun(): bool
    {
        return true;
    }

    public function run(): void
    {
        foreach ($this->definitionPaths() as $path) {
            $runtimeSeeder = new JsonPageRuntimeSeeder($path);
            $definition = $runtimeSeeder->readDefinition();
            $seederClass = $this->resolveSeederClass($definition, $path);

            if (! $this->shouldRunDefinition($seederClass)) {
                continue;
            }

            $runtimeSeeder = new JsonPageRuntimeSeeder($path, $seederClass);
            $runtimeSeeder->seedFile();
            $this->logDefinitionRun($seederClass);
        }
    }

    protected function definitionDirectory(): string
    {
        return database_path('seeders/Page_V3');
    }

    protected function definitionPaths(): array
    {
        $directory = $this->definitionDirectory();

        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::allFiles($directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'json')
            ->filter(fn ($file) => ! $this->isLocalizationDefinitionPath($file->getPathname()))
            ->filter(fn ($file) => $this->isPageDefinitionPath($file->getPathname()))
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

        if (Str::lower($baseName) === 'definition' && $segments !== []) {
            $className = Str::studly(array_pop($segments));
            $namespacePrefix = 'Database\\Seeders\\Page_V3\\';
        } else {
            $className = Str::studly($baseName);
            $namespacePrefix = 'Database\\Seeders\\Page_V3\\Generated\\';
        }

        if (! Str::endsWith($className, 'Seeder')) {
            $className .= 'Seeder';
        }

        $namespaceSegments = array_map(
            fn (string $segment) => Str::studly(pathinfo($segment, PATHINFO_FILENAME)),
            $segments
        );

        $namespaceSegments[] = $className;

        return $namespacePrefix . implode('\\', $namespaceSegments);
    }

    protected function isLocalizationDefinitionPath(string $path): bool
    {
        return Str::contains(str_replace('\\', '/', $path), '/localizations/');
    }

    protected function isPageDefinitionPath(string $path): bool
    {
        try {
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return false;
        }

        if (! is_array($decoded)) {
            return false;
        }

        $contentType = strtolower(trim((string) data_get($decoded, 'content_type', '')));

        if ($contentType === 'page') {
            return is_array(data_get($decoded, 'page'));
        }

        if ($contentType === 'category') {
            return is_array(data_get($decoded, 'description'));
        }

        return is_array(data_get($decoded, 'page')) || is_array(data_get($decoded, 'description'));
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
