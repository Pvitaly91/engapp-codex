<?php

namespace App\Http\Controllers;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use App\Services\MarkerTheoryMatcherService;
use App\Services\QuestionVariantService;
use Illuminate\Http\Request;

class QuestionHelpController extends Controller
{
    public function __construct(private QuestionVariantService $variantService) {}

    public function hint(Request $request, ChatGPTService $gpt, GeminiService $gemini)
    {
        $data = $request->validate([
            'question_id' => 'sometimes|integer|exists:questions,id',
            'question' => 'required_without:question_id|string',
            'refresh' => 'sometimes|boolean',
            'test_slug' => 'sometimes|string',
        ]);

        $lang = 'uk'; // app()->getLocale();

        if (isset($data['question_id'])) {
            $question = Question::findOrFail($data['question_id']);
            if (! empty($data['test_slug'])) {
                $this->variantService->applyStoredVariant($data['test_slug'], $question);
            }
            $refresh = $data['refresh'] ?? false;

            $chatgptHint = QuestionHint::where('question_id', $question->id)
                ->where('provider', 'chatgpt')
                ->where('locale', $lang)
                ->first();
            if (! $chatgptHint || $refresh) {
                $text = $gpt->hintSentenceStructure($question->renderQuestionText(), $lang);
                $chatgptHint = QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => $lang],
                    ['hint' => $text]
                );
            }

            $geminiHint = QuestionHint::where('question_id', $question->id)
                ->where('provider', 'gemini')
                ->where('locale', $lang)
                ->first();
            if (! $geminiHint || $refresh) {
                $text = $gemini->hintSentenceStructure($question->renderQuestionText(), $lang);
                $geminiHint = QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'gemini', 'locale' => $lang],
                    ['hint' => $text]
                );
            }

            return response()->json([
                'chatgpt' => $chatgptHint->hint,
                'gemini' => $geminiHint->hint,
            ]);
        }

        $text = $data['question'];

        return response()->json([
            'chatgpt' => $gpt->hintSentenceStructure($text, $lang),
            'gemini' => $gemini->hintSentenceStructure($text, $lang),
        ]);
    }

    public function explain(Request $request, ChatGPTService $gpt)
    {
        $data = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'answer' => 'required|string',
            'test_slug' => 'sometimes|string',
            'correct_answer' => 'sometimes|string',
            'marker' => 'sometimes|string',
            'language' => 'sometimes|string',
        ]);

        $lang = $data['language'] ?? 'ua'; // Stored explanations use 'ua'
        $question = Question::with('answers.option')->findOrFail($data['question_id']);

        $originalQuestionText = $question->getOriginal('question') ?? $question->question;

        if (! empty($data['test_slug'])) {
            $this->variantService->applyStoredVariant($data['test_slug'], $question);
        }

        $questionTexts = [];
        foreach ([$originalQuestionText, $question->question] as $text) {
            if (is_string($text)) {
                $trimmed = trim($text);
                if ($trimmed !== '' && ! in_array($trimmed, $questionTexts, true)) {
                    $questionTexts[] = $trimmed;
                }
            }
        }

        if (empty($questionTexts)) {
            $questionTexts[] = $question->question;
        }

        $answersByMarker = $question->answers->mapWithKeys(function ($answer) {
            $value = $answer->option->option ?? $answer->answer ?? '';

            return [$answer->marker => $value];
        });

        $correctAnswer = trim((string) ($data['correct_answer'] ?? ''));

        if ($correctAnswer === '' && isset($data['marker']) && $answersByMarker->has($data['marker'])) {
            $correctAnswer = trim((string) $answersByMarker->get($data['marker']));
        }

        if ($correctAnswer === '') {
            $correctAnswer = trim($answersByMarker->implode(' '));
        }

        if ($correctAnswer === '') {
            $correctAnswer = trim((string) ($answersByMarker->first() ?? ''));
        }

        $given = trim($data['answer']);
        $normalizedGiven = mb_strtolower($given, 'UTF-8');
        $normalizedCorrect = mb_strtolower($correctAnswer, 'UTF-8');
        $isCorrect = $normalizedCorrect !== '' && $normalizedGiven === $normalizedCorrect;

        $storedExplanation = $this->findStoredExplanation(
            $questionTexts,
            $normalizedGiven,
            $normalizedCorrect,
            $lang,
            $isCorrect
        );

        if ($storedExplanation !== null) {
            return response()->json([
                'correct' => $isCorrect,
                'explanation' => $storedExplanation,
            ]);
        }

        if ($isCorrect) {
            return response()->json([
                'correct' => true,
                'explanation' => '',
            ]);
        }

        $questionTextForGpt = $questionTexts[0] ?? $question->question;
        $fallbackCorrect = $correctAnswer !== '' ? $correctAnswer : ($answersByMarker->first() ?? '');
        $explanation = $gpt->explainWrongAnswer($questionTextForGpt, $given, $fallbackCorrect, $lang);

        return response()->json([
            'correct' => false,
            'explanation' => $explanation,
        ]);
    }

    private function findStoredExplanation(array $questionTexts, string $normalizedGiven, string $normalizedCorrect, string $lang, bool $isCorrect): ?string
    {
        if (empty($questionTexts) || $normalizedCorrect === '') {
            return null;
        }

        $languages = [];
        foreach ([$lang, 'ua', 'uk'] as $candidate) {
            if (is_string($candidate) && $candidate !== '' && ! in_array($candidate, $languages, true)) {
                $languages[] = $candidate;
            }
        }

        $baseQuery = ChatGPTExplanation::query()
            ->whereIn('question', $questionTexts)
            ->whereIn('language', $languages)
            ->whereRaw('LOWER(TRIM(correct_answer)) = ?', [$normalizedCorrect]);

        if ($normalizedGiven !== '') {
            $match = (clone $baseQuery)
                ->whereRaw('LOWER(TRIM(wrong_answer)) = ?', [$normalizedGiven])
                ->first();

            if ($match) {
                return $match->explanation;
            }
        }

        if ($isCorrect) {
            $match = (clone $baseQuery)
                ->where(function ($query) use ($normalizedCorrect) {
                    $query->whereRaw('LOWER(TRIM(wrong_answer)) = ?', [$normalizedCorrect])
                        ->orWhereNull('wrong_answer')
                        ->orWhereRaw("TRIM(wrong_answer) = ''");
                })
                ->first();

            if ($match) {
                return $match->explanation;
            }
        }

        return null;
    }

    /**
     * Get theory block for a specific marker in a question.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markerTheory(Request $request, MarkerTheoryMatcherService $matcherService)
    {
        $data = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'marker' => 'required|string|regex:/^a\d+$/',
            'test_slug' => 'sometimes|string',
        ]);

        $theoryBlock = $matcherService->findTheoryBlockForMarker(
            $data['question_id'],
            $data['marker']
        );

        $matchedTagIds = $theoryBlock['matched_tag_ids'] ?? [];
        $matchedTagNames = $theoryBlock['matched_tag_names'] ?? ($theoryBlock['matched_tags'] ?? []);
        $score = $theoryBlock['score'] ?? null;

        return response()->json([
            'theory_block' => $theoryBlock,
            'matched_tag_ids' => $matchedTagIds,
            'matched_tag_names' => $matchedTagNames,
            'score' => $score,
            'marker' => $data['marker'],
        ]);
    }
}
