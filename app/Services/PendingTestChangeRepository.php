<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PendingTestChangeRepository
{
    private const DIRECTORY = 'changes';
    private const GLOBAL_FILENAME = 'global.json';

    public function all(string $slug): array
    {
        $questionChanges = collect($this->groupedByQuestion($slug))->flatMap(fn (array $changes) => $changes);
        $globalChanges = collect($this->allForQuestion($slug, null));

        return $questionChanges->merge($globalChanges)->values()->all();
    }

    public function groupedByQuestion(string $slug): array
    {
        $result = [];

        foreach ($this->listQuestionFiles($slug) as $questionId => $path) {
            $changes = $this->readFromPath($path);

            if (! empty($changes)) {
                $result[$questionId] = $changes;
            }
        }

        ksort($result);

        return $result;
    }

    public function allForQuestion(string $slug, ?int $questionId): array
    {
        return $this->readBucket($slug, $questionId);
    }

    public function count(string $slug): int
    {
        $total = count($this->allForQuestion($slug, null));

        foreach ($this->groupedByQuestion($slug) as $changes) {
            $total += count($changes);
        }

        return $total;
    }

    public function countForQuestion(string $slug, ?int $questionId): int
    {
        return count($this->allForQuestion($slug, $questionId));
    }

    public function add(string $slug, array $change): array
    {
        $questionId = $this->normalizeQuestionId($change['question_id'] ?? null);
        $changes = $this->readBucket($slug, $questionId);

        $change['question_id'] = $questionId;
        $change['id'] = $change['id'] ?? (string) Str::uuid();
        $change['created_at'] = $change['created_at'] ?? now()->toIso8601String();

        $changes[] = $change;

        $this->writeBucket($slug, $questionId, $changes);

        return $change;
    }

    public function find(string $slug, string $changeId): ?array
    {
        foreach ($this->groupedByQuestion($slug) as $questionId => $changes) {
            foreach ($changes as $change) {
                if (($change['id'] ?? null) === $changeId) {
                    return [
                        'question_id' => $questionId,
                        'change' => $change,
                    ];
                }
            }
        }

        foreach ($this->allForQuestion($slug, null) as $change) {
            if (($change['id'] ?? null) === $changeId) {
                return [
                    'question_id' => null,
                    'change' => $change,
                ];
            }
        }

        return null;
    }

    public function remove(string $slug, string $changeId, ?int $questionId = null): void
    {
        $questionId = $this->normalizeQuestionId($questionId);
        $changes = $this->readBucket($slug, $questionId);

        if (empty($changes)) {
            return;
        }

        $filtered = collect($changes)
            ->reject(fn ($change) => ($change['id'] ?? null) === $changeId)
            ->values()
            ->all();

        if (empty($filtered)) {
            $this->deleteBucketFile($slug, $questionId);
        } else {
            $this->writeBucket($slug, $questionId, $filtered);
        }
    }

    public function clearForQuestion(string $slug, int $questionId): void
    {
        $this->deleteBucketFile($slug, $questionId);
    }

    private function readBucket(string $slug, ?int $questionId): array
    {
        $path = $this->path($slug, $questionId);

        return $this->readFromPath($path);
    }

    private function writeBucket(string $slug, ?int $questionId, array $changes): void
    {
        $directory = $this->directory($slug);

        $this->disk()->makeDirectory($directory);
        $this->disk()->put(
            $this->path($slug, $questionId),
            json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function deleteBucketFile(string $slug, ?int $questionId): void
    {
        $path = $this->path($slug, $questionId);

        if ($this->disk()->exists($path)) {
            $this->disk()->delete($path);
        }
    }

    private function readFromPath(string $path): array
    {
        if (! $this->disk()->exists($path)) {
            return [];
        }

        $contents = $this->disk()->get($path);
        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }

    private function listQuestionFiles(string $slug): array
    {
        $directory = $this->directory($slug);

        if (! $this->disk()->exists($directory)) {
            return [];
        }

        $files = $this->disk()->files($directory);
        $result = [];

        foreach ($files as $path) {
            $name = basename($path);

            if (preg_match('/^question-(\d+)\.json$/', $name, $matches)) {
                $result[(int) $matches[1]] = $path;
            }
        }

        ksort($result);

        return $result;
    }

    private function normalizeQuestionId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function directory(string $slug): string
    {
        return self::DIRECTORY . '/' . $slug;
    }

    private function path(string $slug, ?int $questionId): string
    {
        $filename = $questionId === null
            ? self::GLOBAL_FILENAME
            : 'question-' . $questionId . '.json';

        return $this->directory($slug) . '/' . $filename;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk('test_tech');
    }
}
