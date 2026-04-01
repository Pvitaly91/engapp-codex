<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\PromptGeneratorFilterNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class TheoryPagePromptLinkedTestsService
{
    private const LEVEL_PAIRS = [
        ['A1', 'A2'],
        ['A2', 'B1'],
        ['B1', 'B2'],
        ['B2', 'C1'],
        ['C1', 'C2'],
    ];

    private const QUESTIONS_PER_TEST = 10;

    public function buildForPage(Page $page): Collection
    {
        $linkedTests = $this->findForPage($page);

        if ($linkedTests->isEmpty()) {
            return collect();
        }

        $seederClasses = $this->collectSeederClasses($linkedTests);

        if ($seederClasses->isEmpty()) {
            return collect();
        }

        return collect(self::LEVEL_PAIRS)
            ->map(fn (array $levelPair) => $this->buildLevelPairTest($page, $linkedTests, $seederClasses, $levelPair))
            ->filter()
            ->values();
    }

    public function findForPage(Page $page): Collection
    {
        $pageId = (int) $page->getKey();

        if ($pageId <= 0) {
            return collect();
        }

        $tests = $this->loadTestsForPageId($pageId);

        return $tests
            ->map(fn (SavedGrammarTest $test) => $this->hydrateDisplayMetadata($test))
            ->values();
    }

    protected function collectSeederClasses(Collection $linkedTests): Collection
    {
        $declaredSeeders = $linkedTests
            ->flatMap(fn (SavedGrammarTest $test) => Arr::get($test->filters ?? [], 'seeder_classes', []))
            ->filter(fn ($value) => is_string($value) && $value !== '');

        $questionUuids = $linkedTests
            ->flatMap(fn (SavedGrammarTest $test) => $test->questionLinks->pluck('question_uuid'))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()
            ->values();

        $resolvedSeeders = $questionUuids->isEmpty()
            ? collect()
            : Question::query()
                ->whereIn('uuid', $questionUuids)
                ->whereNotNull('seeder')
                ->distinct()
                ->orderBy('seeder')
                ->pluck('seeder');

        return $declaredSeeders
            ->merge($resolvedSeeders)
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()
            ->values();
    }

    protected function buildLevelPairTest(
        Page $page,
        Collection $linkedTests,
        Collection $seederClasses,
        array $levelPair
    ): ?VirtualSavedTest {
        [$levelFrom, $levelTo] = $levelPair;

        $availableCount = Question::query()
            ->whereIn('seeder', $seederClasses->all())
            ->whereIn('level', [$levelFrom, $levelTo])
            ->count();

        if ($availableCount < self::QUESTIONS_PER_TEST) {
            return null;
        }

        $referenceFilters = $this->baseFilters($linkedTests);
        $referenceFilters['levels'] = [$levelFrom, $levelTo];
        $referenceFilters['seeder_classes'] = $seederClasses->values()->all();
        $referenceFilters['num_questions'] = self::QUESTIONS_PER_TEST;
        $referenceFilters['randomize_filtered'] = true;
        $referenceFilters['__meta'] = ['mode' => 'filters'];

        $slug = Str::slug(sprintf(
            '%s-theory-aggregated-%s-%s',
            $page->slug,
            strtolower($levelFrom),
            strtolower($levelTo)
        ));

        $name = sprintf('%s (%s-%s)', $page->title, $levelFrom, $levelTo);

        return (new VirtualSavedTest($name, $slug, $referenceFilters))
            ->setTotalQuestionsAvailable($availableCount);
    }

    protected function baseFilters(Collection $linkedTests): array
    {
        $reference = $linkedTests->first();
        $filters = is_array($reference?->filters) ? $reference->filters : [];

        return array_filter([
            'categories' => Arr::get($filters, 'categories', []),
            'difficulty_from' => Arr::get($filters, 'difficulty_from', 1),
            'difficulty_to' => Arr::get($filters, 'difficulty_to', 10),
            'manual_input' => (bool) Arr::get($filters, 'manual_input', false),
            'autocomplete_input' => (bool) Arr::get($filters, 'autocomplete_input', false),
            'check_one_input' => (bool) Arr::get($filters, 'check_one_input', false),
            'builder_input' => (bool) Arr::get($filters, 'builder_input', false),
            'include_ai' => (bool) Arr::get($filters, 'include_ai', false),
            'only_ai' => (bool) Arr::get($filters, 'only_ai', false),
            'include_ai_v2' => (bool) Arr::get($filters, 'include_ai_v2', false),
            'only_ai_v2' => (bool) Arr::get($filters, 'only_ai_v2', false),
            'question_types' => Arr::get($filters, 'question_types', []),
            'blank_count_from' => Arr::get($filters, 'blank_count_from'),
            'blank_count_to' => Arr::get($filters, 'blank_count_to'),
            'preferred_view' => Arr::get($filters, 'preferred_view'),
        ], fn ($value) => $value !== null);
    }

    protected function loadTestsForPageId(int $pageId): Collection
    {
        try {
            $tests = SavedGrammarTest::query()
                ->with('questionLinks')
                ->withCount('questionLinks')
                ->where(function (Builder $query) use ($pageId): void {
                    $query->where('filters->prompt_generator->theory_page_id', $pageId)
                        ->orWhere('filters->prompt_generator->theory_page->id', $pageId)
                        ->orWhereJsonContains('filters->prompt_generator->theory_page_ids', $pageId);
                })
                ->orderByDesc('updated_at')
                ->orderBy('name')
                ->get();

            if ($tests->isNotEmpty()) {
                return $tests;
            }
        } catch (Throwable) {
        }

        return SavedGrammarTest::query()
            ->with('questionLinks')
            ->withCount('questionLinks')
            ->get()
            ->filter(fn (SavedGrammarTest $test) => $this->filtersReferencePage($test->filters ?? [], $pageId))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filtersReferencePage(array $filters, int $pageId): bool
    {
        $promptGenerator = PromptGeneratorFilterNormalizer::normalize($filters['prompt_generator'] ?? null);

        if (! is_array($promptGenerator)) {
            return false;
        }

        $singleId = (int) ($promptGenerator['theory_page_id'] ?? ($promptGenerator['theory_page']['id'] ?? 0));

        if ($singleId === $pageId) {
            return true;
        }

        $pageIds = $promptGenerator['theory_page_ids'] ?? [];

        if (! is_array($pageIds)) {
            return false;
        }

        return collect($pageIds)
            ->map(fn ($value) => (int) $value)
            ->contains($pageId);
    }

    protected function hydrateDisplayMetadata(SavedGrammarTest $test): SavedGrammarTest
    {
        $filters = is_array($test->filters) ? $test->filters : [];
        $actualQuestionCount = (int) ($test->question_links_count ?? 0);

        if (! isset($filters['num_questions']) && $actualQuestionCount > 0) {
            $filters['num_questions'] = $actualQuestionCount;
            $test->filters = $filters;
        }

        $test->setAttribute(
            'total_questions_available',
            $actualQuestionCount > 0 ? $actualQuestionCount : (int) ($filters['num_questions'] ?? 0)
        );

        return $test;
    }
}
