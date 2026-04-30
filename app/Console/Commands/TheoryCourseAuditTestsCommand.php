<?php

namespace App\Console\Commands;

use App\Services\TheoryCourseManifestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TheoryCourseAuditTestsCommand extends Command
{
    protected $signature = 'theory-course:audit-tests {courseSlug=english-grammar-theory}';

    protected $description = 'Audit related V3 and Polyglot test coverage for the theory-based course';

    public function __construct(
        private TheoryCourseManifestService $manifestService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $courseSlug = trim((string) $this->argument('courseSlug'));

        if ($courseSlug !== TheoryCourseManifestService::COURSE_SLUG) {
            $this->error('Unsupported theory course slug: '.$courseSlug);

            return self::FAILURE;
        }

        $manifest = $this->manifestService->build($courseSlug, true);
        $lessons = array_values((array) ($manifest['flat_lessons'] ?? []));
        $rows = [];
        $missing = [];

        foreach ($lessons as $lesson) {
            if (! is_array($lesson)) {
                continue;
            }

            $counts = (array) ($lesson['test_counts'] ?? []);
            $standardTests = (int) ($counts['standard_tests'] ?? 0);
            $polyglotTests = (int) ($counts['polyglot_tests'] ?? 0);
            $testsTotal = (int) ($counts['tests_total'] ?? 0);
            $questionsTotal = (int) ($counts['questions_total'] ?? 0);
            $hasStandard = $standardTests > 0;
            $hasPolyglot = $polyglotTests > 0;
            $hasTests = $testsTotal > 0 && $questionsTotal > 0;
            $row = [
                'lesson_slug' => $lesson['lesson_slug'],
                'category_slug_path' => $lesson['category_slug_path'],
                'page_slug' => $lesson['page_slug'],
                'title' => $lesson['title'],
                'has_tests' => $hasTests,
                'has_standard_v3' => $hasStandard,
                'has_polyglot_v3' => $hasPolyglot,
                'tests_total' => $testsTotal,
                'standard_tests' => $standardTests,
                'polyglot_tests' => $polyglotTests,
                'questions_total_estimate' => $questionsTotal,
                'suggested_standard_seeder' => $this->suggestedSeederClass($lesson, 'standard'),
                'suggested_polyglot_seeder' => $this->suggestedSeederClass($lesson, 'polyglot'),
            ];

            $rows[] = $row;

            if (! $hasTests || ! $hasStandard || ! $hasPolyglot) {
                $missing[] = array_merge($row, [
                    'missing_standard_v3' => ! $hasStandard,
                    'missing_polyglot_v3' => ! $hasPolyglot,
                    'make_standard_command' => $this->makeSeederCommand($lesson, 'standard'),
                    'make_polyglot_command' => $this->makeSeederCommand($lesson, 'polyglot'),
                ]);
            }
        }

        $summary = [
            'course_slug' => $courseSlug,
            'total_theory_pages' => count($rows),
            'pages_with_related_tests' => collect($rows)->where('has_tests', true)->count(),
            'pages_with_standard_v3_tests' => collect($rows)->where('has_standard_v3', true)->count(),
            'pages_with_polyglot_v3_tests' => collect($rows)->where('has_polyglot_v3', true)->count(),
            'pages_with_no_tests' => collect($rows)->where('has_tests', false)->count(),
            'pages_with_only_polyglot' => collect($rows)->filter(fn (array $row): bool => $row['has_polyglot_v3'] && ! $row['has_standard_v3'])->count(),
            'pages_with_only_standard' => collect($rows)->filter(fn (array $row): bool => $row['has_standard_v3'] && ! $row['has_polyglot_v3'])->count(),
            'pages_with_both' => collect($rows)->filter(fn (array $row): bool => $row['has_standard_v3'] && $row['has_polyglot_v3'])->count(),
        ];

        $report = [
            'generated_at' => now()->toIso8601String(),
            'summary' => $summary,
            'missing' => $missing,
            'lessons' => $rows,
        ];

        $directory = storage_path('app/theory-course');
        File::ensureDirectoryExists($directory);

        $jsonPath = $directory.'/missing-tests-'.$courseSlug.'.json';
        $mdPath = $directory.'/missing-tests-'.$courseSlug.'.md';
        File::put($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        File::put($mdPath, $this->markdownReport($summary, $missing));

        $this->info('Theory course test audit complete.');
        $this->line('Total theory pages: '.$summary['total_theory_pages']);
        $this->line('Pages with related tests: '.$summary['pages_with_related_tests']);
        $this->line('Pages with standard V3 tests: '.$summary['pages_with_standard_v3_tests']);
        $this->line('Pages with Polyglot V3 tests: '.$summary['pages_with_polyglot_v3_tests']);
        $this->line('Pages with no tests: '.$summary['pages_with_no_tests']);
        $this->line('Pages with only Polyglot: '.$summary['pages_with_only_polyglot']);
        $this->line('Pages with only standard: '.$summary['pages_with_only_standard']);
        $this->line('Pages with both: '.$summary['pages_with_both']);
        $this->line('JSON report: '.$this->relativePath($jsonPath));
        $this->line('Markdown report: '.$this->relativePath($mdPath));

        return self::SUCCESS;
    }

    private function suggestedSeederClass(array $lesson, string $type): string
    {
        $base = Str::studly((string) ($lesson['page_slug'] ?? 'TheoryPage'));

        return $type === 'polyglot'
            ? 'Polyglot'.$base.'TheoryCourseLessonSeeder'
            : $base.'TheoryCourseV3Seeder';
    }

    private function makeSeederCommand(array $lesson, string $type): string
    {
        return sprintf(
            'php artisan theory-course:make-missing-test-seeder "%s" "%s" --type=%s',
            $lesson['category_slug_path'] ?? '',
            $lesson['page_slug'] ?? '',
            $type
        );
    }

    private function markdownReport(array $summary, array $missing): string
    {
        $lines = [
            '# Theory Course Missing Tests',
            '',
            '- Course: `'.$summary['course_slug'].'`',
            '- Total theory pages: '.$summary['total_theory_pages'],
            '- Pages with related tests: '.$summary['pages_with_related_tests'],
            '- Pages with no tests: '.$summary['pages_with_no_tests'],
            '- Pages with only Polyglot: '.$summary['pages_with_only_polyglot'],
            '- Pages with only standard: '.$summary['pages_with_only_standard'],
            '- Pages with both: '.$summary['pages_with_both'],
            '',
            '## Missing / Partial Coverage',
            '',
        ];

        if ($missing === []) {
            $lines[] = 'No missing or partial coverage found.';

            return implode(PHP_EOL, $lines).PHP_EOL;
        }

        foreach ($missing as $row) {
            $lines[] = sprintf(
                '- `%s/%s` — standard: %s, polyglot: %s',
                $row['category_slug_path'],
                $row['page_slug'],
                $row['has_standard_v3'] ? 'yes' : 'missing',
                $row['has_polyglot_v3'] ? 'yes' : 'missing'
            );
            $lines[] = '  - Standard seeder: `'.$row['suggested_standard_seeder'].'`';
            $lines[] = '  - Polyglot seeder: `'.$row['suggested_polyglot_seeder'].'`';
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace('\\', '/', Str::after(str_replace('\\', '/', $path), str_replace('\\', '/', base_path()))), '/');
    }
}
