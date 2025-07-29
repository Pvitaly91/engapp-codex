<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sentence;
use App\Services\ChatGPTService;

class SentenceTranslationTestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('reset')) {
            session()->forget([
                'translation_stats',
                'translation_queue',
                'translation_total',
                'current_sentence_id',
                'current_attempts',
            ]);
        }

        $stats = session('translation_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        $queue = session('translation_queue');
        $totalCount = session('translation_total', 0);

        if (!$queue) {
            $queue = Sentence::pluck('id')->shuffle()->toArray();
            $totalCount = count($queue);
            session(['translation_queue' => $queue, 'translation_total' => $totalCount]);
        }

        $currentId = session('current_sentence_id');
        if (!$currentId) {
            if (empty($queue)) {
                return view('translate.complete', [
                    'stats' => $stats,
                    'percentage' => $percentage,
                    'totalCount' => $totalCount,
                ]);
            }
            $currentId = array_shift($queue);
            session(['translation_queue' => $queue, 'current_sentence_id' => $currentId, 'current_attempts' => 0]);
        }

        $sentence = Sentence::find($currentId);
        $attempts = session('current_attempts', 0);
        $feedback = session('translation_feedback');

        return view('translate.test', [
            'sentence' => $sentence,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'attempts' => $attempts,
            'feedback' => $feedback,
        ]);
    }

    public function check(Request $request, ChatGPTService $gpt)
    {
        $request->validate([
            'sentence_id' => 'required|exists:sentences,id',
            'answer' => 'required|string',
        ]);

        $sentence = Sentence::findOrFail($request->input('sentence_id'));

        $attempts = session('current_attempts', 0);
        $stats = session('translation_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $result = $gpt->checkTranslation($sentence->text_uk, $sentence->text_en, $request->input('answer'));

        if ($result['is_correct']) {
            $stats['correct']++; $stats['total']++;
            session([
                'translation_stats' => $stats,
                'current_sentence_id' => null,
                'current_attempts' => 0,
            ]);
            session()->flash('translation_feedback', [
                'isCorrect' => true,
                'userAnswer' => $request->input('answer'),
                'correct' => $sentence->text_en,
                'explanation' => '',
            ]);
        } else {
            if ($attempts >= 1) {
                $stats['wrong']++; $stats['total']++;
                session([
                    'translation_stats' => $stats,
                    'current_sentence_id' => null,
                    'current_attempts' => 0,
                ]);
                session()->flash('translation_feedback', [
                    'isCorrect' => false,
                    'userAnswer' => $request->input('answer'),
                    'explanation' => $result['explanation'] ?? '',
                ]);
            } else {
                session(['current_attempts' => $attempts + 1]);
                session()->flash('translation_feedback', [
                    'isCorrect' => false,
                    'userAnswer' => $request->input('answer'),
                    'explanation' => $result['explanation'] ?? '',
                ]);
            }
        }

        return redirect()->route('translate.test');
    }

    public function reset()
    {
        session()->forget([
            'translation_stats',
            'translation_queue',
            'translation_total',
            'current_sentence_id',
            'current_attempts',
        ]);

        return redirect()->route('translate.test');
    }
}
