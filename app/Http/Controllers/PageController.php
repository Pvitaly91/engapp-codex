<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
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

        $selectedCategory?->load(['pages' => fn ($query) => $query->orderBy('title')]);

        return view('engram.pages.index', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'categoryPages' => $selectedCategory?->pages ?? collect(),
        ]);
    }

    /**
     * Display a specific category with its related theory pages.
     */
    public function category(PageCategory $category)
    {
        $categories = $this->categoryList();
        $category->load(['pages' => fn ($query) => $query->orderBy('title')]);

        return view('engram.pages.index', [
            'categories' => $categories,
            'selectedCategory' => $category,
            'categoryPages' => $category->pages,
        ]);
    }

    /**
     * Display the specified theory page using structured text blocks.
     */
    public function show(PageCategory $category, string $pageSlug)
    {
        $page = Page::query()
            ->with('textBlocks')
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

        $category->load(['pages' => fn ($query) => $query->orderBy('title')]);
        $categories = $this->categoryList();

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
            'categoryPages' => $category->pages,
        ]);
    }

    protected function categoryList(): Collection
    {
        return PageCategory::query()
            ->withCount('pages')
            ->orderBy('title')
            ->get();
    }
}
