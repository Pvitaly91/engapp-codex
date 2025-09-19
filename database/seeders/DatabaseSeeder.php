<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Database\Seeders\Ai\MixedTenseUsageAiSeeder;
use Database\Seeders\Ai\NegativePresentPerfectHabitsTestSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $categories = [
            1 => 'Past',
            2 => 'Present',
            3 => 'Present Continuous',
            4 => 'Future',
            5 => 'Present Perfect',
            6 => 'Conditionals',
        ];

        foreach ($categories as $id => $name) {
            Category::firstOrCreate(['id' => $id], ['name' => $name]);
        }

        $this->call([
            TenseTagsSeeder::class,
            DoDoesIsAreSeeder::class,
            FutureSimpleTest1Seeder::class,
            GrammarQuizPastSimpleSeeder::class,
            GrammarTestAISeeder::class,
            GrammarTestSeeder::class,
            PastSimpleRegularSeeder::class,
            PastSimpleRegularVerbsFullSeeder::class,
            PresentPastRevisionSeeder::class,
            PresentSimpleExercisesSeeder::class,
            PresentSimpleSeeder::class,
            QuizPresentSimpleSeeder::class,
            RevisionTensesFullSeeder::class,
            ShortAnswersSeeder::class,
            PresentContinuousDialogueSeeder::class,
            PresentContinuousShortAnswersSeeder::class,
            PresentContinuousShortFormsSeeder::class,
            SimplePresentPastSeeder::class,
            ThisThatTheseThoseExercise2Seeder::class,
            ThisThatTheseThoseExercise3Seeder::class,
            ThisThatTheseThoseSeeder::class,
            ToBeTenseSeeder::class,
            WordsWithTranslationsSeeder::class,
            PronounWordsSeeder::class,
            SentenceTranslationSeeder::class,
            CanCantAbilitySeeder::class,
            CanCantAbilityExercise2Seeder::class,
            CanCantAbilityExercise3Seeder::class,
            HaveGotHasGotSeeder::class,
            HaveGotExercise2Seeder::class,
            HaveGotExercise3Seeder::class,
            TestsSqlSeeder::class,
            WordsFromSentencesSeeder::class,
            WordsFromOptionsSeeder::class,
            ChatGPTExplanationsSeeder::class,
            ChatGPTTranslationChecksSeeder::class,
            PresentContinuousStorySeeder::class,
            PresentSimpleOrContinuousSeeder::class,
            PresentContinuousPastSimpleTestSeeder::class,
            PastContinuousTenseTestSeeder::class,
            PastSimpleContinuousSentencesTestSeeder::class,
            PastSimpleContinuousImageTestSeeder::class,
            PastSimpleOrPastContinuousTestSeeder::class,
            PastSimpleContinuousStorySeeder::class,
            ThereIsThereAreSeeder::class,
            ThereIsThereAreImageTestSeeder::class,
            SomeAnyTestSeeder::class,
            SomeAnyTest2Seeder::class,
            AAnSomeAnyTestSeeder::class,
            AAnTheTestSeeder::class,
            AAnTheTest2Seeder::class,
            PresentPerfectPastSimpleTestSeeder::class,
            PresentPerfectExercisesSeeder::class,
            PresentPerfectOrPresentPerfectContinuousExercise03Seeder::class,
            PresentPerfectOrPresentPerfectContinuousExercise04Seeder::class,
            PastSimpleOrPresentPerfectEx4Seeder::class,
            PastSimpleVsPresentPerfectMultipleChoiceSeeder::class,
            PastSimplePresentPerfectPastPerfectTestSeeder::class,
            PastSimplePresentPerfectSimpleTestSeeder::class,
            PastPerfectA2TestSeeder::class,
            PastSimpleOrPastPerfectTestSeeder::class,
            FuturePerfectVsFutureContinuousExercise1Seeder::class,
            QuestionTenseAssignmentSeeder::class,
            TestContiniusesSeeder::class,
            QuestionLevelSeeder::class,
            PastPerfectVsPastSimpleTestSeeder::class,
            PagesSeeder::class,
            IrregularVerbsSeeder::class,
            FutureSimpleFutureContinuousFuturePerfectTestSeeder::class,
            FutureSimpleOrFutureContinuousSeeder::class,
            NegativePresentPerfectHabitsTestSeeder::class,
            MixedTenseUsageAiSeeder::class,
        ]);
    }
}
