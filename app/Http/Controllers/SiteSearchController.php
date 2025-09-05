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

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $pages = Page::query()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'title' => $p->title,
                'type' => 'page',
                'url' => route('pages.show', $p->slug),
            ]);

        $tests = Test::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'title' => $t->name,
                'type' => 'test',
                'url' => route('saved-test.show', $t->slug),
            ]);

        return response()->json($pages->concat($tests)->values());
    }
}

