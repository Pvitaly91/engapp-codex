<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        $tagsByCategory = Tag::whereHas('questions')
            ->whereNotIn('category', ['AI', 'ai', 'Others', 'others'])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('question-review', [
            'question' => $question,
            'tagsByCategory' => $tagsByCategory,
        ]);
    }

    public function edit(Question $question)
    {
        $question->load(['options', 'answers', 'tags', 'category']);
        $tagsByCategory = Tag::whereHas('questions')
            ->whereNotIn('category', ['AI', 'ai', 'Others', 'others'])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('question-review', [
            'question' => $question,
            'tagsByCategory' => $tagsByCategory,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'level' => 'nullable|in:A1,A2,B1,B2,C1,C2',
        ]);

        $question = Question::with('tags', 'answers')->findOrFail($request->input('question_id'));

        $answers = [];
        foreach ($question->answers as $ans) {
            $default = $ans->option->option ?? $ans->answer;
            $answers[$ans->marker] = $request->input('answers.'.$ans->marker, $default);
        }

        $tags = $request->input('tags', []);
        $originalTags = $question->tags->pluck('id')->all();

        $preserved = $question->tags()
            ->whereIn('category', ['AI', 'ai', 'Others', 'others'])
            ->pluck('tags.id')
            ->all();

        $question->tags()->sync(array_unique(array_merge($tags, $preserved)));
        if (Schema::hasColumn('questions', 'level')) {
            $question->level = $request->input('level') ?: null;
        }
        $question->save();

        QuestionReviewResult::create([
            'question_id' => $question->id,
            'answers' => $answers,
            'tags' => $tags,
            'original_tags' => $originalTags,
            'comment' => $request->input('comment'),
        ]);

        $reviewed = session('reviewed_questions', []);
        $reviewed[] = $question->id;
        session(['reviewed_questions' => $reviewed]);

        return redirect()->route('question-review.index');
    }
}
