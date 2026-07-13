<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SavedTestClientAnswerLogicTest extends TestCase
{
    public function test_shared_answer_matcher_canonicalizes_future_negative_forms(): void
    {
        $helper = file_get_contents($this->resourcePath('views/components/saved-test-js-helpers.blade.php'));

        $this->assertStringContainsString('function canonicalTestAnswer(value)', $helper);
        $this->assertStringContainsString('.replace(/[‘’ʼ`]/g, "\'")', $helper);
        $this->assertStringContainsString('.replace(/\\bwill\\s+not\\b/g, "won\'t")', $helper);
        $this->assertStringContainsString('function testAnswerMatches(question, slotIndex, value)', $helper);
    }

    public function test_all_new_answer_checking_modes_use_the_shared_matcher(): void
    {
        $views = [
            'card-easy.blade.php',
            'card-medium.blade.php',
            'card-hard.blade.php',
            'card-expert.blade.php',
            'step-easy.blade.php',
            'step-medium.blade.php',
            'step-hard.blade.php',
            'step-expert.blade.php',
            'dialogue.blade.php',
        ];

        foreach ($views as $view) {
            $source = file_get_contents($this->resourcePath("views/test-modes/{$view}"));
            $this->assertStringContainsString('testAnswerMatches(', $source, $view);
        }
    }

    public function test_easy_modes_use_live_first_attempt_accuracy_and_only_explain_wrong_answers(): void
    {
        $views = [
            'card-easy.blade.php' => 'pct(state.correct, state.answered)',
            'step-easy.blade.php' => 'pct(state.correct, answered)',
        ];

        foreach ($views as $view => $accuracyExpression) {
            $source = file_get_contents($this->resourcePath("views/test-modes/{$view}"));

            $this->assertStringContainsString("let explanationPromise = Promise.resolve('');", $source, $view);
            $this->assertStringContainsString('explanationPromise = ensureExplanation(', $source, $view);
            $this->assertStringContainsString($accuracyExpression, $source, $view);
            $this->assertStringContainsString("testUi('status.corrected_after_retry')", $source, $view);
        }
    }

    public function test_persistence_prefers_the_newest_snapshot_and_serializes_server_writes(): void
    {
        $helper = file_get_contents($this->resourcePath('views/components/saved-test-js-helpers.blade.php'));

        $this->assertStringContainsString('meta.saved_at = new Date().toISOString();', $helper);
        $this->assertStringContainsString('savedAt(localState) > savedAt(serverState)', $helper);
        $this->assertStringContainsString('let JS_TEST_SAVE_QUEUE = Promise.resolve();', $helper);
        $this->assertStringContainsString('JS_TEST_SAVE_QUEUE = JS_TEST_SAVE_QUEUE', $helper);
    }

    private function resourcePath(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
