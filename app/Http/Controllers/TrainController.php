<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainController extends Controller
{
    public function index(Request $request, $topic = null)
    {
        return view('train', compact('topic'));
    }
}
