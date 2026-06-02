<?php

namespace App\Console\Commands;

use App\Services\TheoryDrivenCourseBuilder;
use Illuminate\Console\Command;

/**
 * Read-only audit for the theory-driven course MVP.
 * Shows every theory page, candidate-tests count, question-pool count,
 * and whether a lesson would be created. Does NOT mutate the database.
 *
 * GLZ-PROMPT-0068-THEORY-DRIVEN-COURSE-MVP
 */
class CourseAuditTheoryTestsCommand extends Command
{
    protected $signature = 'course:audit-theory-tests';

    protected $description = 'Read-only audit: theory pages, candidate tests, question pool, lesson creation plan (theory-driven course).';

    public function handle(TheoryDrivenCourseBuilder $builder): int
    {
        $rows = $builder->scan();

        if ($rows === []) {
            $this->warn('No theory pages found.');

            return self::SUCCESS;
        }

        $headers = ['order', 'category_slug', 'page_slug', 'page_title', 'cand_tests', 'pool', 'lesson?', 'reason'];
        $tableRows = [];

        foreach ($rows as $row) {
            $tableRows[] = [
                $row['order'],
                $row['category_slug'] ?? '-',
                $row['page_slug'],
                $this->truncate($row['page_title'], 40),
                $row['candidate_tests_count'],
                $row['question_pool_count'],
                $row['will_create_lesson'] ? 'yes' : 'no',
                $row['reason'],
            ];
        }

        $this->table($headers, $tableRows);

        $totalPages = count($rows);
        $withQuestions = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if ($row['will_create_lesson']) {
                $withQuestions++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line(sprintf('  total_pages:                %d', $totalPages));
        $this->line(sprintf('  pages_with_questions:       %d', $withQuestions));
        $this->line(sprintf('  skipped_without_questions:  %d', $skipped));
        $this->line(sprintf('  total_candidate_lessons:    %d', $withQuestions));

        return self::SUCCESS;
    }

    private function truncate(string $value, int $length): string
    {
        if (mb_strlen($value) <= $length) {
            return $value;
        }

        return mb_substr($value, 0, $length - 1) . '…';
    }
}
