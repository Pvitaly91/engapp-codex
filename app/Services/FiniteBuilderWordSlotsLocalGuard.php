<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** One explicit local correction, bound to the captured finite catalog inventory. */
final class FiniteBuilderWordSlotsLocalGuard extends TheoryLocalReleaseGuard
{
    public const BASELINE_SHA256 = 'f671cd7b8d9d376e9bbb3cab539f7f8a8f3dee186b767031467e84186425a88c';
    protected const LABEL = 'Finite builder word slots';
    protected const PRIVATE_DIRECTORY = 'finite-builder-word-slots';
    protected const ENDPOINT = 'finite-builder-word-slots-';

    public static function baseline(): array
    {
        $path = self::privateDirectory().'/baseline.json';
        if (!is_file($path) || is_link($path) || hash_file('sha256', $path) !== self::BASELINE_SHA256) {
            throw new RuntimeException('Exact captured finite builder baseline is required.');
        }
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($data['schema_version'] ?? null) !== 1 || count($data['banks'] ?? []) !== 193) {
            throw new RuntimeException('Exact 193-bank baseline inventory differs.');
        }
        return $data;
    }

    public static function scope(): array
    {
        return array_map(static fn (array $bank): array => [
            'identity' => $bank['mapping']['builder']['class'], 'locale' => 'uk',
            'catalog' => $bank['catalog'], 'topic' => $bank['topic'], 'baseline_sha256' => self::BASELINE_SHA256,
        ], self::baseline()['banks']);
    }

    public static function protectedRow(string $table, array $row, array $targetIds): ?array
    {
        return PresentPerfectPassiveWordSlotsLocalGuard::protectedRow($table, $row, $targetIds);
    }

    protected function windowsEvidence(int $port): array
    {
        if ($port !== 3306) { throw new RuntimeException('Exact local MySQL port required.'); }
        $script = dirname(__DIR__, 2).'/tools/diagnostics/inspect-passive-voice-local-target.ps1';
        $process = new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe', '-NoProfile', '-NonInteractive',
            '-Command', '& {'.file_get_contents($script).'} -DatabasePort 3306']);
        $process->setTimeout(20); $process->run();
        if (!$process->isSuccessful()) { throw new RuntimeException('Local endpoint/PID evidence unavailable.'); }
        $evidence = json_decode(trim($process->getOutput()), true, flags: JSON_THROW_ON_ERROR);
        if (($evidence['listener_evidence'] ?? null) !== 'bound-endpoint-pid-and-live-loopback-connect') {
            throw new RuntimeException('Live local accept evidence missing.');
        }
        return $evidence;
    }
}
