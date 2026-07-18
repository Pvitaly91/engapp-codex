<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SavedTestClientAnswerLogicTest extends TestCase
{
    public function test_shared_answer_matcher_canonicalizes_supported_negative_forms(): void
    {
        $helper = file_get_contents($this->resourcePath('views/components/saved-test-js-helpers.blade.php'));

        $this->assertStringContainsString('function canonicalTestAnswer(value)', $helper);
        $this->assertStringContainsString('.replace(/[‘’ʼ`]/g, "\'")', $helper);
        $this->assertStringContainsString('.replace(/\\bwill\\s+not\\b/g, "won\'t")', $helper);
        $this->assertStringContainsString('.replace(/\\bhave\\s+not\\b/g, "haven\'t")', $helper);
        $this->assertStringContainsString('.replace(/\\bhas\\s+not\\b/g, "hasn\'t")', $helper);
        $this->assertStringContainsString("normalized.replace(/\\bhaven't\\b/gi, 'have not')", $helper);
        $this->assertStringContainsString("normalized.replace(/\\bhasn't\\b/gi, 'has not')", $helper);
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

    public function test_easy_modes_render_green_feedback_above_answer_options(): void
    {
        $views = [
            'card-easy.blade.php' => [
                'render_start' => 'function renderQuestions(showOnlyWrong = false) {',
                'render_end' => 'function renderOptionsBlock',
                'feedback_id' => 'id="feedback-${idx}"',
                'theory_id' => 'id="theory-panel-${idx}"',
            ],
            'step-easy.blade.php' => [
                'render_start' => 'function render() {',
                'render_end' => 'function renderOptionsBlock',
                'feedback_id' => 'id="feedback"',
                'theory_id' => 'id="theory-panel"',
            ],
        ];

        foreach ($views as $view => $markers) {
            $source = file_get_contents($this->resourcePath("views/test-modes/{$view}"));
            $feedback = $this->sourceBetween(
                $source,
                'function renderFeedback(q) {',
                $view === 'card-easy.blade.php'
                    ? 'function onChoose(idx, opt) {'
                    : "document.getElementById('question-card').addEventListener('click'"
            );

            $this->assertStringContainsString(
                "q.feedback === 'correct' || q.feedback === 'corrected'",
                $feedback,
                $view
            );
            $this->assertStringContainsString("testUi('status.correct')", $feedback, $view);
            $this->assertStringContainsString("testUi('status.corrected_after_retry')", $feedback, $view);
            $this->assertStringContainsString('from-emerald-50 to-teal-50', $feedback, $view);
            $this->assertStringContainsString('border-emerald-200', $feedback, $view);
            $this->assertStringContainsString('bg-emerald-500', $feedback, $view);
            $this->assertStringContainsString('text-emerald-800', $feedback, $view);
            $this->assertStringContainsString('M5 13l4 4L19 7', $feedback, $view);
            $this->assertStringNotContainsString('amber-', $feedback, $view);
            $this->assertStringNotContainsString('yellow-', $feedback, $view);

            $render = $this->sourceBetween($source, $markers['render_start'], $markers['render_end']);
            $theoryPosition = strpos($render, $markers['theory_id']);
            $feedbackPosition = strpos($render, $markers['feedback_id']);
            $optionsPosition = strpos($render, 'renderOptionsBlock(');

            $this->assertNotFalse($theoryPosition, $view);
            $this->assertNotFalse($feedbackPosition, $view);
            $this->assertNotFalse($optionsPosition, $view);
            $this->assertGreaterThan($theoryPosition, $feedbackPosition, $view);
            $this->assertGreaterThan($feedbackPosition, $optionsPosition, $view);
            $this->assertSame(1, substr_count($render, $markers['feedback_id']), $view);
            $this->assertStringContainsString('mb-5 empty:hidden sm:mb-6', $render, $view);
            $this->assertStringContainsString('role="status" aria-live="polite"', $render, $view);
        }
    }

    public function test_easy_manual_input_only_commits_through_explicit_user_actions(): void
    {
        foreach (['card-easy.blade.php', 'step-easy.blade.php'] as $view) {
            $source = file_get_contents($this->resourcePath("views/test-modes/{$view}"));
            $optionsDismissal = $view === 'card-easy.blade.php'
                ? 'dismissManualSuggestions(idx, q.activeSlot);'
                : 'dismissManualSuggestions(currentIdx, currentQ.activeSlot);';
            $shortcut = $this->sourceBetween(
                $source,
                'function handleManualAnswerShortcut(e) {',
                'async function init'
            );

            $this->assertStringContainsString(
                "const isCommitKey = e.key === 'Enter' || (e.key === 'Tab' && !e.shiftKey);",
                $shortcut,
                $view
            );
            $this->assertStringContainsString('if (e.isComposing || !isCommitKey) return false;', $shortcut, $view);
            $this->assertStringContainsString('commitManualWord(', $shortcut, $view);
            $this->assertStringContainsString($optionsDismissal, $source, $view);

            $matched = preg_match(
                "~addEventListener\\('focusout', \\(e\\) => \\{\\s*(.*?)\\n\\s*\\}\\);~s",
                $source,
                $focusoutMatch
            );

            $this->assertSame(1, $matched, $view);
            $focusout = $focusoutMatch[1] ?? '';
            $this->assertStringContainsString('dismissManualSuggestions(', $focusout, $view);
            $this->assertStringNotContainsString('submitManualAnswer(', $focusout, $view);
            $this->assertStringNotContainsString('manualAnswerIsComplete(', $focusout, $view);

            $invalidate = $this->sourceBetween(
                $source,
                'function invalidateManualSuggestions(item, slotIndex) {',
                'function dismissManualSuggestions(idx, slotIndex) {'
            );
            $dismiss = $this->sourceBetween(
                $source,
                'function dismissManualSuggestions(idx, slotIndex) {',
                'function commitManualWord'
            );
            $commitWord = $this->sourceBetween(
                $source,
                'function commitManualWord(',
                'function applyManualWordSuggestion'
            );
            $answerProgress = $this->sourceBetween(
                $source,
                'function manualAnswerProgress(q, slotIndex, value) {',
                'function manualAnswerIsComplete'
            );

            $this->assertStringContainsString('item.wordSearchRequestBySlot[slotIndex] =', $invalidate, $view);
            $this->assertStringContainsString('item.wordSuggestionsBySlot[slotIndex] = [];', $invalidate, $view);
            $this->assertStringContainsString('invalidateManualSuggestions(item, slotIndex);', $dismiss, $view);
            $this->assertStringContainsString('updateManualSuggestionsDom(idx, slotIndex);', $dismiss, $view);
            $this->assertStringContainsString('invalidateManualSuggestions(item, slotIndex);', $commitWord, $view);
            $this->assertStringContainsString('manualAnswerProgress(item, slotIndex, answer)', $commitWord, $view);
            $this->assertStringContainsString("progress === 'prefix'", $commitWord, $view);
            $this->assertStringContainsString("'correct', currentWord, currentWord, wordIndex", $commitWord, $view);
            $this->assertStringContainsString("'incorrect', currentWord, currentWord, wordIndex", $commitWord, $view);
            $this->assertStringContainsString('item.manualWordIndexBySlot[slotIndex] = wordIndex + 1;', $commitWord, $view);
            $this->assertStringContainsString('submitManualAnswer(', $commitWord, $view);
            $this->assertStringContainsString('const hasRawPrefix = accepted.some(', $answerProgress, $view);
            $this->assertStringContainsString('const hasCanonicalPrefix = accepted.some(', $answerProgress, $view);
            $this->assertStringContainsString('if (hasRawPrefix || hasCanonicalPrefix)', $answerProgress, $view);

            $suggestion = $this->sourceBetween(
                $source,
                'function applyManualWordSuggestion(idx, slotIndex, wordIndex, value) {',
                'async function searchManualWords'
            );

            $this->assertStringContainsString('commitManualWord(', $suggestion, $view);
        }
    }

    public function test_easy_feedback_tracks_the_answer_and_exact_target_slot(): void
    {
        $views = [
            'card-easy.blade.php' => 'item',
            'step-easy.blade.php' => 'q',
        ];

        foreach ($views as $view => $questionVariable) {
            $source = file_get_contents($this->resourcePath("views/test-modes/{$view}"));
            $manualInput = $this->sourceBetween($source, 'function renderManualGapInput(', 'function focusManualAnswer');
            $sentence = $this->sourceBetween($source, 'function renderSentence(q, idx) {', 'function shouldRenderPolyglotTranslationPreview');
            $feedbackHelpers = $this->sourceBetween(
                $source,
                'function rememberFeedbackAnswer(',
                'function renderFeedback(q) {'
            );

            $this->assertStringContainsString('feedbackMeta: null,', $source, $view);
            $this->assertStringContainsString('submittedAnswer: String(submittedAnswer', $feedbackHelpers, $view);
            $this->assertStringContainsString('displayedAnswer: String(displayedAnswer', $feedbackHelpers, $view);
            $this->assertStringContainsString('wordIndex: Number.isInteger(wordIndex) ? wordIndex : null', $feedbackHelpers, $view);
            $this->assertStringContainsString('meta.slotIndex !== slotIndex', $feedbackHelpers, $view);
            $this->assertStringContainsString('Number.isInteger(meta.wordIndex)', $feedbackHelpers, $view);
            $this->assertStringContainsString("meta.result === 'incorrect' ? 'incorrect' : 'correct'", $feedbackHelpers, $view);
            $this->assertStringContainsString('data-feedback-answer', $feedbackHelpers, $view);
            $this->assertStringContainsString("testUi('status.submitted_answer'", $feedbackHelpers, $view);
            $this->assertStringContainsString('data-feedback-correct-answer', $feedbackHelpers, $view);
            $this->assertStringContainsString(
                "rememberFeedbackAnswer({$questionVariable}, slotIndex, 'correct', opt);",
                $source,
                $view
            );
            $this->assertStringContainsString(
                "rememberFeedbackAnswer({$questionVariable}, slotIndex, 'incorrect', opt);",
                $source,
                $view
            );
            $this->assertStringContainsString(
                "rememberFeedbackAnswer({$questionVariable}, slotIndex, 'corrected', opt, expected);",
                $source,
                $view
            );
            $this->assertStringContainsString('slotFeedbackState(q, slotIndex)', $source, $view);
            $this->assertStringContainsString('slotFeedbackState(q, i)', $source, $view);
            $this->assertStringContainsString('border-emerald-400', $source, $view);
            $this->assertStringContainsString('ring-emerald-100', $source, $view);
            $this->assertStringContainsString('border-red-400', $source, $view);
            $this->assertStringContainsString('ring-red-100', $source, $view);
            $this->assertStringContainsString('data-answer-state=', $source, $view);
            $this->assertStringContainsString('manualWordFeedbackState(q, slotIndex, wordIndex)', $manualInput, $view);
            $this->assertStringContainsString("feedbackState === 'incorrect'", $manualInput, $view);
            $this->assertStringContainsString('border-red-400 bg-red-50 text-red-800 ring-4 ring-red-100', $manualInput, $view);
            $this->assertTrue(
                str_contains($manualInput, 'class="${inputClass}"') || str_contains($manualInput, '${stateClass}'),
                "{$view} must apply its manual-input feedback class"
            );
            $this->assertStringContainsString('data-answer-slot="${slotIndex}"', $manualInput, $view);
            $this->assertStringContainsString('data-answer-state="${answerState}"', $manualInput, $view);
            $this->assertStringContainsString('data-manual-word-wrap="${wordIndex}"', $manualInput, $view);
            $this->assertStringContainsString('wordIndex === activeWordIndex ? renderManualSuggestions', $manualInput, $view);
            $this->assertStringContainsString('slotFeedbackState(q, i)', $sentence, $view);
            $this->assertStringContainsString('border-emerald-400 bg-emerald-50 text-emerald-800 ring-4 ring-emerald-100', $sentence, $view);
            $this->assertStringContainsString('data-answer-slot="${i}" data-answer-state="${feedbackState || \'filled\'}"', $sentence, $view);
        }

        $testPage = file_get_contents($this->resourcePath('views/test-show.blade.php'));
        $this->assertStringContainsString('input[data-manual-gap][data-answer-state="correct"]', $testPage);
        $this->assertStringContainsString('mark[data-answer-slot][data-answer-state="correct"]', $testPage);
        $this->assertStringContainsString('input[data-manual-gap][data-answer-state="incorrect"]', $testPage);
        $this->assertStringContainsString('mark[data-answer-slot][data-answer-state="incorrect"]', $testPage);

        foreach (['uk', 'en', 'pl'] as $locale) {
            $translation = file_get_contents($this->resourcePath("lang/{$locale}/frontend.php"));
            $this->assertStringContainsString("'submitted_answer' =>", $translation, $locale);
        }
    }

    public function test_easy_modes_allow_only_the_first_unfilled_gap_and_current_word(): void
    {
        foreach (['card-easy.blade.php', 'step-easy.blade.php'] as $view) {
            $source = file_get_contents($this->resourcePath("views/test-modes/{$view}"));
            $clamp = $this->sourceBetween($source, 'function clampActiveSlot(q) {', 'function getMarkerLabel');
            $sentence = $this->sourceBetween($source, 'function renderSentence(q, idx) {', 'function shouldRenderPolyglotTranslationPreview');
            $preview = $this->sourceBetween(
                $source,
                $view === 'card-easy.blade.php'
                    ? 'function renderPolyglotTranslationPreview(q, idx) {'
                    : 'function renderPolyglotTranslationPreview(q) {',
                'function buildExplanationKey'
            );
            $manualInput = $this->sourceBetween($source, 'function renderManualGapInput(', 'function focusManualAnswer');
            $submit = $this->sourceBetween($source, 'function submitManualAnswer(', 'function renderSentence');

            $this->assertStringContainsString('const firstUnfilled = findFirstUnfilledSlot(q);', $clamp, $view);
            $this->assertStringContainsString('q.activeSlot = firstUnfilled;', $clamp, $view);
            $this->assertStringContainsString('data-locked-gap=', $sentence, $view);
            $this->assertStringNotContainsString('data-gap="${i}"', $sentence, $view);
            $this->assertStringContainsString('!isFilled && isActive && !questionContainsSlotMarker(q, slotIndex)', $preview, $view);
            $this->assertStringContainsString('data-answer-state=', $preview, $view);
            $this->assertStringNotContainsString('data-gap="${slotIndex}"', $preview, $view);
            $this->assertStringContainsString('data-manual-active=', $manualInput, $view);
            $this->assertStringContainsString('disabled aria-disabled="true" tabindex="-1"', $manualInput, $view);
            $this->assertStringContainsString('wordIndex === activeWordIndex', $manualInput, $view);
            $this->assertStringContainsString('slotIndex !== item.activeSlot', $submit, $view);
            $this->assertStringNotContainsString('item.activeSlot = slotIndex', $submit, $view);
            $this->assertStringNotContainsString('activeSlot = gapIndex', $source, $view);
            $this->assertStringContainsString('manualWordIndexBySlot[slotIndex] = wordIndex + 1;', $source, $view);
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

    private function sourceBetween(string $source, string $start, string $end): string
    {
        $startPosition = strpos($source, $start);
        $this->assertNotFalse($startPosition, "Missing source marker: {$start}");

        $endPosition = strpos($source, $end, $startPosition + strlen($start));
        $this->assertNotFalse($endPosition, "Missing source marker: {$end}");

        return substr($source, $startPosition, $endPosition - $startPosition);
    }
}
