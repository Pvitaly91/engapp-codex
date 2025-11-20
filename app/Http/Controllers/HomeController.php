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
        // Simplified version without database queries for demo
        return view('home-new');
    }
}
