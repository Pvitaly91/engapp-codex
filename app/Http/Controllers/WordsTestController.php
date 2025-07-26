<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;

class WordsTestController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', 'uk');
        $feedback = session('feedback');
        $stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        // Випадково: 0 — слово англ, 1 — слово укр
        $questionType = rand(0, 1);

        if ($questionType === 0) {
            // EN -> UK (як було)
            $word = Word::with(['translates' => function($q) use ($lang) {
                $q->where('lang', $lang);
            }])->inRandomOrder()->first();

            if (!$word || !$word->translates->first()) {
                return view('words.test', ['word' => null]);
            }

            $correct = $word->translates->first()->translation;

            $otherTranslations = \App\Models\Translate::where('lang', $lang)
                ->where('translation', '!=', $correct)
                ->inRandomOrder()
                ->limit(4)
                ->pluck('translation')
                ->toArray();

            $options = $otherTranslations;
            $options[] = $correct;
            shuffle($options);

            return view('words.test', [
                'word' => $word,
                'options' => $options,
                'lang' => $lang,
                'feedback' => $feedback,
                'stats' => $stats,
                'questionType' => 'en_to_uk', // для Blade
            ]);
        } else {
            // UK -> EN
            // Випадковий переклад
            $translate = \App\Models\Translate::where('lang', $lang)->inRandomOrder()->first();

            if (!$translate || !$translate->word) {
                return view('words.test', ['word' => null]);
            }

            $correctEn = $translate->word->word;

            $otherWords = Word::where('id', '!=', $translate->word_id)
                ->inRandomOrder()
                ->limit(4)
                ->pluck('word')
                ->toArray();

            $options = $otherWords;
            $options[] = $correctEn;
            shuffle($options);

            return view('words.test', [
                'word' => $translate->word, // тут важливо: word — модель Word!
                'translation' => $translate->translation, // слово українською
                'options' => $options,
                'lang' => $lang,
                'feedback' => $feedback,
                'stats' => $stats,
                'questionType' => 'uk_to_en',
            ]);
        }
    }

    public function check(Request $request)
    {
        $request->validate([
            'word_id' => 'required|exists:words,id',
            'answer' => 'required|string',
            'lang' => 'required|string',
            'questionType' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $lang = $request->input('lang', 'uk');
        $stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $stats['total']++;

        if ($request->input('questionType') === 'en_to_uk') {
            $word = Word::with(['translates' => function($q) use ($lang) {
                $q->where('lang', $lang);
            }])->findOrFail($request->input('word_id'));
            $correct = optional($word->translates->first())->translation ?? '';
        } else {
            $word = Word::findOrFail($request->input('word_id'));
            // знаходимо переклад
            $translate = $word->translates()->where('lang', $lang)->first();
            $correct = $word->word;
        }

        $isCorrect = trim($request->input('answer')) === trim($correct);

        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }
        session(['words_test_stats' => $stats]);

        session()->flash('feedback', [
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correct,
            'userAnswer' => $request->input('answer'),
            'word' => $word->word,
            'questionType' => $request->input('questionType'),
        ]);

        return redirect()->route('words.test');
    }
}
