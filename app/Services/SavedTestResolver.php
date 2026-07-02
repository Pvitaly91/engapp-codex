<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use App\Support\SentenceBuilderBranding;
use App\Support\VirtualTestRegistry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SavedTestResolver
{
    /** Maximum length for base64 encoded filter input (64KB). Category theory tests carry page-group metadata. */
    private const MAX_ENCODED_FILTER_LENGTH = 65536;

    /** Maximum length for test name parameter */
    private const MAX_NAME_LENGTH = 255;

    public function __construct(
        private GrammarTestFilterService $filterService,
        private TheoryPagePromptLinkedTestsService $theoryPageTests,
    ) {}

    public function resolve(string $slug): ResolvedSavedTest
    {
        $lookupSlug = SentenceBuilderBranding::legacyLessonSlug($slug);
        $legacy = Test::where('slug', $lookupSlug)->first();

        if ($legacy) {
            $this->decoratePublicBranding($legacy);

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

        $saved = SavedGrammarTest::with('questionLinks')->where('slug', $lookupSlug)->first();

        if ($saved) {
            $this->decoratePublicBranding($saved);

            return $this->resolveFromModel($saved);
        }

        $virtualTest = $this->resolveFromRegistry($slug);
        if ($virtualTest) {
            return $virtualTest;
        }

        $virtualTest = $this->resolveFromTheoryPageSlug($slug);
        if ($virtualTest) {
            return $virtualTest;
        }

        // Try to resolve from query parameters (legacy virtual/filter-based links)
        $virtualTest = $this->resolveFromQueryParams($slug);
        if ($virtualTest) {
            return $virtualTest;
        }

        throw new ModelNotFoundException("Saved test [{$slug}] not found.");
    }

    private function resolveFromRegistry(string $slug): ?ResolvedSavedTest
    {
        $payload = VirtualTestRegistry::resolve($slug);

        if (! is_array($payload)) {
            return null;
        }

        $filters = $payload['filters'] ?? null;
        if (! is_array($filters) || ! $this->validateFilterStructure($filters)) {
            return null;
        }

        $name = $payload['name'] ?? 'Тест';
        $name = is_string($name) && $name !== '' ? $name : 'Тест';
        $description = $payload['description'] ?? null;
        $description = is_string($description) ? $description : null;
        $totalQuestionsAvailable = $payload['total_questions_available'] ?? 0;

        return $this->resolveVirtualTest(
            $slug,
            $name,
            $filters,
            $description,
            is_numeric($totalQuestionsAvailable) ? (int) $totalQuestionsAvailable : 0
        );
    }

    private function resolveFromTheoryPageSlug(string $slug): ?ResolvedSavedTest
    {
        $segments = array_values(array_filter(explode('/', trim($slug, '/')), 'strlen'));

        if (count($segments) < 2 || count($segments) > 3) {
            return null;
        }

        [$topicSlug, $pageSegment] = $segments;
        $category = PageCategory::query()->where('slug', $topicSlug)->first();

        if (! $category) {
            return null;
        }

        $candidateSlugs = array_values(array_unique([
            $topicSlug.'-'.$pageSegment,
            $pageSegment,
        ]));

        $pages = Page::query()
            ->with('category')
            ->where('page_category_id', $category->getKey())
            ->whereIn('slug', $candidateSlugs)
            ->get()
            ->keyBy('slug');

        $page = collect($candidateSlugs)
            ->map(fn (string $candidate) => $pages->get($candidate))
            ->filter()
            ->first();

        if (! $page instanceof Page) {
            return null;
        }

        $test = $this->theoryPageTests
            ->buildForPage($page)
            ->first(fn (mixed $candidate): bool => $candidate instanceof VirtualSavedTest
                && (string) $candidate->getAttribute('public_slug') === trim($slug, '/'));

        if (! $test instanceof VirtualSavedTest) {
            return null;
        }

        return $this->resolveVirtualTest(
            trim($slug, '/'),
            $test->name,
            $test->filters,
            $test->description,
            $test->totalQuestionsAvailable
        );
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

        if (! $encodedFilters) {
            return null;
        }

        // Validate input length to prevent abuse
        if (! is_string($encodedFilters) || strlen($encodedFilters) > self::MAX_ENCODED_FILTER_LENGTH) {
            return null;
        }

        // Only allow valid base64 characters
        if (! preg_match('/^[A-Za-z0-9+\/=]+$/', $encodedFilters)) {
            return null;
        }

        $decodedFilters = base64_decode($encodedFilters, true);
        if ($decodedFilters === false) {
            return null;
        }

        $filters = json_decode($decodedFilters, true);
        if (! is_array($filters)) {
            return null;
        }

        // Validate required filter structure
        if (! $this->validateFilterStructure($filters)) {
            return null;
        }

        // Sanitize the name parameter
        $name = $request->query('name', 'Тест');
        if (! is_string($name) || strlen($name) > self::MAX_NAME_LENGTH) {
            $name = 'Тест';
        }

        return $this->resolveVirtualTest($slug, $name, $filters);
    }

    private function resolveVirtualTest(
        string $slug,
        string $name,
        array $filters,
        ?string $description = null,
        int $totalQuestionsAvailable = 0
    ): ResolvedSavedTest {
        $virtualTest = new VirtualSavedTest(
            name: SentenceBuilderBranding::publicText($name),
            slug: $slug,
            filters: $filters,
            description: $description,
            totalQuestionsAvailable: $totalQuestionsAvailable
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
            // Virtual tests carry a preloaded Question collection produced
            // by the filter service. That collection only has a fixed set
            // of relations eager-loaded; if the caller asks for extra ones
            // (e.g. `theoryTextBlocks` for the test page's theory hint),
            // load just the missing ones in bulk to avoid N+1 lazy hits.
            $preloaded = $resolved->preloadedQuestions;

            if ($relations !== [] && $preloaded->isNotEmpty()) {
                // Wrap in an Eloquent collection so we can use loadMissing()
                // even when the upstream service returned a base Collection.
                $eloquentCollection = $preloaded->first() instanceof Question
                    ? new \Illuminate\Database\Eloquent\Collection($preloaded->all())
                    : null;

                $missing = collect($relations)->reject(
                    fn (string $relation): bool => $preloaded->contains(
                        fn ($question): bool => $question instanceof Question
                            && $question->relationLoaded($relation)
                    )
                )->values()->all();

                if ($missing !== [] && $eloquentCollection !== null) {
                    $eloquentCollection->loadMissing($missing);
                }
            }

            return $preloaded;
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
        if (! isset($filters['__meta']['mode']) || $filters['__meta']['mode'] !== 'filters') {
            return false;
        }

        // Validate tags array
        if (isset($filters['tags']) && ! is_array($filters['tags'])) {
            return false;
        }

        // Validate levels array
        if (isset($filters['levels']) && ! is_array($filters['levels'])) {
            return false;
        }

        // Validate num_questions is a reasonable integer
        if (isset($filters['num_questions'])) {
            if (! is_int($filters['num_questions']) && ! is_numeric($filters['num_questions'])) {
                return false;
            }
            $numQuestions = (int) $filters['num_questions'];
            if ($numQuestions < 1 || $numQuestions > 1000) {
                return false;
            }
        }

        return true;
    }

    private function decoratePublicBranding(Test|SavedGrammarTest $test): void
    {
        $test->setAttribute('public_slug', SentenceBuilderBranding::canonicalLessonSlug((string) $test->slug));
        $test->setAttribute('name', SentenceBuilderBranding::publicText((string) ($test->name ?? '')));
        $test->setAttribute('description', SentenceBuilderBranding::publicText((string) ($test->description ?? '')));
    }
}
