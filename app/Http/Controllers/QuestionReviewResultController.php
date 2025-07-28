<?php

namespace App\Http\Controllers;

use App\Models\QuestionReviewResult;

class QuestionReviewResultController extends Controller
{
    public function index()
    {
        $results = QuestionReviewResult::with(['question.answers.option'])
            ->latest()->paginate(20);

        return view('question-review-results', compact('results'));
    }
}
