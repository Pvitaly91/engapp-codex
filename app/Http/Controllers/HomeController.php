<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Tag;

class HomeController extends Controller
{
    public function index()
    {
        $latestTests = SavedGrammarTest::query()
            ->withCount('questionLinks')
            ->latest()
            ->take(3)
            ->get();

        $featuredCategories = PageCategory::query()
            ->withCount('pages')
            ->orderBy('title')
            ->take(4)
            ->get();

        $recentPages = Page::query()
            ->with('category')
            ->whereHas('category')
            ->latest('updated_at')
            ->take(4)
            ->get();

        $stats = [
            'tests' => SavedGrammarTest::count(),
            'questions' => Question::count(),
            'pages' => Page::count(),
            'tags' => Tag::count(),
        ];

        return view('home-new', [
            'latestTests' => $latestTests,
            'featuredCategories' => $featuredCategories,
            'recentPages' => $recentPages,
            'stats' => $stats,
        ]);
    }
}
