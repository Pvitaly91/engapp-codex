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

        foreach ($this->listQuestionFiles($slug) as $questionUuid => $path) {
            $changes = $this->readFromPath($path);

            if (empty($changes)) {
                continue;
            }

            foreach ($changes as $change) {
                $questionId = $this->normalizeQuestionId($change['question_id'] ?? null);

                if (! array_key_exists($questionId, $result)) {
                    $result[$questionId] = [];
                }

                $result[$questionId][] = $change;
            }
        }

        ksort($result);

        return $result;
    }

    public function allForQuestion(string $slug, ?int $questionId): array
    {
        $grouped = $this->groupedByQuestion($slug);
        $changes = $grouped[$questionId] ?? [];

        if ($questionId === null) {
            $changes = array_merge($this->readBucket($slug, null), $changes);
        }

        return $changes;
    }

    public function count(string $slug): int
    {
        $total = count($this->readBucket($slug, null));

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
        $questionUuid = $change['question_uuid'] ?? null;

        if (! $questionUuid) {
            $questionUuid = (string) Str::uuid();
        }

        $change['question_id'] = $questionId;
        $change['question_uuid'] = $questionUuid;
        $change['id'] = $change['id'] ?? (string) Str::uuid();
        $change['created_at'] = $change['created_at'] ?? now()->toIso8601String();

        $changes = $this->readBucket($slug, $questionUuid);
        $changes[] = $change;

        $this->writeBucket($slug, $questionUuid, $changes);

        return $change;
    }

    public function find(string $slug, string $changeId): ?array
    {
        foreach ($this->listQuestionFiles($slug) as $questionUuid => $path) {
            $changes = $this->readFromPath($path);

            foreach ($changes as $change) {
                if (($change['id'] ?? null) === $changeId) {
                    return [
                        'question_id' => $this->normalizeQuestionId($change['question_id'] ?? null),
                        'question_uuid' => $questionUuid,
                        'change' => $change,
                    ];
                }
            }
        }

        $global = $this->readBucket($slug, null);

        foreach ($global as $change) {
            if (($change['id'] ?? null) === $changeId) {
                return [
                    'question_id' => null,
                    'question_uuid' => null,
                    'change' => $change,
                ];
            }
        }

        return null;
    }

    public function remove(string $slug, string $changeId, ?int $questionId = null): void
    {
        $questionId = $this->normalizeQuestionId($questionId);

        $found = $this->find($slug, $changeId);

        if (! $found) {
            return;
        }

        $questionUuid = $found['question_uuid'] ?? null;
        $changes = $this->readBucket($slug, $questionUuid);

        if (empty($changes)) {
            return;
        }

        $filtered = collect($changes)
            ->reject(fn ($change) => ($change['id'] ?? null) === $changeId)
            ->values()
            ->all();

        if (empty($filtered)) {
            $this->deleteBucketFile($slug, $questionUuid);
        } else {
            $this->writeBucket($slug, $questionUuid, $filtered);
        }
    }

    public function clearForQuestion(string $slug, int $questionId): void
    {
        $questionId = $this->normalizeQuestionId($questionId);

        foreach ($this->listQuestionFiles($slug) as $questionUuid => $path) {
            $changes = $this->readFromPath($path);

            $shouldDelete = collect($changes)
                ->every(fn ($change) => $this->normalizeQuestionId($change['question_id'] ?? null) === $questionId);

            if ($shouldDelete) {
                $this->deleteBucketFile($slug, $questionUuid);
            }
        }
    }

    private function readBucket(string $slug, ?string $questionUuid): array
    {
        $path = $this->path($slug, $questionUuid);

        return $this->readFromPath($path);
    }

    private function writeBucket(string $slug, ?string $questionUuid, array $changes): void
    {
        $directory = $this->directory($slug);

        $this->disk()->makeDirectory($directory);
        $this->disk()->put(
            $this->path($slug, $questionUuid),
            json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function deleteBucketFile(string $slug, ?string $questionUuid): void
    {
        $path = $this->path($slug, $questionUuid);

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

            if ($name === self::GLOBAL_FILENAME) {
                continue;
            }

            if (preg_match('/^question-([a-f0-9-]+)\.json$/i', $name, $matches)) {
                $result[$matches[1]] = $path;
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

    private function path(string $slug, ?string $questionUuid): string
    {
        $filename = $questionUuid === null
            ? self::GLOBAL_FILENAME
            : 'question-' . $questionUuid . '.json';

        return $this->directory($slug) . '/' . $filename;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk('test_tech');
    }
}
