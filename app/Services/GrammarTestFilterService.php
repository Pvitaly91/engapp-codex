<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Question;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class GrammarTestFilterService
{
    public function prepare(array $input): array
    {
        $filters = $this->normalize($input);

        $categories = Category::all();
        $minDifficulty = 1;
        $maxDifficulty = 10;
        $maxQuestions = 999999;

        $selectedCategories = $filters['categories'];
        $difficultyFrom = $filters['difficulty_from'];
        $difficultyTo = $filters['difficulty_to'];
        $numQuestions = max(1, min($filters['num_questions'], $maxQuestions));

        $manualInput = $filters['manual_input'];
        $autocompleteInput = $filters['autocomplete_input'];
        $checkOneInput = $filters['check_one_input'];
        $builderInput = $filters['builder_input'];
        $includeAi = $filters['include_ai'];
        $onlyAi = $filters['only_ai'];
        $includeAiV2 = $filters['include_ai_v2'];
        $onlyAiV2 = $filters['only_ai_v2'];
        $selectedTags = $filters['tags'];
        $selectedLevels = $filters['levels'];
        $selectedSources = $filters['sources'];
        $selectedSeederClasses = $filters['seeder_classes'];
        $selectedTypes = $filters['types'];
        $randomizeFiltered = $filters['randomize_filtered'];

        $groupBy = ! empty($selectedSources) ? 'source_id' : 'category_id';
        if (! empty($selectedSeederClasses) || ! empty($selectedTags)) {
            $groupBy = null;
            $groups = [null];
        } else {
            $groups = ! empty($selectedCategories)
                ? $selectedCategories
                : $categories->pluck('id')->toArray();

            if ($groupBy === 'source_id') {
                $groups = $selectedSources;
            }

            $groups = array_values($groups);

            if (empty($groups)) {
                $groups = [null];
            }
        }

        $groupCount = max(count($groups), 1);
        $questionsPerGroup = (int) floor($numQuestions / $groupCount);
        $remaining = $numQuestions % $groupCount;

        $questions = collect();
        $canRandomizeFiltered = false;

        foreach ($groups as $group) {
            $take = $questionsPerGroup + ($remaining > 0 ? 1 : 0);
            if ($remaining > 0) {
                $remaining--;
            }

            $query = Question::with(['category', 'answers.option', 'options', 'verbHints.option', 'source'])
                ->whereBetween('difficulty', [$difficultyFrom, $difficultyTo]);

            if (! empty($selectedLevels)) {
                $query->whereIn('level', $selectedLevels);
            }

            if ($groupBy === 'source_id' && $group !== null) {
                $query->where('source_id', $group);
            } elseif ($groupBy === 'category_id' && $group !== null) {
                $query->where('category_id', $group);
            }

            if (! empty($selectedSeederClasses)) {
                $query->whereIn('seeder', $selectedSeederClasses);
            }

            if (! empty($selectedSources) && $groupBy !== 'source_id') {
                $query->whereIn('source_id', $selectedSources);
            }

            if (! empty($selectedCategories) && $groupBy !== 'category_id') {
                $query->whereIn('category_id', $selectedCategories);
            }

            if (! empty($selectedTags)) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('name', $selectedTags));
            }

            if (! empty($selectedTypes)) {
                if (Schema::hasColumn('questions', 'type')) {
                    $query->whereIn('type', $selectedTypes);
                }
            }

            $onlyFlags = [];
            if ($onlyAi) {
                $onlyFlags[] = 1;
            }
            if ($onlyAiV2) {
                $onlyFlags[] = 2;
            }

            if (! empty($onlyFlags)) {
                if (count($onlyFlags) === 1) {
                    $query->where('flag', $onlyFlags[0]);
                } else {
                    $query->whereIn('flag', $onlyFlags);
                }
            } else {
                $allowedFlags = [0];
                if ($includeAi) {
                    $allowedFlags[] = 1;
                }
                if ($includeAiV2) {
                    $allowedFlags[] = 2;
                }

                $allowedFlags = array_values(array_unique($allowedFlags));

                if (count($allowedFlags) === 1) {
                    $query->where('flag', $allowedFlags[0]);
                } elseif (count($allowedFlags) < 3) {
                    $query->whereIn('flag', $allowedFlags);
                }
            }

            $availableCount = (clone $query)->count();

            if ($availableCount > $take && $take > 0) {
                $canRandomizeFiltered = true;
            }

            if ($take > 0) {
                $selectionQuery = clone $query;

                if ($randomizeFiltered && $availableCount > $take) {
                    $selectionQuery->inRandomOrder();
                } else {
                    $selectionQuery->orderBy('id');
                }

                $selected = $selectionQuery->take($take)->get();

                $questions = $questions->merge($selected);
            }
        }

        $questions = $randomizeFiltered
            ? $questions->values()
            : $questions->sortBy('id')->values();

        $categoryNames = $questions->pluck('category.name')->filter()->unique()->values();
        $autoTestName = ucwords($categoryNames->join(' - '));

        $sources = Schema::hasTable('sources')
            ? Source::orderBy('name')->get()
            : collect();

        $allTags = Schema::hasTable('tags')
            ? Tag::whereHas('questions')->get()
            : collect();

        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        $levels = Question::select('level')->distinct()->pluck('level')
            ->filter()
            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
            ->values();

        $seederClasses = Schema::hasColumn('questions', 'seeder')
            ? Question::query()
                ->select('seeder')
                ->whereNotNull('seeder')
                ->distinct()
                ->orderBy('seeder')
                ->pluck('seeder')
                ->values()
            : collect();
        $seederSourceGroups = $this->seederSourceGroups();

        $questionTypes = Schema::hasColumn('questions', 'type')
            ? Question::query()
                ->select('type')
                ->whereNotNull('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->values()
            : collect();

        return [
            'categories' => $categories,
            'minDifficulty' => $minDifficulty,
            'maxDifficulty' => $maxDifficulty,
            'maxQuestions' => $maxQuestions,
            'selectedCategories' => $selectedCategories,
            'difficultyFrom' => $difficultyFrom,
            'difficultyTo' => $difficultyTo,
            'numQuestions' => $numQuestions,
            'manualInput' => $manualInput,
            'autocompleteInput' => $autocompleteInput,
            'checkOneInput' => $checkOneInput,
            'builderInput' => $builderInput,
            'includeAi' => $includeAi,
            'onlyAi' => $onlyAi,
            'includeAiV2' => $includeAiV2,
            'onlyAiV2' => $onlyAiV2,
            'questions' => $questions,
            'sources' => $sources,
            'selectedSources' => $selectedSources,
            'selectedSeederClasses' => $selectedSeederClasses,
            'autoTestName' => $autoTestName,
            'allTags' => $allTags,
            'selectedTags' => $selectedTags,
            'levels' => $levels,
            'selectedLevels' => $selectedLevels,
            'randomizeFiltered' => $randomizeFiltered,
            'canRandomizeFiltered' => $canRandomizeFiltered,
            'seederClasses' => $seederClasses,
            'seederSourceGroups' => $seederSourceGroups,
            'questionTypes' => $questionTypes,
            'selectedTypes' => $selectedTypes,
            'normalizedFilters' => $filters,
        ];
    }

    public function seederSourceGroups(): Collection
    {
        if (! Schema::hasColumn('questions', 'seeder')) {
            return collect();
        }

        $pairs = Question::query()
            ->select('seeder', 'source_id')
            ->whereNotNull('seeder')
            ->groupBy('seeder', 'source_id')
            ->get();

        if ($pairs->isEmpty()) {
            return collect();
        }

        $sourceIds = $pairs->pluck('source_id')
            ->filter()
            ->unique()
            ->values();

        $sources = $sourceIds->isEmpty()
            ? collect()
            : Source::whereIn('id', $sourceIds)
                ->orderByDesc('id')
                ->get()
                ->keyBy('id');

        return $pairs
            ->groupBy('seeder')
            ->filter(fn ($group, $seeder) => filled($seeder))
            ->map(function (Collection $group, string $seeder) use ($sources) {
                $seederSources = $group->pluck('source_id')
                    ->filter()
                    ->unique()
                    ->sortDesc()
                    ->map(fn ($id) => $sources->get($id))
                    ->filter()
                    ->values();

                return [
                    'seeder' => $seeder,
                    'sources' => $seederSources,
                ];
            })
            ->sortBy('seeder')
            ->values();
    }

    public function normalize(array $input): array
    {
        $categories = $this->intArray(Arr::get($input, 'categories', []));
        $levels = $this->stringArray(Arr::get($input, 'levels', []));
        $tags = $this->stringArray(Arr::get($input, 'tags', []));
        $sources = $this->intArray(Arr::get($input, 'sources', []));
        $seeders = $this->stringArray(Arr::get($input, 'seeder_classes', []));
        $types = $this->stringArray(Arr::get($input, 'types', []));

        return [
            'categories' => $categories,
            'difficulty_from' => $this->intValue(Arr::get($input, 'difficulty_from'), 1),
            'difficulty_to' => $this->intValue(Arr::get($input, 'difficulty_to'), 10),
            'num_questions' => $this->intValue(Arr::get($input, 'num_questions'), 10),
            'manual_input' => $this->toBool(Arr::get($input, 'manual_input', false)),
            'autocomplete_input' => $this->toBool(Arr::get($input, 'autocomplete_input', false)),
            'check_one_input' => $this->toBool(Arr::get($input, 'check_one_input', false)),
            'builder_input' => $this->toBool(Arr::get($input, 'builder_input', false)),
            'include_ai' => $this->toBool(Arr::get($input, 'include_ai', false)),
            'only_ai' => $this->toBool(Arr::get($input, 'only_ai', false)),
            'include_ai_v2' => $this->toBool(Arr::get($input, 'include_ai_v2', false)),
            'only_ai_v2' => $this->toBool(Arr::get($input, 'only_ai_v2', false)),
            'levels' => $levels,
            'tags' => $tags,
            'sources' => $sources,
            'seeder_classes' => $seeders,
            'types' => $types,
            'randomize_filtered' => $this->toBool(Arr::get($input, 'randomize_filtered', false)),
        ];
    }

    public function questionsFromFilters(array $filters): Collection
    {
        $data = $this->prepare($filters);

        return $data['questions'];
    }

    private function intArray($value): array
    {
        return collect($value)->filter(fn($v) => $v !== null && $v !== '')->map(fn($v) => (int) $v)->values()->all();
    }

    private function stringArray($value): array
    {
        return collect($value)->filter(fn($v) => is_string($v) && $v !== '')->map(fn($v) => (string) $v)->values()->all();
    }

    private function intValue($value, int $default): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'on', 'yes'], true);
        }

        return (bool) $value;
    }
}
