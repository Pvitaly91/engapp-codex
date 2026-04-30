<?php

namespace App\Console\Commands;

use App\Services\PolyglotLessonLinkIntegrityService;
use Illuminate\Console\Command;

class PolyglotRepairLinkIntegrityCommand extends Command
{
    protected $signature = 'polyglot:repair-link-integrity
        {--course=* : Limit scan/repair to one or more Polyglot course slugs}
        {--lesson=* : Limit scan/repair to one or more lesson slugs}
        {--apply : Apply repairs. Without this flag the command is dry-run only}
        {--no-reseed : Do not run canonical seeder classes before pruning invalid links}';

    protected $description = 'Scan and repair Polyglot saved-test question link integrity.';

    public function __construct(
        private readonly PolyglotLessonLinkIntegrityService $linkIntegrityService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $courses = $this->option('course') ?: [];
        $lessons = $this->option('lesson') ?: [];

        if (! $this->option('apply')) {
            $scan = $this->linkIntegrityService->scan($lessons, $courses);
            $this->writeScanSummary('Dry run', $scan);

            return ((int) ($scan['failed'] ?? 0)) === 0 ? self::SUCCESS : self::FAILURE;
        }

        $result = $this->linkIntegrityService->repair(
            $lessons,
            $courses,
            ! $this->option('no-reseed')
        );

        $this->writeScanSummary('Before repair', $result['before'] ?? []);
        $this->newLine();
        $this->line(sprintf(
            'Seeded classes: %d',
            count($result['seeded_classes'] ?? [])
        ));

        foreach (($result['seed_errors'] ?? []) as $error) {
            $this->warn(sprintf(
                'Seeder failed: %s — %s',
                (string) ($error['class'] ?? ''),
                (string) ($error['message'] ?? '')
            ));
        }

        $this->line(sprintf(
            'Pruned links: orphan=%d; duplicate=%d',
            (int) data_get($result, 'pruned.deleted_orphan_links', 0),
            (int) data_get($result, 'pruned.deleted_duplicate_links', 0)
        ));
        $this->newLine();
        $this->writeScanSummary('After repair', $result['after'] ?? []);

        return ((int) data_get($result, 'after.failed', 0)) === 0
            && ($result['seed_errors'] ?? []) === []
                ? self::SUCCESS
                : self::FAILURE;
    }

    private function writeScanSummary(string $title, array $scan): void
    {
        $this->line($title);
        $this->line(sprintf(
            'Lessons checked: %d; passed: %d; failed: %d; orphan links: %d; duplicate links: %d',
            (int) ($scan['total_lessons_checked'] ?? 0),
            (int) ($scan['passed'] ?? 0),
            (int) ($scan['failed'] ?? 0),
            (int) ($scan['orphan_links'] ?? 0),
            (int) ($scan['duplicate_links'] ?? 0)
        ));

        $affected = $scan['affected_slugs'] ?? [];

        if ($affected !== []) {
            $this->line('Affected lessons:');

            foreach ($affected as $slug) {
                $this->line(' - ' . $slug);
            }
        }
    }
}
