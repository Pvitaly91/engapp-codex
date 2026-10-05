<?php

namespace Tests\Unit;

use App\Models\SavedGrammarTest;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\SentenceReorderQuestionFactory;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class PastPerfectContinuousMixedPresentationTest extends TestCase
{
    public function test_all_four_finite_banks_opt_into_existing_balancing_without_database_filter_writes(): void
    {
        $service = (new ReflectionClass(TheoryPagePromptLinkedTestsService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($service, 'mixedInterleaveQuestionTypesEnabled');
        foreach (['Forms', 'Negatives', 'Questions', 'TimeExpressions'] as $topic) {
            $seeder = 'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuous'.$topic.'AllLevelsV3Seeder';
            $this->assertTrue($method->invoke($service, collect(), collect([$seeder => []])));
            $test = new SavedGrammarTest(['filters' => ['seeder_classes' => [$seeder]]]);
            $this->assertTrue($method->invoke($service, collect([$test]), collect()));
        }
        $this->assertFalse($method->invoke($service, collect(), collect(['Database\\Seeders\\V3\\Tenses\\OtherSeeder' => []])));
        $this->assertFalse($method->invoke($service, collect(), collect(['Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousComparisonSeeder' => []])));
    }

    public function test_each_level_keeps_seven_builders_four_gaps_and_three_reorders_even_for_long_advanced_targets(): void
    {
        $items = [];
        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            for ($number = 1; $number <= 7; $number++) {
                $items[] = ['uuid' => "$level-gap-$number", 'type' => '0', 'level' => $level,
                    'question' => 'Before the report was corrected the repeated briefings {a1} an uncertain provisional estimate as an established fact for several months.',
                    'answer_map' => ['a1' => 'had been presenting']];
                $items[] = ['uuid' => "$level-builder-$number", 'type' => '4', 'level' => $level,
                    'question' => 'Побудуй речення.', 'answer_map' => ['a1' => 'They']];
            }
        }
        $result = SentenceReorderQuestionFactory::addToMixedTheoryTest($items, ['__meta' => [
            'theory_page_mixed_polyglot_test' => true, 'theory_page_mixed_interleave_question_types' => true,
        ]]);
        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $kinds = collect($result)->where('level', $level)->map(fn ($q) => $q['type'] === '4' ? 'builder'
                : (($q['presentation'] ?? null) === SentenceReorderQuestionFactory::PRESENTATION ? 'reorder' : 'gap'))->values()->all();
            $this->assertSame(['gap', 'builder', 'reorder', 'builder', 'gap', 'builder', 'reorder', 'builder', 'gap', 'builder', 'reorder', 'builder', 'gap', 'builder'], $kinds);
        }
    }
}
