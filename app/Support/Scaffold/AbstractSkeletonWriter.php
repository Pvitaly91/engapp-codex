<?php

namespace App\Support\Scaffold;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

abstract class AbstractSkeletonWriter
{
    /**
     * @param  array<string, string>  $files
     * @return array<string, mixed>
     */
    protected function writeFiles(array $files, bool $force = false): array
    {
        $existing = array_values(array_filter(array_keys($files), fn (string $path) => File::exists($path)));

        if ($existing !== [] && ! $force) {
            throw new RuntimeException(sprintf(
                'Skeleton files already exist. Re-run with --force to overwrite: %s',
                implode(', ', array_map([$this, 'relativePath'], $existing))
            ));
        }

        foreach ($files as $path => $contents) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $contents);
        }

        return [
            'written' => array_map([$this, 'relativePath'], array_keys($files)),
            'count' => count($files),
        ];
    }

    protected function encodeJson(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($json)) {
            throw new RuntimeException('Unable to encode scaffold JSON payload.');
        }

        return $json . PHP_EOL;
    }

    protected function absolutePath(string $relativePath): string
    {
        return base_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    }

    protected function relativePath(string $absolutePath): string
    {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim(Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
