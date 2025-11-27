<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use App\Services\GrammarTestFilterService;
use App\Services\VirtualSavedTest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class SavedTestResolver
{
    /** Maximum length for base64 encoded filter input (10KB) */
    private const MAX_ENCODED_FILTER_LENGTH = 10240;

    /** Maximum length for test name parameter */
    private const MAX_NAME_LENGTH = 255;

    public function __construct(
        private GrammarTestFilterService $filterService,
    ) {
    }

    public function resolve(string $slug): ResolvedSavedTest
    {
        $legacy = Test::where('slug', $slug)->first();

        if ($legacy) {
            $questionIds = collect($legacy->questions ?? [])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values();

            $uuidMap = $questionIds->isEmpty()
                ? collect()
                : Question::whereIn('id', $questionIds)->pluck('uuid', 'id');

            $questionUuids = $questionIds
                ->map(fn ($id) => $uuidMap[$id] ?? null)
                ->filter()
                ->values();

            return new ResolvedSavedTest($legacy, $questionIds, $questionUuids, false);
        }

        $saved = SavedGrammarTest::with('questionLinks')->where('slug', $slug)->first();

        if ($saved) {
            return $this->resolveFromModel($saved);
        }

        // Try to resolve from query parameters (for virtual/filter-based tests)
        $virtualTest = $this->resolveFromQueryParams($slug);
        if ($virtualTest) {
            return $virtualTest;
        }

        throw new ModelNotFoundException("Saved test [{$slug}] not found.");
    }

    /**
     * Resolve a test from a SavedGrammarTest model.
     */
    private function resolveFromModel(SavedGrammarTest $saved): ResolvedSavedTest
    {
        $rawFilters = $saved->filters ?? [];
        if (is_string($rawFilters)) {
            $decoded = json_decode($rawFilters, true);
            $rawFilters = is_array($decoded) ? $decoded : [];
        }

        $isFilterBased = is_array($rawFilters)
            && Arr::get($rawFilters, '__meta.mode') === 'filters';

        if ($isFilterBased) {
            $normalized = $this->filterService->normalize($rawFilters);
            $questions = $this->filterService->questionsFromFilters($normalized);

            $questionIds = $questions->pluck('id')
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values();

            $questionUuids = $questions->pluck('uuid')
                ->filter(fn ($uuid) => filled($uuid))
                ->values();

            return new ResolvedSavedTest($saved, $questionIds, $questionUuids, true, $questions, true);
        }

        $questionUuids = $saved->questionLinks
            ->sortBy('position')
            ->pluck('question_uuid')
            ->filter(fn ($uuid) => filled($uuid))
            ->values();

        $idMap = $questionUuids->isEmpty()
            ? collect()
            : Question::whereIn('uuid', $questionUuids)->pluck('id', 'uuid');

        $questionIds = $questionUuids
            ->map(fn ($uuid) => isset($idMap[$uuid]) ? (int) $idMap[$uuid] : null)
            ->filter(fn ($id) => $id !== null)
            ->values();

        return new ResolvedSavedTest($saved, $questionIds, $questionUuids, true);
    }

    /**
     * Resolve a virtual test from query parameters.
     * 
     * This supports filter-based tests that aren't stored in the database.
     * The filters are passed as a base64-encoded JSON string in the 'filters' query param.
     */
    private function resolveFromQueryParams(string $slug): ?ResolvedSavedTest
    {
        $request = request();
        $encodedFilters = $request->query('filters');
        
        if (!$encodedFilters) {
            return null;
        }

        // Validate input length to prevent abuse
        if (!is_string($encodedFilters) || strlen($encodedFilters) > self::MAX_ENCODED_FILTER_LENGTH) {
            return null;
        }

        // Only allow valid base64 characters
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $encodedFilters)) {
            return null;
        }

        $decodedFilters = base64_decode($encodedFilters, true);
        if ($decodedFilters === false) {
            return null;
        }

        $filters = json_decode($decodedFilters, true);
        if (!is_array($filters)) {
            return null;
        }

        // Validate required filter structure
        if (!$this->validateFilterStructure($filters)) {
            return null;
        }

        // Sanitize the name parameter
        $name = $request->query('name', 'Тест');
        if (!is_string($name) || strlen($name) > self::MAX_NAME_LENGTH) {
            $name = 'Тест';
        }

        // Create a virtual test model
        $virtualTest = new VirtualSavedTest(
            name: $name,
            slug: $slug,
            filters: $filters
        );

        $normalized = $this->filterService->normalize($filters);
        $questions = $this->filterService->questionsFromFilters($normalized);

        $questionIds = $questions->pluck('id')
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        $questionUuids = $questions->pluck('uuid')
            ->filter(fn ($uuid) => filled($uuid))
            ->values();

        return new ResolvedSavedTest($virtualTest, $questionIds, $questionUuids, true, $questions, true);
    }

    public function loadQuestions(ResolvedSavedTest $resolved, array $relations = []): Collection
    {
        if ($resolved->preloadedQuestions !== null) {
            return $resolved->preloadedQuestions;
        }

        if ($resolved->questionIds->isEmpty()) {
            return collect();
        }

        $query = Question::with($relations)->whereIn('id', $resolved->questionIds);

        $questions = $query->get()->keyBy('id');

        return $resolved->questionIds
            ->map(fn ($id) => $questions->get($id))
            ->filter()
            ->values();
    }

    /**
     * Validate the basic structure of filter parameters.
     * 
     * This ensures the filters contain expected keys with valid types.
     */
    private function validateFilterStructure(array $filters): bool
    {
        // Check for filter mode (required for virtual tests)
        if (!isset($filters['__meta']['mode']) || $filters['__meta']['mode'] !== 'filters') {
            return false;
        }

        // Validate tags array
        if (isset($filters['tags']) && !is_array($filters['tags'])) {
            return false;
        }

        // Validate levels array
        if (isset($filters['levels']) && !is_array($filters['levels'])) {
            return false;
        }

        // Validate num_questions is a reasonable integer
        if (isset($filters['num_questions'])) {
            if (!is_int($filters['num_questions']) && !is_numeric($filters['num_questions'])) {
                return false;
            }
            $numQuestions = (int) $filters['num_questions'];
            if ($numQuestions < 1 || $numQuestions > 1000) {
                return false;
            }
        }

        return true;
    }
}
