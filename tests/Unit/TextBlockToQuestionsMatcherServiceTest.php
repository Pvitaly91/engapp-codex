<?php

namespace Tests\Unit;

use App\Services\Theory\TextBlockToQuestionsMatcherService;
use PHPUnit\Framework\TestCase;

class TextBlockToQuestionsMatcherServiceTest extends TestCase
{
    private TextBlockToQuestionsMatcherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TextBlockToQuestionsMatcherService();
    }

    /** @test */
    public function it_classifies_anchor_tags_correctly(): void
    {
        $tagNames = collect([
            'types of questions',
            'yes/no questions',
            'grammar',
            'some random tag',
        ]);

        $result = $this->service->classifyTags($tagNames);

        $this->assertContains('types of questions', $result['anchors']->toArray());
        $this->assertContains('yes/no questions', $result['anchors']->toArray());
        $this->assertNotContains('grammar', $result['anchors']->toArray());
        $this->assertNotContains('some random tag', $result['anchors']->toArray());
    }

    /** @test */
    public function it_classifies_detail_tags_correctly(): void
    {
        $tagNames = collect([
            'can/could',
            'will/would',
            'present simple',
            'do/does/did',
        ]);

        $result = $this->service->classifyTags($tagNames);

        $this->assertContains('can/could', $result['details']->toArray());
        $this->assertContains('will/would', $result['details']->toArray());
        $this->assertContains('present simple', $result['details']->toArray());
        $this->assertContains('do/does/did', $result['details']->toArray());
    }

    /** @test */
    public function it_separates_anchor_and_detail_tags(): void
    {
        $tagNames = collect([
            'types of questions',
            'yes/no questions',
            'can/could',
            'will/would',
            'should',
            'must',
            'may/might',
        ]);

        $result = $this->service->classifyTags($tagNames);

        // Anchors should be the theme/type tags
        $this->assertCount(2, $result['anchors']);
        $this->assertContains('types of questions', $result['anchors']->toArray());
        $this->assertContains('yes/no questions', $result['anchors']->toArray());

        // Details should be the modal verb tags
        $this->assertCount(5, $result['details']);
        $this->assertContains('can/could', $result['details']->toArray());
        $this->assertContains('will/would', $result['details']->toArray());
        $this->assertContains('should', $result['details']->toArray());
        $this->assertContains('must', $result['details']->toArray());
        $this->assertContains('may/might', $result['details']->toArray());
    }

    /** @test */
    public function it_ignores_general_tags(): void
    {
        $tagNames = collect([
            'grammar',
            'theory',
            'english grammar theme',
            'types of questions',
        ]);

        $result = $this->service->classifyTags($tagNames);

        // Only 'types of questions' should be in anchors
        $this->assertCount(1, $result['anchors']);
        $this->assertContains('types of questions', $result['anchors']->toArray());

        // Ignored tags should not appear in either category
        $this->assertNotContains('grammar', $result['anchors']->toArray());
        $this->assertNotContains('grammar', $result['details']->toArray());
        $this->assertNotContains('theory', $result['anchors']->toArray());
        $this->assertNotContains('theory', $result['details']->toArray());
    }

    /** @test */
    public function it_handles_case_insensitive_tag_matching(): void
    {
        $tagNames = collect([
            'TYPES OF QUESTIONS',
            'Yes/No Questions',
            'CAN/COULD',
        ]);

        $result = $this->service->classifyTags($tagNames);

        $this->assertCount(2, $result['anchors']);
        $this->assertCount(1, $result['details']);
    }

    /** @test */
    public function it_returns_empty_collections_for_unknown_tags(): void
    {
        $tagNames = collect([
            'some random tag',
            'another unknown tag',
        ]);

        $result = $this->service->classifyTags($tagNames);

        $this->assertTrue($result['anchors']->isEmpty());
        $this->assertTrue($result['details']->isEmpty());
    }

    /** @test */
    public function it_handles_empty_tag_collection(): void
    {
        $tagNames = collect();

        $result = $this->service->classifyTags($tagNames);

        $this->assertTrue($result['anchors']->isEmpty());
        $this->assertTrue($result['details']->isEmpty());
    }

    /** @test */
    public function it_deduplicates_tags(): void
    {
        $tagNames = collect([
            'types of questions',
            'types of questions',
            'TYPES OF QUESTIONS',
            'can/could',
            'Can/Could',
        ]);

        $result = $this->service->classifyTags($tagNames);

        // Should have unique values
        $this->assertCount(1, $result['anchors']);
        $this->assertCount(1, $result['details']);
    }

    /** @test */
    public function it_classifies_all_modal_verb_tags_as_details(): void
    {
        $modalTags = collect([
            'can/could',
            'will/would',
            'should',
            'must',
            'may/might',
            'modal verbs',
        ]);

        $result = $this->service->classifyTags($modalTags);

        foreach ($modalTags as $tag) {
            $this->assertContains(strtolower($tag), $result['details']->toArray());
        }
    }

    /** @test */
    public function it_classifies_all_question_type_tags_as_anchors(): void
    {
        $questionTypeTags = collect([
            'types of questions',
            'yes/no questions',
            'wh-questions',
            'tag questions',
            'indirect questions',
            'negative questions',
            'subject questions',
            'alternative questions',
        ]);

        $result = $this->service->classifyTags($questionTypeTags);

        foreach ($questionTypeTags as $tag) {
            $this->assertContains(strtolower($tag), $result['anchors']->toArray());
        }
    }

    /** @test */
    public function it_classifies_tense_tags_as_details(): void
    {
        $tenseTags = collect([
            'present simple',
            'past simple',
            'future simple',
            'present continuous',
            'past continuous',
            'present perfect',
            'past perfect',
        ]);

        $result = $this->service->classifyTags($tenseTags);

        foreach ($tenseTags as $tag) {
            $this->assertContains(strtolower($tag), $result['details']->toArray());
        }
    }

    /** @test */
    public function it_classifies_auxiliary_verb_tags_as_details(): void
    {
        $auxiliaryTags = collect([
            'do/does/did',
            'have/has/had',
            'be (am/is/are/was/were)',
        ]);

        $result = $this->service->classifyTags($auxiliaryTags);

        foreach ($auxiliaryTags as $tag) {
            $this->assertContains(strtolower($tag), $result['details']->toArray());
        }
    }
}
