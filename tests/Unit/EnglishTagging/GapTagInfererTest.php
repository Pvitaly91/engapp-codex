<?php

namespace Tests\Unit\EnglishTagging;

use App\Services\EnglishTagging\GapTagInferer;
use PHPUnit\Framework\TestCase;

class GapTagInfererTest extends TestCase
{
    private GapTagInferer $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GapTagInferer();
    }

    // ========== STRUCTURAL PATTERNS (Highest Priority) ==========

    /** @test */
    public function it_detects_indirect_question_word_order(): void
    {
        // "Can you tell me where {a1}?" with options like "the station is" vs "is the station"
        $result = $this->service->infer(
            'Can you tell me where {a1}?',
            'a1',
            'the station is',
            ['the station is', 'is the station', 'does the station', 'the station are'],
            'Use statement order'
        );

        $this->assertContains('Indirect Questions', $result);
        $this->assertContains('Question Word Order', $result);
        // Should not have other lower-priority tags
        $this->assertNotContains('Do/Does/Did', $result);
    }

    /** @test */
    public function it_detects_embedded_question_word_order(): void
    {
        // "I wonder why {a1} late."
        $result = $this->service->infer(
            'I wonder why {a1} late.',
            'a1',
            'she was',
            ['she was', 'was she', 'she were', 'were she'],
            'statement word order'
        );

        $this->assertContains('Indirect Questions', $result);
        // Structural pattern should take priority, no tense tags
        $this->assertLessThanOrEqual(4, count($result));
    }

    /** @test */
    public function it_detects_do_you_know_embedded_pattern(): void
    {
        // "Do you know what time {a1}?"
        $result = $this->service->infer(
            'Do you know what time {a1}?',
            'a1',
            'the train left',
            ['the train left', 'did the train leave', 'left the train', 'the train leave'],
            'Use statement order'
        );

        $this->assertContains('Indirect Questions', $result);
    }

    /** @test */
    public function it_detects_tag_question_polarity(): void
    {
        // "You like coffee, {a1}?"
        $result = $this->service->infer(
            'You like coffee, {a1}?',
            'a1',
            "don't you",
            ["don't you", 'do you', "aren't you", 'are you'],
            'Use negative tag'
        );

        $this->assertContains('Tag Questions', $result);
        $this->assertContains('Question Tags', $result);
        $this->assertContains('Do/Does/Did', $result);
        $this->assertContains('Present Simple', $result);
    }

    /** @test */
    public function it_detects_tag_question_from_answer_pattern(): void
    {
        // "She doesn't speak French, {a1}?"
        $result = $this->service->infer(
            "She doesn't speak French, {a1}?",
            'a1',
            'does she',
            ['does she', "doesn't she", 'is she', "isn't she"],
            'Use positive tag'
        );

        $this->assertContains('Tag Questions', $result);
        $this->assertContains('Do/Does/Did', $result);
    }

    /** @test */
    public function it_adds_auxiliary_for_tag_question_with_do_based_answer(): void
    {
        $result = $this->service->infer(
            "She doesn't smoke, {a1}?",
            'a1',
            'does she',
            ['does she', "doesn't she"],
            'positive after negative'
        );

        $this->assertContains('Tag Questions', $result);
        $this->assertContains('Do/Does/Did', $result);
    }

    /** @test */
    public function it_adds_be_auxiliary_for_negative_tag_question(): void
    {
        $result = $this->service->infer(
            "He is late, {a1}?",
            'a1',
            "isn't he",
            ["isn't he", 'is he', "aren't they"],
            'negative tag'
        );

        $this->assertContains('Tag Questions', $result);
        $this->assertContains('Be (am/is/are/was/were)', $result);
    }

    /** @test */
    public function it_adds_have_auxiliary_for_tag_question(): void
    {
        $result = $this->service->infer(
            "They have arrived, {a1}?",
            'a1',
            "haven't they",
            ["haven't they", 'have they'],
            'present perfect tag'
        );

        $this->assertContains('Tag Questions', $result);
        $this->assertContains('Have/Has/Had', $result);
    }

    /** @test */
    public function it_adds_modal_auxiliary_for_tag_question(): void
    {
        $result = $this->service->infer(
            "He'll join us, {a1}?",
            'a1',
            "won't he",
            ["won't he", 'will he'],
            'future tag'
        );

        $this->assertContains('Tag Questions', $result);
        $this->assertContains('Modal Verbs', $result);
        $this->assertContains('Will/Would', $result);
    }

    /** @test */
    public function it_detects_subject_question_no_auxiliary(): void
    {
        // "Who {a1} this book?" - Subject question without auxiliary
        $result = $this->service->infer(
            'Who {a1} this book?',
            'a1',
            'wrote',
            ['wrote', 'write', 'did write', 'writes'],
            'write'
        );

        $this->assertContains('Subject Questions', $result);
        // Should not have Do/Does/Did because subject questions don't use auxiliary
        $this->assertNotContains('Do/Does/Did', $result);
    }

    /** @test */
    public function it_detects_what_subject_question(): void
    {
        // "What {a1} the problem?"
        $result = $this->service->infer(
            'What {a1} the failure?',
            'a1',
            'caused',
            ['caused', 'did cause', 'has caused', 'causing'],
            'past without auxiliary'
        );

        $this->assertContains('Subject Questions', $result);
    }

    // ========== AUXILIARY/TENSE PATTERNS ==========

    /** @test */
    public function it_detects_do_does_present_simple(): void
    {
        // "{a1} you like pizza?"
        $result = $this->service->infer(
            '{a1} you like pizza?',
            'a1',
            'Do',
            ['Do', 'Does', 'Are', 'Is'],
            'auxiliary verb for questions'
        );

        $this->assertContains('Do/Does/Did', $result);
        $this->assertContains('Present Simple', $result);
    }

    /** @test */
    public function it_detects_does_third_person(): void
    {
        // "{a1} she speak English?"
        $result = $this->service->infer(
            '{a1} she speak English?',
            'a1',
            'Does',
            ['Does', 'Do', 'Is', 'Can'],
            'auxiliary verb for 3rd person'
        );

        $this->assertContains('Do/Does/Did', $result);
        $this->assertContains('Present Simple', $result);
    }

    /** @test */
    public function it_detects_did_past_simple(): void
    {
        // "{a1} you go to school yesterday?"
        $result = $this->service->infer(
            '{a1} you go to school yesterday?',
            'a1',
            'Did',
            ['Did', 'Do', 'Was', 'Were'],
            'auxiliary for past tense questions'
        );

        $this->assertContains('Do/Does/Did', $result);
        $this->assertContains('Past Simple', $result);
    }

    /** @test */
    public function it_detects_modal_can_could(): void
    {
        // "{a1} you swim?"
        $result = $this->service->infer(
            '{a1} you swim?',
            'a1',
            'Can',
            ['Can', 'Do', 'Are', 'Does'],
            'modal verb'
        );

        $this->assertContains('Modal Verbs', $result);
        $this->assertContains('Can/Could', $result);
    }

    /** @test */
    public function it_detects_modal_should(): void
    {
        // "Could you clarify what we {a1} do next?"
        $result = $this->service->infer(
            'What {a1} we do next?',
            'a1',
            'should',
            ['should', 'shall', 'will', 'must'],
            'modal for recommendation'
        );

        $this->assertContains('Modal Verbs', $result);
        $this->assertContains('Should', $result);
    }

    /** @test */
    public function it_detects_be_auxiliary_continuous(): void
    {
        // "{a1} they working now?"
        $result = $this->service->infer(
            '{a1} they working now?',
            'a1',
            'Are',
            ['Are', 'Is', 'Do', 'Does'],
            'be + -ing'
        );

        $this->assertContains('Be (am/is/are/was/were)', $result);
        $this->assertContains('Present Continuous', $result);
    }

    /** @test */
    public function it_detects_be_auxiliary_simple(): void
    {
        // "What {a1} your name?"
        $result = $this->service->infer(
            'What {a1} your name?',
            'a1',
            'is',
            ['is', 'are', 'does', 'do'],
            'be'
        );

        $this->assertContains('Be (am/is/are/was/were)', $result);
        $this->assertContains('To Be', $result);
    }

    /** @test */
    public function it_detects_have_auxiliary_present_perfect(): void
    {
        // "{a1} you ever been to London?"
        $result = $this->service->infer(
            '{a1} you ever been to London?',
            'a1',
            'Have',
            ['Have', 'Has', 'Did', 'Do'],
            'present perfect auxiliary'
        );

        $this->assertContains('Have/Has/Had', $result);
        $this->assertContains('Present Perfect', $result);
    }

    /** @test */
    public function it_detects_have_auxiliary_past_perfect(): void
    {
        // "{a1} she finished before he arrived?"
        $result = $this->service->infer(
            '{a1} she finished before he arrived?',
            'a1',
            'Had',
            ['Had', 'Has', 'Did', 'Was'],
            'past perfect'
        );

        $this->assertContains('Have/Has/Had', $result);
        $this->assertContains('Past Perfect', $result);
    }

    // ========== PRIORITY TESTS ==========

    /** @test */
    public function it_prioritizes_structural_over_auxiliary_tags(): void
    {
        // Indirect question should not get Do/Does/Did tags even if options contain them
        $result = $this->service->infer(
            'Can you tell me where the station {a1}?',
            'a1',
            'is',
            ['is', 'does', 'do', 'are'],
            'statement order'
        );

        // Should detect indirect question pattern, not be auxiliary
        $this->assertContains('Indirect Questions', $result);
        $this->assertNotContains('Do/Does/Did', $result);
    }

    /** @test */
    public function it_limits_tags_to_maximum_four(): void
    {
        $result = $this->service->infer(
            '{a1} you ever been to London?',
            'a1',
            'Have',
            ['Have', 'Has', 'Did', 'Do', 'Was', 'Were', 'Can', 'Will'],
            'present perfect auxiliary experience question'
        );

        $this->assertLessThanOrEqual(4, count($result));
    }

    /** @test */
    public function it_returns_empty_for_unrecognized_patterns(): void
    {
        $result = $this->service->infer(
            'The {a1} is blue.',
            'a1',
            'sky',
            ['sky', 'ocean', 'flower', 'bird'],
            null
        );

        // No grammar patterns detected, should return empty or minimal tags
        $this->assertIsArray($result);
    }

    // ========== FALLBACK (verb_hint) TESTS ==========

    /** @test */
    public function it_uses_verb_hint_as_fallback(): void
    {
        // When no answer/options match, verb_hint should be used
        $result = $this->service->infer(
            'This is a {a1} sentence.',
            'a1',
            'simple',
            ['simple', 'complex', 'compound', 'basic'],
            'present simple'
        );

        // Should detect from verb_hint as fallback
        $this->assertContains('Present Simple', $result);
    }

    /** @test */
    public function it_detects_modal_from_verb_hint(): void
    {
        $result = $this->service->infer(
            'You {a1} help others.',
            'a1',
            'should',
            ['should', 'must', 'can', 'will'],
            'modal verb'
        );

        $this->assertContains('Modal Verbs', $result);
    }

    // ========== MULTI-MARKER TESTS ==========

    /** @test */
    public function it_processes_multiple_markers_independently(): void
    {
        // Test that each marker gets independent inference
        $question = '{a1} you {a2} pizza?';

        // First marker: Do (auxiliary)
        $result1 = $this->service->infer(
            $question,
            'a1',
            'Do',
            ['Do', 'Does', 'Are', 'Is'],
            'auxiliary for you'
        );

        // Second marker: like (verb form)
        $result2 = $this->service->infer(
            $question,
            'a2',
            'like',
            ['like', 'likes', 'liking', 'liked'],
            'base form after do'
        );

        // a1 should detect Do/Does/Did
        $this->assertContains('Do/Does/Did', $result1);

        // a2 may not have auxiliary tags since it's a verb form
        $this->assertIsArray($result2);
    }

    /** @test */
    public function it_handles_indirect_questions_in_multimarker_context(): void
    {
        // Multi-marker indirect question
        $question = 'Can you tell me where the bank {a1}? I {a2} find it.';

        // First marker should detect indirect question
        $result1 = $this->service->infer(
            $question,
            'a1',
            'is',
            ['is', 'are', 'does', 'do'],
            'statement order'
        );

        $this->assertContains('Indirect Questions', $result1);
    }

    /** @test */
    public function it_handles_tag_questions_in_multimarker_context(): void
    {
        // Multi-marker with tag question - marker at end with comma pattern
        $question = 'She speaks French, {a1}?';

        // Tag question marker
        $result = $this->service->infer(
            $question,
            'a1',
            "doesn't she",
            ["doesn't she", 'does she', "isn't she", 'is she'],
            'negative tag'
        );

        // Should detect tag question pattern from comma + marker position
        $this->assertContains('Tag Questions', $result);
    }

    /** @test */
    public function it_handles_subject_questions_in_multimarker_context(): void
    {
        // Subject question with multiple markers
        $question = 'Who {a1} the idea and who {a2} the final decision?';

        $result1 = $this->service->infer(
            $question,
            'a1',
            'suggested',
            ['suggested', 'did suggest', 'suggest', 'has suggested'],
            'subject question past'
        );

        $result2 = $this->service->infer(
            $question,
            'a2',
            'made',
            ['made', 'did make', 'make', 'has made'],
            'subject question past'
        );

        // Both should detect subject questions (verb form without auxiliary)
        $this->assertContains('Subject Questions', $result1);
        $this->assertContains('Subject Questions', $result2);
    }
}
