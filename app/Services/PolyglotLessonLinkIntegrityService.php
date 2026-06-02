<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\Database\JsonTestSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Throwable;

class PolyglotLessonLinkIntegrityService
{
    public function scan(array $lessonSlugs = [], array $courseSlugs = []): array
    {
        $lessonSlugs = $this->normalizeList($lessonSlugs);
        $courseSlugs = $this->normalizeList($courseSlugs);
        $targetSlugs = $lessonSlugs !== []
            ? $lessonSlugs
            : $this->implementedLessonSlugs($courseSlugs);

        $tests = $this->loadSavedTests($targetSlugs);
        $lessons = $tests
            ->map(fn (SavedGrammarTest $test): array => $this->scanSavedTest($test))
            ->values();

        return $this->summarize($lessons);
    }

    public function repair(array $lessonSlugs = [], array $courseSlugs = [], bool $reseed = true): array
    {
        $before = $this->scan($lessonSlugs, $courseSlugs);
        $targetSlugs = $lessonSlugs !== []
            ? $this->normalizeList($lessonSlugs)
            : $this->implementedLessonSlugs($this->normalizeList($courseSlugs));
        $seededClasses = [];
        $seedErrors = [];

        if ($reseed) {
            foreach ($this->loadSavedTests($targetSlugs) as $test) {
                foreach ($this->seederClassesForTest($test) as $className) {
                    if (in_array($className, $seededClasses, true)) {
                        continue;
                    }

                    try {
                        if (! class_exists($className) || ! is_subclass_of($className, JsonTestSeeder::class)) {
                            continue;
                        }

                        app($className)->run();
                        $seededClasses[] = $className;
                    } catch (Throwable $exception) {
                        $seedErrors[] = [
                            'class' => $className,
                            'message' => $exception->getMessage(),
                        ];
                    }
                }
            }
        }

        $pruned = $this->pruneBrokenLinks($targetSlugs);
        $after = $this->scan($lessonSlugs, $courseSlugs);

        return [
            'before' => $before,
            'after' => $after,
            'seeded_classes' => $seededClasses,
            'seed_errors' => $seedErrors,
            'pruned' => $pruned,
        ];
    }

    public function implementedLessonSlugs(array $courseSlugs = []): array
    {
        $courseSlugs = $this->normalizeList($courseSlugs);
        $directory = database_path('seeders/V3/Polyglot/Course');

        if (! File::isDirectory($directory)) {
            return [];
        }

        $slugs = [];

        foreach (File::files($directory) as $file) {
            $courseSlug = $file->getFilenameWithoutExtension();

            if ($courseSlugs !== [] && ! in_array($courseSlug, $courseSlugs, true)) {
                continue;
            }

            $payload = json_decode(File::get($file->getPathname()), true);

            if (! is_array($payload)) {
                continue;
            }

            foreach (($payload['lessons'] ?? []) as $lesson) {
                if (is_array($lesson) && ($lesson['status'] ?? null) === 'implemented') {
                    $slug = trim((string) ($lesson['slug'] ?? ''));

                    if ($slug !== '') {
                        $slugs[] = $slug;
                    }
                }
            }
        }

        return array_values(array_unique($slugs));
    }

    /**
     * @return Collection<int, SavedGrammarTest>
     */
    private function loadSavedTests(array $lessonSlugs): Collection
    {
        if ($lessonSlugs === []) {
            return collect();
        }

        return SavedGrammarTest::query()
            ->whereIn('slug', $lessonSlugs)
            ->with('questionLinks')
            ->orderBy('slug')
            ->get();
    }

