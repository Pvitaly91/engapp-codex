<?php

namespace Tests\Feature;

use App\Support\M76PronounRelations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Discoverable frozen-package runner. Node executes the actual existing factory, never a copied grader. */
class M76PracticeEngineTest extends TestCase
{
    public function test_all_frozen_practice_tasks_and_six_full_sentence_inputs_use_the_unchanged_native_factory(): void
    {
        $root = dirname(__DIR__, 2);
        $release = M76PronounRelations::load($root);
        M76PronounRelations::validate($release[0], $release[1], $release[2], $release[3]);
        $node = getenv('NODE_BINARY') ?: 'node';
        $command = [$node, $root.'/tests/Browser/m76-own-practice.cjs'];
        $factoryRoot = getenv('GRAMLYZE_M76_FACTORY_ROOT');
        if (is_string($factoryRoot) && $factoryRoot !== '') { $command = [...$command, '--factory-root', $factoryRoot]; }
        $process = new Process($command, $root, timeout: 30);
        $process->run(); self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('frozen final M76 package; NOT live browser acceptance', $result['mode']);
        self::assertSame(M76PronounRelations::MASTER_SHA, $result['master_sha256']);
        self::assertSame(46, $result['graded_tasks']); self::assertSame(2, $result['ungraded_demos']);
        self::assertSame(46, $result['wrong_correct_empty_retry_paths']); self::assertGreaterThanOrEqual(46, $result['accepted_values_checked']);
        self::assertSame(6, $result['full_sentence_inputs']); self::assertSame(24, $result['full_sentence_path_cases']);
        foreach (['full_sentence_manual_paths', 'full_sentence_token_paths', 'clear_return_rebuild_paths', 'mixed_manual_token_paths'] as $field) {
            self::assertSame(6, $result[$field]);
        }
        foreach (['token_word_suggestions_suppressed', 'independent_group_scores_and_reset', 'source_bytes_unchanged'] as $field) { self::assertTrue($result[$field]); }
        foreach (['network_calls', 'sql_calls', 'file_writes'] as $field) { self::assertSame(0, $result[$field]); }
    }
}
