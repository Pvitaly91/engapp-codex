<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PendingTestChangeRepository
{
    private const DIRECTORY = 'test-changes';

    public function all(string $slug): array
    {
        return $this->read($slug);
    }

    public function add(string $slug, array $change): array
    {
        $changes = $this->read($slug);

        $change['id'] = $change['id'] ?? (string) Str::uuid();
        $change['created_at'] = $change['created_at'] ?? now()->toIso8601String();

        $changes[] = $change;

        $this->write($slug, $changes);

        return $change;
    }

    public function find(string $slug, string $changeId): ?array
    {
        return collect($this->read($slug))->firstWhere('id', $changeId);
    }

    public function remove(string $slug, string $changeId): void
    {
        $filtered = collect($this->read($slug))
            ->reject(fn ($change) => ($change['id'] ?? null) === $changeId)
            ->values()
            ->all();

        $this->write($slug, $filtered);
    }

    public function replace(string $slug, array $changes): void
    {
        $this->write($slug, $changes);
    }

    private function read(string $slug): array
    {
        $path = $this->path($slug);

        if (! Storage::disk('local')->exists($path)) {
            return [];
        }

        $contents = Storage::disk('local')->get($path);
        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }

    private function write(string $slug, array $changes): void
    {
        $path = $this->path($slug);

        Storage::disk('local')->makeDirectory(self::DIRECTORY);

        Storage::disk('local')->put(
            $path,
            json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function path(string $slug): string
    {
        return self::DIRECTORY . '/' . $slug . '.json';
    }
}
