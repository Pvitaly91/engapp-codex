<?php

namespace App\Console\Commands;

use App\Services\TheoryDrivenCourseBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Builds (idempotently) the theory-driven course on top of existing
 * Page_V3 theory pages and existing V3 / Polyglot tests.
 *
 * - Touches ONLY SavedGrammarTest rows whose slug starts with course-td-
 *   or whose filters.course_slug = "theory-driven".
 * - Never creates new Question rows.
 * - Writes a course manifest JSON consumed by PolyglotCourseBlueprintService.
 *
 * GLZ-PROMPT-0068-THEORY-DRIVEN-COURSE-MVP
 */
class CourseBuildTheoryDrivenCommand extends Command
{
    protected $signature = 'course:build-theory-driven';

    protected $description = 'Idempotently build the theory-driven course from existing Page_V3 + V3/Polyglot tests.';

    public function handle(TheoryDrivenCourseBuilder $builder): int
    {
        $this->info('Building theory-driven course...');

        $summary = DB::transaction(fn () => $builder->build());

        $this->newLine();
        $this->info('Build summary:');
        $this->line(sprintf('  total_pages:                  %d', $summary['total_pages']));
        $this->line(sprintf('  pages_with_questions:         %d', $summary['pages_with_questions']));
        $this->line(sprintf('  pages_skipped:                %d', $summary['pages_skipped']));
        $this->line(sprintf('  lessons_created:              %d', $summary['lessons_created']));
        $this->line(sprintf('  lessons_updated:              %d', $summary['lessons_updated']));
        $this->line(sprintf('  orphans_removed:              %d', $summary['orphans_removed'] ?? 0));
        $this->line(sprintf('  total_pool_questions:         %d', $summary['total_pool_questions']));
        $this->line(sprintf('  compose_compatible_questions: %d', $summary['compose_compatible_questions']));
        $this->line(sprintf('  selected_questions:           %d', $summary['selected_questions']));
        $this->line(sprintf('  skipped_non_compose_questions:%d', $summary['skipped_non_compose_questions']));
        $this->line(sprintf('  blueprint_path:               %s', $summary['blueprint_path']));

        if (! empty($summary['eligible'])) {
            $first = $summary['eligible'][0];
            $this->newLine();
            $this->info('First lesson:');
            $this->line(sprintf('  slug:           %s', $first['lesson_slug']));
            $this->line(sprintf('  page_slug:      %s', $first['page_slug']));
            $this->line(sprintf('  page_title:     %s', $first['page_title']));
            $this->line(sprintf('  topic_priority: %s', $first['topic_priority_label'] ?? '—'));
            $this->line(sprintf('  questions:      %d', count($first['selected_uuids'])));
            $this->line(sprintf('  test_url:       /test/%s/step/compose', $first['lesson_slug']));

            $this->newLine();
            $this->info('First 20 lesson slugs (priority-ordered):');
            $sample = array_slice($summary['eligible'], 0, 20);
            foreach ($sample as $row) {
                $this->line(sprintf(
                    '  %3d. [%s] %s',
                    (int) $row['lesson_order'],
                    $row['topic_priority_label'] ?? '—',
                    $row['lesson_slug']
                ));
            }
        }

        $this->newLine();
        $this->info(sprintf('Course landing URL: /courses/%s', TheoryDrivenCourseBuilder::COURSE_SLUG));

        return self::SUCCESS;
    }
}
