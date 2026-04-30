<?php

namespace App\Services;

class CourseCatalogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function courses(): array
    {
        return [
            [
                'slug' => 'polyglot-english',
                'title' => 'Polyglot English',
                'description' => __('public.courses.polyglot_description'),
                'levels' => array_map(
                    fn (string $level): array => $this->polyglotLevel($level),
                    ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
                ),
            ],
        ];
    }

    /**
     * @return array{level: string, course_slug: string, url: string}
     */
    private function polyglotLevel(string $level): array
    {
        $courseSlug = 'polyglot-english-'.strtolower($level);

        return [
            'level' => $level,
            'course_slug' => $courseSlug,
            'url' => localized_route('courses.show', $courseSlug, false),
        ];
    }
}