    private function scanSavedTest(SavedGrammarTest $test): array
    {
        $links = $test->questionLinks;
        $uuids = $links->pluck('question_uuid')
            ->map(fn ($uuid) => trim((string) $uuid))
            ->filter()
            ->values();
        $questionsByUuid = $uuids->isEmpty()
            ? collect()
            : Question::query()
                ->whereIn('uuid', $uuids->unique()->all())
                ->get(['uuid', 'type'])
                ->keyBy('uuid');
        $duplicateCount = max(0, $uuids->count() - $uuids->unique()->count());
        $missingUuids = $uuids
            ->unique()
            ->reject(fn (string $uuid): bool => $questionsByUuid->has($uuid))
            ->values()
            ->all();
        $filters = is_array($test->filters) ? $test->filters : [];
        $composeTokens = ($filters['mode'] ?? null) === 'compose_tokens'
            || (int) ($filters['question_type'] ?? 0) === 4;
        $type4Count = $questionsByUuid
            ->filter(fn (Question $question): bool => (string) ($question->type ?? '') === Question::TYPE_COMPOSE_TOKENS)
            ->count();
        $expectedCount = (int) ($filters['num_questions'] ?? $uuids->count());
        $failed = $missingUuids !== []
            || $duplicateCount > 0
            || ($expectedCount > 0 && $uuids->count() !== $expectedCount)
            || $questionsByUuid->count() !== $uuids->unique()->count()
            || ($composeTokens && $type4Count !== $questionsByUuid->count());

        return [
            'slug' => (string) $test->slug,
            'course_slug' => (string) ($filters['course_slug'] ?? ''),
            'links' => $uuids->count(),
            'distinct_links' => $uuids->unique()->count(),
            'existing_questions' => $questionsByUuid->count(),
            'type4' => $type4Count,
            'expected' => $expectedCount,
            'orphan_links' => count($missingUuids),
            'duplicate_links' => $duplicateCount,
            'missing_question_uuids' => $missingUuids,
            'failed' => $failed,
        ];
    }

    private function summarize(Collection $lessons): array
    {
        $failedLessons = $lessons->where('failed', true)->values();

        return [
            'total_lessons_checked' => $lessons->count(),
            'passed' => $lessons->where('failed', false)->count(),
            'failed' => $failedLessons->count(),
            'orphan_links' => $lessons->sum('orphan_links'),
            'duplicate_links' => $lessons->sum('duplicate_links'),
            'affected_slugs' => $failedLessons->pluck('slug')->values()->all(),
            'lessons' => $lessons->values()->all(),
        ];
    }

    private function pruneBrokenLinks(array $lessonSlugs): array
    {
        $deletedOrphans = 0;
        $deletedDuplicates = 0;

        foreach ($this->loadSavedTests($lessonSlugs) as $test) {
            $links = $test->questionLinks()->orderBy('position')->orderBy('id')->get();
            $uuids = $links->pluck('question_uuid')
                ->map(fn ($uuid) => trim((string) $uuid))
                ->filter()
                ->unique()
                ->values();
            $existingUuids = $uuids->isEmpty()
                ? []
                : Question::query()
                    ->whereIn('uuid', $uuids->all())
                    ->pluck('uuid')
                    ->all();
            $existingLookup = array_fill_keys($existingUuids, true);
            $seen = [];
            $keptIds = [];

            foreach ($links as $link) {
                $uuid = trim((string) ($link->question_uuid ?? ''));

                if ($uuid === '' || ! isset($existingLookup[$uuid])) {
                    $link->delete();
                    $deletedOrphans++;

                    continue;
                }

                if (isset($seen[$uuid])) {
                    $link->delete();
                    $deletedDuplicates++;

                    continue;
                }

                $seen[$uuid] = true;
                $keptIds[] = (int) $link->getKey();
            }

            foreach ($keptIds as $position => $id) {
                DB::table('saved_grammar_test_questions')
                    ->where('id', $id)
                    ->update(['position' => $position + 1]);
            }
        }

        return [
            'deleted_orphan_links' => $deletedOrphans,
            'deleted_duplicate_links' => $deletedDuplicates,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function seederClassesForTest(SavedGrammarTest $test): array
    {
        $filters = is_array($test->filters) ? $test->filters : [];

        return collect(Arr::wrap($filters['seeder_classes'] ?? []))
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, string>
     */
    private function normalizeList(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
