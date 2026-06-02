<?php

namespace App\Console\Commands;

use App\Services\TheoryPageTestsUnificationAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TheoryPageTestsUnificationAuditCommand extends Command
{
    protected $signature = 'theory-pages:audit-tests-unification
        {--json= : Output JSON path}
        {--md= : Output Markdown path}';

    protected $description = 'Audit Page_V3 theory pages for Sentence Builder, mixed A1-C2 tests, and theory links';

    public function handle(TheoryPageTestsUnificationAuditService $auditService): int
    {
        $result = $auditService->write(
            $this->option('json') ? (string) $this->option('json') : null,
            $this->option('md') ? (string) $this->option('md') : null,
        );

        $summary = (array) ($result['audit']['summary'] ?? []);

        $this->info('Theory page tests unification audit complete.');
        $this->line('Total theory pages audited: ' . (int) ($summary['total_theory_pages'] ?? 0));
        $this->line('Pages already OK: ' . (int) ($summary['pages_ok'] ?? 0));
        $this->line('Pages missing only theory links: ' . (int) ($summary['pages_missing_only_theory_links'] ?? 0));
        $this->line('Pages missing Sentence Builder: ' . (int) ($summary['pages_missing_sentence_builder'] ?? 0));
        $this->line('Pages missing V3: ' . (int) ($summary['pages_missing_v3'] ?? 0));
        $this->line('Pages missing both tests: ' . (int) ($summary['pages_missing_both_tests'] ?? 0));
        $this->line('JSON report: ' . $this->relativePath($result['json_path']));
        $this->line('Markdown report: ' . $this->relativePath($result['markdown_path']));

        return self::SUCCESS;
    }

    private function relativePath(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', base_path()), '/');

        return ltrim((string) Str::after($normalizedPath, $normalizedRoot), '/');
    }
}
