<?php

namespace Tests\Feature;

use App\Support\M77OnePrepositionsPhrasal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Actual existing factory through Node: isolated, nonvisual, and never live browser acceptance. */
class M77PracticeEngineTest extends TestCase
{
    public function test_six_lessons_keep_the_same_native_practice_and_forty_eight_offline_mode_size_paths(): void
    {
        $root = dirname(__DIR__, 2);
        $draft = getenv('GRAMLYZE_M77_DRAFT_AUTHOR_SHA256');
        $command = [getenv('NODE_BINARY') ?: 'node', $root.'/tests/Browser/m77-practice-contract.cjs'];
        if (is_string($draft) && $draft !== '') {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $draft);
            $command = [...$command, '--draft-author-sha256', $draft];
            $mode = 'explicit SHA-bound six-author draft; NOT frozen or browser acceptance';
            $expectedSha = $draft;
        } else {
            $release = M77OnePrepositionsPhrasal::load($root);
            M77OnePrepositionsPhrasal::validate($release[0], $release[1], $release[2], $release[3]);
            $mode = 'frozen final M77 package; NOT live browser acceptance';
            $expectedSha = M77OnePrepositionsPhrasal::MASTER_SHA;
        }
        $factoryRoot = getenv('GRAMLYZE_M77_FACTORY_ROOT');
        if (is_string($factoryRoot) && $factoryRoot !== '') {
            $command = [...$command, '--factory-root', $factoryRoot];
        }
        $process = new Process($command, $root, timeout: 30);
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($mode, $result['mode']); self::assertSame($expectedSha, $result['master_sha256']);
        self::assertSame(6, count($result['lessons']));
        self::assertSame(42, $result['graded_tasks']); self::assertSame(0, $result['ungraded_demos']);
        self::assertSame(42, $result['wrong_correct_empty_retry_paths']);
        self::assertGreaterThanOrEqual(53, $result['accepted_values_checked']);
        self::assertSame(70, $result['displayed_options_checked']);
        self::assertSame(12, $result['full_sentence_inputs']);
        self::assertSame([320, 1280], $result['nominal_widths']);
        self::assertTrue($result['width_labels_are_not_layout_acceptance']);
        self::assertSame(48, $result['full_sentence_mode_size_cases']);
        foreach (['manual_path_cases', 'token_path_cases', 'deletion_returns_token_paths', 'clear_return_rebuild_paths'] as $field) {
            self::assertSame(24, $result[$field]);
        }
        self::assertSame(12, $result['mixed_manual_token_paths']);
        self::assertSame(24, count($result['token_settled_texts']));
        self::assertGreaterThanOrEqual(42, $result['semantic_cases']);
        self::assertGreaterThan(4000, $result['assertions']);
        foreach (['token_word_suggestions_suppressed', 'independent_group_scores_and_reset', 'source_bytes_unchanged'] as $field) {
            self::assertTrue($result[$field]);
        }
        foreach (['network_calls', 'sql_calls', 'file_writes'] as $field) { self::assertSame(0, $result[$field]); }
    }
}
