<?php

namespace Tests\Unit;

use App\Models\SavedGrammarTest;
use App\Services\TheoryPagePromptLinkedTestsService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class TheoryPageMixedInterleaveMetadataTest extends TestCase
{
    public function test_any_linked_test_can_declaratively_enable_level_aware_interleaving(): void
    {
        $service = $this->service();
        $plain = $this->savedTest(['seeder_classes' => ['BuilderSeeder']]);
        $optedIn = $this->savedTest([
            'seeder_classes' => ['StandardSeeder'],
            '__meta' => ['theory_page_mixed_interleave_question_types' => true],
        ]);

        $this->assertTrue($service->interleaves(collect([$plain, $optedIn]), collect()));
        $this->assertFalse($service->interleaves(collect([$plain]), collect()));
    }

    public function test_definition_metadata_can_enable_level_aware_interleaving(): void
    {
        $service = $this->service();
        $definitions = collect([
            'StandardSeeder' => [
                'saved_test' => [
                    'filters' => [
                        '__meta' => ['theory_page_mixed_interleave_question_types' => true],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($service->interleaves(collect(), $definitions));
    }

    private function service(): TheoryPagePromptLinkedTestsService
    {
        return new class extends TheoryPagePromptLinkedTestsService
        {
            public function interleaves(Collection $tests, Collection $definitions): bool
            {
                return $this->mixedInterleaveQuestionTypesEnabled($tests, $definitions);
            }
        };
    }

    /** @param array<string, mixed> $filters */
    private function savedTest(array $filters): SavedGrammarTest
    {
        $test = new SavedGrammarTest;
        $test->setAttribute('filters', $filters);

        return $test;
    }
}
