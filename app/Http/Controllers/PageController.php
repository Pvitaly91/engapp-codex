<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SavedGrammarTest;
use Illuminate\Support\Collection;

class PageController extends Controller
{
    /**
     * Display the available categories with the first category's pages.
     */
    public function index()
    {
        $categories = $this->categoryList();
        $selectedCategory = $categories->first();

        $selectedCategory?->load([
            'pages' => fn ($query) => $query->orderBy('title'),
            'textBlocks',
        ]);

        return view('engram.pages.index', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'categoryPages' => $selectedCategory?->pages ?? collect(),
            'categoryDescription' => $this->categoryDescriptionData($selectedCategory),
        ]);
    }

    /**
     * Display a specific category with its related theory pages.
     */
    public function category(PageCategory $category)
    {
        $categories = $this->categoryList();
        $category->load([
            'pages' => fn ($query) => $query->orderBy('title'),
            'textBlocks',
        ]);

        return view('engram.pages.index', [
            'categories' => $categories,
            'selectedCategory' => $category,
            'categoryPages' => $category->pages,
            'categoryDescription' => $this->categoryDescriptionData($category),
        ]);
    }

    /**
     * Display the specified theory page using structured text blocks.
     */
    public function show(PageCategory $category, string $pageSlug)
    {
        $page = Page::query()
            ->with(['textBlocks', 'tags'])
            ->where('slug', $pageSlug)
            ->where('page_category_id', $category->getKey())
            ->firstOrFail();

        $blocks = $page->textBlocks;

        $subtitleBlock = $blocks->firstWhere(fn ($block) => $block->type === 'subtitle');

        $columns = [
            'left' => $blocks->filter(fn ($block) => $block->column === 'left'),
            'right' => $blocks->filter(fn ($block) => $block->column === 'right'),
        ];

        $locale = $subtitleBlock->locale
            ?? ($blocks->first()?->locale)
            ?? 'uk';

        $categories = $this->categoryList();

        $categoryPages = Page::query()
            ->where('page_category_id', $category->getKey())
            ->where('id', '!=', $page->getKey())
            ->inRandomOrder()
            ->limit(3)
            ->get();

        if ($categoryPages->isEmpty()) {
            $categoryPages = collect([$page]);
        }

        // Get saved tests with matching tags
        $pageTagIds = $page->tags->pluck('id')->toArray();
        $relatedTests = !empty($pageTagIds)
            ? SavedGrammarTest::withMatchingTags($pageTagIds)->orderBy('name')->get()
            : collect();

        // Enrich related tests with level ranges and matching tags
        if ($relatedTests->isNotEmpty()) {
            $relatedTests = $relatedTests->map(function ($test) use ($pageTagIds) {
                // Get all questions for this test
                $questionUuids = $test->questionLinks->pluck('question_uuid')->toArray();
                
                if (!empty($questionUuids)) {
                    // Get questions with their levels and tags
                    $questions = \App\Models\Question::whereIn('uuid', $questionUuids)
                        ->with('tags')
                        ->get();
                    
                    // Extract unique levels and sort them
                    $levels = $questions->pluck('level')->filter()->unique()->sort();
                    $order = array_flip(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']);
                    $levels = $levels->sortBy(fn($lvl) => $order[$lvl] ?? 99)->values();
                    
                    // Find matching tags
                    $testTagIds = $questions->pluck('tags')->flatten()->pluck('id')->unique()->toArray();
                    $matchingTagIds = array_intersect($pageTagIds, $testTagIds);
                    $matchingTags = $page->tags->whereIn('id', $matchingTagIds)->pluck('name');
                    
                    // Add computed properties
                    $test->level_range = $levels;
                    $test->matching_tags = $matchingTags;
                } else {
                    $test->level_range = collect();
                    $test->matching_tags = collect();
                }
                
                return $test;
            });
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Теорія', 'url' => route('pages.index')],
            ['label' => $category->title, 'url' => route('pages.category', $category->slug)],
            ['label' => $page->title],
        ];

        return view('engram.pages.show', [
            'page' => $page,
            'breadcrumbs' => $breadcrumbs,
            'subtitleBlock' => $subtitleBlock,
            'columns' => $columns,
            'locale' => $locale,
            'categories' => $categories,
            'selectedCategory' => $category,
            'categoryPages' => $categoryPages,
            'relatedTests' => $relatedTests,
        ]);
    }

    protected function categoryList(): Collection
    {
        return PageCategory::query()
            ->withCount('pages')
            ->orderBy('title')
            ->get();
    }

    protected function categoryDescriptionData(?PageCategory $category): array
    {
        if (! $category) {
            return [
                'blocks' => collect(),
                'subtitleBlock' => null,
                'columns' => [
                    'left' => collect(),
                    'right' => collect(),
                ],
                'locale' => app()->getLocale() ?? 'uk',
                'hasBlocks' => false,
            ];
        }

        $blocks = $category->textBlocks ?? collect();
        $subtitleBlock = $blocks->firstWhere(fn ($block) => $block->type === 'subtitle');

        $columns = [
            'left' => $blocks->filter(fn ($block) => $block->column === 'left'),
            'right' => $blocks->filter(fn ($block) => $block->column === 'right'),
        ];

        $locale = $subtitleBlock->locale
            ?? ($blocks->first()?->locale)
            ?? app()->getLocale()
            ?? 'uk';

        return [
            'blocks' => $blocks,
            'subtitleBlock' => $subtitleBlock,
            'columns' => $columns,
            'locale' => $locale,
            'hasBlocks' => $blocks->isNotEmpty(),
        ];
    }
}
