<?php

namespace App\Services;

use App\Models\Question;
use App\Models\UserPolyglotAnswerAttempt;
use App\Support\ComposeModeEligibility;
use Illuminate\Support\Facades\Schema;

class PolyglotLessonDebugPayloadBuilder
{
    public function build(mixed $test, array $composePayload, ?array $courseContext = null): array
    {
        $filters = ComposeModeEligibility::normalizedFilters($test);
        $courseContext = is_array($courseContext) ? $courseContext : [];
        $lessonSlug = trim((string) ($test->slug ?? ''));
        $courseSlug = trim((string) data_get($courseContext, 'course_slug', data_get($filters, 'course_slug', '')));
        $lessonOrder = data_get($courseContext, 'lesson_order', data_get($filters, 'lesson_order'));
        $completion = data_get($courseContext, 'completion', data_get($filters, 'completion', []));
        $completion = is_array($completion) ? $completion : [];
        $minRating = (float) ($completion['min_rating'] ?? data_get($filters, 'completion.min_rating', 4.5));
        $theory = $this->buildTheoryBinding($filters);
        $lessons = $this->normalizeLessons(data_get($courseContext, 'lessons', []));
        $questionAttemptStats = $this->buildQuestionAttemptStats(
            $courseSlug,
            $lessonSlug,
            $composePayload,
            $minRating
        );

        return [
            'generated_at' => now()->toIso8601String(),
            'admin_only' => true,
            'marker' => 'polyglot-admin-debug-payload',
            'lesson' => [
                'slug' => $lessonSlug,
                'title' => (string) ($test->name ?? $lessonSlug),
                'description' => (string) ($test->description ?? ''),
                'order' => $lessonOrder === null ? null : (int) $lessonOrder,
                'topic' => data_get($courseContext, 'topic', data_get($filters, 'topic')),
                'level' => data_get($courseContext, 'level', data_get($filters, 'level')),
                'mode' => data_get($courseContext, 'mode', data_get($filters, 'mode')),
                'question_type' => (string) data_get($filters, 'question_type', Question::TYPE_COMPOSE_TOKENS),
                'total_questions' => count($composePayload),
                'compose_route' => $lessonSlug !== '' ? localized_route('test.step-compose', $lessonSlug) : null,
                'previous_lesson_slug' => data_get($courseContext, 'previous_lesson_slug', data_get($filters, 'previous_lesson_slug')),
                'previous_lesson_route' => data_get($courseContext, 'previous_lesson_url'),
                'next_lesson_slug' => data_get($courseContext, 'next_lesson_slug', data_get($filters, 'next_lesson_slug')),
                'next_lesson_route' => data_get($courseContext, 'next_lesson_url'),
                'is_final_lesson' => (bool) data_get($courseContext, 'is_final_lesson', ! filled(data_get($courseContext, 'next_lesson_slug'))),
            ],
            'course' => [
                'slug' => $courseSlug !== '' ? $courseSlug : null,
                'name' => data_get($courseContext, 'course_name'),
                'description' => data_get($courseContext, 'course_description'),
                'route' => data_get($courseContext, 'course_url', $courseSlug !== '' ? localized_route('courses.show', $courseSlug) : null),
                'first_lesson_slug' => data_get($courseContext, 'first_lesson_slug'),
                'first_lesson_route' => data_get($courseContext, 'first_lesson_url'),
                'total_lessons' => data_get($courseContext, 'total_lessons'),
                'lessons' => $lessons,
                'lesson_slugs' => array_values(array_filter(array_column($lessons, 'slug'))),
            ],
            'theory' => $theory,
            'completion' => [
                'rolling_window' => max(1, (int) ($completion['rolling_window'] ?? data_get($filters, 'completion.rolling_window', 100))),
                'min_rating' => $minRating,
                'raw' => $completion,
            ],
            'stats' => [
                'question_attempts' => $this->summarizeQuestionAttemptStats($questionAttemptStats),
            ],
            'storage_keys' => $this->buildStorageKeys($courseSlug, $lessonSlug),
            'questions' => $this->buildQuestions($composePayload, $questionAttemptStats),
            'raw_filters' => $filters,
        ];
    }

