<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuestionLevelSeeder extends Seeder
{
    public function run(): void 
    {
        $mapping = [
            PastSimpleOrPastContinuousTestSeeder::class => 'B1',
            ThisThatTheseThoseExercise3Seeder::class     => 'A1',
            PastSimpleContinuousStorySeeder::class       => 'B1',
            CanCantAbilityExercise3Seeder::class         => 'A1',
            GrammarTestSeeder::class                     => 'B1',
            GrammarQuizPastSimpleSeeder::class           => 'A2',
            PresentContinuousStorySeeder::class          => 'A2',
            PastSimpleRegularSeeder::class               => 'A2',
            QuizPresentSimpleSeeder::class               => 'A1',
            RevisionTensesFullSeeder::class              => 'B2',
            PresentContinuousShortAnswersSeeder::class   => 'A1',
            ThisThatTheseThoseExercise2Seeder::class     => 'A1',
            PresentContinuousPastSimpleTestSeeder::class => 'B1',
            ThisThatTheseThoseSeeder::class              => 'A1',
            PcImageTestSeeder::class                     => 'B1',
            PresentSimpleExercisesSeeder::class          => 'A1',
            PresentContinuousShortFormsSeeder::class     => 'A1',
            PastPresFutContinuousSeeder::class           => 'B1',
            FutConImageTestSeeder::class                 => 'B1',
            PastContinuousTenseTestSeeder::class         => 'B1',
            PresentSimpleSeeder::class                   => 'A1',
            HaveGotExercise3Seeder::class                => 'A1',
            PastSimpleContinuousImageTestSeeder::class   => 'B1',
            HaveGotHasGotSeeder::class                   => 'A1',
            CanCantAbilitySeeder::class                  => 'A1',
            PastSimpleRegularVerbsFullSeeder::class      => 'A2',
            PastSimpleContinuousSentencesTestSeeder::class => 'B1',
            CanCantAbilityExercise2Seeder::class         => 'A1',
            PresentPastRevisionSeeder::class             => 'B1',
            ShortAnswersSeeder::class                    => 'A1',
            FutureSimpleTest1Seeder::class               => 'A2',
            PresentSimpleOrContinuousSeeder::class       => 'A2',
            HaveGotExercise2Seeder::class                => 'A1',
            DoDoesIsAreSeeder::class                     => 'A1',
            ToBeTenseSeeder::class                       => 'A1',
            GrammarTestAISeeder::class                   => 'B2',
            PresentContinuousDialogueSeeder::class       => 'A2',
            SimplePresentPastSeeder::class               => 'A1',
            TestContiniusesSeeder::class                 => 'B1',
        ];

        foreach ($mapping as $seederClass => $level) {
            $slug   = Str::slug(class_basename($seederClass));
            $prefix = substr($slug, 0, 33);
            Question::where('uuid', 'like', $prefix . '%')->update(['level' => $level]);
        }
    }
}
