<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainController extends Controller
{
    // Список тем (можеш дістати з БД, або зробити масив тут)
    public $topics = [
        'all' => 'All topics',
        'past_simple_tense' => 'Past Simple Tense',
        'future_simple_tense' => 'Future Simple Tense',
        'present_simple_tense' => 'Present Simple Tense',
        /*'family' => 'Family',
        'school' => 'School',
        'nature' => 'Nature',
        'business' => 'Business',*/
        // ...
    ];

    public function index(Request $request, $topic = 'all')
    {
        $topics = $this->topics;
        $current = $topic;
        return view('train.index', compact('topics', 'current'));
    }
}
