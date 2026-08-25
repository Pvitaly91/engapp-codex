<?php

namespace Tests\Unit;

use App\Support\SavedTestJsState;
use PHPUnit\Framework\TestCase;

class SavedTestJsStateSynonymsTest extends TestCase
{
    public function test_fresh_synonym_metadata_replaces_stale_saved_question_content(): void
    {
        $state = [
            'items' => [[
                'uuid' => 'ukm-fpq-poly-b1-05',
                'type' => 4,
                'answers' => ['team'],
                'accepted_answers_by_marker' => ['a1' => ['team']],
                'chosen' => [],
            ]],
        ];
        $fresh = [[
            'uuid' => 'ukm-fpq-poly-b1-05',
            'type' => 4,
            'answers' => ['team'],
            'accepted_answers_by_marker' => ['a1' => ['team', 'crew']],
            'answer_synonyms_by_marker' => ['a1' => ['crew']],
            'answer_synonym_tokens_by_marker' => ['a1' => ['team' => ['crew']]],
        ]];

        $merged = SavedTestJsState::mergeCurrentQuestionData($state, $fresh);

        $this->assertSame(['team', 'crew'], $merged['items'][0]['accepted_answers_by_marker']['a1']);
        $this->assertSame(['crew'], $merged['items'][0]['answer_synonyms_by_marker']['a1']);
        $this->assertSame(
            ['team' => ['crew']],
            $merged['items'][0]['answer_synonym_tokens_by_marker']['a1']
        );
        $this->assertSame($fresh, $merged['__meta']['question_data']);
    }
}
