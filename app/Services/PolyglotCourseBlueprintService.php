<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PolyglotCourseBlueprintService
{
    public function __construct(
        private PolyglotCourseManifestService $manifestService,
    ) {}

    public function getCourseBlueprint(string $courseSlug): array
    {
        $path = $this->resolveBlueprintPath($courseSlug);

        if (! File::exists($path)) {
            throw new RuntimeException(sprintf(
                'Polyglot course blueprint [%s] was not found.',
                trim($courseSlug)
            ));
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException(sprintf(
                'Polyglot course blueprint [%s] is invalid.',
                trim($courseSlug)
            ));
        }

        return $this->normalizeBlueprint($decoded, trim($courseSlug), $path);
    }

    public function getImplementedLessons(string $courseSlug): array
    {
        return $this->buildCourseStatus($courseSlug)['implemented_lessons'];
    }

    public function getPlannedLessons(string $courseSlug): array
    {
        return $this->buildCourseStatus($courseSlug)['planned_lessons'];
    }

    public function getMissingLessons(string $courseSlug): array
    {
        return $this->buildCourseStatus($courseSlug)['missing_lessons'];
    }

    public function getNextPlannedLesson(string $courseSlug): ?array
    {
        return $this->buildCourseStatus($courseSlug)['next_planned_lesson'];
    }

    public function validateCourseGraph(string $courseSlug): array
    {
        return $this->buildCourseStatus($courseSlug)['validation'];
    }

    public function buildCourseStatus(string $courseSlug): array
    {
        $manifest = $this->manifestService->build($courseSlug);
        $blueprint = $this->getCourseBlueprint($courseSlug);
        $manifestLessons = $manifest['lessons'] ?? [];
        $manifestBySlug = $manifest['by_slug'] ?? [];
        $displayLessons = [];
        $implementedLessons = [];
        $plannedLessons = [];
        $missingLessons = [];

        foreach ($blueprint['lessons'] as $blueprintLesson) {
            $slug = $blueprintLesson['slug'];
            $implementedLesson = is_array($manifestBySlug[$slug] ?? null)
                ? $manifestBySlug[$slug]
                : null;
            $isImplemented = $implementedLesson !== null;
            $isMissing = ! $isImplemented && $blueprintLesson['status'] === 'implemented';
            $displayLesson = array_merge($blueprintLesson, [
                'name' => $implementedLesson['name'] ?? $blueprintLesson['title'],
                'description' => $implementedLesson['description'] ?? '',
                'compose_url' => $implementedLesson['compose_url'] ?? null,
                'completion' => $implementedLesson['completion'] ?? null,
                'course_slug' => $implementedLesson['course_slug'] ?? $blueprint['course_slug'],
                'interface_locale' => $implementedLesson['interface_locale'] ?? ($blueprint['defaults']['interface_locale'] ?? null),
                'study_locale' => $implementedLesson['study_locale'] ?? ($blueprint['defaults']['study_locale'] ?? null),
                'target_locale' => $implementedLesson['target_locale'] ?? ($blueprint['defaults']['target_locale'] ?? null),
                'is_implemented' => $isImplemented,
                'is_planned_only' => ! $isImplemented,
                'is_missing' => $isMissing,
                'availability_status' => $isImplemented ? 'implemented' : ($isMissing ? 'missing' : 'planned'),
            ]);

            $displayLessons[] = $displayLesson;

            if ($isImplemented) {
                $implementedLessons[] = $displayLesson;
            } else {
                $plannedLessons[] = $displayLesson;
            }

            if ($isMissing) {
                $missingLessons[] = $displayLesson;
            }
        }

        $nextPlannedLesson = $plannedLessons[0] ?? null;
        $validation = $this->buildValidation($blueprint['lessons'], $manifestLessons, $manifestBySlug);

        return [
            'course' => [
                'slug' => $blueprint['course_slug'],
                'name' => $blueprint['course_title'],
                'description' => $blueprint['description'],
                'compose_url' => localized_route('courses.show', $blueprint['course_slug']),
                'level' => $blueprint['defaults']['level'] ?? ($manifest['course']['level'] ?? null),
                'mode' => $blueprint['defaults']['mode'] ?? ($manifest['course']['mode'] ?? null),
            ],
            'blueprint' => $blueprint,
            'display_lessons' => $displayLessons,
            'implemented_lessons' => $implementedLessons,
            'planned_lessons' => $plannedLessons,
            'missing_lessons' => $missingLessons,
            'implemented_routes' => array_values(array_filter(array_map(
                fn (array $lesson) => $lesson['compose_url'] ?? null,
                $implementedLessons
            ))),
            'next_planned_lesson' => $nextPlannedLesson,
            'counts' => [
                'planned_total' => $blueprint['total_planned_lessons'],
                'implemented_total' => count($implementedLessons),
                'planned_only_total' => count($plannedLessons),
                'missing_total' => count($missingLessons),
            ],
            'validation' => $validation,
            'manifest' => $manifest,
        ];
    }

    private function normalizeBlueprint(array $decoded, string $courseSlug, string $path): array
    {
        $resolvedCourseSlug = trim((string) ($decoded['course_slug'] ?? $courseSlug));

        if ($resolvedCourseSlug === '') {
            throw new RuntimeException(sprintf(
                'Blueprint [%s] must define course_slug.',
                $path
            ));
        }

        $lessons = [];
        $seenOrders = [];
        $seenSlugs = [];

        foreach (($decoded['lessons'] ?? []) as $index => $lesson) {
            if (! is_array($lesson)) {
                throw new RuntimeException(sprintf(
                    'Blueprint lesson at index [%d] must be an object.',
                    $index
                ));
            }

            $normalized = $this->normalizeBlueprintLesson($lesson, $index + 1);

            if (in_array($normalized['lesson_order'], $seenOrders, true)) {
                throw new RuntimeException(sprintf(
                    'Blueprint lesson_order [%d] is duplicated.',
                    $normalized['lesson_order']
                ));
            }

            if (in_array($normalized['slug'], $seenSlugs, true)) {
                throw new RuntimeException(sprintf(
                    'Blueprint slug [%s] is duplicated.',
                    $normalized['slug']
                ));
            }

            $seenOrders[] = $normalized['lesson_order'];
            $seenSlugs[] = $normalized['slug'];
            $lessons[] = $normalized;
        }

        usort($lessons, fn (array $left, array $right) => $left['lesson_order'] <=> $right['lesson_order']);

        $plannedTotal = (int) ($decoded['total_planned_lessons'] ?? count($lessons));

        if ($plannedTotal <= 0) {
            $plannedTotal = count($lessons);
        }

        return [
            'schema_version' => (int) ($decoded['schema_version'] ?? 1),
            'path' => $path,
            'course_slug' => $resolvedCourseSlug,
            'course_title' => trim((string) ($decoded['course_title'] ?? Str::of($resolvedCourseSlug)->replace('-', ' ')->headline()->toString())),
            'description' => trim((string) ($decoded['description'] ?? '')),
            'total_planned_lessons' => $plannedTotal,
            'defaults' => [
                'interface_locale' => $this->nullableString($decoded['defaults']['interface_locale'] ?? 'uk') ?? 'uk',
                'study_locale' => $this->nullableString($decoded['defaults']['study_locale'] ?? 'uk') ?? 'uk',
                'target_locale' => $this->nullableString($decoded['defaults']['target_locale'] ?? 'en') ?? 'en',
                'level' => $this->nullableString($decoded['defaults']['level'] ?? 'A1') ?? 'A1',
                'mode' => $this->nullableString($decoded['defaults']['mode'] ?? 'compose_tokens') ?? 'compose_tokens',
            ],
            'lessons' => $lessons,
        ];
    }

    private function normalizeBlueprintLesson(array $lesson, int $fallbackOrder): array
    {
        $lessonOrder = max(1, (int) ($lesson['lesson_order'] ?? $fallbackOrder));
        $slug = trim((string) ($lesson['slug'] ?? ''));

        if ($slug === '') {
            throw new RuntimeException('Blueprint lesson slug cannot be empty.');
        }

        $status = strtolower(trim((string) ($lesson['status'] ?? 'planned')));
        if (! in_array($status, ['implemented', 'planned'], true)) {
            $status = 'planned';
        }

        return [
            'lesson_order' => $lessonOrder,
            'slug' => $slug,
            'title' => trim((string) ($lesson['title'] ?? Str::of($slug)->replace('-', ' ')->headline()->toString())),
            'topic' => trim((string) ($lesson['topic'] ?? '')),
            'level' => trim((string) ($lesson['level'] ?? 'A1')),
            'status' => $status,
            'previous_lesson_slug' => $this->nullableString($lesson['previous_lesson_slug'] ?? null),
            'next_lesson_slug' => $this->nullableString($lesson['next_lesson_slug'] ?? null),
            'theory_category_slug' => $this->nullableString($lesson['theory_category_slug'] ?? null),
            'theory_page_slug' => $this->nullableString($lesson['theory_page_slug'] ?? null),
        ];
    }

    private function buildValidation(array $blueprintLessons, array $manifestLessons, array $manifestBySlug): array
    {
        $blueprintBySlug = [];
        foreach ($blueprintLessons as $lesson) {
            $blueprintBySlug[$lesson['slug']] = $lesson;
        }

        $brokenPreviousRefs = [];
        $brokenNextRefs = [];
        $graphMismatches = [];

        foreach ($blueprintLessons as $lesson) {
            $previous = $lesson['previous_lesson_slug'];
            $next = $lesson['next_lesson_slug'];

            if ($previous !== null && ! array_key_exists($previous, $blueprintBySlug)) {
                $brokenPreviousRefs[] = [
                    'slug' => $lesson['slug'],
                    'previous_lesson_slug' => $previous,
                ];
            }

            if ($next !== null && ! array_key_exists($next, $blueprintBySlug)) {
                $brokenNextRefs[] = [
                    'slug' => $lesson['slug'],
                    'next_lesson_slug' => $next,
                ];
            }

            if ($previous !== null && array_key_exists($previous, $blueprintBySlug)) {
                $expectedNext = $blueprintBySlug[$previous]['next_lesson_slug'] ?? null;
                if ($expectedNext !== $lesson['slug']) {
                    $graphMismatches[] = [
                        'slug' => $lesson['slug'],
                        'type' => 'blueprint_previous_next_mismatch',
                        'expected_previous_next_lesson_slug' => $lesson['slug'],
                        'actual_previous_next_lesson_slug' => $expectedNext,
                    ];
                }
            }

            if ($next !== null && array_key_exists($next, $blueprintBySlug)) {
                $expectedPrevious = $blueprintBySlug[$next]['previous_lesson_slug'] ?? null;
                if ($expectedPrevious !== $lesson['slug']) {
                    $graphMismatches[] = [
                        'slug' => $lesson['slug'],
                        'type' => 'blueprint_next_previous_mismatch',
                        'expected_next_previous_lesson_slug' => $lesson['slug'],
                        'actual_next_previous_lesson_slug' => $expectedPrevious,
                    ];
                }
            }

            $manifestLesson = $manifestBySlug[$lesson['slug']] ?? null;
            if (! is_array($manifestLesson)) {
                continue;
            }

            if (($manifestLesson['previous_lesson_slug'] ?? null) !== $previous) {
                $graphMismatches[] = [
                    'slug' => $lesson['slug'],
                    'type' => 'manifest_previous_mismatch',
                    'expected' => $previous,
                    'actual' => $manifestLesson['previous_lesson_slug'] ?? null,
                ];
            }

            if (($manifestLesson['next_lesson_slug'] ?? null) !== $next) {
                $graphMismatches[] = [
                    'slug' => $lesson['slug'],
                    'type' => 'manifest_next_mismatch',
                    'expected' => $next,
                    'actual' => $manifestLesson['next_lesson_slug'] ?? null,
                ];
            }
        }

        return [
            'broken_previous_refs' => $brokenPreviousRefs,
            'broken_next_refs' => $brokenNextRefs,
            'graph_mismatches' => $graphMismatches,
            'implemented_missing_from_blueprint' => array_values(array_filter(array_map(
                function (array $lesson) use ($blueprintBySlug) {
                    return array_key_exists($lesson['slug'], $blueprintBySlug) ? null : $lesson['slug'];
                },
                $manifestLessons
            ))),
        ];
    }

    private function resolveBlueprintPath(string $courseSlug): string
    {
        return database_path('seeders/V3/Polyglot/Course/' . trim($courseSlug) . '.json');
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
