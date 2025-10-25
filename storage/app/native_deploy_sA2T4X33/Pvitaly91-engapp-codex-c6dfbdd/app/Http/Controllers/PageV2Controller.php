<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageV2Controller extends Controller
{
    /**
     * Display a listing of the available theory pages sourced from the database.
     */
    public function index()
    {
        $pages = Page::query()->orderBy('title')->get();

        return view('engram.pages.v2.index', [
            'pages' => $pages,
            'targetRoute' => 'pages-v2.show',
        ]);
    }

    /**
     * Display the specified theory page using structured text blocks.
     */ 
    public function show(string $slug)
    {
        $page = $this->page($slug);

        $blocks = $page->textBlocks;

        $subtitleBlock = $blocks->firstWhere(fn ($block) => $block->type === 'subtitle');

        $columns = [
            'left' => $blocks->filter(fn ($block) => $block->column === 'left'),
            'right' => $blocks->filter(fn ($block) => $block->column === 'right'),
        ];

        $locale = $subtitleBlock->locale
            ?? ($blocks->first()?->locale)
            ?? 'uk';

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Pages', 'url' => route('pages-v2.index')],
            ['label' => $page->title],
        ];

        return view('engram.pages.v2.show', [
            'page' => $page,
            'breadcrumbs' => $breadcrumbs,
            'subtitleBlock' => $subtitleBlock,
            'columns' => $columns,
            'locale' => $locale,
        ]);
    }

    /**
     * Retrieve a single structured static page by slug.
     */
    protected function page(string $slug): Page
    {
        return Page::query()
            ->with(['textBlocks'])
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
