<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
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
            QuestionsSeeder::class,
            QuizPresentSimpleSeeder::class,
            RevisionTensesFullSeeder::class,
            ShortAnswersSeeder::class,
            SimplePresentPastSeeder::class,
            ThisThatTheseThoseExercise2Seeder::class,
            ThisThatTheseThoseExercise3Seeder::class,
            ThisThatTheseThoseSeeder::class,
            ToBeTenseSeeder::class,
            WordsWithTranslationsSeeder::class,
        ]);
    }
}
