<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Support\EngramPages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->query('q', '');

        $pages = collect();
        $tests = collect();

        if (strlen($query) >= 2) {
            $needle = Str::lower($query);

            $pages = EngramPages::all()
                ->filter(fn ($page) => Str::contains(Str::lower($page->title), $needle)
                    || Str::contains(Str::lower($page->slug), $needle))
                ->take(10)
                ->map(fn ($page) => [
                    'title' => $page->title,
                    'type' => 'page',
                    'url' => route('pages.show', $page->slug),
                ]);

            $tests = Test::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('slug', 'like', "%{$query}%")
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