    private function buildQuestions(array $composePayload, array $questionAttemptStats): array
    {
        return collect($composePayload)
            ->map(function (mixed $question, int $index) use ($questionAttemptStats) {
                $question = is_array($question) ? $question : [];
                $position = $index + 1;
                $statsKey = $this->questionStatsKey($question, $position);
                $tokenBank = collect($question['tokenBank'] ?? [])
                    ->filter(fn ($token) => is_array($token))
                    ->values();
                $correctTokens = $this->stringList($question['correctTokens'] ?? $question['correctTokenValues'] ?? []);
                $correctTokenValues = $this->stringList($question['correctTokenValues'] ?? $correctTokens);
                $correctTokenIds = $this->stringList($question['correctTokenIds'] ?? []);
                $distractors = $tokenBank
                    ->reject(fn ($token) => (bool) ($token['isCorrect'] ?? false))
                    ->map(fn ($token) => [
                        'id' => (string) ($token['id'] ?? ''),
                        'value' => (string) ($token['value'] ?? ''),
                    ])
                    ->values()
                    ->all();
                $tags = data_get($question, 'tech_info.tags', []);

                return [
                    'position' => $position,
                    'id' => $question['id'] ?? null,
                    'uuid' => $question['uuid'] ?? null,
                    'type' => $question['type'] ?? null,
                    'level' => $question['level'] ?? null,
                    'source_text_uk' => $question['sourceTextUk'] ?? $question['question'] ?? null,
                    'target_text' => $question['correctText'] ?? null,
                    'correct_tokens' => $correctTokens,
                    'correct_token_values' => $correctTokenValues,
                    'correct_token_ids' => $correctTokenIds,
                    'token_bank' => $tokenBank->all(),
                    'distractors' => $distractors,
                    'hint_uk' => $question['hintUk'] ?? null,
                    'grammar_tags' => is_array($tags) ? array_values($tags) : [],
                    'explanations' => is_array($question['explanations'] ?? null) ? $question['explanations'] : [],
                    'tech_info' => $question['tech_info'] ?? null,
                    'server_stats' => $questionAttemptStats[$statsKey]
                        ?? $this->finalizeQuestionAttemptStats($this->emptyQuestionAttemptStats($question, $position, false)),
                    'raw' => $question,
                ];
            })
            ->values()
            ->all();
    }

    private function buildQuestionAttemptStats(
        string $courseSlug,
        string $lessonSlug,
        array $composePayload,
        float $minRating
    ): array {
        $storageAvailable = $this->questionAttemptStorageAvailable();
        $stats = [];
        $uuidToKey = [];

        foreach (array_values($composePayload) as $index => $question) {
            $question = is_array($question) ? $question : [];
            $position = $index + 1;
            $key = $this->questionStatsKey($question, $position);
            $stats[$key] = $this->emptyQuestionAttemptStats($question, $position, $storageAvailable);

            $uuid = trim((string) ($question['uuid'] ?? ''));
            if ($uuid !== '') {
                $uuidToKey[$uuid] = $key;
            }
        }

        if (! $storageAvailable || $courseSlug === '' || $lessonSlug === '' || $uuidToKey === []) {
            return array_map(fn (array $item) => $this->finalizeQuestionAttemptStats($item), $stats);
        }

        $attempts = UserPolyglotAnswerAttempt::query()
            ->where('course_slug', $courseSlug)
            ->where('lesson_slug', $lessonSlug)
            ->whereIn('question_uuid', array_keys($uuidToKey))
            ->orderBy('answered_at')
            ->orderBy('id')
            ->get(['question_uuid', 'user_id', 'rating', 'is_correct', 'answered_at']);

        foreach ($attempts as $attempt) {
            $uuid = trim((string) $attempt->question_uuid);
            $key = $uuidToKey[$uuid] ?? null;

            if ($key === null || ! isset($stats[$key])) {
                continue;
            }

            $rating = (float) $attempt->rating;
            $isCorrect = $attempt->is_correct === true || $rating >= $minRating;
            $answeredAt = $this->formatDateTime($attempt->answered_at);

            $stats[$key]['answered']++;
            $stats[$key]['shown'] = $stats[$key]['answered'];
            $stats[$key][$isCorrect ? 'correct' : 'incorrect']++;
            $stats[$key]['_rating_sum'] += $rating;

            if ($attempt->user_id !== null) {
                $stats[$key]['_user_ids'][(string) $attempt->user_id] = true;
            }

            if ($answeredAt !== null) {
                $lastAnsweredAt = $stats[$key]['last_answered_at'];
                $stats[$key]['last_answered_at'] = $lastAnsweredAt === null || strcmp($answeredAt, $lastAnsweredAt) > 0
                    ? $answeredAt
                    : $lastAnsweredAt;
                $stats[$key]['last_seen_at'] = $stats[$key]['last_answered_at'];
            }
        }

        return array_map(fn (array $item) => $this->finalizeQuestionAttemptStats($item), $stats);
    }

