<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Tag;
use App\Models\QuestionReviewResult;

class QuestionReviewController extends Controller
{
    public function index()
    {
        $reviewed = session('reviewed_questions', []);

        $question = Question::with(['options', 'answers', 'tags', 'category'])
            ->whereNotIn('id', $reviewed)
            ->inRandomOrder()
            ->first();

        if (! $question) {
            return view('question-review-complete');
        }

        $allTags = Tag::whereHas('questions')
            ->orderBy('name')
            ->get();

        return view('question-review', [
            'question' => $question,
            'allTags' => $allTags,
        ]);
    }

    public function edit(Question $question)
    {
        $question->load(['options', 'answers', 'tags', 'category']);
        $allTags = Tag::whereHas('questions')->orderBy('name')->get();

        return view('question-review', [
            'question' => $question,
            'allTags' => $allTags,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        $question = Question::with('tags', 'answers')->findOrFail($request->input('question_id'));

        $answers = [];
        foreach ($question->answers as $ans) {
            $default = $ans->option->option ?? $ans->answer;
            $answers[$ans->marker] = $request->input('answers.'.$ans->marker, $default);
        }

        $tags = $request->input('tags', []);
        $question->tags()->sync($tags);

        QuestionReviewResult::create([
            'question_id' => $question->id,
            'answers' => $answers,
            'tags' => $tags,
            'comment' => $request->input('comment'),
        ]);

        $reviewed = session('reviewed_questions', []);
        $reviewed[] = $question->id;
        session(['reviewed_questions' => $reviewed]);

        return redirect()->route('question-review.index');
    }
}
