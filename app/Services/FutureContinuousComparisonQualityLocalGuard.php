<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exact comparison leaf only; never the complete Future Forms category. */
final class FutureContinuousComparisonQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Future Continuous comparison quality';
    protected const PRIVATE_DIRECTORY = 'future-continuous-vs-future-perfect-continuous-quality';
    protected const ENDPOINT = 'future-continuous-comparison-quality-';

    public const BINDINGS = [
        'comparison' => ['page_id' => 59, 'category_id' => 12,
            'identity' => 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder',
            'path' => '/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn (array $binding): array => [
            'identity' => $binding['identity'], 'locale' => 'uk', 'path' => $binding['path'],
        ], self::BINDINGS));
    }

    /** User-approved single-task alternative to this host's corrupt CIM State0. */
    protected function windowsEvidence(int $port): array
    {
        if ($port !== 3306) { throw new RuntimeException('Exact local MySQL port required.'); }
        $script = dirname(__DIR__, 2).'/tools/diagnostics/inspect-future-continuous-comparison-local-target.ps1';
        $commands = '& {'.file_get_contents($script).'} -DatabasePort 3306';
        $process = new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe', '-NoProfile', '-NonInteractive', '-Command', $commands]);
        $process->setTimeout(20);
        $process->run();
        if (!$process->isSuccessful()) { throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.'); }
        $evidence = json_decode(trim($process->getOutput()), true, flags: JSON_THROW_ON_ERROR);
        if (($evidence['listener_evidence'] ?? null) !== 'bound-endpoint-pid-and-live-loopback-connect') {
            throw new RuntimeException('Task-specific TCP accept evidence missing.');
        }
        return $evidence;
    }
}
