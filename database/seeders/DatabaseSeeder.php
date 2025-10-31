<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Support\Database\Seeder;
use Database\Seeders\Ai\FirstConditionalAiFormsV2Seeder;
use Database\Seeders\Ai\FirstConditionalChooseABCAiSeeder;
use Database\Seeders\Ai\ConditionalsComprehensiveAiSeeder;
use Database\Seeders\Ai\ConditionalsModalComprehensiveAiSeeder;
use Database\Seeders\Ai\NegativePresentPerfectHabitsTestSeeder;
use Database\Seeders\Ai\ModalVerbsComprehensiveAiSeeder;
use Database\Seeders\Ai\ModalVerbsModalOnlyAiSeeder;
use Database\Seeders\Ai\SecondConditionalComprehensiveAiSeeder;
use Database\Seeders\DragDrop\QuestionWordsDragDropSeeder;
use Database\Seeders\Pages\GrammarPagesSeeder;
use Database\Seeders\V2\PastTimeClausesMixedTestSeeder;
use Database\Seeders\V2\FutureTensesPracticeV2Seeder;
use Database\Seeders\V2\FirstConditionalPracticeV2Seeder;
use Database\Seeders\V2\FirstConditionalChooseABCV2Seeder;
use Database\Seeders\V2\SecondConditionalTestV2Seeder;
use Database\Seeders\V2\ConditionalsMixedPracticeV2Seeder;
use Database\Seeders\Ai\ConditionalsMixedPracticeCustomSeeder;
use Database\Seeders\V2\MixedConditionalsBridgePracticeV2Seeder;
use Database\Seeders\V2\ConditionalsType1And2WorksheetV2Seeder;
use Database\Seeders\V2\ConditionalsZeroToSecondWorksheetV2Seeder;
use Database\Seeders\V2\IfClausesType012WorksheetV2Seeder;
use Database\Seeders\V2\ThirdConditionalPracticeV2Seeder;
use Database\Seeders\V2\Modals\ModalObligationNecessityV2Seeder;
use Database\Seeders\V2\Modals\ModalDeductionPossibilityV2Seeder;

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
            PastPerfectSimpleVsContinuousCTestSeeder::class,
            PastTimeClausesMixedTestSeeder::class,
            ConditionalsType1And2WorksheetV2Seeder::class,
            ConditionalsZeroToSecondWorksheetV2Seeder::class,
            IfClausesType012WorksheetV2Seeder::class,
            ConditionalsMixedPracticeV2Seeder::class,
            ConditionalsMixedPracticeCustomSeeder::class,
            MixedConditionalsBridgePracticeV2Seeder::class,
            FutureTensesPracticeV2Seeder::class,
            ModalObligationNecessityV2Seeder::class,
            ModalDeductionPossibilityV2Seeder::class,
            FirstConditionalPracticeV2Seeder::class,
            FirstConditionalChooseABCV2Seeder::class,
            SecondConditionalTestV2Seeder::class,
            ThirdConditionalPracticeV2Seeder::class,
            GrammarPagesSeeder::class,
            FirstConditionalAiFormsV2Seeder::class,
            FirstConditionalChooseABCAiSeeder::class,
            IrregularVerbsSeeder::class,
            FutureSimpleFutureContinuousFuturePerfectTestSeeder::class,
            FutureSimpleOrFutureContinuousSeeder::class,
            NegativePresentPerfectHabitsTestSeeder::class,
            MixedTenseUsageAiSeeder::class,
            Ai\MixedPerfectTenseDetailedSeeder::class,
            Ai\PastPerfectComprehensiveAiSeeder::class,
            Ai\PastTenseFormsAiSeeder::class,
            Ai\FutureTensesComprehensiveAiSeeder::class,
            Ai\FutureTensesPracticeComprehensiveAiSeeder::class,
            Ai\DoDoesIsAreFormsComprehensiveAiSeeder::class,
            ConditionalsComprehensiveAiSeeder::class,
            ConditionalsModalComprehensiveAiSeeder::class,
            SecondConditionalComprehensiveAiSeeder::class,
            ModalVerbsComprehensiveAiSeeder::class,
            ModalVerbsModalOnlyAiSeeder::class,
            QuestionWordsDragDropSeeder::class,

        ]);
    }
}
