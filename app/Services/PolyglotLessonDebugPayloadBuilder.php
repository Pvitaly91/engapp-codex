<?php

namespace App\Services;

use App\Models\Question;
use App\Support\ComposeModeEligibility;

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
        $theory = $this->buildTheoryBinding($filters);
        $lessons = $this->normalizeLessons(data_get($courseContext, 'lessons', []));

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
                'min_rating' => (float) ($completion['min_rating'] ?? data_get($filters, 'completion.min_rating', 4.5)),
                'raw' => $completion,
            ],
            'storage_keys' => $this->buildStorageKeys($courseSlug, $lessonSlug),
            'questions' => $this->buildQuestions($composePayload),
            'raw_filters' => $filters,
        ];
    }

    private function buildQuestions(array $composePayload): array
    {
        return collect($composePayload)
            ->map(function (mixed $question, int $index) {
                $question = is_array($question) ? $question : [];
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
                    'position' => $index + 1,
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
                    'raw' => $question,
                ];
            })
            ->values()
            ->all();
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
}
