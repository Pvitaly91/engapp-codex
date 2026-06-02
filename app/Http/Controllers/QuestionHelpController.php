<?php

namespace App\Http\Controllers;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use App\Services\MarkerTheoryMatcherService;
use App\Services\QuestionVariantService;
use App\Support\AiOutputSanitizer;
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
            'locale' => 'sometimes|string',
            'language' => 'sometimes|string',
        ]);

        $locale = $this->resolveRequestedLocale($request, $data['locale'] ?? $data['language'] ?? null);

        if (isset($data['question_id'])) {
            $question = Question::findOrFail($data['question_id']);
            if (! empty($data['test_slug'])) {
                $this->variantService->applyStoredVariant($data['test_slug'], $question);
            }
            $refresh = $data['refresh'] ?? false;

            $chatgptHint = $refresh ? null : $this->findStoredHint($question->id, 'chatgpt', $locale);
            if (! $chatgptHint || $refresh) {
                $text = $gpt->hintSentenceStructure($question->renderQuestionText(), $locale);
                $chatgptHint = QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => $locale],
                    ['hint' => $text]
                );
            }

            $geminiHint = $refresh ? null : $this->findStoredHint($question->id, 'gemini', $locale);
            if (! $geminiHint || $refresh) {
                $text = $gemini->hintSentenceStructure($question->renderQuestionText(), $locale);
                $geminiHint = QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'gemini', 'locale' => $locale],
                    ['hint' => $text]
                );
            }

            return response()->json([
                'chatgpt' => AiOutputSanitizer::sanitize($chatgptHint->hint),
                'gemini' => AiOutputSanitizer::sanitize($geminiHint->hint),
            ]);
        }

        $text = $data['question'];

        return response()->json([
            'chatgpt' => AiOutputSanitizer::sanitize($gpt->hintSentenceStructure($text, $locale)),
            'gemini' => AiOutputSanitizer::sanitize($gemini->hintSentenceStructure($text, $locale)),
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

        $locale = $this->resolveRequestedLocale($request, $data['language'] ?? null);
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
            $locale,
            $isCorrect
        );

        if ($storedExplanation !== null) {
            return response()->json([
                'correct' => $isCorrect,
                'explanation' => AiOutputSanitizer::sanitize($storedExplanation),
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
        $explanation = $gpt->explainWrongAnswer($questionTextForGpt, $given, $fallbackCorrect, $locale);

        return response()->json([
            'correct' => false,
            'explanation' => AiOutputSanitizer::sanitize($explanation),
        ]);
    }

    private function findStoredExplanation(array $questionTexts, string $normalizedGiven, string $normalizedCorrect, string $locale, bool $isCorrect): ?string
    {
        if (empty($questionTexts) || $normalizedCorrect === '') {
            return null;
        }

        $languages = $this->preferredExplanationLanguages($locale);

        $baseQuery = ChatGPTExplanation::query()
            ->whereIn('question', $questionTexts)
            ->whereIn('language', $languages)
            ->whereRaw('LOWER(TRIM(correct_answer)) = ?', [$normalizedCorrect]);

        if ($normalizedGiven !== '') {
            $match = $this->selectPreferredExplanation(
                (clone $baseQuery)
                    ->whereRaw('LOWER(TRIM(wrong_answer)) = ?', [$normalizedGiven])
                    ->get(),
                $languages
            );

            if ($match) {
                return AiOutputSanitizer::sanitize($match->explanation);
            }
        }

        if ($isCorrect) {
            $match = $this->selectPreferredExplanation(
                (clone $baseQuery)
                    ->where(function ($query) use ($normalizedCorrect) {
                        $query->whereRaw('LOWER(TRIM(wrong_answer)) = ?', [$normalizedCorrect])
                            ->orWhereNull('wrong_answer')
                            ->orWhereRaw("TRIM(wrong_answer) = ''");
                    })
                    ->get(),
                $languages
            );

            if ($match) {
                return AiOutputSanitizer::sanitize($match->explanation);
            }
        }

        return null;
    }

    private function findStoredHint(int $questionId, string $provider, string $locale): ?QuestionHint
    {
        $locales = $this->preferredHintLocales($locale);
        $matches = QuestionHint::query()
            ->where('question_id', $questionId)
            ->where('provider', $provider)
            ->whereIn('locale', $locales)
            ->get();

        return $this->selectPreferredHint($matches, $locales);
    }

    private function selectPreferredHint($matches, array $locales): ?QuestionHint
    {
        if ($matches->isEmpty()) {
            return null;
        }

        $priority = array_flip($locales);

        return $matches
            ->sortBy(fn (QuestionHint $hint) => $priority[strtolower(trim((string) $hint->locale))] ?? PHP_INT_MAX)
            ->first();
    }

    private function selectPreferredExplanation($matches, array $languages): ?ChatGPTExplanation
    {
        if ($matches->isEmpty()) {
            return null;
        }

        $priority = array_flip($languages);

        return $matches
            ->sortBy(fn (ChatGPTExplanation $explanation) => $priority[strtolower(trim((string) $explanation->language))] ?? PHP_INT_MAX)
            ->first();
    }

    private function preferredHintLocales(string $locale): array
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized === 'uk') {
            return ['uk', 'ua'];
        }

        return [$normalized];
    }

    private function preferredExplanationLanguages(string $locale): array
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized === 'uk') {
            return ['uk', 'ua'];
        }

        return [$normalized];
    }

    private function resolveRequestedLocale(Request $request, ?string $explicit = null): string
    {
        $sessionLocale = $request->hasSession() ? $request->session()->get('locale') : null;

        foreach ([
            $explicit,
            $request->input('locale'),
            $request->input('language'),
            app()->getLocale(),
            $sessionLocale,
            config('app.locale', 'uk'),
        ] as $candidate) {
            $normalized = $this->normalizeLocale($candidate);

            if ($normalized !== '') {
                return $normalized;
            }
        }

        return 'uk';
    }

    private function normalizeLocale(?string $locale): string
    {
        $normalized = strtolower(trim((string) $locale));

        if ($normalized === '' || $normalized === 'ua') {
            return 'uk';
        }

        return $normalized;
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

        return response()->json([
            'theory_block' => $theoryBlock,
            'matched_tag_ids' => $theoryBlock ? ($theoryBlock['matched_tag_ids'] ?? []) : [],
            'matched_tag_names' => $theoryBlock ? ($theoryBlock['matched_tag_names'] ?? ($theoryBlock['matched_tags'] ?? [])) : [],
            'score' => $theoryBlock ? ($theoryBlock['score'] ?? null) : null,
            'marker' => $data['marker'],
        ]);
    }
}
