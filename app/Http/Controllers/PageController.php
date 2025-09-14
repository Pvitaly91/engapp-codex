<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();
        return view('engram.pages.index', compact('pages'));
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Theory', 'url' => route('pages.index')],
            ['label' => $page->title],
        ];
        return view('engram.pages.show', compact('page', 'breadcrumbs'));
    }
}
