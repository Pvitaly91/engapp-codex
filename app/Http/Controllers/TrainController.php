<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainController extends Controller
{
    /**
     * Display the training interface.
     */
    public function index(Request $request, ?string $topic = null): View
    {
        return view('train.index', compact('topic'));
    }
}
