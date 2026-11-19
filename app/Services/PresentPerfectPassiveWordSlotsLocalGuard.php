<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Explicit local-only correction of the eighteen existing Present Perfect Passive builders. */
final class PresentPerfectPassiveWordSlotsLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Present Perfect Passive word slots';
    protected const PRIVATE_DIRECTORY = 'present-perfect-passive-word-slots';
    protected const ENDPOINT = 'present-perfect-passive-word-slots-';

    public static function scope(): array
    {
        return [['identity' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentPerfectTheorySeeder',
            'locale' => 'uk', 'path' => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-perfect']];
    }

    public static function protectedRow(string $table, array $row, array $targetIds): ?array
    {
        $target = isset($targetIds[(string) ($row[$table === 'questions' ? 'id' : 'question_id'] ?? '')]);
        if (!$target) { return $row; }
        // These exact target-owned sets are independently checked against all authored markers/options.
        if (in_array($table, ['question_answers', 'verb_hints', 'question_option_question'], true)) { return null; }
        foreach (match ($table) {
            'questions' => ['question', 'options_by_marker'],
            'question_variants' => ['text'],
            'question_hints' => ($row['provider'] ?? '') === 'compose_prompt' && ($row['locale'] ?? '') === 'uk' ? ['hint'] : [],
            default => [],
        } as $column) { $row[$column] = null; }
        return $row;
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
