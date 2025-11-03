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
        // Temporary placeholder data for demo purposes
        $stats = [
            'tests' => 127,
            'questions' => 3542,
            'pages' => 89,
            'tags' => 234,
        ];

        return view('home', [
            'stats' => $stats,
        ]);
    }
}
