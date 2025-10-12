<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Test;
use Illuminate\Http\Request;

class SiteSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->query('q', '');

        $pages = collect();
        $tests = collect();

        if (strlen($query) >= 2) {
            $searchTerm = "%{$query}%";

            $pages = Page::query()
                ->where(fn ($builder) => $builder
                    ->where('title', 'like', $searchTerm)
                    ->orWhere('slug', 'like', $searchTerm))
                ->orderBy('title')
                ->limit(10)
                ->get()
                ->map(fn ($page) => [
                    'title' => $page->title,
                    'type' => 'page',
                    'url' => route('pages.show', $page->slug),
                ]);

            $tests = Test::query()
                ->where('name', 'like', $searchTerm)
                ->orWhere('slug', 'like', $searchTerm)
                ->limit(10)
                ->get()
                ->map(fn ($t) => [
                    'title' => $t->name,
                    'type' => 'test',
                    'url' => route('saved-test.js', $t->slug),
                ]);
        }

        $results = $pages->concat($tests)->values();

        if ($request->expectsJson()) {
            return response()->json($results);
        }

        return view('search.results', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
