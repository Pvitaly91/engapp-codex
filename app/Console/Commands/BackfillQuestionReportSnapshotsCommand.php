<?php

namespace App\Console\Commands;

use App\Services\QuestionReportFileStore;
use Illuminate\Console\Command;

class BackfillQuestionReportSnapshotsCommand extends Command
{
    protected $signature = 'question-reports:backfill-snapshots {--dry-run : Show what would be changed without writing files} {--only-open : Only backfill reports whose status is not fixed}';

    protected $description = 'Backfill question_snapshot blocks for file-based question reports.';

    public function handle(QuestionReportFileStore $reports): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyOpen = (bool) $this->option('only-open');

        $stats = $reports->backfillMissingSnapshots($dryRun, $onlyOpen);

        $this->info('Question report snapshot backfill');
        $this->line('Mode: '.($dryRun ? 'dry-run' : 'write'));
        $this->line('Only open: '.($onlyOpen ? 'yes' : 'no'));
        $this->line('Total reports: '.$stats['total']);
        $this->line('Already had snapshot: '.$stats['already_had_snapshot']);
        $this->line('Backfilled: '.$stats['backfilled']);
        $this->line('Would backfill: '.$stats['would_backfill']);
        $this->line('Skipped fixed: '.$stats['skipped_fixed']);
        $this->line('Skipped question not found: '.$stats['skipped_question_not_found']);
        $this->line('Invalid JSON: '.$stats['invalid_json']);
        $this->line('Failed: '.$stats['failed']);

        foreach ($stats['files'] as $file) {
            $status = $file['status'] ?? 'unknown';
            $path = $file['file'] ?? '';

            if ($status === 'would_backfill') {
                $this->line("Would backfill: {$path}");
            } elseif ($status === 'backfilled') {
                $this->line("Backfilled: {$path}");
            } elseif ($status === 'question_not_found') {
                $this->warn("Skipped question not found: {$path}");
            } elseif ($status === 'invalid_json' || $status === 'failed') {
                $this->error("{$status}: {$path}");
            }
        }

        return $stats['failed'] > 0 || $stats['invalid_json'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
