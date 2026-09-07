<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TheoryPracticeSetTokenBankUiTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->source = file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR
            .'views'.DIRECTORY_SEPARATOR.'engram'.DIRECTORY_SEPARATOR.'theory'.DIRECTORY_SEPARATOR
            .'blocks-v3'.DIRECTORY_SEPARATOR.'practice-set.blade.php'
        );
    }

    public function test_manual_practice_inputs_suppress_native_browser_suggestions(): void
    {
        foreach (['inputs-{{ $index }}', 'rephrase-{{ $index }}'] as $inputKey) {
            $tag = $this->inputTag($inputKey);

            $this->assertStringContainsString('autocomplete="off"', $tag, $inputKey);
            $this->assertStringContainsString('autocorrect="off"', $tag, $inputKey);
            $this->assertStringContainsString('autocapitalize="none"', $tag, $inputKey);
            $this->assertStringContainsString('spellcheck="false"', $tag, $inputKey);
        }
    }

    public function test_token_bank_input_keeps_manual_sync_without_registering_word_suggestion_handlers(): void
    {
        $tag = $this->inputTag('inputs-{{ $index }}');

        $this->assertStringContainsString('@input="syncInputTokenBank({{ $index }})"', $tag);
        $this->assertMatchesRegularExpression(
            '/@unless\(\$hasInputTokenBank\).*?@input\.debounce\.150ms="searchWordSuggestions/s',
            $tag
        );
        $this->assertMatchesRegularExpression(
            '/@unless\(\$hasInputTokenBank\)\s*<div\s+x-cloak\s+x-show="isWordSuggestionOpen/s',
            $this->source
        );
    }

    public function test_token_bank_source_is_rendered_as_buttons_without_the_plain_slash_text(): void
    {
        $inputExercise = $this->sourceBetween(
            '{{-- Input Exercise --}}',
            '{{-- Rephrase Exercise --}}'
        );

        $this->assertMatchesRegularExpression(
            '/@unless\(\$hasInputTokenBank\)\s*<span>\{!! \$item\[\'before\'\] \?\? \'\' !!\}<\/span>\s*@endunless/s',
            $inputExercise
        );
        $this->assertStringContainsString(
            'x-for="token in inputTokenBank({{ $index }})"',
            $inputExercise
        );
        $this->assertStringContainsString('x-text="token.value"', $inputExercise);
    }

    public function test_token_bank_suggestions_are_rejected_by_the_alpine_state_as_a_second_guard(): void
    {
        $openMethod = $this->sourceBetween(
            'isWordSuggestionOpen(group, index) {',
            'closeWordSuggestions(group, index) {'
        );
        $searchMethod = $this->sourceBetween(
            'async searchWordSuggestions(group, index, event) {',
            'applyWordSuggestion(group, index, item) {'
        );

        $guard = "group === 'inputs' && this.inputTokenBank(index).length > 0";

        $this->assertStringContainsString($guard, $openMethod);
        $this->assertStringContainsString('return false;', $openMethod);
        $this->assertStringContainsString($guard, $searchMethod);
        $this->assertStringContainsString('this.closeWordSuggestions(group, index);', $searchMethod);
        $this->assertMatchesRegularExpression('/closeWordSuggestions\(group, index\);\s*return;/', $searchMethod);
    }

    public function test_backspace_and_manual_edits_recalculate_used_tokens_from_current_input(): void
    {
        $syncMethod = $this->sourceBetween(
            'syncInputTokenBank(index) {',
            'inputTokenWords(value) {'
        );

        $this->assertStringContainsString('const words = this.inputTokenWords(this.inputAnswers[index]);', $syncMethod);
        $this->assertStringContainsString('const usedIds = new Set();', $syncMethod);
        $this->assertStringContainsString('used: usedIds.has(token.id)', $syncMethod);
    }

    public function test_separate_choice_exercise_has_its_own_answers_checking_and_reset_state(): void
    {
        $choiceExercise = $this->sourceBetween(
            '{{-- Choice Exercise --}}',
            '{{-- Input Exercise --}}'
        );

        $this->assertStringContainsString('@if(!empty($choices))', $choiceExercise);
        $this->assertStringContainsString('@foreach($choiceOptions as $option)', $choiceExercise);
        $this->assertStringContainsString('choiceAnswers[{{ $index }}]', $choiceExercise);
        $this->assertStringContainsString("check('choices')", $choiceExercise);
        $this->assertStringContainsString("resetGroup('choices')", $choiceExercise);
        $this->assertStringContainsString('choices: config.choices || []', $this->source);
        $this->assertStringContainsString('choiceAnswers: {}', $this->source);
        $this->assertStringContainsString('choices: this.choiceAnswers', $this->source);
    }

    private function inputTag(string $inputKey): string
    {
        $pattern = sprintf(
            '/<input\b(?:(?!\/>).)*data-word-suggestion-input="%s"(?:(?!\/>).)*\/>/s',
            preg_quote($inputKey, '/')
        );

        $matched = preg_match($pattern, $this->source, $matches);

        $this->assertSame(1, $matched, "Missing practice input: {$inputKey}");

        return $matches[0];
    }

    private function sourceBetween(string $start, string $end): string
    {
        $startPosition = strpos($this->source, $start);
        $this->assertNotFalse($startPosition, "Missing source marker: {$start}");

        $endPosition = strpos($this->source, $end, $startPosition + strlen($start));
        $this->assertNotFalse($endPosition, "Missing source marker: {$end}");

        return substr($this->source, $startPosition, $endPosition - $startPosition);
    }
}
