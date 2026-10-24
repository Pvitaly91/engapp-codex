<?php

namespace Tests\Feature;

use App\Support\M85VerbToBe as Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Frozen author data through the unchanged native factory; no browser or working DB. */
class M85VerbToBeNativePracticeTest extends TestCase
{
    public function test_all_36_tasks_and_12_full_inputs_replay_the_native_model_offline(): void
    {
        $root = dirname(__DIR__, 2);
        [$before, $patch, $master, $contract] = Package::load($root);
        Package::validate($before, $patch, $master, $contract);
        $factory = getenv('GRAMLYZE_M85_FACTORY_ROOT') ?: $root;
        $process = new Process([getenv('NODE_BINARY') ?: 'node', $root.'/tests/Browser/m85-native-practice.cjs', $factory], $root, timeout: 45);
        $process->setInput(json_encode(['lessons' => $master['lessons']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $r = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        foreach (['tasks' => 36, 'demos' => 0, 'full_inputs' => 12, 'manual_token_nominal_cases' => 48,
            'delete_return_cases' => 24, 'mixed_edit_cases' => 24, 'clear_rebuild_cases' => 24,
            'group_score_cases' => 18, 'semantic_cases' => 37, 'terminal_positive_reply_rejections' => 3, 'network_calls' => 0, 'db_calls' => 0, 'file_writes' => 0] as $key => $expected) {
            self::assertSame($expected, $r[$key], $key);
        }
        self::assertSame([6, 6, 6, 6, 6, 6], array_column($r['lessons'], 'graded'));
        self::assertSame([0, 0, 0, 0, 0, 0], array_column($r['lessons'], 'demos'));
        self::assertSame([2, 2, 2, 2, 2, 2], array_column($r['lessons'], 'full_inputs'));
        self::assertGreaterThanOrEqual(36, $r['aliases']); self::assertGreaterThanOrEqual(36, $r['shown_options']);
        self::assertSame([1440, 320], $r['nominal_widths']);
        self::assertTrue($r['width_labels_are_not_layout_acceptance']); self::assertTrue($r['source_bytes_unchanged']);
        self::assertSame('offline native factory only; no live browser or layout claim', $r['acceptance']);
    }
}
