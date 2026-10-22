<?php

namespace Tests\Feature;

use App\Support\M78PhrasalQuestionBasics;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Isolated native JavaScript contract: never a claim of actual browser acceptance. */
class M78NativePracticeTest extends TestCase
{
    public function test_all_sixty_seven_tasks_and_retained_demos_use_the_unchanged_native_factory(): void
    {
        $root = dirname(__DIR__, 2);
        $draft = getenv('GRAMLYZE_M78_PARTS_SHA256');
        $command = [getenv('NODE_BINARY') ?: 'node', $root.'/tests/Browser/m78-native-practice.cjs'];
        if (is_string($draft) && $draft !== '') {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $draft);
            $command = [...$command, '--parts', '--parts-sha256', $draft];
            $mode = 'explicit SHA-bound six-author draft; NOT frozen or browser acceptance';
            $expectedSha = $draft;
        } else {
            $release = M78PhrasalQuestionBasics::load($root);
            M78PhrasalQuestionBasics::validate($release[0], $release[1], $release[2], $release[3]);
            $mode = 'frozen final M78 package; NOT live browser acceptance';
            $expectedSha = M78PhrasalQuestionBasics::MASTER_SHA;
        }
        $factoryRoot = getenv('GRAMLYZE_M78_FACTORY_ROOT');
        if (is_string($factoryRoot) && $factoryRoot !== '') {
            $command = [...$command, '--factory-root', $factoryRoot];
        }
        $process = new Process($command, $root, timeout: 30);
        $process->run();
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('actual unchanged native theoryPracticeSet factory; offline engine contract only', $result['kind']);
        self::assertSame($mode, $result['mode']);
        self::assertSame($expectedSha, $result['master_sha256']);
        self::assertCount(6, $result['lessons']);
        foreach (['graded_tasks' => 67, 'retained_graded_tasks' => 45, 'new_graded_tasks' => 22,
            'retained_graded_rephrase' => 13, 'ungraded_demos' => 4, 'accepted_values_checked' => 76,
            'displayed_options_checked' => 85, 'wrong_correct_empty_retry_paths' => 67,
            'native_inputs' => 22, 'full_sentence_inputs' => 18, 'partial_inputs' => 4,
            'partial_manual_nominal_size_cases' => 8,
            'manual_path_cases' => 36, 'token_path_cases' => 36, 'full_sentence_mode_size_cases' => 72,
            'deletion_returns_token_paths' => 36, 'clear_return_rebuild_paths' => 36,
            'mixed_manual_token_paths' => 18] as $field => $expected) {
            self::assertSame($expected, $result[$field], $field);
        }
        self::assertSame([6, 6, 14, 13, 13, 15], array_column($result['lessons'], 'graded'));
        self::assertSame([0, 0, 1, 1, 1, 1], array_column($result['lessons'], 'ungraded_demos'));
        self::assertSame([2, 2, 4, 4, 4, 2], array_column($result['lessons'], 'full_sentence_inputs'));
        self::assertSame([0, 0, 0, 0, 0, 4], array_column($result['lessons'], 'partial_inputs'));
        self::assertSame([320, 1280], $result['nominal_widths']);
        self::assertCount(36, $result['token_settled_texts']);
        self::assertGreaterThanOrEqual(50, $result['semantic_cases']);
        self::assertGreaterThan(6000, $result['assertions']);
        foreach (['width_labels_are_not_layout_acceptance', 'token_word_suggestions_suppressed',
            'independent_group_scores_and_reset', 'source_bytes_unchanged'] as $field) {
            self::assertTrue($result[$field], $field);
        }
        foreach (['network_calls', 'sql_calls', 'file_writes'] as $field) {
            self::assertSame(0, $result[$field], $field);
        }
    }
}
