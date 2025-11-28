<?php

namespace App\Http\Controllers;

class TrainController extends Controller
{
    public function index(?string $topic = null)
    {
        return view('home', ['topic' => $topic]);
    }
}
