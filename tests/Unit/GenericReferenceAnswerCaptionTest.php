<?php

namespace Tests\Unit;

use App\Support\NounsArticlesQuantityPracticeQuality as Quality;
use App\Support\SavedTestJsState;
use PHPUnit\Framework\TestCase;

final class GenericReferenceAnswerCaptionTest extends TestCase
{
    private function payload(array $source): array
    {
        $answer = $source['markers']['a1']['answer'];

        return [
            'uuid' => $source['uuid'], 'type' => '0', 'question' => $source['question'],
            'answer_map' => ['a1' => $answer], 'answers' => [$answer],
            'options_by_marker' => ['a1' => $source['markers']['a1']['options']],
            'verb_hint' => $source['verb_hint'], 'verb_hints' => ['a1' => $source['verb_hint']],
        ];
    }

    public function test_generic_reference_gaps_are_caption_opted_in_without_rewriting_answers_or_options(): void
    {
        $noArticle = 0;
        foreach (Quality::compile('generic-reference', 'standard')['questions'] as $source) {
            $payload = $this->payload($source);
            $rendered = Quality::decorate($payload);
            self::assertSame($rendered['psp_content_revision'], $rendered['naqq_generic_reference_caption_revision']);
            self::assertSame($payload['answer_map'], $rendered['answer_map']);
            self::assertSame($payload['options_by_marker'], $rendered['options_by_marker']);
            if ($payload['answers'] !== ['-']) {
                self::assertSame($payload['verb_hint'], $rendered['verb_hint']);
                continue;
            }

            ++$noArticle;
            self::assertStringNotContainsString('дефіс', $rendered['verb_hint']);
            self::assertStringContainsString('Без артикля', $rendered['verb_hint']);
            self::assertSame($rendered['verb_hint'], $rendered['verb_hints']['a1']);
            self::assertSame($source['target_text'], Quality::reorderSentence($rendered));
            self::assertSame($rendered, Quality::restoreGenericReferenceHint(array_replace($rendered, [
                'verb_hint' => $payload['verb_hint'], 'verb_hints' => $payload['verb_hints'],
            ]), 'uk'));
        }

        self::assertSame(14, $noArticle);
    }

    public function test_caption_is_not_enabled_for_a_modified_question_or_another_topic(): void
    {
        $generic = Quality::compile('generic-reference', 'standard')['questions'][0];
        $other = Quality::compile('zero-article', 'standard')['questions'][0];
        foreach ([$this->payload($other), array_replace($this->payload($generic), ['question' => 'Modified {a1}.'])] as $payload) {
            self::assertArrayNotHasKey('naqq_generic_reference_caption_revision', Quality::decorate($payload));
        }
    }

    public function test_cached_payload_refresh_and_state_restore_keep_previous_dash_progress(): void
    {
        $source = Quality::compile('generic-reference', 'standard')['questions'][0];
        $fresh = Quality::decorate($this->payload($source));
        $old = $fresh;
        unset($old['naqq_generic_reference_caption_revision']);
        $old['chosen'] = ['-'];
        $old['done'] = true;
        self::assertTrue(Quality::cachedNeedsRefresh([$old]));

        $merged = SavedTestJsState::mergeCurrentQuestionData(['items' => [$old]], [$fresh])['items'][0];
        self::assertSame($fresh['naqq_generic_reference_caption_revision'], $merged['naqq_generic_reference_caption_revision']);
        self::assertSame(['-'], $merged['chosen']);
        self::assertTrue($merged['done']);
    }
}