    private function emptyQuestionAttemptStats(array $question, int $position, bool $storageAvailable): array
    {
        return [
            'scope' => 'server_all_users',
            'storage_available' => $storageAvailable,
            'shown_source' => 'answered_attempts',
            'position' => $position,
            'uuid' => trim((string) ($question['uuid'] ?? '')) ?: null,
            'id' => $question['id'] ?? null,
            'shown' => 0,
            'answered' => 0,
            'correct' => 0,
            'incorrect' => 0,
            'correct_percent' => null,
            'incorrect_percent' => null,
            'average_rating' => null,
            'unique_users' => 0,
            'last_seen_at' => null,
            'last_answered_at' => null,
            'note' => $storageAvailable
                ? 'Server stats use answered attempts as the persisted shown count.'
                : 'user_polyglot_answer_attempts table is unavailable.',
            '_rating_sum' => 0.0,
            '_user_ids' => [],
        ];
    }

    private function finalizeQuestionAttemptStats(array $stats): array
    {
        $answered = max(0, (int) ($stats['answered'] ?? 0));
        $correct = max(0, (int) ($stats['correct'] ?? 0));
        $incorrect = max(0, (int) ($stats['incorrect'] ?? 0));

        $stats['shown'] = max((int) ($stats['shown'] ?? 0), $answered);
        $stats['answered'] = $answered;
        $stats['correct'] = $correct;
        $stats['incorrect'] = $incorrect;
        $stats['correct_percent'] = $answered > 0 ? round($correct * 100 / $answered, 1) : null;
        $stats['incorrect_percent'] = $answered > 0 ? round($incorrect * 100 / $answered, 1) : null;
        $stats['average_rating'] = $answered > 0 ? round(((float) ($stats['_rating_sum'] ?? 0.0)) / $answered, 2) : null;
        $stats['unique_users'] = is_array($stats['_user_ids'] ?? null) ? count($stats['_user_ids']) : 0;

        unset($stats['_rating_sum'], $stats['_user_ids']);

        return $stats;
    }

    private function summarizeQuestionAttemptStats(array $questionAttemptStats): array
    {
        $answered = 0;
        $correct = 0;
        $incorrect = 0;
        $questionsWithAttempts = 0;
        $weightedRatingSum = 0.0;
        $storageAvailable = true;

        foreach ($questionAttemptStats as $stats) {
            $itemAnswered = max(0, (int) ($stats['answered'] ?? 0));
            $answered += $itemAnswered;
            $correct += max(0, (int) ($stats['correct'] ?? 0));
            $incorrect += max(0, (int) ($stats['incorrect'] ?? 0));
            $storageAvailable = $storageAvailable && (bool) ($stats['storage_available'] ?? false);

            if ($itemAnswered > 0) {
                $questionsWithAttempts++;
                $weightedRatingSum += ((float) ($stats['average_rating'] ?? 0.0)) * $itemAnswered;
            }
        }

        return [
            'scope' => 'server_all_users',
            'storage_available' => $storageAvailable,
            'total_questions' => count($questionAttemptStats),
            'questions_with_attempts' => $questionsWithAttempts,
            'shown' => $answered,
            'answered' => $answered,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'correct_percent' => $answered > 0 ? round($correct * 100 / $answered, 1) : null,
            'incorrect_percent' => $answered > 0 ? round($incorrect * 100 / $answered, 1) : null,
            'average_rating' => $answered > 0 ? round($weightedRatingSum / $answered, 2) : null,
            'shown_source' => 'answered_attempts',
        ];
    }

