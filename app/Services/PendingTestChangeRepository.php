<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PendingTestChangeRepository
{
    private const DIRECTORY = 'changes';
    private const GLOBAL_FILENAME = 'global.json';
    private const STATUS_PENDING = 'pending';
    private const STATUS_APPLIED = 'applied';
    private const STATUS_DISCARDED = 'discarded';
    private const HISTORY_LIMIT = 50;

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
            $changes = $this->readBucket($slug, $questionUuid);

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
        $hasQuestionContext = $questionId !== null || $questionUuid;
        $now = now()->toIso8601String();

        if ($hasQuestionContext) {
            if (! $questionUuid) {
                $questionUuid = (string) Str::uuid();
            }

            $existingRecords = $this->readAllRecords($slug, $questionUuid);
            $existing = $existingRecords[0] ?? null;

            $shouldReuseId = $existing && $this->isPending($existing);
            $changeId = $shouldReuseId ? ($existing['id'] ?? null) : null;
            $createdAt = $shouldReuseId ? ($existing['created_at'] ?? $now) : $now;
            $history = $existing['history'] ?? [];

            $history[] = [
                'stored_at' => $now,
                'summary' => $change['summary'] ?? null,
                'change_type' => $change['change_type'] ?? 'generic',
                'payload' => $change['payload'] ?? null,
                'snapshot' => $change['snapshot'] ?? null,
                'previous_snapshot' => $change['previous_snapshot'] ?? null,
                'response_payload' => $change['response_payload'] ?? null,
            ];

            $storedChange = array_merge($existing ?? [], $change, [
                'id' => $changeId ?? (string) Str::uuid(),
                'question_id' => $questionId,
                'question_uuid' => $questionUuid,
                'status' => self::STATUS_PENDING,
                'created_at' => $createdAt,
                'updated_at' => $now,
                'history' => $this->truncateHistory($history),
            ]);

            $existingRecords[0] = $storedChange;

            $this->writeRecords($slug, $questionUuid, $existingRecords);

            return $storedChange;
        }

        $change['question_id'] = $questionId;
        $change['question_uuid'] = null;
        $change['id'] = $change['id'] ?? (string) Str::uuid();
        $change['status'] = self::STATUS_PENDING;
        $change['created_at'] = $change['created_at'] ?? $now;
        $change['updated_at'] = $now;

        $changes = $this->readAllRecords($slug, null);
        $changes[] = $change;

        $this->writeRecords($slug, null, $changes);

        return $change;
    }

    public function find(string $slug, string $changeId): ?array
    {
        foreach ($this->listQuestionFiles($slug) as $questionUuid => $path) {
            $records = $this->readAllRecords($slug, $questionUuid);

            foreach ($records as $change) {
                if (($change['id'] ?? null) === $changeId && $this->isPending($change)) {
                    return [
                        'question_id' => $this->normalizeQuestionId($change['question_id'] ?? null),
                        'question_uuid' => $questionUuid,
                        'change' => $change,
                    ];
                }
            }
        }

        $global = $this->readAllRecords($slug, null);

        foreach ($global as $change) {
            if (($change['id'] ?? null) === $changeId && $this->isPending($change)) {
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
        $this->markDiscarded($slug, $changeId);
    }

    public function markApplied(string $slug, string $changeId): void
    {
        $this->markStatus($slug, $changeId, self::STATUS_APPLIED, function (&$change, string $timestamp) {
            $change['applied_at'] = $timestamp;
            $change['last_applied_snapshot'] = $change['snapshot'] ?? null;
        });
    }

    public function markDiscarded(string $slug, string $changeId): void
    {
        $this->markStatus($slug, $changeId, self::STATUS_DISCARDED, function (&$change, string $timestamp) {
            $change['discarded_at'] = $timestamp;
        });
    }

    private function markStatus(string $slug, string $changeId, string $status, ?callable $callback = null): void
    {
        $found = $this->find($slug, $changeId);

        if (! $found) {
            return;
        }

        $questionUuid = $found['question_uuid'] ?? null;
        $records = $this->readAllRecords($slug, $questionUuid);

        if (empty($records)) {
            return;
        }

        $now = now()->toIso8601String();
        $updated = false;

        foreach ($records as &$record) {
            if (($record['id'] ?? null) !== $changeId) {
                continue;
            }

            $record['status'] = $status;
            $record['updated_at'] = $now;

            $history = $record['history'] ?? [];
            $history[] = [
                'stored_at' => $now,
                'event' => $status,
            ];
            $record['history'] = $this->truncateHistory($history);

            if ($callback) {
                $callback($record, $now);
            }

            $updated = true;
            break;
        }

        unset($record);

        if (! $updated) {
            return;
        }

        $this->writeRecords($slug, $questionUuid, $records);
    }

    public function clearForQuestion(string $slug, int $questionId): void
    {
        $questionId = $this->normalizeQuestionId($questionId);

        foreach ($this->listQuestionFiles($slug) as $questionUuid => $path) {
            $records = $this->readAllRecords($slug, $questionUuid);

            $idsToDiscard = collect($records)
                ->filter(function ($change) use ($questionId) {
                    return $this->isPending($change)
                        && $this->normalizeQuestionId($change['question_id'] ?? null) === $questionId;
                })
                ->map(fn ($change) => $change['id'] ?? null)
                ->filter()
                ->values()
                ->all();

            foreach ($idsToDiscard as $changeId) {
                $this->markDiscarded($slug, $changeId);
            }
        }
    }

    private function readBucket(string $slug, ?string $questionUuid): array
    {
        $records = $this->readAllRecords($slug, $questionUuid);

        return collect($records)
            ->filter(fn ($change) => $this->isPending($change))
            ->values()
            ->all();
    }

    private function writeRecords(string $slug, ?string $questionUuid, array $changes): void
    {
        $directory = $this->directory($slug);

        $this->disk()->makeDirectory($directory);
        $normalized = $this->isSequentialArray($changes)
            ? array_values($changes)
            : $changes;

        $this->disk()->put(
            $this->path($slug, $questionUuid),
            json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function readAllRecords(string $slug, ?string $questionUuid): array
    {
        $path = $this->path($slug, $questionUuid);

        if (! $this->disk()->exists($path)) {
            return [];
        }
        $contents = $this->disk()->get($path);
        $data = json_decode($contents, true);

        if (! is_array($data)) {
            return [];
        }

        if ($this->isSequentialArray($data)) {
            return array_values(array_filter($data, fn ($item) => is_array($item)));
        }

        return [is_array($data) ? $data : []];
    }

    private function truncateHistory(array $history): array
    {
        if (count($history) <= self::HISTORY_LIMIT) {
            return $history;
        }

        return array_slice($history, -self::HISTORY_LIMIT);
    }

    private function isSequentialArray(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function isPending(array $change): bool
    {
        return ($change['status'] ?? self::STATUS_PENDING) === self::STATUS_PENDING;
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
