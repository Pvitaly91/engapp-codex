<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    /**
     * Display a listing of the available theory pages.
     */
    public function index()
    {
        $pages = Page::query()->orderBy('title')->get();

        $view = request()->routeIs('pages-v2.*')
            ? 'engram.pages.v2.index'
            : 'engram.pages.index';

        $targetRoute = request()->routeIs('pages-v2.*')
            ? 'pages-v2.show'
            : 'pages.show';

        return view($view, compact('pages', 'targetRoute'));
    }

    /**
     * Display the specified theory page by slug.
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
            ?? $blocks->first()->locale
            ?? 'uk';

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Pages', 'url' => route(request()->routeIs('pages-v2.*') ? 'pages-v2.index' : 'pages.index')],
            ['label' => $page->title],
        ];

        $view = request()->routeIs('pages-v2.*')
            ? 'engram.pages.v2.show'
            : 'engram.pages.show';

        return view($view, [
            'page' => $page,
            'breadcrumbs' => $breadcrumbs,
            'subtitleBlock' => $subtitleBlock,
            'columns' => $columns,
            'locale' => $locale,
        ]);
    }

    /**
     * Retrieve a single configured static page by slug.
     */
    protected function page(string $slug): Page
    {
        return Page::query()
            ->with(['textBlocks'])
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
