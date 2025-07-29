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

    public function indexV2(Request $request)
    {
        if ($request->boolean('reset')) {
            session()->forget([
                'translation2_stats',
                'translation2_queue',
                'translation2_total',
                'current_sentence_id2',
                'current_attempts2',
            ]);
        }

        $stats = session('translation2_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        $queue = session('translation2_queue');
        $totalCount = session('translation2_total', 0);

        if (! $queue) {
            $queue = Sentence::pluck('id')->shuffle()->toArray();
            $totalCount = count($queue);
            session(['translation2_queue' => $queue, 'translation2_total' => $totalCount]);
        }

        $currentId = session('current_sentence_id2');
        if (! $currentId) {
            if (empty($queue)) {
                return view('translate.complete', [
                    'stats' => $stats,
                    'percentage' => $percentage,
                    'totalCount' => $totalCount,
                ]);
            }
            $currentId = array_shift($queue);
            session(['translation2_queue' => $queue, 'current_sentence_id2' => $currentId, 'current_attempts2' => 0]);
        }

        $sentence = Sentence::find($currentId);
        $attempts = session('current_attempts2', 0);
        $feedback = session('translation_feedback2');

        return view('translate.test2', [
            'sentence' => $sentence,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'attempts' => $attempts,
            'feedback' => $feedback,
        ]);
    }

    public function checkV2(Request $request, ChatGPTService $gpt)
    {
        $request->validate([
            'sentence_id' => 'required|exists:sentences,id',
            'words' => 'required|array|min:1',
            'words.*' => 'string',
        ]);

        $sentence = Sentence::findOrFail($request->input('sentence_id'));
        $answer = trim(implode(' ', $request->input('words')));

        $attempts = session('current_attempts2', 0);
        $stats = session('translation2_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $result = $gpt->checkTranslation($sentence->text_uk, $sentence->text_en, $answer);

        if ($result['is_correct']) {
            $stats['correct']++; $stats['total']++;
            session([
                'translation2_stats' => $stats,
                'current_sentence_id2' => null,
                'current_attempts2' => 0,
            ]);
            session()->flash('translation_feedback2', [
                'isCorrect' => true,
                'userAnswer' => $answer,
                'correct' => $sentence->text_en,
                'explanation' => '',
            ]);
        } else {
            if ($attempts >= 1) {
                $stats['wrong']++; $stats['total']++;
                session([
                    'translation2_stats' => $stats,
                    'current_sentence_id2' => null,
                    'current_attempts2' => 0,
                ]);
                session()->flash('translation_feedback2', [
                    'isCorrect' => false,
                    'userAnswer' => $answer,
                    'explanation' => $result['explanation'] ?? '',
                ]);
            } else {
                session(['current_attempts2' => $attempts + 1]);
                session()->flash('translation_feedback2', [
                    'isCorrect' => false,
                    'userAnswer' => $answer,
                    'explanation' => $result['explanation'] ?? '',
                ]);
            }
        }

        return redirect()->route('translate.test2');
    }

    public function resetV2()
    {
        session()->forget([
            'translation2_stats',
            'translation2_queue',
            'translation2_total',
            'current_sentence_id2',
            'current_attempts2',
        ]);

        return redirect()->route('translate.test2');
    }
}
