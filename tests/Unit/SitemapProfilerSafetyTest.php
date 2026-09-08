<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class SitemapProfilerSafetyTest extends TestCase
{
    public function test_profiler_requires_explicit_local_read_only_opt_in_before_bootstrap(): void
    {
        $script = dirname(__DIR__, 2).'/tools/diagnostics/seo-m4-1-profile.php';
        foreach ([[], ['--production'], ['--local-read-only', '../unsafe'], ['--local-read-only']] as $args) {
            $process = new Process([PHP_BINARY, '-d', 'opcache.enable_cli=0', $script, ...$args]);
            $process->run();
            $this->assertSame(2, $process->getExitCode());
            $this->assertStringContainsString('Usage:', $process->getErrorOutput());
            $this->assertSame('', $process->getOutput());
        }
    }
}
