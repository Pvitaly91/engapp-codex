<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

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
            SimplePresentPastSeeder::class,
            ThisThatTheseThoseExercise2Seeder::class,
            ThisThatTheseThoseExercise3Seeder::class,
            ThisThatTheseThoseSeeder::class,
            ToBeTenseSeeder::class,
            WordsWithTranslationsSeeder::class,
            PronounWordsSeeder::class,
            TestsSqlSeeder::class,
            QuestionTenseAssignmentSeeder::class,
        ]);
    }
}
