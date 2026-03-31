<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SavedGrammarTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class TheoryPagePromptLinkedTestsService
{
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

    protected function loadTestsForPageId(int $pageId): Collection
    {
        try {
            return SavedGrammarTest::query()
                ->withCount('questionLinks')
                ->where(function (Builder $query) use ($pageId): void {
                    $query->where('filters->prompt_generator->theory_page_id', $pageId)
                        ->orWhere('filters->prompt_generator->theory_page->id', $pageId)
                        ->orWhereJsonContains('filters->prompt_generator->theory_page_ids', $pageId);
                })
                ->orderByDesc('updated_at')
                ->orderBy('name')
                ->get();
        } catch (Throwable) {
            return SavedGrammarTest::query()
                ->withCount('questionLinks')
                ->get()
                ->filter(fn (SavedGrammarTest $test) => $this->filtersReferencePage($test->filters ?? [], $pageId))
                ->values();
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filtersReferencePage(array $filters, int $pageId): bool
    {
        $promptGenerator = $filters['prompt_generator'] ?? null;

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
