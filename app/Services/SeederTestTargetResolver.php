<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeederTestTargetResolver
{
    private ?Collection $preparedSavedTests = null;

    private ?Collection $preparedLegacyTests = null;

    public function resolveForSeeder(string $className): ?array
    {
        return $this->resolveForSeeders([$className])->get($className);
    }

    /**
     * @param  iterable<int, string>  $classNames
     * @return Collection<string, array<string, mixed>>
     */
    public function resolveForSeeders(iterable $classNames): Collection
    {
        $normalizedClassNames = collect($classNames)
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedClassNames->isEmpty()) {
            return collect();
        }

        $questionsBySeeder = collect();

        if (Schema::hasColumn('questions', 'seeder')) {
            $questionsBySeeder = Question::query()
                ->select(['id', 'uuid', 'seeder'])
                ->whereIn('seeder', $normalizedClassNames->all())
                ->whereNotNull('seeder')
                ->get()
                ->groupBy('seeder');
        }

        $savedTests = $this->preparedSavedTests();
        $legacyTests = $this->preparedLegacyTests();

        return $normalizedClassNames
            ->mapWithKeys(function (string $className) use ($questionsBySeeder, $savedTests, $legacyTests) {
                $resolved = $this->resolveForPreparedTests(
                    $className,
                    $questionsBySeeder->get($className, collect()),
                    $savedTests,
                    $legacyTests
                );

                return $resolved ? [$className => $resolved] : [];
            });
    }

    protected function preparedSavedTests(): Collection
    {
        if ($this->preparedSavedTests instanceof Collection) {
            return $this->preparedSavedTests;
        }

        $this->preparedSavedTests = SavedGrammarTest::query()
            ->with('questionLinks')
            ->get()
            ->map(function (SavedGrammarTest $test) {
                $filters = $this->normalizeFilters($test->filters ?? []);
                $questionUuids = $test->questionLinks
                    ->pluck('question_uuid')
                    ->filter(fn ($uuid) => filled($uuid))
                    ->map(fn ($uuid) => (string) $uuid)
                    ->values()
                    ->all();

                return [
                    'model' => $test,
                    'filters' => $filters,
                    'route_name' => $this->resolvePublicRouteName($filters),
                    'created_at_timestamp' => optional($test->created_at)->timestamp ?? 0,
                    'normalized_seeder_classes' => $this->normalizeSeederClasses(
                        Arr::get($filters, 'seeder_classes', [])
                    ),
                    'question_signature' => $this->buildSignature($questionUuids),
                ];
            });

        return $this->preparedSavedTests;
    }

    protected function preparedLegacyTests(): Collection
    {
        if ($this->preparedLegacyTests instanceof Collection) {
            return $this->preparedLegacyTests;
        }

        $this->preparedLegacyTests = Test::query()
            ->get()
            ->map(function (Test $test) {
                $filters = $this->normalizeFilters($test->filters ?? []);
                $questionIds = collect($test->questions ?? [])
                    ->filter(fn ($id) => filled($id))
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                return [
                    'model' => $test,
                    'filters' => $filters,
                    'route_name' => $this->resolvePublicRouteName($filters),
                    'created_at_timestamp' => optional($test->created_at)->timestamp ?? 0,
                    'question_signature' => $this->buildSignature($questionIds),
                ];
            });

        return $this->preparedLegacyTests;
    }

    protected function resolveForPreparedTests(
        string $className,
        Collection $questions,
        Collection $savedTests,
        Collection $legacyTests,
    ): ?array {
        $normalizedClassName = $this->normalizeClassName($className);
        $questionIds = $questions
            ->pluck('id')
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $questionUuids = $questions
            ->pluck('uuid')
            ->filter(fn ($uuid) => filled($uuid))
            ->map(fn ($uuid) => (string) $uuid)
            ->values()
            ->all();

        $questionIdSignature = $this->buildSignature($questionIds);
        $questionUuidSignature = $this->buildSignature($questionUuids);

        $candidates = collect();

        foreach ($savedTests as $preparedTest) {
            $score = 0;
            $matchType = null;

            if (
                $normalizedClassName !== ''
                && in_array($normalizedClassName, $preparedTest['normalized_seeder_classes'], true)
            ) {
                $score = count($preparedTest['normalized_seeder_classes']) === 1 ? 240 : 210;
                $matchType = 'seeder_filter';
            }

            if (
                $questionUuidSignature !== null
                && $preparedTest['question_signature'] !== null
                && hash_equals($questionUuidSignature, $preparedTest['question_signature'])
            ) {
                $score = 320;
                $matchType = 'exact_saved';
            }

            if ($score === 0) {
                continue;
            }

            $candidates->push([
                'score' => $score,
                'match_type' => $matchType,
                'route_name' => $preparedTest['route_name'],
                'created_at_timestamp' => $preparedTest['created_at_timestamp'],
                'model' => $preparedTest['model'],
            ]);
        }

        foreach ($legacyTests as $preparedTest) {
            if (
                $questionIdSignature === null
                || $preparedTest['question_signature'] === null
                || ! hash_equals($questionIdSignature, $preparedTest['question_signature'])
            ) {
                continue;
            }

            $candidates->push([
                'score' => 300,
                'match_type' => 'exact_legacy',
                'route_name' => $preparedTest['route_name'],
                'created_at_timestamp' => $preparedTest['created_at_timestamp'],
                'model' => $preparedTest['model'],
            ]);
        }

        $bestCandidate = $candidates
            ->sort(function (array $left, array $right) {
                return [$right['score'], $right['created_at_timestamp']]
                    <=> [$left['score'], $left['created_at_timestamp']];
            })
            ->first();

        if (! $bestCandidate || ! filled($bestCandidate['model']->slug ?? null)) {
            return $this->buildVirtualTarget($className, count($questionIds));
        }

        return [
            'label' => __('Готовий тест'),
            'title' => $bestCandidate['model']->name ?? $bestCandidate['model']->slug,
            'slug' => $bestCandidate['model']->slug,
            'url' => localized_route($bestCandidate['route_name'], $bestCandidate['model']->slug),
            'route_name' => $bestCandidate['route_name'],
            'match_type' => $bestCandidate['match_type'],
            'class_name' => $className,
        ];
    }

    protected function normalizeFilters(mixed $filters): array
    {
        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            $filters = is_array($decoded) ? $decoded : [];
        }

        return is_array($filters) ? $filters : [];
    }

    /**
     * @param  mixed  $seederClasses
     * @return array<int, string>
     */
    protected function normalizeSeederClasses(mixed $seederClasses): array
    {
        if (! is_array($seederClasses)) {
            return [];
        }

        return collect($seederClasses)
            ->map(fn ($className) => $this->normalizeClassName((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeClassName(string $className): string
    {
        return Str::lower(trim($className));
    }

    /**
     * @param  array<int, int|string>  $values
     */
    protected function buildSignature(array $values): ?string
    {
        $normalized = collect($values)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $normalized === [] ? null : implode('|', $normalized);
    }

    protected function resolvePublicRouteName(array $filters): string
    {
        return match (Arr::get($filters, 'preferred_view')) {
            'drag-drop' => 'test.drag-drop',
            'match' => 'test.match',
            'dialogue' => 'test.dialogue',
            default => 'test.show',
        };
    }

    protected function buildVirtualTarget(string $className, int $questionCount): ?array
    {
        if ($questionCount <= 0) {
            return null;
        }

        $displayBaseName = class_basename($className);
        $titleBase = preg_replace('/Seeder$/', '', $displayBaseName) ?: $displayBaseName;
        $title = Str::headline($titleBase);
        $slug = 'virtual-seeder-' . Str::slug($titleBase) . '-' . substr(md5($className), 0, 8);
        $preferredView = $this->inferPreferredView($className);
        $filters = [
            'seeder_classes' => [$className],
            'num_questions' => min(max($questionCount, 1), 1000),
            'randomize_filtered' => false,
            '__meta' => ['mode' => 'filters'],
        ];

        if ($preferredView !== null) {
            $filters['preferred_view'] = $preferredView;
        }

        $routeName = $this->resolvePublicRouteName($filters);
        $encodedFilters = base64_encode(json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $url = localized_route($routeName, $slug) . '?' . http_build_query([
            'filters' => $encodedFilters,
            'name' => $title,
        ]);

        return [
            'label' => __('Готовий тест'),
            'title' => $title,
            'slug' => $slug,
            'url' => $url,
            'route_name' => $routeName,
            'match_type' => 'virtual_filter',
            'class_name' => $className,
            'is_virtual' => true,
        ];
    }

    protected function inferPreferredView(string $className): ?string
    {
        $normalized = Str::lower(class_basename($className));

        return match (true) {
            Str::contains($normalized, 'dragdrop') => 'drag-drop',
            Str::contains($normalized, 'match') => 'match',
            Str::contains($normalized, 'dialogue') => 'dialogue',
            default => null,
        };
    }
}