    private function questionAttemptStorageAvailable(): bool
    {
        return Schema::hasTable('user_polyglot_answer_attempts');
    }

    private function questionStatsKey(array $question, int $position): string
    {
        $uuid = trim((string) ($question['uuid'] ?? ''));

        return $uuid !== '' ? 'uuid:' . $uuid : 'position:' . $position;
    }

    private function buildStorageKeys(string $courseSlug, string $lessonSlug): array
    {
        return [
            'lesson_progress' => $lessonSlug !== '' ? "polyglot_progress:{$lessonSlug}" : null,
            'course_progress' => $courseSlug !== '' ? "polyglot_course_progress:{$courseSlug}" : null,
            'legacy_course_progress' => $courseSlug !== '' ? "polyglot_course_state:{$courseSlug}" : null,
            'lesson_debug_policy' => ($courseSlug !== '' && $lessonSlug !== '') ? "polyglot_debug_unlock_policy:{$courseSlug}:{$lessonSlug}" : null,
            'course_debug_policy' => $courseSlug !== '' ? "polyglot_debug_unlock_policy:{$courseSlug}:__course__" : null,
            'course_debug_policy_prefix' => $courseSlug !== '' ? "polyglot_debug_unlock_policy:{$courseSlug}:" : null,
            'all_debug_prefix' => 'polyglot_debug_',
            'all_lesson_progress_prefix' => 'polyglot_progress:',
            'all_course_progress_prefix' => 'polyglot_course_progress:',
        ];
    }

    private function buildTheoryBinding(array $filters): array
    {
        $theoryPage = data_get($filters, 'prompt_generator.theory_page', []);
        $theoryPage = is_array($theoryPage) ? $theoryPage : [];
        $pageSlug = trim((string) ($theoryPage['slug'] ?? ''));
        $categoryPath = trim((string) ($theoryPage['category_slug_path'] ?? ''));
        $categorySlug = $this->lastSlugSegment($categoryPath);

        return [
            'source_type' => data_get($filters, 'prompt_generator.source_type'),
            'page_slug' => $pageSlug !== '' ? $pageSlug : null,
            'title' => $theoryPage['title'] ?? null,
            'category_slug_path' => $categoryPath !== '' ? $categoryPath : null,
            'category_slug' => $categorySlug,
            'page_seeder_class' => $theoryPage['page_seeder_class'] ?? null,
            'configured_url' => $theoryPage['url'] ?? null,
            'route' => ($pageSlug !== '' && $categorySlug !== null)
                ? localized_route('theory.show', [$categorySlug, $pageSlug])
                : ($theoryPage['url'] ?? null),
        ];
    }

    private function normalizeLessons(mixed $lessons): array
    {
        if (! is_array($lessons)) {
            return [];
        }

        return collect($lessons)
            ->filter(fn ($lesson) => is_array($lesson))
            ->map(fn (array $lesson) => [
                'slug' => $lesson['slug'] ?? null,
                'name' => $lesson['name'] ?? null,
                'lesson_order' => $lesson['lesson_order'] ?? null,
                'topic' => $lesson['topic'] ?? null,
                'level' => $lesson['level'] ?? null,
                'previous_lesson_slug' => $lesson['previous_lesson_slug'] ?? null,
                'next_lesson_slug' => $lesson['next_lesson_slug'] ?? null,
                'compose_url' => $lesson['compose_url'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }

    private function lastSlugSegment(string $path): ?string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn ($segment) => $segment !== ''));
        $last = $segments !== [] ? end($segments) : null;

        return is_string($last) && trim($last) !== '' ? trim($last) : null;
    }

    private function formatDateTime(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (method_exists($value, 'toDateTimeString')) {
            return $value->toDateTimeString();
        }

        return is_string($value) ? $value : null;
    }
}
