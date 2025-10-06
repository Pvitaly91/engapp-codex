<?php

namespace App\Http\Controllers;

use App\Support\EngramPages;
use Illuminate\Support\Fluent;

class PageController extends Controller
{
    /**
     * Display a listing of the available theory pages.
     */
    public function index()
    {
        $pages = EngramPages::all();

        return view('engram.pages.index', compact('pages'));
    }

    /**
     * Display the specified theory page by slug.
     */
    public function show(string $slug)
    {
        $page = $this->page($slug);

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Pages', 'url' => route('pages.index')],
            ['label' => $page->title],
        ];

        return view('engram.pages.show', compact('page', 'breadcrumbs'));
    }

    /**
     * Retrieve a single configured static page by slug.
     */
    protected function page(string $slug): Fluent
    {
        $page = EngramPages::find($slug);

        abort_unless($page, 404);

        return $page;
    }
}
