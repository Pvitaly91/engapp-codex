<?php

namespace Tests\Feature;

use App\Support\M79QuestionTypesCompletion;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Exact unchanged native JavaScript, isolated from working MySQL, .env and browser storage. */
class M79NativePracticeTest extends TestCase
{
    public function test_all_fifty_seven_own_tasks_and_three_demos_use_the_unchanged_native_factory(): void
    {
        $root = dirname(__DIR__, 2);
        $draft = getenv('GRAMLYZE_M79_PARTS_SHA256');
        $command = [getenv('NODE_BINARY') ?: 'node', $root.'/tests/Browser/m79-native-practice.cjs'];
        if (is_string($draft) && $draft !== '') {
            self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $draft);
            $command = [...$command, '--parts', '--parts-sha256', $draft];
            $mode = 'explicit SHA-bound six-author draft; NOT frozen or browser acceptance';
            $expectedSha = $draft;
        } else {
            $release = M79QuestionTypesCompletion::load($root);
            M79QuestionTypesCompletion::validate($release[0], $release[1], $release[2], $release[3]);
            $mode = 'frozen final M79 package; NOT live browser acceptance';
            $expectedSha = M79QuestionTypesCompletion::MASTER_SHA;
        }
        $factoryRoot = getenv('GRAMLYZE_M79_FACTORY_ROOT');
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
        foreach (['graded_tasks' => 57, 'retained_graded_tasks' => 31, 'new_graded_tasks' => 26,
            'ungraded_demos' => 3, 'accepted_values_checked' => 77, 'displayed_options_checked' => 72,
            'wrong_correct_empty_retry_paths' => 57, 'native_inputs' => 20, 'full_sentence_inputs' => 16,
            'partial_inputs' => 4, 'partial_manual_nominal_size_cases' => 8, 'manual_path_cases' => 32,
            'token_path_cases' => 32, 'full_sentence_mode_size_cases' => 64, 'deletion_returns_token_paths' => 32,
            'clear_return_rebuild_paths' => 32, 'mixed_manual_token_paths' => 16] as $field => $expected) {
            self::assertSame($expected, $result[$field], $field);
        }
        self::assertSame([12, 15, 12, 6, 6, 6], array_column($result['lessons'], 'graded'));
        self::assertSame([1, 1, 1, 0, 0, 0], array_column($result['lessons'], 'ungraded_demos'));
        self::assertSame([4, 2, 4, 2, 2, 2], array_column($result['lessons'], 'full_sentence_inputs'));
        self::assertSame([0, 4, 0, 0, 0, 0], array_column($result['lessons'], 'partial_inputs'));
        self::assertSame([320, 1280], $result['nominal_widths']);
        self::assertCount(32, $result['token_settled_texts']);
        self::assertGreaterThanOrEqual(40, $result['semantic_cases']);
        self::assertGreaterThan(3000, $result['assertions']);
        foreach (['width_labels_are_not_layout_acceptance', 'token_word_suggestions_suppressed',
            'independent_group_scores_and_reset', 'source_bytes_unchanged'] as $field) {
            self::assertTrue($result[$field], $field);
        }
        foreach (['network_calls', 'sql_calls', 'file_writes'] as $field) {
            self::assertSame(0, $result[$field], $field);
        }
    }
}
