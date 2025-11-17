<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainController extends Controller
{
    public function index($topic = null)
    {
        // Stub implementation
        return view('train.index', compact('topic'));
    }
}
